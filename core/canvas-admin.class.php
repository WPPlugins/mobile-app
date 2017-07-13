<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}
require_once(dirname(__FILE__) . '/canvas_views.class.php' );
require_once(CANVAS_DIR . 'core/push/canvas-notifications.class.php' );

class CanvasAdmin {

	public static $admin_pages = array(
		'options' => 'Theme Options',
		'editor' => 'CSS Editor',
		'push' => 'Push Notifications',
		'login' => 'Login & Subscriptions'
	);
	public static $admin_notifications = array(
		'notifications' => 'Notifications'
	);
	private static $admin_screens = array();
	private static $push_screen = '';

	public static  function init() {
		add_action( 'admin_menu', array( 'CanvasAdmin', 'on_admin_menu' ) );
		add_action( 'current_screen', array( 'CanvasAdmin', 'current_screen' ) );
		add_action( 'admin_init', array( 'CanvasAdmin', 'activation_redirect' ), 1 );
		add_action( 'wp_ajax_canvas_save_initial_data', array( 'CanvasAdmin', 'save_initial_data' ) );

		if (Canvas::get_option( 'push_app_id' ) && Canvas::get_option( 'push_key' )) {
			add_action( 'wp_ajax_canvas_attachment_content', array( 'CanvasNotifications', 'attachment_content' ) );
			add_action( 'wp_ajax_canvas_notification_check_duplicate', array( 'CanvasNotifications', 'notification_check_duplicate' ) );
			add_action( 'wp_ajax_canvas_notification_manual_send', array( 'CanvasNotifications', 'notification_manual_send' ) );
			add_action( 'wp_ajax_canvas_notification_history', array( 'CanvasNotifications', 'notification_history' ) );
		}
	}

