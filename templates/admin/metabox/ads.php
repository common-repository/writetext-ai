<?php
/**
 * Ads metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$banner_data = wtai_get_ad_banner();

if ( ! $banner_data ) {
	return;
}

if ( WTAI_PREMIUM ) {
	$custom_class .= ' wtai-hide-premium-feature ';
}
?>

<div class="postbox wtai-ads-placeholder-wrap <?php echo esc_attr( $custom_class ); ?>">
	<div class="wtai-ad-banner-wrap" >
		<span class="wtai-ad-cta" ><?php echo wp_kses_post( __( 'Stop seeing ads', 'writetext-ai' ) ); ?></span>
		
		<a href="<?php echo esc_url( $banner_data['link'] ); ?>" target="_blank" >
			<img src="<?php echo esc_url( $banner_data['imageUrl'] ); ?>" alt="ads-placeholder" />
		</a>
	</div>
</div>