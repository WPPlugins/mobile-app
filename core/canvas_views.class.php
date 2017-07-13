<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}
class CanvasViews {

	static public function view($template, $options = array()) {
		$dir = dirname(dirname(__FILE__)) . '/views/';
		if (!Canvas::get_option( 'configured' )) {
			if (isset($_POST[ 'configured' ])) {
				Canvas::set_option( 'configured', 1);
			} else {
				$template = 'init';
				$options = array();
			}
		}
		$filename = $dir . $template . '.php';
		if (file_exists($filename)) {
			if (!empty($options)) {
				extract($options);
			}
			require_once($dir . '/header.php' );
			require_once($filename);
			require_once($dir . '/footer.php' );
		}
	}
}