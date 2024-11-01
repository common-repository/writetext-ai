<?php
/**
 * Suggested keywords template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$items_per_first_page = defined( 'WTAI_KEYWORDS_MAX_ITEMS_PER_PAGE' ) ? WTAI_KEYWORDS_MAX_ITEMS_PER_PAGE : 5; // Number of items on the first page.

$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
$max_keyword_count  = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;

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

$sort_filter_data           = wtai_get_keyword_analysis_sort_filter( $record_id, 'suggested', $record_type );
$sort_type_selected         = isset( $sort_filter_data['sort_type'] ) ? $sort_filter_data['sort_type'] : 'relevance';
$sort_direction_selected    = isset( $sort_filter_data['sort_direction'] ) ? $sort_filter_data['sort_direction'] : 'asc';
$volume_filter_selected     = isset( $sort_filter_data['volume_filter'] ) ? $sort_filter_data['volume_filter'] : 'all';
$difficulty_filter_selected = isset( $sort_filter_data['difficulty_filter'] ) ? $sort_filter_data['difficulty_filter'] : array_keys( $difficulty_filters );

if ( ! $difficulty_filter_selected ) {
	$difficulty_filter_selected = array_keys( $difficulty_filters );
}

if ( ! $volume_filter_selected ) {
	$volume_filter_selected = 'all';
}

$has_custom_filter = false;
if ( 'all' !== $volume_filter_selected || count( $difficulty_filter_selected ) < 3 ) {
	$has_custom_filter = true;
}

$disabled_add_class = '';
if ( count( $target_keywords ) >= $max_keyword_count ) {
	$disabled_add_class = ' disabled wtai-not-allowed ';
}

/* translators: %s: Max keyword length */
$add_disabled_tooltip = sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one to the "Keywords to be included in your text".', 'writetext-ai' ), $max_keyword_count );

