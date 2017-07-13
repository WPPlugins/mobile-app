<?php
define( 'ML_INIT_URL', 'https://canvas.mobiloud.com/' );
$activate_allowed = get_option( 'canvas-init_site' );
global $current_user;
?>
<script type="text/javascript">
	jQuery(function () {
		jQuery('#configure_button').on('click', function() {
			var ml_name = jQuery("#canvas-user-name").val();
			var ml_email = jQuery("#canvas-user-email").val();
			var ml_site = jQuery("#canvas-user-site").val();
			if (ml_name == '' || ml_email == '' || ml_site == '') {
				ml_alert_show('Please complete all details');
				return false;
			}
			var url = "<?php echo esc_attr(ML_INIT_URL); ?>?name=" + ml_name + '&email=' + ml_email + '&url=' + ml_site;
			jQuery('#configure_button').attr('href', url);
			var data = {
				action: "canvas_save_initial_data",
				ml_name: ml_name,
				ml_email: ml_email,
				ml_site: ml_site,
				'_': Math.random()
			};
			jQuery.post(ajaxurl, data, function (response) {
				can_submit = true;
				jQuery('.canvas-init-button input').trigger('click');
			});
			setTimeout(function() {
				jQuery('#activate_plugin_button').removeAttr('disabled');
				}, 250);
			jQuery('#ml_alert').hide();
			return true;
		})

	});

	function ml_alert_show(message) {
		jQuery('#ml_alert').hide().text(message).show('slow');
		jQuery('html, body').stop().animate({
			scrollTop: jQuery("#ml_alert").offset().top - 50
			}, 2000);
	}
</script>

<h1>Convert your site into native mobile apps</h1>
<h2>Try Canvas for free. Test your app online.</h2>
<div id="ml_alert" class="error" style="display:none;"><?php echo htmlspecialchars($error_text); ?></div>
<div class="clear"></div>

<div class="canvas-init-page">
	<!-- Configure block -->
	<div class="canvas-col-twothirds-f">
		<div id='canvas-initial-details' class="card">
			<h3>Configure and Preview your app</h3>
			<p>With Canvas you can turn your mobile site (any theme), into native mobile apps</p>
			<form action="<?php echo esc_attr(ML_INIT_URL); ?>" method="post" onsubmit="return form_submit();">
				<input type="hidden" name="backurl" id="backurl" value="">
				<div class='canvas-col-row'>
					<div class='canvas-col-onethirds-f'>
						<p>Your Website URL</p>
					</div>
					<div class='canvas-col-twothirds-f'>
						<input type="text" id="canvas-user-site" name="url" placeholder="Enter your website"
							value='<?php echo Canvas::get_option( 'init_site', get_site_url() ); ?>'
							required maxlength="256">
					</div>
				</div>

				<div class='canvas-col-row'>
					<div class='canvas-col-onethirds-f'>
						<p>Your Name</p>
					</div>
					<div class='canvas-col-twothirds-f'>
						<input type="text" id="canvas-user-name" name="name" placeholder="Enter your name"
							value='<?php echo Canvas::get_option( 'init_name', '' ); ?>' required maxlength="256">
					</div>
				</div>

				<div class='canvas-col-row'>
					<div class='canvas-col-onethirds-f'>
						<p>Your Email</p>
					</div>
					<div class='canvas-col-twothirds-f'>
						<input type="email" id="canvas-user-email" name="email" placeholder="Enter your email"
							value='<?php echo Canvas::get_option( 'init_email', $current_user->user_email ); ?>'
							required maxlength="256">
					</div>
				</div>
				<div class='canvas-col-row canvas-init-button'>
					<a id="configure_button" class="button button-hero button-primary" href="<?php echo esc_attr(ML_INIT_URL); ?>"
						target="_blank">Configure your app</a>
				</div>
				<div class='canvas-col-row canvas-any-questions'>
					<p>Got any questions? Contact us at <a href="mailto:support@mobiloud.com">support@mobiloud.com</a></p>
				</div>
			</form>
		</div>
		<!-- Activate block -->
		<div class="card canvas-nowrap">
			<h3>Done with configuration?</h3>
			<p>Once you've configured your app, activate the plugin to enable
				additional functionality, like Code Editor and Login support.</p>
			<form action="" method="post">
				<div class='canvas-col-row'>
					<?php wp_nonce_field( 'form-settings-options' ); ?>
					<input type="hidden" name="configured" value="1">
					<input type="submit" name="submit" class="button button-hero button-primary"
						value="Activate the plugin" id="activate_plugin_button"<?php if (empty($activate_allowed)) {?> disabled="disabled"<?php } ?>>
				</div>
			</form>
		</div>
	</div>
	<!-- Learn more block -->
	<div class="canvas-col-onethirds-f">

		<div class="card">
			<h3>Learn more about MobiLoud</h3>
			<p>Turn your website into native mobile apps with Canvas. Any mobile or responsive theme and plugin supported.</p>
			<p>
				<a href="https://www.mobiloud.com/wordpress-mobile-app/">Features</a><br>
				<a href="https://www.mobiloud.com/pricing/?type=other">Pricing</a><br>
				<a href="https://www.mobiloud.com/help/article-categories/canvas/">Knowledge Base</a><br>
				<a href="https://calendly.com/pietro/mobiloud-welcome">Schedule a Call</a><br>
				<a href="mailto:support@mobiloud.com">Support</a>
			</p>
		</div>
	</div>
	<div class="clear"></div>
	<div id="ml_init_terms">
		<small>By signing up you agree to MobiLoud's <a target="_blank" href="https://www.mobiloud.com/terms">Terms
			of service</a> and <a target="_blank" href="https://www.mobiloud.com/privacy/">Privacy policy</a>.
		</small>
	</div>
</div>
