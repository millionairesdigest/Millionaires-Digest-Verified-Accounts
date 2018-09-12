<?php
/*
Plugin Name: Millionaire's Digest Verified Accounts
Description: Allow accounts to be verified by displaying a check mark next to their display name. (Set and controlled by the Founder & CEO of the Millionaire's Digest.)
Version: 1.0.0
Author: K&L (Founder of the Millionaire's Digest)
Author URI: https://millionairedigest.com/
*/

/**
 * Autoloads files with classes when needed
 *
 * @since  2.4.0
 * @param  string $class_name Name of the class being requested.
 * @return void
 */
function buddyverified_autoload_classes( $class_name ) {
	if ( 0 !== strpos( $class_name, 'BV_' ) ) {
		return;
	}

	$filename = strtolower( str_replace(
		'_', '-',
		substr( $class_name, strlen( 'BV_' ) )
	) );

	BuddyVerified::include_file( $filename );
}
spl_autoload_register( 'buddyverified_autoload_classes' );

define( 'VERIFIED_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main initiation class
 *
 * @since  2.4.0
 */
final class BuddyVerified {

	/**
	 * Current version
	 *
	 * @var  string
	 * @since  2.4.0
	 */
	const VERSION = '2.4.1';

	/**
	 * URL of plugin directory
	 *
	 * @var string
	 * @since  2.4.0
	 */
	protected $url = '';

	/**
	 * Path of plugin directory
	 *
	 * @var string
	 * @since  2.4.0
	 */
	protected $path = '';

	/**
	 * Plugin basename
	 *
	 * @var string
	 * @since  2.4.0
	 */
	protected $basename = '';

	/**
	 * Singleton instance of plugin
	 *
	 * @var BuddyVerified
	 * @since  2.4.0
	 */
	protected static $single_instance = null;

	/**
	 * Instance of BV_Admin
	 *
	 * @since NEXT
	 * @var BV_Admin
	 */
	protected $admin;

	/**
	 * Instance of BV_Functions
	 *
	 * @since NEXT
	 * @var BV_Functions
	 */
	protected $functions;

	/**
	 * Creates or returns an instance of this class.
	 *
	 * @since  2.4.0
	 * @return BuddyVerified A single instance of this class.
	 */
	public static function get_instance() {
		if ( null === self::$single_instance ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;
	}

	/**
	 * Sets up our plugin
	 *
	 * @since  2.4.0
	 */
	protected function __construct() {
		$this->basename = plugin_basename( __FILE__ );
		$this->url      = plugin_dir_url( __FILE__ );
		$this->path     = plugin_dir_path( __FILE__ );
	}

	/**
	 * Attach other plugin classes to the base plugin class.
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function plugin_classes() {
		// Attach other plugin classes to the base plugin class.
		$this->admin = new BV_Admin( $this );
		$this->functions = new BV_Functions( $this );
	} // END OF PLUGIN CLASSES FUNCTION

	/**
	 * Add hooks and filters
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function hooks() {

		add_action( 'init', array( $this, 'init' ) );
	}

	/**
	 * Activate the plugin
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function _activate() {
		// Make sure any rewrite functionality has been loaded.
		flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin
	 * Uninstall routines should be in uninstall.php
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function _deactivate() {}

	/**
	 * Init hooks
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function init() {
		if ( $this->check_requirements() ) {
			load_plugin_textdomain( 'buddyverified', false, dirname( $this->basename ) . '/languages/' );
			$this->plugin_classes();
		}
	}

	/**
	 * Check if the plugin meets requirements and
	 * disable it if they are not present.
	 *
	 * @since  2.4.0
	 * @return boolean result of meets_requirements
	 */
	public function check_requirements() {
		if ( ! $this->meets_requirements() ) {

			// Add a dashboard notice.
			add_action( 'all_admin_notices', array( $this, 'requirements_not_met_notice' ) );

			// Deactivate our plugin.
			add_action( 'admin_init', array( $this, 'deactivate_me' ) );

			return false;
		}

		return true;
	}

	/**
	 * Deactivates this plugin, hook this function on admin_init.
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function deactivate_me() {
		deactivate_plugins( $this->basename );
	}

	/**
	 * Check that all plugin requirements are met
	 *
	 * @since  2.4.0
	 * @return boolean True if requirements are met.
	 */
	public static function meets_requirements() {
		// Do checks for required classes / functions
		// function_exists('') & class_exists('').
		// We have met all requirements.
		return true;
	}

	/**
	 * Adds a notice to the dashboard if the plugin requirements are not met
	 *
	 * @since  2.4.0
	 * @return void
	 */
	public function requirements_not_met_notice() {
		// Output our error.
		echo '<div id="message" class="error">';
		echo '<p>' . sprintf( esc_attr__( 'BuddyVerified is missing requirements and has been <a href="%s">deactivated</a>. Please make sure all requirements are available.', 'buddyverified' ), admin_url( 'plugins.php' ) ) . '</p>';
		echo '</div>';
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  2.4.0
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'version':
				return self::VERSION;
			case 'basename':
			case 'url':
			case 'path':
			case 'admin':
			case 'functions':
				return $this->$field;
			default:
				throw new Exception( 'Invalid ' . __CLASS__ . ' property: ' . $field );
		}
	}

	/**
	 * Include a file from the includes directory
	 *
	 * @since  2.4.0
	 * @param  string $filename Name of the file to be included.
	 * @return bool   Result of include call.
	 */
	public static function include_file( $filename ) {
		$file = self::dir( 'includes/class-' . $filename . '.php' );
		if ( file_exists( $file ) ) {
			return include_once( $file );
		}
		return false;
	}

	/**
	 * This plugin's directory
	 *
	 * @since  2.4.0
	 * @param  string $path (optional) appended path.
	 * @return string       Directory and path
	 */
	public static function dir( $path = '' ) {
		static $dir;
		$dir = $dir ? $dir : trailingslashit( dirname( __FILE__ ) );
		return $dir . $path;
	}

	/**
	 * This plugin's url
	 *
	 * @since  2.4.0
	 * @param  string $path (optional) appended path.
	 * @return string       URL and path
	 */
	public static function url( $path = '' ) {
		static $url;
		$url = $url ? $url : trailingslashit( plugin_dir_url( __FILE__ ) );
		return $url . $path;
	}
}

/**
 * Grab the BuddyVerified object and return it.
 * Wrapper for BuddyVerified::get_instance()
 *
 * @since  2.4.0
 * @return BuddyVerified  Singleton instance of plugin class.
 */
function buddyverified() {
	return BuddyVerified::get_instance();
}

// Kick it off.
add_action( 'bp_loaded', array( buddyverified(), 'hooks' ) );

register_activation_hook( __FILE__, array( buddyverified(), '_activate' ) );
register_deactivation_hook( __FILE__, array( buddyverified(), '_deactivate' ) );
