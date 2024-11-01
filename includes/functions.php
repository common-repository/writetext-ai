<?php
/**
 * Helper function for WTAI
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Sort data by index.
 *
 * @param array  $data_array Array to sort.
 * @param string $field Field to sort.
 * @param string $direction Direction to sort.
 * @param string $numeric_value Numeric value.
 */
function wtai_sort_by_index( $data_array, $field, $direction = 'asc', $numeric_value = '' ) {

	usort(
		$data_array,
		function ( $a, $b ) use ( $field, $direction, $numeric_value ) {
			if ( $numeric_value ) {
				$a = is_numeric( $a[ $field ] ) ? $a[ $field ] : 0;
				$a = is_numeric( $b[ $field ] ) ? $b[ $field ] : 0;
			} else {
				$a = $a[ $field ];
				$b = $b[ $field ];
			}

			if ( $a === $b ) {
				return 0;
			}
			if ( 'desc' === $direction ) {
				return ( $a > $b ) ? -1 : 1;
			} else {
				return ( $a < $b ) ? -1 : 1;
			}
		}
	);

	return $data_array;
}

/**
 * Function to get product attribute.
 *
 * @param int $product_id Product ID.
 */
function wtai_get_product_attr( $product_id = null ) {

	if ( null !== $product_id ) {
		$product = wc_get_product( $product_id );

		if ( $product ) {
			$product_attr = array(
				'Price'        => wc_price( $product->get_price() ),
				'Stock Status' => $product->get_stock_status(),
				'Weight'       => $product->get_weight(),
				'attributes'   => array(),
			);

			foreach ( $product->get_attributes() as $key => $atrribute ) {
				$attr_name  = $atrribute->get_name();
				$attr_value = str_replace( ', ', '|', $product->get_attribute( $key ) );
				if ( 0 !== $atrribute->get_id() ) {
					$attr_name  = wc_attribute_label( $key );
					$attr_value = $product->get_attribute( $key );
					$attr_value = str_replace( ', ', '|', $attr_value );
				}
				if ( strpos( $attr_value, '|' ) !== false ) {
					$attr_value = explode( '|', $attr_value );
					$attr_value = array_map( 'trim', $attr_value );
				}

				$product_attr['attributes'][ $key ] = array(
					'id'      => $atrribute->get_id(),
					'name'    => $attr_name,
					'options' => $attr_value,
				);
			}
		}
	} else {
		$product_attr = array(
			'Price'        => __( 'Price', 'woocommerce' ),
			'Stock Status' => __( 'Stock status', 'woocommerce' ),
			'Weight'       => __( 'Weight', 'woocommerce' ),
			'attributes'   => array(),
		);

		$wc_attr      = wc_get_attribute_taxonomies();
		$wc_prod_attr = array();
		if ( ! empty( $wc_attr ) ) {
			foreach ( $wc_attr as $key => $value ) {
				$wc_prod_attr[ 'attr-' . $value->attribute_name ] = $value->attribute_label;
			}
		}
		$attr_list = array();
		$attr      = get_option( 'wtai_transient_wc_custom_pa', array() );
		if ( ! empty( $attr ) ) {
			// get custom pa.
			array_walk_recursive(
				$attr,
				function ( $val ) use ( &$attr_list ) {
					$key_attr                         = strtolower( $val );
					$attr_list[ 'attr-' . $key_attr ] = $val;
				}
			);
		}
		if ( ! empty( $attr_list ) ) {
			$wc_prod_attr = array_merge( $wc_prod_attr, $attr_list );
		}

		$product_attr['attributes'] = $wc_prod_attr;
	}

	return $product_attr;
}

/**
 * Get post meta data.
 *
 * @param int    $post_id Post ID.
 * @param string $column_label Column label.
 * @param bool   $single Single.
 */
function wtai_get_post_meta( $post_id, $column_label, $single = true ) {
	$result = '';
	if ( $single && $column_label ) {
		$result = get_post_meta( $post_id, $column_label, $single );
	}
	return $result;
}

/**
 * Get field template for the product edit page.
 *
 * @param int    $post_id Post ID.
 * @param string $column_key Column key.
 * @param string $column_label Column label.
 * @param string $field_value Field value.
 * @param string $field_id Field ID.
 * @param int    $max_length Max length.
 */
function wtai_get_field_template( $post_id, $column_key, $column_label = '', $field_value = '', $field_id = '', $max_length = 0 ) {
	$html             = '';
	$max_length_count = $max_length;
	$max_length       = ( $max_length ) ? 'maxlength="' . $max_length . '"' : '';
	$field_value      = str_replace( '\\', '', $field_value );
	switch ( $column_key ) {
		case 'page_title':
		case 'page_description':
		case 'open_graph':
			if ( $column_label ) {
				/* translators: %s: column label */
				$html .= '<label for="' . $column_key . '"><span class="wtai-field-name wtai-has-popup">' . __( 'WriteText.ai', 'writetext-ai' ) . ' ' . $column_label . '</span> <span class="wtai-generated-status-label" ></span><span class="wtai-transferred-status-label">' . __( 'Not transferred', 'writetext-ai' ) . '</span></label>';
			}
			$html .= '<input type="hidden" class="wtai-api-data-text-id wtai-api-data-' . $column_key . '_id" data-postfield="' . $column_key . '_id" name="' . $column_key . '_id" id="wtai-wp-field-input-' . $column_key . '_id" />';
			$html .= '<div class="wtai-generate-textarea-wrap" >
						<textarea name="' . $column_key . '" id="wtai-wp-field-input-' . $column_key . '" class="wtai-wp-editor-setup-others wtai-api-data-' . $column_key . ' string-count-input wp_editor_trigger" data-postfield="' . $column_key . '"  style="resize:none;" ' . $max_length . ' disabled ></textarea>
						<div class="wtai-generate-textarea-highlight-wrap-2"><textarea name="' . $column_key . '_cloned" id="wtai-wp-field-input-' . $column_key . '_cloned" class="wtai-wp-editor-setup-others-cloned wtai-wp-editor-cloned wtai-api-data-cloned' . $column_key . ' string-count-input wp_editor_trigger_cloned" data-postfield="' . $column_key . '"  style="resize:none;" ' . $max_length . ' disabled ></textarea></div>
						<div class="wtai-generate-textarea-highlight-wrap" ></div>
						<div class="wtai-generate-disable-overlay-wrap" title="' . __( 'No text generated yet.', 'writetext-ai' ) . '" ></div>
					</div>';
			break;
		case 'product_description':
		case 'product_excerpt':
		case 'category_description':
			if ( $column_label ) {
				/* translators: %s: column label */
				$html .= '<label for="' . $column_key . '">' . __( 'WriteText.ai', 'writetext-ai' ) . ' ' . $column_label . ' <span class="wtai-generated-status-label" ></span> <span class="wtai-transferred-status-label" >' . __( 'Not transferred', 'writetext-ai' ) . '</span></label>';
			}
			$html .= '<input type="hidden" value="' . $field_id . '" name="' . $column_key . '_id" id="wtai-wp-field-input-' . $column_key . '_id" class="wtai-api-data-text-id wtai-api-data-' . $column_key . '_id" />';
			$html .= '<div class="wtai-generate-textarea-wrap" >
						<textarea name="' . $column_key . '" id="wtai-wp-field-input-' . $column_key . '" style="resize:none;" ' . $max_length . ' class="wtai-wp-editor-setup wtai-api-data-' . $column_key . ' wp_editor_trigger" data-postfield="' . $column_key . '"  ></textarea>
						
						<div class="wtai-generate-textarea-highlight-wrap-2"><textarea name="' . $column_key . '_cloned" id="wtai-wp-field-input-' . $column_key . '_cloned" style="resize:none;" ' . $max_length . ' class="wtai-wp-editor-setup-cloned wtai-wp-editor-cloned wtai-api-data-cloned-' . $column_key . ' wp_editor_trigger_cloned" data-postfield="' . $column_key . '"  ></textarea></div>
						<div class="wtai-generate-textarea-highlight-wrap wtai-has-tinymce-formatter" ></div>
						<div class="wtai-generate-disable-overlay-wrap wtai-has-tinymce-formatter" title="' . __( 'No text generated yet.', 'writetext-ai' ) . '" ></div>
					</div>';
			break;
		case 'otherproductdetails':
			if ( $column_label ) {
				$html .= '<label class="wtai-char-count-parent-wrap" for="' . $column_key . '">' . $column_label . '</label>';
			}
			$html .= '<textarea disabled name="' . $column_key . '" id="wtai-wp-field-input-' . $column_key . '" class="wtai-post-data wtai-max-length-field" 
				data-postfield="otherproductdetails" style="resize:none;" ' . $max_length . ' ></textarea>
				<div class="wtai-char-count-wrap"><span class="wtai-char-count">0</span>/<span class="wtai-max-count">' . $max_length_count . '</span> ' . __( ' Char', 'writetext-ai' ) . '</div>
				';
			break;
		default:
			// Nothing goes here.
			break;
	}

	return apply_filters( 'render_template_offline_field', $html, $post_id, $column_key, $column_label );
}

/**
 * Get field template for the current WTA value.
 *
 * @param int    $post_id    The post ID.
 * @param string $column_key The column key.
 * @param string $column_label The column label.
 * @param string $field_value The field value.
 * @param string $field_id The field ID.
 * @param int    $max_length The max length.
 */
function wtai_get_field_template_current( $post_id, $column_key, $column_label = '', $field_value = '', $field_id = '', $max_length = 0 ) {
	$html        = '';
	$max_length  = ( $max_length ) ? 'maxlength="' . $max_length . '"' : '';
	$field_value = str_replace( '\\', '', $field_value );

	$product_status = strtolower( wtai_get_product_wp_status( $post_id ) );

	$product_status_display = '';
	if ( '' !== $product_status ) {
		$product_status_display = '(' . $product_status . ')';
	}

	/* translators: %s: column label */
	$html = '<label>' . __( 'WordPress', 'writetext-ai' ) . ' ' . $column_label . ' <em class="wtai-field-product-status" >' . $product_status_display . '</em></label>
			<div class="wtai-current-text">
				<div class="wtai-current-value">
					<p class="wtai-api-data-' . $column_key . '_value wtai-text-message" ></p>
				</div>
			</div>
			<div class="wtai-static-count-display" >
				<span class="wtai-char-count">0</span>' . __( ' Char', 'writetext-ai' ) . ' | <span class="word-count" >0</span>' . __( ' word/s', 'writetext-ai' ) . '
			</div>
			';

	return apply_filters( 'render_template_offline_field', $html, $post_id, $column_key, $column_label );
}

/**
 * Get meta values.
 *
 * @param int   $post_id The post ID.
 * @param array $fields The fields.
 */
function wtai_get_meta_values( $post_id, $fields = array() ) {
	$results = array();
	if ( is_array( $fields ) && ! empty( $fields ) ) {
		foreach ( $fields as $meta_key ) {
			$meta_key = trim( $meta_key );
			switch ( $meta_key ) {
				case 'page_title':
					$key                  = wtai_get_meta_key_source( 'title' );
					$text                 = wtai_yoast_seo_format_value( $post_id, $key );
					$results[ $meta_key ] = wtai_clean_up_html_string( $text );
					break;
				case 'page_description':
					$key                  = wtai_get_meta_key_source( 'desc' );
					$text                 = wtai_yoast_seo_format_value( $post_id, $key );
					$results[ $meta_key ] = wtai_clean_up_html_string( $text );
					break;
				case 'open_graph':
					$key                  = wtai_get_meta_key_source( 'opengraph' );
					$text                 = wtai_yoast_seo_format_value( $post_id, $key );
					$results[ $meta_key ] = wtai_clean_up_html_string( $text );
					break;
				case 'product_excerpt':
					$product = wc_get_product( $post_id );
					if ( $product ) {
						$results[ $meta_key ] = wtai_clean_up_html_string( $product->get_short_description() );
					} else {
						$results[ $meta_key ] = '';
					}
					break;
				case 'product_description':
					$product = wc_get_product( $post_id );
					if ( $product ) {
						$results[ $meta_key ] = wtai_clean_up_html_string( $product->get_description() );
					} else {
						$results[ $meta_key ] = '';
					}
					break;
				default:
					$results[ $meta_key ] = get_post_meta( $post_id, $meta_key, true );
					break;
			}
		}
	}

	return $results;
}

/**
 * Get yoast SEO format value.
 *
 * @param int    $post_id The post ID.
 * @param string $meta_key The meta key.
 */
function wtai_yoast_seo_format_value( $post_id, $meta_key ) {
	switch ( $meta_key ) {
		case 'product_description':
			$text = get_the_content( null, false, $post_id );
			break;
		case 'product_excerpt':
			$text = get_the_excerpt( $post_id );
			break;
		default:
			$text = get_post_meta( $post_id, $meta_key, true );
			break;
	}

	if ( function_exists( 'wpseo_replace_vars' ) ) {
		$args = get_post( $post_id, ARRAY_A );
		$text = wpseo_replace_vars( $text, $args );
	}
	return $text;
}

/**
 * Get meta key source.
 *
 * @param string $size The size.
 */
function wtai_get_meta_key_source( $size = '' ) {
	$source = get_option( 'wtai_installation_source', '' );

	$seos = array(
		'wordpress-seo-premium'   => array(
			'desc'             => '_yoast_wpseo_metadesc',
			'opengraph'        => '_yoast_wpseo_opengraph-description',
			'opengraphtwitter' => '_yoast_wpseo_twitter-description',
			'opengraphtitle'   => '_yoast_wpseo_opengraph-title',
			'twittertitle'     => '_yoast_wpseo_twitter-title',
			'keyword'          => '_yoast_wpseo_focuskw',
			'title'            => '_yoast_wpseo_title',
		),
		'wordpress-seo'           => array(
			'desc'             => '_yoast_wpseo_metadesc',
			'opengraph'        => '_yoast_wpseo_opengraph-description',
			'opengraphtwitter' => '_yoast_wpseo_twitter-description',
			'opengraphtitle'   => '_yoast_wpseo_opengraph-title',
			'twittertitle'     => '_yoast_wpseo_twitter-title',
			'keyword'          => '_yoast_wpseo_focuskw',
			'title'            => '_yoast_wpseo_title',
		),
		'all-in-one-seo-pack'     => array(
			'desc'             => '_aioseo_description',
			'opengraph'        => '_aioseo_og_description',
			'opengraphtwitter' => '_aioseo_twitter_description',
			'opengraphtitle'   => '_aioseo_og_title',
			'twittertitle'     => '_aioseo_twitter_title',
			'keyword'          => '_aioseo_keywords',
			'title'            => '_aioseo_title',
		),
		'all-in-one-seo-pack-pro' => array(
			'desc'             => '_aioseo_description',
			'opengraph'        => '_aioseo_og_description',
			'opengraphtwitter' => '_aioseo_twitter_description',
			'opengraphtitle'   => '_aioseo_og_title',
			'twittertitle'     => '_aioseo_twitter_title',
			'keyword'          => '_aioseo_keywords',
			'title'            => '_aioseo_title',
		),
		'seo-by-rank-math'        => array(
			'desc'             => 'rank_math_description',
			'opengraph'        => 'rank_math_facebook_description',
			'opengraphtwitter' => 'rank_math_twitter_description',
			'opengraphtitle'   => 'rank_math_facebook_title',
			'twittertitle'     => 'rank_math_twitter_title',
			'keyword'          => 'rank_math_focus_keyword',
			'title'            => 'rank_math_title',
		),
		'seo-by-rank-math-pro'    => array(
			'desc'             => 'rank_math_description',
			'opengraph'        => 'rank_math_facebook_description',
			'opengraphtwitter' => 'rank_math_twitter_description',
			'opengraphtitle'   => 'rank_math_facebook_title',
			'twittertitle'     => 'rank_math_twitter_title',
			'keyword'          => 'rank_math_focus_keyword',
			'title'            => 'rank_math_title',
		),
	);
	return isset( $seos[ $source ][ $size ] ) ? $seos[ $source ][ $size ] : '';
}

/**
 * Save field value.
 *
 * @param int    $product_id The product ID.
 * @param string $field_key The field key.
 * @param string $field_value The field value.
 */
function wtai_save_on_the_field( $product_id, $field_key, $field_value ) {
	switch ( $field_key ) {
		case 'page_title':
			$key = wtai_get_meta_key_source( 'title' );
			update_post_meta( $product_id, $key, $field_value );
			$key = wtai_get_meta_key_source( 'opengraphtitle' );
			update_post_meta( $product_id, $key, $field_value );
			$source       = get_option( 'wtai_installation_source', '' );
			$save_twitter = true;
			if ( 'seo-by-rank-math' === $source ) {
				$mrtuf = get_post_meta( $product_id, 'rank_math_twitter_use_facebook', true );
				if ( $mrtuf && 'off' === $mrtuf ) {
					$save_twitter = false;
				}
			}
			if ( $save_twitter ) {
				$key = wtai_get_meta_key_source( 'twittertitle' );
				update_post_meta( $product_id, $key, $field_value );
			}

			break;
		case 'page_description':
			$key = wtai_get_meta_key_source( 'desc' );
			update_post_meta( $product_id, $key, $field_value );
			break;
		case 'open_graph':
			$key = wtai_get_meta_key_source( 'opengraph' );
			update_post_meta( $product_id, $key, $field_value );
			$source       = get_option( 'wtai_installation_source', '' );
			$save_twitter = true;
			if ( 'seo-by-rank-math' === $source ) {
				$mrtuf = get_post_meta( $product_id, 'rank_math_twitter_use_facebook', true );
				if ( $mrtuf && 'off' === $mrtuf ) {
					$save_twitter = false;
				}
			}
			if ( $save_twitter ) {
				$key = wtai_get_meta_key_source( 'opengraphtwitter' );
				update_post_meta( $product_id, $key, $field_value );
			}
			break;
		case 'product_description':
			wp_update_post(
				array(
					'ID'           => $product_id,
					'post_content' => $field_value,
				)
			);
			$product_contents['post_content'] = $field_value;
			break;
		case 'product_excerpt':
			wp_update_post(
				array(
					'ID'           => $product_id,
					'post_excerpt' => $field_value,
				)
			);
			break;
	}
	return false;
}

/**
 * Get country by code.
 *
 * @param string $code The country code.
 */
