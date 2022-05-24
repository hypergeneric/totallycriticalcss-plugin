<?php

// pull the options
$totallycriticalcss_api_key          = tccss()->options()->get( 'api_key' );
$totallycriticalcss_viewport_width   = tccss()->options()->get( 'viewport_width', 1400 );
$totallycriticalcss_viewport_height  = tccss()->options()->get( 'viewport_height', 1080 );
$totallycriticalcss_simplemode       = tccss()->options()->get( 'simplemode' );
$totallycriticalcss_show_metaboxes   = tccss()->options()->get( 'show_metaboxes' );
$totallycriticalcss_adminmode        = tccss()->options()->get( 'adminmode' );
$totallycriticalcss_selected_cpt     = tccss()->options()->get( 'selected_cpt', [] );
$totallycriticalcss_custom_dequeue   = tccss()->options()->get( 'custom_dequeue', [] );
$totallycriticalcss_custom_routes    = tccss()->options()->get( 'custom_routes', [] );
$totallycriticalcss_ignore_routes    = tccss()->options()->get( 'ignore_routes', [] );
$totallycriticalcss_selected_styles  = tccss()->options()->get( 'selected_styles', [] );

// get a list of custom post types
$my_post_types = get_post_types();
$sheetlist     = ! $totallycriticalcss_simplemode ? tccss()->sheetlist()->get_current() : [];

