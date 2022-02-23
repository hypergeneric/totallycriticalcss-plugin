<?php
// Admin View Options Page
?>
<div id="admin-view">
	<form id="admin-view-form">
		<h1>Totally Critical CSS</h1>
		<div class="field api-key">
			<label for="apiKey">First name:</label><br>
			<input id="apiKey" name="apiKey" type="text" placeholder="Insert API Key" value="<?php echo get_option( 'totallycriticalcss_api_key' ); ?>">
		</div>
		<div class="field custom-theme">
			<label for="customTheme">Custom Theme Location:</label><br>
			<input id="customTheme" name="customTheme" type="text" placeholder="Insert Custom Theme Location" value="<?php echo get_option( 'totallycriticalcss_custom_theme_location' ); ?>">
		</div>
		<div class="field custom-stylesheet">
			<label for="customStylesheet">Custom Stylesheet Location:</label><br>
			<input id="customStylesheet" name="customStylesheet" type="text" placeholder="Insert Custom Stylesheet Location" value="<?php echo get_option( 'totallycriticalcss_custom_stylesheet_location' ); ?>">
		</div>
		<input id="submitForm" name="submitForm" type="submit" value="Submit">
	</form>
</div>