function wtai_get_country_by_code( $code = '' ) {
	$site_languages = array(
		'bs'          => 'Bosnian',
		'ee_TG'       => 'Ewe (Togo)',
		'ms'          => 'Malay',
		'kam_KE'      => 'Kamba (Kenya)',
		'mt'          => 'Maltese',
		'ha'          => 'Hausa',
		'es_HN'       => 'Spanish (Honduras)',
		'ml_IN'       => 'Malayalam (India)',
		'ro_MD'       => 'Romanian (Moldova)',
		'kab_DZ'      => 'Kabyle (Algeria)',
		'he'          => 'Hebrew',
		'es_CO'       => 'Spanish (Colombia)',
		'my'          => 'Burmese',
		'es_PA'       => 'Spanish (Panama)',
		'az_Latn'     => 'Azerbaijani (Latin)',
		'mer'         => 'Meru',
		'en_NZ'       => 'English (New Zealand)',
		'xog_UG'      => 'Soga (Uganda)',
		'sg'          => 'Sango',
		'fr_GP'       => 'French (Guadeloupe)',
		'sr_Cyrl_BA'  => 'Serbian (Cyrillic, Bosnia and Herzegovina)',
		'hi'          => 'Hindi',
		'fil_PH'      => 'Filipino (Philippines)',
		'lt_LT'       => 'Lithuanian (Lithuania)',
		'si'          => 'Sinhala',
		'en_MT'       => 'English (Malta)',
		'si_LK'       => 'Sinhala (Sri Lanka)',
		'luo_KE'      => 'Luo (Kenya)',
		'it_CH'       => 'Italian (Switzerland)',
		'teo'         => 'Teso',
		'mfe'         => 'Morisyen',
		'sk'          => 'Slovak',
		'uz_Cyrl_UZ'  => 'Uzbek (Cyrillic, Uzbekistan)',
		'sl'          => 'Slovenian',
		'rm_CH'       => 'Romansh (Switzerland)',
		'az_Cyrl_AZ'  => 'Azerbaijani (Cyrillic, Azerbaijan)',
		'fr_GQ'       => 'French (Equatorial Guinea)',
		'kde'         => 'Makonde',
		'sn'          => 'Shona',
		'cgg_UG'      => 'Chiga (Uganda)',
		'so'          => 'Somali',
		'fr_RW'       => 'French (Rwanda)',
		'es_SV'       => 'Spanish (El Salvador)',
		'mas_TZ'      => 'Masai (Tanzania)',
		'en_MU'       => 'English (Mauritius)',
		'sq'          => 'Albanian',
		'hr'          => 'Croatian',
		'sr'          => 'Serbian',
		'en_PH'       => 'English (Philippines)',
		'ca'          => 'Catalan',
		'hu'          => 'Hungarian',
		'mk_MK'       => 'Macedonian (Macedonia)',
		'fr_TD'       => 'French (Chad)',
		'nb'          => 'Norwegian Bokmål',
		'sv'          => 'Swedish',
		'kln_KE'      => 'Kalenjin (Kenya)',
		'sw'          => 'Swahili',
		'nd'          => 'North Ndebele',
		'sr_Latn'     => 'Serbian (Latin)',
		'el_GR'       => 'Greek (Greece)',
		'hy'          => 'Armenian',
		'ne'          => 'Nepali',
		'el_CY'       => 'Greek (Cyprus)',
		'es_CR'       => 'Spanish (Costa Rica)',
		'fo_FO'       => 'Faroese (Faroe Islands)',
		'pa_Arab_PK'  => 'Punjabi (Arabic, Pakistan)',
		'seh'         => 'Sena',
		'ar_YE'       => 'Arabic (Yemen)',
		'ja_JP'       => 'Japanese (Japan)',
		'ur_PK'       => 'Urdu (Pakistan)',
		'pa_Guru'     => 'Punjabi (Gurmukhi)',
		'gl_ES'       => 'Galician (Spain)',
		'zh_Hant_HK'  => 'Chinese (Traditional Han, Hong Kong SAR China)',
		'ar_EG'       => 'Arabic (Egypt)',
		'nl'          => 'Dutch',
		'th_TH'       => 'Thai (Thailand)',
		'es_PE'       => 'Spanish (Peru)',
		'fr_KM'       => 'French (Comoros)',
		'nn'          => 'Norwegian Nynorsk',
		'kk_Cyrl_KZ'  => 'Kazakh (Cyrillic, Kazakhstan)',
		'kea'         => 'Kabuverdianu',
		'lv_LV'       => 'Latvian (Latvia)',
		'kln'         => 'Kalenjin',
		'tzm_Latn'    => 'Central Morocco Tamazight (Latin)',
		'yo'          => 'Yoruba',
		'gsw_CH'      => 'Swiss German (Switzerland)',
		'ha_Latn_GH'  => 'Hausa (Latin, Ghana)',
		'is_IS'       => 'Icelandic (Iceland)',
		'pt_BR'       => 'Portuguese (Brazil)',
		'cs'          => 'Czech',
		'en_PK'       => 'English (Pakistan)',
		'fa_IR'       => 'Persian (Iran)',
		'zh_Hans_SG'  => 'Chinese (Simplified Han, Singapore)',
		'luo'         => 'Luo',
		'ta'          => 'Tamil',
		'fr_TG'       => 'French (Togo)',
		'kde_TZ'      => 'Makonde (Tanzania)',
		'mr_IN'       => 'Marathi (India)',
		'ar_SA'       => 'Arabic (Saudi Arabia)',
		'ka_GE'       => 'Georgian (Georgia)',
		'mfe_MU'      => 'Morisyen (Mauritius)',
		'id'          => 'Indonesian',
		'fr_LU'       => 'French (Luxembourg)',
		'de_LU'       => 'German (Luxembourg)',
		'ru_MD'       => 'Russian (Moldova)',
		'cy'          => 'Welsh',
		'zh_Hans_HK'  => 'Chinese (Simplified Han, Hong Kong SAR China)',
		'te'          => 'Telugu',
		'bg_BG'       => 'Bulgarian (Bulgaria)',
		'shi_Latn'    => 'Tachelhit (Latin)',
		'ig'          => 'Igbo',
		'ses'         => 'Koyraboro Senni',
		'ii'          => 'Sichuan Yi',
		'es_BO'       => 'Spanish (Bolivia)',
		'th'          => 'Thai',
		'ko_KR'       => 'Korean (South Korea)',
		'ti'          => 'Tigrinya',
		'it_IT'       => 'Italian (Italy)',
		'shi_Latn_MA' => 'Tachelhit (Latin, Morocco)',
		'pt_MZ'       => 'Portuguese (Mozambique)',
		'ff_SN'       => 'Fulah (Senegal)',
		'haw'         => 'Hawaiian',
		'zh_Hans'     => 'Chinese (Simplified Han)',
		'so_KE'       => 'Somali (Kenya)',
		'bn_IN'       => 'Bengali (India)',
		'en_UM'       => 'English (U.S. Minor Outlying Islands)',
		'to'          => 'Tonga',
		'id_ID'       => 'Indonesian (Indonesia)',
		'uz_Cyrl'     => 'Uzbek (Cyrillic)',
		'en_GU'       => 'English (Guam)',
		'es_EC'       => 'Spanish (Ecuador)',
		'en_US_POSIX' => 'English (United States, Computer)',
		'sr_Latn_BA'  => 'Serbian (Latin, Bosnia and Herzegovina)',
		'is'          => 'Icelandic',
		'luy'         => 'Luyia',
		'tr'          => 'Turkish',
		'en_NA'       => 'English (Namibia)',
		'it'          => 'Italian',
		'da'          => 'Danish',
		'bo_IN'       => 'Tibetan (India)',
		'vun_TZ'      => 'Vunjo (Tanzania)',
		'ar_SD'       => 'Arabic (Sudan)',
		'uz_Latn_UZ'  => 'Uzbek (Latin, Uzbekistan)',
		'az_Latn_AZ'  => 'Azerbaijani (Latin, Azerbaijan)',
		'de'          => 'German',
		'es_GQ'       => 'Spanish (Equatorial Guinea)',
		'ta_IN'       => 'Tamil (India)',
		'de_DE'       => 'German (Germany)',
		'fr_FR'       => 'French (France)',
		'rof_TZ'      => 'Rombo (Tanzania)',
		'ar_LY'       => 'Arabic (Libya)',
		'en_BW'       => 'English (Botswana)',
		'asa'         => 'Asu',
		'zh'          => 'Chinese',
		'ha_Latn'     => 'Hausa (Latin)',
		'fr_NE'       => 'French (Niger)',
		'es_MX'       => 'Spanish (Mexico)',
		'bem_ZM'      => 'Bemba (Zambia)',
		'zh_Hans_CN'  => 'Chinese (Simplified Han, China)',
		'bn_BD'       => 'Bengali (Bangladesh)',
		'pt_GW'       => 'Portuguese (Guinea-Bissau)',
		'om'          => 'Oromo',
		'jmc'         => 'Machame',
		'de_AT'       => 'German (Austria)',
		'kk_Cyrl'     => 'Kazakh (Cyrillic)',
		'sw_TZ'       => 'Swahili (Tanzania)',
		'ar_OM'       => 'Arabic (Oman)',
		'et_EE'       => 'Estonian (Estonia)',
		'or'          => 'Oriya',
		'da_DK'       => 'Danish (Denmark)',
		'ro_RO'       => 'Romanian (Romania)',
		'zh_Hant'     => 'Chinese (Traditional Han)',
		'bm_ML'       => 'Bambara (Mali)',
		'ja'          => 'Japanese',
		'fr_CA'       => 'French (Canada)',
		'naq'         => 'Nama',
		'zu'          => 'Zulu',
		'en_IE'       => 'English (Ireland)',
		'ar_MA'       => 'Arabic (Morocco)',
		'es_GT'       => 'Spanish (Guatemala)',
		'uz_Arab_AF'  => 'Uzbek (Arabic, Afghanistan)',
		'en_AS'       => 'English (American Samoa)',
		'bs_BA'       => 'Bosnian (Bosnia and Herzegovina)',
		'am_ET'       => 'Amharic (Ethiopia)',
		'ar_TN'       => 'Arabic (Tunisia)',
		'haw_US'      => 'Hawaiian (United States)',
		'ar_JO'       => 'Arabic (Jordan)',
		'fa_AF'       => 'Persian (Afghanistan)',
		'uz_Latn'     => 'Uzbek (Latin)',
		'en_BZ'       => 'English (Belize)',
		'nyn_UG'      => 'Nyankole (Uganda)',
		'ebu_KE'      => 'Embu (Kenya)',
		'te_IN'       => 'Telugu (India)',
		'cy_GB'       => 'Welsh (United Kingdom)',
		'uk'          => 'Ukrainian',
		'nyn'         => 'Nyankole',
		'en_JM'       => 'English (Jamaica)',
		'en_US'       => 'English (United States)',
		'fil'         => 'Filipino',
		'ar_KW'       => 'Arabic (Kuwait)',
		'af_ZA'       => 'Afrikaans (South Africa)',
		'en_CA'       => 'English (Canada)',
		'fr_DJ'       => 'French (Djibouti)',
		'ti_ER'       => 'Tigrinya (Eritrea)',
		'ig_NG'       => 'Igbo (Nigeria)',
		'en_AU'       => 'English (Australia)',
		'ur'          => 'Urdu',
		'fr_MC'       => 'French (Monaco)',
		'pt_PT'       => 'Portuguese (Portugal)',
		'pa'          => 'Punjabi',
		'es_419'      => 'Spanish (Latin America)',
		'fr_CD'       => 'French (Congo - Kinshasa)',
		'en_SG'       => 'English (Singapore)',
		'bo_CN'       => 'Tibetan (China)',
		'kn_IN'       => 'Kannada (India)',
		'sr_Cyrl_RS'  => 'Serbian (Cyrillic, Serbia)',
		'lg_UG'       => 'Ganda (Uganda)',
		'gu_IN'       => 'Gujarati (India)',
		'ee'          => 'Ewe',
		'nd_ZW'       => 'North Ndebele (Zimbabwe)',
		'bem'         => 'Bemba',
		'uz'          => 'Uzbek',
		'sw_KE'       => 'Swahili (Kenya)',
		'sq_AL'       => 'Albanian (Albania)',
		'hr_HR'       => 'Croatian (Croatia)',
		'mas_KE'      => 'Masai (Kenya)',
		'el'          => 'Greek',
		'ti_ET'       => 'Tigrinya (Ethiopia)',
		'es_AR'       => 'Spanish (Argentina)',
		'pl'          => 'Polish',
		'en'          => 'English',
		'eo'          => 'Esperanto',
		'shi'         => 'Tachelhit',
		'kok'         => 'Konkani',
		'fr_CF'       => 'French (Central African Republic)',
		'fr_RE'       => 'French (Réunion)',
		'mas'         => 'Masai',
		'rof'         => 'Rombo',
		'ru_UA'       => 'Russian (Ukraine)',
		'yo_NG'       => 'Yoruba (Nigeria)',
		'dav_KE'      => 'Taita (Kenya)',
		'gv_GB'       => 'Manx (United Kingdom)',
		'pa_Arab'     => 'Punjabi (Arabic)',
		'es'          => 'Spanish',
		'teo_UG'      => 'Teso (Uganda)',
		'ps'          => 'Pashto',
		'es_PR'       => 'Spanish (Puerto Rico)',
		'fr_MF'       => 'French (Saint Martin)',
		'et'          => 'Estonian',
		'pt'          => 'Portuguese',
		'eu'          => 'Basque',
		'ka'          => 'Georgian',
		'rwk_TZ'      => 'Rwa (Tanzania)',
		'nb_NO'       => 'Norwegian Bokmål (Norway)',
		'fr_CG'       => 'French (Congo - Brazzaville)',
		'cgg'         => 'Chiga',
		'zh_Hant_TW'  => 'Chinese (Traditional Han, Taiwan)',
		'sr_Cyrl_ME'  => 'Serbian (Cyrillic, Montenegro)',
		'lag'         => 'Langi',
		'ses_ML'      => 'Koyraboro Senni (Mali)',
		'en_ZW'       => 'English (Zimbabwe)',
		'ak_GH'       => 'Akan (Ghana)',
		'vi_VN'       => 'Vietnamese (Vietnam)',
		'sv_FI'       => 'Swedish (Finland)',
		'to_TO'       => 'Tonga (Tonga)',
		'fr_MG'       => 'French (Madagascar)',
		'fr_GA'       => 'French (Gabon)',
		'fr_CH'       => 'French (Switzerland)',
		'de_CH'       => 'German (Switzerland)',
		'es_US'       => 'Spanish (United States)',
		'ki'          => 'Kikuyu',
		'my_MM'       => 'Burmese (Myanmar [Burma])',
		'vi'          => 'Vietnamese',
		'ar_QA'       => 'Arabic (Qatar)',
		'ga_IE'       => 'Irish (Ireland)',
		'rwk'         => 'Rwa',
		'bez'         => 'Bena',
		'ee_GH'       => 'Ewe (Ghana)',
		'kk'          => 'Kazakh',
		'as_IN'       => 'Assamese (India)',
		'ca_ES'       => 'Catalan (Spain)',
		'kl'          => 'Kalaallisut',
		'fr_SN'       => 'French (Senegal)',
		'ne_IN'       => 'Nepali (India)',
		'km'          => 'Khmer',
		'ms_BN'       => 'Malay (Brunei)',
		'ar_LB'       => 'Arabic (Lebanon)',
		'ta_LK'       => 'Tamil (Sri Lanka)',
		'kn'          => 'Kannada',
		'ur_IN'       => 'Urdu (India)',
		'fr_CI'       => 'French (Côte d’Ivoire)',
		'ko'          => 'Korean',
		'ha_Latn_NG'  => 'Hausa (Latin, Nigeria)',
		'sg_CF'       => 'Sango (Central African Republic)',
		'om_ET'       => 'Oromo (Ethiopia)',
		'zh_Hant_MO'  => 'Chinese (Traditional Han, Macau SAR China)',
		'uk_UA'       => 'Ukrainian (Ukraine)',
		'fa'          => 'Persian',
		'mt_MT'       => 'Maltese (Malta)',
		'ki_KE'       => 'Kikuyu (Kenya)',
		'luy_KE'      => 'Luyia (Kenya)',
		'kw'          => 'Cornish',
		'pa_Guru_IN'  => 'Punjabi (Gurmukhi, India)',
		'en_IN'       => 'English (India)',
		'kab'         => 'Kabyle',
		'ar_IQ'       => 'Arabic (Iraq)',
		'ff'          => 'Fulah',
		'en_TT'       => 'English (Trinidad and Tobago)',
		'bez_TZ'      => 'Bena (Tanzania)',
		'es_NI'       => 'Spanish (Nicaragua)',
		'uz_Arab'     => 'Uzbek (Arabic)',
		'ne_NP'       => 'Nepali (Nepal)',
		'fi'          => 'Finnish',
		'khq'         => 'Koyra Chiini',
		'gsw'         => 'Swiss German',
		'zh_Hans_MO'  => 'Chinese (Simplified Han, Macau SAR China)',
		'en_MH'       => 'English (Marshall Islands)',
		'hu_HU'       => 'Hungarian (Hungary)',
		'en_GB'       => 'English (United Kingdom)',
		'fr_BE'       => 'French (Belgium)',
		'de_BE'       => 'German (Belgium)',
		'saq'         => 'Samburu',
		'be_BY'       => 'Belarusian (Belarus)',
		'sl_SI'       => 'Slovenian (Slovenia)',
		'sr_Latn_RS'  => 'Serbian (Latin, Serbia)',
		'fo'          => 'Faroese',
		'fr'          => 'French',
		'xog'         => 'Soga',
		'fr_BF'       => 'French (Burkina Faso)',
		'tzm'         => 'Central Morocco Tamazight',
		'sk_SK'       => 'Slovak (Slovakia)',
		'fr_ML'       => 'French (Mali)',
		'he_IL'       => 'Hebrew (Israel)',
		'ha_Latn_NE'  => 'Hausa (Latin, Niger)',
		'ru_RU'       => 'Russian (Russia)',
		'fr_CM'       => 'French (Cameroon)',
		'teo_KE'      => 'Teso (Kenya)',
		'seh_MZ'      => 'Sena (Mozambique)',
		'kl_GL'       => 'Kalaallisut (Greenland)',
		'fi_FI'       => 'Finnish (Finland)',
		'kam'         => 'Kamba',
		'es_ES'       => 'Spanish (Spain)',
		'af'          => 'Afrikaans',
		'asa_TZ'      => 'Asu (Tanzania)',
		'cs_CZ'       => 'Czech (Czech Republic)',
		'tr_TR'       => 'Turkish (Turkey)',
		'es_PY'       => 'Spanish (Paraguay)',
		'tzm_Latn_MA' => 'Central Morocco Tamazight (Latin, Morocco)',
		'lg'          => 'Ganda',
		'ebu'         => 'Embu',
		'en_HK'       => 'English (Hong Kong SAR China)',
		'nl_NL'       => 'Dutch (Netherlands)',
		'en_BE'       => 'English (Belgium)',
		'ms_MY'       => 'Malay (Malaysia)',
		'es_UY'       => 'Spanish (Uruguay)',
		'ar_BH'       => 'Arabic (Bahrain)',
		'kw_GB'       => 'Cornish (United Kingdom)',
		'ak'          => 'Akan',
		'chr'         => 'Cherokee',
		'dav'         => 'Taita',
		'lag_TZ'      => 'Langi (Tanzania)',
		'am'          => 'Amharic',
		'so_DJ'       => 'Somali (Djibouti)',
		'shi_Tfng_MA' => 'Tachelhit (Tifinagh, Morocco)',
		'sr_Latn_ME'  => 'Serbian (Latin, Montenegro)',
		'sn_ZW'       => 'Shona (Zimbabwe)',
		'or_IN'       => 'Oriya (India)',
		'ar'          => 'Arabic',
		'as'          => 'Assamese',
		'fr_BI'       => 'French (Burundi)',
		'jmc_TZ'      => 'Machame (Tanzania)',
		'chr_US'      => 'Cherokee (United States)',
		'eu_ES'       => 'Basque (Spain)',
		'saq_KE'      => 'Samburu (Kenya)',
		'vun'         => 'Vunjo',
		'lt'          => 'Lithuanian',
		'naq_NA'      => 'Nama (Namibia)',
		'ga'          => 'Irish',
		'af_NA'       => 'Afrikaans (Namibia)',
		'kea_CV'      => 'Kabuverdianu (Cape Verde)',
		'es_DO'       => 'Spanish (Dominican Republic)',
		'lv'          => 'Latvian',
		'kok_IN'      => 'Konkani (India)',
		'de_LI'       => 'German (Liechtenstein)',
		'fr_BJ'       => 'French (Benin)',
		'az'          => 'Azerbaijani',
		'guz_KE'      => 'Gusii (Kenya)',
		'rw_RW'       => 'Kinyarwanda (Rwanda)',
		'mg_MG'       => 'Malagasy (Madagascar)',
		'km_KH'       => 'Khmer (Cambodia)',
		'gl'          => 'Galician',
		'shi_Tfng'    => 'Tachelhit (Tifinagh)',
		'ar_AE'       => 'Arabic (United Arab Emirates)',
		'fr_MQ'       => 'French (Martinique)',
		'rm'          => 'Romansh',
		'sv_SE'       => 'Swedish (Sweden)',
		'az_Cyrl'     => 'Azerbaijani (Cyrillic)',
		'ro'          => 'Romanian',
		'so_ET'       => 'Somali (Ethiopia)',
		'en_ZA'       => 'English (South Africa)',
		'ii_CN'       => 'Sichuan Yi (China)',
		'fr_BL'       => 'French (Saint Barthélemy)',
		'hi_IN'       => 'Hindi (India)',
		'gu'          => 'Gujarati',
		'mer_KE'      => 'Meru (Kenya)',
		'nn_NO'       => 'Norwegian Nynorsk (Norway)',
		'gv'          => 'Manx',
		'ru'          => 'Russian',
		'ar_DZ'       => 'Arabic (Algeria)',
		'ar_SY'       => 'Arabic (Syria)',
		'en_MP'       => 'English (Northern Mariana Islands)',
		'nl_BE'       => 'Dutch (Belgium)',
		'rw'          => 'Kinyarwanda',
		'be'          => 'Belarusian',
		'en_VI'       => 'English (U.S. Virgin Islands)',
		'es_CL'       => 'Spanish (Chile)',
		'bg'          => 'Bulgarian',
		'mg'          => 'Malagasy',
		'hy_AM'       => 'Armenian (Armenia)',
		'zu_ZA'       => 'Zulu (South Africa)',
		'guz'         => 'Gusii',
		'mk'          => 'Macedonian',
		'es_VE'       => 'Spanish (Venezuela)',
		'ml'          => 'Malayalam',
		'bm'          => 'Bambara',
		'khq_ML'      => 'Koyra Chiini (Mali)',
		'bn'          => 'Bengali',
		'ps_AF'       => 'Pashto (Afghanistan)',
		'so_SO'       => 'Somali (Somalia)',
		'sr_Cyrl'     => 'Serbian (Cyrillic)',
		'pl_PL'       => 'Polish (Poland)',
		'fr_GN'       => 'French (Guinea)',
		'bo'          => 'Tibetan',
		'om_KE'       => 'Oromo (Kenya)',
	);

	return $code && isset( $site_languages[ $code ] ) ? $site_languages[ $code ] : $code;
}

/**
 * Get current user capability.
 *
 * @param string $type Capability type.
 */
function wtai_current_user_can( $type = '' ) {
	if ( is_super_admin() ) {
		return true;
	} else {
		return current_user_can( $type );
	}
}

/**
 * Get product various meta values.
 *
 * @param int $post_id Post ID.
 */
function wtai_get_product_data_values( $post_id ) {
	$post                               = get_post( $post_id );
	$post_return['post_status_ucfirst'] = ucfirst( get_post_status( $post_id ) );

	switch ( $post->post_status ) {
		case 'private':
			$post_return['status'] = __( 'Privately Published' );
			break;
		case 'publish':
			$post_return['status'] = __( 'Published' );
			break;
		case 'future':
			$post_return['status'] = __( 'Scheduled' );
			break;
		case 'pending':
			$post_return['status'] = __( 'Pending Review' );
			break;
		case 'draft':
		case 'auto-draft':
			$post_return['status'] = __( 'Draft' );
			break;
	}

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

	$post_return['post_visibility'] = esc_html( $visibility_trans );

	/* translators: Publish box date string. 1: Date, 2: Time. See https://www.php.net/manual/datetime.format.php */
	$date_string = __( '%1$s at %2$s' );

	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );
	if ( 'future' === $post->post_status ) { // Scheduled for publishing at a future date.
		/* translators: Post date information. %s: Date on which the post is currently scheduled to be published. */
		$stamp = __( 'Scheduled for: %s' );
	} elseif ( 'publish' === $post->post_status || 'private' === $post->post_status ) { // Already published.
		/* translators: Post date information. %s: Date on which the post was published. */
		$stamp = __( 'Published on: %s' );
	} elseif ( '0000-00-00 00:00:00' === $post->post_date_gmt ) { // Draft, 1 or more saves, no date specified.
		$stamp = __( 'Publish <b>immediately</b>' );
	} elseif ( time() < strtotime( $post->post_date_gmt . ' +0000' ) ) { // Draft, 1 or more saves, future date specified.
		/* translators: Post date information. %s: Date on which the post is to be published. */
		$stamp = __( 'Schedule for: %s' );
	} else { // Draft, 1 or more saves, date specified.
		/* translators: Post date information. %s: Date on which the post is to be published. */
		$stamp = __( 'Publish on: %s' );
	}

	$date = sprintf(
		$date_string,
		date_i18n( $date_format, strtotime( $post->post_date ) ),
		date_i18n( $time_format, strtotime( $post->post_date ) )
	);

	$post_return[''] = sprintf( $stamp, '<b>' . $date . '</b>' );

	$row = array(
		'wtai_id'            => $post_id,
		'wtai_title'         => '<a href="' . get_permalink( $post_id ) . '" target="_blank" class="wtai-cwe-action-title">' . get_the_title( $post_id ) . '</a>',
		'wtai_language'      => apply_filters( 'wtai_column_language', get_locale(), $post ),
		'wtai_generate_date' => get_post_meta( $post_id, 'wtai_generate_date', true ),
		'wtai_transfer_date' => get_post_meta( $post_id, 'wtai_transfer_date', true ),
		'wtai_data'          => wp_json_encode( $post_return ),
	);

	return wp_json_encode( $post_return );
}

/*
Preview Change Hooks in
name="description" = page description
property="og:title" = page title
property="og:description" = page description
name="twitter:title" = page title
name="twitter:description" = page description
*/
/* WordPress SEO PLUGIN (YOAST) */

/**
 * Preview changes data fetch.
 */
function wtai_preview_changes() {
	if ( ! is_product() ) {
		return array();
	}

	global $post;
	$product_id = $post->ID;

	if ( ! $product_id ) {
		return array();
	}

	$results = apply_filters( 'wtai_generate_product_text', array(), $product_id, array( 'historyCount' => 1 ) );

	$preview_fields = array();

	foreach ( $results as $key => $value ) {
		if ( isset( $value['page_title'] ) ) {
			$preview_fields['page_title'] = stripslashes_deep( $value['page_title'][0]['value'] );
		}
		if ( isset( $value['page_description'] ) ) {
			$preview_fields['page_description'] = stripslashes_deep( $value['page_description'][0]['value'] );
		}
		if ( isset( $value['product_description'] ) ) {
			$preview_fields['product_description'] = stripslashes_deep( $value['product_description'][0]['value'] );
		}
		if ( isset( $value['product_excerpt'] ) ) {
			$preview_fields['product_excerpt'] = stripslashes_deep( $value['product_excerpt'][0]['value'] );
		}
		if ( isset( $value['open_graph'] ) ) {
			$preview_fields['open_graph'] = stripslashes_deep( $value['open_graph'][0]['value'] );
		}
		break;
	}

	return $preview_fields;
}

/**
 * Preview changes data fetch.
 */
function wtai_preview_category_changes() {
	if ( ! is_product_category() ) {
		return array();
	}

	$term        = get_queried_object();
	$category_id = $term->term_id;

	if ( ! $category_id ) {
		return array();
	}

	$results = apply_filters( 'wtai_generate_category_text', array(), $category_id, array( 'historyCount' => 1 ) );

	$preview_fields = array();

	foreach ( $results as $key => $value ) {
		if ( isset( $value['page_title'] ) ) {
			$preview_fields['page_title'] = stripslashes_deep( $value['page_title'][0]['value'] );
		}
		if ( isset( $value['page_description'] ) ) {
			$preview_fields['page_description'] = stripslashes_deep( $value['page_description'][0]['value'] );
		}
		if ( isset( $value['category_description'] ) ) {
			$preview_fields['category_description'] = stripslashes_deep( $value['category_description'][0]['value'] );
		}
		if ( isset( $value['open_graph'] ) ) {
			$preview_fields['open_graph'] = stripslashes_deep( $value['open_graph'][0]['value'] );
		}
		break;
	}

	return $preview_fields;
}

/**
 * Preview product title.
 *
 * @param string $title Product title.
 */
function wtai_preview_product_title( $title ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['page_title'] ) ) {
			$title = nl2br( $fields['page_title'] );
		}
	}
	return $title;
}
add_filter( 'aioseo_title', 'wtai_preview_product_title', PHP_INT_MAX ); // all in one seo.

/**
 * Preview product content.
 *
 * @param string $content Product content.
 */
function wtai_preview_product_description( $content ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		$fields = wtai_preview_changes();

		if ( isset( $fields['product_description'] ) ) {
			$content = nl2br( $fields['product_description'] );
		}
	}
	return $content;
}
add_filter( 'the_content', 'wtai_preview_product_description' );

/**
 * Preview product description.
 *
 * @param string $content Product description.
 */
function wtai_preview_product_description_tab( $content ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		$fields = wtai_preview_changes();

		if ( isset( $fields['product_description'] ) ) {
			$content = wpautop( $fields['product_description'] );
		}
	}
	echo do_shortcode( $content );
}

/**
 * Filter woocommerce_product_tabs for preview display.
 *
 * @param array $tabs Product tabs.
 */
function wtai_display_empty_description_tab( $tabs ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] && isset( $fields['product_description'] ) ) {
		$fields = wtai_preview_changes();

		$tabs['description'] = array(
			'title'    => __( 'Description', 'woocommerce' ),
			'priority' => 10,
			'callback' => 'wtai_preview_product_description_tab',
		);
	}
	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'wtai_display_empty_description_tab' );

/**
 * Preview Product short description.
 *
 * @param string $excerpt Product short description.
 */
function wtai_preview_product_excerpt( $excerpt ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		$fields = wtai_preview_changes();
		if ( isset( $fields['product_excerpt'] ) ) {
			$excerpt = wpautop( $fields['product_excerpt'] );
		}
	}
	return $excerpt;
}
add_filter( 'woocommerce_short_description', 'wtai_preview_product_excerpt' );

/**
 * Preview Category description.
 *
 * @param string $description Category description.
 * @param object $term Category term.
 */
function wtai_preview_product_category_desc( $description, $term ) {
	if ( ! $term ) {
		$term = get_queried_object();
	}

	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		$fields = wtai_preview_category_changes();
		if ( isset( $fields['category_description'] ) ) {
			$description = wpautop( $fields['category_description'] );
		}
	}
	return $description;
}
add_filter( 'woocommerce_taxonomy_archive_description_raw', 'wtai_preview_product_category_desc', 10, 2 );

/**
 * Preview wpseo meta title.
 *
 * @param string $title Product title.
 */
