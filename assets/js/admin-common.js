/* jshint -W065 */
/* jshint -W117 */

console.log('WriteText.ai - Version: [1.40.4 - 2024-10-10 - 2');

jQuery( document ).ready( function( $ ){
  
    /*Prevent 0 and Negative values. Min should not equal or more thanks Max, Max should not less than or equal to min*/

    $( document ).on( 'keydown', '.wtai-specs-input.wtai-min-text, .wtai-specs-input.wtai-max-text', function( e ) {
        // Check if the pressed key is not a number (0-9)
        if (e.which === 190 || e.which === 110) {
            e.preventDefault(); // Prevent non-numeric characters from being entered
        }
    });

    $(document).on('blur','.wtai-specs-input', function() {
        var value = parseInt( $(this).val() );

        var minvalue = parseInt( $(this).closest('.wtai-input-group').find('input').attr('data-mintext') );
        var maxvalue = parseInt( $(this).closest('.wtai-input-group').find('input').attr('data-maxtext') );

        var maxInput = parseInt( $(this).closest('.wtai-button-text-length').find('.wtai-max-text').val() );
        var minInput = parseInt( $(this).closest('.wtai-button-text-length').find('.wtai-min-text').val() );
        
        if( isNaN( maxInput ) ){
            maxInput = 0;
        }

        if( isNaN( minInput ) ){
            minInput = 0;
        }

        if( isNaN( value ) ){
            value = 0;
        }

        var adjustedValue = 0;

        if ( $(this).val().indexOf('-') !== -1 ) {
            if ($(this).hasClass('wtai-min-text')) {
                if ( value >= minvalue ) {
                    if( value > maxInput || value === maxInput) {
                        adjustedValue = maxInput - 1;
                        $(this).val(adjustedValue);
                    }
                } else {
                    $(this).val(minvalue);
                }
            } else if ($(this).hasClass('wtai-max-text')) {
                if( value > maxvalue ) {
                    $(this).val(maxvalue);
                } else {
                    if( value <= minInput ){
                        adjustedValue = minInput + 1;
                        $(this).val(adjustedValue);
                    }
                }
            }
        }
       
        if ($(this).hasClass('wtai-min-text')) {
            if ( value >= minvalue ) {
                if( value > maxInput || value === maxInput) {
                    adjustedValue = maxInput - 1;
                    $(this).val(adjustedValue);
                }
            } else {
                $(this).val(minvalue);
            }
        } else if ($(this).hasClass('wtai-max-text')) {
            if( value > maxvalue ) {
                $(this).val(maxvalue);
            } else {
                if( value <= minInput ){
                    adjustedValue = minInput + 1;
                    $(this).val(adjustedValue);
                }
            }
        }

        //trgigger change event
        if( $('.wtai-single-product-max-length').length ){
            $('.wtai-single-product-max-length').trigger('change');
        }

        if( $('.wtai-bulk-product-max-length').length ){
            $('.wtai-bulk-product-max-length').trigger('change');
        }
    });
    
    $(document).on('click','.wtai-settings-setup #submit', function() {
        var hasMaxExcess = true;
        $('.wtai-button-text-length input').each(function() {
            var value = $(this).val();
            if ( value > 9999 ) {
                hasMaxExcess = false;
            }
        });

        return hasMaxExcess;

    });

    // Plus button click event
    $(document).on('click', '.wtai-txt-plus', function(){
        var maxvalue = $(this).closest('.wtai-input-group').find('input').attr('data-maxtext');
        var input = $(this).closest('.wtai-input-group').find('input');
        var value = parseInt(input.val());
        var maxInput = $(this).closest('.wtai-button-text-length').find('.wtai-max-text');

        if( $(this).hasClass('noactivity') ) {
            input.val(value + 1);
        } else {    
            // Increment the value
            if ( input.hasClass('wtai-min-text') && parseInt( maxInput.val() ) > value ) {
                if( ( value + 1 ) !== parseInt( maxInput.val() ) ){
                    input.val(value + 1);
                }
                
            } else if ( input.hasClass('wtai-max-text') &&  value < maxvalue ) {
                input.val(value + 1);
            }
        }

        setDataNewValues();

        input.trigger('change');
    });
  
    // Minus button click event
    $(document).on('click', '.wtai-txt-minus', function(){
        var minInput = $(this).closest('.wtai-button-text-length').find('.wtai-min-text');
        var minValue = $(this).closest('.wtai-input-group').find('input').attr('data-mintext');
        var input = $(this).closest('.wtai-input-group').find('input');
        var value = parseInt(input.val());
        if( $(this).hasClass('noactivity') ) {
            if ( value > 1 ) {
                input.val(value - 1);
            }
            
        } else {
            // Decrement the value, but ensure it stays non-negative
            if ( input.hasClass('wtai-max-text') && value > parseInt( minInput.val() ) ) {
                if( ( value - 1 ) !== parseInt( minInput.val() ) ){
                    input.val(value - 1);
                }
            } else if ( input.hasClass('wtai-min-text') && value > minValue) {
                input.val(value - 1);
            }
        }

        setDataNewValues();

        input.trigger('change');
    });

    function newSettingValues(){
        var typesProdItem = [];
        $('.wtai-card-container-wrapper').find('.wtai-product-item').each(function() {
            var cb = $(this).find('.wtai-product-cb');
            if ( true === cb.is(':checked') ) {
                var value = cb.val();
                typesProdItem.push(value);
            }
        });

        var typesProdAttrItem = [];
        $('.wtai-card-container-wrapper').find('.wtai-product-attr-item').each(function() {
            var cb = $(this).find('.wtai-product-attr-cb');
            if ( true === cb.is(':checked') ) {
                var value = cb.val();
                typesProdAttrItem.push(value);
            }
        });

        var prodDescMin = $('#wtai-installation-product-description-min').val();
        var prodDescMax = $('#wtai-installation-product-description-max').val();
        
        var prodExcerptMin = $('#wtai-installation-product-excerpt-min').val();
        var prodExcerptMax = $('#wtai-installation-product-excerpt-max').val();

        var prodItem = '';
        var prodAttrItem = '';
        if( typesProdItem.length > 0 ) {
            prodItem = typesProdItem.join(',') + ',' ;
        } 

        if ( typesProdAttrItem.length > 0 ) {
            prodAttrItem = typesProdAttrItem.join(',') + ',';
        }

        var selectedOptions = prodItem + prodAttrItem + prodDescMin + ',' + prodDescMax + ',' + prodExcerptMin + ',' + prodExcerptMax;

        var genPopup = '';
        var typesNewValues = '';
        if( $('#dont_show_bulk_generate_popup').is(':checked' ) ) {
            genPopup = 1;
            typesNewValues = selectedOptions + ',' + genPopup;
        } else {
            typesNewValues = selectedOptions;
        }
        
        return typesNewValues;
    }
    
    function setDataNewValues(){
        var typesNewValues = newSettingValues();
        $('#wtai-types-orig-values').attr('data-newvalues', typesNewValues);
    }

    $(document).on('click', '#wtai-form-settings input:not([type="submit"])', function() {
        setDataNewValues();
    });

    $(document).on('click', function(event) {
        var form = $('#wtai-form-settings'); // Replace 'yourFormId' with the actual ID of your form
        var clickedElement = event.target;

        // Check if the clicked element is not within the form
        if (!form.is(clickedElement) && !form.has(clickedElement).length) {
            var typesOrigValues = $('#wtai-types-orig-values').val();
            var typesNewValues = $('#wtai-types-orig-values').attr('data-newvalues');
            if( 'undefined' !== typeof typesNewValues  && typesOrigValues !== typesNewValues ) {
                var link = $(clickedElement).closest('li').find('a').attr('href');

                $('.wtai-exit-edit-leave').attr('data-href', link);
                $('#wpbody-content').addClass('overlayDiv');
                $('#wtai-form-settings .wtai-loader-generate#wtai-product-edit-cancel').show();
                return false;
            } 
        } 
    });

    var form = $('#wtai-form-settings'); 
    var typesOrigValues = $('#wtai-types-orig-values').val();
    localStorage.setItem('typesOrigValues', typesOrigValues);

    // Variable to track if the submit button was clicked
    var submitButtonClicked = false;

    form.on('submit', function() {
        submitButtonClicked = true;
    });

    $(window).on('beforeunload', function(event) {
        var storedOrigValues = localStorage.getItem('typesOrigValues');
        var typesNewValues = $('#wtai-types-orig-values').attr('data-newvalues');

        // Check if the condition is met or if the back or refresh button is clicked
        if ((typeof typesNewValues !== 'undefined' && storedOrigValues !== typesNewValues) || event.originalEvent === undefined) {
            if (submitButtonClicked) {
                // Submit button clicked, allow default behavior
                return;
            } else {
                
                // Back or refresh button clicked, prevent default behavior
                event.preventDefault();
                event.stopImmediatePropagation();
                return false;
            }
        }

        // Clear the stored form values from local storage
        localStorage.removeItem('typesOrigValues');
    });

    $(document).on('click', '#wtai-form-settings #wtai-product-edit-cancel .exit-edit-cancel', function() {
        $('#wpbody-content').removeClass('overlayDiv');
        $('#wtai-form-settings .wtai-loader-generate').hide();
    });
    
    $(document).on('click', '#wtai-form-settings #wtai-product-edit-cancel .wtai-exit-edit-leave', function() {
        $('#wpbody-content').removeClass('overlayDiv');
        $(window).off('beforeunload');
        $(this).addClass('disabled');
        var link = $(this).attr('data-href');

        if( link === 'undefined' || undefined === link ){
            location.reload();
            return;
        }
        
        if( link ) {
            $('#wtai-form-settings .wtai-loader-generate').hide(); 
            window.location.href = link;
        } else {
            $('#wtai-form-settings .wtai-loader-generate').hide(); 
        }
    });

    $(document).on('click', '.wtai-product-cb-all', function() {
        var cbId = $(this).attr('id');
        var cb = $(this);
        var checked = cb.is(':checked');
        
        if( cb.hasClass('disabled') ){
            return false;
        } 

        $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').removeClass('warning');

        if( checked ){
            $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').each(function(){
                if( $(this).is(':disabled') ){
                    
                }
                else{
                    $(this).prop('checked', true);
                }
            });

            $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').removeAttr('style');
            $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').removeClass('warning');
        } 
        else {
            if( 'wtai-select-all-audiences' !== cbId && 'wtai-select-all-attr' !== cbId ){
                $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').addClass('warning');
            }
            
            $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').prop('checked', false);
        }

        var parentWrapper = '';
        if( $('.wtai-table-list-wrapper').length ){
            parentWrapper = '#TB_window.wtai-tb-window-modal-generate';
        }
        else if( $('.wrap.wtai-cart-install-wrapper').length ){
            parentWrapper = '.wrap.wtai-cart-install-wrapper';
        }

        if( '' !== parentWrapper ){
            setDisallowedCombinations( parentWrapper );
        }       

        if ( !$('#TB_ajaxContent').length ) {
            setDataNewValues();
        }
    });

    //check if all siblings are checked
    $(document).on('click', '.wtai-product-all-trigger input:not(.wtai-product-cb-all)', function(){
        var cb = $(this);
        var checked = cb.is(':checked');
        var count = cb.closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all):checked').length;
        
        if( cb.hasClass('disabled') ){
            return false;
        } 
       
        if( $('.thickbox-loading').length > 0 && count > 0 ) {
            $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').removeClass('warning');
        } else {
            if( $(this).hasClass('wtai-product-audiences-cb') || $(this).hasClass('wtai-product-attr-cb') ){
                //skip audience and attribute
            }
            else{
                if( count <= 0 ){
                    $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').addClass('warning');
                }
                else{
                    $(this).closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').removeClass('warning');
                }
            }
        }

        if( checked ) {
            var allChecked = true;
            cb.closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').each(function() {
                if( false === $(this).is(':checked') ) {
                    allChecked = false;
                }
            });
            if( allChecked ) {
                cb.closest('.wtai-product-all-trigger').find('.wtai-product-cb-all').prop('checked', true);
            } 
        } else {
            cb.closest('.wtai-product-all-trigger').find('.wtai-product-cb-all').prop('checked', false);
        }   

        if ( !$('#TB_ajaxContent').length ) {
            setDataNewValues();
        }
    });
    
    //on load check if all siblings are checked
    $('.wtai-product-all-trigger input:not(.wtai-product-cb-all)').each(function() {
        var cb = $(this);
        var checked = cb.is(':checked');
        if( checked ) {
            var allChecked = true;
            cb.closest('.wtai-product-all-trigger').find('input:not(.wtai-product-cb-all)').each(function() {
                if( false === $(this).is(':checked') ) {
                    allChecked = false;
                }
            });
            if( allChecked ) {
                cb.closest('.wtai-product-all-trigger').find('.wtai-product-cb-all').prop('checked', true);
            }   
        } else {
            cb.closest('.wtai-product-all-trigger').find('.wtai-product-cb-all').prop('checked', false);
        }   
    });    

    var userAgent = navigator.userAgent.toLowerCase();

    // Check for Windows
    if (userAgent.indexOf('win') > -1) {
        $('html').addClass('windows');
    }

    // Check for wtai-macos
    if (userAgent.indexOf('mac') > -1) {
        $('html').addClass('wtai-macos');
    }

    // Check for specific browsers
    if (userAgent.indexOf('chrome') > -1) {
        $('html').addClass('chrome');
    } else if (userAgent.indexOf('firefox') > -1) {
        $('html').addClass('firefox');
    } else if (userAgent.indexOf('safari') > -1) {
        $('html').addClass('safari');
    } else if (userAgent.indexOf('edge') > -1) {
        $('html').addClass('edge');
    } else if (userAgent.indexOf('msie') > -1 || userAgent.indexOf('trident') > -1) {
        $('html').addClass('ie');
    } else {
        $('html').addClass('unknown-browser');
    }

    function setDisallowedCombinations( parentElement ){
        var parent = $( parentElement );

        if( ! parent.length ){
            return;
        }

        var disallowedCombinations = WTAI_COMMON_OBJ.disallowedCombinations;

        var checkedIds = [];

        //applies to tones and audiences
        if( parent.find('.wtai-product-tonestyles-container input[type="checkbox"]').length ){
            parent.find('.wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
                if( 'wtaCustom' != $(this).val() && $(this).is(':checked') ){
                    checkedIds.push( $(this).val() );
                }
            });
        }

        //applies to style
        if( parent.find('.wtai-product-tonestyles-container input[type="radio"]').length ){
            parent.find('.wtai-product-tonestyles-container input[type="radio"]').each(function(){
                if( 'wtaCustom' != $(this).val() && $(this).is(':checked') ){
                    checkedIds.push( $(this).val() );
                }
            });
        }

        parent.find('.wtai-product-tonestyles-container input[type="checkbox"]').closest('label').removeClass('disabled-label');
        parent.find('.wtai-product-tonestyles-container input[type="checkbox"]').prop('disabled', false);

        parent.find('.wtai-product-tonestyles-container input[type="radio"]').closest('label').removeClass('disabled-label');
        parent.find('.wtai-product-tonestyles-container input[type="radio"]').prop('disabled', false);

        //disable the tooltip first
        if( parent.find('.tooltip-generate-filter').hasClass('tooltipstered') ) {
            parent.find('.tooltip-generate-filter').each(function(){
                $(this).tooltipster('disable');
            });
        }

        $.each(disallowedCombinations, function( index, combinationData ){
            var selectedCombinations = [];
            var disabledCombinations = [];
            var combinations = combinationData.combination;
            $.each(combinations, function( index2, combination ){
                var combID = combination.id;

                if (checkedIds.indexOf(combID) !== -1) {
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
                        if( parent.find('.wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').length ){
                            parent.find('.wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');

                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 100 );
                                }
                            });
                        }
                    }
                    else if( 'style' === combType ){
                        if( parent.find('.wtai-product-tonestyles-container input[type="radio"]').length ){
                            parent.find('.wtai-product-tonestyles-container input[type="radio"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');

                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 100 );
                                }
                            });
                        }
                    }
                    else if( 'audience' === combType ){
                        if( parent.find('.wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').length ){
                            parent.find('.wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').each(function(){
                                if( combID === $(this).val() ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');

                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 100 );
                                }
                            });
                        }
                    }
                });
            }
        });
    }

    function getCountries(){
        var countries = window.WTAI_COUNTRY_OPTIONS;
        var countriesFiltered = countries;

        $('.wtai-selected-items .item').each(function(){
            var dataValue = $(this).attr('data-value');

            countriesFiltered = countriesFiltered.filter(function(item) {
                return item.value !== dataValue;
            });
        });

        return countriesFiltered;
    }

    // Function to move selected items to the target div
    function moveSelectedItems() {
        var items = selectize.$control.find('.item');
        var targetDiv = $('.wtai-country-selection-dropdown-inner-wrap-selected .wtai-selected-items');

        items.each(function(index, item) {
            targetDiv.append(item);
        });
    }

    var countrySelect = null;
    if( $('.wtai-localized-country-dropdown').length ){
        // Initialize Selectize
        var selectElem = $('.wtai-localized-country-dropdown').selectize({
            onChange: function(value) {
                // Move selected items to the target div
                var targetDiv = $('.wtai-country-selection-dropdown-inner-wrap-selected .wtai-selected-items');
                var selectize = this;
                value.forEach(function(itemValue) {
                    var item = selectize.getItem(itemValue);
                    targetDiv.append(item);
                });
                
                var newSelect = '';
                targetDiv.find('div').each(function() {
                    var dataValue = $(this).data('value');
                    var dataName = $(this).text();
                    newSelect += '<option value="' + dataValue + '" selected="selected">' + dataName + '</option>';
                });
                $('#wtai-localized-country-dropdown').html(newSelect);
                if ($('.wtai-selected-items').find('div').length ) {
                    $('.wtai-selected-items').parent().removeClass('wtai-empty');
                }   
            },
            onItemAdd : function() {
                $('.wtai-country-selection-dropdown-wrap .selectize-input').removeClass('error-required');
                
                var selectize = this;
                selectize.close();
            }
        });

        var selectize = selectElem[0].selectize;

        // Call the function to move selected items after the page has loaded
        moveSelectedItems();        
        
        // Assuming a click event on the wtai-selected-items container
        $('.wtai-selected-items').on('click', 'div', function() {
            // Remove the clicked div.
            $(this).remove();
           
            var selectElem = $('#wtai-localized-country-dropdown');
            var selectize = selectElem[0].selectize;
            selectize.clear();
            selectize.clearOptions();
            selectize.refreshOptions(); // Refresh the dropdown.
            selectize.refreshItems(); // Refresh the selected items.
            
            /* Add options and item and then refresh state*/                    
            selectize.addOption(getCountries());
           
            selectize.refreshState();      

            // Check if .wtai-selected-items is empty and add a class if it is.
            if ($('.wtai-selected-items').find('div').length === 0) {
                $('.wtai-selected-items').parent().addClass('wtai-empty');
            } else {
                $('.wtai-selected-items').parent().removeClass('wtai-empty');
            }
        });

        var inputField = selectElem[0].selectize.$control_input;

        inputField.on('input', function() {
            // Check if the input is not empty.
            if (inputField.val().trim() !== '') {
                // Hide the placeholder
                $('.wtai-select-placeholder').hide();
            } else {
                // Show the placeholder.
                $('.wtai-select-placeholder').show();
            }
        });

        inputField.on('blur', function() {
            // Check if the input is empty on blur.
            if (inputField.val().trim() === '') {
              // Show the placeholder.
              $('.wtai-select-placeholder').show();
            }
        });

        $('.wtai-select-placeholder').on('click', function() { 
            // Focus the input field.
            inputField.focus();
        });
    }

    $('.wtai-country-selection-cta').on('click', function(){
        var country = $('#wtai-country-single-dropdown').val();

        var wtaiNonce = $('#wtai-country-nonce').val();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_COMMON_OBJ.ajaxUrl,
            data: {
                action: 'wtai_save_localized_countries',
                country: country,
                wtai_nonce: wtaiNonce,
            },
            success: function() {
                showHideCountryLocalizedPopup( 'hide' );

                location.reload();
            }
        });
    });

    function showHideCountryLocalizedPopup( status_display ){
        if( 'hide' === status_display ){
            $('.wtai-country-selection-popup-overlay').removeClass('wtai-shown');
            $('.wtai-country-selection-popup-wrap').removeClass('wtai-shown');

            if( $('.wtai-selected-items').length ){            
                // Reload list.
                $('.wtai-selected-items').html('');
                var countries = window.WTAI_COUNTRY_SELECTED;
                $.each(countries, function( index, item ){
                    $('.wtai-selected-items').append( '<div class="wtai-item" data-value="' + item.value + '">' + item.text + '</div>' );
                });

                var selectElem = $('#wtai-localized-country-dropdown');
                var selectize = selectElem[0].selectize;
                selectize.clear();
                selectize.clearOptions();
                selectize.refreshOptions(); // Refresh the dropdown.
                selectize.refreshItems(); // Refresh the selected items.
                
                /* Add options and item and then refresh state*/                    
                selectize.addOption(getCountries());
            
                selectize.refreshState();
            }
        }
        else{
            $('.wtai-country-selection-popup-overlay').addClass('wtai-shown');
            $('.wtai-country-selection-popup-wrap').addClass('wtai-shown');
        }
    } 


    $('.wtai-country-global').on('click', function(){
        showHideCountryLocalizedPopup( 'show' );
    });

    $('.wtai-country-selection-popup-overlay.wtai-close-on-click, .wtai-country-selection-close').on('click', function(){
        showHideCountryLocalizedPopup( 'hide' );
    });

    $('.wtai-country-count-placeholder').on('click', function(){
        if( countrySelect != null ){
            countrySelect[0].selectize.$control.show();
        }
    });

    $(document).on('click', 'div.selectize-input div.item', function() {
        // 1. Get the value.
        var selectedValue = $(this).attr('data-value');
        // 2. Remove the option.
        countrySelect[0].selectize.removeItem(selectedValue);
        countrySelect[0].selectize.refreshItems();
        countrySelect[0].selectize.refreshOptions();
    });

    $(document).on('click', '.wtai-premium-wrap, .wtai-ad-cta', function( e ){
        e.preventDefault(); 

        $('.wtai-premium-modal-overlay-wrap').removeClass('wtai-shown');
        $('.wtai-premium-modal-wrap').removeClass('wtai-shown');

        $('.wtai-premium-modal-overlay-wrap').addClass('wtai-shown');
        $('.wtai-premium-modal-wrap').addClass('wtai-shown');
    });

    $(document).on('click', '.wtai-premium-modal-overlay-wrap, .wtai-pm-close-ico', function( e ){
        e.preventDefault(); 

        $('.wtai-premium-modal-overlay-wrap').removeClass('wtai-shown');
        $('.wtai-premium-modal-wrap').removeClass('wtai-shown');
    });

    //initial load of premium blocks
    handleBodyPremiumClass( WTAI_COMMON_OBJ.isPremium );

    function handleBodyPremiumClass( isPremium ){
        if( isPremium == '1' ){
            //body global flex class
            $('body').removeClass('wtai-premium-badge-displayed');
        }
        else{
            //body global flex class
            $('body').addClass('wtai-premium-badge-displayed');
        }
    }

    if( WTAI_COMMON_OBJ.freePremiumPopupHtml != '' ){
        var showPremium = true;
        if( $('.wtai-installation-main-wrap').length > 0 ){
            showPremium = false;
        }

        if( showPremium ){
            $('body').append( WTAI_COMMON_OBJ.freePremiumPopupHtml );

            showFreemiumPopup();
        }
    }
   
    function showFreemiumPopup(){
        $('.wtai-freemium-popup-wrap').fadeIn();
    }

    // Event to render freemium popup
    $(document).on('wtai_show_freemium_popup', function(e, args){
        e.stopImmediatePropagation();

        var freePremiumPopupHtml = args.freePremiumPopupHtml;

        if( $('.wtai-freemium-popup-wrap').length ){
            $('.wtai-freemium-popup-wrap').remove();
        }
        
        $('body').append( freePremiumPopupHtml );

        showFreemiumPopup();
    });

    $(document).on('click', '.wtai-freemium-popup-close', function(){
        var wtaiNonce = $('.wtai-freemium-popup-wrap').attr('data-nonce');

        $('.wtai-freemium-popup-wrap').fadeOut();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_COMMON_OBJ.ajaxUrl,
            data: {
                action: 'wtai_freemium_popup_closed',
                wtai_nonce: wtaiNonce,
            },
            success: function() {
                
            }
        });        
    });

    $(document).on('click', '.wtai-popup-blocker-notice .notice-dismiss', function( e ){
        e.preventDefault();

        $('.wtai-popup-blocker-notice').fadeOut();
        var wtaiNonce = $('#wtai-popupblocker-nonce').val();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_COMMON_OBJ.ajaxUrl,
            data: {
                action: 'wtai_dismiss_popup_blocker_notice',
                wtai_nonce: wtaiNonce,
            },
            success: function() {
                
            }
        });
    });
});
