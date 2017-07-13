<div class="canvas-block">
	<div class="canvas-header"><h1><?php echo CanvasAdmin::$admin_pages[$active_tab]; ?></h1></div>
	<div class="canvas-body">


		<div class="canvas-line">
			<div class="canvas-row-50">
				<h4>Enable Login Features</h4>
				<p>Add the ability to send push notifications per user and improves general login features on the website</p>
			</div>
			<div class="canvas-row-50">
				<?php
				$page_enabled = Canvas::get_option( 'login_enabled' );
				?>
				<p>
					<input name="canvas_login_enabled" type="checkbox" id="canvas_login_enabled" value="1" <?php if ($page_enabled) { echo 'checked="checked"';}; ?>
						class="canvas-other-options-checkbox">
					<label for="canvas_login_enabled">Enable login features</label>
				</p>
			</div>
		</div>

		<div class="canvas-line canvas-other-options<?php if (!$page_enabled) { echo ' canvas_hidden';} ?>">
			<div class="canvas-row-50">
				<h4>Hide links in the login page</h4>
				<p>Settings to hide elements that are displayed by default under the wp-login.php page.</p>
			</div>
			<div class="canvas-row-50">
				<p>
					<input name="canvas_login_hide_back" type="checkbox" id="canvas_login_hide_back" value="1"
						<?php if (Canvas::get_option( 'login_hide_back', true )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_login_hide_back">Hide the "Go back to website name" link</label>
				</p>
				<p>
					<input name="canvas_login_hide_register" type="checkbox" id="canvas_login_hide_register" value="1"
						<?php if (Canvas::get_option( 'login_hide_register', true )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_login_hide_register">Hide the "Register" link</label>
				</p>
				<p>
					<input name="canvas_login_hide_remind" type="checkbox" id="canvas_login_hide_remind" value="1"
						<?php if (Canvas::get_option( 'login_hide_remind', true )) { echo 'checked="checked"';}; ?>>
					<label for="canvas_login_hide_remind">Hide the "Remind me" checkbox</label>
				</p>
			</div>
		</div>

		<div class="canvas-line canvas-other-options<?php if (!$page_enabled) { echo ' canvas_hidden';} ?>">
			<div class="canvas-row-50">
				<h4>Custom CSS for Login Screen</h4>
				<p>Include the CSS code that should be injected into your App for Login Screen</p>
			</div>
			<div class="canvas-row-50">
				<p>
					<textarea class="canvas-editor-textarea" name="canvas_editor_login_css"
						id="canvas_editor_login_css"><?php echo stripslashes( htmlspecialchars(Canvas::get_option( 'editor_login_css', ''))); ?></textarea>
				</p>
			</div>
		</div>
		<div class="clearfix"></div>
	</div>

</div>