function wtai_preview_wpseo_metatitle( $title ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['page_title'] ) ) {
			$title = wp_strip_all_tags( $fields['page_title'] );
		}
	}
	return $title;
}
add_filter( 'wpseo_title', 'wtai_preview_wpseo_metatitle' );
add_filter( 'wpseo_opengraph_title', 'wtai_preview_wpseo_metatitle' );
add_filter( 'wpseo_twitter_title', 'wtai_preview_wpseo_metatitle' );

/**
 * Preview wpseo meta desc.
 *
 * @param string $description Product description.
 */
function wtai_preview_wpseo_metadesc( $description ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['page_description'] ) ) {
			$description = wp_strip_all_tags( $fields['page_description'] );
		}
	}
	return $description;
}
add_filter( 'wpseo_metadesc', 'wtai_preview_wpseo_metadesc' );
add_filter( 'rank_math/frontend/description', 'wtai_preview_wpseo_metadesc', PHP_INT_MAX, 2 );

/**
 * Preview wpseo og desc.
 *
 * @param string $description Product description.
 */
function wtai_preview_wpseo_og_description( $description ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['open_graph'] ) ) {
			$description = wp_strip_all_tags( $fields['open_graph'] );
		}
	}
	return $description;
}
add_filter( 'wpseo_opengraph_desc', 'wtai_preview_wpseo_og_description' );
add_filter( 'wpseo_twitter_description', 'wtai_preview_wpseo_og_description' );

/* RANK MATH PLUGIN */

/**
 * Preview rank math OG title.
 *
 * @param string $title Product title.
 */
function wtai_preview_rankmath_og_title( $title ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['page_title'] ) ) {
			$title = wp_strip_all_tags( $fields['page_title'] );
		}
	}
	return $title;
}
add_filter( 'rank_math/frontend/title', 'wtai_preview_rankmath_og_title', 10, 2 );

/**
 * Preview rank math OG desc.
 *
 * @param string $description Product description.
 */
function wtai_preview_rankmath_og_desc( $description ) {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		if ( is_product() ) {
			$fields = wtai_preview_changes();
		} elseif ( is_product_category() ) {
			$fields = wtai_preview_category_changes();
		}

		if ( isset( $fields['page_description'] ) ) {
			$description = wp_strip_all_tags( $fields['page_description'] );
		}
	}
	return $description;
}
add_filter( 'rank_math/frontend/description', 'wtai_preview_rankmath_og_desc', 10, 2 );

/**
 * Preview rank math header data.
 */
add_action(
	'rank_math/head',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
			remove_all_actions( 'rank_math/opengraph/facebook' );
			remove_all_actions( 'rank_math/opengraph/twitter' );

			if ( is_product() ) {
				$fields = wtai_preview_changes();
			} elseif ( is_product_category() ) {
				$fields = wtai_preview_category_changes();
			}

			$new_meta = '';
			if ( isset( $fields['page_title'] ) ) {
				$title     = wp_strip_all_tags( $fields['page_title'] );
				$new_meta  = '<meta property="og:title" content="' . $title . '">';
				$new_meta .= '<meta property="twitter:title" content="' . $title . '">';
			}
			if ( isset( $fields['page_description'] ) ) {
				$description = wp_strip_all_tags( $fields['page_description'] );
			}
			if ( isset( $fields['open_graph'] ) ) {
				$opengraph_desc = wp_strip_all_tags( $fields['open_graph'] );
				$new_meta      .= '<meta property="og:description" content="' . $opengraph_desc . '">';
				$new_meta      .= '<meta property="twitter:description" content="' . $opengraph_desc . '">';
			}

			echo wp_kses( $new_meta, wtai_kses_allowed_html() ); //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
);

/**
 * Preview AIOSEO filters.
 */
function wtai_aioseo_filters() {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
		add_filter( 'aioseo_description', 'wtai_preview_aioseo_meta_description' );
		add_filter( 'aioseo_facebook_tags', 'wtai_preview_aioseo_facebook_tags' );
		add_filter( 'aioseo_twitter_tags', 'wtai_preview_aioseo_twitter_tags' );
	}
}
add_action( 'wp', 'wtai_aioseo_filters', 9999 );

/**
 * Preview AIOSEO meta desc.
 *
 * @param string $description Product description.
 */
function wtai_preview_aioseo_meta_description( $description ) {
	if ( is_product() ) {
		$fields = wtai_preview_changes();
	} elseif ( is_product_category() ) {
		$fields = wtai_preview_category_changes();
	}

	if ( isset( $fields['page_description'] ) ) {
		$description = wp_strip_all_tags( $fields['page_description'] );
	}
	return $description;
}

/**
 * Preview AIOSEO facebook desc.
 *
 * @param string $facebook_meta Product description.
 */
function wtai_preview_aioseo_facebook_tags( $facebook_meta ) {
	if ( is_product() ) {
		$fields = wtai_preview_changes();
	} elseif ( is_product_category() ) {
		$fields = wtai_preview_category_changes();
	}

	if ( isset( $fields['page_title'] ) ) {
		$title                     = wp_strip_all_tags( $fields['page_title'] );
		$facebook_meta['og:title'] = $title;
	}
	if ( isset( $fields['open_graph'] ) ) {
		$opengraph_desc                  = wp_strip_all_tags( $fields['open_graph'] );
		$facebook_meta['og:description'] = $opengraph_desc;
	}
	return $facebook_meta;
}

/**
 * Preview AIOSEO twitter desc.
 *
 * @param string $twitter_meta Product description.
 */
function wtai_preview_aioseo_twitter_tags( $twitter_meta ) {
	if ( is_product() ) {
		$fields = wtai_preview_changes();
	} elseif ( is_product_category() ) {
		$fields = wtai_preview_category_changes();
	}

	if ( isset( $fields['page_title'] ) ) {
		$title                         = wp_strip_all_tags( $fields['page_title'] );
		$twitter_meta['twitter:title'] = $title;
	}

	if ( isset( $fields['page_description'] ) ) {
		$description = wp_strip_all_tags( $fields['page_description'] );
	}

	if ( isset( $fields['open_graph'] ) ) {
		$opengraph_desc = wp_strip_all_tags( $fields['open_graph'] );
		if ( $opengraph_desc ) {
			$description = $opengraph_desc;
		}

		$twitter_meta['twitter:description'] = $description;
	}

	return $twitter_meta;
}

/**
 * Hide update notice in writextai page.
 */
function wtai_hide_update_messages() {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['page'] ) && ( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) ) {
		if ( wtai_is_allowed_beta_language() ) {
			remove_all_actions( 'admin_notices' );
		}
	}
}
add_action( 'admin_head', 'wtai_hide_update_messages' );

/**
 * Get bulk generate jobs.
 *
 * @param bool $fetch_api Fetch API.
 * @param bool $consider_user_done_request Consider user done request.
 */
function wtai_get_bulk_generate_jobs( $fetch_api = true, $consider_user_done_request = true ) {
	// Get generate jobs.

	$all_bulk_requests = wtai_get_all_bulk_requests();

	$all_bulk_current_user_done_requests = wtai_get_done_requests_for_current_user(); // done request for the current user.

	$current_user_id = get_current_user_id();
	$jobs            = array();
	$own_job         = array();
	foreach ( $all_bulk_requests as $user_id => $bulk_request ) {
		$request_id          = $bulk_request['request_id'];
		$product_ids         = $bulk_request['product_ids'];
		$date_bulk_generated = $bulk_request['date_bulk_generated'];

		$is_completed_by_user = false;
		if ( $consider_user_done_request && $all_bulk_current_user_done_requests
			&& in_array( $request_id, $all_bulk_current_user_done_requests, true ) ) {
			$is_completed_by_user = true;
		}

		if ( $is_completed_by_user ) {
			continue; // Lets move on to the next record.
		}

		$request = array();
		if ( $fetch_api ) {
			$request = apply_filters( 'wtai_generate_product_bulk', array(), $request_id );

			if ( 1 === count( $product_ids ) && '1' === $request['error'] && 404 === intval( $request['http_header'] ) ) {
				$request = array(
					'id'           => $request_id,
					'status'       => 'Completed',
					'completedIds' => $product_ids,
					'completed'    => count( $product_ids ),
					'total'        => count( $product_ids ),
				);
			}
		}

		if ( $current_user_id === $user_id ) {
			$own_job = array(
				'request_id'          => $request_id,
				'user_id'             => $user_id,
				'product_ids'         => $product_ids,
				'request'             => $request,
				'date_bulk_generated' => $date_bulk_generated,
				'type'                => 'generate',
			);
		} else {
			$jobs[] = array(
				'request_id'          => $request_id,
				'user_id'             => $user_id,
				'product_ids'         => $product_ids,
				'request'             => $request,
				'date_bulk_generated' => $date_bulk_generated,
				'type'                => 'generate',
			);
		}
	}

	// Get transfer jobs.
	$transfer_product_jobs                = wtai_get_all_bulk_transfer_products();
	$all_bulk_current_user_done_transfers = wtai_get_done_transfers_for_current_user(); // done request for the current user.

	if ( $transfer_product_jobs ) {
		foreach ( $transfer_product_jobs as $user_id => $data ) {
			$product_ids = $data['product_ids'];
			$product_ids = array_unique( $product_ids );
			$product_ids = array_values( $product_ids );

			$date_bulk_generated = $data['date_bulk_generated'];
			$completed_ids       = $data['completed_ids'];

			$is_completed_by_user = false;
			if ( $consider_user_done_request && $all_bulk_current_user_done_transfers ) {
				if ( in_array( $user_id, $all_bulk_current_user_done_transfers, true ) ) {
					$is_completed_by_user = true;
				}
			}

			if ( $is_completed_by_user ) {
				continue; // lets move on to the next record.
			}

			if ( $current_user_id === $user_id ) {
				$own_job = array(
					'user_id'             => $user_id,
					'product_ids'         => $product_ids,
					'completed_ids'       => $completed_ids,
					'date_bulk_generated' => $date_bulk_generated,
					'type'                => 'transfer',
				);
			} else {
				$jobs[] = array(
					'user_id'             => $user_id,
					'product_ids'         => $product_ids,
					'completed_ids'       => $completed_ids,
					'date_bulk_generated' => $date_bulk_generated,
					'type'                => 'transfer',
				);
			}
		}
	}

	if ( $jobs ) {
		$columns = array_column( $jobs, 'date_bulk_generated' );
		array_multisort( $columns, SORT_DESC, $jobs );

		if ( $own_job ) {
			array_unshift( $jobs, $own_job );
		}
	} elseif ( $own_job ) {
		$jobs[] = $own_job;
	}

	return $jobs;
}

/**
 * Get bulk generate products.
 */
function wtai_get_current_user_bulk_generation_products() {
	$user_id       = get_current_user_id();
	$generated_ids = get_user_meta( $user_id, 'wtai_bulk_generated_ids', true );

	if ( ! $generated_ids ) {
		$generated_ids = array();
	}

	return $generated_ids;
}

/**
 * Record current user bulk generated products.
 *
 * @param array $product_ids Product IDs.
 */
function wtai_record_current_user_bulk_generation_products( $product_ids = array() ) {
	$user_id = get_current_user_id();
	update_user_meta( $user_id, 'wtai_bulk_generated_ids', $product_ids );
}

/**
 * Get current user bulk request ID.
 */
function wtai_get_current_user_bulk_request_id() {
	$user_id    = get_current_user_id();
	$request_id = get_user_meta( $user_id, 'wtai_bulk_request_id', true );

	if ( ! $request_id ) {
		$request_id = array();
	}

	return $request_id;
}

/**
 * Record current user bulk record id.
 *
 * @param string $request_id Request ID.
 */
function wtai_record_current_user_bulk_request_id( $request_id = '' ) {
	$user_id = get_current_user_id();

	update_user_meta( $user_id, 'wtai_bulk_request_id', $request_id );
}

/**
 * Get all bulk requests.
 */
function wtai_get_all_bulk_requests() {
	$user_id = get_current_user_id();

	$current_requests = get_option( 'wtai_bulk_generate_request', array() );

	if ( ! $current_requests ) {
		$current_requests = array();
	}

	// get current user requests.
	$current_user_request_id = wtai_get_current_user_bulk_request_id();
	$current_user_products   = wtai_get_current_user_bulk_generation_products();
	if ( $current_user_request_id && ! isset( $current_requests[ $user_id ] ) ) {
		wtai_record_all_bulk_requests( $current_user_request_id, $current_user_products );
	}

	return $current_requests;
}

/**
 * Record all bulk requests.
 *
 * @param string $request_id Request ID.
 * @param array  $product_ids Product IDs.
 */
function wtai_record_all_bulk_requests( $request_id = '', $product_ids = array() ) {
	$current_user_id = get_current_user_id();

	$current_requests = get_option( 'wtai_bulk_generate_request', array() );

	if ( ! $current_requests ) {
		$current_requests = array();
	}

	$current_requests[ $current_user_id ] = array(
		'request_id'          => $request_id,
		'product_ids'         => $product_ids,
		'date_bulk_generated' => current_time( 'mysql' ),
	);

	update_option( 'wtai_bulk_generate_request', $current_requests );

	return $current_requests;
}

/**
 * Record bulk request and product ids.
 *
 * @param string $request_id Request ID.
 * @param array  $product_ids Product IDs.
 */
function wtai_record_bulk_generation( $request_id, $product_ids ) {
	wtai_record_all_bulk_requests( $request_id, $product_ids );
	wtai_record_current_user_bulk_request_id( $request_id );
	wtai_record_current_user_bulk_generation_products( $product_ids );
	wtai_record_all_bulk_product_ids();
}

/**
 * Get done bulk requests.
 */
function wtai_get_done_requests() {
	$done_requests = get_option( 'wtai_bulk_generate_request_done', array() );

	if ( ! $done_requests ) {
		$done_requests = array();
	}

	return $done_requests;
}

/**
 * Get done product requests.
 */
function wtai_get_done_products() {
	$done_requests = get_option( 'wtai_bulk_generate_products_done', array() );

	if ( ! $done_requests ) {
		$done_requests = array();
	}

	return $done_requests;
}

/**
 * Get done requests for current user.
 */
function wtai_get_done_requests_for_current_user() {
	$done_requests = wtai_get_done_requests();

	if ( ! $done_requests ) {
		$done_requests = array();
	}

	$current_user_id                = get_current_user_id();
	$done_requests_for_current_user = array();
	foreach ( $done_requests as $user_id => $request_ids ) {
		if ( $current_user_id === $user_id ) {
			if ( is_array( $request_ids ) ) {
				foreach ( $request_ids as $request_id ) {
					$done_requests_for_current_user[] = $request_id;
				}
			}
		}
	}

	return $done_requests_for_current_user;
}

/**
 * Get done products for current user.
 */
function wtai_get_done_products_for_current_user() {
	$done_products = wtai_get_done_products();

	if ( ! $done_products ) {
		$done_products = array();
	}

	$current_user_id                = get_current_user_id();
	$done_products_for_current_user = array();
	foreach ( $done_products as $user_id => $products_ids ) {
		if ( $current_user_id === $user_id ) {
			if ( is_array( $products_ids ) ) {
				foreach ( $products_ids as $product_id ) {
					$done_products_for_current_user[] = $product_id;
				}
			}
		}
	}

	return $done_products_for_current_user;
}

/**
 * Get done transfer users.
 */
function wtai_get_done_transfer_users() {
	$done_requests = get_option( 'wtai_bulk_transfer_users_done', array() );

	if ( ! $done_requests ) {
		$done_requests = array();
	}

	return $done_requests;
}

/**
 * Get done transfer users for current user.
 */
function wtai_get_done_transfers_for_current_user() {
	$done_products = wtai_get_done_transfer_users();

	if ( ! $done_products ) {
		$done_products = array();
	}

	$current_user_id                 = get_current_user_id();
	$done_transfers_for_current_user = array();
	foreach ( $done_products as $user_id => $user_ids ) {
		if ( $current_user_id === $user_id ) {
			if ( is_array( $user_ids ) ) {
				foreach ( $user_ids as $done_user_id ) {
					$done_transfers_for_current_user[] = $done_user_id;
				}
			}
		}
	}

	return $done_transfers_for_current_user;
}

/**
 * Clear user bulk generation.
 *
 * @param string $request_specific_id Request specific ID.
 */
function wtai_clear_user_bulk_generation( $request_specific_id = '' ) {
	$current_user_id = get_current_user_id();

	$all_requests                    = get_option( 'wtai_bulk_generate_request', array() );
	$hide_for_current_users          = get_option( 'wtai_bulk_generate_request_done', array() );
	$hide_products_for_current_users = get_option( 'wtai_bulk_generate_products_done', array() );

	if ( ! is_array( $hide_for_current_users ) ) {
		$hide_for_current_users = array();
	}

	if ( ! is_array( $hide_products_for_current_users ) ) {
		$hide_products_for_current_users = array();
	}

	$user_done_request    = array();
	$user_done_productids = array();

	if ( isset( $hide_for_current_users[ $current_user_id ] ) && is_array( $hide_for_current_users[ $current_user_id ] ) ) {
		$user_done_request = $hide_for_current_users[ $current_user_id ];
	}

	$is_own_request = false;
	if ( $all_requests ) {
		foreach ( $all_requests as $user_id => $request_data ) {
			$request_id  = $request_data['request_id'];
			$product_ids = $request_data['product_ids'];

			$do_clear = false;
			if ( '' !== $request_specific_id ) {
				if ( $request_specific_id === $request_id ) {
					$do_clear = true;
				}
			} else {
				$do_clear = true;
			}

			if ( $do_clear ) {
				if ( $current_user_id === $user_id ) {
					unset( $all_requests[ $user_id ] );

					$is_own_request = true;
				} else {
					$user_done_request[] = $request_id;

					foreach ( $product_ids as $prod_id ) {
						$user_done_productids[] = $prod_id;
					}
				}
			}
		}

		if ( $user_done_request ) {
			$user_done_request = array_unique( $user_done_request );
		}

		$hide_for_current_users[ $current_user_id ]          = $user_done_request;
		$hide_products_for_current_users[ $current_user_id ] = $user_done_productids;
	}

	if ( ! $all_requests ) {
		$hide_for_current_users          = array();
		$hide_products_for_current_users = array();
	}

	update_option( 'wtai_bulk_generate_request', $all_requests );
	update_option( 'wtai_bulk_generate_request_done', $hide_for_current_users );
	update_option( 'wtai_bulk_generate_products_done', $hide_products_for_current_users );

	if ( $is_own_request ) {
		wtai_record_current_user_bulk_request_id( '' );
		wtai_record_current_user_bulk_generation_products( '' );
	}

	// refresh the product ids.
	wtai_record_all_bulk_product_ids();

	$output = array(
		'is_own_request' => $is_own_request,
	);

	return $output;
}

/**
 * Record ALL bulk product ids.
 */
function wtai_record_all_bulk_product_ids() {
	$current_product_ids = array();

	$all_requests = get_option( 'wtai_bulk_generate_request', array() );
	foreach ( $all_requests as $request_data ) {
		$product_ids = $request_data['product_ids'];
		foreach ( $product_ids as $product_id ) {
			$current_product_ids[] = $product_id;
		}
	}

	update_option( 'wtai_bulk_product_ids', $current_product_ids );

	return $current_product_ids;
}

/**
 * Record current user bulk transfer products.
 *
 * @param array $product_ids Array of product ids.
 */
function wtai_record_current_user_bulk_transfer_products( $product_ids = array() ) {
	$user_id = get_current_user_id();

	update_user_meta( $user_id, 'wtai_bulk_transfer_ids', $product_ids );
}

/**
 * Get current user bulk transfer products.
 */
function wtai_get_current_user_bulk_transfer_products() {
	$user_id       = get_current_user_id();
	$generated_ids = get_user_meta( $user_id, 'wtai_bulk_transfer_ids', true );

	if ( ! $generated_ids ) {
		$generated_ids = array();
	}

	return $generated_ids;
}

/**
 * Record all bulk transfer products.
 *
 * @param array $product_ids Array of product ids.
 * @param int   $completed_product_id Completed product id.
 */
function wtai_record_all_bulk_transfer_products( $product_ids = array(), $completed_product_id = 0 ) {
	$current_user_id = get_current_user_id();

	$current_requests = get_option( 'wtai_bulk_generate_transfers', array() );

	if ( ! $current_requests ) {
		$current_requests = array();
	}

	if ( $current_requests && isset( $current_requests[ $current_user_id ] ) ) {
		$completed_ids = $current_requests[ $current_user_id ]['completed_ids'];
	} else {
		$completed_ids = array();
	}

	if ( $completed_product_id ) {
		$completed_ids[] = $completed_product_id;
	}

	$current_requests[ $current_user_id ] = array(
		'product_ids'         => $product_ids,
		'date_bulk_generated' => current_time( 'mysql' ),
		'completed_ids'       => $completed_ids,
	);

	update_option( 'wtai_bulk_generate_transfers', $current_requests );

	return $current_requests;
}

/**
 * Get all bulk transfer products.
 */
function wtai_get_all_bulk_transfer_products() {
	$current_requests = get_option( 'wtai_bulk_generate_transfers', array() );

	if ( ! $current_requests ) {
		$current_requests = array();
	}

	return $current_requests;
}

/**
 * Get all bulk transfer products combined.
 */
function wtai_get_all_bulk_transfer_products_combined() {
	$current_requests = wtai_get_all_bulk_transfer_products();

	$product_ids = array();
	if ( $current_requests ) {
		foreach ( $current_requests as $data ) {
			$product_ids = array_merge( $product_ids, $data['product_ids'] );
		}
	}

	return $product_ids;
}

/**
 * Record bulk transfer products.
 *
 * @param array $product_ids Array of product ids.
 * @param int   $completed_product_id Completed product id.
 */
function wtai_record_bulk_transfer( $product_ids, $completed_product_id = 0 ) {
	wtai_record_all_bulk_transfer_products( $product_ids, $completed_product_id );
	wtai_record_current_user_bulk_transfer_products( $product_ids );
}

/**
 * Clear user bulk transfer.
 */
function wtai_clear_user_bulk_transfer() {
	$current_user_id = get_current_user_id();

	$all_requests                    = wtai_get_all_bulk_transfer_products();
	$hide_products_for_current_users = get_option( 'wtai_bulk_transfer_users_done', array() );

	if ( ! is_array( $hide_products_for_current_users ) ) {
		$hide_products_for_current_users = array();
	}

	$user_done_ids = array();

	if ( isset( $hide_products_for_current_users[ $current_user_id ] ) && is_array( $hide_products_for_current_users[ $current_user_id ] ) ) {
		$user_done_ids = $hide_products_for_current_users[ $current_user_id ];
	}

	$is_own_request = false;
	if ( $all_requests ) {
		foreach ( $all_requests as $user_id => $request_data ) {

			$product_ids = $request_data['product_ids'];

			if ( $current_user_id === $user_id ) {
				unset( $all_requests[ $user_id ] );

				if ( $hide_products_for_current_users && is_array( $hide_products_for_current_users ) ) {
					foreach ( $hide_products_for_current_users as $uid => $uids ) {
						if ( in_array( $user_id, $uids, true ) ) {
							unset( $hide_products_for_current_users[ $uid ] );
						}
					}
				}

				$is_own_request = true;
			} else {
				$user_done_ids[] = $user_id;
			}
		}

		$hide_products_for_current_users[ $current_user_id ] = $user_done_ids;
	}

	if ( ! $all_requests ) {
		$hide_products_for_current_users = array();
	}

	update_option( 'wtai_bulk_generate_transfers', $all_requests );
	update_option( 'wtai_bulk_transfer_users_done', $hide_products_for_current_users );

	if ( $is_own_request ) {
		wtai_record_current_user_bulk_transfer_products( '' );
	}

	$output = array(
		'is_own_request' => $is_own_request,
	);

	return $output;
}

/**
 * Get finished product ids.
 *
 * @param array $jobs Jobs.
 */
function wtai_get_finished_products_ids( $jobs ) {
	$finished_product_ids = array();
	foreach ( $jobs as $job ) {
		$user_id            = $job['user_id'];
		$request            = $job['request'];
		$product_ids        = $job['product_ids'];
		$request_job_status = isset( $request['status'] ) ? $request['status'] : '';

		$is_finished = false;
		if ( 'generate' === $job['type'] ) {
			$has_generate = 1;

			if ( ! empty( $request['completedIds'] ) && is_array( $request['completedIds'] ) ) {
				$bulk_completed = count( $request['completedIds'] );
			} else {
				$bulk_completed = 0;
			}
			$bulk_total = isset( $request['total'] ) ? (int) $request['total'] : 0;
			if ( 'Completed' === $request_job_status ) {
				$is_finished = true;
			} elseif ( $bulk_completed >= $bulk_total ) {
					$is_finished = true;
			}
		}
		if ( 'transfer' === $job['type'] ) {
			$has_transfer = 1;

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
		}

		if ( $is_finished ) {
			$finished_product_ids = array_merge( $finished_product_ids, $product_ids );
		}
	}

	return $finished_product_ids;
}

