<?php
/*
 * Plugin Name: Viber Sharing Button for Jetpack
 * Plugin URI: http://valeriosouza.com.br/portfolio/viber-sharing-button-for-jetpack/
 * Description: Add Viber button to Jetpack Sharing
 * Version: 1.0.0
 * Author: Valerio Souza, WordLab Academy
 * Author URI: http://www.valeriosouza.com.br
 * License: GPLv3 or later
 * Text Domain: jetpack-viber
 * Domain Path: /languages/
 * GitHub Branch: beta
 * GitHub Plugin URI: https://github.com/valeriosouza/jetpack-viber
*/

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if( version_compare( get_bloginfo('version'), '3.8', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );
}

add_action( 'admin_init', 'jv_check_dependencies' );

//Check if jetpack is active.
function jv_check_dependencies() {
	if ( ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
		add_action( 'admin_notices', 'jv_dependencies_notice' );
		deactivate_plugins( __FILE__ );

		//I used it to not appear "plugin active" message! We can discuss a better way to do this.
		unset( $_GET['activate'] );
	}
}

//Show error notice if jetpack is NOT active.
function jv_dependencies_notice() {
    ?>
    <div class="error">
        <p><strong><?php _e( 'Jetpack has NOT been activated! You need to install and activate the Jetpack plugin to work sharing with Viber.', 'jetpack-viber' ); ?></strong></p>
    </div>
    <?php
}

define( 'jeviber__PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'jeviber__PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'jeviber__PLUGIN_FILE', __FILE__ );
define( 'jeviber__VERSION',     '1.0.0' );

add_action( 'init', array( 'Jetpack_Viber_Pack', 'init' ) );

class Jetpack_Viber_Pack {
	static $instance;

	private $data;

	static function init() {
		if( !self::$instance ) {
			if( did_action('plugins_loaded') ) {
				self::plugin_textdomain();
			} else {
				add_action( 'plugins_loaded', array( __CLASS__, 'plugin_textdomain' ) );
			}

			self::$instance = new Jetpack_Viber_Pack;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts',    array( &$this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_menu_assets' ) );

		if( did_action('plugins_loaded') ) {
			$this->require_services();
		} else {
			add_action( 'plugins_loaded', array( &$this, 'require_services' ) );
		}
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	function register_assets() {
		if( get_option('sharedaddy_disable_resources') ) {
			return;
		}

		if( !Jetpack::is_module_active('sharedaddy') ) {
			return;
		}
		wp_enqueue_script( 'jetpack-viber', jeviber__PLUGIN_URL . 'assets/js/count.js', array('jquery','sharing-js'), jeviber__VERSION, true );
		wp_enqueue_style( 'jetpack-viber', jeviber__PLUGIN_URL . 'assets/css/style.css', array(), jeviber__VERSION );
	}

	function admin_menu_assets( $hook ) {
		if( $hook == 'settings_page_sharing' ) {
			wp_enqueue_style( 'jetpack-viber', jeviber__PLUGIN_URL . 'assets/css/style.css', array('sharing', 'sharing-admin'), jeviber__VERSION );
		}
	}

	function require_services() {
		if( class_exists('Jetpack') ) {
			require_once( jeviber__PLUGIN_DIR . 'includes/class.viber-service.php' );
		}
	}

	static function plugin_textdomain() {
		//load_plugin_textdomain( 'jetpack-viber', false, dirname( plugin_basename( jeviber__PLUGIN_FILE ) ) . '/languages/' );
		$locale = get_locale();

		load_plugin_textdomain( 'jetpack-viber', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function plugin_row_meta( $links, $file ) {
		if( plugin_basename( jeviber__PLUGIN_FILE ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url('http://wordlab.com.br/donate/'),
				__( 'Donate', 'jetpack-viber' )
			);
		}
		return $links;
	}
}
