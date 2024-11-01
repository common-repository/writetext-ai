<?php
/**
 * Footer template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$page_type = 'product';
if ( 'category' === wtai_get_current_page_type() ) {
	$page_type = 'category';
}

$is_hidden_guideline_checked = '';
if ( wtai_get_hide_guidelines_user_preference( $page_type ) ) {
	$is_hidden_guideline_checked = ' checked="checked" ';
}

$hide_other_settings  = '';
$hide_credit_settings = '';
// phpcs:ignore WordPress.Security.NonceVerification
if ( isset( $_GET['page'] ) && 'write-text-ai-settings' === $_GET['page'] ) {
	$hide_other_settings = ' display: none; ';
}

$hide_mobile_class = 'wtai-footer-hidden-mobile';
if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
	$hide_other_settings  = ' display: none; ';
	$hide_credit_settings = ' display: none; ';
	$hide_mobile_class    = '';
}

$credit_count_label = '';
if ( defined( 'WTAI_CREDIT_ACCOUNT_DETAILS' ) && WTAI_CREDIT_ACCOUNT_DETAILS ) {
	$credit_account_details = WTAI_CREDIT_ACCOUNT_DETAILS;
	$available_credit_count = $credit_account_details['available_credits'];
	$credit_count_label     = wtai_get_available_credit_label( $available_credit_count );
}

if ( '1' === get_option( 'wtai_latest_version_outdated' ) || '1' === get_option( 'wtai_force_version_update' ) ) {
	$hide_other_settings  = ' display: none; ';
	$hide_credit_settings = ' display: none; ';
}
?>
<!-- Start: Sticky footer -->
<div class="wtai-footer-wrap" >
	<div class="wtai-footer-content-wrap" >
		<div class="wtai-footer-left-wrap" >
			<div class="wtai-footer-link-wrap" >
				<div class="wtai-credit-info-wrap" style="<?php echo esc_attr( $hide_credit_settings ); ?>" >
					<div class="wtai-credit-available-wrap" >
						<a href="https://platform.writetext.ai/premium" target="_blank" >
							<span class="wtai-credit-available" ><?php echo wp_kses_post( $credit_count_label ); ?></span>
						</a>
					</div>
					<div class="wtai-credit-info-links-wrap" >
						<div class="wtai-footer-credit-tooltip wtai-tooltip">
							<span class="wtai-icon-tooltip dashicons dashicons-editor-help"></span>
							<div class="wtai-tooltiptext">
								<div class="wtai-tooltip-arrow"></div>
								<a class="" href="https://writetext.ai/frequently-asked-questions#credit-cost" target="_blank" ><?php echo wp_kses_post( __( 'How credit cost is calculated', 'writetext-ai' ) ); ?></a>
							</div>	
						</div>						
					</div>
				</div>

				<div class="wtai-footer-separator wtai-restore-global-settings-separator wtai-credit-info-separator <?php echo esc_attr( $hide_mobile_class ); ?>" style="<?php echo esc_attr( $hide_other_settings ); ?>" ></div>
				<div class="wtai-restore-global-settings-wrap <?php echo esc_attr( $hide_mobile_class ); ?>" style="<?php echo esc_attr( $hide_other_settings ); ?>" >
					<a href="#" id="wtai-restore-global-settings" name="wtai-restore-global-settings" class="wtai-restore-global-settings" ><?php echo wp_kses_post( __( 'Restore global settings', 'writetext-ai' ) ); ?></a>
				</div>
				
				<div class="wtai-footer-separator wtai-hide-step-separator" style="<?php echo esc_attr( $hide_other_settings ); ?>" ></div>
				<div class="wtai-hide-step-cb-wrap" style="<?php echo esc_attr( $hide_other_settings ); ?>" >
					<input type="checkbox" id="wtai-hide-step-cb" name="wtai_hide_step_cb" value="1" <?php echo esc_attr( $is_hidden_guideline_checked ); ?> /> <label for="wtai-hide-step-cb" ><?php echo wp_kses_post( __( 'Hide step guides', 'writetext-ai' ) ); ?></label>
				</div>
			</div>
		</div>
		<div class="wtai-footer-right-wrap <?php echo esc_attr( $hide_mobile_class ); ?>" >
			<div class="wtai-site-credits-wrap" >
				<div class="wtai-site-credits wtai-site-credits-1" >
					<?php
					/* translators: %s: copy right year */
					echo wp_kses_post( sprintf( __( '&copy; %s', 'writetext-ai' ), current_time( 'Y' ) ) ) . ' &nbsp;';
					/* translators: %1$s: wta ai site link, %2$s: 1902 WP site link */
					echo wp_kses_post( sprintf( __( '<a href="%1$s" target="_blank" >WriteText.ai</a> by <a href="%2$s" target="_blank" >1902 Software</a>', 'writetext-ai' ), 'https://writetext.ai/', 'https://1902software.com/wordpress/' ) );
					?>
				</div>

				<div class="wtai-footer-separator wtai-hide-mobile"></div>

				<div class="wtai-site-credits" >
					<a class="<?php echo esc_attr( $hide_mobile_class ); ?>" href="https://writetext.ai/create-a-ticket" target="_blank" ><?php echo wp_kses_post( __( 'Send feedback', 'writetext-ai' ) ); ?></a>
				</div>
				<div class="wtai-footer-separator"></div>
				
				<div class="wtai-site-credits" >
					<?php
					$version = wtai_get_version( true );
					if ( defined( 'WTAI_ENV' ) && WTAI_ENV === 'development' ) {
						$version .= '-dev';
					}

					/* translators: %s: wta version */
					echo wp_kses_post( sprintf( __( 'version %s', 'writetext-ai' ), $version ) );
					?>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END: Sticky footer -->
