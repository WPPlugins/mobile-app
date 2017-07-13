<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}
require_once( CANVAS_DIR . 'core/canvas-admin.class.php' );
require_once( dirname(__FILE__) . '/canvas-notifications-db.class.php' );
require_once( dirname(__FILE__) . '/canvas-notifications-view.class.php' );

class CanvasNotifications {
	/**
	* @var CanvasOnesignalApi
	*/
	protected $api;

	/**
	* @var CanvasNotifications
	*/
	static private $instance = NULL;

	public static function get() {
		if (is_null(self::$instance)){
			self::$instance = new CanvasNotifications();
		}
		return self::$instance;
	}

	public function __construct() {
		require_once(dirname(__FILE__) . '/canvas-onesignal-api.class.php' );
		$this->api = new CanvasOnesignalApi(false);
	}

	/**
	* Using manual push notification require at least 'publish_posts' capability
	*
	* @param string $print Message with error
	*/
	private static function check_is_action_allowed($print = 'Not allowed' ) {
		if (!current_user_can( 'publish_posts' )) {
			die( $print );
		}
	}

	public function send_notifications($data, $tagNames = array()) {
		return $this->api->send_batch_notification( $data, $tagNames );
	}

	public function registered_devices_count() {
		return $this->api->registered_devices_count();
	}

