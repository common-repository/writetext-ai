/* jshint -W065 */
/* jshint -W117 */

jQuery(document).ready(function($){
    $('.wtai-card-wrapper').on('click', 'a.wtai-next', function(e){
        var event = $(this);
        var hasError = false;
        var varStepData = {};
        if ( ! event.hasClass('disabled') ){
            var step = event.data('step');
            var seoRadioList = '';

            event.addClass('disabled');

            if ( 2 === parseInt( step ) ){
                if ( $('.wtai-seo-button-radio-list:checked').length > 0 ){
                    seoRadioList =  $('.wtai-seo-button-radio-list:checked').val();
                } else if ($('.wtai-seo-button-hidden-list').length > 0 ) {
                    seoRadioList =  $('.wtai-seo-button-hidden-list').val();
                } else {
                    seoRadioList = '';
                }

                varStepData  = {
                    action: 'wtai_get_process_seo_step', 
                    step: step, 
                    seo_choice : seoRadioList
                };
            } else if ( 3 === parseInt( step ) ) {
                var productAttr = [];
                $('.wtai-product-attr-container').find('.wtai-product-attr-cb').each( function(){
                    if ( $(this).is( ':checked' ) ) {
                        productAttr.push($(this).val());
                    }   
                }); 

                var tones = [];
                $('.wtai-product-tonestyles-container').find('.wtai-product-tones-cb').each( function(){
                    if ( $(this).is( ':checked' ) ) {
                        tones.push($(this).val());
                    }   
                });

                var scrolled = false;
                $('.wtai-product-tonestyles-container').find('.wtai-product-tones-cb').removeClass('warning');
                if( 0 === tones.length ){
                    $('.wtai-product-tonestyles-container').find('.wtai-product-tones-cb').addClass('warning');
                    hasError = true;

                    $('html, body').animate({
                        scrollTop: $('.wtai-step-3-wrapper').offset().top
                    }, 'fast');

                    scrolled = true;
                }

                $('.wtai-product-tonestyles-container').find('.wtai-product-styles-cb').removeClass('warning');
                if( 0 === $('.wtai-product-tonestyles-container').find('.wtai-product-styles-cb:checked').length ){
                    $('.wtai-product-tonestyles-container').find('.wtai-product-styles-cb').addClass('warning');

                    hasError = true;

                    if( ! scrolled ){
                        $('html, body').animate({
                            scrollTop: $('.wtai-step-3-wrapper').offset().top
                        }, 'fast');
                    }
                }

                var audiences = [];
                $('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb').each( function(){
                    if ( $(this).is( ':checked' ) ) {
                        audiences.push($(this).val());
                    }   
                }); 
                
                varStepData  =  {
                    action: 'wtai_get_process_seo_step', 
                    step: step, 
                    select_text_tones          : tones.join(','),
                    select_text_audiences      : audiences.join(','),
                    select_product_attr        : productAttr.join(','),
                    select_text_styles         : $('.wtai-product-tonestyles-container').find('.wtai-product-styles-cb:checked').val(),
                    product_description_min    : $('#wtai-installation-product-description-min').val(),
                    product_description_max    : $('#wtai-installation-product-description-max').val(),
                    product_excerpt_min        : $('#wtai-installation-product-excerpt-min').val(),
                    product_excerpt_max        : $('#wtai-installation-product-excerpt-max').val(),
                    category_description_min   : $('#wtai-installation-category-description-min').val(),
                    category_description_max   : $('#wtai-installation-category-description-max').val()
                };
            } else {
                varStepData  = {
                    action: 'wtai_get_process_seo_step', 
                    step: step
                };
            }

            if( hasError ){
                event.removeClass('disabled');
                e.preventDefault();
                return;
            }

            var wtai_nonce = $('#wtai-install-nonce').val();
            varStepData['wtai_nonce'] = wtai_nonce;

            $.ajax({
                type: 'POST',
                url: WTAI_OBJ.ajaxUrl,
                data: varStepData,
                success: function(data) {
                    var position = data.search('{');
                    if ( 0 === parseInt( position ) ){
                        data = $.parseJSON( data );
                    } else {
                        data = data.split('{');
                        data = $.parseJSON( '{'+data[1] );
                    }
                    
                    var status = data.status;
                    var resStep = '';
                    var resMessage = '';

                    if ( status ){
                        if ( $( '.wtai-cart-install-wrapper' ).find('#message').length > 0  ){
                            $( '.wtai-cart-install-wrapper' ).find('#message').remove();
                        }
                        
                        resStep = parseInt( data.step );
                        if ( 5 === resStep ){
                            location.href =  WTAI_OBJ.adminPageSettings;
                        } else {
                            for (var ctrStep = 1; ctrStep < resStep; ctrStep++) {
                                $('.wtai-card-container-wrapper').find('.wtai-step-'+ctrStep+'-wrapper').removeClass('wtai-active');
                                $('.wtai-card-container-wrapper').find('.wtai-step-'+ctrStep+'-wrapper').addClass('wtai-completed');   
                            }
                            $('.wtai-card-container-wrapper').find('.wtai-step-'+resStep+'-wrapper').addClass('wtai-active');    
                        }
                    } else {
                        resStep = data.step;
                        resMessage = data.message;

                        if( resMessage == '' && resStep != '' ){
                            resMessage = resStep;
                        }

                        if ( $( '.wtai-cart-install-wrapper' ).find('#message').length > 0  ){
                            $( '.wtai-cart-install-wrapper' ).find('#message').remove();
                        }
                        $('<div id="message" class="error notice is-dismissible"><p>'+resMessage+' </p></div>').insertAfter( $( '.wtai-cart-install-wrapper' ).find('.wtai-site-title') );
                        event.removeClass('disabled');

                        $('html, body').animate({ scrollTop: 0 }, 'fast');
                    }
                }
            });
        }
        e.preventDefault();
    });

    $('.wtai-seo-lists').on('click', '.wtai-seo-button-radio-list', function(){
        var buttonLabel = $(this).attr('data-buttonlabel');
        $(this).closest('.wtai-content').find('.wtai-next > span').text(buttonLabel);
    }); 

    loadGenerateFilterTooltip();

    function loadGenerateFilterTooltip(){
        $('.tooltip-generate-filter').each(function(){
            $(this).tooltipster({
                'theme': 'tooltipform-default',
                'position': 'bottom',
                'arrow': true,
                debug: false,
                contentAsHTML: true,
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

            $(this).hover(function(){
                $(this).attr('tooltip-data', $(this).attr('title'));
                $(this).removeAttr('title');
              }, function(){
                $(this).attr('title', $(this).attr('tooltip-data'));
                $(this).removeAttr('tooltip-data');
            });

            //disable this by default
            $(this).tooltipster('disable');
        });
    }

    setDisallowedCombinations();
    $(document).on('click', '.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb, .wtai-product-tones-wrap .wtai-product-item .wtai-product-cb, .wtai-product-audiences-wrap .wtai-product-item .wtai-product-cb', function(){
        setDisallowedCombinations();
    });

    function setDisallowedCombinations(){
        //disable the tooltip first
        if($('.tooltip-generate-filter').hasClass('tooltipstered')) {
            $('.tooltip-generate-filter').each(function(){
                $(this).tooltipster('disable');
            });
        }

        var disallowedCombinations = WTAI_OBJ.disallowedCombinations;

        var checkedIds = [];

        //applies to tones and audiences
        if( $('.wtai-product-tonestyles-container input[type="checkbox"]').length ){
            $('.wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
                if( $(this).is(':checked') && 'wtaCustom' !== $(this).val() ){
                    checkedIds.push( $(this).val() );
                }
            });
        }

        //applies to style
        if( $('.wtai-product-tonestyles-container input[type="radio"]').length ){
            $('.wtai-product-tonestyles-container input[type="radio"]').each(function(){
                if( $(this).is(':checked') && 'wtaCustom' !== $(this).val() ){
                    checkedIds.push( $(this).val() );
                }
            });
        }

        $('.wtai-product-tonestyles-container input[type="checkbox"]').closest('label').removeClass('disabled-label');
        $('.wtai-product-tonestyles-container input[type="checkbox"]').prop('disabled', false);

        $('.wtai-product-tonestyles-container input[type="radio"]').closest('label').removeClass('disabled-label');
        $('.wtai-product-tonestyles-container input[type="radio"]').prop('disabled', false);

        $.each(disallowedCombinations, function( index, combinationData ){
            var selectedCombinations = [];
            var disabledCombinations = [];
            var combinations = combinationData.combination;
            $.each(combinations, function( index2, combination ){
                var combID = combination.id;

                if ( checkedIds.indexOf(combID) !== -1 ) {
                    selectedCombinations.push( combination );
                }
                else{
                    disabledCombinations.push( combination );
                }
            });

            var shouldDisableLength = combinations.length - 1;
            if( selectedCombinations.length === shouldDisableLength && disabledCombinations.length ){
                $.each(disabledCombinations, function( index3, combinationDisable ){
                    var combID = combinationDisable.id;
                    var combType = combinationDisable.type.toLowerCase();
    
                    if( 'tone' === combType ){
                        if( $('.wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').length ){
                            $('.wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    $(this).closest('label').tooltipster('enable');
                                }
                            });
                        }
                    }
                    else if( 'style' === combType ){
                        if( $('.wtai-product-tonestyles-container input[type="radio"]').length ){
                            $('.wtai-product-tonestyles-container input[type="radio"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    $(this).closest('label').tooltipster('enable');
                                }
                            });
                        }
                    }
                    else if( 'audience' === combType ){
                        if( $('.wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').length ){
                            $('.wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    $(this).closest('label').tooltipster('enable');
                                }
                            });
                        }
                    }
                });
            }
        });
    }
});