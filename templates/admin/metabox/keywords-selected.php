<?php
/**
 * Selected keywords template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$difficulty_filters = array(
	'LOW'    => __( 'LOW', 'writetext-ai' ),
	'MEDIUM' => __( 'MEDIUM', 'writetext-ai' ),
	'HIGH'   => __( 'HIGH', 'writetext-ai' ),
);

$check_ranked = false;
?>
<div class="wtai-keyword-table-parent-wrap" >
	<table class="wtai-keyword-table wtai-keyword-table-selected-keywords wtai-has-rank-intent-data">
		<thead>
			<tr class="wtai-border-bottom">
				<th class="wtai-col-keyword wtai-col-sticky-mobile"><?php echo wp_kses_post( __( 'Keywords', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-rank wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Rank', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-intent wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Intent', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-volume wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Search vol.', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-difficulty wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Difficulty', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-action wtai-col-normal-mobile">
					&nbsp;
				</th>
			</tr>
		</thead>
		<tbody class="wtai-post-data wtai-keyword-tbody" data-postfield="keyword_selected_table" >
			<?php
			foreach ( $keywords as $keyword ) {
				$rank       = '-';
				$intent     = '-';
				$search_vol = '-';
				$difficulty = '-';

				$keyword_data_found = false;
				$serp_type          = '';
				$featured_serp_info = array();
				$spell              = '';
				// Get data from .keywords.
				if ( $keywords_statistics ) {
					foreach ( $keywords_statistics as $stats ) {
						if ( strtolower( $stats['keyword'] ) === strtolower( $keyword ) ) {
							$rank       = $stats['rank_group'];
							$intent     = $stats['intent'];
							$search_vol = $stats['search_volume'];
							$difficulty = $stats['competition'];
							$serp_infos = $stats['serp_infos'];
							$spell      = $stats['spell'];
							$serp_type  = 'ranked';

							if ( $serp_infos ) {
								$keyword_data_found = true;

								$featured_serp_info = wtai_get_serp_featured_snippet( $serp_infos );
							}
							break;
						}
					}
				}

				// Get data from .keywords.
				if ( $check_ranked && $ranked_keywords ) {
					foreach ( $ranked_keywords as $stats ) {
						if ( strtolower( $stats['keyword'] ) === strtolower( $keyword ) ) {
							if ( '' === trim( $rank ) ) {
								$rank = $stats['rank_group'];
							}

							if ( '' === trim( $intent ) ) {
								$intent = $stats['intent'];
							}

							if ( '' === trim( $search_vol ) ) {
								$search_vol = $stats['search_volume'];
							}

							if ( '' === trim( $difficulty ) ) {
								$difficulty = $stats['competition'];
							}

							break;
						}
					}
				}

				if ( '' === trim( $rank ) ) {
					if ( $keyword_data_found ) {
						$rank = __( 'No data', 'writetext-ai' );
					} else {
						$rank = '-';
					}
				}

				if ( '' === trim( $intent ) ) {
					if ( $keyword_data_found ) {
						$intent = __( 'No data', 'writetext-ai' );
					} else {
						$intent = '-';
					}
				}

				if ( '' === trim( $search_vol ) ) {
					if ( $keyword_data_found ) {
						$search_vol = __( 'No data', 'writetext-ai' );
					} else {
						$search_vol = '-';
					}
				}

				if ( '' === trim( $difficulty ) ) {
					if ( $keyword_data_found ) {
						$difficulty = __( 'No data', 'writetext-ai' );
					} else {
						$difficulty = '-';
					}
				}

				$difficulty_text = isset( $difficulty_filters[ $difficulty ] ) ? $difficulty_filters[ $difficulty ] : $difficulty;

				$top_serp_infos = wtai_get_top_serp_data( $serp_infos );

				// Get serp info popup.
				$serp_info_html     = '';
				$serp_tooltip_class = '';
				if ( $top_serp_infos ) {
					$serp_date = $rank_serp_date;
					if ( 'competitor' === $serp_type ) {
						$serp_date = $competitor_serp_date;
					}

					$serp_info_html = wtai_get_keyword_serp_html( $record_id, $keyword, $serp_type, $top_serp_infos, $serp_date, $featured_serp_info );

					if ( $serp_info_html ) {
						$serp_tooltip_class = ' wtai-column-keyword-name-tooltip wtai-tooltip ';
					}
				}

				?>
				<tr class="wtai-has-data wtai-keyword-tr">
					<td class="wtai-col-keyword wtai-col-sticky-mobile">
						<div class="wtai-column-keyword-name <?php echo esc_attr( $serp_tooltip_class ); ?>">
							<span class="wtai-column-keyword-name-text" ><?php echo wp_kses_post( $keyword ); ?></span>
							<?php
							if ( $serp_info_html ) {
								echo wp_kses_post( $serp_info_html );
							}
							?>
						</div>
						<?php
						if ( $spell && $spell !== $keyword ) {
							?>
							<div class="wtai-keyword-spellcheck-wrap" >
								<span class="wtai-keyword-spellcheck" >
									<?php
									/* translators: %s: suggested correct spelling */
									echo wp_kses_post( sprintf( __( 'Did you mean %s?', 'writetext-ai' ), '<span class="wtai-keyword-spellcheck-link" >' . $spell . '</span>' ) );
									?>
								</span>
							</div>
							<?php
						}
						?>
					</td>
					<td class="wtai-col-rank wtai-col-normal-mobile"><?php echo wp_kses_post( $rank ); ?></td>
					<td class="wtai-col-intent wtai-col-normal-mobile"><?php echo wp_kses_post( $intent ); ?></td>
					<td class="wtai-col-volume wtai-col-normal-mobile"><?php echo wp_kses_post( $search_vol ); ?></td>
					<td class="wtai-col-difficulty wtai-col-normal-mobile"><?php echo wp_kses_post( $difficulty_text ); ?></td>
					<td class="wtai-col-action wtai-col-normal-mobile">
						<span class="dashicons dashicons-minus wtai-keyword-action-button-v2" 
							data-keyword-type="selected" data-type="remove" data-keyword="<?php echo wp_kses_post( $keyword ); ?>"
							data-tooltip="<?php echo wp_kses_post( __( 'Remove as target keyword', 'writetext-ai' ) ); ?>" 
							></span>
					</td>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>
</div>