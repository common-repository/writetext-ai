<?php
/**
 * Category dropdown product list metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( $product_data ) {
	$current_page   = $page;
	$total_products = $product_data['total_products'];
	$total_pages    = $product_data['total_pages'];
	$products       = $product_data['products'];

	if ( 1 === $current_page && ! $force_hide_wrap ) {
		?>
		<div class="wtai-cat-product-items-wrap" data-category-id="<?php echo esc_attr( $category_id ); ?>" 
			data-current-page="<?php echo esc_attr( $current_page ); ?>" 
			data-total-pages="<?php echo esc_attr( $total_pages ); ?>" >
		<?php
	}

	foreach ( $products as $product ) {
		$product_id             = $product['id'];
		$product_name           = $product['name'];
		$product_image_url      = $product['product_image_url'];
		$product_image_id       = $product['product_image_id'];
		$product_image_full_url = $product['product_image_full_url'];
		$post_status            = $product['post_status'];

		$product_name_short = wp_trim_words( $product_name, 15, null );

		$product_name_formatted      = $product_name_short . ' (#' . $product_id . ') <span class="wtai-post-status-label" >(' . $post_status . ')</span>';
		$product_name_long_formatted = $product_name . ' (#' . $product_id . ') (' . $post_status . ')';

		$has_image_class = '';
		if ( $product_image_id ) {
			$has_image_class = ' wtai-has-featured-image ';
		}
		?>
		<div class="wtai-cat-product-item wtai-cat-product-item-<?php echo esc_attr( $product_id ); ?> <?php echo esc_attr( $has_image_class ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" >
			<div class="wtai-cat-product-thumb wtai-alt-image" data-popimage="<?php echo esc_url( $product_image_full_url ); ?>" >
				<img src="<?php echo esc_url( $product_image_url ); ?>" />
				<span class="wtai-alt-image-hover-text" ><?php echo wp_kses_post( __( 'View', 'writetext-ai' ) ); ?></span>
			</div>
			<div class="wtai-cat-product-name" data-product-name="<?php echo esc_attr( $product_name_long_formatted ); ?>" >
				<?php echo wp_kses_post( $product_name_formatted ); ?>
			</div>
		</div>
		<?php
	}

	if ( $representative_products ) {
		foreach ( $representative_products as $rep_product_id ) {
			$product_name = get_the_title( $rep_product_id );

			$product_image_id = get_post_thumbnail_id( $rep_product_id );
			if ( $product_image_id ) {
				$product_image     = wp_get_attachment_image_src( $product_image_id, 'thumbnail' );
				$product_image_url = $product_image[0];

				$product_image_full     = wp_get_attachment_image_src( $product_image_id, 'large' );
				$product_image_full_url = $product_image_full[0];
			} else {
				$product_image_url = wc_placeholder_img_src();

				$product_image_url      = wc_placeholder_img_src();
				$product_image_full_url = $product_image_url;
			}

			$has_image_class = '';
			if ( $product_image_id ) {
				$has_image_class = ' wtai-has-featured-image ';
			}

			$product_name_short = wp_trim_words( $product_name, 15, null );

			$product_name_formatted      = $product_name_short . ' (#' . $rep_product_id . ')';
			$product_name_long_formatted = $product_name . ' (#' . $rep_product_id . ')';
			?>
			<div class="wtai-cat-product-item wtai-cat-product-item-<?php echo esc_attr( $rep_product_id ); ?> wtai-hidden <?php echo esc_attr( $has_image_class ); ?>" data-product-id="<?php echo esc_attr( $rep_product_id ); ?>" >
				<div class="wtai-cat-product-thumb wtai-alt-image" data-popimage="<?php echo esc_url( $product_image_full_url ); ?>" >
					<img src="<?php echo esc_url( $product_image_url ); ?>" />
					<span class="wtai-alt-image-hover-text" ><?php echo wp_kses_post( __( 'View', 'writetext-ai' ) ); ?></span>
				</div>
				<div class="wtai-cat-product-name" data-product-name="<?php echo esc_attr( $product_name_long_formatted ); ?>" >
					<?php echo wp_kses_post( $product_name_formatted ); ?>
				</div>
			</div>
			<?php
		}
	}

	if ( 1 === $current_page && ! $force_hide_wrap ) {
		if ( $total_products <= 0 && ! $search ) {
			$no_product_text = __( "We couldn't find any items in this category.", 'writetext-ai' );
		} else {
			$no_product_text = __( 'No product/s found.', 'writetext-ai' );
		}
		?>
		</div>

		<div class="wtai-rep-dp-no-products-found">
			<?php echo wp_kses_post( $no_product_text ); ?>
		</div>
		<?php

		if ( $total_pages > 1 && $current_page < $total_pages ) {
			?>
			<div class="wtai-cat-product-items-load-more" ></div>
			<?php
		}
	}
} else {
	?>
	<div class="wtai-rep-dp-no-products-found"><?php echo wp_kses_post( __( 'No product/s found.', 'writetext-ai' ) ); ?></div>
	<?php
}