<?php
/**
 * Product keyword metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
$max_keyword_length = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
?>
<div id="wtai-keywords-list" class="postbox">	
	<div class="postbox-header">
		<h2 class="hndle ui-sortable-handle">
			<span class="wtai-mb-headline wtai-mb-headline-has-featured-badge" >
				<span class="wtai-step-guideline wtai-step-guideline-1" ><?php echo wp_kses_post( __( 'Step 1', 'writetext-ai' ) ); ?></span>
				<span class="wtai-mb-headline-span-title" ><?php echo wp_kses_post( __( 'Keyword analysis', 'writetext-ai' ) ); ?></span>
				
				<?php do_action( 'wtai_product_single_premium_badge', 'wtai-premium-keyword' ); ?>
			</span>
		</h2>
		<div class="wtai-tooltip">
			<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
			<div class="wtai-tooltiptext">
				<div class="wtai-tooltip-arrow"></div>
				<?php
				echo '<p class="wtai-heading">' . wp_kses_post( __( 'Target keywords', 'writetext-ai' ) ) . '</p>';

				if ( 'category' === wtai_get_current_page_type() ) {
					/* translators: %s: number of keywrods that can be added */
					echo '<p>' . wp_kses_post( sprintf( __( 'Click the "Do keyword analysis" button to add up to %s target keywords for your category page. This will also open the dashboard where you can get keyword ideas as well as data on search volume and difficulty from Google and other search sources. If there are no target keywords added, WriteText.ai will generate text based on the category name and if selected, category image and representative products.', 'writetext-ai' ), $max_keyword_length ) ) . '</p>
						<p class="wtai-heading">' . wp_kses_post( __( 'Semantic keywords', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Semantic keywords are words or phrases that are closely related to a primary keyword or topic, which helps search engines and users understand the context and meaning of a piece of content. Using semantic keywords in your content can improve its relevance and quality. WriteText.ai automatically suggests semantic keywords related to your category name and target keywords. Click on the semantic keyword if you want it to be considered in generating your category texts.', 'writetext-ai' ) ) . '</p>

						<p class="wtai-heading">' . wp_kses_post( __( 'Keyword density', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Keyword density refers to the percentage of times your target keyword or semantic keyword appears throughout the page, in comparison to the total number of words in it. In WriteText.ai, we only take into account the text visible on your page — which is the category description. This means that the meta title, meta description, and Open Graph text are not counted. You can see the keyword density percentage displayed next to every keyword in this section.', 'writetext-ai' ) ) . '</p>';
				} else {
					/* translators: %s: number of keywrods that can be added */
					echo '<p>' . wp_kses_post( sprintf( __( 'Click the "Do keyword analysis" button to add up to %s target keywords for your product page. This will also open the dashboard where you can get keyword ideas as well as data on search volume and difficulty from Google and other search sources. If there are no target keywords added, WriteText.ai will generate text based on the product name and selected product attributes.', 'writetext-ai' ), $max_keyword_length ) ) . '</p>
						<p class="wtai-heading">' . wp_kses_post( __( 'Semantic keywords', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Semantic keywords are words or phrases that are closely related to a primary keyword or topic, which helps search engines and users understand the context and meaning of a piece of content. Using semantic keywords in your content can improve its relevance and quality. WriteText.ai automatically suggests semantic keywords related to your product name and target keywords. Click on the semantic keyword if you want it to be considered in generating your product texts.', 'writetext-ai' ) ) . '</p>

						<p class="wtai-heading">' . wp_kses_post( __( 'Keyword density', 'writetext-ai' ) ) . '</p>
						<p>' . wp_kses_post( __( 'Keyword density refers to the percentage of times your target keyword or semantic keyword appears throughout the page, in comparison to the total number of words in it. In WriteText.ai, we only take into account the text visible on your page — which are the product description or the product short description. This means that the meta title, meta description, and Open Graph text are not counted. There may also be cases where you only use the product description in the page and not the product short description or vice versa. We advise you to leave the field empty for the text type you are not using so that you can get an accurate keyword density. You can see the keyword density percentage displayed next to every keyword in this section.', 'writetext-ai' ) ) . '</p>';
				}
				?>
			</div>	
		</div>
		<div class="handle-actions hide-if-no-js">
			<a type="button" class="handlediv" aria-expanded="false"><span class="toggle-indicator" aria-hidden="true"></span></a>
		</div>
	</div>
	
	<?php
	$global_rule_fields         = apply_filters( 'wtai_global_rule_fields', array() );
	$max_keyword_count          = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
	$max_semantic_keyword_count = isset( $global_rule_fields['maxSemanticKeywords'] ) ? $global_rule_fields['maxSemanticKeywords'] : 0;
	?>
	<div class="inside wtai-keyword-analysis-options-wrap <?php echo WTAI_PREMIUM ? '' : 'wtai-disable-premium-feature'; ?>" >
		<div class="wtai-col-row-wrapper">
			<div class="wtai-col-left-wrapper ">
				<div class=" wtai-target-keywords-wrapper">
					<div class="wtai-target-keywords-wtai-header-wrapper">
						<span onClick="wtaiGetKeywordPopin(this);" class="button button-primary wtai-keyword-analysis-button disabled"><?php echo wp_kses_post( __( 'Do keyword analysis', 'writetext-ai' ) ); ?></span>
					</div>

					<div class="wtai-target-keywords-main-list-wrapper" >
						<div class="wtai-target-keywords-main-list-left-wrapper" >
							<span class="wtai-hdg"><?php echo wp_kses_post( __( 'Target keywords', 'writetext-ai' ) ); ?> <span class="wtai-keyword-max-count-wrap wtai-keyword-max-count-wrap-left">(<span class="wtai-keyword-count">0</span>/<span class="wtai-keyword-max-count"><?php echo wp_kses_post( $max_keyword_count ); ?></span>)</span></span>
						</div>
						<div class="wtai-target-keywords-main-list-right-wrapper" >
							<div class="wtai-target-wtai-keywords-list-wrapper wtai-post-data" data-postfield="keywords"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="wtai-col-right-wrapper wtai-semantic-keywords-wrapper">
				<div class="wtai-semantic-keywords-wrapper-wtai-header-wrapper">
					<span class="wtai-header-title wtai-col-wide">
						<span class="wtai-mb-headline" >
							<span class="wtai-step-guideline wtai-step-guideline-2" ><?php echo wp_kses_post( __( 'Step 2', 'writetext-ai' ) ); ?></span>
							
							<span><?php echo wp_kses_post( __( 'Select semantic keywords', 'writetext-ai' ) ); ?> </span>

							<?php if ( $max_semantic_keyword_count > 0 ) { ?>
								<span class="wtai-semantic-keyword-counter-wrap" title="<?php echo wp_kses_post( __( 'You have selected the maximum number of semantic keywords.', 'writetext-ai' ) ); ?>" >
									(<span class="wtai-active-count">0</span>/<span class="wtai-max-count"><?php echo wp_kses_post( $max_semantic_keyword_count ); ?></span>)
								</span>
							<?php } ?>
						</span>
					</span>
				</div>
				<div class="wtai-semantic-keywords-wrapper-list-wrapper" data-postfield="keyword_semantic">
					<div class="wtai-semantic-keywords-wrapper-list">
						<div class="wtai-header-label  wtai-post-data-json" data-postfield="post_title"></div>
						<div class="wtai-semantic-list wtai-post-data wtai-product-title-semantic-list" data-postfield="product_title_semantic"></div>
					</div>
					<div class="wtai-data-semantic-keywords-wrapper-list-wrapper wtai-post-data" data-postfield="keyword_semantic"></div>
				</div>
				<div class="wtai-target-keywords-density-wrapper">
					<div class="wtai-target-keywords-highlight-wrapper">
						<label class="wtai-highlight-cb-wrap" for="wtai-highlight">
							<input type="checkbox" name="wtai_highlight" id="wtai-highlight" data-postfield="wtai_highlight" class="wtai-post-data wtai-highlight-check wtai-keywords-highlight disabled" value="1"><?php echo wp_kses_post( __( 'Highlight keywords and semantic keywords', 'writetext-ai' ) ); ?>
						</label>

						<label class="wtai-highlight-premium-dummy-cb-wrap" for="wtai_highlight_dummy_cb" style="display: none" >
							<input type="checkbox" name="wtai_highlight_dummy_cb" id="wtai_highlight_dummy_cb" class="wtai-highlight-premium-dummy-cb" value="1" ><?php echo wp_kses_post( __( 'Highlight keywords and semantic keywords', 'writetext-ai' ) ); ?>
						</label>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>