/**
 * Get all pending bulk ids.
 *
 * @param array $finished_product_ids Finished product ids.
 * @param bool  $check_finished_in_api Check finished in api.
 */
function wtai_get_all_pending_bulk_ids( $finished_product_ids = array(), $check_finished_in_api = false ) {
	$check_api = false;
	if ( $check_finished_in_api ) {
		$check_api = true;
	}

	$jobs = wtai_get_bulk_generate_jobs( $check_api, false, false );

	if ( $check_finished_in_api ) {
		$finished_product_ids = wtai_get_finished_products_ids( $jobs );
	}

	$all_product_ids = array();
	foreach ( $jobs as $job ) {
		$product_ids = $job['product_ids'];
		if ( $product_ids ) {
			if ( $finished_product_ids ) {
				foreach ( $product_ids as $product_id ) {
					if ( ! in_array( $product_id, $finished_product_ids, true ) ) {
						$all_product_ids[] = $product_id;
					}
				}
			} else {
				$all_product_ids = array_merge( $all_product_ids, $product_ids );
			}
		}
	}

	return $all_product_ids;
}

/**
 * Get pending generation bulk ids.
 */
function wtai_get_all_pending_generation_bulk_ids() {
	$jobs = wtai_get_bulk_generate_jobs( false );

	$all_product_ids = array();
	foreach ( $jobs as $job ) {
		$type        = $job['type'];
		$product_ids = $job['product_ids'];
		if ( $product_ids && 'generate' === $type ) {
			$all_product_ids = array_merge( $all_product_ids, $product_ids );
		}
	}

	return $all_product_ids;
}

/**
 * Get bulk products ids.
 */
function wtai_get_bulk_products_ids() {
	$wtai_bulk_product_ids = get_option( 'wtai_bulk_product_ids', array() );

	$final_product_ids = $wtai_bulk_product_ids;

	return $final_product_ids;
}

/**
 * Get generate text fields user preference.
 */
function wtai_get_bulk_generate_text_fields_user_preference() {
	$current_user_id = get_current_user_id();

	$fields                   = get_user_meta( $current_user_id, 'wtai_bulk_generate_text_field_user_preference', true );
	$wtai_bulk_generate_popup = get_user_meta( get_current_user_id(), 'wtai_bulk_generate_popup', true );

	// handled if no selected and disabled bulkpopup is enabled.
	if ( ! $fields || $wtai_bulk_generate_popup ) {
		$fields_array = apply_filters( 'wtai_fields', array() );
		$fields       = array_keys( $fields_array );

		// Add alt text field.
		$fields[] = 'alt_text';
	}

	return $fields;
}

/**
 * Get transfer text fields user preference.
 */
function wtai_get_bulk_transfer_text_fields_user_preference() {
	$current_user_id = get_current_user_id();

	$fields = get_user_meta( $current_user_id, 'wtai_bulk_transfer_text_field_user_preference', true );

	if ( ! $fields ) {
		$fields_array = apply_filters( 'wtai_fields', array() );
		$fields       = array_keys( $fields_array );

		// Add alt text field.
		$fields[] = 'alt_text';
	}

	return $fields;
}

/**
 * Get user highlight cb.
 *
 * @param string $type Type.
 */
function wtai_get_user_highlight_cb( $type = 'product' ) {
	$current_user_id = get_current_user_id();

	if ( 'category' === $type ) {
		$highlight_default_flag = get_user_meta( $current_user_id, 'wtai_highlight_default_category_flag', true );
	} else {
		$highlight_default_flag = get_user_meta( $current_user_id, 'wtai_highlight_default_flag', true );
	}

	if ( ! $highlight_default_flag ) {
		// Set default value for first time user.
		if ( 'category' === $type ) {
			update_user_meta( $current_user_id, 'wtai_highlight_default_category_flag', '1' );
			update_user_meta( $current_user_id, 'wtai_highlight_category', '1' );
		} else {
			update_user_meta( $current_user_id, 'wtai_highlight_default_flag', '1' );
			update_user_meta( $current_user_id, 'wtai_highlight', '1' );
		}

		$cb = 1;
	} else {
		if ( 'category' === $type ) {
			$cb = get_user_meta( $current_user_id, 'wtai_highlight_category', true );
		} else {
			$cb = get_user_meta( $current_user_id, 'wtai_highlight', true );
		}

		if ( ! $cb ) {
			$cb = 0;
		}
	}

	return $cb;
}

/**
 * Get user comparison cb.
 *
 * @param string $type Type of comparison.
 */
function wtai_get_user_comparison_cb( $type = 'product' ) {
	$current_user_id = get_current_user_id();

	if ( 'category' === $type ) {
		$cb = get_user_meta( $current_user_id, 'wtai_comparison_category_cb', true );
	} else {
		$cb = get_user_meta( $current_user_id, 'wtai_comparison_cb', true );
	}

	if ( ! $cb ) {
		$cb = 0;
	}

	return $cb;
}

/**
 * Get user preselcted types.
 *
 * @param string $type Type.
 */
function wtai_get_user_preselected_types( $type = 'product' ) {
	$current_user_id = get_current_user_id();

	$preselected_types = array();

	if ( 'category' === $type ) {
		$wtai_preselected_types_default_flag = get_user_meta( $current_user_id, 'wtai_preselected_types_default_category_flag', true );
	} else {
		$wtai_preselected_types_default_flag = get_user_meta( $current_user_id, 'wtai_preselected_types_default_flag', true );
	}

	if ( ! $wtai_preselected_types_default_flag ) {
		if ( 'category' === $type ) {
			$preselected_types = array_keys( apply_filters( 'wtai_category_fields', array() ) );
		} else {
			$preselected_types = array_keys( apply_filters( 'wtai_fields', array() ) );

			$preselected_types[] = 'image_alt_text';
		}

		// Set default values for first time user.
		if ( 'category' === $type ) {
			update_user_meta( $current_user_id, 'wtai_preselected_types_default_category_flag', '1' );
			update_user_meta( $current_user_id, 'wtai_preselected_category_types', $preselected_types );
		} else {
			update_user_meta( $current_user_id, 'wtai_preselected_types_default_flag', '1' );
			update_user_meta( $current_user_id, 'wtai_preselected_types', $preselected_types );
		}
	} elseif ( 'category' === $type ) {
			$preselected_types = get_user_meta( $current_user_id, 'wtai_preselected_category_types', true );
	} else {
		$preselected_types = get_user_meta( $current_user_id, 'wtai_preselected_types', true );
	}

	if ( ! $preselected_types ) {
		$preselected_types = array();
	}

	return $preselected_types;
}

/**
 * Set seo title for all in one seo.
 *
 * @param string $title Title.
 */
function wtai_aioseo_filter_title( $title ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id     = $post->ID;
		$title_check = get_post_meta( $post_id, '_aioseo_title', true );
		if ( $title_check ) {
			$title = wp_strip_all_tags( $title_check );
		}
	}

	return $title;
}
add_filter( 'aioseo_title', 'wtai_aioseo_filter_title' );

/**
 * Set seo description and og description for all in one seo.
 *
 * @param string $description Description.
 */
function wtai_aioseo_filter_description( $description ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id           = $post->ID;
		$description_check = get_post_meta( $post_id, '_aioseo_description', true );

		if ( $description_check ) {
			$description = wp_strip_all_tags( $description_check );
		}
	}

	return $description;
}
add_filter( 'aioseo_description', 'wtai_aioseo_filter_description' );

/**
 * Set seo fb meta for all in one seo.
 *
 * @param string $facebook_meta Facebook meta.
 */
function wtai_aioseo_filter_facebook_meta( $facebook_meta ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id           = $post->ID;
		$description_check = get_post_meta( $post_id, '_aioseo_og_description', true );
		if ( $description_check ) {
			$facebook_meta['og:description'] = wp_strip_all_tags( $description_check );
		}
	}

	return $facebook_meta;
}
add_filter( 'aioseo_facebook_tags', 'wtai_aioseo_filter_facebook_meta' );

/**
 * Set seo twitter meta for all in one seo.
 *
 * @param string $twitter_meta Twitter meta.
 */
function wtai_aioseo_filter_twitter_tags( $twitter_meta ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id           = $post->ID;
		$description_check = get_post_meta( $post_id, '_aioseo_og_description', true );
		if ( $description_check ) {
			$twitter_meta['twitter:description'] = wp_strip_all_tags( $description_check );
		}
	}

	return $twitter_meta;
}
add_filter( 'aioseo_twitter_tags', 'wtai_aioseo_filter_twitter_tags' );


/**
 * Set rank math seo title.
 *
 * @param string $title Title.
 */
function wtai_rankmath_filter_title( $title ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id     = $post->ID;
		$title_check = get_post_meta( $post_id, 'rank_math_title', true );
		if ( $title_check ) {
			$title = wp_strip_all_tags( $title_check );
		}
	}

	return $title;
}
add_filter( 'rank_math/frontend/title', 'wtai_rankmath_filter_title' );

/**
 * Set rank math seo description.
 *
 * @param string $description Description.
 */
function wtai_rankmath_filter_description( $description ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id           = $post->ID;
		$description_check = get_post_meta( $post_id, 'rank_math_description', true );
		if ( $description_check ) {
			$description = wp_strip_all_tags( $description_check );
		}
	}

	return $description;
}
add_filter( 'rank_math/frontend/description', 'wtai_rankmath_filter_description' );

/**
 * Set rank math OG description.
 *
 * @param string $description Description.
 */
function wtai_rankmath_filter_facebook_desc( $description ) {
	global $post;

	if ( 'product' === get_post_type( $post ) && ! is_tax( 'product_cat' ) ) {
		$post_id           = $post->ID;
		$description_check = get_post_meta( $post_id, 'rank_math_facebook_description', true );
		if ( $description_check ) {
			$description = wp_strip_all_tags( $description_check );
		}
	}

	return $description;
}
add_filter( 'rank_math/opengraph/facebook/og:description', 'wtai_rankmath_filter_facebook_desc' );

/**
 * Get product location code.
 *
 * @param int $product_id Product id.
 */
function wtai_get_product_location_code( $product_id = 0 ) {
	$location_code = 0;
	if ( $product_id ) {
		$location_code = get_post_meta( $product_id, 'wtai_keyword_location_code', true );
	}

	return $location_code;
}

/**
 * Get product reference id.
 *
 * @param int $product_id Product id.
 */
function wtai_get_product_reference_id( $product_id = 0 ) {
	$product_reference_id = 0;
	if ( $product_id ) {
		$product_reference_id = get_post_meta( $product_id, 'wtai_product_reference_id', true );
	}

	return $product_reference_id;
}

/**
 * Get product reference list.
 *
 * @param array  $excluded_product_ids Excluded product ids.
 * @param string $search_term Search term.
 * @param int    $per_page Per page.
 * @param int    $page Page number.
 */
function wtai_get_reference_product_list( $excluded_product_ids = array(), $search_term = '', $per_page = 50, $page = 1 ) {
	$fields = apply_filters( 'wtai_fields', array() );
	$fields = array_keys( $fields );

	global $wpdb;
	$seo_meta_key_title      = wtai_get_meta_key_source( 'title' );
	$seo_meta_key_page_desc  = wtai_get_meta_key_source( 'desc' );
	$seo_meta_key_open_graph = wtai_get_meta_key_source( 'opengraph' );

	$post__in = array();
	if ( $search_term ) {
		$data_store = WC_Data_Store::load( 'product' );
		$post__in   = $data_store->search_products( wc_clean( wp_unslash( $search_term ) ), '', true, true, null, null, $excluded_product_ids );
	}

	$additional_cond = '';
	if ( $post__in ) {
		$query_args_product_ids  = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post__in'       => $post__in,
			'fields'         => 'ids',
		);
		$result_product_ids_init = new WP_Query( $query_args_product_ids );
		$result_product_ids      = $result_product_ids_init->posts;
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$result_product_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_content != '' AND post_excerpt != '' AND post_type = 'product' " );
	}

	$products_with_complete_data_ids = array();
	if ( $result_product_ids ) {
		$query_args_meta1_product_ids = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post__in'       => $result_product_ids,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => $seo_meta_key_title,
					'value'   => '',
					'compare' => '!=',
				),
			),
			'fields'         => 'ids',
		);
		$meta1_product_ids_init       = new WP_Query( $query_args_meta1_product_ids );
		$meta1_product_ids            = $meta1_product_ids_init->posts;

		if ( $meta1_product_ids ) {
			$query_args_meta2_product_ids = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post__in'       => $meta1_product_ids,
				'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					array(
						'key'     => $seo_meta_key_page_desc,
						'value'   => '',
						'compare' => '!=',
					),
				),
				'fields'         => 'ids',
			);
			$meta2_product_ids_init       = new WP_Query( $query_args_meta2_product_ids );
			$meta2_product_ids            = $meta2_product_ids_init->posts;

			if ( $meta2_product_ids ) {
				$query_args_meta3_product_ids    = array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'post__in'       => $meta2_product_ids,
					'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
						array(
							'key'     => $seo_meta_key_open_graph,
							'value'   => '',
							'compare' => '!=',
						),
					),
					'fields'         => 'ids',
				);
				$meta3_product_ids_init          = new WP_Query( $query_args_meta3_product_ids );
				$products_with_complete_data_ids = $meta3_product_ids_init->posts;
			}
		}
	}

	// list woocomerce products.
	$query_args = array(
		'post_type'      => 'product',
		'posts_per_page' => $per_page,
		'paged'          => $page,
		'post_status'    => 'any',
		'orderby'        => 'title',
		'order'          => 'ASC',
	);

	if ( $products_with_complete_data_ids ) {
		foreach ( $products_with_complete_data_ids as $cp_index => $cp_id ) {
			if ( in_array( $cp_id, $excluded_product_ids, true ) ) {
				unset( $products_with_complete_data_ids[ $cp_index ] );
			}
		}

		$query_args['post__in'] = $products_with_complete_data_ids;
	} else {
		$query_args['post__in'] = array( 0 );
	}

	$products       = new WP_Query( $query_args );
	$products_posts = $products->posts;

	$products_list = array();
	if ( $products_posts ) {
		foreach ( $products_posts as $product ) {

			$product_id   = $product->ID;
			$product_name = $product->post_title;

			$post_content = wtai_clean_up_html_string( $product->post_content );
			$post_excerpt = wtai_clean_up_html_string( $product->post_excerpt );

			$products_list[ $product_id ] = array(
				'name'        => $product_name,
				'description' => $post_content,
				'excerpt'     => $post_excerpt,
			);
		}

		wp_reset_postdata();
	}

	$output = array(
		'products'    => $products_list,
		'total_count' => $products->found_posts,
	);

	return $output;
}

/**
 * Record product last activity.
 *
 * @param int    $product_id (default: 0).
 * @param string $type (generate|transfer|edited).
 */
function wtai_record_product_last_activity( $product_id = 0, $type = 'generate' ) {
	if ( ! $product_id ) {
		return;
	}

	$last_activity_date = current_time( 'mysql' );

	update_post_meta( $product_id, 'wtai_last_activity_date', $last_activity_date );
	update_post_meta( $product_id, 'wtai_last_activity', $type );
}

/**
 * Record category last activity.
 *
 * @param int    $category_id (default: 0).
 * @param string $type (generate|transfer|edited).
 */
function wtai_record_category_last_activity( $category_id = 0, $type = 'generate' ) {
	if ( ! $category_id ) {
		return;
	}

	$last_activity_date = current_time( 'mysql' );

	update_term_meta( $category_id, 'wtai_last_activity_date', $last_activity_date );
	update_term_meta( $category_id, 'wtai_last_activity', $type );
}

/**
 * Record product field last activity.
 *
 * @param int    $product_id (default: 0).
 * @param string $type (generate|transfer|edited).
 * @param string $field (title|description|excerpt).
 */
function wtai_record_product_field_last_activity( $product_id = 0, $type = 'generate', $field = '' ) {
	if ( ! $product_id ) {
		return;
	}

	$last_activity_date = current_time( 'mysql' );

	update_post_meta( $product_id, 'wtai_last_activity_date_' . $field, $last_activity_date );
	update_post_meta( $product_id, 'wtai_last_activity_' . $field, $type );
}

/**
 * Record category field last activity.
 *
 * @param int    $category_id (default: 0).
 * @param string $type (generate|transfer|edited).
 * @param string $field (title|description|excerpt).
 */
function wtai_record_category_field_last_activity( $category_id = 0, $type = 'generate', $field = '' ) {
	if ( ! $category_id ) {
		return;
	}

	$last_activity_date = current_time( 'mysql' );

	update_term_meta( $category_id, 'wtai_last_activity_date_' . $field, $last_activity_date );
	update_term_meta( $category_id, 'wtai_last_activity_' . $field, $type );
}

/**
 * Prevent preview URL redirect.
 */
function wtai_prevent_preview_url_redirects() {
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( is_preview() && isset( $_GET['wtai-preview'] ) ) {
		add_filter( 'redirect_canonical', '__return_false' );
	}
}
add_action( 'template_redirect', 'wtai_prevent_preview_url_redirects' );

/**
 * Redirect preview if not logged in.
 */
function wtai_redirect_previewlink_if_not_logged_in() {
	// Check if user is not logged in.
	if ( ! is_user_logged_in() ) {
		// Get the current URL.
		$host = '';
		if ( isset( $_SERVER['HTTP_HOST'] ) ) {
			$host = sanitize_url( wp_unslash( $_SERVER['HTTP_HOST'] ) );
		}

		$request_uri = '';
		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$request_uri = sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) );
		}

		$current_url = $host . $request_uri;

		// Check if the current URL contains the directory and the parameter.
		// phpcs:ignore WordPress.Security.NonceVerification
		if ( false !== strpos( $current_url, '?wtai-preview' ) && isset( $_GET['wtai-preview'] ) && 'true' === $_GET['wtai-preview'] ) {
			// Remove the 'wtai-preview' parameter from the URL.
			$redirect_url = remove_query_arg( 'wtai-preview', $current_url );

			// Redirect to the modified URL.
			wp_safe_redirect( $redirect_url );
			exit();
		}
	}
}
add_action( 'template_redirect', 'wtai_redirect_previewlink_if_not_logged_in' );

/**
 * Get current user style preference.
 */
function wtai_get_user_default_product_style() {
	$current_user_id = get_current_user_id();
	$style           = get_user_meta( $current_user_id, 'wtai_styles_options_user_preference', true );
	if ( ! $style ) {
		$style = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( 'styles' ) );
	}

	return $style;
}

/**
 * Check if user token is active.
 */
function wtai_is_token_active() {
	$wtai_api_token = get_option( 'wtai_api_token', '' );
	if ( $wtai_api_token ) {
		$time = get_option( 'wtai_api_token_time', '' );
		if ( $time && $time > strtotime( 'now' ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Check if user token is expired.
 */
function wtai_is_token_expired() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Only do checking if we are on these pages.
	// phpcs:ignore WordPress.Security.NonceVerification
	if ( isset( $_GET['page'] ) &&
			( 'write-text-ai' === $_GET['page'] || 'write-text-ai-settings' === $_GET['page'] || 'write-text-ai-category' === $_GET['page'] ) // phpcs:ignore WordPress.Security.NonceVerification
		) {
		$is_token_expired_checked_transient = get_transient( 'wtai_api_token_expired_checked' );
		$is_token_expired_transient         = get_transient( 'wtai_api_token_expired' );

		if ( defined( 'WTAI_DOING_INSTALLATION' ) && WTAI_DOING_INSTALLATION ) {
			$is_token_expired_checked_transient = false;
		}

		if ( ! $is_token_expired_checked_transient ) {
			$wtai_api_token = get_option( 'wtai_api_token', '' );

			$is_expired = false;
			if ( false === wtai_is_token_active() && $wtai_api_token ) {
				$is_expired = true;
			} elseif ( $wtai_api_token ) {
				$is_text_call_expired = apply_filters( 'wtai_validate_etag_token_expired', false );

				if ( ! $is_text_call_expired ) {
					$access_token = apply_filters( 'wtai_check_connect_token_api', '', $wtai_api_token );

					if ( ! $access_token ) {
						$is_expired = true;
					}
				} else {
					$is_expired = true;
				}
			}

			if ( $is_expired ) {
				// reset token time.
				update_option( 'wtai_api_token_last_checked', '' );
				update_option( 'wtai_api_token_time', '' );
			}

			set_transient( 'wtai_api_token_expired_checked', '1', MINUTE_IN_SECONDS * 1 );
			set_transient( 'wtai_api_token_expired', $is_expired, MINUTE_IN_SECONDS * 1 );
		} else {
			$is_expired = $is_token_expired_transient;
		}

		return $is_expired;
	} else {
		return;
	}
}

/**
 * Get API base URL.
 *
 * @param bool $check_fallback Whether to check fallback.
 */
function wtai_get_api_base_url( $check_fallback = true ) {
	$api_base_url = get_option( 'wtai_api_base_url', '' );

	if ( ! $api_base_url && $check_fallback ) {
		$api_base_url = WTAI_API_HOST;
	}

	return $api_base_url;
}

/**
 * Check if base url is set.
 */
function wtai_has_api_base_url() {
	$api_base_url = wtai_get_api_base_url( false );
	if ( $api_base_url ) {
		return true;
	}

	return false;
}

/**
 * Get login callback url.
 */
function wtai_get_login_callback_url() {
	$region = apply_filters( 'wtai_get_api_region', '' );

	$login_callback_url  = 'https://' . WTAI_AUTH_HOST . '/?redirect=true&callback_url=' . rawurlencode( admin_url( 'admin.php?page=write-text-ai' ) );
	$login_callback_url .= '&region=' . rawurlencode( $region );
	$login_callback_url .= '&platform=WordPress';
	$login_callback_url .= '&verification_url=' . rawurlencode( site_url() );

	return $login_callback_url;
}

/**
 * Get IP Addres.
 */
function wtai_get_ip_address() {
	// function to get ip address.
	$ip_address = '';

	if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
	} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) );
	} elseif ( ! empty( $_SERVER['REMOTE_ADDR'] ) ) {
		$ip_address = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
	}

	return $ip_address;
}

/**
 * Get user preference tones.
 */
function wtai_get_user_preference_tones() {
	$current_user_id = get_current_user_id();

	$tones            = get_user_meta( $current_user_id, 'wtai_tones_options_user_preference', true );
	$custom_tone      = get_user_meta( $current_user_id, 'wtai_tones_custom_user_preference', true );
	$custom_tone_text = get_user_meta( $current_user_id, 'wtai_tones_custom_text_user_preference', true );

	if ( $custom_tone_text ) {
		$tones   = array();
		$tones[] = 'wtaCustom::' . $custom_tone_text;
	} elseif ( ! $tones || ( isset( $tones[0] ) && '' === $tones[0] ) ) {
		$tones = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( 'tones' ) );
	}

	return $tones;
}

/**
 * Get user preference styles.
 */
function wtai_get_user_preference_styles() {
	$current_user_id = get_current_user_id();

	$styles            = get_user_meta( $current_user_id, 'wtai_styles_options_user_preference', true );
	$custom_style      = get_user_meta( $current_user_id, 'wtai_styles_custom_user_preference', true );
	$custom_style_text = get_user_meta( $current_user_id, 'wtai_styles_custom_text_user_preference', true );

	if ( $custom_style_text ) {
		$styles = 'wtaCustom::' . $custom_style_text;
	} elseif ( ! $styles || '' === $styles ) {
		$styles = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( 'styles' ) );
	}

	return $styles;
}

/**
 * Get user preference audiences.
 */
function wtai_get_user_preference_audiences() {
	$current_user_id = get_current_user_id();

	$audiences = get_user_meta( $current_user_id, 'wtai_audiences_options_user_preference', true );

	if ( ! $audiences ) {
		$audiences = apply_filters( 'wtai_global_settings', 'wtai_installation_' . strtolower( 'audiences' ) );
	}

	return $audiences;
}

/**
 * Get product preference attributes.
 *
 * @param int $product_id Product ID.
 */
function wtai_get_product_attribute_preference( $product_id = 0 ) {
	if ( ! $product_id ) {
		return array();
	}

	$product_attributes = get_post_meta( $product_id, 'wtai_product_attribute_preference', true );

	if ( $product_attributes ) {
		$product_attributes = array_filter( $product_attributes );
		$product_attributes = array_values( $product_attributes );
	}

	return $product_attributes;
}

/**
 * Get generation API rules/varialbles.
 *
 * @param array $global_rule_fields Global rule fields.
 * @param array $credit_vars Credit vars.
 */
