<?php
/**
 * Install setup template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$is_token_expired = wtai_is_token_expired();

$get_current_step = intval( $get_current_step );

$current_user_id = get_current_user_id();

$installation_nonce             = wp_create_nonce( 'wtai-install-nonce' );
$popupblocker_nonce             = wp_create_nonce( 'wtai-popupblocker-nonce' );
$popup_blocker_notice_dismissed = wtai_get_popup_blocker_dismiss_state();

if ( $get_current_step > 4 && ! $is_token_expired ) {
	wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
	exit;
}
?>

<input type="hidden" id="wtai-install-nonce" value="<?php echo esc_attr( $installation_nonce ); ?>">

<div class="wtai-woocommerce-layout-header">
	<div class="wtai-woocommerce-layout-wtai-header-wrapper">
		<h1 data-wp-c16t="true" data-wp-component="Text" class="wtai-woocommerce-layout-header-heading"><?php echo wp_kses_post( __( 'WriteText.ai', 'writetext-ai' ) ); ?></h1>
	</div>
</div>
<div class="wrap wtai-installation-main-wrap wtai-cart-install-wrapper wtai-install-setup" >
	<div class="wtai-card-token-wrapper">
		<?php if ( ! $popup_blocker_notice_dismissed ) { ?>
			<input type="hidden" id="wtai-popupblocker-nonce" value="<?php echo esc_attr( $popupblocker_nonce ); ?>" />
			<div id="wtai-popup-blocker-notice" class="updated error notice wtai-popup-blocker-notice is-dismissible" style="margin-bottom: 20px;text-align: left;" >
				<p><?php echo wp_kses_post( __( '<strong>Warning:</strong> Disable all pop-up blockers then refresh this page. WriteText.ai does not work when you have pop-up blockers enabled.', 'writetext-ai' ) ); ?></p>
			</div>
		<?php } ?>
		
		<?php
		$is_allowed = false;
		if ( is_super_admin() || current_user_can( 'activate_plugins' ) ) {
			$is_allowed = true;
		}

		if ( 1 === intval( get_option( 'wtai_installation_source_updated' ) ) ) {
			$message_seo_updated = __( 'You have been redirected here because we detected a change in the SEO plugin that you use. You will need to set an SEO plugin so WriteText.ai can have a place to transfer your SEO text to. Don\'t worry, all your content are saved and you will be redirected back to your dashboard after setting this up.', 'writetext-ai' );
			if ( ! $is_allowed ) {
				$message_seo_updated = __( "We have detected a change in the SEO plugin used in your site. An administrator needs to set this up before you can continue using WriteText.ai. Don't worry, all your content is saved and you will be redirected back to your dashboard after your administrator has set this up.", 'writetext-ai' );
			}
			?>
			<div id="wtai-seo-need-update-message" class="updated error notice" style="margin-bottom: 20px;text-align: left;" >
				<p><?php echo wp_kses_post( $message_seo_updated ); ?></p>
			</div>
			<?php

			if ( ! $is_allowed ) {
				return;
			}
		}

		if ( ! $is_allowed ) {
			?>
			<div id="wtai-seo-change-message" class="updated error notice is-dismissible">
				<p><?php echo wp_kses( __( "We have detected a change in the SEO plugin used in your site. An administrator needs to set this up before you can continue using WriteText.ai. Don't worry, all your content is saved and you will be redirected back to your dashboard after your administrator has set this up.", 'writetext-ai' ), 'post' ); ?></p>
			</div>
			<?php
			return;
		}

		if ( $is_token_expired ) {
			$get_current_step = 1;
			?>
		<div class="wtai-token-expired-notice notice notice-error"><?php echo wp_kses_post( __( 'Your token is expired. Please log in again to extend your token.', 'writetext-ai' ) ); ?></div>
		<?php } ?>

		<?php
		// Check old installations if region is setup.
		if ( ! wtai_has_api_base_url() && wtai_is_token_active() ) {
			$get_current_step = 1;
			?>
		<div class="wtai-token-expired-notice notice notice-error">
			<?php echo wp_kses_post( __( 'Please log in again to set up your API region correctly in order to fetch data faster.', 'writetext-ai' ) ); ?>
		</div>
		<?php } ?>

		<?php
		require WTAI_ABSPATH . 'templates/admin/translation-ongoing.php';
		?>
	</div>
   
	<div class="wtai-card-title-wrapper">
		<div class="wtai-site-title"><img src="<?php echo esc_url( WTAI_DIR_URL . 'assets/images/writetext_logo.png' ); ?>"></h1>
		<div class="wtai-plugin-setup-guide"><?php echo wp_kses_post( __( 'Plugin setup guide', 'writetext-ai' ) ); ?></div>
	</div>
	
	<div class="wtai-card-container-wrapper">
			<div class="wtai-card-wrapper wtai-step-1-wrapper <?php echo 1 === $get_current_step ? 'wtai-active' : ''; ?> <?php echo $get_current_step > 1 ? 'wtai-completed' : ''; ?>">
				<div class="wtai-card-step-number-wrapper">
					<span class="wtai-step-number">1</span>
				</div>
				<div class="wtai-card wtai-card-details-wrapper">
					<div class="wtai-step"><?php echo wp_kses_post( __( 'Step 1', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Connect to your WriteText.ai account', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-completed"><?php echo wp_kses_post( __( 'Completed', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
						<p><?php echo wp_kses_post( __( 'Log in or register on our backend in order to link this site to your WriteText.ai account. This is where you can manage billing, see reports, and check or purchase credits. If you have more than one ecommerce site, you can link it to the same WriteText.ai account.', 'writetext-ai' ) ); ?></p>
						<?php if ( ! $domain_validate || $is_token_expired || ! wtai_has_api_base_url() ) : ?>
							<div class="wtai-validate-url-wrapper">
								<div class="wtai-validate-domain">
									<a href="<?php echo esc_url( get_site_url() ); ?>" target="_blank" ><?php echo wp_kses_post( str_replace( array( 'http://', 'https://' ), '', get_site_url() ) ); ?></a>
								</div>
								<div class="wtai-validate-domain-button">
									<?php
									$login_callback_url = wtai_get_login_callback_url();
									?>
									<a class="wtai-verify-button" href="<?php echo esc_url( $login_callback_url ); ?>"><?php echo wp_kses_post( __( 'Log in / Sign up', 'writetext-ai' ) ); ?></a>
								</div>
							</div>
						<?php endif; ?>
					 
					</div>
				</div>
			</div>

			<!-- Freemium badge -->
			<?php if ( wtai_display_freemium_setup_badge() && 1 !== $get_current_step ) { ?>
				<div class="wtai-card-wrapper wtai-step-freemium-wrapper" >
					<div class="wtai-card-step-number-wrapper">
					</div>
					<div class="wtai-card wtai-card-details-wrapper">
						<?php do_action( 'wtai_freemium_badge' ); ?>
					</div>
				</div>
			<?php } ?>
			<!-- End freemium badge -->

			<div class="wtai-card-wrapper wtai-step-2-wrapper  <?php echo 2 === $get_current_step ? 'wtai-active' : ''; ?> <?php echo $get_current_step > 2 ? 'wtai-completed' : ''; ?>">
				<div class="wtai-card-step-number-wrapper">
					<span class="wtai-step-number">2</span>
				</div>
				<div class="wtai-card wtai-card-details-wrapper">
					<div class="wtai-step"><?php echo wp_kses_post( __( 'Step 2', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Check SEO plugin', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-completed"><?php echo wp_kses_post( __( 'Completed', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
						<?php
						if ( 'no_active_no_install' === $seo_lists['status'] ) {
							?>
							<p><?php echo wp_kses_post( __( 'You currently do not have an SEO plugin installed.', 'writetext-ai' ) ); ?></p>
							<p><?php echo wp_kses_post( __( 'In addition to writing product descriptions, WriteText.ai also automatically generates meta titles, meta descriptions, and Open Graph texts for you. However, WordPress does not have built-in fields for updating these SEO tags, so you first need to install a compatible SEO plugin in order for WriteText.ai to have a place in WordPress to transfer these texts to.', 'writetext-ai' ) ); ?></p>
							<p><?php echo wp_kses_post( __( 'Select your preferred SEO plugin:', 'writetext-ai' ) ); ?></p>
							<?php
						} elseif ( 'no_active_single_install' === $seo_lists['status'] ) {
							?>
							<p><?php echo wp_kses_post( __( 'We have detected that you currently have an SEO plugin installed but not activated in your WordPress site. You will need to activate this plugin first in order for Writetext.ai to have a place to transfer the SEO text that it will generate for you. If you don’t want to use this SEO plugin, you can also select other SEO plugins that WriteText.ai is compatible with in the list below and we will install and activate it for you.', 'writetext-ai' ) ); ?></p>
							<p style="font-weight:600;"><?php echo wp_kses_post( __( 'SEO plugin(s) detected', 'writetext-ai' ) ); ?>:</p>
							<?php
						} elseif ( 'no_active_multi_install' === $seo_lists['status'] ) {
							?>
							<p><?php echo wp_kses_post( __( 'We have detected that you currently have SEO plugins installed but not activated in your WordPress site. You will need to activate one plugin in order for WriteText.ai to have a place to transfer the SEO text that it will generate for you. <strong>Note</strong>: Activate only ONE of these plugins to avoid conflict and duplicates in your SEO tags.', 'writetext-ai' ) ); ?></p>
							<p style="font-weight:600;"><?php echo wp_kses_post( __( 'Choose a different SEO plugin', 'writetext-ai' ) ); ?>:</p>
							<?php
						} elseif ( 'multi_active' === $seo_lists['status'] ) {
							?>
							<p><?php echo wp_kses_post( __( 'We have detected multiple SEO plugins installed and activated in your WordPress site. You will need to manually deactivate any SEO plugin(s) you are not using and leave your preferred plugin activated. This will be where WriteText.ai will transfer the SEO text that it will generate for you.<br /><br /><strong>Note:</strong> This is to avoid conflict and duplicates in your SEO tags. Make sure to double-check before deactivating in order to avoid losing your data.<br><br>Once you\'re done, please refresh this page.', 'writetext-ai' ) ); ?></p>
							<p style="font-weight:600;"><?php echo wp_kses_post( __( 'SEO plugin(s) detected', 'writetext-ai' ) ); ?>:</p>
							<?php
						} else {
							?>
							<p><?php echo wp_kses_post( __( 'Great! You already have a compatible SEO plugin installed and activated. This is where WriteText.ai will transfer the SEO texts (meta title, meta description, Open Graph text) that it generates.', 'writetext-ai' ) ); ?></p>
							<?php
						}
						?>
						   
							<ul class="wtai-seo-lists">
								<?php
								$ctr          = 1;
								$button_label = '';
								foreach ( $seo_lists['results'] as $seo_list_key => $seo_list_label ) :
									if ( 'multi_active_rankmath' === $seo_lists['status'] && ! in_array( $seo_list_key, array( 'seo-by-rank-math-pro', 'seo-by-rank-math' ), true ) ) {
										continue;
									}
									if ( 'multi_active_yoast' === $seo_lists['status'] && ! in_array( $seo_list_key, array( 'wordpress-seo-premium', 'wordpress-seo' ), true ) ) {
										continue;
									}
									$label = apply_filters( 'wtai_seo_plugin_status', array(), $seo_list_label['plugin_uri'] );
									if ( 1 === $ctr ) {
										$button_label = $label['label_suffix'];
									}
									?>
										<li>
										<label for="<?php echo esc_attr( $seo_list_key ); ?>">
										<?php
										if ( 'no_active_single_install' === $seo_lists['status'] ||
											( 'multi_active_rankmath' === $seo_lists['status'] && 'seo-by-rank-math-pro' === $seo_list_key ) ||
											( 'multi_active_yoast' === $seo_lists['status'] && 'wordpress-seo-premium' === $seo_list_key )
										) :
											?>
											<input class="wtai-seo-button-hidden-list" type="hidden" value="<?php echo esc_attr( wp_unslash( $seo_list_key ) ); ?>" />
										<?php endif; ?>
										<?php
										if ( in_array( $seo_lists['status'], array( 'no_active_no_install', 'no_active_single_install', 'no_active_multi_install' ), true ) ) :
											?>
											<input class="wtai-seo-button-radio-list" type="radio" name="wtai-seo-list" id="<?php echo esc_attr( $seo_list_key ); ?>" value="<?php echo esc_attr( wp_unslash( $seo_list_key ) ); ?>" <?php echo ( 1 === $ctr ) ? 'checked' : ''; ?> data-buttonlabel="<?php echo esc_attr( $label['label_suffix'] ); ?>"/><?php endif; ?><?php echo wp_kses_post( $seo_list_label['name'] ); ?></label><span class="button-status <?php echo esc_attr( $label['class'] ); ?>"><?php echo wp_kses_post( $label['label'] ); ?></span></li>
									<?php
									++$ctr;
									endforeach;
								?>
							</ul>
						<?php if ( 'multi_active' !== $seo_lists['status'] ) : ?> 
							<a href="#" class="wtai-next" data-step="2"><span><?php echo wp_kses_post( $button_label ); ?></span></a>
						<?php endif; ?> 
						<?php if ( 'multi_active' === $seo_lists['status'] ) : ?>  
							<div class="wtai-validate-domain-button wtai-multi-active">  
								<?php
								$plugin_url = site_url() . '/wp-admin/plugins.php';
								?>
								<a class="wtai-verify-button" href="<?php echo esc_url( $plugin_url ); ?>" target="_blank"><?php echo wp_kses_post( __( 'Manage plugins', 'writetext-ai' ) ); ?> <span class="dashicons dashicons-external"></span></a>
							</div>
						<?php endif; ?> 
					</div>
				</div>
			</div>
			<div class="wtai-card-wrapper wtai-step-3-wrapper  <?php echo 3 === $get_current_step ? 'wtai-active' : ''; ?> <?php echo $get_current_step > 3 ? 'wtai-completed' : ''; ?>">
				<div class="wtai-card-step-number-wrapper">
					<span class="wtai-step-number">3</span>
				</div>
				<div class="wtai-card wtai-card-details-wrapper">
					<div class="wtai-step"><?php echo wp_kses_post( __( 'Step 3', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Configure global settings', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-completed"><?php echo wp_kses_post( __( 'Completed', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
					<p><?php echo wp_kses_post( __( 'Almost done! Before you begin generating text, choose the default global settings for the plugin. You can always change these settings later in WriteText.ai > Settings.', 'writetext-ai' ) ); ?></p>
					
					<div class="wtai-field-form-wrap">
						<?php echo wp_kses( $tone_and_styles, wtai_kses_allowed_html() ); ?>
					</div>
					
					<div class="wtai-field-form-wrap wtai-product-attribute-wrapper">
						<label for="wtai-select-text-tone"><?php echo wp_kses_post( __( 'Product attributes', 'writetext-ai' ) ); ?></label>
						<?php echo wp_kses( $product_attributes, wtai_kses_allowed_html() ); ?>
					</div>
					<?php
							$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
							$min_output_words   = $global_rule_fields['minOutputWords'];
							$max_output_words   = $global_rule_fields['maxOutputWords'];
					?>
					<div class="wtai-field-form-wrap wtai-text-length wtai-product-description-text-length">
						<div class="wtai-button-text-length">
							<label><?php echo wp_kses_post( __( 'Target length - Product description', 'writetext-ai' ) ); ?></label>
							<span>
								<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" id="wtai-installation-product-description-min" name="wtai_installation_product_description_min" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( $field_text_fields['product_description_min'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>"  data-original-value="<?php echo esc_attr( $field_text_fields['product_description_min'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>"/>
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
							<span>
								<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" class="wtai-specs-input wtai-max-text" id="wtai-installation-product-description-max" name="wtai_installation_product_description_max"   value="<?php echo esc_attr( wp_unslash( $field_text_fields['product_description_max'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>"  data-original-value="<?php echo esc_attr( $field_text_fields['product_description_max'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>">
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
						</div>
					</div>
					<div class="wtai-field-form-wrap wtai-text-length product-excerpt-text-length">
						<div class="wtai-button-text-length">
							<label><?php echo wp_kses_post( __( 'Target length - Product short description', 'writetext-ai' ) ); ?></label>
							<span>
								<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number"  id="wtai-installation-product-excerpt-min" class="wtai-specs-input wtai-min-text" name="wtai_installation_product_excerpt_min" value="<?php echo esc_attr( wp_unslash( $field_text_fields['product_excerpt_min'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>" data-original-value="<?php echo esc_attr( $field_text_fields['product_excerpt_min'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
							<span>
								<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" class="wtai-specs-input wtai-max-text" id="wtai-installation-product-excerpt-max" name="wtai_installation_product_excerpt_max" value="<?php echo esc_attr( wp_unslash( $field_text_fields['product_excerpt_max'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>"  data-original-value="<?php echo esc_attr( $field_text_fields['product_excerpt_max'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
						</div>
					</div>
					<div class="wtai-field-form-wrap wtai-text-length wtai-category-description-text-length">
						<div class="wtai-button-text-length">
							<label><?php echo wp_kses_post( __( 'Target length - Category description', 'writetext-ai' ) ); ?></label>
							<span>
								<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number"  id="wtai-installation-category-description-min" class="wtai-specs-input wtai-min-text" name="wtai_installation_category_description_min" value="<?php echo esc_attr( wp_unslash( $field_text_fields['category_description_min'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>" data-original-value="<?php echo esc_attr( $field_text_fields['category_description_min'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
							<span>
								<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" class="wtai-specs-input wtai-max-text" id="wtai-installation-category-description-max" name="wtai_installation_category_description_max" value="<?php echo esc_attr( wp_unslash( $field_text_fields['category_description_max'] ) ); ?>" min="<?php echo esc_attr( $global_rule_fields['minOutputWords'] ); ?>"  data-original-value="<?php echo esc_attr( $field_text_fields['category_description_max'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
						</div>
					</div>
					<a href="#" class="wtai-next" data-step="3"><span><?php echo wp_kses_post( __( 'Finalize setup', 'writetext-ai' ) ); ?></span></a>

					</div>
				</div>
			</div>
			<div class="wtai-card-wrapper wtai-step-4-wrapper <?php echo 4 === $get_current_step ? 'wtai-active' : ''; ?> <?php echo $get_current_step > 4 ? 'wtai-completed' : ''; ?>">
				<div class="wtai-card-step-number-wrapper">
					<span class="wtai-step-number">4</span>
				</div>
				<div class="wtai-card wtai-card-details-wrapper">
				<div class="wtai-step"><?php echo wp_kses_post( __( 'Step 4', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'All done!', 'writetext-ai' ) ); ?> </div>
					<div class="wtai-step-completed"><?php echo wp_kses_post( __( 'Completed', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
						<p><?php echo wp_kses_post( __( 'You can now start generating text with WriteText.ai.', 'writetext-ai' ) ); ?></p>
						<a href="#" class="wtai-next wtai-finish" data-step="4"><span><?php echo wp_kses_post( __( 'Let\'s start', 'writetext-ai' ) ); ?></span></a>
					</div>
				</div>
			</div>
	</div>
</div>


