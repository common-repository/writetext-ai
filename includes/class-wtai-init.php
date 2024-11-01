<?php
/**
 * Init class for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * WTAI initial class.
 */
class WTAI_Init {
	/**
	 * Construct class.
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
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'get_userrole_and_capabilities' ) );
		add_filter( 'body_class', array( $this, 'add_class_based_on_os' ) );

		add_action( 'admin_head', array( $this, 'inline_style_menu_icon' ) );
		add_filter( 'wtai_fields', array( $this, 'get_fields_list' ) );
		add_filter( 'wtai_category_fields', array( $this, 'get_category_fields_list' ) );

		add_action( 'admin_init', array( $this, 'add_userrole_plus_caps' ) );

		// WPML functions.
		add_filter( 'wtai_language_code_by_product', array( $this, 'get_language_locale_by_product' ), 10, 3 );
		add_filter( 'wtai_language_code', array( $this, 'get_language_locale' ), 10, 2 );
		add_filter( 'wtai_language_active', array( $this, 'get_language_actives' ), 10 );

		// Translate user role names.
		add_filter( 'gettext_with_context', array( $this, 'translate_user_role_names' ), 10, 4 );

		add_action( 'woocommerce_new_product', array( $this, 'save_new_product_attributes' ), 10, 2 );
		add_action( 'woocommerce_update_product', array( $this, 'save_new_product_attributes' ), 10, 2 );

		add_filter( 'plugin_action_links', array( $this, 'plugin_action_links' ), 10, 2 );

		add_action( 'init', array( $this, 'admin_maybe_redirect' ), 20 );
	}

	/**
	 * Check if multilingual plugin is active.
	 */
	public function is_multilinugal_plugin_active() {
		$is_active = false;
		if ( function_exists( 'pll_current_language' ) ) {
			$is_active = true;
		} else {
			$wpml_current_lang = apply_filters( 'wpml_current_language', null );
			if ( $wpml_current_lang ) {
				$is_active = true;
			}
		}

		return $is_active;
	}

	/**
	 * Get active languages.
	 *
	 * @param  array $language_actives Language Actives.
	 */
	public function get_language_actives( $language_actives = array() ) {
		$language_actives = array(
			wtai_get_site_language(),
		);
		if ( function_exists( 'pll_the_languages' ) ) {
			$languages = pll_the_languages(
				array(
					'hide_if_empty' => 0,
					'raw'           => 1,
				)
			);
			foreach ( $languages as $language_key => $language_value ) {
				if ( ! in_array( str_replace( '-', '_', $language_value['locale'] ), $language_actives, true ) ) {
					$language_actives[] = str_replace( '-', '_', $language_value['locale'] );
				}
			}
		} else {
			$wpml_active_languages = apply_filters( 'wpml_active_languages', null );
			if ( is_array( $wpml_active_languages ) && ! empty( $wpml_active_languages ) ) {
				foreach ( $wpml_active_languages as $wpml_active_language_key => $wpml_active_language_values ) {
					if ( ! in_array( $wpml_active_language_values['default_locale'], $language_actives, true ) ) {
						$language_actives[] = $wpml_active_language_values['default_locale'];
					}
				}
			}
		}
		return $language_actives;
	}

	/**
	 * Get language locale for the site.
	 *
	 * @param  string $language_code Language Code.
	 * @param  bool   $do_language_mapping Do Language Mapping.
	 */
	public function get_language_locale( $language_code, $do_language_mapping = true ) {
		if ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language( 'locale' );
			if ( ! $lang ) {
				$lang = pll_default_language( 'locale' );
			}

			return trim( $lang );
		} else {
			$my_current_lang = apply_filters( 'wpml_current_language', null );
			if ( $my_current_lang ) {
				$wpml_active_languages = apply_filters( 'wpml_active_languages', null );
				if ( isset( $wpml_active_languages[ $my_current_lang ]['default_locale'] ) ) {
					return trim( $wpml_active_languages[ $my_current_lang ]['default_locale'] );
				}
			}
		}

		if ( $do_language_mapping ) {
			$language_code = wtai_match_language_locale( $language_code );
		}