function wtai_get_generation_limit_vars( $global_rule_fields = array(), $credit_vars = array() ) {
	if ( ! $global_rule_fields ) {
		$global_rule_fields = apply_filters( 'wtai_global_rule_fields', array() );
	}

	if ( ! $credit_vars ) {
		$credit_vars = apply_filters( 'wtai_get_credits_count', array() );
	}

	// set default values.
	$generation_limit_vars = array(
		'wordsPerCredit'                        => 200,
		'maxReferenceTextPageTitleLength'       => 250,
		'maxReferenceTextPageDescriptionLength' => 400, // applied to page desc and og desc.
		'maxReferenceTextLength'                => 1200, // for reference and rewrite generation.
		'additionalReferenceTextLength'         => 1600, // next tier of reference and rewrite generation.
		'maxSemanticKeywords'                   => 10,
		'maxCustomToneLength'                   => 50,
		'maxCustomStyleLength'                  => 50,
		'maxCustomAudienceLength'               => 150,
		'maxKeywordLength'                      => 35,
		'maxOtherDetailsLength'                 => 250,
		'minOutputWords'                        => 5,
		'maxOutputWords'                        => 2000,
		'altText'                               => 1,
		'maxImageAltTextPerRequest'             => 20,
	);

	// fetch from api dynamic values.
	// TODO: ensure that values are populated from api.
	if ( isset( $credit_vars['wordsPerCredit'] ) ) {
		$generation_limit_vars['wordsPerCredit'] = $credit_vars['wordsPerCredit'];
	}
	if ( isset( $credit_vars['altText'] ) ) {
		$generation_limit_vars['altText'] = $credit_vars['altText'];
	}

	foreach ( $generation_limit_vars as $key => $value ) {
		if ( 'wordsPerCredit' !== $key ) {
			if ( isset( $global_rule_fields[ $key ] ) ) {
				$generation_limit_vars[ $key ] = $global_rule_fields[ $key ];
			}
		}
	}

	// limit vars from settings.
	$generation_limit_vars['prodDescMaxWordLength']    = apply_filters( 'wtai_global_settings', 'wtai_installation_product_description_max' );
	$generation_limit_vars['prodExcerptMaxWordLength'] = apply_filters( 'wtai_global_settings', 'wtai_installation_product_excerpt_max' );

	// get max input character length for product short description and product description.
	$max_loop                = ceil( $generation_limit_vars['maxOutputWords'] / $generation_limit_vars['wordsPerCredit'] );
	$reference_credit_matrix = array();
	$input_char_length       = 0;
	$word_length             = 0;
	for ( $c = 1; $c <= $max_loop; $c++ ) {
		if ( 1 === $c ) {
			$input_char_length = $input_char_length + $generation_limit_vars['maxReferenceTextLength'];
		} else {
			$input_char_length = $input_char_length + $generation_limit_vars['additionalReferenceTextLength'];
		}

		$word_length += $generation_limit_vars['wordsPerCredit'];

		$reference_credit_matrix[] = array(
			'creditCount'     => $c,
			'inputCharLength' => $input_char_length,
			'wordLength'      => $word_length,
		);
	}

	krsort( $reference_credit_matrix );

	$generation_limit_vars['referenceCreditMatrix']            = $reference_credit_matrix;
	$generation_limit_vars['maxReferenceInputCharacterLength'] = $reference_credit_matrix[ count( $reference_credit_matrix ) - 1 ]['inputCharLength'];

	return $generation_limit_vars;
}

/**
 * Get words array text.
 *
 * @param string $text Text to get words array.
 */
function wtai_get_words_array( $text ) {
	$text = preg_replace( "/'/", '', $text ); // Remove all single quotes.

	$words = preg_match_all( "/\b(?:\w+(?:[.-]\w+)*|\w+)\b/", strtolower( $text ), $matches );

	if ( false === $words || false === $matches ) {
		return array();
	}

	$words = $matches[0];

	return $words;
}

/**
 * Get word count/length.
 *
 * @param string $text String to get word count.
 */
function wtai_word_count( $text = '' ) {
	$words = wtai_get_words_array( $text );

	return count( $words );
}

/**
 * Get not allowed keywords
 */
function wtai_get_not_allowed_keywords() {
	$array = array(
		'-',
		'*',
		'#',
		'$',
		'@',
		'.',
		'>',
		'<',
		'|',
		'%',
		'"',
		'"',
	);

	return $array;
}

/**
 * Check if formal/informal language is supported.
 */
function wtai_is_formal_informal_lang_supported() {
	$languages           = apply_filters( 'wtai_generate_text_filters', array(), 'FormalLanguageSupport', false );
	$current_lang_locale = apply_filters( 'wtai_language_code', wtai_get_site_language() );
	$current_lang_arr    = explode( '_', $current_lang_locale );
	$current_lang        = $current_lang_arr[0];

	$is_supported = false;
	if ( in_array( $current_lang, $languages, true ) ) {
		$is_supported = true;
	}

	return $is_supported;
}

/**
 * Get disallowed combination tooltip messahe.
 *
 * @param string $option_id Option ID.
 * @param string $option_type Option Type.
 */
function wtai_get_disallowed_combination_tooltip_message( $option_id = '', $option_type = '' ) {
	if ( '' === $option_id || '' === $option_type ) {
		return '';
	}

	$parse_option_type = $option_type;
	if ( 'styles' === $option_type ) {
		$parse_option_type = 'style';
	} elseif ( 'tones' === $option_type ) {
		$parse_option_type = 'tone';
	} elseif ( 'audiences' === $option_type ) {
		$parse_option_type = 'audience';
	}

	$disallowed_combinations_api = apply_filters( 'wtai_get_disallowed_combinations', array(), false );

	$tooltip_messages        = array();
	$selected_combinations   = array();
	$disallowed_combinations = array();

	if ( $disallowed_combinations_api ) {
		$filter_types = array(
			'tones',
			'styles',
			'audiences',
		);

		$filter_type_values = array();
		foreach ( $filter_types as $filter_type ) {
			$lists = apply_filters( 'wtai_generate_text_filters', array(), ucfirst( $filter_type ), '' );
			foreach ( $lists as $list_key => $list_label ) {
				$filter_type_values[ $filter_type ][ $list_key ] = $list_label;
			}
		}

		foreach ( $disallowed_combinations_api as $combination_index => $combination_data ) {
			if ( isset( $combination_data['combination'] ) ) {
				$combination_array = $combination_data['combination'];

				foreach ( $combination_array as $combination ) {
					if ( strtolower( $combination['type'] ) === $parse_option_type && $combination['id'] === $option_id ) {
						$selected_combinations[] = $combination;
					}

					if ( strtolower( $combination['type'] ) !== $parse_option_type && $combination['id'] !== $option_id ) {
						$disallowed_combinations[] = $combination;
					}
				}

				if ( $selected_combinations && $disallowed_combinations ) {
					foreach ( $disallowed_combinations as $combination ) {
						$type_key = '-';
						if ( 'style' === strtolower( $combination['type'] ) ) {
							$type_key = 'styles';
						} elseif ( 'tone' === strtolower( $combination['type'] ) ) {
							$type_key = 'tones';
						} elseif ( 'audience' === strtolower( $combination['type'] ) ) {
							$type_key = 'audiences';
						}

						$combination_label = isset( $filter_type_values[ $type_key ][ $combination['id'] ] ) ? $filter_type_values[ $type_key ][ $combination['id'] ] : $combination['id'];

						if ( 'style' === strtolower( $combination['type'] ) ) {
							/* translators: %s: combination label */
							$tooltip_messages[] = sprintf( __( 'Unavailable when the "%s" style is selected.', 'writetext-ai' ), $combination_label );
						} elseif ( 'tone' === strtolower( $combination['type'] ) ) {
							/* translators: %s: combination label */
							$tooltip_messages[] = sprintf( __( 'Unavailable when the "%s" tone is selected.', 'writetext-ai' ), $combination_label );
						} elseif ( 'audience' === strtolower( $combination['type'] ) ) {
							/* translators: %s: combination label */
							$tooltip_messages[] = sprintf( __( 'Unavailable when the "%s" audience is selected.', 'writetext-ai' ), $combination_label );
						}
					}
				}
			}
		}
	}

	return $tooltip_messages;
}

/**
 * Get hide guidelines user preference
 *
 * @param string $type Type.
 */
function wtai_get_hide_guidelines_user_preference( $type = 'product' ) {
	$current_user_id = get_current_user_id();

	$is_hidden = false;

	if ( 'category' === $type ) {
		$wtai_hide_guidelines = get_user_meta( $current_user_id, 'wtai_hide_category_guidelines', true );
	} else {
		$wtai_hide_guidelines = get_user_meta( $current_user_id, 'wtai_hide_guidelines', true );
	}

	if ( '1' === $wtai_hide_guidelines ) {
		$is_hidden = true;
	}

	return $is_hidden;
}

/**
 * Get product last activity
 *
 * @param int $product_id Product ID.
 * @return string $last_activity last activity.
 */
function wtai_get_product_last_activity( $product_id = 0 ) {
	if ( ! $product_id ) {
		return;
	}

	$last_activity = get_post_meta( $product_id, 'wtai_last_activity', true );

	return $last_activity;
}

/**
 * Get category last activity
 *
 * @param int $category_id Category ID.
 * @return string $last_activity last activity.
 */
function wtai_get_category_last_activity( $category_id = 0 ) {
	if ( ! $category_id ) {
		return;
	}

	$last_activity = get_term_meta( $category_id, 'wtai_last_activity', true );

	return $last_activity;
}

/**
 * Get the localized country code for the current site
 *
 * @param int    $product_id Product ID.
 * @param string $field Field type.
 */
function wtai_get_product_field_last_activity( $product_id = 0, $field = '' ) {
	if ( ! $product_id || '' === $field ) {
		return;
	}

	$last_activity = get_post_meta( $product_id, 'wtai_last_activity_' . $field, true );

	return $last_activity;
}

/**
 * Get the localized countries for the current site
 */
function wtai_get_site_localized_countries() {
	$default_locale       = apply_filters( 'wtai_language_code', get_locale() );
	$default_locale_array = explode( '_', $default_locale );
	$lang                 = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

	$country_init = wtai_get_country_per_language( $lang );

	$countries = array();
	if ( $country_init ) {
		foreach ( $country_init as $country_lang ) {
			$countries[] = $country_lang['country'];
		}
	}

	return $countries;
}

/**
 * Check if the localized country has been set before in the DB
 *
 * @param string $lang Language code.
 */
function wtai_is_site_localized_countries_set( $lang = '' ) {
	if ( ! $lang ) {
		$default_locale       = apply_filters( 'wtai_language_code', get_locale() );
		$default_locale_array = explode( '_', $default_locale );
		$lang                 = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.
	}

	$is_set = get_option( 'wtai_localized_countries_enabled', array() );

	if ( $is_set && is_array( $is_set ) && in_array( $lang, $is_set, true ) ) {
		$is_set = true;
	} else {
		$is_set = false;
	}

	return $is_set;
}

/**
 * Get country mapping per language
 *
 * @param string $lang Language code.
 */
function wtai_get_country_per_language( $lang = '' ) {
	$wtai_localized_countries = get_option( 'wtai_localized_countries', array() );

	$language_country_mapping = array();
	if ( $wtai_localized_countries ) {
		foreach ( $wtai_localized_countries as $data ) {
			if ( is_array( $data ) && isset( $data['lang'] ) && isset( $data['country'] ) ) {
				if ( $lang ) {
					if ( $data['lang'] === $lang ) {
						$language_country_mapping[] = $data;
					}
				} else {
					$language_country_mapping[] = $data;
				}
			}
		}
	}

	return $language_country_mapping;
}

/**
 * Set languages were country has been set
 */
function wtai_set_localized_country_enabled() {
	$default_locale       = apply_filters( 'wtai_language_code', get_locale() );
	$default_locale_array = explode( '_', $default_locale );
	$lang                 = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.

	$set_countries_per_language = get_option( 'wtai_localized_countries_enabled', array() );

	// Lets force this option to be an array.
	if ( ! is_array( $set_countries_per_language ) ) {
		$set_countries_per_language = array();
	}

	$set_countries_per_language[] = $lang;
	$set_countries_per_language   = array_unique( $set_countries_per_language );
	$set_countries_per_language   = array_values( $set_countries_per_language );

	update_option( 'wtai_localized_countries_enabled', $set_countries_per_language );
}

/**
 * Set country mapping per language
 *
 * @param string $lang Language code.
 * @param string $country Country code.
 */
function wtai_set_country_per_language( $lang = '', $country = '' ) {
	if ( ! $lang || ! $country ) {
		return;
	}

	$language_country_mapping_current = wtai_get_country_per_language();

	$language_country_mapping = array();
	if ( $language_country_mapping_current ) {
		if ( ! is_array( $language_country_mapping_current ) ) {
			$language_country_mapping_current = array();
		}

		$data_updated = false;
		foreach ( $language_country_mapping_current as $data ) {
			if ( is_array( $data ) && isset( $data['lang'] ) ) {
				if ( $lang && $data['lang'] === $lang ) {
					$language_country_mapping[ $lang ] = array(
						'lang'    => $lang,
						'country' => $country,
					);

					$data_updated = true;
				} else {
					$language_country_mapping[ $data['lang'] ] = $data;
				}
			}
		}

		if ( ! $data_updated ) {
			$language_country_mapping[ $lang ] = array(
				'lang'    => $lang,
				'country' => $country,
			);
		}
	} else {
		$language_country_mapping          = array();
		$language_country_mapping[ $lang ] = array(
			'lang'    => $lang,
			'country' => $country,
		);
	}

	$language_country_mapping = array_values( $language_country_mapping );

	update_option( 'wtai_localized_countries', $language_country_mapping );

	return $language_country_mapping;
}

/**
 * Get the localized country code for the current site
 *
 * @param string $language_code Language Code.
 */
function wtai_match_language_locale( $language_code ) {
	$parsed_language_locale = $language_code;
	switch ( $language_code ) {
		case 'ca':
			$parsed_language_locale = 'ca_ES';
			break;
		case 'af':
			$parsed_language_locale = 'af_ZA';
			break;
		case 'sq':
			$parsed_language_locale = 'sq_AL';
			break;
		case 'arq':
			$parsed_language_locale = 'arq_DZ';
			break;
		case 'ak':
			$parsed_language_locale = 'ak_GH';
			break;
		case 'am':
			$parsed_language_locale = 'am_ET';
			break;
		case 'ar':
			$parsed_language_locale = 'ar_SA';
			break;
		case 'hy':
			$parsed_language_locale = 'hy_AM';
			break;
		case 'frp':
			$parsed_language_locale = 'frp_FR';
			break;
		case 'as':
			$parsed_language_locale = 'as_IN';
			break;
		case 'ast':
			$parsed_language_locale = 'ast_ES';
			break;
		case 'az':
			$parsed_language_locale = 'az_AZ';
			break;
		case 'bcc':
			$parsed_language_locale = 'bcc_PK';
			break;
		case 'ba':
			$parsed_language_locale = 'ba_RU';
			break;
		case 'eu':
			$parsed_language_locale = 'eu_ES';
			break;
		case 'bel':
			$parsed_language_locale = 'bel_BY';
			break;
		case 'bho':
			$parsed_language_locale = 'bho_IN';
			break;
		case 'brx':
			$parsed_language_locale = 'brx_IN';
			break;
		case 'gax':
			$parsed_language_locale = 'gax_ET';
			break;
		case 'br':
			$parsed_language_locale = 'br_FR';
			break;
		case 'bal':
			$parsed_language_locale = 'bal_ES';
			break;
		case 'ceb':
			$parsed_language_locale = 'ceb_PH';
			break;
		case 'cor':
			$parsed_language_locale = 'cor_GB';
			break;
		case 'co':
			$parsed_language_locale = 'co_FR';
			break;
		case 'hr':
			$parsed_language_locale = 'hr_HR';
			break;
		case 'dv':
			$parsed_language_locale = 'dv_MV';
			break;
		case 'dzo':
			$parsed_language_locale = 'dzo_BT';
			break;
		case 'en-sa':
			$parsed_language_locale = 'en-sa_ZA';
			break;
		case 'et':
			$parsed_language_locale = 'et_EE';
			break;
		case 'ewe':
			$parsed_language_locale = 'ewe_GH';
			break;
		case 'fo':
			$parsed_language_locale = 'fo_FO';
			break;
		case 'fi':
			$parsed_language_locale = 'fi_FI';
			break;
		case 'fon':
			$parsed_language_locale = 'fon_BJ';
			break;
		case 'fy':
			$parsed_language_locale = 'fy_NL';
			break;
		case 'fur':
			$parsed_language_locale = 'fur_IT';
			break;
		case 'fuc':
			$parsed_language_locale = 'fuc_NG';
			break;
		case 'el':
			$parsed_language_locale = 'el_GR';
			break;
		case 'kal':
			$parsed_language_locale = 'kal_GL';
			break;
		case 'gn':
			$parsed_language_locale = 'gn_PY';
			break;
		case 'hat':
			$parsed_language_locale = 'hat_HT';
			break;
		case 'hau':
			$parsed_language_locale = 'hau_NG';
			break;
		case 'haz':
			$parsed_language_locale = 'haz_AF';
			break;
		case 'ibo':
			$parsed_language_locale = 'ibo_NG';
			break;
		case 'ga':
			$parsed_language_locale = 'ga_IE';
			break;
		case 'ja':
			$parsed_language_locale = 'ja_JP';
			break;
		case 'kab':
			$parsed_language_locale = 'kab_DZ';
			break;
		case 'kn':
			$parsed_language_locale = 'kn_IN';
			break;
		case 'kaa':
			$parsed_language_locale = 'kaa_UZ';
			break;
		case 'kk':
			$parsed_language_locale = 'kk_KZ';
			break;
		case 'km':
			$parsed_language_locale = 'km_KH';
			break;
		case 'kin':
			$parsed_language_locale = 'kin_RW';
			break;
		case 'ckb':
			$parsed_language_locale = 'ckb_IQ';
			break;
		case 'kmr':
			$parsed_language_locale = 'kmr_TR';
			break;
		case 'kir':
			$parsed_language_locale = 'kir_KG';
			break;
		case 'lo':
			$parsed_language_locale = 'lo_LA';
			break;
		case 'lv':
			$parsed_language_locale = 'lv_LV';
			break;
		case 'lij':
			$parsed_language_locale = 'lij_IT';
			break;
		case 'li':
			$parsed_language_locale = 'li_NL';
			break;
		case 'lin':
			$parsed_language_locale = 'lin_CG';
			break;
		case 'lmo':
			$parsed_language_locale = 'lmo_IT';
			break;
		case 'dsb':
			$parsed_language_locale = 'dsb_DE';
			break;
		case 'lug':
			$parsed_language_locale = 'lug_UG';
			break;
		case 'mai':
			$parsed_language_locale = 'mai_IN';
			break;
		case 'mlt':
			$parsed_language_locale = 'mlt_MT';
			break;
		case 'mri':
			$parsed_language_locale = 'mri_NZ';
			break;
		case 'mfe':
			$parsed_language_locale = 'mfe_MU';
			break;
		case 'mr':
			$parsed_language_locale = 'mr_IN';
			break;
		case 'xmf':
			$parsed_language_locale = 'xmf_GE';
			break;
		case 'mn':
			$parsed_language_locale = 'mn_MN';
			break;
		case 'ary':
			$parsed_language_locale = 'ary_MA';
			break;
		case 'pcm':
			$parsed_language_locale = 'pcm_NG';
			break;
		case 'oci':
			$parsed_language_locale = 'oci_FR';
			break;
		case 'ory':
			$parsed_language_locale = 'ory_IN';
			break;
		case 'os':
			$parsed_language_locale = 'os_RU';
			break;
		case 'ps':
			$parsed_language_locale = 'ps_AF';
			break;
		case 'rhg':
			$parsed_language_locale = 'rhg_MM';
			break;
		case 'roh':
			$parsed_language_locale = 'roh_CH';
			break;
		case 'rue':
			$parsed_language_locale = 'rue_UA';
			break;
		case 'sah':
			$parsed_language_locale = 'sah_RU';
			break;
		case 'skr':
			$parsed_language_locale = 'skr_PK';
			break;
		case 'srd':
			$parsed_language_locale = 'srd_IT';
			break;
		case 'gd':
			$parsed_language_locale = 'gd_GB';
			break;
		case 'sna':
			$parsed_language_locale = 'sna_ZW';
			break;
		case 'scn':
			$parsed_language_locale = 'scn_IT';
			break;
		case 'szl':
			$parsed_language_locale = 'szl_PL';
			break;
		case 'azb':
			$parsed_language_locale = 'azb_IR';
			break;
		case 'ssw':
			$parsed_language_locale = 'ssw_SZ';
			break;
		case 'sw':
			$parsed_language_locale = 'sw_TZ';
			break;
		case 'gsw':
			$parsed_language_locale = 'gsw_CH';
			break;
		case 'syr':
			$parsed_language_locale = 'syr_SY';
			break;
		case 'tl':
			$parsed_language_locale = 'tl_PH';
			break;
		case 'tah':
			$parsed_language_locale = 'tah_PF';
			break;
		case 'tg':
			$parsed_language_locale = 'tg_TJ';
			break;
		case 'tzm':
			$parsed_language_locale = 'tzm_MA';
			break;
		case 'zgh':
			$parsed_language_locale = 'zgh_MA';
			break;
		case 'te':
			$parsed_language_locale = 'te_IN';
			break;
		case 'th':
			$parsed_language_locale = 'th_TH';
			break;
		case 'bo':
			$parsed_language_locale = 'bo_CN';
			break;
		case 'tir':
			$parsed_language_locale = 'tir_ET';
			break;
		case 'tuk':
			$parsed_language_locale = 'tuk_TM';
			break;
		case 'twd':
			$parsed_language_locale = 'twd_NL';
			break;
		case 'uk':
			$parsed_language_locale = 'uk_UA';
			break;
		case 'hsb':
			$parsed_language_locale = 'hsb_DE';
			break;
		case 'ur':
			$parsed_language_locale = 'ur_PK';
			break;
		case 'vec':
			$parsed_language_locale = 'vec_IT';
			break;
		case 'vi':
			$parsed_language_locale = 'vi_VN';
			break;
		case 'wa':
			$parsed_language_locale = 'wa_BE';
			break;
		case 'cy':
			$parsed_language_locale = 'cy_GB';
			break;
		case 'wol':
			$parsed_language_locale = 'wol_SN';
			break;
		case 'xho':
			$parsed_language_locale = 'xho_ZA';
			break;
		case 'yor':
			$parsed_language_locale = 'yor_NG';
			break;
		case 'zul':
			$parsed_language_locale = 'zul_ZA';
			break;
		case 'en':
			$parsed_language_locale = 'en_US';
			break;
	}

	return $parsed_language_locale;
}

/**
 * Get the product status.
 *
 * @param int $post_id The post ID.
 * @return string Status display
 */
function wtai_get_product_wp_status( $post_id = 0 ) {
	if ( ! $post_id ) {
		return;
	}

	$product_status = get_post_status( $post_id );

	$status_display = '';
	switch ( $product_status ) {
		case 'private':
			$status_display = __( 'Privately Published' );
			break;
		case 'publish':
			$status_display = __( 'Published' );
			break;
		case 'future':
			$status_display = __( 'Scheduled' );
			break;
		case 'pending':
			$status_display = __( 'Pending Review' );
			break;
		case 'draft':
		case 'auto-draft':
			$status_display = __( 'Draft' );
			break;
	}

	return $status_display;
}

/**
 * Remove and sanitize current value rewrite and reference text strings.
 *
 * @param int  $html HTML String.
 * @param bool $strip_shortcodes Whether to strip shortcodes or not.
 *
 * @return string Clean and sanitized string for AI.
 */
function wtai_clean_up_html_string( $html = '', $strip_shortcodes = false ) {
	if ( ! $html ) {
		return;
	}

	if ( $strip_shortcodes ) {
		$html = strip_shortcodes( $html );
		$html = preg_replace( '#\[[^\]]+\]#', '', $html );
	}

	$html = str_replace( '<!--more-->', '<p><a href="#"><span>(' . __( 'more...' ) . ')</span></a></p>', $html );

	$allowed_tags = array(
		'ul'         => array(),
		'li'         => array(),
		'ol'         => array(),
		'p'          => array(
			'style' => array(),
		),
		'blockquote' => array(),
		'strong'     => array(),
		'em'         => array(),
		'pre'        => array(),
		'del'        => array(),
		'h1'         => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'h5'         => array(),
		'h6'         => array(),
		'a'          => array(
			'href' => array(),
		),
		'hr'         => array(),
		'br'         => array(),
		'span'       => array(
			'style' => array(),
		),
		'u'          => array(),
	);

	$html = wp_kses( $html, $allowed_tags );

	// Replace or add href attribute in <a> tags.
	$html = preg_replace_callback(
		'/<a([^>]*)>/',
		function ( $matches ) {
			$attributes = $matches[1];
			// Check if href attribute already exists.
			if ( preg_match( '/\bhref\s*=\s*[\'"]([^\'"]*)[\'"]/', $attributes, $href_matches ) ) {
				// Href attribute exists, replace its value with #.
				$new_attributes = preg_replace( '/\bhref\s*=\s*[\'"][^\'"]*[\'"]/', 'href="#"', $attributes );
			} else {
				// Href attribute does not exist, add href="#".
				$new_attributes = $attributes . ' href="#"';
			}
			// Return modified <a> tag.
			return '<a' . $new_attributes . '>';
		},
		$html
	);

	return $html;
}

