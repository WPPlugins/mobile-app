<?php
/*
* Some of the code for theme switching is a derivative work of the code from the Apppresser plugin,
* which is licensed GPLv2. This code is also licensed under the terms of the GNU Public License, verison 2.
*/


defined( 'CANVAS_URL' ) || die();

class CanvasTheme extends Canvas {

	public $original_template = null;
	public $original_stylesheet = null;

	public $canvas_theme = false;

	public function __construct() {
		add_action( 'plugins_loaded', array($this, 'on_plugins_loaded' ), self::PRIORITY);
		if ($this->is_request_from_application()) {
			if (Canvas::get_option( 'login_enabled' )) {
				add_action( 'login_enqueue_scripts', array( &$this, 'login_add_css' ) );
			}

			add_action( 'wp_head', array( &$this, 'on_head' ) );

			header( 'ml_available: true' );
			// Add ml_username header for requests from application
			$user = get_current_user_id();
			if (!empty($user)) {
				header( 'ml_username: ' . self::get_username());
			}
		}
	}

	/**
	* Switch theme for Canvas mobile app
	*
	*/
	public function on_plugins_loaded() {
		// Do not switch default theme for admin view if it is not customizing
		if (is_admin() && !$this->is_request_from_theme_customizer()) {
			return;
		}

		// Switch theme for Canvas application or for theme settings
		if (Canvas::get_option(self::THEME_OPTION) && ($this->is_request_from_application() || $this->is_request_from_theme_customizer())) {
			$this->canvas_theme = wp_get_theme(Canvas::get_option( self::THEME_OPTION));

			add_filter( 'option_template', array($this, 'on_template_request' ), 5);
			add_filter( 'option_stylesheet', array($this, 'on_stylesheet_request' ), 5);
			add_filter( 'template', array( $this, 'on_template' ) );
		}
	}

	/**
	* Check and switch template hook
	*
	* @param mixed $template
	*/
	public function on_template_request($template) {
		// Cache our original template request
		if (is_null($this->original_template)) {
			$this->original_template = $template;
		}

		return $this->check_and_switch_template($template);
	}

	/**
	* Check and switch stylesheet hook
	*
	* @param mixed $stylesheet
	*/
	public function on_stylesheet_request($stylesheet) {
		if (is_null($this->original_stylesheet)) {
			$this->original_stylesheet = $stylesheet;
		}

		return $this->check_and_switch_stylesheet($stylesheet);
	}

	/**
	* Check and switch template or stylesheet hook
	*
	* @param mixed $template
	*/
	public function on_template($template = '', $stylesheet_request = false) {
		return $stylesheet_request ? $this->check_and_switch_stylesheet($template) : $this->check_and_switch_template($template);
	}

	/**
	* Check and switch stylesheet
	*
	* @param mixed $stylesheet
	*/
	protected function check_and_switch_stylesheet($stylesheet = '' ) {
		// No need to switch, return original or default stylesheet
		if (!$this->canvas_theme) {
			return !empty($stylesheet) ? $stylesheet : $this->original_stylesheet;
		}

		// New stylesheet
		return Canvas::get_option(self::THEME_OPTION);
	}

	/**
	* Check and switch template
	*
	* @param mixed $template
	*/
	protected function check_and_switch_template($template = '' ) {
		// No need to switch, return original or default template
		if (!$this->canvas_theme) {
			return !empty($template) ? $template : $this->original_template;
		}
		// New template
		return $this->canvas_theme->get_template();
	}

	/**
	* Source of request is a Canvas application
	*
	*/
	protected function is_request_from_application() {
		return strstr(strtolower($_SERVER[ 'HTTP_USER_AGENT' ]), 'canvas' );
	}

	/**
	* Source of request is one of theme customizer pages
	*
	*/
	public function is_request_from_theme_customizer() {
		// Cached result
		if (isset($this->is_theme_customizer_now)) {
			return $this->is_theme_customizer_now;
		}

		// Check if we are in the Canvas theme customizer
		$this->is_theme_customizer_now = isset($_GET[self::$slug_theme]) && isset($_GET[ 'theme' ])
		// or if it's an AJAX request
		|| (isset($_REQUEST[ 'wp_customize' ]) && isset($_REQUEST[ 'theme' ]) && Canvas::get_option(self::THEME_OPTION, '' ) == $_REQUEST[ 'theme' ] );

		return $this->is_theme_customizer_now;
	}


	/**
	* Create unique user ID
	*
	*/
	public static function get_username() {
		$user_id = get_current_user_id();
		if (!empty($user_id)) {
			$result = get_user_option( 'canvas-username', $user_id );
			if (empty($result)) {
				$chars = 'abcdefghijklmnopqrstuvwxyz0123456789';
				$result = '';
				for ( $i = 0; $i < 32; $i++ ) {
					$result .= substr($chars, wp_rand(0, strlen($chars) - 1), 1);
				}
				update_user_option($user_id, 'canvas-username', $result );
			} elseif (false !== strpos($result, '@' )) {
				$result = substr($result, 0, strpos($result, '@' ));
				update_user_option($user_id, 'canvas-username', $result );
			}
			return $result;
		} else {
			return '';
		}
	}

	/**
	* Add custom CSS only when the site is loaded in the Canvas app
	*
	*/
	public function on_head() {
		$custom_css = stripslashes(get_option( 'canvas_editor_css', '' ));
		if (!empty($custom_css)) {
			?>
			<style type="text/css" media="screen"><?php echo $custom_css; ?></style><?php
		}
	}

	public function login_add_css() {
		$custom_css = stripslashes(Canvas::get_option( 'editor_login_css', '' ));
		if (Canvas::get_option( 'login_hide_back', true )) {
			$custom_css .= " #backtoblog {display:none !important;}";
		};
		if (Canvas::get_option( 'login_hide_register', true )) {
			$custom_css .= " #nav > a:first-child{display:none;} #nav {visibility: collapse;} #nav a + a {visibility:visible;}";
		};
		if (Canvas::get_option( 'login_hide_remind', true )) {
			$custom_css .= " #forgetmenot {display:none !important;}";
		};
		if (!empty($custom_css)) { ?>
			<style type="text/css" media="screen"><?php echo $custom_css; ?></style><?php
		}
	}

}