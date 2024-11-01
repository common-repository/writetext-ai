<?php
/**
 * Premium modal template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! wtai_current_user_can( 'writeai_generate_text' ) ) {
	return;
}
?>

<div class="wtai-premium-modal-overlay-wrap" ></div>

<div class="wtai-premium-modal-wrap" >
	<div class="wtai-premium-modal-content-wrap" >
		<div class="wtai-premium-modal-header-wrap" >
			<div class="wtai-pm-header" >
				<h2>
					<span class="wtai-premium-wrap-ico"></span>
					<span><?php echo wp_kses_post( __( 'Unlock Premium', 'writetext-ai' ) ); ?></span>
				</h2>
				<div class="wtai-pm-description" >
					<?php echo wp_kses_post( __( 'Unlock Premium features by purchasing credits with our pay-as-you-go solution, or by subscribing to a monthly or annual plan. With Premium, you\'ll be able to:', 'writetext-ai' ) ); ?>
				</div>
			</div>
			<span class="wtai-pm-close-ico" ></span>
		</div>

		<div class="wtai-premium-modal-body-wrap" >
			<div class="wtai-pm-features-wrap" >
				<ul>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Bulk generate and bulk transfer text', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Add target keywords', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Get keyword search volume and competition data as well as related keyword ideas (Keyword Analysis)', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Get semantic keyword suggestions', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Set a reference product for generating text', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Rewrite existing text', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Write your own custom tone and style', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Get specific target market suggestions', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Write your own target market', 'writetext-ai' ) ); ?></span>
					</li>
					<li>
						<span class="wtai-pm-features-check-ico" ></span>
						<span class="wtai-pm-features-label" ><?php echo wp_kses_post( __( 'Add other product details on top of existing product attributes to improve the quality and relevance of the text', 'writetext-ai' ) ); ?></span>
					</li>
				</ul>
			</div>

			<div class="wtai-pm-cta-wrap" >
				<a class="wtai-pm-cta-link" href="<?php echo esc_url( WTAI_PREMIUM_SUBSCRIPTION_LINK ); ?>" target="_blank" >
					<span class="wtai-pm-cta-label" ><?php echo wp_kses_post( __( 'Unlock Premium', 'writetext-ai' ) ); ?></span>
					<span class="wtai-pm-cta-ico"></span>
				</a>
			</div>
		</div>
	</div>
</div>