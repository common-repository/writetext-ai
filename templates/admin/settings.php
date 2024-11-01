<?php
/**
 * Settings template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_values_tones = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
$tones_orig_values    = is_array( $product_values_tones ) ? implode( ',', $product_values_tones ) : array();
$types_orig_values    = $tones_orig_values;

$product_values_styles = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );

if ( ! empty( $product_values_styles ) ) {
	$types_orig_values .= ',' . $product_values_styles;
}

$product_values_audiences = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
$audiences_orig_values    = is_array( $product_values_audiences ) ? implode( ',', $product_values_audiences ) : array();

if ( ! empty( $audiences_orig_values ) ) {
	$types_orig_values .= ',' . $audiences_orig_values;
}

$product_values_attributes = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
$attributes_orig_values    = is_array( $product_values_attributes ) ? implode( ',', $product_values_attributes ) : array();

if ( ! empty( $attributes_orig_values ) ) {
	$types_orig_values .= ',' . $attributes_orig_values;
}

$product_description_min = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' );
$product_description_max = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' );
if ( ! empty( $product_description_min ) ) {
	$types_orig_values .= ',' . $product_description_min;
}
if ( ! empty( $product_description_max ) ) {
	$types_orig_values .= ',' . $product_description_max;
}
$product_excerp_min = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' );
$product_excerp_max = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' );
if ( ! empty( $product_excerp_min ) ) {
	$types_orig_values .= ',' . $product_excerp_min;
}
if ( ! empty( $product_excerp_max ) ) {
	$types_orig_values .= ',' . $product_excerp_max;
}

$wtai_bulk_generate_ppopup = get_user_meta( get_current_user_id(), 'wtai_bulk_generate_popup', true );
if ( $wtai_bulk_generate_ppopup ) {
	$check              = 'checked="true"';
	$types_orig_values .= ',' . $wtai_bulk_generate_ppopup;
} else {
	$check = '';
}
?>

<input type="hidden" value="<?php echo esc_attr( wp_unslash( $types_orig_values ) ); ?>" name="wtai_types_orig_values" id="wtai-types-orig-values" />
<form method="post" novalidate="novalidate" id="wtai-form-settings" >
	<?php wp_nonce_field( 'wtai_settings', 'wtai_settings_wpnonce' ); ?>
	<div id="wtai-product-edit-cancel" class="wtai-loader-generate">
		<div class="wtai-loading-edit-cancel-container wtai-d-flex">
			<div class="wtai-loading-details-container">
				<div class="wtai-loading-wtai-header-wrapper">
					<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'You have unsaved changes. Are you sure you want to leave this page?', 'writetext-ai' ) ); ?></div>
				</div>
			</div>
			<div class="wtai-loading-actions-container wtai-d-flex">
				<span class="wtai-exit-edit-leave button button-primary"><?php echo wp_kses_post( __( 'Leave', 'writetext-ai' ) ); ?></span>&nbsp;<span class="button exit-edit-cancel"><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></span>
			</div>
		</div>
	</div>
	<div class="wrap wtai-cart-install-wrapper wtai-settings-setup" >
		<div class="wtai-card-title-wrapper">
			<div class="wtai-site-title"><img class="wtai-logo" width="200" src="<?php echo esc_url( WTAI_DIR_URL . 'assets/images/logo_writetext.svg' ); ?>" alt="logo"></h1>
			<div class="wtai-plugin-setup-guide"><?php echo wp_kses_post( __( 'Global settings', 'writetext-ai' ) ); ?></div>
		</div>
	   
		<div class="wtai-card-container-wrapper wtai-setting">
				<div class="wtai-card wtai-card-details-wrapper wtai-checkbox-list">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default tones', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content" data-origvalue="<?php echo esc_attr( $tones_orig_values ); ?>">
						<p><?php echo wp_kses_post( __( 'Select one or more tones to set the overall mood and attitude of the text. What you choose here will apply to all your products unless you choose different options for a specific product page. We recommend setting defaults in order to ensure a consistent voice throughout your entire website.', 'writetext-ai' ) ); ?></p>
						<?php echo wp_kses( $tones, wtai_kses_allowed_html() ); ?>
					</div>
				</div>
		   
				<div class="wtai-card wtai-card-details-wrapper wtai-checkbox-list">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default style', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content" data-origvalue="<?php echo esc_attr( $styles_orig_values ); ?>">
						<p><?php echo wp_kses_post( __( 'Choose a style to set the voice and structure of the text. What you choose here will apply to all your products unless you choose different options for a specific product page. We recommend setting a default in order to ensure a consistent voice throughout your entire website.', 'writetext-ai' ) ); ?></p>
					   
						<?php echo wp_kses( $styles, wtai_kses_allowed_html() ); ?>
					</div>
				</div>
			
				<div class="wtai-card wtai-card-details-wrapper wtai-checkbox-list">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default audiences', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content" data-origvalue="<?php echo esc_attr( $audiences_orig_values ); ?>">
						<p><?php echo wp_kses_post( __( 'Select one or more audiences for your product so WriteText.ai can generate text that will appeal to them. If you don’t select an audience, the generated text will default to a “neutral” audience. What you choose here will apply to all your products unless you choose different audiences for a specific product page.', 'writetext-ai' ) ); ?></p>
						<?php echo wp_kses( $audiences, wtai_kses_allowed_html() ); ?>
					</div>
				</div>
			
				<div class="wtai-card wtai-card-details-wrapper wtai-checkbox-list">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default product attributes', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content" data-origvalue="<?php echo esc_attr( $attributes_orig_values ); ?>">
						<p><?php echo wp_kses_post( __( 'Choose the default product attributes that will be considered in generating your product text. You can also select your main product image here and AI will analyze it in order to generate more accurate and relevant text. Please make sure that the image accurately represents the product (i.e., it is not some kind of placeholder or a generic image for your shop). Note that selecting a product attribute is not a guarantee that it will appear in the text itself, but it does influence how the text will be written.', 'writetext-ai' ) ); ?></p>
						<div class="wtai-attribute">
							<?php echo wp_kses( $attributes, wtai_kses_allowed_html() ); ?>
						</div>
					</div>
				</div>
		   
		</div>
		<?php
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
		$min_output_words   = $global_rule_fields['minOutputWords'];
		$max_output_words   = $global_rule_fields['maxOutputWords'];
		?>
		<div class="wtai-card-container-wrapper wtai-setting">
				<div class="wtai-card wtai-card-details-wrapper wtai-target-length">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default product description length', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
						<p><?php echo wp_kses_post( __( 'Indicate your target length by setting a minimum and maximum word count for your product descriptions. WriteText.ai will aim to generate text within the number you have set, but it may give you more words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' ) ); ?></p>
						<div class="wtai-button-text-length wtai-prod-desc">
							<label><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
							<span class="wtai-min">
								<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" id="wtai-installation-product-description-min" name="wtai_installation_product_description_min" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" >
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
							<span>
								<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" id="wtai-installation-product-description-max" name="wtai_installation_product_description_max"  class="wtai-specs-input wtai-max-text" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>">
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
						</div>
					</div>
				</div>
		  
				<div class="wtai-card wtai-card-details-wrapper wtai-target-length">
					<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default product short description length', 'writetext-ai' ) ); ?></div>
					<div class="wtai-content">
						<p><?php echo wp_kses_post( __( 'Indicate your target length by setting a minimum and maximum word count for your product short descriptions. WriteText.ai will aim to generate text within the number you have set, but it may give you more words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' ) ); ?></p>
						<div class="wtai-button-text-length prod_excerpt">
							<label><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
							<span class="wtai-min">
								<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" min="1" id="wtai-installation-product-excerpt-min" class="wtai-specs-input wtai-min-text" name="wtai_installation_product_excerpt_min" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>">
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
							<span>
								<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
								<span class="wtai-input-group">
									<input type="number" min="1" class="wtai-specs-input wtai-max-text" id="wtai-installation-product-excerpt-max" name="wtai_installation_product_excerpt_max" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>">
									<span class="wtai-plus-minus-wrapper">
										<span class="dashicons dashicons-plus wtai-txt-plus"></span>
										<span class="dashicons dashicons-minus wtai-txt-minus"></span>
									</span>
								</span>
							</span>
						</div>
					</div>
				</div>				
		</div>

		<div class="wtai-card-container-wrapper wtai-setting wtai-last">
			<div class="wtai-card wtai-card-details-wrapper wtai-target-length">
				<div class="wtai-step-title"><?php echo wp_kses_post( __( 'Set default category description length', 'writetext-ai' ) ); ?></div>
				<div class="wtai-content">
					<p><?php echo wp_kses_post( __( 'Indicate your target length by setting a minimum and maximum word count for your category descriptions. WriteText.ai will aim to generate text within the number you have set, but it may give you more words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' ) ); ?></p>
					<div class="wtai-button-text-length wtai-prod-desc">
						<label><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
						<span class="wtai-min">
							<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
							<span class="wtai-input-group">
								<input type="number" id="wtai-installation-category-description-min" name="wtai_installation_category_description_min" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_category_description_min' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_category_description_min' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" >
								<span class="wtai-plus-minus-wrapper">
									<span class="dashicons dashicons-plus wtai-txt-plus"></span>
									<span class="dashicons dashicons-minus wtai-txt-minus"></span>
								</span>
							</span>
						</span>
						<span>
							<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
							<span class="wtai-input-group">
								<input type="number" id="wtai-installation-category-description-max" name="wtai_installation_category_description_max"  class="wtai-specs-input wtai-max-text" value="<?php echo esc_attr( wp_unslash( apply_filters( 'wtai_global_settings', 'wtai_installation_category_description_max' ) ) ); ?>" data-original-value="<?php echo esc_attr( apply_filters( 'wtai_global_settings', 'wtai_installation_category_description_max' ) ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>">
								<span class="wtai-plus-minus-wrapper">
									<span class="dashicons dashicons-plus wtai-txt-plus"></span>
									<span class="dashicons dashicons-minus wtai-txt-minus"></span>
								</span>
							</span>
						</span>
					</div>
				</div>
			</div>
		</div>
	</div>


	<div class="wtai-button-set" style="text-align:right;">
		<div></div>
		<input type="submit" id="submit" class="button button-primary wtai-settings-btn-save" value="<?php echo wp_kses_post( __( 'Save Changes' ) ); ?>">
	</div>
   
	<?php
	do_action( 'wtai_country_selection_popup' );
	?>
		
</form>

<div class="wtai-footer-mobile-settings-wrap" >
	<?php do_action( 'wtai_admin_mobile_footer' ); ?>
</div>


