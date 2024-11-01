<?php
/**
 * API Services class for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * API services class.
 */
class WTAI_API_Services extends WTAI_Init {
	/**
	 * API base url.
	 *
	 * @var string
	 */
	public $api_base_url = '';

	/**
	 * Construct class.
	 */
	public function __construct() {
		$this->define_vars();
		$this->init_hooks();
	}

	/**
	 * Define variables.
	 */
	public function define_vars() {
		$this->set_base_api_url();
	}

	/**
	 * Initialize hooks.
	 */
	public function init_hooks() {
		add_action( 'admin_init', array( $this, 'get_token_debug' ) );

		add_action( 'init', array( $this, 'get_token_process' ) );

		// Intialize first process.
		add_action( 'wtai_initial_process', array( $this, 'validate_account_token' ) );
		add_action( 'admin_init', array( $this, 'validate_etag_token' ) );

		// Filters.
		add_filter( 'wtai_generate_options_text', array( $this, 'get_generate_options_text' ), 10, 3 );
		add_filter( 'wtai_generate_product_text', array( $this, 'get_generate_product_text' ), 10, 3 );
		add_filter( 'wtai_generate_history', array( $this, 'get_generate_history' ), 10, 4 );
		add_filter( 'wtai_generate_product_status', array( $this, 'get_generate_product_status' ), 10, 3 );
		add_filter( 'wtai_generate_product_bulk', array( $this, 'get_generate_product_bulk' ), 10, 2 );
		add_filter( 'wtai_generate_product_bulk_cancel', array( $this, 'get_generate_product_bulk_cancel' ), 10, 2 );
		add_filter( 'wtai_generate_product_bulk_queue_all', array( $this, 'get_generate_product_bulk_queue_all' ), 10, 2 );

		// Store.
		add_filter( 'wtai_stored_generate_text', array( $this, 'add_generate_product_text' ), 10, 2 );
		add_action( 'wp_ajax_wtai_generate_bulk_progress', array( $this, 'get_generate_bulk_progress' ) );

		add_action( 'wp_ajax_wtai_generate_text', array( $this, 'get_generate_text' ) );
		add_action( 'wp_ajax_wtai_transfer_grid_text', array( $this, 'add_store_grid_text' ) );

		add_action( 'wp_ajax_wtai_store_single_text', array( $this, 'add_store_single_text' ) );
		add_action( 'wp_ajax_wtai_store_bulk_text', array( $this, 'add_store_bulk_text' ) );

		// Settings.
		add_filter( 'wtai_field_conversion', array( $this, 'get_metakey_to_apikey' ), 10, 2 );

		// Filters.
		add_filter( 'wtai_filter_endpoint', array( $this, 'get_filter_endpoint' ), 10 );
		add_filter( 'wtai_generate_text_filters', array( $this, 'get_generate_text_filters' ), 10, 3 );
		add_filter( 'wtai_global_rule_fields', array( $this, 'get_global_rule_fields' ), 10 );

		add_filter( 'wtai_web_token', array( $this, 'get_web_token_callback' ), 10 );

		add_action( 'wtai_default_generate_text_filters', array( $this, 'process_default_generate_text_filters' ), 10 );

		add_action( 'wp_ajax_wtai_generate_suggested_audience', array( $this, 'get_suggested_audience' ) );

		add_filter( 'wtai_get_suggested_audiences_text', array( $this, 'get_suggested_audiences_text' ), 10, 4 );
		add_filter( 'wtai_get_keyword_semantics_text', array( $this, 'get_keyword_semantics_text' ), 10, 5 );
		add_filter( 'wtai_set_selected_keyword_semantics_text', array( $this, 'set_selected_keyword_semantics_text' ), 10, 4 );

		add_filter( 'wtai_set_custom_audience_text', array( $this, 'set_custom_audience_text' ), 10, 3 );

		add_filter( 'wtai_generate_keywordanalysis_location', array( $this, 'get_keywordanalysis_location' ), 10 );
		add_filter( 'wtai_generate_keywordanalysis_ideas', array( $this, 'get_keywordanalysis_ideas' ), 10, 4 );

		add_action( 'wp_ajax_wtai_set_custom_audience_callback', array( $this, 'set_custom_audience_callback' ) );

		add_action( 'wp_ajax_wtai_generate_queue_progress', array( $this, 'get_generate_queue_progress' ) );

		add_filter( 'wtai_get_generate_bulk_data', array( $this, 'get_generate_bulk_data' ), 10, 4 );

		add_action( 'wp_ajax_wtai_reload_loader_data', array( $this, 'reload_loader_data' ) );
		add_action( 'wp_ajax_wtai_retry_bulk_generate', array( $this, 'retry_bulk_generate' ) );

		add_filter( 'wtai_generate_product_bulk_retry', array( $this, 'writetextai_generate_product_bulk_retry' ), 10, 2 );

		add_filter( 'wtai_get_credits_count', array( $this, 'get_credits_count' ), 10, 1 );

		add_filter( 'wtai_check_connect_token_api', array( $this, 'check_connect_token_api' ), 10, 2 );

		add_filter( 'wtai_get_api_region', array( $this, 'get_api_region' ), 10, 2 );

		add_action( 'admin_init', array( $this, 'debug_api_region' ), 9999 );

		add_filter( 'wtai_record_product_reviewed_api', array( $this, 'record_product_reviewed' ), 10, 4 );
		add_filter( 'wtai_validate_etag_token_expired', array( $this, 'validate_etag_token_expired' ), 10, 1 );
		add_filter( 'wtai_get_data_via_api', array( $this, 'get_data_via_api_filter' ), 10, 5 );

		add_action( 'admin_init', array( $this, 'check_premium_account' ) );

		add_filter( 'wtai_get_product_extension_review', array( $this, 'get_product_extension_review' ), 10, 2 );
		add_filter( 'wtai_save_product_extension_review', array( $this, 'save_product_extension_review' ), 10, 4 );
		add_filter( 'wtai_get_review_product_extension_status', array( $this, 'get_review_product_extension_status' ), 10, 3 );
		add_filter( 'wtai_get_product_image_from_api', array( $this, 'get_product_image_from_api' ), 10, 5 );
		add_filter( 'wtai_save_product_image_to_api', array( $this, 'save_product_image_to_api' ), 10, 6 );
		add_filter( 'wtai_generate_alt_text_for_images', array( $this, 'generate_alt_text_for_images' ), 10, 5 );
		add_filter( 'wtai_get_alt_text_for_images', array( $this, 'get_alt_text_for_images' ), 10, 4 );
		add_filter( 'wtai_get_alt_text_for_image', array( $this, 'get_alt_text_for_image' ), 10, 4 );

		add_action( 'wp_ajax_wtai_generate_alt_text', array( $this, 'get_generate_alt_text' ) );
		add_action( 'wp_ajax_wtai_transfer_image_alt_text', array( $this, 'transfer_image_alt_text' ) );

		add_filter( 'wtai_save_alt_text_for_image_api', array( $this, 'save_alt_text_for_image_api' ), 10, 2 );
		add_filter( 'wtai_get_available_credits', array( $this, 'get_available_credits' ), 10, 1 );

		add_action( 'wtai_record_installation_statistics', array( $this, 'record_installation_statistics' ), 10, 2 );

		add_filter( 'wtai_record_alt_image_id_reviewed_api', array( $this, 'record_alt_image_id_reviewed_api' ), 10, 4 );

		add_filter( 'wtai_start_ai_keyword_analysis', array( $this, 'start_ai_keyword_analysis' ), 10, 4 );
		add_filter( 'wtai_get_ranked_keywords', array( $this, 'get_ranked_keywords' ), 10, 4 );
		add_filter( 'wtai_process_manual_keyword', array( $this, 'process_manual_keyword' ), 10, 4 );

		// Category generate.
		add_filter( 'wtai_generate_category_options_text', array( $this, 'generate_category_options_text' ), 10, 3 );
		add_filter( 'wtai_generate_category_text', array( $this, 'get_generate_category_text' ), 10, 3 );
		add_filter( 'wtai_stored_generate_category_text', array( $this, 'add_generate_category_text' ), 10, 2 );
		add_filter( 'wtai_record_category_reviewed_api', array( $this, 'record_category_reviewed_api' ), 10, 4 );
		add_filter( 'wtai_generate_category_status', array( $this, 'get_generate_category_status' ), 10, 3 );
		add_filter( 'wtai_record_freemium_seen_api', array( $this, 'record_freemium_seen_api' ), 10, 1 );
		add_filter( 'wtai_check_freemium_badge_display', array( $this, 'check_freemium_badge_display' ), 10, 1 );
	}

	/**
	 * Get the API base URL.
	 */
	public function set_base_api_url() {
		$api_base_url = wtai_get_api_base_url();

		$this->api_base_url = $api_base_url;
	}

