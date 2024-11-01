<?php
/**
 * Global settings class for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Global settings class.
 */
class WTAI_Global_Settings extends WTAI_Init {

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->define_vars();
		$this->init_hooks();
	}

	/**
	 * Define variables.
	 */
	public function define_vars() {
	}

	/**
	 * Initialize hooks.
	 */
	public function init_hooks() {
		add_action( 'admin_menu', array( $this, 'get_submenu_page' ) );

		add_filter( 'wtai_global_settings', array( $this, 'get_global_settings' ), 10 );
	}

	/**
	 * Get settings sub menu.
	 */
	public function get_submenu_page() {
		if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && wtai_current_user_can( 'writeai_settings_page' ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
			add_submenu_page(
				'write-text-ai',
				__( 'Settings', 'writetext-ai' ),
				__( 'Settings', 'writetext-ai' ),
				'read',
				'write-text-ai-settings',
				array( $this, 'get_settings_dashboard_callback' ),
				54
			);
		}
	}

	/**
	 * Display callback for the submenu page.
	 */
	public function get_settings_dashboard_callback() {
		if ( false === wtai_is_allowed_beta_language() ) {
			return;
		}

		if ( '1' === get_option( 'wtai_latest_version_outdated' ) ) {
			$latest_version_message = get_option( 'wtai_latest_version_message' );
			?>
			<div class="wtai-update-notice notice notice-error is-dismissible" style="margin-left: -5px" >
				<p><?php echo wp_kses( $latest_version_message, 'post' ); ?></p>
			</div>
			<?php

			if ( '1' === get_option( 'wtai_force_version_update' ) ) {
				return;
			}
		}

		$current_user_id = get_current_user_id();

		$popupblocker_nonce             = wp_create_nonce( 'wtai-popupblocker-nonce' );
		$popup_blocker_notice_dismissed = wtai_get_popup_blocker_dismiss_state();

		if ( ! $popup_blocker_notice_dismissed ) {
			?>
			<input type="hidden" id="wtai-popupblocker-nonce" value="<?php echo esc_attr( $popupblocker_nonce ); ?>" />
			<div id="wtai-popup-blocker-notice" class="updated error notice wtai-popup-blocker-notice is-dismissible" style="margin-bottom: 20px;margin-top: 20px;text-align: left;" >
				<p><?php echo wp_kses_post( __( '<strong>Warning:</strong> Disable all pop-up blockers then refresh this page. WriteText.ai does not work when you have pop-up blockers enabled.', 'writetext-ai' ) ); ?></p>
			</div>
			<?php
		}

		require WTAI_ABSPATH . 'templates/admin/translation-ongoing.php';

		do_action( 'wtai_premium_modal' );

		$this->save_settings();

		$attributes = $this->get_product_settings_attribute();
		$tones      = $this->get_product_text_tones();
		$styles     = $this->get_product_text_styles();
		$audiences  = $this->get_product_text_audiences();

		include_once WTAI_ABSPATH . 'templates/admin/settings.php';
	}

	/**
	 * Save global settings.
	 */
	public function save_settings() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_POST['wtai_settings_wpnonce'] ) &&
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['wtai_settings_wpnonce'] ) ), 'wtai_settings' ) ) {

			$fields = array(
				'wtai_installation_tones',
				'wtai_installation_styles',
				'wtai_installation_audiences',
				'wtai_installation_product_attr',
				'wtai_installation_product_description_min',
				'wtai_installation_product_description_max',
				'wtai_installation_product_excerpt_min',
				'wtai_installation_product_excerpt_max',
				'wtai_installation_category_description_min',
				'wtai_installation_category_description_max',
			);

			$account_credit_details = wtai_get_account_credit_details( true );
			$is_premium             = $account_credit_details['is_premium'];

			foreach ( $fields as $field ) {
				if ( 'wtai_installation_product_attr' === $field ) {
					if ( isset( $_POST[ $field ] ) ) {
						if ( is_array( $_POST[ $field ] ) ) {
							$value = isset( $_POST[ $field ] ) && ! empty( $_POST[ $field ] ) ? map_deep( wp_unslash( $_POST[ $field ] ), 'wp_kses_post' ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
						} else {
							$value = isset( $_POST[ $field ] ) && ! empty( $_POST[ $field ] ) ? wp_kses_post( wp_unslash( $_POST[ $field ] ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
						}
					}
				} elseif ( is_array( $_POST[ $field ] ) ) {
						$value = map_deep( wp_unslash( $_POST[ $field ] ), 'wp_kses_post' ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					$value = wp_kses_post( wp_unslash( $_POST[ $field ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				update_option( $field, $value );
			}

			$current_user_id = get_current_user_id();
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_POST['wtai-bulk-generate-ppopup'] ) ) {
				update_user_meta( $current_user_id, 'wtai_bulk_generate_popup', wp_kses( wp_unslash( $_POST['wtai-bulk-generate-ppopup'] ), 'post' ) ); // phpcs:ignore WordPress.Security.NonceVerification
			} else {
				delete_user_meta( $current_user_id, 'wtai_bulk_generate_popup' );
			}

			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai-settings' ) );
		}
	}

	/**
	 * Get global settings.
	 *
	 * @param array $key_settings Key settings.
	 */
	public function get_global_settings( $key_settings = '' ) {
		if ( ! $key_settings ) {
			return $key_settings;
		}

		$fields = array(
			'wtai_installation_tones'                    => array(),
			'wtai_installation_styles'                   => '',
			'wtai_installation_audiences'                => array(),
			'wtai_installation_product_attr'             => array(),
			'wtai_installation_product_description_min'  => 0,
			'wtai_installation_product_description_max'  => 0,
			'wtai_installation_product_excerpt_min'      => 0,
			'wtai_installation_product_excerpt_max'      => 0,
			'wtai_installation_category_description_min' => 0,
			'wtai_installation_category_description_max' => 0,
		);

		if ( ! isset( $fields[ $key_settings ] ) ) {
			return '';
		}

		$value = get_option( $key_settings, $fields[ $key_settings ] );

		if ( ! $value ) {
			$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
			switch ( $key_settings ) {
				case 'wtai_installation_product_description_min':
					$value = 75;
					break;
				case 'wtai_installation_product_excerpt_min':
					$value = 25;
					break;
				case 'wtai_installation_product_description_max':
					$value = 150;
					break;
				case 'wtai_installation_product_excerpt_max':
					$value = 50;
					break;
				case 'wtai_installation_category_description_min':
					$value = 75;
					break;
				case 'wtai_installation_category_description_max':
					$value = 150;
					break;
			}
		}

		return $value;
	}
}

new WTAI_Global_Settings();
