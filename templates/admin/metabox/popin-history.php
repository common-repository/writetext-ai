<?php
/**
 * Product single history template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="wtai-history wtai-history-single">
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
					<option value="" class="wtai-option-author-default"><?php echo wp_kses_post( __( 'Filter by user', 'writetext-ai' ) ); ?></option>
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