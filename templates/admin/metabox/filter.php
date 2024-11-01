<?php
/**
 * Product filter metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$current_user_id = get_current_user_id();

$style_default   = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
$tones_array     = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
$audiences_array = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );

$tones = '';
if ( ! empty( $tones_array ) ) {
	$tones = implode( ',', $tones_array );
}

$audiences = '';
if ( ! empty( $audiences_array ) ) {
	$audiences = implode( ',', $audiences_array );
}

// Default settings values.
echo '<input type="hidden" id="wtai-settting-style" value="' . esc_attr( wp_unslash( $style_default ) ) . '" />';
echo '<input type="hidden" id="wtai-settting-tones" value="' . esc_attr( wp_unslash( $tones ) ) . '" />';
echo '<input type="hidden" id="wtai-settting-audiences" value="' . esc_attr( wp_unslash( $audiences ) ) . '" />';
?>
<div class="wtai-wp-filter wtai-filter-main-wrap">
	<div class="wtai-postbox-process wtai-flex-item">
		<div class="wtai-postbox-process-left">
			<div class="wtai-postbox-process-content-left" >
				<div class="wtai-step-4-container-wrap" >
					<span class="wtai-step-guideline wtai-step-guideline-4" ><?php echo wp_kses_post( __( 'Step 4', 'writetext-ai' ) ); ?></span>
				</div>

				<div class="wtai-postbox-process-style-tone-wrapper" >
					<div class="wtai-tone-and-styles-wrapper wtai-postbox-process-wrapper wtai-tone-and-style-form-wrapper">
						<div class="wtai-tone-and-styles-label wtai-tone-and-styles-label-1">
							<?php echo wp_kses_post( __( 'Tones & Style', 'writetext-ai' ) ); ?>
						</div>
						<div class="wtai-tone-and-styles-select wtai-tone-and-style-form-select">
							<span class="wtai-button-label wtai-tone-style-filter-label" data-type="tone_and_style" >
								<span class="wtai-button-num"><?php echo wp_kses_post( $style_and_tones_count ); ?></span>&nbsp;<?php echo wp_kses_post( __( 'Selected', 'writetext-ai' ) ); ?>
							</span>
							<?php echo wp_kses( $style_and_tones_list, wtai_kses_allowed_html() ); ?>
							<div class="wtai-tooltip">
								<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<?php
									// TODO: Update PO files.
									echo '<p>' . wp_kses_post( __( 'Select one or more tones to set the overall mood and attitude of the text, and then choose a style to set the voice and structure of the text.', 'writetext-ai' ) ) . '</p>';

									if ( wtai_is_formal_informal_lang_supported() ) {
										echo '<p>' . wp_kses_post( __( 'If you\'re aiming to write formally, you can check the "Highlight potentially incorrect pronouns" box to see informal pronouns in the text.', 'writetext-ai' ) ) . '</p>';
									}
									?>
								</div>
							</div>
						</div>
					</div>

					<div class="wtai-tone-and-styles-wrapper wtai-postbox-process-wrapper wtai-audiences-form-wrapper">
						<div class="wtai-tone-and-styles-label wtai-tone-and-styles-label-2">
							<?php echo wp_kses_post( __( 'Audience', 'writetext-ai' ) ); ?>
						</div>
						<div class="wtai-tone-and-styles-select wtai-audiences-form-select">
							<span class="wtai-button-label wtai-audience-filter-label"  data-type="audiences" class="wtai-audiences-dropdown" >
								<span class="wtai-button-num"><?php echo wp_kses_post( $audience_cont ); ?></span>&nbsp;<?php echo wp_kses_post( __( 'Selected', 'writetext-ai' ) ); ?>
							</span>
							<?php echo wp_kses( $audience_list, wtai_kses_allowed_html() ); ?>
							<div class="wtai-tooltip">
								<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<p>
									<?php
									// TODO: Update PO files.
									echo wp_kses_post( __( 'Select the applicable audience for your product so WriteText.ai can generate text that will appeal to them.  If you don’t select an audience, the generated text will default to a “neutral” audience. WriteText.ai also suggests more specific target markets based on your keywords. Click on the audience to select it or type in your own custom audience in the box.', 'writetext-ai' ) );
									?>
									</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		</div>
		<div class="wtai-postbox-process-mid">
			<div class="wtai-or-label"><?php echo wp_kses_post( __( 'OR', 'writetext-ai' ) ); ?></div>
		</div>
		<div class="wtai-postbox-process-right">
			<div class="wtai-reference-product-postbox-process-wrapper wtai-postbox-process-wrapper wtai-ref-product-form-postbox-wrapper wtai-reference-product-filter">
				<label class="wtai-reference-product-label-wrapper <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>" for="wtai-custom-style-ref-prod">
					<span class="wtai-ref-cb-sel-group" >
						<input type="checkbox" name="wtai_custom_style_ref-_prod" class="wtai-custom-style-ref-prod" data-type="custom-style-refprod" id="wtai-custom-style-ref-prod" value="wtaRefprod" />
						<span class="wtai-ref-product-span-wrap" >
							<span><?php echo wp_kses_post( __( 'Use another product as reference', 'writetext-ai' ) ); ?></span>
						</span>
					</span>
					<?php do_action( 'wtai_product_single_premium_badge', 'wtai-premium-reference' ); ?>
				</label>
				<div class="wtai-select-wrapper wtai-reference-product-wrapper">
					<div class="wtai-reference-product-wtai-select-wrapper <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>" >
						<select class="wtai-custom-style-ref-prod-sel" name="wtai_custom_style_ref_prod_sel" data-type="style" placeholder="<?php echo wp_kses_post( __( 'Select or enter product name', 'writetext-ai' ) ); ?>">
						</select>
					</div>
					<div class="wtai-tooltip">
						<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext">
							<div class="wtai-tooltip-arrow"></div>
							<?php
							echo '<p>' . wp_kses_post( __( 'If you have already edited a certain product’s texts to fit the specific style and structure that you want, you can set it as a “Reference product” which WriteText.ai will use as the basis for generating text. When using a reference product, the individual settings for tone, style, and audience will be ignored. Note that image alt text generation will not be affected by the reference product.', 'writetext-ai' ) ) . '</p>
							<p>' . wp_kses_post( __( 'Note: The texts that will be used as reference are the ones currently in WordPress. Only products with all text types saved in WordPress will be available for selection in this list.', 'writetext-ai' ) ) . '</p>' .
							'<p>' . wp_kses_post( __( 'The credit cost for generating text using a reference product will depend on the length of the reference text and any formatting (e.g., bold, italics, numbered or bulleted lists) applied to it. For a more detailed explanation, you may read our FAQ section on <a href="https://writetext.ai/frequently-asked-questions#credit-cost" target="_blank" >how your credit cost is calculated</a>.', 'writetext-ai' ) ) . '</p>';
							?>
						</div>
					</div>
				</div>
			</div>
		</div>		
	</div>
</div>

<div class="wtai-filter-main-actions" >
	<div class="wtai-collapse-expand-wrapper" >
		<ul class="subsubsub wtai-flex-item">
			<li class="wtai-filter-cb-publish">
				<label for="wtai-checkboxes-all">
					<input type="checkbox" id="wtai-checkboxes-all" class="wtai-checkboxes-all disabled wtai-init-fields" disabled /> 
					<a href="#" class="wtai-select-all-checkbox  disabled wtai-init-fields"><?php echo wp_kses_post( __( 'Select all', 'writetext-ai' ) ); ?> </a> |
				</label>
			</li>
			<li class="wtai-filter-cb-byorder">
				<a href="#"  class="wtai-select-all-checkbox-expand"><?php echo wp_kses_post( __( 'Collapse', 'writetext-ai' ) ); ?>/<?php echo wp_kses_post( __( 'Expand all', 'writetext-ai' ) ); ?></a>
			</li>
		</ul>
	</div>
	
	<div class="wtai-generate-wrapper wtai-postbox-process-wrapper wtai-flex-item">
		<?php
		$generate_type_selected = 'generate';
		?>

		<div class="wtai-generate-cta-radio-wrap" >
			<div class="wtai-step-5-container-wrap" >
				<span class="wtai-step-guideline wtai-step-guideline-5" ><?php echo wp_kses_post( __( 'Step 5', 'writetext-ai' ) ); ?></span>
			</div>
			<div class="wtai-cta-radio-container-wrap" >
				<div class="wtai-cta-radio-option-wrap" >
					<label class="wtai-cta-radio-label" >
						<input type="radio" <?php echo checked( $generate_type_selected, 'generate' ); ?> id="wtai-cta-generate-type-generate" name="wtai_cta_generate_type" value="generate" class="wtai-cta-radio wtai-cta-generate-type" /> <?php echo wp_kses_post( __( 'Generate new text', 'writetext-ai' ) ); ?>
					</label>

					<div class="wtai-generating-cta-overlay wtai-generating-cta-overlay-generate" title="<?php echo wp_kses_post( __( 'Generation ongoing', 'writetext-ai' ) ); ?>" ></div>
				</div>
				<div class="wtai-cta-radio-option-wrap" >
					<label class="wtai-cta-radio-label-rewrite wtai-cta-radio-label wtai-cta-radio-option-label <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature wtai-disable-premium-feature-beige'; ?>" >
						<input type="radio" <?php echo checked( $generate_type_selected, 'rewrite' ); ?> id="wtai-cta-generate-type-rewrite" name="wtai_cta_generate_type" value="rewrite" class="wtai-cta-radio wtai-cta-generate-type" /> <?php echo wp_kses_post( __( 'Rewrite existing', 'writetext-ai' ) ); ?>

						<?php do_action( 'wtai_product_single_premium_badge', 'wtai-premium-rewrite' ); ?>
					</label>

					<div class="wtai-generating-cta-overlay wtai-generating-cta-overlay-rewrite" title="<?php echo wp_kses_post( __( 'Generation ongoing', 'writetext-ai' ) ); ?>" ></div>
				</div>				
			</div>
		</div>
		<div class="wtai-generate-cta-wrap" >
			<div class="wtai-toggle-wrapper">
				<a type="button" class="button button-primary wtai-page-generate-all disabled" data-rewrite="0" ><?php echo '<span class="wtai-cta-type-label" >' . wp_kses_post( __( 'Generate selected', 'writetext-ai' ) ) . '</span><span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">0</span> <span class="wtai-cred-label" >' . wp_kses_post( __( 'credits', 'writetext-ai' ) ) . '</span>)'; ?></span></a>
			</div>
		
			<div class="wtai-tooltip wtai-d-flex wtai-generate-tooltip">
				<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
				<div class="wtai-tooltiptext">
					<div class="wtai-tooltip-arrow"></div>
					<?php
					echo '<p class="wtai-heading">' . wp_kses_post( __( 'Generate new text', 'writetext-ai' ) ) . '</p>
					<p>' . wp_kses_post( __( 'Generate text for your selected text types based on the settings on this page. ', 'writetext-ai' ) ) . '</p>
					<p class="wtai-heading">' . wp_kses_post( __( 'Rewrite existing', 'writetext-ai' ) ) . '</p>
					<p>' . wp_kses_post( __( 'If any of your settings (e.g., keywords, product attributes, tone, style, and audiences) have changed but you don’t necessarily want to generate completely new text, you can use the Rewrite feature to keep the existing structure of the text but rewrite it to reflect any changes. Note that for the image alt text, only the product name and keywords are considered in the rewrite.', 'writetext-ai' ) ) . '</p>
					<p>' . wp_kses_post( __( 'Note: Any media or shortcode you have inserted into the existing text will not be included in the rewritten version. The language of the text you are rewriting will be the same language used in the output.', 'writetext-ai' ) ) . '</p>' .
					'<p>' . wp_kses_post( __( 'The credit cost for rewriting text will depend on the length of the original text and any formatting (e.g., bold, italics, numbered or bulleted lists) applied to it. For a more detailed explanation, you may read our FAQ section on <a href="https://writetext.ai/frequently-asked-questions#credit-cost" target="_blank" >how your credit cost is calculated</a>.', 'writetext-ai' ) ) . '</p>';
					?>
				</div>
			</div>
		</div>
	</div>
</div>