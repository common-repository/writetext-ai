<?php
/**
 * Selected keywords template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;
$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;

$disabled_add_class = '';
if ( count( $target_keywords ) >= $max_keyword_count ) {
	$disabled_add_class = ' disabled wtai-not-allowed ';
}

$difficulty_filters = array(
	'LOW'    => __( 'LOW', 'writetext-ai' ),
	'MEDIUM' => __( 'MEDIUM', 'writetext-ai' ),
	'HIGH'   => __( 'HIGH', 'writetext-ai' ),
);

$trash_disabled_tooltip = __( 'Remove this keyword from the "Keywords to be included in your text" before deleting it.', 'writetext-ai' );

/* translators: %s: Max keyword length */
$add_disabled_tooltip = sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one to the "Keywords to be included in your text".', 'writetext-ai' ), $max_keyword_count );
?>
<div class="wtai-keyword-table-parent-wrap" >
	<table class="wtai-keyword-table wtai-keyword-table-your-keywords">
		<thead>
			<tr class="wtai-border-bottom">
				<th class="wtai-col-trash">
					&nbsp;
				</th>
				<th class="wtai-col-keyword wtai-col-sticky-mobile"><?php echo wp_kses_post( __( 'Your own keywords', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-volume wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Search vol.', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-difficulty wtai-col-normal-mobile"><?php echo wp_kses_post( __( 'Difficulty', 'writetext-ai' ) ); ?></th>
				<th class="wtai-col-trash-mobile wtai-col-normal-mobile">
					&nbsp;
				</th>
				<th class="wtai-col-action wtai-col-normal-mobile">
					<span class="keyword-manual-max-count-wrap keyword-manual-max-count-wrap-popin">(<span class="wtai-keyword-count"><?php echo wp_kses_post( count( $keywords ) ); ?></span>/<span class="wtai-keyword-max-count"><?php echo wp_kses_post( $max_manual_keyword_count ); ?></span>)</span>
				</th>
			</tr>
		</thead>
		<tbody class="wtai-post-data" data-postfield="keyword_manual_table">
			<?php
			foreach ( $keywords as $keyword ) {
				$keyword = strtolower( $keyword );

				$search_vol = '-';
				$difficulty = '-';

				$keyword_data_found = false;
				if ( $keywords_statistics ) {
					foreach ( $keywords_statistics as $stats ) {
						if ( strtolower( $stats['keyword'] ) === strtolower( $keyword ) ) {
							$search_vol = $stats['search_volume'];
							$difficulty = $stats['competition'];

							$keyword_data_found = true;
							break;
						}
					}
				}

				if ( ! $keyword_data_found && $ranked_keywords ) {
					foreach ( $ranked_keywords as $stats ) {
						if ( strtolower( $stats['keyword'] ) === strtolower( $keyword ) ) {
							$search_vol = $stats['search_volume'];
							$difficulty = $stats['competition'];

							$keyword_data_found = true;
							break;
						}
					}
				}

				if ( ! $keyword_data_found && $competitor_keywords ) {
					foreach ( $competitor_keywords as $stats ) {
						if ( strtolower( $stats['keyword'] ) === strtolower( $keyword ) ) {
							$search_vol = $stats['search_volume'];
							$difficulty = $stats['competition'];

							$keyword_data_found = true;
							break;
						}
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

				$tr_class             = '';
				$keyword_added        = false;
				$disabled_trash_class = '';
				if ( in_array( $keyword, $target_keywords, true ) ) {
					$tr_class            .= ' wtai-tr-selected ';
					$disabled_trash_class = ' disabled wtai-not-allowed ';
					$keyword_added        = true;
				}

				$difficulty_text = isset( $difficulty_filters[ $difficulty ] ) ? $difficulty_filters[ $difficulty ] : $difficulty;

				?>
				<tr class="wtai-has-data wtai-keyword-tr <?php echo esc_attr( $tr_class ); ?>">
					<td class="wtai-col-trash">
						<a href="#" class="wtai-keyword-action-trash <?php echo wp_kses_post( $disabled_trash_class ); ?>" 
						data-tooltip-disabled="<?php echo wp_kses_post( $trash_disabled_tooltip ); ?>" 
						data-tooltip="<?php echo wp_kses_post( __( 'Delete keyword', 'writetext-ai' ) ); ?>" 
						>&nbsp;</a>
					</td>
					<td class="wtai-col-keyword wtai-col-sticky-mobile">
						<span class="wtai-column-keyword-name-text" ><?php echo wp_kses_post( $keyword ); ?></span>
					</td>
					<td class="wtai-col-volume wtai-col-normal-mobile"><?php echo wp_kses_post( $search_vol ); ?></td>
					<td class="wtai-col-difficulty wtai-col-normal-mobile"><?php echo wp_kses_post( $difficulty_text ); ?></td>
					<td class="wtai-col-trash-mobile wtai-col-normal-mobile">
						<a href="#" class="wtai-keyword-action-trash <?php echo wp_kses_post( $disabled_trash_class ); ?>" >&nbsp;</a>
					</td>
					<td class="wtai-col-action wtai-col-normal-mobile">
						<?php
						if ( $keyword_added ) {
							?>
							<span class="dashicons dashicons-minus wtai-keyword-action-button-v2" 
								data-keyword-type="manual" data-type="remove" 
								data-keyword="<?php echo wp_kses_post( $keyword ); ?>" 
								data-tooltip="<?php echo wp_kses_post( __( 'Remove as target keyword', 'writetext-ai' ) ); ?>" 
								></span>
							<?php
						} else {
							?>
							<span class="dashicons dashicons-plus-alt2 wtai-keyword-action-button-add wtai-keyword-action-button-v2 <?php echo esc_attr( $disabled_add_class ); ?>" 
								data-keyword-type="manual" data-type="add_to_selected" 
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
			}
			?>
		</tbody>
	</table>
</div>