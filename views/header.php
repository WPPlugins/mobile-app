<?php
if (!empty($updated)) { // Show message
	?>

	<div id="message" class="updated">
		<p>Settings updated.</p>
	</div>
	<?php
}
?>
<div id="wrap" class="canvas_wrap">
<div class="canvas_header">
	<a href="<?php echo esc_url(Canvas::main_settings_url()); ?>" class="canvas-logo"><img src="<?php echo CANVAS_URL; ?>assets/img/canvas-logo-black.png" width="120"/></a>

	<br/>		<br/>

</div>
<?php if (!empty($tabs) && count($tabs) > 1) { ?>
	<div class="wp-filter">
		<ul class="filter-links">
			<?php foreach ($tabs as $tab => $tab_name) {
				$active_class = $tab == $active_tab ? ' class="current"' : '';
				?><li><a href="<?php echo admin_url( $tab_path . $tab ); ?>"<?php echo $active_class; ?>><?php echo esc_html( $tab_name ); ?></a></li>
				<?php
			} ?>
		</ul>
	</div>
	<?php } ?>
<div>
<?php if (!empty($show_sidebar)) { // include sidebar
	require_once($dir . '/sidebar.php' );
}
if (!empty($show_form)) { // include form wrapper
	?>
	<form method="post" action="<?php echo admin_url( 'admin.php?page=canvas&tab=' . $active_tab ); ?>" id="form_editor">
	<?php wp_nonce_field( 'form-settings-' . $active_tab ); ?>
	<?php } ?>