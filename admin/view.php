<?php

// pull all the stylesheets from the homepage
$sheets = [];
$response = wp_remote_get( get_home_url() );
if ( is_array( $response ) && ! is_wp_error( $response ) ) {
	$body = $response[ 'body' ]; // use the content
	$doc  = new DOMDocument();
	$doc->loadHTML( $body, LIBXML_NOWARNING | LIBXML_NOERROR );
	$domcss = $doc->getElementsByTagName( 'link' );
	foreach ( $domcss as $links ) {
		if ( strtolower( $links->getAttribute( 'rel' ) ) == "stylesheet" ) {
			$sheets[] = $links;
		}
	}
}

// get a list of custom post types
$my_post_types = get_post_types();
$totallycriticalcss_selected_cpt = get_option( 'totallycriticalcss_selected_cpt' ) === false ? [] : get_option( 'totallycriticalcss_selected_cpt' );

// get the options for custom styles\
$totallycriticalcss_custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' ) === false ? [] : get_option( 'totallycriticalcss_custom_dequeue' );

// get the options for custom styles\
$totallycriticalcss_custom_routes = get_option( 'totallycriticalcss_custom_routes' ) === false ? [] : get_option( 'totallycriticalcss_custom_routes' );
print_r( $totallycriticalcss_custom_routes);

$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' ) === false ? [] : get_option( 'totallycriticalcss_selected_styles' );
$stylesheets = [];
foreach ( $selected_stylesheet_dequeue as $style ) {
	$stylesheets[] = $style[ 'name' ];
}

?>
<div id="admin-view">
	
	<div id="logo"><img src="<?php echo plugin_dir_url( __DIR__ ); ?>admin/images/logo.png"></div>
	
	<form id="admin-view-form" autocomplete="off">
		
		<section id="tccssWrapper">
			
			<ul class="tabs">
				<li class="active">Stylesheets</li>
				<li class="">Custom Post Types</li>
				<li class="">Routes</li>
				<li class="">Settings</li>
			</ul>

			<ul class="tab__content">
				<li class="active">
					<div class="content__wrapper">
						
						<div class="group">
							
							<table>
								<th>
									<td>
										<span class='handle'>Custom Stylesheets</span>
									</td>
								</th>
								<?php foreach ( $totallycriticalcss_custom_dequeue as $handle => $url ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<label for="<?php echo $handle; ?>">
												<span class='handle'>( <?php echo $handle; ?> )</span>
												<span class="url"><?php echo $url; ?></span>
											</label>
										</td>
										<td class="actions">
											<button class="button dequeue-delete" data-handle="<?php echo $handle; ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
							</table>
							
							<div id="custum-dequeue-add-form" class="adder-form">
								<label for="">Handle</label>
								<input id="add-form-handle" type="text" />
								<label for="">URL</label>
								<input id="add-form-url" type="text" />
								<button id="add-custum-dequeue" class="button button-primary">Add</button>
								<button id="cancel-custum-dequeue" class="button">Cancel</button>
							</div>
							
							<button id="show-custum-dequeue" class="button button-primary">Add Custom Stylesheet</button>
							
							<table>
								<th>
									<td>
										<span class='handle'>Enqueued Stylesheets</span>
									</td>
								</th>
								<?php foreach ( $sheets as $sheet ) { 
									$sheetid = $sheet->getAttribute('id');
									$sheetid_bits = explode( '-', $sheetid );
									array_pop( $sheetid_bits );
									$sheetid_clean = implode( '-', $sheetid_bits );
									?>
									<tr>
										<td class="check">
											<input type="checkbox" 
											name="sheets" id="<?php echo $sheetid_clean; ?>" 
											value="<?php echo $sheetid_clean; ?>" 
											data-url="<?php echo $sheet->getAttribute( 'href' ); ?>" 
											<?php echo in_array( $sheetid_clean, $stylesheets ) ? 'checked="checked"' : ''; ?> />
										</td>
										<td>
											<label for="<?php echo $sheetid_clean; ?>">
												<span class='handle'>( <?php echo $sheetid_clean; ?> )</span>
												<span class="url"><?php echo $sheet->getAttribute('href'); ?></span>
											</label>
										</td>
									</tr>
								<?php } ?>
							</table>
							
							<button id="styles-toggle-all" class="button button-primary">Toggle All</button>
							
						</div>
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						
						<div class="group">
							
							<table>
								<th>
									<td>
										<span class='handle'>Post Types Enabled</span>
									</td>
								</th>
								<?php foreach ( $my_post_types as $my_post_type ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
											name="my_post_types" 
											id="<?php echo $my_post_type; ?>" 
											value="<?php echo $my_post_type; ?>" 
											<?php echo in_array( $my_post_type, $totallycriticalcss_selected_cpt ) ? 'checked="checked"' : ''; ?>>
										</td>
										<td>
											<label for="<?php echo $my_post_type; ?>">
												<span class='handle'><?php echo get_post_type_object( $my_post_type )->labels->singular_name; ?></span>
												<span class="url">( <?php echo $my_post_type; ?> )</span>
											</label>
										</td>
									</tr>
								<?php } ?>
							</table>
							
						</div>
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						
						<table>
							<th>
								<td>
									<span class='handle'>Custom Routes</span>
								</td>
							</th>
							<?php $i = 0; foreach ( $totallycriticalcss_custom_routes as $url ) { ?>
								<tr>
									<td class="check">
										<input type="checkbox" 
											disabled="disabled"
											checked="checked" />
									</td>
									<td>
										<span class=''><?php echo $url; ?></span>
									</td>
									<td class="actions">
										<button class="button route-delete" data-index="<?php echo $i; ?>">
											<span class="dashicons dashicons-trash"></span>
										</button>
									</td>
								</tr>
							<?php $i++; } ?>
						</table>
						
						<div id="custum-dequeue-add-route" class="adder-form">
							<label for="">URL</label>
							<input id="add-route-url" type="text" />
							<button id="add-custum-route" class="button button-primary">Add</button>
							<button id="cancel-custom-route" class="button">Cancel</button>
						</div>
						
						<button id="show-custum-route" class="button button-primary">Add Custom Route</button>
						
					</div>
				</li>
				<li>
					<div class="content__wrapper">
						<div class="field api-key">
							<label for="apiKey">API Key:</label><br>
							<input id="apiKey" name="apiKey" type="text" placeholder="Insert API Key" value="<?php echo get_option( 'totallycriticalcss_api_key' ); ?>">
						</div>
						<div class="field custom-stylesheet">
							<label for="customStylesheet">Custom Stylesheet Location:</label><br>
							<input id="customStylesheet" name="customStylesheet" type="text" placeholder="Insert Custom Stylesheet Location" value="<?php echo get_option( 'totallycriticalcss_custom_stylesheet_location' ); ?>">
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
