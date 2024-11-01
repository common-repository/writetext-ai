<?php
/**
 * Installation class for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WTAI Installation class.
 */
class WTAI_Installation extends WTAI_Init {
	/**
	 * SEO plugin lists.
	 *
	 * @var string
	 */
	private $get_list_of_seoplugin = array();

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
	 * Get list of SEO plugins.
	 */
	public function get_list_of_seoplugin() {
		return array(
			'wordpress-seo-premium'   => array(
				'name'       => 'Yoast SEO Premium Plugin',
				'plugin_uri' => 'wordpress-seo-premium/wp-seo-premium.php',
				'type'       => 'paid',
			),
			'wordpress-seo'           => array(
				'name'       => 'Yoast SEO',
				'plugin_uri' => 'wordpress-seo/wp-seo.php',
				'type'       => 'free',
			),
			'seo-by-rank-math-pro'    => array(
				'name'       => 'Rank Math SEO PRO',
				'plugin_uri' => 'seo-by-rank-math-pro/rank-math-pro.php',
				'type'       => 'paid',
			),
			'seo-by-rank-math'        => array(
				'name'       => 'Rank Math SEO',
				'plugin_uri' => 'seo-by-rank-math/rank-math.php',
				'type'       => 'free',
			),
			'all-in-one-seo-pack'     => array(
				'name'       => 'All in One SEO',
				'plugin_uri' => 'all-in-one-seo-pack/all_in_one_seo_pack.php',
				'type'       => 'free',
			),
			'all-in-one-seo-pack-pro' => array(
				'name'       => 'All in One SEO Pro',
				'plugin_uri' => 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
				'type'       => 'paid',
			),

		);
	}

	/**
	 * Initialize hooks.
	 */
	public function init_hooks() {
		add_action( 'init', array( $this, 'verify_seo_plugin_if_still_install' ) );

		// Checking plugin status.
		add_filter( 'wtai_seo_plugin_status', array( $this, 'get_seo_plugin_status' ), 10, 2 );

		// Checking plugin status.
		add_filter( 'wtai_seo_plugin_list_filters', array( $this, 'get_seo_plugin_status_filters' ), 10 );

		// Download and install plugin.
		add_action( 'wtai_seo_plugin_download_and_active', array( $this, 'get_seo_download_and_active' ), 10, 1 );

		// Callback for installation process.
		add_action( 'wtai_installation_render', array( $this, 'get_installation_template_callback' ) );

		// Ajax setup.
		add_action( 'wp_ajax_wtai_get_process_seo_step', array( $this, 'get_process_seo_step' ) );

		add_action( 'rest_api_init', array( $this, 'remote_install_and_activate_endpoints' ), 10 );
	}

