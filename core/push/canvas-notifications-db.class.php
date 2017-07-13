<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}

class CanvasNotificationsDb {

	/**
	* Check is post notified
	*
	* @param int $post_id
	*/
	public static function is_notified( $post_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "canvas_notifications";
		$num        = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM $table_name WHERE post_id = %d", $post_id ) );

		return $num > 0;
	}

	/**
	* Mark post as notified
	*
	* @param mixed $postID
	*/
	public static function set_post_id_as_notified( $postID ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "canvas_notifications";
		$wpdb->insert(
			$table_name,
			array(
				'time'    => current_time( "timestamp" ),
				'post_id' => $postID,
			)
		);

		if (!empty($wpdb->last_error)) {
			CanvasAdmin::run_db_install();
			$wpdb->insert(
				$table_name,
				$values
			);
		}
	}

	/**
	* Get data for last notifications
	*
	* @param int $limit
	*/
	public static function get_last_notifications( $limit = null ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "canvas_notifications";
		$sql        = "SELECT * FROM $table_name ORDER BY time DESC";
		if ( $limit != null ) {
			$sql .= " LIMIT " . $limit;
		}

		return $wpdb->get_results( $sql );
	}

	/**
	* Get notifications by filds from filter
	*
	* @param array $filter
	* @return array
	*/
	public static function get_notification_by( $filter = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . "canvas_notifications";
		$sql        = "
		SELECT * FROM " . $table_name . "
		WHERE
		msg = '" . $wpdb->escape( $filter['msg'] ) . "'
		";
		if ( $filter['post_id'] != null ) {
			$sql .= " AND post_id = " . $wpdb->escape( $filter['post_id'] );
		}
		if ( $filter['url'] != null ) {
			$sql .= " AND url = '" . $wpdb->escape( $filter['url'] ) . "'";
		}
		$sql .= " AND android = '" . $wpdb->escape( $filter['android'] ) . "'";
		$sql .= " AND ios = '" . $wpdb->escape( $filter['ios'] ) . "'";

		$results = $wpdb->get_results( $sql );

		// try to update db
		if ($wpdb->last_error) {
			CanvasAdmin::run_db_install();

			$results = $wpdb->get_results( $sql );
		}

		return $results;
	}

	/**
	* Insert new record to notifications db
	*
	* @param mixed $values
	*/
	public static function insert_to_db($values) {
		global $wpdb;
		$table_name = $wpdb->prefix . "canvas_notifications";
		$wpdb->insert(
			$table_name,
			$values
		);
	}
}