	/**
	 * Debug token and API vars.
	 */
	public function get_token_debug() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_generated_text_options'] ) ) {
			$web_token  = $this->get_web_token();
			$product_id = sanitize_text_field( wp_unslash( $_GET['wtai_generated_text_options'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$meta_keys  = apply_filters( 'wtai_fields', array() );
			$meta_keys  = array_keys( $meta_keys );
			$fields     = array(
				'browsertime'     => -480,
				'fields'          => $meta_keys,
				'autoselectFirst' => true,
				'options'         => 1,
				'token'           => $web_token,
			);
			print '<pre>';
			$results = apply_filters( 'wtai_generate_options_text', array(), $product_id, $fields );
			print_r( $results ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';
			die();
		} elseif ( isset( $_GET['wtai_generated_text_product'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$product_id = sanitize_text_field( wp_unslash( $_GET['wtai_generated_text_product'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			$results    = apply_filters( 'wtai_generate_product_text', array(), $product_id, array( 'historyCount' => 1 ) );
			print '<pre>';
			print_r( $results ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';
			die();
		} elseif ( isset( $_GET['wtai_generate_text_filters'] ) // phpcs:ignore WordPress.Security.NonceVerification
			&& in_array( $_GET['wtai_generate_text_filters'], array( 'Tones', 'Styles', 'Audiences' ), true ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
			die();
		} elseif ( isset( $_GET['wtai_web_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			print '<div style="white-space:pre-wrap;width:500px;word-wrap: anywhere;">';
			print wp_kses( $this->get_web_token(), 'post' );
			print '</div>';
			die();
		} elseif ( isset( $_GET['wtai_api_token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			print '<div style="white-space:pre-wrap;width:500px;word-wrap: anywhere;">';
			print wp_kses( get_option( 'wtai_api_token', '' ), '' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</div>';
			die();
		} elseif ( isset( $_GET['wtai_keywordanalysis_ideas'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$product_id            = 22;
			$fields                = array(
				'keyword'       => array( 'Effortless' ),
				'location_code' => 2840,
			);
			$keywordanalysis_ideas = apply_filters( 'wtai_generate_keywordanalysis_ideas', array(), $product_id, $fields );
			print '<pre>';
			print_r( $keywordanalysis_ideas ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';
			die();
		} elseif ( isset( $_GET['check_keyword_rules'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

			echo 'Global rules <br>';
			print '<pre>';
			print_r( $global_rule_fields ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';

			$credit_array = apply_filters( 'wtai_get_credits_count', array() );
			echo 'Credit count vars <br>';
			print '<pre>';
			print_r( $credit_array ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';

			$generation_credit_vars = wtai_get_generation_limit_vars();
			echo 'Generation credit vars <br>';
			print '<pre>';
			print_r( $generation_credit_vars ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			print '</pre>';

			echo '<br>AVAILABLE CREDITS<br>';
			echo '<pre>';
			print_r( $this->get_available_credits() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';

			echo '<br>IS PREMIUM<br>';
			echo '<pre>';
			print_r( wtai_get_account_credit_details() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';

			echo 'TEST AVAILABLE CREDIT: ';
			echo '<pre>';
			print_r( $this->is_available_credit( 70761 ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';

			echo 'Display Freemium setup badge?: ';
			echo '<pre>';
			print_r( $this->check_freemium_badge_display() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';

			die();
		} elseif ( isset( $_GET['force_etag_check'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$allow_filters = apply_filters( 'wtai_filter_endpoint', array() );

			foreach ( $allow_filters as $type ) {
				$result = apply_filters( 'wtai_generate_text_filters', array(), $type, true );

				echo wp_kses( "Type: $type <br>", 'post' );
				echo '<pre>';
				print_r( $result ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				echo '</pre>';
			}

			echo ' is formal informal supported: ' . wp_kses( wtai_is_formal_informal_lang_supported(), '' ) . '<br>';

			die();
		}
	}

	/**
	 * Get token process.
	 */
	public function get_token_process() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( ( isset( $_GET['page'] ) && 'write-text-ai' === $_GET['page'] ) &&
			isset( $_GET['token'] ) && ! empty( $_GET['token'] ) && // phpcs:ignore WordPress.Security.NonceVerification
			isset( $_GET['region'] ) && ! empty( $_GET['region'] ) // phpcs:ignore WordPress.Security.NonceVerification
			) {

			$get_token_value = isset( $_GET['token'] ) ? sanitize_text_field( wp_unslash( $_GET['token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$token           = $this->get_connect_token_api( $get_token_value );

			if ( $token ) {
				// Record deactivation statistics.
				do_action( 'wtai_record_installation_statistics', 'Step 2', 0 );

				wtai_reset_user_tokens(); // Lets reset all current user tokens so they can generate a new one once installation is done.

				$is_token_expired = wtai_is_token_expired();

				if ( ! $is_token_expired ) {
					wtai_reset_user_preferences( 'setup' ); // Lets reset all user preferences so they can start fresh.
				}

				$token_old = get_option( 'wtai_api_token_old', '' );

				if ( $token_old !== $get_token_value && ! $is_token_expired ) {
					// Reset last activity records for product and category.
					wtai_reset_product_activity_meta();
					wtai_reset_category_activity_meta();
				}

				update_option( 'wtai_api_token_old', '' );
				update_option( 'wtai_api_token', $get_token_value );
				update_option( 'wtai_api_token_time', strtotime( '+364 Days' ) );
				update_option( 'wtai_api_token_last_checked', strtotime( current_time( 'mysql' ) ) );

				$region = isset( $_GET['region'] ) ? sanitize_text_field( wp_unslash( $_GET['region'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $region ) {
					update_option( 'wtai_api_base_url', $region );
				}

				do_action( 'wtai_default_generate_text_filters' );

				$installation_step = intval( get_option( 'wtai_installation_step', 1 ) );

				if ( 1 === $installation_step ) {
					update_option( 'wtai_installation_step', 2 );
				}
			}

			// Redirect back to wta.
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit;
		} elseif ( isset( $_GET['wtai_reset'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wtai_reset_all_settings();
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit();
		} elseif ( isset( $_GET['wtai_token_expire'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			update_option( 'wtai_api_token_time', '' );
		} elseif ( isset( $_GET['wtai_reset_generate_bulk'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wtai_record_all_bulk_requests( array() );
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit();
		} elseif ( isset( $_GET['page'] ) && 'write-text-ai' === $_GET['page'] && isset( $_GET['force_reset_etag'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit;
		}
	}

	/**
	 * Get the filter endpoint.
	 *
	 * @param string $endpoints Endpoint types.
	 */
	public function get_filter_endpoint( $endpoints ) {
		$endpoints = array( 'Tones', 'Styles', 'Audiences', 'FormalLanguages', 'FormalLanguageSupport', 'disallowedCombinations' );
		return $endpoints;
	}

	/**
	 * Get the metakey to apikey mapping.
	 *
	 * @param string $meta_key Meta key to check.
	 * @param string $record_type Record type.
	 */
	public function get_metakey_to_apikey( $meta_key, $record_type = 'product' ) {
		if ( 'category' === strtolower( $record_type ) ) {
			if ( 'page_title' === $meta_key ) {
				$meta_key = 'category_page_title';
			}
			if ( 'page_description' === $meta_key ) {
				$meta_key = 'category_page_description';
			}
			if ( 'open_graph' === $meta_key ) {
				$meta_key = 'category_open_graph';
			}
		}

		$parsed_key = '';
		switch ( $meta_key ) {
			case 'page_title':
				$parsed_key = 'page title';
				break;
			case 'category_page_title':
				$parsed_key = 'category page title';
				break;
			case 'page_description':
				$parsed_key = 'page description';
				break;
			case 'category_page_description':
				$parsed_key = 'category page description';
				break;
			case 'product_excerpt':
				$parsed_key = 'excerpt';
				break;
			case 'product_description':
				$parsed_key = 'product description';
				break;
			case 'category_description':
				$parsed_key = 'category description';
				break;
			case 'long_product_description':
				$parsed_key = 'long description';
				break;
			case 'long':
				$parsed_key = 'long description';
				break;
			case 'short':
				$parsed_key = 'short description';
				break;
			case 'open_graph':
				$parsed_key = 'open graph text';
				break;
			case 'category_open_graph':
				$parsed_key = 'category open graph text';
				break;
			case 'page title':
				$parsed_key = 'page_title';
				break;
			case 'category page title':
				$parsed_key = 'page_title';
				break;
			case 'page description':
				$parsed_key = 'page_description';
				break;
			case 'category page description':
				$parsed_key = 'page_description';
				break;
			case 'excerpt':
				$parsed_key = 'product_excerpt';
				break;
			case 'product description':
				$parsed_key = 'product_description';
				break;
			case 'long description':
				$parsed_key = 'long_product_description';
				break;
			case 'open graph text':
				$parsed_key = 'open_graph';
				break;
			case 'category open graph text':
				$parsed_key = 'open_graph';
				break;
			case 'category description':
				$parsed_key = 'category_description';
				break;
			case 'not_generated':
				$parsed_key = '';
				break;
			case 'generated':
				$parsed_key = 'Generated';
				break;
			case 'transfered':
				$parsed_key = 'Transferred';
				break;
			case 'edited':
				$parsed_key = 'Edited';
				break;
			case 'reviewed':
				$parsed_key = 'Reviewed';
				break;
			default:
				$parsed_key = $meta_key;
				break;
		}

		return $parsed_key;
	}

	/**
	 * Validate account token.
	 */
	public function validate_account_token() {
		$token_validated = false;
		// phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
		if ( ! $this->verify_site_token() && 5 === intval( get_option( 'wtai_installation_step', 1 ) ) ) {
			// Validate token here.
			$token_validated = true;
		}
	}

	/**
	 * Validate etag token.
	 *
	 * @param string $force_reset Force reset.
	 */
	public function validate_etag_token( $force_reset = '' ) {
		global $pagenow;

		$check_etag = false;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( is_user_logged_in() &&
			( ( isset( $_GET['page'] ) && ( 'write-text-ai' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] ) ) // phpcs:ignore WordPress.Security.NonceVerification
			|| ( isset( $pagenow ) && 'plugins.php' === $pagenow ) ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
			$check_etag = true;
		}

		if ( ! $check_etag ) {
			return;
		}

		$token = $this->get_web_token();

		if ( ! $token ) {
			update_option( 'wtai_force_version_update', '' );
			update_option( 'wtai_latest_version_outdated', '' );
			update_option( 'wtai_latest_version_available', '' );
			update_option( 'wtai_latest_version_message', '' );

			return;
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Etags',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $token,
		);
		$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( 200 === intval( $content['http_header'] ) ) {
			$content['result'] = json_decode( $content['result'], true );
			$etags             = $content['result'];

			foreach ( $etags as $etag_key => $etag_value ) {
				$active_languages = apply_filters( 'wtai_language_active', array() );
				foreach ( $active_languages as $active_language ) {
					$language_suffix  = str_replace( '_', '-', str_replace( '_formal', '', $active_language ) );
					$etag_key_opt     = $etag_key . '_' . $language_suffix;
					$option_key       = 'wtai_filters_' . $etag_key_opt . '_etag';
					$option_ref_value = get_option( $option_key, '' );
					$etag_value       = str_replace( '"', '', $etag_value );
					if ( ( $option_ref_value && $option_ref_value !== $etag_value && ! $force_reset ) || $force_reset ) {
						update_option( $option_key, '' );
					}
				}
			}

			// Check if plugin version is outdated.
			$response_headers = $content['headers'];

			$wtai_version = wtai_get_version();
			$wtai_version = str_replace( '-dev', '', $wtai_version );

			if ( isset( $response_headers['writetextai-latestversion'] ) ) {
				if ( isset( $response_headers['writetextai-latestversion'][0] )
					&& version_compare( $response_headers['writetextai-latestversion'][0], $wtai_version, '>' ) ) {
					$latest_plugin_version = isset( $response_headers['writetextai-pluginversionmessage'] ) ? $response_headers['writetextai-pluginversionmessage'][0] : '';

					if ( ! $latest_plugin_version ) {
						$latest_plugin_version = __( 'This WriteText.ai plugin version is outdated. Please update to the latest version.', 'writetext-ai' );
					}

					update_option( 'wtai_force_version_update', '' );
					update_option( 'wtai_latest_version_outdated', '1' );
					update_option( 'wtai_latest_version_available', $response_headers['writetextai-latestversion'][0] );
					update_option( 'wtai_latest_version_message', $latest_plugin_version );
				} else {
					update_option( 'wtai_force_version_update', '' );
					update_option( 'wtai_latest_version_outdated', '' );
					update_option( 'wtai_latest_version_available', '' );
					update_option( 'wtai_latest_version_message', '' );
				}
			} else {
				update_option( 'wtai_force_version_update', '' );
				update_option( 'wtai_latest_version_outdated', '' );
				update_option( 'wtai_latest_version_available', '' );
				update_option( 'wtai_latest_version_message', '' );
			}
		} elseif ( 400 === intval( $content['http_header'] ) ) {
				$result = json_decode( $content['result'], true );

				$latest_plugin_version = __( 'This WriteText.ai plugin version is outdated. Please update to the latest version.', 'writetext-ai' );
			if ( isset( $result['error'] ) ) {
				$latest_plugin_version = $result['error'];
			}

				update_option( 'wtai_force_version_update', '1' );
				update_option( 'wtai_latest_version_outdated', '1' );
				update_option( 'wtai_latest_version_available', '' );
				update_option( 'wtai_latest_version_message', $latest_plugin_version );
		} else {
			update_option( 'wtai_force_version_update', '' );
			update_option( 'wtai_latest_version_outdated', '' );
			update_option( 'wtai_latest_version_available', '' );
			update_option( 'wtai_latest_version_message', '' );
		}
	}

	/**
	 * Get generate bulk data.
	 *
	 * @param array $output Output.
	 * @param array $jobs Jobs.
	 * @param bool  $get_generated_texts Get generated texts.
	 * @param bool  $show_hidden Show hidden.
	 */
	public function get_generate_bulk_data( $output = array(), $jobs = array(), $get_generated_texts = true, $show_hidden = false ) {
		if ( ! $jobs ) {
			return;
		}

		$estimated_text = __( 'Estimated time remaining', 'writetext-ai' );

		$current_user_id = get_current_user_id();
		$meta_keys       = apply_filters( 'wtai_fields', array() );
		$meta_keys       = array_keys( $meta_keys );

		$generated_texts = array();

		$shown_show = false;

		ob_start();

		$job_ctr                     = 0;
		$total_jobs                  = count( $jobs );
		$jobs_user_ids               = array();
		$job_loader_data             = array();
		$job_completed_data          = array();
		$has_ongoing_generation_jobs = 0;
		$has_ongoing_transfer_jobs   = 0;
		$has_error                   = false;

		$finished_product_ids = array();

		if ( $jobs ) {
			$ok_ctr = 0;
			foreach ( $jobs as $job ) {
				$user_id     = $job['user_id'];
				$request_id  = $job['request_id'];
				$product_ids = $job['product_ids'];
				$request     = $job['request'];
				$type        = $job['type'];

				$user = get_user_by( 'ID', $user_id );

				$jobs_user_ids[] = $user_id;

				$job_status        = '';
				$job_is_cancelled  = false;
				$job_is_cancelling = false;

				$generation_timed_out          = false;
				$generation_timed_out_nonowner = false;
				$is_ok_enabled                 = false;
				if ( 'generate' === $type ) {
					if ( ! empty( $product_ids ) && $get_generated_texts ) {

						foreach ( $product_ids as $product_id ) {
							$fields               = array(
								'fields' => $meta_keys,
							);
							$trim_text            = isset( $_REQUEST['trim_text'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['trim_text'] ) ) : 15; // phpcs:ignore WordPress.Security.NonceVerification
							$api_generate_product = apply_filters( 'wtai_generate_product_text', array(), $product_id, $fields );

							foreach ( $api_generate_product[ $product_id ] as $api_field_key => $api_field_value ) {
								$api_field_value = reset( $api_field_value );
								$text_value      = $api_field_value['value'];
								switch ( $api_field_key ) {
									case 'page_title':
										$generated_texts[ $product_id ][ $api_field_key ]['trim'] = ( $trim_text && $text_value ) ? wp_trim_words( $text_value, $trim_text, null ) : $text_value;
										$generated_texts[ $product_id ][ $api_field_key ]['text'] = wpautop( nl2br( $text_value ) );
										break;
									case 'page_description':
										$generated_texts[ $product_id ][ $api_field_key ]['trim'] = ( $trim_text && $text_value ) ? wp_trim_words( $text_value, $trim_text, null ) : $text_value;
										$generated_texts[ $product_id ][ $api_field_key ]['text'] = wpautop( nl2br( $text_value ) );
										break;
									case 'open_graph':
										$generated_texts[ $product_id ][ $api_field_key ]['trim'] = ( $trim_text && $text_value ) ? wp_trim_words( $text_value, $trim_text, null ) : $text_value;
										$generated_texts[ $product_id ][ $api_field_key ]['text'] = wpautop( nl2br( $text_value ) );
										break;
									case 'product_description':
										$generated_texts[ $product_id ][ $api_field_key ]['trim'] = ( $trim_text && $text_value ) ? wp_trim_words( $text_value, $trim_text, null ) : $text_value;
										$generated_texts[ $product_id ][ $api_field_key ]['text'] = wpautop( nl2br( $text_value ) );
										break;
									case 'product_excerpt':
										$generated_texts[ $product_id ][ $api_field_key ]['trim'] = ( $trim_text && $text_value ) ? wp_trim_words( $text_value, $trim_text, null ) : $text_value;
										$generated_texts[ $product_id ][ $api_field_key ]['text'] = wpautop( nl2br( $text_value ) );
										break;
								}

								$current_time     = current_time( 'mysql' );
								$current_time_gmt = current_time( 'mysql', 1 );
								$time             = strtotime( $current_time );

								$generated_texts[ $product_id ]['generate_date'] = sprintf(
									/* translators: %1$s: date  %2$s time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $time ),
									date_i18n( get_option( 'time_format' ), $time )
								);

								update_post_meta( $product_id, 'wtai_generate_date', $time );

								$post_data = array(
									'ID'                => $product_id,
									'post_modified'     => $current_time,
									'post_modified_gmt' => $current_time_gmt,
								);

								wp_update_post( $post_data );
							}
						}
					}

					$request_job_status = $request['status'];

					// Get bulk percentage.
					$completed_ids  = $request['completedIds'];
					$bulk_completed = isset( $request['completedIds'] ) && is_array( $request['completedIds'] ) ? count( $request['completedIds'] ) : 0;
					$bulk_total     = (int) $request['total'];

					if ( 'Completed' === $request_job_status ) {
						$bulk_perc  = 100;
						$job_status = 'done';

						$bulk_completed = $bulk_total;

						$job_completed_data[] = $job;
					} elseif ( $bulk_completed < $bulk_total ) {
							$bulk_perc  = $bulk_completed / $bulk_total;
							$bulk_perc *= 100;
							$bulk_perc  = number_format( $bulk_perc, 0, '', '' );

							$has_ongoing_generation_jobs = 1;

							$job_status = 'ongoing';
					} else {
						$bulk_perc  = 100;
						$job_status = 'done';

						$job_completed_data[] = $job;
					}

					$bulk_remaining = $bulk_total - $bulk_completed;

					// Get bulk estimate count.
					$start = $request['startTime'] ? strtotime( $request['startTime'] ) : '';
					$end   = $request['estimatedEndTime'] ? strtotime( $request['estimatedEndTime'] ) : '';
					if ( $start && $end ) {
						if ( $end > $start ) {
							$secs = ( $end - $start );
							if ( $secs < 60 ) {
								$secs          = $secs . ' ' . __( 'second/s', 'writetext-ai' );
								$bulk_estimate = '(' . $estimated_text . ' ' . $secs . ')';
							} else {
								$secs = $secs / 60;
								$secs = number_format( $secs, 0, '', '' );

								$secs          = $secs . ' ' . __( 'minute/s', 'writetext-ai' );
								$bulk_estimate = '(' . $estimated_text . ' ' . $secs . ')';
							}
						}
					}

					$button_class = 'bulk-generate-cancel';
					$button       = __( 'Cancel', 'writetext-ai' );
					if ( $bulk_completed === $bulk_total ) {
						$button_class  = 'wtai-bulk-generate-submit button-primary';
						$button        = __( 'OK', 'writetext-ai' );
						$is_ok_enabled = true;
					}

					if ( 'TimedOut' === $request_job_status ) {
						$generation_timed_out = true;
						$has_error            = true;

						if ( $current_user_id !== $user_id ) {
							$button       = __( 'OK', 'writetext-ai' );
							$button_class = 'wtai-bulk-generate-submit button-primary';

							$generation_timed_out_nonowner = true;
							$is_ok_enabled                 = true;
						}
					}

					if ( 'Cancelled' === $request_job_status ) {
						$job_is_cancelled = true;
						wtai_clear_user_bulk_generation( $request_id );
						continue; // Lets not display this and clear it from the db.
					}
					if ( 'Cancelling' === $request_job_status ) {
						$job_is_cancelling = true;
					}
				} elseif ( 'transfer' === $type ) {
					$button_class = '';

					$completed_ids = $job['completed_ids'];
					if ( $completed_ids ) {
						$completed_ids = array_unique( $completed_ids );
					}

					$bulk_completed = count( $completed_ids );
					$bulk_total     = count( $product_ids );
					if ( $bulk_completed < $bulk_total ) {
						$bulk_perc  = $bulk_completed / $bulk_total;
						$bulk_perc *= 100;
						$bulk_perc  = number_format( $bulk_perc, 0, '', '' );

						$has_ongoing_transfer_jobs = 1;
						$job_status                = 'ongoing';
					} else {
						$bulk_perc            = 100;
						$job_status           = 'done';
						$job_completed_data[] = $job;
						$is_ok_enabled        = true;
					}

					$bulk_remaining = $bulk_total - $bulk_completed;
				}

				if ( $is_ok_enabled ) {
					$finished_product_ids = array_merge( $finished_product_ids, $product_ids );
				}

				$is_own_generation = 'yes';
				if ( $current_user_id !== $user_id ) {
					if ( 100 !== $bulk_perc ) {
						if ( ! $generation_timed_out_nonowner ) {
							$button_class .= ' disabled ';
						}
					}

					$is_own_generation = 'no';
				}

				if ( $job_is_cancelling ) {
					$button_class .= ' disabled ';
				}

				// Disable cancel button for generation <= 2 products.
				if ( $bulk_remaining <= 2 && 'generate' === $type && 'done' !== $job_status ) {
					$button_class .= ' disabled ';
				}

				$loader_container_class = '';
				if ( $job_ctr > 0 ) {
					$loader_container_class = 'wtai-loading-estimate-time-container-others';
				}

				if ( $is_ok_enabled ) {
					++$ok_ctr;
				}

				$loader_container_class .= ' wtai-loading-estimate-time-container-user-' . $user_id;
				$loader_container_class .= ' wtai-loading-estimate-time-container-' . $type;

				?>
				<div class="wtai-loading-estimate-time-container wtai-loading-estimate-time-container-<?php echo esc_attr( $job_ctr ); ?> <?php echo esc_attr( $loader_container_class ); ?> <?php echo esc_attr( 'wtai-' . $job_status ); ?>" 
					data-product-ids="<?php echo is_array( $product_ids ) ? esc_attr( implode( ',', $product_ids ) ) : ''; ?>" 
					data-completed-ids="<?php echo is_array( $completed_ids ) ? esc_attr( implode( ',', $completed_ids ) ) : ''; ?>" 
					data-is-own="<?php echo esc_attr( $is_own_generation ); ?>" 
					data-user-id="<?php echo esc_attr( $user_id ); ?>"
					data-job-status="<?php echo esc_attr( $job_status ); ?>"	
				>
				<?php
						$class = '';
				if ( 100 === $bulk_perc ) {
					$class = 'wtai-done';
				} else {
					$class = 'wtai-ongoing';
				}

				if ( $generation_timed_out ) {
					$class = 'wtai-bulk-error';
				}
				if ( $job_is_cancelling ) {
					$class = 'wtai-bulk-cancelling';
				}
				if ( $job_is_cancelled ) {
					$class = 'wtai-bulk-cancelled';
				}
				?>
					<?php
					ob_start();
					if ( 'generate' === $type ) {
						?>
					<div class="wtai-loading-details-container wtai-d-flex <?php echo esc_attr( $class ); ?>">
						<div class="wtai-bulk-generate-check-ico-wrap">
							<span class="wtai-bulkgenerate-check-ico" ></span>
						</div>
						<div class="wtai-loading-header-details wtai-d-flex">
							<div class="wtai-bulk-generate-headline-txt-wrapper wtai-d-flex">
								<div class="wtai-loading-header-text">
									<span class="wtai-bulk-generate-headline-txt" data-initial-text="<?php echo wp_kses( __( 'Generating text', 'writetext-ai' ), 'post' ); ?>..." >
										<?php
										if ( $generation_timed_out ) {
											echo wp_kses( __( 'WriteText.ai is currently experiencing a high volume of requests and has timed out.', 'writetext-ai' ), 'post' ) . '  ';

											if ( $current_user_id === $user_id ) {
												echo '<a href="#" class="wtai-bulk-retry-link" data-request-id="' . esc_attr( $request_id ) . '" >' . wp_kses( __( 'Try again', 'writetext-ai' ), 'post' ) . '</a>';
											}
										} elseif ( $job_is_cancelling ) {
											echo wp_kses( __( 'Cancelling...', 'writetext-ai' ), 'post' );
										} elseif ( $job_is_cancelled ) {
											echo wp_kses( __( 'Cancelled', 'writetext-ai' ), 'post' );
										} elseif ( 100 === $bulk_perc ) {
												echo wp_kses( __( 'WriteText.ai is done generating text for your selected products.', 'writetext-ai' ), 'post' );
										} else {
											echo wp_kses( __( 'Generating text...', 'writetext-ai' ), 'post' );
											echo '<span class="wtai-estimated-time">' . wp_kses( $bulk_estimate, 'post' ) . '</span>';
										}
										?>
									</span>
									<span class="wtai-generate-username" ><?php echo wp_kses( $user->display_name, 'post' ); ?></span>
								</div>
								<?php
								if ( ! $generation_timed_out ) {
									if ( 0 === $bulk_completed && 0 === $bulk_total ) {
										$bulk_completed = 1;
										$bulk_total     = 1;
									}
									?>
									<div class="wtai-loading-header-number"><span><?php echo wp_kses( $bulk_completed, 'post' ); ?> / <?php echo wp_kses( $bulk_total, 'post' ); ?></span> <?php echo wp_kses( __( 'product/s', 'writetext-ai' ), 'post' ); ?></div>		
									<?php
								}
								?>
							</div>
							<div class="wtai-loading-loader-msg-wrapper">	
								<div class="wtai-loading-loader-wrapper">
									<div class="wtai-main-loading" <?php echo esc_attr( $bulk_perc ) ? 'style="width:' . esc_attr( $bulk_perc ) . '%"' : ''; ?>></div>
								</div>
							</div>
						</div>
					</div>
					<div class="wtai-loading-actions-container" >
						<a href="#" data-request-id="<?php echo esc_attr( $request_id ); ?>" class="button action-bulk-generate <?php echo esc_attr( $button_class ); ?>"><?php echo wp_kses( $button, 'post' ); ?></a>
					</div>

						<?php
					} elseif ( 'transfer' === $type ) {
						if ( 100 === $bulk_perc ) {
							$class          = 'wtai-done';
							$bulk_completed = $bulk_total;
						} else {
							$class = 'wtai-ongoing';
						}
						?>
						<div class="wtai-loading-details-container wtai-d-flex <?php echo esc_attr( $class ); ?>">
							<div class="wtai-bulk-generate-check-ico-wrap">
								<span class="wtai-bulkgenerate-check-ico" ></span>
							</div>
							<div class="wtai-loading-header-details wtai-d-flex">
								<div class="wtai-bulk-generate-headline-txt-wrapper wtai-d-flex">
									<div class="wtai-loading-header-text">
										<span class="wtai-bulk-generate-headline-txt" >
											<?php
											if ( 100 === $bulk_perc ) {
												echo wp_kses( __( 'Done transferring text for your selected products.', 'writetext-ai' ), 'post' );
											} else {
												echo wp_kses( __( 'Transferring text', 'writetext-ai' ), 'post' ) . '...';
												echo '<span class="wtai-estimated-time"></span>';
											}
											?>
										</span>
										<span class="wtai-generate-username" ><?php echo wp_kses( $user->display_name, 'post' ); ?></span>
									</div>
									<div class="wtai-loading-header-number mcrtransfer"><span><?php echo wp_kses( $bulk_completed, 'post' ); ?> / <?php echo wp_kses( $bulk_total, 'post' ); ?></span> <?php echo wp_kses( __( 'product/s', 'writetext-ai' ), 'post' ); ?></div>
								</div>
								<div class="wtai-loading-loader-msg-wrapper">
									<div class="wtai-loading-loader-wrapper">
										<div class="wtai-main-loading " <?php echo $bulk_perc ? 'style="width:' . esc_attr( $bulk_perc ) . '%"' : ''; ?> ></div>
									</div>
								</div>
							</div>
						</div>
						<div class="wtai-loading-actions-container" >
							<?php
							$remaining_items        = $bulk_total - $bulk_completed;
							$button_class_cancel    = '';
							$button_ok_hidden_class = '';
							if ( $remaining_items <= 2 ) {
								$button_class_cancel .= ' disabled ';
							}

							$hide_cancel = '';
							$hide_ok     = '';
							if ( 100 === intval( $bulk_perc ) ) {
								$hide_cancel          = ' display: none; ';
								$button_class_cancel .= ' wtai-bulk-transfer-cancel-btn-hidden ';
							} else {
								$hide_ok                 = 'display: none; ';
								$button_ok_hidden_class .= ' bulk-transfer-ok-btn-hidden ';
							}
							?>
							<a href="#" class="button wtai-action-bulk-transfer-cancel <?php echo esc_attr( $button_class ) . ' ' . esc_attr( $button_class_cancel ); ?>" style="<?php echo esc_attr( $hide_cancel ); ?>" ><?php echo wp_kses( __( 'Cancel', 'writetext-ai' ), 'post' ); ?></a>
							<a href="#" class="button wtai-action-bulk-transfer button-primary <?php echo esc_attr( $button_class ) . ' ' . esc_attr( $button_ok_hidden_class ); ?>" style="<?php echo esc_attr( $hide_ok ); ?>" ><?php echo wp_kses( __( 'OK', 'writetext-ai' ), 'post' ); ?></a>
						</div>
						<?php
					}

					$inner_html = ob_get_clean();
					echo wp_kses( $inner_html, 'post' );
					?>
				</div>
				<?php

				$job_loader_data[ $user_id ] = array(
					'job_data' => $job,
					'html'     => $inner_html,
				);

				++$job_ctr;
			}
		}

		$html = ob_get_clean();

		$wtai_bulk_product_ids = wtai_get_bulk_products_ids();

		$output = array(
			'html'                        => $html,
			'generated_texts'             => $generated_texts,
			'product_ids'                 => $wtai_bulk_product_ids,
			'jobs_user_ids'               => $jobs_user_ids,
			'job_loader_data'             => $job_loader_data,
			'has_ongoing_generation_jobs' => $has_ongoing_generation_jobs,
			'has_ongoing_transfer_jobs'   => $has_ongoing_transfer_jobs,
			'job_completed_data'          => $job_completed_data,
			'has_error'                   => $has_error,
			'ok_ctr'                      => $ok_ctr,
			'finished_product_ids'        => $finished_product_ids,
		);

		return $output;
	}

	/**
	 * Get generate bulk progress.
	 */
	public function get_generate_bulk_progress() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$show_hidden     = isset( $_GET['show_hidden'] ) && ( 'yes' === $_GET['show_hidden'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$refresh_credits = isset( $_GET['refresh_credits'] ) && ( 1 === intval( $_GET['refresh_credits'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs            = wtai_get_bulk_generate_jobs( true );

				$output = $this->get_generate_bulk_data( array(), $jobs, true, $show_hidden );

				$finished_product_ids = $output['finished_product_ids'];

				$has_error = 0;
				if ( $output['has_error'] ) {
					$has_error = 1;
				}

				$all_pending_ids = wtai_get_all_pending_bulk_ids( $finished_product_ids );

				// Get default tones/styles/audiences/product attributes to reset the bulk popup.
				$default_style            = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
				$tones_array              = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
				$audiences_array          = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
				$product_attributes_array = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
				$description_min_default  = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_min' );
				$description_max_default  = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' );
				$excerpt_min_default      = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_min' );
				$excerpt_max_default      = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' );

				$default_tones = '';
				if ( ! empty( $tones_array ) ) {
					$default_tones = implode( ',', $tones_array );
				}

				$default_audiences = '';
				if ( ! empty( $audiences_array ) ) {
					$default_audiences = implode( ',', $audiences_array );
				}

				$default_product_attributes = '';
				if ( ! empty( $product_attributes_array ) ) {
					$default_product_attributes = implode( ',', $product_attributes_array );
				}

				$account_credit_details = wtai_get_account_credit_details( $refresh_credits );
				$is_premium             = $account_credit_details['is_premium'];
				$available_credit_count = $credit_account_details['available_credits'];

				$is_premium             = $is_premium ? '1' : '0';
				$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

				echo wp_json_encode(
					array(
						'html'                        => $output['html'],
						'generated_texts'             => $output['generated_texts'],
						'product_ids'                 => $output['product_ids'],
						'jobs_user_ids'               => $output['jobs_user_ids'],
						'job_loader_data'             => $output['job_loader_data'],
						'has_ongoing_generation_jobs' => $output['has_ongoing_generation_jobs'],
						'has_ongoing_transfer_jobs'   => $output['has_ongoing_transfer_jobs'],
						'has_error'                   => $has_error,
						'all_pending_ids'             => $all_pending_ids,
						'default_style'               => $default_style,
						'default_tones'               => $default_tones,
						'default_audiences'           => $default_audiences,
						'default_product_attributes'  => $default_product_attributes,
						'default_desc_min'            => $description_min_default,
						'default_desc_max'            => $description_max_default,
						'default_excerpt_min'         => $excerpt_min_default,
						'default_excerpt_max'         => $excerpt_max_default,
						'is_premium'                  => $is_premium,
						'available_credit_label'      => $available_credit_label,
						'message'                     => '',
					)
				);
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;

				echo wp_json_encode(
					array(
						'html'                        => '',
						'generated_texts'             => array(),
						'product_ids'                 => array(),
						'jobs_user_ids'               => array(),
						'job_loader_data'             => '',
						'has_ongoing_generation_jobs' => array(),
						'has_ongoing_transfer_jobs'   => array(),
						'has_error'                   => 1,
						'all_pending_ids'             => array(),
						'default_style'               => array(),
						'default_tones'               => array(),
						'default_audiences'           => array(),
						'default_product_attributes'  => array(),
						'default_desc_min'            => '',
						'default_desc_max'            => '',
						'default_excerpt_min'         => '',
						'default_excerpt_max'         => '',
						'is_premium'                  => 0,
						'available_credit_label'      => '',
						'message'                     => $message,
					)
				);
			}

			exit;
		}
	}

	/**
	 * Get generate text.
	 */
	public function get_generate_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax                 = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$result                  = array();
		$message_token           = '';
		$access                  = 1;
		$error                   = 0;
		$error_alt_image_message = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$account_credit_details = wtai_get_account_credit_details( true );
				$is_premium             = $account_credit_details['is_premium'];
				$available_credit_count = $credit_account_details['available_credits'];

				$is_premium             = $is_premium ? '1' : '0';
				$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {

						$credit_count_needed = isset( $_POST['creditCountNeeded'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['creditCountNeeded'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						// Maybe clear current user transfer data.
						$clear_response = wtai_clear_user_bulk_transfer();

						$doing_bulk_generation = isset( $_POST['doingBulkGeneration'] ) ? sanitize_text_field( wp_unslash( $_POST['doingBulkGeneration'] ) ) : '0'; // phpcs:ignore WordPress.Security.NonceVerification

						if ( 0 === intval( $is_premium ) &&
							( 1 === intval( $doing_bulk_generation ) ||
							( isset( $_POST['rewriteText'] ) && 1 === intval( $_POST['rewriteText'] ) ) || // phpcs:ignore WordPress.Security.NonceVerification
							( isset( $_POST['referenceProductID'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['referenceProductID'] ) ) ) || // phpcs:ignore WordPress.Security.NonceVerification
							( isset( $_POST['otherproductdetails'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['otherproductdetails'] ) ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
							echo wp_json_encode(
								array(
									'results'     => null,
									'access'      => $access,
									/* translators: %s: Premium url */
									'message'     => sprintf( __( '<a href="%s" target="_blank" >Premium</a> is required to do this action.', 'writetext-ai' ), WTAI_PREMIUM_SUBSCRIPTION_LINK ),
									'is_premium'  => $is_premium,
									'api_results' => array(),
								)
							);
							exit;
						}

						$product_ids             = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$generated               = isset( $_POST['save_generated'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$single_result           = isset( $_POST['single_result'] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime             = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$include_ranked_keywords = isset( $_POST['includeRankedKeywords'] ) ? sanitize_text_field( wp_unslash( $_POST['includeRankedKeywords'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$special_instructions    = isset( $_POST['specialInstructions'] ) ? sanitize_text_field( wp_unslash( $_POST['specialInstructions'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

						$values = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
						);

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['fields'] ) ) {
							$values['fields'] = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['fields'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						} else {
							$fields           = apply_filters( 'wtai_fields', array() );
							$values['fields'] = array_keys( $fields );
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['attr_fields'] ) ) {
							$values['attr_fields'] = isset( $_POST['attr_fields'] ) ? sanitize_text_field( wp_unslash( $_POST['attr_fields'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['options'] ) && is_numeric( $_POST['options'] ) ) {
							$values['options'] = isset( $_POST['options'] ) ? sanitize_text_field( wp_unslash( $_POST['options'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['otherproductdetails'] ) ) {
							$values['otherproductdetails'] = isset( $_POST['otherproductdetails'] ) ? sanitize_text_field( wp_unslash( $_POST['otherproductdetails'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						if ( $generated ) {
							$values['autoselectFirst'] = true;
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['tones'] ) ) {
							$values['tones'] = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['tones'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['tones'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['tones'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$values['tones'] = array_filter( $values['tones'] );
						} else {
							$values['tones'] = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['audiences'] ) ) {
							$values['audiences'] = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['audiences'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['audiences'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['audiences'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						} else {
							$values['audiences'] = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['customAudience'] ) ) {
							$values['customAudience'] = isset( $_POST['customAudience'] ) ? sanitize_text_field( wp_unslash( $_POST['customAudience'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['semanticKeywords'] ) ) {
							$values['semanticKeywords'] = isset( $_POST['semanticKeywords'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['semanticKeywords'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['keywords'] ) ) {
							$values['keywords'] = isset( $_POST['keywords'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['styles'] ) ) {
							$values['styles'] = isset( $_POST['styles'] ) ? sanitize_text_field( wp_unslash( $_POST['styles'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						} else {
							$values['styles'] = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['formalLanguage'] ) ) {
							$values['formalLanguage'] = isset( $_POST['formalLanguage'] ) ? sanitize_text_field( wp_unslash( $_POST['formalLanguage'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['customTone'] ) ) {
							$values['customTone'] = isset( $_POST['customTone'] ) ? sanitize_text_field( wp_unslash( $_POST['customTone'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['customStyle'] ) ) {
							$values['customStyle'] = isset( $_POST['customStyle'] ) ? sanitize_text_field( wp_unslash( $_POST['customStyle'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['rewriteText'] ) && 1 === intval( $_POST['rewriteText'] ) ) {
							$values['rewriteText'] = 1;
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['referenceProductID'] ) && '' !== $_POST['referenceProductID'] ) {
							$values['referenceProductID'] = isset( $_POST['referenceProductID'] ) ? sanitize_text_field( wp_unslash( $_POST['referenceProductID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['keywordAnalysisViewsCount'] ) && '' !== sanitize_text_field( wp_unslash( $_POST['keywordAnalysisViewsCount'] ) ) ) {
							$values['keywordAnalysisViews'] = isset( $_POST['keywordAnalysisViewsCount'] ) ? sanitize_text_field( wp_unslash( $_POST['keywordAnalysisViewsCount'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						}

						foreach ( $values['fields']  as $field_key ) {
							// phpcs:ignore WordPress.Security.NonceVerification
							if ( isset( $_POST[ $field_key . '_length_max' ] ) ) {
								$values[ $field_key . '_length_max' ] = isset( $_POST[ $field_key . '_length_max' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_key . '_length_max' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								$values[ $field_key . '_length_max' ] = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field_key . '_max' );
							}

							// phpcs:ignore WordPress.Security.NonceVerification
							if ( isset( $_POST[ $field_key . '_length_min' ] ) ) {
								$values[ $field_key . '_length_min' ] = isset( $_POST[ $field_key . '_length_min' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $field_key . '_length_min' ] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								$values[ $field_key . '_length_min' ] = apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field_key . '_min' );
							}
						}

						$queue_generate = false;
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['queueAPI'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['queueAPI'] ) ) ) {
							$values['queue'] = 1;
							$queue_generate  = true;
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['includeFeaturedImage'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['includeFeaturedImage'] ) ) ) {
							$values['includeFeaturedImage'] = 1;
						}

						$bulk_one_only = false;
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['bulkOneOnly'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['bulkOneOnly'] ) ) ) {
							$bulk_one_only = true;
						}

						$single_list_generate = false;
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['bulkOneOnly'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['bulkOneOnly'] ) ) ) {
							$bulk_one_only = true;
						}

						$is_doing_bulk_generate = false;
						$product_ids_array      = ( false !== strpos( $product_ids, ',' ) ) ? explode( ',', $product_ids ) : array( $product_ids );
						if ( count( $product_ids_array ) > 1 ) {
							$is_doing_bulk_generate = true;
						} elseif ( $queue_generate && ! $bulk_one_only ) {
							$is_doing_bulk_generate = true;
						}

						// Alt image text generation.
						// phpcs:ignore WordPress.Security.NonceVerification
						$alt_image_ids = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['altimages'] ) ) {
							$alt_image_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}

						$values['imageAltTexts'] = $alt_image_ids;

						$error_alt_images = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['altimageserror'] ) ) {
							$error_alt_images = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

							if ( $error_alt_images ) {
								$error_alt_images = array_unique( $error_alt_images );

								ob_start();
								?>
								<div class="wtai-error-thumbnail-wrap" >
									<div class="wtai-error-thumbnail-item" >
										<?php
										foreach ( $error_alt_images as $alt_image_id ) {
											$gallery_image_data = wp_get_attachment_image_src( $alt_image_id, 'thumbnail' );
											$product_thumbnail  = $gallery_image_data[0];
											$image_url          = wp_get_attachment_url( $alt_image_id );
											$image_filename     = basename( $image_url );
											?>
											<div class="wtai-error-thumbnail" data-image-id="<?php echo esc_attr( $alt_image_id ); ?>" >
												<a href="<?php echo esc_url( $image_url ); ?>" target="_blank" ><img src="<?php echo esc_attr( $product_thumbnail ); ?>" title="<?php echo esc_attr( $image_filename ); ?>" /></a>
											</div>
											<?php
										}
										?>
									</div>
								</div>
								<?php
								$error_alt_image_message = ob_get_clean();
							}
						}

						if ( 1 === intval( $include_ranked_keywords ) ) {
							$values['includeRankedKeywords'] = true;
						}

						if ( isset( $special_instructions ) ) {
							$values['specialInstructions'] = $special_instructions;
						}

						$api_results = apply_filters( 'wtai_generate_options_text', array(), $product_ids, $values );

						if ( ! is_array( $api_results ) && ! empty( $api_results ) ) {
							$message_token = $api_results;
						} elseif ( is_array( $api_results ) && ! empty( $api_results ) ) {
							$product_ids = ( strpos( $product_ids, ',' ) !== false ) ? explode( ',', $product_ids ) : array( $product_ids );
							if ( isset( $api_results['requestId'] ) && $api_results['requestId'] ) {
								$results['requestId'] = $api_results['requestId'];

								if ( $queue_generate && ! $bulk_one_only ) {
									foreach ( $product_ids as $product_id ) {
										foreach ( $values['fields'] as $field ) {
											update_post_meta( $product_id, 'wtai_bulk_queue_id_' . $field, $results['requestId'] );
										}
									}
								} else {
									wtai_record_bulk_generation( $api_results['requestId'], $product_ids );
								}
							} else {
								foreach ( $product_ids as $product_id ) {
									// phpcs:ignore WordPress.Security.NonceVerification
									if ( ! isset( $_POST['no_settings_save'] ) ) {
										// phpcs:ignore WordPress.Security.NonceVerification
										if ( isset( $_POST['otherproductdetails'] ) ) {
											update_post_meta( $product_id, 'wtai_otherproductdetails', sanitize_text_field( wp_unslash( $_POST['otherproductdetails'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
										}
									}

									foreach ( $api_results[ $product_id ]  as $result_key => $result_value ) {
										if ( $single_result ) {
											$text_result_value = is_array( $result_value['output'] ) ? reset( $result_value['output'] ) : $result_value['output'];
										} else {
											$text_result_value = $result_value['output'];
										}

										if ( is_array( $text_result_value ) ) {
											$trim = array_map(
												function ( $result_value ) {
													return wp_trim_words( $result_value, 15, null );
												},
												$text_result_value
											);
										} else {
											$trim = wp_trim_words( $text_result_value, 15, null );
										}

										$results[ $product_id ][ $result_key ] = array(
											'text_id' => $result_value['textId'],
											'trim'    => $trim,
											'text'    => $text_result_value,
											'count'   => is_array( $text_result_value ) ? array_map(
												function ( $result_value ) {
													return strlen( $result_value ); },
												$text_result_value
											) : strlen( $text_result_value ),
											'words'   => is_array( $text_result_value ) ? array_map(
												function ( $result_value ) {
													return str_word_count( $result_value ); },
												$text_result_value
											) : str_word_count( $text_result_value ),
											'keyword' => 0,
											'semantic_keyword' => 0,
										);

										if ( $generated ) {
											$browser_offset = isset( $_POST['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) * -1 ) * 60 : 0; // phpcs:ignore WordPress.Security.NonceVerification
											$time           = strtotime( current_time( 'mysql' ) );
											update_post_meta( $product_id, 'wtai_generate_date', $time );

											$results[ $product_id ]['generate_date'] = sprintf(
												/* translators: %1$s: date  %2$s time */
												__( '%1$s at %2$s' ),
												date_i18n( get_option( 'date_format' ), $time ),
												date_i18n( get_option( 'time_format' ), $time )
											);

											$post_data = array(
												'ID' => $product_id,
												'post_modified' => strtotime( $time ),
											);
											wp_update_post( $post_data );
										}
									}
								}

								if ( $single_result ) {
									$results = reset( $results );
								}
							}

							if ( $product_ids ) {
								foreach ( $product_ids as $product_id ) {
									wtai_record_product_last_activity( $product_id, 'generate' );

									foreach ( $values['fields']  as $field_key ) {
										wtai_record_product_field_last_activity( $product_id, 'generate', $field_key );
									}

									if ( $generated ) {
										$browser_offset = isset( $_POST['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) * -1 ) * 60 : 0; // phpcs:ignore WordPress.Security.NonceVerification
										$time           = strtotime( current_time( 'mysql' ) );
										update_post_meta( $product_id, 'wtai_generate_date', $time );

										$results[ $product_id ]['generate_date'] = sprintf(
											/* translators: %1$s: date  %2$s time */
											__( '%1$s at %2$s' ),
											date_i18n( get_option( 'date_format' ), $time ),
											date_i18n( get_option( 'time_format' ), $time )
										);

										$post_data = array(
											'ID' => $product_id,
											'post_modified' => strtotime( $time ),
										);
										wp_update_post( $post_data );
									}
								}
							}
						}
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$access = 0;
				}
			} else {
				$message_token = WTAI_INVALID_NONCE_MESSAGE;
			}

			if ( ! $results && ! $message_token ) {
				$message_token = WTAI_GENERAL_ERROR_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'results'                 => $results,
					'access'                  => $access,
					'message'                 => $message_token,
					'is_premium'              => $is_premium,
					'api_results'             => $api_results,
					'error_alt_image_message' => $error_alt_image_message,
					'available_credit_label'  => $available_credit_label,
				)
			);
			exit;
		}
	}

	/**
	 * Get generate text filters
	 *
	 * @param array  $results Results.
	 * @param string $type    Type.
	 * @param string $force   Force.
	 */
	public function get_generate_text_filters( $results = array(), $type = 'Tones', $force = '' ) {
		$allow_filters          = apply_filters( 'wtai_filter_endpoint', array() );
		$api_type               = ( $type && in_array( $type, $allow_filters, true ) ) ? $type : 'Tones';
		$get_var_type           = strtolower( $api_type );
		$processed_text_filters = false;

		global $global_style_and_tones;

		$token = $this->get_web_token();
		if ( ! $token ) {
			return ( $global_style_and_tones && isset( $global_style_and_tones[ $api_type ] ) ) ? $global_style_and_tones[ $api_type ] : array();
		}

		if ( ! isset( $global_style_and_tones[ $api_type ] ) || $force ) {
			if ( ! is_array( $global_style_and_tones ) ) {
				$global_style_and_tones = array();
			}

			$language                   = apply_filters( 'wtai_language_code', wtai_get_site_language() );
			$get_tsa_type               = $get_var_type . '_' . $language;
			$option_ref_value           = get_option( 'wtai_filters_' . $get_tsa_type . '_etag', '' );
			$option_ref_value_last_date = get_option( 'wtai_filters_' . $get_tsa_type . '_global_settings_last_date_checked', '' );
			$current_time               = strtotime( current_time( 'mysql' ) );

			$refresh = false;
			if ( ! $option_ref_value_last_date ) {
				$refresh = true;
			} else {
				$refresh_time_diff = $current_time - $option_ref_value_last_date;
				$diff_minutes      = $refresh_time_diff / 60;

				if ( $diff_minutes >= 30 ) {
					$refresh = true; // Recheck this every 30 minutes.
				}
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['wtaForceEtagRefresh'] ) && '1' === $_GET['wtaForceEtagRefresh'] ) {
				$refresh = true;
			}

			$check_etag = true;// If match, status is 304, if not status is 200.

			if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
				$check_etag = true;
				$refresh    = true;
			} else {
				$wtai_generate_filters_etag_check = get_transient( 'wtai_generate_filters_etag_check' );

				// Lets check for etag changes every hour to lessen API calls for etags.
				if ( $wtai_generate_filters_etag_check ) {
					$check_etag = false;
					$force      = false;
				} else {
					set_transient( 'wtai_generate_filters_etag_check', '1', MINUTE_IN_SECONDS * 5 );
				}
			}

			if ( ! $option_ref_value || $force || $refresh ) {

				if ( $force || $refresh ) {
					$check_etag = false;
				}

				$etag_settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Etags',
				);
				$etag_headers  = array(
					'Cache-Control' => 'no-cache',
					'Authorization' => 'Bearer ' . $token,
					'If-None-Match' => $option_ref_value,
				);
				$etag_content  = $this->get_data_via_api( array(), $etag_settings, $etag_headers, 'GET' );

				if ( 304 === intval( $etag_content['http_header'] ) && $check_etag ) {
					// Lets do nothing since nothing has changed since our last api fetch.
					$processed_text_filters = false;
				} else {
					$settings = array(
						'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/' . $api_type,
					);

					$headers = array(
						'Cache-Control'   => 'no-cache',
						'Authorization'   => 'Bearer ' . $token,
						'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
					);

					$content = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

					if ( 200 === intval( $content['http_header'] ) ) {
						$content['result']   = json_decode( $content['result'], true );
						$api_content_results = $content['result'];

						if ( 'disallowedCombinations' === $api_type ) {
							$results[] = $api_content_results[0];
						} else {
							$default_values = array();
							foreach ( $api_content_results as $api_content_result ) {
								switch ( $api_type ) {
									case 'FormalLanguages':
										$value_name = $api_content_result;
										unset( $value_name['id'] );
										$results[ $api_content_result['id'] ] = $value_name;
										break;
									case 'FormalLanguageSupport':
										$value_name = $api_content_result;
										$results[]  = $value_name;
										break;
									default:
										$results[ $api_content_result['id'] ] = $api_content_result['name'];

										if ( 'Tones' === $api_type || 'Styles' === $api_type || 'Audiences' === $api_type ) {
											if ( $api_content_result['default'] ) {
												$default_values[] = $api_content_result['id'];
											}
										}

										break;
								}
							}

							if ( $default_values && defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
								$results[ $api_type . '_defaults' ] = $default_values;
							}
						}
					}

					if ( isset( $content['headers']['etag'][0] ) ) {
						unset( $results[ $api_type . '_defaults' ] ); // Unset the temp default values.

						$content['headers']['etag'][0] = str_replace( '"', '', $content['headers']['etag'][0] );
						update_option( 'wtai_filters_' . $get_tsa_type . '_value', $results );
						update_option( 'wtai_filters_' . $get_tsa_type . '_etag', $content['headers']['etag'][0] );
					}
				}

				update_option( 'wtai_filters_' . $get_tsa_type . '_global_settings_last_date_checked', strtotime( current_time( 'mysql' ) ) );
			} else {
				$results = get_option( 'wtai_filters_' . $get_tsa_type . '_value', array() );
			}

			$global_style_and_tones[ $api_type ] = $results;
		}

		return $global_style_and_tones[ $api_type ];
	}

	/**
	 * Get global rule fields.
	 *
	 * @param array $results Results.
	 */
	public function get_global_rule_fields( $results = array() ) {
		global $global_rule_fields;

		$token = $this->get_web_token();
		if ( ! $token ) {
			return $global_rule_fields;
		}

		if ( ! is_array( $global_rule_fields ) ) {
			$global_rule_fields = array();
			$language           = apply_filters( 'wtai_language_code', wtai_get_site_language() );
			$get_tsa_type       = 'rules_' . $language;
			$option_ref_value   = get_option( 'wtai_filters_' . $get_tsa_type . '_etag', '' );

			if ( ! $option_ref_value ) {
				$settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Rules',
				);

				$headers = array(
					'Cache-Control' => 'no-cache',
					'Authorization' => 'Bearer ' . $token,
				);

				$content = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

				if ( 200 === intval( $content['http_header'] ) ) {
					$content['result'] = json_decode( $content['result'], true );
					$results           = $content['result'];
				}

				if ( isset( $content['headers']['etag'][0] ) ) {
					$content['headers']['etag'][0] = str_replace( '"', '', $content['headers']['etag'][0] );

					update_option( 'wtai_filters_rules_value', $results );
					update_option( 'wtai_filters_rules_etag', $content['headers']['etag'][0] );
				}
			} else {
				$results = get_option( 'wtai_filters_rules_value', array() );
			}

			$global_rule_fields = $results;
		}

		return $global_rule_fields;
	}

	/**
	 * Process default generate text filters.
	 */
	public function process_default_generate_text_filters() {
		$resave_opt = get_option( 'wtai_installation_style_and_tone_reset', '' );
		$token      = $this->get_web_token();
		if ( ! $resave_opt && $token ) {
			foreach ( array( 'Tones', 'Styles', 'Audiences' ) as $type ) {

				$settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/' . $type,
				);
				$headers  = array(
					'Cache-Control'   => 'no-cache',
					'Authorization'   => 'Bearer ' . $token,
					'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
				);
				$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );
				if ( 200 === intval( $content['http_header'] ) ) {
					$default_values      = ( 'Styles' === $type ) ? '' : array();
					$content['result']   = json_decode( $content['result'], true );
					$api_content_results = $content['result'];
					foreach ( $api_content_results as $api_content_result ) {
						if ( ! $api_content_result['default'] ) {
							continue;
						}
						if ( 'Styles' === $type ) {
							$default_values = $api_content_result['id'];
							break;
						} else {
							$default_values[] = $api_content_result['id'];
						}
					}
					update_option( 'wtai_installation_' . strtolower( $type ), $default_values );
				}
			}
			update_option( 'wtai_installation_style_and_tone_reset', '1' );
		}
	}

	/**
	 * Add store grid text.
	 */
	public function add_store_grid_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax                       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results                       = array();
		$results_alt                   = array();
		$message_token                 = '';
		$access                        = 1;
		$no_data_to_transfer           = 0;
		$to_transfer_array             = array();
		$to_transfer_last_action_array = array();
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$account_credit_details = wtai_get_account_credit_details();
					$is_premium             = $account_credit_details['is_premium'];

					$is_premium = $is_premium ? '1' : '0';

					$doing_bulk_transfer = isset( $_POST['isDoingBulkTransfer'] ) ? sanitize_text_field( wp_unslash( $_POST['isDoingBulkTransfer'] ) ) : '0'; // phpcs:ignore WordPress.Security.NonceVerification
					if ( 1 === intval( $doing_bulk_transfer ) && 0 === intval( $is_premium ) ) {
						echo wp_json_encode(
							array(
								'results'             => array(),
								'access'              => $access,
								/* translators: %s: Premium url */
								'message'             => sprintf( __( '<a href="%s" target="_blank" >Premium</a> is required to do this action.', 'writetext-ai' ), WTAI_PREMIUM_SUBSCRIPTION_LINK ),
								'api_request'         => array(),
								'html'                => '',
								'jobs_user_ids'       => array(),
								'job_loader_data'     => array(),
								'has_error'           => true,
								'no_data_to_transfer' => '1',
								'to_transfer_array'   => array(),
								'to_transfer_last_action_array' => array(),
							)
						);
						exit;
					}

					$product_ids = ( isset( $_POST['product_id'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
					$fields      = ( isset( $_POST['fields'] ) && false !== strpos( sanitize_text_field( wp_unslash( $_POST['fields'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
					$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

					if ( ! $fields ) {
						$fields = array( 'page_title', 'page_description', 'product_description', 'product_excerpt', 'open_graph', 'alt_text' );
					}

					$do_alt_text_transfer = in_array( 'alt_text', $fields, true );

					$alt_text_index = array_search( 'alt_text', $fields, true );

					if ( false !== $alt_text_index ) {
						unset( $fields[ $key ] );
					}

					// Record transfer data.
					$bulk = isset( $_POST['bulk'] ) && ( '1' === sanitize_text_field( wp_unslash( $_POST['bulk'] ) ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

					$do_transfer                   = false;
					$to_transfer_array             = array();
					$to_transfer_last_action_array = array();
					$data_alt_values               = array();
					if ( $bulk ) {
						$initial_check = isset( $_POST['initial_check'] ) ? sanitize_text_field( wp_unslash( $_POST['initial_check'] ) ) : '0'; // phpcs:ignore WordPress.Security.NonceVerification
						if ( '1' === $initial_check ) {
							$product_ids_for_checking = isset( $_POST['all_product_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['all_product_ids'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

							foreach ( $product_ids_for_checking as $product_id ) {
								$api_settings = array(
									'fields'               => $fields,
									'includeUpdateHistory' => true,
									'historyCount'         => 1,
								);

								$api_result_values = apply_filters( 'wtai_generate_product_text', array(), $product_id, $api_settings );
								foreach ( $fields as $field_key ) {
									$textid = isset( $api_result_values[ $product_id ][ $field_key ][0]['id'] ) ? $api_result_values[ $product_id ][ $field_key ][0]['id'] : '';

									if ( $textid ) {
										$field_published = 0;
										$field_reviewed  = 0;
										if ( isset( $api_result_values[ $product_id ][ $field_key ][0]['history'][0] ) ) {
											$field_published = $api_result_values[ $product_id ][ $field_key ][0]['history'][0]['publish'];
											$field_reviewed  = $api_result_values[ $product_id ][ $field_key ][0]['history'][0]['reviewed'];
										}

										$to_transfer_array[] = $product_id . '|' . $field_key . '|' . $textid . '|' . $field_published . '|' . $field_reviewed;

										break; // Lets bypass early on initial checking since we already found at least one data to transfer.
									}
								}

								// Check if alt image ids can be transferred.
								if ( $do_alt_text_transfer ) {
									$alt_image_ids = wtai_get_product_image( $product_id );
									if ( $alt_image_ids ) {
										$api_alt_results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $alt_image_ids, false );

										foreach ( $api_alt_results as $api_alt_data ) {
											$alt_text_data = $api_alt_data['altText'];
											if ( $alt_text_data && isset( $alt_text_data['id'] ) ) {
												$data_alt_values[] = array(
													'attachment_id' => $api_alt_data['imageId'],
													'alt_text' => $alt_text_data['value'],
													'text_id'  => $alt_text_data['id'],
												);

												break; // Lets bypass early on initial checking since we already found at least one data to transfer.
											}
										}
									}
								}

								if ( $to_transfer_array || $data_alt_values ) {
									break; // Lets bypass early on initial checking since we already found at least one data to transfer.
								}
							}

							if ( $to_transfer_array || $data_alt_values ) {
								$do_transfer = true;
							}
						} else {
							$do_transfer = true;
						}
					} else {
						$do_transfer = true;
					}

					$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$api_results = array();
					$api_fields  = array(
						'browsertime' => $browsertime,
						'publish'     => true,
					);

					if ( $do_transfer ) {

						foreach ( $product_ids as $product_id ) {
							$api_result_values = apply_filters( 'wtai_generate_product_text', array(), $product_id, array( 'fields' => $fields ) );
							foreach ( $fields as $field_key ) {
								$textid = isset( $api_result_values[ $product_id ][ $field_key ][0]['id'] ) ? $api_result_values[ $product_id ][ $field_key ][0]['id'] : '';

								if ( $textid ) {
									$api_results[ $product_id ][ $field_key ] = array(
										'textId' => esc_attr( $textid ),
										'output' => $api_result_values[ $product_id ][ $field_key ][0]['value'],
									);
								}
							}
						}

						$stored_api_results = apply_filters( 'wtai_stored_generate_text', $api_results, $api_fields );

						if ( 200 === intval( $stored_api_results['http_header'] ) ) {
							foreach ( $product_ids as $product_id ) {
								foreach ( $fields as $field_key ) {
									$field_value = $api_results[ $product_id ][ $field_key ]['output'];
									if ( $field_value ) {
										$field_value                          = str_replace( "\\'", "'", $field_value );
										$field_value                          = str_replace( '\\"', '"', $field_value );
										$results[ $product_id ][ $field_key ] = array(
											'trim'  => wp_trim_words( $field_value, 15, null ),
											'text'  => $field_value,
											'count' => strlen( $field_value ),
											'words' => str_word_count( $field_value ),
										);
										$time                                 = get_post_meta( $product_id, 'wtai_transfer_date', true );
										$results[ $product_id ]['wtai_transfer_date'] = sprintf(
											/* translators: %1$s: date  %2$s time */
											__( '%1$s at %2$s' ),
											date_i18n( get_option( 'date_format' ), $time ),
											date_i18n( get_option( 'time_format' ), $time )
										);
										wtai_save_on_the_field( $product_id, $field_key, $field_value );

										wtai_record_product_field_last_activity( $product_id, 'transfer', $field_key );
									}
								}

								// Record last date activity.
								wtai_record_product_last_activity( $product_id, 'transfer' );
							}
						}

						$data_alt_values_current = array();
						$results_alt             = array();
						if ( $do_alt_text_transfer ) {
							foreach ( $product_ids as $product_id ) {
								$data_alt_values_current_init = array();

								$alt_image_ids = wtai_get_product_image( $product_id );
								if ( $alt_image_ids ) {
									$api_alt_results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $alt_image_ids, false );

									foreach ( $api_alt_results as $api_alt_data ) {
										$alt_text_data = $api_alt_data['altText'];
										if ( $alt_text_data && isset( $alt_text_data['id'] ) ) {
											$data_alt_values_current_init[] = array(
												'attachment_id' => $api_alt_data['imageId'],
												'alt_text' => $alt_text_data['value'],
												'text_id'  => $alt_text_data['id'],
											);
										}
									}
								}

								if ( $data_alt_values_current_init ) {
									$alt_payload = array(
										'browsertime' => $browsertime,
										'publish'     => 1,
										'product_id'  => $product_id,
										'data_values' => $data_alt_values_current_init,
									);

									$results_alt_init = apply_filters( 'wtai_save_alt_text_for_image_api', array(), $alt_payload );

									if ( $results_alt_init ) {
										$results_alt = array_merge( $results_alt, array_keys( $results_alt_init ) );

										$results[ $product_id ]['alt_text'] = array_keys( $results_alt_init );
									}
								}
							}
						}

						$has_transferred_data = false;
						if ( $results ) {
							$has_transferred_data = true;
						}

						if ( $results_alt ) {
							$has_transferred_data = true;
						}

						if ( ! $has_transferred_data ) {
							$message_token = __( 'Transfer failed because there are no generated texts yet for some of the products selected. Generate text first before transferring.', 'writetext-ai' );
						}
					} else {
						$no_data_to_transfer = 1;
						$message_token       = __( 'Transfer failed because there are no generated texts yet for some of the products selected. Generate text first before transferring.', 'writetext-ai' );
					}

					$html            = '';
					$jobs_user_ids   = array();
					$job_loader_data = array();
					if ( $bulk ) {
						$all_product_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['all_product_ids'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['all_product_ids'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['all_product_ids'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification

						foreach ( $product_ids as $pid ) {
							wtai_record_bulk_transfer( $all_product_ids, $pid );
						}

						$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['show_hidden'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$jobs        = wtai_get_bulk_generate_jobs( true );

						$output = $this->get_generate_bulk_data( array(), $jobs, false, $show_hidden );

						$html            = $output['html'];
						$jobs_user_ids   = $output['jobs_user_ids'];
						$job_loader_data = $output['job_loader_data'];

						$has_error = 0;
						if ( $output['has_error'] ) {
							$has_error = 1;
						}
					}
				} else {
					$access          = 0;
					$html            = '';
					$jobs_user_ids   = array();
					$job_loader_data = array();
					$has_error       = 0;
				}
			} else {
				$access          = 1;
				$html            = '';
				$jobs_user_ids   = array();
				$job_loader_data = array();
				$has_error       = 1;
				$message_token   = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'results'                       => $results,
					'results_alt'                   => $results_alt,
					'access'                        => $access,
					'message'                       => $message_token,
					'api_request'                   => $stored_api_results,
					'html'                          => $html,
					'jobs_user_ids'                 => $jobs_user_ids,
					'job_loader_data'               => $job_loader_data,
					'has_error'                     => $has_error,
					'no_data_to_transfer'           => $no_data_to_transfer,
					'to_transfer_array'             => $to_transfer_array,
					'to_transfer_last_action_array' => $to_transfer_last_action_array,
					'data_alt_values'               => $data_alt_values,
					'data_alt_values_current'       => $data_alt_values_current,
					'product_ids'                   => $product_ids,
				)
			);
			exit;
		}
	}

	/**
	 * Add store single text callback.
	 */
	public function add_store_single_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results       = array();
		$message_token = '';
		$access        = 1;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {
						$textid        = isset( $_POST['textid'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['textid'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$product_id    = isset( $_POST['product_id'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$meta_field    = isset( $_POST['fields'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$field         = $meta_field;
						$publish       = isset( $_POST['publish'] ) && sanitize_text_field( wp_unslash( $_POST['publish'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$message_value = isset( $_POST['message_value'] ) ? sanitize_text_field( wp_unslash( $_POST['message_value'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime   = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						$api_results = array();

						$api_results[ $product_id ][ $field ] = array(
							'textId' => $textid,
							'output' => $message_value,
						);

						$values = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
							'publish'     => $publish,
						);

						if ( $publish ) {
							$pre_values            = $values;
							$pre_values['publish'] = false;
							apply_filters( 'wtai_stored_generate_text', $api_results, $pre_values );
						}

						$api_results = apply_filters( 'wtai_stored_generate_text', $api_results, $values );
						if ( 200 === intval( $api_results['http_header'] ) ) {
							$field          = ( $publish ) ? $field : 'wtai_' . $field;
							$meta_post_date = ( $publish ) ? 'transfer' : 'generate';

							if ( $publish ) {
								$time                                    = get_post_meta( $product_id, 'wtai_generate_date', true );
								$results[ $product_id ]['generate_date'] = sprintf(
									/* translators: %1$s: date  %2$s time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $time ),
									date_i18n( get_option( 'time_format' ), $time )
								);
							}

							$time = get_post_meta( $product_id, 'wtai_' . $meta_post_date . '_date', true );
							$results[ $product_id ][ $meta_post_date . '_date' ] = sprintf(
								/* translators: %1$s: date  %2$s time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $time ),
								date_i18n( get_option( 'time_format' ), $time )
							);
							$message_value                    = str_replace( "\\'", "'", $message_value );
							$message_value                    = str_replace( '\\"', '"', $message_value );
							$results[ $product_id ][ $field ] = array(
								'trim'  => wp_trim_words( $message_value, 15, null ),
								'text'  => $message_value,
								'count' => strlen( $message_value ),
								'words' => str_word_count( $message_value ),
							);
							if ( $publish ) {
								$results[ $product_id ][ 'wtai_' . $field ] = array(
									'trim'  => wp_trim_words( $message_value, 15, null ),
									'text'  => $message_value,
									'count' => strlen( $message_value ),
									'words' => str_word_count( $message_value ),
								);

							}

							if ( $publish ) {
								wtai_save_on_the_field( $product_id, $meta_field, $message_value );
							}
						}
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$access = 0;
				}
			} else {
				$message_token = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'results' => $results,
					'access'  => $access,
					'message' => $message_token,
				)
			);
			exit;
		}
	}

	/**
	 * Save transfer or Save bulk text to API.
	 */
	public function add_store_bulk_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results       = array();
		$message_token = '';
		$access        = 1;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {
						$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

						$fields = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['data_fields'] ) ) {
							// phpcs:ignore WordPress.Security.NonceVerification
							if ( is_array( $_POST['data_fields'] ) ) {
								$fields = map_deep( wp_unslash( $_POST['data_fields'] ), 'wp_kses_post' ); // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								// phpcs:ignore WordPress.Security.NonceVerification
								$fields = wp_kses( wp_unslash( $_POST['data_fields'] ), 'post' );
							}
						}

						$ids = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['data_ids'] ) ) {
							// phpcs:ignore WordPress.Security.NonceVerification
							if ( is_array( $_POST['data_fields'] ) ) {
								$ids = map_deep( wp_unslash( $_POST['data_ids'] ), 'sanitize_text_field' ); // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								// phpcs:ignore WordPress.Security.NonceVerification
								$ids = sanitize_text_field( wp_unslash( $_POST['data_ids'] ) );
							}
						}

						$submittype  = isset( $_POST['submittype'] ) ? sanitize_text_field( wp_unslash( $_POST['submittype'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$publish     = isset( $_POST['publish'] ) && sanitize_text_field( wp_unslash( $_POST['publish'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						$values = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
							'publish'     => $publish,
						);

						$has_text_id = 0;
						$api_results = array();
						foreach ( $fields as $field_key => $field_value ) {
							$api_results[ $product_id ][ $field_key ] = array(
								'textId' => esc_attr( $ids[ $field_key ] ),
								'output' => $field_value,
							);

							if ( $ids[ $field_key ] ) {
								$has_text_id = 1;
							}
						}

						if ( $publish ) {
							$pre_values            = $values;
							$pre_values['publish'] = false;
							apply_filters( 'wtai_stored_generate_text', $api_results, $pre_values );
						}

						$api_results = apply_filters( 'wtai_stored_generate_text', $api_results, $values );

						if ( 200 === intval( $api_results['http_header'] ) ) {
							// Check status current post per field.
							$api_status_fields = array(
								'fields'               => array_keys( $fields ),
								'includeUpdateHistory' => true,
								'historyCount'         => 1,
							);

							$api_status_results = apply_filters( 'wtai_generate_product_text', array(), $product_id, $api_status_fields );

							$meta_post_date = ( $publish ) ? 'transfer' : 'generate';

							if ( $publish ) {
								$time                                    = get_post_meta( $product_id, 'wtai_generate_date', true );
								$results[ $product_id ]['generate_date'] = sprintf(
									/* translators: %1$s: date  %2$s time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $time ),
									date_i18n( get_option( 'time_format' ), $time )
								);
							}
							$time = get_post_meta( $product_id, 'wtai_' . $meta_post_date . '_date', true );
							$results[ $product_id ][ $meta_post_date . '_date' ] = sprintf(
								/* translators: %1$s: date  %2$s time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $time ),
								date_i18n( get_option( 'time_format' ), $time )
							);

							foreach ( $fields as $field_key => $field_value ) {
								$field = ( $publish ) ? $field_key : 'wtai_' . $field_key;

								$field_value = str_replace( "\\'", "'", $field_value );
								$field_value = str_replace( '\\"', '"', $field_value );

								if ( 'product_description' === $field || 'product_excerpt' === $field ) {
									$field_value = wpautop( $field_value );
								} else {
									$field_value = wpautop( nl2br( $field_value ) );
								}

								$results[ $product_id ][ $field ] = array(
									'trim'                => wp_trim_words( $field_value, 15, null ),
									'text'                => $field_value,
									'count'               => strlen( $field_value ),
									'words'               => str_word_count( $field_value ),
									'words_count'         => wtai_word_count( wp_strip_all_tags( $field_value ) ),
									'string_count'        => mb_strlen( wp_strip_all_tags( $field_value ), 'UTF-8' ),
									'string_count_credit' => mb_strlen( $field_value, 'UTF-8' ),
								);

								if ( $publish ) {
									$results[ $product_id ][ 'wtai_' . $field ] = array(
										'trim'  => wp_trim_words( $field_value, 15, null ),
										'text'  => $field_value,
										'count' => strlen( $field_value ),
										'words' => str_word_count( $field_value ),
									);
									wtai_save_on_the_field( $product_id, $field_key, $field_value );

									$last_activity = ( 'bulk_transfer' === $submittype ) ? 'transfer' : 'edit';
									wtai_record_product_field_last_activity( $product_id, $last_activity, $field_key );
								}

								$meta_values       = wtai_get_meta_values( $product_id, array( $field_key ) );
								$saved_field_value = $meta_values[ $field_key ];

								$results[ $product_id ][ $field ]['words_count']         = wtai_word_count( wp_strip_all_tags( $saved_field_value ) );
								$results[ $product_id ][ $field ]['string_count']        = mb_strlen( wp_strip_all_tags( $saved_field_value ), 'UTF-8' );
								$results[ $product_id ][ $field ]['string_count_credit'] = mb_strlen( $saved_field_value, 'UTF-8' );

								$field_published = 0;
								$field_reviewed  = 0;
								if ( isset( $api_status_results[ $product_id ][ $field ][0]['history'][0] ) ) {
									$field_published = $api_status_results[ $product_id ][ $field ][0]['history'][0]['publish'];
									$field_reviewed  = $api_status_results[ $product_id ][ $field ][0]['history'][0]['reviewed'];
								}

								$results[ $product_id ][ $field ]['published'] = $field_published;
								$results[ $product_id ][ $field ]['reviewed']  = $field_reviewed;
							}

							// Record last activity.
							$last_activity = ( 'bulk_transfer' === $submittype ) ? 'transfer' : 'edit';
							wtai_record_product_last_activity( $product_id, $last_activity );
						} elseif ( ! $api_results ) {
							$publish_type_message = __( 'No record found yet for the text fields. Please generate text first before saving.', 'writetext-ai' );
							if ( $publish ) {
								$publish_type_message = __( 'No record found yet for the text fields. Please generate text first before transferring.', 'writetext-ai' );
							}

							$message_token = $publish_type_message;
						} elseif ( 200 !== intval( $api_results['http_header'] ) ) {
							$result_error  = json_decode( $api_results['result'] );
							$message_token = $result_error->error;
						}
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$access = 0;
				}
			} else {
				$message_token = WTAI_INVALID_NONCE_MESSAGE;
			}

			$account_credit_details = wtai_get_account_credit_details();
			$is_premium             = $account_credit_details['is_premium'];

			$is_premium = $is_premium ? '1' : '0';

			echo wp_json_encode(
				array(
					'results'            => $results,
					'access'             => $access,
					'message'            => $message_token,
					'api_status_results' => $api_status_results,
					'has_text_id'        => $has_text_id,
					'is_premium'         => $is_premium,
				)
			);
			exit;
		}
	}

	/**
	 * Get generate options text.
	 *
	 * @param array  $results     Results.
	 * @param string $product_ids Product IDs.
	 * @param array  $fields      Fields.
	 */
	public function get_generate_options_text( $results, $product_ids = null, $fields = array() ) {
		if ( ! empty( $fields ) ) {
			if ( false !== strpos( $product_ids, ',' ) ) {
				$product_ids = explode( ',', $product_ids );
			} else {
				$product_ids = array( $product_ids );
			}

			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), $product_ids );
			$language = str_replace( '_', '-', $language );

			$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

			$max_keyword_count          = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
			$max_semantic_keyword_count = isset( $global_rule_fields['maxSemanticKeywords'] ) ? $global_rule_fields['maxSemanticKeywords'] : 0;

			$curl_params              = array();
			$curl_params['Type']      = 'Product';
			$curl_params['storeId']   = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['language']  = $language;
			$curl_params['ipAddress'] = $this->getClientIP();

			if ( isset( $fields['queue'] ) && 1 === intval( $fields['queue'] ) ) {
				$curl_params['queue'] = true;
			}

			$site_localized_countries = wtai_get_site_localized_countries();
			if ( $site_localized_countries ) {
				$curl_params['countries'] = $site_localized_countries;
			}

			if ( isset( $fields['includeRankedKeywords'] ) && $fields['includeRankedKeywords'] ) {
				$curl_params['includeRankedKeywords'] = true;
				$curl_params['location_code']         = wtai_get_location_code();
			}

			// Override the other details with special instructions during bulk generation.
			$special_instructions = '';
			if ( isset( $fields['specialInstructions'] ) && $fields['specialInstructions'] ) {
				$special_instructions               = wp_strip_all_tags( $fields['specialInstructions'] );
				$curl_params['specialInstructions'] = ( $special_instructions && isset( $global_rule_fields['maxOtherDetailsLength'] ) ) ? substr( $special_instructions, 0, $global_rule_fields['maxOtherDetailsLength'] ) : $special_instructions;
			}

			$curl_params['Texts'] = array();
			$product_attrs        = array();
			if ( isset( $fields['attr_fields'] ) ) {
				$input_attrs = ( false !== strpos( $fields['attr_fields'], ',' ) ) ? explode( ',', $fields['attr_fields'] ) : array( $fields['attr_fields'] );

				// Sanitize input attrs.
				foreach ( $input_attrs as $input_attr_index => $input_attr ) {
					$input_attrs[ $input_attr_index ] = sanitize_title( $input_attr );
				}

				$product_attrs = wtai_get_product_attr();

				$product_info = $product_attrs;
				unset( $product_info['attributes'] );
				$product_attrs = array_merge( $product_info, $product_attrs['attributes'] );
				foreach ( $product_attrs as $id => $attr ) {
					if ( false === strpos( 'attr-', $id ) ) {
						$old_value = $product_attrs[ $id ];
						unset( $product_attrs[ $id ] );
						$id                   = strtolower( str_replace( ' ', '-', $id ) );
						$product_attrs[ $id ] = $old_value;
					}

					if ( ! in_array( $id, $input_attrs, true ) ) {
						unset( $product_attrs[ $id ] );
					}
				}

				// Lets get attribuites per product.
				foreach ( $product_ids as $pid ) {
					$product_attr_info  = wtai_get_product_attr( $pid );
					$product_attributes = $product_attr_info['attributes'];

					foreach ( $product_attributes as $id => $attr ) {
						if ( false === strpos( 'attr-', $id ) ) {
							$id                   = 'attr-' . $id;
							$product_attrs[ $id ] = is_array( $attr ) ? $attr['name'] : $attr;
						}

						if ( ! in_array( $id, $input_attrs, true ) ) {
							unset( $product_attrs[ $id ] );
						}
					}
				}
			}

			$keyword_field = wtai_get_meta_key_source( 'keyword' );
			foreach ( $product_ids as $product_id ) {
				$sku = get_post_meta( $product_id, '_sku', true );

				$product_attributes = array();
				$attributes         = wtai_get_product_attr( $product_id );

				foreach ( $attributes as $attribute_key => $attribute_value ) {
					if ( isset( $global_rule_fields['maxAttributes'] ) &&
						count( $product_attributes ) === $global_rule_fields['maxAttributes'] ) {
						break;
					}

					if ( 'attributes' === $attribute_key ) {
						foreach ( $attribute_value as $attribute_child_key => $attribute_child_value ) {
							if ( isset( $global_rule_fields['maxAttributes'] ) &&
								count( $product_attributes ) === $global_rule_fields['maxAttributes'] ) {
								break;
							}

							if ( false !== strpos( $attribute_child_key, 'pa_' ) ) {
								$attribute_child_key = str_replace( 'pa_', 'attr-', $attribute_child_key );
							} else {
								$attribute_child_key = 'attr-' . $attribute_child_key;
							}

							// Changed in_array to array_key_exists.
							if ( ( ! empty( $product_attrs ) && array_key_exists( $attribute_child_key, $product_attrs ) ) ) {
								if ( ( is_array( $attribute_child_value['options'] ) && ! empty( $attribute_child_value['options'] ) ) ||
								( ! is_array( $attribute_child_value['options'] ) && $attribute_child_value['options'] )
								) {
									$array_values = array(
										'Name'     => $attribute_child_value['name'],
										'IsCustom' => 'true',
									);
									if ( is_array( $attribute_child_value['options'] ) ) {
										$array_values['Values'] = $attribute_child_value['options'];
									} else {
										$array_values['Value'] = $attribute_child_value['options'];
									}
									if ( 'Price' !== $attribute_child_value['name'] && isset( $global_rule_fields['maxAttributeValueLength'] ) ) {
										if ( isset( $array_values['Values'] ) ) {
											$max_attribute_value_length = $global_rule_fields['maxAttributeValueLength'];
											$array_values['Values']     = array_map(
												function ( $values ) use ( $max_attribute_value_length ) {
													return substr( wp_strip_all_tags( $values ), 0, $max_attribute_value_length );
												},
												$array_values['Values']
											);
										} else {
											$array_values['Value'] = substr( wp_strip_all_tags( $array_values['Value'] ), 0, $global_rule_fields['maxAttributeValueLength'] );
										}
									}
									$product_attributes[] = $array_values;
								}
							}
						}
					} else {

						// Check case insensitive attribute key.
						$is_attr_found = false;
						foreach ( $product_attrs as $attr_key_c => $attr_value_c ) {
							if ( strtolower( $attr_value_c ) === strtolower( $attribute_key ) ) {
								$is_attr_found = true;
							}
						}

						if ( ( ! empty( $product_attrs ) && in_array( $attribute_key, $product_attrs, true ) ) || $is_attr_found ) {
							if ( ( is_array( $attribute_value ) && ! empty( $attribute_value ) ) ||
								( ! is_array( $attribute_value ) && $attribute_value )
							) {
								$attribute_value = wp_strip_all_tags( $attribute_value ); // Remove html tags from attribute value.

								if ( isset( $global_rule_fields['maxAttributeValueLength'] ) ) {
									$attribute_value = substr( $attribute_value, 0, $global_rule_fields['maxAttributeValueLength'] );
								}

								$product_attributes[] = array(
									'Name'     => $attribute_key,
									'Value'    => $attribute_value,
									'IsCustom' => 'false',
								);
							}
						}
					}
				}

				$curl_field_texts = array();
				$fields_value     = wtai_get_meta_values( $product_id, $fields['fields'] );

				$generation_limit_vars                = wtai_get_generation_limit_vars();
				$max_reference_input_character_length = intval( $generation_limit_vars['maxReferenceInputCharacterLength'] );

				$reference_product_id = 0;
				foreach ( $fields_value as $meta_key => $meta_value ) {
					if ( ! $meta_key ) {
						continue;
					}

					if ( false === $meta_value ) {
						$meta_value = '';
					}

					$meta_field_value = array(
						'field'        => strtolower( apply_filters( 'wtai_field_conversion', $meta_key, 'product' ) ),
						'currentValue' => wtai_clean_up_html_string( $meta_value, true ),
					);

					if ( isset( $fields[ $meta_key . '_length_min' ] ) && $fields[ $meta_key . '_length_min' ] ) {
						$meta_field_value['minWords'] = $fields[ $meta_key . '_length_min' ];
					}

					if ( isset( $fields[ $meta_key . '_length_max' ] ) && $fields[ $meta_key . '_length_max' ] ) {
						$meta_field_value['maxWords'] = $fields[ $meta_key . '_length_max' ];
					}

					if ( isset( $fields['rewriteText'] ) && 1 === intval( $fields['rewriteText'] ) ) {
						$rewrite_text = $meta_value;
						if ( $rewrite_text ) {
							$rewrite_text = wtai_clean_up_html_string( $rewrite_text, true );

							if ( $max_reference_input_character_length > 0 ) {
								$rewrite_text = substr( $rewrite_text, 0, $max_reference_input_character_length );
							}

							$meta_field_value['rewriteText'] = $rewrite_text;
						}
					}

					$reference_product_id = 0;
					if ( isset( $fields['referenceProductID'] )
						&& '' !== $fields['referenceProductID'] && 1 !== intval( $fields['rewriteText'] ) ) {
						$reference_product_id = $fields['referenceProductID'];
						$reference_field      = $meta_key;
						$reference_text_array = wtai_get_meta_values( $reference_product_id, array( $reference_field ) );

						$reference_text = $reference_text_array[ $reference_field ];
						if ( $reference_text ) {
							$reference_text = wtai_clean_up_html_string( $reference_text, true );

							if ( $max_reference_input_character_length > 0 ) {
								$reference_text = substr( $reference_text, 0, $max_reference_input_character_length );
							}

							$meta_field_value['referenceText']     = $reference_text;
							$meta_field_value['referenceRecordId'] = $reference_product_id;
						}
					}

					// Keyword analysis views count.
					if ( isset( $fields['keywordAnalysisViews'] ) ) {
						$meta_field_value['keywordAnalysisViews'] = $fields['keywordAnalysisViews'];
					}

					// Simulate error.
					$simulate_error = false;
					if ( $simulate_error ) {
						if ( 'page_title' === $meta_key ||
							'page_description' === $meta_key ||
							'product_description' === $meta_key ||
							'product_excerpt' === $meta_key ||
							'open_graph' === $meta_key
						) {
							$meta_field_value['developmentThrowErrorInSeconds'] = 1;
						}
					}

					$curl_field_texts[] = $meta_field_value;
				}

				$keyword_values = array();
				$keywords       = ( isset( $fields['keywords'] ) ) ? $fields['keywords'] : array();
				if ( ! $keywords ) {
					$keywords_from_api = apply_filters( 'wtai_keyword_values', array(), $product_id, 'input', false );

					// Suggested audience.
					$keywords = array();
					foreach ( $keywords_from_api as $keyword_input_data_iindex => $keyword_input_data ) {
						if ( $keyword_input_data_iindex > 0 ) {
							$keywords[] = stripslashes( $keyword_input_data['name'] );
						}
					}
				}

				if ( is_array( $keywords ) ) {
					$keyword_count = 1;
					foreach ( $keywords as $keyword ) {
						$keyword_values[] = stripslashes( $keyword );
						if ( isset( $global_rule_fields['maxKeywords'] )
							&& $global_rule_fields['maxKeywords'] === $keyword_count ) {
							break;
						}
						++$keyword_count;
					}
				}

				// Reset wtai_review meta.
				delete_post_meta( $product_id, 'wtai_review' );

				$product_url = get_permalink( $product_id );
				// For debugging ranked keywords data only.
				if ( defined( 'WTAI_TEST_PRODUCT_URL' ) && isset( $fields['includeRankedKeywords'] ) && $fields['includeRankedKeywords'] ) {
					$product_url = WTAI_TEST_PRODUCT_URL;
				}

				$text_results = array(
					'RecordId'        => $product_id,
					'sku'             => $sku,
					'url'             => $product_url,
					'Browsertime'     => $fields['browsertime'],
					'name'            => ( isset( $global_rule_fields['maxNameLength'] ) ) ? substr( get_the_title( $product_id ), 0, $global_rule_fields['maxNameLength'] ) : get_the_title( $product_id ),
					'keywords'        => array_filter( $keyword_values ),
					'Attributes'      => $product_attributes,
					'options'         => isset( $fields['options'] ) && $fields['options'] ? $fields['options'] : WTAI_MAX_CHOICE,
					'fields'          => $curl_field_texts,
					'style'           => isset( $fields['styles'] ) ? $fields['styles'] : apply_filters( 'wtai_global_settings', 'wtai_installation_styles' ),
					'autoselectFirst' => isset( $fields['autoselectFirst'] ) ? $fields['autoselectFirst'] : false,
				);

				$otherdetails = '';
				if ( isset( $fields['otherproductdetails'] ) && $fields['otherproductdetails'] ) {
					$otherdetails = $fields['otherproductdetails'];
				} else {
					$wtai_product_attribute_preference = get_post_meta( $product_id, 'wtai_product_attribute_preference', true );
					if ( is_array( $wtai_product_attribute_preference ) && in_array( 'otherproductdetails', $wtai_product_attribute_preference, true ) ) {
						$otherdetails = get_post_meta( $product_id, 'wtai_otherproductdetails', true );
						if ( is_array( $otherdetails ) ) {
							$otherdetails = reset( $otherdetails );
						}
					}
				}

				if ( $otherdetails ) {
					$otherdetails = wp_strip_all_tags( $otherdetails );

					$text_results['otherDetails'] = ( $otherdetails && isset( $global_rule_fields['maxOtherDetailsLength'] ) ) ? substr( $otherdetails, 0, $global_rule_fields['maxOtherDetailsLength'] ) : $otherdetails;
				}

				if ( isset( $special_instructions ) && '' !== $special_instructions ) {
					// Remove other details because the special instruction should be the priority.
					unset( $text_results['otherDetails'] );
				}

				if ( isset( $fields['formalLanguage'] ) && $fields['formalLanguage'] ) {
					$text_results['formalLanguage'] = $fields['formalLanguage'];
				}

				$tones = ( isset( $fields['tones'] ) ) ? $fields['tones'] : apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );

				// Temporary only one for now.
				if ( is_array( $tones ) ) {
					if ( 1 === count( $tones ) ) {
						$text_results['tone'] = reset( $tones );
					} else {
						$text_results['tones'] = $tones;
					}
				}

				$audiences = ( isset( $fields['audiences'] ) ) ? $fields['audiences'] : apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
				if ( ! $audiences ) {
					$audiences = array();
				}

				if ( is_array( $audiences ) ) {
					$audiences = array_filter( $audiences );

					if ( 1 === count( $audiences ) ) {
						$text_results['audience'] = reset( $audiences );
					} elseif ( count( $audiences ) > 1 ) {
						$text_results['audiences'] = $audiences;
					}
				}

				$custom_audience = ( isset( $fields['customAudience'] ) ) ? $fields['customAudience'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_audience' );
				if ( $custom_audience ) {
					$text_results['customAudience'] = $custom_audience;
				}

				$semantic_keywords = ( isset( $fields['semanticKeywords'] ) ) ? $fields['semanticKeywords'] : apply_filters( 'wtai_global_settings', 'wtai_installation_semantic_keywords' );
				if ( $semantic_keywords ) {
					$text_results['semanticKeywords'] = array_filter( $semantic_keywords );
				} else {
					$keywords_data         = apply_filters( 'wtai_keyword_values', array(), $product_id, 'input', false );
					$semantics_selected_pt = array();
					foreach ( $keywords_data as $k_data ) {
						foreach ( $k_data['semantic'] as $sa_data ) {
							if ( 1 === $sa_data['active'] ) {
								$semantics_selected_pt[] = $sa_data['name'];
							}
						}
					}

					if ( $semantics_selected_pt ) {
						$text_results['semanticKeywords'] = $semantics_selected_pt;
					}
				}

				$custom_tone = ( isset( $fields['customTone'] ) ) ? $fields['customTone'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_tone' );
				if ( $custom_tone ) {
					$text_results['customTone'] = stripslashes( $custom_tone );
				}

				$custom_style = ( isset( $fields['customStyle'] ) ) ? $fields['customStyle'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_style' );
				if ( $custom_style ) {
					$text_results['customStyle'] = stripslashes( $custom_style );
				}

				// Remove custom tone and style if there is reference product id.
				if ( $reference_product_id ) {
					unset( $text_results['customTone'] );
					unset( $text_results['customStyle'] );
					unset( $text_results['customAudience'] );
					unset( $text_results['audiences'] );
					unset( $text_results['tones'] );
					unset( $text_results['style'] );
				}

				// Remove if empty keywords.
				if ( ! $text_results['keywords'] ) {
					unset( $text_results['keywords'] );
				} else {
					$keywords_for_generation_array = array();
					$keywords_for_generation_ctr   = 0;
					foreach ( $text_results['keywords'] as $keyword_value ) {
						if ( $keywords_for_generation_ctr < $max_keyword_count ) {
							$keywords_for_generation_array[] = $keyword_value;
						}

						++$keywords_for_generation_ctr;
					}

					$text_results['keywords'] = $keywords_for_generation_array;
				}

				// Remove if empty semantic keywords.
				if ( ! $text_results['semanticKeywords'] ) {
					unset( $text_results['semanticKeywords'] );
				} else {
					$semantic_keywords_for_generation_array = array();
					$semantic_keywords_for_generation_ctr   = 0;
					foreach ( $text_results['semanticKeywords'] as $keyword_value ) {
						if ( $semantic_keywords_for_generation_ctr < $max_semantic_keyword_count ) {
							$semantic_keywords_for_generation_array[] = $keyword_value;
						}

						++$semantic_keywords_for_generation_ctr;
					}

					$text_results['semanticKeywords'] = $semantic_keywords_for_generation_array;
				}

				// Remove if empty attributes.
				if ( ! $text_results['Attributes'] ) {
					unset( $text_results['Attributes'] );
				}

				// Handle featured image prompt.
				if ( isset( $fields['includeFeaturedImage'] ) && $fields['includeFeaturedImage'] ) {
					// Get main featured image attachment id.
					$featured_image_id = get_post_thumbnail_id( $product_id );
					if ( $featured_image_id ) {
						$image_api_data = wtai_get_image_for_api_generation( $product_id, $featured_image_id, $fields['browsertime'], false );

						if ( $image_api_data && isset( $image_api_data['url'] ) ) {
							$text_results['images'] = array( strval( $featured_image_id ) );
						}
					}
				}

				if ( isset( $fields['imageAltTexts'] ) ) {
					// Get current product alt image ids.
					$product_alt_image_ids = wtai_get_product_image( $product_id );

					$alt_image_data     = array();
					$alt_image_data_ids = array();
					foreach ( $fields['imageAltTexts'] as $alt_image_id ) {
						// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
						if ( $alt_image_id && in_array( $alt_image_id, $product_alt_image_ids ) && ! in_array( $alt_image_id, $alt_image_data_ids ) ) {
							$alt_current_value = get_post_meta( $alt_image_id, '_wp_attachment_image_alt', true );
							$alt_image_data[]  = array(
								'imageId'      => strval( $alt_image_id ),
								'currentValue' => strval( $alt_current_value ),
							);

							$alt_image_data_ids[] = $alt_image_id;
						}
					}

					if ( $alt_image_data ) {
						$text_results['imageAltTexts'] = $alt_image_data;
					}
				}

				$curl_params['Texts'][] = $text_results;
			}

			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/v2',
			);
			$token    = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();
			$headers  = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', wtai_get_site_language() ) ),
			);

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['wtai_generated_text_options'] ) ) {
				print '<pre>';
				print_r( $curl_params ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['wtai_generated_text_options'] ) ) {
				$api_results = json_decode( $api_results['result'], true );
				print_r( $api_results ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				print '</pre>';
			}

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['requestId'] ) && ! is_null( $api_results['requestId'] ) ) {
					$results['requestId'] = $api_results['requestId'];
				} else {
					$api_results = isset( $api_results['value'] ) ? $api_results['value'] : $api_results;
					if ( isset( $api_results['texts'] ) && ! empty( $api_results['texts'] ) ) {
						foreach ( $api_results['texts'] as $textvalue ) {
							$field = apply_filters( 'wtai_field_conversion', $textvalue['field'], 'product' );
							if ( ! is_array( $textvalue['outputs'] ) ) {
								continue;
							}
							$results[ $textvalue['recordId'] ][ $field ] = array(
								'textId' => $textvalue['id'],
								'output' => ( 1 === count( $textvalue['outputs'] ) ) ? $textvalue['outputs'][0] : $textvalue['outputs'],
							);
						}
					}
				}
			} elseif ( ! $api_results['result'] ) {
				$results = 'Error Header Code : ' . $api_results['http_header'];
			} elseif ( 200 !== intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['Error'] ) ) {
					$results = $api_results['Error'];
				}
				if ( isset( $api_results['error'] ) ) {
					$results = $api_results['error'];
				}
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['Error'] ) ) {
					$results = $api_results['Error'];
				}
				if ( isset( $api_results['error'] ) ) {
					$results = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Get product generated text from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param string $product_ids   Product IDs.
	 * @param array  $fields Fields to get from the API.
	 */
	public function get_generate_product_text( $results, $product_ids = null, $fields = array() ) {
		$locale_lang = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$language = apply_filters( 'wtai_language_code_by_product', $locale_lang, $product_ids );
		$language = str_replace( '_', '-', $language );

		$curl_params        = array();
		$params['Type']     = 'Product';
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;
		$params['recordId'] = $product_ids;

		if ( isset( $fields['historyCount'] ) && is_numeric( $fields['historyCount'] ) ) {
			$params['historyCount'] = $fields['historyCount'];
		}

		if ( isset( $fields['includeUpdateHistory'] ) && $fields['includeUpdateHistory'] ) {
			$params['includeUpdateHistory'] = 'true';
		}

		if ( isset( $fields['fields'] ) && ! empty( $fields['fields'] ) ) {
			$params['field'] = array_map(
				function ( $meta_key ) {
					return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'product' );
				},
				$fields['fields']
			);
			$params['field'] = implode( ',', $params['field'] );
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate?' . http_build_query( $params ),
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			$reviews = array();

			if ( isset( $api_results['records'] ) && ! empty( $api_results['records'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['wtai_product_api_debug'] ) ) {
					print '<pre>';
					print_r( $api_results['records'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					print '</pre>';
				}
				foreach ( $api_results['records'] as $stores ) {
					foreach ( $stores['stores'] as $store ) {

						$field                                    = apply_filters( 'wtai_field_conversion', $store['field'], 'product' );
						$store_text                               = ( isset( $fields['single_value'] ) ) ? array( $store['texts'][0] ) : $store['texts'];
						$results[ $stores['recordId'] ][ $field ] = $store_text;

						$reviews = array();
						if ( isset( $fields['single_value'] ) && isset( $store['reviews'][0] ) ) {
							$reviews = array( $store['reviews'][0] );

						} elseif ( $store['reviews'] ) {
							$reviews = $store['reviews'];
						}

						$results[ $stores['recordId'] ][ $field ]['reviews'] = $reviews;
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Get product history data from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param string $record_ids   Record IDs.
	 * @param array  $fields Fields to get from the API.
	 * @param string $type Type of data to get.
	 */
	public function get_generate_history( $results, $record_ids = null, $fields = array(), $type = 'product' ) {
		$accept_language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		if ( 'product' === $type ) {
			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), $record_ids );
		} else {
			$language = $accept_language;
		}

		if ( ! $record_ids ) {
			$language = $accept_language;
			$language = wtai_match_language_locale( $language );
		}

		$params             = $fields;
		$params['type']     = ucfirst( $type );
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = str_replace( '_', '-', $language );
		if ( ! is_null( $record_ids ) ) {
			$params['recordId'] = $record_ids;
		}

		if ( ! $params['startDate'] ) {
			unset( $params['startDate'] );
		}

		if ( ! $params['endDate'] ) {
			unset( $params['endDate'] );
		}

		if ( ! $params['userName'] ) {
			unset( $params['userName'] );
		}

		if ( ! $params['continuationToken'] ) {
			unset( $params['continuationToken'] );
		}

		if ( ! $params['recordId'] ) {
			unset( $params['recordId'] );
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/History?' . http_build_query( $params ),
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $accept_language ) ),
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			if ( isset( $api_results['histories'] ) ) {
				return $api_results;
			}
		}

		return $results;
	}

	/**
	 * Get bulk generate request ID data.
	 *
	 * @param array  $results    Results from the API.
	 * @param string $request_id    Request ID.
	 */
	public function get_generate_product_bulk( $results, $request_id = null ) {

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Bulk/' . $request_id,
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );
		} else {
			$results['error']       = 1;
			$results['http_header'] = $api_results['http_header'];
		}

		$results['header_date'] = $api_results['headers']['date'][0];
		return $results;
	}

	/**
	 * Cancel bulk generate.
	 *
	 * @param array  $results    Results from the API.
	 * @param string $request_id    Request ID.
	 */
	public function get_generate_product_bulk_cancel( $results, $request_id = null ) {

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Bulk/' . $request_id . '/cancel',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'POST' );

		$results = 0;
		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = 1;
		}
		return $results;
	}

	/**
	 * Get generate product status from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param array  $fields     Fields from the product.
	 * @param string $continuation_token Continuation token from the API.
	 */
	public function get_generate_product_status( $results, $fields = array(), $continuation_token = '' ) {
		$params            = $fields;
		$params['type']    = 'Product';
		$params['storeId'] = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		if ( isset( $fields['language'] ) && $fields['language'] ) {
			$params['language'] = str_replace( '_', '-', str_replace( '_formal', '', $fields['language'] ) );
		} else {
			$params['language'] = str_replace( '_', '-', apply_filters( 'wtai_language_code_by_product', wtai_get_site_language() ) );
		}

		if ( isset( $params['status'] ) ) {
			if ( ! is_array( $params['status'] ) ) {
				$params['status'] = rawurlencode( apply_filters( 'wtai_field_conversion', $params['status'], 'product' ) );
			} else {
				$wtai_statuses = array();
				foreach ( $params['status'] as $status_value ) {
					$wtai_statuses[] = rawurlencode( apply_filters( 'wtai_field_conversion', $status_value, 'product' ) );
				}

				if ( $wtai_statuses ) {
					$params['status'] = $wtai_statuses;
				}
			}
		}

		$add_params = '';
		if ( isset( $params['wtai_fields'] ) ) {
			if ( is_array( $params['wtai_fields'] ) ) {
				$wtai_fields = array();
				foreach ( $params['wtai_fields'] as $value ) {
					$wtai_fields[] = 'fields=' . rawurlencode( apply_filters( 'wtai_field_conversion', $value, 'product' ) );
				}
				$add_params .= implode( '&', $wtai_fields );
			}

			unset( $params['wtai_fields'] );
		}

		if ( isset( $params['startDate'] ) ) {
			$params['startDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['startDate'] ) );
		}

		if ( isset( $params['endDate'] ) ) {
			$params['endDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['endDate'] ) );
		}

		if ( $add_params ) {
			$add_params = '&' . $add_params;
		}

		$url = 'https://' . $this->api_base_url . '/text/Generate/Status?' . http_build_query( $params ) . $add_params;

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_grid_filter_debug'] ) ) {
			echo wp_kses( $url, 'post' );
		}

		$settings = array(
			'remote_url' => $url,
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		$continuation_token = '';
		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
				$results = array();
				foreach ( $api_results['records'] as $records ) {
					if ( ! in_array( $records['recordId'], $results, true ) ) {
						$results[] = $records['recordId'];
					}
				}

				$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
				if ( $continuation_token ) {
					$orig_params = $params;
					while ( $continuation_token ) {
						$orig_params['continuationToken'] = $continuation_token;

						$url = 'https://' . $this->api_base_url . '/text/Generate/Status?' . http_build_query( $orig_params ) . $add_params;

						$settings = array(
							'remote_url' => $url,
						);

						$headers = array(
							'Cache-Control' => 'no-cache',
							'Authorization' => 'Bearer ' . $this->get_web_token(),
							'Content-Type'  => 'application/json',
						);

						$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );
						if ( 200 === intval( $api_results['http_header'] ) ) {
							$api_results = json_decode( $api_results['result'], true );

							if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
								foreach ( $api_results['records'] as $records ) {
									if ( ! in_array( $records['recordId'], $results, true ) ) {
										$results[] = $records['recordId'];
									}
								}

								$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
							} else {
								$continuation_token = '';
								break;
							}
						} else {
							$continuation_token = '';
							break;
						}
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Add generated text to the product.
	 *
	 * @param array $results    Results from the API.
	 * @param array $fields     Fields from the product.
	 */
	public function add_generate_product_text( $results = array(), $fields = array() ) {
		if ( ! empty( $results ) ) {
			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/text',
			);
			$headers  = array(
				'Cache-Control' => 'no-cache',
				'Host'          => $this->api_base_url,
				'Authorization' => 'Bearer ' . $this->get_web_token(),
				'Content-Type'  => 'application/json',
			);

			$publish  = isset( $fields['publish'] ) ? $fields['publish'] : false;
			$reviewed = isset( $fields['reviewed'] ) ? $fields['reviewed'] : false;

			if ( $publish ) {
				$reviewed = true;
			}

			if ( isset( $fields['reviewed'] ) ) {
				unset( $fields['reviewed'] );
			}

			$review_extension_lang = wtai_get_review_extension_language();

			$extension_reviews_for_tagging = array();
			foreach ( $results as $product_id => $result ) {
				$language = isset( $fields['language'] ) ? str_replace( '_', '-', str_replace( '_formal', '', $fields['language'] ) ) : str_replace( '_', '-', apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) ) );

				$curl_params                = array();
				$curl_params['Type']        = 'Product';
				$curl_params['storeId']     = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
				$curl_params['language']    = $language;
				$curl_params['recordId']    = (string) $product_id;
				$curl_params['browsertime'] = $fields['browsertime'];
				$curl_fields                = array();
				foreach ( $result as $field => $result_value ) {
					$values = is_array( $result_value['output'] ) ? reset( $result_value['output'] ) : $result_value['output'];

					$field_publish = $publish;
					if ( isset( $result_value['publish'] ) ) {
						$field_publish = $result_value['publish'];
					}

					$curl_fields[] = array(
						'textId'   => $result_value['textId'],
						'field'    => apply_filters( 'wtai_field_conversion', $field, 'product' ),
						'value'    => $values,
						'publish'  => $field_publish,
						'platform' => WTAI_GENERATE_TEXT_PLATFORM,
						'reviewed' => $reviewed,
					);
				}

				if ( $publish ) {
					$extension_reviews_for_tagging[] = array(
						'product_id' => $product_id,
						'fields'     => array_keys( $result ),
					);
				}

				$curl_params['fields'] = $curl_fields;

				$meta_post_date = ( $publish ) ? 'transfer' : 'generate';

				if ( isset( $fields['reviewed'] ) && $fields['reviewed'] ) {
					$meta_post_date = 'reviewed';
				}

				// Added browser.
				$browser_offset = isset( $fields['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $fields['browsertime'] ) ) * -1 ) * 60 : 0;
				$time           = strtotime( current_time( 'mysql' ) );
				update_post_meta( $product_id, 'wtai_' . $meta_post_date . '_date', $time );

				// Added modified date.
				$post_data = array(
					'ID'            => $product_id,
					'post_modified' => strtotime( $time ),
				);
				wp_update_post( $post_data );

				$results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

				if ( $extension_reviews_for_tagging && $review_extension_lang !== $language ) {
					$allowed_statuses = array( 1, 2, 3 );
					foreach ( $extension_reviews_for_tagging as $extension_review_t ) {
						$e_product_id      = $extension_review_t['product_id'];
						$extension_reviews = $this->get_product_extension_review( array(), $e_product_id );

						if ( $extension_reviews && isset( $extension_reviews['reviews'] ) ) {
							$e_reviews       = $extension_reviews['reviews'];
							$e_review_fields = $extension_review_t['fields'];

							$save_review_ids    = array();
							$save_review_fields = array();
							foreach ( $e_reviews as $e_review ) {
								$e_review_id         = $e_review['id'];
								$e_api_review_fields = $e_review['fields'];

								foreach ( $e_api_review_fields as $e_api_review_field ) {
									$field_type = $e_api_review_field['field'];
									$status     = intval( $e_api_review_field['status'] );

									foreach ( $e_review_fields as $et_field ) {
										$current_field_to_api_key = apply_filters( 'wtai_field_conversion', trim( $et_field ), 'product' );

										if ( $current_field_to_api_key === $field_type && in_array( $status, $allowed_statuses, true ) ) {
											$save_review_ids[]    = $e_review_id;
											$save_review_fields[] = $et_field;
										}
									}
								}
							}

							if ( $save_review_ids && $save_review_fields ) {
								$this->save_product_extension_review( array(), $save_review_ids, $e_product_id, $save_review_fields );
							}
						}
					}
				}
			}
		}
		return $results;
	}

	/**
	 * Get web token callback.
	 *
	 * @param string $web_token Web token.
	 */
	public function get_web_token_callback( $web_token = '' ) {
		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			$web_token = $this->get_web_token();
		}
		return $web_token;
	}

	/**
	 * Get user token meta name.
	 */
	public function get_user_token_meta_name() {
		$site_id = 1;
		if ( is_multisite() ) {
			$site_id = get_current_blog_id();

			$meta_key = 'wtai_api_web_token_' . $site_id;
		} else {
			$meta_key = 'wtai_api_web_token';
		}

		return $meta_key;
	}

	/**
	 * Get web token.
	 */
	private function get_web_token() {
		global $current_user;

		$current_user_id = get_current_user_id();

		if ( ! $current_user_id ) {
			return;
		}

		$token_meta_key_name = $this->get_user_token_meta_name();

		$wtai_api_token = get_user_meta( $current_user_id, $token_meta_key_name, true );

		if ( $wtai_api_token ) {
			$time = get_user_meta( $current_user_id, $token_meta_key_name . '_time', true );

			if ( $time && $time > strtotime( current_time( 'mysql' ) ) ) {
				return $wtai_api_token;
			}
		}

		$wtai_connect_token = $this->get_connect_token();

		if ( $wtai_connect_token ) {
			$settings = array();
			if ( isset( $current_user->data->user_email ) ) {
				// Add first name and last name in web token request.
				$first_name = get_user_meta( $current_user_id, 'first_name', true );
				$last_name  = get_user_meta( $current_user_id, 'last_name', true );

				$addtl_token_params = '';
				if ( $first_name ) {
					$addtl_token_params .= '&firstName=' . rawurlencode( $first_name );
				}
				if ( $last_name ) {
					$addtl_token_params .= '&lastName=' . rawurlencode( $last_name );
				}

				$settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/web/token?email=' . rawurlencode( sanitize_email( $current_user->data->user_email ) ) . $addtl_token_params,
				);
			}
			$headers = array(
				'Cache-Control' => 'no-cache',
				'Authorization' => 'Bearer ' . $wtai_connect_token,
			);
			if ( $settings ) {
				$content = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

				if ( 200 === intval( $content['http_header'] ) ) {
					$token_meta_key_name = $this->get_user_token_meta_name();

					$content['result'] = json_decode( $content['result'], true );

					update_user_meta( $current_user_id, $token_meta_key_name, $content['result']['access_token'] );
					update_user_meta( $current_user_id, $token_meta_key_name . '_time', strtotime( current_time( 'mysql' ) ) + $content['result']['expires_in'] );

					return $content['result']['access_token'];
				}
			}
		}

		return '';
	}

	/**
	 * Get connect token.
	 */
	private function get_connect_token() {
		if ( $this->verify_site_token() ) {
			$wtai_api_token = get_option( 'wtai_api_token', '' );

			return $this->get_connect_token_api( $wtai_api_token );
		}
		return '';
	}

	/**
	 * Get connect token API.
	 *
	 * @param bool $user_api_token  User API Token.
	 */
	private function get_connect_token_api( $user_api_token ) {
		$curl_params = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => 'WriteTextAI.Plugin',
			'refresh_token' => $user_api_token,
		);
		$curl_params = http_build_query( $curl_params );
		$settings    = array(
			'remote_url' => 'https://' . WTAI_AUTH_HOST . '/connect/token',
			'host_url'   => WTAI_AUTH_HOST,
		);
		$headers     = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $settings['host_url'],
			'Content-Type'  => 'application/x-www-form-urlencoded',
		);

		$content = $this->get_data_via_api( $curl_params, $settings, $headers );

		if ( 200 === intval( $content['http_header'] ) ) {
			$content['result'] = json_decode( $content['result'], true );

			return $content['result']['access_token'];
		}
		return '';
	}

	/**
	 * Check connect token API.
	 *
	 * @param string $access_token Access token.
	 * @param string $user_api_token User API token.
	 */
	public function check_connect_token_api( $access_token, $user_api_token ) {
		$current_user_id = get_current_user_id();

		if ( ! $current_user_id ) {
			return;
		}

		$current_time = strtotime( current_time( 'mysql' ) );

		$do_delay_check    = false;
		$time_last_checked = get_option( 'wtai_api_token_last_checked', '' );
		if ( $time_last_checked && $do_delay_check ) {
			$refresh_time_diff = $current_time - $time_last_checked;
			$diff_minutes      = $refresh_time_diff / 60;

			if ( $diff_minutes <= 60 ) {
				return true;
			}
		}

		$curl_params = array(
			'grant_type'    => 'refresh_token',
			'client_id'     => 'WriteTextAI.Plugin',
			'refresh_token' => $user_api_token,
		);
		$curl_params = http_build_query( $curl_params );

		$settings = array(
			'remote_url' => 'https://' . WTAI_AUTH_HOST . '/connect/token',
			'host_url'   => WTAI_AUTH_HOST,
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $settings['host_url'],
			'Content-Type'  => 'application/x-www-form-urlencoded',
		);

		$content = $this->get_data_via_api( $curl_params, $settings, $headers );

		update_option( 'wtai_api_token_last_checked', strtotime( current_time( 'mysql' ) ) );

		if ( 200 === intval( $content['http_header'] ) ) {
			$content['result'] = json_decode( $content['result'], true );

			return $content['result']['access_token'];
		}

		return '';
	}

	/**
	 * Validate if etag token expired.
	 *
	 * @param bool $is_expired  Is expired.
	 */
	public function validate_etag_token_expired( $is_expired = false ) {
		if ( '' === $this->get_web_token() ) {
			return $is_expired;
		}

		// Lets try to check etag.
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Etags',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $this->get_web_token(),
		);
		$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		$is_expired = false;
		if ( 401 === intval( $content['http_header'] ) ) {
			$is_expired = true;

			remove_filter( 'wtai_validate_etag_token_expired', array( $this, 'validate_etag_token_expired' ) );
		}

		return $is_expired;
	}

	/**
	 * Verify site token.
	 */
	private function verify_site_token() {
		$wtai_api_token = get_option( 'wtai_api_token', '' );

		if ( $wtai_api_token ) {
			$time = get_option( 'wtai_api_token_time', '' );

			if ( ! $time ) {
				return true; // Lets return true if time not set so we can get a new refresh token.
			}

			if ( $time && $time > strtotime( 'now' ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Call API data filter.
	 *
	 * @param array  $results       API Results.
	 * @param array  $curl_params   Parameters to pass to the API.
	 * @param array  $settings      Settings for the API.
	 * @param array  $headers       Headers for the API.
	 * @param string $method        Method for the API.
	 */
	public function get_data_via_api_filter( $results = array(), $curl_params = '', $settings = array(), $headers = array(), $method = 'POST' ) {
		$results = $this->get_data_via_api( $curl_params, $settings, $headers, $method );

		return $results;
	}

	/**
	 * Call API data.
	 *
	 * @param array  $curl_params    Parameters to pass to the API.
	 * @param array  $settings       Settings for the API.
	 * @param array  $headers        Headers for the API.
	 * @param string $method        Method for the API.
	 */
	private function get_data_via_api( $curl_params = '', $settings = array(), $headers = array(), $method = 'POST' ) {
		$current_user_id = get_current_user_id();

		$start = microtime( true );

		if ( ! $current_user_id ) {
			$time_elapsed_secs = microtime( true ) - $start;

			return array(
				'status'         => 0,
				'result'         => array(),
				'http_header'    => 404, // Unauthorized.
				'headers'        => $headers,
				'execution_time' => $time_elapsed_secs,
			);
		}

		$wtai_version = wtai_get_version();
		$wtai_version = str_replace( '-dev', '', $wtai_version );

		// Add plugin version and wp version to headers.
		$headers['WriteTextAI-PlatformVersion'] = wtai_get_wp_version();
		$headers['WriteTextAI-PluginVersion']   = $wtai_version;
		$headers['WriteTextAI-PHPVersion']      = phpversion();

		$request_args = array(
			'headers'     => $headers,
			'redirection' => 10,
			'timeout'     => 300,
			'httpversion' => '1.0',
			'sslverify'   => false,
		);

		if ( 'POST' === $method ) {
			$request_args['body'] = $curl_params;
			$response             = wp_remote_post( $settings['remote_url'], $request_args );
		} elseif ( 'GET' === $method ) {
			$response = wp_remote_get( $settings['remote_url'], $request_args );
		} else {
			$request_args['body']   = $curl_params;
			$request_args['method'] = $method;
			$response               = wp_remote_request( $settings['remote_url'], $request_args );
		}

		$status   = 1;
		$return   = '';
		$httpcode = '';
		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$response_headers = $response['headers']; // Array of http header lines.
			$return           = $response['body']; // Use the content.
			$httpcode         = $response['response']['code']; // Use the content.

			foreach ( $response_headers as $hdr_id => $hdr_val ) {
				if ( is_array( $hdr_val ) ) {
					$headers[ $hdr_id ] = $hdr_val;
				} else {
					$headers[ $hdr_id ][] = $hdr_val;
				}
			}
		} else {
			$status = 0;

			$return = $response->get_error_message();
		}

		if ( isset( $headers['www-authenticate'] ) && $headers['www-authenticate'] ) {
			if ( is_array( $headers['www-authenticate'] ) ) {
				$headers['www-authenticate'] = reset( $headers['www-authenticate'] );
			}
			if ( strpos( $headers['www-authenticate'], 'invalid_token' ) !== false ) {
				$httpcode = 401;
			}
		}

		// Accept_header_code.
		if ( ! in_array( $httpcode, array( 200 ), true ) ) {
			$status = 0;
		}

		$time_elapsed_secs = microtime( true ) - $start;

		$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? strtolower( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) ) : '';
		$referrer   = isset( $_SERVER['HTTP_REFERER'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';

		if ( defined( 'WTAI_API_LOGGING' ) && WTAI_API_LOGGING ) {
			$this->log( '--------' );
			$this->log( 'api call' );
			$this->log( 'url: ' . $settings['remote_url'] );
			$this->log( 'payload: ' . print_r( $curl_params, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			$this->log( 'headers: ' . print_r( $headers, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			$this->log( 'response code: ' . print_r( $httpcode, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			$this->log( 'execution time: ' . $time_elapsed_secs . ' secs' );
			$this->log( 'referrer: ' . $referrer );
			$this->log( 'user_agent: ' . $user_agent );
			$this->log( 'current_user_id: ' . $current_user_id );
			$this->log( "--------\n" );
		}

		return array(
			'status'         => $status,
			'result'         => $return,
			'http_header'    => $httpcode,
			'headers'        => $headers,
			'execution_time' => $time_elapsed_secs,
		);
	}

	/**
	 * Get client IP address.
	 */
	private function getClientIP() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = isset( $_SERVER['HTTP_CLIENT_IP'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) ) : '';
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) : '';
		}
		$ips = explode( ',', $ip );

		return $ips[0];
	}

	/**
	 * Get suggested audiences ajax callback.
	 */
	public function get_suggested_audience() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$result        = array();
		$message_token = '';
		$access        = 1;
		$success       = 0;
		$error         = 0;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {
						$product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$data_type      = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : 'Product'; // phpcs:ignore WordPress.Security.NonceVerification
						$clear_all_text = isset( $_POST['clearAllText'] ) ? sanitize_text_field( wp_unslash( $_POST['clearAllText'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime    = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$keywords       = isset( $_POST['keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

						$values = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
							'keywords'    => $keywords,
							'type'        => $data_type,
						);

						$results = apply_filters( 'wtai_get_suggested_audiences_text', array(), $product_id, $values, $clear_all_text );
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$access = 0;
				}

				$success = 1;
			} else {
				$message_token = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'results' => $results,
					'access'  => $access,
					'message' => $message_token,
				)
			);
			exit;
		}
	}

	/**
	 * Get suggested audiences text.
	 *
	 * @param  array $results   Results.
	 * @param  int   $product_id   Product ID.
	 * @param  array $values     Values.
	 * @param  int   $clear_all_text Clear all text.
	 */
	public function get_suggested_audiences_text( $results, $product_id = null, $values = array(), $clear_all_text = 0 ) {
		$results = array();
		if ( ! empty( $product_id ) ) {
			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

			$global_rule_fields      = apply_filters( 'wtai_global_rule_fields', array() );
			$max_keyword_char_length = $global_rule_fields['maxKeywordLength'];

			$product_name = get_the_title( $product_id );
			$product_name = trim( substr( $product_name, 0, $max_keyword_char_length ) );

			$keywords   = array();
			$keywords[] = $product_name;

			if ( $values['keywords'] ) {
				foreach ( $values['keywords'] as $keyword ) {
					if ( $keyword ) {
						$keywords[] = trim( substr( $keyword, 0, $max_keyword_char_length ) );
					}
				}
			}

			$curl_params             = array();
			$curl_params['storeId']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['recordId'] = $product_id;
			$curl_params['Text']     = $keywords;

			if ( $values['type'] ) {
				$curl_params['type'] = ucfirst( $values['type'] );
			}

			$clear_all_text = intval( $clear_all_text );
			if ( 1 === $clear_all_text ) {
				$curl_params['clearAllText'] = 'true';
			}

			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Suggestion/Audience?' . http_build_query( $curl_params ),
			);

			$token = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();

			$headers = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
			);

			$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );

				if ( isset( $api_results['values'] ) ) {
					$results['suggested_audiences'] = $api_results['values'];
					$results['selected_audience']   = $api_results['selected'][0];
				} else {
					$results['error'] = __( 'No suggestions found', 'writetext-ai' );
				}
			} elseif ( ! $api_results['result'] ) {
					$results['error'] = 'Error Header Code : ' . $api_results['http_header'];
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['error'] ) ) {
					$results['error'] = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Get semantics text.
	 *
	 * @param  array  $results   Results.
	 * @param  int    $record_id   Record ID.
	 * @param  array  $values     Values.
	 * @param  int    $clear_all_text Clear all text.
	 * @param  string $record_type Record type.
	 */
	public function get_keyword_semantics_text( $results, $record_id = null, $values = array(), $clear_all_text = 0, $record_type = 'product' ) {
		$results = array();
		if ( ! empty( $record_id ) ) {
			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

			$keywords = $values['keywords'];
			$keywords = array_unique( $keywords );

			$global_rule_fields      = apply_filters( 'wtai_global_rule_fields', array() );
			$max_keyword_char_length = $global_rule_fields['maxKeywordLength'];

			foreach ( $keywords as $kindex => $keyword ) {
				$keywords[ $kindex ] = trim( substr( $keyword, 0, $max_keyword_char_length ) );
			}

			$curl_params             = array();
			$curl_params['storeId']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['recordId'] = $record_id;
			$curl_params['Text']     = $keywords;
			$curl_params['type']     = ucfirst( $record_type );

			if ( 1 === $clear_all_text ) {
				$curl_params['clearAllText'] = 'true';
			}
			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Suggestion/Keywords?' . http_build_query( $curl_params ),
			);

			$token = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();

			$headers = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
			);

			$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );

				if ( isset( $api_results['texts'] ) ) {
					$results = $api_results;
				} else {
					$results['error'] = WTAI_GENERAL_ERROR_MESSAGE;
				}
			} elseif ( ! $api_results['result'] ) {
					$results['error'] = 'Error Header Code : ' . $api_results['http_header'];
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['error'] ) ) {
					$results['error'] = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Get keywordanalysis location.
	 *
	 * @param  array $results   Results.
	 */
	public function get_keywordanalysis_location( $results ) {
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/KeywordAnalysis/Locations',
		);

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( $api_results['status'] && 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			if ( isset( $api_results['result'] ) ) {
				$results = $api_results['result'];
			} else {
				$results['error'] = WTAI_GENERAL_ERROR_MESSAGE;
			}
		} elseif ( ! $api_results['result'] ) {
				$results['error'] = 'Error Header Code : ' . $api_results['http_header'];
		} else {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			}
		}

		return $results;
	}

	/**
	 * Get keywordanalysis ideas.
	 *
	 * @param  array  $results   Results.
	 * @param  int    $record_id Record ID.
	 * @param  array  $fields    Fields.
	 * @param  string $record_type Record Type.
	 */
	public function get_keywordanalysis_ideas( $results, $record_id = null, $fields = array(), $record_type = 'product' ) {
		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/KeywordAnalysis/Ideas',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$record_id = intval( $record_id );

		if ( 'category' === $record_type ) {
			$term       = get_term( $record_id, 'product_cat' );
			$record_url = get_term_link( $term );
		} else {
			$record_url = get_permalink( $record_id );
		}

		$curl_params                               = array();
		$curl_params['storeId']                    = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['recordId']                   = (string) $record_id;
		$curl_params['ipAddress']                  = wtai_get_ip_address();
		$curl_params['url']                        = $record_url;
		$curl_params['performCompetitiveAnalysis'] = false;
		$curl_params['type']                       = ucfirst( $record_type );

		// For debugging ranked keywords data only.
		if ( defined( 'WTAI_TEST_PRODUCT_URL' ) ) {
			$curl_params['url'] = WTAI_TEST_PRODUCT_URL;
		}

		$keywords = array();
		if ( isset( $fields['manualKeywords'] ) ) {
			foreach ( $fields['manualKeywords'] as $kw ) {
				if ( '' !== trim( $kw ) ) {
					$keywords[] = strtolower( stripslashes( $kw ) );
				}
			}
		}

		if ( $keywords ) {
			$curl_params['text'] = $keywords;
		}

		$target_keywords = array();
		if ( isset( $fields['targetKeywords'] ) ) {
			foreach ( $fields['targetKeywords'] as $kw ) {
				if ( '' !== trim( $kw ) ) {
					$target_keywords[] = strtolower( stripslashes( $kw ) );
				}
			}
		}

		if ( $target_keywords ) {
			$curl_params['targetKeywords'] = $target_keywords;
		}

		if ( isset( $fields['location_code'] ) ) {
			$curl_params['location_code'] = $fields['location_code'];
		}

		if ( isset( $fields['pageSize'] ) ) {
			$curl_params['pageSize'] = intval( $fields['pageSize'] );
		}

		if ( isset( $fields['filterByCompetition'] ) ) {
			$competition_filter = array_filter( $fields['filterByCompetition'] );
			if ( $competition_filter ) {
				$curl_params['filterByCompetition'] = $competition_filter;
			}
		}

		if ( isset( $fields['filterBySearchVolumeMinimum'] ) ) {
			$curl_params['filterBySearchVolumeMinimum'] = $fields['filterBySearchVolumeMinimum'];
		}

		if ( isset( $fields['filterBySearchVolumeMaximum'] ) ) {
			$curl_params['filterBySearchVolumeMaximum'] = $fields['filterBySearchVolumeMaximum'];
		}

		// Waiting for the actual field name.
		if ( isset( $fields['sorting'] ) ) {
			$curl_params['sorting'] = $fields['sorting'];
		}

		if ( true === $fields['refresh'] ) {
			$curl_params['refresh'] = true;
		}

		if ( true === $fields['nogenerate'] || ! $keywords ) {
			$curl_params['nogenerate'] = true;
		}

		if ( $fields['page'] ) {
			$curl_params['page'] = intval( $fields['page'] );
		}

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

		$http_header = $api_results['http_header'];

		if ( $api_results['status'] ) {
			if ( 200 === intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );

				$nomatch = $api_results['not_match_keywords_location'];

				if ( is_array( $api_results ) ) {
					$results = $api_results;
				} else {
					$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
				}

				if ( 40202 === intval( $api_results['status_code'] ) || 40209 === intval( $api_results['status_code'] ) || 50301 === intval( $api_results['status_code'] ) ) {
					$results['error'] = WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE;
				}
			} else {
				$results['error'] = WTAI_KEYWORD_GENERAL_ERROR_MESSAGE;
			}
		} elseif ( ! $api_results['result'] ) {
				$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
		} else {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			}
		}

		$results['detailed_result'] = array(
			'api_result'  => $api_results,
			'headers'     => $headers,
			'curl_params' => $curl_params,
			'http_header' => $http_header,
		);

		return $results;
	}


	/**
	 * Set selected keyword semantics text.
	 *
	 * @param  array  $results   Results.
	 * @param  int    $record_id Product ID.
	 * @param  array  $values    Values.
	 * @param  string $record_type Record type.
	 */
	public function set_selected_keyword_semantics_text( $results, $record_id = null, $values = array(), $record_type = 'product' ) {
		$results = array();
		if ( ! empty( $record_id ) ) {
			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

			$global_rule_fields      = apply_filters( 'wtai_global_rule_fields', array() );
			$max_keyword_char_length = $global_rule_fields['maxKeywordLength'];

			$curl_params             = array();
			$curl_params['storeId']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['recordId'] = (string) $record_id;
			$curl_params['text']     = trim( substr( $values['keyword'], 0, $max_keyword_char_length ) );
			$curl_params['selected'] = $values['semantic_keywords'];
			$curl_params['type']     = ucfirst( $record_type );

			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Suggestion/Keywords',
			);

			$token = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();

			$headers = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
			);

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );

				$results = $api_results;
			} elseif ( ! $api_results['result'] ) {
					$results['error'] = 'Error Header Code : ' . $api_results['http_header'];
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['error'] ) ) {
					$results['error'] = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Set custom audience ajax callback
	 */
	public function set_custom_audience_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$result        = array();
		$message_token = '';
		$access        = 1;
		$error         = 0;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = apply_filters( 'wtai_web_token', '' );
					if ( $web_token ) {
						$product_id      = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$custom_audience = isset( $_POST['customAudience'] ) ? sanitize_text_field( wp_unslash( $_POST['customAudience'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime     = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$data_type       = isset( $_POST['data_type'] ) ? sanitize_text_field( wp_unslash( $_POST['data_type'] ) ) : 'Product'; // phpcs:ignore WordPress.Security.NonceVerification

						$values = array(
							'browsertime'    => $browsertime,
							'token'          => $web_token,
							'customAudience' => $custom_audience,
							'type'           => $data_type,
						);

						$results = apply_filters( 'wtai_set_custom_audience_text', array(), $product_id, $values );
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$access = 0;
				}
			} else {
				$message_token = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'results' => $results,
					'access'  => $access,
					'message' => $message_token,
				)
			);
			exit;
		}
	}

	/**
	 * Set custom audience text
	 *
	 * @param array $results    The results.
	 * @param int   $product_id The product id.
	 * @param array $values    The values.
	 *
	 * @return array $results The results.
	 */
	public function set_custom_audience_text( $results, $product_id = null, $values = array() ) {
		$results = array();
		if ( ! empty( $product_id ) ) {
			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

			$global_rule_fields      = apply_filters( 'wtai_global_rule_fields', array() );
			$max_keyword_char_length = $global_rule_fields['maxKeywordLength'];

			$product_name = get_the_title( $product_id );
			$product_name = trim( substr( $product_name, 0, $max_keyword_char_length ) );

			$curl_params             = array();
			$curl_params['storeId']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['recordId'] = $product_id;
			$curl_params['text']     = $product_name;
			$curl_params['selected'] = $values['customAudience'];

			if ( $values['type'] ) {
				$curl_params['type'] = ucfirst( $values['type'] );
			}

			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Suggestion/Audience',
			);

			$token = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();

			$headers = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
			);

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );

				$results = $api_results;
			} elseif ( ! $api_results['result'] ) {
					$results['error'] = 'Error Header Code : ' . $api_results['http_header'];
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['error'] ) ) {
					$results['error'] = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Create custom log folder
	 */
	public function create_logs_folder() {
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'];
		$upload_url = $upload['url'];

		$upload_dir = $upload_dir . '/writextai-logs';
		$upload_url = $upload_url . '/writextai-logs';

		if ( ! is_dir( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		$dirs = array(
			'dir' => $upload_dir,
			'url' => $upload_url,
		);

		return $dirs;
	}

	/**
	 * Custom API logging.
	 *
	 * @param string $error_msg Error message.
	 * @param string $log_file Log file name.
	 * @param bool   $seperate_log_by_date Seperate log by date.
	 */
	public function log( $error_msg = '', $log_file = 'writextai-api-logs.log', $seperate_log_by_date = true ) {
		$upload_dir = $this->create_logs_folder();
		$file_dir   = $upload_dir['dir'] . '/';

		if ( $seperate_log_by_date ) {
			$base_filename       = basename( $log_file );
			$extension           = pathinfo( $base_filename, PATHINFO_EXTENSION );
			$bydate_log_filename = str_replace( '.' . $extension, '-' . gmdate( 'Y-m-d' ) . '.' . $extension, $base_filename );
			$log_file            = $bydate_log_filename;
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( current_time( 'Y-m-d H:i:s' ) . ' :: ' . $error_msg . "\n", 3, $file_dir . $log_file );
	}

	/**
	 * Get generate queue progress.
	 */
	public function get_generate_queue_progress() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			$request_id = isset( $_POST['requestID'] ) ? sanitize_text_field( wp_unslash( $_POST['requestID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			if ( $request_id ) {
				$api_results = apply_filters( 'wtai_generate_product_bulk', array(), $request_id );

				if ( ! $api_results['queuedIds'] ) {
					$api_results['queuedIds'] = array();
				}

				if ( '1' === $api_results['completed'] ) {
					update_post_meta( $product_id, 'wtai_bulk_queue_id', '' );
				} else {
					$estimated_end_time = $api_results['estimatedEndTime'];
					$time_now           = strtotime( current_time( 'mysql' ) );
					$start_time         = $api_results['startTime'];

					// Get number of hours difference between startTime and estimatedEndTime.
					$diff  = abs( $time_now - strtotime( $estimated_end_time ) );
					$hours = floor( $diff / ( 60 * 60 ) );
					if ( $hours > 24 ) {
						update_post_meta( $product_id, 'wtai_bulk_queue_id', '' );

						$api_results_orig = $api_results;

						// Force stop queue if it takes more than 24 hours.
						$api_results = array(
							'error'            => 1,
							'hours'            => $hours,
							'estimated'        => strtotime( $estimated_end_time ),
							'start'            => strtotime( $start_time ),
							'diff'             => $diff,
							'time_now'         => $time_now,
							'queuedIds'        => $api_results['queuedIds'],
							'api_results_orig' => $api_results_orig,
						);
					} else {
						$api_results['hours']     = $hours;
						$api_results['estimated'] = strtotime( $estimated_end_time );
						$api_results['start']     = strtotime( $start_time );
						$api_results['diff']      = $diff;
						$api_results['time_now']  = $time_now;
					}
				}
			} else {
				$api_results = array(
					'error'     => 1,
					'queuedIds' => array(),
				);
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;

			$api_results = array(
				'error'     => 1,
				'queuedIds' => array(),
				'message'   => $message,
			);
		}

		echo wp_json_encode( $api_results );

		exit;
	}

	/**
	 * Bulk generate queue all products.
	 *
	 * @param array $results Results.
	 */
	public function get_generate_product_bulk_queue_all( $results ) {

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Bulk/',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );
		}

		return $results;
	}

	/**
	 * Reload loader ajax callback.
	 */
	public function reload_loader_data() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$access               = 1;
		$jobs_user_ids        = array();
		$job_loader_data      = array();
		$finished_product_ids = array();
		$all_pending_ids      = array();
		$has_error            = 0;
		$html                 = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$show_hidden = isset( $_GET['show_hidden'] ) && ( 'yes' === $_GET['show_hidden'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs        = wtai_get_bulk_generate_jobs( true );

				$output = $this->get_generate_bulk_data( array(), $jobs, false, $show_hidden );

				$html                 = $output['html'];
				$jobs_user_ids        = $output['jobs_user_ids'];
				$job_loader_data      = $output['job_loader_data'];
				$finished_product_ids = $output['finished_product_ids'];

				if ( $output['has_error'] ) {
					$has_error = 1;
				}

				$all_pending_ids = wtai_get_all_pending_bulk_ids( $finished_product_ids );
			}
		}

		echo wp_json_encode(
			array(
				'html'            => $html,
				'jobs_user_ids'   => $jobs_user_ids,
				'job_loader_data' => $job_loader_data,
				'all_pending_ids' => $all_pending_ids,
				'has_error'       => $has_error,
			)
		);
		exit;
	}

	/**
	 * Retry bulk generate ajax callback.
	 */
	public function retry_bulk_generate() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$access               = 1;
		$jobs_user_ids        = array();
		$job_loader_data      = array();
		$finished_product_ids = array();
		$all_pending_ids      = array();
		$has_error            = 0;
		$html                 = '';
		$message              = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$request_id = isset( $_POST['requestID'] ) ? sanitize_text_field( wp_unslash( $_POST['requestID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( '' !== $request_id ) {
					$api_results = apply_filters( 'wtai_generate_product_bulk_retry', '', $request_id );

					$jobs = wtai_get_bulk_generate_jobs( true );

					$output = $this->get_generate_bulk_data( array(), $jobs, false, $show_hidden );

					$html                 = $output['html'];
					$jobs_user_ids        = $output['jobs_user_ids'];
					$job_loader_data      = $output['job_loader_data'];
					$finished_product_ids = $output['finished_product_ids'];

					if ( $output['has_error'] ) {
						$has_error = 1;
					}
				}

				$all_pending_ids = wtai_get_all_pending_bulk_ids( $finished_product_ids );
			} else {
				$message   = WTAI_INVALID_NONCE_MESSAGE;
				$has_error = 1;
			}
		}

		echo wp_json_encode(
			array(
				'html'            => $html,
				'jobs_user_ids'   => $jobs_user_ids,
				'job_loader_data' => $job_loader_data,
				'all_pending_ids' => $all_pending_ids,
				'has_error'       => $has_error,
				'message'         => $message,
			)
		);

		exit;
	}

	/**
	 * Retry product bulk generate.
	 *
	 * @param array  $results Results initial array.
	 * @param string $request_id Request ID.
	 * @return array $results Results from API.
	 */
	public function writetextai_generate_product_bulk_retry( $results, $request_id = null ) {
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Bulk/' . $request_id . '/retry',
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'POST' );

		$results = 0;
		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = 1;
		}

		return $results;
	}

	/**
	 * Get credits count.
	 *
	 * @param array $credits Credits.
	 * @return array $credits Credits.
	 */
	public function get_credits_count( $credits = array() ) {
		$current_time = strtotime( current_time( 'mysql' ) );

		$etag_credit_key          = get_option( 'wtai_etag_credit_key', '' );
		$etag_credit_key_lasttime = get_option( 'wtai_etag_credit_key_lasttime', '' );
		$credits_count_array      = get_option( 'wtai_credits_count', array() );

		// Lets check if etag has refreshed.
		$do_etag_refresh = false;
		if ( $etag_credit_key ) {
			if ( ! $etag_credit_key_lasttime ) {
				$do_etag_refresh = true;
			} else {
				$refresh_time_diff = $current_time - $etag_credit_key_lasttime;
				$diff_minutes      = $refresh_time_diff / 60;

				if ( $diff_minutes >= 30 ) {
					$etag_settings = array(
						'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Etags',
					);
					$etag_headers  = array(
						'Cache-Control' => 'no-cache',
						'Authorization' => 'Bearer ' . $this->get_web_token(),
						'If-None-Match' => '"' . $etag_credit_key . '"',
					);
					$etag_content  = $this->get_data_via_api( array(), $etag_settings, $etag_headers, 'GET' );

					if ( 304 === intval( $etag_content['http_header'] ) ) {
						$do_etag_refresh = false;
					} else {
						$do_etag_refresh = true;
					}
				}
			}
		} else {
			$do_etag_refresh = true;
		}

		if ( ! $credits_count_array ) {
			$do_etag_refresh = true;
		}

		if ( $do_etag_refresh ) {
			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Etags',
			);
			$headers  = array(
				'Cache-Control' => 'no-cache',
				'Authorization' => 'Bearer ' . $this->get_web_token(),
			);
			$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

			$credits = array();
			if ( 200 === intval( $content['http_header'] ) ) {
				$result = json_decode( $content['result'], true );

				$credits_key = $result['credits'];

				if ( $credits_key ) {
					$credits_key = str_replace( '"', '', $credits_key );

					update_option( 'wtai_etag_credit_key', $credits_key );
					update_option( 'wtai_etag_credit_key_lasttime', strtotime( current_time( 'mysql' ) ) );

					$settings_credits = array(
						'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/Credits',
					);
					$headers_key      = array(
						'Cache-Control' => 'no-cache',
						'Authorization' => 'Bearer ' . $this->get_web_token(),
					);

					$response_credit = $this->get_data_via_api( array(), $settings_credits, $headers_key, 'GET' );
					if ( 200 === intval( $response_credit['http_header'] ) ) {
						$credits = json_decode( $response_credit['result'], true );

						if ( $credits ) {
							update_option( 'wtai_credits_count', $credits );
						}
					}
				}
			}
		} else {
			$credits = $credits_count_array;
		}

		if ( $credits && isset( $credits['generation'] ) ) {
			$generation_parsed = array();
			foreach ( $credits['generation'] as $key => $val ) {
				$internal_key = '';
				if ( 'page title' === $key ) {
					$internal_key = 'page_title';
				} elseif ( 'page description' === $key ) {
					$internal_key = 'page_description';
				} elseif ( 'product description' === $key ) {
					$internal_key = 'product_description';
				} elseif ( 'excerpt' === $key ) {
					$internal_key = 'product_excerpt';
				} elseif ( 'open graph text' === $key ) {
					$internal_key = 'open_graph';
				}

				if ( $internal_key ) {
					$generation_parsed[ $internal_key ] = $val;
				}
			}

			$generation_tier_parsed = array();
			foreach ( $credits['generationTier'] as $key => $val ) {
				$internal_key = '';
				if ( 'page title' === $key ) {
					$internal_key = 'page_title';
				} elseif ( 'page description' === $key ) {
					$internal_key = 'page_description';
				} elseif ( 'product description' === $key ) {
					$internal_key = 'product_description';
				} elseif ( 'excerpt' === $key ) {
					$internal_key = 'product_excerpt';
				} elseif ( 'open graph text' === $key ) {
					$internal_key = 'open_graph';
				}

				if ( $internal_key ) {
					$generation_tier_parsed[ $internal_key ] = $val;
				}
			}

			$credits['generationParsed']     = $generation_parsed;
			$credits['generationTierParsed'] = $generation_tier_parsed;
		}

		return $credits;
	}

	/**
	 * Debug API region list.
	 */
	public function debug_api_region() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['1902debugapiregions'] ) && '1' === $_GET['1902debugapiregions'] ) {
			$this->get_api_region();

			die();
		}
	}

	/**
	 * Get closest api region.
	 *
	 * @return string $fastest_region Faster region.
	 */
	public function get_api_region() {
		$settings = array(
			'remote_url' => 'https://' . WTAI_API_HOST . '/web/Regions',
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
		);

		$content = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		$fastest_region          = '';
		$region_ping_times_array = array();
		if ( 200 === intval( $content['http_header'] ) ) {
			$regions = json_decode( $content['result'], true );

			if ( $regions ) {
				foreach ( $regions as $region ) {
					$settings_ping = array(
						'remote_url' => 'https://' . $region . '/text/Ping',
					);

					$headers_ping = array(
						'Cache-Control' => 'no-cache',
					);

					$content_ping = $this->get_data_via_api( array(), $settings_ping, $headers_ping, 'GET' );

					if ( 200 === intval( $content_ping['http_header'] ) ) {
						$region_ping_time = $content_ping['execution_time'];

						$region_ping_times_array[ $region ] = $region_ping_time;
					}
				}

				asort( $region_ping_times_array );

				$region_ping_times_keys_array = array_keys( $region_ping_times_array );

				$fastest_region = $region_ping_times_keys_array[0];

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['1902debugapiregions'] ) && '1' === $_GET['1902debugapiregions'] ) {
					echo wp_kses( "fastest region: $fastest_region<br>", 'post' );
					echo '<pre>';
					print_r( $region_ping_times_array ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					echo '</pre>';
				}
			}
		}

		return $fastest_region;
	}

	/**
	 * Record product reviewed status.
	 *
	 * @param array $api_results API results.
	 * @param int   $product_id Product ID.
	 * @param bool  $reviewed Reviewed status.
	 * @param int   $browsertime Browser time.
	 *
	 * @return array $api_results Filtered api results.
	 */
	public function record_product_reviewed( $api_results = array(), $product_id = 0, $reviewed = true, $browsertime = 0 ) {
		// Pass to api.
		$fields = apply_filters( 'wtai_fields', array() );
		$fields = array_keys( $fields );

		$api_results = array();
		$api_fields  = array(
			'browsertime' => $browsertime,
			'publish'     => false,
			'reviewed'    => $reviewed,
		);

		$api_result_values = apply_filters(
			'wtai_generate_product_text',
			array(),
			$product_id,
			array(
				'fields'               => $fields,
				'includeUpdateHistory' => true,
			)
		);

		foreach ( $fields as $field_key ) {
			$text_id = $api_result_values[ $product_id ][ $field_key ][0]['id'];

			if ( $text_id ) {
				$field_published = false;
				$field_reviewed  = 0;
				if ( $reviewed ) {
					if ( isset( $api_result_values[ $product_id ][ $field_key ][0]['history'][0] ) ) {
						$field_published = $api_result_values[ $product_id ][ $field_key ][0]['history'][0]['publish'];
						if ( '1' === $field_published ) {
							$field_published = true;
						} else {
							$field_published = false;
						}

						$field_reviewed = $api_results[ $product_id ][ $field ][0]['history'][0]['reviewed'];
					}

					if ( true === $field_published ) {
						$api_fields['publish'] = true;
					}
				}

				$proceed_with_update = true;
				if ( $reviewed && true === $field_published ) {
					$proceed_with_update = false;
				}

				if ( $proceed_with_update ) {
					$api_results[ $product_id ][ $field_key ] = array(
						'textId'  => esc_attr( $api_result_values[ $product_id ][ $field_key ][0]['id'] ),
						'output'  => $api_result_values[ $product_id ][ $field_key ][0]['value'],
						'publish' => $field_published,
					);
				}
			}
		}

		if ( $api_results ) {
			$api_results = apply_filters( 'wtai_stored_generate_text', $api_results, $api_fields );
		}

		return $api_results;
	}

	/**
	 * Get available credits from API
	 *
	 * @param array $result Result.
	 */
	public function get_available_credits( $result = array() ) {
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Credit',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $this->get_web_token(),
		);
		$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( 200 === intval( $content['http_header'] ) ) {
			$content = json_decode( $content['result'], true );

			return $content;
		}

		return array();
	}

	/**
	 * Check if there is enough credit to use
	 *
	 * @param int $credit_to_use Credit to use.
	 *
	 * @return array $output Output.
	 */
	public function is_available_credit( $credit_to_use = 0 ) {
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Credit/isAvailable/' . $credit_to_use,
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $this->get_web_token(),
		);
		$content  = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		$is_available = 0;
		$message      = 'Insufficient credits.'; // Internal message for insufficient credits.
		if ( 200 === intval( $content['http_header'] ) ) {
			$is_available = 1;
			$message      = '';
		} else {
			$content = json_decode( $content['result'], true );
			$message = $content['error'];
		}

		$output = array(
			'is_available' => $is_available,
			'message'      => $message,
		);

		return $output;
	}

	/**
	 * Check premium account.
	 */
	public function check_premium_account() {
		global $pagenow;

		$referrer = wp_get_referer();

		$check_account_details = false;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( is_user_logged_in() &&
			( ( isset( $_GET['page'] ) && ( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) ) || // phpcs:ignore WordPress.Security.NonceVerification
			( $referrer && ( ( false !== strpos( $referrer, 'write-text-ai' ) || false !== strpos( $referrer, 'write-text-ai-settings' ) || false !== strpos( $referrer, 'write-text-ai-category' ) ) ) ) ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
			$check_account_details = true;
		}

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( $is_ajax && isset( $_REQUEST['action'] ) && ( 'wtai_poll_background_jobs' === $_REQUEST['action'] || 'heartbeat' === $_REQUEST['action'] ) ) {
			$check_account_details = false;
		}

		if ( ! $check_account_details ) {
			return;
		}

		$account_credit_details = wtai_get_account_credit_details();

		$is_premium = isset( $account_credit_details['is_premium'] ) ? $account_credit_details['is_premium'] : false;

		define( 'WTAI_PREMIUM', $is_premium );
		define( 'WTAI_CREDIT_ACCOUNT_DETAILS', $account_credit_details );
	}

	/**
	 * Get product extension reviews.
	 *
	 * @param array $reviews Review data.
	 * @param int   $product_id Product ID.
	 */
	public function get_product_extension_review( $reviews = array(), $product_id = 0 ) {
		if ( $product_id ) {
			$languages = array();

			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ), false );
			$language = str_replace( '_', '-', $language );

			if ( $language ) {
				$languages[] = $language;
			}

			$review_extension_lang = wtai_get_review_extension_language();
			if ( $review_extension_lang ) {
				$languages[] = $review_extension_lang;
			}

			$params = array(
				'type'     => 'Product',
				'recordId' => $product_id,
				'all'      => 'true',
			);

			$reviews = array();
			foreach ( $languages as $lang ) {
				$params['language'] = $lang;

				$settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/text/Review?' . http_build_query( $params ),
				);

				$headers = array(
					'Cache-Control' => 'no-cache',
					'Host'          => $this->api_base_url,
					'Authorization' => 'Bearer ' . $this->get_web_token(),
					'Content-Type'  => 'application/json',
				);

				$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

				if ( 200 === intval( $api_results['http_header'] ) ) {
					$reviews_init = json_decode( $api_results['result'], true );

					if ( $reviews_init && is_array( $reviews_init['reviews'] ) && isset( $reviews_init['reviews'][0] ) ) {
						$reviews = $reviews_init;
					}
				} else {
					$reviews = array();
				}

				if ( $reviews ) {
					break;
				}
			}
		}

		return $reviews;
	}

	/**
	 * Save product extension review.
	 *
	 * @param array $results Results data.
	 * @param array $review_ids Reviews id list.
	 * @param int   $product_id Product ID.
	 * @param int   $field_types Field types.
	 */
	public function save_product_extension_review( $results, $review_ids = array(), $product_id = null, $field_types = array() ) {
		$results = array();
		if ( $review_ids ) {
			foreach ( $review_ids as $review_id ) {
				$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ), false );
				$language = str_replace( '_', '-', $language );

				$review_extension_lang = wtai_get_review_extension_language();
				if ( $review_extension_lang ) {
					$language = $review_extension_lang;
				}

				$curl_params             = array();
				$curl_params['id']       = $review_id;
				$curl_params['type']     = 'Product';
				$curl_params['recordId'] = (string) $product_id;
				$curl_params['url']      = get_permalink( $product_id );

				$fields = array();
				foreach ( $field_types as $type ) {
					$type_api_key = apply_filters( 'wtai_field_conversion', trim( $type ), 'product' );

					$fields[] = array(
						'field'  => $type_api_key,
						'status' => -1,
					);
				}

				$curl_params['fields'] = $fields;

				$settings = array(
					'remote_url' => 'https://' . $this->api_base_url . '/text/Review',
				);

				$headers = array(
					'Cache-Control'   => 'no-cache',
					'Host'            => $this->api_base_url,
					'Authorization'   => 'Bearer ' . $this->get_web_token(),
					'Content-Type'    => 'application/json',
					'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
				);

				$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

				if ( 200 === intval( $api_results['http_header'] ) ) {
					$api_results = json_decode( $api_results['result'], true );

					$results[ $review_id ] = $api_results;
				}
			}
		}

		return $results;
	}

	/**
	 * Get generate product status from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param array  $fields     Fields from the product.
	 * @param string $continuation_token Continuation token from the API.
	 */
	public function get_review_product_extension_status( $results, $fields = array(), $continuation_token = '' ) {
		$params            = $fields;
		$params['type']    = 'Product';
		$params['storeId'] = str_replace( array( 'http://', 'https://' ), '', get_site_url() );

		$languages = array();

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language(), false );
		$language = str_replace( '_', '-', $language );

		if ( $language ) {
			$languages[] = $language;
		}

		$review_extension_lang = wtai_get_review_extension_language();
		if ( $review_extension_lang ) {
			$languages[] = $review_extension_lang;
		}

		if ( isset( $params['status'] ) ) {
			if ( ! is_array( $params['status'] ) ) {
				$params['status'] = rawurlencode( apply_filters( 'wtai_field_conversion', $params['status'], 'product' ) );
			} else {
				$wtai_statuses = array();
				foreach ( $params['status'] as $status_value ) {
					$wtai_statuses[] = rawurlencode( apply_filters( 'wtai_field_conversion', $status_value, 'product' ) );
				}

				if ( $wtai_statuses ) {
					$params['status'] = $wtai_statuses;
				}
			}
		}

		$add_params = '';
		if ( isset( $params['wtai_fields'] ) ) {
			if ( is_array( $params['wtai_fields'] ) ) {
				$wtai_fields = array();
				foreach ( $params['wtai_fields'] as $value ) {
					$wtai_fields[] = 'fields=' . rawurlencode( apply_filters( 'wtai_field_conversion', $value, 'product' ) );
				}
				$add_params .= implode( '&', $wtai_fields );
			}

			unset( $params['wtai_fields'] );
		}

		if ( isset( $params['startDate'] ) ) {
			$params['startDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['startDate'] ) );
		}

		if ( isset( $params['endDate'] ) ) {
			$params['endDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['endDate'] ) );
		}

		if ( $add_params ) {
			$add_params = '&' . $add_params;
		}

		foreach ( $languages as $lang ) {
			$params['language'] = $lang;

			$url = 'https://' . $this->api_base_url . '/text/Review/Status?' . http_build_query( $params ) . $add_params;

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['wtai_grid_filter_debug'] ) ) {
				echo wp_kses( $url, 'post' );
			}

			$settings = array(
				'remote_url' => $url,
			);

			$headers = array(
				'Cache-Control' => 'no-cache',
				'Authorization' => 'Bearer ' . $this->get_web_token(),
				'Content-Type'  => 'application/json',
			);

			$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

			$continuation_token = '';
			if ( 200 === intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );

				if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
					$results = array();
					foreach ( $api_results['records'] as $records ) {
						if ( ! in_array( $records['recordId'], $results, true ) ) {
							$results[] = $records['recordId'];
						}
					}

					$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
					if ( $continuation_token ) {
						$orig_params = $params;
						while ( $continuation_token ) {
							$orig_params['continuationToken'] = $continuation_token;

							$url = 'https://' . $this->api_base_url . '/text/Review/Status?' . http_build_query( $orig_params ) . $add_params;

							$settings = array(
								'remote_url' => $url,
							);

							$headers = array(
								'Cache-Control' => 'no-cache',
								'Authorization' => 'Bearer ' . $this->get_web_token(),
								'Content-Type'  => 'application/json',
							);

							$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );
							if ( 200 === intval( $api_results['http_header'] ) ) {
								$api_results = json_decode( $api_results['result'], true );

								if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
									foreach ( $api_results['records'] as $records ) {
										if ( ! in_array( $records['recordId'], $results, true ) ) {
											$results[] = $records['recordId'];
										}
									}

									$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
								} else {
									$continuation_token = '';
									break;
								}
							} else {
								$continuation_token = '';
								break;
							}
						}
					}

					break;
				}
			}
		}

		return $results;
	}

	/**
	 * Send main image to API.
	 *
	 * @param array  $results  Results from the API.
	 * @param int    $record_id Product ID.
	 * @param int    $attachment_id Attachment ID.
	 * @param int    $browsertime   Browser Time.
	 * @param bool   $overwrite   Overwrite parameter.
	 * @param string $type   Type of the product.
	 */
	public function save_product_image_to_api( $results = array(), $record_id = 0, $attachment_id = 0, $browsertime = 0, $overwrite = false, $type = 'product' ) {
		if ( ! $record_id || ! $attachment_id ) {
			return array();
		}

		$image_url = wp_get_attachment_url( $attachment_id );

		if ( 'category' === $type ) {
			$language = apply_filters( 'wtai_language_code', get_locale() );
		} else {
			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $record_id ) );
		}

		$language = str_replace( '_', '-', $language );

		$curl_params                = array();
		$curl_params['storeId']     = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['ipAddress']   = $this->getClientIP();
		$curl_params['browsertime'] = intval( $browsertime );
		$curl_params['imageId']     = strval( $attachment_id );

		$skip_url_param = false;
		if ( false !== strpos( $curl_params['storeId'], '.test' ) ) {
			$skip_url_param = true;
		}

		if ( wtai_is_image_publicly_available( $image_url ) === true && ! $skip_url_param ) {
			$curl_params['url'] = $image_url;
		} else {
			$attachment_file = get_attached_file( $attachment_id );

			// Read the file content.
			$image_data = file_get_contents( $attachment_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			// Convert to base64.
			$image_data_formatted = '';
			if ( $image_data ) {
				$image_base64 = base64_encode( $image_data ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode

				$image_mime_type = mime_content_type( $attachment_file );

				$image_data_formatted = 'data:' . $image_mime_type . ';base64,' . $image_base64;
			}

			if ( $image_data_formatted ) {
				$curl_params['imageData'] = $image_data_formatted;
			}
		}

		if ( $overwrite ) {
			$curl_params['overwrite'] = true;
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			$results = $api_results;
		} else {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			}
		}

		return $results;
	}

	/**
	 * Get image data from API.
	 *
	 * @param array  $results  Results from the API.
	 * @param int    $record_ids Record IDS.
	 * @param int    $attachment_id Attachment ID.
	 * @param bool   $include_history   Include history.
	 * @param string $type   Type.
	 */
	public function get_product_image_from_api( $results = array(), $record_ids = array(), $attachment_id = 0, $include_history = false, $type = 'product' ) {
		if ( ! $attachment_id ) {
			return array();
		}

		if ( 'category' === $type ) {
			$language = apply_filters( 'wtai_language_code', get_locale() );
		} else {
			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), $record_ids );
		}

		$language = str_replace( '_', '-', $language );

		$params             = array();
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;
		$params['imageId']  = $attachment_id;

		if ( $include_history ) {
			$params['includeUpdateHistory'] = $include_history;
		}

		$settings    = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image?' . http_build_query( $params ),
		);
		$headers     = array(
			'Cache-Control'   => 'no-cache',
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Accept-Language' => $language,
		);
		$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );
		}

		return $results;
	}

	/**
	 * Get alt texts for product images.
	 *
	 * @param array $results  Results from the API.
	 * @param int   $product_id Product ID.
	 * @param int   $image_id Image id.
	 * @param bool  $include_history Include history.
	 */
	public function get_alt_text_for_image( $results = array(), $product_id = 0, $image_id = 0, $include_history = false ) {
		if ( ! $product_id || ! $image_id ) {
			return array();
		}

		$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) );
		$language = str_replace( '_', '-', $language );

		$image_id = strval( $image_id );

		$params             = array();
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;
		$params['imageId']  = $image_id;

		if ( $include_history ) {
			$params['includeUpdateHistory'] = 'true';
		}

		$settings    = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image?' . http_build_query( $params ),
		);
		$headers     = array(
			'Cache-Control'   => 'no-cache',
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Accept-Language' => $language,
		);
		$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );
		}

		return $results;
	}

	/**
	 * Get alt texts for product images.
	 *
	 * @param array $results  Results from the API.
	 * @param int   $product_id Product ID.
	 * @param array $image_ids Array of image attachment ids.
	 * @param bool  $include_history Include history.
	 */
	public function get_alt_text_for_images( $results = array(), $product_id = 0, $image_ids = array(), $include_history = false ) {
		if ( ! $image_ids ) {
			$image_ids = wtai_get_product_image( $product_id );
		}

		if ( ! $image_ids ) {
			return array();
		}

		$image_ids = array_map( 'strval', $image_ids );

		$language = '';
		if ( $product_id ) {
			$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) );
		} else {
			$language = apply_filters( 'get_language_locale', wtai_get_site_language() );
		}

		$language = str_replace( '_', '-', $language );

		$params             = array();
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;
		$params['images']   = $image_ids;

		if ( $include_history ) {
			$params['includeUpdateHistory'] = 'true';
		}

		$settings    = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image/List?' . http_build_query( $params ),
		);
		$headers     = array(
			'Cache-Control'   => 'no-cache',
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Accept-Language' => $language,
		);
		$api_results = $this->get_data_via_api( array(), $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );

			if ( ! empty( $results ) && isset( $results['images'] ) ) {
				$results_image = $results['images'];

				$results = array();
				foreach ( $results_image as $key => $result ) {
					$image_id = $result['imageId'];

					$results[ $image_id ] = $result;
				}
			}
		}

		return $results;
	}

	/**
	 * Generate alt texts for product images.
	 *
	 * @param array $results  Results from the API.
	 * @param int   $product_id Product ID.
	 * @param array $image_ids Array of image attachment ids.
	 * @param array $keywords Array of keywords.
	 * @param int   $browsertime Browser time.
	 */
	public function generate_alt_text_for_images( $results = array(), $product_id = 0, $image_ids = array(), $keywords = array(), $browsertime = 0 ) {
		if ( ! $image_ids || ! $product_id ) {
			return array();
		}

		$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) );
		$language = str_replace( '_', '-', $language );

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image/GenerateAltText',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		// Split the request into multiple requests if the number of images is more than the max image per request.
		$global_rule_fields             = apply_filters( 'wtai_global_rule_fields', array() );
		$max_image_alt_text_per_request = $global_rule_fields['maxImageAltTextPerRequest'];

		$image_ctr       = 0;
		$split_index     = 0;
		$split_image_ids = array();
		foreach ( $image_ids as $image_id ) {
			if ( $image_ctr < $max_image_alt_text_per_request ) {
				$split_image_ids[ $split_index ][] = $image_id;
			} else {
				++$split_index;
				$split_image_ids[ $split_index ][] = $image_id;
				$image_ctr                         = -1;
			}

			++$image_ctr;
		}

		foreach ( $split_image_ids as $image_ids_to_process ) {
			$curl_params                = array();
			$curl_params['storeId']     = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['ipAddress']   = $this->getClientIP();
			$curl_params['browsertime'] = intval( $browsertime );
			$curl_params['productName'] = get_the_title( $product_id );
			$curl_params['language']    = $language;

			if ( $keywords ) {
				$curl_params['keywords'] = $keywords;
			}

			$images_data = array();
			foreach ( $image_ids_to_process as $image_id ) {
				// Make sure the image is uploaded in the API.
				$image_api_data = wtai_get_image_for_api_generation( $product_id, $image_id, $browser_time, false );

				$alt_text = get_post_meta( $image_id, '_wp_attachment_image_alt', true );

				if ( $image_api_data && isset( $image_api_data['url'] ) ) {
					$images_data[] = array(
						'imageId'      => strval( $image_id ),
						'currentValue' => strval( $alt_text ),
					);
				}
			}

			$curl_params['images'] = $images_data;

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

			if ( 200 === intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );

				$results[] = $api_results;
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['error'] ) ) {
					$results['error'] = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Generate alt text for images.
	 */
	public function get_generate_alt_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax        = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results        = array();
		$api_results    = array();
		$access         = 1;
		$message_token  = '';
		$message_notice = '';

		if ( $is_ajax ) {
			if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
				if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {
						$alt_image_ids = array();

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['altimages'] ) ) {
							$alt_image_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}

						$product_id  = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						$keywords = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['keywords'] ) ) {
							$keywords = isset( $_POST['keywords'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
						}

						// Handle alt images prompt.
						if ( $alt_image_ids ) {
							$api_results = apply_filters( 'wtai_generate_alt_text_for_images', array(), $product_id, $alt_image_ids, $keywords, $browsertime );

							if ( $api_results ) {
								$alt_images_generated_texts = array();
								foreach ( $api_results as $api_result ) {
									if ( isset( $api_result['texts'] ) ) {
										$alt_images_generated_texts = array_merge( $alt_images_generated_texts, $api_result['texts'] );
									}
								}

								$alt_images_parsed = array();
								foreach ( $alt_images_generated_texts as $alt_images_generated_text ) {
									$api_image_id = $alt_images_generated_text['imageId'];

									$alt_images_parsed[ $api_image_id ] = $alt_images_generated_text;
								}

								$processed_image_ids = array_keys( $alt_images_parsed );
								$failed_image_ids    = array_diff( $alt_image_ids, $processed_image_ids );

								/* translators: %1$s: Processed image ids, %2$s: total alt text images passed */
								$message_notice = sprintf( __( '%1$s out of %2$s image alt text/s were generated.', 'writetext-ai' ), count( $processed_image_ids ), count( $alt_image_ids ) );

								if ( count( $failed_image_ids ) > 0 ) {
									$failed_image_filenames = array();
									foreach ( $failed_image_ids as $failed_image_id ) {
										$image_url      = wp_get_attachment_url( $failed_image_id );
										$image_filename = basename( $image_url );

										$failed_image_filenames[] = $image_filename;
									}

									/* translators: %s: Failed image filenames */
									$message_notice .= ' <div class="alt-image-error-message" >' . sprintf( __( 'The following image/s are invalid: "%s". Please upload a different image and try again.', 'writetext-ai' ), implode( ', ', $failed_image_filenames ) ) . '</div>';
								}

								$results['altImages']      = $alt_images_parsed;
								$results['message_notice'] = $message_notice;
							}
						}
					} else {
						$message_token = 'expire_token';
					}
				} else {
					$message_token = WTAI_INVALID_NONCE_MESSAGE;
				}
			}
		}

		echo wp_json_encode(
			array(
				'results'     => $results,
				'access'      => $access,
				'message'     => $message_token,
				'is_premium'  => $is_premium,
				'api_results' => $api_results,
			)
		);
		exit;
	}

	/**
	 * Transfer alt image text to WP platform
	 */
	public function transfer_image_alt_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results       = array();
		$message_token = '';
		$access        = 1;
		if ( $is_ajax ) {
			if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
				if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
					$web_token = $this->get_web_token();
					if ( $web_token ) {
						$product_id  = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$submittype  = isset( $_POST['submittype'] ) ? sanitize_text_field( wp_unslash( $_POST['submittype'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$publish     = isset( $_POST['publish'] ) && sanitize_text_field( wp_unslash( $_POST['publish'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$data_values = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['data_values'] ) ) {
							// phpcs:ignore WordPress.Security.NonceVerification
							if ( is_array( $_POST['data_values'] ) ) {
								$data_values = map_deep( wp_unslash( $_POST['data_values'] ), 'wp_kses_post' ); // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								// phpcs:ignore WordPress.Security.NonceVerification
								$data_values = wp_kses( wp_unslash( $_POST['data_values'] ), 'post' );
							}
						}

						$payload = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
							'publish'     => $publish,
							'product_id'  => $product_id,
							'data_values' => $data_values,
						);

						$api_results = apply_filters( 'wtai_save_alt_text_for_image_api', array(), $payload );

					} else {
						$message_token = 'expire_token';
					}
				} else {
					$message_token = WTAI_INVALID_NONCE_MESSAGE;
				}
			} else {
				$access = 0;
			}
		}

		echo wp_json_encode(
			array(
				'results'     => $results,
				'access'      => $access,
				'message'     => $message_token,
				'data_values' => $data_values,
				'api_results' => $api_results,
			)
		);
		exit;
	}

	/**
	 * Save alt data to API.
	 *
	 * @param array $results  Results from the API.
	 * @param array $payload  Payload.
	 */
	public function save_alt_text_for_image_api( $results = array(), $payload = array() ) {
		$product_id  = isset( $payload['product_id'] ) ? $payload['product_id'] : 0;
		$browsertime = isset( $payload['browsertime'] ) ? $payload['browsertime'] : 0;
		$data_values = isset( $payload['data_values'] ) ? $payload['data_values'] : array();
		$publish     = isset( $payload['publish'] ) ? $payload['publish'] : 0;

		if ( $publish ) {
			$publish = true;
		} else {
			$publish = false;
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image/altText',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) );
		$language = str_replace( '_', '-', $language );

		foreach ( $data_values as $value ) {
			$attachment_id = $value['attachment_id'];
			$alt_text      = $value['alt_text'];
			$text_id       = $value['text_id'];

			$curl_params                = array();
			$curl_params['storeId']     = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['ipAddress']   = $this->getClientIP();
			$curl_params['browsertime'] = intval( $browsertime );
			$curl_params['publish']     = $publish;
			$curl_params['language']    = $language;
			$curl_params['imageId']     = strval( $attachment_id );
			$curl_params['textId']      = $text_id;
			$curl_params['value']       = wp_strip_all_tags( $alt_text );

			if ( $publish ) {
				// Tag as Reviewed = true automatically if published.
				$curl_params['reviewed'] = true;

				// Update alt text value in platform.
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
			}

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

			if ( 200 === intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );

				$results[ $attachment_id ] = $api_results;
			} else {
				$results[ $attachment_id ] = $api_results;
			}
		}

		return $results;
	}

	/**
	 * Record statistics.
	 *
	 * @param string $action  Action.
	 * @param int    $count  Count.
	 */
	public function record_installation_statistics( $action = '', $count = 0 ) {
		$settings = array(
			'remote_url' => 'https://' . WTAI_API_HOST . '/web/Statistics',
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Content-Type'  => 'application/json',
		);

		$curl_params             = array();
		$curl_params['action']   = $action;
		$curl_params['domain']   = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['platform'] = 'WordPress';
		$curl_params['count']    = $count;

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );

		return $api_results;
	}

	/**
	 * Mark image alt text as reviewed.
	 *
	 * @param array $results  Results.
	 * @param int   $product_id  Product ID.
	 * @param array $data_values  Data payload values.
	 * @param bool  $reviewed  Reviewed status.
	 */
	public function record_alt_image_id_reviewed_api( $results = array(), $product_id = 0, $data_values = array(), $reviewed = false ) {
		if ( ! $product_id || ! $data_values ) {
			return $results;
		}

		$language = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), array( $product_id ) );
		$language = str_replace( '_', '-', $language );

		$api_results = array();
		$results     = array();

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Image/altText',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		foreach ( $data_values as $value ) {
			$attachment_id = $value['image_id'];
			$text_id       = $value['text_id'];
			$publish       = $value['publish'];
			$value         = $value['value'];

			$curl_params              = array();
			$curl_params['storeId']   = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['ipAddress'] = $this->getClientIP();
			$curl_params['language']  = $language;
			$curl_params['imageId']   = strval( $attachment_id );
			$curl_params['textId']    = $text_id;
			$curl_params['reviewed']  = $reviewed;
			$curl_params['publish']   = $publish;
			$curl_params['value']     = $value;

			$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

			if ( 200 === intval( $api_results['http_header'] ) ) {
				$results[ $attachment_id ] = $api_results;
			} else {
				$results[ $attachment_id ] = $api_results;
			}
		}

		return $results;
	}

	/**
	 * Keywords AI analysis API call.
	 *
	 * @param  array  $results   Results.
	 * @param  int    $record_id Record ID.
	 * @param  array  $fields    Fields.
	 * @param  string $record_type Record type.
	 */
	public function get_ranked_keywords( $results, $record_id = 0, $fields = array(), $record_type = 'product' ) {
		if ( ! $record_id ) {
			return;
		}

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/KeywordAnalysis/Ranked',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$record_id = intval( $record_id );

		if ( 'category' === $record_type ) {
			$record_url = get_term_link( $record_id, 'product_cat' );

		} else {
			$record_url = get_permalink( $record_id );
		}

		$curl_params              = array();
		$curl_params['storeId']   = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['recordId']  = (string) $record_id;
		$curl_params['ipAddress'] = wtai_get_ip_address();
		$curl_params['url']       = $record_url;

		// For debugging ranked keywords data only.
		if ( defined( 'WTAI_TEST_PRODUCT_URL' ) ) {
			$curl_params['url'] = WTAI_TEST_PRODUCT_URL;
		}

		if ( isset( $fields['location_code'] ) ) {
			$curl_params['location_code'] = $fields['location_code'];
		}

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			if ( is_array( $api_results ) ) {
				$results['results'] = $api_results;
			} else {
				$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
			}
		} elseif ( 404 === intval( $api_results['http_header'] ) ) {
			$results = array(); // Response if no ranked keywords were found for the url provided.
		} else {
			$results['error'] = WTAI_GENERAL_ERROR_MESSAGE;
		}

		$results['detailed_result'] = array(
			'api_result'  => $api_results,
			'headers'     => $headers,
			'curl_params' => $curl_params,
		);

		return $results;
	}

	/**
	 * Keywords AI analysis API call.
	 *
	 * @param  array  $results   Results.
	 * @param  int    $record_id Record ID.
	 * @param  array  $fields    Fields.
	 * @param  string $record_type Record type.
	 */
	public function start_ai_keyword_analysis( $results, $record_id = 0, $fields = array(), $record_type = 'product' ) {
		if ( ! $record_id ) {
			return;
		}

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
		$language = wtai_match_language_locale( $language );

		$default_locale_array = explode( '_', $language );
		$default_lang         = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/KeywordAnalysis/Ideas',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$record_id = intval( $record_id );

		if ( 'category' === $record_type ) {
			$term        = get_term( $record_id, 'product_cat' );
			$record_name = $term->name;
			$record_url  = get_term_link( $record_id, 'product_cat' );

		} else {
			$record_name = get_the_title( $record_id );
			$record_url  = get_permalink( $record_id );
		}

		$curl_params                               = array();
		$curl_params['storeId']                    = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['recordId']                   = (string) $record_id;
		$curl_params['ipAddress']                  = wtai_get_ip_address();
		$curl_params['url']                        = $record_url;
		$curl_params['language_code']              = $default_lang;
		$curl_params['productName']                = $record_name;
		$curl_params['performCompetitiveAnalysis'] = true; // Set to true to run analysis on the background to fix issue with 90 seconds timeout.
		$curl_params['type']                       = ucfirst( $record_type );

		// For debugging ranked keywords data only.
		if ( defined( 'WTAI_TEST_PRODUCT_URL' ) ) {
			$curl_params['url'] = WTAI_TEST_PRODUCT_URL;
		}

		if ( isset( $fields['location_code'] ) ) {
			$curl_params['location_code'] = $fields['location_code'];
		}

		if ( true === $fields['refresh'] ) {
			$curl_params['refresh'] = true;
		}

		$keywords = array();
		if ( isset( $fields['manualKeywords'] ) ) {
			foreach ( $fields['manualKeywords'] as $kw ) {
				if ( '' !== trim( $kw ) ) {
					$keywords[] = strtolower( stripslashes( $kw ) );
				}
			}
		}

		$target_keywords = array();
		if ( isset( $fields['targetKeywords'] ) ) {
			foreach ( $fields['targetKeywords'] as $kw ) {
				if ( '' !== trim( $kw ) ) {
					$target_keywords[] = strtolower( stripslashes( $kw ) );
				}
			}
		}

		// Refresh: 1 credit cost.
		if ( 'selected-keywords' === $fields['refresh_type'] || 'competitor-keywords' === $fields['refresh_type'] ) {
			$curl_params['refresh']                    = false;
			$curl_params['performCompetitiveAnalysis'] = true;
		}

		// Refresh: 2 credit cost.
		if ( 'suggested-keywords' === $fields['refresh_type'] || 'your-keywords' === $fields['refresh_type'] ) {
			$curl_params['refresh']                    = true;
			$curl_params['performCompetitiveAnalysis'] = false;
		}

		if ( $target_keywords ) {
			$curl_params['targetKeywords'] = $target_keywords;
		}

		if ( $keywords ) {
			$curl_params['text'] = $keywords;
		}

		if ( ! $keywords ) {
			$curl_params['nogenerate'] = true;
		}

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

		$header_code = $api_results['http_header'];

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			$nomatch = $api_results['not_match_keywords_location'];

			if ( is_array( $api_results ) ) {
				$results['results'] = $api_results;
			} else {
				$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
			}

			if ( 40202 === intval( $api_results['status_code'] ) || 40209 === intval( $api_results['status_code'] ) || 50301 === intval( $api_results['status_code'] ) || 50401 === intval( $api_results['status_code'] ) ) {
				$results['error'] = WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE;
			}
		} elseif ( 400 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			} else {
				$results['error'] = WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE;
			}
		} elseif ( ! $api_results['result'] ) {
				$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
		} else {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			} else {
				$results['error'] = WTAI_KEYWORD_GENERAL_ERROR_MESSAGE;
			}
		}

		$results['detailed_result'] = array(
			'api_result'  => $api_results,
			'headers'     => $headers,
			'curl_params' => $curl_params,
			'header_code' => $header_code,
		);

		return $results;
	}

	/**
	 * Add or remove manual keyword API call.
	 *
	 * @param  array $results   Results.
	 * @param  int   $record_id Record ID.
	 * @param  array $fields    Fields.
	 * @param  array $record_type    Record type.
	 */
	public function process_manual_keyword( $results, $record_id = 0, $fields = array(), $record_type = 'product' ) {
		if ( ! $record_id ) {
			return;
		}

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/KeywordAnalysis/Ideas',
		);

		$headers = array(
			'Cache-Control'   => 'no-cache',
			'Host'            => $this->api_base_url,
			'Authorization'   => 'Bearer ' . $this->get_web_token(),
			'Content-Type'    => 'application/json',
			'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', $language ) ),
		);

		$curl_params               = array();
		$curl_params['storeId']    = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$curl_params['recordId']   = (string) $record_id;
		$curl_params['ipAddress']  = wtai_get_ip_address();
		$curl_params['nogenerate'] = true;
		$curl_params['type']       = ucfirst( $record_type );

		if ( isset( $fields['location_code'] ) ) {
			$curl_params['location_code'] = $fields['location_code'];
		}

		if ( isset( $fields['saveAsKeyword'] ) ) {
			$curl_params['saveAsKeyword'] = strtolower( $fields['saveAsKeyword'] );
		}

		if ( isset( $fields['removeAsKeyword'] ) ) {
			$curl_params['removeAsKeyword'] = $fields['removeAsKeyword'];
		}

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

		if ( $api_results['status'] ) {
			if ( 200 === intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );

				$nomatch = $api_results['not_match_keywords_location'];

				if ( is_array( $api_results ) ) {
					$results['results'] = $api_results;
				} else {
					$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
				}

				if ( 40202 === intval( $api_results['status_code'] ) || 40209 === intval( $api_results['status_code'] ) || 50301 === intval( $api_results['status_code'] ) || 50401 === intval( $api_results['status_code'] ) ) {
					$results['error'] = WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE;
				}
			} else {
				$results['error'] = WTAI_GENERAL_ERROR_MESSAGE;
			}
		} elseif ( ! $api_results['result'] ) {
				$results['error'] = __( 'No keyword ideas', 'writetext-ai' );
		} else {
			$api_results = json_decode( $api_results['result'], true );
			if ( isset( $api_results['error'] ) ) {
				$results['error'] = $api_results['error'];
			}
		}

		$results['detailed_result'] = array(
			'api_result'  => $api_results,
			'headers'     => $headers,
			'curl_params' => $curl_params,
		);

		return $results;
	}

	/**
	 * Get generate options text for the category.
	 *
	 * @param array  $results     Results.
	 * @param string $category_ids Category IDs.
	 * @param array  $fields      Fields.
	 */
	public function generate_category_options_text( $results, $category_ids = null, $fields = array() ) {
		if ( ! empty( $fields ) ) {
			if ( false !== strpos( $category_ids, ',' ) ) {
				$category_ids = explode( ',', $category_ids );
			} else {
				$category_ids = array( $category_ids );
			}

			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
			$language = str_replace( '_', '-', $language );

			$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

			$max_keyword_count          = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
			$max_semantic_keyword_count = isset( $global_rule_fields['maxSemanticKeywords'] ) ? $global_rule_fields['maxSemanticKeywords'] : 0;

			$curl_params              = array();
			$curl_params['Type']      = 'Category';
			$curl_params['storeId']   = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
			$curl_params['language']  = $language;
			$curl_params['ipAddress'] = $this->getClientIP();

			if ( isset( $fields['queue'] ) && 1 === intval( $fields['queue'] ) ) {
				$curl_params['queue'] = true;
			}

			$site_localized_countries = wtai_get_site_localized_countries();
			if ( $site_localized_countries ) {
				$curl_params['countries'] = $site_localized_countries;
			}

			if ( isset( $fields['includeRankedKeywords'] ) && $fields['includeRankedKeywords'] ) {
				$curl_params['includeRankedKeywords'] = true;
				$curl_params['location_code']         = wtai_get_location_code();
			}

			$fields_to_process = $fields['fields'];

			$curl_params['Texts'] = array();

			foreach ( $category_ids as $category_id ) {
				$category_id = intval( $category_id );

				$category = get_term( $category_id, 'product_cat' );

				$curl_field_texts = array();
				$fields_value     = wtai_get_category_values( $category_id );

				$generation_limit_vars                = wtai_get_generation_limit_vars();
				$max_reference_input_character_length = intval( $generation_limit_vars['maxReferenceInputCharacterLength'] );

				foreach ( $fields_value as $meta_key => $meta_value ) {
					if ( ! $meta_key ) {
						continue;
					}

					if ( $fields_to_process ) {
						$field_found = false;
						foreach ( $fields_to_process as $field_to_process ) {
							if ( $meta_key === $field_to_process ) {
								$field_found = true;
								break;
							}
						}

						if ( ! $field_found ) {
							continue;
						}
					}

					if ( false === $meta_value ) {
						$meta_value = '';
					}

					$meta_field_value = array(
						'field'        => strtolower( apply_filters( 'wtai_field_conversion', $meta_key, 'category' ) ),
						'currentValue' => wtai_clean_up_html_string( $meta_value, true ),
					);

					if ( isset( $fields[ $meta_key . '_length_min' ] ) && $fields[ $meta_key . '_length_min' ] ) {
						$meta_field_value['minWords'] = $fields[ $meta_key . '_length_min' ];
					}

					if ( isset( $fields[ $meta_key . '_length_max' ] ) && $fields[ $meta_key . '_length_max' ] ) {
						$meta_field_value['maxWords'] = $fields[ $meta_key . '_length_max' ];
					}

					if ( isset( $fields['rewriteText'] ) && 1 === intval( $fields['rewriteText'] ) ) {
						$rewrite_text = $meta_value;
						if ( $rewrite_text ) {
							$rewrite_text = wtai_clean_up_html_string( $rewrite_text, true );

							if ( $max_reference_input_character_length > 0 ) {
								$rewrite_text = substr( $rewrite_text, 0, $max_reference_input_character_length );
							}

							$meta_field_value['rewriteText'] = $rewrite_text;
						}
					}

					// Keyword analysis views count.
					if ( isset( $fields['keywordAnalysisViews'] ) ) {
						$meta_field_value['keywordAnalysisViews'] = $fields['keywordAnalysisViews'];
					}

					// Simulate error.
					$simulate_error = false;
					if ( $simulate_error ) {
						if ( 'page_title' === $meta_key ||
							'page_description' === $meta_key ||
							'product_description' === $meta_key ||
							'product_excerpt' === $meta_key ||
							'open_graph' === $meta_key ||
							'category_description' === $meta_key
						) {
							$meta_field_value['developmentThrowErrorInSeconds'] = 1;
						}
					}

					$curl_field_texts[] = $meta_field_value;
				}

				$keyword_values = array();
				$keywords       = ( isset( $fields['keywords'] ) ) ? $fields['keywords'] : array();
				if ( ! $keywords ) {
					$keywords_from_api = apply_filters( 'wtai_keyword_values', array(), $category_id, 'input', false, 'category' );

					// Suggested audience.
					$keywords = array();
					foreach ( $keywords_from_api as $keyword_input_data_iindex => $keyword_input_data ) {
						if ( $keyword_input_data_iindex > 0 ) {
							$keywords[] = stripslashes( $keyword_input_data['name'] );
						}
					}
				}

				if ( is_array( $keywords ) ) {
					$keyword_count = 1;
					foreach ( $keywords as $keyword ) {
						$keyword_values[] = stripslashes( $keyword );
						if ( isset( $global_rule_fields['maxKeywords'] )
							&& $global_rule_fields['maxKeywords'] === $keyword_count ) {
							break;
						}
						++$keyword_count;
					}
				}

				// Reset wtai_review meta.
				delete_term_meta( $category_id, 'wtai_review' );

				$category_url = get_term_link( $category_id );

				$text_results = array(
					'RecordId'        => (string) $category_id,
					'sku'             => '',
					'url'             => $category_url,
					'Browsertime'     => $fields['browsertime'],
					'name'            => ( isset( $global_rule_fields['maxNameLength'] ) ) ? substr( $category->name, 0, $global_rule_fields['maxNameLength'] ) : $category->name,
					'keywords'        => array_filter( $keyword_values ),
					'Attributes'      => array(),
					'options'         => isset( $fields['options'] ) && $fields['options'] ? $fields['options'] : WTAI_MAX_CHOICE,
					'fields'          => $curl_field_texts,
					'style'           => isset( $fields['styles'] ) ? $fields['styles'] : apply_filters( 'wtai_global_settings', 'wtai_installation_styles' ),
					'autoselectFirst' => isset( $fields['autoselectFirst'] ) ? $fields['autoselectFirst'] : false,
				);

				$otherdetails = '';
				if ( isset( $fields['otherproductdetails'] ) && $fields['otherproductdetails'] ) {
					$otherdetails = $fields['otherproductdetails'];
				} else {
					$other_category_details = wtai_get_category_other_details( $category_id );

					if ( $other_category_details && 1 === intval( $other_category_details['enabled'] ) && '' !== $other_category_details['value'] ) {
						$otherdetails = $other_category_details['value'];
					}
				}

				if ( $otherdetails ) {
					$otherdetails = wp_strip_all_tags( $otherdetails );

					$text_results['otherDetails'] = ( $otherdetails && isset( $global_rule_fields['maxOtherDetailsLength'] ) ) ? substr( $otherdetails, 0, $global_rule_fields['maxOtherDetailsLength'] ) : $otherdetails;
				}

				if ( isset( $fields['formalLanguage'] ) && $fields['formalLanguage'] ) {
					$text_results['formalLanguage'] = $fields['formalLanguage'];
				}

				$tones = ( isset( $fields['tones'] ) ) ? $fields['tones'] : apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );

				// Temporary only one for now.
				if ( is_array( $tones ) ) {
					if ( 1 === count( $tones ) ) {
						$text_results['tone'] = reset( $tones );
					} else {
						$text_results['tones'] = $tones;
					}
				}

				$audiences = ( isset( $fields['audiences'] ) ) ? $fields['audiences'] : apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );
				if ( ! $audiences ) {
					$audiences = array();
				}

				if ( is_array( $audiences ) ) {
					$audiences = array_filter( $audiences );

					if ( 1 === count( $audiences ) ) {
						$text_results['audience'] = reset( $audiences );
					} elseif ( count( $audiences ) > 1 ) {
						$text_results['audiences'] = $audiences;
					}
				}

				$custom_audience = ( isset( $fields['customAudience'] ) ) ? $fields['customAudience'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_audience' );
				if ( $custom_audience ) {
					$text_results['customAudience'] = $custom_audience;
				}

				$semantic_keywords = ( isset( $fields['semanticKeywords'] ) ) ? $fields['semanticKeywords'] : apply_filters( 'wtai_global_settings', 'wtai_installation_semantic_keywords' );
				if ( $semantic_keywords ) {
					$text_results['semanticKeywords'] = array_filter( $semantic_keywords );
				} else {
					$keywords_data         = apply_filters( 'wtai_keyword_values', array(), $category_id, 'input', false, 'category' );
					$semantics_selected_pt = array();
					foreach ( $keywords_data as $k_data ) {
						foreach ( $k_data['semantic'] as $sa_data ) {
							if ( 1 === $sa_data['active'] ) {
								$semantics_selected_pt[] = $sa_data['name'];
							}
						}
					}

					if ( $semantics_selected_pt ) {
						$text_results['semanticKeywords'] = $semantics_selected_pt;
					}
				}

				$custom_tone = ( isset( $fields['customTone'] ) ) ? $fields['customTone'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_tone' );
				if ( $custom_tone ) {
					$text_results['customTone'] = stripslashes( $custom_tone );
				}

				$custom_style = ( isset( $fields['customStyle'] ) ) ? $fields['customStyle'] : apply_filters( 'wtai_global_settings', 'wtai_installation_custom_style' );
				if ( $custom_style ) {
					$text_results['customStyle'] = stripslashes( $custom_style );
				}

				// Remove if empty keywords.
				if ( ! $text_results['keywords'] ) {
					unset( $text_results['keywords'] );
				} else {
					$keywords_for_generation_array = array();
					$keywords_for_generation_ctr   = 0;
					foreach ( $text_results['keywords'] as $keyword_value ) {
						if ( $keywords_for_generation_ctr < $max_keyword_count ) {
							$keywords_for_generation_array[] = $keyword_value;
						}

						++$keywords_for_generation_ctr;
					}

					$text_results['keywords'] = $keywords_for_generation_array;
				}

				// Remove if empty semantic keywords.
				if ( ! $text_results['semanticKeywords'] ) {
					unset( $text_results['semanticKeywords'] );
				} else {
					$semantic_keywords_for_generation_array = array();
					$semantic_keywords_for_generation_ctr   = 0;
					foreach ( $text_results['semanticKeywords'] as $keyword_value ) {
						if ( $semantic_keywords_for_generation_ctr < $max_semantic_keyword_count ) {
							$semantic_keywords_for_generation_array[] = $keyword_value;
						}

						++$semantic_keywords_for_generation_ctr;
					}

					$text_results['semanticKeywords'] = $semantic_keywords_for_generation_array;
				}

				// Remove if empty attributes.
				if ( ! $text_results['Attributes'] ) {
					unset( $text_results['Attributes'] );
				}

				// Handle featured image prompt.
				if ( isset( $fields['includeFeaturedImage'] ) && $fields['includeFeaturedImage'] ) {
					// Get main featured image attachment id.
					$featured_image_id = get_term_meta( $category_id, 'thumbnail_id', true );
					if ( $featured_image_id ) {
						$image_api_data = wtai_get_image_for_api_generation( $category_id, $featured_image_id, $fields['browsertime'], false );

						if ( $image_api_data && isset( $image_api_data['url'] ) ) {
							$text_results['images'] = array( strval( $featured_image_id ) );
						}
					}
				}

				// Handling for representative products here.
				$representative_product_data = array();
				if ( $fields['representative_product_ids'] ) {
					foreach ( $fields['representative_product_ids'] as $rep_prod_id ) {
						if ( $rep_prod_id ) {
							$rep_prod_featured_image_id = get_post_thumbnail_id( $rep_prod_id );
							$rep_product_title          = ( isset( $global_rule_fields['maxNameLength'] ) ) ? substr( get_the_title( $rep_prod_id ), 0, $global_rule_fields['maxNameLength'] ) : get_the_title( $rep_prod_id );
							$rep_product_meta_value     = wtai_get_meta_values( $rep_prod_id, array( 'product_description' ) );
							$rep_product_desc           = $rep_product_meta_value['product_description'];

							$rep_product_data = array(
								'id'          => $rep_prod_id,
								'name'        => $rep_product_title,
								'description' => $rep_product_desc,
							);

							if ( $rep_prod_featured_image_id ) {
								$rep_image_api_data = wtai_get_image_for_api_generation( $rep_prod_id, $rep_prod_featured_image_id, $fields['browsertime'], false );

								if ( $rep_image_api_data && isset( $rep_image_api_data['url'] ) ) {
									$rep_product_data['image'] = (string) $rep_prod_featured_image_id;
								}
							}

							$representative_product_data[] = $rep_product_data;
						}
					}
				}

				if ( $representative_product_data ) {
					$text_results['products'] = $representative_product_data;
				}

				$curl_params['Texts'][] = $text_results;
			}

			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/v2',
			);
			$token    = isset( $fields['token'] ) && $fields['token'] ? $fields['token'] : $this->get_web_token();
			$headers  = array(
				'Cache-Control'   => 'no-cache',
				'Host'            => $this->api_base_url,
				'Authorization'   => 'Bearer ' . $token,
				'Content-Type'    => 'application/json',
				'Accept-Language' => str_replace( '_', '-', str_replace( '_formal', '', wtai_get_site_language() ) ),
			);

			$debug = false;
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( $debug || isset( $_GET['wtai_generated_text_options'] ) ) {
				print '<pre>';
				print_r( $curl_params ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			}

			$api_results     = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers );
			$raw_api_results = $api_results;

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( $debug || isset( $_GET['wtai_generated_text_options'] ) ) {
				$api_results = json_decode( $api_results['result'], true );
				print_r( $api_results ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
				print '</pre>';
			}

			if ( $api_results['status'] ) {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['requestId'] ) && ! is_null( $api_results['requestId'] ) ) {
					$results['requestId'] = $api_results['requestId'];
				} else {
					$api_results = isset( $api_results['value'] ) ? $api_results['value'] : $api_results;
					if ( isset( $api_results['texts'] ) && ! empty( $api_results['texts'] ) ) {
						foreach ( $api_results['texts'] as $textvalue ) {
							$field = apply_filters( 'wtai_field_conversion', $textvalue['field'], 'category' );
							if ( ! is_array( $textvalue['outputs'] ) ) {
								continue;
							}
							$results[ $textvalue['recordId'] ][ $field ] = array(
								'textId' => $textvalue['id'],
								'output' => ( 1 === count( $textvalue['outputs'] ) ) ? $textvalue['outputs'][0] : $textvalue['outputs'],
							);
						}
					}
				}

				$results['detailed_result'] = array(
					'api_result'  => $raw_api_results,
					'headers'     => $headers,
					'curl_params' => $curl_params,
					'http_header' => $http_header,
				);
			} elseif ( ! $api_results['result'] ) {
				$results = 'Error Header Code : ' . $api_results['http_header'];
			} elseif ( 200 !== intval( $api_results['http_header'] ) ) {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['Error'] ) ) {
					$results = $api_results['Error'];
				}
				if ( isset( $api_results['error'] ) ) {
					$results = $api_results['error'];
				}
			} else {
				$api_results = json_decode( $api_results['result'], true );
				if ( isset( $api_results['Error'] ) ) {
					$results = $api_results['Error'];
				}
				if ( isset( $api_results['error'] ) ) {
					$results = $api_results['error'];
				}
			}
		}

		return $results;
	}

	/**
	 * Get category generated text from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param string $category_ids   Category IDs.
	 * @param array  $fields Fields to get from the API.
	 */
	public function get_generate_category_text( $results, $category_ids = null, $fields = array() ) {
		$locale_lang = apply_filters( 'wtai_language_code', wtai_get_site_language() );

		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
		$language = str_replace( '_', '-', $language );

		$curl_params        = array();
		$params['Type']     = 'Category';
		$params['storeID']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;
		$params['recordId'] = $category_ids;

		if ( isset( $fields['historyCount'] ) && is_numeric( $fields['historyCount'] ) ) {
			$params['historyCount'] = $fields['historyCount'];
		}

		if ( isset( $fields['includeUpdateHistory'] ) && $fields['includeUpdateHistory'] ) {
			$params['includeUpdateHistory'] = 'true';
		}

		if ( isset( $fields['fields'] ) && ! empty( $fields['fields'] ) ) {
			$params['field'] = array_map(
				function ( $meta_key ) {
					return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'category' );
				},
				$fields['fields']
			);
			$params['field'] = implode( ',', $params['field'] );
		}

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Generate?' . http_build_query( $params ),
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			$reviews = array();

			if ( isset( $api_results['records'] ) && ! empty( $api_results['records'] ) ) {
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['wtai_product_api_debug'] ) ) {
					print '<pre>';
					print_r( $api_results['records'] ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
					print '</pre>';
				}
				foreach ( $api_results['records'] as $stores ) {
					foreach ( $stores['stores'] as $store ) {

						$field                                    = apply_filters( 'wtai_field_conversion', $store['field'], 'category' );
						$store_text                               = ( isset( $fields['single_value'] ) ) ? array( $store['texts'][0] ) : $store['texts'];
						$results[ $stores['recordId'] ][ $field ] = $store_text;

						$reviews = array();
						if ( isset( $fields['single_value'] ) && isset( $store['reviews'][0] ) ) {
							$reviews = array( $store['reviews'][0] );

						} elseif ( $store['reviews'] ) {
							$reviews = $store['reviews'];
						}

						$results[ $stores['recordId'] ][ $field ]['reviews'] = $reviews;
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Save or transfer generated text to the API.
	 *
	 * @param array $results    Results from the API.
	 * @param array $fields     Fields from the category.
	 */
	public function add_generate_category_text( $results = array(), $fields = array() ) {
		if ( ! empty( $results ) ) {
			$settings = array(
				'remote_url' => 'https://' . $this->api_base_url . '/text/Generate/text',
			);
			$headers  = array(
				'Cache-Control' => 'no-cache',
				'Host'          => $this->api_base_url,
				'Authorization' => 'Bearer ' . $this->get_web_token(),
				'Content-Type'  => 'application/json',
			);

			$publish  = isset( $fields['publish'] ) ? $fields['publish'] : false;
			$reviewed = isset( $fields['reviewed'] ) ? $fields['reviewed'] : false;

			if ( $publish ) {
				$reviewed = true;
			}

			if ( isset( $fields['reviewed'] ) ) {
				unset( $fields['reviewed'] );
			}

			$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
			$language = str_replace( '_', '-', $language );

			foreach ( $results as $category_id => $result ) {
				$curl_params                = array();
				$curl_params['Type']        = 'Category';
				$curl_params['storeId']     = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
				$curl_params['language']    = $language;
				$curl_params['recordId']    = (string) $category_id;
				$curl_params['browsertime'] = $fields['browsertime'];
				$curl_fields                = array();
				foreach ( $result as $field => $result_value ) {
					$values = is_array( $result_value['output'] ) ? reset( $result_value['output'] ) : $result_value['output'];

					$field_publish = $publish;
					if ( isset( $result_value['publish'] ) ) {
						$field_publish = $result_value['publish'];
					}

					$curl_fields[] = array(
						'textId'   => $result_value['textId'],
						'field'    => apply_filters( 'wtai_field_conversion', $field, 'category' ),
						'value'    => $values,
						'publish'  => $field_publish,
						'platform' => WTAI_GENERATE_TEXT_PLATFORM,
						'reviewed' => $reviewed,
					);
				}

				$curl_params['fields'] = $curl_fields;

				$meta_post_date = ( $publish ) ? 'transfer' : 'generate';

				if ( isset( $fields['reviewed'] ) && $fields['reviewed'] ) {
					$meta_post_date = 'reviewed';
				}

				// Added browser.
				$time = strtotime( current_time( 'mysql' ) );
				update_term_meta( $category_id, 'wtai_' . $meta_post_date . '_date', $time );

				$results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );
			}
		}

		return $results;
	}

	/**
	 * Record product reviewed status.
	 *
	 * @param array $api_results API results.
	 * @param int   $category_id Category ID.
	 * @param bool  $reviewed Reviewed status.
	 * @param int   $browsertime Browser time.
	 *
	 * @return array $api_results Filtered api results.
	 */
	public function record_category_reviewed_api( $api_results = array(), $category_id = 0, $reviewed = true, $browsertime = 0 ) {
		// Pass to api.
		$fields = apply_filters( 'wtai_category_fields', array() );
		$fields = array_keys( $fields );

		$api_results = array();
		$api_fields  = array(
			'browsertime' => $browsertime,
			'publish'     => false,
			'reviewed'    => $reviewed,
		);

		$api_result_values = apply_filters(
			'wtai_generate_category_text',
			array(),
			$category_id,
			array(
				'fields'               => $fields,
				'includeUpdateHistory' => true,
			)
		);

		foreach ( $fields as $field_key ) {
			$text_id = $api_result_values[ $category_id ][ $field_key ][0]['id'];

			if ( $text_id ) {
				$field_published = false;
				$field_reviewed  = 0;
				if ( $reviewed ) {
					if ( isset( $api_result_values[ $category_id ][ $field_key ][0]['history'][0] ) ) {
						$field_published = $api_result_values[ $category_id ][ $field_key ][0]['history'][0]['publish'];

						if ( '1' === $field_published ) {
							$field_published = true;
						} else {
							$field_published = false;
						}

						$field_reviewed = $api_results[ $category_id ][ $field ][0]['history'][0]['reviewed'];
					}

					if ( true === $field_published ) {
						$api_fields['publish'] = true;
					}
				}

				$proceed_with_update = true;
				if ( $reviewed && true === $field_published ) {
					$proceed_with_update = false;
				}

				if ( $proceed_with_update ) {
					$api_results[ $category_id ][ $field_key ] = array(
						'textId'  => esc_attr( $api_result_values[ $category_id ][ $field_key ][0]['id'] ),
						'output'  => $api_result_values[ $category_id ][ $field_key ][0]['value'],
						'publish' => $field_published,
					);
				}
			}
		}

		if ( $api_results ) {
			$api_results = apply_filters( 'wtai_stored_generate_category_text', $api_results, $api_fields );
		}

		return $api_results;
	}

	/**
	 * Get generate category status from the API.
	 *
	 * @param array  $results    Results from the API.
	 * @param array  $fields     Fields from the product.
	 * @param string $continuation_token Continuation token from the API.
	 */
	public function get_generate_category_status( $results, $fields = array(), $continuation_token = '' ) {
		$language = apply_filters( 'wtai_language_code', wtai_get_site_language() );
		$language = str_replace( '_', '-', $language );

		$params             = $fields;
		$params['type']     = 'Category';
		$params['storeId']  = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
		$params['language'] = $language;

		if ( isset( $params['status'] ) ) {
			if ( ! is_array( $params['status'] ) ) {
				$params['status'] = rawurlencode( apply_filters( 'wtai_field_conversion', $params['status'], 'category' ) );
			} else {
				$wtai_statuses = array();
				foreach ( $params['status'] as $status_value ) {
					$wtai_statuses[] = rawurlencode( apply_filters( 'wtai_field_conversion', $status_value, 'category' ) );
				}

				if ( $wtai_statuses ) {
					$params['status'] = $wtai_statuses;
				}
			}
		}

		$add_params = '';
		if ( isset( $params['wtai_fields'] ) ) {
			if ( is_array( $params['wtai_fields'] ) ) {
				$wtai_fields = array();
				foreach ( $params['wtai_fields'] as $value ) {
					$wtai_fields[] = 'fields=' . rawurlencode( apply_filters( 'wtai_field_conversion', $value, 'category' ) );
				}
				$add_params .= implode( '&', $wtai_fields );
			}

			unset( $params['wtai_fields'] );
		}

		if ( isset( $params['startDate'] ) ) {
			$params['startDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['startDate'] ) );
		}

		if ( isset( $params['endDate'] ) ) {
			$params['endDate'] = gmdate( 'Y-m-d\TH:i:s.s\Z', strtotime( $params['endDate'] ) );
		}

		if ( $add_params ) {
			$add_params = '&' . $add_params;
		}

		$url = 'https://' . $this->api_base_url . '/text/Generate/Status?' . http_build_query( $params ) . $add_params;

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_grid_filter_debug'] ) ) {
			echo wp_kses( $url, 'post' );
		}

		$settings = array(
			'remote_url' => $url,
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );

		$continuation_token = '';
		if ( 200 === intval( $api_results['http_header'] ) ) {
			$api_results = json_decode( $api_results['result'], true );

			if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
				$results = array();
				foreach ( $api_results['records'] as $records ) {
					if ( ! in_array( $records['recordId'], $results, true ) ) {
						$results[] = $records['recordId'];
					}
				}

				$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
				if ( $continuation_token ) {
					$orig_params = $params;
					while ( $continuation_token ) {
						$orig_params['continuationToken'] = $continuation_token;

						$url = 'https://' . $this->api_base_url . '/text/Generate/Status?' . http_build_query( $orig_params ) . $add_params;

						$settings = array(
							'remote_url' => $url,
						);

						$headers = array(
							'Cache-Control' => 'no-cache',
							'Authorization' => 'Bearer ' . $this->get_web_token(),
							'Content-Type'  => 'application/json',
						);

						$api_results = $this->get_data_via_api( '', $settings, $headers, 'GET' );
						if ( 200 === intval( $api_results['http_header'] ) ) {
							$api_results = json_decode( $api_results['result'], true );

							if ( isset( $api_results['records'] ) && ( is_array( $api_results['records'] ) && count( $api_results['records'] ) > 0 ) ) {
								foreach ( $api_results['records'] as $records ) {
									if ( ! in_array( $records['recordId'], $results, true ) ) {
										$results[] = $records['recordId'];
									}
								}

								$continuation_token = isset( $api_results['continuationToken'] ) ? $api_results['continuationToken'] : null;
							} else {
								$continuation_token = '';
								break;
							}
						} else {
							$continuation_token = '';
							break;
						}
					}
				}
			}
		}

		return $results;
	}

	/**
	 * Check from the API if we should display the Free Premium badge or not.
	 *
	 * @param bool $display_freemium_badge Whether to display the Free Premium badge or not.
	 */
	public function check_freemium_badge_display( $display_freemium_badge = false ) {
		$display_freemium_badge = false;
		$free_premium_credits   = 0;

		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/web/account/all?GetAccount=true',
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$curl_params = array();

		$api_results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'GET' );

		if ( 200 === intval( $api_results['http_header'] ) ) {
			$results = json_decode( $api_results['result'], true );

			if ( isset( $results['account'] ) && isset( $results['account']['company'] ) && isset( $results['account']['company']['eligibleForFreePremiumCredits'] ) && ( 'true' === $results['account']['company']['eligibleForFreePremiumCredits'] || 1 === intval( $results['account']['company']['eligibleForFreePremiumCredits'] ) ) ) {
				$display_freemium_badge = true;
			}

			if ( isset( $results['account'] ) && isset( $results['account']['company'] ) && isset( $results['account']['company']['eligibleForFreePremiumCredits'] ) && ( 'true' === $results['account']['company']['freePremiumCredits'] || intval( $results['account']['company']['freePremiumCredits'] ) > 0 ) ) {
				$free_premium_credits = $results['account']['company']['freePremiumCredits'];
			}
		}

		$freemium_data = array(
			'display_freemium_badge' => $display_freemium_badge,
			'free_premium_credits'   => $free_premium_credits,
		);

		return $freemium_data;
	}

	/**
	 * Record product reviewed status.
	 *
	 * @param array $results Results.
	 *
	 * @return array $results Filtered results.
	 */
	public function record_freemium_seen_api( $results = array() ) {
		$settings = array(
			'remote_url' => 'https://' . $this->api_base_url . '/text/Credit/setFreePremiumCreditsToFalse',
		);

		$headers = array(
			'Cache-Control' => 'no-cache',
			'Host'          => $this->api_base_url,
			'Authorization' => 'Bearer ' . $this->get_web_token(),
			'Content-Type'  => 'application/json',
		);

		$curl_params = array();

		$results = $this->get_data_via_api( wp_json_encode( $curl_params ), $settings, $headers, 'POST' );

		return $results;
	}
}

new WTAI_API_Services();