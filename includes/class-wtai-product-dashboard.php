<?php
/**
 * Product dashboard hooks and filter class for WTA
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WTAI Product dashboard class.
 */
class WTAI_Product_Dashboard extends WTAI_Init {
	/**
	 * Main data fields.
	 *
	 * @var array
	 */
	private $main_data_fields = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->define_vars();
		$this->init_hooks();
	}

	/**
	 * Define vars.
	 */
	public function define_vars() {
	}

	/**
	 * Initialize hooks.
	 */
	public function init_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_script' ) );

		add_action( 'wtai_bulk_generate_loader', array( $this, 'get_bulk_generate_loader' ), 10, 1 );
		add_action( 'wtai_bulk_transfer_loader', array( $this, 'get_bulk_transfer_loader' ), 10, 1 );
		add_action( 'wtai_bulk_edit_cancel_and_exit', array( $this, 'get_product_edit_cancel_and_exit' ), 10, 1 );
		add_action( 'wtai_bulk_edit_generate_cancel', array( $this, 'get_product_generate_cancel_all' ), 10, 1 );

		add_action( 'wtai_product_generate_cancel_popup', array( $this, 'get_product_generate_cancel' ) ); // All html.

		add_action( 'wtai_edit_product_form', array( $this, 'get_edit_product_form' ), 10, 1 );
		add_action( 'wp_ajax_wtai_get_generated_tooltip_text', array( $this, 'get_generated_tooltip_text_callback' ) );

		add_action( 'wp_ajax_wtai_product_data', array( $this, 'get_product_data_callback' ) );
		add_action( 'wp_ajax_wtai_single_product_data_text', array( $this, 'get_product_field_data_callback' ) );

		add_action( 'wp_ajax_wtai_generate_bulk_success', array( $this, 'get_generate_bulk_success_callback' ) );
		add_action( 'wp_ajax_wtai_generate_bulk_cancel', array( $this, 'get_generate_bulk_cancel_callback' ) );
		add_action( 'wp_ajax_wtai_single_product_history', array( $this, 'get_product_history_callback' ) );
		add_action( 'wp_ajax_wtai_global_product_history', array( $this, 'get_global_history_callback' ) );

		add_action( 'wp_ajax_wtai_otherproductdetails_text', array( $this, 'process_otherproductdetails_callback' ) );
		add_action( 'wp_ajax_wtai_product_review_check', array( $this, 'process_product_review_callback' ) );

		add_filter( 'wtai_column_language', array( $this, 'get_country_language_string' ), 10, 2 );
		add_action( 'admin_menu', array( $this, 'get_menu_page' ) );

		add_action( 'wp_ajax_wtai_record_generate_preselected_types', array( $this, 'record_generate_preselected_types_callback' ) );
		add_action( 'wp_ajax_wtai_user_highlight_check', array( $this, 'process_user_highlight_callback' ) );

		// wtai_comparision saving.
		add_action( 'wp_ajax_wtai_comparison_user_check', array( $this, 'process_user_comparison_callback' ) );
		add_action( 'wp_ajax_wtai_poll_background_jobs', array( $this, 'poll_background_jobs_callback' ) );

		// Bulk generate popup modal.
		add_action( 'wp_ajax_wtai_user_bulk_generate_popup_check', array( $this, 'process_user_bulk_generate_popup_callback' ) );

		add_action( 'wp_ajax_wtai_transfer_bulk_success', array( $this, 'get_transfer_bulk_success_callback' ) );
		add_action( 'wp_ajax_wtai_transfer_bulk_cancel', array( $this, 'get_transfer_bulk_cancel_callback' ) );

		add_action( 'wp_ajax_wtai_bulk_dismiss_all', array( $this, 'bulk_dismiss_all' ) );
		add_action( 'wp_ajax_wtai_save_bulk_generate_text_field_user_preference', array( $this, 'save_bulk_generate_text_field_user_preference' ) );
		add_action( 'wp_ajax_wtai_save_bulk_transfer_text_field_user_preference', array( $this, 'save_bulk_transfer_text_field_user_preference' ) );

		add_action( 'wp_ajax_wtai_save_tones_styles_option_user_preference', array( $this, 'save_tones_styles_option_user_preference' ) );

		add_action( 'admin_init', array( $this, 'reset_bulk_options_values' ) );
		add_action( 'admin_init', array( $this, 'job_checker_temp' ) );

		add_action( 'wp_ajax_wtai_save_product_keyword_location_code', array( $this, 'save_product_keyword_location_code' ) );

		add_action( 'admin_init', array( $this, 'resave_last_activity_temp' ) );

		add_action( 'admin_footer', array( $this, 'render_wtai_admin_footer' ), 10 );

		add_action( 'wp_ajax_wtai_search_reference_product', array( $this, 'search_reference_product' ) );

		add_filter( 'wtai_get_disallowed_combinations', array( $this, 'get_disallowed_combinations' ), 10, 2 );
		add_filter( 'wtai_get_formal_informal_pronouns', array( $this, 'get_formal_informal_pronouns' ), 10, 2 );

		add_action( 'wp_ajax_wtai_user_highlight_pronouns_check', array( $this, 'process_user_highlight_pronouns_callback' ) );

		add_action( 'wp_ajax_wtai_record_single_product_attribute_preference', array( $this, 'record_single_product_attribute_preference_callback' ) );

		add_action( 'wp_ajax_wtai_user_hide_guidelines', array( $this, 'process_wtai_user_hide_guidelines_callback' ) );

		add_action( 'wtai_country_selection_popup', array( $this, 'get_wtai_country_selection_popup' ), 10, 1 );

		add_action( 'wp_ajax_wtai_save_localized_countries', array( $this, 'save_localized_countries' ) );
		add_action( 'wp_ajax_wtai_reset_user_preferences', array( $this, 'wtai_reset_user_preferences' ) );

		add_action( 'wtai_restore_global_setting_completed', array( $this, 'get_restore_global_setting_completed_popup' ), 10 );

		add_action( 'wtai_premium_modal', array( $this, 'get_premium_modal' ), 10 );
		add_action( 'wp_ajax_wtai_tag_extension_review_as_done', array( $this, 'tag_extension_review_as_done' ), 10 );
		add_action( 'wp_ajax_wtai_preprocess_images', array( $this, 'preprocess_images' ), 10 );
		add_action( 'wtai_preprocess_image_loader', array( $this, 'preprocess_image_loader' ), 10 );
		add_action( 'wtai_image_confirmation_proceed_loader', array( $this, 'image_confirmation_proceed_loader' ), 10 );
		add_action( 'wtai_image_confirmation_proceed_bulk_loader', array( $this, 'image_confirmation_proceed_bulk_loader' ), 10 );
		add_action( 'wp_ajax_wtai_get_alt_text', array( $this, 'wtai_get_alt_text' ), 10 );

		add_action( 'wp_head', array( $this, 'add_product_custom_body_class' ) );
		add_action( 'wtai_admin_mobile_footer', array( $this, 'render_wtai_admin_mobile_footer' ), 10, 1 );
		add_action( 'wtai_render_intent_tooltip', array( $this, 'render_intent_tooltip' ), 10, 1 );
		add_action( 'wtai_freemium_badge', array( $this, 'render_wtai_freemium_badge' ), 10 );
		add_action( 'wtai_freemium_popup', array( $this, 'render_wtai_freemium_popup' ), 10, 1 );
		add_action( 'wp_ajax_wtai_freemium_popup_closed', array( $this, 'freemium_popup_close' ), 10 );
		add_action( 'wp_ajax_wtai_get_global_settings', array( $this, 'get_global_settings_ajax' ), 10 );

		add_action( 'wp_ajax_wtai_dismiss_popup_blocker_notice', array( $this, 'dismiss_popup_blocker_notice' ), 10 );
	}

	/**
	 * Get country language string.
	 *
	 * @param string $locale Locale.
	 * @param object $post Post object.
	 */
	public function get_country_language_string( $locale, $post ) {
		$locale = wtai_get_country_by_code( $locale );
		return $locale;
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_script() {
		$cache_buster_version = WTAI_VERSION . '-' . wp_rand();

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'write-text-ai' === $_GET['page'] ) {
			$disallowed_combinations = apply_filters( 'wtai_get_disallowed_combinations', array(), false );

			wp_enqueue_style( 'wtai-admin', WTAI_DIR_URL . 'assets/css/admin.css', array(), $cache_buster_version );

			$is_doing_install = false;
			if ( 5 !== intval( get_option( 'wtai_installation_step', 1 ) ) || wtai_is_token_expired() || ! wtai_has_api_base_url() ) {
				wp_register_style( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/css/tooltipster.bundle.min.css', array(), 'v4.2.8' );
				wp_enqueue_style( 'wtai-admin-installation', WTAI_DIR_URL . 'assets/css/admin-installation.css', array( 'wtai-toolstipster' ), $cache_buster_version );

				wp_register_script( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/js/tooltipster.bundle.min.js', array( 'jquery' ), 'v4.2.8' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_enqueue_script( 'wtai-admin-installation', WTAI_DIR_URL . 'assets/js/admin-installation.js', array( 'jquery', 'wtai-toolstipster' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_localize_script(
					'wtai-admin-installation',
					'WTAI_OBJ',
					array(
						'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
						'adminPageSettings'      => admin_url( 'admin.php?page=write-text-ai' ),
						'disallowedCombinations' => $disallowed_combinations,
						'isPremium'              => WTAI_PREMIUM ? '1' : '0',
					)
				);

				$is_doing_install = true;
			} else {
				$web_token = apply_filters( 'wtai_web_token', '' );

				$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
				$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
				$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;

				wp_deregister_script( 'autosave' );
				wp_enqueue_editor();
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_register_script( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/js/tooltipster.bundle.min.js', array( 'jquery' ), 'v4.2.8' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				wp_register_style( 'wtai-jquery-ui', WTAI_DIR_URL . 'assets/lib/jquery-ui.css', array(), 'v1.12.1' );
				wp_enqueue_style( 'wtai-jquery-ui' );

				$enable_streaming_debug = '0';
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['WTAEnableStreamingDebug'] ) && '1' === $_GET['WTAEnableStreamingDebug'] ) {
					$enable_streaming_debug = '1';
				}

				$stream_debug_field = '';
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_GET['streamDebugField'] ) && '' !== $_GET['streamDebugField'] ) {
					$stream_debug_field = sanitize_text_field( wp_unslash( $_GET['streamDebugField'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$current_user_id = get_current_user_id();
				$user            = get_user_by( 'id', $current_user_id );

				$api_base_url = wtai_get_api_base_url();

				wp_enqueue_script( 'wtai-admin-signalr', WTAI_DIR_URL . 'assets/js/signalr.min.js', array(), '3.1.31' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_enqueue_script( 'wtai-admin-streaming', WTAI_DIR_URL . 'assets/js/admin-streaming.js', array( 'jquery', 'wtai-admin-signalr' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_localize_script(
					'wtai-admin-streaming',
					'WTAI_STREAMING_OBJ',
					array(
						'accessToken'          => $web_token,
						'connectionBaseURL'    => 'https://' . $api_base_url . '/',
						'storeID'              => str_replace( array( 'http://', 'https://' ), '', get_site_url() ),
						'connectedText'        => 'Connected', // Internal label not used, no need to translate.
						'disconnectedText'     => 'Disconnected', // Internal label not used, no need to translate.
						'enableStreamingDebug' => $enable_streaming_debug,
						'streamDebugField'     => $stream_debug_field,
						'userEmail'            => $user->user_email,
					)
				);

				wp_enqueue_style( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.default.css', array(), $cache_buster_version );
				wp_enqueue_script( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.min.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				$current_user_id = get_current_user_id();

				$loader_html_temp_markup = $this->get_loader_html_temp_markup();

				$user_can_transfer = '1';
				if ( false === wtai_current_user_can( 'writeai_transfer_generated_text' ) ) {
					$user_can_transfer = '0';
				}

				$credit_array              = apply_filters( 'wtai_get_credits_count', array() );
				$user_generate_text_fields = wtai_get_bulk_generate_text_fields_user_preference();

				// Credit computation vars.
				$generation_limit_vars = wtai_get_generation_limit_vars( $global_rule_fields, $credit_array );

				$reference_char_count_html = '<div class="wtai-reference-count-main-wrap" >
						<div class="wt-reference-count-flex-wrap" >
							<span class="wt-reference-count-label">' . __( 'Reference: ', 'writetext-ai' ) . '</span>
							<span class="wt-reference-count-prod-name" ></span>
							<span class="wt-reference-count-wrap" >
								(<span class="wtai-char-count">0</span> ' . __( ' Char', 'writetext-ai' ) . ' | 
								<span class="word-count">0</span> ' . __( ' word/s', 'writetext-ai' ) . ')
							</span>
						</div>
					</div>';

				$field_type_labels = wtai_get_field_type_labels();

				$formal_informal_pronouns = apply_filters( 'wtai_get_formal_informal_pronouns', array(), true );

				$formal_language_support = 0;
				if ( wtai_is_formal_informal_lang_supported() ) {
					$formal_language_support = 1;
				}

				$version_outdated         = get_option( 'wtai_latest_version_outdated' );
				$version_outdated_message = get_option( 'wtai_latest_version_message' );

				$current_user_can_generate = wtai_current_user_can( 'writeai_generate_text' ) ? '1' : '0';

				/* translators: %s: Max keyword length */
				$add_disabled_tooltip = sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one to the "Keywords to be included in your text".', 'writetext-ai' ), $max_keyword_count );

				/* translators: %s: Max keyword length */
				$max_keyword_tooltip_message = wp_kses_post( sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one.', 'writetext-ai' ), $max_manual_keyword_count ) );

				// Common functions script.
				wp_enqueue_script( 'wtai-admin-common-functions', WTAI_DIR_URL . 'assets/js/admin-common-functions.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				// Filter script.
				wp_enqueue_script( 'wtai-admin-filter', WTAI_DIR_URL . 'assets/js/admin-filter.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_localize_script(
					'wtai-admin-filter',
					'WTAI_FILTER_OBJ',
					array(
						'ajax_url'                        => admin_url( 'admin-ajax.php' ),
						'tooltipDisableToneStyleMessage1' => __( 'Tones and styles are unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableAudienceMessage1'  => __( 'Audience is unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableReferenceMessage2' => __( 'Reference product is unavailable when doing rewrite.', 'writetext-ai' ),
						'generateCTAText'                 => __( 'Generate selected', 'writetext-ai' ),
						'rewriteCTAText'                  => __( 'Rewrite selected', 'writetext-ai' ),
						'disallowedCombinations'          => $disallowed_combinations,
						'referenceCharCountHTML'          => $reference_char_count_html,
						'tooltipDisableRewriteMessage1'   => __( 'Rewrite is unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableRewriteMessage2'   => __( 'Rewrite is unavailable when no WordPress text is found.', 'writetext-ai' ),
					)
				);

				// Keywords script.
				wp_enqueue_script( 'wtai-admin-keywords', WTAI_DIR_URL . 'assets/js/admin-keywords.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
				wp_localize_script(
					'wtai-admin-keywords',
					'WTAI_KEYWORD_OBJ',
					array(
						'ajax_url'                         => admin_url( 'admin-ajax.php' ),
						'access_denied'                    => __( 'Access denied', 'writetext-ai' ),
						'keyword_max'                      => isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD,
						'keyword_exist_msg'                => __( 'The entered keyword already exists.', 'writetext-ai' ),
						'keyword_ideas_msg'                => __( 'No keyword data received from the API. Check for misspellings in your keyword, check your country selection, or use a different keyword and try again.', 'writetext-ai' ),
						'keyword_ideas_stale_msg'          => __( 'Keyword data are refreshed at the start of every month. The data you’re seeing might have changed since you last requested it. Click the “Start AI-powered keyword analysis” button to refresh data.', 'writetext-ai' ),
						'finalKeywordAnalysisMessage'      => __( 'Getting results ready...', 'writetext-ai' ),
						'maxSemanticKeywordMessage'        => __( 'You have selected the maximum number of semantic keywords.', 'writetext-ai' ),
						'keywordTooManyRequestError'       => WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE,
						'generalErrorMessage'              => WTAI_GENERAL_ERROR_MESSAGE,
						'startKeywordAnalysisErrorMessage' => WTAI_KEYWORD_GENERAL_ERROR_MESSAGE,
						'startKeywordAnalysisMessage'      => __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ),
						'refreshingKeywordAnalysisMessage' => __( 'Refreshing data...', 'writetext-ai' ),
						'finalRefreshKeywordAnalysisMessage' => __( 'Getting results ready...', 'writetext-ai' ),
						'ongoingKeywordAnalysisMessage'    => __( 'Keyword analysis is already in progress...', 'writetext-ai' ),
						'productNameNotAllowedMsg'         => __( 'The product name is considered by default in generating text. Please add a different keyword.', 'writetext-ai' ),
						'productNameNotAllowedMsgFromPlus' => __( 'This keyword is the same as the product name and is already considered by default when generating text. Please add a different keyword.', 'writetext-ai' ),
						'keywordTrashDisabledTooltip'      => __( 'Remove this keyword from the "Keywords to be included in your text" before deleting it.', 'writetext-ai' ),
						'keywordPlusDisabledTooltip'       => $add_disabled_tooltip,
						'keywordPlusTooltip'               => __( 'Add as target keyword', 'writetext-ai' ),
						'keywordMinusTooltip'              => __( 'Remove as target keyword', 'writetext-ai' ),
						'keywordTrashTooltip'              => __( 'Delete keyword', 'writetext-ai' ),
						'emptyRankMessage'                 => __( 'Click the "Start AI-powered keyword analysis" button to get started.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyRankMessageWithAnalysis'     => __( 'This page is not ranking for any keywords as of %s. <br><br>You may click the "Start AI-powered keyword analysis" button to refresh ranking data for the whole domain. We recommend doing this after a month has passed since your last request — any less than that may not return any significant results.', 'writetext-ai' ),
						'emptyCompetitorMessage'           => __( 'Click the “Start AI-powered keyword analysis” button to get started. If there are no keywords you are currently ranking for or selected keywords to be included in your text, WriteText.ai will search for possible competitors you may have based on your product name.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyCompetitorMessageWithAnalysis' => __( 'No competitor keywords found as of %s. <br><br>Select or manually type other keywords and try again.', 'writetext-ai' ),
						'emptySuggestedMessage'            => __( 'Click the “Start AI-powered keyword analysis” button to get data for your manually typed keywords (keyword ideas, search volume, and difficulty).', 'writetext-ai' ),
						'emptySuggestedMessageWithAnalysis' => __( 'No keyword data received. Check for misspellings in your keyword(s) or use a different keyword and try again.', 'writetext-ai' ),
						'manualKeywordTooltipMessage'      => __( 'Add your own keyword here...', 'writetext-ai' ),
						'maxManualKeywordTooltipMessage'   => $max_keyword_tooltip_message,
					)
				);

				$popupblocker_nonce                  = wp_create_nonce( 'wtai-popupblocker-nonce' );
				$popup_blocker_notice_dismissed_list = wtai_get_popup_blocker_dismiss_state() ? '1' : '';

				wp_enqueue_script( 'wtai-admin-installed', WTAI_DIR_URL . 'assets/js/admin-installed.js', array( 'jquery', 'wtai-toolstipster', 'jquery-ui-datepicker', 'wtai-selectize', 'wp-tinymce', 'select2', 'wtai-admin-common-functions', 'wtai-admin-filter', 'wtai-admin-keywords' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				wp_localize_script(
					'wtai-admin-installed',
					'WTAI_OBJ',
					array(
						'ajax_url'                         => admin_url( 'admin-ajax.php' ),
						'access_denied'                    => __( 'Access denied', 'writetext-ai' ),
						/* translators: %s: Login url */
						'expire_token'                     => sprintf( __( 'API token has expired. Please <a href=\"%s\" target=\"_blank\">log in again</a>', 'writetext-ai' ), 'https://' . WTAI_AUTH_HOST . '/Account/Login?callback_url=' . rawurlencode( admin_url( 'admin.php?page=write-text-ai' ) ) ),
						'transfer_btn_label'               => __( 'Transfer to WordPress', 'writetext-ai' ),
						'loading'                          => __( 'Loading', 'writetext-ai' ),
						'bulk_generate'                    => __( 'Bulk generate', 'writetext-ai' ),
						'bulk_transfer'                    => __( 'Bulk transfer', 'writetext-ai' ),
						'attribute_guide'                  => __( 'Select the text types you want to transfer to WordPress.<br /><br />Transferring your text to WordPress will either save it as a draft or publish it on the website, depending on the current status of your product.', 'writetext-ai' ),
						'attribute_guide_generate'         => __( 'The selected attributes will be considered in generating product text. Note that selecting a product attribute is not a guarantee that it will appear in the text itself, but it will help guide WriteText.ai in generating more relevant text.', 'writetext-ai' ),
						'option_choices'                   => WTAI_MAX_CHOICE,
						'keyword_max'                      => isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD,
						/* translators: %char%: Character count placeholder */
						'char'                             => __( '%char% Char', 'writetext-ai' ),
						/* translators: %words%: Words count placeholder */
						'words'                            => __( '%words% word/s', 'writetext-ai' ),
						'LoadMoreHistory'                  => __( 'Load more', 'writetext-ai' ),
						'pageSize'                         => WTAI_MAX_HISTORY_PAGESIZE,
						'ok'                               => __( 'OK', 'writetext-ai' ),
						'cancel'                           => __( 'Cancel', 'writetext-ai' ),
						'confirm_leave'                    => __( 'You have unsaved changes. Are you sure you want to leave this page?', 'writetext-ai' ),
						'text_limit'                       => array(
							'page_title'       => WTAI_PAGE_TITLE_TEXT_LIMIT,
							'page_description' => WTAI_MAX_PAGE_DESCRIPTION_LIMIT,
							'open_graph'       => WTAI_MAX_OPEN_GRAPH_LIMIT,
							'image_alt_text'   => WTAI_MAX_IMAGE_ALT_TEXT_LIMIT,
						),
						'no_data'                          => __( 'No data', 'writetext-ai' ),
						'bulkGenerateDoneHeader'           => __( '<a href="https://writetext.ai/" target="_blank" >WriteText.ai</a> is done generating text for your selected products.', 'writetext-ai' ),
						'keyword_exist_msg'                => __( 'The entered keyword already exists.', 'writetext-ai' ),
						'keyword_ideas_msg'                => __( 'No keyword data received from the API. Check for misspellings in your keyword, check your country selection, or use a different keyword and try again.', 'writetext-ai' ),
						'tinymcelinktext1'                 => __( 'Insert/Edit link', 'writetext-ai' ),
						'tinymcelinktext2'                 => __( 'Insert', 'writetext-ai' ),
						'tinymcelinktext3'                 => __( 'Cancel', 'writetext-ai' ),
						'tinymcelinktext4'                 => __( 'Link text', 'writetext-ai' ),
						'tinymcelinktext5'                 => __( 'Link URL', 'writetext-ai' ),
						'tinymcelinktext6'                 => __( 'Open link in new window', 'writetext-ai' ),
						'keyword_ideas_stale_msg'          => __( 'Keyword data are refreshed at the start of every month. The data you’re seeing might have changed since you last requested it. Click the “Start AI-powered keyword analysis” button to refresh data.', 'writetext-ai' ),
						'current_user_id'                  => $current_user_id,
						'generate_temp_html'               => $loader_html_temp_markup['generate_temp_html'],
						'transfer_temp_html'               => $loader_html_temp_markup['transfer_temp_html'],
						'single_generate_temp_html'        => $loader_html_temp_markup['single_generate_temp_html'],
						'single_transfer_temp_html'        => $loader_html_temp_markup['single_transfer_temp_html'],
						'see_more_hide_html'               => $loader_html_temp_markup['see_more_hide_html'],
						'loadBackgroundJobs'               => '1',
						'bulkGeneratempOngoingText'        => __( 'Bulk actions ongoing', 'writetext-ai' ),
						'bulkGeneratempDoneText'           => __( 'Bulk actions completed', 'writetext-ai' ),
						'userCanTransfer'                  => $user_can_transfer,
						'creditCounts'                     => $credit_array,
						'userGenerateTextFields'           => $user_generate_text_fields,
						'creditLabelSingular'              => __( 'credit', 'writetext-ai' ),
						'creditLabelPlural'                => __( 'credits', 'writetext-ai' ),
						'bulkDirectTonesError'             => __( 'Default tone is not selected in the settings.', 'writetext-ai' ),
						'generateCompleteTextPopup'        => __( 'Text generation completed.', 'writetext-ai' ),
						'generateCompleteWithErrorTextPopup' => __( 'Some text has been generated but there has been an error generating other text. See error below.', 'writetext-ai' ) . '<div class="alt-image-notice" ></div><div class="wtai-generate-text-complete-sub" >' . __( 'Please remember to check the output for any factual mistakes or inaccuracies before you publish the text to your live site.', 'writetext-ai' ) . '</div>',
						'rewriteCompleteTextPopup'         => __( 'Text rewrite completed.', 'writetext-ai' ),
						'searchNoResult'                   => __( 'No result found.', 'writetext-ai' ),
						'high'                             => __( 'HIGH', 'writetext-ai' ),
						'low'                              => __( 'LOW', 'writetext-ai' ),
						'medium'                           => __( 'MEDIUM', 'writetext-ai' ),
						'generationLimitVars'              => $generation_limit_vars,
						'referenceCharCountHTML'           => $reference_char_count_html,
						'fieldTypeLabels'                  => $field_type_labels,
						/* translators: %fields%: wta text fields */
						'generateErrorTextPopup'           => __( 'Error encountered while generating the following text: %fields%.', 'writetext-ai' ),
						'disallowedCombinations'           => $disallowed_combinations,
						'formalInformalPronouns'           => $formal_informal_pronouns,
						'versionOutdated'                  => $version_outdated,
						'versionOutdatedMessage'           => $version_outdated_message,
						'formalLanguageSupport'            => $formal_language_support,
						'attentionTextString'              => __( 'Attention', 'writetext-ai' ),
						'generateCTAText'                  => __( 'Generate selected', 'writetext-ai' ),
						'rewriteCTAText'                   => __( 'Rewrite selected', 'writetext-ai' ),
						'generatedStatusText'              => '(' . __( 'generated', 'writetext-ai' ) . ')',
						'notGeneratedStatusText'           => '(' . __( 'not generated', 'writetext-ai' ) . ')',
						'tooltipDisableToneStyleMessage1'  => __( 'Tones and styles are unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableAudienceMessage1'   => __( 'Audience is unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableReferenceMessage2'  => __( 'Reference product is unavailable when doing rewrite.', 'writetext-ai' ),
						'tooltipDisableRewriteMessage1'    => __( 'Rewrite is unavailable when reference product is selected.', 'writetext-ai' ),
						'tooltipDisableRewriteMessage2'    => __( 'Rewrite is unavailable when no WordPress text is found.', 'writetext-ai' ),
						'tooltipActiveTransferSingle'      => __( 'Transfer', 'writetext-ai' ),
						'tooltipInactiveTransferSingle'    => __( 'Nothing to transfer / Already transferred', 'writetext-ai' ),
						'is_premium'                       => WTAI_PREMIUM ? '1' : '0',
						'current_user_can_generate'        => $current_user_can_generate,
						'translation_ongoing'              => WTAI_TRANSLATION_ONGOING ? '1' : '0',
						'translationOngoingMessage'        => 'Notice: Translation of plugin help text and labels is ongoing. Please stay tuned.', // This text is intentionally not translated as it is a notice for translators.
						'isCurrentLocaleEN'                => wtai_is_current_locale_en() ? '1' : '0',
						'WTAI_DIR_URL'                     => WTAI_DIR_URL,
						'extReviewlabel1'                  => __( 'For rewrite', 'writetext-ai' ),
						'extReviewlabel2'                  => __( 'For fact checking', 'writetext-ai' ),
						'noAltTextImageToGenerate'         => __( 'Image alt text generation failed because there are no images uploaded to the products selected. Upload images first before generating.', 'writetext-ai' ),
						'maxSemanticKeywordMessage'        => __( 'You have selected the maximum number of semantic keywords.', 'writetext-ai' ),
						'keywordTooManyRequestError'       => WTAI_KEYWORD_TIMEOUT_ERROR_MESSAGE,
						'generalErrorMessage'              => WTAI_GENERAL_ERROR_MESSAGE,
						'startKeywordAnalysisErrorMessage' => WTAI_KEYWORD_GENERAL_ERROR_MESSAGE,
						'startKeywordAnalysisMessage'      => __( 'Starting AI-powered keyword analysis...', 'writetext-ai' ),
						'finalKeywordAnalysisMessage'      => __( 'Getting results ready...', 'writetext-ai' ),
						'refreshingKeywordAnalysisMessage' => __( 'Refreshing data...', 'writetext-ai' ),
						'finalRefreshKeywordAnalysisMessage' => __( 'Getting results ready...', 'writetext-ai' ),
						'ongoingKeywordAnalysisMessage'    => __( 'Keyword analysis is already in progress...', 'writetext-ai' ),
						'productNameNotAllowedMsg'         => __( 'The product name is considered by default in generating text. Please add a different keyword.', 'writetext-ai' ),
						'productNameNotAllowedMsgFromPlus' => __( 'This keyword is the same as the product name and is already considered by default when generating text. Please add a different keyword.', 'writetext-ai' ),
						'keywordTrashDisabledTooltip'      => __( 'Remove this keyword from the "Keywords to be included in your text" before deleting it.', 'writetext-ai' ),
						'keywordPlusDisabledTooltip'       => $add_disabled_tooltip,
						'keywordPlusTooltip'               => __( 'Add as target keyword', 'writetext-ai' ),
						'keywordMinusTooltip'              => __( 'Remove as target keyword', 'writetext-ai' ),
						'keywordTrashTooltip'              => __( 'Delete keyword', 'writetext-ai' ),
						'emptyRankMessage'                 => __( 'Click the "Start AI-powered keyword analysis" button to get started.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyRankMessageWithAnalysis'     => __( 'This page is not ranking for any keywords as of %s. <br><br>You may click the "Start AI-powered keyword analysis" button to refresh ranking data for the whole domain. We recommend doing this after a month has passed since your last request — any less than that may not return any significant results.', 'writetext-ai' ),
						'emptyCompetitorMessage'           => __( 'Click the “Start AI-powered keyword analysis” button to get started. If there are no keywords you are currently ranking for or selected keywords to be included in your text, WriteText.ai will search for possible competitors you may have based on your product name.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyCompetitorMessageWithAnalysis' => __( 'No competitor keywords found as of %s. <br><br>Select or manually type other keywords and try again.', 'writetext-ai' ),
						'emptySuggestedMessage'            => __( 'Click the “Start AI-powered keyword analysis” button to get data for your manually typed keywords (keyword ideas, search volume, and difficulty).', 'writetext-ai' ),
						'emptySuggestedMessageWithAnalysis' => __( 'No keyword data received. Check for misspellings in your keyword(s) or use a different keyword and try again.', 'writetext-ai' ),
						'noHistoryMessage'                 => __( 'No log found.', 'writetext-ai' ),
						'disablePopupBlockerStatus'        => $popup_blocker_notice_dismissed_list,
						'popupblocker_nonce'               => $popupblocker_nonce,
						'disablePopupBlockerMessage'       => __( '<strong>Warning:</strong> Disable all pop-up blockers then refresh this page. WriteText.ai does not work when you have pop-up blockers enabled.', 'writetext-ai' ),
						'bulkCancellingText'               => __( 'Cancelling...', 'writetext-ai' ),
					)
				);

				if ( wtai_get_hide_guidelines_user_preference() ) {
					wp_add_inline_script(
						'wtai-admin-installed',
						'
							jQuery(document).ready(function($){
								$(".wtai-step-guideline").addClass("wtai-hide");
							});
					',
						'before'
					);
				}

				wp_register_style( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/css/tooltipster.bundle.min.css', array(), 'v4.2.8' );
				wp_enqueue_style( 'wtai-admin-installed', WTAI_DIR_URL . 'assets/css/admin-installed.css', array( 'wtai-toolstipster' ), $cache_buster_version );
			}
		} elseif ( isset( $_GET['page'] ) && 'write-text-ai-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$disallowed_combinations = apply_filters( 'wtai_get_disallowed_combinations', array(), false );

			wp_enqueue_style( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.default.css', array(), $cache_buster_version );
			wp_enqueue_script( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.min.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

			wp_register_style( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/css/tooltipster.bundle.min.css', array(), 'v4.2.8' );
			wp_enqueue_style( 'wtai-admin-installation', WTAI_DIR_URL . 'assets/css/admin-installation.css', array( 'wtai-toolstipster' ), $cache_buster_version );

			wp_register_script( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/js/tooltipster.bundle.min.js', array( 'jquery' ), 'v4.2.8' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_enqueue_script( 'wtai-admin-installation', WTAI_DIR_URL . 'assets/js/admin-setting.js', array( 'jquery', 'wtai-toolstipster' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_localize_script(
				'wtai-admin-installation',
				'WTAI_OBJ',
				array(
					'ajax_url'               => admin_url( 'admin-ajax.php' ),
					'disallowedCombinations' => $disallowed_combinations,
					'is_premium'             => WTAI_PREMIUM ? '1' : '0',
				)
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && ( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] ) ) {
			$disallowed_combinations = apply_filters( 'wtai_get_disallowed_combinations', array(), false );

			$free_premium_popup_html = wtai_get_fremium_popup_html();

			wp_enqueue_style( 'wtai-admin-common', WTAI_DIR_URL . 'assets/css/admin-common.css', array(), $cache_buster_version );
			wp_enqueue_script( 'wtai-admin-common', WTAI_DIR_URL . 'assets/js/admin-common.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_localize_script(
				'wtai-admin-common',
				'WTAI_COMMON_OBJ',
				array(
					'ajaxUrl'                => admin_url( 'admin-ajax.php' ),
					'disallowedCombinations' => $disallowed_combinations,
					'isPremium'              => WTAI_PREMIUM ? '1' : '0',
					'creditAccountDetails'   => WTAI_CREDIT_ACCOUNT_DETAILS ? WTAI_CREDIT_ACCOUNT_DETAILS : array(),
					'freePremiumPopupHtml'   => $free_premium_popup_html,
				)
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'write-text-ai' === $_GET['page'] && ! $is_doing_install ) {
			$product_country_lists            = $this->get_wtai_country_variables();
			$product_country_options          = $product_country_lists['product_country_options'];
			$product_country_options_selected = $product_country_lists['product_country_options_selected'];

			wp_add_inline_script( 'wtai-admin-common', 'window.WTAI_COUNTRY_OPTIONS = ' . wp_json_encode( $product_country_options ) . '; window.WTAI_COUNTRY_SELECTED = ' . wp_json_encode( $product_country_options_selected ) . ';', 'before' );
		}
	}

	/**
	 * Menu page.
	 */
	public function get_menu_page() {
		do_action( 'installation_checker' );

		$logo_file = 'ic-wt-platform-menu.svg';

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) &&
			( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
			$logo_file = 'ic-wt-platform-menu-active.svg';
		}

		$hook = add_menu_page(
			__( 'WriteText.ai', 'writetext-ai' ),
			__( 'WriteText.ai', 'writetext-ai' ),
			'read',
			'write-text-ai',
			array( $this, 'get_main_dashboard_callback' ),
			WTAI_DIR_URL . 'assets/images/' . $logo_file,
			51
		);

		if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && wtai_current_user_can( 'writeai_generate_text' ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
			add_submenu_page(
				'write-text-ai',
				__( 'WriteText.ai - Products', 'writetext-ai' ),
				__( 'Products', 'writetext-ai' ),
				'read',
				'write-text-ai',
				array( $this, 'get_main_dashboard_callback' ),
				52
			);
		}
	}

	/**
	 * Dashboard.
	 */
	public function get_main_dashboard_callback() {
		if ( false === wtai_is_allowed_beta_language() ) {
			return;
		}

		// Display installation if token is expired.
		if ( wtai_is_token_expired() ) {
			do_action( 'wtai_installation_render' );
			return;
		}

		// TODO: check api base url is valid or not.
		if ( ! wtai_has_api_base_url() ) {
			do_action( 'wtai_installation_render' );
			return;
		}

		if ( '1' === get_option( 'wtai_latest_version_outdated' ) && '1' === get_option( 'wtai_force_version_update' ) ) {
			$latest_version_message = get_option( 'wtai_latest_version_message' );
			?>
			<div class="wtai-update-notice notice notice-error is-dismissible" >
				<p><?php echo wp_kses( $latest_version_message, wtai_kses_allowed_html() ); ?></p>
			</div>
			<?php

			return;
		}

		if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) ) {
			if ( ! class_exists( 'WTAI_Product_List_Table' ) ) {
				require_once WTAI_ABSPATH . 'includes/class-wtai-product-list-table.php';
			}
			$wtai_product_list_table = new WTAI_Product_List_Table();
			$wtai_product_list_table->prepare_items();

			include_once WTAI_ABSPATH . 'templates/admin/dashboard.php';
		} else {
			do_action( 'wtai_installation_render' );
		}
	}

	/**
	 * Tooltip text.
	 */
	public function get_generated_tooltip_text_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$text = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$product_id   = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$obj_name_key = isset( $_POST['colgrp'] ) ? sanitize_text_field( wp_unslash( $_POST['colgrp'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				switch ( $obj_name_key ) {
					case 'page_title':
						$column_name = wtai_get_meta_key_source( 'title' );
						break;
					case 'page_description':
						$column_name = wtai_get_meta_key_source( 'desc' );
						break;
					case 'open_graph':
						$column_name = wtai_get_meta_key_source( 'opengraph' );
						break;
					default:
						$column_name = $obj_name_key;
						break;
				}
				$text = '';
				if ( 'page_title' === $obj_name_key ) {
					$text = wtai_yoast_seo_format_value( $product_id, $column_name );
				} elseif ( in_array( $obj_name_key, array( 'product_description', 'product_excerpt' ), true ) ) {
						$text = ( 'product_description' === $obj_name_key ) ? get_the_content( null, false, $product_id ) : get_the_excerpt( $product_id );
				} else {
					$text = wtai_yoast_seo_format_value( $product_id, $column_name );
				}

				$text = wpautop( nl2br( $text ) );
			} else {
				$text = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode( array( 'text' => $text ) );
			exit;
		}
	}

	/**
	 * Process other product details callback.
	 */
	public function process_otherproductdetails_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$account_credit_details = wtai_get_account_credit_details();
		$is_premium             = $account_credit_details['is_premium'];

		$access  = 0;
		$success = 0;
		$message = '';
		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( $is_premium ) {
					$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$value      = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					update_post_meta( $product_id, 'wtai_otherproductdetails', $value );
				}
				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$access = 1;
		}

		echo wp_json_encode(
			array(
				'access'  => $access,
				'success' => $success,
				'message' => $message,
			)
		);
		exit;
	}

	/**
	 * Process product review callback.
	 */
	public function process_product_review_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$access          = 0;
		$api_updated     = 0;
		$error_message   = '';
		$alt_api_results = array();
		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$alt_image_data = array();
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['alt_image_data'] ) ) {
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( is_array( $_POST['alt_image_data'] ) ) {
						$alt_image_data = map_deep( wp_unslash( $_POST['alt_image_data'] ), 'wp_kses_post' ); // phpcs:ignore WordPress.Security.NonceVerification
					} else {
						// phpcs:ignore WordPress.Security.NonceVerification
						$alt_image_data = wp_kses( wp_unslash( $_POST['alt_image_data'] ), 'post' );
					}
				}

				$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				// Check if has generated text.
				$fields = apply_filters( 'wtai_fields', array() );
				$fields = array_keys( $fields );
				// Call api.
				$api_fields = array(
					'fields'               => $fields,
					'includeUpdateHistory' => true,
					'historyCount'         => 1,
				);

				$api_results = apply_filters( 'wtai_generate_product_text', array(), $product_id, $api_fields );

				$has_generated_text = false;
				foreach ( $fields as $field ) {
					if ( isset( $api_results[ $product_id ][ $field ][0]['value'] ) || isset( $api_results[ $product_id ][ $field ][0]['history'][0] ) ) {
						$has_generated_text = true;
					}
				}

				$product_images = wtai_get_product_image( $product_id );
				// Get current api alt values.
				$api_alt_results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $product_images, true );

				if ( $api_alt_results && $product_images ) {
					foreach ( $product_images as $alt_image_id ) {
						if ( isset( $api_alt_results[ $alt_image_id ] ) ) {
							if ( isset( $api_alt_results[ $alt_image_id ]['altText']['value'] ) || isset( $api_alt_results[ $alt_image_id ]['altText']['history'][0] ) ) {
								$has_generated_text = true;
							}
						}
					}
				}

				if ( $has_generated_text ) {
					$reviewed = false;
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( isset( $_POST['value'] ) && '0' !== $_POST['value'] ) {
						$reviewed = true;
					}

					// Mark normal text fields as reviewed.
					$api_results    = apply_filters( 'wtai_record_product_reviewed_api', array(), $product_id, $reviewed, $browsertime );
					$review_success = false;
					if ( 200 === intval( $api_results['http_header'] ) && 1 === intval( $api_results['status'] ) ) {
						$review_success = true;
					}

					if ( $alt_image_data ) {
						$product_images = array();
						foreach ( $alt_image_data as $a_data ) {
							$product_images[] = $a_data['image_id'];
						}

						$api_alt_results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $product_images, true );

						if ( $api_alt_results ) {
							$payload_alt_image = array();
							foreach ( $api_alt_results as $alt_api_data ) {
								if ( $alt_api_data['imageId'] && $alt_api_data['altText'] ) {
									$alt_text    = $alt_api_data['altText'];
									$alt_publish = false;
									if ( isset( $alt_text['history'] ) && isset( $alt_text['history'][0] ) && 1 === intval( $alt_text['history'][0]['publish'] ) ) {
										$alt_publish = true;
									}

									$payload_alt_image[] = array(
										'image_id' => $alt_api_data['imageId'],
										'text_id'  => $alt_text['id'],
										'value'    => $alt_text['value'],
										'publish'  => $alt_publish,
									);
								}
							}

							$alt_api_results = apply_filters( 'wtai_record_alt_image_id_reviewed_api', array(), $product_id, $payload_alt_image, $reviewed );

							if ( $alt_api_results ) {
								$review_success = true;
							}
						}
					}

					if ( $review_success ) {
						if ( $reviewed ) {
							update_post_meta( $product_id, 'wtai_review', 1 );
						} else {
							delete_post_meta( $product_id, 'wtai_review' );
						}

						$api_updated = 1;
					} else {
						$error_message = __( 'There is an error encountered while saving review status to the API. Please try again later.', 'writetext-ai' );

						delete_post_meta( $product_id, 'wtai_review' );
					}
				} else {
					$error_message = __( "You haven't generated any text yet.", 'writetext-ai' );
				}
			} else {
				$error_message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$access = 1;
		}

		echo wp_json_encode(
			array(
				'access'            => $access,
				'api_updated'       => $api_updated,
				'error_message'     => $error_message,
				'api_results'       => $api_results,
				'reviewed'          => $reviewed,
				'alt_image_data'    => $alt_image_data,
				'alt_api_results'   => $alt_api_results,
				'payload_alt_image' => $payload_alt_image,
			)
		);

		exit;
	}

	/**
	 * Get edit product html.
	 *
	 * @param string $width Width.
	 */
	public function get_edit_product_form( $width = '' ) {
		?>
		<div class="wtai-slide-right-text-wrapper wtai-main-wrapper" wrapperwidth="">
			<div class="wtai-top-header header-slider">
				<div class="wtai-inner-flex">
					<span class="wtai-ai-logo"><img class="wtai-logo" src="<?php echo esc_url( WTAI_DIR_URL . 'assets/images/logo_writetext.svg' ); ?>" alt="logo"></span>
					<span class="wtai-global-loader" style="display:none;"></span>

					<span class="wtai-link-preview" onclick="wtaiGetLinkPreview(this)"  >
						<span class="dashicons wtai-dashicons-backup"></span>
						<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'Link preview', 'writetext-ai' ) ); ?></span>
					</span>
					<span class="wtai-history-single-btn" onclick="wtaiGetHistoryPopin(this)"  >
						<span class="dashicons wtai-dashicons-backup"></span>
						<span class="wtai-hist-text-log"><?php echo wp_kses_post( __( 'History log', 'writetext-ai' ) ); ?></span>
					</span>
					<span class="wtai-close wtai-pending dashicons dashicons-no-alt"></span>
					<button class="wtai-btn-close-history"><span class="dashicons dashicons-no-alt"></span></button>
					<button class="wtai-btn-close-keyword"><span class="dashicons dashicons-no-alt"></span></button>
				</div>
			</div>
			
			<div class="wtai-content">
				<?php
					$columns                = $this->get_fields_list();
					$global_rule_fields     = apply_filters( 'wtai_global_rule_fields', array() );
					$attributes             = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
					$wtai_preselected_types = wtai_get_user_preselected_types();

					include_once WTAI_ABSPATH . 'templates/admin/post.php';

					do_action( 'wtai_product_single_main_footer' );
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Get bulk generate loader html.
	 */
	public function get_bulk_generate_loader() {
		$jobs = wtai_get_bulk_generate_jobs( true );

		$html                      = '';
		$loader_estimate_container = '';
		$has_ongoing               = false;
		$has_error                 = false;
		$ok_ctr                    = 0;
		$job_completed_data        = array();
		if ( $jobs ) {
			$loader_estimate_container = 'style="display:block;"';

			$output                      = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, false );
			$html                        = $output['html'];
			$job_completed_data          = $output['job_completed_data'];
			$has_error                   = $output['has_error'];
			$has_ongoing_generation_jobs = intval( $output['has_ongoing_generation_jobs'] );
			$has_ongoing_transfer_jobs   = intval( $output['has_ongoing_transfer_jobs'] );
			$ok_ctr                      = intval( $output['ok_ctr'] );

			if ( $has_ongoing_generation_jobs || $has_ongoing_transfer_jobs ) {
				$has_ongoing = true;
			}
		}

		$loading_ico_class = 'wtai-done';
		if ( $has_ongoing ) {
			$loading_ico_class = 'wtai-ongoing';
		}
		if ( $has_error ) {
			$loading_ico_class = 'wtai-bulk-error';
		}

		$display_ok_all_button_class = 'hidden';
		if ( count( $job_completed_data ) > 1 ) {
			$display_ok_all_button_class = '';
		}

		if ( $ok_ctr > 1 ) {
			$display_ok_all_button_class = '';
		}

		?>
		<div id="wtai-loader-estimated-time" 
			<?php echo esc_attr( $loader_estimate_container ); ?> 
			class="wtai-loader-generate" >
			<div class="wtai-bulk-minimized-wrapper" >
				<div class="wtai-bulk-minimized-left-wrap" >
					<div class="wtai-bulk-minimized-label wtai-d-flex <?php echo esc_attr( $loading_ico_class ); ?>">
						<?php
						if ( $has_ongoing ) {
							?>
							<div class="wtai-bulk-generate-check-ico-wrap">
								<span class="wtai-bulkgenerate-check-ico" ></span>
							</div>
							<div class="wtai-bulk-generate-check-label-wrap">
								<?php echo wp_kses_post( __( 'Bulk actions ongoing', 'writetext-ai' ) ); ?>
							</div>
							<?php
						} else {
							?>
							<div class="wtai-bulk-generate-check-ico-wrap">
								<span class="wtai-bulkgenerate-check-ico" ></span>
							</div>
							<div class="wtai-bulk-generate-check-label-wrap">
								<?php echo wp_kses_post( __( 'Bulk actions completed', 'writetext-ai' ) ); ?>
							</div>
						<?php } ?>
					</div>
				</div>
				<div class="wtai-bulk-minimized-right-wrap" >
					<div class="wtai-loading-actions-show-wrap" >
						<a href="#" class="wtai-loading-actions-show-hide-cta wtai-show" data-type="show" ><?php echo wp_kses_post( __( 'View', 'writetext-ai' ) ); ?></a>
					</div>
				</div>
			</div>

			<div class="wtai-bulk-popup-wrapper hidden">
				<div class="wtai-ok-all-wrap <?php echo esc_attr( $display_ok_all_button_class ); ?>" >
					<a href="#" class="wtai-action-bulk-ok-all" ><?php echo wp_kses_post( __( 'Dismiss completed processes', 'writetext-ai' ) ); ?></a>
				</div>

				<div class="wtai-job-list-wrapper">
					<?php echo wp_kses( $html, wtai_kses_allowed_html() ); ?>
				</div>

				<div class="wtai-bulk-bottom-info-wrap" >
					<div class="wtai-loading-loader-message" >
							<?php
							echo wp_kses_post( __( 'These processes run in the background. You can safely navigate away from this page while these are in progress, but you cannot edit the products where bulk actions are currently being applied.', 'writetext-ai' ) );
							?>
							<?php
							echo wp_kses_post( __( 'Cancellation is dependent on the server response; it may be disabled upon initial processing or when there are only 2 products or less left in your queue.', 'writetext-ai' ) );
							?>
					</div>
					<div class="wtai-see-more-less-wrapper">
						<div class="wtai-loading-actions-hide-wrap" >
							<a href="#" class="wtai-loading-actions-show-hide-cta wtai-less" data-type="hide" ><?php echo wp_kses_post( __( 'Hide', 'writetext-ai' ) ); ?></a>
						</div>
					</div>
				</div>
			</div>
			
			<div id="wtai-product-generate-completed-bulk" class="wtai-loader-generate-bulk wtai-product-generate-completed-popup">
				<div class="wtai-loading-completed-container wtai-d-flex">
					<div class="wtai-loading-details-container">
						<div class="wtai-loading-wtai-header-wrapper">
							<div class="wtai-loading-header-details">
								<span class="wtai-notif-label" >
									<?php echo wp_kses_post( __( 'Text generation completed', 'writetext-ai' ) ); ?>
								</span>

								<div class="wtai-notif-error-wrap wtai-error-message-container" >
									<div class="wtai-notif-error-headline" >
										<?php echo wp_kses_post( __( 'Error encountered while generating the following text:', 'writetext-ai' ) ); ?>
									</div>
									<div class="wtai-notif-error-text-fields" >
										<ul>
										<?php
										$field_type_labels = wtai_get_field_type_labels();
										foreach ( $field_type_labels as $field_type => $field_label ) {
											?>
											<li class="wtai-notif-error-text-field wtai-notif-error-text-field-<?php echo esc_attr( $field_type ); ?>" ><?php echo wp_kses_post( $field_label ); ?></li>
											<?php
										}
										?>
										</ul>
									</div>
									<div class="wtai-notif-error-altimage-fields" >
										
									</div>
								</div>

								<div class="wtai-generate-text-complete-sub" >
									<?php echo wp_kses_post( __( 'Please remember to check the output for any factual mistakes or inaccuracies before you publish the text to your live site.', 'writetext-ai' ) ); ?>
								</div>
							</div>
						</div>

						<div class="wtai-loading-action-wrapper">
							<div class="wtai-loading-button-action">
								<span><?php echo wp_kses_post( __( 'OK', 'writetext-ai' ) ); ?></span>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get bulk transfer loader.
	 */
	public function get_bulk_transfer_loader() {
		?>
		<div id="wtai-transfer-estimated-time" class="wtai-loader-generate">
			<div class="wtai-loading-estimate-time-container">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'Transferring text', 'writetext-ai' ) ); ?>...</span>&nbsp;<span class="wtai-estimated-time"></span></div>
						<div class="wtai-loading-header-number"><span></span> <?php echo wp_kses_post( __( 'product/s', 'writetext-ai' ) ); ?></div>
					</div>
					<div class="wtai-loading-loader-wrapper">
						<div class="wtai-main-loading"></div>
					</div>
				</div>
				<div class="wtai-loading-actions-container" >
					<a href="#" class="button wtai-action-bulk-transfer-cancel" ><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></a>
					<a href="#" class="button wtai-action-bulk-transfer" style="display:none;" ><?php echo wp_kses_post( __( 'OK', 'writetext-ai' ) ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get product edit cancel and exit.
	 */
	public function get_product_edit_cancel_and_exit() {
		?>
		<div id="wtai-product-edit-cancel" class="wtai-loader-generate">
			<div class="wtai-loading-edit-cancel-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'You have unsaved changes. Are you sure you want to leave this page?', 'writetext-ai' ) ); ?></div>
					</div>
				</div>
				<div class="wtai-loading-actions-container wtai-d-flex">
					<span class="button wtai-exit-edit-leave button-primary"><?php echo wp_kses_post( __( 'Leave', 'writetext-ai' ) ); ?></span>&nbsp;<span class="button exit-edit-cancel"><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get product generate cancel.
	 */
	public function get_product_generate_cancel() {
		?>
		<div class="wtai-product-generate-forced wtai-loader-generate">
			<div class="wtai-loading-edit-cancel-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'You have unsaved changes. Are you sure you want to regenerate and replace text?', 'writetext-ai' ) ); ?></div>
					</div>
				</div>
				<div class="wtai-loading-actions-container wtai-d-flex">
					<span class="button wtai-product-generate-proceed button-primary"><?php echo wp_kses_post( __( 'Generate', 'writetext-ai' ) ); ?></span>&nbsp;<span class="button wtai-product-generate-cancel"><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></span>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get product generate cancel all.
	 */
	public function get_product_generate_cancel_all() {
		?>
		<div id="wtai-product-generate-forced" class="wtai-loader-generate">
			<div class="wtai-loading-edit-cancel-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'You have unsaved changes. Are you sure you want to regenerate and replace text?', 'writetext-ai' ) ); ?></div>
					</div>
				</div>
				<div class="wtai-loading-actions-container wtai-d-flex">
					<span class="button wtai-product-generate-proceed button-primary"><?php echo wp_kses_post( __( 'Generate', 'writetext-ai' ) ); ?></span>&nbsp;<span class="button wtai-product-generate-cancel"><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></span>
				</div>
			</div>
		</div>
		<div id="wtai-product-generate-completed" class="wtai-loader-generate product-generate-completed-popup">
			<div class="wtai-loading-completed-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details">
							<span class="wtai-notif-label" >
								<?php echo wp_kses_post( __( 'Text generation completed', 'writetext-ai' ) ); ?>
							</span>

							<div class="wtai-notif-error-wrap wtai-error-message-container" >
								<div class="wtai-notif-error-headline" >
									<?php
									if ( 'category' === wtai_get_current_page_type() ) {
										echo wp_kses_post( __( 'The following image/s are invalid:', 'writetext-ai' ) );
									} else {
										echo wp_kses_post( __( 'Error encountered while generating the following text:', 'writetext-ai' ) );
									}
									?>
								</div>
								<?php if ( 'product' === wtai_get_current_page_type() ) { ?>
									<div class="wtai-notif-error-text-fields" >
										<ul>
										<?php
										$field_type_labels = wtai_get_field_type_labels();
										foreach ( $field_type_labels as $field_type => $field_label ) {
											?>
											<li class="wtai-notif-error-text-field wtai-notif-error-text-field-<?php echo esc_attr( $field_type ); ?>" ><?php echo wp_kses_post( $field_label ); ?></li>
											<?php
										}
										?>
										</ul>
									</div>
								<?php } ?>
								<div class="wtai-notif-error-altimage-fields" >
									
								</div>
							</div>

							<div class="wtai-generate-text-complete-sub" >
								<?php echo wp_kses_post( __( 'Please remember to check the output for any factual mistakes or inaccuracies before you publish the text to your live site.', 'writetext-ai' ) ); ?>
							</div>
						</div>
					</div>

					<div class="wtai-loading-action-wrapper">
						<div class="wtai-loading-button-action">
							<span><?php echo wp_kses_post( __( 'OK', 'writetext-ai' ) ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get product data callback.
	 */
	public function get_product_data_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success     = 0;
			$message     = '';
			$post_return = array();
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$product_id                       = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$post_return['productnamesuffix'] = get_post_meta( $product_id, 'wtai_productnamesuffix', true );

				$product_name = get_the_title( $product_id );

				$keyword_ideas = array();

				$keywords_data = apply_filters( 'wtai_keyword_values', array(), $product_id, 'input', false );

				$keyword_input          = array();
				$product_title_semantic = array();
				$k_ctr                  = 0;
				foreach ( $keywords_data as $k_data ) {
					if ( 0 === $k_ctr ) {
						$semantics_pt          = array();
						$semantics_selected_pt = array();
						foreach ( $k_data['semantic'] as $sa_data ) {
							$semantics_pt[] = $sa_data['name'];

							if ( 1 === $sa_data['active'] ) {
								$semantics_selected_pt[] = $sa_data['name'];
							}
						}
						$product_title_semantic = array(
							'selected' => $semantics_selected_pt,
							'values'   => $semantics_pt,
						);
					} else {
						$keyword_input[] = $k_data;
					}
					++$k_ctr;
				}

				$post_return['keywords']               = array_merge( $keyword_input, $keyword_ideas );
				$post_return['keywords_input']         = $keyword_input;
				$post_return['keywords_ideas']         = $keyword_ideas;
				$post_return['product_title_semantic'] = $product_title_semantic;

				$wtai_review                = get_post_meta( $product_id, 'wtai_review', true );
				$post_return['wtai_review'] = $wtai_review ? 1 : 0;

				$wtai_highlight                = wtai_get_user_highlight_cb();
				$post_return['wtai_highlight'] = $wtai_highlight ? 1 : 0;

				$post_return['post_permalink'] = get_permalink( $product_id );
				$post_return['post_title']     = get_the_title( $product_id );

				$product_sku                = get_post_meta( $product_id, '_sku', true );
				$post_return['product_sku'] = $product_sku;

				$locale          = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), $product_id );
				$locale          = str_replace( '-', '_', $locale );
				$locale_array    = explode( '_', $locale );
				$locale_language = isset( $locale_array[0] ) ? $locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

				// Get language country code.
				$localized_country = wtai_get_site_localized_countries();

				$post_return['locale']   = $locale;
				$post_return['language'] = $locale_language;

				$product_countries = apply_filters( 'wtai_keywordanalysis_location', array() );

				// Sort by value alphabetically.
				uasort(
					$product_countries,
					function ( $a, $b ) {
						return strnatcmp( $a['name'], $b['name'] );
					}
				);

				$country_name             = '';
				$product_countries_sorted = array();
				$location_code            = '';
				foreach ( $product_countries as $product_country_id => $product_country ) {
					$product_country['product_country_id'] = $product_country_id;

					$product_countries_sorted[] = $product_country;

					if ( $localized_country && strtolower( $product_country['code'] ) === strtolower( $localized_country[0] ) ) {
						$country_name  = $product_country['name'];
						$location_code = $product_country['product_country_id'];
					}
				}

				$post_return['country']      = $localized_country ? $localized_country[0] : '';
				$post_return['country_name'] = $country_name;

				$post_return['product_country']          = $product_countries;
				$post_return['product_countries_sorted'] = $product_countries_sorted;

				$locale = str_replace( '_formal', '', $post_return['locale'] );
				$locale = str_replace( '_informal', '', $locale );
				$locale = str_replace( '-formal', '', $locale );
				$locale = str_replace( '-informal', '', $locale );
				$locale = str_replace( '-ao90', '', $locale );

				$locale_clean = str_replace( '_', '-', $locale );

				$post_return['product_country_selected'] = $locale_clean;
				$post_return['product_country_selected'] = explode( '-', $post_return['product_country_selected'] );
				$post_return['product_country_selected'] = end( $post_return['product_country_selected'] );

				$product_selected_location_code = wtai_get_product_location_code( $product_id );
				if ( $product_selected_location_code ) {
					$product_selected_location_code1 = $product_countries[ $product_selected_location_code ];
					if ( $product_selected_location_code1 ) {
						$post_return['product_country_selected'] = $product_selected_location_code1['code'];
					}
				}

				$post_return['product_selected_location_code'] = $product_selected_location_code;

				$post_return['language_formal_field'] = array();

				// Suggested audience.
				$sa_keywords = array();
				foreach ( $keyword_input as $keyword_input_data ) {
					$sa_keywords[] = $keyword_input_data['name'];
				}

				$sa_values = array(
					'keywords' => $sa_keywords,
					'type'     => 'Product',
				);

				$suggested_audiences               = apply_filters( 'wtai_get_suggested_audiences_text', array(), $product_id, $sa_values, 0 );
				$post_return['suggested_audience'] = $suggested_audiences;

				$volume_filter_selected      = get_post_meta( $product_id, 'wtai_keyword_ideas_volume_filter', true );
				$difficulty_filter_selected  = get_post_meta( $product_id, 'wtai_keyword_ideas_difficulty_filter', true );
				$keyword_ideas_sort_selected = get_post_meta( $product_id, 'wtai_keyword_ideas_sort', true );
				$keyword_ideas_sorting       = get_post_meta( $product_id, 'wtai_keyword_ideas_sorting', true );

				$account_credit_details = wtai_get_account_credit_details();
				$is_premium             = $account_credit_details['is_premium'];

				$post_return['volumeFilterSelected']     = $volume_filter_selected;
				$post_return['difficultyFilterSelected'] = $difficulty_filter_selected;
				$post_return['keywordIdeasSortSelected'] = $keyword_ideas_sort_selected;
				$post_return['keywordIdeasSorting']      = $keyword_ideas_sorting;
				$post_return['is_premium']               = $is_premium ? '1' : '0';

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'result'  => $post_return,
					'success' => $success,
					'message' => $message,
				)
			);
			exit;
		}
	}

	/**
	 * Bulk generate success callback.
	 */
	public function get_generate_bulk_success_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax              = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$is_own_request       = 0;
		$success              = 0;
		$message              = '';
		$html                 = '';
		$all_pending_ids      = array();
		$finished_product_ids = array();
		$jobs                 = array();
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$request_id = isset( $_POST['requestID'] ) ? sanitize_text_field( wp_unslash( $_POST['requestID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$clear_response = wtai_clear_user_bulk_generation( $request_id );
				$is_own_request = $clear_response['is_own_request'];
				$is_own_request = $is_own_request ? 1 : 0;

				$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === $_POST['show_hidden'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs        = wtai_get_bulk_generate_jobs( true );

				$output = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, $show_hidden );

				$html = $output['html'];

				if ( $html ) {
					$all_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success'              => 1,
					'html'                 => $html,
					'all_pending_ids'      => $all_pending_ids,
					'is_own_request'       => $is_own_request,
					'finished_product_ids' => $finished_product_ids,
					'jobs'                 => $jobs,
				)
			);
			exit;
		}
	}

	/**
	 * Bulk generate cancel callback.
	 */
	public function get_generate_bulk_cancel_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax     = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results     = array();
		$api_results = array();
		$message     = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$request_id = isset( $_POST['requestID'] ) ? sanitize_text_field( wp_unslash( $_POST['requestID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$html       = '';
				if ( $request_id ) {
					$api_results = apply_filters( 'wtai_generate_product_bulk_cancel', '', $request_id );

					if ( 1 === $api_results ) {
						$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === $_POST['show_hidden'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$jobs        = wtai_get_bulk_generate_jobs( true );

						$output = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, $show_hidden );

						$html = $output['html'];
					}
				}

				if ( $html ) {
					$all_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'sucess'          => $api_results,
					'html'            => $html,
					'all_pending_ids' => $all_pending_ids,
					'message'         => $message,
				)
			);

			exit;
		}
	}

	/**
	 * Bulk transfer success callback.
	 */
	public function get_transfer_bulk_success_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$is_own_request  = 0;
			$success         = 0;
			$message         = '';
			$html            = '';
			$all_pending_ids = array();
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$clear_response = wtai_clear_user_bulk_transfer();
				$is_own_request = $clear_response['is_own_request'];
				$is_own_request = $is_own_request ? 1 : 0;

				$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === $_POST['show_hidden'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs        = wtai_get_bulk_generate_jobs( true );

				$output = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, $show_hidden );

				$html = $output['html'];

				if ( $html ) {
					$all_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success'         => $success,
					'message'         => $message,
					'html'            => $html,
					'all_pending_ids' => $all_pending_ids,
					'is_own_request'  => $is_own_request,
				)
			);
			exit;
		}
	}

	/**
	 * Bulk transfer callback.
	 */
	public function get_transfer_bulk_cancel_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success = 0;
			$message = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				wtai_clear_user_bulk_transfer();

				$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === $_POST['show_hidden'] ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs        = wtai_get_bulk_generate_jobs( true );

				$output = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, $show_hidden );

				$html = $output['html'];

				if ( $html ) {
					$all_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success'         => $success,
					'message'         => $message,
					'html'            => $html,
					'all_pending_ids' => $all_pending_ids,
				)
			);

			exit;
		}
	}

	/**
	 * Get product field data
	 */
	public function get_product_field_data_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$result  = array();
			$message = '';
			$success = 0;
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

				$refresh_credits = isset( $_POST['refresh_credits'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['refresh_credits'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$refresh_credits_bool = false;
				if ( 1 === $refresh_credits ) {
					$refresh_credits_bool = true;
				}

				$account_credit_details = wtai_get_account_credit_details( $refresh_credits_bool );
				$is_premium             = $account_credit_details['is_premium'];
				$available_credit_count = $credit_account_details['available_credits'];
				$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

				$product_id = isset( $_POST['product_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$field      = isset( $_POST['field'] ) ? sanitize_text_field( wp_unslash( $_POST['field'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $field ) {
					$fields = array( $field );
				} else {
					$fields = apply_filters( 'wtai_fields', array() );
					$fields = array_keys( $fields );
				}

				$fields = apply_filters( 'wtai_fields', array() );
				$fields = array_keys( $fields );

				$field_force = isset( $_POST['fieldForce'] ) ? sanitize_text_field( wp_unslash( $_POST['fieldForce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				if ( $field_force ) {
					$fields = array( $field_force );
				}

				// Call api.
				$api_fields = array(
					'fields'               => $fields,
					'includeUpdateHistory' => true,
					'historyCount'         => 1,
				);

				$api_results = apply_filters( 'wtai_generate_product_text', array(), $product_id, $api_fields );
				$post        = get_post( $product_id );

				$has_generated_text              = 0;
				$has_platform_text               = 0;
				$has_reviewed_text               = 0;
				$has_generated_not_reviewed_text = 0;
				$has_transferred_text            = 0;
				$generated_ctr                   = 0;
				$transferred_ctr                 = 0;
				$reviewed_ctr                    = 0;
				$has_missing_extension_review    = 0;
				foreach ( $fields as $field ) {
					$product = wtai_get_meta_values( $product_id, array( $field ) );
					if ( 'private' === $post->post_status ) {
						$post->post_password = '';
						$visibility          = 'private';
						$visibility_trans    = __( 'Private' );
					} elseif ( ! empty( $post->post_password ) ) {
						$visibility       = 'password';
						$visibility_trans = __( 'Password protected' );
					} elseif ( 'post' === $post->post_type && is_sticky( $post_id ) ) {
						$visibility       = 'public';
						$visibility_trans = __( 'Public, Sticky' );
					} else {
						$visibility       = 'public';
						$visibility_trans = __( 'Public' );
					}
					$result['post_visibility'] = esc_html( $visibility_trans );

					$prod = wc_get_product( $product_id );
					if ( 'password' === $visibility && 'product_description' === $field ) {
						$product_content             = $prod->get_description();
						$result[ $field . '_value' ] = isset( $product[ $field ] ) ? $product_content : '';
					} elseif ( 'password' === $visibility && 'product_excerpt' === $field ) {
						$product_excerpt             = $prod->get_short_description();
						$result[ $field . '_value' ] = isset( $product[ $field ] ) ? $product_excerpt : '';
					} else {
						$result[ $field . '_value' ] = isset( $product[ $field ] ) ? $product[ $field ] : '';
					}

					$result[ $field . '_value_string_count' ] = $result[ $field . '_value' ] ? count_chars( $result[ $field . '_value' ] ) : 0;
					$result[ $field . '_value_words_count' ]  = $result[ $field . '_value' ] ? str_word_count( $result[ $field . '_value' ] ) : 0;

					$result[ $field . '_options' ] = ( $api_results[ $product_id ][ $field ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return str_replace( '\\', '', $option );
						},
						$api_results[ $product_id ][ $field ][0]['outputs']
					) : array();

					$result[ $field . '_options_string_count' ] = ( $api_results[ $product_id ][ $field ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return strlen( $option );
						},
						$api_results[ $product_id ][ $field ][0]['outputs']
					) : array();
					$result[ $field . '_options_words_count' ]  = ( $api_results[ $product_id ][ $field ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return str_word_count( $option );
						},
						$api_results[ $product_id ][ $field ][0]['outputs']
					) : array();

					$result[ $field . '_id' ] = ( $api_results[ $product_id ][ $field ][0]['id'] ) ? $api_results[ $product_id ][ $field ][0]['id'] : '';

					$result_api_field_value = ( $api_results[ $product_id ][ $field ][0]['value'] ) ? str_replace( '\\', '', $api_results[ $product_id ][ $field ][0]['value'] ) : '';

					// Trim newlines, spaces, and non-breaking spaces from the end.
					$result_api_field_value = wtai_remove_trailing_new_lines( $result_api_field_value );

					if ( 'product_description' === $field || 'product_excerpt' === $field ) {
						$result[ $field ] = wtai_remove_trailing_new_lines( wpautop( $result_api_field_value ) );
					} else {
						$result[ $field ] = wtai_remove_trailing_new_lines( wpautop( $result_api_field_value ) );
					}

					$platform_field_value = $result[ $field . '_value' ];
					$platform_field_value = preg_replace( '/<([^>]*(<|$))/', '&lt;$1', $platform_field_value );
					$platform_field_value = html_entity_decode( wp_strip_all_tags( $platform_field_value ), ENT_COMPAT | ENT_HTML5, 'UTF-8' );

					$generated_field_value = $result[ $field ];
					$generated_field_value = preg_replace( '/<([^>]*(<|$))/', '&lt;$1', $generated_field_value );
					$generated_field_value = html_entity_decode( wp_strip_all_tags( $generated_field_value ), ENT_COMPAT | ENT_HTML5, 'UTF-8' );

					// Platform value word and string count.
					$result[ $field . '_platform_words_count' ]             = wtai_word_count( wp_strip_all_tags( $platform_field_value ) );
					$result[ $field . '_platform_string_count' ]            = mb_strlen( wp_strip_all_tags( $platform_field_value ), 'UTF-8' );
					$result[ $field . '_platform_string_count_for_credit' ] = mb_strlen( $result[ $field . '_value' ], 'UTF-8' );

					$current_wp_value = $result[ $field . '_value' ];

					// Trim newlines, spaces, and non-breaking spaces from the end.
					$current_wp_value = wtai_remove_trailing_new_lines( $current_wp_value );

					if ( isset( $result[ $field . '_value' ] ) && ( 'product_description' === $field || 'product_excerpt' === $field ) ) {
						$result[ $field . '_value' ] = wtai_remove_trailing_new_lines( wpautop( $current_wp_value ) );
					} else {
						$result[ $field . '_value' ] = wtai_remove_trailing_new_lines( wpautop( nl2br( $current_wp_value ) ) );
					}

					$result[ $field . '_trimmed' ] = wp_trim_words( $result[ $field ], 15 );

					$result[ $field . '_string_count' ] = ( $api_results[ $product_id ][ $field ][0]['value'] ) ? mb_strlen( $api_results[ $product_id ][ $field ][0]['value'], 'UTF-8' ) : 0;

					$result[ $field . '_words_count' ]         = 0;
					$result[ $field . '_keyword_input' ]       = 0;
					$result[ $field . '_keyword_match_found' ] = 0;
					$result[ $field . '_keyword_total_word' ]  = 0;

					$result[ $field . '_semantic_keyword_input' ] = 0;
					$option_key_value                             = -1;
					foreach ( $result[ $field . '_options' ] as $option_key => $option_value ) {
						if ( $option_value === $result[ $field ] ) {
							$option_key_value = $option_key;
							break;
						}
					}
					$result[ $field . '_option_key' ] = $option_key_value;

					$raw_field_value = trim( wp_strip_all_tags( $result[ $field ] ) );

					$has_field_generated_text = false;
					if ( '' !== $raw_field_value ) {
						$has_generated_text       = 1;
						$has_field_generated_text = true;
						++$generated_ctr;
					}

					$raw_platform_value = trim( wp_strip_all_tags( $result[ $field . '_value' ] ) );
					if ( '' !== $raw_platform_value ) {
						$has_platform_text = 1;
					}

					$field_published = 0;
					$field_reviewed  = 0;
					if ( isset( $api_results[ $product_id ][ $field ][0]['history'][0] ) ) {
						$field_published = $api_results[ $product_id ][ $field ][0]['history'][0]['publish'];
						$field_reviewed  = $api_results[ $product_id ][ $field ][0]['history'][0]['reviewed'];
					}

					if ( $has_field_generated_text && $field_reviewed ) {
						$has_reviewed_text = 1;
						++$reviewed_ctr;
					}

					if ( $has_field_generated_text && ! $field_reviewed ) {
						$has_generated_not_reviewed_text = 1;
					}

					if ( $field_published ) {
						$has_transferred_text = 1;
						++$transferred_ctr;
					}

					$result[ $field . '_published' ] = $field_published;
					$result[ $field . '_reviewed' ]  = $field_reviewed;

					$last_activity = '';
					if ( $field_published ) {
						$last_activity = 'transfer';
					} elseif ( $field_reviewed ) {
							$last_activity = 'review';
					} elseif ( $has_field_generated_text && wp_strip_all_tags( $raw_field_value ) !== wp_strip_all_tags( $raw_platform_value ) ) {
						$last_activity = 'generate';
					}

					$result[ $field . '_last_activity' ] = $last_activity;

					$extension_reviews = $api_results[ $product_id ][ $field ]['reviews'];

					$extension_reviews_output = array();
					if ( $extension_reviews ) {
						$extension_reviews_output = wtai_get_extension_review_popup_html( $product_id, $field, $extension_reviews );
					} else {
						$has_missing_extension_review = 1;
					}

					$result[ $field . '_extension_reviews_html' ] = $extension_reviews_output;
				}

				if ( $has_missing_extension_review ) {
					$extension_reviews_response = apply_filters( 'wtai_get_product_extension_review', array(), $product_id );

					if ( $extension_reviews_response && isset( $extension_reviews_response['reviews'] ) ) {
						$extension_reviews = $extension_reviews_response['reviews'];

						if ( $extension_reviews ) {
							foreach ( $fields as $field ) {
								$extension_reviews_output = wtai_get_extension_review_popup_html( $product_id, $field, $extension_reviews );

								$result[ $field . '_extension_reviews_html' ] = $extension_reviews_output;
							}
						}
					}
				}

				$result['product_last_activity'] = wtai_get_product_last_activity( $product_id );

				// Other product details.
				$result['wp_product_title']    = get_the_title( $product_id );
				$result['otherproductdetails'] = get_post_meta( $product_id, 'wtai_otherproductdetails', true );
				$product_attr                  = wtai_get_product_attr( $product_id );
				$attributes                    = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
				ob_start();
				foreach ( $product_attr as $product_attr_key => $product_attr_value ) :
					$key_input = strtolower( str_replace( ' ', '-', $product_attr_key ) );
					if ( 'attributes' === $product_attr_key ) :
						foreach ( $product_attr_value as $custom_attr ) :
							$key_input = 'attr-' . strtolower( str_replace( ' ', '-', $custom_attr['name'] ) );
							?>
								<li><input type="checkbox" class="wtai-attr-checkboxes" name="attribute[]" data-apiname="<?php echo esc_attr( $key_input ); ?>" <?php echo ( is_array( $attributes ) && in_array( $key_input, $attributes, true ) ) ? 'checked' : ''; ?> value="<?php echo esc_attr( wp_unslash( $key_input ) ); ?>" /><label class="wtai-details"><strong><?php echo is_array( $custom_attr['name'] ) ? wp_kses_post( implode( ', ', $custom_attr['name'] ) ) : wp_kses_post( $custom_attr['name'] ); ?></strong><br /><span class="other-details"><?php echo is_array( $custom_attr['options'] ) ? wp_kses_post( implode( ', ', $custom_attr['options'] ) ) : wp_kses_post( $custom_attr['options'] ); ?></span></label></li>
							<?php endforeach; ?>
						<?php
						else :
							$prod_attr_label = $product_attr_key;
							if ( 'stock status' === strtolower( $product_attr_key ) ) {
								$prod_attr_label = __( 'Stock status', 'woocommerce' );
							} elseif ( 'weight' === strtolower( $product_attr_key ) ) {
								$prod_attr_label = __( 'Weight', 'woocommerce' );
							} elseif ( 'price' === strtolower( $product_attr_key ) ) {
								$prod_attr_label = __( 'Price', 'woocommerce' );
							}
							?>
							<li><input type="checkbox" class="wtai-attr-checkboxes" name="attribute[]" value="<?php echo esc_attr( wp_unslash( $key_input ) ); ?>" data-apiname="<?php echo esc_attr( $key_input ); ?>" <?php echo ( is_array( $attributes ) && in_array( $key_input, $attributes, true ) ) ? 'checked' : ''; ?>  /><label class="wtai-details"><strong><?php echo wp_kses_post( $prod_attr_label ); ?></strong><br /><span class="wtai-details"><?php echo wp_kses_post( $product_attr_value ); ?></span></label></li>
						<?php endif; ?>
					<?php
					endforeach;
				$result['product_attr']   = ob_get_clean();
				$result['post_permalink'] = get_permalink( $product_id );

				/* translators: %1$s: date  %2$s time */
				$date_string = __( '%1$s at %2$s' );
				$date_format = get_option( 'date_format' );
				$time_format = get_option( 'time_format' );
				if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
					/* translators: %s: datetime */
					$stamp = __( 'Scheduled for: %s' );
				} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published.
					/* translators: %s: datetime */
					$stamp = __( 'Published on: %s' );
				} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
					$stamp = __( 'Publish <b>immediately</b>' );
				} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
					/* translators: %s: datetime */
					$stamp = __( 'Schedule for: %s' );
				} else { // Draft, 1 or more saves, date specified.
					/* translators: %s: datetime */
					$stamp = __( 'Publish on: %s' );
				}

				$date = sprintf(
					$date_string,
					date_i18n( $date_format, strtotime( $post->post_date ) ),
					date_i18n( $time_format, strtotime( $post->post_date ) )
				);

				$result['post_publish_date'] = sprintf( $stamp, '<b>' . $date . '</b>' );

				$result['post_publish_date2'] = sprintf(
					/* translators: %1$s: date, %2$s: time */
					__( '%1$s at %2$s' ),
					get_the_date( get_option( 'date_format' ), $product_id ),
					get_the_date( get_option( 'time_format' ), $product_id )
				);

				// Added for autogrid update from edit.
				$time                    = get_post_meta( $product_id, 'wtai_generate_date', true );
				$result['generate_date'] = sprintf(
					/* translators: %1$s: date, %2$s: time */
					__( '%1$s at %2$s' ),
					date_i18n( get_option( 'date_format' ), $time ),
					date_i18n( get_option( 'time_format' ), $time )
				);
				switch ( $post->post_status ) {
					case 'private':
						$result['post_status'] = __( 'Privately Published' );
						break;
					case 'publish':
						$result['post_status'] = __( 'Published' );
						break;
					case 'future':
						$result['post_status'] = __( 'Scheduled' );
						break;
					case 'pending':
						$result['post_status'] = __( 'Pending Review' );
						break;
					case 'draft':
					case 'auto-draft':
						$result['post_status'] = __( 'Draft' );
						break;
				}

				// Queued jobs.
				$clear_queue = isset( $_POST['clearQueue'] ) ? intval( $_POST['clearQueue'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$bulk_queue  = isset( $_POST['bulkQueue'] ) ? intval( $_POST['bulkQueue'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				if ( '1' === $clear_queue ) {
					foreach ( $fields as $field ) {
						update_post_meta( $product_id, 'wtai_bulk_queue_id_' . $field, '' );
					}
				}

				$queued_jobs           = apply_filters( 'wtai_generate_product_bulk_queue_all', array() );
				$result['queued_jobs'] = $queued_jobs;

				$ongoing_text_queue = array();

				foreach ( $fields as $field ) {
					$field_queue_id = get_post_meta( $product_id, 'wtai_bulk_queue_id_' . $field, true );

					if ( $field_queue_id ) {
						if ( $queued_jobs ) {
							$found_job_id = false;
							foreach ( $queued_jobs as $job ) {
								if ( $field_queue_id === $job['id'] ) {
									$ongoing_text_queue[] = array(
										'id'   => $job['id'],
										'type' => $field,
									);
									$found_job_id         = true;
								}
							}

							if ( ! $found_job_id ) {
								// Clear the saved queue cause its already ended.
								update_post_meta( $product_id, 'wtai_bulk_queue_id_' . $field, '' );
							}
						} else {
							// Clear the saved queue cause its already ended.
							update_post_meta( $product_id, 'wtai_bulk_queue_id_' . $field, '' );
						}
					}
				}

				$product_images = wtai_get_product_image( $product_id );
				// Get current api alt values.
				$api_alt_results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $product_images, true );

				if ( $api_alt_results && $product_images ) {
					foreach ( $product_images as $alt_image_id ) {
						if ( isset( $api_alt_results[ $alt_image_id ] ) ) {
							$alt_field_published = 0;
							$alt_field_reviewed  = 0;
							if ( isset( $api_alt_results[ $alt_image_id ]['altText']['history'][0] ) ) {
								$alt_field_published = $api_alt_results[ $alt_image_id ]['altText']['history'][0]['publish'];
								$alt_field_reviewed  = $api_alt_results[ $alt_image_id ]['altText']['history'][0]['reviewed'];
							}

							++$generated_ctr;
							$has_generated_text       = 1;
							$has_field_generated_text = true;

							if ( $alt_field_reviewed ) {
								$has_reviewed_text = 1;
								++$reviewed_ctr;
							}

							if ( ! $alt_field_reviewed ) {
								$has_generated_not_reviewed_text = 1;
							}

							if ( $alt_field_published ) {
								$has_transferred_text = 1;
								++$transferred_ctr;
							}
						}
					}
				}

				$total_fields_to_compare = count( $fields ) + count( $product_images );

				$is_all_transferred = 0;
				if ( $total_fields_to_compare === $transferred_ctr ) {
					$is_all_transferred = 1;
				}

				$is_all_reviewed = 0;
				if ( $total_fields_to_compare === $reviewed_ctr ) {
					$is_all_reviewed = 1;
				}

				$is_all_generated_reviewed = 0;
				if ( $generated_ctr === $reviewed_ctr ) {
					$is_all_generated_reviewed = 1;
				}

				$result['queued_jobs_ongoing'] = $ongoing_text_queue;

				// Get preselected types.
				$wtai_preselected_types           = wtai_get_user_preselected_types();
				$result['wtai_preselected_types'] = $wtai_preselected_types;

				$result['has_generated_text']              = $has_generated_text;
				$result['has_reviewed_text']               = $has_reviewed_text;
				$result['is_all_reviewed']                 = $is_all_reviewed;
				$result['is_all_generated_reviewed']       = $is_all_generated_reviewed;
				$result['has_generated_not_reviewed_text'] = $has_generated_not_reviewed_text;
				$result['has_transferred_text']            = $has_transferred_text;
				$result['has_platform_text']               = $has_platform_text;
				$result['is_all_transferred']              = $is_all_transferred;

				$result['reference_product_list']        = array();
				$reference_product_list_parsed           = array();
				$result['reference_product_list_parsed'] = $reference_product_list_parsed;

				$reference_product_id = wtai_get_product_reference_id( $product_id );
				if ( $reference_product_id ) {
					$rp_value = get_the_title( $reference_product_id );
					$rp_value = html_entity_decode( $rp_value, ENT_COMPAT | ENT_HTML5, 'UTF-8' );

					$product_reference_id_data[] = array(
						'value' => $reference_product_id,
						'text'  => $rp_value . ' (#' . $reference_product_id . ')',
					);
				}

				$result['product_reference_id']      = $reference_product_id;
				$result['product_reference_id_data'] = $product_reference_id_data;

				$result['product_default_style'] = wtai_get_user_default_product_style();

				// Get user preference tones.
				$result['preference_tones']              = wtai_get_user_preference_tones();
				$result['preference_styles']             = wtai_get_user_preference_styles();
				$result['preference_audiences']          = wtai_get_user_preference_audiences();
				$result['preference_product_attributes'] = wtai_get_product_attribute_preference( $product_id );

				$product_sku           = get_post_meta( $product_id, '_sku', true );
				$result['product_sku'] = $product_sku ? $product_sku : '';

				$max_keyword_char_length       = $global_rule_fields['maxKeywordLength'];
				$product_title                 = get_the_title( $product_id );
				$result['product_short_title'] = trim( substr( $product_title, 0, $max_keyword_char_length ) );

				$wtai_highlight_pronouns           = get_user_meta( get_current_user_id(), 'wtai_highlight_pronouns', true );
				$result['wtai_highlight_pronouns'] = $wtai_highlight_pronouns ? 1 : 0;

				$result['field_product_status'] = '(' . strtolower( wtai_get_product_wp_status( $product_id ) ) . ')';

				$hide_step_guide = 0;
				if ( wtai_get_hide_guidelines_user_preference() ) {
					$hide_step_guide = 1;
				}

				$result['hide_step_guide'] = $hide_step_guide;
				$result['api_results']     = $api_results;

				$result['is_premium'] = $is_premium ? '1' : '0';

				// Product image main attribute html.
				$main_image_product_html           = wtai_get_main_image( $product_id, $is_premium );
				$result['main_image_product_html'] = $main_image_product_html;

				$result['product_images']    = $product_images;
				$result['product_has_image'] = $product_images ? '1' : '0';

				$alt_images_html           = wtai_product_alt_image_html( $product_id, $product_images, $api_alt_results );
				$result['alt_images_html'] = $alt_images_html;
				$result['api_alt_results'] = $api_alt_results;

				$result['available_credit_label'] = $available_credit_label;

				$product_edit_nonce           = wp_create_nonce( 'wtai-product-nonce' );
				$result['product_edit_nonce'] = $product_edit_nonce;

				$free_premium_popup_html           = wtai_get_fremium_popup_html();
				$result['free_premium_popup_html'] = $free_premium_popup_html;

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'result'  => $result,
					'success' => $success,
					'message' => $message,
				)
			);
			exit;
		}
	}

	/**
	 * Product history callback.
	 */
	public function get_product_history_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$product_id     = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
				$page_size      = isset( $_POST['pageSize'] ) && is_numeric( $_POST['pageSize'] ) ? sanitize_text_field( wp_unslash( $_POST['pageSize'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$date_from      = isset( $_POST['date_from'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) ) ) . 'T00:00:00Z' : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$date_to        = isset( $_POST['date_to'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) ) ) . 'T23:59:59Z' : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$author         = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$continue_token = isset( $_POST['continue_token'] ) ? sanitize_text_field( wp_unslash( $_POST['continue_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$browser_offset = isset( $_POST['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) * -1 ) * 60 : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$results    = array();
				$message    = '';
				$cont_token = '';
				$user_list  = array();
				$fields     = array();
				$filters    = array(
					'startDate' => '',
					'endDate'   => '',
					'userName'  => '',
				);
				if ( $page_size ) {
					$filters['pageSize'] = $page_size;
				}

				if ( $date_from ) {
					$filters['startDate'] = $date_from;
				}

				if ( $date_to ) {
					$filters['endDate'] = $date_to;
				}

				if ( $author ) {
					$filters['userName'] = $author;
				}

				if ( $continue_token ) {
					$filters['continuationToken'] = $continue_token;
				}

				$api_results = apply_filters( 'wtai_generate_history', '', $product_id, $filters, 'product' );
				if ( is_array( $api_results ) && ! empty( $api_results ) ) {
					$meta_keys_original = apply_filters( 'wtai_fields', array() );

					$meta_keys = apply_filters( 'wtai_fields', array() );
					$meta_keys = array_keys( $meta_keys );
					$meta_keys = array_map(
						function ( $meta_key ) {
							return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'product' );
						},
						$meta_keys
					);

					if ( isset( $api_results['continuationToken'] ) && $api_results['continuationToken'] ) {
						$cont_token = $api_results['continuationToken'];
					}

					if ( isset( $api_results['histories'] ) && ( is_array( $api_results['histories'] ) && ! empty( $api_results['histories'] ) ) ) {
						foreach ( $api_results['histories'] as $history ) {
							if ( ! in_array( $history['editor'], $user_list, true ) ) {
								$user_list[] = $history['editor'];
							}

							$field_type_raw       = $history['textType'];
							$field_type_converted = apply_filters( 'wtai_field_conversion', trim( $field_type_raw ), 'product' );
							$text_display         = $history['textTypeDisplay'];
							if ( $field_type_converted ) {
								$text_display = $meta_keys_original[ $field_type_converted ];
							}

							$api_content_value = html_entity_decode( str_replace( '\\', '', $history['value'] ), ENT_QUOTES | ENT_HTML5 );

							// Trim newlines, spaces, and non-breaking spaces from the end.
							$api_content_value = wtai_remove_trailing_new_lines( $api_content_value );

							// Timezone convertion utc to browser time.
							$timezone_converted_timestamp = strtotime( get_date_from_gmt( $history['timestamp'], 'Y-m-d H:i:s' ) );
							$timekey                      = gmdate( 'Ymdhi', $timezone_converted_timestamp ) . '-' . md5( $history['actionDisplay'] );
							$result                       = array(
								'timestamp' => $history['timestamp'],
								'field'     => $text_display,
								'field_key' => $history['textType'],
								'value'     => $api_content_value,
							);
							if ( ! isset( $results[ $timekey ]['date'] ) ) {
								$results[ $timekey ]['date'] = sprintf(
									/* translators: %1$s: date, %2$s: time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $timezone_converted_timestamp ),
									gmdate( get_option( 'time_format' ), $timezone_converted_timestamp )
								);
							}

							if ( ! isset( $results[ $timekey ]['action_desc'] ) ) {
								$results[ $timekey ]['action_desc'] = $history['actionDisplay'];
							}
							$results[ $timekey ]['values'][] = $result;

						}

						foreach ( $results as $timekey => $values ) {
							if ( count( $values['values'] ) > 1 ) {
								$new_list_values = array();
								$old_values      = $values['values'];
								foreach ( $meta_keys as $meta_key ) {
									foreach ( $old_values as $index_old => $old_value ) {
										if ( $old_value['field_key'] === $meta_key ) {
											$new_list_values[] = $old_values[ $index_old ];
											unset( $old_values[ $index_old ] );
										}
									}
								}

								if ( ! empty( $new_list_values ) ) {
									$results[ $timekey ]['values'] = $new_list_values;
								}
							}
						}
					}
				} else {
					$message = 'no_history';
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$has_results = $results ? 'yes' : 'no';

			echo wp_json_encode(
				array(
					'results'     => $results,
					'has_results' => $has_results,
					'user_list'   => $user_list,
					'message'     => $message,
					'cont_token'  => $cont_token,
					'filters'     => $filters,
				)
			);
			exit;
		}
	}

	/**
	 * Global history callback.
	 */
	public function get_global_history_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$page_size      = isset( $_POST['pageSize'] ) && is_numeric( $_POST['pageSize'] ) ? sanitize_text_field( wp_unslash( $_POST['pageSize'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$date_from      = isset( $_POST['date_from'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['date_from'] ) ) ) ) . 'T00:00:00Z' : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$date_to        = isset( $_POST['date_to'] ) ? gmdate( 'Y-m-d', strtotime( sanitize_text_field( wp_unslash( $_POST['date_to'] ) ) ) ) . 'T23:59:59Z' : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$author         = isset( $_POST['author'] ) ? sanitize_text_field( wp_unslash( $_POST['author'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$continue_token = isset( $_POST['continue_token'] ) ? sanitize_text_field( wp_unslash( $_POST['continue_token'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$browser_offset = isset( $_POST['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) * -1 ) * 60 : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$results    = array();
				$message    = '';
				$cont_token = '';
				$user_list  = array();
				$fields     = array();
				$filters    = array(
					'startDate' => '',
					'endDate'   => '',
					'userName'  => '',
				);
				if ( $page_size ) {
					$filters['pageSize'] = $page_size;
				}

				if ( $date_from ) {
					$filters['startDate'] = $date_from;
				}

				if ( $date_to ) {
					$filters['endDate'] = $date_to;
				}

				if ( $author ) {
					$filters['userName'] = $author;
				}

				if ( $continue_token ) {
					$filters['continuationToken'] = $continue_token;
				}

				$api_results = apply_filters( 'wtai_generate_history', '', '', $filters, 'product' );

				if ( is_array( $api_results ) && ! empty( $api_results ) ) {
					$meta_keys_original = apply_filters( 'wtai_fields', array() );

					$meta_keys = apply_filters( 'wtai_fields', array() );
					$meta_keys = array_keys( $meta_keys );
					$meta_keys = array_map(
						function ( $meta_key ) {
							return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'product' );
						},
						$meta_keys
					);

					if ( isset( $api_results['continuationToken'] ) && $api_results['continuationToken'] ) {
						$cont_token = $api_results['continuationToken'];
					}

					if ( isset( $api_results['histories'] ) && ( is_array( $api_results['histories'] ) && ! empty( $api_results['histories'] ) ) ) {
						foreach ( $api_results['histories'] as $history ) {

							if ( ! in_array( $history['editor'], $user_list, true ) ) {
								$user_list[] = $history['editor'];
							}

							$field_type_raw       = $history['textType'];
							$field_type_converted = apply_filters( 'wtai_field_conversion', trim( $field_type_raw ), 'product' );
							$text_display         = $history['textTypeDisplay'];
							if ( $field_type_converted ) {
								$text_display = $meta_keys_original[ $field_type_converted ];
							}

							// Timezone convertion utc to browser time.
							$timezone_converted_timestamp = strtotime( get_date_from_gmt( $history['timestamp'], 'Y-m-d H:i:s' ) );
							$timekey                      = gmdate( 'Ymdhi', $timezone_converted_timestamp ) . '-' . md5( $history['actionDisplay'] ) . '-' . $history['recordId'];

							$api_content_value = html_entity_decode( str_replace( '\\', '', $history['value'] ), ENT_QUOTES | ENT_HTML5 );

							// Trim newlines, spaces, and non-breaking spaces from the end.
							$api_content_value = wtai_remove_trailing_new_lines( $api_content_value );

							$result = array(
								'timestamp'   => $history['timestamp'],
								'timestamp_o' => $history['timestamp'],
								'field'       => $text_display,
								'field_key'   => $history['textType'],
								'value'       => $api_content_value,
							);
							if ( ! isset( $results[ $timekey ]['date'] ) ) {
								$results[ $timekey ]['date'] = sprintf(
									/* translators: %1$s: date, %2$s: time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $timezone_converted_timestamp ),
									date_i18n( get_option( 'time_format' ), $timezone_converted_timestamp )
								);
							}
							if ( ! isset( $results[ $timekey ]['recordId'] ) ) {
								$results[ $timekey ]['product_id']          = $history['recordId'];
								$results[ $timekey ]['product_link']        = get_permalink( $history['recordId'] );
								$results[ $timekey ]['product_name']        = get_the_title( $history['recordId'] );
								$results[ $timekey ]['product_data_values'] = esc_attr( wtai_get_product_data_values( $history['recordId'] ) );
								$results[ $timekey ]['timestamp']           = get_date_from_gmt( $history['timestamp'], 'Y-m-d H:i:s' );
								$results[ $timekey ]['timestamp_o']         = $history['timestamp'];
							}

							if ( ! isset( $results[ $timekey ]['action_desc'] ) ) {
								$results[ $timekey ]['action_desc'] = $history['actionDisplay'];
							}
							$results[ $timekey ]['values'][] = $result;
						}

						foreach ( $results as $timekey => $values ) {
							if ( count( $values['values'] ) > 1 ) {
								$new_list_values = array();
								$old_values      = $values['values'];
								foreach ( $meta_keys as $meta_key ) {
									foreach ( $old_values as $index_old => $old_value ) {
										if ( $old_value['field_key'] === $meta_key ) {
											$new_list_values[] = $old_values[ $index_old ];
											unset( $old_values[ $index_old ] );
										}
									}
								}

								if ( ! empty( $new_list_values ) ) {
									$results[ $timekey ]['values'] = $new_list_values;
								}
							}
						}
					}
				} else {
					$message = 'no_history';
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$has_results = $results ? 'yes' : 'no';

			echo wp_json_encode(
				array(
					'results'     => $results,
					'has_results' => $has_results,
					'user_list'   => $user_list,
					'message'     => $message,
					'cont_token'  => $cont_token,
					'filters'     => $filters,
				)
			);
			exit;
		}
	}

	/**
	 * Get product attribute.
	 */
	public function get_product_attribute() {
		$product_attr          = wtai_get_product_attr();
		$product_attr_settings = get_option( 'wtai_installation_product_attr', array() );
		$i                     = 0;
		$product_info          = $product_attr;
		unset( $product_info['attributes'] );
		$product_attr = array_merge( $product_info, $product_attr['attributes'] );
		$html         = ' <div class="wtai-product-attr-container"> ';

		$featured_image_attr = array(
			'wtai-featured-product-image' => __( 'Featured Image', 'writetext-ai' ) . ' <span class="wtai-featured-image-sub" >' . __( '(Analyze image to generate more relevant text)', 'writetext-ai' ) . '</span>',
		);

		$product_attr = $featured_image_attr + $product_attr;

		$product_attr = array_filter( $product_attr );
		$total        = count( $product_attr );
		$col          = round( $total / 2 );

		foreach ( $product_attr as $id => $attr ) {
			$id       = sanitize_title( $id );
			$selected = ( is_array( $product_attr_settings ) && ! empty( $product_attr_settings ) && in_array( $id, $product_attr_settings, true ) ) ? 'checked' : '';

			// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			if ( 0 === ( $i % $col ) ) { // phpcs:ignore WordPress.PHP.YodaConditions.NotYoda
				$html .= '<div class="wtai-product-attr-wrap">';
			}

			$label_title = '';
			$label_class = '';
			if ( 'wtai-featured-product-image' === $id ) {
				$label_title = '<div class="wtai-featured-image-tooltip" >' . __( 'Note: Selecting the product image to be considered in generating text might result in a significantly longer time to complete your bulk generation, This is because we have to download and resize the image and then send it to the AI for analysis. The time it takes for this processing on a single product generation may be negligible, but it can add up when doing bulk generation for multiple products.', 'writetext-ai' ) . '</div>';
				$label_class = 'wtai-featured-product-image-label';
			}

			$html .= '<div class="wtai-product-attr-item" ' . $selected . '>';
			$html .= '<label class="' . $label_class . '" title="' . esc_attr( $label_title ) . '" ><input type="checkbox" class="wtai-product-attr-cb" value="' . $id . '" ' . $selected . ' />' . wp_unslash( $attr ) . '</label>';
			$html .= '</div>';

			// phpcs:ignore Universal.Operators.StrictComparisons.LooseEqual
			if ( ( $i % $col == $col - 1 ) || ( $i == $col - 1 ) || ( $i == $total - 1 ) ) {
				$html .= '</div>';
			}
			++$i;
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Get product fields.
	 *
	 * @param bool   $consider_bulk_preference Consider bulk preference.
	 * @param string $user_preference_type User preference type.
	 */
	public function get_product_fields( $consider_bulk_preference = false, $user_preference_type = '' ) {
		$fields = apply_filters( 'wtai_fields', array() );

		// Lets add alt text in the field list.
		$fields['alt_text'] = __( 'Image alt text', 'writetext-ai' );

		$user_text_fields = array();
		if ( 'generate' === $user_preference_type ) {
			$user_text_fields = wtai_get_bulk_generate_text_fields_user_preference();
		}
		$class_trigger = '';
		if ( 'transfer' === $user_preference_type ) {
			$user_text_fields = wtai_get_bulk_transfer_text_fields_user_preference();
			$class_trigger    = 'wtai-product-all-trigger transfer';
		}

		$html = ' <div class="wtai-product-attr-container"> ';

		$i     = 0;
		$total = count( $fields );
		foreach ( $fields as $id => $attr ) {
			$id = sanitize_title( $id );

			$checked = 'checked';
			if ( $consider_bulk_preference && $user_preference_type && ! in_array( $id, $user_text_fields, true ) ) {
				$checked = '';
			}

			if ( 0 === $i % 7 ) {
				$html .= '<div class="wtai-product-attr-wrap ' . $class_trigger . '">';
				if ( 'transfer' === $user_preference_type ) {
					$html .= '<div class="wtai-label-select-all-wrap"><label for="wtai-select-all-transfer"><input type="checkbox" name="wtai_select_all_transfer" id="wtai-select-all-transfer" class="wtai-product-cb-all" />' . __( 'Select all', 'writetext-ai' ) . '</label></div>';
				}
			}
			$html .= '<div class="wtai-product-attr-item">';
			$html .= '<label><input type="checkbox" ' . $checked . ' class="wtai-product-attr-cb" value="' . $id . '" />' . wc_clean( wp_unslash( $attr ) );
			$html .= '</div>';
			if ( ( 7 - 1 === $i % 7 ) || ( 7 - 1 === $i ) || ( $total - 1 === $i ) ) {
				$html .= '</div>';
			}
			++$i;
		}
		$html .= '</div>';

		return $html;
	}

	/**
	 * Add options.
	 */
	public function add_options() {
		// No options to add.
	}

	/**
	 * Record generate preselected types callback.
	 */
	public function record_generate_preselected_types_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$current_user_id = get_current_user_id();
		$is_ajax         = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$selected_types  = array();
		$success         = 0;
		$message         = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$selected_types     = isset( $_POST['selectedTypes'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['selectedTypes'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$selected_image_ids = isset( $_POST['selectedImageIds'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['selectedImageIds'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$product_id         = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $selected_types ) {
					$selected_types = array_filter( $selected_types );
				}

				$selected_image_ids = array_filter( $selected_image_ids );

				if ( $selected_image_ids ) {
					$selected_types[] = 'image_alt_text';
				} else {
					// Search for the key of the value.
					$image_alt_text_key = array_search( 'image_alt_text', $selected_types, true );

					// Check if the value exists in the array.
					if ( false !== $image_alt_text_key ) {
						// Remove the element.
						unset( $selected_types[ $image_alt_text_key ] );
					}

					// Reset array keys if needed.
					$selected_types = array_values( $selected_types );
				}

				delete_user_meta( $current_user_id, 'wtai_preselected_types' );
				update_user_meta( $current_user_id, 'wtai_preselected_types', $selected_types );

				// Update selected image ids per user and product.
				wtai_update_image_alt_user_preference( $product_id, $selected_image_ids );

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}
		}

		echo wp_json_encode(
			array(
				'result'                 => '1',
				'wtai_preselected_types' => $selected_types,
				'current_user_id'        => $current_user_id,
				'success'                => $success,
				'message'                => $message,
			)
		);
		exit;
	}

	/**
	 * Process user highlight callback.
	 */
	public function process_user_highlight_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$access  = 0;
		$success = 0;
		$message = '';

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
				$current_user_id = get_current_user_id();
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['value'] ) ) {
					update_user_meta( $current_user_id, 'wtai_highlight', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_highlight' );
				}
				$access = 1;
			}

			$success = 1;
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'access'  => $access,
				'success' => $success,
				'message' => $message,
			)
		);
		exit;
	}

	/**
	 * Process user comparison callback.
	 */
	public function process_user_comparison_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$success = 0;
		$message = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$current_user_id = get_current_user_id();
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['value'] ) ) {
					update_user_meta( $current_user_id, 'wtai_comparison_cb', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_comparison_cb' );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'message' => $message,
			)
		);

		exit;
	}

	/**
	 * Poll background jobs callback.
	 */
	public function poll_background_jobs_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$current_user_id = get_current_user_id();

		$jobs = wtai_get_bulk_generate_jobs( true );

		$has_generate           = 0;
		$has_ongoing_generate   = 0;
		$has_transfer           = 0;
		$transfer_product_ids   = array();
		$transfer_completed_ids = array();
		$transfer_pending_ids   = array();
		$finished_product_ids   = array();

		$own_transfer_job = 0;
		foreach ( $jobs as $job ) {
			$user_id            = $job['user_id'];
			$request            = $job['request'];
			$product_ids        = $job['product_ids'];
			$request_job_status = $request['status'];

			$is_finished = false;
			if ( 'generate' === $job['type'] ) {
				$has_generate = 1;

				$bulk_completed = isset( $request['completedIds'] ) ? count( $request['completedIds'] ) : 0;
				$bulk_total     = (int) $request['total'];
				if ( 'Completed' === $request_job_status ) {
					$is_finished = true;
				} elseif ( $bulk_completed < $bulk_total ) {
						$has_ongoing_generate = 1;
				} else {
					$is_finished = true;
				}
			} elseif ( 'transfer' === $job['type'] ) {
				$has_transfer = 1;

				if ( $user_id === $current_user_id ) {
					$own_transfer_job = 1;
				}

				$product_ids   = $job['product_ids'];
				$completed_ids = $job['completed_ids'];
				if ( $completed_ids ) {
					$completed_ids = array_unique( $completed_ids );
				}

				$bulk_completed = count( $completed_ids );
				$bulk_total     = count( $product_ids );
				if ( $bulk_completed >= $bulk_total ) {
					$is_finished = true;
				}

				if ( $current_user_id === $user_id ) {
					if ( $product_ids ) {
						$transfer_product_ids = array_merge( $transfer_product_ids, $product_ids );
					}

					if ( $completed_ids ) {
						$transfer_completed_ids = array_merge( $transfer_completed_ids, $completed_ids );
					}
				}
			}

			if ( $is_finished ) {
				$finished_product_ids = array_merge( $finished_product_ids, $product_ids );
			}
		}

		if ( $transfer_product_ids ) {
			if ( $transfer_completed_ids ) {
				foreach ( $transfer_product_ids as $product_id ) {
					if ( ! in_array( $product_id, $transfer_completed_ids, true ) ) {
						$transfer_pending_ids[] = $product_id;
					}
				}
			} else {
				$transfer_pending_ids = $transfer_product_ids;
			}
		}

		$transfer_pending_ids = array_filter( $transfer_pending_ids );

		if ( $finished_product_ids ) {
			$pending_bulk_ids = wtai_get_all_pending_bulk_ids( $finished_product_ids );
		} else {
			$pending_bulk_ids = wtai_get_all_pending_bulk_ids( array(), true );
		}

		echo wp_json_encode(
			array(
				'bulk_jobs'              => $jobs,
				'has_generate'           => $has_generate,
				'has_ongoing_generate'   => $has_ongoing_generate,
				'has_transfer'           => $has_transfer,
				'own_transfer_job'       => $own_transfer_job,
				'transfer_product_ids'   => $transfer_product_ids,
				'transfer_completed_ids' => $transfer_completed_ids,
				'transfer_pending_ids'   => $transfer_pending_ids,
				'pending_bulk_ids'       => $pending_bulk_ids,
				'finished_product_ids'   => $finished_product_ids,
			)
		);

		exit;
	}

	/**
	 * Process user bulk generate callback.
	 */
	public function process_user_bulk_generate_popup_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$access  = 0;
		$success = 0;
		$message = '';
		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
				$current_user_id = get_current_user_id();
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['value'] ) ) {
					update_user_meta( $current_user_id, 'wtai_bulk_generate_popup', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_bulk_generate_popup' );
				}
				$access = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$success = 1;
		}

		echo wp_json_encode(
			array(
				'access'  => $access,
				'success' => $success,
				'message' => $message,
			)
		);
		exit;
	}

	/**
	 * Get loader html markup.
	 */
	public function get_loader_html_temp_markup() {
		$user_id = get_current_user_id();
		$user    = get_user_by( 'id', $user_id );

		ob_start();

		$button_class    = 'bulk-generate-cancel';
		$button          = __( 'Cancel', 'writetext-ai' );
		$request_id      = '';
		$show_more_class = '';
		?>
		<div class="wtai-loading-estimate-time-container wtai-loading-estimate-time-container-temp 
			wtai-loading-estimate-time-container-user-<?php echo esc_attr( $user_id ); ?> wtai-loading-estimate-time-container-generate wtai-ongoing" 
			data-product-ids="{{dataProductIDs}}" 
			data-is-own="yes" 
			data-user-id="<?php echo esc_attr( $user_id ); ?>"
		>
			<div class="wtai-loading-details-container wtai-d-flex wtai-ongoing">
				<div class="wtai-bulk-generate-check-ico-wrap">
					<span class="wtai-bulkgenerate-check-ico" ></span>
				</div>
				<div class="wtai-loading-header-details wtai-d-flex">
					<div class="wtai-bulk-generate-headline-txt-wrapper wtai-d-flex">
						<div class="wtai-loading-header-text">
							<span class="wtai-bulk-generate-headline-txt" data-initial-text="<?php echo wp_kses_post( __( 'Generating text', 'writetext-ai' ) ); ?>..." >
								<?php echo wp_kses_post( __( 'Generating text...', 'writetext-ai' ) ); ?>
								<span class="wtai-estimated-time"></span>
							</span>
							<span class="wtai-generate-username" ><?php echo wp_kses_post( $user->display_name ); ?></span>
						</div>
						<div class="wtai-loading-header-number"><span>{{startProductCount}} / {{endProductCount}}</span> <?php echo wp_kses_post( __( 'product/s', 'writetext-ai' ) ); ?></div>
					</div>
					<div class="wtai-loading-loader-msg-wrapper">
						<div class="wtai-loading-loader-wrapper">
							<div class="wtai-main-loading" ></div>
						</div>
					</div>
				</div>
			</div>
			<div class="wtai-loading-actions-container" >
				<a href="#" data-request-id="<?php echo esc_attr( $request_id ); ?>" class="button action-bulk-generate <?php echo esc_attr( $button_class ); ?> disabled"><?php echo wp_kses_post( $button ); ?></a>
			</div>
		</div>

		<?php
		$generate_temp_html = ob_get_clean();

		// Get template for transfer.
		ob_start();
		$button_class = '';
		?>
		<div class="wtai-loading-estimate-time-container wtai-loading-estimate-time-container-temp 
			wtai-loading-estimate-time-container-user-<?php echo esc_attr( $user_id ); ?> wtai-loading-estimate-time-container-transfer wtai-ongoing" 
			data-product-ids="{{dataProductIDs}}" 
			data-is-own="yes" 
			data-user-id="<?php echo esc_attr( $user_id ); ?>" 
		>
			<div class="wtai-loading-details-container wtai-d-flex wtai-ongoing">
				<div class="wtai-bulk-generate-check-ico-wrap">
					<span class="wtai-bulkgenerate-check-ico" ></span>
				</div>
				<div class="wtai-loading-header-details wtai-d-flex">
					<div class="wtai-bulk-generate-headline-txt-wrapper wtai-d-flex">
						<div class="wtai-loading-header-text">
							<span class="wtai-bulk-generate-headline-txt" >
								<?php echo wp_kses_post( __( 'Transferring text...', 'writetext-ai' ) ); ?>
								<span class="wtai-estimated-time"></span>
							</span>							
							<span class="wtai-generate-username" ><?php echo wp_kses_post( $user->display_name ); ?></span>
							
						</div>
						<div class="wtai-loading-header-number"><span>{{startProductCount}} / {{endProductCount}}</span> <?php echo wp_kses_post( __( 'product/s', 'writetext-ai' ) ); ?></div>
					</div>
					<div class="wtai-loading-loader-msg-wrapper">
						<div class="wtai-loading-loader-wrapper">
							<div class="wtai-main-loading " ></div>
						</div>
					</div>
				</div>
			</div>
			<div class="wtai-loading-actions-container" >
				<?php
				$hide_cancel = '';
				$hide_ok     = 'display: none;';
				?>
				<a href="#" class="button wtai-action-bulk-transfer-cancel <?php echo esc_attr( $button_class ); ?>" style="<?php echo esc_attr( $hide_cancel ); ?>" ><?php echo wp_kses_post( __( 'Cancel', 'writetext-ai' ) ); ?></a>
				<a href="#" class="button wtai-action-bulk-transfer button-primary <?php echo esc_attr( $button_class ); ?>" style="<?php echo esc_attr( $hide_ok ); ?>" ><?php echo wp_kses_post( __( 'OK', 'writetext-ai' ) ); ?></a>
			</div>
		</div>

		<?php
		$transfer_temp_html = ob_get_clean();

		ob_start();
		?>
		<div class="wtai-single-loading-header-details wtai-d-flex hidden">
			<div class="wtai-single-loading-header">
				<div class="wtai-single-wtai-loading-header-text"><?php echo wp_kses_post( __( 'Generating text...', 'writetext-ai' ) ); ?></div>
			
				<div class="wtai-loading-loader-wrapper">
					<div class="wtai-main-loading"></div>
				</div>
				<div class="wtai-single-wtai-loading-header-number"><span>{{startProductCount}} / {{endProductCount}}</span> <?php echo wp_kses_post( __( 'product/s', 'writetext-ai' ) ); ?></div>
			</div>	
			<div class="wtai-single-loading-actions-show-wrap ">
				<a href="#" class="wtai-loading-actions-show-hide-cta wtai-show" data-type="show"><?php echo wp_kses_post( __( 'See more' ) ); ?></a>
			</div>
		</div>
		<?php
		$single_generate_temp_html = ob_get_clean();

		ob_start();
		?>
		<div class="wtai-single-loading-header-details wtai-d-flex hidden">
			<div class="wtai-single-loading-header">
				<div class="wtai-single-wtai-loading-header-text"><?php echo wp_kses_post( __( 'Transferring text...', 'writetext-ai' ) ); ?></div>
			
				<div class="wtai-loading-loader-wrapper">
					<div class="wtai-main-loading"></div>
				</div>
				<div class="wtai-single-wtai-loading-header-number"><span>{{startProductCount}} / {{endProductCount}}</span> <?php echo wp_kses_post( __( 'product/s', 'writetext-ai' ) ); ?></div>
			</div>	
			<div class="wtai-single-loading-actions-show-wrap ">
				<a href="#" class="wtai-loading-actions-show-hide-cta wtai-show" data-type="show"><?php echo wp_kses_post( __( 'See more' ) ); ?></a>
			</div>
		</div>
		<?php
		$single_transfer_temp_html = ob_get_clean();

		ob_start();
		?>
		<div class="wtai-see-more-less-wrapper">
			<div class="wtai-loading-actions-show-wrap <?php echo esc_attr( $show_more_class ); ?>" >
				<a href="#" class="wtai-loading-actions-show-hide-cta wtai-show" data-type="show" ><?php echo wp_kses_post( __( 'See more' ) ); ?></a>
			</div>
			<div class="wtai-loading-actions-hide-wrap <?php echo esc_attr( $show_less_class ); ?>" >
				<a href="#" class="wtai-loading-actions-show-hide-cta wtai-less" data-type="hide" ><?php echo wp_kses_post( __( 'See less' ) ); ?></a>
			</div>
		</div>
		<?php
		$see_more_hide_html = ob_get_clean();

		$output = array(
			'generate_temp_html'        => $generate_temp_html,
			'transfer_temp_html'        => $transfer_temp_html,
			'single_generate_temp_html' => $single_generate_temp_html,
			'single_transfer_temp_html' => $single_transfer_temp_html,
			'see_more_hide_html'        => $see_more_hide_html,
		);

		return $output;
	}

	/**
	 * Bulk dismiss all.
	 */
	public function bulk_dismiss_all() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success = 0;
			$message = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$request_id  = isset( $_POST['requestID'] ) ? sanitize_text_field( wp_unslash( $_POST['requestID'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$request_ids = isset( $_POST['requestIDs'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['requestIDs'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

				// Clear all generations.
				if ( $request_ids ) {
					foreach ( $request_ids as $req_id ) {
						if ( $req_id ) {
							wtai_clear_user_bulk_generation( $req_id );
						}
					}
				}

				// Clear all transfers.
				wtai_clear_user_bulk_transfer();

				$disable_bulk_generate = 0;
				if ( wtai_get_current_user_bulk_generation_products() ) {
					$disable_bulk_generate = 1;
				} elseif ( wtai_get_current_user_bulk_transfer_products() ) {
					$disable_bulk_generate = 1;
				}

				// Get updated list of jobs.
				$show_hidden = isset( $_POST['show_hidden'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['show_hidden'] ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
				$jobs        = wtai_get_bulk_generate_jobs( true );

				$output = apply_filters( 'wtai_get_generate_bulk_data', array(), $jobs, false, $show_hidden );

				$all_pending_ids = array();
				if ( $html ) {
					$all_pending_ids = wtai_get_all_pending_bulk_ids( array(), true );
				}

				$html = $output['html'];

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success'               => $success,
					'html'                  => $html,
					'all_pending_ids'       => $all_pending_ids,
					'disable_bulk_generate' => $disable_bulk_generate,
					'message'               => $message,
				)
			);
			exit;
		}
	}

	/**
	 * Reset bulk options value.
	 */
	public function reset_bulk_options_values() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_reset_bulkoptions_values'] ) && '1' === $_GET['wtai_reset_bulkoptions_values'] ) {
			wtai_reset_bulk_options_values();

			die();
		}
	}

	/**
	 * Job checker temp.
	 */
	public function job_checker_temp() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_list_current_jobs'] ) && '1' === $_GET['wtai_list_current_jobs'] ) {

			$jobs = wtai_get_bulk_generate_jobs( false, false );

			echo 'JOBS: <br>';
			echo '<pre>';
			print_r( $jobs ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';
			echo '<br>------<br>';

			$jobs_api = apply_filters( 'wtai_generate_product_bulk_queue_all', array() );
			echo 'JOBS API: <br>';
			echo '<pre>';
			print_r( $jobs_api ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions
			echo '</pre>';
			echo '<br>------<br>';
			die();
		}
	}

	/**
	 * Save bulk geenrate text field user preference.
	 */
	public function save_bulk_generate_text_field_user_preference() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success = 0;
			$message = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$current_user_id = get_current_user_id();

				$fields = isset( $_POST['fields'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

				update_user_meta( $current_user_id, 'wtai_bulk_generate_text_field_user_preference', $fields );

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success' => $success,
					'fields'  => $fields,
					'message' => $message,
				)
			);

			exit;
		}
	}

	/**
	 * Save bulk transfer text field user preference.
	 */
	public function save_bulk_transfer_text_field_user_preference() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success = 0;
			$message = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$current_user_id = get_current_user_id();

				$fields = isset( $_POST['fields'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

				update_user_meta( $current_user_id, 'wtai_bulk_transfer_text_field_user_preference', $fields );

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success' => $success,
					'message' => $message,
				)
			);

			exit;
		}
	}

	/**
	 * Save tones and styles user preference.
	 */
	public function save_tones_styles_option_user_preference() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success                 = 0;
			$message                 = '';
			$tones                   = array();
			$styles                  = array();
			$custom_tone_cb          = '';
			$custom_tone_text        = '';
			$custom_style_cb         = '';
			$custom_style_text       = '';
			$custom_style_refprod    = '';
			$custom_style_refprodsel = '';
			$is_premium              = '';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$current_user_id        = get_current_user_id();
				$account_credit_details = wtai_get_account_credit_details();
				$is_premium             = $account_credit_details['is_premium'];

				$tones = isset( $_POST['wtai_installation_tones'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['wtai_installation_tones'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$tones = array_filter( $tones );

				update_user_meta( $current_user_id, 'wtai_tones_options_user_preference', $tones );

				$custom_tone_cb = isset( $_POST['customToneCb'] ) ? sanitize_text_field( wp_unslash( $_POST['customToneCb'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				update_user_meta( $current_user_id, 'wtai_tones_custom_user_preference', $custom_tone_cb );

				$custom_tone_text = isset( $_POST['customToneText'] ) ? sanitize_text_field( wp_unslash( $_POST['customToneText'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $is_premium ) {
					update_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', $custom_tone_text );
				} elseif ( $tones ) {
					update_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', '' );
				}

				$styles = isset( $_POST['wtai_installation_styles'] ) ? sanitize_text_field( wp_unslash( $_POST['wtai_installation_styles'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				update_user_meta( $current_user_id, 'wtai_styles_options_user_preference', $styles );

				$custom_style_cb = isset( $_POST['customStyleCb'] ) ? sanitize_text_field( wp_unslash( $_POST['customStyleCb'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				update_user_meta( $current_user_id, 'wtai_styles_custom_user_preference', $custom_style_cb );

				$custom_style_text = isset( $_POST['customStyleText'] ) ? sanitize_text_field( wp_unslash( $_POST['customStyleText'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				if ( $is_premium ) {
					update_user_meta( $current_user_id, 'wtai_styles_custom_text_user_preference', $custom_style_text );
				} elseif ( $styles ) {
					update_user_meta( $current_user_id, 'wtai_styles_custom_text_user_preference', '' );
				}

				// Save custom user defined audience.
				$audiences = isset( $_POST['audiences'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['audiences'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				update_user_meta( $current_user_id, 'wtai_audiences_options_user_preference', $audiences );

				$is_premium = $is_premium ? '1' : '0';

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success'               => $success,
					'tones'                 => $tones,
					'styles'                => $styles,
					'customToneCb'          => $custom_tone_cb,
					'customToneText'        => $custom_tone_text,
					'customStyleCb'         => $custom_style_cb,
					'customStyleText'       => $custom_style_text,
					'customStyleRefprod'    => $custom_style_refprod,
					'customStyleRefprodsel' => $custom_style_refprodsel,
					'is_premium'            => $is_premium,
					'message'               => $message,
				)
			);

			exit;
		}
	}

	/**
	 * Save product keyword location code.
	 */
	public function save_product_keyword_location_code() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success = 0;
			$message = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$product_id    = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$location_code = isset( $_POST['location_code'] ) ? sanitize_text_field( wp_unslash( $_POST['location_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $product_id && $location_code ) {
					update_post_meta( $product_id, 'wtai_keyword_location_code', $location_code );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'success' => $success,
					'message' => $message,
				)
			);

			exit;
		}
	}

	/**
	 * Resave last activity temp.
	 */
	public function resave_last_activity_temp() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_resave_last_activity_temp'] ) && 1 === $_GET['wtai_resave_last_activity_temp'] ) {
			$args             = array(
				'fields'         => 'ids',
				'post_type'      => 'product',
				'post_status'    => 'any',
				'posts_per_page' => -1,
			);
			$query_result     = new WP_Query( $args );
			$query_result_ids = $query_result->posts;
			if ( $query_result_ids ) {
				foreach ( $query_result_ids as $product_id ) {
					$generate_date      = get_post_meta( $product_id, 'wtai_generate_date', true );
					$transfer_date      = get_post_meta( $product_id, 'wtai_transfer_date', true );
					$last_activity_date = get_post_meta( $product_id, 'wtai_last_activity_date', true );
					$last_activity      = '';
					if ( '' === $last_activity_date ) {
						if ( $generate_date ) {
							$last_activity_date = gmdate( 'Y-m-d H:i:s', $generate_date );
							$last_activity      = 'generate';
						}

						if ( $transfer_date && $transfer_date > $generate_date ) {
							$last_activity_date = gmdate( 'Y-m-d H:i:s', $transfer_date );
							$last_activity      = 'transfer';
						}

						if ( $last_activity_date ) {
							update_post_meta( $product_id, 'wtai_last_activity_date', $last_activity_date );
							update_post_meta( $product_id, 'wtai_last_activity', $last_activity );
						}
					}
				}
			}

			die();
		}
	}

	/**
	 * Render admin footer.
	 */
	public function render_wtai_admin_footer() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) &&
			( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
			?>
			<style type="text/css" >
				#wpfooter {
					position: relative !important;
					display: none !important;
				}
			</style>
			<?php
			include_once WTAI_ABSPATH . 'templates/admin/footer.php';
		}
	}

	/**
	 * Search reference product.
	 */
	public function search_reference_product() {
		define( 'WTAI_DOING_AJAX', true );

		$reference_product_list_parsed = array();
		$total_count                   = 0;
		$max_page                      = 0;
		$excluded_in_search            = array();

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			$term                 = isset( $_POST['term'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['term'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
			$product_id           = isset( $_POST['product_id'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
			$per_page             = isset( $_POST['per_page'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['per_page'] ) ) : 50; // phpcs:ignore WordPress.Security.NonceVerification
			$page                 = isset( $_POST['page'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
			$reference_product_id = isset( $_POST['reference_product_id'] ) ? (string) sanitize_text_field( wp_unslash( $_POST['reference_product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

			$excluded_in_search = array();
			if ( $product_id ) {
				$excluded_in_search[] = $product_id;
			}

			$products_output = wtai_get_reference_product_list( $excluded_in_search, $term, $per_page, $page );
			$products        = $products_output['products'];
			$total_count     = $products_output['total_count'];

			// Parse results.
			$reference_product_list_parsed = array();

			if ( $reference_product_id ) {
				$rp_value = get_the_title( $reference_product_id );
				$rp_value = html_entity_decode( $rp_value, ENT_COMPAT | ENT_HTML5, 'UTF-8' );

				$reference_product_list_parsed[] = array(
					'value' => $reference_product_id,
					'text'  => $rp_value . ' (#' . $reference_product_id . ')',
				);
			}

			foreach ( $products as $rp_key => $rp_data ) {
				if ( $reference_product_id && $reference_product_id === $rp_key ) {
					continue;
				}

				$rp_value   = $rp_data['name'];
				$rp_desc    = $rp_data['description'];
				$rp_excerpt = $rp_data['excerpt'];

				$rp_value_orig   = $rp_value;
				$rp_desc_orig    = $rp_desc;
				$rp_excerpt_orig = $rp_excerpt;

				$rp_desc = preg_replace( '/<([^>]*(<|$))/', '&lt;$1', $rp_desc );

				$rp_value   = html_entity_decode( $rp_value, ENT_COMPAT | ENT_HTML5, 'UTF-8' );
				$rp_desc    = html_entity_decode( wp_strip_all_tags( $rp_desc ), ENT_COMPAT | ENT_HTML5, 'UTF-8' );
				$rp_excerpt = html_entity_decode( wp_strip_all_tags( $rp_excerpt ), ENT_COMPAT | ENT_HTML5, 'UTF-8' );

				$description_char_length = mb_strlen( wp_strip_all_tags( $rp_desc ), 'UTF-8' );
				$excerpt_char_length     = mb_strlen( wp_strip_all_tags( $rp_excerpt ), 'UTF-8' );

				$description_word_count = wtai_word_count( wp_strip_all_tags( $rp_desc ) );
				$excerpt_word_count     = wtai_word_count( wp_strip_all_tags( $rp_excerpt ) );

				$description_char_length_for_credit = mb_strlen( $rp_desc_orig, 'UTF-8' );
				$excerpt_char_length_for_credit     = mb_strlen( $rp_excerpt_orig, 'UTF-8' );

				$rp_index = $rp_key . '-'
					. $description_char_length . '-'
					. $excerpt_char_length . '-'
					. $description_word_count . '-'
					. $excerpt_word_count . '-'
					. $description_char_length_for_credit . '-'
					. $excerpt_char_length_for_credit;

				$reference_product_list_parsed[] = array(
					'id'                                 => $rp_key,
					'value'                              => $rp_index,
					'text'                               => $rp_value . ' (#' . $rp_key . ')',
					'description'                        => $rp_desc,
					'rp_desc_orig'                       => $rp_desc_orig,
					'rp_excerpt_orig'                    => $rp_excerpt_orig,
					'excerpt'                            => $rp_excerpt,
					'description_char_length'            => $description_char_length,
					'excerpt_char_length'                => $excerpt_char_length,
					'description_word_count'             => $description_word_count,
					'excerpt_word_count'                 => $excerpt_word_count,
					'description_char_length_for_credit' => $description_char_length_for_credit,
					'excerpt_char_length_for_credit'     => $excerpt_char_length_for_credit,
				);
			}

			$max_page = ceil( $total_count / $per_page );
		}

		wp_send_json(
			array(
				'products'           => $reference_product_list_parsed,
				'total_count'        => $total_count,
				'max_page'           => $max_page,
				'excluded_in_search' => $excluded_in_search,
			)
		);

		exit;
	}

	/**
	 * Get disallowed combinations.
	 *
	 * @param array $disallowed_combinations Disallowed combinations.
	 * @param bool  $force                     Force to get the data.
	 */
	public function get_disallowed_combinations( $disallowed_combinations = array(), $force = false ) {
		$disallowed_combinations = apply_filters( 'wtai_generate_text_filters', array(), 'disallowedCombinations', $force );

		return $disallowed_combinations;
	}

	/**
	 * Get formal and informal pronouns.
	 *
	 * @param array $formal_informal_pronouns Formal and informal pronouns.
	 * @param bool  $force                     Force to get the data.
	 */
	public function get_formal_informal_pronouns( $formal_informal_pronouns = array(), $force = false ) {
		$formal_informal_pronouns = apply_filters( 'wtai_generate_text_filters', array(), 'FormalLanguages', $force );

		return $formal_informal_pronouns;
	}

	/**
	 * Process user highlight pronouns callback.
	 */
	public function process_user_highlight_pronouns_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$access  = 0;
		$message = '';
		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
				$current_user_id = get_current_user_id();
				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['value'] ) ) {
					update_user_meta( $current_user_id, 'wtai_highlight_pronouns', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_highlight_pronouns' );
				}
				$access = 1;
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'access'  => $access,
				'message' => $message,
			)
		);
		exit;
	}

	/**
	 * Record single product attribute preference callback.
	 */
	public function record_single_product_attribute_preference_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$success = 0;
		$message = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$product_attributes = isset( $_POST['product_attributes'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['product_attributes'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$product_id         = isset( $_POST['product_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $product_id ) {
					$account_credit_details = wtai_get_account_credit_details();
					$is_premium             = $account_credit_details['is_premium'];

					if ( ! $is_premium ) {
						foreach ( $product_attributes as $key => $attr ) {
							if ( 'otherproductdetails' === $attr ) {
								unset( $product_attributes[ $key ] );
							}
						}
					}

					$has_product_image = false;
					foreach ( $product_attributes as $key => $attr ) {
						if ( 'product-main-image' === $attr ) {
							unset( $product_attributes[ $key ] );

							$has_product_image = true;
						}
					}

					if ( $has_product_image ) {
						update_user_meta( get_current_user_id(), 'wtai_include_featured_image_in_generation', '1' );
					} else {
						$featured_image_id = get_post_thumbnail_id( $product_id );
						if ( $featured_image_id ) {
							update_user_meta( get_current_user_id(), 'wtai_include_featured_image_in_generation', '0' );
						}
					}

					if ( $product_attributes ) {
						$product_attributes = array_filter( $product_attributes );
						$product_attributes = array_values( $product_attributes );
					}

					update_post_meta( $product_id, 'wtai_product_attribute_preference', $product_attributes );

					$success = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'message' => $product_attributes,
			)
		);
		exit;
	}

	/**
	 * Process hide guide per user settings callback.
	 */
	public function process_wtai_user_hide_guidelines_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );

		$success = 0;
		$message = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$current_user_id = get_current_user_id();

				$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $current_user_id ) {
					update_user_meta( $current_user_id, 'wtai_hide_guidelines', $value );

					$success = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}
		}

		echo wp_json_encode(
			array(
				'message'         => $message,
				'success'         => $success,
				'current_user_id' => $current_user_id,
			)
		);
		exit;
	}

	/**
	 * Get country list.
	 */
	public function get_wtai_country_variables() {
		$site_localized_countries = wtai_get_site_localized_countries();

		$product_countries = apply_filters( 'wtai_keywordanalysis_location', array() );

		// Sort by value alphabetically.
		uasort(
			$product_countries,
			function ( $a, $b ) {
				return strnatcmp( $a['name'], $b['name'] );
			}
		);

		$product_countries_sorted = array();
		$product_country_options  = array();
		foreach ( $product_countries as $product_country_id => $product_country ) {
			$product_country['product_country_id'] = $product_country_id;

			$product_countries_sorted[] = $product_country;

			$product_country_options[] = array(
				'value' => $product_country['code'],
				'text'  => $product_country['name'],
			);
		}

		$default_locale = apply_filters( 'wtai_language_code', get_locale() );

		$default_locale_array = explode( '_', $default_locale );
		$default_country      = isset( $default_locale_array[1] ) ? $default_locale_array[1] : '';
		$lang                 = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

		// Autosave default country.
		if ( $default_country && ! wtai_is_site_localized_countries_set() ) {
			wtai_set_country_per_language( $lang, $default_country );

			$site_localized_countries = wtai_get_site_localized_countries();
		}

		$selected_count = 0;
		foreach ( $product_countries_sorted as $product_country ) {
			if ( $site_localized_countries ) {
				$selected = in_array( $product_country['code'], $site_localized_countries, true ) ? 'selected' : '';
			} else {
				$selected = strtolower( $product_country['code'] ) === strtolower( $default_country ) ? 'selected' : '';
			}

			if ( 'selected' === $selected ) {
				++$selected_count;

				$product_country_options_selected[] = array(
					'value' => $product_country['code'],
					'text'  => $product_country['name'],
				);
			}
		}

		$output = array(
			'product_country_options'          => $product_country_options,
			'product_country_options_selected' => $product_country_options_selected,
		);

		return $output;
	}

	/**
	 * Get country popup.
	 */
	public function get_wtai_country_selection_popup() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && ( 'write-text-ai' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) ) {

			$site_localized_countries = wtai_get_site_localized_countries();

			$product_countries = apply_filters( 'wtai_keywordanalysis_location', array() );

			// Sort by value alphabetically.
			uasort(
				$product_countries,
				function ( $a, $b ) {
					return strnatcmp( $a['name'], $b['name'] );
				}
			);

			$product_countries_sorted = array();
			$product_country_options  = array();
			foreach ( $product_countries as $product_country_id => $product_country ) {
				$product_country['product_country_id'] = $product_country_id;

				$product_countries_sorted[] = $product_country;

				$product_country_options[] = array(
					'value' => $product_country['code'],
					'text'  => $product_country['name'],
				);
			}

			$default_locale = apply_filters( 'wtai_language_code', get_locale(), false );

			$default_locale_array = explode( '_', $default_locale );
			$default_lang         = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.
			$default_country      = isset( $default_locale_array[1] ) ? $default_locale_array[1] : '';

			$is_localized_country_set = wtai_is_site_localized_countries_set( $default_lang );

			$shown_class = '';
			$allow_close = true;
			if ( ! $default_country && ! $is_localized_country_set ) {
				$shown_class = ' wtai-shown ';
				$allow_close = false;
			}

			if ( ! wtai_current_user_can( 'writeai_select_localized_country' ) || ! wtai_current_user_can( 'writeai_generate_text' ) ) {
				$shown_class = '';
				$allow_close = true;
			}

			// Autosave default country.
			$suggested_default_country = '';
			if ( $default_country ) {
				wtai_set_country_per_language( $default_lang, $default_country );
				wtai_set_localized_country_enabled(); // Set localized country custom selection.

				$site_localized_countries = wtai_get_site_localized_countries();
			} else {
				$suggested_default_locale       = wtai_match_language_locale( $default_lang );
				$suggested_default_locale_array = explode( '_', $suggested_default_locale );
				$suggested_default_country      = isset( $suggested_default_locale_array[1] ) ? $suggested_default_locale_array[1] : '';
			}

			$wtai_nonce = wp_create_nonce( 'wtai-country-nonce' );
			?>
			<div class="wtai-country-selection-popup-overlay <?php echo esc_attr( $shown_class ); ?> <?php echo $allow_close ? 'wtai-close-on-click' : ''; ?>" ></div>
			<div class="wtai-country-selection-popup-wrap wtai-loader-generate <?php echo esc_attr( $shown_class ); ?>" >
				<div class="wtai-country-selection-content-popup-wrap">
					<div class="wtai-country-selection-header" >
						<h2>
							<?php echo wp_kses_post( __( 'Select your target country', 'writetext-ai' ) ); ?>
						</h2>
						<div class="wtai-country-selection-description">
							<?php echo wp_kses_post( __( 'WriteText.ai will attempt to localize the generated text for your selected country/countries.', 'writetext-ai' ) ); ?>
						</div>

						<?php if ( $allow_close ) { ?>
							<div class="wtai-country-selection-close" >
								<span class="dashicons dashicons-no-alt"></span>
							</div>
						<?php } ?>
					</div>

					<div class="wtai-country-selection-dropdown-container-wrap" >
						<div class="wtai-country-selection-dropdown-inner-wrap" >
							<label class="wtai-country-selection-dropdown-label" >
								<?php echo wp_kses_post( __( 'Country', 'writetext-ai' ) ); ?>
							</label>

							<div class="wtai-country-selection-single-dropdown-wrap" >
								<select id="wtai-country-single-dropdown" class="wtai-country-single-dropdown" >
									<?php
									$selected_count = 0;
									foreach ( $product_countries_sorted as $product_country ) {
										if ( $site_localized_countries ) {
											$selected = in_array( $product_country['code'], $site_localized_countries, true ) ? 'selected' : '';
										} elseif ( $suggested_default_country ) {
											$selected = strtolower( $product_country['code'] ) === strtolower( $suggested_default_country ) ? 'selected' : '';
										} else {
											$selected = strtolower( $product_country['code'] ) === strtolower( $default_country ) ? 'selected' : '';
										}

										if ( 'selected' === $selected ) {
											++$selected_count;

											$product_country_options_selected[] = array(
												'value' => $product_country['code'],
												'text'  => $product_country['name'],
											);
										}
										?>
										<option value="<?php echo esc_attr( $product_country['code'] ); ?>" <?php echo esc_attr( $selected ); ?> >
											<?php echo wp_kses_post( $product_country['name'] ); ?>
										</option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
					</div>
					<div class="wtai-country-selection-cta-wrap" >
						<?php
						$disabled_save_state = '';
						if ( ! wtai_current_user_can( 'writeai_select_localized_country' ) || ! wtai_current_user_can( 'writeai_generate_text' ) ) {
							$disabled_save_state = ' disabled ';
						}
						?>
						<input type="button" class="wtai-country-selection-cta button-primary" <?php echo esc_attr( $disabled_save_state ); ?> value="<?php echo wp_kses_post( __( 'Save', 'writetext-ai' ) ); ?>" />
					</div>
				</div>

				<input type="hidden" id="wtai-country-nonce" value="<?php echo esc_attr( $wtai_nonce ); ?>" />
			</div>
			<?php
		}
	}

	/**
	 * Save localized countries AJAX
	 */
	public function save_localized_countries() {
		define( 'WTAI_DOING_AJAX', true );

		$message = '';
		$success = 0;

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-country-nonce' ) ) {
			$country = isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

			$default_locale       = apply_filters( 'wtai_language_code', get_locale() );
			$default_locale_array = explode( '_', $default_locale );
			$lang                 = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

			wtai_set_country_per_language( $lang, $country );

			wtai_set_localized_country_enabled(); // Set localized country custom selection.

			$success = 1;
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'success' => $success,
				'message' => $message,
				'country' => $country,
			)
		);
		exit;
	}

	/**
	 * Reset user preference
	 */
	public function wtai_reset_user_preferences() {
		define( 'WTAI_DOING_AJAX', true );

		$current_user_id = get_current_user_id();

		if ( $current_user_id ) {
			update_user_meta( $current_user_id, 'wtai_tones_options_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_tones_custom_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_styles_options_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_styles_custom_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_styles_custom_text_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_audiences_options_user_preference', '' );
			update_user_meta( $current_user_id, 'wtai_product_attribute_preference', '' );
			update_user_meta( $current_user_id, 'wtai_include_featured_image_in_generation', '0' );
			update_user_meta( $current_user_id, 'wtai_category_image_checked_status', '0' );
		}

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
		$check_featured_image       = '0';
		if ( ! empty( $product_attributes_array ) ) {
			$default_product_attributes = implode( ',', $product_attributes_array );

			$featured_image_key = array_search( 'wtai-featured-product-image', $product_attributes_array ); // phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
			if ( false !== $featured_image_key ) {
				$check_featured_image = '1';
			}
		}

		echo wp_json_encode(
			array(
				'success'                    => 1,
				'default_style'              => $default_style,
				'default_tones'              => $default_tones,
				'default_audiences'          => $default_audiences,
				'default_product_attributes' => $default_product_attributes,
				'default_desc_min'           => $description_min_default,
				'default_desc_max'           => $description_max_default,
				'default_excerpt_min'        => $excerpt_min_default,
				'default_excerpt_max'        => $excerpt_max_default,
				'check_featured_image'       => $check_featured_image,
			)
		);
		exit;
	}

	/**
	 * Get the restore global settings completed popup modal
	 */
	public function get_restore_global_setting_completed_popup() {
		?>
		<div id="wtai-restore-global-setting-completed" class="wtai-loader-generate" >
			<div class="wtai-loading-restore-global-setting-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-loading-header-details"><span><?php echo wp_kses_post( __( 'Global settings restored', 'writetext-ai' ) ); ?></span></div>
						<div class="wtai-loading-button-action"><span><?php echo wp_kses_post( __( 'OK', 'writetext-ai' ) ); ?></span></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Get premium popup modal
	 */
	public function get_premium_modal() {
		include_once WTAI_ABSPATH . 'templates/admin/metabox/premium-modal.php';
	}

	/**
	 * Save product reference by user.
	 */
	public function tag_extension_review_as_done() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$success = 0;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$review_ids = isset( $_POST['review_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['review_ids'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$field_type = isset( $_POST['field_type'] ) ? sanitize_text_field( wp_unslash( $_POST['field_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$product_id = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$results = array();
				if ( $review_ids && $field_type && $product_id ) {
					$results = apply_filters( 'wtai_save_product_extension_review', array(), $review_ids, $product_id, array( $field_type ) );
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'message' => $message,
					'success' => $success,
					'results' => $results,
				)
			);

			exit;
		}
	}

	/**
	 * Preprocess ajax image before using them ini generation.
	 */
	public function preprocess_images() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$success = 0;
		$message = '';
		if ( $is_ajax ) {
			$alt_image_ids = array();

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['altimages'] ) ) {
					$alt_image_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$alt_image_error_ids = array();

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['altImageIdsError'] ) ) {
					$alt_image_error_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altImageIdsError'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altImageIdsError'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altImageIdsError'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				$product_ids            = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$include_featured_image = isset( $_POST['includeFeaturedImage'] ) ? sanitize_text_field( wp_unslash( $_POST['includeFeaturedImage'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$browsertime            = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$has_normal_field_type  = isset( $_POST['has_normal_field_type'] ) ? sanitize_text_field( wp_unslash( $_POST['has_normal_field_type'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$image_ids_to_process = array();
				if ( 1 === intval( $include_featured_image ) ) {
					$product_ids_array = ( false !== strpos( $product_ids, ',' ) ) ? explode( ',', $product_ids ) : array( $product_ids );

					foreach ( $product_ids_array as $product_id ) {
						$featured_image_id = get_post_thumbnail_id( $product_id );
						if ( $featured_image_id ) {
							$image_ids_to_process[] = $featured_image_id;
						}
					}
				}

				if ( $alt_image_ids ) {
					$product_ids_array = ( false !== strpos( $product_ids, ',' ) ) ? explode( ',', $product_ids ) : array( $product_ids );
					foreach ( $product_ids_array as $product_id ) {
						$current_alt_image_ids = wtai_get_product_image( $product_id );
						foreach ( $current_alt_image_ids as $image_id ) {
							foreach ( $alt_image_ids as $ai_inner_alt_id ) {
								if ( intval( $ai_inner_alt_id ) === intval( $image_id ) ) {
									$image_ids_to_process[] = $image_id;
									break;
								}
							}
						}
					}
				}

				$image_ids_to_process = array_filter( $image_ids_to_process );

				$results          = array();
				$error_process    = array();
				$error_images     = array();
				$error_alt_images = array();
				$error_message    = '';
				if ( $image_ids_to_process ) {
					$image_ids_to_process = array_unique( $image_ids_to_process );
					$image_ids_to_process = array_values( $image_ids_to_process );

					foreach ( $image_ids_to_process as $image_id ) {
						// Make sure the image is uploaded in the API.
						$image_api_data = wtai_get_image_for_api_generation( $product_ids, $image_id, $browser_time, false );

						if ( isset( $image_api_data['error'] ) ) {
							$image_url      = wp_get_attachment_url( $image_id );
							$image_filename = basename( $image_url );

							$error_process[] = array(
								'image_id'       => $image_id,
								'image_url'      => $image_url,
								'image_filename' => $image_filename,
							);

							$error_images[] = $image_filename;

							foreach ( $alt_image_ids as $alt_image_id ) {
								if ( intval( $alt_image_id ) === intval( $image_id ) ) {
									$error_alt_images[] = $image_id;
								}
							}
						} else {
							$results[ $image_id ] = $image_api_data;
						}
					}

					if ( $error_alt_images ) {
						$error_alt_images = array_unique( $error_alt_images );
					}

					if ( $error_images || $alt_image_error_ids ) {
						ob_start();
						?>
						<div class="wtai-error-header-wrap" >
							<div class="wtai-error-header" >
								<?php echo wp_kses_post( __( 'The following image/s are invalid:', 'writetext-ai' ) ); ?>
							</div>
							<div class="wtai-error-description">
								<?php
								if ( ! $results && ! $has_normal_field_type ) {
									echo wp_kses_post( __( 'Please upload a different image and try again.', 'writetext-ai' ) );
								} else {
									echo wp_kses_post( __( 'You can proceed with text generation without the invalid image(s) being taken into consideration, or cancel and upload a different image to try again.', 'writetext-ai' ) );
								}
								?>
							</div>
						</div>
						<div class="wtai-error-thumbnail-wrap" >
							<div class="wtai-error-thumbnail-item" >
								<?php
								if ( $alt_image_error_ids ) {
									$alt_image_error_ids = array_unique( $alt_image_error_ids );
									foreach ( $alt_image_error_ids as $alt_image_id ) {
										if ( $alt_image_id && is_numeric( $alt_image_id ) ) {
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
									}
								}

								foreach ( $error_process as $alt_image_data ) {
									$alt_image_id       = $alt_image_data['image_id'];
									$gallery_image_data = wp_get_attachment_image_src( $alt_image_id, 'thumbnail' );
									$product_thumbnail  = $gallery_image_data[0];
									$image_url          = $alt_image_data['image_url'];
									$image_filename     = $alt_image_data['image_filename'];

									// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
									if ( $alt_image_error_ids && in_array( $alt_image_id, $alt_image_error_ids ) ) {
										continue;
									}
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
						$error_message = ob_get_clean();
					}
				}

				$success = 1;
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$success_ids = is_array( $results ) ? array_keys( $results ) : array();

			echo wp_json_encode(
				array(
					'message'              => $message,
					'success'              => $success,
					'results'              => $results,
					'success_ids'          => $success_ids,
					'error_process'        => $error_process,
					'error_alt_images'     => $error_alt_images,
					'error_message'        => $error_message,
					'image_ids_to_process' => $image_ids_to_process,
					'alt_image_error_ids'  => $alt_image_error_ids,
				)
			);

			exit;
		}
	}

	/**
	 * Loader for image generation.
	 */
	public function preprocess_image_loader() {
		?>
		<div id="wtai-preprocess-image-loader" class="wtai-loader-generate wtai-preprocess-image-loader" >
			<div class="wtai-loading-preprocess-image-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-bulk-generate-check-ico-wrap">
							<span class="wtai-bulkgenerate-check-ico" ></span>
						</div>
						<div class="wtai-loading-header-details">
							<span><?php echo wp_kses_post( __( 'Preparing images for generation. Please do not reload or close this page.', 'writetext-ai' ) ); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Confirmation proceed loader.
	 */
	public function image_confirmation_proceed_loader() {
		?>
		<div id="wtai-confirmation-proceed-image-loader" class="wtai-loader-generate" >
			<div class="wtai-loading-preprocess-image-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-bulk-generate-error-ico-wrap">
							<span class="wtai-bulk-generate-error-ico" ></span>
						</div>
						<div class="wtai-loading-header-details">
							<span class="wtai-error-message-container" ></span>
						</div>
					</div>

					<div class="wtai-loading-action-wrapper" >
						<div class="wtai-loading-actions-container" >
							<a href="#" class="button action-bulk-image-process button-primary" ><?php echo wp_kses( __( 'Proceed', 'writetext-ai' ), 'post' ); ?></a>
							<a href="#" class="button action-bulk-image-process-cancel"  ><?php echo wp_kses( __( 'Cancel', 'writetext-ai' ), 'post' ); ?></a>
							<a href="#" class="button action-bulk-image-process-ok-cancel button-primary" style="display: none;" ><?php echo wp_kses( __( 'OK', 'writetext-ai' ), 'post' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Confirmation proceed loader.
	 */
	public function image_confirmation_proceed_bulk_loader() {
		?>
		<div id="wtai-confirmation-proceed-image-bulk-loader" class="wtai-loader-generate" >
			<div class="wtai-loading-preprocess-image-container wtai-d-flex">
				<div class="wtai-loading-details-container">
					<div class="wtai-loading-wtai-header-wrapper">
						<div class="wtai-bulk-generate-error-ico-wrap">
							<span class="wtai-bulk-generate-error-ico" ></span>
						</div>
						<div class="wtai-loading-header-details">
							<span class="wtai-error-message-container" ></span>
						</div>
					</div>

					<div class="wtai-loading-action-wrapper" >
						<div class="wtai-loading-actions-container" >
							<a href="#" class="button wtai-action-bulk-image-process-bulk button-primary" ><?php echo wp_kses( __( 'Proceed', 'writetext-ai' ), 'post' ); ?></a>
							<a href="#" class="button wtai-action-bulk-image-process-bulk-cancel"  ><?php echo wp_kses( __( 'Cancel', 'writetext-ai' ), 'post' ); ?></a>
							<a href="#" class="button wtai-action-bulk-image-process-bulk-ok-cancel button-primary" style="display: none;" ><?php echo wp_kses( __( 'OK', 'writetext-ai' ), 'post' ); ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Custom head meta in frontend product page.
	 */
	public function add_product_custom_body_class() {
		// Display custom verification.
		$domain_id = preg_replace( '(^https?://)', '', site_url() );
		?>
		<meta name="wtai-verification" content="<?php echo esc_attr( sha1( $domain_id ) ); ?>" />
		<?php

		if ( function_exists( 'is_product' ) && is_product() ) {
			global $post;
			$post_id = $post->ID;

			?>
			<meta name="wtai-pid" data-platform="WordPress" content="<?php echo esc_attr( $post_id ); ?>" />
			<meta name="wtai-fid" content="page title,page description,product description,excerpt,open graph text" />
			<?php
		}
	}

	/**
	 * Get alt image generated values.
	 */
	public function wtai_get_alt_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax         = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results         = array();
		$message         = '';
		$refresh_credits = 0;

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			if ( $is_ajax ) {
				$product_id      = isset( $_POST['product_id'] ) ? sanitize_text_field( wp_unslash( $_POST['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$refresh_credits = isset( $_POST['refresh_credits'] ) ? sanitize_text_field( wp_unslash( $_POST['refresh_credits'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$alt_image_ids = array();

				// phpcs:ignore WordPress.Security.NonceVerification
				if ( isset( $_POST['altimages'] ) ) {
					$alt_image_ids = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimages'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				}

				if ( $alt_image_ids ) {
					$results = apply_filters( 'wtai_get_alt_text_for_images', array(), $product_id, $alt_image_ids, false );
				}
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		$refresh_credits_bool = false;
		if ( 1 === intval( $refresh_credits ) ) {
			$refresh_credits_bool = true;
		}

		$account_credit_details = wtai_get_account_credit_details( $refresh_credits_bool );
		$is_premium             = $account_credit_details['is_premium'];
		$available_credit_count = $credit_account_details['available_credits'];

		$is_premium             = $is_premium ? '1' : '0';
		$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

		echo wp_json_encode(
			array(
				'success'                => 1,
				'results'                => $results,
				'available_credit_label' => $available_credit_label,
				'message'                => $message,
			)
		);

		exit;
	}

	/**
	 * Render admin mobile footer.
	 */
	public function render_wtai_admin_mobile_footer() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) &&
			( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
			include WTAI_ABSPATH . 'templates/admin/footer-mobile.php';
		}
	}

	/**
	 * Render intent tooltip.
	 */
	public function render_intent_tooltip() {
		include WTAI_ABSPATH . 'templates/admin/intent-tooltip.php';
	}

	/**
	 * Render freemium badge.
	 */
	public function render_wtai_freemium_badge() {
		include WTAI_ABSPATH . 'templates/admin/freemium-badge.php';
	}

	/**
	 * Render freemium popup.
	 *
	 * @param array $account_credit_details Account credit details.
	 */
	public function render_wtai_freemium_popup( $account_credit_details = array() ) {
		if ( ! $account_credit_details ) {
			$account_credit_details = array();
		}

		include WTAI_ABSPATH . 'templates/admin/freemium-popup.php';
	}

	/**
	 * AJAX function when closing the freemium popup.
	 */
	public function freemium_popup_close() {
		define( 'WTAI_DOING_AJAX', true );

		$message     = '';
		$success     = 0;
		$api_results = array();

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-freemium-popup-nonce' ) ) {
			// Do API call to close the popup.
			$api_results = apply_filters( 'wtai_record_freemium_seen_api', array() );

			if ( 200 === intval( $api_results['http_header'] ) ) {
				$success = 1;

				// Refresh the credit account details transient.
				if ( defined( 'WTAI_CREDIT_ACCOUNT_DETAILS' ) ) {
					define( 'WTAI_CREDIT_ACCOUNT_DETAILS', wtai_get_account_credit_details( true ) );
				}
			} else {
				$message = WTAI_GENERAL_ERROR_MESSAGE;
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'success'     => $success,
				'message'     => $message,
				'api_results' => $api_results,
			)
		);
		exit;
	}

	/**
	 * AJAX to get global settings for tone, style and audiences.
	 */
	public function get_global_settings_ajax() {
		define( 'WTAI_DOING_AJAX', true );

		$message = '';
		$success = 0;

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			$success = 1;

			$style_default   = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
			$tones_array     = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
			$audiences_array = apply_filters( 'wtai_global_settings', 'wtai_installation_audiences' );

			$tones = '';
			if ( ! empty( $tones_array ) ) {
				$tones = implode( ',', $tones_array );
			}

			$audiences = '';
			if ( ! empty( $audiences_array ) ) {
				$audiences = implode( ',', $audiences_array );
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'success'   => $success,
				'message'   => $message,
				'styles'    => $style_default,
				'tones'     => $tones,
				'audiences' => $audiences,
			)
		);
		exit;
	}

	/**
	 * Dismiss the popup blocker notice.
	 */
	public function dismiss_popup_blocker_notice() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		$message = '';

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-popupblocker-nonce' ) ) {
			if ( $is_ajax ) {
				$current_user_id = get_current_user_id();

				update_user_meta( $current_user_id, 'wtai_popup_blocker_notice_dismissed', 1 );
			}
		} else {
			$message = WTAI_INVALID_NONCE_MESSAGE;
		}

		echo wp_json_encode(
			array(
				'success' => 1,
				'message' => $message,
			)
		);

		exit;
	}
}
global $wtai_product_dashboard;
$wtai_product_dashboard = new WTAI_Product_Dashboard();