<?php
/**
 * Category grid list template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="wtai-table-list-wrapper wrap wtai-main-wrapper writetext-table-list-category-wrapper">
	<div class="wtai-top-header wtai-dashboard">
		<div class="wtai-inner-flex">
				<span class="wtai-global-loader" style="display:none;"></span>

				<div class="wtai-product-list-dashboard-wrap" >     
					<?php if ( wtai_is_country_selection_hidden() ) { ?> 
						<span class="wtai-country-global"  >
							<span class="dashicons wtai-dashicons-country"></span>
							<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'Country', 'writetext-ai' ) ); ?></span>
						</span>          
					<?php } ?>     
					<span class="wtai-history-global" onclick="wtaiGetHistoryGlobalPopin(this)"  >
						<span class="dashicons wtai-dashicons-backup"></span>
						<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'History log', 'writetext-ai' ) ); ?></span>
					</span>
				</div>
				<button class="wtai-btn-close-history-global"><span class="dashicons dashicons-no-alt"></span></button>
		</div>
	</div>
	<div id="wtai-imaginary-div-topheader"></div>
	<div class="wtai-title-header wtai-page-title-header-wrap">
		<img class="wtai-logo" width="200" src="<?php echo esc_url( WTAI_DIR_URL . 'assets/images/logo_writetext.svg' ); ?>" alt="logo">
		<h1 class="wtai-page-title-header" ><?php echo wp_kses_post( __( 'Categories', 'writetext-ai' ) ); ?></h1>
	</div>
	<?php

	$status_views_list = $wtai_category_list_table->get_views();
	$status_views      = array();
	foreach ( $status_views_list as $status_key => $status_value ) {
		$status_views[] = '<li class="' . $status_key . '">' . $status_value . '</li>';
	}
	?>
	<ul class="subsubsub wtai-status-view">
		<?php echo wp_kses_post( implode( ' | ', $status_views ) ); ?>
	</ul>

	<?php
	$request_uri_action = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	?>
	<div id="wtai-frm-search-products" class="wtai-frm-search-products" >
		<p class="wtai-search-box">
			<label class="screen-reader-text" for="wtai-post-search-input"><?php echo wp_kses_post( __( 'Search categories', 'writetext-ai' ) ); ?></label>
			<input type="search" id="wtai-post-search-input" name="s" value="<?php echo esc_attr( wp_unslash( _admin_search_query() ) ); ?>">
			<input type="button" id="wtai-search-product-submit" class="button" value="<?php echo wp_kses_post( __( 'Search categories', 'writetext-ai' ) ); ?>">
		</p>
	</div>
	<div class="wtai-show-comparison wtai-show-comparison">
		<?php
		$wtai_comparison_status = wtai_get_user_comparison_cb( 'category' );
		if ( $wtai_comparison_status && $wtai_comparison_status > 0 ) {
			$checked = 'checked';
		} else {
			$checked = '';
		}

		?>
		<input type="checkbox" name="wtai_comparison_cb" id="wtai-comparison-cb" value="1" <?php echo esc_attr( $checked ); ?> />
		<label for="wtai-comparison-cb"><?php echo wp_kses_post( __( 'Show text preview on hover', 'writetext-ai' ) ); ?></label>

	</div>
	<?php $wtai_category_list_table->display(); ?>
	<div class="wtai-content">
		<div class="wtai-history wtai-history-global">
			<div class="wtai-d-inner-wrapper">
				<div class="wtai-history-header"><?php echo wp_kses_post( __( 'History log', 'writetext-ai' ) ); ?></div>
				<div class="wtai-history-filter">
					<div class="wtai-history-filter-form">
					<span class="wtai-history-date-from wtai-calendar-field">
						<input type="text" class="wtai-history-date-input wtai-history-date-input-from" data-field="from" placeholder="<?php echo wp_kses_post( __( 'Start date', 'writetext-ai' ) ); ?>" />
					</span>
					<span class="wtai-history-date-to wtai-calendar-field">
						<input type="text" class="wtai-history-date-input wtai-history-date-input-to" data-field="to" placeholder="<?php echo wp_kses_post( __( 'End date', 'writetext-ai' ) ); ?>"  />
					</span>
					<span class="wtai-history-date-author">
						<select class="wtai-history-author-select" id="wtai-history-author-select" >
							<option value="" class="wtai-option-author-default">
							<?php echo wp_kses_post( __( 'Filter by user', 'writetext-ai' ) ); ?></option>
						</select>
					</span>
					<span class="wtai-history-date-action">
						<a href="#" class="button wtai-history-filter-button"><?php echo wp_kses_post( __( 'Filter', 'writetext-ai' ) ); ?></a>
					</span>
					</div>
				</div>
				<div class="wtai-history-content"></div>
			</div>
		</div>
	</div>
</div>

<div class="wtai-history-content-corrector" style="display: none;" ></div>

<?php do_action( 'wtai_edit_category_form' ); ?>
<?php do_action( 'wtai_bulk_edit_generate_cancel' ); ?>
<?php do_action( 'wtai_bulk_edit_cancel_and_exit' ); ?>
<?php do_action( 'wtai_country_selection_popup' ); ?>
<?php do_action( 'wtai_restore_global_setting_completed' ); ?>
<?php do_action( 'wtai_premium_modal' ); ?>
<?php do_action( 'wtai_preprocess_image_loader' ); ?>
<?php do_action( 'wtai_image_confirmation_proceed_loader' ); ?>
<?php do_action( 'wtai_image_confirmation_proceed_bulk_loader' ); ?>
<?php do_action( 'wtai_admin_mobile_footer' ); ?>