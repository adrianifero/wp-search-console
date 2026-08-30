#!/usr/bin/env php
<?php
/**
 * Headless tests for AT Search Console 1.2.0 property picker / GSC URL logic.
 * Not shipped in the WordPress.org zip (.distignore /bin).
 */
define( 'ABSPATH', __DIR__ . '/' );

$GLOBALS['test_home']    = 'https://infoeplus.com';
$GLOBALS['test_site']    = 'https://infoeplus.com';
$GLOBALS['test_options'] = array();

function add_action() {}
function add_filter() {}
function register_activation_hook() {}
function plugin_basename( $file ) {
	return 'at-search-console/at-search-console.php';
}
function __( $text, $domain = '' ) {
	return $text;
}
function home_url( $path = '' ) {
	return rtrim( $GLOBALS['test_home'], '/' ) . $path;
}
function site_url( $path = '' ) {
	return rtrim( $GLOBALS['test_site'], '/' ) . $path;
}
function trailingslashit( $value ) {
	return rtrim( (string) $value, '/\\' ) . '/';
}
function wp_parse_url( $url, $component = -1 ) {
	return parse_url( $url, $component );
}
function get_option( $key, $default = false ) {
	return array_key_exists( $key, $GLOBALS['test_options'] ) ? $GLOBALS['test_options'][ $key ] : $default;
}
function update_option( $key, $value ) {
	$GLOBALS['test_options'][ $key ] = $value;
	return true;
}
function delete_option( $key ) {
	unset( $GLOBALS['test_options'][ $key ] );
	return true;
}
function add_query_arg( $args, $url ) {
	return $url . ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args );
}

require dirname( __DIR__ ) . '/at-search-console.php';

$plugin = new ReflectionClass( 'AT_Search_Console' );
$obj    = $plugin->newInstanceWithoutConstructor();

function call_private( $obj, $plugin, $method, array $args = array() ) {
	$m = $plugin->getMethod( $method );
	$m->setAccessible( true );
	return $m->invokeArgs( $obj, $args );
}

$failed = 0;
function expect( $label, $ok ) {
	global $failed;
	echo ( $ok ? 'PASS' : 'FAIL' ) . "  $label\n";
	if ( ! $ok ) {
		$failed++;
	}
}

$candidates = call_private( $obj, $plugin, 'property_candidates' );
expect(
	'infoeplus.com includes sc-domain:infoeplus.com',
	isset( $candidates['sc-domain:infoeplus.com'] )
);
expect(
	'infoeplus.com includes https://infoeplus.com/',
	isset( $candidates['https://infoeplus.com/'] )
);
expect(
	'infoeplus.com includes https://www.infoeplus.com/',
	isset( $candidates['https://www.infoeplus.com/'] )
);

$GLOBALS['test_home'] = 'https://www.example.com/blog';
$GLOBALS['test_site'] = 'https://www.example.com/wp';
$candidates           = call_private( $obj, $plugin, 'property_candidates' );
expect(
	'subdir www site keeps /blog on bare host variant',
	isset( $candidates['https://example.com/blog/'] )
);
expect(
	'subdir includes site_url prefix',
	isset( $candidates['https://www.example.com/wp/'] )
);
expect(
	'subdir domain is apex',
	isset( $candidates['sc-domain:example.com'] )
);

$filter = call_private(
	$obj,
	$plugin,
	'page_filter_expression',
	array( 'https://infoeplus.com/hello-world/', 'sc-domain:infoeplus.com' )
);
expect( 'domain property filters by path', '*\/hello-world/' === $filter || '*/hello-world/' === $filter );

$filter_prefix = call_private(
	$obj,
	$plugin,
	'page_filter_expression',
	array( 'https://infoeplus.com/hello-world/', 'https://www.infoeplus.com/' )
);
expect(
	'url-prefix property filters by full URL',
	'*https://infoeplus.com/hello-world/' === $filter_prefix
);

$GLOBALS['test_home']    = 'https://infoeplus.com';
$GLOBALS['test_site']    = 'https://infoeplus.com';
$GLOBALS['test_options'] = array(
	'at_search_console_settings' => array(
		'property_type' => 'domain',
	),
);
AT_Search_Console::maybe_migrate_property_type();
$migrated = get_option( 'at_search_console_settings' );
expect(
	'1.1.x domain radio migrates to sc-domain:infoeplus.com',
	isset( $migrated['resource_id'] ) && 'sc-domain:infoeplus.com' === $migrated['resource_id']
);

$GLOBALS['test_home']    = 'https://infoeplus.com';
$GLOBALS['test_options'] = array(
	'at_search_console_settings' => array(
		'property_type' => 'url_prefix',
	),
);
AT_Search_Console::maybe_migrate_property_type();
$migrated = get_option( 'at_search_console_settings' );
expect(
	'1.1.x url_prefix radio migrates to home URL prefix',
	isset( $migrated['resource_id'] ) && 'https://infoeplus.com/' === $migrated['resource_id']
);

$GLOBALS['test_options'] = array(
	'at_search_console_settings' => array(
		'resource_id'        => 'sc-domain:infoeplus.com',
		'custom_resource_id' => '',
		'setup_dismissed'    => true,
	),
);
$gsc = call_private( $obj, $plugin, 'build_gsc_url', array( 'https://infoeplus.com/hello-world/' ) );
expect( 'admin-bar URL includes resource_id', false !== strpos( $gsc, 'resource_id=' . rawurlencode( 'sc-domain:infoeplus.com' ) ) || false !== strpos( $gsc, 'resource_id=sc-domain%3Ainfoeplus.com' ) || false !== strpos( $gsc, 'sc-domain%3Ainfoeplus.com' ) || false !== strpos( $gsc, 'sc-domain:infoeplus.com' ) );
expect( 'admin-bar URL includes 16-month range', false !== strpos( $gsc, 'num_of_months=16' ) );
expect( 'admin-bar URL includes path filter', false !== strpos( $gsc, 'hello-world' ) );

$test_url = call_private( $obj, $plugin, 'build_property_test_url', array( 'sc-domain:infoeplus.com' ) );
expect( 'test URL has no page filter', false === strpos( $test_url, 'page=' ) );

$js = file_get_contents( dirname( __DIR__ ) . '/js/settings.js' );
expect( 'settings.js updates test link from dropdown', false !== strpos( $js, 'testLink.href' ) && false !== strpos( $js, 'select.addEventListener' ) );

echo $failed ? "\n$failed failed\n" : "\nAll checks passed\n";
exit( $failed ? 1 : 0 );
