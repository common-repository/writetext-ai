<?php
/**
 * Freemium popup template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! $account_credit_details ) {
	return;
}

$free_premium_credits         = $account_credit_details['free_premium_credits'];
$free_credits_already_premium = $account_credit_details['free_credits_already_premium'];

$display_popup = false;
if ( $free_premium_credits || $free_credits_already_premium ) {
	$display_popup = true;
}

$freemium_credits = wtai_get_freemium_credit_count();

$freemium_type = '';
if ( $free_credits_already_premium ) {
	$freemium_type = 'wtai-already-premium';

	/* translators: %s: Freemium credit count */
	$text_headline = sprintf( __( 'You earned %s free credits!', 'writetext-ai' ), $freemium_credits );

	/* translators: %s: Freemium credit count */
	$text_content = sprintf( __( "You've generated your first text within 24 hours of signup. As a special reward, we're giving you <strong>free %s credits</strong>! Enjoy enhanced features and more powerful tools to make your content shine.", 'writetext-ai' ), $freemium_credits );
} elseif ( $free_premium_credits ) {
	$freemium_type = 'wtai-free-premium';

	$text_headline = __( "You've unlocked premium!", 'writetext-ai' );

	/* translators: %s: Freemium credit count */
	$text_content = sprintf( __( "You've generated your first text within 24 hours of signup. As a special reward, we're giving you <strong>access to Premium* and free %s credits</strong>! Enjoy enhanced features and more powerful tools to make your content shine.", 'writetext-ai' ), $freemium_credits );

	/* translators: %s: Freemium credit count */
	$text_content .= '<span class="wtai-premium-disclaimer-text" >' . sprintf( __( '*Premium access is valid for one year or until you have used up your premium %s credits, whichever comes first.', 'writetext-ai' ), $freemium_credits ) . '</span>';
} else {
	return;
}

$wtai_nonce = wp_create_nonce( 'wtai-freemium-popup-nonce' );
?>
<div class="wtai-freemium-popup-wrap <?php echo esc_attr( $freemium_type ); ?>" data-nonce="<?php echo esc_attr( $wtai_nonce ); ?>" >
	<div class="wtai-freemium-popup-content-wrap" >
		<div class="wtai-freemium-popup-content-header" >
			<span class="wtai-crown-ico" ></span>
			<span class="wtai-freemium-header-txt"><?php echo wp_kses_post( $text_headline ); ?></span>
			<span class="wtai-freemium-popup-close"></span>
		</div>
		<div class="wtai-freemium-popup-description" >
			<?php echo wp_kses_post( $text_content ); ?>
		</div>
	</div>
</div>
