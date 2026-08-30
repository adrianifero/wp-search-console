<?php
/**
 * Plugin Name: AT Search Console
 * Plugin URI:  https://adriantoro.com/wordpress-plugins/at-search-console/
 * Description: Open the current page in Google Search Console from the WordPress admin bar. One click to that URL's performance.
 * Version:     1.2.0
 * Author:      Adrian Toro
 * Author URI:  https://adriantoro.com
 * Text Domain: at-search-console
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AT_Search_Console {

	const OPTION_KEY        = 'at_search_console_settings';
	const LEGACY_OPTION_KEY   = 'at_search_console_option';
	const VERSION             = '1.2.0';
	const INSTALL_SLUG        = 'at-search-console';
	const GSC_PERFORMANCE_URL = 'https://search.google.com/search-console/performance/search-analytics';

	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_migrate_legacy_option' ), 5 );
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_migrate_property_type' ), 6 );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 100 );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_init', array( $this, 'maybe_dismiss_setup_notice' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_settings_scripts' ) );
		add_action( 'admin_notices', array( $this, 'maybe_admin_notices' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
	}

	/**
	 * On activate: migrate legacy keys and seed a default property if none is saved.
	 */
	public static function activate() {
		self::maybe_migrate_legacy_option();
		self::maybe_migrate_property_type();

		$stored = get_option( self::OPTION_KEY, false );
		if ( false === $stored || ! is_array( $stored ) || empty( $stored['resource_id'] ) ) {
			update_option(
				self::OPTION_KEY,
				array(
					'resource_id'      => self::default_resource_id(),
					'custom_resource_id' => '',
					'setup_dismissed'  => false,
				)
			);
		}
	}

	/**
	 * Migrate legacy option `at_search_console_option` (regular|domain).
	 */
	public static function maybe_migrate_legacy_option() {
		$legacy = get_option( self::LEGACY_OPTION_KEY, false );
		if ( false === $legacy ) {
			return;
		}

		$stored = get_option( self::OPTION_KEY, false );
		if ( false === $stored || ! is_array( $stored ) ) {
			$stored = array();
		}

		if ( empty( $stored['resource_id'] ) ) {
			$stored['resource_id'] = ( 'domain' === $legacy )
				? self::domain_resource_id_for_host( wp_parse_url( home_url(), PHP_URL_HOST ) )
				: trailingslashit( home_url() );
		}

		if ( ! isset( $stored['setup_dismissed'] ) ) {
			$stored['setup_dismissed'] = true;
		}

		update_option( self::OPTION_KEY, $stored );
		delete_option( self::LEGACY_OPTION_KEY );
	}

	/**
	 * Migrate 1.1.x property_type radios into a concrete resource_id.
	 */
	public static function maybe_migrate_property_type() {
		$stored = get_option( self::OPTION_KEY, false );
		if ( ! is_array( $stored ) || ! empty( $stored['resource_id'] ) ) {
			return;
		}

		if ( empty( $stored['property_type'] ) ) {
			return;
		}

		if ( 'domain' === $stored['property_type'] ) {
			$stored['resource_id'] = self::default_resource_id();
		} else {
			$stored['resource_id'] = trailingslashit( home_url() );
		}

		unset( $stored['property_type'] );
		if ( ! isset( $stored['setup_dismissed'] ) ) {
			$stored['setup_dismissed'] = true;
		}

		update_option( self::OPTION_KEY, $stored );
	}

	/**
	 * @return array{resource_id: string, custom_resource_id: string, setup_dismissed: bool}
	 */
	private function settings() {
		$defaults = array(
			'resource_id'        => self::default_resource_id(),
			'custom_resource_id' => '',
			'setup_dismissed'    => false,
		);
		$stored   = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( $defaults, $stored );
	}

	/**
	 * Default property: domain on apex host (recommended for new installs).
	 *
	 * @return string
	 */
	private static function default_resource_id() {
		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		$id   = self::domain_resource_id_for_host( $host );
		if ( '' !== $id ) {
			return $id;
		}
		return trailingslashit( home_url() );
	}

	/**
	 * @param mixed $host Hostname.
	 * @return string sc-domain resource_id or empty.
	 */
	private static function domain_resource_id_for_host( $host ) {
		if ( ! is_string( $host ) || '' === $host ) {
			return '';
		}
		$apex = self::apex_host( $host );
		return '' !== $apex ? 'sc-domain:' . $apex : '';
	}

	/**
	 * Same URL prefix with a different host (keeps scheme, path, and port).
	 *
	 * @param string $url  Absolute site URL.
	 * @param string $host Replacement hostname.
	 * @return string Trailing-slashed prefix or empty.
	 */
	private static function url_prefix_with_host( $url, $host ) {
		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) || ! is_string( $host ) || '' === $host ) {
			return '';
		}
		$scheme = ( isset( $parsed['scheme'] ) && is_string( $parsed['scheme'] ) && '' !== $parsed['scheme'] )
			? $parsed['scheme']
			: 'https';
		$path = ( isset( $parsed['path'] ) && is_string( $parsed['path'] ) ) ? $parsed['path'] : '';
		$port = isset( $parsed['port'] ) ? ':' . $parsed['port'] : '';
		return trailingslashit( $scheme . '://' . $host . $port . $path );
	}

	/**
	 * Strip leading www. for domain properties.
	 *
	 * @param string $host Hostname.
	 * @return string
	 */
	private static function apex_host( $host ) {
		$host = strtolower( $host );
		if ( 0 === strpos( $host, 'www.' ) ) {
			return substr( $host, 4 );
		}
		return $host;
	}

	/**
	 * Likely Search Console resource_id values for this WordPress install.
	 *
	 * @return array<string, string> resource_id => label
	 */
	private function property_candidates() {
		$home   = home_url();
		$site   = site_url();
		$parsed = wp_parse_url( $home );
		$host   = ( isset( $parsed['host'] ) && is_string( $parsed['host'] ) ) ? $parsed['host'] : '';

		$candidates = array();

		$domain_id = self::domain_resource_id_for_host( $host );
		if ( '' !== $domain_id ) {
			$candidates[ $domain_id ] = sprintf(
				/* translators: %s: sc-domain resource_id */
				__( 'Domain — %s (recommended)', 'at-search-console' ),
				$domain_id
			);
		}

		$home_prefix = trailingslashit( $home );
		$candidates[ $home_prefix ] = sprintf(
			/* translators: %s: URL-prefix property */
			__( 'URL prefix — %s', 'at-search-console' ),
			$home_prefix
		);

		if ( '' !== $host && 0 !== stripos( $host, 'www.' ) ) {
			$www_prefix = self::url_prefix_with_host( $home, 'www.' . $host );
			if ( '' !== $www_prefix && ! isset( $candidates[ $www_prefix ] ) ) {
				$candidates[ $www_prefix ] = sprintf(
					/* translators: %s: URL-prefix property with www */
					__( 'URL prefix — %s', 'at-search-console' ),
					$www_prefix
				);
			}
		}

		if ( '' !== $host && 0 === stripos( $host, 'www.' ) ) {
			$bare_prefix = self::url_prefix_with_host( $home, substr( $host, 4 ) );
			if ( '' !== $bare_prefix && ! isset( $candidates[ $bare_prefix ] ) ) {
				$candidates[ $bare_prefix ] = sprintf(
					/* translators: %s: URL-prefix property without www */
					__( 'URL prefix — %s', 'at-search-console' ),
					$bare_prefix
				);
			}
		}

		$site_prefix = trailingslashit( $site );
		if ( $site_prefix !== $home_prefix && ! isset( $candidates[ $site_prefix ] ) ) {
			$candidates[ $site_prefix ] = sprintf(
				/* translators: %s: URL-prefix from site_url() */
				__( 'URL prefix — %s (site URL)', 'at-search-console' ),
				$site_prefix
			);
		}

		return $candidates;
	}

	/**
	 * Active GSC resource_id (custom override wins).
	 *
	 * @return string
	 */
	private function resource_id() {
		$settings = $this->settings();
		$custom   = trim( (string) $settings['custom_resource_id'] );
		if ( '' !== $custom && self::is_valid_resource_id( $custom ) ) {
			return $custom;
		}
		$selected = trim( (string) $settings['resource_id'] );
		if ( '' !== $selected && self::is_valid_resource_id( $selected ) ) {
			return $selected;
		}
		return self::default_resource_id();
	}

	/**
	 * @param string $id resource_id candidate.
	 * @return bool
	 */
	private static function is_valid_resource_id( $id ) {
		if ( 0 === strpos( $id, 'sc-domain:' ) ) {
			$host = substr( $id, 10 );
			return '' !== $host && false === strpos( $host, ' ' );
		}
		$parsed = wp_parse_url( $id );
		return is_array( $parsed )
			&& ! empty( $parsed['scheme'] )
			&& ! empty( $parsed['host'] );
	}

	/**
	 * @param string $resource_id GSC property id.
	 * @return bool
	 */
	private static function is_domain_resource( $resource_id ) {
		return 0 === strpos( $resource_id, 'sc-domain:' );
	}

	public function register_settings_page() {
		add_options_page(
			__( 'AT Search Console', 'at-search-console' ),
			__( 'AT Search Console', 'at-search-console' ),
			'manage_options',
			'at-search-console',
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting(
			'at_search_console',
			self::OPTION_KEY,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
				'default'           => array(
					'resource_id'        => self::default_resource_id(),
					'custom_resource_id' => '',
					'setup_dismissed'    => false,
				),
			)
		);
	}

	/**
	 * @param mixed $input Raw settings.
	 * @return array{resource_id: string, custom_resource_id: string, setup_dismissed: bool}
	 */
	public function sanitize_settings( $input ) {
		$current  = $this->settings();
		$candidates = $this->property_candidates();
		$output   = array(
			'resource_id'        => $current['resource_id'],
			'custom_resource_id' => '',
			'setup_dismissed'    => true,
		);

		if ( ! is_array( $input ) ) {
			return $output;
		}

		if ( isset( $input['resource_id'] ) && is_string( $input['resource_id'] ) ) {
			$choice = $input['resource_id'];
			if ( isset( $candidates[ $choice ] ) || self::is_valid_resource_id( $choice ) ) {
				$output['resource_id'] = $choice;
			}
		}

		if ( isset( $input['custom_resource_id'] ) && is_string( $input['custom_resource_id'] ) ) {
			$custom = trim( $input['custom_resource_id'] );
			if ( '' === $custom || self::is_valid_resource_id( $custom ) ) {
				$output['custom_resource_id'] = $custom;
			}
		}

		return $output;
	}

	/**
	 * Live-update the test link and preview when the property dropdown changes.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_settings_scripts( $hook_suffix ) {
		if ( 'settings_page_at-search-console' !== $hook_suffix ) {
			return;
		}

		wp_enqueue_script(
			'at-search-console-settings',
			plugin_dir_url( __FILE__ ) . 'js/settings.js',
			array(),
			self::VERSION,
			true
		);

		wp_localize_script(
			'at-search-console-settings',
			'atSearchConsoleSettings',
			array(
				'performanceBase' => self::GSC_PERFORMANCE_URL,
			)
		);
	}

	public function maybe_dismiss_setup_notice() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( empty( $_GET['at_sc_dismiss_setup'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'at_sc_dismiss_setup' ) ) {
			return;
		}

		$settings                    = $this->settings();
		$settings['setup_dismissed'] = true;
		update_option( self::OPTION_KEY, $settings );

		wp_safe_redirect( admin_url( 'options-general.php?page=at-search-console' ) );
		exit;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings    = $this->settings();
		$candidates  = $this->property_candidates();
		$resource_id = $this->resource_id();
		$test_url    = $this->build_property_test_url( $resource_id );
		$icon_path   = plugin_dir_path( __FILE__ ) . 'img/icon-256x256.png';
		$icon_url    = plugin_dir_url( __FILE__ ) . 'img/icon-256x256.png';
		$shot_path   = plugin_dir_path( __FILE__ ) . 'img/screenshot-1.png';
		$shot_url    = plugin_dir_url( __FILE__ ) . 'img/screenshot-1.png';
		$just_saved  = isset( $_GET['settings-updated'] ) && 'true' === $_GET['settings-updated']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		?>
		<div class="wrap">
			<?php if ( file_exists( $icon_path ) ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="60" height="60" style="vertical-align:middle;margin-right:12px;" />
			<?php endif; ?>
			<h1 style="display:inline-block;vertical-align:middle;"><?php echo esc_html__( 'AT Search Console', 'at-search-console' ); ?></h1>

			<p><?php echo esc_html__( 'Adds a “View in Search Console” link to the admin bar. It opens the current page’s performance in Google Search Console so you do not have to paste the URL by hand.', 'at-search-console' ); ?></p>
			<p><?php echo esc_html__( 'Open Search Console and check the property list in the top-left. Pick the same entry here, then test it before using the admin bar link.', 'at-search-console' ); ?></p>

			<?php if ( $just_saved ) : ?>
				<div class="notice notice-success is-dismissible"><p><?php echo esc_html__( 'Settings saved. Reload any open front-end pages so the admin bar link uses the new property.', 'at-search-console' ); ?></p></div>
			<?php endif; ?>

			<form method="post" action="options.php">
				<?php settings_fields( 'at_search_console' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="at-sc-resource-id"><?php echo esc_html__( 'Search Console property', 'at-search-console' ); ?></label></th>
						<td>
							<select name="<?php echo esc_attr( self::OPTION_KEY ); ?>[resource_id]" id="at-sc-resource-id" class="regular-text">
								<?php foreach ( $candidates as $id => $label ) : ?>
									<option value="<?php echo esc_attr( $id ); ?>" <?php selected( $settings['resource_id'], $id ); ?>><?php echo esc_html( $label ); ?></option>
								<?php endforeach; ?>
								<?php if ( ! empty( $settings['resource_id'] ) && ! isset( $candidates[ $settings['resource_id'] ] ) ) : ?>
									<option value="<?php echo esc_attr( $settings['resource_id'] ); ?>" selected><?php echo esc_html( $settings['resource_id'] ); ?></option>
								<?php endif; ?>
							</select>
							<p class="description">
								<?php echo esc_html__( 'Admin bar links will open:', 'at-search-console' ); ?>
								<code id="at-sc-preview-resource-id"><?php echo esc_html( $resource_id ); ?></code>
							</p>
							<p>
								<a id="at-sc-test-property" class="button button-secondary" href="<?php echo esc_url( $test_url ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html__( 'Test this property', 'at-search-console' ); ?></a>
								<span class="description" style="margin-left:8px;"><?php echo esc_html__( 'Opens site-wide performance in Search Console. If you see your data, the property is correct.', 'at-search-console' ); ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="at-sc-custom-resource-id"><?php echo esc_html__( 'Custom property (advanced)', 'at-search-console' ); ?></label></th>
						<td>
							<input type="text" class="large-text code" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[custom_resource_id]" id="at-sc-custom-resource-id" value="<?php echo esc_attr( $settings['custom_resource_id'] ); ?>" placeholder="sc-domain:example.com or https://www.example.com/" />
							<p class="description"><?php echo esc_html__( 'Optional. Overrides the dropdown when your verified property is not listed above.', 'at-search-console' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>

			<p><?php echo esc_html__( 'After saving, visit any front-end page and use View in Search Console in the admin bar.', 'at-search-console' ); ?></p>
			<?php if ( file_exists( $shot_path ) ) : ?>
				<img src="<?php echo esc_url( $shot_url ); ?>" alt="<?php echo esc_attr__( 'View in Search Console in the admin bar', 'at-search-console' ); ?>" style="max-width:680px;height:auto;" />
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Site-wide performance URL to verify property access (no page filter).
	 *
	 * @param string $resource_id GSC property.
	 * @return string
	 */
	private function build_property_test_url( $resource_id ) {
		return add_query_arg(
			array(
				'resource_id'   => $resource_id,
				'metrics'       => 'CLICKS,IMPRESSIONS,CTR,POSITION',
				'num_of_months' => 16,
			),
			self::GSC_PERFORMANCE_URL
		);
	}

	/**
	 * @param array<int, string> $links Existing links.
	 * @return array<int, string>
	 */
	public function action_links( $links ) {
		$url = admin_url( 'options-general.php?page=at-search-console' );
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'at-search-console' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Current page URL for the GSC page filter.
	 *
	 * @return string Empty when there is no useful URL.
	 */
	private function current_page_url() {
		if ( is_admin() ) {
			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
			if ( $screen && 'post' === $screen->base ) {
				$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				if ( $post_id && 'publish' === get_post_status( $post_id ) ) {
					$permalink = get_permalink( $post_id );
					return is_string( $permalink ) ? $permalink : '';
				}
			}
			return '';
		}

		if ( is_singular() ) {
			$permalink = get_permalink( get_queried_object_id() );
			return is_string( $permalink ) ? $permalink : '';
		}

		if ( is_front_page() ) {
			return home_url( '/' );
		}

		global $wp;
		if ( isset( $wp->request ) && is_string( $wp->request ) && '' !== $wp->request ) {
			return home_url( user_trailingslashit( $wp->request ) );
		}

		return home_url( '/' );
	}

	/**
	 * Page filter expression for GSC (URLs containing).
	 *
	 * @param string $page_url    Absolute page URL.
	 * @param string $resource_id GSC property.
	 * @return string
	 */
	private function page_filter_expression( $page_url, $resource_id ) {
		if ( self::is_domain_resource( $resource_id ) ) {
			$path = wp_parse_url( $page_url, PHP_URL_PATH );
			if ( is_string( $path ) && '' !== $path && '/' !== $path ) {
				return '*' . $path;
			}
		}
		return '*' . $page_url;
	}

	/**
	 * Build the Search Console performance URL for a page.
	 *
	 * @param string $page_url Absolute page URL.
	 * @return string
	 */
	private function build_gsc_url( $page_url ) {
		$resource_id = $this->resource_id();
		$query       = array(
			'resource_id'   => $resource_id,
			'metrics'       => 'CLICKS,IMPRESSIONS,CTR,POSITION',
			'num_of_months' => 16,
			'page'          => $this->page_filter_expression( $page_url, $resource_id ),
		);

		return add_query_arg( $query, self::GSC_PERFORMANCE_URL );
	}

	/**
	 * Admin notices: setup, duplicate plugin, wrong install folder.
	 */
	public function maybe_admin_notices() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$settings_url = admin_url( 'options-general.php?page=at-search-console' );
		$settings     = $this->settings();

		if ( empty( $settings['setup_dismissed'] ) ) {
			$dismiss_url = wp_nonce_url(
				add_query_arg( 'at_sc_dismiss_setup', '1', $settings_url ),
				'at_sc_dismiss_setup'
			);
			echo '<div class="notice notice-info is-dismissible"><p>';
			echo esc_html__( 'AT Search Console: pick the Search Console property that matches your site, click Test this property, then use View in Search Console on any page.', 'at-search-console' );
			echo ' <a href="' . esc_url( $settings_url ) . '">' . esc_html__( 'Open settings', 'at-search-console' ) . '</a>';
			echo ' | <a href="' . esc_url( $dismiss_url ) . '">' . esc_html__( 'Dismiss', 'at-search-console' ) . '</a>';
			echo '</p></div>';
		}

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$ours    = plugin_basename( __FILE__ );
		$our_dir = dirname( $ours );
		$dupes   = array();

		foreach ( get_plugins() as $path => $data ) {
			if ( $path === $ours ) {
				continue;
			}
			if ( empty( $data['Name'] ) || 'AT Search Console' !== $data['Name'] ) {
				continue;
			}
			if ( is_plugin_active( $path ) ) {
				$dupes[] = $path;
			}
		}

		if ( ! empty( $dupes ) ) {
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Another copy of AT Search Console is active. Deactivate and delete the older copy so Settings and the admin bar link only appear once.', 'at-search-console' );
			echo ' <a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Open Plugins', 'at-search-console' ) . '</a>';
			echo '</p></div>';
		}

		if ( self::INSTALL_SLUG !== $our_dir ) {
			echo '<div class="notice notice-warning"><p>';
			printf(
				/* translators: 1: current folder name, 2: required folder name */
				esc_html__( 'AT Search Console is installed in “%1$s”. For WordPress.org updates to replace this install, the folder must be “%2$s”. Deactivate this copy, delete it, then install from Plugins → Add New (or upload a zip whose top-level folder is %2$s).', 'at-search-console' ),
				esc_html( $our_dir ),
				esc_html( self::INSTALL_SLUG )
			);
			echo '</p></div>';
		}
	}

	/**
	 * @param WP_Admin_Bar $admin_bar Admin bar instance.
	 */
	public function add_admin_bar_item( $admin_bar ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$page_url = $this->current_page_url();
		if ( '' === $page_url ) {
			return;
		}

		$admin_bar->add_node(
			array(
				'id'    => 'at-view-gsc',
				'title' => __( 'View in Search Console', 'at-search-console' ),
				'href'  => $this->build_gsc_url( $page_url ),
				'meta'  => array(
					'target' => '_blank',
					'rel'    => 'noopener noreferrer',
					'title'  => __( 'Open this page in Google Search Console performance', 'at-search-console' ),
				),
			)
		);
	}
}

register_activation_hook( __FILE__, array( 'AT_Search_Console', 'activate' ) );

new AT_Search_Console();