/**
 * Check if user has premium account.
 */
function wtai_is_premium_account() {
	$web_token = apply_filters( 'wtai_web_token', '' );

	if ( ! $web_token ) {
		return false;
	}

	$settings = array(
		'remote_url' => 'https://' . wtai_get_api_base_url() . '/text/Credit',
	);
	$headers  = array(
		'Cache-Control' => 'no-cache',
		'Authorization' => 'Bearer ' . $web_token,
	);
	$content  = apply_filters( 'wtai_get_data_via_api', array(), array(), $settings, $headers, 'GET' );

	$is_premium = false;
	if ( 200 === intval( $content['http_header'] ) ) {
		$content = json_decode( $content['result'], true );

		if ( isset( $content['isPremium'] ) && ( 'true' === $content['isPremium'] || 1 === intval( $content['isPremium'] ) ) ) {
			$is_premium = true;
		}
	}

	return $is_premium;
}

/**
 * Get ad banner.
 */
function wtai_get_ad_banner() {
	$web_token = apply_filters( 'wtai_web_token', '' );

	if ( ! $web_token ) {
		return array();
	}

	$settings = array(
		'remote_url' => 'https://' . wtai_get_api_base_url() . '/web/ad/SquareWordPress',
	);
	$headers  = array(
		'Cache-Control' => 'no-cache',
		'Authorization' => 'Bearer ' . $web_token,
	);
	$content  = apply_filters( 'wtai_get_data_via_api', array(), array(), $settings, $headers, 'GET' );

	$banner_data = array();
	if ( 200 === intval( $content['http_header'] ) ) {
		$content = json_decode( $content['result'], true );

		if ( isset( $content['imageUrl'] ) ) {
			$banner_data = array(
				'imageUrl' => $content['imageUrl'],
				'link'     => $content['link'],
			);
		}
	}

	return $banner_data;
}

/**
 * Get product image /product galleries.
 *
 * @param int $product_id Product ID.
 */
function wtai_get_product_image( $product_id = 0 ) {
	if ( ! $product_id ) {
		return;
	}

	$product_images = array();
	$image_ctr      = 0;

	// Get the featured image ID.
	$featured_image_id = get_post_thumbnail_id( $product_id );
	if ( $featured_image_id ) {
		$product_images[] = $featured_image_id;

		++$image_ctr;
	}

	// Get the product gallery attachment IDs.
	$gallery_max_limit      = 10; // Temporary limit for the gallery images.
	$gallery_attachment_ids = get_post_meta( $product_id, '_product_image_gallery', true );
	if ( $gallery_attachment_ids ) {
		foreach ( explode( ',', $gallery_attachment_ids ) as $attachment_id ) {
			if ( ! in_array( $attachment_id, $product_images, true ) ) {
				// Check if the attachment ID exists.
				$attachment_metadata = wp_get_attachment_metadata( $attachment_id );

				if ( $attachment_metadata ) {
					if ( $image_ctr >= $gallery_max_limit ) {
						break;
					}

					// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
					if ( ! in_array( $attachment_id, $product_images ) ) {
						$product_images[] = $attachment_id;

						++$image_ctr;
					}
				}
			}
		}
	}

	$product_images = array_unique( $product_images );
	$product_images = array_values( $product_images );

	return $product_images;
}

/**
 * Get image template.
 *
 * @param int    $attachment_id Attahcment ID.
 * @param int    $product_id Product ID.
 * @param bool   $is_featured_image Is featured image.
 * @param string $suffix_text Suffix text.
 * @param string $api_alt_text API Alt text.
 * @param string $api_alt_text_id API Alt text id.
 * @param array  $api_alt_text_history API Alt text history.
 * @param bool   $checked Checked state.
 * @param bool   $is_transferred Transferred state.
 */
function wtai_get_image_template( $attachment_id, $product_id, $is_featured_image = false, $suffix_text = '', $api_alt_text = '', $api_alt_text_id = '', $api_alt_text_history = array(), $checked = false, $is_transferred = false ) {
	$html                    = '';
	$gallery_image_data      = wp_get_attachment_image_src( $attachment_id, array( 89, 89 ) );
	$gallery_image_popup     = wp_get_attachment_image_src( $attachment_id, 'full' );
	$gallery_image_popup_url = $gallery_image_popup[0];

	if ( $gallery_image_data ) {
		$gallery_image_url = $gallery_image_data[0];
		$alt_text          = ''; // This should be from writetext.ai.
		$column_key        = 'image_alt_text';

		$checked_flag = '';
		if ( $checked ) {
			$checked_flag = ' checked ';
		}

		$generated_label = '(' . __( 'not generated', 'writetext-ai' ) . ')';
		if ( '' !== $api_alt_text_id ) {
			$generated_label = '(' . __( 'generated', 'writetext-ai' ) . ')';
		}

		$hide_transfer_label_class = '';
		if ( $is_transferred || '' === $api_alt_text_id ) {
			$hide_transfer_label_class = ' wtai-hide-not-transferred-label ';
		}

		$no_generated_overlay_class = '';
		if ( '' === $api_alt_text_id ) {
			$no_generated_overlay_class = 'tooltipstered wtai-shown';
		}

		$html         .= '<div class="cb_and_image wtai-d-flex align-self-end" data-product-id="' . $product_id . '" >';
				$html .= '<input type="checkbox" value="' . $attachment_id . '" class="wtai-checkboxes-alt wtai-init-fields" ' . $checked_flag . ' />';
				$html .= '<div class="wtai-alt-image" data-popimage="' . $gallery_image_popup_url . '"><img src="' . $gallery_image_data[0] . '" alt="' . $alt_text . '" /><span>' . __( 'View image', 'writetext-ai' ) . '</span></div>';
			$html     .= '</div>';

			$html     .= '<div class="generated_transfer_current_box wtai-d-flex">';
			$html     .= '<div class="wtai-col-row wtai-generate-value-wrapper"><div class="wtai-d-flex wtai-image-alt-text flex-dir-col">';
				$html .= '<label for="' . $attachment_id . '"><span class="wtai-field-name">' . __( 'WriteText.ai image alt text', 'writetext-ai' ) . $suffix_text . '</span> <span class="wtai-generated-status-label" >' . $generated_label . '</span><span class="wtai-alt-transferred-status-label wtai-status-label ' . $hide_transfer_label_class . ' " >' . __( 'Not transferred', 'writetext-ai' ) . '</span></label>';
				$html .= '<input type="hidden" class="wtai-api-data-' . $column_key . '_id" data-postfield="' . $column_key . '_id" name="' . $column_key . '_id" id="wtai-wp-field-input-' . $column_key . '_id" value="' . $api_alt_text_id . '" />';

				$html .= '<div class="wtai-generate-textarea-wrap" >
							<textarea name="' . $column_key . '_' . $attachment_id . '" id="wtai-wp-field-input-' . $column_key . '_' . $attachment_id . '" class="wtai-wp-editor-setup-alt wtai-api-data-' . $column_key . ' string-count-input wp_editor_alt_trigger" data-postfield="' . $column_key . '"  style="resize:none;" disabled >' . $api_alt_text . '</textarea>
							
							<div class="wtai-generate-disable-overlay-wrap ' . $no_generated_overlay_class . '" title="' . __( 'No text generated yet.', 'writetext-ai' ) . '" ></div>

							<div class="wtai-typing-cursor-alt-wrap" ><span class="typing-cursor">&nbsp;</span></div>
						</div>';
		$html         .= '<span class="wtai-text-count-details"><span class="wtai-char-counting"><span class="wtai-char-count" data-count="0">0/' . WTAI_MAX_IMAGE_ALT_TEXT_LIMIT . ' ' . __( 'char', 'writetext-ai' ) . '</span> | <span class="word-count" data-count="0">0 ' . __( 'word/s', 'writetext-ai' ) . '</span></span></span>';
		$html         .= '</div></div>';
	}
	return $html;
}

/**
 * Get image template current.
 *
 * @param int $attachment_id Attahcment ID.
 * @param int $product_id Product ID.
 */
function wtai_get_image_template_current( $attachment_id, $product_id ) {
	$html      = '';
	$alt_text  = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	$html     .= '<div class="wtai-d-flex wtai-image-alt-text flex-dir-col" data-product-id="' . $product_id . '" >';
		$html .= '<label for="' . $attachment_id . '">' . __( 'WordPress image alt text', 'writetext-ai' ) . '</label>';
		$html .= '<div class="wtai-current-text"><div class="wtai-current-value"><p class="wtai-api-data-product_image_alt_text_value wtai-text-message">' . $alt_text . '</p></div></div>';
		$html .= '<div class="wtai-static-count-display">
						<span class="wtai-char-count" wtai-char-count-credit="0">0</span> Char | <span class="word-count">0</span> word/s
					</div>';
	$html     .= '</div>';

	return $html;
}

/**
 * Get main image.
 *
 * @param int  $product_id Product ID.
 * @param bool $is_premium Is premium.
 */
function wtai_get_main_image( $product_id, $is_premium = false ) {
	if ( ! $is_premium ) {
		$is_premium = false;
	}

	$is_checked = wtai_get_generation_featured_image_user_preference();

	$checked = '';
	if ( $is_checked ) {
		$checked = ' checked ';
	}

	$featured_image_id = get_post_thumbnail_id( $product_id );
	$html              = '';

	if ( $featured_image_id ) {
		$attachment_id           = $featured_image_id;
		$gallery_image_data      = wp_get_attachment_image_src( $attachment_id, array( 60, 60 ) );
		$gallery_image_popup     = wp_get_attachment_image_src( $attachment_id, 'full' );
		$gallery_image_popup_url = $gallery_image_popup[0];
		$html                   .= '<ul class="wtai-post-main-image" data-is-checked="' . $is_checked . '" >
										<li>
											<input type="checkbox"  id="wtai-product-main-image" class="wtai-attr-checkboxes wtai-post-data" data-postfield="product-main-image_checked" data-apiname="product-main-image" value="1" ' . $checked . ' />
											<div class="wtai-alt-image" data-popimage="' . $gallery_image_popup_url . '"><img src="' . $gallery_image_data[0] . '" alt="' . $alt_text . '" /><span>' . __( 'View', 'writetext-ai' ) . '</span></div>
											<div class="wtai-alt-text rel">' . __( 'Featured Image', 'writetext-ai' ) . ' <span class="wtai-featured-image-sub" >' . __( '(Analyze image to generate more relevant text)', 'writetext-ai' ) . '</span>
											</div>
										</li>
									</ul>';
	}

	return $html;
}

/**
 * Get main image.
 *
 * @param int $product_id Product ID.
 */
function wtai_get_product_thumbnail( $product_id ) {
	$html              = '';
	$alt_text          = '';
	$featured_image_id = get_post_thumbnail_id( $product_id );
	if ( $featured_image_id ) {
		$attachment_id      = $featured_image_id;
		$gallery_image_data = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );
		$product_thumbnail  = $gallery_image_data[0];
		$alt_text           = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	} else {
		$product_thumbnail = wc_placeholder_img_src();
	}

	$html .= '<img src="' . $product_thumbnail . '" alt="' . $alt_text . '" />';
	return $html;
}

/**
 * Get custom allowed kses html elements.
 */
function wtai_kses_allowed_html() {
	$allowed_tags = array(
		'a'        => array(
			'class'              => array(),
			'href'               => array(),
			'rel'                => array(),
			'title'              => array(),
			'data-request-id'    => array(),
			'data-action'        => array(),
			'data-sku'           => array(),
			'data-postfield'     => array(),
			'style'              => array(),
			'target'             => array(),
			'type'               => array(),
			'data-product-id'    => array(),
			'data-image-id'      => array(),
			'data-image-ids'     => array(),
			'data-category-name' => array(),
		),
		'div'      => array(
			'id'                 => array(),
			'class'              => array(),
			'data-product-ids'   => array(),
			'data-completed-ids' => array(),
			'data-is-own'        => array(),
			'data-user-id'       => array(),
			'data-job-status'    => array(),
			'title'              => array(),
			'data-postfield'     => array(),
			'style'              => array(),
			'data-popimage'      => array(),
			'data-product-id'    => array(),
			'data-image-id'      => array(),
			'data-image-ids'     => array(),
		),
		'form'     => array(
			'method'             => array(),
			'novalidate'         => array(),
			'id'                 => array(),
			'style'              => array(),
			'data-product-id'    => array(),
			'data-image-id'      => array(),
			'data-image-ids'     => array(),
			'data-product-nonce' => array(),
		),
		'img'      => array(
			'class'           => array(),
			'width'           => array(),
			'src'             => array(),
			'alt'             => array(),
			'title'           => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
		),
		'label'    => array(
			'for'             => array(),
			'class'           => array(),
			'id'              => array(),
			'name'            => array(),
			'title'           => array(),
			'data-postfield'  => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
		),
		'span'     => array(
			'class'           => array(),
			'title'           => array(),
			'data-postfield'  => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
		),
		'p'        => array(
			'class'           => array(),
			'id'              => array(),
			'name'            => array(),
			'title'           => array(),
			'data-postfield'  => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
		),
		'input'    => array(
			'type'                => array(),
			'id'                  => array(),
			'name'                => array(),
			'class'               => array(),
			'value'               => array(),
			'data-original-value' => array(),
			'data-mintext'        => array(),
			'data-maxtext'        => array(),
			'min'                 => array(),
			'checked'             => array(),
			'disabled'            => array(),
			'readonly'            => array(),
			'data-post-id'        => array(),
			'data-type'           => array(),
			'maxlength'           => array(),
			'placeholder'         => array(),
			'data-postfield'      => array(),
			'data-value'          => array(),
			'title'               => array(),
			'style'               => array(),
			'data-product-id'     => array(),
			'data-image-id'       => array(),
			'data-image-ids'      => array(),
			'data-date_from'      => array(),
			'data-date_to'        => array(),
		),
		'button'   => array(
			'type'                => array(),
			'id'                  => array(),
			'name'                => array(),
			'class'               => array(),
			'value'               => array(),
			'data-original-value' => array(),
			'data-mintext'        => array(),
			'data-maxtext'        => array(),
			'min'                 => array(),
			'checked'             => array(),
			'disabled'            => array(),
			'readonly'            => array(),
			'data-post-id'        => array(),
			'data-type'           => array(),
			'maxlength'           => array(),
			'placeholder'         => array(),
			'data-postfield'      => array(),
			'data-value'          => array(),
			'title'               => array(),
			'style'               => array(),
			'data-product-id'     => array(),
			'data-image-id'       => array(),
			'data-image-ids'      => array(),
		),
		'textarea' => array(
			'type'                => array(),
			'id'                  => array(),
			'name'                => array(),
			'class'               => array(),
			'value'               => array(),
			'data-original-value' => array(),
			'data-mintext'        => array(),
			'data-maxtext'        => array(),
			'min'                 => array(),
			'checked'             => array(),
			'disabled'            => array(),
			'readonly'            => array(),
			'data-post-id'        => array(),
			'data-type'           => array(),
			'maxlength'           => array(),
			'placeholder'         => array(),
			'data-postfield'      => array(),
			'data-value'          => array(),
			'title'               => array(),
			'style'               => array(),
			'data-product-id'     => array(),
			'data-image-id'       => array(),
			'data-image-ids'      => array(),
		),
		'select'   => array(
			'type'                => array(),
			'id'                  => array(),
			'name'                => array(),
			'class'               => array(),
			'value'               => array(),
			'data-original-value' => array(),
			'data-mintext'        => array(),
			'data-maxtext'        => array(),
			'min'                 => array(),
			'checked'             => array(),
			'disabled'            => array(),
			'readonly'            => array(),
			'data-post-id'        => array(),
			'data-type'           => array(),
			'maxlength'           => array(),
			'placeholder'         => array(),
			'data-postfield'      => array(),
			'data-value'          => array(),
			'title'               => array(),
			'style'               => array(),
			'data-product-id'     => array(),
			'data-image-id'       => array(),
			'data-image-ids'      => array(),
		),
		'option'   => array(
			'id'              => array(),
			'name'            => array(),
			'class'           => array(),
			'value'           => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
		),
		'tr'       => array(
			'id'              => array(),
			'name'            => array(),
			'class'           => array(),
			'data-colname'    => array(),
			'data-text'       => array(),
			'data-colgrp'     => array(),
			'title'           => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
			'data-id'         => array(),
			'data-values'     => array(),
		),
		'td'       => array(
			'id'              => array(),
			'name'            => array(),
			'class'           => array(),
			'data-colname'    => array(),
			'data-text'       => array(),
			'data-colgrp'     => array(),
			'title'           => array(),
			'style'           => array(),
			'data-product-id' => array(),
			'data-image-id'   => array(),
			'data-image-ids'  => array(),
			'colspan'         => array(),
			'rowspan'         => array(),
		),
		'ul'       => array(
			'id'               => array(),
			'name'             => array(),
			'class'            => array(),
			'data-loader-text' => array(),
			'data-postfield'   => array(),
			'data-value'       => array(),
			'title'            => array(),
			'style'            => array(),
			'data-product-id'  => array(),
			'data-image-id'    => array(),
			'data-image-ids'   => array(),
		),
		'li'       => array(
			'id'               => array(),
			'name'             => array(),
			'class'            => array(),
			'data-loader-text' => array(),
			'data-postfield'   => array(),
			'data-value'       => array(),
			'title'            => array(),
			'style'            => array(),
			'data-product-id'  => array(),
			'data-image-id'    => array(),
			'data-image-ids'   => array(),
		),
		'em'       => array(
			'id'               => array(),
			'name'             => array(),
			'class'            => array(),
			'data-loader-text' => array(),
			'data-postfield'   => array(),
			'data-value'       => array(),
			'title'            => array(),
			'style'            => array(),
			'data-product-id'  => array(),
			'data-image-id'    => array(),
			'data-image-ids'   => array(),
		),
		'br'       => array(
			'id'               => array(),
			'name'             => array(),
			'class'            => array(),
			'data-loader-text' => array(),
			'data-postfield'   => array(),
			'data-value'       => array(),
			'title'            => array(),
			'style'            => array(),
			'data-product-id'  => array(),
			'data-image-id'    => array(),
			'data-image-ids'   => array(),
		),
		'meta'     => array(
			'property'      => array(),
			'content'       => array(),
			'data-platform' => array(),
			'name'          => array(),
		),
		'ENTER'    => array(),
		'enter'    => array(),
	);

	return $allowed_tags;
}

/**
 * Get extension review popup html
 *
 * @param int    $product_id Product ID.
 * @param string $current_field_type Current field.
 * @param array  $extension_reviews_data Extension reviews data.
 *
 * @return string
 */
function wtai_get_extension_review_popup_html( $product_id = 0, $current_field_type = '', $extension_reviews_data = array() ) {
	if ( ! $product_id || ! $current_field_type || ! $extension_reviews_data ) {
		return;
	}

	$allowed_statuses = array( 1, 2, 3 );

	$current_field_to_api_key = apply_filters( 'wtai_field_conversion', trim( $current_field_type ), 'product' );

	// Lets check if the current field is in the extension reviews data.
	$popupinfo_html       = '';
	$popupinfo_items_html = '';

	$statuses_found = array();
	foreach ( $extension_reviews_data as $extension_review_data ) {
		$created_by = $extension_review_data['createdByUserName'];
		if ( isset( $extension_review_data['createdByName'] ) && '' !== $extension_review_data['createdByName'] ) {
			$created_by = $extension_review_data['createdByName'];
		}

		$created_date = $extension_review_data['date'];
		$comment      = $extension_review_data['comment'];
		$id           = $extension_review_data['id'];

		$created_date_timestamp = strtotime( get_date_from_gmt( $created_date, 'Y-m-d H:i:s' ) );
		$created_date_formatted = sprintf(
			/* translators: %1$s: date, %2$s: time */
			__( '%1$s at %2$s' ),
			date_i18n( get_option( 'date_format' ), $created_date_timestamp ),
			date_i18n( get_option( 'time_format' ), $created_date_timestamp )
		);

		$fields = $extension_review_data['fields'];
		$fields = $extension_review_data['fields'];
		foreach ( $fields as $field_data ) {
			$field_type = $field_data['field'];
			$status     = intval( $field_data['status'] );

			if ( $current_field_to_api_key === $field_type && in_array( $status, $allowed_statuses, true ) ) {
				$statuses_found[] = $status;

				$status_label = '';
				if ( 1 === $status ) {
					$status_label = __( 'For rewrite', 'writetext-ai' );
				}

				if ( 2 === $status ) {
					$status_label = __( 'For fact checking', 'writetext-ai' );
				}

				if ( 3 === $status ) {
					$status_label = __( 'For rewrite', 'writetext-ai' ) . ' / ' . __( 'For fact checking', 'writetext-ai' );
				}

				$form_html = '<div class="wtai-status-popup-info-content">
							<div class="wtai-d-flex flex-nowrap">
								<label>' . __( 'Reviewed by', 'writetext-ai' ) . '</label>
								<span class="reviewedby">' . $created_by . '</span>
							</div>
							<div class="wtai-d-flex flex-nowrap">
								<label>' . __( 'Date', 'writetext-ai' ) . '</label>
								<span class="date">' . $created_date_formatted . '</span>
							</div>
							<div class="wtai-d-flex flex-nowrap">
								<label>' . __( 'Status', 'writetext-ai' ) . '</label>
								<span class="date">' . $status_label . '</span>
							</div>
							<div class="wtai-d-flex flex-dir-col">
								<label>' . __( 'Comments', 'writetext-ai' ) . '</label>
								<span class="comments">' . wp_kses_post( nl2br( $comment ) ) . '</span>

								<input type="hidden" class="wtai-ext-review-id" value="' . $id . '" />
								<input type="hidden" class="wtai-ext-review-status" value="' . $status . '" />
							</div>
						</div>';

				$popupinfo_items_html .= $form_html;
			}
		}
	}

	if ( $popupinfo_items_html ) {
		$popupinfo_html  = '<div class="wtai-status-popup-info"><div class="wtai-status-popup-info-main" ><div class="wtai-status-popup-info-items-wrap" >';
		$popupinfo_html .= $popupinfo_items_html;
		$popupinfo_html .= '</div><div class="wtai-extension-review-btn-wrap btn">
								<button type="button" class="btn-done button button-primary wtai-submit-extension-review-btn" data-type="' . $current_field_type . '" data-product-id="' . $product_id . '" >' . __( 'Done', 'writetext-ai' ) . '</button>
							</div>';
		$popupinfo_html .= '</div></div>';
	}

	$statuses_found = array_unique( $statuses_found );

	$status_label_global = '';
	if ( in_array( 3, $statuses_found, true ) || ( in_array( 1, $statuses_found, true ) && in_array( 2, $statuses_found, true ) ) ) {
		$status_label_global = __( 'For rewrite', 'writetext-ai' ) . ' / ' . __( 'For fact checking', 'writetext-ai' );
	} elseif ( in_array( 1, $statuses_found, true ) ) {
		$status_label_global = __( 'For rewrite', 'writetext-ai' );
	} elseif ( in_array( 2, $statuses_found, true ) ) {
		$status_label_global = __( 'For fact checking', 'writetext-ai' );
	}

	$output = array(
		'popupinfo_html'      => $popupinfo_html,
		'status_label_global' => $status_label_global,
		'statuses_found'      => $statuses_found,
	);

	return $output;
}

/**
 * Migrate wtai_ post meta to wtai_ post meta.
 */
function wtai_maybe_migrate_wta_metas() {
	global $wpdb;

	$meta_keys = array(
		'wta_product_reference_id',
		'wta_keyword_ideas_volume_filter',
		'wta_keyword_ideas_difficulty_filter',
		'wta_keyword_ideas_sorting',
		'wta_last_activity_date',
		'wta_last_activity',
		'wta_generate_date',
		'wta_transfer_date',
		'wta_last_activity_date_page_title',
		'wta_last_activity_page_title',
		'wta_last_activity_date_page_description',
		'wta_last_activity_page_description',
		'wta_last_activity_date_product_description',
		'wta_last_activity_product_description',
		'wta_last_activity_date_product_excerpt',
		'wta_last_activity_product_excerpt',
		'wta_last_activity_date_open_graph',
		'wta_last_activity_open_graph',
		'wta_bulk_queue_id_product_description',
		'wta_bulk_queue_id_product_excerpt',
		'wta_bulk_queue_id_page_title',
		'wta_bulk_queue_id_page_description',
		'wta_bulk_queue_id_open_graph',
		'wta_product_attribute_preference',
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery
	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM $wpdb->postmeta WHERE meta_key IN (%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s,%s) AND meta_value != '' ",
			'wta_product_reference_id',
			'wta_keyword_ideas_volume_filter',
			'wta_keyword_ideas_difficulty_filter',
			'wta_keyword_ideas_sorting',
			'wta_last_activity_date',
			'wta_last_activity',
			'wta_generate_date',
			'wta_transfer_date',
			'wta_last_activity_date_page_title',
			'wta_last_activity_page_title',
			'wta_last_activity_date_page_description',
			'wta_last_activity_page_description',
			'wta_last_activity_date_product_description',
			'wta_last_activity_product_description',
			'wta_last_activity_date_product_excerpt',
			'wta_last_activity_product_excerpt',
			'wta_last_activity_date_open_graph',
			'wta_last_activity_open_graph',
			'wta_bulk_queue_id_product_description',
			'wta_bulk_queue_id_product_excerpt',
			'wta_bulk_queue_id_page_title',
			'wta_bulk_queue_id_page_description',
			'wta_bulk_queue_id_open_graph',
			'wta_product_attribute_preference'
		)
	);

	foreach ( $post_ids as $product_id ) {
		foreach ( $meta_keys as $key ) {
			$old_meta_value = get_post_meta( $product_id, $key, true );

			$new_meta_key   = str_replace( 'wta_', 'wtai_', $key );
			$new_meta_value = get_post_meta( $product_id, $new_meta_key, true );

			if ( ! $new_meta_value && $old_meta_value ) {
				update_post_meta( $product_id, $new_meta_key, $old_meta_value );
			}

			// Remove the old meta key.
			delete_post_meta( $product_id, $key );
		}
	}
}

