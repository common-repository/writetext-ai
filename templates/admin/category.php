<?php
/**
 * Product category metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$source = get_option( 'wtai_installation_source', '' );

$product_edit_nonce = wp_create_nonce( 'wtai-product-nonce' );

$global_rule_fields         = apply_filters( 'wtai_global_rule_fields', array() );
$max_representative_product = isset( $global_rule_fields['maxRepresentativeProducts'] ) ? $global_rule_fields['maxRepresentativeProducts'] : WTAI_MAX_REPRESENTATIVE_PRODUCT;

/* translators: %s: Max keyword length */
$rep_product_disabled_tooltip = '<span class="wtai-rep-prod-tooltip" >' . sprintf( __( 'You can only add up to %s products. Please remove an existing product to add a new one.', 'writetext-ai' ), $max_representative_product ) . '</span>';

?>

<form method="post" id="wtai-edit-product-line-form" data-product-nonce="<?php echo esc_attr( $product_edit_nonce ); ?>" >
<div class="wrap wtai-edit-product-line">
	<div class="wtai-header-wrapper">
		<div class="wtai-header-title">
			<h1 class="wp-heading-inline wtai-post-title wtai-post-data-json" data-postfield="post_title"></h1>
			<p class="wtai-permalink-wrapper"><a href="#" class="wtai-post-data-json" data-postfield="post_permalink" style="display:none;"></a></p>
			<input type="hidden" class="wtai-product-short-title wtai-post-data-json" data-postfield="product_short_title" />
		</div>
		<div class="wtai-header-configuration">
			<div class="wtai-review-wrapper">
				<?php if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) : ?>
					<input type="checkbox" disabled class="wtai-post-data wtai-review-check" data-postfield="wtai_review" name="wtai_review" value="1"  /> <label for="wtai_review"><?php echo wp_kses_post( __( 'Mark as reviewed', 'writetext-ai' ) ); ?></label>
					<div class="wtai-tooltip"><span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow wtai-noshadow"></div>
						<?php
						echo '<p>' . wp_kses_post( __( 'Check this box to keep track of the categories where you have reviewed the text. This is especially helpful if you have an internal workflow where text needs to go through a review process first before being published on the website. This checkbox does not affect the live content, it is only a classification.', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'You can filter reviewed categories by selecting “Reviewed” under the “Filter by WriteText.ai status” dropdown in the category list.', 'writetext-ai' ) ) . '</p>';
						?>
						</div>
					</div>
				<?php endif; ?>
				
			</div>
			<span class="wtai-product-pager-wrapper">
				<a href="#" class="button wtai-button-prev disabled">
					<span class="dashicons dashicons-arrow-left-alt2"></span>
				</a>
				<a href="#" class="button wtai-button-next disabled">
					<span class="dashicons dashicons-arrow-right-alt2"></span>
				</a>
			</span>
		</div>
	</div>
	<hr class="wp-header-end">
		<?php wp_nonce_field( 'writext_ai_edit_product', '_wpnonce' ); ?>
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" style="position: relative;"></div>
				
				<div id="postbox-container-1" class="postbox-container">
					<div id="side-sortables" class="meta-box-sortables wtai-ui-sortable">
						<div id="wtai-submitdiv" class="postbox wtai-metabox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle wtai-post-data-json" data-postfield="post_status_ucfirst">
									<span class="wtai-mb-headline" >
										<span class="wtai-step-guideline wtai-step-guideline-7" ><?php echo wp_kses_post( __( 'Step 7', 'writetext-ai' ) ); ?></span>
										<?php echo wp_kses_post( __( 'Transfer to WordPress', 'writetext-ai' ) ); ?>
									</span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext">
										<div class="wtai-tooltip-arrow"></div>
										<?php
										echo '<p>' . wp_kses_post( __( "When you're done generating and/or editing text, you have the option to save the draft inside WriteText.ai or transfer the text to WordPress. Transferring your text to WordPress will publish it on the website.", 'writetext-ai' ) ) . '</p>
										<p>' . wp_kses_post( __( 'Note: Any media or shortcode you have inserted in your current WordPress text will be overwritten when you transfer from WriteText.ai.', 'writetext-ai' ) ) . '</p>';
										?>
									</div>
								</div>
								<div class="handle-actions hide-if-no-js">
									<a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a>
								</div>
							</div>
							<div class="inside">
								<div class="submitbox" id="submitpost">
									<div id="minor-publishing">
										<div id="minor-publishing-actions">
											<div id="save-action">
												<?php if ( wtai_current_user_can( 'writeai_generate_text' ) ) : ?> 
													<a class="button wtai-button-interchange wtai-bulk-button-text disabled" data-typesave="bulk_generated" value="<?php echo wp_kses_post( __( 'Save', 'writetext-ai' ) ); ?>"><?php echo wp_kses_post( __( 'Save', 'writetext-ai' ) ); ?></a>
												<?php endif; ?>
											</div>
											<div id="wtai-preview-action-setup">
												<a class="button wtai-button-preview" value="<?php echo wp_kses_post( __( 'Preview changes', 'writetext-ai' ) ); ?>"><?php echo wp_kses_post( __( 'Preview changes', 'writetext-ai' ) ); ?></a>
												<div class="wtai-tooltip">
													<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
													<div class="wtai-tooltiptext">
														<div class="wtai-tooltip-arrow"></div>
														<?php echo wp_kses_post( __( 'Save your changes to preview how they will look on your website.', 'writetext-ai' ) ); ?>
													</div>
												</div>	
											</div>
											<div class="clear"></div>
										</div>
									</div>
									<input name="wtai_edit_post_id" class="wtai-post-data-json" data-postfield="post_id" type="hidden" id="wtai-edit-post-id"  value="" />
									<?php if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) : ?>
										<div id="major-publishing-actions">
											<div id="publishing-action">
												<a class="button button-primary button-large  wtai-button-interchange wtai-bulk-button-text disabled" data-typesave="bulk_transfer" >
													<?php echo wp_kses_post( __( 'Transfer selected to WordPress', 'writetext-ai' ) ); ?>
												</a>												
											</div>											

											<div class="clear"></div>
										</div>
									<?php endif; ?>	
								</div>
							</div>
						</div>

						<?php do_action( 'wtai_ads_placeholder' ); ?>

						<div id="wtai-woocommerce-category-image" class="postbox wtai-metabox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle">
									<span class="wtai-mb-headline" >
										<span class="wtai-step-guideline wtai-step-guideline-3" ><?php echo wp_kses_post( __( 'Step 3', 'writetext-ai' ) ); ?></span>
										<?php echo wp_kses_post( __( 'Category image', 'writetext-ai' ) ); ?>
									</span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext">
										<div class="wtai-tooltip-arrow"></div>
										<?php
										echo '<p>' . wp_kses_post( __( 'If available, you can let AI analyze the category image in order to generate more accurate and relevant text. Please check that the image accurately represents the category (i.e., it is not some kind of placeholder or a generic image for your shop).', 'writetext-ai' ) ) . '</p>';
										?>
									</div>
								</div>
								<div class="handle-actions hide-if-no-js">
									<a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a>
								</div>
							</div>
							<div class="postbox-content inside" > 
								<div class="wtai-category-image-wrap" >
									<?php echo '<p class="wtai-cat-no-image-wrap" >' . wp_kses_post( __( 'No image found.', 'writetext-ai' ) ) . '</p>'; ?>
								</div>
							</div>
						</div>
						
						<div id="wtai-woocommerce-product-attributes" class="postbox wtai-metabox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle">
									<span class="wtai-mb-headline" >
										<span class="wtai-step-guideline wtai-step-guideline-4" ><?php echo wp_kses_post( __( 'Step 4', 'writetext-ai' ) ); ?></span>
										<?php echo wp_kses_post( __( 'Representative products', 'writetext-ai' ) ); ?>
									</span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext">
										<div class="wtai-tooltip-arrow"></div>
										<?php
										/* translators: %s: Max representative product count. */
										echo '<p>' . wp_kses_post( sprintf( __( 'Select a maximum of %s products that best represent this category. Depending on the data available, the product name, product description, and product images will be used as reference for generating text for this category.', 'writetext-ai' ), $max_representative_product ) ) . '</p>';
										?>
									</div>
								</div>
								<div class="handle-actions hide-if-no-js">
									<a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a>
								</div>
								
							</div>
							<?php if ( wtai_current_user_can( 'writeai_generate_text' ) ) : ?>
									<div class="postbox-content inside" post > 
										<div class="wtai-representative-product-wrap" >
											<div class="wtai-representative-product-input-wrap" title="<?php echo esc_attr( $rep_product_disabled_tooltip ); ?>" >
												<div class="wtai-representative-product-input-container" >
													<input type="text" class="wtai-representative-product-input" placeholder="<?php echo wp_kses_post( __( 'Select or enter product name', 'writetext-ai' ) ); ?>" />
												</div>

												<div class="wtai-representative-product-input-items-wrap" >
													<?php echo wp_kses_post( __( 'No product/s found.', 'writetext-ai' ) ); ?>
												</div>
											</div>

											<div class="wtai-representative-product-items-wrap" >
												<div class="wtai-representative-product-empty" >
													<span class="wtai-representative-product-empty-text" >
														<?php echo wp_kses_post( __( 'Your selected product/s will display here.', 'writetext-ai' ) ); ?>
													</span>
												</div>

												<div class="wtai-representative-product-counter-wrap" >
													(<span class="wtai-rpc-item-count" >0</span>/<span class="wtai-rpc-total" ><?php echo esc_attr( $max_representative_product ); ?></span>)
												</div>
												<div class="wtai-representative-product-items-list" >
												</div>
											</div>
										</div>

										<ul class="wtai-post-data" data-postfield="product_attr">
											<?php if ( wtai_current_user_can( 'writeai_orderproduct_details' ) ) : ?>
												<li class="text wtai-char-count-parent-wrap wtai-other-product-details-main-wrap <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>">
													<input type="checkbox"  id="wtai-other-product-details" class="wtai-attr-checkboxes wtai-post-data" data-postfield="otherproductdetails_checked" data-apiname="otherproductdetails" value="1" />
													
													<label class="wtai-details" for="wtai-other-product-details" >
														<strong><?php echo wp_kses_post( __( 'Other details', 'writetext-ai' ) ); ?></strong>
														<?php do_action( 'wtai_product_single_premium_badge', 'wtai-premium-other-product-details' ); ?>
														<div class="wtai-details"><?php echo wp_kses_post( __( 'Use a comma to separate details.', 'writetext-ai' ) ); ?></div>
													</label>

													<div class="wtai-otherproddetails-container" >
														<?php echo wp_kses( wtai_get_field_template( 0, 'otherproductdetails', '', '', '', isset( $global_rule_fields['maxOtherDetailsLength'] ) ? $global_rule_fields['maxOtherDetailsLength'] : 0 ), wtai_kses_allowed_html() ); ?>
													</div>
												</li>
											<?php endif; ?>
										</ul>
									</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<div id="postbox-container-2" class="postbox-container">

					<div id="normal-sortables" class="meta-box-sortables wtai-ui-sortable ">
						<?php do_action( 'wtai_product_category_main_metabox' ); ?>
						<?php
						// TODO: Update PO files.
						/* translators: %s: max meta title limit */
						$page_title_tooltip = '<p>' . sprintf( __( 'WriteText.ai aims to generate a title with around %s characters, based on current SEO best practices. The current meta title saved for the category is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_PAGE_TITLE_TEXT_LIMIT ) . '</p>';

						// TODO: Update PO files.
						/* translators: %s: max meta description limit */
						$page_description_tooltip = '<p>' . sprintf( __( 'WriteText.ai aims to generate a description with around %s characters, based on current SEO best practices. The current meta description saved for the category is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_MAX_PAGE_DESCRIPTION_LIMIT ) . '</p>';

						// TODO: Update PO files.
						$product_description_tooltip  = '<p>' . __( 'The current category description saved for the category is displayed in the box on the right for your reference only.', 'writetext-ai' ) . '</p>';
						$product_description_tooltip .= '<p>' . __( 'Target length<br/>', 'writetext-ai' ); // TODO: Update PO files.
						$product_description_tooltip .= __( 'Indicate your target length by setting a minimum and maximum word count. WriteText.ai will aim to generate text within the number you set, but it may give you more or less words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' );

						// TODO: Update PO files.
						/* translators: %s: max open graph limit */
						$open_graph_tooltip = '<p>' . sprintf( __( 'This is the text that appears in the preview when you share the category page on social media sites like Facebook, Twitter, or LinkedIn. WriteText.ai aims to generate text with more or less %s characters, based on current best practices. The current Open Graph text saved for the category is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_MAX_OPEN_GRAPH_LIMIT ) . '</p>';

						$tooltiptext_array = array(
							'page_title'           => $page_title_tooltip,
							'page_description'     => $page_description_tooltip,
							'category_description' => $product_description_tooltip,
							'open_graph'           => $open_graph_tooltip,
						);

						foreach ( $columns as $column_key => $column_label ) {
							$column_label    = $column_label;
							$text_length_min = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $column_key . '_min' );
							$text_length_max = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $column_key . '_max' );

							$column_cb_is_checked = '';
							if ( $wtai_preselected_types && in_array( $column_key, $wtai_preselected_types, true ) ) {
								$column_cb_is_checked = 'checked';
							}

							$force_disable        = false;
							$force_disable_class  = '';
							$force_disabled_label = '';
							$forced_disable_cb    = '';

							if ( 'all-in-one-seo-pack' === $source && 'category_description' !== $column_key ) {
								$force_disable       = true;
								$force_disable_class = 'wtai-disabled-seo-field';
								$forced_disable_cb   = 'disabled';

								$force_disabled_label .= ' <span class="wtai-seo-field-disabled-label">' . esc_html__( '(field not supported by SEO plugin)', 'writetext-ai' ) . '</span>';
							}
							?>
							<div id="wtai-product-details-<?php echo esc_attr( $column_key ); ?>" class="postbox wtai-metabox wtai-loading-metabox wtai-metabox-<?php echo esc_attr( $column_key ); ?> <?php echo esc_attr( $force_disable_class ); ?>"  data-type="<?php echo esc_attr( $column_key ); ?>">
								
								<div class="postbox-header">
									<input data-checked="<?php echo esc_attr( $column_cb_is_checked ); ?>" <?php echo esc_attr( $forced_disable_cb ); ?> class="wtai-checkboxes disabled wtai-init-fields" <?php echo esc_attr( $column_cb_is_checked ); ?> type="checkbox"  data-type="<?php echo esc_attr( $column_key ); ?>" disabled />

									<h2 class="hndle ui-sortable-handle"><span><?php echo wp_kses_post( $column_label ) . '' . wp_kses_post( $force_disabled_label ); ?></span></h2>
									
									<div class="wtai-status-postheader">
										<span class="wtai-rewrite-checking-label wtai-status-label wtai-status-label-rewrite hidden">
											<span class="wtai-extension-review-label" ></span>
											<span class="wtai-extension-review-comment-form" ></span>
										</span>
									</div>

									<div class="wtai-tooltip">
										<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
										<div class="wtai-tooltiptext">
											<div class="wtai-tooltip-arrow"></div>
											<?php echo wp_kses( $tooltiptext_array[ $column_key ], wtai_kses_allowed_html() ); ?>
										</div>
									</div>
									<div class="handle-actions hide-if-no-js">
										<a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a>
									</div>
									<?php do_action( 'wtai_product_generate_cancel_popup' ); ?>
								</div>
								
								<div class="inside">
									
									<?php if ( wtai_current_user_can( 'writeai_generate_text' ) ) : ?>
										<?php
										$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
										$min_output_words   = $global_rule_fields['minOutputWords'];
										$max_output_words   = $global_rule_fields['maxOutputWords'];
										?>
										<div class="wtai-button-spin-wrapper <?php echo ( $text_length_min && $text_length_max ) ? 'with_text_length' : 'wtai-with-no-text-length'; ?>">
											<?php // Move the class and other required data in wtai-button-left-wrapper from the button. ?>
											<div class="wtai-button-left-wrapper wtai-generate-text wtai-generate-text-single-<?php echo esc_attr( $column_key ); ?>" data-type="<?php echo esc_attr( $column_key ); ?>">
												
												<?php if ( $text_length_min && $text_length_max ) : ?>
													<div class="wtai-button-text-length wtai-tooltip-single-length-set" >
														<label><?php echo wp_kses_post( __( 'Target length (in words)', 'writetext-ai' ) ); ?></label>
														<span class="wtai-min">
															<span><?php echo wp_kses_post( __( 'Min', 'writetext-ai' ) ); ?></span>
															<span class="wtai-input-group">
																<input type="number" id="<?php echo esc_attr( $column_key ) . '_length_min'; ?>" class="wtai-specs-input wtai-min-text" value="<?php echo esc_attr( wp_unslash( $text_length_min ) ); ?>" data-original-value="<?php echo esc_attr( $text_length_min ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
																<span class="wtai-plus-minus-wrapper">
																	<span class="dashicons dashicons-plus wtai-txt-plus"></span>
																	<span class="dashicons dashicons-minus wtai-txt-minus"></span>
																</span>
															</span>
														</span>
														<span>
															<span class="wtai-text-input-label"><?php echo wp_kses_post( __( 'Max', 'writetext-ai' ) ); ?></span>
															<span class="wtai-input-group">
																<input type="number" class="wtai-single-product-max-length wtai-specs-input wtai-max-text" data-type="<?php echo esc_attr( $column_key ); ?>" id="<?php echo esc_attr( $column_key ) . '_length_max'; ?>" value="<?php echo esc_attr( wp_unslash( $text_length_max ) ); ?>" data-original-value="<?php echo esc_attr( $text_length_max ); ?>" data-mintext="<?php echo esc_attr( $min_output_words ); ?>" data-maxtext="<?php echo esc_attr( $max_output_words ); ?>" />
																<span class="wtai-plus-minus-wrapper">
																	<span class="dashicons dashicons-plus wtai-txt-plus"></span>
																	<span class="dashicons dashicons-minus wtai-txt-minus"></span>
																</span>
															</span>
														</span>
													</div>
												<?php endif; ?>
											</div>
											<div class="wtai-button-right-wrapper">&nbsp;</div>
										</div>
									<?php endif; ?>
									<div class="wtai-col-row-wrapper">
										
										<div class="wtai-columns-3 wtai-col-row wtai-generate-value-wrapper">
											<?php
											$column_label_formatted = strtolower( $column_label );
											if ( 'open_graph' === $column_key ) {
												$column_label_formatted = __( 'Open Graph text', 'writetext-ai' );
											}

											echo wp_kses( wtai_get_field_template( $post_id, $column_key, $column_label_formatted ), wtai_kses_allowed_html() );
											?>
										</div>

										<div class="columns-2 wtai-col-row wtai-single-transfer-btn-wrapper">
											<?php if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) : ?>
											<button type="button" class="button wtai-single-transfer-btn wtai-disabled-button" 
												data-type="<?php echo esc_attr( $column_key ); ?>" 
											><span class="dashicons dashicons-arrow-right-alt2"></span></button>
											<?php endif; ?>
										</div>

										<div class="wtai-columns-1 wtai-col-row wtai-current-value-wrapper" >
											<?php echo wp_kses( wtai_get_field_template_current( $post_id, $column_key, $column_label_formatted ), wtai_kses_allowed_html() ); ?>
										</div>
									</div>									
								</div>
							</div>
						<?php } // End foreach. ?>

						<?php do_action( 'wtai_admin_mobile_footer' ); ?>
					</div>		
					
					<div class="wtai-category-single-spacer" ></div>
				</div>
			</div>
		</div>
	</div>

	<div class="wtai-image-popup">
		<div class="wtai-image-popup-content">
			<span class="wtai-btn-close-popup">&nbsp;</span>
			<div class="wtai-image-popup-inner"><img src="" /></div>
		</div>
	</div>

	<input type="hidden" class="wtai-keyword-analysis-view" id="wtai-keyword-analysis-view" value="0" />
	<input type="hidden" class="wtai-record-type" id="wtai-record-type" value="category" />
</form>

