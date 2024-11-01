<?php
/**
 * Category filter metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $thumbnail_id ) {
	$is_checked = wtai_get_category_image_checked_status();

	$image_full     = wp_get_attachment_image_src( $thumbnail_id, 'large' );
	$image_full_url = $image_full[0];

	$cat_image = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
	$image_url = $cat_image[0];
	?>
	<div class="wtai-category-image-selection-wrap" >
		<div class="wtai-category-image-selection-cb-wrap">
			<input type="checkbox" class="wtai-category-image-selection-cb" id="wtai-category-image-selection-cb" <?php echo esc_attr( checked( $is_checked ) ); ?> />
		</div>
		<div class="wtai-category-image-selection-thumb-wrap">
			<div class="wtai-alt-image" data-popimage="<?php echo esc_url( $image_full_url ); ?>">
				<img src="<?php echo esc_url( $image_url ); ?>" alt="">
				<span><?php echo wp_kses_post( __( 'View', 'writetext-ai' ) ); ?></span>
			</div>
		</div>
		<div class="wtai-category-image-selection-text-wrap">
			<span><?php echo wp_kses_post( __( 'Category image', 'writetext-ai' ) ); ?></span>
			<span class="wtai-featured-image-sub" >
				<?php echo wp_kses_post( __( '(Analyze image to generate more relevant text)', 'writetext-ai' ) ); ?>
			</span>
		</div>
	</div>
	<?php
} else {
	echo '<p class="wtai-cat-no-image-wrap" >' . wp_kses_post( __( 'No image found.', 'writetext-ai' ) ) . '</p>';
}
