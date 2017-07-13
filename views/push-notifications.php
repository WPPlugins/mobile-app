<?php
if (!defined( 'CANVAS_DIR' )) {
	die();
}
require_once(CANVAS_DIR . 'core/push/canvas-notifications.class.php' );
?>
<div class="canvas-block">
	<div class="canvas-header"><h1>Send manual message</h1></div>
	<div class="canvas-body">
		<form action="" id="canvas_manual_message">
			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Message</h4>
				</div>
				<div class="canvas-row-50">
					<input name="canvas_message" type="text" id="canvas_message" value="">
					<p id="canvas_message_chars" class="description">107 characters left.</p>
				</div>
			</div>

			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Attach</h4>
				</div>
				<div class="canvas-row-50">
					<select id="canvas_notification_data_id">
						<option value=''>Loading...</option>
					</select>
					<p class="description">You can attach a post or a page to your notification (optional).</p>
				</div>
			</div>

			<div class="canvas-line" id="canvas_post_id_block" style="display: none;">
				<div class="canvas-row-50">
					<h4>Custom Post/Page ID</h4>
				</div>
				<div class="canvas-row-50">
					<input id="canvas_post_id" placeholder="Custom ID" name="canvas_post_id" type="text">
				</div>
			</div>

			<div class="canvas-line" id="canvas_url_block" style="display: none;">
				<div class="canvas-row-50">
					<h4>URL</h4>
				</div>
				<div class="canvas-row-50">
					<input id="canvas_url" placeholder="http://www.domain.com/url" name="canvas_url" type="url" maxlength="255"/>
				</div>
			</div>

			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Send to Platform</h4>
				</div>
				<div class="canvas-row-50">
					<?php
					$push_api = CanvasNotifications::get();;
					$registeredDevicesCount = $push_api->registered_devices_count();

					$total_count            = (isset($registeredDevicesCount[ 'total' ])) ? $registeredDevicesCount[ 'total' ] : 0;
					$android_count          = 0;
					$ios_count              = 0;
					if ( $registeredDevicesCount[ 'android' ] !== null ) {
						$total_count += $registeredDevicesCount[ 'android' ];
						$android_count = $registeredDevicesCount[ 'android' ];
					}
					if ( $registeredDevicesCount[ 'ios' ] !== null ) {
						$total_count += $registeredDevicesCount[ 'ios' ];
						$ios_count = $registeredDevicesCount[ 'ios' ];
					}
					?>
					<p>
						<input id="canvas_os_all" type="radio" name='canvas_os' value="all" checked="checked">
						<label for="canvas_os_all"> All (<?php echo $total_count; ?> total devices)</label>
					</p>
					<p>
						<input id="canvas_os_android" type="radio" name='canvas_os' value="android">
						<label for="canvas_os_android"> Android only</label>
					</p>
					<p>
						<input id="canvas_os_ios" type="radio" name='canvas_os' value="ios">
						<label for="canvas_os_ios"> iOS only</label>
					</p>
				</div>
			</div>

			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Notification tags</h4>
				</div>
				<div class="canvas-row-50">
					<input id="category_as_tag" type="checkbox" name='category_as_tag'
						value="1"><label for="category_as_tag"> Use post category slugs as tags if a post is attached</label>
				</div>
			</div>

			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Manual notification tags</h4>
				</div>
				<div class="canvas-row-50">
					<input class="canvas-tags" id="canvas_tags_list" type="text" name='canvas_tags_list' value="">
					<p class="description canvas-tags">The field values must be placed in a comma separated list.</p>
				</div>
			</div>


			<div class="canvas-line">
				<div id="success-message" class="updated" style="display: none;">Your message has been sent!</div>
				<div id="error-message" class="error" style="display: none;"></div>

				<input type="submit" onclick="return false;" class='button button-primary button-large'
					id="canvas_notification_manual_send_submit" value="<?php _e( 'Send' ); ?>"
					data-send="<?php _e( 'Send' ); ?>"
					data-sending="<?php _e( 'Sending...' ); ?>"
					/>
			</div>

		</form>
	</div>
</div>


<div class="canvas-block" id="canvas_history_block">
	<div class="canvas-header"><h1>Notification history</h1></div>
	<div class="canvas-body">
		<div id="canvas_notification_history"></div>
	</div>
</div>

