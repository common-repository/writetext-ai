<?php
/**
 * Product category hooks and filter class for WTA
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WTAI Product dashboard class.
 */
class WTAI_Product_Category extends WTAI_Init {
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

		add_action( 'admin_menu', array( $this, 'get_submenu_page' ) );

		add_action( 'wp_ajax_wtai_get_category_tooltip_text', array( $this, 'get_category_tooltip_text_callback' ) );

		add_action( 'wtai_edit_category_form', array( $this, 'get_edit_category_form' ), 10, 1 );

		// Metaboxes for category page.
		add_action( 'wtai_product_category_main_metabox', array( $this, 'get_keyword_list' ) );
		add_action( 'wtai_product_category_main_metabox', array( $this, 'get_filter_all_list' ) );

		// AJAX for wtai_comparision saving.
		add_action( 'wp_ajax_wtai_comparison_category_user_check', array( $this, 'process_user_comparison_callback' ) );

		// Hide category guidelines.
		add_action( 'wp_ajax_wtai_user_hide_category_guidelines', array( $this, 'process_wtai_user_hide_guidelines_callback' ) );

		// Get keywords data, suggested audience and country data.
		add_action( 'wp_ajax_wtai_category_data', array( $this, 'get_category_data_callback' ) );

		// Get category data.
		add_action( 'wp_ajax_wtai_single_category_data_text', array( $this, 'get_category_field_data_callback' ) );

		// Add or remove representative product AJAX.
		add_action( 'wp_ajax_wtai_process_representative_product', array( $this, 'process_representative_product' ) );

		// Load more representative products.
		add_action( 'wp_ajax_wtai_load_more_representative_product', array( $this, 'load_more_representative_product' ) );

		// Search representative products.
		add_action( 'wp_ajax_wtai_search_representative_product', array( $this, 'search_representative_product' ) );

		// Save other category details.
		add_action( 'wp_ajax_wtai_othercategorydetails_text', array( $this, 'process_othercategorydetails_callback' ) );

		// Save preselected types.
		add_action( 'wp_ajax_wtai_record_category_preselected_types', array( $this, 'record_category_preselected_types_callback' ) );

		// Preprocess the category image.
		add_action( 'wp_ajax_wtai_preprocess_category_images', array( $this, 'preprocess_images' ), 10 );

		// Generate text for ajax category.
		add_action( 'wp_ajax_wtai_generate_category_text', array( $this, 'get_generate_text' ) );

		// Transfer or save generated text.
		add_action( 'wp_ajax_wtai_transfer_or_save_category_text', array( $this, 'transfer_or_save_category_text' ) );

		// Global history popup.
		add_action( 'wp_ajax_wtai_global_category_history', array( $this, 'get_global_history_callback' ) );

		// Single history popup.
		add_action( 'wp_ajax_wtai_single_category_history', array( $this, 'get_category_history_callback' ) );

		// Mark as reviewed.
		add_action( 'wp_ajax_wtai_category_review_check', array( $this, 'process_mark_as_review_callback' ) );

		// Highlight category pronouns.
		add_action( 'wp_ajax_wtai_user_highlight_pronouns_category_check', array( $this, 'process_user_highlight_pronouns_callback' ) );

		// HIghlight category check.
		add_action( 'wp_ajax_wtai_user_category_highlight_check', array( $this, 'process_user_highlight_callback' ) );