?>
<div class="wtai-keyword-main-list-data-wrap" >
	<div class="wtai-keyword-table-parent-wrap wtai-has-filter-wrap" >
		<table class="wtai-keyword-table wtai-keyword-table-suggested-keywords wtai-has-filter" 
			data-keyword-type="suggested" data-sort-field="<?php echo esc_attr( $sort_type_selected ); ?>" 
			data-sort="<?php echo esc_attr( $sort_direction_selected ); ?>" >
			<thead>
				<tr class="wtai-border-bottom">
					<th class="wtai-col-keyword wtai-col-sticky-mobile">
						<div class="wtai-sort-ideas-btn" >
							<div class="wtai-sort-ideas-select wtai-sort-ideas-select-keyword" 
								data-type="relevance" >
								<span><?php echo wp_kses_post( __( 'Keyword ideas', 'writetext-ai' ) ); ?></span>
								<span class="wtai-keyword-by-relevance-label" style="<?php echo ( 'relevance' === $sort_type_selected ) ? '' : 'display: none;'; ?>" ><?php echo wp_kses_post( __( '(by relevance)', 'writetext-ai' ) ); ?></span>
							</div>
						</div>
					</th>
					<th class="wtai-col-volume wtai-col-normal-mobile">
						<div class="wtai-sort-ideas-btn wtai-sort-style2" >
							<div class="wtai-sort-ideas-select wtai-sort-ideas-select-volume wtai-hover-sorting <?php echo ( 'volume' === $sort_type_selected ) ? 'wtai-active-sort' : ''; ?>" data-type="volume" >
								<span class="wtai-lbl"><?php echo wp_kses_post( __( 'Search vol.', 'writetext-ai' ) ); ?></span>
					
							</div>
						</div>
					</th>
					<th class="wtai-col-difficulty wtai-col-normal-mobile">
						<div class="wtai-sort-ideas-btn wtai-sort-style2" >
							<div class="wtai-sort-ideas-select wtai-sort-ideas-select-difficulty wtai-hover-sorting <?php echo ( 'difficulty' === $sort_type_selected ) ? 'wtai-active-sort' : ''; ?>" data-type="difficulty" >
								<span class="wtai-lbl"><?php echo wp_kses_post( __( 'Difficulty', 'writetext-ai' ) ); ?></span>
				
							</div>
						</div>
					</th>
					<th class="wtai-col-action wtai-col-normal-mobile">
						<div class="wtai-sort-volume-difficulty-select wtai-sort-ideas-select-volume_difficulty" data-type="volume_difficulty" >
							<span class="wtai-volume-difficulty-ico wtai-ico-style3 <?php echo $has_custom_filter ? 'wtai-active' : ''; ?>" ></span>
							<div class="wtai-volume-difficulty-dropdown">
								<div class="wtai-sort-idea-filter-wrap wtai-sort-idea-filter-difficulty-wrap" data-type="difficulty" >
									<div class="wtai-difficulty-filter-wrap" >
										<label class="wtai-sort-idea-option-label" ><?php echo wp_kses_post( __( 'Difficulty', 'writetext-ai' ) ); ?></label>
										<div class="wtai-difficulty-filter-options wtai-sort-idea-options" >
											
											<?php
											foreach ( $difficulty_filters as $filter_id => $filter_label ) {
												$cb_checked = ( in_array( $filter_id, $difficulty_filter_selected, true ) ) ? 'checked' : '';
												echo '<div>
														<label>
															<input type="checkbox" ' . esc_attr( $cb_checked ) . ' id="wtai-difficulty-filter-' . esc_attr( $filter_id ) . '" name="wtai_difficulty_filter_suggested[]" class="wtai-sort-idea-filter-input wtai-difficulty-filter wtai-difficulty-filter-' . esc_attr( $filter_id ) . '" value="' . esc_attr( wp_unslash( $filter_id ) ) . '" /> ' . wp_kses_post( $filter_label ) . '
														</label>
														</div>';
											}
											?>
										</div>
									</div>
								</div>
								<div class="wtai-sort-idea-filter-wrap wtai-sort-idea-filter-volume-wrap" data-type="volume" >
									<div class="wtai-volume-filter-wrap" >
										<label class="wtai-sort-idea-option-label" ><?php echo wp_kses_post( __( 'Search volume', 'writetext-ai' ) ); ?></label>
										<div class="wtai-volume-filter-options wtai-sort-idea-options" >
											<?php
											foreach ( $volume_filters as $filter_id => $filter_label ) {
												$cb_checked = ( $filter_id === $volume_filter_selected ) ? 'checked' : '';
												echo '<div>
															<label>
																<input type="radio" ' . esc_attr( $cb_checked ) . ' id="wtai-volume-filter-' . esc_attr( $filter_id ) . '" name="volume_filter_suggested" class="wtai-sort-idea-filter-input wtai-volume-filter wtai-volume-filter-' . esc_attr( $filter_id ) . '" value="' . esc_attr( wp_unslash( $filter_id ) ) . '" /> ' . wp_kses_post( $filter_label ) . '
															</label>
														</div>';
											}
											?>
										</div>
									</div>
								</div>			
								<div style="display:none">
									<?php
									foreach ( $volume_sorting as $sort_id => $sort_label ) {
										$cb_checked = ( $sort_id === $volume_sort_selected ) ? 'checked' : '';
										echo '<input type="radio" ' . esc_attr( $cb_checked ) . ' id="volume-sort-' . esc_attr( $sort_id ) . '" name="volume_sort" class="wtai-sort-idea-filter-input volume-sort volume-sort-' . esc_attr( $sort_id ) . '" value="' . esc_attr( wp_unslash( $sort_id ) ) . '" style="display:none;"/>';
									}
									foreach ( $difficulty_sorting as $sort_id => $sort_label ) {
										$cb_checked = ( $sort_id === $difficulty_sort_selected ) ? 'checked' : '';
										echo '<input type="radio" ' . esc_attr( $cb_checked ) . ' id="difficulty-sort-' . esc_attr( $sort_id ) . '" name="difficulty_sort"  class="wtai-sort-idea-filter-input difficulty-sort difficulty-sort-' . esc_attr( $sort_id ) . '" value="' . esc_attr( wp_unslash( $sort_id ) ) . '" style="display:none;"/>';
									}
									?>
								</div>
							</div>
						</div>
					</th>
				</tr>
				
			</thead>
			<tbody class="wtai-post-data" data-postfield="keyword_suggested_table" >
				<?php
				$item_ctr = 1;
				foreach ( $keyword_ideas as $keyword_data ) {
					$keyword    = $keyword_data['keyword'];
					$search_vol = $keyword_data['search_volume'];
					$difficulty = $keyword_data['competition'];

					if ( '' === trim( $search_vol ) ) {
						$search_vol = '-';
					}

					if ( '' === trim( $difficulty ) ) {
						$difficulty = '-';
					}

					$difficulty_text = isset( $difficulty_filters[ $difficulty ] ) ? $difficulty_filters[ $difficulty ] : $difficulty;

					$tr_class = '';
					if ( 1 === $page_no && $item_ctr > $items_per_first_page ) {
						$tr_class .= ' wtai-keyword-tr-hidden ';
					}

					$keyword_added = false;
					if ( in_array( $keyword, $target_keywords, true ) ) {
						$tr_class .= ' wtai-tr-selected ';

						$keyword_added = true;
					}

					?>
					<tr class="wtai-has-data wtai-keyword-tr wtai-keyword-tr-<?php echo wp_kses_post( $item_ctr ); ?> <?php echo wp_kses_post( $tr_class ); ?>">
						<td class="wtai-col-keyword wtai-col-sticky-mobile"><span class="wtai-column-keyword-name-text" ><?php echo wp_kses_post( $keyword ); ?></span></td>
						<td class="wtai-col-volume wtai-col-normal-mobile"><?php echo wp_kses_post( $search_vol ); ?></td>
						<td class="wtai-col-difficulty wtai-col-normal-mobile"><?php echo wp_kses_post( $difficulty_text ); ?></td>
						<td class="wtai-col-action wtai-col-normal-mobile">
							<?php
							if ( $keyword_added ) {
								?>
								<span class="dashicons dashicons-minus wtai-keyword-action-button-v2" 
									data-keyword-type="suggested" 
									data-type="remove" 
									data-keyword="<?php echo wp_kses_post( $keyword ); ?>" 
									data-tooltip="<?php echo wp_kses_post( __( 'Remove as target keyword', 'writetext-ai' ) ); ?>" 
								></span>
								<?php
							} else {
								?>
								<span class="dashicons dashicons-plus-alt2 wtai-keyword-action-button-add wtai-keyword-action-button-v2 <?php echo esc_attr( $disabled_add_class ); ?>" 
									data-keyword-type="suggested" 
									data-type="add_to_selected" 
									data-keyword="<?php echo wp_kses_post( $keyword ); ?>" 
									data-tooltip-disabled="<?php echo wp_kses_post( $add_disabled_tooltip ); ?>" 
									data-tooltip="<?php echo wp_kses_post( __( 'Add as target keyword', 'writetext-ai' ) ); ?>" 
									></span>
								<?php
							}
							?>
						</td>
					</tr>
					<?php

					++$item_ctr;
				}
				?>
			</tbody>
		</table>
	</div>

	<?php
	$display_filtered_message       = false;
	$display_filtered_message_style = '';
	$display_no_more_message_style  = '';

	if ( count( $keyword_ideas ) < $items_per_page && 1 === $page_no ) {
		if ( $has_custom_filter ) {
			$display_filtered_message       = true;
			$display_filtered_message_style = 'display:block;';
		} else {
			$display_no_more_message_style = 'display:block;';
		}
	}


	if ( $page_no === $total_pages ) {
		$display_no_more_message_style = 'display:block;';
	}
	?>
	<div class="wtai-keyword-list-bottom-data">
		<div class="wtai-load-more-wrap" >
			<?php if ( $total_pages > 1 && $page_no < $total_pages && ! $display_filtered_message ) { ?> 
				<a href="#" class="wtai-load-more-cta" 
					data-items-per-page="<?php echo esc_attr( $items_per_page ); ?>" 
					data-total-records="<?php echo esc_attr( $total_items ); ?>" 
					data-total-pages="<?php echo esc_attr( $total_pages ); ?>" 
					data-keyword-type="suggested" 
					data-current-page-no="<?php echo esc_attr( $page_no ); ?>" 
					data-ideas-count="<?php echo esc_attr( count( $keyword_ideas ) ); ?>" 
					><?php echo wp_kses_post( __( 'Load more', 'writetext-ai' ) ); ?></a>
			<?php } ?>
		</div>

		<div class="wtai-keyword-ideas-no-more-data-wrap" style="<?php echo esc_attr( $display_no_more_message_style ); ?>" >
			<?php echo wp_kses_post( __( 'No more to show.', 'writetext-ai' ) ); ?>
		</div>

		<div class="wtai-keyword-ideas-no-more-data-custom-filter-wrap" style="<?php echo esc_attr( $display_filtered_message_style ); ?>" >
			<?php echo wp_kses_post( __( 'You have custom filters set up which might limit the keyword ideas you see in this list.', 'writetext-ai' ) ); ?>
		</div>
	</div>
</div>