/**
 * Get alt product image html.
 *
 * @param int   $product_id Product ID.
 * @param array $product_images Product images.
 * @param array $api_alt_results API results.
 */
function wtai_product_alt_image_html( $product_id = 0, $product_images = array(), $api_alt_results = array() ) {
	if ( ! $product_id ) {
		return;
	}

	if ( ! $product_images ) {
		$product_images = wtai_get_product_image( $product_id );
	}

	$featured_image_id = get_post_thumbnail_id( $product_id );
	$column_key        = 'image_alt_text';

	ob_start();
	if ( $product_images ) {
		$alt_user_preference = wtai_is_image_alt_text_selected();
		$img_ctr             = 1;

		foreach ( $product_images as $attachment_id ) {
			$is_featured_image = false;

			/* translators: %s: Image ctr */
			$suffix_text = ' - ' . sprintf( __( 'Image %s', 'writetext-ai' ), $img_ctr );
			if ( $featured_image_id === $attachment_id ) {
				$is_featured_image = true;
				$suffix_text       = ' - ' . __( 'Featured Image', 'writetext-ai' );
			} else {
				++$img_ctr;
			}

			$api_alt_text         = '';
			$api_alt_text_id      = '';
			$api_alt_text_history = array();
			if ( $api_alt_results && isset( $api_alt_results[ $attachment_id ]['altText'] ) ) {
				$api_alt_text         = $api_alt_results[ $attachment_id ]['altText']['value'];
				$api_alt_text_id      = $api_alt_results[ $attachment_id ]['altText']['id'];
				$api_alt_text_history = $api_alt_results[ $attachment_id ]['altText']['history'];
			}

			$product_alt_user_preference = array();
			$checked                     = false;
			if ( $alt_user_preference ) {
				$checked = true;
			}

			$current_alt_text      = get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
			$is_transferred        = false;
			$transfer_button_class = '';
			if ( $current_alt_text && $api_alt_text_id && $api_alt_text_history && 1 === intval( $api_alt_text_history[0]['publish'] ) ) {
				$is_transferred        = true;
				$transfer_button_class = ' wtai-disabled-button ';
			}

			if ( '' === $api_alt_text_id || '' === $api_alt_text ) {
				$transfer_button_class = ' wtai-disabled-button ';
			}

			if ( $current_alt_text === $api_alt_text && '' !== $api_alt_text ) {
				// lets check if current value is same as api value, then lets disable.
				$transfer_button_class = ' wtai-disabled-button ';
				$is_transferred        = true;
			}
			?>
			<div class="wtai-col-row-wrapper wtai-image-alt-metabox wtai-image-alt-metabox-<?php echo esc_attr( $attachment_id ); ?>" data-id="<?php echo esc_attr( $attachment_id ); ?>" >
				
				<?php
				echo wp_kses( wtai_get_image_template( $attachment_id, $product_id, $is_featured_image, $suffix_text, $api_alt_text, $api_alt_text_id, $api_alt_text_history, $checked, $is_transferred ), wtai_kses_allowed_html() );
				?>
				
					<div class="wtai-col-row wtai-single-transfer-btn-wrapper">
						<?php if ( wtai_current_user_can( 'writeai_transfer_generated_text' ) ) : ?>
						<button type="button" class="button wtai-single-transfer-btn wtai-single-transfer-alt-btn <?php echo esc_attr( $transfer_button_class ); ?>" 
							data-type="<?php echo esc_attr( $column_key ); ?>" 
							data-image-id="<?php echo esc_attr( $attachment_id ); ?>" 
							data-product-id="<?php echo esc_attr( $product_id ); ?>" 
							data-id="<?php echo esc_attr( $api_alt_text_id ); ?>" 
						><span class="dashicons dashicons-arrow-right-alt2"></span></button>
						<?php endif; ?>
					</div>
					<div class="wtai-col-row wtai-current-value-wrapper">
						<?php echo wp_kses( wtai_get_image_template_current( $attachment_id, $product_id ), wtai_kses_allowed_html() ); ?>
					</div>
				</div>
			</div>
		<?php } ?>

	<?php } else { ?>
		<div class="no-image-found"><?php echo wp_kses_post( __( 'No image found.', 'writetext-ai' ) ); ?></div>
		<?php
	}

	$html = ob_get_clean();

	return $html;
}

/**
 * Get product image user preference if checked.
 */
function wtai_get_generation_featured_image_user_preference() {
	$checked = false;

	$current_user_id = get_current_user_id();

	$wtai_product_image_checked_status_set = get_user_meta( $current_user_id, 'wtai_product_image_checked_status_set', true );

	if ( ! $wtai_product_image_checked_status_set ) {
		$product_attr_settings = apply_filters( 'wtai_global_settings', 'wtai_installation_product_attr' );
		$feature_image_id      = 'wtai-featured-product-image';

		$selected_featured_image = ( is_array( $product_attr_settings ) && ! empty( $product_attr_settings ) && in_array( $feature_image_id, $product_attr_settings, true ) ) ? '1' : '';

		update_user_meta( $current_user_id, 'wtai_product_image_checked_status_set', '1' );
		update_user_meta( $current_user_id, 'wtai_include_featured_image_in_generation', $selected_featured_image );

		if ( $selected_featured_image ) {
			$checked = true;
		}
	} else {
		$wtai_include_featured_image_in_generation = get_user_meta( $current_user_id, 'wtai_include_featured_image_in_generation', true );
		$wtai_include_featured_image_in_generation = intval( $wtai_include_featured_image_in_generation );

		if ( 1 === $wtai_include_featured_image_in_generation ) {
			$checked = true;
		} else {
			$checked = false;
		}
	}

	return $checked;
}

/**
 * Check if an image is publicly visible.
 *
 * @param string $image_url Image URL.
 */
function wtai_is_image_publicly_available( $image_url ) {
	// Check if the URL is not a local file.
	if ( filter_var( $image_url, FILTER_VALIDATE_URL ) === false ) {
		return false;
	}

	$headers = get_headers( $image_url, 1 );

	// Check if the Content-Type header indicates an image.
	if ( isset( $headers['Content-Type'] ) && strpos( $headers['Content-Type'], 'image' ) !== false ) {
		// Check if the HTTP response code is in the 200 range.
		if ( isset( $headers[0] ) && strpos( $headers[0], '200' ) !== false ) {
			return true; // Image is publicly accessible on the web.
		}
	}

	return false; // Image is not publicly accessible or the URL is invalid.
}

/**
 * Handle image API generation.
 *
 * @param int    $record_id Record ID.
 * @param int    $attachment_id Attachment ID.
 * @param int    $browser_time Browser time.
 * @param bool   $include_history include history.
 * @param string $type Type.
 */
function wtai_get_image_for_api_generation( $record_id = 0, $attachment_id = 0, $browser_time = 0, $include_history = false, $type = 'product' ) {
	if ( ! $record_id || ! $attachment_id ) {
		return array();
	}

	$last_modified = get_post_meta( $attachment_id, 'wtai_image_last_modified', true );

	$attachment_file          = get_attached_file( $attachment_id );
	$last_image_modified_date = filemtime( $attachment_file );

	$image_api_data = apply_filters( 'wtai_get_product_image_from_api', array(), $record_id, $attachment_id, $include_history );

	$save_fresh = true;
	if ( $image_api_data && isset( $image_api_data['url'] ) ) {
		if ( isset( $image_api_data['imageDataExpires'] ) && strtotime( $image_api_data['imageDataExpires'] ) < strtotime( current_time( 'mysql' ) ) ) {
			$save_fresh = true;
		} else {
			$save_fresh = false;
		}
	}

	// Save if image is modified.
	if ( intval( $last_modified ) < intval( $last_image_modified_date ) ) {
		$save_fresh = true;
	}

	if ( $save_fresh ) {
		$image_api_data = apply_filters( 'wtai_save_product_image_to_api', array(), $record_id, $attachment_id, $browser_time, true, $type );

		// Lets update the image last modified date.
		update_post_meta( $attachment_id, 'wtai_image_last_modified', $last_image_modified_date );
	}

	return $image_api_data;
}

/**
 * Handle image API generation.
 *
 * @param int   $product_id Product ID.
 * @param array $image_ids Attachment Image IDs.
 */
function wtai_update_image_alt_user_preference( $product_id = 0, $image_ids = array() ) {
	$current_user_id = get_current_user_id();

	if ( ! $current_user_id || ! $product_id ) {
		return;
	}

	$selected_product_alt_image_ids = get_user_meta( $current_user_id, 'wtai_selected_product_alt_image_ids', true );

	if ( ! is_array( $selected_product_alt_image_ids ) ) {
		$selected_product_alt_image_ids = array();
	}

	$selected_product_alt_image_ids[ $product_id ] = $image_ids;

	update_user_meta( $current_user_id, 'wtai_selected_product_alt_image_ids', $selected_product_alt_image_ids );
}

/**
 * Get image alt user preference.
 */
function wtai_get_image_alt_user_preference() {
	$current_user_id = get_current_user_id();

	if ( ! $current_user_id ) {
		return array();
	}

	$selected_product_alt_image_ids = get_user_meta( $current_user_id, 'wtai_selected_product_alt_image_ids', true );

	if ( ! is_array( $selected_product_alt_image_ids ) ) {
		$selected_product_alt_image_ids = array();
	}

	return $selected_product_alt_image_ids;
}

/**
 * Check if image alt text should be selected per user preference.
 */
function wtai_is_image_alt_text_selected() {
	$current_user_id = get_current_user_id();

	if ( ! $current_user_id ) {
		return false;
	}

	$is_checked = false;

	$wtai_preselected_types = get_user_meta( $current_user_id, 'wtai_preselected_types', true );

	if ( in_array( 'image_alt_text', $wtai_preselected_types, true ) ) {
		$is_checked = true;
	}

	return $is_checked;
}

/**
 * Get field type labels.
 */
function wtai_get_field_type_labels() {
	$field_type_labels = array(
		'page_title'          => __( 'Meta title', 'writetext-ai' ),
		'page_description'    => __( 'Meta description', 'writetext-ai' ),
		'product_description' => __( 'Product description', 'writetext-ai' ),
		'product_excerpt'     => __( 'Product short description', 'writetext-ai' ),
		'open_graph'          => __( 'Open Graph text', 'writetext-ai' ),
		'alt_text'            => __( 'Image alt text', 'writetext-ai' ),
	);

	return $field_type_labels;
}

/**
 * Get available credit count.
 */
function wtai_get_available_credit_count() {
	$available_credits = '--';
	$api_credits       = apply_filters( 'wtai_get_available_credits', array() );

	if ( $api_credits && isset( $api_credits['availableCredits'] ) ) {
		$available_credits = intval( $api_credits['availableCredits'] );
	}

	return $available_credits;
}

/**
 * Get available credit label.
 *
 * @param int $credit_count Credit count.
 */
function wtai_get_available_credit_label( $credit_count = '--' ) {
	if ( ! $credit_count || '--' === $credit_count ) {
		$account_credit_details = wtai_get_account_credit_details();
		$credit_count           = $account_credit_details['available_credits'];
	}

	if ( '--' === $credit_count ) {
		/* translators: %s: Credit count */
		$available_credits_label = sprintf( __( '%s credits available', 'writetext-ai' ), $credit_count );
	} elseif ( $credit_count <= 1 ) {
		/* translators: %s: Credit count */
		$available_credits_label = sprintf( __( '%s credit available', 'writetext-ai' ), $credit_count );
	} else {
		/* translators: %s: Credit count */
		$available_credits_label = sprintf( __( '%s credits available', 'writetext-ai' ), $credit_count );
	}

	return $available_credits_label;
}

/**
 * Check if the current site language is EN.
 */
function wtai_is_current_locale_en() {
	$current_language = apply_filters( 'wtai_language_code', get_locale() );

	$is_en = false;
	if ( $current_language ) {
		$current_language = str_replace( '_', '-', str_replace( '_formal', '', $current_language ) );
		$current_language = str_replace( '_', '-', str_replace( '_informal', '', $current_language ) );

		$locale_array = explode( '-', $current_language );
		$locale_lang  = $locale_array[0];

		if ( 'en' === $locale_lang ) {
			$is_en = true;
		}
	}

	return $is_en;
}

/**
 * Get account credit details.
 *
 * @param bool $refresh Refresh the data.
 */
function wtai_get_account_credit_details( $refresh = false ) {
	$web_token = apply_filters( 'wtai_web_token', '' );

	if ( ! $web_token ) {
		return array();
	}

	$credit_transient = get_transient( 'wtai_account_credit_details' );

	if ( ! $credit_transient || $refresh ) {
		$settings = array(
			'remote_url' => 'https://' . wtai_get_api_base_url() . '/text/Credit',
		);
		$headers  = array(
			'Cache-Control' => 'no-cache',
			'Authorization' => 'Bearer ' . $web_token,
		);
		$content  = apply_filters( 'wtai_get_data_via_api', array(), array(), $settings, $headers, 'GET' );

		$result = array(
			'is_premium'                   => false,
			'available_credits'            => '--',
			'total_credits'                => '--',
			'free_premium_credits'         => false,
			'free_credits_already_premium' => false,
		);
		if ( 200 === intval( $content['http_header'] ) ) {
			$content = json_decode( $content['result'], true );

			if ( isset( $content['isPremium'] ) && ( 'true' === $content['isPremium'] || 1 === intval( $content['isPremium'] ) ) ) {
				$result['is_premium'] = true;
			}
			if ( isset( $content['availableCredits'] ) ) {
				$result['available_credits'] = $content['availableCredits'];
			}
			if ( isset( $content['totalCredits'] ) ) {
				$result['total_credits'] = $content['totalCredits'];
			}
			if ( isset( $content['freePremiumCredits'] ) && ( 'true' === $content['freePremiumCredits'] || 1 === intval( $content['freePremiumCredits'] ) ) ) {
				$result['free_premium_credits'] = true;
			}
			if ( isset( $content['freeCreditsAlreadyPremium'] ) && ( 'true' === $content['freeCreditsAlreadyPremium'] || 1 === intval( $content['freeCreditsAlreadyPremium'] ) ) ) {
				$result['free_credits_already_premium'] = true;
			}
		}

		set_transient( 'wtai_account_credit_details', $result, 5 );
	} else {
		$result = $credit_transient;
	}

	return $result;
}

/**
 * Get review extension language.
 */
function wtai_get_review_extension_language() {
	$lang_attributes = get_language_attributes();

	$pattern = '/lang="([^"]*)"/'; // Regular expression pattern to match any string inside lang="".
	preg_match( $pattern, $lang_attributes, $matches ); // Perform the regex match.

	$language_code = '';
	if ( isset( $matches[1] ) ) {
		$language_code = $matches[1];
	}

	return $language_code;
}

/**
 * Check if the country CTA popup should be hidden or not.
 */
function wtai_is_country_selection_hidden() {
	$default_locale = apply_filters( 'wtai_language_code', get_locale() );

	$default_locale_array     = explode( '_', $default_locale );
	$default_lang             = isset( $default_locale_array[0] ) ? $default_locale_array[0] : 'en'; // Lets get the default language to English if no locale is found.
	$default_country          = isset( $default_locale_array[1] ) ? $default_locale_array[1] : '';
	$is_localized_country_set = wtai_is_site_localized_countries_set( $default_lang );

	$is_hidden = false;
	if ( ! $default_country ) {
		$is_hidden = true;

		if ( $is_localized_country_set ) {
			$is_hidden = false;
		}
	}

	return $is_hidden;
}

/**
 * Get selected keyword html.
 *
 * @param int    $record_id Product ID.
 * @param array  $keywords Keywords.
 * @param array  $keywords_statistics Keywords statistics.
 * @param array  $ranked_keywords Ranked keywords.
 * @param array  $competitor_keywords Competitor keywords.
 * @param string $rank_serp_date Rank SERP date.
 * @param string $competitor_serp_date Competitor SERP date.
 * @param string $record_type Record type.
 */
