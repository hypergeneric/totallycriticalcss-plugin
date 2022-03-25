<?php

// Admin View Options Page

$sheets = [];
$response = wp_remote_get( get_home_url() );
if ( is_array( $response ) && ! is_wp_error( $response ) ) {
	$headers = $response[ 'headers' ]; // array of http header lines
	$body    = $response[ 'body' ]; // use the content
	$doc = new DOMDocument();
	$doc->loadHTML( $body, LIBXML_NOWARNING | LIBXML_NOERROR );
	$domcss = $doc->getElementsByTagName( 'link' );
	foreach ( $domcss as $links ) {
		if( strtolower( $links->getAttribute( 'rel' ) ) == "stylesheet" ) {
			$sheets[] = $links;
		}
	}
}

$my_post_types = get_post_types();

?>
<div id="admin-view">
	<div id="logo"><img src="<?php echo plugin_dir_url( __DIR__ ); ?>admin/images/logo.png"></div>
	<form id="admin-view-form">
		<section id="tccssWrapper">
			<ul class="tabs">
				<li class="active">Stylesheets</li>
				<li class="">Custom Post Types</li>
				<li class="">Settings</li>
				<li class="">API Key</li>
			</ul>

			<ul class="tab__content">
				<li class="active">
					<div class="content__wrapper">
						<!-- Make these toggleable / dequeue all selected and add to footer / add toggle all -->
						<div class="group">
							<div class="field custom-dequeue">
								<label for="customDequeue">Additional Custom Stylesheet Dequeue (comma-separated):</label><br>
								<input id="customDequeue" name="customDequeue" type="text" placeholder="Insert Stylesheet Names i.e. parent,child" value="<?php echo get_option( 'totallycriticalcss_custom_dequeue' ); ?>">
							</div>	
							<div class="rows">
							<?php
							foreach ( $sheets as $sheet ) {
								$sheetid = $sheet->getAttribute('id');
								$sheetid_bits = explode( '-', $sheetid );
								array_pop( $sheetid_bits );
								$sheetid_clean = implode( '-', $sheetid_bits );
								?>
								<div class="row">
									<?php
									$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' );

									$stylesheets = array();
									if( $selected_stylesheet_dequeue ) {
										foreach ( $selected_stylesheet_dequeue as $style) {
											$name = $style[ 'name' ];
											if( $name == $sheetid_clean ) {
												array_push( $stylesheets, $name );
											}
										}
									} ?>
									<input type="checkbox" name="sheets" id="<?php echo $sheetid_clean; ?>" value="<?php echo $sheetid_clean; ?>" data-url="<?php echo $sheet->getAttribute( 'href' ); ?>" <?php echo in_array( $sheetid_clean, $stylesheets ) ? 'checked="checked"' : ''; ?>>

									<label for="<?php echo $sheetid_clean; ?>"><span class='sheetid'><?php echo '( ' . $sheetid_clean . ' ):</span> ' . $sheet->getAttribute('href'); ?></label><br>
								</div>
								<?php
							}
							?>
							</div>
							<div class="toggle-all">Toggle All</div>
						</div>
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						<!-- Make these toggleable / dequeue all selected and add to footer / add toggle all -->
						<div class="group">
							<div class="rows">
							<?php
							$totallycriticalcss_selected_cpt = get_option( 'totallycriticalcss_selected_cpt' ) === false ? [] : get_option( 'totallycriticalcss_selected_cpt' );
							foreach ( $my_post_types as $my_post_type ) {
								$post_type_obj = get_post_type_object( $my_post_type );
							?>
								<div class="row">
									<input type="checkbox" name="my_post_types" id="<?php echo $my_post_type; ?>" value="<?php echo $my_post_type; ?>" <?php echo in_array( $my_post_type, $totallycriticalcss_selected_cpt ) ? 'checked="checked"' : ''; ?>>
									<label for="<?php echo $my_post_type; ?>"><span class='cpt'><?php echo $post_type_obj->labels->singular_name; ?></span></label><br>
								</div>
							<?php } ?>
							</div>
						</div>
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						<div class="field custom-stylesheet">
							<label for="customStylesheet">Custom Stylesheet Location:</label><br>
							<input id="customStylesheet" name="customStylesheet" type="text" placeholder="Insert Custom Stylesheet Location" value="<?php echo get_option( 'totallycriticalcss_custom_stylesheet_location' ); ?>">
						</div>					
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						<div class="field api-key">
							<label for="apiKey">API Key:</label><br>
							<input id="apiKey" name="apiKey" type="text" placeholder="Insert API Key" value="<?php echo get_option( 'totallycriticalcss_api_key' ); ?>">
						</div>
					</div>
				</li>
			</ul>
		</section>
		<div id="submitWrap">
			<input id="submitForm" name="submitForm" type="submit" value="Save">
		</div>
	</form>
</div>
