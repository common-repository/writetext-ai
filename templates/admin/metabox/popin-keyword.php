<?php
/**
 * Product single keyword ideas template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;

$credit_array         = apply_filters( 'wtai_get_credits_count', array() );
$credit_keyword_count = isset( $credit_array['keywordAnalysis'] ) ? intval( $credit_array['keywordAnalysis'] ) : 0;

$difficulty_filters = array(
	'LOW'    => __( 'LOW', 'writetext-ai' ),
	'MEDIUM' => __( 'MEDIUM', 'writetext-ai' ),
	'HIGH'   => __( 'HIGH', 'writetext-ai' ),
);
$difficulty_sorting = array(
	'low'  => 'low', // Internal label not used, no need to translate.
	'high' => 'high', // Internal label not used, no need to translate.
);

$volume_filters = array(
	'all'         => __( 'All', 'writetext-ai' ),
	'50001'       => __( '50,001 and over', 'writetext-ai' ),
	'10001-50000' => __( '10,001 - 50,000', 'writetext-ai' ),
	'0-10000'     => __( '0 - 10,000', 'writetext-ai' ),
);

$volume_sorting = array(
	'desc' => 'desc', // Internal label not used, no need to translate.
	'asc'  => 'asc', // Internal label not used, no need to translate.
);

$volume_filter_selected      = 'all';
$volume_sort_selected        = 'desc';
$difficulty_sort_selected    = 'low';
$difficulty_filter_selected  = array_keys( $difficulty_filters );
$keyword_ideas_sort_selected = 'asc';
$keyword_ideas_sorting       = array();

$sort_field    = '';
$sort_asc_desc = '';
if ( $keyword_ideas_sorting ) {
	$sort_field    = $keyword_ideas_sorting[0];
	$sort_asc_desc = $keyword_ideas_sorting[1];

	if ( 'keyword' === $sort_field ) {
		$keyword_ideas_sort_selected = $sort_asc_desc;
	}
	if ( 'search_volume' === $sort_field ) {
		$volume_sort_selected = $sort_asc_desc;
	}
	if ( 'competition_index' === $sort_field ) {
		if ( 'asc' === $sort_asc_desc ) {
			$difficulty_sort_selected = 'low';
		}
		if ( 'desc' === $sort_asc_desc ) {
			$difficulty_sort_selected = 'high';
		}
	}
}
?>
<div class="wtai-keyword wtai-keyword-single wtai-keyword-analysis-popin-right">
	<div class="wtai-d-inner-wrapper">	<!-- inner wrapper -->
		<div class="wtai-keyword-header">
			<div class="wtai-keyword-title">
				<?php echo wp_kses_post( __( 'Keyword analysis', 'writetext-ai' ) ); ?>

				<div class="wtai-keyword-lang-country-info-wrap" >
					<div class="wtai-language-locale">
						<div class="wtai-label"><?php echo wp_kses_post( __( 'Language', 'writetext-ai' ) ); ?></div>
						<div class="wtai-value wtai-post-data keyword-language-post-data" data-postfield="language"></div>
					</div>
					<span class="wtai-language-locale-sep" ><span class="wtai-dot" ></span></span>
					<div class="wtai-language-locale">
						<div class="wtai-label"><?php echo wp_kses_post( __( 'Country', 'writetext-ai' ) ); ?></div>
						<div class="wtai-value wtai-post-data wtai-keyword-country-post-data" data-postfield="country"></div>
					</div>
				</div>
			</div>
			
			<div class="wtai-keyword-filter-header">
				<input type="button" class="button button-primary wtai-start-ai-analysis-btn" value="<?php echo wp_kses_post( __( 'Start AI-powered keyword analysis', 'writetext-ai' ) ); ?>" />
				<input type="hidden" id="wtai-analysis-data-available-flag" value="0" />
				<div class="wtai-keyword-tooltip wtai-keyword-cta-tooltip wtai-tooltip">
					<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
					<div class="wtai-tooltiptext">
						<div class="wtai-tooltip-arrow"></div>
						<?php
						echo wp_kses_post( __( 'Click "Start AI-powered Keyword Analysis" to automatically enhance your keyword strategy. WriteText.ai will retrieve available keyword data for applicable sections. Check the help text for each section or watch this explainer video to learn more.', 'writetext-ai' ) );
						?>
					</div>	
				</div>
			</div>

			<input type="hidden" class="wtai-post-data wtai-keyword-location-code" data-postfield="keyword_country" value="" />

		</div>

		<div class="wtai-keyword-analysis-content-bottom-section" >
		
			<div class="wtai-keyword-analysis-progress-loader-overlay" ></div>

			<div class="wtai-keyword-analysis-progress-loader" data-progress="0" data-max-progress="10" >
				<div class="wtai-keyword-analysis-progress-loader-content-wrap" >
					<div class="wtai-bulk-generate-check-ico-wrap">
						<span class="wtai-bulkgenerate-check-ico"></span>
					</div>
					<div class="wtai-keyword-analysis-progress-loader-content">
						<div class="wtai-keyword-analysis-progress-loader-text" ><?php echo wp_kses_post( __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ) ); ?></div>
						<div class="wtai-loading-loader-msg-wrapper">	
							<div class="wtai-loading-loader-wrapper">
								<div class="wtai-main-loading" style=""></div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- START selected keyword section -->
			<div class="wtai-keyword-analysis-content-wrap wtai-selected-keywords wtai-has-competitive-analysis">
				<div class="wtai-keyword-analysis-content" >
					<div class="wtai-keyword-analysis-content-header" >
						<div class="wtai-keyword-analysis-content-title" >
							<span class="wtai-keyword-title-label" ><?php echo wp_kses_post( __( 'Keywords to be included in your text', 'writetext-ai' ) ); ?></span>
							<div class="wtai-keyword-tooltip wtai-tooltip">
								<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<?php
									echo '<p>' . wp_kses_post( __( 'If you have selected any, WriteText.ai will retrieve the SERP data for each keyword (which you can preview upon clicking the keyword) along with intent, search volume, and difficulty.', 'writetext-ai' ) ) . '</p>';
									do_action( 'wtai_render_intent_tooltip' );
									?>
								</div>	
							</div>
						</div>

						<div class="wtai-keyword-analysis-content-right" >
							<div class="wtai-keyword-analysis-refresh-cta-wrap hidden" >
								<a href="#" class="wtai-keyword-analysis-refresh-cta" data-type="selected-keywords" ><span class="wtai-refresh-ico" ></span><?php echo wp_kses_post( __( 'Refresh data', 'writetext-ai' ) ); ?></a>
							</div>
							<div class="wtai-keyword-analysis-counter-wrap" >
								<span class="wtai-keyword-max-count-wrap wtai-keyword-max-count-wrap-popin">(<span class="wtai-keyword-count">0</span>/<span class="wtai-keyword-max-count"><?php echo wp_kses_post( $max_keyword_count ); ?></span>)</span>
							</div>
						</div>
					</div>

					<div class="wtai-keyword-analysis-content-data" >
						<div class="wtai-keyword-analysis-progress-loader-mini" data-progress="0" data-max-progress="8" >
							<div class="wtai-keyword-analysis-progress-loader-content-wrap" >
								<div class="wtai-bulk-generate-check-ico-wrap">
									<span class="wtai-bulkgenerate-check-ico"></span>
								</div>
								<div class="wtai-keyword-analysis-progress-loader-content">
									<div class="wtai-keyword-analysis-progress-loader-text" ><?php echo wp_kses_post( __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ) ); ?></div>
									<div class="wtai-loading-loader-msg-wrapper">	
										<div class="wtai-loading-loader-wrapper">
											<div class="wtai-main-loading" style=""></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="wtai-keyword-analysis-api-loader" ></div>
						<div class="wtai-keyword-analysis-api-data hidden" ></div>
						<div class="wtai-keyword-analysis-empty-label hidden" >
							<?php echo wp_kses_post( __( 'Keywords you select to be included in your text will be displayed here.', 'writetext-ai' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<!-- END selected keywords section -->

			<!-- START keywords your currently ranking on section -->
			<div class="wtai-keyword-analysis-content-wrap wtai-current-rank-keywords">
				<div class="wtai-keyword-analysis-content" >
					<div class="wtai-keyword-analysis-content-header" >
						<div class="wtai-keyword-analysis-content-title wtai-has-toggle" >
							<span class="wtai-keyword-title-icon star" ></span>
							<span class="wtai-keyword-title-label" ><?php echo wp_kses_post( __( "Keywords you're currently ranking for", 'writetext-ai' ) ); ?></span>
							<div class="wtai-keyword-tooltip wtai-tooltip">
								<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<?php
									if ( 'category' === wtai_get_current_page_type() ) {
										echo '<p>' . wp_kses_post( __( 'If it’s the first time you’re doing an AI-powered keyword analysis on the site, WriteText.ai will retrieve ranking data for the whole domain (i.e., this page and your other category pages as well as product pages). If your page is ranking for any keyword/s, they will show here along with data on intent, search volume, and difficulty.', 'writetext-ai' ) ) . '</p>';
									} else {
										echo '<p>' . wp_kses_post( __( 'If it’s the first time you’re doing an AI-powered keyword analysis on the site, WriteText.ai will retrieve ranking data for the whole domain (i.e., this page and your other product pages as well as category pages). If your page is ranking for any keyword/s, they will show here along with data on intent, search volume, and difficulty.', 'writetext-ai' ) ) . '</p>';
									}

									do_action( 'wtai_render_intent_tooltip' );
									?>
								</div>	
							</div>
						</div>

						<div class="wtai-keyword-analysis-content-right" >
							<div class="wtai-keyword-analysis-toggle-wrap" >
								<span class="wtai-keyword-analysis-toggle" data-state='shown' ></span>
							</div>
						</div>
					</div>

					<div class="wtai-keyword-analysis-content-data" >
						<div class="wtai-keyword-analysis-api-loader" ></div>
						<div class="wtai-keyword-analysis-api-data hidden" ></div>
						<div class="wtai-keyword-analysis-empty-label hidden" ><?php echo wp_kses_post( __( 'Click the "Start AI-powered keyword analysis" button to get started.', 'writetext-ai' ) ); ?></div>
					</div>
				</div>
			</div>
			<!-- END keywords your currently ranking on section -->

			<!-- START keywords your competitors are ranking on section -->
			<div class="wtai-keyword-analysis-content-wrap wtai-competitor-keywords wtai-has-competitive-analysis">
				<div class="wtai-keyword-analysis-content" >
					<div class="wtai-keyword-analysis-content-header" >
						<div class="wtai-keyword-analysis-content-title wtai-has-toggle" >
							<span class="wtai-keyword-title-icon rank" ></span>
							<span class="wtai-keyword-title-label" ><?php echo wp_kses_post( __( 'Keywords your competitors are ranking for', 'writetext-ai' ) ); ?></span>
							<div class="wtai-keyword-tooltip wtai-tooltip">
								<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
								<div class="wtai-tooltiptext">
									<div class="wtai-tooltip-arrow"></div>
									<?php
									if ( 'category' === wtai_get_current_page_type() ) {
										echo '<p>' . wp_kses_post( __( 'Competitors are pages that are ranking for either the same keywords you’re currently ranking for or the keywords you selected to be included in your text. You can check which competitors are ranking for which keyword by clicking on the keyword. The keywords shown in this section are ones that your competing pages are ranking for but this category page is not. Data on search volume, intent, and difficulty are also displayed here.', 'writetext-ai' ) ) . '</p>';
									} else {
										echo '<p>' . wp_kses_post( __( 'Competitors are pages that are ranking for either the same keywords you’re currently ranking for or the keywords you selected to be included in your text. You can check which competitors are ranking for which keyword by clicking on the keyword. The keywords shown in this section are ones that your competing pages are ranking for but this product page is not. Data on search volume, intent, and difficulty are also displayed here.', 'writetext-ai' ) ) . '</p>';
									}
									do_action( 'wtai_render_intent_tooltip' );
									?>
								</div>	
							</div>
						</div>

						<div class="wtai-keyword-analysis-content-right" >
							<div class="wtai-keyword-analysis-refresh-cta-wrap hidden" >
								<a href="#" class="wtai-keyword-analysis-refresh-cta" data-type="competitor-keywords" ><span class="wtai-refresh-ico" ></span><?php echo wp_kses_post( __( 'Refresh data', 'writetext-ai' ) ); ?></a>
							</div>
							<div class="wtai-keyword-analysis-toggle-wrap" >
								<span class="wtai-keyword-analysis-toggle" data-state='shown' ></span>
							</div>
						</div>
					</div>

					<div class="wtai-keyword-analysis-content-data" >
						<div class="wtai-keyword-analysis-progress-loader-mini" data-progress="0" data-max-progress="8" >
							<div class="wtai-keyword-analysis-progress-loader-content-wrap" >
								<div class="wtai-bulk-generate-check-ico-wrap">
									<span class="wtai-bulkgenerate-check-ico"></span>
								</div>
								<div class="wtai-keyword-analysis-progress-loader-content">
									<div class="wtai-keyword-analysis-progress-loader-text" ><?php echo wp_kses_post( __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ) ); ?></div>
									<div class="wtai-loading-loader-msg-wrapper">	
										<div class="wtai-loading-loader-wrapper">
											<div class="wtai-main-loading" style=""></div>
										</div>
									</div>
								</div>
							</div>
						</div>

						<div class="wtai-keyword-analysis-api-loader" ></div>
						<div class="wtai-keyword-analysis-api-data hidden" ></div>
						<div class="wtai-keyword-analysis-empty-label hidden" >
							<?php echo wp_kses_post( __( 'Click the “Start AI-powered keyword analysis” button to get started. If there are no keywords you are currently ranking for or selected keywords to be included in your text, WriteText.ai will search for possible competitors you may have based on your product name.', 'writetext-ai' ) ); ?>
						</div>
					</div>
				</div>
			</div>
			<!-- END keywords your competitors are ranking on section -->

			<!-- Retain this wrapper for backward compatibility of previous CSS version class -->
			<div class="wtai-keyword-content wtai-keyword-content-bw-wrap">
				<!-- START keywords your currently ranking on section -->
				<div class="wtai-keyword-analysis-content-wrap wtai-your-keywords wtai-keyword-ideas-group">
					<div class="wtai-keyword-analysis-content" >
						<div class="wtai-keyword-analysis-content-header" >
							<div class="wtai-keyword-analysis-content-title wtai-has-toggle" >
								<span class="wtai-keyword-title-label" ><?php echo wp_kses_post( __( 'Your own keywords (optional)', 'writetext-ai' ) ); ?></span>
								<div class="wtai-keyword-tooltip wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext">
										<div class="wtai-tooltip-arrow"></div>
										<?php
										echo '<p>' . wp_kses_post( __( 'If you want to know the search volume and difficulty data for a certain keyword, manually type it here and click “Start AI-powered keyword analysis” at the top to retrieve data.', 'writetext-ai' ) ) . '</p>';
										echo '<p>' . wp_kses_post( __( 'You can also manually add a keyword to be included in your text by typing it here and clicking the + sign.', 'writetext-ai' ) ) . '</p>';
										?>
									</div>	
								</div>
							</div>

							<div class="wtai-keyword-analysis-content-right" >
								<div class="wtai-keyword-analysis-refresh-cta-wrap hidden" >
									<a href="#" class="wtai-keyword-analysis-refresh-cta" data-type="your-keywords" ><span class="wtai-refresh-ico" ></span><?php echo wp_kses_post( __( 'Refresh data', 'writetext-ai' ) ); ?></a>
								</div>
								<div class="wtai-keyword-analysis-toggle-wrap" >
									<span class="wtai-keyword-analysis-toggle" data-state='shown' ></span>
								</div>
							</div>
						</div>

						<div class="wtai-keyword-analysis-content-data" >
							<div class="wtai-keyword-filter-wrapper">
								<?php
								/* translators: %s: Max keyword length */
								$max_keyword_tooltip = wp_kses_post( sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one.', 'writetext-ai' ), $max_manual_keyword_count ) );
								?>
								<div class="wtai-keyword-input-filter-wrap wtai-char-count-parent-wrap" >
									<input type="hidden" value="<?php echo esc_attr( wp_unslash( $max_manual_keyword_count ) ); ?>" name="max_keywords" id="maxnum_keywords" />
									<input type="type" class="wtai-keyword-input keyword_input wtai-max-length-field" data-postfield="keyword_input" 
										data-maxtext="<?php echo esc_attr( $global_rule_fields['maxKeywordLength'] ); ?>" 
										maxlength="<?php echo esc_attr( $global_rule_fields['maxKeywordLength'] ); ?>"
										placeholder="<?php echo wp_kses_post( __( 'Add your own keyword here...', 'writetext-ai' ) ); ?>" 
										title="<?php echo wp_kses_post( $max_keyword_tooltip ); ?>" 
										/>

									<div class="wtai-keyword-input-bottom-wrap">
										<div class="wtai-keyword-input-label-wrap">
											<span class="wtai-keyword-input-label-subtext" >
												<?php
												/* translators: %s: Max keyword length */
												echo wp_kses_post( sprintf( __( 'Enter up to %s keywords. Press [ENTER] or use a comma to separate each keyword.', 'writetext-ai' ), $max_manual_keyword_count ) );
												?>
											</span>
										</div>
										<div class="wtai-char-count-wrap">
											<span class="wtai-char-count">0</span>/<span class="wtai-max-count"><?php echo wp_kses_post( $global_rule_fields['maxKeywordLength'] ); ?></span><?php echo wp_kses_post( __( ' Char', 'writetext-ai' ) ); ?>
										</div>
									</div>
								</div>
							</div>

							<div class="wtai-keyword-analysis-progress-loader-mini" data-progress="0" data-max-progress="2" >
								<div class="wtai-keyword-analysis-progress-loader-content-wrap" >
									<div class="wtai-bulk-generate-check-ico-wrap">
										<span class="wtai-bulkgenerate-check-ico"></span>
									</div>
									<div class="wtai-keyword-analysis-progress-loader-content">
										<div class="wtai-keyword-analysis-progress-loader-text" ><?php echo wp_kses_post( __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ) ); ?></div>
										<div class="wtai-loading-loader-msg-wrapper">	
											<div class="wtai-loading-loader-wrapper">
												<div class="wtai-main-loading" style=""></div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="wtai-keyword-analysis-api-loader" ></div>
							<div class="wtai-keyword-analysis-api-data hidden" ></div>
							<div class="wtai-keyword-analysis-empty-label hidden" ><?php echo wp_kses_post( __( 'Your manually entered keywords will display here.', 'writetext-ai' ) ); ?></div>							
						</div>
					</div>
				</div>
				<!-- END your keywords section -->

				<!-- START keywords your currently ranking on section -->
				<div class="wtai-keyword-analysis-content-wrap wtai-suggested-keywords wtai-keyword-ideas-group">
					<div class="wtai-keyword-analysis-content" >
						<div class="wtai-keyword-analysis-content-header" >
							<div class="wtai-keyword-analysis-content-title wtai-has-toggle" >
								<span class="wtai-keyword-title-label" ><?php echo wp_kses_post( __( 'Suggested keywords', 'writetext-ai' ) ); ?></span>
								<div class="wtai-keyword-tooltip wtai-tooltip">
									<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
									<div class="wtai-tooltiptext">
										<div class="wtai-tooltip-arrow"></div>
										<?php
										echo wp_kses_post( __( 'Keyword ideas based on the keywords you selected to be included in the text or your own manually-added keywords.', 'writetext-ai' ) );
										?>
									</div>	
								</div>
							</div>

							<div class="wtai-keyword-analysis-content-right" >
								<div class="wtai-keyword-analysis-refresh-cta-wrap hidden" >
									<a href="#" class="wtai-keyword-analysis-refresh-cta" data-type="suggested-keywords" ><span class="wtai-refresh-ico" ></span><?php echo wp_kses_post( __( 'Refresh data', 'writetext-ai' ) ); ?></a>
								</div>
								<div class="wtai-keyword-analysis-toggle-wrap" >
									<span class="wtai-keyword-analysis-toggle" data-state='shown' ></span>
								</div>
							</div>
						</div>

						<div class="wtai-keyword-analysis-content-data" >
							<div class="wtai-keyword-analysis-progress-loader-mini" data-progress="0" data-max-progress="2" >
								<div class="wtai-keyword-analysis-progress-loader-content-wrap" >
									<div class="wtai-bulk-generate-check-ico-wrap">
										<span class="wtai-bulkgenerate-check-ico"></span>
									</div>
									<div class="wtai-keyword-analysis-progress-loader-content">
										<div class="wtai-keyword-analysis-progress-loader-text" ><?php echo wp_kses_post( __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ) ); ?></div>
										<div class="wtai-loading-loader-msg-wrapper">	
											<div class="wtai-loading-loader-wrapper">
												<div class="wtai-main-loading" style=""></div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="wtai-keyword-analysis-api-loader" ></div>
							<div class="wtai-keyword-analysis-api-data hidden" ></div>
							<div class="wtai-keyword-analysis-empty-label hidden" >
								<?php echo wp_kses_post( __( 'Click the “Start AI-powered keyword analysis” button to get data for your manually typed keywords (keyword ideas, search volume, and difficulty).', 'writetext-ai' ) ); ?>
							</div>							

							<input type="hidden" id="wtai-keyword-ideas-filter-sort-triggered" value="0" />
							<input type="hidden" id="wtai-keyword-ideas-last-sort-selected" value="<?php echo $keyword_ideas_sorting ? implode( ':', wp_kses_post( $keyword_ideas_sorting ) ) : ''; ?>" />
						</div>
					</div>
				</div>
			</div>	

			<div class="wtai-keyword-footer-spacer" >&nbsp;</div>
		</div> <!-- bottom section wrapper -->
	</div>	<!-- inner wrapper -->
</div>

