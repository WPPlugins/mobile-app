<?php
/*
Plugin Name: MobiLoud Canvas
Plugin URI: http://www.mobiloud.com
Description: Turn your responsive or mobile site into mobile apps
Author: MobiLoud
Version: 1.7
Author URI: https://www.mobiloud.com
*/

define( 'CANVAS_URL', plugin_dir_url(__FILE__) );
define( 'CANVAS_DIR', dirname(__FILE__) . '/' );

require_once(CANVAS_DIR . 'core/canvas.class.php' );
require_once(CANVAS_DIR . 'api/canvas_api.class.php' );

register_activation_hook( __FILE__, array( 'Canvas', 'activate' ) );

if ( is_admin() ) {
	require_once( CANVAS_DIR . 'core/canvas-admin.class.php' );
	add_action( 'init', array( 'CanvasAdmin', 'init' ) );
}
Canvas::get();