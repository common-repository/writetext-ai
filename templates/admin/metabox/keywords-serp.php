<?php
/**
 * Keywords SERP template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$serp_header_title = '';
if ( 'ranked' === $keyword_type ) {
	$serp_header_title = __( 'Search Engine Results Page (SERP) Overview', 'writetext-ai' );
} elseif ( 'competitor' === $keyword_type ) {
	$serp_header_title = __( 'Competitors', 'writetext-ai' );
}

$serp_date = '';
foreach ( $serp_infos as $serp_info ) {
	$serp_date = $serp_info['date'];
	if ( $serp_date ) {
		break;
	}
}
?>
<div class="wtai-keyword-serp-wrap wtai-tooltiptext wtai-keyword-serp-wrap-<?php echo esc_attr( $keyword_type ); ?>" >
	<div class="wtai-keyword-serp-header-wrap" >
		<?php
		if ( 'competitor' === $keyword_type ) {
			?>
			<div class="wtai-keyword-serp-header-competitor-wrap" >
				<div class="wtai-keyword-serp-header-title"><?php echo wp_kses_post( $serp_header_title ); ?></div>
				<div class="wtai-keyword-serp-header-rank-title"><?php echo wp_kses_post( __( 'Rank', 'writetext-ai' ) ); ?></div>
			</div>
			<?php
		} else {
			?>
			<div class="wtai-keyword-serp-header-ranked-wrap" >
				<div class="wtai-keyword-serp-header-icon" ><span class="wtai-ranked-serp-ico" ></span></div>
				<?php
				if ( $serp_header_title ) {
					?>
					<div class="wtai-keyword-serp-header-title">
						<?php echo wp_kses_post( $serp_header_title ); ?>
						<?php
						if ( $serp_date ) {
							$serp_date_timestamp = strtotime( get_date_from_gmt( $serp_date, 'Y-m-d H:i:s' ) );
							$serp_date_formatted = sprintf(
								/* translators: %1$s: date, %2$s: time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $serp_date_timestamp ),
								date_i18n( get_option( 'time_format' ), $serp_date_timestamp )
							);
							?>
							<div class="wtai-keyword-serp-date"><?php echo wp_kses_post( __( 'SERP data as of ', 'writetext-ai' ) . $serp_date_formatted ); ?></div>
							<?php
						}
						?>
					</div>
					<?php
				}
				?>
			</div>
			<?php
		}
		?>
	</div>

	<?php
	$has_featured_class_name = '';
	if ( $featured_serp_info && 'ranked' === $keyword_type ) {
		$has_featured_class_name = 'has-featured-snippet';
	}
	?>

	<div class="wtai-keyword-serp-content-wrap <?php echo esc_attr( $has_featured_class_name ); ?>" >
		<!-- Featured SERP -->
		<?php
		if ( $featured_serp_info && 'ranked' === $keyword_type ) {
			$featured_desc   = $featured_serp_info['description'];
			$featured_title  = $featured_serp_info['title'];
			$featured_url    = $featured_serp_info['url'];
			$featured_domain = $featured_serp_info['domain'];
			?>
			<div class="wtai-keyword-serp-featured-wrap" >
				<div class="wtai-keyword-serp-featured-content-wrap" >
					<div class="wtai-keyword-serp-featured-content-spacer" >
						&nbsp;
					</div>
					<div class="wtai-keyword-serp-featured-content-main" >
						<div class="wtai-keyword-serp-featured-title"><?php echo wp_kses_post( __( 'Featured snippet', 'writetext-ai' ) ); ?></div>
						<div class="wtai-keyword-serp-featured-description">
							<?php echo wp_kses_post( $featured_desc ); ?>
						</div>
						<div class="wtai-keyword-serp-featured-details-wrap">
							<div class="wtai-keyword-serp-featured-url-title">
								<a href="<?php echo esc_url( $featured_url ); ?>" target="_blank" ><?php echo wp_kses_post( $featured_title ); ?></a>
							</div>
							<div class="wtai-keyword-serp-featured-url-link">
								<a href="<?php echo esc_url( $featured_url ); ?>" target="_blank" ><?php echo wp_kses_post( $featured_domain ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<!-- END Featured SERP -->
		
		<table class="wtai-keyword-serp-list-wrap" >
			<?php
			foreach ( $serp_infos as $serp_info ) {
				$serp_domain = $serp_info['domain'];
				$serp_rank   = $serp_info['rank_group'];
				$serp_title  = $serp_info['title'];
				$serp_url    = $serp_info['url'];
				$serp_type   = $serp_info['type'];
				$is_own      = $serp_info['is_own'];
				$serp_date   = $serp_info['date'];

				$item_class = '';
				if ( $is_own ) {
					$item_class = ' wtai-keyword-serp-item-own ';
				}
				?>
				<tr class="wtai-keyword-serp-item <?php echo esc_attr( $item_class ); ?>" >
					<?php
					if ( 'competitor' === $keyword_type ) {
						$competitor_for = $serp_info['competitor_for'];
						?>
						<td class="wtai-keyword-serp-details" >
							<div class="wtai-keyword-serp-title" >
								<a href="<?php echo esc_url( $serp_url ); ?>" target="_blank" ><?php echo wp_kses_post( $serp_title ); ?></a>
							</div>
							<div class="wtai-keyword-serp-url-wrap" >
								<a class="wtai-keyword-serp-url" href="<?php echo esc_url( $serp_url ); ?>" target="_blank" ><?php echo wp_kses_post( $serp_domain ); ?><span class="wtai-url-new-tab-ico" ></span></a>
							</div>

							<?php if ( $competitor_for ) { ?>
								<div class="wtai-keyword-serp-competitor-for-wrap" >
									<?php
									/* translators: %s: competitor for keyword value */
									echo wp_kses_post( sprintf( __( 'Competitor for keyword %s', 'writetext-ai' ), '<span class="serp-competitor-for" >"' . $competitor_for . '"</span>' ) );
									?>
								</div>
							<?php } ?>
						</td>
						<td class="wtai-keyword-serp-rank" ><?php echo wp_kses_post( $serp_rank ); ?></td>
						<?php
					} else {
						?>
					<td class="wtai-keyword-serp-rank" ><?php echo wp_kses_post( $serp_rank ); ?></td>
					<td class="wtai-keyword-serp-details" >
						<div class="wtai-keyword-serp-title" >
							<a href="<?php echo esc_url( $serp_url ); ?>" target="_blank" ><?php echo wp_kses_post( $serp_title ); ?></a>
						</div>
						<div class="wtai-keyword-serp-url-wrap" >
							<a class="wtai-keyword-serp-url" href="<?php echo esc_url( $serp_url ); ?>" target="_blank" ><?php echo wp_kses_post( $serp_domain ); ?><span class="wtai-url-new-tab-ico" ></span></a>
						</div>
					</td>
					<?php } ?>
				</tr>
				<?php
			}
			?>
		</table>
	</div>
</div>
