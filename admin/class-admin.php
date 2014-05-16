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

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// to debug
		set_site_transient( 'update_themes' , null );

		add_filter( 'pre_set_site_transient_update_themes', array( &$this, 'theme_update' ) );

		// This MUST come before we get details about the plugins so the headers are correctly retrieved
		add_filter( 'extra_theme_headers', array( $this, 'add_theme_headers' ) );
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

	function theme_update($updates) {
		if ( isset( $updates->checked ) ) {
			$updates = $this->theme_check( $updates );
		}

		return $updates;
	}

	public function theme_check($updates) {

		add_filter( 'http_request_args', array( &$this, 'http_timeout' ), 10, 1 );

		$installed_themes = wp_get_themes();
		
		$themes = $checked = $request = array();

		foreach ( $installed_themes as $theme ) {
			$checked[ $theme->get_stylesheet() ] = $theme->get('Version');

			$themes[ $theme->get_stylesheet() ] = array(
				'Name'       => $theme->get('Name'),
				'Title'      => $theme->get('Name'),
				'Version'    => $theme->get('Version'),
				'Author'     => $theme->get('Author'),
				'Author URI' => $theme->get('AuthorURI'),
				'Self Hosted URI' => $theme->get('Self Hosted URI'),
				'Template'   => $theme->get_template(),
				'Stylesheet' => $theme->get_stylesheet(),
			);
		}

		$installed = wp_get_theme();
		// get parent template, even if using child theme
		$template = $installed->get_template();
		if ( is_child_theme() ) {
			$version = $installed->parent()->Version;
		}
		else {
			$version = $installed->Version;
		}
		
		$options = array(
			'timeout'		=> 3,
			'body'			=> array( 'version' => $version, 'template' => $template ),
			'user-agent'	=> 'WordPress/' . get_bloginfo('version') . '; ' . get_bloginfo( 'url' )
		);

		$raw_response = wp_remote_post( 'http://update.webplantmedia.com/', $options );

		if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) )
			return false;

		$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
		if ( ! empty( $response ) && is_array( $response ) && isset( $response[ $template ] ) ) {
			$updates->response[ $template ] = $response[ $template ];
		}

		remove_filter( 'http_request_args' ,array( &$this,'http_timeout' ) );

		return $updates;
	}

	public function http_timeout($req) {
		// increase timeout for api request
		$req["timeout"] = 300;
		return $req;
	}

	/**
	 * Add extra headers to wp_get_themes()
	 *
	 * @since 1.0.0
	 * @param $extra_headers
	 *
	 * @return array
	 */
	public function add_theme_headers( $extra_headers ) {
		$extra_headers[] = 'Self Hosted URI';

		return $extra_headers;
	}
}
