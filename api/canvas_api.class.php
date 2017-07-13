<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}
class Canvas_Api {

	/**
	* Plugin API initialization
	*/
	public static function init() {
		add_action( 'init', array( 'Canvas_Api', 'add_endpoint'), 0);
		add_filter( 'query_vars', array( 'Canvas_Api', 'add_query_vars' ), 0 );
		add_action( 'parse_request', array( 'Canvas_Api', 'check_requests' ), 0 );
	}

	/**
	* Plugin API activation
	*/
	public static function activate() {
		self::add_endpoint();
		flush_rewrite_rules();
	}

	/**
	* Add public query vars
	* @return array $vars
	*/
	public static function add_query_vars( $vars ) {
		$vars[] = '__canvas_api';
		return $vars;
	}

	/**
	* Add Endpoint
	*/
	public static function add_endpoint() {
		add_rewrite_rule( '^canvas-api/loginstate/?', 'index.php?__canvas_api=loginstate', 'top' );
	}

	/**
	* Check Requests
	*
	* @param WP $wp
	*/
	public static function check_requests($wp) {
		if ( isset( $wp->query_vars[ '__canvas_api' ] ) ) {
			self::request( $wp->query_vars[ '__canvas_api' ] );
			exit;
		}
	}

	/**
	* Handle Requests
	*/
	protected static function request( $api_endpoint ) {
		switch ( $api_endpoint ) {
			case 'loginstate':
				self::header_json();
				include_once dirname(__FILE__) . '/loginstate.php';
				break;
			default:
				echo 'Mobiloud API v1.';
		}
	}

	static public function header_json() {
		header( 'Content-Type: application/json' );
	}
}

Canvas_Api::init();