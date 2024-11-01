/* jshint -W065 */
/* jshint -W117 */

jQuery(document).ready(function($){   
    $(document).on('click', '.wtai-settings-btn-save', function( e ){
        var hasError = false;

        if( $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb:checked').length <= 0 ){
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').addClass('warning');

            hasError = true;
        }
        else{
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').removeClass('warning');
        }

        if( $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb:checked').length <= 0 ){
            $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb').addClass('warning');

            hasError = true;
        }
        else{
            $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb').removeClass('warning');
        }

        if( hasError ){
            $('html, body').animate({ scrollTop: 0 }, 'fast');

            e.preventDefault();
        }
    });

    $(document).on('click', '.wtai-product-tones-wrap .wtai-product-item', function(){
        var checkedCount = $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb:checked').length;
        if( checkedCount <= 0 ){
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').addClass('warning');
        }
        else{
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').removeClass('warning');
        }
    });


    $(document).on('click', '.wtai-product-styles-wrap .wtai-product-item', function(){
        var checkedCount = $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb:checked').length;
        if( checkedCount <= 0 ){
            $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb').addClass('warning');
        }
        else{
            $('.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb').removeClass('warning');
        }
    });

    $(document).on('click', '.wtai-product-tones-wrap .wtai-label-select-all-wrap', function(){
        var $this = $(this);

        if( $this.find('.wtai-product-cb-all').is(':checked') ){
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').removeClass('warning');
        }
        else{
            $('.wtai-product-tones-wrap .wtai-product-item .wtai-product-cb').addClass('warning');
        }
    });

    load_generate_filter_tooltip();

    function load_generate_filter_tooltip(){
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

    set_disallowed_combinations();
    $(document).on('click', '.wtai-product-styles-wrap .wtai-product-item .wtai-product-cb, .wtai-product-tones-wrap .wtai-product-item .wtai-product-cb, .wtai-product-audiences-wrap .wtai-product-item .wtai-product-cb', function(){
        set_disallowed_combinations();
    });

    function set_disallowed_combinations(){
        //disable the tooltip first
        if($('.tooltip-generate-filter').hasClass('tooltipstered')) {
            $('.tooltip-generate-filter').each(function(){
                $(this).tooltipster('disable');
            });
        }
        var disallowed_combinations = WTAI_OBJ.disallowedCombinations;

        var checked_ids = [];

        //applies to tones and audiences
        if( $('.wtai-product-tonestyles-container input[type="checkbox"]').length ){
            $('.wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
                if( $(this).is(':checked') && 'wtaCustom' !== $(this).val() ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        //applies to style
        if( $('.wtai-product-tonestyles-container input[type="radio"]').length ){
            $('.wtai-product-tonestyles-container input[type="radio"]').each(function(){
                if( $(this).is(':checked') && 'wtaCustom' !== $(this).val() ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        $('.wtai-product-tonestyles-container input[type="checkbox"]').closest('label').removeClass('disabled-label');
        $('.wtai-product-tonestyles-container input[type="checkbox"]').prop('disabled', false);

        $('.wtai-product-tonestyles-container input[type="radio"]').closest('label').removeClass('disabled-label');
        $('.wtai-product-tonestyles-container input[type="radio"]').prop('disabled', false);

        $.each(disallowed_combinations, function( index, combinationData ){
            var selectedCombinations = [];
            var disabledCombinations = [];
            var combinations = combinationData.combination;
            $.each(combinations, function( index2, combination ){
                var combID = combination.id;

                if ( -1 !== checked_ids.indexOf(combID) ) {
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

    /*Tooltiptext*/
    $(document).on('click', '.wtai-tooltip', function(){
        $('.wtai-tooltip').not(this).removeClass('hover');
        $(this).toggleClass('hover');
    });

    $(document).on('mouseup', function(e){     
        var tooltip = $('.wtai-tooltip');
        if ( !tooltip.is(e.target) && tooltip.has(e.target).length === 0 ) {
            $('.wtai-tooltip').removeClass('hover');
        }
    });
});