	/**
	* Callback for auto push notifications
	*
	* @param string $new_status
	* @param string $old_status
	* @param WP_Post $post
	*/
	static public function post_published_notification( $new_status, $old_status, $post ) {

		if ( CanvasNotificationsDb::is_notified( $post->ID ) || ! self::check_post_notification_required( $post->ID ) ) {
			return;
		}

		$push_types = Canvas::get_option( "push_post_types", "post" );
		if ( strlen( $push_types ) > 0 ) {
			$push_types = explode( ",", $push_types );

			if ( $new_status == 'publish' && $old_status != 'publish' && in_array( $post->post_type, $push_types ) ) {  // only send push if it's a new publish
				$payload = array(
					'post_id' => strval( $post->ID ),
				);

				$image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'medium_large' );
				if ( is_array( $image ) ) {
					$payload[ 'featured_image' ] = $image[0];
				}
				$tagNames   = self::get_post_tags( $post->ID );
				$data       = array(
					'platform' => array( 0, 1 ),
					'msg'      => strip_tags( trim( $post->post_title ) ),
					'sound'    => 'default',
					'badge'    => '+1',
					'payload'  => $payload,
					'tags' => Canvas::get_option( 'push_auto_tags', array())
				);
				if (Canvas::get_option( 'push_auto_use_cat', false )) {
					$data[ 'tags' ] = array_merge($data[ 'tags' ], $tagNames);
				} else {
					$tagNames = array();
				}
				$push_api = CanvasNotifications::get();
				$result = $push_api->send_notifications($data, $tagNames);
				if (true === $result) {
					if (!CanvasNotificationsDb::is_notified( $post->ID )) {
						CanvasNotificationsDb::set_post_id_as_notified($post->ID);
					}
				}
			}
		}
	}

	private static function check_post_notification_required( $postId ) {
		$notification_categories = CanvasAdmin::push_notification_taxonomies_get();
		$notification_taxonomies = CanvasAdmin::push_notification_taxonomies_get( 'taxonomy' );

		if (empty($notification_categories) && empty($notification_taxonomies)) {
			return true;
		}

		if ( is_array( $notification_categories ) && count( $notification_categories ) > 0 ) {
			$post_categories = wp_get_post_categories( $postId );

			$found           = false;
			if ( is_array( $post_categories ) && count( $post_categories ) > 0 ) {
				foreach ( $post_categories as $post_category_id ) {
					foreach ( $notification_categories as $notification_category ) {
						if ( $notification_category == $post_category_id ) {
							return true;
						}
					}
				}
			}
		}

		if ( is_array( $notification_taxonomies ) && count( $notification_taxonomies ) > 0 ) {
			$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );
			$tax_list = array();
			foreach ( $taxonomies as $tax ) {
				if ($tax->query_var) {
					$tax_list[] = $tax->query_var;
				}
			}

			$post_tax = wp_get_object_terms($postId, $tax_list );
			if ( !is_wp_error( $post_tax ) && is_array( $post_tax ) && count( $post_tax ) > 0 ) {
				foreach ( $post_tax as $tax ) {
					if (in_array($tax->term_id, $notification_taxonomies)) {
						return true;
					}
				}
			}
		}
		return false;
	}

	/**
	* Get id of tags for post
	*
	* @param int $postId
	* @return array $tags
	*/
	private static function get_post_tag_ids( $postId ) {
		$post_categories = wp_get_post_categories( $postId );
		$tags            = array();
		foreach ( $post_categories as $c ) {
			$tags[] = $c;
		}

		return $tags;
	}

	/**
	* Get slugs of tags for post
	*
	* @param int $postId
	* @return array $tags
	*/
	private static function get_post_tags( $postId ) {
		$post_categories = wp_get_post_categories( $postId );
		$tags            = array();

		foreach ( $post_categories as $c ) {
			$cat    = get_category( $c );
			$tags[] = $cat->slug;
		}

		return $tags;
	}

	/**
	* Ajax callback. Check notification for duplicates. Show empty string if no dupes exists, 'true' otherwise
	*
	*/
	static public function notification_check_duplicate() {
		self::check_is_action_allowed();
		$postId  = null;
		$url  = null;
		$android = null;
		$ios     = null;
		$data_id = strlen( $_POST['data_id'] ) > 0 ? $_POST['data_id'] : false;

		if ( $data_id ) {
			if ( 'custom' == $data_id) {
				$postId = $_POST[ 'post_id' ];
			} elseif ( 'url'  == $data_id) {
				$url = $_POST[ 'url' ];
			} else {
				$postId = substr( $_POST[ 'data_id' ], 8 );
			}
		}

		switch ( $_POST['os'] ) {
			case 'all':
				$android = 'Y';
				$ios     = 'Y';
				break;
			case 'android':
				$android = 'Y';
				$ios     = 'N';
				break;
			case 'ios':
				$android = 'N';
				$ios     = 'Y';
				break;
		}
		$notifications = CanvasNotificationsDb::get_notification_by( array(
			'msg'     => trim( $_POST['msg'] ),
			'post_id' => $postId,
			'url'     => $url,
			'android' => $android,
			'ios'     => $ios
		) );
		CanvasNotificationsView::show_true_false(count( $notifications ) > 0);
		exit;
	}

	/**
	* Ajax callback. Send manual notification. Show result as json string
	*
	*/
	static public function notification_manual_send() {
		self::check_is_action_allowed();
		$result = "There was an error sending this notification";
		if ( isset( $_POST['msg'] ) ) {
			$platform = array();
			switch ( $_POST['os'] ) {
				case 'all':
					$platform = array( 0, 1 );
					break;
				case 'android':
					$platform = array( 1 );
					break;
				case 'ios':
					$platform = array( 0 );
					break;
			}
			$tags     = array();
			$postId   = null;
			$url = null;
			$category_as_tag = !empty($_POST['category_as_tag']);
			$tags_list = $_POST['tags_list'];
			if ( strlen( $_POST['data_id'] ) > 0 ) {
				if ( strpos( $_POST['data_id'], 'custom' ) !== false ) {
					$postId = $_POST['post_id'];
				} else if ( strpos( $_POST['data_id'], 'url' ) !== false ) {
					$url = $_POST['url'];
				} else {
					$postId = substr( $_POST['data_id'], 8 );
				}
			}
			if ( $postId != null && $category_as_tag ) {
				$tags = self::get_post_tags( $postId );
			}
			// append manual tags to both lists
			if ($tags_list) {
				foreach(explode( ',', $tags_list) as $manual_tag) {
					$manual_tag = trim($manual_tag);
					if (strlen($manual_tag)) {
						$tags[] = $manual_tag;
					}
				}
			}
			$payload    = array();
			if ( $postId !== null ) {
				$image   = wp_get_attachment_image_src( get_post_thumbnail_id( $postId ), 'single-post-thumbnail' );
				$payload = array(
					'post_id' => $postId,
				);
				if ( is_array( $image ) ) {
					$payload['featured_image'] = $image[0];
				}
			} elseif ($url !== null) {
				$payload = array(
					'url' => $url,
				);
			}
			$data = array(
				'platform' => $platform,
				'msg'      => trim( $_POST['msg'] ),
				'sound'    => 'default',
				'badge'    => '+1',
				'payload'  => $payload,
				'tags'     => $tags
			);

			$push_api = CanvasNotifications::get();
			$result = $push_api->send_notifications($data, $tags);
		}

		CanvasNotificationsView::show_json($result);
		exit;
	}

	/**
	* Show history chart
	*
	* @param array $notifications
	*/
	static private function notification_chart($notifications) {
		CanvasNotificationsView::show_chart($notifications);
	}

	/**
	* Ajax callback. Show history block: chart + table
	*
	*/
	static public function notification_history() {
		self::check_is_action_allowed();
		$notifications = CanvasNotificationsDb::get_last_notifications( 100 );
		self::notification_chart($notifications);
		CanvasNotificationsView::show_history($notifications);
		exit;
	}

	/**
	* Ajax callback. Show attach select content
	*
	*/
	public static function attachment_content() {
		self::check_is_action_allowed( '<option>Not allowed</option>' );
		$posts = get_posts( array(
			'posts_per_page' => 10,
			'orderby'        => 'post_date',
			'order'          => 'DESC',
			'post_type'      => 'post'
		) );
		$pages = get_pages( array(
			'sort_order'  => 'ASC',
			'sort_column' => 'post_title',
			'post_type'   => 'page',
			'post_status' => 'publish'
		) );
		CanvasNotificationsView::show_attachment($posts, $pages);
		exit;
	}

	/**
	* Save message at fields to db
	*
	* @param array $fields
	*/
	static public function save_sent_message($fields) {
		CanvasNotificationsDb::insert_to_db($fields);
	}

}