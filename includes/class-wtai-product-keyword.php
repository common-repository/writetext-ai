<?php
/**
 * Product keywords class for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WTAI Product keyword class.
 */
class WTAI_Product_Keyword extends WTAI_Init {

	/**
	 * Construct.
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
	 * Init hooks.
	 */
	public function init_hooks() {
		// Keyword metas.
		add_filter( 'wtai_keyword_values', array( $this, 'get_keyword_values' ), 10, 5 );
		add_filter( 'wtai_keywordanalysis_location', array( $this, 'get_keywordanalysis_location' ), 10 );

		add_action( 'wp_ajax_wtai_keyword_text', array( $this, 'process_keyword_text_callback' ) );
		add_action( 'wp_ajax_wtai_keyword_ideas', array( $this, 'get_keyword_ideas_callback' ) );
		add_action( 'wp_ajax_wtai_select_semantic_keyword', array( $this, 'set_semantic_keyword_callback' ) );

		add_action( 'wp_ajax_wtai_start_ai_keyword_analysis', array( $this, 'start_ai_keyword_analysis_callback' ) );
		add_action( 'wp_ajax_wtai_process_manual_keyword', array( $this, 'process_manual_keyword' ) );
		add_action( 'wp_ajax_wtai_keyword_analysis_sort_filter', array( $this, 'keyword_analysis_sort_filter' ) );
		add_action( 'wp_ajax_wtai_keyword_analysis_save_sort_filter', array( $this, 'keyword_analysis_save_sort_filter' ) );
		add_action( 'wp_ajax_wtai_apply_spellcheck_keyword', array( $this, 'apply_spellcheck_keyword' ) );
	}