?>
<div id="admin-view">
	
	<div id="logo"><img src="<?php echo esc_url( TCCSS_PLUGIN_DIR . 'admin/images/logo.png' ); ?>"></div>
	
	<form id="admin-view-form" autocomplete="off">
		
		<section id="tccssWrapper">
			
			<ul class="tabs">
				<li data-tab="settings"><?php esc_html_e( 'Settings', 'tccss' ); ?></li>
				<li data-tab="ignore-routes"><?php esc_html_e( 'Ignore Routes', 'tccss' ); ?></li>
				<li class="<?php echo esc_attr( $totallycriticalcss_simplemode == true ? 'disabled' : '' ); ?>" data-tab="stylesheets"><?php esc_html_e( 'Stylesheets', 'tccss' ); ?></li>
				<li class="<?php echo esc_attr( $totallycriticalcss_simplemode == true ? 'disabled' : '' ); ?>" data-tab="cpt"><?php esc_html_e( 'Custom Post Types', 'tccss' ); ?></li>
				<li class="<?php echo esc_attr( $totallycriticalcss_simplemode == true ? 'disabled' : '' ); ?>" data-tab="routes"><?php esc_html_e( 'Routes', 'tccss' ); ?></li>
				<li data-tab="status"><?php esc_html_e( 'Status', 'tccss' ); ?></li>
			</ul>

			<ul class="tab__content">
				
				<li id="tab-settings">
					<div class="content__wrapper">
						
						<div class="field">
							<label for="api_key"><?php esc_html_e( 'API Key', 'tccss' ); ?></label><br>
							<input id="api_key" name="api_key" type="text" placeholder="<?php esc_attr_e( 'Insert API Key', 'tccss' ); ?>" value="<?php echo esc_attr( $totallycriticalcss_api_key ); ?>">
							<div class="desc">
								<?php printf(
									esc_html__( 'You\'ll need one of these for this to work.  Sign up at %1$s or if you already have an account, get the API key in your %2$s.', 'tccss' ),
									'<a href="https://totallycriticalcss.com/" target="_blank">https://totallycriticalcss.com/</a>',
									sprintf(
										'<a href="https://totallycriticalcss.com/my-account/" target="_blank">%1$s</a>',
										esc_html__( 'account', 'tccss' ),
									)
								); ?>
							</div>
						</div>
						
						<div class="field">
							<label for="viewport_width"><?php esc_html_e( 'Viewport Size', 'tccss' ); ?></label><br>
							<div class="viewport">
								<input id="viewport_width" name="viewport_width" type="number" placeholder="<?php esc_attr_e( 'In Pixels', 'tccss' ); ?>" value="<?php echo esc_attr( $totallycriticalcss_viewport_width ); ?>">
								<span>X</span>
								<input id="viewport_height" name="viewport_height" type="number" placeholder="<?php esc_attr_e( 'In Pixels', 'tccss' ); ?>" value="<?php echo esc_attr( $totallycriticalcss_viewport_height ); ?>">
							</div>
							<div class="desc">
								<?php esc_html_e( 'You can probably leave these alone.  You can change them if your desktop site width is greater than 1400 or if you want to include items lower than 1080 pixels down below the fold.', 'tccss' ); ?>
							</div>
						</div>
						
						<div class="checkbox">
							<div class="check">
								<input type="checkbox" 
									name="simplemode" id="simplemode" 
									value="<?php echo esc_attr( $totallycriticalcss_simplemode ? 'true' : 'false' ); ?>" 
									<?php if ( $totallycriticalcss_simplemode == true ) : ?>checked="checked"<?php endif; ?>
								/>
							</div>
							<div class="label">
								<label for="simplemode"><?php esc_html_e( 'Simple Mode', 'tccss' ); ?></label>
							</div>
							<div class="desc">
								<?php esc_html_e( 'Just set it and forget it.  Totally Critical CSS will work passively in the background on all pages and dynamic routes.  We\'ll automatically dequeue all styles to the page footer and process Critical CSS in the head.', 'tccss' ); ?>
								<br />
								<br />
								<?php esc_html_e( 'It all happens in the background seamlessly, using WP CRON and things gets queued up in our API as well.  Totally Critical CSS will mostly be invisible.', 'tccss' ); ?>
							</div>
						</div>
						
						<div class="checkbox">
							<div class="check">
								<input type="checkbox" 
									name="show_metaboxes" id="show_metaboxes" 
									value="<?php echo esc_attr( $totallycriticalcss_show_metaboxes ? 'true' : 'false' ); ?>" 
									<?php if ( $totallycriticalcss_show_metaboxes == true ) : ?>checked="checked"<?php endif; ?>
								/>
							</div>
							<div class="label">
								<label for="show_metaboxes"><?php esc_html_e( 'Show Meta Boxes', 'tccss' ); ?></label>
							</div>
							<div class="desc"><?php esc_html_e( 'Enable the Meta Box on the sidebar for Posts & Pages ( or any other CPT in advanced Mode )', 'tccss' ); ?></div>
						</div>
						
						<div class="checkbox">
							<div class="check">
								<input type="checkbox" 
									name="adminmode" id="adminmode" 
									value="<?php echo esc_attr( $totallycriticalcss_adminmode ? 'true' : 'false' ); ?>" 
									<?php if ( $totallycriticalcss_adminmode == true ) : ?>checked="checked"<?php endif; ?>
								/>
							</div>
							<div class="label">
								<label for="adminmode"><?php esc_html_e( 'Admin Mode', 'tccss' ); ?></label>
							</div>
							<div class="desc"><?php esc_html_e( 'This will have the effect of ignoring the Critical CSS insertion, but leaving comments in the HTML Source to give you debug information.', 'tccss' ); ?></div>
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'tccss' ); ?>" />
						
					</div>
				</li>
				
				<li id="tab-ignore-routes">
					<div class="content__wrapper">
						
						<div id="ignore_routes" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
						
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'><?php esc_html_e( 'Ignore Routes', 'tccss' ); ?></span>
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
											<button class="button button-delete ignore-route-delete" data-url="">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php foreach ( $totallycriticalcss_ignore_routes as $url ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												disabled="disabled"
												checked="checked" />
										</td>
										<td>
											<span class='route'><?php echo esc_html( $url ); ?></span>
										</td>
										<td class="actions">
											<button class="button button-delete ignore-route-delete" data-url="<?php echo esc_attr( $url ); ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<div class="adder-form">
								<label for="add-ignore-url"><?php esc_html_e( 'URL', 'tccss' ); ?></label>
								<input id="add-ignore-url" type="text" />
								<button id="add-form-ignore-route" class="button button-primary"><?php esc_html_e( 'Add', 'tccss' ); ?></button>
								<button class="button adder-form-cancel"><?php esc_html_e( 'Cancel', 'tccss' ); ?></button>
							</div>
							
							<button class="button button-primary adder-form-show"><?php esc_html_e( 'Add Ignore Route', 'tccss' ); ?></button>
						
						</div>
						
					</div>
				</li>
				
				<?php if ( $totallycriticalcss_simplemode == false ) { ?>
				
				<li id="tab-stylesheets">
					<div class="content__wrapper">
						
						<div id="custom_dequeue" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'><?php esc_html_e( 'Custom Stylesheets', 'tccss' ); ?></span>
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
												<span class='handle'></span>
												<span class="url"></span>
											</label>
										</td>
										<td class="actions">
											<button class="button button-delete custum-dequeue-delete" data-handle="">
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
												<span class='handle'>( <?php echo esc_attr( $handle ); ?> )</span>
												<span class="url"><?php echo esc_attr( $url ); ?></span>
											</label>
										</td>
										<td class="actions">
											<button class="button button-delete custum-dequeue-delete" data-handle="<?php echo esc_attr( $handle ); ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<div class="adder-form">
								<label for=""><?php esc_html_e( 'Handle', 'tccss' ); ?></label>
								<input id="add-form-handle" type="text" />
								<label for=""><?php esc_html_e( 'URL', 'tccss' ); ?></label>
								<input id="add-form-url" type="text" />
								<button id="add-form-custum-dequeue" class="button button-primary"><?php esc_html_e( 'Add', 'tccss' ); ?></button>
								<button class="button adder-form-cancel"><?php esc_html_e( 'Cancel', 'tccss' ); ?></button>
							</div>
							
							<button class="button button-primary adder-form-show"><?php esc_html_e( 'Add Custom Stylesheet', 'tccss' ); ?></button>
							
						</div>
						
						<div id="stylesheet_dequeue">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
							
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'><?php esc_html_e( 'Enqueued Stylesheets', 'tccss' ); ?></span>
										</td>
									</th>
								</thead>
								<tbody>
								<?php foreach ( $sheetlist as $handle => $url ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												name="sheets" id="<?php echo esc_attr( $handle ); ?>" 
												value="<?php echo esc_attr( $handle ); ?>" 
												data-url="<?php echo esc_attr( $url ); ?>" 
												<?php if ( isset( $totallycriticalcss_selected_styles[$handle] ) ) : ?>checked="checked"<?php endif; ?>
											/>
										</td>
										<td>
											<label for="<?php echo esc_attr( $handle ); ?>">
												<span class='handle'>( <?php echo esc_attr( $handle ); ?> )</span>
												<span class="url"><?php echo esc_attr( $url ); ?></span>
											</label>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'tccss' ); ?>" />
							<button id="styles-toggle-all" class="button button-secondary"><?php esc_html_e( 'Toggle All', 'tccss' ); ?></button>
							
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
											<span class='handle'><?php esc_html_e( 'Post Types Enabled', 'tccss' ); ?></span>
										</td>
									</th>
								</thead>
								<tbody>
								<?php foreach ( $my_post_types as $my_post_type ) { ?>
									<tr>
										<td class="check">
											<input type="checkbox" 
												name="selected_cpt" 
												id="<?php echo esc_attr( $my_post_type ); ?>" 
												value="<?php echo esc_attr( $my_post_type ); ?>" 
												<?php if ( in_array( $my_post_type, $totallycriticalcss_selected_cpt ) ) : ?>checked="checked"<?php endif; ?>
											/>
										</td>
										<td>
											<label for="<?php echo esc_attr( $my_post_type ); ?>">
												<span class='handle'><?php echo esc_attr( get_post_type_object( $my_post_type )->labels->singular_name ); ?></span>
												<span class="url">( <?php echo esc_attr( $my_post_type ); ?> )</span>
											</label>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
						</div>
						
						<input id="submitForm" class="button button-primary" name="submitForm" type="submit" value="<?php esc_attr_e( 'Save', 'tccss' ); ?>" />
						
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
											<span class='handle'><?php esc_html_e( 'Custom Routes', 'tccss' ); ?></span>
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
											<span class='route'><?php echo esc_attr( $url ); ?></span>
										</td>
										<td class="actions">
											<button class="button button-delete custom-route-delete" data-url="<?php echo esc_attr( $url ); ?>">
												<span class="dashicons dashicons-trash"></span>
											</button>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
							
							<div class="adder-form">
								<label for="add-custom-url"><?php esc_html_e( 'URL', 'tccss' ); ?></label>
								<input id="add-custom-url" type="text" />
								<button id="add-form-custum-route" class="button button-primary"><?php esc_html_e( 'Add', 'tccss' ); ?></button>
								<button class="button adder-form-cancel"><?php esc_html_e( 'Cancel', 'tccss' ); ?></button>
							</div>
							
							<button class="button button-primary adder-form-show"><?php esc_html_e( 'Add Custom Route', 'tccss' ); ?></button>
						
						</div>
						
					</div>
				</li>
				
				<?php } ?>
				
				<li id="tab-status">
					<div class="content__wrapper">
						
						<div id="status" class="ajax-group">
							
							<div class="screen" style="background-image: url( <?php echo esc_url( get_admin_url() . 'images/loading.gif' ); ?> );"></div>
						
							<table>
								<thead>
									<th>
										<td>
											<span class='handle'><?php esc_html_e( 'Route', 'tccss' ); ?></span>
										</td>
									</th>
								</thead>
								<tbody>
									<tr class="seed">
										<td class="icon">
											<span class='state'></span>
										</td>
										<td>
											<span class='route'></span>
										</td>
										<td class="actions">
											<button class="button button-delete status-invalidate" data-type="" data-route="">
												<span class="dashicons dashicons-update"></span>
											</button>
										</td>
									</tr>
								</tbody>
							</table>
							
							<button class="button button-primary status-refresh"><?php esc_html_e( 'Refresh', 'tccss' ); ?></button>
						
						</div>
						
					</div>
				</li>
				
			</ul>
		</section>

	</form>
</div>
