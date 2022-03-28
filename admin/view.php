<?php

// pull the options
$totallycriticalcss_selected_cpt = get_option( 'totallycriticalcss_selected_cpt' ) === false ? [] : get_option( 'totallycriticalcss_selected_cpt' );
$totallycriticalcss_custom_dequeue = get_option( 'totallycriticalcss_custom_dequeue' ) === false ? [] : get_option( 'totallycriticalcss_custom_dequeue' );
$totallycriticalcss_custom_routes = get_option( 'totallycriticalcss_custom_routes' ) === false ? [] : get_option( 'totallycriticalcss_custom_routes' );
$selected_stylesheet_dequeue = get_option( 'totallycriticalcss_selected_styles' ) === false ? [] : get_option( 'totallycriticalcss_selected_styles' );

// get a list of custom post types
$my_post_types = get_post_types();

// pull all the stylesheets from the homepage
$sheets = [];
$response = wp_remote_get( get_home_url() . "/?totallycriticalcss=preview" );
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

?>
<div id="admin-view">
	
	<div id="logo"><img src="<?php echo plugin_dir_url( __DIR__ ); ?>admin/images/logo.png"></div>
	
	<form id="admin-view-form" autocomplete="off">
		
		<section id="tccssWrapper">
			
			<ul class="tabs">
				<li data-tab="settings">Settings</li>
				<li data-tab="stylesheets">Stylesheets</li>
				<li data-tab="cpt">Custom Post Types</li>
				<li data-tab="routes">Routes</li>
			</ul>

			<ul class="tab__content">
				
				<li id="tab-settings">
					<div class="content__wrapper">
						
						<div class="field api-key">
							<label for="apiKey">API Key:</label><br>
							<input id="apiKey" name="apiKey" type="text" placeholder="Insert API Key" value="<?php echo get_option( 'totallycriticalcss_api_key' ); ?>">
						</div>
						
						<div class="field custom-stylesheet">
							<label for="customStylesheet">Custom Stylesheet Location:</label><br>
							<input id="customStylesheet" name="customStylesheet" type="text" placeholder="Insert Custom Stylesheet Location" value="<?php echo get_option( 'totallycriticalcss_custom_stylesheet_location' ); ?>">
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="Save" />
						
					</div>
				</li>
				
				<li id="tab-stylesheets">
					<div class="content__wrapper">
						
						<div id="custom_dequeue" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'>Custom Stylesheets</span>
										</td>
									</th>
								</thead>
								<tbody>
									<tr class="seed">
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<label>
												<span class='handle'>( <?php echo $handle; ?> )</span>
												<span class="url"><?php echo $url; ?></span>
											</label>
										</td>
										<td class="actions">
											<button class="button button-delete custum-dequeue-delete" data-handle="<?php echo $handle; ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php foreach ( $totallycriticalcss_custom_dequeue as $handle => $url ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<label>
												<span class='handle'>( <?php echo $handle; ?> )</span>
												<span class="url"><?php echo $url; ?></span>
											</label>
										</td>
										<td class="actions">
											<button class="button button-delete custum-dequeue-delete" data-handle="<?php echo $handle; ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<div class="adder-form">
								<label for="">Handle</label>
								<input id="add-form-handle" type="text" />
								<label for="">URL</label>
								<input id="add-form-url" type="text" />
								<button id="add-form-custum-dequeue" class="button button-primary">Add</button>
								<button class="button adder-form-cancel">Cancel</button>
							</div>
							
							<button class="button button-primary adder-form-show">Add Custom Stylesheet</button>
							
						</div>
						
						<div id="stylesheet_dequeue">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'>Enqueued Stylesheets</span>
										</td>
									</th>
								</thead>
								<tbody>
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
											<?php echo isset( $selected_stylesheet_dequeue[$sheetid_clean] ) ? 'checked="checked"' : ''; ?> />
										</td>
										<td>
											<label for="<?php echo $sheetid_clean; ?>">
												<span class='handle'>( <?php echo $sheetid_clean; ?> )</span>
												<span class="url"><?php echo $sheet->getAttribute('href'); ?></span>
											</label>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="Save" />
							<button id="styles-toggle-all" class="button button-secondary">Toggle All</button>
							
						</div>
						
					</div>
				</li>
				
				<li id="tab-cpt">
					<div class="content__wrapper">
						
						<div id="selected_cpt" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'>Post Types Enabled</span>
										</td>
									</th>
								</thead>
								<tbody>
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
								</tbody>
							</table>
							
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="Save" />
						
					</div>
				</li>
				
				<li id="tab-routes">
					<div class="content__wrapper">
						
						<div id="custom_routes" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
						
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'>Custom Routes</span>
										</td>
									</th>
								</thead>
								<tbody>
									<tr class="seed">
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<span class='route'></span>
										</td>
										<td class="actions">
											<button class="button button-delete custom-route-delete" data-url="">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php foreach ( $totallycriticalcss_custom_routes as $url ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<span class='route'><?php echo $url; ?></span>
										</td>
										<td class="actions">
											<button class="button button-delete custom-route-delete" data-url="<?php echo $url; ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<div class="adder-form">
								<label for="add-route-url">URL</label>
								<input id="add-route-url" type="text" />
								<button id="add-form-custum-route" class="button button-primary">Add</button>
								<button class="button adder-form-cancel">Cancel</button>
							</div>
							
							<button class="button button-primary adder-form-show">Add Custom Route</button>
						
						</div>
						
					</div>
				</li>
				
			</ul>
		</section>

	</form>
</div>
