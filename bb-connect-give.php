<?php
/**
 * Plugin Name: BB Connect for Give Donations
 * Plugin URI: https://wordpress.org/plugins/bb-connect-for-give-donations/
 * Description: Easily integrate Give Donations with Beaver Builder.
 * Version: 2.2.1
 * Author: CauseLabs
 * Author URI: https://causelabs.com
 * Copyright: (c) 2017 CauseLabs, LLC
 * License: GNU General Public License v2.0
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: bbc-give
 * Domain Path: /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'BBC_GiveWP' ) ) :
	final class BBC_GiveWP {
		/**
		 * @var BBC_GiveWP The one true BBC_GiveWP
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * @var array Container array for errors
		 */
		public static $errors;

		/**
		 * @var array Beaver Builder Lite slug
		 */
		public static $bb_lite;

		/**
		 * @var array Beaver Builder Pro slug
		 */
		public static $bb_pro;


		/**
		 * Main BBC_GiveWP Instance
		 *
		 * @since     1.0
		 * @static
		 * @see       BBC_GiveWP()
		 * @return object|BBC_GiveWP The one true BBC_GiveWP
		 */
		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof BBC_GiveWP ) ) {

				self::$instance = new BBC_GiveWP;
				self::$errors   = array();
				self::$bb_lite  = 'beaver-builder-lite-version/fl-builder.php';
				self::$bb_pro   = 'bb-plugin/fl-builder.php';

				self::$instance->define_constants();
				self::$instance->init();
			}

			return self::$instance;
		}

		/**
		 * Throw error on object clone.
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bbc-give' ), '1.0' );
		}

		/**
		 * Disable de-serializing of the class.
		 *
		 * @since  1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// de-serializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'bbc-give' ), '1.0' );
		}

		/**
		 * Initialize the plugin's constants.
		 *
		 * @since 1.0
		 *
		 * @return void
		 */
		private static function define_constants() {

			// Plugin Folder Path.
			if ( ! defined( 'BBC_GIVE_DIR' ) ) {
				define( 'BBC_GIVE_DIR', plugin_dir_path( __FILE__ ) );
			}

			// Plugin Folder URL.
			if ( ! defined( 'BBC_GIVE_URL' ) ) {
				define( 'BBC_GIVE_URL', plugin_dir_url( __FILE__ ) );
			}

			// Plugin Root File.
			if ( ! defined( 'BBC_GIVE_FILE' ) ) {
				define( 'BBC_GIVE_FILE', __FILE__ );
			}
		}

		/**
		 * Initiate the plugin
		 */
		public function init() {
			add_action( 'plugins_loaded', array( $this, 'plugins_check' ) );
			add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
			add_action( 'init', array( $this, 'includes' ) );
		}

		/**
		 * Check if required plugins are activated
		 */
		public function plugins_check() {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';

			// Exit early if required plugins are active.
			if ( class_exists( 'FLBuilder' ) && class_exists( 'Give' ) ) {
				return;
			}

			// Display notice and deactivate plugin
			if ( is_admin() && current_user_can( 'activate_plugins' ) ) {
				add_action( 'admin_notices', array( self::$instance, 'admin_notices' ) );
				add_action( 'network_admin_notices', array( self::$instance, 'admin_notices' ) );

				$plugin = plugin_basename( __FILE__ );
				deactivate_plugins( $plugin, true );

				if ( isset( $_GET['activate'] ) ) {
					unset( $_GET['activate'] );
				}
			}
		}

		/**
		 * Include required files.
		 *
		 * @access private
		 * @since  1.0
		 * @return void
		 */
		public function includes() {
			if ( class_exists( 'FLBuilder' ) ) {
				// Custom Fields
				include_once BBC_GIVE_DIR . 'fields/bbc-toggle/bbc-toggle.php';

				// Modules
				include_once BBC_GIVE_DIR . 'modules/bbc-give-forms/bbc-give-forms.php';
				include_once BBC_GIVE_DIR . 'modules/bbc-give-goal/bbc-give-goal.php';
				include_once BBC_GIVE_DIR . 'modules/bbc-give-account/bbc-give-account.php';
			}
		}

		/**
		 * Loads the plugin language files.
		 *
		 * @access public
		 * @since  1.0
		 * @return void
		 */
		public function load_textdomain() {
			load_plugin_textdomain( 'bbc-give', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		}

		/**
		 * Admin notices.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function admin_notices() {

			if ( ! class_exists( 'Give' ) ) {
				?>
                <div class="notice notice-error">
                    <p>
						<?php
						$givewp = '<a href="https://wordpress.org/plugins/give/" target="_blank">Give - WordPress Donation Plugin</a>';
						echo sprintf( esc_html__( 'BB Connect for Give Donations requires the %1$s. Plugin deactivated.', 'bbc-give' ), $givewp ); ?>
                    </p>
                </div>
				<?php
			} else if ( is_plugin_active( self::$bb_lite ) && is_plugin_active( self::$bb_pro ) ) {
				?>
                <div class="notice notice-error">
                    <p>
						<?php
						$bbb_give = '<a href="https://wordpress.org/plugins/bb-connect-for-give-donations/" target="_blank">BB Connect for Give Donations</a>';
						echo sprintf( esc_html__( '%s requires only one activated version of Beaver Builder.', 'bbc-give' ), $bbb_give ); ?>
                    </p>
                </div>
				<?php
			} else if ( ! class_exists( 'FLBuilder' ) ) {
				?>
                <div class="notice notice-error">
                    <p>
						<?php
						$bb_lite = '<a href="https://wordpress.org/plugins/beaver-builder-lite-version/" target="_blank">Beaver Builder</a>';
						echo sprintf( esc_html__( 'BB Connect for Give Donations requires the %1$s plugin. Plugin deactivated.', 'bbc-give' ), $bb_lite ); ?>
                    </p>
                </div>
				<?php
			} else if ( count( self::$errors ) ) {
				foreach ( self::$errors as $key => $msg ) {
					?>
                    <div class="notice notice-error">
                        <p><?php echo $msg; ?></p>
                    </div>
					<?php
				}
			}
		}
	}
endif;

/**
 * The main function that returns BBC_GiveWP
 *
 * @since  1.0
 * @return object|BBC_GiveWP The main BBC_GiveWP instance.
 */
function BBC_Give() {
	return BBC_GiveWP::instance();
}

// Get BBC_GiveWP Running.
BBC_Give();