	/**
	* Redirect to plugin's page on plugin activation
	*
	*/
	public static function activation_redirect() {
		if ( get_transient( '__canvas_activation_redirect' ) && ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) ) {
			delete_transient( '__canvas_activation_redirect' );
			if ( isset( $_GET[ 'activate-multi' ] ) ) {
				return;
			}

			wp_safe_redirect( add_query_arg( array( 'page' => Canvas::$slug ), get_admin_url( NULL, 'admin.php' ) ) );
		}
	}



	public static function on_admin_menu() {
		// show basic settings
		$image = "data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+PHN2ZyAgIHhtbG5zOmRjPSJodHRwOi8vcHVybC5vcmcvZGMvZWxlbWVudHMvMS4xLyIgICB4bWxuczpjYz0iaHR0cDovL2NyZWF0aXZlY29tbW9ucy5vcmcvbnMjIiAgIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyIgICB4bWxuczpzdmc9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgICB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgICB4bWxuczpzb2RpcG9kaT0iaHR0cDovL3NvZGlwb2RpLnNvdXJjZWZvcmdlLm5ldC9EVEQvc29kaXBvZGktMC5kdGQiICAgeG1sbnM6aW5rc2NhcGU9Imh0dHA6Ly93d3cuaW5rc2NhcGUub3JnL25hbWVzcGFjZXMvaW5rc2NhcGUiICAgdmVyc2lvbj0iMS4wIiAgIGlkPSJMYXllcl8xIiAgIHg9IjBweCIgICB5PSIwcHgiICAgd2lkdGg9IjI0cHgiICAgaGVpZ2h0PSIyNHB4IiAgIHZpZXdCb3g9IjAgMCAyNCAyNCIgICBlbmFibGUtYmFja2dyb3VuZD0ibmV3IDAgMCAyNCAyNCIgICB4bWw6c3BhY2U9InByZXNlcnZlIiAgIGlua3NjYXBlOnZlcnNpb249IjAuNDguNCByOTkzOSIgICBzb2RpcG9kaTpkb2NuYW1lPSJtbC1tZW51LWljb250ci5zdmciPjxtZXRhZGF0YSAgICAgaWQ9Im1ldGFkYXRhMjkiPjxyZGY6UkRGPjxjYzpXb3JrICAgICAgICAgcmRmOmFib3V0PSIiPjxkYzpmb3JtYXQ+aW1hZ2Uvc3ZnK3htbDwvZGM6Zm9ybWF0PjxkYzp0eXBlICAgICAgICAgICByZGY6cmVzb3VyY2U9Imh0dHA6Ly9wdXJsLm9yZy9kYy9kY21pdHlwZS9TdGlsbEltYWdlIiAvPjxkYzp0aXRsZSAvPjwvY2M6V29yaz48L3JkZjpSREY+PC9tZXRhZGF0YT48ZGVmcyAgICAgaWQ9ImRlZnMyNyI+PGNsaXBQYXRoICAgICAgIGlkPSJTVkdJRF8yXy0yIj48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTktMSIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDE4Ij48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTMwMjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAyMiI+PHVzZSAgICAgICAgIGhlaWdodD0iMTA1Mi4zNjIyIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgeT0iMCIgICAgICAgICB4PSIwIiAgICAgICAgIHN0eWxlPSJvdmVyZmxvdzp2aXNpYmxlIiAgICAgICAgIHhsaW5rOmhyZWY9IiNTVkdJRF8xXy04IiAgICAgICAgIG92ZXJmbG93PSJ2aXNpYmxlIiAgICAgICAgIGlkPSJ1c2UzMDI0IiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMjYiPjx1c2UgICAgICAgICBoZWlnaHQ9IjEwNTIuMzYyMiIgICAgICAgICB3aWR0aD0iNzQ0LjA5NDQ4IiAgICAgICAgIHk9IjAiICAgICAgICAgeD0iMCIgICAgICAgICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8tOCIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICBpZD0idXNlMzAyOCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDMwIj48dXNlICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiICAgICAgICAgd2lkdGg9Ijc0NC4wOTQ0OCIgICAgICAgICB5PSIwIiAgICAgICAgIHg9IjAiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgaWQ9InVzZTMwMzIiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAzNCI+PHVzZSAgICAgICAgIGhlaWdodD0iMTA1Mi4zNjIyIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgeT0iMCIgICAgICAgICB4PSIwIiAgICAgICAgIHN0eWxlPSJvdmVyZmxvdzp2aXNpYmxlIiAgICAgICAgIHhsaW5rOmhyZWY9IiNTVkdJRF8xXy04IiAgICAgICAgIG92ZXJmbG93PSJ2aXNpYmxlIiAgICAgICAgIGlkPSJ1c2UzMDM2IiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMzgiPjx1c2UgICAgICAgICBoZWlnaHQ9IjEwNTIuMzYyMiIgICAgICAgICB3aWR0aD0iNzQ0LjA5NDQ4IiAgICAgICAgIHk9IjAiICAgICAgICAgeD0iMCIgICAgICAgICBzdHlsZT0ib3ZlcmZsb3c6dmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8tOCIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICBpZD0idXNlMzA0MCIgLz48L2NsaXBQYXRoPjxkZWZzICAgICAgIGlkPSJkZWZzNSI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0iU1ZHSURfMV8iIC8+PC9kZWZzPjxjbGlwUGF0aCAgICAgICBpZD0iU1ZHSURfMl8iPjx1c2UgICAgICAgICBpZD0idXNlOSIgICAgICAgICBvdmVyZmxvdz0idmlzaWJsZSIgICAgICAgICB4bGluazpocmVmPSIjU1ZHSURfMV8iIC8+PC9jbGlwUGF0aD48ZGVmcyAgICAgICBpZD0iZGVmczUtMiI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0iU1ZHSURfMV8tOCIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9kZWZzPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDQ1Ij48dXNlICAgICAgICAgaWQ9InVzZTMwNDciICAgICAgICAgb3ZlcmZsb3c9InZpc2libGUiICAgICAgICAgeGxpbms6aHJlZj0iI1NWR0lEXzFfLTgiICAgICAgICAgc3R5bGU9Im92ZXJmbG93OnZpc2libGUiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAgICAgICAgIHdpZHRoPSI3NDQuMDk0NDgiICAgICAgICAgaGVpZ2h0PSIxMDUyLjM2MjIiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9IlNWR0lEXzJfLTgiPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTktMiIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAxOC0wIj48cmVjdCAgICAgICAgIGhlaWdodD0iMjQiICAgICAgICAgd2lkdGg9IjI0IiAgICAgICAgIGlkPSJ1c2UzMDIwLTkiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMjItNSI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0idXNlMzAyNC05IiAgICAgICAgIHg9IjAiICAgICAgICAgeT0iMCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDI2LTciPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTMwMjgtMyIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48Y2xpcFBhdGggICAgICAgaWQ9ImNsaXBQYXRoMzAzMC0xIj48cmVjdCAgICAgICAgIGhlaWdodD0iMjQiICAgICAgICAgd2lkdGg9IjI0IiAgICAgICAgIGlkPSJ1c2UzMDMyLTEiICAgICAgICAgeD0iMCIgICAgICAgICB5PSIwIiAvPjwvY2xpcFBhdGg+PGNsaXBQYXRoICAgICAgIGlkPSJjbGlwUGF0aDMwMzQtNiI+PHJlY3QgICAgICAgICBoZWlnaHQ9IjI0IiAgICAgICAgIHdpZHRoPSIyNCIgICAgICAgICBpZD0idXNlMzAzNi04IiAgICAgICAgIHg9IjAiICAgICAgICAgeT0iMCIgLz48L2NsaXBQYXRoPjxjbGlwUGF0aCAgICAgICBpZD0iY2xpcFBhdGgzMDM4LTQiPjxyZWN0ICAgICAgICAgaGVpZ2h0PSIyNCIgICAgICAgICB3aWR0aD0iMjQiICAgICAgICAgaWQ9InVzZTMwNDAtMyIgICAgICAgICB4PSIwIiAgICAgICAgIHk9IjAiIC8+PC9jbGlwUGF0aD48L2RlZnM+PHNvZGlwb2RpOm5hbWVkdmlldyAgICAgcGFnZWNvbG9yPSIjZmZmZmZmIiAgICAgYm9yZGVyY29sb3I9IiM2NjY2NjYiICAgICBib3JkZXJvcGFjaXR5PSIxIiAgICAgb2JqZWN0dG9sZXJhbmNlPSIxMCIgICAgIGdyaWR0b2xlcmFuY2U9IjEwIiAgICAgZ3VpZGV0b2xlcmFuY2U9IjEwIiAgICAgaW5rc2NhcGU6cGFnZW9wYWNpdHk9IjAiICAgICBpbmtzY2FwZTpwYWdlc2hhZG93PSIyIiAgICAgaW5rc2NhcGU6d2luZG93LXdpZHRoPSI3MzAiICAgICBpbmtzY2FwZTp3aW5kb3ctaGVpZ2h0PSI0ODAiICAgICBpZD0ibmFtZWR2aWV3MjUiICAgICBzaG93Z3JpZD0iZmFsc2UiICAgICBpbmtzY2FwZTp6b29tPSI5LjgzMzMzMzMiICAgICBpbmtzY2FwZTpjeD0iMy4wMjQxMzI1IiAgICAgaW5rc2NhcGU6Y3k9IjIxLjIwNTUwNSIgICAgIGlua3NjYXBlOndpbmRvdy14PSI1MjUiICAgICBpbmtzY2FwZTp3aW5kb3cteT0iNjYiICAgICBpbmtzY2FwZTp3aW5kb3ctbWF4aW1pemVkPSIwIiAgICAgaW5rc2NhcGU6Y3VycmVudC1sYXllcj0iTGF5ZXJfMSIgLz48cGF0aCAgICAgc3R5bGU9ImZpbGw6Izk5OTk5OTtmaWxsLW9wYWNpdHk6MSIgICAgIGNsaXAtcGF0aD0idXJsKCNTVkdJRF8yXykiICAgICBkPSJNIDQsMCBDIDEuNzkxLDAgMCwxLjc5MSAwLDQgbCAwLDE2IGMgMCwyLjIwOSAxLjc5MSw0IDQsNCBsIDE2LDAgYyAyLjIwOSwwIDQsLTEuNzkxIDQsLTQgTCAyNCw0IEMgMjQsMS43OTEgMjIuMjA5LDAgMjAsMCBMIDQsMCB6IG0gOS41LDMuNSBjIDAuMTI2NDcsMCAwLjI2MDA3NSwwLjAyNzgwOCAwLjM3NSwwLjA2MjUgMC4wODkzMiwwLjAyNTUxMSAwLjE2OTU2NiwwLjA1MDkyIDAuMjUsMC4wOTM3NSAwLjAyMTI2LDAuMDEyMDMzIDAuMDQxOTgsMC4wMTgwNzMgMC4wNjI1LDAuMDMxMjUgMC4xMTA4OTUsMC4wNjcwMTIgMC4xOTQ5MzcsMC4xNTQyOTg2IDAuMjgxMjUsMC4yNSAwLjA3OTE5LDAuMDg2OTk3IDAuMTMyNTAzLDAuMTc2NjQwOSAwLjE4NzUsMC4yODEyNSBsIDAuMDMxMjUsMCBjIDAuMDE1MjIsMC4wMjk2NTcgMC4wMTYyLDAuMDYzOTkyIDAuMDMxMjUsMC4wOTM3NSAwLjEzMjc5MiwwLjI2MjYwNjMgMC4yNTU2MTEsMC41MTEwNDY2IDAuMzc1LDAuNzgxMjUgMC4wMTMzNCwwLjAzMDE0NiAwLjAxODA4LDAuMDYzNTE5IDAuMDMxMjUsMC4wOTM3NSAwLjExODAzLDAuMjcxNDExMyAwLjIzOTY0OCwwLjUzMzk1OTUgMC4zNDM3NSwwLjgxMjUgMC4xMjU1MjgsMC4zMzQ4MTMyIDAuMjM5NDI0LDAuNjg3MTQ4MyAwLjM0Mzc1LDEuMDMxMjUgMC4wODY3NiwwLjI4NzQ3OTUgMC4xNzgyMjYsMC41ODEzMzQ2IDAuMjUsMC44NzUgMC4wMDQ5LDAuMDE5ODg3IC0wLjAwNDgsMC4wNDI1ODUgMCwwLjA2MjUgMC4wNzM3NywwLjMwNjUyNDcgMC4xNjE3ODksMC42MjQ3NzkgMC4yMTg3NSwwLjkzNzUgMC4wMDE4LDAuMDEwMDI3IC0wLjAwMTgsMC4wMjEyMTYgMCwwLjAzMTI1IDAuMDU4MTQsMC4zMjI1MjQzIDAuMDg1MjgsMC42NDAyMDM1IDAuMTI1LDAuOTY4NzUgMC4wODExMSwwLjY3NjgxMiAwLjEyNSwxLjM2MDc3NCAwLjEyNSwyLjA2MjUgbCAwLDAuMDMxMjUgMC4wMzEyNSwwIDAsMC4wMzEyNSBjIDAsMC42ODUgLTAuMDQ0OCwxLjM3MDEyMiAtMC4xMjUsMi4wMzEyNSAtMC4wMDEyLDAuMDEwMTkgMC4wMDEyLDAuMDIxMDYgMCwwLjAzMTI1IC0wLjAzOTQzLDAuMzE5OTc5IC0wLjA5OTAxLDAuNjIzNTk0IC0wLjE1NjI1LDAuOTM3NSAtMC4wMDM2LDAuMDIwMzEgMC4wMDM3LDAuMDQyMjEgMCwwLjA2MjUgLTAuMDU2NTEsMC4zMDM1NTQgLTAuMTE0OTIxLDAuNjA4Njg3IC0wLjE4NzUsMC45MDYyNSAtMC4wNjUyLDAuMjczNTIzIC0wLjE0MDU0OSwwLjU0NDI2NiAtMC4yMTg3NSwwLjgxMjUgLTAuMTA0OTk4LDAuMzUyNDM4IC0wLjIxNzAxNywwLjY4ODM2NSAtMC4zNDM3NSwxLjAzMTI1IC0wLjIxNjUwMSwwLjU5NjI3NSAtMC40NzEwMDIsMS4xNTU2MzcgLTAuNzUsMS43MTg3NSAtMC4wMTAzMSwwLjAyMDgxIC0wLjAyMDg2LDAuMDQxNzQgLTAuMDMxMjUsMC4wNjI1IC0wLjAwNywwLjAxODkzIDAuMDA3OCwwLjA0Mzk5IDAsMC4wNjI1IC0wLjAxNjg3LDAuMDMzNDMgLTAuMDQ1NDEsMC4wNjA0NSAtMC4wNjI1LDAuMDkzNzUgLTAuMDU1MDcsMC4xMDQ1MjUgLTAuMTA4Mjk4LDAuMTk0MjY5IC0wLjE4NzUsMC4yODEyNSAtMC4wNTQ2LDAuMDYwNDQgLTAuMTIyNjI0LDAuMTA2Nzg5IC0wLjE4NzUsMC4xNTYyNSBDIDE0LjA5NDcxLDIwLjM4OTM2NiAxMy44Mjg2NzQsMjAuNSAxMy41MzEyNSwyMC41IGMgLTAuMTAxMjg3LDAgLTAuMTg2NTU4LC0wLjAwOTYgLTAuMjgxMjUsLTAuMDMxMjUgLTAuMDc1NDYsLTAuMDE1NDQgLTAuMTQ4NTcyLC0wLjAzNDcyIC0wLjIxODc1LC0wLjA2MjUgLTAuMDA3OSwtMC4wMDMzIC0wLjAyMzM5LDAuMDAzNSAtMC4wMzEyNSwwIC0wLjE1NzI2NiwtMC4wNjY0OCAtMC4yODcxODcsLTAuMTYyMzEyIC0wLjQwNjI1LC0wLjI4MTI1IC0wLjIzNzUsLTAuMjM3MjUgLTAuMzc1LC0wLjU3NCAtMC4zNzUsLTAuOTM3NSAwLC0wLjA5OTYxIDAuMDA5MSwtMC4xOTIxMzEgMC4wMzEyNSwtMC4yODEyNSAwLjAwMjMsLTAuMDExMzIgLTAuMDAyNiwtMC4wMjAwNCAwLC0wLjAzMTI1IDAuMDA2MSwtMC4wMjIyMSAwLjAyMzkyLC0wLjA0MDc0IDAuMDMxMjUsLTAuMDYyNSAwLjAyNDU2LC0wLjA4MjgyIDAuMDU0MjIsLTAuMTQzNjQgMC4wOTM3NSwtMC4yMTg3NSBsIC0wLjAzMTI1LDAgYyAxLjAxMSwtMS45NjkgMS41NjI1LC00LjE5NzUgMS41NjI1LC02LjU2MjUgbCAwLC0wLjAzMTI1IDAsLTAuMDMxMjUgYyAwLC0wLjI5NTYyNSAtMC4wMTMzMiwtMC41ODM4ODEgLTAuMDMxMjUsLTAuODc1IEMgMTMuODM5ODgzLDEwLjUxMTI2NiAxMy43NTg1NTUsOS45MzY4NTk0IDEzLjY1NjI1LDkuMzc1IDEzLjU1MTQwNiw4LjgwODY1NzggMTMuNDE5MDc4LDguMjU5ODgwOSAxMy4yNSw3LjcxODc1IDEzLjE2NzI4NSw3LjQ1MDE5NTMgMTMuMDk3NzM0LDcuMTk5MDkzOCAxMyw2LjkzNzUgMTIuODAzMjQyLDYuNDE0NzM0NCAxMi41NjUsNS44OTg1IDEyLjMxMjUsNS40MDYyNSAxMi4zMDgyLDUuMzk3NTMgMTIuMzE2NSw1LjM4MzkwMSAxMi4zMTI1LDUuMzc1IDEyLjI4ODIxNyw1LjMyMjczMTYgMTIuMjY3MzQ3LDUuMjc0NDY4OCAxMi4yNSw1LjIxODc1IDEyLjIzNzk5LDUuMTc2NjM0NiAxMi4yMjY3MzksNS4xMzc2MzU4IDEyLjIxODc1LDUuMDkzNzUgMTIuMjAxMTk5LDUuMDA4MDY4NCAxMi4xODc1LDQuOTAzMzc1IDEyLjE4NzUsNC44MTI1IDEyLjE4NzUsNC4wODU1IDEyLjc3MywzLjUgMTMuNSwzLjUgeiBNIDguNzUsNS45Mzc1IGMgMC4zNzk0MTEzLDAgMC43MzExNDMzLDAuMTc2NTA4OSAwLjk2ODc1LDAuNDM3NSAwLjA3OTIwMiwwLjA4Njk5NyAwLjEzMjQyODksMC4xNzY2NDA5IDAuMTg3NSwwLjI4MTI1IEwgOS45Mzc1LDYuNjI1IGMgMC4wMTkyMzIsMC4wMzc1MjcgMC4wMTI0MTEsMC4wODcyNDEgMC4wMzEyNSwwLjEyNSAwLjU4OTAzMywxLjE4MDYyMTkgMC45ODg3NiwyLjQ4MDY5NjQgMS4xNTYyNSwzLjg0Mzc1IDAuMDU1ODMsMC40NTQzNTEgMC4wOTM3NSwwLjkwNTI2OCAwLjA5Mzc1LDEuMzc1IGwgMCwwLjAzMTI1IDAsMC4wMzEyNSBjIDAsMS45MjQgLTAuNDU5MjUsMy43NDA3NSAtMS4yODEyNSw1LjM0Mzc1IEwgOS45MDYyNSwxNy4zNDM3NSBjIC0wLjIyMDI4NDMsMC40MTg0NzkgLTAuNjE5MTE4MiwwLjcxODc1IC0xLjEyNSwwLjcxODc1IC0wLjcyNiwwIC0xLjMxMjUsLTAuNTg0NSAtMS4zMTI1LC0xLjMxMjUgMCwtMC4yMDU3NDQgMC4wNDA1NjUsLTAuMzg5MDQ5IDAuMTI1LC0wLjU2MjUgTCA3LjU2MjUsMTYuMTU2MjUgYyAwLjYzNCwtMS4yMzcgMSwtMi42NCAxLC00LjEyNSBsIDAsLTAuMDMxMjUgLTAuMDMxMjUsMCAwLC0wLjAzMTI1IGMgMCwtMS40ODUgLTAuMzM0NzUsLTIuODg5IC0wLjk2ODc1LC00LjEyNSBsIDAuMDMxMjUsMCBDIDcuNTQ1NTU3LDcuNzUyMzAxOCA3LjQ5NTc2NDIsNy42NjA0MzU3IDcuNDY4NzUsNy41NjI1IDcuNDQxNzM1Nyw3LjQ2NDU2NDMgNy40Mzc1LDcuMzYwNTU5MSA3LjQzNzUsNy4yNSA3LjQzNzUsNi41MjMgOC4wMjQsNS45Mzc1IDguNzUsNS45Mzc1IHoiICAgICBpZD0icGF0aDExIiAgICAgaW5rc2NhcGU6Y29ubmVjdG9yLWN1cnZhdHVyZT0iMCIgICAgIHRyYW5zZm9ybT0ibWF0cml4KDAuODQ3NDU3NjIsMCwwLDAuODQ3NDU3NjIsMS44MzA1MDg1LDEuODMwNTA4NSkiIC8+PC9zdmc+";

		self::$admin_screens[] = add_submenu_page( Canvas::$slug, 'Configuration', 'Configuration', "activate_plugins", Canvas::$slug, array( 'CanvasAdmin', 'main_menu' ) );
		self::$admin_screens[] = add_menu_page( 'Canvas', 'Canvas', 'activate_plugins', Canvas::$slug, array( 'CanvasAdmin', 'main_menu'), $image, '25.31415926' );

		// Show manual notifications only if app id and key set
		if (Canvas::get_option( 'push_app_id' ) && Canvas::get_option( 'push_key' )) {
			self::$push_screen = add_submenu_page( Canvas::$slug, 'Push Notification', 'Push Notifications', "publish_posts", Canvas::$slug . '_push', array( 'CanvasAdmin', 'push_menu' ) );
			self::$admin_screens[] = self::$push_screen;
		}
		do_action( 'canvas_on_menu', Canvas::$slug);
	}

	public static function current_screen() {
		if (is_admin()) {
			$screen = get_current_screen();
			if ($screen instanceof WP_Screen && in_array($screen->id, self::$admin_screens)) {
				self::add_scripts(self::$push_screen == $screen->id);
			}
		}
	}

	public static function main_menu() {
		// for old Wordpress versions
		if (!function_exists( 'set_current_screen' )) {
			self::add_scripts();
		}

		do_action( 'canvas_main_menu', Canvas::$slug);
		$active_tab = (isset($_GET[ 'tab' ]) && isset(CanvasAdmin::$admin_pages[$_GET[ 'tab' ]])) ? $_GET[ 'tab' ] :  'options';
		// save settings
		$updated = false;
		if ( count( $_POST ) && check_admin_referer( 'form-settings-' . $active_tab ) && !isset($_POST[ 'configured' ])) {
			switch ($active_tab) {
				case 'options':
					Canvas::set_theme(!empty( $_POST[ 'different_theme_for_app' ] ), $_POST[ 'theme' ] );
					break;

				case 'editor':
					update_option( 'canvas_editor_css', (isset($_POST[ 'canvas_editor_css' ]) ? $_POST[ 'canvas_editor_css' ] : '' ));
					break;

				case 'push':
					Canvas::set_option( 'push_app_id', sanitize_text_field( $_POST[ 'canvas_push_app_id' ] ) );
					Canvas::set_option( 'push_key', sanitize_text_field( $_POST[ 'canvas_push_key' ] ) );
					Canvas::set_option( 'push_auto_enabled', !empty( $_POST[ 'canvas_push_auto_enabled' ] ) );

					// Categories and taxonomies
					CanvasAdmin::push_notification_taxonomies_clear();
					CanvasAdmin::push_notification_taxonomies_clear( 'taxonomy' );
					if ( isset( $_POST[ 'canvas_push_categories' ] ) ) {
						if ( is_array( $_POST[ 'canvas_push_categories' ] ) ) {
							$cat_list = array();
							$tax_list = array();
							foreach ( $_POST[ 'canvas_push_categories' ] as $categoryID ) {
								if (0 === strpos($categoryID, 'tax:' )) {
									$tax_list[] = absint(str_replace( 'tax:', '', $categoryID));
								} else {
									$cat_list[] = $categoryID;
								}
							}
							CanvasAdmin::push_notification_taxonomies_set($cat_list);
							CanvasAdmin::push_notification_taxonomies_set($tax_list, 'taxonomy' );
						}
					}

					// Post types
					$include_post_types = '';
					if ( isset( $_POST['canvas_push_post_types'] ) && count( $_POST[ 'canvas_push_post_types' ] ) ) {
						$include_post_types = implode( ",", $_POST[ 'canvas_push_post_types' ] );
					}
					Canvas::set_option( 'push_post_types', sanitize_text_field( $include_post_types ) );
					Canvas::set_option( 'push_auto_use_cat', isset( $_POST[ 'canvas_push_auto_use_cat' ] ) );

					$push_auto_tags = array();
					if (!empty($_POST[ 'canvas_push_auto_tags' ])) {
						$push_auto_tags = explode( ',', $_POST[ 'canvas_push_auto_tags' ] );
						foreach ($push_auto_tags as $key => $value) {
							$push_auto_tags[$key] = strtolower(trim($value));
						}
					}
					Canvas::set_option( 'push_auto_tags', $push_auto_tags);

					Canvas::set_option( 'push_log_enable', isset( $_POST[ 'canvas_push_log_enable' ] ) );
					break;

				case 'login':
					Canvas::set_option( 'login_enabled', !empty($_POST[ 'canvas_login_enabled' ]));
					Canvas::set_option( 'login_hide_back', !empty($_POST[ 'canvas_login_hide_back' ]));
					Canvas::set_option( 'login_hide_register', !empty($_POST[ 'canvas_login_hide_register' ]));
					Canvas::set_option( 'login_hide_remind', !empty($_POST[ 'canvas_login_hide_remind' ]));
					Canvas::set_option( 'editor_login_css', (isset($_POST[ 'canvas_editor_login_css' ]) ? $_POST[ 'canvas_editor_login_css' ] : '' ));
					break;
			}
			$updated = true;
		}
		// show settings form
		CanvasViews::view( 'settings-' . $active_tab, array(
			'active_tab' => $active_tab,
			'updated' => $updated,
			'show_sidebar' => true,
			'tabs' => CanvasAdmin::$admin_pages,
			'tab_path' => 'admin.php?page=canvas&tab=',
			'show_form' => true,
		) );
	}

	public static function push_menu() {
		// for old Wordpress versions
		if (!function_exists( 'set_current_screen' )) {
			self::add_scripts(true);
		}
		do_action( 'canvas_push_menu', Canvas::$slug);

		// show settings form
		$active_tab = 'notifications';
		CanvasViews::view( 'push-notifications', array(
			'active_tab' => $active_tab,
			'tabs' => CanvasAdmin::$admin_notifications,
			'tab_path' => 'admin.php?page=canvas_push&tab=',
			'show_sidebar' => false
		) );

	}

	public static function add_scripts($add_google = false) {
		wp_enqueue_media();

		wp_register_script( 'areyousure', CANVAS_URL . 'assets/libs/jquery.are-you-sure.js', 'jquery', null, true );
		wp_register_script( 'jquerychosen', CANVAS_URL . 'assets/libs/chosen/chosen.jquery.min.js', 'jquery', null, true );
		wp_register_script( 'canvas-admin-js', CANVAS_URL . 'assets/js/admin.js', array( 'jquery', 'jquerychosen' ,'areyousure' ), null, true );
		if ($add_google) {
			wp_register_script( 'google_chart', 'https://www.google.com/jsapi' );
			wp_enqueue_script( 'google_chart' );
		}
		wp_enqueue_script( 'areyousure' );
		wp_enqueue_script( 'jquerychosen' );
		wp_enqueue_script( 'canvas-admin-js' );

		wp_register_style( 'canvas-css', CANVAS_URL . 'assets/css/admin.css' );
		wp_register_style( 'jquerychosen-css', CANVAS_URL . 'assets/libs/chosen/chosen.css' );
		wp_enqueue_style( 'jquerychosen-css' );
		wp_enqueue_style( 'canvas-css' );
	}

	/**
	* Save options from initial screen
	*
	*/
	public static function save_initial_data() {
		Canvas::set_option( 'init_site', isset($_POST[ 'ml_site' ]) ? $_POST[ 'ml_site' ] : '');
		Canvas::set_option( 'init_name', isset($_POST[ 'ml_name' ]) ? $_POST[ 'ml_name' ] : '');
		Canvas::set_option( 'init_email', isset($_POST[ 'ml_email' ]) ? $_POST[ 'ml_email' ] : '');
		die('Ok');
	}

	/**
	* Return name of log file
	*
	*/
	public static function get_push_log_name( $web_path = false ) {
		$filename = Canvas::get_option( 'push_log_name' );
		if (empty($filename)) {
			$site = str_replace(array( 'https://', 'http://', '/', ':' ), array( '', '', '_', '' ), get_site_url());
			$filename = $site . '-canvaspush' .  rand(10000000, 99999999) . '.txt';
			Canvas::set_option( 'push_log_name', $filename);
		}
		$paths = wp_upload_dir();
		if ($web_path) {
			return $paths[ 'baseurl' ] . '/' . $filename;
		} else {
			return $paths[ 'basedir' ] . '/' . $filename;
		}
	}

	public static function run_db_install() {
		global $wpdb;
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_name = $wpdb->prefix . "canvas_notifications";
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
			$sql = "CREATE TABLE " . $table_name . " (
			id bigint(11) NOT NULL AUTO_INCREMENT,
			time bigint(11) DEFAULT '0' NOT NULL,
			post_id bigint(11),
			url VARCHAR(255) NULL DEFAULT NULL,
			msg blob,
			android varchar(1) NOT NULL,
			ios varchar(1) NOT NULL,
			tags blob,
			UNIQUE KEY id (id),
			KEY post_id (post_id)
			);";

			dbDelta( $sql );
		}
	}

	/**
	* Get list of categories or taxonomies allowed for notifications
	*
	* @param string $taxonomy
	*/
	static public function push_notification_taxonomies_get($taxonomy = 'category' ) {
		return Canvas::get_option( 'push_list_' . $taxonomy, array());
	}

	/**
	* Clear list of categories or taxonomies allowed for notifications
	*
	* @param string $taxonomy
	*/
	static private function push_notification_taxonomies_clear($taxonomy = 'category' ) {
		Canvas::set_option( 'push_list_' . $taxonomy, array());
	}

	/**
	* Save list of categories or taxonomies allowed for notifications
	*
	* @param array $taxonomies_list
	* @param string $taxonomy
	*/
	static private function push_notification_taxonomies_set( $taxonomies_list, $taxonomy = 'category'  ) {
		Canvas::set_option( 'push_list_' . $taxonomy, $taxonomies_list);
	}
}