<?php
// Admin View Options Page

$sheets = [];
$response = wp_remote_get( get_home_url() );

if ( is_array( $response ) && ! is_wp_error( $response ) ) {
	$headers = $response['headers']; // array of http header lines
	$body    = $response['body']; // use the content
	
	$doc = new DOMDocument();
	$doc->loadHTML($body, LIBXML_NOWARNING | LIBXML_NOERROR);
	$domcss = $doc->getElementsByTagName('link');
	foreach ( $domcss as $links ) {
		if( strtolower($links->getAttribute('rel')) == "stylesheet" ) {
			$sheets[] = $links;
		}
	}
}

?>
<div id="admin-view">
	<form id="admin-view-form">
		<h1>Totally Critical CSS</h1>
		<div class="field api-key">
			<label for="apiKey">API Key:</label><br>
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
		<div class="field custom-dequeue">
			<label for="customDequeue">Custom Stylesheet Dequeue (comma-separated):</label><br>
			<input id="customDequeue" name="customDequeue" type="text" placeholder="Insert Stylesheet Names i.e. parent,child" value="<?php echo get_option( 'totallycriticalcss_custom_dequeue' ); ?>">
		</div>
		<div class="group">
			<?php
			foreach ( $sheets as $sheet ) {
				$sheetid = $sheet->getAttribute('id');
				$sheetid_bits = explode( '-', $sheetid );
				array_pop( $sheetid_bits );
				$sheetid_clean = implode( '-', $sheetid_bits );
				?>
				<input type="checkbox" name="sheets" value="<?php echo $sheetid_clean; ?>">
				<label for="vehicle1"><?php echo '( ' . $sheetid_clean . ' ): ' . $sheet->getAttribute('href'); ?></label><br>
				<?php
			}
			?>
		</div>
		<input id="submitForm" name="submitForm" type="submit" value="Submit">
	</form>
</div>
