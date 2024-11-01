<?php
/**
 * Plugin Name: WriteText.ai
 * Plugin URI: https://writetext.ai/woocommerce
 * Description: Let AI automatically generate product descriptions and other content from your product data.
 * Version: 1.40.4
 * Author:  1902 Software
 * Author URI: https://writetext.ai/
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
 * Text Domain: writetext-ai
 * Domain Path: /languages/
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	// Plugin ENV.
	if ( ! defined( 'WTAI_ENV' ) ) {
		define( 'WTAI_ENV', 'production' );
	}

	// ENABLE LOGGING.
	if ( ! defined( 'WTAI_API_LOGGING' ) ) {
		define( 'WTAI_API_LOGGING', false );
	}

	// Define WTAI_PLUGIN_FILE.
	if ( ! defined( 'WTAI_PLUGIN_FILE' ) ) {
		define( 'WTAI_PLUGIN_FILE', __FILE__ );
	}

	if ( ! defined( 'WTAI_ABSPATH' ) ) {
		define( 'WTAI_ABSPATH', dirname( WTAI_PLUGIN_FILE ) . '/' );
	}

	if ( ! defined( 'WTAI_PLUGIN_BASENAME' ) ) {
		define( 'WTAI_PLUGIN_BASENAME', plugin_basename( dirname( WTAI_PLUGIN_FILE ) . '/' ) );
	}

	if ( ! defined( 'WTAI_DIR_URL' ) ) {
		define( 'WTAI_DIR_URL', untrailingslashit( plugins_url( '/', WTAI_PLUGIN_FILE ) ) . '/' );
	}

	if ( ! defined( 'WTAI_FOLDER_NAME' ) ) {
		define( 'WTAI_FOLDER_NAME', 'writetext-ai' );
	}

	if ( ! defined( 'WTAI_VERSION' ) ) {
		define( 'WTAI_VERSION', '1.40.4' );
	}

	if ( ! defined( 'WTAI_POST_TYPE' ) ) {
		define( 'WTAI_POST_TYPE', 'product' );
	}

	if ( ! defined( 'WTAI_TRANSLATIONS_ENABLED' ) ) {
		define( 'WTAI_TRANSLATIONS_ENABLED', false );
	}

	if ( ! defined( 'WTAI_ALLOWED_ALL_LANGUAGES' ) ) {
		define( 'WTAI_ALLOWED_ALL_LANGUAGES', true );
	}

	if ( ! defined( 'WTAI_TRANSLATION_ONGOING' ) ) {
		define( 'WTAI_TRANSLATION_ONGOING', true );
	}

	if ( ! defined( 'WTAI_API_HOST' ) ) {
		if ( WTAI_ENV === 'production' ) {
			define( 'WTAI_API_HOST', 'api.writetext.ai' );
		} else {
			define( 'WTAI_API_HOST', 'writetextai-api-dev.azurewebsites.net' );
		}
	}

	if ( ! defined( 'WTAI_PREMIUM_SUBSCRIPTION_LINK' ) ) {
		if ( WTAI_ENV === 'production' ) {
			define( 'WTAI_PREMIUM_SUBSCRIPTION_LINK', 'https://platform.writetext.ai/premium' );
		} else {
			define( 'WTAI_PREMIUM_SUBSCRIPTION_LINK', 'https://writetextai-dev.azurewebsites.net/premium' );
		}
	}

	if ( ! defined( 'WTAI_AUTH_HOST' ) ) {
		if ( WTAI_ENV === 'production' ) {
			define( 'WTAI_AUTH_HOST', 'login.writetext.ai' );
		} else {
			define( 'WTAI_AUTH_HOST', 'writetextai-auth-dev.azurewebsites.net' );
		}
	}

	if ( ! defined( 'WTAI_GENERATE_TEXT_PLATFORM' ) ) {
		define( 'WTAI_GENERATE_TEXT_PLATFORM', 'WordPress' );
	}

	if ( ! defined( 'WTAI_MAX_CHOICE' ) ) {
		define( 'WTAI_MAX_CHOICE', 1 );
	}

	if ( ! defined( 'WTAI_MAX_KEYWORD' ) ) {
		define( 'WTAI_MAX_KEYWORD', 5 );
	}

	if ( ! defined( 'WTAI_MAX_MANUAL_KEYWORD' ) ) {
		define( 'WTAI_MAX_MANUAL_KEYWORD', 15 );
	}

	if ( ! defined( 'WTAI_PAGE_TITLE_TEXT_LIMIT' ) ) {
		define( 'WTAI_PAGE_TITLE_TEXT_LIMIT', 60 );
	}

	if ( ! defined( 'WTAI_MAX_PAGE_DESCRIPTION_LIMIT' ) ) {
		define( 'WTAI_MAX_PAGE_DESCRIPTION_LIMIT', 255 );
	}

	if ( ! defined( 'WTAI_MAX_OPEN_GRAPH_LIMIT' ) ) {
		define( 'WTAI_MAX_OPEN_GRAPH_LIMIT', 180 );
	}

	if ( ! defined( 'WTAI_MAX_HISTORY_PAGESIZE' ) ) {
		define( 'WTAI_MAX_HISTORY_PAGESIZE', 150 );
	}

	if ( ! defined( 'WTAI_MAX_IMAGE_ALT_TEXT_LIMIT' ) ) {
		define( 'WTAI_MAX_IMAGE_ALT_TEXT_LIMIT', 125 );
	}

	if ( ! defined( 'WTAI_MAX_REPRESENTATIVE_PRODUCT' ) ) {
		define( 'WTAI_MAX_REPRESENTATIVE_PRODUCT', 5 );
	}

	if ( ! defined( 'WTAI_REPRESENTATIVE_PRODUCT_LIMIT_PER_PAGE' ) ) {
		define( 'WTAI_REPRESENTATIVE_PRODUCT_LIMIT_PER_PAGE', 10 );
	}

	if ( ! defined( 'WTAI_FREEMIUM_CREDITS' ) ) {
		define( 'WTAI_FREEMIUM_CREDITS', 110 );
	}

	if ( ! defined( 'WTAI_INVALID_NONCE_MESSAGE' ) ) {
		define( 'WTAI_INVALID_NONCE_MESSAGE', __( 'Security nonce verification failed. Reload this page to try again.', 'writetext-ai' ) );
	}

	if ( ! defined( 'WTAI_GENERAL_ERROR_MESSAGE' ) ) {
		define( 'WTAI_GENERAL_ERROR_MESSAGE', __( 'A system error has occurred. Please try again. If the issue persists, please contact our support team at support@writetext.ai.', 'writetext-ai' ) );
	}

	if ( ! defined( 'WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE' ) ) {
		define( 'WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE', __( 'The server is currently handling a lot of requests. Please retry after a few minutes.', 'writetext-ai' ) );
	}

	if ( ! defined( 'WTAI_KEYWORD_GENERAL_ERROR_MESSAGE' ) ) {
		define( 'WTAI_KEYWORD_GENERAL_ERROR_MESSAGE', __( 'A system error has occurred. Please click the "Start AI-powered analysis" button again. If the issue persists, please contact our support team at support@writetext.ai.', 'writetext-ai' ) );
	}

	if ( ! defined( 'WTAI_KEYWORDS_MAX_ITEMS_PER_PAGE' ) ) {
		define( 'WTAI_KEYWORDS_MAX_ITEMS_PER_PAGE', 5 );
	}

	if ( ! defined( 'WTAI_KEYWORDS_MAX_ITEM_PER_LOAD' ) ) {
		define( 'WTAI_KEYWORDS_MAX_ITEM_PER_LOAD', 10 );
	}

	/**
	 * Redirects to the plugin page after activation.
	 *
	 * @param string $plugin Plugin.
	 */
	function wtai_activation_redirect( $plugin ) {
		$current_wp_version = wtai_get_wp_version();

		// Redirect to the plugin setup page only if WP version is less than 6.5.2.
		if ( version_compare( $current_wp_version, '6.5.2', '<' ) && plugin_basename( __FILE__ ) === $plugin ) {
			exit( esc_url( wp_safe_redirect( admin_url( '?page=write-text-ai' ) ) ) );
		}
	}
	add_action( 'activated_plugin', 'wtai_activation_redirect' );

	/**
	 * Create product schema and checking if current site language is supported.
	 */
	function wtai_plugin_activation_hook() {
		// Check if language is supported upon plugin installation.
		if ( false === wtai_is_allowed_beta_language() ) {

			if ( WTAI_TRANSLATIONS_ENABLED ) {
				/* translators: %s: settings admin url */
				$message = sprintf( "Your default language is currently unsupported. At this stage, we only support English, Danish, German, Norwegian, Swedish, French, Spanish, Portuguese, Dutch, Catalan, and Italian. <a href='%s' target='_blank' >Please switch to a supported language in your settings</a> if you want to use WriteText.ai.", admin_url( 'options-general.php' ) ); // No need to translate this text cause this will only appear on NON EN language that is NOT supported and no translation is available.
			} else {
				/* translators: %s: settings admin url */
				$message = sprintf( "Your default language is currently unsupported. At this stage, we only support English. <a href='%s' target='_blank' >Please switch to a supported language in your settings</a> if you want to use WriteText.ai.", admin_url( 'options-general.php' ) ); // No need to translate this text cause this will only appear on NON EN language that is NOT supported and no translation is available.
			}

			echo '<style>
                body{    
                    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                    font-size: 13px;
                    line-height: 1.5;
                    color: #3c434a;
                    margin: 0
                }
                </style>';
			echo '<p style="margin-bottom: 0;" >' . wp_kses( $message, 'post' ) . '</p>';

			die();
		}

		$current_wp_version        = wtai_get_wp_version();
		$minimum_wp_version        = '6.0';
		$wp_version_compare_result = version_compare( $current_wp_version, $minimum_wp_version );
		if ( -1 === $wp_version_compare_result ) {
			$message = __( 'You need WordPress version 6.0 or higher to install and use Writetext.ai.', 'writetext-ai' );

			echo '<style>
                body{    
                    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
                    font-size: 13px;
                    line-height: 1.5;
                    color: #3c434a;
                    margin: 0
                }
                </style>';
			echo '<p style="margin-bottom: 0;" >' . wp_kses( $message, 'post' ) . '</p>';

			die();
		}

		// Record activation statistics.
		do_action( 'wtai_record_installation_statistics', 'Activate', 0 );
	}
	register_activation_hook( __FILE__, 'wtai_plugin_activation_hook' );

	/**
	 * Generate placeholders for prepared statements.
	 *
	 * @param array $values Values array.
	 */
	function wtai_generate_wpdb_prepare_placeholders_from_array( $values ) {
		$placeholders = array_map(
			function ( $item ) {
				return is_string( $item ) ? '%s' : ( is_float( $item ) ? '%f' : ( is_int( $item ) ? '%d' : '' ) );
			},
			$values
		);

		return '(' . join( ',', $placeholders ) . ')';
	}

	/**
	 * Reset all settings, user defined preferences and product preferences.
	 */
	function wtai_reset_all_settings() {
		$old_token = get_option( 'wtai_api_token' );

		update_option( 'wtai_userrole', '' );
		update_option( 'wtai_userrole_multisite', '' );
		update_option( 'wtai_installation_product_attr', array() );
		update_option( 'wtai_installation_tone', '' );
		update_option( 'wtai_installation_tones', array() );
		update_option( 'wtai_installation_etag_tones', '' );
		update_option( 'wtai_installation_styles', array() );
		update_option( 'wtai_installation_etag_styles', '' );
		update_option( 'wtai_installation_style_and_tone_reset', '' );
		update_option( 'wtai_installation_options', '' );
		update_option( 'wtai_installation_source', '' );
		update_option( 'wtai_installation_etag_global_rules', '' );
		update_option( 'wtai_installation_global_rules', '' );
		update_option( 'wtai_installation_step', 1 );
		update_option( 'wtai_api_token', '' );
		update_option( 'wtai_api_token_time', '' );
		update_option( 'wtai_installation_source_updated', '' );
		update_option( 'wtai_installation_step_1_loaded', '' );

		update_option( 'wtai_installation_product_description_min', '' );
		update_option( 'wtai_installation_product_description_max', '' );
		update_option( 'wtai_installation_product_excerpt_min', '' );
		update_option( 'wtai_installation_product_excerpt_max', '' );
		update_option( 'wtai_installation_category_description_min', '' );
		update_option( 'wtai_installation_category_description_max', '' );
		update_option( 'wtai_keywordanalysis_location', array() );

		update_option( 'wtai_api_token_old', $old_token );

		wtai_reset_user_tokens();

		wtai_reset_user_preferences( 'deactivate' );

		wtai_reset_bulk_options_values();

		wtai_reset_product_review();

		wtai_reset_bulk_generate_text_field_user_preference();

		wtai_reset_product_location();

		wtai_reset_product_meta();

		wtai_reset_category_meta();

		// Delete transients.
		delete_transient( 'wtai_generate_filters_etag_check' );
		delete_transient( 'wtai_api_token_expired_checked' );
		delete_transient( 'wtai_api_token_expired' );
		delete_transient( 'wtai_account_credit_details' );
	}

	/**
	 * Reset saved user tokens in the DB.
	 */
	function wtai_reset_user_tokens() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result_users = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				'SELECT user_id, meta_key FROM %1s 
            	WHERE meta_key LIKE %s OR meta_key LIKE %s GROUP BY user_id',
				$wpdb->usermeta,
				'wtai_api_web_token%',
				'wtai_api_web_token_time%'
			)
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared.

		foreach ( $result_users as $user ) {
			$meta_key = $user->meta_key;

			if ( false !== strpos( $meta_key, 'wtai_api_web_token' ) ) {
				update_user_meta( $user->user_id, $meta_key, '' );
			}
		}
	}

	/**
	 * Reset saved user preferences in the DB.
	 *
	 * @param string $reset_type Reset type.
	 */
	function wtai_reset_user_preferences( $reset_type = 'deactivate' ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result_users = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				'SELECT user_id FROM %1s 
            	WHERE meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
					OR meta_key LIKE %s 
				GROUP BY user_id',
				$wpdb->usermeta,
				'wtai_comparison_cb%',
				'wtai_preselected_types%',
				'wtai_preselected_types_default_flag%',
				'wtai_tones_options_user_preference%',
				'wtai_tones_custom_user_preference%',
				'wtai_tones_custom_text_user_preference%',
				'wtai_styles_options_user_preference%',
				'wtai_styles_custom_user_preference%',
				'wtai_styles_custom_text_user_preference%',
				'wtai_audiences_options_user_preference%',
				'wtai_product_attribute_preference%',
				'wtai_hide_guidelines%',
				'wtai_highlight%',
				'wtai_highlight_default_flag%',
				'wtai_highlight_pronouns%',
				'wtai_bulk_generate_text_field_user_preference%',
				'wtai_bulk_transfer_text_field_user_preference%',
				'wtai_selected_product_alt_image_ids%',
				'wtai_hide_category_guidelines%',
				'wtai_comparison_category_cb%',
				'wtai_highlight_default_category_flag%',
				'wtai_highlight_category%',
				'wtai_preselected_types_default_category_flag%',
				'wtai_preselected_category_types%',
				'wtai_highlight_pronouns_category%',
				'wtai_popup_blocker_notice_dismissed%',
				'wtai_category_image_checked_status_set%',
				'wtai_category_image_checked_status%',
				'wtai_product_image_checked_status_set%'
			)
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared.

		foreach ( $result_users as $user ) {
			update_user_meta( $user->user_id, 'wtai_comparison_cb', '' );
			update_user_meta( $user->user_id, 'wtai_preselected_types', '' );
			update_user_meta( $user->user_id, 'wtai_preselected_types_default_flag', '' );
			update_user_meta( $user->user_id, 'wtai_tones_options_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_tones_custom_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_tones_custom_text_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_styles_options_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_styles_custom_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_styles_custom_text_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_audiences_options_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_product_attribute_preference', '' );
			update_user_meta( $user->user_id, 'wtai_hide_guidelines', '' );
			update_user_meta( $user->user_id, 'wtai_highlight', '' );
			update_user_meta( $user->user_id, 'wtai_highlight_default_flag', '' );
			update_user_meta( $user->user_id, 'wtai_highlight_pronouns', '' );
			update_user_meta( $user->user_id, 'wtai_bulk_generate_text_field_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_bulk_transfer_text_field_user_preference', '' );
			update_user_meta( $user->user_id, 'wtai_selected_product_alt_image_ids', '' );
			update_user_meta( $user->user_id, 'wtai_hide_category_guidelines', '' );
			update_user_meta( $user->user_id, 'wtai_comparison_category_cb', '' );
			update_user_meta( $user->user_id, 'wtai_highlight_default_category_flag', '' );
			update_user_meta( $user->user_id, 'wtai_highlight_category', '' );
			update_user_meta( $user->user_id, 'wtai_preselected_types_default_category_flag', '' );
			update_user_meta( $user->user_id, 'wtai_preselected_category_types', '' );
			update_user_meta( $user->user_id, 'wtai_highlight_pronouns_category', '' );
			update_user_meta( $user->user_id, 'wtai_category_image_checked_status_set', '' );
			update_user_meta( $user->user_id, 'wtai_category_image_checked_status', '' );
			update_user_meta( $user->user_id, 'wtai_product_image_checked_status_set', '' );

			// Reset popup blocker dismiss option.
			if ( 'deactivate' === $reset_type ) {
				update_user_meta( $user->user_id, 'wtai_popup_blocker_notice_dismissed', '' );
				update_user_meta( $user->user_id, 'wtai_popup_blocker_notice_dismissed_list', '' ); // Backward compat for previous versions.
				update_user_meta( $user->user_id, 'wtai_popup_blocker_notice_dismissed_install', '' ); // Backward compat for previous versions.
				update_user_meta( $user->user_id, 'wtai_popup_blocker_notice_dismissed_settings', '' ); // Backward compat for previous versions.
				update_user_meta( $user->user_id, 'wtai_popup_blocker_notice_dismissed_list_category', '' ); // Backward compat for previous versions.
			}
		}
	}

	/**
	 * Reset bulk generation db flags.
	 */
	function wtai_reset_bulk_options_values() {
		global $wpdb;

		update_option( 'wtai_bulk_generate_request', '' );
		update_option( 'wtai_bulk_generate_request_done', '' );
		update_option( 'wtai_bulk_product_ids', '' );
		update_option( 'wtai_bulk_generate_transfers', '' );
		update_option( 'wtai_bulk_transfer_users_done', '' );
		update_option( 'wtai_localized_countries', '' ); // Reset localized countries.
		update_option( 'wtai_localized_countries_enabled', array() ); // Reset localized countries.

		if ( is_multisite() ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				$blog_id = $site->blog_id;

				if ( 1 === intval( $site->archived ) || 1 === intval( $site->deleted ) ) {
					continue;
				}

				if ( $current_blog_id === $blog_id ) {
					continue;
				}

				switch_to_blog( $blog_id );

				update_option( 'wtai_bulk_generate_request', '' );
				update_option( 'wtai_bulk_generate_request_done', '' );
				update_option( 'wtai_bulk_product_ids', '' );
				update_option( 'wtai_bulk_generate_transfers', '' );
				update_option( 'wtai_bulk_transfer_users_done', '' );
				update_option( 'wtai_localized_countries', '' ); // Reset localized countries.
				update_option( 'wtai_localized_countries_enabled', array() ); // Reset localized countries.

				restore_current_blog();
			}
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$user_data = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $wpdb->usermeta . ' WHERE meta_key IN (%s, %s, %s)',
				'wtai_bulk_generated_ids',
				'wtai_bulk_request_id',
				'wtai_bulk_transfer_ids'
			),
			ARRAY_A
		);

		if ( $user_data ) {
			foreach ( $user_data as $user_meta ) {
				update_user_meta( $user_meta['user_id'], $user_meta['meta_key'], '' );
			}
		}
	}

	/**
	 * Reset product review db flags.
	 */
	function wtai_reset_product_review() {
		$args = array(
			'post_type'      => 'product', // Replace with the actual post type of your products.
			'posts_per_page' => -1,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'wtai_review',
					'compare' => 'EXISTS',
				),
			),
		);

		$products = new WP_Query( $args );

		if ( $products->have_posts() ) {
			while ( $products->have_posts() ) {
				$products->the_post();
				$product_id = get_the_ID();

				update_post_meta( $product_id, 'wtai_review', '' );
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Reset product location db flags.
	 */
	function wtai_reset_product_location() {
		$args = array(
			'post_type'      => 'product', // Replace with the actual post type of your products.
			'posts_per_page' => -1,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'wtai_keyword_location_code',
					'compare' => 'EXISTS',
				),
			),
		);

		$products = new WP_Query( $args );

		if ( $products->have_posts() ) {
			while ( $products->have_posts() ) {
				$products->the_post();
				$product_id = get_the_ID();

				update_post_meta( $product_id, 'wtai_keyword_location_code', '' );
			}
		}

		wp_reset_postdata();
	}

	/**
	 * Reset product wta meta db flags.
	 */
	function wtai_reset_product_meta() {
		global $wpdb;

		$meta_keys = array(
			'wtai_product_reference_id',
			'wtai_keyword_ideas_volume_filter',
			'wtai_keyword_ideas_difficulty_filter',
			'wtai_keyword_ideas_sorting',
			'wtai_keyword_analysis_sort_filter_suggested',
			'wtai_keyword_analysis_sort_filter_competitor',
			'wtai_keyword_analysis_sort_filter_ranked',
			'wtai_bulk_queue_id_product_description',
			'wtai_bulk_queue_id_product_excerpt',
			'wtai_bulk_queue_id_page_title',
			'wtai_bulk_queue_id_page_description',
			'wtai_bulk_queue_id_open_graph',
			'wtai_product_attribute_preference',
			'wtai_refresh_suggested_idea',
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s) AND meta_value != '' ",
				'wtai_product_reference_id',
				'wtai_keyword_ideas_volume_filter',
				'wtai_keyword_ideas_difficulty_filter',
				'wtai_keyword_ideas_sorting',
				'wtai_keyword_analysis_sort_filter_suggested',
				'wtai_keyword_analysis_sort_filter_competitor',
				'wtai_keyword_analysis_sort_filter_ranked',
				'wtai_bulk_queue_id_product_description',
				'wtai_bulk_queue_id_product_excerpt',
				'wtai_bulk_queue_id_page_title',
				'wtai_bulk_queue_id_page_description',
				'wtai_bulk_queue_id_open_graph',
				'wtai_product_attribute_preference',
				'wtai_refresh_suggested_idea',
			)
		);

		foreach ( $post_ids as $product_id ) {
			foreach ( $meta_keys as $key ) {
				delete_post_meta( $product_id, $key, '' );
			}
		}
	}

	/**
	 * Reset category wta meta db flags.
	 */
	function wtai_reset_category_meta() {
		global $wpdb;

		$meta_keys = array(
			'wtai_keyword_analysis_sort_filter_suggested',
			'wtai_keyword_analysis_sort_filter_competitor',
			'wtai_keyword_analysis_sort_filter_ranked',
			'wtai_refresh_suggested_idea',
			'wtai_review',
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT term_id FROM $wpdb->termmeta WHERE meta_key IN (%s,%s,%s,%s,%s) AND meta_value != '' ",
				'wtai_keyword_analysis_sort_filter_suggested',
				'wtai_keyword_analysis_sort_filter_competitor',
				'wtai_keyword_analysis_sort_filter_ranked',
				'wtai_refresh_suggested_idea',
				'wtai_review',
			)
		);

		foreach ( $term_ids as $category_id ) {
			foreach ( $meta_keys as $key ) {
				delete_term_meta( $category_id, $key, '' );
			}
		}
	}

	/**
	 * Reset product wta meta db flags.
	 */
	function wtai_reset_product_activity_meta() {
		global $wpdb;

		$meta_keys = array(
			'wtai_last_activity_date',
			'wtai_generate_date',
			'wtai_transfer_date',
			'wtai_last_activity',
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN (%s,%s,%s,%s) AND meta_value != '' ",
				'wtai_last_activity_date',
				'wtai_generate_date',
				'wtai_transfer_date',
				'wtai_last_activity',
			)
		);

		foreach ( $post_ids as $product_id ) {
			foreach ( $meta_keys as $key ) {
				delete_post_meta( $product_id, $key, '' );
			}
		}
	}

	/**
	 * Reset category wta meta db flags.
	 */
	function wtai_reset_category_activity_meta() {
		global $wpdb;

		$meta_keys = array(
			'wtai_last_activity_date',
			'wtai_generate_date',
			'wtai_transfer_date',
			'wtai_last_activity',
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$term_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT term_id FROM $wpdb->termmeta WHERE meta_key IN (%s,%s,%s,%s) AND meta_value != '' ",
				'wtai_last_activity_date',
				'wtai_generate_date',
				'wtai_transfer_date',
				'wtai_last_activity',
			)
		);

		foreach ( $term_ids as $category_id ) {
			foreach ( $meta_keys as $key ) {
				delete_term_meta( $category_id, $key, '' );
			}
		}
	}

	/**
	 * Reset bulk generate text field user preference.
	 */
	function wtai_reset_bulk_generate_text_field_user_preference() {
		global $wpdb;
		$query_user_meta = "SELECT * FROM {$wpdb->usermeta} 
            WHERE meta_key IN ('wtai_bulk_generate_text_field_user_preference')
            ORDER BY user_id, meta_key";

		$product_fields_all = apply_filters( 'wtai_fields', array() );
		$product_field_key  = array_keys( $product_fields_all );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$user_data = $wpdb->get_results(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				'SELECT * FROM %1s 
                WHERE meta_key IN (%s) ORDER BY user_id, meta_key ',
				$wpdb->usermeta,
				'wtai_bulk_generate_text_field_user_preference'
			),
			ARRAY_A
		);

		if ( $user_data ) {
			foreach ( $user_data as $user_meta ) {
				update_user_meta( $user_meta['user_id'], $user_meta['meta_key'], $product_field_key );
			}
		}
	}

	/**
	 * Deactivation hook.
	 */
	function wtai_plugin_deactivate() {
		// Check if we're in a multisite environment.
		if ( is_multisite() ) {
			// Get the plugin basename (e.g., 'my-plugin/my-plugin.php').
			$plugin = plugin_basename( __FILE__ );

			// Check if the plugin is active for the entire network.
			if ( is_plugin_active_for_network( $plugin ) ) {
				// This is a network-wide deactivation.

				// Retrieve all subsites in the network.
				$sites = get_sites();

				// Loop through each site and run the reset function.
				foreach ( $sites as $site ) {
					$site_id = $site->blog_id;

					// Switch to each subsite.
					switch_to_blog( $site_id );

					// Call the reset function for each subsite.
					wtai_reset_all_settings();

					// Restore the main site after each switch.
					restore_current_blog();
				}
			} else {
				// This is a single-site deactivation.
				wtai_reset_all_settings();
			}
		} else {
			// This is a regular (non-multisite) deactivation.
			wtai_reset_all_settings();
		}

		// Record deactivation statistics.
		do_action( 'wtai_record_installation_statistics', 'Deactivate', 0 );
	}
	register_deactivation_hook( __FILE__, 'wtai_plugin_deactivate' );

	/**
	 * Get current site language used in WP Settings.
	 */
	function wtai_get_site_language() {
		$locale = '';

		// If multisite, check options.
		if ( is_multisite() ) {
			// Don't check blog option when installing.
			if ( wp_installing() ) {
				$ms_locale = get_site_option( 'WPLANG' );
			} else {
				$ms_locale = get_option( 'WPLANG' );
				if ( false === $ms_locale ) {
					$ms_locale = get_site_option( 'WPLANG' );
				}
			}

			if ( false !== $ms_locale ) {
				$locale = $ms_locale;
			}
		} else {
			$db_locale = get_option( 'WPLANG' );
			if ( false !== $db_locale ) {
				$locale = $db_locale;
			}
		}

		if ( empty( $locale ) ) {
			$locale = 'en_US';
		}

		return $locale;
	}

	/**
	 * Check if current beta language is allowed.
	 */
	function wtai_is_allowed_beta_language() {
		if ( WTAI_ALLOWED_ALL_LANGUAGES ) {
			return true;
		}

		$excluded_domains = array(
			// 'writetextai.test',
			'writetextaim.1902dev3.com',
			'writetextaim.1902dev3.com/dk',
			'writetextdawp.1902dev3.com',
			'scanlux3.1902dev3.com',
		);
		// tested bulk generate, bulk transfer, single generate text, rewrite,transfer text, save, prev/next, unsaved popup.
		// bulk generate from grid pull the correct language, but in single generate, it pulls always the EN language. Informed Tel and she's currently working on it.
		if ( WTAI_TRANSLATIONS_ENABLED ) {
			$included_languages = array(
				'en', // English (United States).
				'da', // Dansk.
				'de', // Deutsch (Schweiz), tested.
				'no', // added nn_NO, same for nb because no lang='no'.
				'nb', // added Norsk bokmål, tested.
				'nn', // added Norsk nynorsk, tested.
				'sv', // Svenska.
				'fr', // Français.
				'es', // Español.
				'pt', // added pt_BR.
				'is', // Íslenska.
				'nl', // Nederlands.
				'it', // italian.
				'ca', // Catalan.
			);
		} else {
			$included_languages = array(
				'en', // English (United States).
			);
		}

		$site_url = site_url();
		$site_url = preg_replace( '(^https?://)', '', $site_url );

		if ( in_array( $site_url, $excluded_domains, true ) ) {
			return true;
		}

		// lets check if WPML is active.
		$locale_lang = '';
		if ( function_exists( 'pll_current_language' ) ) {
			$locale_lang = pll_current_language( 'slug' );
			if ( ! $locale_lang ) {
				$locale_lang = pll_default_language( 'slug' );
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['lang'] ) && '' !== $_GET['lang'] ) {
				$locale_lang = wp_kses( wp_unslash( $_GET['lang'] ), 'post' ); // phpcs:ignore WordPress.Security.NonceVerification
			}
		} else {
			$current_language = apply_filters( 'wpml_current_language', null );
			$default_lang     = apply_filters( 'wpml_default_language', null );
			if ( $default_lang && $current_language ) {
				$locale_lang = $current_language;
			}
		}

		if ( '' === $locale_lang ) {
			$locale       = wtai_get_site_language();
			$locale_array = explode( '_', $locale );
			$locale_lang  = $locale_array[0];
		}

		if ( ! in_array( $locale_lang, $included_languages, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Displays notice error if current language is not allowed.
	 */
	function wtai_check_beta_locale_language() {
		if ( false === wtai_is_allowed_beta_language() ) {
			if ( WTAI_TRANSLATIONS_ENABLED ) {
				/* translators: %s: settings admin url */
				$message = sprintf( "Your default language is currently unsupported. At this stage, we only support English, Danish, German, Norwegian, Swedish, French, Spanish, Portuguese, Dutch, Catalan, and Italian. <a href='%s' target='_blank' >Please switch to a supported language in your settings</a> if you want to use WriteText.ai.", admin_url( 'options-general.php' ) ); // No need to translate this text cause this will only appear on NON EN language that is NOT supported and no translation is available.
			} else {
				/* translators: %s: settings admin url */
				$message = sprintf( "Your default language is currently unsupported. At this stage, we only support English. <a href='%s' target='_blank' >Please switch to a supported language in your settings</a> if you want to use WriteText.ai.", admin_url( 'options-general.php' ) ); // No need to translate this text cause this will only appear on NON EN language that is NOT supported and no translation is available.
			}

			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['page'] ) && 'write-text-ai' === $_GET['page'] ) {
				echo '<style>
                    .notice{
                        display: none;
                    }
                    .wtai-lang-error-notice{
                        display: block;
                    }
                </style>';
			}

			echo '<div class="notice notice-error is-dismissible wtai-lang-error-notice">
                <p>' . wp_kses( $message, 'post' ) . '</p>
            </div>';
		}
	}
	add_action( 'admin_notices', 'wtai_check_beta_locale_language' );

	/**
	 * Displays notice error if current plugin version is outdated and needs an update.
	 */
	function wtai_display_plugin_update_notice() {
		if ( '1' === get_option( 'wtai_latest_version_outdated' ) || '1' === get_option( 'wtai_force_version_update' ) ) {
			$latest_version_message = get_option( 'wtai_latest_version_message' );
			?>
			<div class="wtai-update-notice notice notice-error is-dismissible">
				<p><?php echo wp_kses( $latest_version_message, 'post' ); ?></p>
			</div>
			<?php
		}
	}
	add_action( 'admin_notices', 'wtai_display_plugin_update_notice' );

	/**
	 * Get current writetext plugin version.
	 *
	 * @param bool $clean_dev_suffix If true, removes '-dev' suffix from version.
	 */
	function wtai_get_version( $clean_dev_suffix = true ) {
		$plugin_data    = get_plugin_data( __FILE__ );
		$plugin_version = $plugin_data['Version'];

		if ( $clean_dev_suffix ) {
			$plugin_version = str_replace( '-dev', '', $plugin_version );
		}

		return $plugin_version;
	}

	/**
	 * Get current WP core version.
	 */
	function wtai_get_wp_version() {
		if ( function_exists( 'get_bloginfo' ) ) {
			return get_bloginfo( 'version' );
		} else {
			global $wp_version;
			return $wp_version;
		}
	}

	/**
	 * Check if current WP core version is not supported by the plugin.
	 */
	function wtai_is_wp_version_outdated() {
		$current_wp_version        = wtai_get_wp_version();
		$minimum_wp_version        = '6.0';
		$wp_version_compare_result = version_compare( $current_wp_version, $minimum_wp_version );

		return -1 === $wp_version_compare_result;
	}

	include_once WTAI_ABSPATH . 'includes/class-wtai-init.php';
}