<?php
/**
 * Premium badge template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! wtai_current_user_can( 'writeai_generate_text' ) ) {
	return;
}

if ( WTAI_PREMIUM ) {
	$custom_class .= ' wtai-hide-premium-feature ';
}
?>

<span class="wtai-premium-wrap <?php echo esc_attr( $custom_class ); ?>" >
	<span class="wtai-premium-wrap-ico"></span>
	<span class="wtai-premium-wrap-label">
		<?php echo wp_kses_post( __( 'Premium', 'writetext-ai' ) ); ?>
	</span>
</span>