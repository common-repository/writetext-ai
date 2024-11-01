<?php
/**
 * Main dashboard template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="wtai-table-list-wrapper wrap wtai-main-wrapper wtai-table-list-product-wrapper">
	<div class="wtai-top-header wtai-dashboard">
		<div class="wtai-inner-flex">
				<span class="wtai-global-loader" style="display:none;"></span>

				<div class="wtai-product-list-dashboard-wrap" >     
					<?php if ( wtai_is_country_selection_hidden() ) { ?> 
						<span class="wtai-country-global"  >
							<span class="dashicons wtai-dashicons-country"></span>
							<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'Country', 'writetext-ai' ) ); ?></span>
						</span>          
					<?php } ?>     
					<span class="wtai-history-global" onclick="wtaiGetHistoryGlobalPopin(this)"  >
						<span class="dashicons wtai-dashicons-backup"></span>
						<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'History log', 'writetext-ai' ) ); ?></span>
					</span>
				</div>
				<button class="wtai-btn-close-history-global"><span class="dashicons dashicons-no-alt"></span></button>
		</div>
	</div>
	<div id="wtai-imaginary-div-topheader"></div>
	<div class="wtai-title-header wtai-page-title-header-wrap">
		<img class="wtai-logo" width="200" src="<?php echo esc_url( WTAI_DIR_URL . 'assets/images/logo_writetext.svg' ); ?>" alt="logo">
		<h1 class="wtai-page-title-header" ><?php echo wp_kses_post( __( 'Products', 'writetext-ai' ) ); ?></h1>
	</div>
	<?php

	$status_views_list = $wtai_product_list_table->get_views();
	$status_views      = array();
	foreach ( $status_views_list as $status_key => $status_value ) {
		$status_views[] = '<li class="' . $status_key . '">' . $status_value . '</li>';
	}
	?>
	<ul class="subsubsub wtai-status-view">
		<?php echo wp_kses_post( implode( ' | ', $status_views ) ); ?>
	</ul>

	<?php
	$request_uri_action = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	?>
	<div id="wtai-frm-search-products" class="wtai-frm-search-products" >
		<p class="wtai-search-box">
			<label class="screen-reader-text" for="wtai-post-search-input"><?php echo wp_kses_post( __( 'Search products:', 'writetext-ai' ) ); ?></label>
			<input type="search" id="wtai-post-search-input" name="s" value="<?php echo esc_attr( wp_unslash( _admin_search_query() ) ); ?>">
			<input type="button" id="wtai-search-product-submit" class="button" value="<?php echo wp_kses_post( __( 'Search products', 'writetext-ai' ) ); ?>">
		</p>
	</div>
	<div class="wtai-show-comparison wtai-show-comparison">
		<?php
		$wtai_comparison_status = wtai_get_user_comparison_cb();
		if ( $wtai_comparison_status && $wtai_comparison_status > 0 ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		?>
		<input type="checkbox" name="wtai_comparison_cb" id="wtai-comparison-cb" value="1" <?php echo esc_attr( $checked ); ?> />
		<label for="wtai-comparison-cb"><?php echo wp_kses_post( __( 'Show text preview on hover', 'writetext-ai' ) ); ?></label>

	</div>
	<?php $wtai_product_list_table->display(); ?>
	<div class="wtai-content">
		<div class="wtai-history wtai-history-global">
			<div class="wtai-d-inner-wrapper">
				<div class="wtai-history-header"><?php echo wp_kses_post( __( 'History log', 'writetext-ai' ) ); ?></div>
				<div class="wtai-history-filter">
					<div class="wtai-history-filter-form">
					<span class="wtai-history-date-from wtai-calendar-field">
						<input type="text" class="wtai-history-date-input wtai-history-date-input-from" data-field="from" placeholder="<?php echo wp_kses_post( __( 'Start date', 'writetext-ai' ) ); ?>" />
					</span>
					<span class="wtai-history-date-to wtai-calendar-field">
						<input type="text" class="wtai-history-date-input wtai-history-date-input-to" data-field="to" placeholder="<?php echo wp_kses_post( __( 'End date', 'writetext-ai' ) ); ?>"  />
					</span>
					<span class="wtai-history-date-author">
						<select class="wtai-history-author-select" id="wtai-history-author-select" >
							<option value="" class="wtai-option-author-default"><?php echo wp_kses_post( __( 'Filter by user', 'writetext-ai' ) ); ?></option>
						</select>
					</span>
					<span class="wtai-history-date-action">
						<a href="#" class="button wtai-history-filter-button"><?php echo wp_kses_post( __( 'Filter', 'writetext-ai' ) ); ?></a>
					</span>
					</div>
				</div>
				<div class="wtai-history-content"></div>
			</div>
		</div>
	</div>

</div>
<?php
global $wtai_product_dashboard;
add_thickbox();
$product_attribute   = $wtai_product_dashboard->get_product_attribute();
$product_fields      = $wtai_product_dashboard->get_product_fields( true, 'transfer' );
$style_and_tones     = $wtai_product_dashboard->get_product_text_style_tone_audiences( '', '', array(), true );
$product_text_fields = $wtai_product_dashboard->get_product_fields( true, 'generate' );

$product_description_length = array(
	'min' => apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' ),
	'max' => apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' ),
);

$product_excerpt_length = array(
	'min' => apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' ),
	'max' => apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' ),
);

$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

$max_other_details_length = isset( $global_rule_fields['maxOtherDetailsLength'] ) ? $global_rule_fields['maxOtherDetailsLength'] : 0;
?>

<div id="wtai-bulk-generate-modal"  style="display:none;">
	<div class="wtai-header-modal"></div>
	<div class="wtai-product-fields-wrapper">
		<div class="wtai-product-container wtai-product-textfields-container">
			<div class="wtai-product-wrap wtai-product-all-trigger wtai-product-textfield-wrap">
				<span class="wtai-product-label-text"><?php echo wp_kses_post( __( 'Generate', 'writetext-ai' ) ); ?></span>
				<div class="wtai-label-select-all-wrap">
					<label for="wtai-select-all-generate">
						<input type="checkbox" name="wtai_select_all_generate" id="wtai-select-all-generate" class="wtai-product-cb-all" />
						<?php echo wp_kses_post( __( 'Select all', 'writetext-ai' ) ); ?>
					</label>
				</div>
				<?php echo wp_kses( $product_text_fields, wtai_kses_allowed_html() ); ?>
			</div>
		</div>

		<?php echo wp_kses( $style_and_tones, wtai_kses_allowed_html() ); ?>
	
		<div class="wtai-product-container wtai-product-attributes-container wtai-product-all-trigger wtai-bulk-prod-attribute-wrapper">
			<span class="wtai-product-attr-title"><?php echo wp_kses_post( __( 'Product attributes', 'writetext-ai' ) ); ?></span>
			<div class="wtai-label-select-all-wrap">
				<label for="wtai-select-all-attr">
					<input type="checkbox" name="wtai_select_all_attr" id="wtai-select-all-attr" class="wtai-product-cb-all" />
					<?php echo wp_kses_post( __( 'Select all', 'writetext-ai' ) ); ?>
				</label>
			</div>
		   
			<?php echo wp_kses( $product_attribute, wtai_kses_allowed_html() ); ?>

			<div class="wtai-bulk-other-details-wrap wtai-char-count-bulk-parent-wrap" >
				<label for="wtai-bulk-other-details" >
					<span><?php echo wp_kses_post( __( 'Special instructions', 'writetext-ai' ) ); ?></span>
					<div class="wtai-special-sintruction-tooltip wtai-tooltip">
						<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext">
							<div class="wtai-tooltip-arrow"></div>
							<?php
							echo wp_kses_post( __( 'Enter other details or instructions that you would like WriteText.ai to consider in generating text for the products you selected (e.g., add information on discounts, special sales, etc). This will not replace any details you may have added in the “Other product details” section in the individual product pages. Special instructions will be cleared at the end of a user session.', 'writetext-ai' ) );
							?>
						</div>	
					</div>
				</label>
				<p class="wtai-sub-desc" ><?php echo wp_kses_post( __( 'Use a comma to separate details.', 'writetext-ai' ) ); ?></p>
				<textarea id="wtai-bulk-other-details" class="wtai-max-length-field wtai-bulk-other-details" maxlength="<?php echo esc_attr( $max_other_details_length ); ?>" ></textarea>
				<div class="wtai-char-count-wrap">
					<span class="wtai-char-count-bulk">0</span>/<span class="wtai-max-count"><?php echo esc_attr( $max_other_details_length ); ?></span> <?php echo wp_kses_post( __( ' Char', 'writetext-ai' ) ); ?>
				</div>
			</div>
		</div>
		<?php

		$min_output_words = $global_rule_fields['minOutputWords'];
		$max_output_words = $global_rule_fields['maxOutputWords'];
		?>
		<div class="wtai-product-container wtai-product-textlength-container">
			<span class="wtai-product-textlength-title"><?php echo wp_kses_post( __( 'Product description', 'writetext-ai' ) ); ?></span>
			
			<div class="wtai-product-textlength-field-container wtai-product-textlength-field-container-desc wtai-button-text-length wtai-tooltip-bulk-length-set" >
				<label class="wtai-bulk-length-label"><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
				<span class="wtai-product-textlength-field-wrapper wtai-min">
					<span class="wtai-text-label"><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
					<span class="wtai-input-group">
						<input type="number" id="wtai-product-description-length-min" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( $product_description_length['min'] ) ); ?>" data-original-value="<?php echo esc_attr( $product_description_length['min'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>"/>
						<span class="wtai-plus-minus-wrapper">
							<span class="dashicons dashicons-plus wtai-txt-plus"></span>
							<span class="dashicons dashicons-minus wtai-txt-minus"></span>
						</span>
					</span>
				</span>
				<span class="wtai-product-textlength-field-wrapper">
					<span class="wtai-text-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
					<span class="wtai-input-group">
						<input type="number" id="wtai-product-description-length-max" class="wtai-bulk-product-max-length wtai-specs-input wtai-max-text" value="<?php echo esc_attr( wp_unslash( $product_description_length['max'] ) ); ?>" data-original-value="<?php echo esc_attr( $product_description_length['max'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>"/>
						<span class="wtai-plus-minus-wrapper">
							<span class="dashicons dashicons-plus wtai-txt-plus"></span>
							<span class="dashicons dashicons-minus wtai-txt-minus"></span>
						</span>
					</span>
				</span>
			</div>
			<span class="wtai-product-textlength-title"><?php echo wp_kses_post( __( 'Product short description', 'writetext-ai' ) ); ?></span>
			
			<div class="wtai-product-textlength-field-container wtai-button-text-length wtai-tooltip-bulk-length-set" >
				<label class="wtai-bulk-length-label" ><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
				<span class="wtai-product-textlength-field-wrapper wtai-min">
					<span class="wtai-text-label"><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
					<span class="wtai-input-group">
						<input type="number" id="wtai-product-excerpt-length-min" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( $product_excerpt_length['min'] ) ); ?>" data-original-value="<?php echo esc_attr( $product_excerpt_length['min'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
						<span class="wtai-plus-minus-wrapper">
							<span class="dashicons dashicons-plus wtai-txt-plus"></span>
							<span class="dashicons dashicons-minus wtai-txt-minus"></span>
						</span>
					</span>
				</span>
				<span class="wtai-product-textlength-field-wrapper">
					<span class="wtai-text-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
					<span class="wtai-input-group">
						<input type="number" id="wtai-product-excerpt-length-max" class="wtai-bulk-product-max-length wtai-specs-input wtai-max-text" value="<?php echo esc_attr( wp_unslash( $product_excerpt_length['max'] ) ); ?>" data-original-value="<?php echo esc_attr( $product_excerpt_length['max'] ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
						<span class="wtai-plus-minus-wrapper">
							<span class="dashicons dashicons-plus wtai-txt-plus"></span>
							<span class="dashicons dashicons-minus wtai-txt-minus"></span>
						</span>
					</span>
				</span>
			</div>
			
		</div>

	</div>
	<?php
	$wtai_bulk_generate_ppopup = get_user_meta( get_current_user_id(), 'wtai_bulk_generate_popup', true );
	if ( $wtai_bulk_generate_ppopup ) {
		$check = 'checked="true"';
	} else {
		$check = '';
	}

	$wtai_refproduct_byuser    = get_user_meta( get_current_user_id(), 'wtai_refproduct_byuser', true );
	$wtai_refproduct_byuser_cb = get_user_meta( get_current_user_id(), 'wtai_refproduct_byuser_cb', true );

	$check    = '';
	$classref = 'disabled';
	?>
	<?php
			$html      = '<div class="wtai-reference-product-wrapper">
						<label for="wtai-bulk-custom-style-ref-prod">
                        <input type="checkbox" name="wtai_bulk_custom_style_ref_prod" class="wtai-bulk-custom-style-ref-prod" data-type="custom-style-refprod" id="wtai-bulk-custom-style-ref-prod" value="1" ' . $check . '/><span class="wtai-ref-product-span-wrap" ><span>' . __( 'Reference product', 'writetext-ai' ) . '</span></span></label>
						<select data-prodid="' . $wtai_refproduct_byuser . '" class="wtai-bulk-custom-style-ref-product-select ' . $classref . '" name="wtai_custom_style_ref_prod_sel" data-type="style" placeholder="' . __( 'Select or enter product name', 'writetext-ai' ) . '">';
				$html .= '</select>
							<div class="wtai-tooltip"><span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow"></div>'
								. '<p>' . __( 'If you have already edited a certain product’s texts to fit the specific style and structure that you want, you can set it as a “Reference product” which WriteText.ai will use as the basis for generating text. When using a reference product, the individual settings for tone, style, and audience will be ignored. Note that image alt text generation will not be affected by the reference product.', 'writetext-ai' ) . '</p>
								<p>' . __( 'Note: The texts that will be used as reference are the ones currently in WordPress. Only products with all text types saved in WordPress will be available for selection in this list.', 'writetext-ai' ) . '</p>
								<p>' . __( 'The credit cost for generating text using a reference product will depend on the length of the reference text and any formatting (e.g., bold, italics, numbered or bulleted lists) applied to it. For a more detailed explanation, you may read our FAQ section on <a href="https://writetext.ai/frequently-asked-questions#credit-cost" target="_blank" >how your credit cost is calculated</a>.', 'writetext-ai' ) . '</p>
								</div>
							</div>
					</div>';
					echo wp_kses( $html, wtai_kses_allowed_html() );
	?>
	<div class="wtai-footer-modal wtai-d-flex" >
	
		<label class="wtai-dont-show-bulk-generate-popup-label" for="wtai-use-ranking-keywords">
			<input type="checkbox" name="wtai-use-ranking-keywords" id="wtai-use-ranking-keywords" value="1"  />
			<?php
				echo wp_kses_post( __( "Use keywords you're currently ranking for", 'writetext-ai' ) );
			?>
		</label>

		<a class="button button-primary" id="wtai-generate-bulk-btn" onClick="wtaiBulkGenerate(this, event)" > <?php echo wp_kses_post( __( 'Generate', 'writetext-ai' ) ) . ' '; ?><span class="wtai-credit-cost-wrap" style="display: none;" > (<?php echo wp_kses_post( __( '<span class="wtai-credvalue">0</span> <span class="wtai-cred-label" >credits</span>', 'writetext-ai' ) ); ?>)</span></a>
	</div>
</div>

<div id="wtai-bulk-transfer-modal" style="display:none;">
	<div class="wtai-header-modal"></div>
	<?php echo wp_kses( $product_fields, wtai_kses_allowed_html() ); ?>
	<div class="wtai-footer-modal">
		<button class="button button-primary" id="wtai-transfer-bulk-btn"  onClick="wtaiBulkTransfer(this, event)"> <?php echo wp_kses_post( __( 'Transfer', 'writetext-ai' ) ); ?></button>
	</div>
</div>

<div id="wtai-attention-modal" style="display:none;">

	<div class="wtai-attention-modal-container">
		<p>  <?php echo wp_kses_post( __( 'Please select some products first.', 'writetext-ai' ) ); ?> </p>
	</div>
	<div class="wtai-footer-modal end">
		<button class="button button-primary"  id="wtai-attention-ok-btn"><?php echo wp_kses_post( __( 'Ok', 'writetext-ai' ) ); ?></button>
	</div>
	
</div>

<div class="wtai-history-content-corrector" style="display: none;" ></div>

<?php do_action( 'wtai_bulk_generate_loader' ); ?>
<?php do_action( 'wtai_bulk_edit_generate_cancel' ); ?>
<?php do_action( 'wtai_bulk_edit_cancel_and_exit' ); ?>
<?php do_action( 'wtai_edit_product_form' ); ?>
<?php do_action( 'wtai_country_selection_popup' ); ?>
<?php do_action( 'wtai_restore_global_setting_completed' ); ?>
<?php do_action( 'wtai_premium_modal' ); ?>
<?php do_action( 'wtai_preprocess_image_loader' ); ?>
<?php do_action( 'wtai_image_confirmation_proceed_loader' ); ?>
<?php do_action( 'wtai_image_confirmation_proceed_bulk_loader' ); ?>
<?php do_action( 'wtai_admin_mobile_footer' ); ?>