	/**
	 * Verify SEO plugin if still installed.
	 */
	public function verify_seo_plugin_if_still_install() {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'write-text-ai', 'write-text-ai-settings', 'write-text-ai-category' ), true ) ) {
			$source = get_option( 'wtai_installation_source', '' );
			if ( $source ) {
				$verify_source_if_active = $this->get_seo_plugin_installed( $source );

				if ( ! $verify_source_if_active ) {
					update_option( 'wtai_installation_source', '' );
					update_option( 'wtai_installation_step', 2 );

					update_option( 'wtai_installation_source_updated', '1' );
				}
			} else {
				$option_step = intval( get_option( 'wtai_installation_step', 1 ) );
				if ( 1 !== $option_step ) {
					update_option( 'wtai_installation_step', 2 );
				}
			}
		}
	}

	/**
	 * Get SEO process step
	 */
	public function get_process_seo_step() {
		define( 'WTAI_DOING_AJAX', true );

		$is_ajax = ( defined( 'DOING_AJAX' ) && DOING_AJAX );
		$message = '';
		$status  = 0;
		$step    = 0;

		if ( $is_ajax ) {
			if ( isset( $_REQUEST['wtai_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['wtai_nonce'] ) ), 'wtai-install-nonce' ) ) {

				// phpcs:ignore WordPress.Security.NonceVerification
				$step   = isset( $_POST['step'] ) && is_numeric( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : 1;
				$status = 1;
				switch ( $step ) {
					case 1:
						if ( get_option( 'wtai_api_token' ) ) {
							$step = 2;
						} else {
							$step = 1;
						}
						break;
					case 2:
						// phpcs:ignore WordPress.Security.NonceVerification
						$seo_choice = isset( $_POST['seo_choice'] ) ? sanitize_text_field( wp_unslash( $_POST['seo_choice'] ) ) : '';
						if ( $seo_choice ) {
							update_option( 'wtai_installation_source', $seo_choice );

							$download_and_activate = $this->get_seo_download_and_active( $seo_choice );

							$apply_steps = 0;
							if ( $download_and_activate ) {
								$apply_steps = 1;
							}

							// Try again.
							if ( 0 === intval( $apply_steps ) ) {
								$max_try = 1;

								for ( $t = 0; $t < $max_try; $t++ ) {
									$download_and_activate = $this->get_seo_download_and_active( $seo_choice );

									if ( $this->is_plugin_installed( $seo_choice ) && $download_and_activate ) {
										$apply_steps = 1;
										break;
									}
								}
							}

							if ( 1 === intval( $apply_steps ) ) {
								$step = 3;
							} else {
								if ( $this->is_plugin_installed( $seo_choice ) ) {
									$message = __( "Can't activate the plugin. Please try again or contact your site's administrator.", 'writetext-ai' ) . ' ';
								} else {
									$message = __( "Can't download the plugin. Please try again or contact your site's administrator.", 'writetext-ai' ) . ' ';
								}

								$status = 0;
							}
						} else {
							$verify_seo_plugin = $this->get_seo_plugin_installed();
							update_option( 'wtai_installation_source', $verify_seo_plugin );
							$step = 3;
						}

						break;
					case 3:
						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['select_text_tones'] ) ) {
							update_option( 'wtai_installation_tones', explode( ',', sanitize_text_field( wp_unslash( $_POST['select_text_tones'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['select_text_audiences'] ) ) {
							update_option( 'wtai_installation_audiences', explode( ',', sanitize_text_field( wp_unslash( $_POST['select_text_audiences'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}

						// phpcs:ignore WordPress.Security.NonceVerification
						if ( isset( $_POST['select_text_styles'] ) ) {
							update_option( 'wtai_installation_styles', sanitize_text_field( wp_unslash( $_POST['select_text_styles'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
						}

						$account_credit_details = wtai_get_account_credit_details( true );
						$is_premium             = $account_credit_details['is_premium'];

						$select_product_attr = isset( $_POST['select_product_attr'] ) ? explode( ',', sanitize_text_field( wp_unslash( $_POST['select_product_attr'] ) ) ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
						if ( is_array( $select_product_attr ) ) {
							update_option( 'wtai_installation_product_attr', $select_product_attr );
						}

						$product_description_min  = isset( $_POST['product_description_min'] ) ? sanitize_text_field( wp_unslash( $_POST['product_description_min'] ) ) : 75; // phpcs:ignore WordPress.Security.NonceVerification
						$product_description_max  = isset( $_POST['product_description_max'] ) ? sanitize_text_field( wp_unslash( $_POST['product_description_max'] ) ) : 150; // phpcs:ignore WordPress.Security.NonceVerification
						$product_excerpt_min      = isset( $_POST['product_excerpt_min'] ) ? sanitize_text_field( wp_unslash( $_POST['product_excerpt_min'] ) ) : 25; // phpcs:ignore WordPress.Security.NonceVerification
						$product_excerpt_max      = isset( $_POST['product_excerpt_max'] ) ? sanitize_text_field( wp_unslash( $_POST['product_excerpt_max'] ) ) : 50; // phpcs:ignore WordPress.Security.NonceVerification
						$category_description_min = isset( $_POST['category_description_min'] ) ? sanitize_text_field( wp_unslash( $_POST['category_description_min'] ) ) : 75; // phpcs:ignore WordPress.Security.NonceVerification
						$category_description_max = isset( $_POST['category_description_max'] ) ? sanitize_text_field( wp_unslash( $_POST['category_description_max'] ) ) : 150; // phpcs:ignore WordPress.Security.NonceVerification

						update_option( 'wtai_installation_product_description_min', $product_description_min );
						update_option( 'wtai_installation_product_description_max', $product_description_max );
						update_option( 'wtai_installation_product_excerpt_min', $product_excerpt_min );
						update_option( 'wtai_installation_product_excerpt_max', $product_excerpt_max );
						update_option( 'wtai_installation_category_description_min', $category_description_min );
						update_option( 'wtai_installation_category_description_max', $category_description_max );

						$current_user_id = get_current_user_id();
						$selected_types  = array( 'page_title', 'page_description', 'product_description', 'product_excerpt', 'open_graph', 'image_alt_text' );

						delete_user_meta( $current_user_id, 'wtai_preselected_types' );
						update_user_meta( $current_user_id, 'wtai_preselected_types', $selected_types );

						$selected_types_category = array( 'page_title', 'page_description', 'category_description', 'open_graph' );

						delete_user_meta( $current_user_id, 'wtai_preselected_category_types' );
						update_user_meta( $current_user_id, 'wtai_preselected_category_types', $selected_types_category );

						$step = 4;

						break;
					case 4:
						// Maybe migrate old product meta.
						wtai_maybe_migrate_wta_metas();

						$step = 5;
						break;
				}
				$result_step = ( is_numeric( $step ) ) ? $step : 3;

				if ( 5 === $result_step ) {
					update_option( 'wtai_installation_source_updated', '' );
				}

				// Record deactivation statistics.
				do_action( 'wtai_record_installation_statistics', 'Step ' . $step, 0 );

				update_option( 'wtai_installation_step', $result_step );
			} else {
				$message = WTAI_INVALID_NONCE_MESSAGE;
			}

			echo wp_json_encode(
				array(
					'step'    => $step,
					'status'  => $status,
					'message' => $message,
				)
			);
			exit;
		}
	}

	/**
	 * Get installation template callback.
	 */
	public function get_installation_template_callback() {
		$is_allowed = false;
		if ( is_super_admin() || current_user_can( 'activate_plugins' ) ) {
			$is_allowed = true;
		}

		define( 'WTAI_DOING_INSTALLATION', true );

		$seo_lists          = $this->get_list_of_seoplugin();
		$product_attributes = $this->get_product_settings_attribute();
		$tone_and_styles    = $this->get_product_text_style_tone_audiences();
		$seo_lists          = apply_filters( 'wtai_seo_plugin_list_filters', $seo_lists );
		$get_current_step   = intval( get_option( 'wtai_installation_step', 1 ) );
		$domain_validate    = get_option( 'wtai_api_token', '' );
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
		$field_text_fields  = array();
		foreach ( array( 'product_description', 'product_excerpt', 'category_description' ) as $field ) {
			$field_text_fields[ $field . '_min' ] = ( apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field . '_min' ) ) ? apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field . '_min' ) : $global_rule_fields['minOutputWords'];
			$field_text_fields[ $field . '_max' ] = ( apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field . '_max' ) ) ? apply_filters( 'wtai_global_settings', 'wtai_installation_' . $field . '_max' ) : $global_rule_fields['maxOutputWords'];
		}

		do_action( 'wtai_premium_modal' );

		// Record step 1 loaded.
		if ( 1 !== intval( get_option( 'wtai_installation_step_1_loaded' ) ) ) {
			do_action( 'wtai_record_installation_statistics', 'Step 1', 0 );

			update_option( 'wtai_installation_step_1_loaded', 1 );
		}

		include_once WTAI_ABSPATH . 'templates/admin/install.php';
	}

	/**
	 * Get SEO download and active.
	 *
	 * @param string $plugin_uri_key Plugin URI key.
	 * @return array
	 */
	public function get_seo_download_and_active( $plugin_uri_key ) {
		$seo_lists = $this->get_list_of_seoplugin();

		$return = true;

		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/misc.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

		$plugin_uri = $seo_lists[ $plugin_uri_key ]['plugin_uri'];
		$plugin_dir = WP_PLUGIN_DIR . '/' . $plugin_uri;

		/*
		 * Don't try installing plugins that already exist (wastes time downloading files that
		 * won't be used
		 */
		if ( ! file_exists( $plugin_dir ) ) {
			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => $plugin_uri_key,
					'fields' => array(
						'short_description' => false,
						'sections'          => false,
						'requires'          => false,
						'rating'            => false,
						'ratings'           => false,
						'downloaded'        => false,
						'last_updated'      => false,
						'added'             => false,
						'tags'              => false,
						'compatibility'     => false,
						'homepage'          => false,
						'donate_link'       => false,
					),
				)
			);

			$skin     = new WP_Ajax_Upgrader_Skin( array( 'api' => $api ) );
			$upgrader = new Plugin_Upgrader( $skin );
			$install  = $upgrader->install( $api->download_link, array( 'clear_update_cache' => true ) );

			if ( true !== $install ) {
				$return = false;
			}
		}

		if ( file_exists( $plugin_dir ) ) {
			$activated = activate_plugin( $plugin_dir );

			if ( ! is_plugin_active( $plugin_uri ) ) {
				$return = false;
			}
		} else {
			$return = false;
		}

		return $return;
	}

	/**
	 * Get list of SEO plugin installed
	 *
	 * @param string $plugin_uri_key_set Plugin URI key.
	 * @return array
	 */
	public function get_seo_plugin_installed( $plugin_uri_key_set = '' ) {
		$seo_active  = array();
		$plugin_uris = $this->get_list_of_seoplugin();
		foreach ( $plugin_uris as $plugin_uri_key => $plugin_uri_value ) {
			if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_uri_value['plugin_uri'] )
				&& is_plugin_active( $plugin_uri_value['plugin_uri'] ) ) {
				$seo_active[] = $plugin_uri_key;
			}
		}

		if ( $plugin_uri_key_set && ! in_array( $plugin_uri_key_set, $seo_active, true ) ) {
			return false;
		}

		if ( 'seo-by-rank-math-pro' === $plugin_uri_key_set && ( in_array( 'seo-by-rank-math-pro', $seo_active, true ) && in_array( 'seo-by-rank-math', $seo_active, true ) ) ) {
			return $plugin_uri_key_set;
		}

		if ( 'wordpress-seo-premium' === $plugin_uri_key_set && ( in_array( 'wordpress-seo-premium', $seo_active, true ) && in_array( 'wordpress-seo', $seo_active, true ) ) ) {
			return $plugin_uri_key_set;
		}

		if ( 1 === count( $seo_active ) ) {
			$seo_list_active = reset( $seo_active );
			if ( $plugin_uri_key_set ) {
				return $seo_list_active === $plugin_uri_key_set ? $seo_list_active : false;
			} else {
				return $seo_list_active;
			}
		}

		return false;
	}

	/**
	 * Get SEO plugin statuses.
	 *
	 * @param string $status Status.
	 * @param string $plugin_uri Plugin URI.
	 *
	 * @return array
	 */
	public function get_seo_plugin_status( $status, $plugin_uri ) {
		if ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin_uri ) ) {
			$results = array(
				'label'        => __( 'Not installed', 'writetext-ai' ),
				'class'        => '',
				'label_suffix' => __( 'Install and activate', 'writetext-ai' ),
			);
		} elseif ( ! is_plugin_active( $plugin_uri ) ) {
			$results = array(
				'label'        => __( 'Inactive', 'writetext-ai' ),
				'class'        => '',
				'label_suffix' => __( 'Activate now', 'writetext-ai' ),
			);
		} else {
			$results = array(
				'label'        => __( 'Active', 'writetext-ai' ),
				'class'        => 'wtai-active',
				'label_suffix' => __( 'Next', 'writetext-ai' ),
			);
		}

		return $results;
	}

	/**
	 * Get SEO plugin status filters.
	 *
	 * @param string $seo_plugins SEO plugins.
	 *
	 * @return array
	 */
	public function get_seo_plugin_status_filters( $seo_plugins ) {
		$results     = array();
		$install     = 0;
		$not_install = 0;
		$active      = 0;
		$active_list = array();
		$status      = '';
		foreach ( $seo_plugins as $plugin_uri => $seo_plugin_value ) {

			if ( is_plugin_active( $seo_plugin_value['plugin_uri'] ) ) {
				$results[ $plugin_uri ]           = $seo_plugin_value;
				$results[ $plugin_uri ]['status'] = 'active';
				$active_list[]                    = $plugin_uri;
				++$active;
			} elseif ( file_exists( WP_PLUGIN_DIR . '/' . $seo_plugin_value['plugin_uri'] ) ) {
				$results[ $plugin_uri ]           = $seo_plugin_value;
				$results[ $plugin_uri ]['status'] = 'install';
				++$install;
			} elseif ( 'free' === $seo_plugin_value['type'] ) {
					$results[ $plugin_uri ]           = $seo_plugin_value;
					$results[ $plugin_uri ]['status'] = 'install';
					++$not_install;
			}

			if ( 'paid' === $seo_plugin_value['type'] && ! file_exists( WP_PLUGIN_DIR . '/' . $seo_plugin_value['plugin_uri'] ) ) {
				unset( $seo_plugins[ $plugin_uri ] );
			}
		}

		if ( 0 === $active ) {
			if ( 0 === $install ) {
				$status = 'no_active_no_install';
			} elseif ( 1 === $install ) {
				$status = 'no_active_single_install';
			} else {
				$status = 'no_active_multi_install';
			}

			$results = $seo_plugins;

		} elseif ( 1 === $active ) {
			foreach ( $results as $result_key => $result ) {
				if ( 'install' === $result['status'] ) {
					unset( $results[ $result_key ] );
				}
			}
			$status = 'one_active';
		} elseif ( 2 === $active && ( in_array( 'seo-by-rank-math-pro', $active_list, true ) && in_array( 'seo-by-rank-math', $active_list, true ) ) ) {
			$status = 'multi_active_rankmath';
		} elseif ( 2 === $active && ( in_array( 'wordpress-seo-premium', $active_list, true ) && in_array( 'wordpress-seo', $active_list, true ) ) ) {
			$status = 'multi_active_yoast';
		} elseif ( $active > 1 ) {
			foreach ( $results as $result_key => $result ) {
				if ( 'install' === $result['status'] ) {
					unset( $results[ $result_key ] );
				}
			}
			$status = 'multi_active';
		}

		return array(
			'results' => $results,
			'status'  => $status,
		);
	}

	/**
	 * Remote install endpoints.
	 */
	public function remote_install_and_activate_endpoints() {
		register_rest_route(
			'wta/v1',
			'/install_seo',
			array(
				array(
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => array( $this, 'install_seo' ),
					'permission_callback' => array( $this, 'validate_route' ),
				),
			)
		);
	}

	/**
	 * Install SEO plugin.
	 *
	 * @param string $request Request.
	 */
	public function install_seo( $request ) {
		$seo_choice = $request['seo_choice'];

		do_action( 'wtai_seo_plugin_download_and_active', $seo_choice );

		return true;
	}

	/**
	 * Remote install SEO plugin.
	 *
	 * @param string $seo_choice SEO plugin choice.
	 */
	public function remote_install_seo( $seo_choice ) {

		$remote_url = site_url() . '/wp-json/wta/v1/install_seo';

		$body = array(
			'seo_choice' => $seo_choice,
		);

		$api_response = wp_remote_post(
			$remote_url,
			array(
				'method'    => 'POST',
				'sslverify' => false,
				'headers'   => array(
					'token' => $this->rest_get_user_token(),
				),
				'body'      => $body,
			)
		);
		$api_body     = json_decode( wp_remote_retrieve_body( $api_response ) );

		$seo_lists = $this->get_list_of_seoplugin();

		$plugin_dir = $seo_lists[ $seo_choice ]['plugin_uri'];

		$is_active = 0;
		if ( is_plugin_active( $plugin_dir ) ) {
			$is_active = 1;
		}

		return $is_active;
	}

	/**
	 * Validate route.
	 *
	 * @param  WP_REST_Request $request Request object.
	 *
	 * @return bool
	 */
	public function validate_route( $request ) {
		// Do permission checking here.
		$token = $request->get_header( 'token' );

		if ( ! $token ) {
			return false;
		}

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
		$token      = base64_decode( $token );
		$token_args = explode( ':', $token );

		$user         = $token_args[0];
		$user_session = $token_args[1];
		$user_nonce   = $token_args[2];

		$manager  = WP_Session_Tokens::get_instance( $user );
		$sessions = $manager->get_all();

		$i = wp_nonce_tick();

		// Nonce generated 0-12 hours ago.
		$expected = substr( wp_hash( $i . '|wtai_install_user_nonce_' . $user . '|' . $user . '|' . $user_session, 'nonce' ), -12, 10 );
		if ( $sessions && hash_equals( $expected, $user_nonce ) ) {
			return true;
		}

		return false;
	}

	/**
	 * REST get user token.
	 *
	 * @return string $token Token.
	 */
	private function rest_get_user_token() {
		$user_session_token = wp_get_session_token();
		$user_id            = get_current_user_id();

		$token = $user_id . ':' . $user_session_token . ':' . wp_create_nonce( 'wtai_install_user_nonce_' . $user_id );

		// phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return base64_encode( $token );
	}

	/**
	 * Check if SEO plugin is active.
	 *
	 * @param string $seo_choice SEO plugin choice.
	 */
	public function is_plugin_installed( $seo_choice = '' ) {
		if ( ! $seo_choice ) {
			return false;
		}

		$seo_lists = $this->get_list_of_seoplugin();

		$plugin_dir  = $seo_lists[ $seo_choice ]['plugin_uri'];
		$plugin_root = WP_PLUGIN_DIR;

		$plugin_loc = $plugin_root . '/' . $plugin_dir;

		$is_installed = false;
		if ( file_exists( $plugin_loc ) ) {
			$is_installed = true;
		}

		return $is_installed;
	}
}

new WTAI_Installation();
