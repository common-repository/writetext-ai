<?php
/**
 * Product single page class for WTA
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Product single class.
 */
class WTAI_Product_Single extends WTAI_Init {

	/**
	 * Construct.
	 */
	public function __construct() {
		$this->define_vars();
		$this->init_hooks();
	}

	/**
	 * Define variables
	 */
	public function define_vars() {
	}

	/**
	 * Init hooks
	 */
	public function init_hooks() {
		add_action( 'wtai_product_single_main_metabox', array( $this, 'get_keyword_list' ) );
		add_action( 'wtai_product_single_main_metabox', array( $this, 'get_filter_all_list' ) );

		add_action( 'wtai_product_single_main_footer', array( $this, 'get_popin_history' ) );
		add_action( 'wtai_product_single_main_footer', array( $this, 'get_popin_keyword' ) );

		add_filter( 'wtai_product_single_formal_language', array( $this, 'get_formal_language' ), 10, 2 );

		add_action( 'wtai_product_single_premium_badge', array( $this, 'get_premium_badge' ), 10, 1 );
		add_action( 'wtai_ads_placeholder', array( $this, 'get_ads_placeholder' ), 10, 1 );
	}

	/**
	 * Get formal language
	 *
	 * @param string $filter_field filter field.
	 * @param string $locales locales.
	 */
	public function get_formal_language( $filter_field, $locales ) {
		$locales                  = explode( '-', $locales );
		$locales                  = reset( $locales );
		$locales                  = strtolower( $locales );
		$format_language_supports = apply_filters( 'wtai_generate_text_filters', array(), 'FormalLanguageSupport' );
		if ( in_array( $locales, $format_language_supports, true ) ) {
			$formal_languages = apply_filters( 'wtai_generate_text_filters', array(), 'FormalLanguages' );
			$i                = 1;
			foreach ( $formal_languages as $formal_language_id => $formal_language_value ) {
				$selected      = $formal_language_value['default'] ? 'checked' : '';
				$filter_field .= '<span class="lang-col-' . $i . '"><input type="radio" class="formal_language_selection" name="FormalLanguage" value="' . $formal_language_id . '" ' . $selected . ' /> <span class="format_language_label">' . $formal_language_value['name'] . '</span></span>';
				++$i;
			}
		}
		return $filter_field;
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

		if ( isset( $tones_user_pref ) && '' !== $tones_user_pref[0] ) {
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

		include_once WTAI_ABSPATH . 'templates/admin/metabox/filter.php';
	}

	/**
	 * Get popin history
	 */
	public function get_popin_history() {
		include_once WTAI_ABSPATH . 'templates/admin/metabox/popin-history.php';
	}

	/**
	 * Get popin keyword
	 */
	public function get_popin_keyword() {
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
		$max_keywords       = isset( $global_rule_fields['maxKeywords'] ) ? $global_rule_fields['maxKeywords'] : WTAI_MAX_KEYWORD;

		include_once WTAI_ABSPATH . 'templates/admin/metabox/popin-keyword.php';
	}

	/**
	 * Get premium badge
	 *
	 * @param string $custom_class Custom class.
	 */
	public function get_premium_badge( $custom_class = '' ) {
		include WTAI_ABSPATH . 'templates/admin/metabox/premium.php';
	}

	/**
	 * Get premium badge
	 *
	 * @param string $custom_class Custom class.
	 */
	public function get_ads_placeholder( $custom_class = '' ) {
		include WTAI_ABSPATH . 'templates/admin/metabox/ads.php';
	}
}

new WTAI_Product_Single();
