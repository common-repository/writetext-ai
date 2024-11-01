<?php
/**
 * Freemium badge template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$freemium_credits = wtai_get_freemium_credit_count();

$is_premium = false;
if ( empty( $account_credit_details ) ) {
	$account_credit_details = wtai_get_account_credit_details();
	$is_premium             = $account_credit_details['is_premium'];
}
?>
<div class="wtai-freemium-badge-wrap" >
	<div class="wtai-freemium-badge-content-wrap" >
		<?php
		ob_start();
		do_action( 'wtai_product_single_premium_badge', 'wtai-premium-freemium-badge wtai-light' );
		$premium_button = ob_get_clean();

		if ( $is_premium ) {
			/* translators: %s - Freemium credit count */
			echo wp_kses( sprintf( __( 'Get additional %s credits for FREE when you generate your first text within 24 hours of signing up!', 'writetext-ai' ), $freemium_credits ), wtai_kses_allowed_html() );
		} else {
			/* translators: %s - Freemium credit count */
			$premium_badge_text = sprintf( __( 'Get %s credits for FREE to access [WTAI_PREMIUM_BUTTON] when you generate your first text within 24 hours of signing up!', 'writetext-ai' ), $freemium_credits );
			$premium_badge_text = str_replace( '[WTAI_PREMIUM_BUTTON]', $premium_button, $premium_badge_text );

			echo wp_kses( $premium_badge_text, wtai_kses_allowed_html() );
		}
		?>
	</div>
</div>