function wtai_get_selected_keyword_html( $record_id = 0, $keywords = array(), $keywords_statistics = array(), $ranked_keywords = array(), $competitor_keywords = array(), $rank_serp_date = '', $competitor_serp_date = '', $record_type = 'product' ) {
	if ( ! $record_id ) {
		return;
	}

	if ( ! $keywords ) {
		$keywords = array();
	}

	if ( ! $keywords_statistics ) {
		$keywords_statistics = array();
	}

	if ( ! $ranked_keywords ) {
		$ranked_keywords = array();
	}

	if ( ! $competitor_keywords ) {
		$competitor_keywords = array();
	}

	if ( ! $rank_serp_date ) {
		$rank_serp_date = '';
	}

	if ( ! $competitor_serp_date ) {
		$competitor_serp_date = '';
	}

	if ( ! $record_type ) {
		$record_type = 'product';
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-selected.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get ranked keyword html.
 *
 * @param int    $record_id Product ID.
 * @param array  $ranked_keywords Ranked keywords.
 * @param array  $target_keywords Target keywords.
 * @param string $serp_date SERP date.
 * @param array  $sort_array Sort array.
 * @param array  $filter_array Filter array.
 * @param string $record_type Record type.
 */
function wtai_get_ranked_keyword_html( $record_id = 0, $ranked_keywords = array(), $target_keywords = array(), $serp_date = '', $sort_array = array(), $filter_array = array(), $record_type = 'product' ) {
	if ( ! $record_id || ! $ranked_keywords ) {
		return;
	}

	if ( ! $target_keywords ) {
		$target_keywords = array();
	}

	if ( ! $serp_date ) {
		$serp_date = '';
	}

	if ( ! $sort_array ) {
		$sort_array = array();
	}

	if ( ! $filter_array ) {
		$filter_array = array();
	}

	if ( ! $record_type ) {
		$record_type = '';
	}

	if ( $sort_array ) {
		$sort_type      = $sort_array['sort_type'];
		$sort_direction = $sort_array['sort_direction'];

		if ( 'relevance' !== $sort_type ) {
			$sort_key = wtai_get_keyword_field_key( $sort_type );

			$sort_field_array = array();
			foreach ( $ranked_keywords as $index => $keyword_data ) {
				$sort_value = $keyword_data[ $sort_key ];

				$sort_field_array[ $index ] = $sort_value;
			}

			if ( 'asc' === $sort_direction ) {
				array_multisort( $sort_field_array, SORT_ASC, $ranked_keywords );
			} else {
				array_multisort( $sort_field_array, SORT_DESC, $ranked_keywords );
			}
		}
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-ranked.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get competitor keyword html.
 *
 * @param int    $record_id Record ID.
 * @param array  $competitor_keywords Competitor keywords.
 * @param array  $target_keywords Target keywords.
 * @param string $serp_date SERP date.
 * @param string $record_type Record type.
 */
function wtai_get_competitor_keyword_html( $record_id = 0, $competitor_keywords = array(), $target_keywords = array(), $serp_date = '', $record_type = 'product' ) {
	if ( ! $record_id || ! $competitor_keywords ) {
		return;
	}

	if ( ! $target_keywords ) {
		$target_keywords = array();
	}

	if ( ! $serp_date ) {
		$serp_date = '';
	}

	if ( ! $record_type ) {
		$record_type = 'product';
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-competitor.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get manual keyword html.
 *
 * @param int    $record_id Record ID.
 * @param array  $keywords Keywords.
 * @param array  $keywords_statistics Keywords statistics.
 * @param array  $target_keywords Target keywords.
 * @param array  $ranked_keywords Ranked keywords.
 * @param array  $competitor_keywords Competitor keywords.
 * @param string $record_type Record type.
 */
function wtai_get_manual_keyword_html( $record_id = 0, $keywords = array(), $keywords_statistics = array(), $target_keywords = array(), $ranked_keywords = array(), $competitor_keywords = array(), $record_type = 'product' ) {
	if ( ! $record_id ) {
		return;
	}

	if ( ! $keywords_statistics ) {
		$keywords_statistics = array();
	}

	if ( ! $target_keywords ) {
		$target_keywords = array();
	}

	if ( ! $ranked_keywords ) {
		$ranked_keywords = array();
	}

	if ( ! $competitor_keywords ) {
		$competitor_keywords = array();
	}

	if ( ! $record_type ) {
		$record_type = 'product';
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-manual.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get suggested keyword html.
 *
 * @param int    $record_id Record ID.
 * @param array  $keyword_ideas Keyword ideas.
 * @param array  $manual_keywords Manual keywords.
 * @param array  $target_keywords Target keywords.
 * @param int    $total_pages Total pages.
 * @param int    $items_per_page Items per page.
 * @param int    $total_items Total items.
 * @param int    $page_no Page number.
 * @param string $record_type Record type.
 */
function wtai_get_suggested_keyword_html( $record_id = 0, $keyword_ideas = array(), $manual_keywords = array(), $target_keywords = array(), $total_pages = 0, $items_per_page = 5, $total_items = 0, $page_no = 1, $record_type = 'product' ) {
	if ( ! $record_id ) {
		return;
	}

	if ( ! $manual_keywords ) {
		$manual_keywords = array();
	}

	if ( ! $target_keywords ) {
		$target_keywords = array();
	}

	if ( ! $total_pages ) {
		$total_pages = 0;
	}

	if ( ! $items_per_page ) {
		$items_per_page = 0;
	}

	if ( ! $total_items ) {
		$total_items = 0;
	}

	if ( ! $page_no ) {
		$page_no = 1;
	}

	if ( ! $record_type ) {
		$record_type = 'product';
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-suggested.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get serp info html.
 *
 * @param int    $product_id Product ID.
 * @param string $keyword Keyword.
 * @param string $keyword_type Keyword type.
 * @param array  $serp_infos SERP info.
 * @param string $serp_date SERP date.
 * @param array  $featured_serp_info Featured SERP info.
 */
function wtai_get_keyword_serp_html( $product_id = 0, $keyword = '', $keyword_type = '', $serp_infos = array(), $serp_date = '', $featured_serp_info = array() ) {
	if ( ! $product_id || ! $keyword || ! $keyword_type || ! $serp_infos ) {
		return;
	}

	if ( ! $serp_date ) {
		$serp_date = '';
	}

	if ( ! $featured_serp_info ) {
		$featured_serp_info = array();
	}

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/keywords-serp.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get location code.
 */
function wtai_get_location_code() {
	$product_countries = apply_filters( 'wtai_keywordanalysis_location', array() );

	$localized_country = wtai_get_site_localized_countries();

	$location_code = '';
	foreach ( $product_countries as $product_country_id => $product_country ) {
		$product_country['product_country_id'] = $product_country_id;

		if ( $localized_country && strtolower( $product_country['code'] ) === strtolower( $localized_country[0] ) ) {
			$location_code = $product_country['product_country_id'];
		}
	}

	return $location_code;
}


/**
 * Filter empty array content.
 *
 * @param array $array_to_check Array to check.
 */
function wtai_filter_empty_array( $array_to_check = array() ) {
	if ( ! $array_to_check ) {
		return $array_to_check;
	}

	$filtered_array = array();
	foreach ( $array_to_check as $value ) {
		if ( '' !== trim( $value ) ) {
			$filtered_array[] = stripslashes( $value );
		}
	}

	return $filtered_array;
}

/**
 * Get corresponding page number of an item number.
 *
 * @param int $item_number Item number.
 * @param int $first_page_item_count First page item count.
 * @param int $per_page Per page.
 */
function wtai_get_item_page_number( $item_number = 0, $first_page_item_count = 5, $per_page = 10 ) {
	// If the item is on the first page.
	if ( $item_number <= $first_page_item_count ) {
		return 1; // First page.
	}

	// For succeeding pages.
	$item_number -= $first_page_item_count; // Adjust item number.
	$page         = ceil( $item_number / $per_page ) + 1; // Calculate page number.

	return $page;
}

/**
 * Get serp featured snippet.
 *
 * @param array $serp_infos SERP Infos.
 */
function wtai_get_serp_featured_snippet( $serp_infos = array() ) {
	if ( ! $serp_infos ) {
		return;
	}

	$featured_serp = array();
	foreach ( $serp_infos as $serp_info ) {
		$type = $serp_info['type'];

		if ( 'featured_snippet' === $type ) {
			$featured_serp = $serp_info;

			break;
		}
	}

	return $featured_serp;
}

/**
 * Get top serp info.
 *
 * @param array $serp_infos SERP Infos.
 */
function wtai_get_top_serp_data( $serp_infos = array() ) {
	if ( ! $serp_infos ) {
		return;
	}

	$site_url = site_url();
	$site_url = preg_replace( '(^https?://)', '', $site_url );

	// For debugging and testing purposes.
	if ( defined( 'WTAI_TEST_SERP_SITE_URL' ) && WTAI_TEST_SERP_SITE_URL ) {
		$site_url = WTAI_TEST_SERP_SITE_URL;
	}

	$own_serp_data  = array();
	$top_serp_data  = array();
	$max_serp_count = 100; // Maximum number of SERP data to display.
	$has_serp_top   = false;

	foreach ( $serp_infos as $serp_info ) {
		$serp_domain = $serp_info['domain'];
		$type        = $serp_info['type'];

		if ( 'organic' !== $type ) {
			continue;
		}

		if ( $site_url === $serp_domain ) {
			$serp_info['is_own'] = true;
		}

		if ( count( $top_serp_data ) < $max_serp_count ) {
			$top_serp_data[] = $serp_info;

			if ( $site_url === $serp_domain ) {
				$has_serp_top = true;
			}
		}

		if ( $site_url === $serp_domain ) {
			$own_serp_data[] = $serp_info;
		}
	}

	if ( ! $has_serp_top && $own_serp_data ) {
		// Remove the last element from $top_serp_data.
		array_pop( $top_serp_data );

		// Get the first element from $own_serp_data.
		$first_element = array_shift( $own_serp_data );

		// Add the first element of $own_serp_data to the end of $top_serp_data.
		$top_serp_data[] = $first_element;
	}

	return $top_serp_data;
}

/**
 * Get keyword field key.
 *
 * @param string $field_type Field type.
 */
function wtai_get_keyword_field_key( $field_type = '' ) {
	if ( ! $field_type ) {
		return;
	}

	$api_key = '';
	if ( 'rank' === $field_type ) {
		$api_key = 'rank_group';
	} elseif ( 'volume' === $field_type ) {
		$api_key = 'search_volume';
	} elseif ( 'difficulty' === $field_type ) {
		$api_key = 'competition';
	} elseif ( 'intent' === $field_type ) {
		$api_key = 'intent';
	}

	return $api_key;
}

/**
 * Save keyword analysis sort and filter.
 *
 * @param int    $record_id Record ID.
 * @param string $keyword_type Keyword type.
 * @param string $sort_type Sort type.
 * @param string $sort_direction Sort direction.
 * @param string $volume_filter Volume filter.
 * @param string $difficulty_filter Difficulty filter.
 * @param string $record_type Record type.
 */
function wtai_save_keyword_analysis_sort_filter( $record_id = 0, $keyword_type = '', $sort_type = 'relevance', $sort_direction = 'asc', $volume_filter = 'all', $difficulty_filter = '', $record_type = 'product' ) {
	if ( ! $record_id || ! $keyword_type ) {
		return;
	}

	$sort_filter_data = array(
		'sort_type'         => $sort_type,
		'sort_direction'    => $sort_direction,
		'volume_filter'     => $volume_filter,
		'difficulty_filter' => $difficulty_filter,
	);

	if ( 'category' === $record_type ) {
		update_term_meta( $record_id, 'wtai_keyword_analysis_sort_filter_' . $keyword_type, $sort_filter_data );

	} else {
		update_post_meta( $record_id, 'wtai_keyword_analysis_sort_filter_' . $keyword_type, $sort_filter_data );
	}
}

/**
 * Get keyword analysis sort and filter.
 *
 * @param int    $record_id Product ID.
 * @param string $keyword_type Keyword type.
 * @param string $record_type Record type.
 */
function wtai_get_keyword_analysis_sort_filter( $record_id = 0, $keyword_type = '', $record_type = 'product' ) {
	if ( ! $record_id || ! $keyword_type ) {
		return;
	}

	if ( 'category' === $record_type ) {
		$sort_filter_data = get_term_meta( $record_id, 'wtai_keyword_analysis_sort_filter_' . $keyword_type, true );

	} elseif ( 'product' === $record_type ) {
		$sort_filter_data = get_post_meta( $record_id, 'wtai_keyword_analysis_sort_filter_' . $keyword_type, true );
	}

	if ( ! $sort_filter_data ) {
		$difficulty_defaults = array( 'LOW', 'MEDIUM', 'HIGH' );

		$sort_filter_data = array(
			'sort_type'         => 'relevance',
			'sort_direction'    => 'asc',
			'volume_filter'     => 'all',
			'difficulty_filter' => $difficulty_defaults,
		);
	}

	return $sort_filter_data;
}

/**
 * Get category field values.
 *
 * @param int    $category_id Category ID.
 * @param string $field_type Field Type.
 */
function wtai_get_category_values( $category_id = 0, $field_type = '' ) {
	$source = get_option( 'wtai_installation_source', '' );

	if ( ! $source || ! $category_id ) {
		return;
	}

	$category_id = intval( $category_id );

	$term = get_term( $category_id );

	if ( is_wp_error( $term ) || ! $term ) {
		return;
	}

	$taxonomy = 'product_cat';

	if ( $field_type ) {
		$fields = apply_filters( 'wtai_category_fields', array() );

		if ( isset( $fields[ $field_type ] ) ) {
			$fields = array( $field_type => $fields[ $field_type ] );
		}
	} else {
		$fields = apply_filters( 'wtai_category_fields', array() );
	}

	$field_values = array();

	if ( 'wordpress-seo-premium' === $source || 'wordpress-seo' === $source ) {
		if ( class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
			foreach ( $fields as $field_key => $field_title ) {
				if ( 'page_title' === $field_key ) {
					$value = WPSEO_Taxonomy_Meta::get_term_meta( $category_id, $taxonomy, 'title' );

					$field_values[ $field_key ] = $value;
				} elseif ( 'page_description' === $field_key ) {
					$value = WPSEO_Taxonomy_Meta::get_term_meta( $category_id, $taxonomy, 'desc' );

					$field_values[ $field_key ] = $value;

				} elseif ( 'category_description' === $field_key ) {
					$value = $term->description;

					$field_values[ $field_key ] = $value;

				} elseif ( 'open_graph' === $field_key ) {
					$value = WPSEO_Taxonomy_Meta::get_term_meta( $category_id, $taxonomy, 'opengraph-description' );

					$field_values[ $field_key ] = $value;
				}
			}
		}
	} elseif ( 'seo-by-rank-math' === $source || 'seo-by-rank-math-pro' === $source ) {
		foreach ( $fields as $field_key => $field_title ) {
			if ( 'page_title' === $field_key ) {
				$value = get_term_meta( $category_id, 'rank_math_title', true );

				$field_values[ $field_key ] = $value;
			} elseif ( 'page_description' === $field_key ) {
				$value = get_term_meta( $category_id, 'rank_math_description', true );

				$field_values[ $field_key ] = $value;

			} elseif ( 'category_description' === $field_key ) {
				$value = $term->description;

				$field_values[ $field_key ] = $value;

			} elseif ( 'open_graph' === $field_key ) {
				$value = get_term_meta( $category_id, 'rank_math_facebook_description', true );

				$field_values[ $field_key ] = $value;
			}
		}
	} elseif ( 'all-in-one-seo-pack' === $source ) {
		foreach ( $fields as $field_key => $field_title ) {
			if ( 'page_title' === $field_key ) {
				$value = get_term_meta( $category_id, '_aioseo_title', true );

				$field_values[ $field_key ] = $value;
			} elseif ( 'page_description' === $field_key ) {
				$value = get_term_meta( $category_id, '_aioseo_description', true );

				$field_values[ $field_key ] = $value;

			} elseif ( 'category_description' === $field_key ) {
				$value = $term->description;

				$field_values[ $field_key ] = $value;

			} elseif ( 'open_graph' === $field_key ) {
				$value = get_term_meta( $category_id, '_aioseo_og_description', true );

				$field_values[ $field_key ] = $value;
			}
		}
	} elseif ( 'all-in-one-seo-pack-pro' === $source ) {
		$meta_data = aioseo()->meta->metaData->getMetaData( $term );

		foreach ( $fields as $field_key => $field_title ) {
			if ( 'page_title' === $field_key ) {
				$value = $meta_data->title;
				$value = aioseo()->tags->replaceTags( $value, $category_id );

				$field_values[ $field_key ] = $value;
			} elseif ( 'page_description' === $field_key ) {
				$value = $meta_data->description;
				$value = aioseo()->tags->replaceTags( $value );

				$field_values[ $field_key ] = $value;

			} elseif ( 'category_description' === $field_key ) {
				$value = $term->description;

				$field_values[ $field_key ] = $value;

			} elseif ( 'open_graph' === $field_key ) {
				$value                      = $meta_data->og_description;
				$value                      = aioseo()->tags->replaceTags( $value );
				$field_values[ $field_key ] = $value;
			}
		}
	}

	return $field_values;
}

/**
 * Get current page type.
 */
function wtai_get_current_page_type() {
	$screen = get_current_screen();

	$type = '';
	if ( $screen && isset( $screen->id ) ) {
		if ( 'toplevel_page_write-text-ai' === $screen->id ) {
			$type = 'product';

		} elseif ( 'writetext-ai_page_write-text-ai-category' === $screen->id ) {
			$type = 'category';
		}
	}

	return $type;
}

/**
 * Get category image html.
 *
 * @param int $category_id Category ID.
 */
function wtai_get_category_image_html( $category_id = 0 ) {
	if ( ! $category_id ) {
		return;
	}

	$category_id  = intval( $category_id );
	$thumbnail_id = get_term_meta( $category_id, 'thumbnail_id', true );

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/category-image.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get category image html.
 *
 * @param int    $category_id Category ID.
 * @param int    $page Page.
 * @param array  $excluded Excluded.
 * @param string $search Search.
 * @param bool   $do_search Do search.
 */
function wtai_get_category_products( $category_id = 0, $page = 1, $excluded = array(), $search = '', $do_search = false ) {
	if ( ! $category_id ) {
		return;
	}

	$limit = WTAI_REPRESENTATIVE_PRODUCT_LIMIT_PER_PAGE;

	$terms = array();

	$category_id = intval( $category_id );

	$terms[] = $category_id;

	$sub_categories = wtai_get_all_subcategories( $category_id );
	if ( ! empty( $sub_categories ) ) {
		foreach ( $sub_categories as $sub_category ) {
			$terms[] = $sub_category['id'];
		}
	}

	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => $limit,
		'paged'          => $page,
		'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $terms,
			),
		),
		'orderby'        => 'post_title',
		'order'          => 'ASC',
	);

	if ( $search && $do_search ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$search_results = $wpdb->get_col(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnquotedComplexPlaceholder
				"SELECT ID FROM %1s 
				WHERE post_type = 'product' AND ( post_title LIKE %s OR ID LIKE %s ) GROUP BY ID",
				$wpdb->posts,
				'%' . $search . '%',
				'%' . $search . '%'
			)
		); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared.

		if ( $excluded && $search_results ) {
			$search_results = array_diff( $search_results, $excluded );
		}

		if ( ! $search_results ) {
			$search_results = array( 0 ); // Lets force no result.
		}

		$args['post__in'] = $search_results;
	}

	if ( $excluded && ! $search ) {
		$args['post__not_in'] = $excluded;
	}

	$products = new WP_Query( $args );

	$total_products = $products->found_posts;
	$total_pages    = $products->max_num_pages;

	$product_list = array();
	while ( $products->have_posts() ) {
		$products->the_post();

		$product_id   = get_the_ID();
		$product_name = get_the_title();

		$post_status_key = get_post_status( $product_id );
		$post_status     = wtai_get_post_status_label( $post_status_key );

		$product_image_id = get_post_thumbnail_id( $product_id );
		if ( $product_image_id ) {
			$product_image     = wp_get_attachment_image_src( $product_image_id, 'thumbnail' );
			$product_image_url = $product_image[0];

			$product_image_full     = wp_get_attachment_image_src( $product_image_id, 'large' );
			$product_image_full_url = $product_image_full[0];
		} else {
			$product_image_url      = wc_placeholder_img_src();
			$product_image_full_url = $product_image_url;
		}

		$product_list[] = array(
			'id'                     => $product_id,
			'name'                   => $product_name,
			'product_image_id'       => $product_image_id,
			'product_image_url'      => $product_image_url,
			'product_image_full_url' => $product_image_full_url,
			'post_status'            => $post_status,
			'post_status_key'        => $post_status_key,
		);
	}

	wp_reset_postdata();

	$output = array(
		'products'       => $product_list,
		'total_products' => $total_products,
		'total_pages'    => $total_pages,
	);

	return $output;
}

/**
 * Get all subcategories of a given category.
 *
 * @param int $parent_category_id The ID of the parent category.
 * @param int $depth The depth of the subcategories.
 * @return array An array of all subcategories.
 */
function wtai_get_all_subcategories( $parent_category_id, $depth = 0 ) {
	$taxonomy = 'product_cat';

	// Retrieve the subcategories.
	$subcategories = get_terms(
		array(
			'parent'     => $parent_category_id,
			'hide_empty' => false,
			'taxonomy'   => $taxonomy,
		)
	);

	// Initialize an empty array to hold all subcategories.
	$all_subcategories = array();

	// Loop through each subcategory.
	foreach ( $subcategories as $subcategory ) {
		// Add the subcategory to the array.
		$all_subcategories[] = array(
			'id'     => $subcategory->term_id,
			'name'   => $subcategory->name,
			'slug'   => $subcategory->slug,
			'parent' => $subcategory->parent,
			'depth'  => $depth,
		);

		// Recursively get the subcategories of this subcategory.
		$child_subcategories = wtai_get_all_subcategories( $subcategory->term_id, $depth + 1 );

		// Merge the child subcategories with the main array.
		$all_subcategories = array_merge( $all_subcategories, $child_subcategories );
	}

	return $all_subcategories;
}

/**
 * Get category product list html dropdown.
 *
 * @param int    $category_id Category ID.
 * @param int    $page Page.
 * @param bool   $force_hide_wrap Force hide wrap.
 * @param string $search Search.
 * @param bool   $do_search Do search.
 */
function wtai_get_category_product_list_dropdown_html( $category_id = 0, $page = 1, $force_hide_wrap = false, $search = '', $do_search = false ) {
	if ( ! $category_id ) {
		return;
	}

	$limit = WTAI_REPRESENTATIVE_PRODUCT_LIMIT_PER_PAGE;

	$category_id = intval( $category_id );

	$representative_products = wtai_get_representative_products( $category_id );
	$product_data            = wtai_get_category_products( $category_id, $page, $representative_products, $search, $do_search );

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/category-product-list-dropdown.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get representative products.
 *
 * @param int $category_id Category ID.
 */
function wtai_get_representative_products( $category_id = 0 ) {
	$product_ids = get_term_meta( $category_id, 'wtai_category_representative_products', true );

	if ( ! $product_ids ) {
		$product_ids = array();
	} else {
		// Verify if all product id is still under the category.
		$product_final_ids = array();
		foreach ( $product_ids as $product_id ) {
			// Check if the product belongs to the specified category.
			if ( has_term( $category_id, 'product_cat', $product_id ) ) {
				// Add the product ID to the final list if it belongs to the category.
				$product_final_ids[] = $product_id;
			}
		}

		// Check if the initial product IDs differ from the verified list of product IDs.
		if ( $product_ids !== $product_final_ids ) {
			// Update the term meta with the verified list of product IDs.
			update_term_meta( $category_id, 'wtai_category_representative_products', $product_final_ids );
			$product_ids = $product_final_ids;
		}
	}

	return $product_ids;
}

/**
 * Get category product list html dropdown.
 *
 * @param int $category_id Category ID.
 */
function wtai_get_category_representative_products_html( $category_id = 0 ) {
	if ( ! $category_id ) {
		return;
	}

	$category_id = intval( $category_id );

	$representative_products = wtai_get_representative_products( $category_id );

	ob_start();
	include WTAI_ABSPATH . 'templates/admin/metabox/category-representative-products.php';
	$html = ob_get_clean();

	return $html;
}

/**
 * Get category other details.
 *
 * @param int $category_id Category ID.
 */
function wtai_get_category_other_details( $category_id = 0 ) {
	if ( ! $category_id ) {
		return;
	}

	$detail  = get_term_meta( $category_id, 'wtai_othercategorydetails', true );
	$enabled = get_term_meta( $category_id, 'wtai_othercategorydetails_enabled', true );

	$other_details = array(
		'enabled' => $enabled,
		'value'   => $detail,
		'length'  => mb_strlen( $detail, 'UTF-8' ),
	);

	return $other_details;
}

/**
 * Save category field value.
 *
 * @param int    $category_id The category ID.
 * @param string $field_key The field key.
 * @param string $field_value The field value.
 */
function wtai_save_category_field_value( $category_id, $field_key, $field_value ) {
	$source = get_option( 'wtai_installation_source', '' );

	$taxonomy    = 'product_cat';
	$category_id = intval( $category_id );

	// Prevents html from being stripped from term descriptions.
	foreach ( array( 'pre_term_description' ) as $filter ) {
		remove_filter( $filter, 'wp_filter_kses' );
	}

	if ( 'wordpress-seo-premium' === $source || 'wordpress-seo' === $source ) {
		if ( class_exists( 'WPSEO_Taxonomy_Meta' ) ) {
			$values = WPSEO_Taxonomy_Meta::get_term_meta( $category_id, $taxonomy );

			if ( 'page_title' === $field_key ) {
				$values['wpseo_title'] = $field_value;

				WPSEO_Taxonomy_Meta::set_values( $category_id, $taxonomy, $values );
			} elseif ( 'page_description' === $field_key ) {
				$values['wpseo_desc'] = $field_value;

				WPSEO_Taxonomy_Meta::set_values( $category_id, $taxonomy, $values );
			} elseif ( 'category_description' === $field_key ) {
				wp_update_term(
					$category_id,
					'product_cat',
					array(
						'description' => $field_value,
					)
				);
			} elseif ( 'open_graph' === $field_key ) {
				$values['wpseo_opengraph-description'] = $field_value;

				WPSEO_Taxonomy_Meta::set_values( $category_id, $taxonomy, $values );
			}
		}
	} elseif ( 'seo-by-rank-math' === $source || 'seo-by-rank-math-pro' === $source ) {
		if ( 'page_title' === $field_key ) {
			update_term_meta( $category_id, 'rank_math_title', $field_value );
		} elseif ( 'page_description' === $field_key ) {
			update_term_meta( $category_id, 'rank_math_description', $field_value );
		} elseif ( 'category_description' === $field_key ) {
			wp_update_term(
				$category_id,
				'product_cat',
				array(
					'description' => $field_value,
				)
			);
		} elseif ( 'open_graph' === $field_key ) {
			update_term_meta( $category_id, 'rank_math_facebook_description', $field_value );
		}
	} elseif ( 'all-in-one-seo-pack' === $source ) {
		if ( 'page_title' === $field_key ) {
			update_term_meta( $category_id, '_aioseo_title', $field_value );
		} elseif ( 'page_description' === $field_key ) {
			update_term_meta( $category_id, '_aioseo_description', $field_value );
		} elseif ( 'category_description' === $field_key ) {
			wp_update_term(
				$category_id,
				'product_cat',
				array(
					'description' => $field_value,
				)
			);
		} elseif ( 'open_graph' === $field_key ) {
			update_term_meta( $category_id, '_aioseo_og_description', $field_value );
		}
	} elseif ( 'all-in-one-seo-pack-pro' === $source ) {
		global $wpdb;
		$aoiseo_table = esc_sql( $wpdb->prefix . 'aioseo_terms' );

		// Do insert to table here if not yet exist.
		$exist = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$aoiseo_table} WHERE term_id = %d ORDER BY id DESC LIMIT 1", $category_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		if ( ! $exist ) {
			$wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$aoiseo_table,
				array(
					'term_id' => $category_id,
					'created' => current_time( 'mysql' ),
					'updated' => current_time( 'mysql' ),
				)
			);
		}

		if ( 'page_title' === $field_key ) {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$aoiseo_table,
				array(
					'title' => $field_value,
				),
				array(
					'term_id' => $category_id,
				)
			);
		} elseif ( 'page_description' === $field_key ) {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$aoiseo_table,
				array(
					'description' => $field_value,
				),
				array(
					'term_id' => $category_id,
				)
			);
		} elseif ( 'category_description' === $field_key ) {
			wp_update_term(
				$category_id,
				'product_cat',
				array(
					'description' => $field_value,
				)
			);
		} elseif ( 'open_graph' === $field_key ) {
			$wpdb->update( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$aoiseo_table,
				array(
					'og_description' => $field_value,
				),
				array(
					'term_id' => $category_id,
				)
			);
		}
	}

	return true;
}

/**
 * Should the freemium setup badge be displayed.
 */
function wtai_display_freemium_setup_badge() {
	$display = false;

	$freemium_data = apply_filters( 'wtai_check_freemium_badge_display', false );
	if ( $freemium_data && $freemium_data['display_freemium_badge'] ) {
		$display = true;
	}

	return $display;
}

/**
 * Should the freemium setup badge be displayed.
 */
function wtai_get_freemium_credit_count() {
	$free_premium_credits = 0;

	$freemium_data = apply_filters( 'wtai_check_freemium_badge_display', false );
	if ( $freemium_data && $freemium_data['free_premium_credits'] ) {
		$free_premium_credits = $freemium_data['free_premium_credits'];
	}

	return $free_premium_credits;
}

/**
 * Get the freemium popup html.
 */
function wtai_get_fremium_popup_html() {
	$free_premium_popup_html = '';
	if ( defined( 'WTAI_CREDIT_ACCOUNT_DETAILS' ) && WTAI_CREDIT_ACCOUNT_DETAILS ) {
		ob_start();
		do_action( 'wtai_freemium_popup', WTAI_CREDIT_ACCOUNT_DETAILS );
		$free_premium_popup_html = ob_get_clean();
	}

	return $free_premium_popup_html;
}

/**
 * Get post status label.
 *
 * @param string $post_status The post status.
 */
function wtai_get_post_status_label( $post_status = '' ) {
	$post_status_label = '';
	switch ( $post_status ) {
		case 'private':
			$post_status_label = __( 'Privately Published' );
			break;
		case 'publish':
			$post_status_label = __( 'Published' );
			break;
		case 'future':
			$post_status_label = __( 'Scheduled' );
			break;
		case 'pending':
			$post_status_label = __( 'Pending Review' );
			break;
		case 'draft':
		case 'auto-draft':
			$post_status_label = __( 'Draft' );
			break;
		default:
			$post_status_label = $post_status;
			break;
	}

	return $post_status_label;
}

/**
 * Remove ending new lines and spaces at the end of a string.
 *
 * @param string $string_value String to clean.
 */
function wtai_remove_trailing_new_lines( $string_value ) {
	$string_value = rtrim( $string_value, " \t\n\r\0\x0B\xC2\xA0" );

	return $string_value;
}

/**
 * Get the category image checked status per user preference.
 */
function wtai_get_category_image_checked_status() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}

	$category_is_checked = true;

	$wtai_category_image_checked_status_set = get_user_meta( $user_id, 'wtai_category_image_checked_status_set', true );
	if ( ! $wtai_category_image_checked_status_set ) {
		update_user_meta( $user_id, 'wtai_category_image_checked_status_set', '1' );
		update_user_meta( $user_id, 'wtai_category_image_checked_status', '1' );
	} else {
		$category_is_checked_status = get_user_meta( $user_id, 'wtai_category_image_checked_status', true );

		if ( 1 === intval( $category_is_checked_status ) ) {
			$category_is_checked = true;
		} else {
			$category_is_checked = false;
		}
	}

	return $category_is_checked;
}

/**
 * Get the popup blocker dismiss state.
 */
function wtai_get_popup_blocker_dismiss_state() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return false;
	}

	$is_dismissed = false;

	$keys = array(
		'wtai_popup_blocker_notice_dismissed',
		'wtai_popup_blocker_notice_dismissed_list',
		'wtai_popup_blocker_notice_dismissed_install',
		'wtai_popup_blocker_notice_dismissed_settings',
		'wtai_popup_blocker_notice_dismissed_list_category',
	);

	// Check if any of the keys are dismissed.
	foreach ( $keys as $key ) {
		$dismissed = get_user_meta( $user_id, $key, true );
		if ( 1 === intval( $dismissed ) ) {
			$is_dismissed = true;
			break;
		}
	}

	return $is_dismissed;
}