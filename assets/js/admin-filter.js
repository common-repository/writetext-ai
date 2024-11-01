/* jshint -W065 */
/* jshint -W117 */
/* jshint -W119 */
/* jshint -W069 */
/* jshint -W024 */
/* jshint -W062 */
/* jshint -W116 */
/* jshint -W004 */
/* jshint -W083 */

jQuery(document).ready(function( $ ){
    window.lastGenerationTypeSelected = null;

    $(document).on('click', '.wtai-tone-and-styles-select .wtai-reset', function(e){
        var button_select = $(this);
        var type  = button_select.closest('.wtai-tone-and-styles-select').find('.wtai-button-label').data('type');

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_FILTER_OBJ.ajax_url,
            data: {
                action: 'wtai_get_global_settings',
                type: type,
                wtai_nonce: wtai_nonce
            },
            success: function( data ) {
                button_select.closest('.wtai-tone-and-styles-select').find('input[type="checkbox"]:checked').each(function(){
                    $(this).prop('checked', false );
                });
        
                //set default value
                var count = 0;
        
                if ( type == 'tone_and_style' ){
                    var default_style = data.styles;
                    var default_tones = data.tones;
        
                    if( default_style != '' ){
                        $('.postbox-container .wtai-product-styles-cb').prop('checked', false);
        
                        $('.postbox-container .wtai-custom-style-cb').prop('checked', false);
                        $('.postbox-container .wtai-custom-style-text').val('');
        
                        $('.postbox-container .wtai-product-styles-cb').each(function(){
                            if( $(this).val() == default_style ){
                                $(this).prop('checked', true);
                            }
                        });
                    }
        
                    if( default_tones != ''){
                        default_tones = default_tones.split(',');
        
                        $('.postbox-container .wtai-product-tones-cb').prop('checked', false);
        
                        $('.postbox-container .wtai-custom-tone-cb').prop('checked', false);
                        $('.postbox-container .wtai-custom-tone-text').val('');
        
                        $.each(default_tones, function( index, tone_selection ){
                            $('.postbox-container .wtai-product-tones-cb').each(function(){
                                if( $(this).val() == tone_selection ){
                                    $(this).prop('checked', true);
                                }
                            });
                        });
                    }
        
                    //set formal informal states
                    set_disallowed_combinations_single();
        
                    //plus one for style
                    var count = $('.postbox-container .wtai-product-tones-cb:checked').length + 1;
                } 
                else if( type == 'audiences' ) {
                    var default_audiences = data.audiences;
        
                    if( default_audiences != '' ){
                        default_audiences = default_audiences.split(',');
        
                        $('.postbox-container .wtai-product-audiences-cb').prop('checked', false);
        
                        $.each(default_audiences, function( index, audience_selection ){
                            $('.postbox-container .wtai-product-audiences-cb').each(function(){
                                if( $(this).val() == audience_selection ){
                                    $(this).prop('checked', true);
                                }
                            });
                        });    
                    }
        
                    count = $('.postbox-container .wtai-product-audiences-cb:checked').length;
                }
                else{
                    count = 0;
                }
        
                //reset user preference
                save_tones_style_user_preference( type, 'yes' );
        
                button_select.closest('.wtai-tone-and-styles-select').find('.wtai-button-label').find('.wtai-button-num').text(count);
            }
        });

        e.preventDefault();
    });

    function save_tones_style_user_preference( data_type, reset = 'no' ) {
        var tones,style,customToneCb,customToneText,customStyleCb,customStyleText,customStyleRefprod,customStyleRefprodsel = '';

        var tones = [];
        $('.wtai-postbox-process .wtai-product-tones-wrap .wtai-product-item').each(function() {
            var cb = $(this).find('.wtai-product-cb');
            if (cb.is(':checked') == true) {
                var value = cb.val();
                tones.push(value);
            }
        });

        var customToneCb = $('.wtai-tone-and-styles-wrapper').find('input.wtai-custom-tone-cb');
        var customToneText = $('.wtai-tone-and-styles-wrapper').find('input#wtai-custom-tone-text').val();
        if( customToneCb.is(':checked') == true ){
            customToneCb = customToneCb.val();
            customToneText = customToneText;
            tones = [];
        } else {
            customToneCb = '';
            customToneText = '';
        }

        var style_radio = $('.wtai-postbox-process .wtai-product-styles-wrap').find('input:checked').val();

        if( style_radio == 'wtaCustom' || style_radio == 'wtaRefprod' ) {
            style = '';
            if( style_radio == 'wtaCustom' ) {
                customStyleCb = style_radio;
                customStyleText = $('.wtai-tone-and-styles-wrapper').find('input#wtai-custom-style-text').val();
            } else if ( style_radio == 'wtaRefprod' ) {
                customStyleRefprod = style_radio;
                customStyleRefprodsel = $('.wtai-tone-and-styles-wrapper').find('select.wtai-custom-style-ref-prod-sel').val();
            }
        } else {
            style = style_radio;
        }

        //audiences
        var audiences = [];
        $('.wtai-postbox-process .wtai-product-audiences-wrap .wtai-product-item').each(function() {
            var cb = $(this).find('.wtai-product-cb');
            if (cb.is(':checked') == true) {
                var value = cb.val();
                audiences.push(value);
            }
        });

        var wtai_nonce = get_wp_nonce();
       
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_FILTER_OBJ.ajax_url,
            data: {
                action: 'wtai_save_tones_styles_option_user_preference',
                wtai_installation_tones: tones.join(','),
                wtai_installation_styles: style,
                audiences: audiences.join(','),
                customToneCb: customToneCb,
                customToneText: customToneText,
                customStyleCb: customStyleCb,
                customStyleText: customStyleText,
                customStyleRefprod: customStyleRefprod,
                customStyleRefprodsel: customStyleRefprodsel,
                reset: reset,
                wtai_nonce: wtai_nonce
            },
            success: function( data ) {
                if( data.is_premium != '1' ){
                    if( data_type == 'tones' ){
                        $('.wtai-tone-and-styles-wrapper').find('input#wtai-custom-tone-text').val('');
                    }

                    if( data_type == 'styles'  ){
                        $('.wtai-tone-and-styles-wrapper').find('input#wtai-custom-style-text').val('');
                    }
                }

                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }
        });
    }

    function get_wp_nonce(){
        var nonce = $('.wtai-list-table').attr('data-product-nonce');
        return nonce;
    }

    $(document).on('keyup', '.wtai-custom-tone-text, .wtai-custom-style-text', function(){
        var xval = $(this).val();
        var dataType = $(this).attr('data-type');
        var count = 0;
        var custom_tone_text = $(this).closest('.wtai-tone-and-styles-wrapper').find('input#wtai-custom-tone-text').val();
        if( dataType == 'custom-tone' && custom_tone_text != '' ) {
            count = 1;
        } else {
            count = $(this).closest('.wtai-tone-and-styles-wrapper').find('input[type="radio"]:checked').length;
        }

        if( $(this).closest('.wtai-tone-and-styles-wrapper').hasClass( 'wtai-tone-and-style-form-wrapper' ) ){
            count += $(this).closest('.wtai-tone-and-styles-wrapper').find('input[type="radio"]:checked').length;
        }

        if( xval.trim() != '' ){
            if( dataType == 'tone' ){
                $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
                $(this).closest('label').find('.wtai-custom-tone-cb').prop('checked', true);
                $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-label').removeClass('warning'); 
                $('.wtai-product-tones-cb').removeClass('warning');
                $('.wtai-custom-tone-cb').removeClass('warning');
                $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-num').text(count);
            }
            if( dataType == 'style' ){
                $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
                $(this).closest('.wtai-product-wrap').find('.wtai-custom-style-ref-prod').prop('checked', false);
                $(this).closest('.wtai-product-wrap').find('.wtai-custom-style-ref-prod-sel').val('');
                $(this).closest('label').find('.wtai-custom-style-cb').prop('checked', true);
                $('.wtai-product-styles-cb').removeClass('warning');
            }
        } else {
            if( dataType == 'tone' ){
                if ( $('.wtai-wp-filter .wtai-product-tones-wrap').find('.wtai-product-cb:checked').length <= 0 ){
                    $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-label').addClass('warning'); 
                    $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-num').text('0');
                } else {
                    $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
                }
            }
        }
    });

    $(document).on('click', '.wtai-wp-filter .wtai-product-tonestyles-container input[type="checkbox"]', function() {
        var cb = $(this);
        var selected = cb.closest('.product_not_all_trigger').find('input[type="checkbox"]:checked').length;
        
        if( cb.hasClass('wtai-highlight-incorrect-pronouns-cb') ){
            return;
        }

        var dataType = $(this).attr('data-type');

        if ( typeof dataType === 'undefined' || dataType === '' || dataType != 'custom-tone' ) {
            if( $(this).closest('.wtai-tone-and-styles-wrapper').find('input.wtai-custom-tone-cb').is(':disabled') == false ){
                $(this).closest('.wtai-tone-and-styles-wrapper').find('input.wtai-custom-tone-cb').prop('checked', false);
                $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-custom-tone-text').val('');
                $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-char-count-wrap .wtai-char-count').text('0');
            }
        }

        var count = 0;
        if( dataType == 'custom-tone' ) {
            count = 1;
        } else {
            count = $(this).closest('.wtai-tone-and-styles-wrapper').find('input[type="checkbox"]:checked').length;
            $('.wtai-custom-tone-text').removeClass('warning');
        }

        if( $(this).closest('.wtai-tone-and-styles-wrapper').hasClass( 'wtai-tone-and-style-form-wrapper' ) ){
            count += $(this).closest('.wtai-tone-and-styles-wrapper').find('input[type="radio"]:checked').length;
        }

        $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-num').text(count); 
        
        if( selected > 0 ) {
            cb.closest('.product_not_all_trigger').find('input[type="checkbox"]').removeClass('warning'); 
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-label').removeClass('warning'); 
        } else {
            if( $(this).hasClass('wtai-product-audiences-cb') ){
                //do not check required if audience
            }
            else{
                cb.closest('.product_not_all_trigger').find('input[type="checkbox"]').addClass('warning'); 
                $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-button-label').addClass('warning'); 
            }
        }

        set_disallowed_combinations_single();
        save_tones_style_user_preference( dataType, 'no' );
    });

    $(document).on('blur', '.wtai-custom-tone-text, .wtai-custom-style-text', function(){
        if( $(this).val().trim() == '' ){
            $(this).addClass('warning');
        } else {
            $(this).removeClass('warning'); 
        }

        var type = $(this).attr('data-type');

        set_disallowed_combinations_single();
        save_tones_style_user_preference( type, 'no' );
    });

    $(document).on('change', '.wtai-product-tonestyles-container input[type="radio"]', function() {
        var dataType = $(this).attr('data-type');

        if ( typeof dataType === 'undefined' || dataType === '' || ( dataType != 'custom-style' && dataType != 'custom-style-refprod' ) ) {
            $(this).closest('.wtai-tone-and-styles-wrapper').find('input.wtai-custom-style-cb').prop('checked', false);
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-custom-style-text').val('');
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-custom-style-text').removeClass('warning');
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-char-count').text('0');

            $(this).closest('.wtai-product-wrap').find('.selectize-input').removeClass('warning');
            
        } else if ( dataType == 'custom-style' ) {
            $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
            $(this).closest('.wtai-product-wrap').find('.selectize-input').removeClass('warning');
            
        } else if ( dataType == 'custom-style-refprod' ) {
            $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
            $(this).closest('.wtai-tone-and-styles-wrapper').find('input.wtai-custom-style-cb').prop('checked', false);
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-custom-style-text').val('');
            $(this).closest('.wtai-tone-and-styles-wrapper').find('.wtai-custom-style-text').removeClass('warning');
        }

        set_disallowed_combinations_single();
        save_tones_style_user_preference( dataType, 'no' );
    });

    $(document).on('change', '.wtai-custom-style-ref-prod', function(){
        if( $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
            $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
        }
        if( $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
            $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
        }

        if ( $(this).is(':checked') ){
            $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.generateCTAText );
            
            $(this).closest('.wtai-ref-product-form-postbox-wrapper').find('.wtai-custom-style-ref-prod-sel').removeClass('disabled');
            $(this).closest('.wtai-ref-product-form-postbox-wrapper').find('.wtai-custom-style-ref-prod').prop('checked', true);
            $(this).closest('.wtai-postbox-process').find('.wtai-button-label').addClass('disabled');
            $(this).closest('.wtai-postbox-process').find('.wtai-button-label').removeClass('warning');

            $('.wtai-generate-wrapper .button-primary.toggle').addClass('disabled');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

            setTimeout(function() {
                updateToolTipForStyleTone( WTAI_FILTER_OBJ.tooltipDisableToneStyleMessage1, 1 );      
                updateToolTipForAudience( WTAI_FILTER_OBJ.tooltipDisableAudienceMessage1, 1 );      
                updateToolTipForReferenceProduct( '...', 0, 'full' );      
            }, 300);

            toggleRewriteDisabledTooltip( WTAI_FILTER_OBJ.tooltipDisableRewriteMessage1, 0 );
        } 
        else {
            $(this).closest('.wtai-ref-product-form-postbox-wrapper').find('.wtai-custom-style-ref-prod-sel').addClass('disabled');
            $(this).closest('.wtai-ref-product-form-postbox-wrapper').find('.wtai-custom-style-ref-prod').prop('checked', false);
            $(this).closest('.wtai-postbox-process').find('.wtai-button-label').removeClass('disabled');
            $(this).closest('.wtai-ref-product-form-postbox-wrapper').find('.selectize-input').removeClass('warning');

            $('.wtai-generate-wrapper .button-primary.toggle').removeClass('disabled');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

            setTimeout(function() {
                updateToolTipForStyleTone( WTAI_FILTER_OBJ.tooltipDisableToneStyleMessage1, 0 );      
                updateToolTipForAudience( WTAI_FILTER_OBJ.tooltipDisableAudienceMessage1, 0 );     
                updateToolTipForReferenceProduct( '...', 0, 'full' );       
            }, 300);

            toggleRewriteDisabledTooltip( WTAI_FILTER_OBJ.tooltipDisableRewriteMessage1, 1 );
        }   

        //disable alt images for rewrite and reference product
        //disable_alt_images_for_reference_and_rewrite();

        //display of reference count
        display_reference_product_count();

        //update generate all credit count
       //updateReferenceButtonCreditCount();
    });

    $(document).on('click', '.wtai-custom-tone-cb', function(){
        if ( $(this).is(':checked') ){
            $(this).closest('.wtai-product-wrap').find('.wtai-product-cb').prop('checked', false);
        }         
    });

    $(document).on('click', '.wtai-custom-tone-cb, .wtai-custom-style-cb', function(){
        set_disallowed_combinations_single();
    });

    function initializeToolTipForGenerateFilter(){
        try{        
            $('.wtai-tone-style-filter-label').each(function(){
                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': 'bottom',
                    'arrow': true,
                    debug: false,
                    contentAsHTML: true,
                    content: '...',
                    trigger: 'custom',
                    triggerOpen: {
                        mouseenter: true,
                        click: true,
                        touchstart: true,
                        tap: true
                    },
                    triggerClose: {
                        mouseleave: true,
                        tap: true,
                        touchleave: true,
                        scroll: true
                    }
                });

                //disable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){
        }

        try{ 
            $('.wtai-audience-filter-label').each(function(){
                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': 'bottom',
                    'arrow': true,
                    debug: false,
                    contentAsHTML: true,
                    content: '...',
                    trigger: 'custom',
                    triggerOpen: {
                        mouseenter: true,
                        click: true,
                        touchstart: true,
                        tap: true
                    },
                    triggerClose: {
                        mouseleave: true,
                        tap: true,
                        touchleave: true,
                        scroll: true
                    }
                });

                //disable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){
        }

        try{ 
            //full
            $('.wtai-filter-main-wrap .wtai-reference-product-filter').each(function(){
                var $elem = $(this);

                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': 'bottom',
                    'arrow': true,
                    debug: false,
                    contentAsHTML: true,
                    content: '...',
                    trigger: 'custom',
                    triggerOpen: {
                        mouseenter: true,
                        click: true,
                        touchstart: true,
                        tap: true
                    },
                    triggerClose: {
                        mouseleave: true,
                        tap: true,
                        touchleave: true,
                        scroll: true
                    }
                });

                //disabled this by default
                $elem.tooltipster('disabled');
            });
        }
        catch( err ){
        }

        try{ 
            $('.wtai-cta-radio-option-label').each(function(){
                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': 'bottom',
                    'arrow': true,
                    debug: false,
                    contentAsHTML: true,
                    content: '...',
                    trigger: 'custom',
                    triggerOpen: {
                        mouseenter: true,
                        click: true,
                        touchstart: true,
                        tap: true
                    },
                    triggerClose: {
                        mouseleave: true,
                        tap: true,
                        touchleave: true,
                        scroll: true
                    }
                });

                //enable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){
        }
    }

    function set_disallowed_combinations_single(){
        var disallowed_combinations = WTAI_FILTER_OBJ.disallowedCombinations;

        var checked_ids = [];

        //applies to tones and audiences
        if( $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="checkbox"]').length ){
            $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
                if( $(this).is(':checked') && $(this).val() != 'wtaCustom' ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        //applies to style
        if( $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="radio"]').length ){
            $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="radio"]').each(function(){
                if( $(this).is(':checked') && $(this).val() != 'wtaCustom' ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
            if( $(this).hasClass('wtai-custom-tone-cb') && $(this).is(':disabled') ){

            }
            else{
                $(this).closest('label').removeClass('disabled-label');
                $(this).prop('disabled', false);
            }
        });

        $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="radio"]').each(function(){
            if( $(this).hasClass('wtai-custom-style-cb') && $(this).is(':disabled') ){

            }
            else{
                $(this).closest('label').removeClass('disabled-label');
                $(this).prop('disabled', false);
            }
        });

        //disable the tooltip first
        setTimeout( function(){
            try{
                if ($('.tooltip-generate-filter').hasClass('tooltipstered')) {
                    $('.tooltip-generate-filter').each(function(){
                        $(this).tooltipster('disable');
                    });
                }
            }
            catch(err) {
            }
        }, 300 );
        
        $.each(disallowed_combinations, function( index, combinationData ){
            var selectedCombinations = [];
            var disabledCombinations = [];
            var combinations = combinationData['combination'];
            $.each(combinations, function( index2, combination ){
                var combID = combination.id;

                if (checked_ids.indexOf(combID) !== -1) {
                    selectedCombinations.push( combination );
                }
                else{
                    disabledCombinations.push( combination );
                }
            });

            var shouldDisableLength = combinations.length - 1;
            if( selectedCombinations.length == shouldDisableLength && disabledCombinations.length ){
                $.each(disabledCombinations, function( index3, combinationDisable ){
                    var combID = combinationDisable.id;
                    var combType = combinationDisable.type.toLowerCase();
    
                    if( combType == 'tone' ){
                        if( $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').length ){
                            $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');

                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        try{
                                            $inputcb.closest('label').tooltipster('enable');
                                        }
                                        catch(err) {
                                        }
                                    }, 300 );
                                }
                            });
                        }
                    }
                    else if( combType == 'style' ){
                        if( $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="radio"]').length ){
                            $('.wtai-wp-filter .wtai-product-tonestyles-container input[type="radio"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    
                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        try{
                                            $inputcb.closest('label').tooltipster('enable');
                                        }
                                        catch(err) {
                                        }
                                    }, 300 );
                                }
                            });
                        }
                    }
                    else if( combType == 'audience' ){
                        if( $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').length ){
                            $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    
                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        try{
                                            $inputcb.closest('label').tooltipster('enable');
                                        }
                                        catch(err) {
                                        }
                                    }, 300 );
                                }
                            });
                        }
                    }
                });                
            }
        });
    }

    function updateToolTipForStyleTone( tooltipMessage, isEnabled = 1 ){
        try{
            $('.wtai-tone-style-filter-label').each(function(){
                $(this).tooltipster('content', tooltipMessage);
    
                if( isEnabled == 1 ){
                    $(this).tooltipster('enable');
                }
                else{
                    $(this).tooltipster('disable');
                }
            });
        }
        catch( err ){
        }
    }

    function updateToolTipForAudience( tooltipMessage, isEnabled = 1 ){
        try{
            $('.wtai-audience-filter-label').each(function(){
                $(this).tooltipster('content', tooltipMessage);

                if( isEnabled == 1 ){
                    $(this).tooltipster('enable');
                }
                else{
                    $(this).tooltipster('disable');
                }   
            });
        }
        catch( err ){
        }
    }

    function updateToolTipForReferenceProduct( tooltipMessage, isEnabled = 1, display = 'full' ){
        try{
            $('.wtai-reference-product-filter').each(function(){
                $(this).tooltipster('content', tooltipMessage);

                if( isEnabled == 1 && display == 'full' ){
                    $(this).tooltipster('enable');
                }
                else{
                    $(this).tooltipster('disable');
                }   
            });
        }
        catch(err) {
        }
    }

    function display_reference_product_count(){
        var referenceProduct = $('select.wtai-custom-style-ref-prod-sel').val();
        var isChecked = $('.wtai-custom-style-ref-prod').is(':checked');

        if( referenceProduct && isChecked ){
            var selectedReferenceproduct = $('select.wtai-custom-style-ref-prod-sel option:selected').text();
            selectedReferenceproduct = selectedReferenceproduct.replace(/\(#\d+\)/g, '');

            if( $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
                $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
            }
            if( $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
                $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
            }

            $('.wtai-metabox-product_description').find('.wtai-text-count-details').prepend( WTAI_FILTER_OBJ.referenceCharCountHTML );
            $('.wtai-metabox-product_excerpt').find('.wtai-text-count-details').prepend( WTAI_FILTER_OBJ.referenceCharCountHTML );

            $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap .wt-reference-count-prod-name').text( selectedReferenceproduct );
            $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap .wt-reference-count-prod-name').attr( 'title', selectedReferenceproduct );
            $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap .wt-reference-count-prod-name').text( selectedReferenceproduct );
            $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap .wt-reference-count-prod-name').attr( 'title', selectedReferenceproduct );
            
            var referenceProductArr = referenceProduct.split('-');
            $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap .wtai-char-count').text( referenceProductArr[1] );
            $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap .wtai-char-count').text( referenceProductArr[2] );
            $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap .word-count').text( referenceProductArr[3] );
            $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap .word-count').text( referenceProductArr[4] );
        }
        else{
            if( $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
                $('.wtai-metabox-product_description .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
            }
            if( $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').length ){
                $('.wtai-metabox-product_excerpt .wtai-text-count-details .wtai-reference-count-main-wrap').remove();
            }
        }
    }

    function toggleRewriteDisabledTooltip( tooltipMessage = '', enabled = 1 ){
        var showTooltip = 0;

        if( $('#postbox-container-2').find('.wtai-metabox .wtai-checkboxes:checked').length > 0 ){        
            if( enabled == 1 ){
                if( hasRewriteText() ){
                    $('#wtai-cta-generate-type-generate').prop('checked', true);
                    $('#wtai-cta-generate-type-rewrite').prop('disabled', false);
                    $('#wtai-cta-generate-type-rewrite').closest('label').removeClass('disabled');
                }
                else{
                    showTooltip = 1;
                }
            }
            else{
                $('#wtai-cta-generate-type-generate').prop('checked', true);
                $('#wtai-cta-generate-type-rewrite').prop('disabled', true);
                $('#wtai-cta-generate-type-rewrite').closest('label').addClass('disabled');
                
                showTooltip = 1;
            }
        }

        toggleRewriteDisabledTooltipState( tooltipMessage, showTooltip );
    }

    function toggleRewriteDisabledTooltipState( tooltipMessage = '', showTooltip = 1 ){
        try{
            $('.wtai-cta-radio-option-label').each(function(){
                $(this).tooltipster('content', tooltipMessage );
    
                if( showTooltip == 1 ){
                    $(this).tooltipster('enable');
                }
                else{
                    $(this).tooltipster('disable');
                }
            });
        }
        catch( err ){
        }
    }

    function hasRewriteText(){
        var hasDataText = false;
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var content = $(this).find('.wtai-columns-1').find('.wtai-text-message').text();
            var cbChecked = $(this).find('.wtai-checkboxes').is(':checked');
           
            if ( content.trim() != '' && cbChecked ) {
                hasDataText = true;                
            }
        });

        return hasDataText;
    }

    $(document).on('click', '.wtai-regenerate-audience', function(e){
        e.preventDefault();

        if( $(this).hasClass('disabled') ){
            return;
        }

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        $('.wtai-input-text-suggested-audiance').val( '' );
        $('.wtai-suggested-audience-input-container').find('.wtai-char-count-wrap .wtai-char-count').html('0');

        reloadSuggestedAudience( 1 );
    });

    function reloadSuggestedAudience( clearAllText ){
        var post_id = $('#wtai-edit-post-id').val();
        var sa_keywords = [];
        var k = 0;
        $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                sa_keywords[k] = keyword;
                k++;
            }
        });

        getSuggestedAudiences( post_id, sa_keywords.join('|'), clearAllText );
    }

    var suggestedAudienceAjax = null;

    function getSuggestedAudiences( post_id, keywords, clearAllText ){
        $('.wtai-input-text-suggested-audiance').val('');
       
        $('.suggested_audience-list').html('<li><span class="typing-cursor">&nbsp;</span></li>');
        
        var date = new Date();
        var offset = date.getTimezoneOffset();

        if( suggestedAudienceAjax != null ){
            suggestedAudienceAjax.abort();
        }

        var wtai_nonce = get_wp_nonce();

        var data_type = $('.wtai-input-text-suggested-audiance').attr('data-type');

        suggestedAudienceAjax = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_FILTER_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_suggested_audience',
                product_id: post_id,
                browsertime : offset,
                keywords : keywords,
                clearAllText : clearAllText,
                wtai_nonce : wtai_nonce,
                data_type : data_type,
            },
            success: function(data) {
                if ( data.success != '1' && data.message ){
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }

                    //display general error message during generation
                    $('<div id="message" class="wtai-nonce-error error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );

                    $('.wtai-slide-right-text-wrapper').animate({ scrollTop: 0 }, 'fast');
                }
                else{
                    if( data.access == '1' ){
                        renderSuggestedAudience( data.results );
                    }

                    suggestedAudienceAjax = null;
                }
            }
        });
    }

    function renderSuggestedAudience( response_data ){
        if( response_data.suggested_audiences ){
            var html = '';
            $.each(response_data.suggested_audiences, function( index, value ) {
                html += '<li class="result" data-value="'+value+'" ></li>';
            });
            $('.suggested_audience-list').html(html);

            $('.suggested_audience-list li.result').each(function(){
                var suggestedValue = $(this).attr('data-value');

                wtaiTypeWriterHTMLBox( $(this), suggestedValue, 0, 20 );
            });
            
        }

        if( response_data.selected_audience ){
            $('.wtai-input-text-suggested-audiance').val(response_data.selected_audience);

            var selectedAudienceCharLength = response_data.selected_audience.length;
            $('.wtai-suggested-audience-input-container').find('.wtai-char-count-wrap .wtai-char-count').html(selectedAudienceCharLength);
        }
        else{
            $('.wtai-input-text-suggested-audiance').val('');
            $('.wtai-suggested-audience-input-container').find('.wtai-char-count-wrap .wtai-char-count').html('0');
        }

        if( response_data.error ){
            $('.suggested_audience-list').html('<li>'+response_data.error+'</li>');
        }
    }

    $(document).on('click', '.suggested_audience-list li.result', function(){
        var selected_text = $(this).text();

        $('.wtai-input-text-suggested-audiance').val( '' );

        wtaiTypeWriterTextBox( $('.wtai-input-text-suggested-audiance'), selected_text, 0, 20 );
    });

    $(document).on('change', '.wtai-input-text-suggested-audiance', function(){
        var customAudience = $(this).val();
        var post_id = $('#wtai-edit-post-id').val();
        var data_type = $(this).attr('data-type');

        var date = new Date();
        var offset = date.getTimezoneOffset();
        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_FILTER_OBJ.ajax_url,
            data: {
                action: 'wtai_set_custom_audience_callback',
                product_id: post_id,
                browsertime : offset,
                customAudience : customAudience,
                wtai_nonce: wtai_nonce,
                data_type: data_type,
            },
            success: function() {
            }
        });
    });

    // Hide the filter when clicking outside of the document.
    $(document).on('click', function(e){
        if ( $(e.target).closest('.wtai-tone-and-styles-select').length == 0  ) {
            var force_close_tone_select = false;
            if( $(e.target).hasClass( 'wtai-premium-modal-overlay-wrap' ) || $(e.target).closest('.wtai-premium-modal-wrap').length > 0 ){
                force_close_tone_select = true;
            }

            if ( $('.wtai-tone-and-styles-select').hasClass('wtai-active') && force_close_tone_select == false ) {
                $('.wtai-tone-and-styles-select').removeClass('wtai-active');
            }
        }
    });

    $(document).on('click', '.wtai-cta-radio', function( e ){    
        if( $(this).closest('label').hasClass('disabled') ){
            $(this).prop('disabled', true);
            e.preventDefault();
            e.stopPropagation();
            return;
        }    

        var cta_type = $(this).val();

        window.lastGenerationTypeSelected = cta_type;

        if( cta_type == 'rewrite' ){
            $('.wtai-page-generate-all').attr('data-rewrite', '1');
            $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.rewriteCTAText );

            $('.wtai-custom-style-ref-prod').prop('disabled', true);
            $('.wtai-custom-style-ref-prod').prop('checked', false);
            $('.wtai-custom-style-ref-prod').closest('label').addClass('disabled-label');
            $('.wtai-custom-style-ref-prod-sel').addClass('disabled');

            setTimeout(function() {
                updateToolTipForStyleTone( WTAI_FILTER_OBJ.tooltipDisableToneStyleMessage1, 0 );      
                updateToolTipForAudience( WTAI_FILTER_OBJ.tooltipDisableAudienceMessage1, 0 );      
                updateToolTipForReferenceProduct( WTAI_FILTER_OBJ.tooltipDisableReferenceMessage2, 1, 'full' );      
            }, 300);
        }
        else{
            $('.wtai-page-generate-all').attr('data-rewrite', '0');
            $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.generateCTAText );

            $('.wtai-postbox-process-style-tone-wrapper .wtai-button-label').removeClass('disabled-select');

            if( $('.wtai-custom-style-ref-prod').closest('.wtai-reference-product-label-wrapper').hasClass('wtai-disable-premium-feature') == false ){
                $('.wtai-custom-style-ref-prod').prop('disabled', false);
                $('.wtai-custom-style-ref-prod').closest('label').removeClass('disabled-label');
            }
            
            if( $('.wtai-custom-style-ref-prod-sel').val() == '' ){
                $('.wtai-custom-style-ref-prod').prop('checked', false);
            }
            
            $('.wtai-custom-style-ref-prod-sel').removeClass('disabled');

            updateToolTipForStyleTone( WTAI_FILTER_OBJ.tooltipDisableToneStyleMessage1, 0 );      
            updateToolTipForAudience( WTAI_FILTER_OBJ.tooltipDisableAudienceMessage1, 0 );  
            updateToolTipForReferenceProduct( WTAI_FILTER_OBJ.tooltipDisableReferenceMessage2, 1, 'partial' );
        }
    });

    $(document).on('click', '.wtai-restore-global-settings', function( e ){
        e.preventDefault();

        $('.wtai-global-loader').addClass('wtai-is-active');

        if( $('body.wtai-open-single-slider').length && $('#wtai-edit-post-id').length ){
            $('.wtai-ai-logo').addClass('wtai-hide');
        }

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_FILTER_OBJ.ajax_url,
            data: {
                action: 'wtai_reset_user_preferences',
                wtai_nonce : wtai_nonce
            },
            success: function(data) {
                //display the popup

                if( $('body.wtai-open-single-slider').length && $('#wtai-edit-post-id').length ){
                    $('.wtai-tone-and-styles-select .wtai-reset').trigger('click');
                }

                //reset bulk settings
                $(document).trigger('wtai_reset_global_settings', data );

                setTimeout(function(){
                    $('#wtai-restore-global-setting-completed').show();
                }, 500);
            }
        });
    });

    $('#wtai-restore-global-setting-completed .wtai-loading-details-container .wtai-loading-wtai-header-wrapper .wtai-loading-button-action').on('click', function( e ){
        e.preventDefault();

        $('#wtai-restore-global-setting-completed').hide();
    });

    // Hide global settings popup when clicked.
    $(document).click(function(e) {
        var popup = $('#wtai-restore-global-setting-completed');

        if (!popup.is(e.target) && popup.has(e.target).length === 0) {
            if ($('#wtai-restore-global-setting-completed').is(':visible')) {
                $('#wtai-restore-global-setting-completed').hide();
            }
        }
    });

    // Event to update rewrite disable state
    $(document).on('wtai_rewrite_disabled_state', function(e, args){
        e.stopImmediatePropagation();

        var tooltipMessage = args.tooltipMessage;
        var showTooltip = args.showTooltip;

        toggleRewriteDisabledTooltipState( tooltipMessage, showTooltip );
    });

    // Event to initialize generate filter tooltip
    $(document).on('wtai_initialize_generate_filter_tooltip', function(e){
        e.stopImmediatePropagation();

        initializeToolTipForGenerateFilter();
    });

    // Event to initialize generate filter tooltip
    $(document).on('wtai_set_disallowed_combinations_single', function(e){
        e.stopImmediatePropagation();

        set_disallowed_combinations_single();
    });

    // Event to update rewrite disable state
    $(document).on('wtai_update_tooltip_for_reference_product', function(e, args){
        e.stopImmediatePropagation();

        var tooltipMessage = args.tooltipMessage;
        var isEnabled = args.isEnabled;
        var display = args.display;

        updateToolTipForReferenceProduct( tooltipMessage, isEnabled, display );
    });

    // Event to display reference product count
    $(document).on('wtai_display_reference_product_count', function(e){
        e.stopImmediatePropagation();

        display_reference_product_count();
    });

    // Event to update rewrite disable state
    $(document).on('wtai_toggle_rewrite_disabled_state', function(e, args){
        e.stopImmediatePropagation();

        var tooltipMessage = args.tooltipMessage;
        var showTooltip = args.showTooltip;

        toggleRewriteDisabledTooltipState( tooltipMessage, showTooltip );
    });

    // Event to reload suggested audience
    $(document).on('wtai_reload_suggested_audience', function(e, args){
        e.stopImmediatePropagation();

        var clearAllText = args.clearAllText;

        reloadSuggestedAudience( clearAllText );
    });

    // Event to render suggested audience
    $(document).on('wtai_render_suggested_audience', function(e, args){
        e.stopImmediatePropagation();

        var response_data = args.response_data;

        renderSuggestedAudience( response_data );
    });
});