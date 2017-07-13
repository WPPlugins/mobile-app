<?php
defined( 'CANVAS_URL' ) || die();

class Canvas {

	public static $instance = null;
	public $canvas_theme_object = null;
	public $canvas_theme_settings_object = null;

	const PRIORITY = 10000;
	const THEME_OPTION = 'theme-for-app';
	const THEME_DIFFERENT = 'different-theme-for-app';

	public static $slug = 'canvas';
	protected static $slug_theme = 'canvas-theme-setup';

	public static function get() {
		if (null === self::$instance) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function __construct() {
		self::$slug = apply_filters( 'canvas_slug', self::$slug);
		self::$slug_theme = apply_filters( 'canvas_slug_theme', self::$slug_theme);

		add_action( 'plugins_loaded', array($this, 'on_plugins_loaded' ) );

		require_once(dirname(__FILE__) . '/canvas_theme_settings.class.php' );

		$this->canvas_theme_settings_object = new CanvasThemeSettings();

		if ( Canvas::get_option( 'push_auto_enabled' ) ) {
			require_once(CANVAS_DIR . 'core/push/canvas-notifications.class.php' );
			add_action( 'transition_post_status', array( 'CanvasNotifications', 'post_published_notification' ), 10, 3 );
		}
	}

	/**
	* Plugin activation hook
	*
	*/
	function activate() {
		set_transient( '__canvas_activation_redirect', 1, 60);
		Canvas_Api::activate();
		require_once( CANVAS_DIR . 'core/canvas-admin.class.php' );
		CanvasAdmin::run_db_install();
	}

	function on_plugins_loaded() {
		require_once( dirname(__FILE__) . '/canvas_theme.class.php' );
		$this->canvas_theme_object = new CanvasTheme();
	}


	/*
	* Some of the code for theme switching is a derivative work of the code from the Apppresser plugin,
	* which is licensed GPLv2. This code is also licensed under the terms of the GNU Public License, verison 2.
	*/

	/**
	* External options set. Required function
	*
	* @param string $name
	* @param mixed $value
	*/
	static public function set_account($name, $value) {
		Canvas::set_option($name, $value);
	}

	/**
	* Set theme for switching
	*
	* @param bool $theme_different
	* @param string $theme
	*/
	static public function set_theme($theme_different, $theme = '' ) {
		self::set_account(self::THEME_DIFFERENT, $theme_different);
		self::set_account(self::THEME_OPTION, $theme_different && !empty($theme) ? $theme : '' );
	}

	/**
	* Update option
	*
	* @param string $name
	* @param mixed $value
	*/
	public static function set_option($name, $value) {
		return update_option( 'canvas-' . $name, $value);
	}

	/**
	* Get option value
	*
	* @param string $name
	* @param mixed $default
	* @return mixed
	*/
	public static function get_option($name, $default = false) {
		return get_option( 'canvas-' . $name, $default);
	}

	/**
	* Get customizer url for current theme
	*
	*/
	public static function get_theme_customize_url() {
		$url = add_query_arg(array(self::$slug_theme => 1, 'theme' => Canvas::get_option(self::THEME_OPTION)), admin_url( 'customize.php' ));
		return $url;
	}

	/**
	* Get plugin settings url
	*
	*/
	public static function main_settings_url() {
		return add_query_arg( 'page', self::$slug, admin_url( 'admin.php' ));
	}
}