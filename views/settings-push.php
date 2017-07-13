<div class="canvas-block">
	<div class="canvas-header"><h1>Push Keys</h1></div>
	<div class="canvas-body">

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Push App ID</h4>
				<p>App ID provided by your Push Service</p>
			</div>
			<div class="canvas-row-50">
				<input name="canvas_push_app_id" type="text" id="canvas_push_app_id" value="<?php echo esc_attr(Canvas::get_option( 'push_app_id' , '' )); ?>">
			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Secret Key</h4>
				<p>Secret Key provided by your Push Service</p>
			</div>
			<div class="canvas-row-50">
				<input name="canvas_push_key" type="text" id="canvas_push_key" value="<?php echo esc_attr(Canvas::get_option( 'push_key' , '' )); ?>">
			</div>
		</div>
		<div class="canvas-line">
			<p>Can't find your keys? <a href='mailto:support@mobiloud.com?subject=Push%20keys'>Request your keys</a> from our support team.</p>
		</div>

		<div class="clearfix"></div>
	</div>
</div>

<div class="canvas-block">
	<div class="canvas-header"><h1>Automatic Push Notifications</h1></div>
	<div class="canvas-body">

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Automatic Push Notifications</h4>
				<p>Automatically send push notifications when a new post is published</p>
			</div>
			<div class="canvas-row-50">
				<p>
					<input name="canvas_push_auto_enabled" type="checkbox" id="canvas_push_auto_enabled" value="1"
						<?php if (Canvas::get_option( 'push_auto_enabled' )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_push_auto_enabled">Send notifications automatically</label>
				</p>
			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Categories for Push Notifications</h4>
				<p>Select which categories will generate a push notification (empty for all)</p>
			</div>
			<div class="canvas-row-50">
				<select id="canvas_push_categories" name='canvas_push_categories[]'
					data-placeholder="Select Categories..." style="width:100%;" multiple class="canvas-chosen-select">
					<option></option>
					<?php
					$categories = get_categories(array( 'hide_empty' => 0));
					$pushCategories = CanvasAdmin::push_notification_taxonomies_get();

					foreach ( $categories as $c ) {
						$selected = (in_array($c->cat_ID, $pushCategories)) ? ' selected' : '';
						echo "<option value='$c->cat_ID'$selected>Category: $c->cat_name</option>";
					}

					$tax_list = CanvasAdmin::push_notification_taxonomies_get( 'taxonomy' ); // current tax list
					$taxonomies = get_taxonomies( array( '_builtin' => false ), 'objects' );

					foreach ( $taxonomies as $tax ) {
						$terms = get_terms( $tax->query_var, array( 'hide_empty' => false ) );
						if ( count( $terms ) ) {
							foreach ( $terms as $term ) {
								$parent_name = '';
								if ( $term->parent ) {
									$parent_term = get_term_by( 'id', $term->parent, $tax->query_var );
									if ( $parent_term ) {
										$parent_name = $parent_term->name . ' - ';
									}
								}
								$selected = in_array($term->term_id, $tax_list) ? ' selected="selected"' : '';
								echo "<option value='tax:{$term->term_id}'$selected>{$tax->label}: {$parent_name}{$term->name}</option>";
							}
						}
					}
					?>
				</select>

			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Post types for Push Notifications</h4>
				<p>Select which post types will generate a push notification</p>
			</div>
			<div class="canvas-row-50">
				<?php
				$posttypes         = get_post_types( '', 'names' );
				$includedPostTypes = explode( ",", Canvas::get_option( "push_post_types", "post" ) );
				foreach ( $posttypes as $v ) {
					if ( $v != "attachment" && $v != "revision" && $v != "nav_menu_item" ) {
						$checked = '';
						if ( in_array( $v, $includedPostTypes ) ) {
							$checked = "checked";
						}
						?>
						<p>
							<input type="checkbox" id='canvas_push_post_types_<?php echo esc_attr( $v ); ?>' name="canvas_push_post_types[]"
								value="<?php echo esc_attr( $v ); ?>" <?php echo $checked; ?>/>
							<label for="canvas_push_post_types_<?php echo esc_attr( $v ); ?>"><?php echo esc_html( $v ); ?></label>
						</p>
						<?php
					}
				}
				?>
			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Tags</h4>
				<p>Use category name as tags for automatic notifications</p>
			</div>
			<div class="canvas-row-50">
				<p>
					<input name="canvas_push_auto_use_cat" type="checkbox" id="canvas_push_auto_use_cat" value="1"
						<?php if (Canvas::get_option( 'push_auto_use_cat' )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_push_auto_use_cat">Use category name as tags</label>
				</p>
			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<p>Always use these tags for automatic notifications</p>
			</div>
			<div class="canvas-row-50">
				<input name="canvas_push_auto_tags" type="text" id="canvas_push_auto_tags" value="<?php echo esc_attr(implode( ',', Canvas::get_option( 'push_auto_tags', array() ))); ?>">
				<p class="description canvas-tags">The field values must be placed in a comma separated list.</p>
			</div>
		</div>

		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Enable logging for debugging</h4>
				<p>Store a log of the requests and responses received from the push server, in the order for us
					to debug any issues with push notifications. Logs will be saved to a file on your server.</p>
			</div>
			<div class="canvas-row-50">
				<p>
					<input name="canvas_push_log_enable" type="checkbox" id="canvas_push_log_enable" value="1"
						<?php if (Canvas::get_option( 'push_log_enable' )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_push_log_enable">Enable Push Logging</label>
				</p>
				<p id="canvas_push_log_name_block"<?php echo Canvas::get_option( 'push_log_enable' ) ? '' : ' style="display:none;"'; ?>>
					<input type="text" value="<?php echo esc_attr(CanvasAdmin::get_push_log_name( true )); ?>" readonly="readonly">
				</p>
			</div>
		</div>

		<div class="clearfix"></div>
	</div>
</div>