		// Set category image state.
		add_action( 'wp_ajax_wtai_set_category_image_state', array( $this, 'set_category_image_state' ) );
	}

	/**
	 * Enqueue admin scripts.
	 */
	public function enqueue_admin_script() {
		$cache_buster_version = WTAI_VERSION . '-' . wp_rand();

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && 'write-text-ai-category' === $_GET['page'] ) {
			$disallowed_combinations = apply_filters( 'wtai_get_disallowed_combinations', array(), false );

			wp_enqueue_style( 'wtai-admin', WTAI_DIR_URL . 'assets/css/admin.css', array(), $cache_buster_version );

			if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
				$web_token = apply_filters( 'wtai_web_token', '' );

				$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
				$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
				$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;

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

				$formal_informal_pronouns = apply_filters( 'wtai_get_formal_informal_pronouns', array(), true );
				$formal_language_support  = 0;
				if ( wtai_is_formal_informal_lang_supported() ) {
					$formal_language_support = 1;
				}

				$version_outdated         = get_option( 'wtai_latest_version_outdated' );
				$version_outdated_message = get_option( 'wtai_latest_version_message' );

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

				wp_register_style( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/css/tooltipster.bundle.min.css', array(), 'v4.2.8' );

				wp_enqueue_style( 'wtai-admin-installed', WTAI_DIR_URL . 'assets/css/admin-installed.css', array( 'wtai-toolstipster' ), $cache_buster_version );
				wp_enqueue_style( 'wtai-admin-category', WTAI_DIR_URL . 'assets/css/admin-category.css', array(), $cache_buster_version );

				wp_enqueue_style( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.default.css', array(), $cache_buster_version );
				wp_enqueue_script( 'wtai-selectize', WTAI_DIR_URL . 'assets/lib/selectize.min.js', array( 'jquery' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				wp_deregister_script( 'autosave' );
				wp_enqueue_editor();
				wp_enqueue_script( 'jquery-ui-datepicker' );

				wp_register_style( 'wtai-jquery-ui', WTAI_DIR_URL . 'assets/lib/jquery-ui.css', array(), 'v1.12.1' );
				wp_enqueue_style( 'wtai-jquery-ui' );

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

				// Tooltipster script.
				wp_register_script( 'wtai-toolstipster', WTAI_DIR_URL . 'assets/js/tooltipster.bundle.min.js', array( 'jquery' ), 'v4.2.8' ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

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

				/* translators: %s: Max keyword length */
				$add_disabled_tooltip = sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one to the "Keywords to be included in your text".', 'writetext-ai' ), $max_keyword_count );

				/* translators: %s: Max keyword length */
				$max_keyword_tooltip_message = wp_kses_post( sprintf( __( 'You can only add up to %s. Remove a keyword to add a new one.', 'writetext-ai' ), $max_manual_keyword_count ) );

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
						'productNameNotAllowedMsg'         => __( 'The category name is considered by default in generating text. Please add a different keyword.', 'writetext-ai' ),
						'productNameNotAllowedMsgFromPlus' => __( 'This keyword is the same as the category name and is already considered by default when generating text. Please add a different keyword.', 'writetext-ai' ),
						'keywordTrashDisabledTooltip'      => __( 'Remove this keyword from the "Keywords to be included in your text" before deleting it.', 'writetext-ai' ),
						'keywordPlusDisabledTooltip'       => $add_disabled_tooltip,
						'keywordPlusTooltip'               => __( 'Add as target keyword', 'writetext-ai' ),
						'keywordMinusTooltip'              => __( 'Remove as target keyword', 'writetext-ai' ),
						'keywordTrashTooltip'              => __( 'Delete keyword', 'writetext-ai' ),
						'emptyRankMessage'                 => __( 'Click the "Start AI-powered keyword analysis" button to get started.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyRankMessageWithAnalysis'     => __( 'This page is not ranking for any keywords as of %s. <br><br>You may click the "Start AI-powered keyword analysis" button to refresh ranking data for the whole domain. We recommend doing this after a month has passed since your last request — any less than that may not return any significant results.', 'writetext-ai' ),
						'emptyCompetitorMessage'           => __( 'Click the “Start AI-powered keyword analysis” button to get started. If there are no keywords you are currently ranking for or selected keywords to be included in your text, WriteText.ai will search for possible competitors you may have based on your category name.', 'writetext-ai' ),
						/* translators: %s: formatted date and time */
						'emptyCompetitorMessageWithAnalysis' => __( 'No competitor keywords found as of %s. <br><br>Select or manually type other keywords and try again.', 'writetext-ai' ),
						'emptySuggestedMessage'            => __( 'Click the “Start AI-powered keyword analysis” button to get data for your manually typed keywords (keyword ideas, search volume, and difficulty).', 'writetext-ai' ),
						'emptySuggestedMessageWithAnalysis' => __( 'No keyword data received. Check for misspellings in your keyword(s) or use a different keyword and try again.', 'writetext-ai' ),
						'manualKeywordTooltipMessage'      => __( 'Add your own keyword here...', 'writetext-ai' ),
						'maxManualKeywordTooltipMessage'   => $max_keyword_tooltip_message,
					)
				);

				// Main category script.
				wp_enqueue_script( 'wtai-admin-category', WTAI_DIR_URL . 'assets/js/admin-category.js', array( 'jquery', 'wtai-toolstipster', 'jquery-ui-datepicker', 'wtai-selectize', 'wp-tinymce', 'select2', 'wtai-admin-common-functions', 'wtai-admin-filter', 'wtai-admin-keywords', 'wtai-admin-streaming' ), $cache_buster_version ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter

				$current_user_can_generate = wtai_current_user_can( 'writeai_generate_text' ) ? '1' : '0';

				$formal_language_support = 0;
				if ( wtai_is_formal_informal_lang_supported() ) {
					$formal_language_support = 1;
				}

				$max_representative_product = isset( $global_rule_fields['maxRepresentativeProducts'] ) ? $global_rule_fields['maxRepresentativeProducts'] : WTAI_MAX_REPRESENTATIVE_PRODUCT;

				/* translators: %s: Max keyword length */
				$rep_product_disabled_tooltip = sprintf( __( 'You can only add up to %s products. Please remove an existing product to add a new one.', 'writetext-ai' ), $max_representative_product );

				$popupblocker_nonce                  = wp_create_nonce( 'wtai-popupblocker-nonce' );
				$popup_blocker_notice_dismissed_list = wtai_get_popup_blocker_dismiss_state() ? '1' : '';

				wp_localize_script(
					'wtai-admin-category',
					'WTAI_OBJ',
					array(
						'ajax_url'                      => admin_url( 'admin-ajax.php' ),
						'admin_page_settings'           => admin_url( 'admin.php?page=write-text-ai' ),
						'disallowedCombinations'        => $disallowed_combinations,
						'is_premium'                    => WTAI_PREMIUM ? '1' : '0',
						'loading'                       => __( 'Loading', 'writetext-ai' ),
						'text_limit'                    => array(
							'page_title'       => WTAI_PAGE_TITLE_TEXT_LIMIT,
							'page_description' => WTAI_MAX_PAGE_DESCRIPTION_LIMIT,
							'open_graph'       => WTAI_MAX_OPEN_GRAPH_LIMIT,
						),
						/* translators: %char%: Character count placeholder */
						'char'                          => __( '%char% Char', 'writetext-ai' ),
						/* translators: %words%: Words count placeholder */
						'words'                         => __( '%words% word/s', 'writetext-ai' ),
						'current_user_can_generate'     => $current_user_can_generate,
						'generatedStatusText'           => '(' . __( 'generated', 'writetext-ai' ) . ')',
						'notGeneratedStatusText'        => '(' . __( 'not generated', 'writetext-ai' ) . ')',
						'tinymcelinktext1'              => __( 'Insert/Edit link', 'writetext-ai' ),
						'tinymcelinktext2'              => __( 'Insert', 'writetext-ai' ),
						'tinymcelinktext3'              => __( 'Cancel', 'writetext-ai' ),
						'tinymcelinktext4'              => __( 'Link text', 'writetext-ai' ),
						'tinymcelinktext5'              => __( 'Link URL', 'writetext-ai' ),
						'tinymcelinktext6'              => __( 'Open link in new window', 'writetext-ai' ),
						'WTAI_DIR_URL'                  => WTAI_DIR_URL,
						'maxSemanticKeywordMessage'     => __( 'You have selected the maximum number of semantic keywords.', 'writetext-ai' ),
						'keyword_max'                   => isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD,
						'formalLanguageSupport'         => $formal_language_support,
						'tooltipActiveTransferSingle'   => __( 'Transfer', 'writetext-ai' ),
						'tooltipInactiveTransferSingle' => __( 'Nothing to transfer / Already transferred', 'writetext-ai' ),
						'transfer_btn_label'            => __( 'Transfer to WordPress', 'writetext-ai' ),
						'option_choices'                => WTAI_MAX_CHOICE,
						'max_representative_products'   => $max_representative_product,
						'empty_category_image'          => '<p class="wtai-cat-no-image-wrap" >' . __( 'No image found.', 'writetext-ai' ) . '</p>',
						'confirm_leave'                 => __( 'You have unsaved changes. Are you sure you want to leave this page?', 'writetext-ai' ),
						'LoadMoreHistory'               => __( 'Load more', 'writetext-ai' ),
						'pageSize'                      => WTAI_MAX_HISTORY_PAGESIZE,
						'tooltipDisableRewriteMessage2' => __( 'Rewrite is unavailable when no WordPress text is found.', 'writetext-ai' ),
						'formalInformalPronouns'        => $formal_informal_pronouns,
						'versionOutdated'               => $version_outdated,
						'versionOutdatedMessage'        => $version_outdated_message,
						'translation_ongoing'           => WTAI_TRANSLATION_ONGOING ? '1' : '0',
						'translationOngoingMessage'     => 'Notice: Translation of plugin help text and labels is ongoing. Please stay tuned.', // This text is intentionally not translated as it is a notice for translators.
						'isCurrentLocaleEN'             => wtai_is_current_locale_en() ? '1' : '0',
						'noHistoryMessage'              => __( 'No log found.', 'writetext-ai' ),
						'maxRepDisabledTooltip'         => $rep_product_disabled_tooltip,
						'disablePopupBlockerStatus'     => $popup_blocker_notice_dismissed_list,
						'popupblocker_nonce'            => $popupblocker_nonce,
						'disablePopupBlockerMessage'    => __( '<strong>Warning:</strong> Disable all pop-up blockers then refresh this page. WriteText.ai does not work when you have pop-up blockers enabled.', 'writetext-ai' ),
					)
				);

				if ( wtai_get_hide_guidelines_user_preference( 'category' ) ) {
					wp_add_inline_script(
						'wtai-admin-category',
						'
							jQuery(document).ready(function($){
								$(".wtai-step-guideline").addClass("wtai-hide");
							});
					',
						'before'
					);
				}

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
		}
	}

	/**
	 * Get settings sub menu.
	 */
	public function get_submenu_page() {
		if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && wtai_current_user_can( 'writeai_generate_text' ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
			add_submenu_page(
				'write-text-ai',
				__( 'WriteText.ai - Categories', 'writetext-ai' ),
				__( 'Categories', 'writetext-ai' ),
				'read',
				'write-text-ai-category',
				array( $this, 'get_category_dashboard_callback' ),
				53
			);
		}
	}

	/**
	 * Display callback for the submenu page.
	 */
	public function get_category_dashboard_callback() {
		if ( false === wtai_is_allowed_beta_language() ) {
			return;
		}

		// Display installation if token is expired.
		if ( wtai_is_token_expired() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit;
		}

		// TODO: check api base url is valid or not.
		if ( ! wtai_has_api_base_url() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit;
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
				require_once WTAI_ABSPATH . 'includes/class-wtai-product-category-list-table.php';
			}
			$wtai_category_list_table = new WTAI_Product_Category_List_Table();
			$wtai_category_list_table->prepare_items();

			include_once WTAI_ABSPATH . 'templates/admin/dashboard-category.php';
		} else {
			wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
			exit;
		}
	}

	/**
	 * Tooltip text.
	 */
	public function get_category_tooltip_text_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$text = '';
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$category_id  = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$obj_name_key = isset( $_POST['colgrp'] ) ? sanitize_text_field( wp_unslash( $_POST['colgrp'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$column_name = $obj_name_key;

				$seo_values = wtai_get_category_values( $category_id, $column_name );
				$text       = $seo_values[ $column_name ];
			} else {
				$text = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'text'        => $text,
					'category_id' => $category_id,
					'seo_values'  => $seo_values,
				)
			);
			exit;
		}
	}

	/**
	 * Get edit category html.
	 *
	 * @param string $width Width.
	 */
	public function get_edit_category_form( $width = '' ) {
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
					$columns                = $this->get_category_fields_list();
					$global_rule_fields     = apply_filters( 'wtai_global_rule_fields', array() );
					$attributes             = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
					$wtai_preselected_types = wtai_get_user_preselected_types();

					include_once WTAI_ABSPATH . 'templates/admin/category.php';

					do_action( 'wtai_product_single_main_footer' );
				?>
			</div>
			
		</div>
		<?php
	}

	/**
	 * Get keyword list
	 */
	public function get_keyword_list() {
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
		$max_keywords       = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;

		include_once WTAI_ABSPATH . 'templates/admin/metabox/keyword.php';
	}

	/**
	 * Get filter all list
	 */
	public function get_filter_all_list() {
		$style_and_tones_list = $this->get_product_text_style_tone_audiences( '', 'form_style_tone', array( 'tones', 'styles' ) );

		$current_user_id   = get_current_user_id();
		$tones_user_pref   = get_user_meta( $current_user_id, 'wtai_tones_options_user_preference', true );
		$style_user_pref   = get_user_meta( $current_user_id, 'wtai_styles_options_user_preference', true );
		$custom_tones_cb   = get_user_meta( $current_user_id, 'wtai_tones_custom_user_preference', true );
		$custom_tones_text = get_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', true );

		$style_and_tones_count = 0;
		$tones                 = apply_filters( 'wtai_global_settings', 'wtai_installation_tones' );
		if ( is_array( $tones ) && ! empty( $tones ) ) {
			$style_and_tones_count += count( $tones );
		}

		if ( '' !== $tones_user_pref[0] ) {
			$style_and_tones_count = count( $tones_user_pref );
		} elseif ( isset( $custom_tones_cb ) && '' !== $custom_tones_cb && '' !== $custom_tones_text ) {
			$style_and_tones_count = 1;
		}

		$styles                 = apply_filters( 'wtai_global_settings', 'wtai_installation_styles' );
		$style_and_tones_count += ( $styles ) ? 1 : 0;

		$audience_list = $this->get_product_text_style_tone_audiences( '', 'form_audience', array( 'audiences' ) );
		$audiences     = wtai_get_user_preference_audiences();

		if ( is_array( $audiences ) && ! empty( $audiences ) ) {
			$audience_cont = count( $audiences );
		} else {
			$audience_cont = 0;
		}

		include_once WTAI_ABSPATH . 'templates/admin/metabox/category-filter.php';
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
					update_user_meta( $current_user_id, 'wtai_comparison_category_cb', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_comparison_category_cb' );
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
					update_user_meta( $current_user_id, 'wtai_hide_category_guidelines', $value );

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
	 * Get product data callback.
	 */
	public function get_category_data_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$success     = 0;
			$message     = '';
			$post_return = array();
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$category_id = intval( $category_id );

				$category_term = get_term( $category_id, 'product_cat' );

				$category_name = $category_term->name;

				$keyword_ideas = array();

				$keywords_data = apply_filters( 'wtai_keyword_values', array(), $category_id, 'input', false, 'category' );

				$keyword_input           = array();
				$category_title_semantic = array();
				$k_ctr                   = 0;
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
						$category_title_semantic = array(
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
				$post_return['product_title_semantic'] = $category_title_semantic;

				$wtai_review                = get_term_meta( $category_id, 'wtai_review', true );
				$post_return['wtai_review'] = $wtai_review ? 1 : 0;

				$wtai_highlight                = wtai_get_user_highlight_cb( 'category' );
				$post_return['wtai_highlight'] = $wtai_highlight ? 1 : 0;

				$post_return['post_permalink'] = get_term_link( $category_id );
				$post_return['post_title']     = $category_name;

				$locale          = apply_filters( 'wtai_language_code_by_product', wtai_get_site_language(), $category_id );
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

				$product_selected_location_code = wtai_get_product_location_code( $category_id );
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
					'type'     => 'Category',
				);

				$suggested_audiences               = apply_filters( 'wtai_get_suggested_audiences_text', array(), $category_id, $sa_values, 0 );
				$post_return['suggested_audience'] = $suggested_audiences;

				$wtai_highlight                = wtai_get_user_highlight_cb( 'category' );
				$post_return['wtai_highlight'] = $wtai_highlight ? 1 : 0;

				$account_credit_details = wtai_get_account_credit_details();
				$is_premium             = $account_credit_details['is_premium'];

				$post_return['is_premium'] = $is_premium ? '1' : '0';

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
	 * Get category field data
	 */
	public function get_category_field_data_callback() {
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

				$category_id = isset( $_POST['category_id'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$category_id = intval( $category_id );

				$fields = apply_filters( 'wtai_category_fields', array() );
				$fields = array_keys( $fields );

				// Call api.
				$api_fields = array(
					'fields'               => $fields,
					'includeUpdateHistory' => true,
					'historyCount'         => 1,
				);

				$api_results = apply_filters( 'wtai_generate_category_text', array(), $category_id, $api_fields );

				$term       = get_term( $category_id, 'product_cat' );
				$seo_values = wtai_get_category_values( $category_id );

				$has_generated_text              = 0;
				$has_platform_text               = 0;
				$has_reviewed_text               = 0;
				$has_generated_not_reviewed_text = 0;
				$has_transferred_text            = 0;
				$transferred_ctr                 = 0;
				$generated_ctr                   = 0;
				$reviewed_ctr                    = 0;
				foreach ( $fields as $field ) {
					$api_field_key = $field;

					$result[ $field . '_value' ] = isset( $seo_values[ $field ] ) ? $seo_values[ $field ] : '';

					$result[ $field . '_value_string_count' ] = $result[ $field . '_value' ] ? count_chars( $result[ $field . '_value' ] ) : 0;
					$result[ $field . '_value_words_count' ]  = $result[ $field . '_value' ] ? str_word_count( $result[ $field . '_value' ] ) : 0;

					$result[ $field . '_options' ] = ( $api_results[ $category_id ][ $api_field_key ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return str_replace( '\\', '', $option );
						},
						$api_results[ $category_id ][ $api_field_key ][0]['outputs']
					) : array();

					$result[ $field . '_options_string_count' ] = ( $api_results[ $category_id ][ $api_field_key ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return strlen( $option );
						},
						$api_results[ $category_id ][ $api_field_key ][0]['outputs']
					) : array();
					$result[ $field . '_options_words_count' ]  = ( $api_results[ $category_id ][ $api_field_key ][0]['outputs'] ) ? array_map(
						function ( $option ) {
							return str_word_count( $option );
						},
						$api_results[ $category_id ][ $api_field_key ][0]['outputs']
					) : array();

					$result[ $field . '_id' ] = ( $api_results[ $category_id ][ $api_field_key ][0]['id'] ) ? $api_results[ $category_id ][ $api_field_key ][0]['id'] : '';

					$result_api_field_value = ( $api_results[ $category_id ][ $api_field_key ][0]['value'] ) ? str_replace( '\\', '', $api_results[ $category_id ][ $api_field_key ][0]['value'] ) : '';

					// Trim newlines, spaces, and non-breaking spaces from the end.
					$result_api_field_value = wtai_remove_trailing_new_lines( $result_api_field_value );

					if ( 'category_description' === $field ) {
						$result[ $field ] = wtai_remove_trailing_new_lines( wpautop( $result_api_field_value ) );
					} else {
						$result[ $field ] = wtai_remove_trailing_new_lines( wpautop( nl2br( $result_api_field_value ) ) );
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

					if ( isset( $result[ $field . '_value' ] ) && 'category_description' === $field ) {
						$result[ $field . '_value' ] = wtai_remove_trailing_new_lines( wpautop( $current_wp_value ) );
					} else {
						$result[ $field . '_value' ] = wtai_remove_trailing_new_lines( wpautop( nl2br( $current_wp_value ) ) );
					}

					$result[ $field . '_trimmed' ] = wp_trim_words( $result[ $field ], 15, '...' );

					$result[ $field . '_tooltip_enabled' ] = '0';
					if ( $this->ends_with_dots( $result[ $field . '_trimmed' ] ) ) {
						$result[ $field . '_tooltip_enabled' ] = '1';
					}

					$result[ $field . '_string_count' ] = ( $api_results[ $category_id ][ $api_field_key ][0]['value'] ) ? mb_strlen( $api_results[ $category_id ][ $api_field_key ][0]['value'], 'UTF-8' ) : 0;

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
					if ( isset( $api_results[ $category_id ][ $api_field_key ][0]['history'][0] ) ) {
						$field_published = $api_results[ $category_id ][ $api_field_key ][0]['history'][0]['publish'];
						$field_reviewed  = $api_results[ $category_id ][ $api_field_key ][0]['history'][0]['reviewed'];
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
				}

				$result['category_last_activity'] = wtai_get_category_last_activity( $category_id );

				// Other product details.
				$result['wp_product_title']    = $term->name;
				$result['otherproductdetails'] = get_term_meta( $category_id, 'wtai_otherproductdetails', true );

				$result['post_permalink'] = get_term_link( $category_id );

				// Added for autogrid update from edit.
				$time                    = get_term_meta( $category_id, 'wtai_generate_date', true );
				$result['generate_date'] = sprintf(
					/* translators: %1$s: date, %2$s: time */
					__( '%1$s at %2$s' ),
					date_i18n( get_option( 'date_format' ), $time ),
					date_i18n( get_option( 'time_format' ), $time )
				);

				$total_fields_to_compare = count( $fields );

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

				// Get preselected types.
				$wtai_preselected_types           = wtai_get_user_preselected_types( 'category' );
				$result['wtai_preselected_types'] = $wtai_preselected_types;

				$result['has_generated_text']              = $has_generated_text;
				$result['has_reviewed_text']               = $has_reviewed_text;
				$result['is_all_reviewed']                 = $is_all_reviewed;
				$result['is_all_generated_reviewed']       = $is_all_generated_reviewed;
				$result['has_generated_not_reviewed_text'] = $has_generated_not_reviewed_text;
				$result['has_transferred_text']            = $has_transferred_text;
				$result['has_platform_text']               = $has_platform_text;
				$result['is_all_transferred']              = $is_all_transferred;
				$result['transferred_ctr']                 = $transferred_ctr;
				$result['total_fields_to_compare']         = $total_fields_to_compare;
				$result['reviewed_ctr']                    = $reviewed_ctr;
				$result['generated_ctr']                   = $generated_ctr;

				$result['product_default_style'] = wtai_get_user_default_product_style(); // This is global.

				// Get user preference tones.
				$result['preference_tones']     = wtai_get_user_preference_tones(); // This is global.
				$result['preference_styles']    = wtai_get_user_preference_styles(); // This is global.
				$result['preference_audiences'] = wtai_get_user_preference_audiences(); // This is global.

				$max_keyword_char_length       = $global_rule_fields['maxKeywordLength'];
				$result['product_short_title'] = trim( substr( $term->name, 0, $max_keyword_char_length ) );

				$wtai_highlight_pronouns           = get_user_meta( get_current_user_id(), 'wtai_highlight_pronouns_category', true );
				$result['wtai_highlight_pronouns'] = $wtai_highlight_pronouns ? 1 : 0;

				$result['field_product_status'] = ''; // No status for category.

				$hide_step_guide = 0;
				if ( wtai_get_hide_guidelines_user_preference( 'category' ) ) {
					$hide_step_guide = 1;
				}

				$result['hide_step_guide'] = $hide_step_guide;
				$result['api_results']     = $api_results;

				$result['is_premium'] = $is_premium ? '1' : '0';

				// Category image main html.
				$category_image_html           = wtai_get_category_image_html( $category_id );
				$result['category_image_html'] = $category_image_html;
				$result['product_has_image']   = $category_image_html ? '1' : '0';

				$result['available_credit_label'] = $available_credit_label;

				$product_edit_nonce           = wp_create_nonce( 'wtai-product-nonce' );
				$result['product_edit_nonce'] = $product_edit_nonce;

				// Reference product dropdown.
				$reference_dropdown_html           = wtai_get_category_product_list_dropdown_html( $category_id );
				$result['reference_dropdown_html'] = $reference_dropdown_html;

				$representative_products_html           = wtai_get_category_representative_products_html( $category_id );
				$result['representative_products_html'] = $representative_products_html;

				$other_details           = wtai_get_category_other_details( $category_id );
				$result['other_details'] = $other_details;

				$source = get_option( 'wtai_installation_source', '' );

				$result['text_source'] = $source;

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
	 * Process representative product.
	 */
	public function process_representative_product() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		if ( $is_ajax ) {
			$access                  = 0;
			$error                   = '';
			$message                 = '';
			$reference_dropdown_html = '';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$category_id  = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$current_page = isset( $_POST['current_page'] ) ? sanitize_text_field( wp_unslash( $_POST['current_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$total_pages  = isset( $_POST['total_pages'] ) ? sanitize_text_field( wp_unslash( $_POST['total_pages'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$product_ids  = isset( $_POST['product_ids'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$category_id  = intval( $category_id );
					$current_page = intval( $current_page );
					$total_pages  = intval( $total_pages );

					update_term_meta( $category_id, 'wtai_category_representative_products', $product_ids );

					// Reference product dropdown.
					$reference_dropdown_temp_html = '';
					for ( $p = 1; $p <= $current_page; $p++ ) {
						$reference_dropdown_temp_html .= wtai_get_category_product_list_dropdown_html( $category_id, $p, true );
					}

					$reference_dropdown_html .= '<div class="wtai-cat-product-items-wrap" data-category-id="' . esc_attr( $category_id ) . '" 
								data-current-page="' . esc_attr( $p ) . '" 
								data-total-pages="' . esc_attr( $total_pages ) . '" >';
					$reference_dropdown_html .= $reference_dropdown_temp_html;
					$reference_dropdown_html .= '</div>';

					$reference_dropdown_html .= '<div class="wtai-rep-dp-no-products-found">' . __( 'No product/s found.', 'writetext-ai' ) . '</div>';

					if ( $total_pages > 1 && $p < $total_pages ) {
						$reference_dropdown_html .= '<div class="wtai-cat-product-items-load-more" ></div>';
					}

					$access = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$referrer = wp_get_referer();

			$account_credit_details = wtai_get_account_credit_details( true );
			$is_premium             = $account_credit_details['is_premium'];
			$available_credit_count = $credit_account_details['available_credits'];

			$is_premium             = $is_premium ? '1' : '0';
			$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

			$output = array(
				'message'                 => $message,
				'access'                  => $access,
				'error'                   => $error,
				'available_credit_label'  => $available_credit_label,
				'product_ids'             => $product_ids,
				'reference_dropdown_html' => $reference_dropdown_html,
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Load more representative product
	 */
	public function load_more_representative_product() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		if ( $is_ajax ) {
			$access                  = 0;
			$error                   = '';
			$message                 = '';
			$reference_dropdown_html = '';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$category_id  = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$current_page = isset( $_POST['current_page'] ) ? sanitize_text_field( wp_unslash( $_POST['current_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$next_page    = isset( $_POST['next_page'] ) ? sanitize_text_field( wp_unslash( $_POST['next_page'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$total_pages  = isset( $_POST['total_pages'] ) ? sanitize_text_field( wp_unslash( $_POST['total_pages'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$search       = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$category_id  = intval( $category_id );
					$current_page = intval( $current_page );
					$total_pages  = intval( $total_pages );
					$next_page    = intval( $next_page );

					// Reference product dropdown.
					$reference_dropdown_html = wtai_get_category_product_list_dropdown_html( $category_id, $next_page, false, $search );

					$access = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$referrer = wp_get_referer();

			$account_credit_details = wtai_get_account_credit_details( true );
			$is_premium             = $account_credit_details['is_premium'];
			$available_credit_count = $credit_account_details['available_credits'];

			$is_premium             = $is_premium ? '1' : '0';
			$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

			$output = array(
				'message'                 => $message,
				'access'                  => $access,
				'error'                   => $error,
				'available_credit_label'  => $available_credit_label,
				'reference_dropdown_html' => $reference_dropdown_html,
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Search representative product
	 */
	public function search_representative_product() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		if ( $is_ajax ) {
			$access                  = 0;
			$error                   = '';
			$message                 = '';
			$reference_dropdown_html = '';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$category_id = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$search      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$category_id = intval( $category_id );

					// Reference product dropdown.
					$reference_dropdown_html = wtai_get_category_product_list_dropdown_html( $category_id, 1, false, $search, true );

					$access = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			$referrer = wp_get_referer();

			$account_credit_details = wtai_get_account_credit_details( true );
			$is_premium             = $account_credit_details['is_premium'];
			$available_credit_count = $credit_account_details['available_credits'];

			$is_premium             = $is_premium ? '1' : '0';
			$available_credit_label = wtai_get_available_credit_label( $available_credit_count );

			$output = array(
				'message'                 => $message,
				'access'                  => $access,
				'error'                   => $error,
				'available_credit_label'  => $available_credit_label,
				'reference_dropdown_html' => $reference_dropdown_html,
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Process other category details callback.
	 */
	public function process_othercategorydetails_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$account_credit_details = wtai_get_account_credit_details();
		$is_premium             = $account_credit_details['is_premium'];

		$access  = 0;
		$success = 0;
		$message = '';
		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( $is_premium ) {
					$category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$enabled     = isset( $_POST['enabled'] ) ? intval( $_POST['enabled'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$value       = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					update_term_meta( $category_id, 'wtai_othercategorydetails_enabled', $enabled );
					update_term_meta( $category_id, 'wtai_othercategorydetails', $value );
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
	 * Record generate preselected types callback.
	 */
	public function record_category_preselected_types_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$current_user_id = get_current_user_id();
		$is_ajax         = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$selected_types  = array();
		$success         = 0;
		$message         = '';
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$selected_types = isset( $_POST['selectedTypes'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['selectedTypes'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
				$category_id    = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				if ( $selected_types ) {
					$selected_types = array_filter( $selected_types );
				}

				delete_user_meta( $current_user_id, 'wtai_preselected_category_types' );
				update_user_meta( $current_user_id, 'wtai_preselected_category_types', $selected_types );

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

				$category_id            = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$product_ids            = isset( $_POST['product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['product_ids'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
				$include_featured_image = isset( $_POST['includeFeaturedImage'] ) ? sanitize_text_field( wp_unslash( $_POST['includeFeaturedImage'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$browsertime            = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
				$has_normal_field_type  = isset( $_POST['has_normal_field_type'] ) ? sanitize_text_field( wp_unslash( $_POST['has_normal_field_type'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

				$image_ids_to_process = array();
				if ( 1 === intval( $include_featured_image ) ) {
					$category_image_id = get_term_meta( $category_id, 'thumbnail_id', true );
					if ( $category_image_id ) {
						$image_ids_to_process[ $category_image_id ] = array(
							'type'      => 'category',
							'image_id'  => $category_image_id,
							'record_id' => $category_id,
						);
					}
				}

				if ( $product_ids ) {
					$product_ids_array = ( false !== strpos( $product_ids, ',' ) ) ? explode( ',', $product_ids ) : array( $product_ids );

					foreach ( $product_ids_array as $product_id ) {
						$featured_image_id = get_post_thumbnail_id( $product_id );
						if ( $featured_image_id ) {
							$image_ids_to_process[ $featured_image_id ] = array(
								'type'      => 'product',
								'image_id'  => $featured_image_id,
								'record_id' => $product_id,
							);
						}
					}
				}

				$results          = array();
				$error_process    = array();
				$error_images     = array();
				$error_alt_images = array();
				$error_message    = '';
				if ( $image_ids_to_process ) {
					foreach ( $image_ids_to_process as $image_data ) {
						$image_id   = $image_data['image_id'];
						$image_type = $image_data['type'];
						$record_id  = $image_data['record_id'];

						// Make sure the image is uploaded in the API.
						$image_api_data = wtai_get_image_for_api_generation( $record_id, $image_id, $browser_time, false, $image_type );

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

					$alt_image_error_ids = array_filter( $alt_image_error_ids );

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

			echo wp_json_encode(
				array(
					'message'              => $message,
					'success'              => $success,
					'results'              => $results,
					'success_ids'          => array_keys( $results ),
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
					$web_token = apply_filters( 'wtai_web_token', '' );

					if ( $web_token ) {
						$credit_count_needed = isset( $_POST['creditCountNeeded'] ) ? intval( sanitize_text_field( wp_unslash( $_POST['creditCountNeeded'] ) ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						// Maybe clear current user transfer data.
						$clear_response = wtai_clear_user_bulk_transfer();

						$doing_bulk_generation = isset( $_POST['doingBulkGeneration'] ) ? sanitize_text_field( wp_unslash( $_POST['doingBulkGeneration'] ) ) : '0'; // phpcs:ignore WordPress.Security.NonceVerification

						if ( 0 === intval( $is_premium ) &&
							( 1 === intval( $doing_bulk_generation ) ||
							( isset( $_POST['rewriteText'] ) && 1 === intval( $_POST['rewriteText'] ) ) || // phpcs:ignore WordPress.Security.NonceVerification
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

						$category_ids               = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
						$generated                  = isset( $_POST['save_generated'] ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
						$single_result              = isset( $_POST['single_result'] ) ? 1 : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime                = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$representative_product_ids = isset( $_POST['representative_product_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['representative_product_ids'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

						$values = array(
							'browsertime' => $browsertime,
							'token'       => $web_token,
						);

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['fields'] ) ) {
							$values['fields'] = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['fields'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['fields'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						} else {
							$fields           = apply_filters( 'wtai_category_fields', array() );
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
						if ( isset( $representative_product_ids ) ) {
							$values['representative_product_ids'] = explode( ',', $representative_product_ids );
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
						$category_ids_array     = ( false !== strpos( $category_ids, ',' ) ) ? explode( ',', $category_ids ) : array( $category_ids );
						if ( count( $category_ids_array ) > 1 ) {
							$is_doing_bulk_generate = true;
						} elseif ( $queue_generate && ! $bulk_one_only ) {
							$is_doing_bulk_generate = true;
						}

						$error_alt_images = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['altimageserror'] ) ) {
							$error_alt_images = ( false !== strpos( sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ), ',' ) ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ) ) : array( sanitize_text_field( wp_unslash( $_POST['altimageserror'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
							$error_alt_images = array_filter( $error_alt_images );

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

						$api_results = apply_filters( 'wtai_generate_category_options_text', array(), $category_ids, $values );

						if ( ! is_array( $api_results ) && ! empty( $api_results ) ) {
							$message_token = $api_results;
						} elseif ( is_array( $api_results ) && ! empty( $api_results ) ) {
							if ( isset( $api_results['requestId'] ) && $api_results['requestId'] ) {
								$results['requestId'] = $api_results['requestId'];

								if ( $queue_generate && ! $bulk_one_only ) {
									foreach ( $category_ids_array as $category_id ) {
										foreach ( $values['fields'] as $field ) {
											update_term_meta( $category_id, 'wtai_bulk_queue_id_' . $field, $results['requestId'] );
										}
									}
								}
							} else {
								foreach ( $category_ids_array as $category_id ) {
									// phpcs:ignore WordPress.Security.NonceVerification
									if ( ! isset( $_POST['no_settings_save'] ) ) {
										// phpcs:ignore WordPress.Security.NonceVerification
										if ( isset( $_POST['otherproductdetails'] ) ) {
											update_term_meta( $category_id, 'wtai_otherproductdetails', sanitize_text_field( wp_unslash( $_POST['otherproductdetails'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
										}
									}

									foreach ( $api_results[ $category_id ]  as $result_key => $result_value ) {
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

										$results[ $category_id ][ $result_key ] = array(
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

											update_term_meta( $category_id, 'wtai_generate_date', $time );

											$results[ $category_id ]['generate_date'] = sprintf(
												/* translators: %1$s: date  %2$s time */
												__( '%1$s at %2$s' ),
												date_i18n( get_option( 'date_format' ), $time ),
												date_i18n( get_option( 'time_format' ), $time )
											);
										}
									}
								}

								if ( $single_result ) {
									$results = reset( $results );
								}
							}

							if ( $category_ids_array ) {
								foreach ( $category_ids_array as $category_id ) {
									wtai_record_category_last_activity( $category_id, 'generate' );

									foreach ( $values['fields']  as $field_key ) {
										wtai_record_category_field_last_activity( $category_id, 'generate', $field_key );
									}

									if ( $generated ) {
										$browser_offset = isset( $_POST['browsertime'] ) ? ( sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) * -1 ) * 60 : 0; // phpcs:ignore WordPress.Security.NonceVerification
										$time           = strtotime( current_time( 'mysql' ) );

										update_term_meta( $category_id, 'wtai_generate_date', $time );

										$results[ $category_id ]['generate_date'] = sprintf(
											/* translators: %1$s: date  %2$s time */
											__( '%1$s at %2$s' ),
											date_i18n( get_option( 'date_format' ), $time ),
											date_i18n( get_option( 'time_format' ), $time )
										);
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
	 * Save transfer or Save bulk text to API.
	 */
	public function transfer_or_save_category_text() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax       = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results       = array();
		$message_token = '';
		$access        = 1;
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
					$web_token = apply_filters( 'wtai_web_token', '' );

					if ( $web_token ) {
						$category_id = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

						$fields = array();
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['data_fields'] ) ) {
							// phpcs:ignore WordPress.Security.NonceVerification
							if ( is_array( $_POST['data_fields'] ) ) {
								$fields = map_deep( wp_unslash( $_POST['data_fields'] ), 'wp_kses_post' ); // phpcs:ignore WordPress.Security.NonceVerification
							} else {
								// phpcs:ignore WordPress.Security.NonceVerification
								$fields = wp_kses( wp_unslash( $_POST['data_fields'] ), wtai_kses_allowed_html() );
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
							$api_results[ $category_id ][ $field_key ] = array(
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
							apply_filters( 'wtai_stored_generate_category_text', $api_results, $pre_values );
						}

						$api_results = apply_filters( 'wtai_stored_generate_category_text', $api_results, $values );

						if ( 200 === intval( $api_results['http_header'] ) ) {
							// Check status current post per field.
							$api_status_fields = array(
								'fields'               => array_keys( $fields ),
								'includeUpdateHistory' => true,
								'historyCount'         => 1,
							);

							$api_status_results = apply_filters( 'wtai_generate_category_text', array(), $category_id, $api_status_fields );

							$meta_post_date = ( $publish ) ? 'transfer' : 'generate';

							if ( $publish ) {
								$time                                     = get_term_meta( $category_id, 'wtai_generate_date', true );
								$results[ $category_id ]['generate_date'] = sprintf(
									/* translators: %1$s: date  %2$s time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $time ),
									date_i18n( get_option( 'time_format' ), $time )
								);
							}

							$time = get_term_meta( $category_id, 'wtai_' . $meta_post_date . '_date', true );

							$results[ $category_id ][ $meta_post_date . '_date' ] = sprintf(
								/* translators: %1$s: date  %2$s time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $time ),
								date_i18n( get_option( 'time_format' ), $time )
							);

							foreach ( $fields as $field_key => $field_value ) {
								$field = ( $publish ) ? $field_key : 'wtai_' . $field_key;

								$field_value = str_replace( "\\'", "'", $field_value );
								$field_value = str_replace( '\\"', '"', $field_value );

								if ( 'category_description' === $field ) {
									$field_value = wpautop( $field_value );
								} else {
									$field_value = wpautop( nl2br( $field_value ) );
								}

								$results[ $category_id ][ $field ] = array(
									'trim'                => wp_trim_words( $field_value, 15, '...' ),
									'text'                => $field_value,
									'count'               => strlen( $field_value ),
									'words'               => str_word_count( $field_value ),
									'words_count'         => wtai_word_count( wp_strip_all_tags( $field_value ) ),
									'string_count'        => mb_strlen( wp_strip_all_tags( $field_value ), 'UTF-8' ),
									'string_count_credit' => mb_strlen( $field_value, 'UTF-8' ),
								);

								if ( $publish ) {
									$results[ $category_id ][ 'wtai_' . $field ] = array(
										'trim'  => wp_trim_words( $field_value, 15, '...' ),
										'text'  => $field_value,
										'count' => strlen( $field_value ),
										'words' => str_word_count( $field_value ),
									);

									wtai_save_category_field_value( $category_id, $field_key, $field_value );

									$last_activity = ( 'bulk_transfer' === $submittype ) ? 'transfer' : 'edit';

									wtai_record_category_field_last_activity( $category_id, $last_activity, $field_key );
								}

								$meta_values       = wtai_get_category_values( $category_id, $field_key );
								$saved_field_value = $meta_values[ $field_key ];

								$results[ $category_id ][ $field ]['words_count']         = wtai_word_count( wp_strip_all_tags( $saved_field_value ) );
								$results[ $category_id ][ $field ]['string_count']        = mb_strlen( wp_strip_all_tags( $saved_field_value ), 'UTF-8' );
								$results[ $category_id ][ $field ]['string_count_credit'] = mb_strlen( $saved_field_value, 'UTF-8' );

								$field_published = 0;
								$field_reviewed  = 0;
								if ( isset( $api_status_results[ $category_id ][ $field ][0]['history'][0] ) ) {
									$field_published = $api_status_results[ $category_id ][ $field ][0]['history'][0]['publish'];
									$field_reviewed  = $api_status_results[ $category_id ][ $field ][0]['history'][0]['reviewed'];
								}

								$results[ $category_id ][ $field ]['published'] = $field_published;
								$results[ $category_id ][ $field ]['reviewed']  = $field_reviewed;
							}

							// Record last activity.
							$last_activity = ( 'bulk_transfer' === $submittype ) ? 'transfer' : 'edit';
							wtai_record_category_last_activity( $category_id, $last_activity );
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

				$api_results = apply_filters( 'wtai_generate_history', '', '', $filters, 'category' );

				if ( is_array( $api_results ) && ! empty( $api_results ) ) {
					$meta_keys_original = apply_filters( 'wtai_category_fields', array() );

					$meta_keys = apply_filters( 'wtai_category_fields', array() );
					$meta_keys = array_keys( $meta_keys );
					$meta_keys = array_map(
						function ( $meta_key ) {
							return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'category' );
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
							$field_type_converted = apply_filters( 'wtai_field_conversion', trim( $field_type_raw ), 'category' );
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
								$term      = get_term( $history['recordId'], 'product_cat' );
								$term_name = '';
								if ( ! is_wp_error( $term ) ) {
									$term_name = $term->name;
								}

								$results[ $timekey ]['product_id']          = $history['recordId'];
								$results[ $timekey ]['product_link']        = get_term_link( $history['recordId'] );
								$results[ $timekey ]['product_name']        = $term_name;
								$results[ $timekey ]['product_data_values'] = array();
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
	 * Product history callback.
	 */
	public function get_category_history_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$category_id    = isset( $_POST['category_id'] ) ? sanitize_text_field( wp_unslash( $_POST['category_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification
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

				$api_results = apply_filters( 'wtai_generate_history', '', $category_id, $filters, 'category' );
				if ( is_array( $api_results ) && ! empty( $api_results ) ) {
					$meta_keys_original = apply_filters( 'wtai_category_fields', array() );

					$meta_keys = apply_filters( 'wtai_category_fields', array() );
					$meta_keys = array_keys( $meta_keys );
					$meta_keys = array_map(
						function ( $meta_key ) {
							return apply_filters( 'wtai_field_conversion', trim( $meta_key ), 'category' );
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
							$field_type_converted = apply_filters( 'wtai_field_conversion', trim( $field_type_raw ), 'category' );
							$text_display         = $history['textTypeDisplay'];
							if ( $field_type_converted ) {
								$text_display = $meta_keys_original[ $field_type_converted ];
							}

							// Timezone convertion utc to browser time.
							$timezone_converted_timestamp = strtotime( get_date_from_gmt( $history['timestamp'], 'Y-m-d H:i:s' ) );
							$timekey                      = gmdate( 'Ymdhi', $timezone_converted_timestamp ) . '-' . md5( $history['actionDisplay'] );

							$api_content_value = html_entity_decode( str_replace( '\\', '', $history['value'] ), ENT_QUOTES | ENT_HTML5 );

							// Trim newlines, spaces, and non-breaking spaces from the end.
							$api_content_value = wtai_remove_trailing_new_lines( $api_content_value );

							$result = array(
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
	 * Process product review callback.
	 */
	public function process_mark_as_review_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$access          = 0;
		$api_updated     = 0;
		$error_message   = '';
		$alt_api_results = array();
		if ( wtai_current_user_can( 'writeai_generate_text' ) ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				$browsertime = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$category_id = isset( $_POST['category_id'] ) ? intval( $_POST['category_id'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

				$fields = apply_filters( 'wtai_category_fields', array() );
				$fields = array_keys( $fields );

				// Call api.
				$api_fields = array(
					'fields'               => $fields,
					'includeUpdateHistory' => true,
					'historyCount'         => 1,
				);

				$api_results        = apply_filters( 'wtai_generate_category_text', array(), $category_id, $api_fields );
				$has_generated_text = false;
				foreach ( $fields as $field ) {
					$api_field_key = $field;

					if ( isset( $api_results[ $category_id ][ $api_field_key ][0]['value'] ) || isset( $api_results[ $category_id ][ $api_field_key ][0]['history'][0] ) ) {
						$has_generated_text = true;
					}
				}

				if ( $has_generated_text ) {
					$reviewed = false;
					// phpcs:ignore WordPress.Security.NonceVerification
					if ( isset( $_POST['value'] ) && '0' !== $_POST['value'] ) {
						$reviewed = true;
					}

					// Mark normal text fields as reviewed.
					$api_results = apply_filters( 'wtai_record_category_reviewed_api', array(), $category_id, $reviewed, $browsertime );

					$review_success = false;
					if ( 200 === intval( $api_results['http_header'] ) && 1 === intval( $api_results['status'] ) ) {
						$review_success = true;
					}

					if ( $review_success ) {
						if ( $reviewed ) {
							update_term_meta( $category_id, 'wtai_review', 1 );
						} else {
							delete_term_meta( $category_id, 'wtai_review' );
						}

						$api_updated = 1;
					} else {
						$error_message = __( 'There is an error encountered while saving review status to the API. Please try again later.', 'writetext-ai' );

						delete_term_meta( $category_id, 'wtai_review' );
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
				'access'        => $access,
				'api_updated'   => $api_updated,
				'error_message' => $error_message,
				'api_results'   => $api_results,
				'reviewed'      => $reviewed,
			)
		);

		exit;
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
					update_user_meta( $current_user_id, 'wtai_highlight_pronouns_category', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_highlight_pronouns_category' );
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
					update_user_meta( $current_user_id, 'wtai_highlight_category', sanitize_text_field( wp_unslash( $_POST['value'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
				} else {
					delete_user_meta( $current_user_id, 'wtai_highlight_category' );
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
	 * Check if last characters of a string is '...'.
	 *
	 * @param string $text Text.
	 */
	private function ends_with_dots( $text ) {
		// Check if the last three characters are '...'.
		return substr( $text, -3 ) === '...';
	}

	/**
	 * Set category image user preference.
	 */
	public function set_category_image_state() {
		define( 'WTAI_DOING_AJAX', true );

		$success = 0;
		$message = '';

		if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
			$current_user_id = get_current_user_id();
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_POST['is_checked'] ) && 1 === intval( $_POST['is_checked'] ) ) {
				update_user_meta( $current_user_id, 'wtai_category_image_checked_status', '1' );
			} else {
				update_user_meta( $current_user_id, 'wtai_category_image_checked_status', '0' );
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
global $wtai_product_category;
$wtai_product_category = new WTAI_Product_Category();