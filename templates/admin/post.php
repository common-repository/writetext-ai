<?php
/**
 * Product post metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$product_edit_nonce = wp_create_nonce( 'wtai-product-nonce' );
?>

<form method="post" id="wtai-edit-product-line-form" data-product-nonce="<?php echo esc_attr( $product_edit_nonce ); ?>" >
<div class="wrap wtai-edit-product-line">
	<div class="wtai-header-wrapper">
		<div class="wtai-header-title">
			<h1 class="wp-heading-inline wtai-post-title wtai-post-data-json" data-postfield="post_title"></h1>
			<p class="wtai-product-sku wtai-post-data-json" data-postfield="product_sku"></p>
			<p class="wtai-permalink-wrapper"><a href="#" class="wtai-post-data-json" data-postfield="post_permalink" style="display:none;"></a></p>
			<input type="hidden" class="wtai-product-short-title wtai-post-data-json" data-postfield="product_short_title" />
		</div>
		<div class="wtai-header-configuration">
			<div class="wtai-review-wrapper">
				<?php if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) : ?>
					<input type="checkbox" disabled class="wtai-post-data wtai-review-check" data-postfield="wtai_review" name="wtai_review" value="1"  /> <label for="wtai_review"><?php echo wp_kses_post( __( 'Mark as reviewed', 'writetext-ai' ) ); ?></label>
					<div class="wtai-tooltip">
						<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
						<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow wtai-noshadow"></div>
						<?php
						echo '<p>' . wp_kses_post( __( 'Check this box to keep track of the products where you have reviewed the text. This is especially helpful if you have an internal workflow where text needs to go through a review process first before being published on the website. This checkbox does not affect the live content, it is only a classification.', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'You can filter reviewed products by selecting “Reviewed” under the “Filter by WriteText.ai status” dropdown in the product list.', 'writetext-ai' ) ) . '</p>';
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
								<h2 class="hndle ui-sortable-handle" >
									<span class="wtai-mb-headline" >
										<span class="wtai-step-guideline wtai-step-guideline-6" ><?php echo wp_kses_post( __( 'Step 6', 'writetext-ai' ) ); ?></span>
										<?php echo wp_kses_post( __( 'Transfer to WordPress', 'writetext-ai' ) ); ?>
									</span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span><div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<?php
									echo '<p>' . wp_kses_post( __( "When you're done generating and/or editing text, you have the option to save the draft inside WriteText.ai or transfer the text to WordPress. Transferring your text to WordPress will either save it as a draft or publish it on the website, depending on the current status of your product.", 'writetext-ai' ) ) . '</p>
									<p>' . wp_kses_post( __( 'For example, if the product is already published, the text you transfer from WriteText.ai will automatically be published live. If the product is still in draft, the text you transfer will also be saved as a draft and you will need to publish the product first if you want to see the text live. You can see the status of the product in this box.', 'writetext-ai' ) ) . '</p>
									<p>' . wp_kses_post( __( 'Note: Any media or shortcode you have inserted in your current WordPress text will be overwritten when you transfer from WriteText.ai.', 'writetext-ai' ) ) . '</p>';
									?>
									</div></div>
								<div class="handle-actions hide-if-no-js"><a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a></div>
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
														<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow"></div>
														<?php echo wp_kses_post( __( 'Save your changes to preview how they will look on your website.', 'writetext-ai' ) ); ?>
													</div>
												</div>	
												</div>
												<div class="clear"></div>
											</div>
										 
										<div id="misc-publishing-actions">
											<div class="misc-pub-section misc-pub-post-status"><?php echo wp_kses_post( __( 'Status:' ) ); ?> <span id="post-status-display" class="wtai-post-data-json" data-postfield="status"></span></div>
											<div class="misc-pub-section misc-pub-visibility" id="visibility"><?php echo wp_kses_post( __( 'Visibility:' ) ); ?> <span id="post-visibility-display" class="wtai-post-data-json" data-postfield="post_visibility" ></span></div>
											<div class="misc-pub-section curtime misc-pub-curtime"><span id="timestamp" class="wtai-post-data-json" data-postfield="post_timedate"></span></div>
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
						
						<div id="wtai-woocommerce-product-attributes" class="postbox wtai-metabox">
							<div class="postbox-header">
								<h2 class="hndle ui-sortable-handle">
									<span class="wtai-mb-headline" >
										<span class="wtai-step-guideline wtai-step-guideline-3" ><?php echo wp_kses_post( __( 'Step 3', 'writetext-ai' ) ); ?></span>
										<?php echo wp_kses_post( __( 'Product attributes', 'writetext-ai' ) ); ?>
									</span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow"></div>
									<?php
									echo '<p>' . wp_kses_post( __( 'Check the product attributes that you want to be considered in generating text. You can also select your main product image here and AI will analyze it in order to generate more accurate and relevant text. Please make sure that the image accurately represents the product (i.e., it is not some kind of placeholder or a generic image for your shop).', 'writetext-ai' ) ) . '</p>
									<p>' . wp_kses_post( __( 'There is no guarantee that a selected product attribute will always appear in the text, but it will help guide WriteText.ai in generating more relevant text.', 'writetext-ai' ) ) . '</p>
									<p>' . wp_kses_post( __( 'If you want to add more information about the product, you can enter them in the “Other product details” section. For example, you can write: ', 'writetext-ai' ) ) . '</p>
									<p><em>' . wp_kses_post( __( '“The shirt comes in a reusable shirt bag that can be used for traveling.”', 'writetext-ai' ) ) . '</em><br/><br/>' .
									wp_kses_post( __( 'WriteText.ai will then attempt to include the text or its meaning in the generated product text, e.g.:', 'writetext-ai' ) ) . '<br/><br/>
									<em>' . wp_kses_post( __( '“...Plus, its reusable bag makes it perfect for travel or to keep in storage when not in use!”', 'writetext-ai' ) ) . '</em></p>';
									?>
									</div>
								</div>
								<div class="handle-actions hide-if-no-js"><a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a></div>
								
							</div>
							<?php if ( wtai_current_user_can( 'writeai_change_product_attr' ) ) : ?>
									<div class="postbox-content inside" post > 

										<div class="wtai-product-attr-image-wrap" ></div>

										<ul class="wtai-post-data" data-postfield="product_attr">
											<?php if ( wtai_current_user_can( 'writeai_orderproduct_details' ) ) : ?>
												<li class="text wtai-char-count-parent-wrap wtai-other-product-details-main-wrap <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>">
													<input type="checkbox"  id="wtai-other-product-details" class="wtai-attr-checkboxes wtai-post-data" data-postfield="otherproductdetails_checked" data-apiname="otherproductdetails" value="1" />
													
													<label class="wtai-details">
														<strong><?php echo wp_kses_post( __( 'Other product details', 'writetext-ai' ) ); ?></strong>
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
						<?php do_action( 'wtai_product_single_main_metabox' ); ?>
						<?php
							// TODO: Update PO files.
							/* translators: %s: max meta title limit */
							$page_title_tooltip = '<p>' . sprintf( __( 'WriteText.ai aims to generate a title with around %s characters, based on current SEO best practices. The current meta title saved for the product is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_PAGE_TITLE_TEXT_LIMIT ) . '</p>';

							// TODO: Update PO files.
							/* translators: %s: max meta description limit */
							$page_description_tooltip = '<p>' . sprintf( __( 'WriteText.ai aims to generate a description with around %s characters, based on current SEO best practices. The current meta description saved for the product is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_MAX_PAGE_DESCRIPTION_LIMIT ) . '</p>';

							// TODO: Update PO files.
							$product_description_tooltip  = '<p>' . __( 'Please check your website to see if you are using the product description or the product short description field (or both) to ensure that you are generating the correct text. The current product description saved for the product is displayed in the box on the right for your reference only.', 'writetext-ai' ) . '</p>';
							$product_description_tooltip .= '<p>' . __( 'Target length<br/>', 'writetext-ai' ); // TODO: Update PO files.
							$product_description_tooltip .= __( 'Indicate your target length by setting a minimum and maximum word count. WriteText.ai will aim to generate text within the number you set, but it may give you more or less words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' );

							$product_excerpt_tooltip  = '<p>' . __( 'Please check your website to see if you are using the product description or the product short description field (or both) to ensure that you are generating the correct text. The current product short description saved for the product is displayed in the box on the right for your reference only.', 'writetext-ai' ) . '</p>';
							$product_excerpt_tooltip .= '<p>' . __( 'Target length<br/>', 'writetext-ai' ); // TODO: Update PO files.
							$product_excerpt_tooltip .= __( 'Indicate your target length by setting a minimum and maximum word count. WriteText.ai will aim to generate text within the number you set, but it may give you more or less words than expected from time to time. Note that your credit cost will depend on the target length you set so make sure to set a reasonable target range.', 'writetext-ai' ) . '</p>';

							// TODO: Update PO files.
							/* translators: %s: max open graph limit */
							$open_graph_tooltip = '<p>' . sprintf( __( 'This is the text that appears in the preview when you share the product page on social media sites like Facebook, Twitter, or LinkedIn. WriteText.ai aims to generate text with more or less %s characters, based on current best practices. The current Open Graph text saved for the product is displayed in the box on the right for your reference only.', 'writetext-ai' ), WTAI_MAX_OPEN_GRAPH_LIMIT ) . '</p>';

							$tooltiptext_array = array(
								'page_title'          => $page_title_tooltip,
								'page_description'    => $page_description_tooltip,
								'product_description' => $product_description_tooltip,
								'product_excerpt'     => $product_excerpt_tooltip,
								'open_graph'          => $open_graph_tooltip,
							);

							foreach ( $columns as $column_key => $column_label ) :
								$column_label    = $column_label;
								$text_length_min = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $column_key . '_min' );
								$text_length_max = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $column_key . '_max' );

								$column_cb_is_checked = '';
								if ( $wtai_preselected_types && in_array( $column_key, $wtai_preselected_types, true ) ) {
									$column_cb_is_checked = 'checked';
								}
								?>
							<div id="wtai-product-details-<?php echo esc_attr( $column_key ); ?>" class="postbox wtai-metabox wtai-loading-metabox wtai-metabox-<?php echo esc_attr( $column_key ); ?>"  data-type="<?php echo esc_attr( $column_key ); ?>">
								
								<div class="postbox-header">
									<input data-checked="<?php echo esc_attr( $column_cb_is_checked ); ?>" class="wtai-checkboxes disabled wtai-init-fields" <?php echo esc_attr( $column_cb_is_checked ); ?> type="checkbox"  data-type="<?php echo esc_attr( $column_key ); ?>" disabled />

									<h2 class="hndle ui-sortable-handle"><span><?php echo wp_kses_post( $column_label ); ?></span></h2>
									
									<div class="wtai-status-postheader">
										<span class="wtai-rewrite-checking-label wtai-status-label wtai-status-label-rewrite hidden">
											<span class="wtai-extension-review-label" ></span>
											<span class="wtai-extension-review-comment-form" ></span>
										</span>
									</div>

									<div class="wtai-tooltip">
										<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
										<div class="wtai-tooltiptext"><div class="wtai-tooltip-arrow"></div>
											<?php echo wp_kses( $tooltiptext_array[ $column_key ], wtai_kses_allowed_html() ); ?>
										</div>
									</div>
									<div class="handle-actions hide-if-no-js"><a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a></div>
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

											echo wp_kses( wtai_get_field_template( 0, $column_key, $column_label_formatted ), wtai_kses_allowed_html() );
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
											<?php echo wp_kses( wtai_get_field_template_current( 0, $column_key, $column_label_formatted ), wtai_kses_allowed_html() ); ?>
										</div>
									</div>									
								</div>
							</div>
						<?php endforeach; ?>

						<?php
							$column_label = __( 'Image alt text', 'writetext-ai' );
							$column_key   = 'image_alt_text';
							/* translators: %s: max open graph limit */
							$image_alt_text_tooltip = '<p>' . sprintf( __( "Analyze your product image and automatically generate an alt image text (incorporating your product name and keywords) to help improve your website's accessibility and the way that search engines understand your content. WriteText.ai aims to generate an alt text with around %s characters based on current best practices. Note: We only allow up to 10 images here, including the product featured image. If you have more than 10 images uploaded to your product, only the first 10 images in your listing will be included.", 'writetext-ai' ), WTAI_MAX_IMAGE_ALT_TEXT_LIMIT ) . '</p>';
						?>
							
						<div id="wtai-product-details-<?php echo esc_attr( $column_key ); ?>" 
							class="postbox wtai-alt-writetext-metabox wtai-alt-loading-metabox wtai-alt-writetext-metabox-<?php echo esc_attr( $column_key ); ?>"  
							data-type="<?php echo esc_attr( $column_key ); ?>">
							<div class="postbox-header">
								<input data-checked="<?php echo esc_attr( $column_cb_is_checked ); ?>" class="wtai-checkboxes wtai-init-fields wtai-checkboxes-alt-text-all" type="checkbox"  data-type="<?php echo esc_attr( $column_key ); ?>" />
								<h2 class="hndle ui-sortable-handle wtai-sortable-handle-has-featured-badge">
									<span><?php echo wp_kses_post( $column_label ); ?></span>
								</h2>
								<div class="wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext wtai-alt-image-text-tooltip">
										<div class="wtai-tooltip-arrow"></div>
										<?php echo wp_kses( $image_alt_text_tooltip, wtai_kses_allowed_html() ); ?>
									</div>
								</div>
								<div class="handle-actions hide-if-no-js"><a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a></div>
							</div>
							<div class="inside wtai-product-alt-images-inside-wrap">
								<div class="wtai-product-alt-images-main-wrap"></div>
							</div>
							<div class="wtai-image-popup">
								<div class="wtai-image-popup-content">
									<span class="wtai-btn-close-popup">&nbsp;</span>
									<div class="wtai-image-popup-inner"><img src="" /></div>
								</div>
							</div>			
						</div>

						<?php do_action( 'wtai_admin_mobile_footer' ); ?>
					</div>					
				</div>
			</div>
		</div>
	</div>

	<input type="hidden" class="wtai-keyword-analysis-view" id="wtai-keyword-analysis-view" value="0" />
	<input type="hidden" class="wtai-record-type" id="wtai-record-type" value="product" />
</form>

