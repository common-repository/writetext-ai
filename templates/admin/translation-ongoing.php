<?php
/**
 * Ads metabox template
 *
 * @package WriteText
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! WTAI_TRANSLATION_ONGOING ) {
	return;
}

if ( wtai_is_current_locale_en() ) {
	return;
}

// This text is intentionally not translated as it is a notice for translators.
echo '<div class="notice notice-error wtai-lang-error-notice" style="text-align: left;" >
    <p>' . wp_kses( 'Notice: Translation of plugin help text and labels is ongoing. Please stay tuned.', 'post' ) . '</p>
</div>';
