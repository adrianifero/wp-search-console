<?php
/**
 * Plugin Name: AT Search Console
 * Plugin URI:  https://adriantoro.com/wordpress-plugins/at-search-console/
 * Description: Open the current page in Google Search Console from the WordPress admin bar. One click to that URL's performance.
 * Version:     1.0.0
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
	const LEGACY_OPTION_KEY = 'at_search_console_option';
	const VERSION           = '1.0.0';
	/** Directory slug on wordpress.org — installs must use this folder name. */
	const INSTALL_SLUG      = 'at-search-console';

	public function __construct() {
		add_action( 'plugins_loaded', array( __CLASS__, 'maybe_migrate_legacy_option' ), 5 );
		add_action( 'admin_bar_menu', array( $this, 'add_admin_bar_item' ), 100 );
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_notices', array( $this, 'maybe_admin_notices' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
	}

	/**
	 * On activate/upgrade: migrate legacy property-type option if present.
	 */
	public static function activate() {
		self::maybe_migrate_legacy_option();
	}

	/**
	 * Migrate legacy option `at_search_console_option` (regular|domain) into
	 * `at_search_console_settings` (url_prefix|domain), then remove the legacy key.
	 *
	 * Does not overwrite settings already saved under the new key.
	 */
	public static function maybe_migrate_legacy_option() {
		$legacy = get_option( self::LEGACY_OPTION_KEY, false );
		if ( false === $legacy ) {
			return;
		}

		$type = ( 'domain' === $legacy ) ? 'domain' : 'url_prefix';

		$stored = get_option( self::OPTION_KEY, false );
		if ( false === $stored ) {
			update_option(
				self::OPTION_KEY,
				array(
					'property_type' => $type,
				)
			);
		}

		delete_option( self::LEGACY_OPTION_KEY );
	}

	/**
	 * Settings: property type for the GSC resource_id.
	 *
	 * @return array{property_type: string}
	 */
	private function settings() {
		$defaults = array(
			'property_type' => 'url_prefix',
		);
		$stored   = get_option( self::OPTION_KEY, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}
		return array_merge( $defaults, $stored );
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
				'default'           => array( 'property_type' => 'url_prefix' ),
			)
		);
	}

	/**
	 * @param mixed $input Raw settings.
	 * @return array{property_type: string}
	 */
	public function sanitize_settings( $input ) {
		$type = 'url_prefix';
		if ( is_array( $input ) && isset( $input['property_type'] ) && 'domain' === $input['property_type'] ) {
			$type = 'domain';
		}
		return array( 'property_type' => $type );
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$settings  = $this->settings();
		$icon_path = plugin_dir_path( __FILE__ ) . 'img/icon-256x256.png';
		$icon_url  = plugin_dir_url( __FILE__ ) . 'img/icon-256x256.png';
		$shot_path = plugin_dir_path( __FILE__ ) . 'img/screenshot-1.png';
		$shot_url  = plugin_dir_url( __FILE__ ) . 'img/screenshot-1.png';
		?>
		<div class="wrap">
			<?php if ( file_exists( $icon_path ) ) : ?>
				<img src="<?php echo esc_url( $icon_url ); ?>" alt="" width="60" height="60" style="vertical-align:middle;margin-right:12px;" />
			<?php endif; ?>
			<h1 style="display:inline-block;vertical-align:middle;"><?php echo esc_html__( 'AT Search Console', 'at-search-console' ); ?></h1>
			<p><?php echo esc_html__( 'Adds a “View in Search Console” link to the admin bar. It opens the current page’s performance in Google Search Console so you do not have to paste the URL by hand.', 'at-search-console' ); ?></p>
			<p><?php echo esc_html__( 'If your site is verified in Google Search Console as a URL-prefix property, you are set. If it is a domain property (sc-domain:…), choose Domain below.', 'at-search-console' ); ?></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'at_search_console' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><?php echo esc_html__( 'Search Console property type', 'at-search-console' ); ?></th>
						<td>
							<fieldset>
								<label>
									<input type="radio" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[property_type]" value="url_prefix" <?php checked( $settings['property_type'], 'url_prefix' ); ?> />
									<?php echo esc_html__( 'URL prefix / regular (https://example.com/)', 'at-search-console' ); ?>
								</label>
								<br />
								<label>
									<input type="radio" name="<?php echo esc_attr( self::OPTION_KEY ); ?>[property_type]" value="domain" <?php checked( $settings['property_type'], 'domain' ); ?> />
									<?php echo esc_html__( 'Domain (sc-domain:example.com)', 'at-search-console' ); ?>
								</label>
								<p class="description">
									<?php echo esc_html__( 'Match the property type you verified in Google Search Console. If the link opens the wrong property, switch this.', 'at-search-console' ); ?>
								</p>
							</fieldset>
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
	 * GSC resource_id for this site.
	 *
	 * @return string
	 */
	private function resource_id() {
		$settings = $this->settings();
		$host     = wp_parse_url( home_url(), PHP_URL_HOST );
		$host     = is_string( $host ) ? $host : '';

		if ( 'domain' === $settings['property_type'] && '' !== $host ) {
			return 'sc-domain:' . $host;
		}

		return trailingslashit( home_url() );
	}

	/**
	 * Build the Search Console performance URL for a page.
	 *
	 * @param string $page_url Absolute page URL.
	 * @return string
	 */
	private function build_gsc_url( $page_url ) {
		$query = array(
			'resource_id' => $this->resource_id(),
			'metrics'     => 'CLICKS,IMPRESSIONS,CTR,POSITION',
			// URLs containing — catches UTM and other query variants.
			'page'        => '*' . $page_url,
		);

		return add_query_arg( $query, 'https://search.google.com/search-console/performance/search-analytics' );
	}

	/**
	 * Warn when another AT Search Console copy is active, or this copy is not in the
	 * wordpress.org install folder (so directory updates would not replace it).
	 */
	public function maybe_admin_notices() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$ours     = plugin_basename( __FILE__ );
		$our_dir  = dirname( $ours );
		$dupes    = array();

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
			$plugins_url = admin_url( 'plugins.php' );
			echo '<div class="notice notice-error"><p>';
			echo esc_html__( 'Another copy of AT Search Console is active. Deactivate and delete the older copy so Settings and the admin bar link only appear once.', 'at-search-console' );
			echo ' <a href="' . esc_url( $plugins_url ) . '">' . esc_html__( 'Open Plugins', 'at-search-console' ) . '</a>';
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
