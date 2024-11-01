<?php
/**
 * Category representative products metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $representative_products ) {
	foreach ( $representative_products as $product_id ) {
		$product_name = get_the_title( $product_id );

		$post_status_key = get_post_status( $product_id );
		$post_status     = wtai_get_post_status_label( $post_status_key );

		$product_image_id = get_post_thumbnail_id( $product_id );
		if ( $product_image_id ) {
			$product_image     = wp_get_attachment_image_src( $product_image_id, 'thumbnail' );
			$product_image_url = $product_image[0];

			$product_image_full     = wp_get_attachment_image_src( $product_image_id, 'large' );
			$product_image_full_url = $product_image_full[0];
		} else {
			$product_image_url      = wc_placeholder_img_src();
			$product_image_full_url = $product_image_url;
		}

		$product_name_short = wp_trim_words( $product_name, 15, null );

		$product_name_formatted = $product_name_short . ' (#' . $product_id . ') <span class="wtai-post-status-label" >(' . $post_status . ')</span>';

		$has_image_class = '';
		if ( $product_image_id ) {
			$has_image_class = ' wtai-has-featured-image ';
		}

		?>
		<div class="wtai-representative-product-item wtai-representative-product-item-<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $has_image_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" >
			<div class="wtai-cat-product-thumb wtai-alt-image" data-popimage="<?php echo esc_url( $product_image_full_url ); ?>" >
				<img src="<?php echo esc_url( $product_image_url ); ?>">
				<span class="wtai-alt-image-hover-text"><?php echo wp_kses_post( __( 'View', 'writetext-ai' ) ); ?></span>
			</div>
			<div class="wtai-cat-product-name" ><?php echo wp_kses_post( $product_name_formatted ); ?></div>
			<div class="wtai-remove-rep-prod"><input class="wtai-remove-rep-prod-btn" type="button" value="×"></div>
		</div>
		<?php
	}
}