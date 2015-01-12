<?php
/**
 * WPC Self Hosted Updates.
 *
 * @package   WPC_Self_Hosted_Updates_Admin
 * @author    Chris Baldelomar <chris@webplantmedia.com>
 * @license   GPL-2.0+
 * @link      http://webplantmedia.com
 * @copyright 2014 Chris Baldelomar
 */

/**
 * Plugin class. This class should ideally be used to work with the
 * administrative side of the WordPress site.
 *
 * If you're interested in introducing public-facing
 * functionality, then refer to `class-plugin-name.php`
 *
 * @package   WPC_Self_Hosted_Updates_Admin
 * @author  Chris Baldelomar <chris@webplantmedia.com>
 */
class WPC_Self_Hosted_Updates_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	const VERSION = '1.2';

	/**
	 * Replace with your own URL's
	 *
	 * @var string
	 */
	protected $check_themes_url = 'http://api.webplantmedia.com/themes/update-check/1.1/';
	protected $check_plugins_url = 'http://api.webplantmedia.com/plugins/update-check/1.1/';

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		define( 'WPC_SELF_HOSTED_UPDATES_IS_ACTIVATED', true );

		$this->force_check();

		add_filter( 'pre_set_site_transient_update_themes', array( &$this, 'update_themes' ) );
		add_filter( 'pre_set_site_transient_update_plugins', array( &$this, 'update_plugins' ) );

		add_filter( 'plugins_api', array( &$this, 'plugins_api' ), 10, 3 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function force_check() {
		$force_check = ! empty( $_GET['force-check'] );

		if ( $force_check ) {
			$admin_page = basename( $_SERVER['SCRIPT_NAME'] );

			if ( 'update-core.php' == $admin_page ) {
				set_site_transient( 'update_themes' , null );
				set_site_transient( 'update_plugins' , null );
			}
		}
	}

	public function plugins_api( $res, $action, $args ) {
		$url = $http_url = $this->check_plugins_url;
		// if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
			// $url = set_url_scheme( $url, 'https' );

		$args = array(
			'timeout' => 15,
			'body' => array(
				'action' => $action,
				'request' => serialize( $args )
			)
		);
		$request = wp_remote_post( $url, $args );

		if ( is_wp_error($request) ) {
			return false;
		} else {
			$res = maybe_unserialize( wp_remote_retrieve_body( $request ) );
			if ( ! is_object( $res ) && ! is_array( $res ) )
				return false;
		}

		return $res;
	}

	public function update_plugins($updates) {
		if ( isset( $updates->checked ) ) {
			$updates = $this->check_plugins( $updates );
		}

		return $updates;
	}

	public function update_themes($updates) {
		if ( isset( $updates->checked ) ) {
			$updates = $this->check_themes( $updates );
		}

		return $updates;
	}

	public function check_themes($updates) {
		global $wp_version;

		// add_filter( 'http_request_args', array( &$this, 'http_timeout' ), 10, 1 );

		$installed_themes = wp_get_themes();
		$translations = wp_get_installed_translations( 'themes' );
		
		$themes = $checked = $request = array();

		// Put slug of current theme into request.
		$request['active'] = get_option( 'stylesheet' );

		foreach ( $installed_themes as $theme ) {
			$checked[ $theme->get_stylesheet() ] = $theme->get('Version');

			$themes[ $theme->get_stylesheet() ] = array(
				'Name'       => $theme->get('Name'),
				'Title'      => $theme->get('Name'),
				'Version'    => $theme->get('Version'),
				'Author'     => $theme->get('Author'),
				'Author URI' => $theme->get('AuthorURI'),
				'Template'   => $theme->get_template(),
				'Stylesheet' => $theme->get_stylesheet(),
			);
		}

		$request['themes'] = $themes;

		$locales = array( get_locale() );
		/**
		 * Filter the locales requested for theme translations.
		 *
		 * @since 3.7.0
		 *
		 * @param array $locales Theme locale. Default is current locale of the site.
		 */
		$locales = apply_filters( 'themes_update_check_locales', $locales );

		$options = array(
			'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
			'body' => array(
				'themes'       => json_encode( $request ),
				'translations' => json_encode( $translations ),
				'locale'       => json_encode( $locales ),
			),
			'user-agent'	=> 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
		);

		$url = $http_url = $this->check_themes_url;
		// if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
			// $url = set_url_scheme( $url, 'https' );

		$raw_response = wp_remote_post( $url, $options );
		/* if ( $ssl && is_wp_error( $raw_response ) ) {
			trigger_error( __( 'An unexpected error occurred. Something may be wrong with WebPlantMedia.com or this server&#8217;s configuration. If you continue to have problems, please try the <a href="http://webplantmedia.com/support/">support forums</a>.' ) . ' ' . __( '(WordPress could not establish a secure connection to WebPlantMedia.com. Please contact your server administrator.)' ), headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
			$raw_response = wp_remote_post( $http_url, $options );
		} */

		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
			return $updates;

		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		if ( ! empty( $response ) && is_array( $response ) ) {
			$updates->response = array_merge( $updates->response, $response );
		}

		// remove_filter( 'http_request_args' ,array( &$this,'http_timeout' ) );

		return $updates;
	}

	public function check_plugins($updates) {
		global $wp_version;

		// add_filter( 'http_request_args', array( &$this, 'http_timeout' ), 10, 1 );

		$plugins = get_plugins();
		$translations = wp_get_installed_translations( 'plugins' );
		
		$active  = get_option( 'active_plugins', array() );

		$to_send = compact( 'plugins', 'active' );

		$locales = array( get_locale() );
		/**
		 * Filter the locales requested for plugin translations.
		 *
		 * @since 3.7.0
		 *
		 * @param array $locales Plugin locale. Default is current locale of the site.
		 */
		$locales = apply_filters( 'plugins_update_check_locales', $locales );

		$options = array(
			'timeout' => ( ( defined('DOING_CRON') && DOING_CRON ) ? 30 : 3),
			'body' => array(
				'plugins'      => json_encode( $to_send ),
				'translations' => json_encode( $translations ),
				'locale'       => json_encode( $locales ),
			),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
		);

		$url = $http_url = $this->check_plugins_url;
		// if ( $ssl = wp_http_supports( array( 'ssl' ) ) )
			// $url = set_url_scheme( $url, 'https' );

		$raw_response = wp_remote_post( $url, $options );
		/* if ( $ssl && is_wp_error( $raw_response ) ) {
			trigger_error( __( 'An unexpected error occurred. Something may be wrong with WebPlantMedia.com or this server&#8217;s configuration. If you continue to have problems, please try the <a href="http://webplantmedia.com/support/">support forums</a>.' ) . ' ' . __( '(WordPress could not establish a secure connection to WebPlantMedia.com. Please contact your server administrator.)' ), headers_sent() || WP_DEBUG ? E_USER_WARNING : E_USER_NOTICE );
			$raw_response = wp_remote_post( $http_url, $options );
		} */

		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
			return $updates;

		$response = json_decode( wp_remote_retrieve_body( $raw_response ), true );

		if ( empty( $response ) || ! is_array( $response ) ) {
			return $updates;
		}

		foreach ( $response as &$plugin ) {
			$plugin = (object) $plugin;
		}
		unset( $plugin );

		if ( ! empty( $response ) && is_array( $response ) ) {
			$updates->response = array_merge( $updates->response, $response );
		}

		// remove_filter( 'http_request_args' ,array( &$this,'http_timeout' ) );

		return $updates;
	}

	public function http_timeout( $req ) {
		// increase timeout for api request
		$req["timeout"] = 300;

		return $req;
	}
}