		return $language_code;
	}

	/**
	 * Get language locale per product.
	 *
	 * @param  string $language_code Language Code.
	 * @param  array  $product_ids   Product IDs.
	 * @param  bool   $do_language_mapping Do Language Mapping.
	 */
	public function get_language_locale_by_product( $language_code, $product_ids = array(), $do_language_mapping = true ) {
		if ( ! is_array( $product_ids ) && ! empty( $product_ids ) ) {
			$product_ids = ( strpos( $product_ids, ',' ) !== false ) ? explode( ',', $product_ids ) : array( $product_ids );
		}

		if ( is_array( $product_ids ) ) {
			$product_ids = array_filter( $product_ids );
		}

		if ( is_array( $product_ids ) && ! empty( $product_ids ) ) {

			foreach ( $product_ids as $product_id ) {
				if ( function_exists( 'pll_get_post_language' ) ) {
					$language = pll_get_post_language( $product_id, 'locale' );
					if ( ! $language ) {
						$language = pll_current_language( 'locale' );
					}
					if ( ! $language ) {
						$language = pll_default_language( 'locale' );
					}
					if ( ! $language ) {
						$language = wtai_get_site_language();
					}

					$language = str_replace( '_', '-', str_replace( '_formal', '', $language ) );
					$language = str_replace( '_', '-', str_replace( '_informal', '', $language ) );

					if ( $do_language_mapping ) {
						$language = wtai_match_language_locale( $language );
					}

					return $language;
				} else {
					$wpml_language_code = apply_filters( 'wpml_post_language_details', null, $product_id );

					if ( ! is_wp_error( $wpml_language_code ) && isset( $wpml_language_code['locale'] ) && $wpml_language_code['locale'] ) {

						$language = str_replace( '_', '-', str_replace( '_formal', '', $wpml_language_code['locale'] ) );
						$language = str_replace( '_', '-', str_replace( '_informal', '', $language ) );

						if ( $do_language_mapping ) {
							$language = wtai_match_language_locale( $language );
						}

						return $language;
					}
				}
			}
		} else {
			// Fallback to default lang if no products where set.
			// Polylang.
			$current_language = '';
			if ( function_exists( 'pll_current_language' ) ) {
				$current_language = pll_current_language( 'locale' );

				if ( ! $current_language ) {
					$current_language = pll_default_language( 'locale' );
				}

				if ( ! $current_language ) {
					$current_language = wtai_get_site_language();
				}
			} else {
				$current_language = apply_filters( 'wpml_current_language', null );
			}

			if ( $current_language ) {
				$language_code = $current_language;
			}
		}

		$language_code = str_replace( '_', '-', str_replace( '_formal', '', $language_code ) );
		$language_code = str_replace( '_', '-', str_replace( '_informal', '', $language_code ) );

		if ( $do_language_mapping ) {
			$language_code = wtai_match_language_locale( $language_code );
		}

		return $language_code;
	}

	/**
	 * Load text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'writetext-ai', false, WTAI_FOLDER_NAME . '/languages' );
	}

	/**
	 * Get WTA text fields.
	 *
	 * @param array $fields Fields.
	 */
	public function get_fields_list( $fields = array() ) {
		$fields = array(
			'page_title'          => __( 'Meta title', 'writetext-ai' ),
			'page_description'    => __( 'Meta description', 'writetext-ai' ),
			'product_description' => __( 'Product description', 'writetext-ai' ),
			'product_excerpt'     => __( 'Product short description', 'writetext-ai' ),
			'open_graph'          => __( 'Open Graph text', 'writetext-ai' ),
		);

		return $fields;
	}

	/**
	 * Get WTA category text fields.
	 *
	 * @param array $fields Fields.
	 */
	public function get_category_fields_list( $fields = array() ) {
		$fields = array(
			'page_title'           => __( 'Meta title', 'writetext-ai' ),
			'page_description'     => __( 'Meta description', 'writetext-ai' ),
			'category_description' => __( 'Category description', 'writetext-ai' ),
			'open_graph'           => __( 'Open Graph text', 'writetext-ai' ),
		);

		return $fields;
	}

	/**
	 * Add capability to user role.
	 */
	public function add_userrole_plus_caps() {
		// Get role editor reference capabilties.
		$editor_capabilities = array(
			'moderate_comments'             => true,
			'manage_categories'             => true,
			'manage_links'                  => true,
			'upload_files'                  => true,
			'unfiltered_html'               => true,
			'edit_posts'                    => true,
			'edit_others_posts'             => true,
			'edit_published_posts'          => true,
			'publish_posts'                 => true,
			'edit_pages'                    => true,
			'read'                          => true,
			'level_7'                       => true,
			'level_6'                       => true,
			'level_5'                       => true,
			'level_4'                       => true,
			'level_3'                       => true,
			'level_2'                       => true,
			'level_true'                    => true,
			'level_0'                       => true,
			'edit_others_pages'             => true,
			'edit_published_pages'          => true,
			'publish_pages'                 => true,
			'delete_pages'                  => true,
			'delete_others_pages'           => true,
			'delete_published_pages'        => true,
			'delete_posts'                  => true,
			'delete_others_posts'           => true,
			'delete_published_posts'        => true,
			'delete_private_posts'          => true,
			'edit_private_posts'            => true,
			'read_private_posts'            => true,
			'delete_private_pages'          => true,
			'edit_private_pages'            => true,
			'read_private_pages'            => true,
			'aioseo_page_analysis'          => true,
			'aioseo_page_general_settings'  => true,
			'aioseo_page_advanced_settings' => true,
			'aioseo_page_schema_settings'   => true,
			'aioseo_page_social_settings'   => true,
			'rank_math_site_analysis'       => true,
			'rank_math_onpage_analysis'     => true,
			'rank_math_onpage_general'      => true,
			'rank_math_onpage_snippet'      => true,
			'rank_math_onpage_social'       => true,
			'wpseo_bulk_edit'               => true,
			'wpseo_edit_advanced_metadata'  => true,
			'edit_others_product'           => true,
			'edit_others_products'          => true,
		);

		// Check wta role incase new capabilities change.
		$wtai_roles = array( 'writetext_ai_administrator', 'writetext_ai_editor', 'writetext_ai_contributor' );
		foreach ( $wtai_roles as $wtai_role ) {
			$wtai_role_cap = get_role( $wtai_role );
			foreach ( $editor_capabilities as $editor_capability_key => $editor_capabilities_access ) {
				if ( ! $wtai_role_cap->has_cap( $editor_capability_key ) && $editor_capabilities_access ) {
					$wtai_role_cap->add_cap( $editor_capability_key );
				}
			}
		}
	}

	/**
	 * Custom user role and capabilities for WTA.
	 */
	public function get_userrole_and_capabilities() {
		$refresh_roles = false;
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['WTARefreshRoles'] ) && '1' === $_GET['WTARefreshRoles'] ) {
			$refresh_roles = true;
		}

		// Check if custom user capability is set.
		$caps  = array(
			'writeai_settings_page',
			'writeai_change_product_attr',
			'writeai_change_tone_style_audience',
			'writeai_change_number_of_words',
			'writeai_generate_text',
			'writeai_keywords',
			'writeai_orderproduct_details',
			'writeai_product_edit_text',
			'writeai_mark_reviewed',
			'writeai_transfer_generated_text',
			'writeai_select_localized_country',
		);
		$roles = array(
			'administrator',
			'writetext_ai_administrator',
			'writetext_ai_editor',
			'writetext_ai_contributor',
		);

		$has_missing_cap = false;
		foreach ( $roles as $role ) {
			$role_object = get_role( $role );
			if ( $role_object ) {
				foreach ( $caps as $cap ) {
					if ( ! $role_object->has_cap( $cap ) ) {
						$has_missing_cap = true;
					}
				}
			}
		}

		// Force refresh cap if there is missing cap.
		if ( $has_missing_cap ) {
			$refresh_roles = true;
		}

		$roles_set = get_option( 'wtai_userrole', '' );
		if ( ! $roles_set || $refresh_roles ) {
			$editor_capabilities = array(
				'moderate_comments'             => true,
				'manage_categories'             => true,
				'manage_links'                  => true,
				'upload_files'                  => true,
				'unfiltered_html'               => true,
				'edit_posts'                    => true,
				'edit_others_posts'             => true,
				'edit_published_posts'          => true,
				'publish_posts'                 => true,
				'edit_pages'                    => true,
				'read'                          => true,
				'level_7'                       => true,
				'level_6'                       => true,
				'level_5'                       => true,
				'level_4'                       => true,
				'level_3'                       => true,
				'level_2'                       => true,
				'level_true'                    => true,
				'level_0'                       => true,
				'edit_others_pages'             => true,
				'edit_published_pages'          => true,
				'publish_pages'                 => true,
				'delete_pages'                  => true,
				'delete_others_pages'           => true,
				'delete_published_pages'        => true,
				'delete_posts'                  => true,
				'delete_others_posts'           => true,
				'delete_published_posts'        => true,
				'delete_private_posts'          => true,
				'edit_private_posts'            => true,
				'read_private_posts'            => true,
				'delete_private_pages'          => true,
				'edit_private_pages'            => true,
				'read_private_pages'            => true,
				'aioseo_page_analysis'          => true,
				'aioseo_page_general_settings'  => true,
				'aioseo_page_advanced_settings' => true,
				'aioseo_page_schema_settings'   => true,
				'aioseo_page_social_settings'   => true,
				'rank_math_site_analysis'       => true,
				'rank_math_onpage_analysis'     => true,
				'rank_math_onpage_general'      => true,
				'rank_math_onpage_snippet'      => true,
				'rank_math_onpage_social'       => true,
				'wpseo_bulk_edit'               => true,
				'wpseo_edit_advanced_metadata'  => true,
			);

			add_role(
				'writetext_ai_administrator',
				__( 'WriteText.ai Administrator', 'writetext-ai' ),
				array_merge(
					$editor_capabilities,
					array(
						'writeai_settings_page'            => true,
						'writeai_change_product_attr'      => true,
						'writeai_change_tone_style_audience' => true,
						'writeai_change_number_of_words'   => true,
						'writeai_generate_text'            => true,
						'writeai_keywords'                 => true,
						'writeai_orderproduct_details'     => true,
						'writeai_product_edit_text'        => true,
						'writeai_mark_reviewed'            => true,
						'writeai_transfer_generated_text'  => true,
						'writeai_select_localized_country' => true,
					)
				)
			);

			add_role(
				'writetext_ai_editor',
				__( 'WriteText.ai Editor', 'writetext-ai' ),
				array_merge(
					$editor_capabilities,
					array(
						'writeai_change_product_attr'      => true,
						'writeai_change_tone_style_audience' => true,
						'writeai_change_number_of_words'   => true,
						'writeai_generate_text'            => true,
						'writeai_keywords'                 => true,
						'writeai_orderproduct_details'     => true,
						'writeai_product_edit_text'        => true,
						'writeai_mark_reviewed'            => true,
						'writeai_transfer_generated_text'  => true,
						'writeai_select_localized_country' => true,
					)
				)
			);

			add_role(
				'writetext_ai_contributor',
				__( 'WriteText.ai Contributor', 'writetext-ai' ),
				array_merge(
					$editor_capabilities,
					array(
						'writeai_change_product_attr'      => true,
						'writeai_change_tone_style_audience' => true,
						'writeai_change_number_of_words'   => true,
						'writeai_generate_text'            => true,
						'writeai_keywords'                 => true,
						'writeai_orderproduct_details'     => true,
						'writeai_product_edit_text'        => true,
						'writeai_select_localized_country' => true,
					)
				)
			);

			// Add new roles for backward compatibility.
			$wtai_roles = array( 'writetext_ai_administrator', 'writetext_ai_editor', 'writetext_ai_contributor' );
			foreach ( $wtai_roles as $wtai_role ) {
				$role = get_role( $wtai_role );
				$role->add_cap( 'writeai_select_localized_country' );
			}

			update_option( 'wtai_userrole', true );
		}

		// Additional capability for admin user role in multisite.
		$roles_set_multisite = get_option( 'wtai_userrole_multisite', '' );
		if ( ( is_multisite() && ! $roles_set_multisite ) || $refresh_roles ) {
			$role = get_role( 'administrator' );

			$role->add_cap( 'writeai_generate_text' );
			$role->add_cap( 'writeai_settings_page' );
			$role->add_cap( 'writeai_change_product_attr' );
			$role->add_cap( 'writeai_change_tone_style_audience' );
			$role->add_cap( 'writeai_change_number_of_words' );
			$role->add_cap( 'writeai_keywords' );
			$role->add_cap( 'writeai_orderproduct_details' );
			$role->add_cap( 'writeai_product_edit_text' );
			$role->add_cap( 'writeai_mark_reviewed' );
			$role->add_cap( 'writeai_transfer_generated_text' );
			$role->add_cap( 'writeai_select_localized_country' );

			update_option( 'wtai_userrole_multisite', true );
		}
	}

	/**
	 * Add body class based on OS.
	 *
	 * @param array $classes Array of body classes.
	 */
	public function add_class_based_on_os( $classes ) {
		$agent           = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $agent, 'Trident/7.0;' ) !== false || strpos( $agent, 'Edge' ) !== false ) {
			$classes[] = 'wp_browser_ie';
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $server_software, 'Ubuntu' ) || strpos( $agent, 'Linux' ) ) {
			$classes[] = 'wp_os_ubuntu_linux';
		}

		// phpcs:ignore WordPress.Security.NonceVerification
		if ( strpos( $agent, 'Mac OS' ) !== false ) {
			$classes[] = 'wp_os_mac';
		}

		return $classes;
	}

	/**
	 * Save new product attributes to WTA attribute transient.
	 *
	 * @param int    $product_id Post ID.
	 * @param object $product    Product object.
	 */
	public function save_new_product_attributes( $product_id, $product ) {

		if ( $product ) {
			$product_attr = $product->get_attributes();

			$custom_pa = get_option( 'wtai_transient_wc_custom_pa' );
			unset( $custom_pa[ $product_id ] );

			if ( $product_attr ) {
				foreach ( $product_attr as $attribute_id => $attr ) {
					$name = $attr->get_name();
					$id   = sanitize_title( $name );
					if ( ! str_contains( $name, 'pa_' ) ) {
						$custom_pa[ $product_id ][ $id ] = $name;
					}
				}
			}

			update_option( 'wtai_transient_wc_custom_pa', $custom_pa );
		}
	}

	/**
	 * Inline style for WTA menu icon.
	 */
	public function inline_style_menu_icon() {
		?>
		<style>
			.toplevel_page_write-text-ai .wp-menu-image.dashicons-before img {
				height: 22px;
				width: 22px;
				padding: 0 !important;
				opacity: 1 !important;
				position: relative;
				top: 5px;
			}
		</style>
		<?php
	}

	/**
	 * Product settings attribute.
	 */
	public function get_product_settings_attribute() {
		$product_attr          = wtai_get_product_attr();
		$product_attr_settings = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
		$product_info          = $product_attr;
		unset( $product_info['attributes'] );
		$product_attr = array_merge( $product_info, $product_attr['attributes'] );
		$html         = '<div class="wtai-product-attr-container"><div class="wtai-product-attr-wrap wtai-product-all-trigger">';
		$html        .= '<div class="wtai-label-select-all-wrap">
					<label for="wtai-select-all-attr"><input type="checkbox" name="wtai-select-all-attr" id="wtai-select-all-attr" class="wtai-product-cb-all" />' . __( 'Select all', 'writetext-ai' ) . '</label>
				  </div>';

		$feature_image_id = 'wtai-featured-product-image';

		$selected = '';

		$selected_featured_image = '';

		if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
			$selected_featured_image = 'checked'; // Featured image is selected by default in the settings page.
		} else {
			$selected_featured_image = ( is_array( $product_attr_settings ) && ! empty( $product_attr_settings ) && in_array( $feature_image_id, $product_attr_settings, true ) ) ? 'checked' : '';
		}

		$fi_extra_class = '';
		$premium_badge  = '';

		$html .= '<div class="wtai-product-attr-item wtai-product-attr-item-featured-image ' . $fi_extra_class . ' ">';
		$html .= '<label for="' . $feature_image_id . '"><input type="checkbox" name="wtai_installation_product_attr[]" id="' . $feature_image_id . '" class="wtai-product-attr-cb" value="' . $feature_image_id . '" ' . $selected_featured_image . ' />' . wc_clean( wp_unslash( __( 'Featured Image', 'writetext-ai' ) ) ) . ' <span class="wtai-featured-image-sub" >' . __( '(Analyze image to generate more relevant text)', 'writetext-ai' ) . '</span></label>' . $premium_badge;
		$html .= '</div>';

		$total = count( $product_attr );
		foreach ( $product_attr as $id => $attr ) {
			$id       = sanitize_title( $id );
			$selected = ( is_array( $product_attr_settings ) && ! empty( $product_attr_settings ) && in_array( $id, $product_attr_settings, true ) ) ? 'checked' : '';

			// Empty is not included.
			if ( '' !== $attr ) {
				$html .= '<div class="wtai-product-attr-item">';
				$html .= '<label for="wtai-product-attr-cb-' . $id . '"><input type="checkbox" name="wtai_installation_product_attr[]" id="wtai-product-attr-cb-' . $id . '" class="wtai-product-attr-cb" value="' . $id . '" ' . $selected . ' />' . wc_clean( wp_unslash( $attr ) ) . '</label>';
				$html .= '</div>';
			}
		}
		$html .= '</div></div>';
		return $html;
	}

	/**
	 * Product text style, tones and audiences.
	 *
	 * @param string $product_id Product ID.
	 * @param string $form Form.
	 * @param array  $fields Text fields.
	 * @param bool   $is_bulk_popup Is for bulk popup div.
	 */
	public function get_product_text_style_tone_audiences( $product_id = '', $form = '', $fields = array(), $is_bulk_popup = false ) {
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

		$texts = array(
			'tones'     => __( 'Tones', 'writetext-ai' ),
			'styles'    => __( 'Style', 'writetext-ai' ),
			'audiences' => __( 'Audiences', 'writetext-ai' ),
		);

		$current_user_id = get_current_user_id();

		if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
			$tones_user_pref    = array();
			$style_user_pref    = array();
			$audience_user_pref = array();
			$custom_tones_cb    = '';
			$custom_styles_cb   = '';
			$custom_styles_text = '';
		} else {
			$tones_user_pref    = get_user_meta( $current_user_id, 'wtai_tones_options_user_preference', true );
			$style_user_pref    = get_user_meta( $current_user_id, 'wtai_styles_options_user_preference', true );
			$audience_user_pref = get_user_meta( $current_user_id, 'wtai_audiences_options_user_preference', true );

			$custom_tones_cb    = get_user_meta( $current_user_id, 'wtai_tones_custom_user_preference', true );
			$custom_tones_text  = get_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', true );
			$custom_styles_cb   = get_user_meta( $current_user_id, 'wtai_styles_custom_user_preference', true );
			$custom_styles_text = get_user_meta( $current_user_id, 'wtai_styles_custom_text_user_preference', true );
		}

		$extra_wrap_class = '';
		$bulk_wrap_title  = '';
		if ( $is_bulk_popup ) {
			$extra_wrap_class = ' wtai-product-tonestyles-container-bulk ';
			$bulk_wrap_title  = ' title="' . __( 'Tones, styles and audiences are unavailable when reference product is selected.', 'writetext-ai' ) . '" ';
		}

		$html = '<div class="wtai-product-container wtai-product-tonestyles-container ' . $extra_wrap_class . '" ' . $bulk_wrap_title . ' >';
		if ( $form ) {
			$html .= '<div class="wtai-product-form-container">';
		}

		$force_check_type      = false;
		$is_doing_installation = false;
		if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
			$force_check_type      = true;
			$is_doing_installation = true;
		}

		$trigger_class = 'wtai-product-all-trigger';
		foreach ( $texts as $type => $label ) {
			if ( ! empty( $fields ) && ! in_array( $type, $fields, true ) ) {
				continue;
			}

			$lists          = apply_filters( 'wtai_generate_text_filters', array(), ucfirst( $type ), $force_check_type );
			$product_values = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( $type ) );
			if ( $form && 'tones' === $type ) {
				if ( '' !== $custom_tones_cb && '' !== $custom_tones_text ) {
					$product_values = array();
				} elseif ( ! empty( $tones_user_pref[0] ) ) {
					$product_values = $tones_user_pref;
				}
				$trigger_class = 'product_not_all_trigger';
			}

			$api_default_values = array();
			if ( $is_doing_installation ) {
				if ( isset( $lists[ ucfirst( $type ) . '_defaults' ] ) ) {
					$api_default_values = $lists[ ucfirst( $type ) . '_defaults' ];

					unset( $lists[ ucfirst( $type ) . '_defaults' ] );
				}
			}

			if ( $form && 'styles' === $type ) {
				if ( '' !== $custom_styles_cb && '' !== $custom_styles_text ) {
					$product_values = '';
				} elseif ( $style_user_pref ) {
					$product_values = $style_user_pref;
				}
			}

			if ( $form && 'audiences' === $type ) {
				$trigger_class = 'product_not_all_trigger';

				if ( $audience_user_pref ) {
					$product_values = $audience_user_pref;
				}
			}

			$html .= '<div class="wtai-product-wrap ' . $trigger_class . ' wtai-product-' . $type . '-wrap">';
			$html .= '<span class="wtai-product-label-text">' . $label . '</span>';

			if ( ! $form && 'styles' !== $type ) {
				$html .= '<div class="wtai-label-select-all-wrap"><label for="wtai-select-all-' . $type . '"><input type="checkbox" name="wtai_select_all_' . $type . '" id="wtai-select-all-' . $type . '" class="wtai-product-cb-all" />' . __( 'Select all', 'writetext-ai' ) . '</label></div>';
			}

			if ( 'form_audience' === $form ) {
				$html .= '<span class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></span>';
			}

			if ( $lists ) {
				foreach ( $lists as $list_key => $list_label ) {

					$selected = '';
					if ( in_array( strtolower( $type ), array( 'tones', 'audiences' ), true ) ) {
						if ( is_array( $product_values ) &&
							! empty( $product_values ) &&
							in_array( $list_key, $product_values, true ) ) {
							$selected = 'checked';
						}
					} else {
						$selected = ( $product_values && $list_key === $product_values ) ? 'checked' : '';
					}

					// Set defaults from api if doing installation.
					if ( $is_doing_installation && $api_default_values ) {
						$selected = in_array( $list_key, $api_default_values, true ) ? 'checked' : '';
					}

					if ( 'styles' === $type ) {
						$input_type = 'radio';
						$name       = 'wtai_installation_styles';
					} elseif ( 'tones' === $type ) {
						$input_type = 'checkbox';
						$name       = 'wtai_installation_tones[]';
					} elseif ( 'audiences' === $type ) {
						$input_type = 'checkbox';
						$name       = 'wtai_installation_audiences[]';
					}

					$html .= '<div class="wtai-product-item wtai-product-' . $type . '-item">';

					$disallowed_combination_tooltip     = wtai_get_disallowed_combination_tooltip_message( $list_key, $type );
					$disallowed_combination_tooltip_msg = '';
					$has_tooltip_class                  = '';
					if ( $disallowed_combination_tooltip ) {
						$tooltip_class_name = 'tooltip-generate-filter';
						if ( $is_bulk_popup ) {
							$tooltip_class_name = 'bulk-tooltip-generate-filter';
						}
						$has_tooltip_class                  = $tooltip_class_name . ' has-tooltip';
						$disallowed_combination_tooltip_msg = '<span class="wtai-disallowed-comb-tooltip" >' . implode( '<br>', $disallowed_combination_tooltip ) . '</span>';
					}

					if ( 'radio' === $input_type ) {
						$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><span><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' data-type="' . $type . '" /></span><span>' . $list_label . '</span></label>';
					} else {
						$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' data-type="' . $type . '" />' . $list_label . '</label>';
					}

					$html .= '</div>';
				}
			}

			if ( $form && 'tones' === $type ) {
				if ( isset( $custom_tones_cb ) && '' !== $custom_tones_cb && '' !== $custom_tones_text ) {
					$selected          = 'checked';
					$custom_tones_text = $custom_tones_text;
				} else {
					$selected          = '';
					$custom_tones_text = '';
				}

				$custom_tones_text_length = mb_strlen( $custom_tones_text, 'UTF-8' );

				ob_start();
				do_action( 'wtai_product_single_premium_badge', 'wtai-premium-custom-tone' );
				$premium_badge_tone = ob_get_clean();

				$disable_custom_tone  = 'disabled';
				$readonly_custom_tone = 'disabled';
				if ( WTAI_PREMIUM ) {
					$disable_custom_tone  = '';
					$readonly_custom_tone = '';
				}

				$input_type = 'checkbox';
				$html      .= '<div class="wtai-product-item wtai-product-tones-item">
					<label for="wtai-custom-tone-cb" class="wtai-char-count-parent-wrap wtai-custom-tone-wrap" ><input type="' . $input_type . '" name="wtai_custom_tone_cb" 
						class="wtai-custom-tone-cb" data-type="custom-tone" id="wtai-custom-tone-cb" value="wtaCustom" ' . $selected . ' ' . $disable_custom_tone . ' /><span class="wtai-custom-headline-span-title" >' . __( 'Custom', 'writetext-ai' ) . '</span>' . $premium_badge_tone . '<br />
						<input class="wtai-custom-tone-text wtai-max-length-field" id="wtai-custom-tone-text" name="wtai_custom_tone_text" value="' . $custom_tones_text . '" type="text" data-type="tone" 
						data-maxtext="' . $global_rule_fields['maxCustomToneLength'] . '" 
						maxlength="' . $global_rule_fields['maxCustomToneLength'] . '" 
						placeholder="' . __( 'Write your specific tone...', 'writetext-ai' ) . '" ' . $readonly_custom_tone . ' />
						<div class="wtai-char-count-wrap"><span class="wtai-char-count">' . $custom_tones_text_length . '</span>/<span class="wtai-max-count">' . $global_rule_fields['maxCustomToneLength'] . '</span> ' . __( ' Char', 'writetext-ai' ) . '</div>
					</label>
					</div>';
			}

			if ( $form && 'styles' === $type ) {
				$input_type = 'radio';

				if ( isset( $custom_styles_cb ) && '' !== $custom_styles_cb && ! empty( $custom_styles_text ) ) {
					$selected           = 'checked';
					$custom_styles_text = $custom_styles_text;

				} else {
					$selected           = '';
					$custom_styles_text = '';
				}

				$custom_styles_text_length = mb_strlen( $custom_styles_text, 'UTF-8' );

				ob_start();
				do_action( 'wtai_product_single_premium_badge', 'wtai-premium-custom-style' );
				$premium_badge_style = ob_get_clean();

				$disable_custom_style  = 'disabled';
				$readonly_custom_style = 'disabled';
				if ( WTAI_PREMIUM ) {
					$disable_custom_style  = '';
					$readonly_custom_style = '';
				}

				$html .= '<div class="wtai-product-item wtai-product-styles-item">
							<label for="wtai-custom-style-cb" class="wtai-char-count-parent-wrap wtai-custom-style-wrap" ><input type="' . $input_type . '" name="wtai_custom_style_cb" 
								class="wtai-custom-style-cb" data-type="custom-style" id="wtai-custom-style-cb" value="wtaCustom" ' . $selected . ' ' . $disable_custom_style . ' /><span class="wtai-custom-headline-span-title" >' . __( 'Custom', 'writetext-ai' ) . '</span>' . $premium_badge_style . '<br />
								<input class="wtai-custom-style-text wtai-max-length-field" id="wtai-custom-style-text" type="text" name="wtai_custom_style_text" value="' . $custom_styles_text . '" data-type="style" 
								data-maxtext="' . $global_rule_fields['maxCustomStyleLength'] . '" 
								maxlength="' . $global_rule_fields['maxCustomStyleLength'] . '" 
								placeholder="' . __( 'Write your specific style...', 'writetext-ai' ) . '" ' . $readonly_custom_style . ' />
								<div class="wtai-char-count-wrap"><span class="wtai-char-count">' . $custom_styles_text_length . '</span>/<span class="wtai-max-count">' . $global_rule_fields['maxCustomStyleLength'] . '</span> ' . __( ' Char', 'writetext-ai' ) . '</div>
							</label>
						</div>';
			}

			$html .= '</div>';
		}

		if ( $form ) {
			if ( 'form_audience' === $form ) {
				ob_start();
				do_action( 'wtai_product_single_premium_badge', 'wtai-premium-target-market' );
				$premium_badge_audience = ob_get_clean();

				ob_start();
				do_action( 'wtai_product_single_premium_badge', 'wtai-premium-target-market-text' );
				$premium_badge_audience_text = ob_get_clean();

				$html     .= '<div class="wtai-suggested-audience-container">';
					$html .= '<div class="wtai-suggested-audience-input-container wtai-char-count-parent-wrap">
						<input class="wtai-input-text-suggested-audiance wtai-max-length-field" type="text" 
						data-maxtext="' . $global_rule_fields['maxCustomAudienceLength'] . '" 
						maxlength="' . $global_rule_fields['maxCustomAudienceLength'] . '" 
						data-type="' . wtai_get_current_page_type() . '" 
						placeholder="' . __( 'Write your specific target market here', 'writetext-ai' ) . '..."  /> ' . $premium_badge_audience_text .
						'<div class="wtai-char-count-wrap"><span class="wtai-char-count">0</span>/<span class="wtai-max-count">' . $global_rule_fields['maxCustomAudienceLength'] . '</span> ' . __( ' Char', 'writetext-ai' ) . '</div>
						</div>';
					$html .= '<span class="wtai-product-label-text"><span class="wtai-product-label-text-display" >' . __( 'Target market (optional)', 'writetext-ai' ) . '</span>' . $premium_badge_audience . '</span>';
					$html .= '<span class="wtai-regenerate-audience-wrapper"><a class="wtai-regenerate-audience" href="#">' . __( 'Regenerate', 'writetext-ai' ) . '</a></span>';
					$html .= '<div class="wtai-suggested-audience-list-wrap" ><ul class="wtai-post-data suggested_audience-list" data-postfield="suggested_audience">';
					$html .= '<li><span class="typing-cursor">&nbsp;</span></li>';
					$html .= '</ul></div>';
				$html     .= '</div>';
			}
			$html .= '</div>';
			if ( 'form_style_tone' === $form ) {
				$highlight_pronoun_html = '';
				if ( wtai_is_formal_informal_lang_supported() ) {
					ob_start();
					?>
					<div class="wtai-highlight-incorrect-pronouns-wrap" >
						<label>
							<input type="checkbox" id="wtai-highlight-incorrect-pronouns-cb" name="wtai-highlight-incorrect-pronouns-cb" class="wtai-highlight-incorrect-pronouns-cb" value="1" />
							<span><?php echo wp_kses( __( 'Highlight potentially incorrect pronouns', 'writetext-ai' ), 'post' ); ?></span>
						</label>
					</div>
					<?php
					$highlight_pronoun_html = ob_get_clean();
				}

				$html .= '<div class="wtai-product-cta-item">';
				$html .= '<div class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></div>';
				$html .= '<div class="wtai-language-formal-field wtai-post-data" data-postfield="language_formal_field">' . $highlight_pronoun_html . '</div>';

				$html .= '</div>';
			}
		}

		$html .= '</div>';
		return $html;
	}

	/**
	 * Product text tones.
	 *
	 * @param string $product_id Product ID.
	 * @param string $form Form.
	 * @param array  $fields Text fields.
	 */
	public function get_product_text_tones( $product_id = '', $form = '', $fields = array() ) {
		$texts = array(
			'tones' => __( 'Tones', 'writetext-ai' ),
		);
		$html  = '<div class="wtai-product-container wtai-product-tonestyles-container">';
		if ( $form ) {
			$html .= '<div class="wtai-product-form-container">';
		}
		foreach ( $texts as $type => $label ) {
			if ( ! empty( $fields ) && ! in_array( $type, $fields, true ) ) {
				continue;
			}
			$lists          = apply_filters( 'wtai_generate_text_filters', array(), ucfirst( $type ), '' );
			$product_values = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( $type ) );
			$html          .= '<div class="wtai-product-wrap wtai-product-all-trigger wtai-product-' . $type . '-wrap">';

			$html .= '<div class="wtai-label-select-all-wrap">
						<label for="wtai-select-all-' . $type . '"><input type="checkbox" name="wtai_select_all_' . $type . '" id="wtai-select-all-' . $type . '" class="wtai-product-cb-all" />' . __( 'Select all', 'writetext-ai' ) . '</label>
					</div>';
			if ( 'form_audience' === $form ) {
				$html .= '<span class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></span>';
			}
			foreach ( $lists as $list_key => $list_label ) {
				$selected = '';
				if ( in_array( strtolower( $type ), array( 'tones', 'audiences' ), true ) ) {
					if ( is_array( $product_values ) &&
						! empty( $product_values ) &&
						in_array( $list_key, $product_values, true ) ) {
						$selected = 'checked';
					}
				} else {
					$selected = ( $product_values && $list_key === $product_values ) ? 'checked' : '';
				}

				if ( 'styles' === $type ) {
					$input_type = 'radio';
					$name       = 'wtai_installation_styles';
				} elseif ( 'tones' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_tones[]';
				} elseif ( 'audiences' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_audiences[]';
				}
				$html .= '<div class="wtai-product-item wtai-product-' . $type . '-item">';

				$disallowed_combination_tooltip     = wtai_get_disallowed_combination_tooltip_message( $list_key, $type );
				$disallowed_combination_tooltip_msg = '';
				$has_tooltip_class                  = '';
				if ( $disallowed_combination_tooltip ) {
					$has_tooltip_class                  = 'tooltip-generate-filter has-tooltip';
					$disallowed_combination_tooltip_msg = '<span class="wtai-disallowed-comb-tooltip" >' . implode( '<br>', $disallowed_combination_tooltip ) . '</span>';
				}

				if ( 'radio' === $input_type ) {
					$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><span><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' /></span><span>' . $list_label . '</span></label>';
				} else {
					$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' />' . $list_label . '</label>';
				}
				$html .= '</div>';
			}
			$html .= '</div>';
		}
		if ( $form ) {
			if ( 'form_style_tone' === $form ) {
				$highlight_pronoun_html = '';
				if ( wtai_is_formal_informal_lang_supported() ) {
					ob_start();
					?>
					<div class="wtai-highlight-incorrect-pronouns-wrap" >
						<label>
							<input type="checkbox" id="wtai-highlight-incorrect-pronouns-cb" name="wtai-highlight-incorrect-pronouns-cb" class="wtai-highlight-incorrect-pronouns-cb" value="1" />
							<span><?php echo wp_kses( __( 'Highlight potentially incorrect pronouns', 'writetext-ai' ), 'post' ); ?></span>
						</label>
					</div>
					<?php
					$highlight_pronoun_html = ob_get_clean();
				}

				$html .= '<div class="wtai-product-cta-item">';
				$html .= '<div class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></div>';
				$html .= '<div class="wtai-language-formal-field wtai-post-data" data-postfield="language_formal_field">' . $highlight_pronoun_html . '</div>';

				$html .= '</div>';
			}
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Product text styles.
	 *
	 * @param string $product_id Product ID.
	 * @param string $form Form.
	 * @param array  $fields Text fields.
	 */
	public function get_product_text_styles( $product_id = '', $form = '', $fields = array() ) {
		$texts = array(
			'styles' => 'Styles', // Internal label not used, no need to translate.
		);
		$html  = '<div class="wtai-product-container wtai-product-tonestyles-container">';
		if ( $form ) {
			$html .= '<div class="wtai-product-form-container">';
		}
		foreach ( $texts as $type => $label ) {
			if ( ! empty( $fields ) && ! in_array( $type, $fields, true ) ) {
				continue;
			}
			$lists          = apply_filters( 'wtai_generate_text_filters', array(), ucfirst( $type ), '' );
			$product_values = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( $type ) );
			$html          .= '<div class="wtai-product-wrap wtai-product-' . $type . '-wrap">';

			if ( 'form_audience' === $form ) {
				$html .= '<span class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></span>';
			}
			foreach ( $lists as $list_key => $list_label ) {
				$selected = '';
				if ( in_array( strtolower( $type ), array( 'tones', 'audiences' ), true ) ) {
					if ( is_array( $product_values ) &&
						! empty( $product_values ) &&
						in_array( $list_key, $product_values, true ) ) {
						$selected = 'checked';
					}
				} else {
					$selected = ( $product_values && $list_key === $product_values ) ? 'checked' : '';
				}

				if ( 'styles' === $type ) {
					$input_type = 'radio';
					$name       = 'wtai_installation_styles';
				} elseif ( 'tones' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_tones[]';
				} elseif ( 'audiences' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_audiences[]';
				}

				$disallowed_combination_tooltip     = wtai_get_disallowed_combination_tooltip_message( $list_key, $type );
				$disallowed_combination_tooltip_msg = '';
				$has_tooltip_class                  = '';
				if ( $disallowed_combination_tooltip ) {
					$has_tooltip_class                  = 'tooltip-generate-filter has-tooltip';
					$disallowed_combination_tooltip_msg = '<span class="wtai-disallowed-comb-tooltip" >' . implode( '<br>', $disallowed_combination_tooltip ) . '</span>';
				}

				$html     .= '<div class="wtai-product-item wtai-product-' . $type . '-item">';
					$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' />' . $list_label . '</label>';
				$html     .= '</div>';
			}
			$html .= '</div>';
		}
		if ( $form ) {
			if ( 'form_style_tone' === $form ) {
				$highlight_pronoun_html = '';
				if ( wtai_is_formal_informal_lang_supported() ) {
					ob_start();
					?>
					<div class="wtai-highlight-incorrect-pronouns-wrap" >
						<label>
							<input type="checkbox" id="wtai-highlight-incorrect-pronouns-cb" name="wtai-highlight-incorrect-pronouns-cb" class="wtai-highlight-incorrect-pronouns-cb" value="1" />
							<span><?php echo wp_kses( __( 'Highlight potentially incorrect pronouns', 'writetext-ai' ), 'post' ); ?></span>
						</label>
					</div>
					<?php
					$highlight_pronoun_html = ob_get_clean();
				}

				$html .= '<div class="wtai-product-cta-item">';
				$html .= '<div class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></div>';
				$html .= '<div class="wtai-language-formal-field wtai-post-data" data-postfield="language_formal_field">' . $highlight_pronoun_html . '</div>';
				$html .= '</div>';
			}
		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Translate custom user role names.
	 *
	 * @param string $product_id Product ID.
	 * @param string $form Form.
	 * @param array  $fields Text fields.
	 */
	public function get_product_text_audiences( $product_id = '', $form = '', $fields = array() ) {
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );

		$texts = array(
			'audiences' => __( 'Audience', 'writetext-ai' ),
		);
		$html  = '<div class="wtai-product-container wtai-product-tonestyles-container">';
		if ( $form ) {
			$html .= '<div class="wtai-product-form-container">';
		}
		$trigger_class = 'wtai-product-all-trigger';
		foreach ( $texts as $type => $label ) {
			if ( ! empty( $fields ) && ! in_array( $type, $fields, true ) ) {
				continue;
			}
			$lists          = apply_filters( 'wtai_generate_text_filters', array(), ucfirst( $type ), '' );
			$product_values = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( $type ) );
			if ( 'form_audience' === $form ) {
				$trigger_class = 'product_not_all_trigger';
			}
			$html .= '<div class="wtai-product-wrap ' . $trigger_class . ' wtai-product-' . $type . '-wrap">';

			$html .= '<div class="wtai-label-select-all-wrap">
						<label for="wtai-select-all-' . $type . '"><input type="checkbox" name="wtai_select_all_' . $type . '" id="wtai-select-all-' . $type . '" class="wtai-product-cb-all" />' . __( 'Select all', 'writetext-ai' ) . '</label>
					  </div>';
			if ( 'form_audience' === $form ) {
				$html .= '<span class="wtai-reset-wrapper"><a class="wtai-reset" href="#">' . __( 'Reset', 'writetext-ai' ) . '</a></span>';
			}
			foreach ( $lists as $list_key => $list_label ) {
				$selected = '';
				if ( in_array( strtolower( $type ), array( 'tones', 'audiences' ), true ) ) {
					if ( is_array( $product_values ) &&
						! empty( $product_values ) &&
						in_array( $list_key, $product_values, true ) ) {
						$selected = 'checked';
					}
				} else {
					$selected = ( $product_values && $list_key === $product_values ) ? 'checked' : '';
				}

				if ( 'styles' === $type ) {
					$input_type = 'radio';
					$name       = 'wtai_installation_styles';
				} elseif ( 'tones' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_tones[]';
				} elseif ( 'audiences' === $type ) {
					$input_type = 'checkbox';
					$name       = 'wtai_installation_audiences[]';
				}

				$disallowed_combination_tooltip     = wtai_get_disallowed_combination_tooltip_message( $list_key, $type );
				$disallowed_combination_tooltip_msg = '';
				$has_tooltip_class                  = '';
				if ( $disallowed_combination_tooltip ) {
					$has_tooltip_class                  = 'tooltip-generate-filter has-tooltip';
					$disallowed_combination_tooltip_msg = '<span class="wtai-disallowed-comb-tooltip" >' . implode( '<br>', $disallowed_combination_tooltip ) . '</span>';
				}

				$html     .= '<div class="wtai-product-item wtai-product-' . $type . '-item">';
					$html .= '<label class="' . $has_tooltip_class . '" title="' . esc_attr( $disallowed_combination_tooltip_msg ) . '" ><input type="' . $input_type . '" name="' . $name . '" class="wtai-product-cb wtai-product-' . $type . '-cb" value="' . $list_key . '" ' . $selected . ' />' . $list_label . '</label>';
				$html     .= '</div>';
			}
			$html .= '</div>';
		}
		if ( $form ) {
			if ( 'form_audience' === $form ) {
				$html     .= '<div class="wtai-suggested-audience-container">';
					$html .= '<input class="wtai-input-text-suggested-audiance wtai-max-length-field" type="text" 
								data-maxtext="' . $global_rule_fields['maxCustomAudienceLength'] . '" 
								maxlength="' . $global_rule_fields['maxCustomAudienceLength'] . '" 
								data-type="' . wtai_get_current_page_type() . '" 
								placeholder="' . __( 'Write your specific target market here', 'writetext-ai' ) . '..."  />';
					$html .= '<span class="wtai-product-label-text">' . __( 'Target market (optional)', 'writetext-ai' ) . '</span>';
					$html .= '<span class="wtai-regenerate-audience-wrapper"><a class="wtai-regenerate-audience" href="#">' . __( 'Regenerate', 'writetext-ai' ) . '</a></span>';
					$html .= '<div class="wtai-suggested-audience-list-wrap" ><ul class="wtai-post-data suggested_audience-list" data-postfield="suggested_audience">';
					$html .= '<li><span class="typing-cursor">&nbsp;</span></li>';
					$html .= '</ul></div>';
				$html     .= '</div>';
			}
			$html .= '</div>';

		}
		$html .= '</div>';
		return $html;
	}

	/**
	 * Translate custom user role names.
	 *
	 * @param string $translation Translation text.
	 * @param string $text Original text.
	 * @param int    $context Text context.
	 * @param int    $domain Text domain.
	 */
	public function translate_user_role_names( $translation, $text, $context, $domain ) {
		if ( ( 'writetext-ai' === $domain || 'default' === $domain ) && 'User role' === $context ) {
			// phpcs:ignore WordPress.WP.I18n.LowLevelTranslationFunction
			$translation_of_role = translate(
				// phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
				$text,
				'writetext-ai'
			);
			if ( $translation_of_role ) {
				$translation = $translation_of_role;
			}
		}

		return $translation;
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param mixed  $actions Plugin Action links.
	 * @param string $plugin_file Plugin file.
	 *
	 * @return array
	 */
	public function plugin_action_links( $actions, $plugin_file ) {
		$new_actions = array();

		if ( WTAI_PLUGIN_BASENAME . '/writetext-ai.php' === $plugin_file ) {
			$settings_url = admin_url( 'admin.php?page=write-text-ai' );
			if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && wtai_current_user_can( 'writeai_settings_page' ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
				$settings_url = admin_url( 'admin.php?page=write-text-ai-settings' );
			}

			/* translators: %s: settings url */
			$new_actions['wtai_settings'] = sprintf( __( '<a href="%s">Settings</a>', 'writetext-ai' ), esc_url( $settings_url ) );
		}

		return array_merge( $new_actions, $actions );
	}

	/**
	 * Redirect to admin setup if accesing category and settings page directly.
	 */
	public function admin_maybe_redirect() {
		if ( is_admin() && is_user_logged_in() ) {
			$do_wtai_setup_redirect = false;
			// phpcs:ignore WordPress.Security.NonceVerification
			if ( isset( $_GET['page'] ) && ( 'write-text-ai-category' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] ) ) {
				if ( 5 === intval( get_option( 'wtai_installation_step', 1 ) ) && wtai_current_user_can( 'writeai_generate_text' ) && ! wtai_is_token_expired() && wtai_has_api_base_url() ) {
					$do_wtai_setup_redirect = false;
				} else {
					$do_wtai_setup_redirect = true;
				}

				if ( $do_wtai_setup_redirect ) {
					wp_safe_redirect( admin_url( 'admin.php?page=write-text-ai' ) );
					exit;
				}
			}
		}
	}
}

new WTAI_Init();

require_once WTAI_ABSPATH . 'includes/functions.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-api-services.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-installation.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-product-keyword.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-product-single.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-product-dashboard.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-product-category.php';
require_once WTAI_ABSPATH . 'includes/class-wtai-global-settings.php';


