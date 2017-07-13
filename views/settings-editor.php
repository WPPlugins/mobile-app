	<div class="canvas-block">
		<div class="canvas-header"><h1><?php echo CanvasAdmin::$admin_pages[$active_tab]; ?></h1></div>
		<div class="canvas-body">

			<div class="canvas-line">
				<div class="canvas-row-50">
					<h4>Custom CSS for your App</h4>
					<p>Include the CSS code that should be injected into your App</p>
				</div>
				<div class="canvas-row-50">
					<p>
					<textarea class="canvas-editor-textarea" name="canvas_editor_css"
						id="canvas_editor_css"><?php echo stripslashes( htmlspecialchars(get_option( 'canvas_editor_css' ))); ?></textarea>
					</p>
				</div>
			</div>

			<div class="clearfix"></div>
		</div>

	</div>