	/**
	 * Process keyword text callback.
	 */
	public function process_keyword_text_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$access               = 0;
			$keyword_input_values = array();
			$keyword_ideas_values = array();
			$message              = '';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification
					$type        = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$value       = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$existing_keywords = isset( $_POST['existing_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['existing_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

					$keywords = array();

					if ( $existing_keywords ) {
						foreach ( $existing_keywords as $keyword ) {
							if ( $keyword ) {
								$keywords[] = $keyword;
							}
						}
					}

					$clearalltext = 0;

					if ( 'add' === $type ) {
						// Newly added keyword.
						if ( $value ) {
							$keywords[] = $value;
						}
					} elseif ( 'remove' === $type ) {
						foreach ( $keywords as $keyword_id => $keyword ) {
							if ( $keyword === $value ) {
								unset( $keywords[ $keyword_id ] );
							}
						}
					}

					// Set refresh state for suggested idea.
					if ( 'category' === $record_type ) {
						update_term_meta( $record_id, 'wtai_refresh_suggested_idea', '1' );
					} else {
						update_post_meta( $record_id, 'wtai_refresh_suggested_idea', '1' );
					}

					if ( count( $keywords ) <= 0 ) {
						$clearalltext = 1;
					}

					// Call api to save keywords.
					$results = $this->get_keyword_semantics( $record_id, $keywords, $clearalltext, $record_type );

					$keyword_input_values = apply_filters( 'wtai_keyword_values', array(), $record_id, 'input', true, $record_type );
					$keyword_ideas_values = apply_filters( 'wtai_keyword_values', array(), $record_id, 'ideas', true, $record_type );

					$access = 1;
				}
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'access'  => $access,
					'message' => $message,
					'result'  => array(
						'keyword_input' => $keyword_input_values,
						'keyword_ideas' => $keyword_ideas_values,
					),

				)
			);

			exit;
		}
	}

	/**
	 * Get keyword ideas callback.
	 */
	public function get_keyword_ideas_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$access                         = 0;
			$result_location                = 0;
			$error                          = '';
			$refresh                        = false;
			$keyword_ideas_values           = array();
			$keyword_statistic_values       = array();
			$detailed_result                = array();
			$ranked_keywords                = array();
			$competitor_keywords            = array();
			$manual_keywords                = array();
			$ranked_keywords_api_result     = array();
			$message                        = '';
			$selected_keywords_html         = '';
			$ranked_keywords_html           = '';
			$competitor_keywords_html       = '';
			$manual_keywords_html           = '';
			$suggested_keywords_html        = '';
			$display_selected_keywords      = '0';
			$display_manual_keywords        = '0';
			$rank_serp_date                 = '';
			$competitor_serp_date           = '';
			$analysis_request_id            = '';
			$display_suggested_keywords     = '0';
			$show_competitor_refresh        = '0';
			$show_suggested_refresh         = '0';
			$ranked_last_date_retrieval     = '';
			$competitor_last_date_retrieval = '';
			$suggested_last_date_retrieval  = '';
			$done_ranked_analysis           = '0';
			$display_ideas_refresh          = '0';
			$display_suggested_refresh      = '0';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification

					$keyword         = isset( $_POST['keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$manual_keywords = isset( $_POST['manual_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['manual_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$refresh         = ( isset( $_POST['refresh'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['refresh'] ) ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
					$nogenerate      = ( isset( $_POST['nogenerate'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['nogenerate'] ) ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification

					$volume_filter     = isset( $_POST['volumeFilter'] ) ? sanitize_text_field( wp_unslash( $_POST['volumeFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$volume_sort       = isset( $_POST['volumeSort'] ) ? sanitize_text_field( wp_unslash( $_POST['volumeSort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$difficulty_filter = isset( $_POST['difficultyFilter'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['difficultyFilter'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$difficulty_sort   = isset( $_POST['difficultySort'] ) ? sanitize_text_field( wp_unslash( $_POST['difficultySort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$keywords_sort     = isset( $_POST['keywordsSort'] ) ? sanitize_text_field( wp_unslash( $_POST['keywordsSort'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$sorting_filter    = isset( $_POST['sorting'] ) ? explode( ':', sanitize_text_field( wp_unslash( $_POST['sorting'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$page_no           = isset( $_POST['pageNo'] ) ? sanitize_text_field( wp_unslash( $_POST['pageNo'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$language_code     = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

					$record_id = intval( $record_id );

					if ( ! $language_code ) {
						$language_code = wtai_get_location_code();
					}

					$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
					$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;
					$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
					$max_keyword_char_length  = $global_rule_fields['maxKeywordLength'];

					if ( 'category' === $record_type ) {
						$term        = get_term( $record_id, 'product_cat' );
						$record_name = $term->name;
					} else {
						$record_name = get_the_title( $record_id );
					}

					$record_name_shortened = trim( substr( $record_name, 0, $max_keyword_char_length ) );

					$sort_filter_data = wtai_get_keyword_analysis_sort_filter( $record_id, 'suggested', $record_type );

					$sort_type_selected      = isset( $sort_filter_data['sort_type'] ) ? $sort_filter_data['sort_type'] : 'relevance';
					$sort_direction_selected = isset( $sort_filter_data['sort_direction'] ) ? $sort_filter_data['sort_direction'] : 'asc';
					$volume_filter           = isset( $sort_filter_data['volume_filter'] ) ? $sort_filter_data['volume_filter'] : 'all';
					$difficulty_filter       = isset( $sort_filter_data['difficulty_filter'] ) ? $sort_filter_data['difficulty_filter'] : array();

					$keywords = array();
					foreach ( $keyword as $kw ) {
						if ( '' !== trim( $kw ) ) {
							$keywords[] = stripslashes( $kw );
						}
					}

					$fields = array(
						'location_code' => $language_code,
						'refresh'       => $refresh,
						'nogenerate'    => $nogenerate,
					);

					if ( $keywords ) {
						$fields['targetKeywords'] = $keywords;
					}

					if ( $manual_keywords ) {
						$mk = 0;
						foreach ( $manual_keywords as $manual_keyword_index => $manual_keyword ) {
							if ( $mk >= $max_manual_keyword_count ) {
								unset( $manual_keywords[ $manual_keyword_index ] );
							}
							++$mk;
						}

						$fields['manualKeywords'] = $manual_keywords;
					}

					$has_custom_filters = false;
					if ( $volume_filter ) {
						if ( '0-10000' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 0;
							$fields['filterBySearchVolumeMaximum'] = 10000;
						} elseif ( '10001-50000' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 10001;
							$fields['filterBySearchVolumeMaximum'] = 50000;
						} elseif ( '50001' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 50001;
						}

						$has_custom_filters = true;
					}

					if ( $difficulty_filter ) {
						$difficulty_filter_parsed = array();
						foreach ( $difficulty_filter as $difficulty ) {
							if ( 'all' !== $difficulty ) {
								$difficulty_filter_parsed[] = $difficulty;
							}
						}

						if ( $difficulty_filter_parsed ) {
							if ( count( $difficulty_filter_parsed ) < 3 ) {
								$fields['filterByCompetition'] = $difficulty_filter_parsed;
								$has_custom_filters            = true;
							}
						}
					}

					$sorting_array = array();
					$has_sorting   = false;
					if ( $sort_type_selected ) {
						if ( isset( $sort_type_selected ) && '' !== $sort_type_selected && 'relevance' !== $sort_type_selected ) {
							$sorting_bool = ( 'asc' === $sort_direction_selected ) ? true : false;

							if ( 'volume' === $sort_type_selected ) {
								$sort_type_selected = 'search_volume';
							}

							if ( 'difficulty' === $sort_type_selected ) {
								$sort_type_selected = 'competition_index';
							}

							$sorting_array[] = array(
								'field'     => $sort_type_selected,
								'ascending' => $sorting_bool,
							);

							$has_sorting        = true;
							$has_custom_filters = true;
						}
					}

					if ( $sorting_array ) {
						$fields['sorting'] = $sorting_array;
					}

					$posts_per_page = defined( 'WTAI_KEYWORDS_MAX_ITEM_PER_LOAD' ) ? WTAI_KEYWORDS_MAX_ITEM_PER_LOAD : 10;

					$fields['page']            = $page_no;
					$fields['get_ranked_data'] = true;
					$fields['pageSize']        = $posts_per_page;

					$api_result = apply_filters( 'wtai_generate_keywordanalysis_ideas', array(), $record_id, $fields, $record_type );

					// Call /Ranked if /Ideas returns 404.
					$call_ranked_api = false;
					if ( ( isset( $api_result['detailed_result'] ) && isset( $api_result['detailed_result']['http_header'] ) && 404 === intval( $api_result['detailed_result']['http_header'] ) ) ) {
						$call_ranked_api = true;
					}
					if ( ( isset( $api_result['detailed_result'] ) && isset( $api_result['detailed_result']['api_result'] ) && isset( $api_result['detailed_result']['api_result']['status'] ) && 404 === intval( $api_result['detailed_result']['api_result']['status'] ) ) ) {
						$call_ranked_api = true;
					}

					if ( $call_ranked_api ) {
						$ranked_keywords_api_result = apply_filters( 'wtai_get_ranked_keywords', array(), $record_id, $fields, $record_type );
					}

					$result_count = 0;
					$total_pages  = 0;
					$stale        = 0;

					if ( isset( $api_result['error'] ) ) {
						$error = $api_result['error'];
					} elseif ( isset( $api_result['result'] ) ) {
						$keyword_ideas_values = array();
						if ( is_array( $api_result['result'] ) && ! empty( $api_result['result'] ) ) {
							foreach ( $api_result['result'] as $result ) {
								$skip_idea = false;
								if ( strtolower( $result['keyword'] ) === strtolower( $record_name ) ) {
									$skip_idea = true;
								}

								if ( strtolower( $result['keyword'] ) === strtolower( $record_name_shortened ) ) {
									$skip_idea = true;
								}

								if ( ! $skip_idea ) {
									$keyword_ideas_values[] = $result;
								}
							}
						}

						if ( is_array( $api_result['keywords'] ) && ! empty( $api_result['keywords'] ) ) {
							foreach ( $api_result['keywords'] as $result ) {
								$keyword_statistic_values[] = array(
									'active'     => 0,
									'name'       => $result['keyword'],
									'search_vol' => $result['search_volume'],
									'diffuculty' => $result['competition'],
									'type'       => 'ideas',
								);
							}
						}

						$result_count = $api_result['result_count'];
						$stale        = true === $api_result['stale'] ? 1 : 0;
						$total_pages  = ceil( $result_count / $posts_per_page );
					}

					// Detailed result for other services.
					$keywords_detailed_result = array();
					if ( isset( $api_result['detailed_result'] ) ) {
						$detailed_result = $api_result['detailed_result'];

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['keywords'] ) ) {
							$keywords_detailed_result = $detailed_result['api_result']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) && isset( $detailed_result['api_result']['competitor_keywords']['keywords'] ) ) {
							$competitor_keywords = $detailed_result['api_result']['competitor_keywords']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['saved_keywords'] ) ) {
							$manual_keywords = $detailed_result['api_result']['saved_keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['ranked'] ) && isset( $detailed_result['api_result']['ranked']['keywords'] ) ) {
							$ranked_keywords = $detailed_result['api_result']['ranked']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['ranked'] ) && isset( $detailed_result['api_result']['ranked']['date'] ) ) {
							$rank_serp_date = $detailed_result['api_result']['ranked']['date'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) && isset( $detailed_result['api_result']['competitor_keywords']['date'] ) ) {
							$competitor_serp_date = $detailed_result['api_result']['competitor_keywords']['date'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['queueRequestId'] ) ) {
							$analysis_request_id = $detailed_result['api_result']['queueRequestId'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) ) {
							$show_competitor_refresh = '1';
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['result'] ) ) {
							$show_suggested_refresh = '1';
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['ranked'] ) && isset( $detailed_result['api_result']['ranked']['date'] ) ) {
							$ranked_last_date_retrieval_api       = $detailed_result['api_result']['ranked']['date'];
							$ranked_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $ranked_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
							$ranked_last_date_retrieval           = sprintf(
								/* translators: %1$s: date, %2$s: time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $ranked_last_date_retrieval_timestamp ),
								date_i18n( get_option( 'time_format' ), $ranked_last_date_retrieval_timestamp )
							);

							$done_ranked_analysis = '1';
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) && isset( $detailed_result['api_result']['competitor_keywords']['date'] ) ) {
							$competitor_last_date_retrieval_api       = $detailed_result['api_result']['competitor_keywords']['date'];
							$competitor_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $competitor_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
							$competitor_last_date_retrieval           = sprintf(
								/* translators: %1$s: date, %2$s: time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $competitor_last_date_retrieval_timestamp ),
								date_i18n( get_option( 'time_format' ), $competitor_last_date_retrieval_timestamp )
							);
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['keywords_date'] ) ) {
							$suggested_last_date_retrieval_api       = $detailed_result['api_result']['keywords_date'];
							$suggested_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $suggested_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
							$suggested_last_date_retrieval           = sprintf(
								/* translators: %1$s: date, %2$s: time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $suggested_last_date_retrieval_timestamp ),
								date_i18n( get_option( 'time_format' ), $suggested_last_date_retrieval_timestamp )
							);
						}
					}

					if ( 0 === intval( $done_ranked_analysis ) ) {
						$ranked_keywords_api_detailed_result = $ranked_keywords_api_result['detailed_result'];

						if ( $ranked_keywords_api_detailed_result && isset( $ranked_keywords_api_detailed_result['api_result'] ) && isset( $ranked_keywords_api_detailed_result['api_result']['date'] ) ) {
							$ranked_last_date_retrieval_api       = $ranked_keywords_api_detailed_result['api_result']['date'];
							$ranked_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $ranked_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
							$ranked_last_date_retrieval           = sprintf(
								/* translators: %1$s: date, %2$s: time */
								__( '%1$s at %2$s' ),
								date_i18n( get_option( 'date_format' ), $ranked_last_date_retrieval_timestamp ),
								date_i18n( get_option( 'time_format' ), $ranked_last_date_retrieval_timestamp )
							);

							$done_ranked_analysis = '1';
						}
					}

					// Lets call /ranked to double check if we have ranked keywords.
					if ( ! $ranked_keywords ) {
						if ( $ranked_keywords_api_result && isset( $ranked_keywords_api_result['results'] ) && isset( $ranked_keywords_api_result['results']['keywords'] ) ) {
							$ranked_keywords = $ranked_keywords_api_result['results']['keywords'];
							$rank_serp_date  = $ranked_keywords_api_result['results']['date'];
						}
					}

					// Get html for keyword ideas section.
					$selected_keywords_html = wtai_get_selected_keyword_html( $record_id, $keywords, $keywords_detailed_result, $ranked_keywords, $competitor_keywords, $rank_serp_date, $competitor_serp_date, $record_type );
					if ( $keywords ) {
						$display_selected_keywords = '1';
					}

					// Get html for ranked keywords section.
					if ( $ranked_keywords ) {
						$ranked_keywords_html = wtai_get_ranked_keyword_html( $record_id, $ranked_keywords, $keywords, $rank_serp_date, array(), array(), $record_type );
					}

					// Get html for competitor keywords section.
					if ( $competitor_keywords ) {
						$competitor_keywords_html = wtai_get_competitor_keyword_html( $record_id, $competitor_keywords, $keywords, $competitor_serp_date, $record_type );
					}

					// Get html for manual keywords section.
					$manual_keywords      = wtai_filter_empty_array( $manual_keywords );
					$manual_keywords_html = wtai_get_manual_keyword_html( $record_id, $manual_keywords, $keywords_detailed_result, $keywords, $ranked_keywords, $competitor_keywords, $record_type );
					if ( $manual_keywords ) {
						$display_manual_keywords = '1';

						// Check if refresh is needed.
						foreach ( $manual_keywords as $manual_keyword ) {
							$kd_found = false;
							foreach ( $keywords_detailed_result as $kd_data ) {
								if ( $kd_data['keyword'] === $manual_keyword ) {
									$kd_found = true;
									break;
								}
							}

							if ( ! $kd_found ) {
								$display_ideas_refresh     = '1';
								$display_suggested_refresh = '1';
								break;
							}
						}
					}

					if ( $keywords ) {
						// Check if refresh is needed.
						foreach ( $keywords as $t_keyword ) {
							$kd_found = false;
							foreach ( $keywords_detailed_result as $kd_data ) {
								if ( $kd_data['keyword'] === $t_keyword ) {
									$kd_found = true;
									break;
								}
							}

							if ( ! $kd_found ) {
								$display_suggested_refresh = '1';
								break;
							}
						}
					}

					$use_internal_flag = false;

					if ( 'category' === $record_type ) {
						$wtai_refresh_suggested_idea = get_term_meta( $record_id, 'wtai_refresh_suggested_idea', true );
					} else {
						$wtai_refresh_suggested_idea = get_post_meta( $record_id, 'wtai_refresh_suggested_idea', true );
					}

					if ( $use_internal_flag && 1 === intval( $wtai_refresh_suggested_idea ) ) {
						$display_suggested_refresh = '1';
					}

					if ( $stale ) {
						$display_ideas_refresh     = '1';
						$display_suggested_refresh = '1';
					}

					// Get html for suggested keywords section.
					$suggested_keywords_html = wtai_get_suggested_keyword_html( $record_id, $keyword_ideas_values, $manual_keywords, $keywords, $total_pages, $posts_per_page, $result_count, 1, $record_type );
					if ( ( ( $manual_keywords && $has_custom_filters ) || $keyword_ideas_values ) && ( $keyword_ideas_values || $has_custom_filters ) ) {
						$display_suggested_keywords = '1';
					} else {
						$show_suggested_refresh = '0';
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

			$done_analysis = '0';
			if ( $show_competitor_refresh || $show_suggested_refresh ) {
				$done_analysis = '1';
			}

			$output = array(
				'message'                => $message,
				'access'                 => $access,
				'error'                  => $error,
				'available_credit_label' => $available_credit_label,
				'result'                 => array(
					'language_code'                  => $language_code,
					'keywords'                       => $keywords,
					'keyword_ideas'                  => $keyword_ideas_values,
					'keyword_statistic_values'       => $keyword_statistic_values,
					'result_count'                   => $result_count,
					'total_pages'                    => $total_pages,
					'stale'                          => $stale,
					'result_location'                => $result_location,
					'is_premium'                     => $is_premium,
					'detailed_result'                => $detailed_result,
					'selected_keywords_html'         => $selected_keywords_html,
					'ranked_keywords_html'           => $ranked_keywords_html,
					'ranked_keywords'                => $ranked_keywords,
					'competitor_keywords_html'       => $competitor_keywords_html,
					'competitor_keywords'            => $competitor_keywords,
					'manual_keywords'                => $manual_keywords,
					'manual_keywords_html'           => $manual_keywords_html,
					'suggested_keywords_html'        => $suggested_keywords_html,
					'display_selected_keywords'      => $display_selected_keywords,
					'display_manual_keywords'        => $display_manual_keywords,
					'analysis_request_id'            => $analysis_request_id,
					'display_suggested_keywords'     => $display_suggested_keywords,
					'ranked_keywords_api_result'     => $ranked_keywords_api_result,
					'show_competitor_refresh'        => $show_competitor_refresh,
					'show_suggested_refresh'         => $show_suggested_refresh,
					'done_ranked_analysis'           => $done_ranked_analysis, // Done domain analysis.
					'done_analysis'                  => $done_analysis, // Done product start ai analysis.
					'ranked_last_date_retrieval'     => $ranked_last_date_retrieval,
					'competitor_last_date_retrieval' => $competitor_last_date_retrieval,
					'suggested_last_date_retrieval'  => $suggested_last_date_retrieval,
					'display_ideas_refresh'          => $display_ideas_refresh,
					'display_suggested_refresh'      => $display_suggested_refresh,
				),
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Get keyword values
	 *
	 * @param array  $values Array of values.
	 * @param int    $record_id Record ID.
	 * @param string $type Type of keyword.
	 * @param bool   $skip_first_record Skip first record.
	 * @param string $record_type Record type.
	 *
	 * @return array
	 */
	public function get_keyword_values( $values = array(), $record_id = 0, $type = '', $skip_first_record = true, $record_type = 'product' ) {
		$metas        = array(
			'wtai_keyword',
			'wtai_keyword_ideas',
		);
		$meta_key_ref = '';
		if ( 'input' === $type ) {
			$meta_key_ref = 'wtai_keyword';
		} elseif ( 'ideas' === $type ) {
			$meta_key_ref = 'wtai_keyword_ideas';
		}

		foreach ( $metas as $meta_key ) {
			if ( $meta_key_ref && $meta_key !== $meta_key_ref ) {
				continue;
			}

			if ( 'wtai_keyword' === $meta_key_ref ) {
				$disallowed_keywords = wtai_get_not_allowed_keywords();

				// Get semantic.
				$keyword_semantics = $this->get_keyword_semantics( $record_id, array(), 0, $record_type );

				$values = array();
				if ( $keyword_semantics ) {
					$keyword_semantics_texts    = $keyword_semantics['texts'];
					$keyword_semantics_selected = $keyword_semantics['selected'];

					$i        = 0;
					$keywords = array();
					foreach ( $keyword_semantics_texts as $semantic ) {
						$semantic_text   = strtolower( stripslashes( $semantic['text'] ) );
						$semantic_values = $semantic['values'];

						if ( in_array( $semantic_text, $disallowed_keywords, true ) ) {
							continue;
						}

						$process_the_keyword = true;
						if ( 0 === $i && $skip_first_record ) {
							// Do nothing, this is the product name.
							$process_the_keyword = false;
						}

						if ( $process_the_keyword ) {
							$values[ $i ] = array(
								'active'     => '_writetextai_keyword' === $meta_key_ref ? 0 : rand( 0, 1 ), // phpcs:ignore WordPress.WP.AlternativeFunctions.rand_rand
								'name'       => $semantic_text,
								'search_vol' => '-',
								'diffuculty' => '-',
								'type'       => 'input',
							);

							foreach ( $semantic_values as $semantic_keyword ) {
								$semantic_is_active         = ( $keyword_semantics_selected && in_array( $semantic_keyword, $keyword_semantics_selected, true ) ) ? 1 : 0;
								$values[ $i ]['semantic'][] = array(
									'name'   => $semantic_keyword,
									'active' => $semantic_is_active,
								);
							}
						}

						if ( $i > 0 ) {
							$keywords[] = $semantic_text;
						}

						++$i;
					}

					if ( $keywords ) {
						$idea_location_code = wtai_get_location_code();

						$fields        = array(
							'keyword'       => $keywords,
							'location_code' => $idea_location_code,
							'refresh'       => false,
							'nogenerate'    => true,
						);
						$keyword_ideas = apply_filters( 'wtai_generate_keywordanalysis_ideas', array(), $record_id, $fields, $record_type );

						if ( $keyword_ideas['result'] ) {
							foreach ( $keyword_ideas['result'] as $idea_data ) {

								foreach ( $values as $value_index => $value_data ) {
									if ( $value_data['name'] === $idea_data['keyword'] ) {
										$values[ $value_index ]['search_vol'] = number_format_i18n( $idea_data['search_volume'] );
										$values[ $value_index ]['diffuculty'] = $idea_data['competition'];
									}
								}
							}
						}
					}
				}
			}
		}
		return $values;
	}

	/**
	 * Get semantic keywords
	 *
	 * @param int    $record_id Record id.
	 * @param array  $keywords Keywords.
	 * @param int    $clearalltext Clear all text.
	 * @param string $record_type Record type.
	 * @return array
	 */
	public function get_keyword_semantics( $record_id, $keywords = array(), $clearalltext = 0, $record_type = 'product' ) {
		$web_token = apply_filters( 'wtai_web_token', '' );
		$results   = array();
		if ( $web_token ) {
			$record_id = intval( $record_id );

			if ( 'category' === $record_type ) {
				$term         = get_term( $record_id, 'product_cat' );
				$record_title = $term->name;
			} else {
				$record_title = get_the_title( $record_id );
			}

			$fetch_keywords   = array();
			$fetch_keywords[] = $record_title;

			if ( ! empty( $keywords ) ) {
				foreach ( $keywords as $keyword ) {
					$fetch_keywords[] = $keyword;
				}
			}

			if ( $keywords ) {
				foreach ( $keywords as $keyword ) {
					$fetch_keywords[] = $keyword;
				}
			}

			$values = array(
				'token'    => $web_token,
				'keywords' => $fetch_keywords,
			);

			$results = apply_filters( 'wtai_get_keyword_semantics_text', array(), $record_id, $values, $clearalltext, $record_type );
		}

		return $results;
	}

	/**
	 * Get semantic keywords
	 */
	public function set_semantic_keyword_callback() {
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
						$record_id         = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
						$record_type       = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification
						$semantic_keywords = isset( $_POST['semantic_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['semantic_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
						$browsertime       = isset( $_POST['browsertime'] ) ? sanitize_text_field( wp_unslash( $_POST['browsertime'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

						if ( 'category' === $record_type ) {
							$record_id = intval( $record_id );
							$term      = get_term( $record_id, 'product_cat' );
							$keyword   = $term->name;
						} else {
							$keyword = get_the_title( $record_id );
						}

						$values = array(
							'browsertime'       => $browsertime,
							'token'             => $web_token,
							'semantic_keywords' => $semantic_keywords,
							'keyword'           => $keyword,
						);

						$results = apply_filters( 'wtai_set_selected_keyword_semantics_text', array(), $record_id, $values, $record_type );
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
	 * Get keyword analysis location
	 *
	 * @param array $locations locations.
	 *
	 * @return array
	 */
	public function get_keywordanalysis_location( $locations ) {
		$locations            = get_option( 'wtai_keywordanalysis_location', array() );
		$locations_last_saved = get_option( 'wtai_keywordanalysis_location_time', '' );

		$current_time = strtotime( current_time( 'mysql' ) );

		$refresh_location = false;
		if ( empty( $locations ) ) {
			$refresh_location = true;
		}

		if ( $locations_last_saved ) {
			$locations_last_saved = intval( $locations_last_saved );
			$diff                 = abs( $current_time - $locations_last_saved );
			$hours                = floor( $diff / ( 60 * 60 ) );
			if ( $hours > 24 ) {
				$refresh_location = true;
			}
		} else {
			$refresh_location = true;
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai_refresh_locations'] ) && '1' === $_GET['wtai_refresh_locations'] ) {
			$refresh_location = true;
		}

		if ( $refresh_location ) {
			$api_locations = apply_filters( 'wtai_generate_keywordanalysis_location', array() );

			if ( ! isset( $api_locations['error'] ) ) {
				$locations = array();
				foreach ( $api_locations as $api_location ) {
					$locations[ $api_location['location_code'] ] = array(
						'name' => $api_location['location_name'],
						'code' => $api_location['country_iso_code'],
					);
				}

				update_option( 'wtai_keywordanalysis_location', $locations );
				update_option( 'wtai_keywordanalysis_location_time', strtotime( current_time( 'mysql' ) ) );
			}
		}

		return $locations;
	}

	/**
	 * Keywords AI analysis callback.
	 */
	public function start_ai_keyword_analysis_callback() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		if ( $is_ajax ) {
			$access                         = 0;
			$result_location                = 0;
			$error                          = '';
			$refresh                        = false;
			$keyword_ideas_values           = array();
			$keyword_statistic_values       = array();
			$message                        = '';
			$keywords_detailed_result       = array();
			$saved_keywords                 = array();
			$ranked_keywords                = array();
			$competitor_keywords            = array();
			$selected_keywords_html         = '';
			$ranked_keywords_html           = '';
			$competitor_keywords_html       = '';
			$manual_keywords_html           = '';
			$status_code                    = '';
			$suggested_keywords_html        = '';
			$display_selected_keywords      = '0';
			$display_manual_keywords        = '0';
			$analysis_request_id            = '';
			$ranked_last_date_retrieval     = '';
			$competitor_last_date_retrieval = '';
			$suggested_last_date_retrieval  = '';
			$done_ranked_analysis           = '0';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification

					$keyword         = isset( $_POST['keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$manual_keywords = isset( $_POST['manual_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['manual_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$refresh         = ( isset( $_POST['refresh'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['refresh'] ) ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
					$nogenerate      = ( isset( $_POST['nogenerate'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['nogenerate'] ) ) ) ? true : false; // phpcs:ignore WordPress.Security.NonceVerification
					$language_code   = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$refresh_type    = isset( $_POST['refresh_type'] ) ? sanitize_text_field( wp_unslash( $_POST['refresh_type'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification

					$keywords = array();
					foreach ( $keyword as $kw ) {
						if ( '' !== trim( $kw ) ) {
							$keywords[] = stripslashes( $kw );
						}
					}

					$keywords        = wtai_filter_empty_array( $keywords );
					$manual_keywords = wtai_filter_empty_array( $manual_keywords );

					$fields = array(
						'location_code' => $language_code,
						'refresh'       => $refresh,
						'nogenerate'    => $nogenerate,
						'refresh_type'  => $refresh_type,
					);

					if ( $keywords ) {
						$fields['targetKeywords'] = $keywords;
					}

					if ( $manual_keywords ) {
						$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
						$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;
						$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;

						$mk = 0;
						foreach ( $manual_keywords as $manual_keyword_index => $manual_keyword ) {
							if ( $mk >= $max_manual_keyword_count ) {
								unset( $manual_keywords[ $manual_keyword_index ] );
							}
							++$mk;
						}

						$fields['manualKeywords'] = $manual_keywords;
					}

					$api_result = apply_filters( 'wtai_start_ai_keyword_analysis', array(), $record_id, $fields, $record_type );

					if ( isset( $api_result['results'] ) ) {
						$results = $api_result['results'];

						if ( isset( $results ) ) {
							$status_code = $results['status_code'];

							if ( isset( $results['keywords'] ) ) {
								$keywords_detailed_result = $results['keywords'];
							}

							if ( isset( $results['saved_keywords'] ) ) {
								$saved_keywords = $results['saved_keywords'];
							}

							if ( $results && isset( $results['queueRequestId'] ) ) {
								$analysis_request_id = $results['queueRequestId'];
							}

							if ( isset( $results['ranked'] ) && isset( $results['ranked']['keywords'] ) ) {
								$ranked_keywords = $results['ranked']['keywords'];
							}

							if ( isset( $results['competitor_keywords'] ) && isset( $results['competitor_keywords']['keywords'] ) ) {
								$competitor_keywords = $results['competitor_keywords']['keywords'];
							}

							if ( isset( $results['result'] ) && isset( $results['result'] ) ) {
								$keyword_ideas_values = $results['result'];
							}

							if ( isset( $results['ranked'] ) && isset( $results['ranked']['date'] ) ) {
								$ranked_last_date_retrieval_api       = $results['ranked']['date'];
								$ranked_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $ranked_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
								$ranked_last_date_retrieval           = sprintf(
									/* translators: %1$s: date, %2$s: time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $ranked_last_date_retrieval_timestamp ),
									date_i18n( get_option( 'time_format' ), $ranked_last_date_retrieval_timestamp )
								);

								$done_ranked_analysis = '1';
							}

							if ( isset( $results['competitor_keywords'] ) && isset( $results['competitor_keywords']['date'] ) ) {
								$competitor_last_date_retrieval_api       = $results['competitor_keywords']['date'];
								$competitor_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $competitor_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
								$competitor_last_date_retrieval           = sprintf(
									/* translators: %1$s: date, %2$s: time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $competitor_last_date_retrieval_timestamp ),
									date_i18n( get_option( 'time_format' ), $competitor_last_date_retrieval_timestamp )
								);
							}

							if ( isset( $results['keywords_date'] ) ) {
								$suggested_last_date_retrieval_api       = $results['keywords_date'];
								$suggested_last_date_retrieval_timestamp = strtotime( get_date_from_gmt( $suggested_last_date_retrieval_api, 'Y-m-d H:i:s' ) );
								$suggested_last_date_retrieval           = sprintf(
									/* translators: %1$s: date, %2$s: time */
									__( '%1$s at %2$s' ),
									date_i18n( get_option( 'date_format' ), $suggested_last_date_retrieval_timestamp ),
									date_i18n( get_option( 'time_format' ), $suggested_last_date_retrieval_timestamp )
								);
							}

							// Get html for keyword ideas section.
							$selected_keywords_html = wtai_get_selected_keyword_html( $record_id, $keywords, $keywords_detailed_result, $ranked_keywords, $competitor_keywords, $record_type );
							if ( $keywords ) {
								$display_selected_keywords = '1';
							}

							// Get html for ranked keywords section.
							if ( $ranked_keywords ) {
								$ranked_keywords_html = wtai_get_ranked_keyword_html( $record_id, $ranked_keywords, $keywords, '', array(), array(), $record_type );
							}

							// Get html for competitor keywords section.
							if ( $competitor_keywords ) {
								$competitor_keywords_html = wtai_get_competitor_keyword_html( $record_id, $competitor_keywords, $keywords, '', $record_type );
							}

							// Get html for manual keywords section.
							$manual_keywords_html = wtai_get_manual_keyword_html( $record_id, $saved_keywords, $keywords_detailed_result, $keywords, $ranked_keywords, $competitor_keywords, $record_type );
							if ( $saved_keywords ) {
								$display_manual_keywords = '1';
							}

							// Get html for suggested keywords section.
							if ( $keyword_ideas_values ) {
								$suggested_keywords_html = wtai_get_suggested_keyword_html( $record_id, $keyword_ideas_values, $saved_keywords, $keywords, 0, 0, 0, 1, $record_type );
							}

							// Reset the filters and sorting.
							$keyword_types = array( 'ranked', 'competitor', 'suggested' );
							if ( 'all' !== $refresh_type ) {
								if ( 'selected-keywords' === $refresh_type ) {
									$keyword_types = array( 'ranked' );
								}
								if ( 'competitor-keywords' === $refresh_type ) {
									$keyword_types = array( 'competitor' );
								}
								if ( 'suggested-keywords' === $refresh_type ) {
									$keyword_types = array( 'suggested' );
								}
							}

							foreach ( $keyword_types as $keyword_type ) {
								wtai_save_keyword_analysis_sort_filter( $record_id, $keyword_type, 'relevance', 'asc', 'all', array(), $record_type );
							}

							// Set refresh state for suggested idea.
							if ( 'category' === $record_type ) {
								update_term_meta( $record_id, 'wtai_refresh_suggested_idea', '0' );

							} else {
								update_post_meta( $record_id, 'wtai_refresh_suggested_idea', '0' );
							}
						}
					} elseif ( isset( $api_result['error'] ) ) {
						$error = $api_result['error'];
					} elseif ( isset( $api_result['Error'] ) ) {
						$error = $api_result['Error'];
					} else {
						$error = WTAI_GENERAL_ERROR_MESSAGE;
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

			$done_analysis = '1';

			$output = array(
				'message'                => $message,
				'access'                 => $access,
				'error'                  => $error,
				'available_credit_label' => $available_credit_label,
				'result'                 => array(
					'status_code'                    => $status_code,
					'language_code'                  => $language_code,
					'keywords'                       => $keywords,
					'keyword_ideas'                  => $keyword_ideas_values,
					'is_premium'                     => $is_premium,
					'detailed_result'                => $results,
					'selected_keywords_html'         => $selected_keywords_html,
					'ranked_keywords_html'           => $ranked_keywords_html,
					'ranked_keywords'                => $ranked_keywords,
					'competitor_keywords_html'       => $competitor_keywords_html,
					'competitor_keywords'            => $competitor_keywords,
					'manual_keywords'                => $manual_keywords,
					'manual_keywords_html'           => $manual_keywords_html,
					'suggested_keywords_html'        => $suggested_keywords_html,
					'display_selected_keywords'      => $display_selected_keywords,
					'analysis_request_id'            => $analysis_request_id,
					'done_ranked_analysis'           => $done_ranked_analysis, // Done domain analysis.
					'done_analysis'                  => $done_analysis, // Done product start ai analysis.
					'ranked_last_date_retrieval'     => $ranked_last_date_retrieval,
					'competitor_last_date_retrieval' => $competitor_last_date_retrieval,
					'suggested_last_date_retrieval'  => $suggested_last_date_retrieval,
				),
			);

			$output['detailed_result'] = $api_result['detailed_result'];

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Add or remove a manual keyword callback.
	 */
	public function process_manual_keyword() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$results = array();
		if ( $is_ajax ) {
			$access                   = 0;
			$result_location          = 0;
			$error                    = '';
			$refresh                  = false;
			$keyword_ideas_values     = array();
			$keyword_statistic_values = array();
			$message                  = '';
			$show_competitor_refresh  = '0';
			$show_suggested_refresh   = '0';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id     = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type   = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification
					$type          = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'add'; // phpcs:ignore WordPress.Security.NonceVerification
					$language_code = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$keyword       = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$fields = array(
						'location_code' => $language_code,
					);

					if ( 'add' === $type ) {
						$fields['saveAsKeyword'] = $keyword;
					} elseif ( 'remove' === $type ) {
						$fields['removeAsKeyword'] = $keyword;
					}

					$api_result = apply_filters( 'wtai_process_manual_keyword', array(), $record_id, $fields, $record_type );

					if ( isset( $api_result['results'] ) ) {
						$results = $api_result['results'];
					} elseif ( isset( $api_result['error'] ) ) {
						$error = $api_result['error'];
					}

					if ( isset( $api_result['detailed_result'] ) ) {
						$detailed_result = $api_result['detailed_result'];
						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) ) {
							$show_competitor_refresh = '1';
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['result'] ) ) {
							$show_suggested_refresh = '1';
						}

						if ( 'category' === $record_type ) {
							update_term_meta( $record_id, 'wtai_refresh_suggested_idea', '1' );

						} else {
							update_post_meta( $record_id, 'wtai_refresh_suggested_idea', '1' );
						}
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
				'result'                  => $results,
				'show_competitor_refresh' => $show_competitor_refresh,
				'show_suggested_refresh'  => $show_suggested_refresh,
			);

			$debug = true;
			if ( false !== strpos( $referrer, '1902debug=1' ) || true === $debug ) {
				$output['raw_result'] = $api_result['raw_result'];
			}

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Sort and filter for keyword analysis sections.
	 */
	public function keyword_analysis_sort_filter() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$access                     = 0;
			$result_location            = 0;
			$error                      = '';
			$refresh                    = false;
			$keyword_ideas_values       = array();
			$keyword_statistic_values   = array();
			$detailed_result            = array();
			$ranked_keywords            = array();
			$competitor_keywords        = array();
			$manual_keywords            = array();
			$message                    = '';
			$section_html               = '';
			$display_selected_keywords  = '0';
			$display_manual_keywords    = '0';
			$rank_serp_date             = '';
			$keywords_date              = '';
			$competitor_serp_date       = '';
			$display_suggested_keywords = '0';
			$show_competitor_refresh    = '0';
			$show_suggested_refresh     = '0';

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification

					$keyword         = isset( $_POST['keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$manual_keywords = isset( $_POST['manual_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['manual_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$language_code   = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$sort_type       = isset( $_POST['sort_type'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_type'] ) ) : 'relevance'; // phpcs:ignore WordPress.Security.NonceVerification
					$sort_direction  = isset( $_POST['sort_direction'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_direction'] ) ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification
					$keyword_type    = isset( $_POST['keyword_type'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$volume_filter        = isset( $_POST['volumeFilter'] ) ? sanitize_text_field( wp_unslash( $_POST['volumeFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$difficulty_filter    = isset( $_POST['difficultyFilter'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['difficultyFilter'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$page_no              = isset( $_POST['pageNo'] ) ? sanitize_text_field( wp_unslash( $_POST['pageNo'] ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification
					$save_filter_and_sort = isset( $_POST['save_filter_and_sort'] ) ? sanitize_text_field( wp_unslash( $_POST['save_filter_and_sort'] ) ) : 'no'; // phpcs:ignore WordPress.Security.NonceVerification

					if ( ! $language_code ) {
						$language_code = wtai_get_location_code();
					}

					$keywords = array();
					foreach ( $keyword as $kw ) {
						if ( '' !== trim( $kw ) ) {
							$keywords[] = stripslashes( $kw );
						}
					}

					$fields = array(
						'location_code' => $language_code,
						'refresh'       => false,
						'nogenerate'    => true,
					);

					$global_rule_fields       = apply_filters( 'wtai_global_rule_fields', array() );
					$max_manual_keyword_count = isset( $global_rule_fields['maxSuggestedKeywords'] ) ? $global_rule_fields['maxSuggestedKeywords'] : WTAI_MAX_MANUAL_KEYWORD;
					$max_keyword_count        = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;
					$max_keyword_char_length  = $global_rule_fields['maxKeywordLength'];

					if ( 'category' === $record_type ) {
						$term        = get_term( $record_id, 'product_cat' );
						$record_name = $term->name;
					} else {
						$record_name = get_the_title( $record_id );
					}

					$record_name_shortened = trim( substr( $record_name, 0, $max_keyword_char_length ) );

					if ( $keywords ) {
						$fields['targetKeywords'] = $keywords;
					}

					if ( $manual_keywords ) {
						$mk = 0;
						foreach ( $manual_keywords as $manual_keyword_index => $manual_keyword ) {
							if ( $mk >= $max_manual_keyword_count ) {
								unset( $manual_keywords[ $manual_keyword_index ] );
							}
							++$mk;
						}

						$fields['manualKeywords'] = $manual_keywords;
					}

					$has_custom_filters = false;
					if ( $volume_filter ) {
						if ( '0-10000' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 0;
							$fields['filterBySearchVolumeMaximum'] = 10000;
						} elseif ( '10001-50000' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 10001;
							$fields['filterBySearchVolumeMaximum'] = 50000;
						} elseif ( '50001' === $volume_filter ) {
							$fields['filterBySearchVolumeMinimum'] = 50001;
						}

						$has_custom_filters = true;
					}

					$difficulty_filter_parsed = array();
					if ( $difficulty_filter ) {
						foreach ( $difficulty_filter as $difficulty ) {
							if ( 'all' !== $difficulty ) {
								$difficulty_filter_parsed[] = $difficulty;
							}
						}

						if ( $difficulty_filter_parsed ) {
							if ( count( $difficulty_filter_parsed ) < 3 ) {
								$fields['filterByCompetition'] = $difficulty_filter_parsed;
								$has_custom_filters            = true;
							}
						}
					}

					$sorting_array = array();
					$has_sorting   = false;
					if ( $sort_type ) {
						if ( isset( $sort_type ) && '' !== $sort_type && 'relevance' !== $sort_type ) {
							$sorting_bool = ( 'asc' === $sort_direction ) ? true : false;

							$sort_field_type = 'search_volume';
							if ( 'difficulty' === $sort_type ) {
								$sort_field_type = 'competition_index';
							}

							$sorting_array[] = array(
								'field'     => $sort_field_type,
								'ascending' => $sorting_bool,
							);

							$has_sorting        = true;
							$has_custom_filters = true;
						}
					}

					if ( $sorting_array ) {
						$fields['sorting'] = $sorting_array;
					}

					$posts_per_page = defined( 'WTAI_KEYWORDS_MAX_ITEM_PER_LOAD' ) ? WTAI_KEYWORDS_MAX_ITEM_PER_LOAD : 10;

					$fields['page']     = $page_no;
					$fields['pageSize'] = $posts_per_page;

					$api_result = apply_filters( 'wtai_generate_keywordanalysis_ideas', array(), $record_id, $fields, $record_type );

					// Detailed result for other services.
					$keywords_detailed_result = array();
					if ( isset( $api_result['detailed_result'] ) ) {
						$detailed_result = $api_result['detailed_result'];

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['keywords'] ) ) {
							$keywords_detailed_result = $detailed_result['api_result']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) && isset( $detailed_result['api_result']['competitor_keywords']['keywords'] ) ) {
							$competitor_keywords = $detailed_result['api_result']['competitor_keywords']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['saved_keywords'] ) ) {
							$manual_keywords = $detailed_result['api_result']['saved_keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['ranked'] ) && isset( $detailed_result['api_result']['ranked']['keywords'] ) ) {
							$ranked_keywords = $detailed_result['api_result']['ranked']['keywords'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['ranked'] ) && isset( $detailed_result['api_result']['ranked']['date'] ) ) {
							$rank_serp_date = $detailed_result['api_result']['ranked']['date'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) && isset( $detailed_result['api_result']['competitor_keywords']['date'] ) ) {
							$competitor_serp_date = $detailed_result['api_result']['competitor_keywords']['date'];
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['competitor_keywords'] ) ) {
							$show_competitor_refresh = '1';
						}

						if ( $detailed_result && isset( $detailed_result['api_result'] ) && isset( $detailed_result['api_result']['result'] ) ) {
							$show_suggested_refresh = '1';
						}
					}

					if ( 'ranked' === $keyword_type ) {
						// Get html for ranked keywords section.
						if ( $ranked_keywords ) {
							$section_html = wtai_get_ranked_keyword_html( $record_id, $ranked_keywords, $keywords, $rank_serp_date, $sort_array, $filter_array, $record_type );
						}
					} elseif ( 'competitor' === $keyword_type ) {
						// Get html for competitor keywords section.
						if ( $competitor_keywords ) {
							$section_html = wtai_get_competitor_keyword_html( $record_id, $competitor_keywords, $keywords, $competitor_serp_date, $record_type );
						}
					} elseif ( 'suggested' === $keyword_type ) {
						$result_count = 0;
						$total_pages  = 0;

						$keyword_ideas_values = array();
						if ( isset( $api_result['result'] ) ) {
							if ( is_array( $api_result['result'] ) && ! empty( $api_result['result'] ) ) {
								foreach ( $api_result['result'] as $result ) {
									$skip_idea = false;
									if ( strtolower( $result['keyword'] ) === strtolower( $record_name ) ) {
										$skip_idea = true;
									}

									if ( strtolower( $result['keyword'] ) === strtolower( $record_name_shortened ) ) {
										$skip_idea = true;
									}

									if ( ! $skip_idea ) {
										$keyword_ideas_values[] = $result;
									}
								}
							}

							$result_count = $api_result['result_count'];
							$total_pages  = ceil( $result_count / $posts_per_page );
						}

						// Get html for suggested keywords section.
						$section_html = wtai_get_suggested_keyword_html( $record_id, $keyword_ideas_values, $manual_keywords, $keywords, $total_pages, $posts_per_page, $result_count, $page_no, $record_type );
						if ( ( ( $manual_keywords && $has_custom_filters ) || $keyword_ideas_values ) && ( $keyword_ideas_values || $has_custom_filters ) ) {
							$display_suggested_keywords = '1';
						}
					}

					// Save the sort and filter per product.
					if ( 'yes' === $save_filter_and_sort ) {
						wtai_save_keyword_analysis_sort_filter( $record_id, $keyword_type, $sort_type, $sort_direction, $volume_filter, $difficulty_filter_parsed, $record_type );
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
				'message'                => $message,
				'access'                 => $access,
				'error'                  => $error,
				'available_credit_label' => $available_credit_label,
				'result'                 => array(
					'language_code'             => $language_code,
					'keywords'                  => $keywords,
					'keyword_ideas'             => $keyword_ideas_values,
					'keyword_statistic_values'  => $keyword_statistic_values,
					'result_count'              => $result_count,
					'total_pages'               => $total_pages,
					'stale'                     => $stale,
					'result_location'           => $result_location,
					'is_premium'                => $is_premium,
					'detailed_result'           => $detailed_result,
					'ranked_keywords'           => $ranked_keywords,
					'competitor_keywords'       => $competitor_keywords,
					'manual_keywords'           => $manual_keywords,
					'display_selected_keywords' => $display_selected_keywords,
					'display_manual_keywords'   => $display_manual_keywords,
					'html'                      => $section_html,
					'show_competitor_refresh'   => $show_competitor_refresh,
					'show_suggested_refresh'    => $show_suggested_refresh,
				),
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Save Sort and filter for keyword analysis sections.
	 */
	public function keyword_analysis_save_sort_filter() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$access             = 0;
			$error              = '';
			$message            = '';
			$saved_sort_filters = array();

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$sort_type      = isset( $_POST['sort_type'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_type'] ) ) : 'relevance'; // phpcs:ignore WordPress.Security.NonceVerification
					$sort_direction = isset( $_POST['sort_direction'] ) ? sanitize_text_field( wp_unslash( $_POST['sort_direction'] ) ) : 'asc'; // phpcs:ignore WordPress.Security.NonceVerification
					$keyword_type   = isset( $_POST['keyword_type'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword_type'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification

					$volume_filter     = isset( $_POST['volumeFilter'] ) ? sanitize_text_field( wp_unslash( $_POST['volumeFilter'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$difficulty_filter = isset( $_POST['difficultyFilter'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['difficultyFilter'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification

					$difficulty_filter_parsed = array();
					if ( $difficulty_filter ) {
						foreach ( $difficulty_filter as $difficulty ) {
							if ( 'all' !== $difficulty ) {
								$difficulty_filter_parsed[] = $difficulty;
							}
						}
					}

					wtai_save_keyword_analysis_sort_filter( $record_id, $keyword_type, $sort_type, $sort_direction, $volume_filter, $difficulty_filter_parsed, $record_type );

					$saved_sort_filters = wtai_get_keyword_analysis_sort_filter( $record_id, $keyword_type, $record_type );

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
				'message'                => $message,
				'access'                 => $access,
				'error'                  => $error,
				'available_credit_label' => $available_credit_label,
				'result'                 => $saved_sort_filters,
			);

			echo wp_json_encode( $output );
			exit;
		}
	}

	/**
	 * Apply spellcheck keyword.
	 */
	public function apply_spellcheck_keyword() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		if ( $is_ajax ) {
			$access                     = 0;
			$error                      = '';
			$message                    = '';
			$results                    = array();
			$keyword_input_values       = array();
			$keyword_ideas_values       = array();
			$manual_keyword_api_results = array();

			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-product-nonce' ) ) {
				if ( wtai_current_user_can( 'writeai_keywords' ) ) {
					$record_id   = isset( $_POST['record_id'] ) ? sanitize_text_field( wp_unslash( $_POST['record_id'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$record_type = isset( $_POST['record_type'] ) ? sanitize_text_field( wp_unslash( $_POST['record_type'] ) ) : 'product'; // phpcs:ignore WordPress.Security.NonceVerification

					$correct_keyword       = isset( $_POST['correct_keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['correct_keyword'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$incorrect_keyword     = isset( $_POST['incorrect_keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['incorrect_keyword'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
					$existing_keywords     = isset( $_POST['existing_keywords'] ) ? explode( '|', sanitize_text_field( wp_unslash( $_POST['existing_keywords'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
					$language_code         = isset( $_POST['language_code'] ) ? sanitize_text_field( wp_unslash( $_POST['language_code'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$update_manual_keyword = isset( $_POST['update_manual_keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['update_manual_keyword'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification
					$update_manual_keyword = intval( $update_manual_keyword );

					$keywords = array();

					if ( $existing_keywords ) {
						foreach ( $existing_keywords as $keyword ) {
							if ( $keyword ) {
								$keywords[] = $keyword;
							}
						}
					}

					if ( count( $keywords ) <= 0 ) {
						$clearalltext = 1;
					}

					// Call api to save keywords.
					$results = $this->get_keyword_semantics( $record_id, $keywords, $clearalltext, $record_type );

					$keyword_input_values = apply_filters( 'wtai_keyword_values', array(), $record_id, 'input', true, $record_type );
					$keyword_ideas_values = apply_filters( 'wtai_keyword_values', array(), $record_id, 'ideas', true, $record_type );

					// Remove and apply correct keyword for the manual keyword.
					if ( $update_manual_keyword ) {
						$fields = array(
							'location_code'   => $language_code,
							'saveAsKeyword'   => $correct_keyword,
							'removeAsKeyword' => $incorrect_keyword,
						);

						$manual_keyword_api_results = apply_filters( 'wtai_process_manual_keyword', array(), $record_id, $fields, $record_type );
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
				'message'                => $message,
				'access'                 => $access,
				'error'                  => $error,
				'available_credit_label' => $available_credit_label,
				'result'                 => array(
					'results'                    => $results,
					'keyword_input'              => $keyword_input_values,
					'keyword_ideas'              => $keyword_ideas_values,
					'manual_keyword_api_results' => $manual_keyword_api_results,
				),
			);

			echo wp_json_encode( $output );
			exit;
		}
	}
}

new WTAI_Product_Keyword();
