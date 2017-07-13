
	<div class="canvas-block">
		<div class="canvas-header"><h1><?php echo CanvasAdmin::$admin_pages[$active_tab]; ?></h1></div>
		<div class="canvas-body">


			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Enable a different theme</h4>
					<p>Use a different theme for your app</p>
				</div>
				<div class="canvas-row-50">
					<?php
					$current_theme_name = Canvas::get_option( Canvas::THEME_OPTION, '' );
					if (empty($current_theme_name)) {
						$current = wp_get_theme();
						$current_theme_name = $current->TextDomain;
					}

					$themes_list = apply_filters( 'canvas_themes', wp_get_themes() );
					?>
					<p>
						<input name="different_theme_for_app" type="checkbox" id="canvas_different_theme" value="1" <?php if (Canvas::get_option( Canvas::THEME_DIFFERENT )) { echo 'checked="checked"';}; ?>>
						<label for="canvas_different_theme">Use a different theme for your mobile app</label>
					</p>
					<p id="theme_choice_block"<?php if (!Canvas::get_option( Canvas::THEME_DIFFERENT )) { echo ' class="canvas_hidden"';} ?>>
						<select id="theme" name="theme">
							<?php foreach ($themes_list as $key => $value) { ?>
								<option value="<?php echo esc_attr($key); ?>" <?php selected(Canvas::get_option( Canvas::THEME_OPTION ), $key, true ); ?>><?php echo htmlspecialchars($value); ?></option>
								<?php
							} ?>
						</select>
						<span id="canvas_theme_link"><a class="button button-large" href="<?php echo Canvas::get_theme_customize_url(); ?>">Customize the theme</a></span>
						<span id="canvas_theme_warning" class="canvas_hidden canvas_warning">Please save settings before customizing this theme.</span>
					</p>
				</div>

			</div>

			<div class="clearfix"></div>

		</div>

	</div>

