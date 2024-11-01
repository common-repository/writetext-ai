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
    var keywordIdeasAJAX = null;
    var addKeyWordAJAX = null;
    var lastGenerationTypeSelected = null;
    var queueGenerateTimer = null;
    var representativeProductAJAX = null;
    var productEditFormHtml = '';
    var singleGenerationErrorFields = [];
    var completeWriting = null;

    reset_image_alt_local_data();

    //display outdated message
    display_outdated_message();
    display_language_translation_ongoing_message();
    display_disable_popup_message();

    function display_outdated_message(){
        var versionOutdated = WTAI_OBJ.versionOutdated;
        var versionOutdatedMessage = WTAI_OBJ.versionOutdatedMessage;

        if( versionOutdated == '1' ){
            setTimeout(() => {
                if ( $('.wtai-table-list-wrapper' ).find('#outdated-message').length > 0  ){
                    $('.wtai-table-list-wrapper' ).find('#outdated-message').remove();
                }
                $('<div id="outdated-message" class="wtai-update-notice error notice is-dismissible"><p>'+versionOutdatedMessage+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );

                $(window).trigger('resize');
            }, 1000);
        }
    }

    function display_language_translation_ongoing_message(){
        if( WTAI_OBJ.translation_ongoing == '1' && WTAI_OBJ.isCurrentLocaleEN != '1' ){        
            setTimeout(() => {
                if ( $('.wtai-table-list-wrapper' ).find('#wtai-language-translation-ongoing-notice').length > 0  ){
                    $('.wtai-table-list-wrapper' ).find('#wtai-language-translation-ongoing-notice').remove();
                }
                $('<div id="wtai-language-translation-ongoing-notice" class="wtai-language-translation-ongoing-notice error notice is-dismissible"><p>'+WTAI_OBJ.translationOngoingMessage+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );

                $(window).trigger('resize');
            }, 1000);
        }
    }

    function display_disable_popup_message(){
        if( WTAI_OBJ.disablePopupBlockerStatus != '1' ){        
            setTimeout(() => {
                if ( $('.wtai-table-list-wrapper' ).find('.wtai-popup-blocker-notice').length > 0  ){
                    $('.wtai-table-list-wrapper' ).find('.wtai-popup-blocker-notice').remove();
                }
                $('<input type="hidden" id="wtai-popupblocker-nonce" value="' + WTAI_OBJ.popupblocker_nonce + '" /><div id="wtai-popup-blocker-notice" class="updated error notice wtai-popup-blocker-notice is-dismissible"><p>'+WTAI_OBJ.disablePopupBlockerMessage+' </p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );

                $(window).trigger('resize');
            }, 1000);
        }
    }

    if( $('.wtai-slide-right-text-wrapper').length ){
        productEditFormHtml = '<div class="wtai-slide-right-text-wrapper wtai-main-wrapper">' + $('.wtai-slide-right-text-wrapper').html() + '</div>';
    }

    /* Handling for category table scrollable content */
    if( $('.wtai-table-list-wrapper').length > 0 || $('.wtai-table-list-wrapper').is(':visible')){
        $('body').addClass('wtai-page-list');
    } 

    var winH = $(window).height();
    var winW= $(window).width();
    var targetElement = $('#wtai-start-sticky').position().top + 32;
    if( winW >= 1200){
        var stickyHeight = winH - targetElement;
        if( $('.wtai-footer-wrap').length ){
            var stickyFooterHeight = $('.wtai-footer-wrap').height() + 20;
            
            stickyHeight = winH - targetElement - stickyFooterHeight;
        }

        $('#wtai-start-sticky').css('max-height',stickyHeight + 'px');
    }
    else{
        $('#wtai-start-sticky').css('max-height','100%');
    }
    
    $(window).resize(function() {
        var targetElement = $('#wtai-start-sticky').position().top + 32;
        var winH = $(window).height();
        var winW= $(window).width();
        
        if( winW >= 1200){
            var stickyHeight = winH - targetElement;
            if( $('.wtai-footer-wrap').length ){
                var stickyFooterHeight = $('.wtai-footer-wrap').height() + 20;
                
                stickyHeight = winH - targetElement - stickyFooterHeight;
            }
            
            $('#wtai-start-sticky').css('max-height',stickyHeight + 'px');
        }
        else{
            $('#wtai-start-sticky').css('max-height','100%');
        }
    });

    /*Writetext Status*/
    function getURLParameterValue(parameterName) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(parameterName);
    }

    /* WTA Filter */
    $(document).on('click', function(e){
        if( ( $(e.target).closest('#wtai-sel-writetext-status').length <= 0 ) && $(e.target).closest('#wpwrap').length && $('.wtai-status-checkbox-options').hasClass('wtai-open') ) {
            $('#wtai-sel-writetext-status > div.wtai-filter-select').trigger('click');
        }
    });

    $('#wtai-sel-writetext-status > div.wtai-filter-select').on('click',function(){
        $(this).siblings('.wtai-status-checkbox-options').toggleClass('wtai-open');
    });

    var paramValue = getURLParameterValue('wtai_writetext_status');
    var status = $('#wtai-sel-writetext-status input[name="wtai_writetext_status"]').val();
    if (paramValue ) {
        status = paramValue;
    }
    write_filter_status(status);

    load_wtai_max_day_tooltip();

    function load_wtai_max_day_tooltip(){
        $('.wtai-no-activity-days').each(function(){
            $(this).tooltipster({
                'theme': 'tooltipform-default',
                'position': 'bottom',
                'arrow': true,
                'offsetX': 200,
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
            //$(this).tooltipster('disable');
        });
    }

    $('#wtai-sel-writetext-status input[name="wtai_writetext_status"]').on('click',function(){
        var status = $(this).val();
        write_filter_status(status);
    });

    function write_filter_status(status){
        if( status == 'all' || status == 'reviewed' ){
            $('#wtai-sel-writetext-status .wtai-filter-date input.filter-date').attr('disabled','disabled');
        } else {
            $('#wtai-sel-writetext-status .wtai-filter-date input.filter-date').removeAttr('disabled');
        }
    }

    $('.wtai-no-activity-days').on('click', function(){
        if( $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').is(':checked') == false ){
            $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').prop('checked', true);
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }

        $('.wtai-custom-status-cb').prop('checked', false);
    });

    $('.wtai-no-activity-days').on('keyup', function(){
        if( $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').is(':checked') == false ){
            $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').prop('checked', true);
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }

        $('.wtai-custom-status-cb').prop('checked', false);

        validate_activity_max_days();
    });

    $('.wtai-no-activity-days').on('blur change', function(){
        validate_activity_max_days();
    });

    function validate_activity_max_days(){
        var input = $('.wtai-no-activity-days');

        var current_days = input.val();
        if( isNaN( current_days ) == true || current_days == '' || current_days == 0 ){
            current_days = 1;
            input.val(1);
        }
        else{
            current_days = parseInt( current_days );
        }

        var max_days = parseInt( input.attr('data-maxtext') );

        if( current_days > max_days ){
            input.val(max_days);

            input.tooltipster('show');
        }
    }

    $('.noactivity-btn').on('click', function(){
        if( $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').is(':checked') == false ){
            $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').prop('checked', true);
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }

        $('.wtai-custom-status-cb').prop('checked', false);

        setTimeout(() => {
            validate_activity_max_days();
        }, 200);
    });

    if( $('.wtai-status-checkbox-options').length > 0 ){
        var numfields = $('.wtai-status-checkbox-options .wtai-col-1 input:checked').length;
        var activeType = $('.wtai-status-checkbox-options .wtai-col-2 input:checked').val();
        if ( numfields == 4 && activeType == 'all') {
            $('#wtai-sel-writetext-status .wtai-filter-option-label').removeClass('wtai-notdefault');
        } else {
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }
        $('.wtai-status-checkbox-options input').on('change', function() {
            
            var numfields = $('.wtai-status-checkbox-options .wtai-col-1 input:checked').length;
            var activeType = $('.wtai-status-checkbox-options .wtai-col-2 input:checked').val();
            if ( numfields == 4 && activeType == 'all') {
                $('#wtai-sel-writetext-status .wtai-filter-option-label').removeClass('wtai-notdefault');
            } else {
                $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
            }
        });
    }

    $('.wtai-custom-status-cb').on('click', function(){
        var checkedCount = $('.wtai-custom-status-cb:checked').length;
        if( checkedCount <= 0 ){
            $('.wtai-custom-grid-status-wrap .wtai-status-rd').prop('checked', false);
            $('.wtai-all-status-rd').prop('checked', true);
        } else {
            $('.wtai-custom-grid-status-wrap .wtai-status-rd').prop('checked', false);
            $('.wtai-custom-status-rd').prop('checked', true);
        }
        $('.wtai-custom-grid-review-status-wrap').removeClass('show');
        $('.wtai-custom-reviewer-status-cb').prop('checked', false);
    });

    $('.wtai-status-rd').on('click', function(){
        var xval = $(this).val();
        if( xval != 'wtai_custom_status' ){
            $('.wtai-custom-grid-status-wrap .wtai-custom-status-cb').prop('checked', false);
        } else {
            $('.wtai-custom-grid-status-wrap .wtai-custom-status-cb').prop('checked', true);
        }
        if( xval == 'wtai_review_status' ){
            $('.wtai-custom-grid-review-status-wrap').addClass('show');
            $('.wtai-custom-grid-review-status-wrap .wtai-custom-reviewer-status-cb').prop('checked', true);
        } else {
            $('.wtai-custom-grid-review-status-wrap').removeClass('show');
            $('.wtai-custom-grid-review-status-wrap .wtai-custom-reviewer-status-cb').prop('checked', false);
        }
    });

    $('.wtai-custom-reviewer-status-cb').on('click', function(){
        var checkedCount = $('.wtai-custom-reviewer-status-cb:checked').length;
        if( checkedCount <= 0 ){
            $('.wtai-custom-grid-status-wrap .wtai-status-rd').prop('checked', false);
            $('.wtai-all-status-rd').prop('checked', true);
        }
        else{
            $('.wtai-custom-status-rv').prop('checked', true);
            $('.wtai-all-status-rd').prop('checked', false);
        }
    });

    /*Tooltiptext*/
    $(document).on('click', '.wtai-tooltip', function(){
        if( $(this).hasClass('wtai-column-keyword-name-tooltip') ){
            $('.wtai-keyword-serp-wrap.wtai-tooltiptext .wtai-keyword-serp-content-wrap').animate({ scrollTop: 0 }, '0');
        }

        $('.wtai-tooltip').not(this).removeClass('hover');
        $(this).toggleClass('hover');

        if( $(this).hasClass('wtai-column-keyword-name-tooltip') && $(this).hasClass('hover') ){
            $('.wtai-keyword table.wtai-keyword-table').addClass('force-not-sticky');
        }
        else{
            $('.wtai-keyword table.wtai-keyword-table').removeClass('force-not-sticky');
        }
    });

    var wtaHistoryGlobalScrollTimer = null;
    var wtaDoingHistoryGlobalScroll = false;
    $('.wtai-table-list-wrapper .wtai-history').scroll( function(){     
        wtaDoingHistoryGlobalScroll = true;

        if(wtaHistoryGlobalScrollTimer !== null) {
            clearTimeout(wtaHistoryGlobalScrollTimer);        
        }

        wtaHistoryGlobalScrollTimer = setTimeout(function() {
            wtaDoingHistoryGlobalScroll = false;
        }, 500);
    });

    var wtaHistoryScrollTimer = null;
    var wtaDoingHistoryScroll = false;
    $('.wtai-slide-right-text-wrapper .wtai-history').scroll( function(){     
        wtaDoingHistoryScroll = true;

        if(wtaHistoryScrollTimer !== null) {
            clearTimeout(wtaHistoryScrollTimer);        
        }

        wtaHistoryScrollTimer = setTimeout(function() {
            wtaDoingHistoryScroll = false;
        }, 500);
    });

    $(document).on('mouseup', function(e){     
        var tooltip = $('.wtai-tooltip');
        if ( !tooltip.is(e.target) && tooltip.has(e.target).length === 0 ) {
            $('.wtai-tooltip').removeClass('hover');
        }

        var con_hist = $('.wtai-history'); //popup div
        var con_btn_lp = $('.wtai-link-preview'); //button
        var con_hist_date = $('.ui-datepicker'); //date picker element
        
        if ( !con_hist_date.is(e.target) && con_hist.has(e.target).length === 0 && $('body').hasClass('wtai-history-open') && 
            !con_btn_lp.is(e.target) && con_btn_lp.has(e.target).length === 0 && $('body').hasClass('wtai-history-open') && 
            !con_hist_date.is(e.target) && con_hist_date.has(e.target).length === 0 && wtaDoingHistoryScroll == false ){
            $('.wtai-slide-right-text-wrapper .wtai-history-single-btn').trigger('click'); // closing popup
        }

        if ( con_hist.has(e.target).length === 0  && $('body').hasClass('wtai-history-global-open') && !con_hist_date.is(e.target) && con_hist_date.has(e.target).length === 0 && wtaDoingHistoryGlobalScroll == false ){
            $('.wtai-table-list-wrapper .wtai-history-global').trigger('click');
        }
    });

    /* Search trigger */
    $('.wtai-frm-search-products #wtai-search-product-submit').on('click', function(){
        trigger_search_filter_submit();
    });

    $('.wtai-frm-search-products #wtai-post-search-input').on('keypress', function (e) {
        if ( e.keyCode === 44 || e.keyCode === 13 ) {
            trigger_search_filter_submit();
        }
    });   

    function trigger_search_filter_submit(){
        var search_text = $('.wtai-frm-search-products #wtai-post-search-input').val();

        $('.wtai-wp-table-list-filter #wtai-filter-search').val( search_text );
        $('.wtai-wp-table-list-filter').submit();
    }

    /* Tooltip for grid */
    var tooltipster_var = {
        content: WTAI_OBJ.loading+'...',
        position: 'top',
        interactive: true,
        //offsetX: '-200px',
        debug: false,
        autoClose: false,
        trigger: 'custom',
        triggerOpen: {
            mouseenter: true,
            click: false,
            touchstart: false,
            tap: false
        },
        triggerClose: {
            mouseleave: true,
            tap: true,
            touchleave: true,
            scroll: true
        },
        functionAfter: function() {
            $('.tooltipster-base').removeClass('wtai-list-hover');
        },
        functionBefore: function(origin) {
            var rowevent = origin._$origin.closest('tr');
            var colgrp = origin._$origin.attr('data-colgrp');
            var wtai_value = rowevent.find('.wtai_'+colgrp).attr('data-text');
            var wtai_label = rowevent.find('.wtai_'+colgrp).attr('data-colname');
            var value = rowevent.find('.'+colgrp).attr('data-text');
            var label = rowevent.find('.'+colgrp).attr('data-colname');
            var wtai_nonce = get_wp_nonce();
            if (  typeof value === 'undefined'  ) {
                var row_id = rowevent.data('id');

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        action: 'wtai_get_category_tooltip_text',
                        category_id: row_id,
                        colgrp: colgrp,
                        wtai_nonce: wtai_nonce
                    },
                    success: function(data) {
                        var  res_text = '';
                        if (data.text === undefined || data.text === null) {
                            res_text = '';
                        }  else {
                            res_text = data.text;
                        }
                        rowevent.find('.'+colgrp).attr('data-text', res_text );
                        var html = html_tooltip (wtai_label, wtai_value, label, res_text );
                        $('.tooltipster-base').find('.tooltipster-content').html(html);
                    }
                });
            } else {
                var html = html_tooltip (wtai_label, wtai_value, label, value );
                var intevalbase = setInterval( function () {
                    if( $('.tooltipster-base').find('.tooltipster-content').length > 0 ){
                        $('.tooltipster-base').find('.tooltipster-content').html(html);
                        clearInterval(intevalbase);
                    }
                }, 200);
            
                return html;
            }
        }
    };

    $('.tooltip_hover').tooltipster(tooltipster_var);

    function html_tooltip (gen_label, gen_text, trans_label, trans_text) {
        var html = '<div class="wtai-tooltip-transfer-text wtai-tooltiptext"><span class="wtai-label">' + gen_label + '</span><p>' + gen_text + '</p></div><div class="wtai-tooltip-generate-text wtai-tooltiptext"><span class="wtai-label">' + trans_label + '</span><p>' + trans_text + '</p></div>';
        return html;
    }

    function get_wp_nonce(){
        var nonce = $('.wtai-list-table').attr('data-product-nonce');
        return nonce;
    }

    tooltipster_callback();
    function tooltipster_callback(){
        var tooltipElements = $('.tooltip_hover.tooltipstered');

        if ($('#wtai-comparison-cb').prop('checked')) {
            tooltipElements.each(function() {
                $(this).tooltipster('enable');
            });
        } else {
            tooltipElements.each(function() {
                $(this).tooltipster('disable');
            });
        }
    }

    function endsWithEllipsis(text) {
        return text.endsWith('...');
    }

    $(document).on('change', '#wtai-comparison-cb', function(){
        //render data
        if(this.checked) {
            var fields = ['page_title', 'page_description', 'category_description', 'open_graph', 'wtai_page_title', 'wtai_page_description', 'wtai_category_description', 'wtai_open_graph'];
            $('.wtai-list-table tbody td').each(function(){
                var data_group = $(this).attr('data-colgrp');
                if (fields.includes(data_group)) {
                    var data_content = $(this).text();
                    var ellipsified = endsWithEllipsis(data_content);

                    if( ellipsified ){
                        if( $(this).hasClass( 'tooltip_hover' ) == false ){
                            $(this).addClass('tooltip_hover');
                            $(this).tooltipster(tooltipster_var);
                        }
                    }
                }
            });
        }

        var tooltipElements = $('.tooltip_hover.tooltipstered');
        if(this.checked) {
            value = 1;
            tooltipElements.each(function() {
                $(this).tooltipster('enable');
            });
        } else {
            value = 0;
            tooltipElements.each(function() {
                $(this).tooltipster('disable');
            });
        }

        var wtai_nonce = get_wp_nonce();
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_comparison_category_user_check',
                value: value,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    });

    var date_picker_params = {
        onSelect: function(dateText, inst) { 
            var month = parseInt(inst.selectedMonth) + 1;
            if( month < 10 ){
                month = '0' + month;
            }

            var day = parseInt(inst.selectedDay);
            if( day < 10 ){
                day = '0' + day;
            }
            var date = inst.selectedYear+'-'+month+'-'+day;
            $(this).attr('date-format', date);

            var field = $(this).attr('data-field');

            if( field == 'from' ){
                $(this).closest('.wtai-history-filter-form').find('.wtai-history-date-input-to').datepicker('option', 'minDate', dateText);

                var $form = $(this).closest('.wtai-history-filter-form');
                var $historyDateTo = $form.find('.wtai-history-date-input-to');
                var $filterDateTo = $(this).closest('.wtai-history-filter-form').find('.filter-date-to');
    
                // Get the current "to" date and compare it with the selected "from" date
                var currentToDate = $historyDateTo.datepicker('getDate');
                var selectedFromDate = new Date(inst.selectedYear, inst.selectedMonth, inst.selectedDay);
    
                if (currentToDate && selectedFromDate > currentToDate) {
                    // If the selected "from" date is after the current "to" date, update "to" date to "from" date
                    $historyDateTo.datepicker('setDate', selectedFromDate);
                    $filterDateTo.datepicker('setDate', selectedFromDate);
                }

                var final_date_to = $(this).closest('.wtai-history-filter-form').find('.wtai-history-date-input-to').datepicker('getDate');
                var final_date_to_string = wtaiFormatDateToYYYYMMDD( final_date_to );

                $(this).closest('.wtai-history-filter-form').find('.wtai-history-date-input-to').attr('date-format', final_date_to_string);
            }
        },
        onClose: function(selectedDate, inst) {
            var field = $(this).attr('data-field');
            if (selectedDate == '' && field == 'from' ) {
                // Clear the minimum date of the end datepicker if the start datepicker is cleared
                $(this).closest('.wtai-history-filter-form').find('.wtai-history-date-input-to').datepicker('option', 'minDate', null);
            }
        },
        maxDate: new Date() // Today's date
    };

    if ( $('.wtai-filter-date').find('.wtai-calendar-field').find('.filter-date').length > 0 ){
        $('.wtai-filter-date').find('.wtai-calendar-field').find('.filter-date').datepicker(date_picker_params);
    }

    if ( $('.wtai-history').find('.wtai-calendar-field').find('.wtai-history-date-input').length > 0 ){
        $('.wtai-history').find('.wtai-calendar-field').find('.wtai-history-date-input').datepicker(date_picker_params);
    }

    $(document).on('click', function(event) {
        if ( $('#TB_ajaxContent').length ) {
            return;
        }
        var form = $('#wpbody'); 
        var clickedElement = event.target;
        $('.wtai-exit-edit-leave').removeAttr('data-type');
        $('.wtai-exit-edit-leave').removeAttr('data-href');
        
        // Check if the clicked element is not within the form
        if ( !form.is(clickedElement) && !form.has(clickedElement).length ) {
            var number_of_changes_unsave = checkChanges();
            var link = $(clickedElement).closest('li').find('a').attr('href');
            $('.wtai-exit-edit-leave').attr('data-href', link);
            
            if( number_of_changes_unsave > 0 && link != undefined ) {
               // Check if the clicked element is not within .mce-container or .mce-floatpanel
                if (!$(event.target).closest('.mce-container, .mce-floatpanel').length) {
                    $('.wtai-slide-right-text-wrapper').find('.header-slider').find('.wtai-close.dashicons').click();
                }
                return false;
            } 
        } 
    });

    $(document).on('click', '#wtai-product-edit-cancel .wtai-exit-edit-leave', function(e){
        $('#wpbody-content').removeClass('wtai-overlay-div-2');
        $(this).closest('.wtai-loader-generate').removeAttr('style');
        if ( $('.wtai-metabox.wtai-metabox-update').length > 0 ){
            $('.wtai-metabox.wtai-metabox-update').each(function(){
                $(this).removeClass('wtai-metabox-update');
            });
        }
        var href = $(this).attr('data-href');
        var type = $(this).attr('data-type');

        if( href) {
            $(window).off('beforeunload');
        }
        switch( type ){
            case 'close':
                if( !href) {
                    close_category_edit_form();
                } else {
                    window.location.href = href;
                }
                break;
            case 'prev':
            case 'next':
                if( !href) {
                    var button =  $('.wtai-slide-right-text-wrapper').find('wtai-product-pager-wrapper').find('.button-'+type);
                    prev_next_to_button(button);
                } else {
                    window.location.href = href;
                }
                break;
        }

        e.preventDefault();
    });

    $(document).on('click', '#wtai-product-edit-cancel .exit-edit-cancel', function(e){
        $('#wpbody-content').removeClass('wtai-overlay-div-2');
        $(this).closest('.wtai-loader-generate').addClass('command-trigger');
        if ( $(this).closest('.wtai-loader-generate').is(':visible') ) {            
            $(this).closest('.wtai-loader-generate').removeAttr('style');
        }
        setTimeout(() => {
            $(this).closest('.wtai-loader-generate').removeClass('command-trigger');
        }, 300);
        e.preventDefault();
    });

    $(document).on('click', '.wtai-product-generate-cancel', function(e){
        $('#wpbody-content').removeClass('wtai-overlay-div-2');
        $(this).closest('.wtai-loader-generate').addClass('command-trigger');
        $(this).closest('.postbox').removeClass('wtai-proceed');
        $('.wtai-page-generate-all').removeClass('wtai-proceed');

        $('.wtai-page-generate-all').attr('data-rewrite', '0');
        if ( $(this).closest('.wtai-loader-generate').is(':visible') ) {            
            $(this).closest('.wtai-loader-generate').removeAttr('style');
        }

        addHighlightKeywords();

        setTimeout(() => {
            $(this).closest('.wtai-loader-generate').removeClass('command-trigger');
        }, 300);
        e.preventDefault();
    });

    /*Generate/Cancel popup*/
    $(document).on('click', '.wtai-product-generate-proceed', function(e){

        $('#wpbody-content').removeClass('wtai-overlay-div-2');
        $(this).closest('.wtai-loader-generate').removeAttr('style');
     
        var type = $(this).attr('data-type');
        var submittype = $(this).attr('data-submittype');

        switch( submittype ){
            case 'single':
                $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                $('#wtai-product-details-'+type).find('.wtai-single-button-text.wtai-generate-text').click();
                break;
            case 'bulk':
                $('#postbox-container-2').find('.wtai-metabox').each(
                function(){
                        if ( $(this).find('.postbox-header').find('.wtai-checkboxes').is( ':checked' ) && $(this).hasClass('wtai-metabox-update') ) {
                            $(this).removeClass('wtai-metabox-update');
                        }
                    }
                );
                $('.wtai-slide-right-text-wrapper').find('.wtai-page-generate-all').click();
                break;
        }

        e.preventDefault();
    });
 
    $(window).on('beforeunload', function() {
        var number_of_changes_unsave = checkChanges();

        if ( $('#wpwrap').hasClass('wtai-loader') ) {
            //if ( $('.wtai-metabox.wtai-metabox-update').length > 0 ) { 
            if( $('.wtai-global-loader').hasClass('wtai-is-active') || number_of_changes_unsave > 0 ) {    
                var message = WTAI_OBJ.confirm_leave;
                return message;
            }  
        }
    });

    (function() {
        tinymce.create('tinymce.plugins.wtacustomlink', {
            init : function(editor) {
                // Add a button that opens a window
                editor.addButton('wtacustomlink', {
                    title : WTAI_OBJ.tinymcelinktext1,
                    icon : 'link',
                    onclick : function() {
                        // Get selected text and link attributes if any
                        var selectedText = editor.selection.getContent({format: 'text'});
                        var selectedNode = editor.selection.getNode();
                        var linkUrl = '';
                        var linkTarget = '';
                        if (selectedNode.nodeName === 'A') {
                            linkUrl = selectedNode.href;
                            linkTarget = selectedNode.target;
                        }
                        
                        // Open window
                        var winLink = editor.windowManager.open({
                            title: WTAI_OBJ.tinymcelinktext1,
                            body: [
                                {type: 'textbox', name: 'linkText', label: WTAI_OBJ.tinymcelinktext4, value: selectedText},
                                {type: 'textbox', name: 'linkUrl', label: WTAI_OBJ.tinymcelinktext5, value: linkUrl},
                                {type: 'checkbox', name: 'openNewWindow', label: WTAI_OBJ.tinymcelinktext6, checked: linkTarget === '_blank'}
                            ],
                            buttons: [
                                {
                                    text: WTAI_OBJ.tinymcelinktext2,
                                    subtype: 'primary',
                                    onclick: function() {
                                        winLink.submit();
                                    }
                                },
                                {
                                    text: WTAI_OBJ.tinymcelinktext3,
                                    onclick: function() {
                                        editor.windowManager.close();
                                    }
                                }
                            ],
                            onsubmit: function(e) {
                                // Insert or update link when the window form is submitted
                                var linkText = e.data.linkText.trim();
                                var linkUrl = e.data.linkUrl.trim();
                                var openNewWindow = e.data.openNewWindow;
                                var html = '<a href="' + linkUrl + '"';
                                if (openNewWindow) {
                                    html += ' target="_blank"';
                                }
                                html += '>' + (linkText !== '' ? linkText : selectedText) + '</a>';
                                
                                if (selectedNode.nodeName === 'A') {
                                    // Update existing link
                                    editor.selection.setContent(html);
                                } else {
                                    // Insert new link
                                    editor.insertContent(html);
                                }
                            },
                            oncancel: function() {
                                // Close the window when the user clicks outside of it
                                editor.windowManager.close();
                            }
                        });
                    }
                });
            },
            createControl : function() {
                return null;
            },
            getInfo : function() {
                return {
                    longname : 'WTA Custom Link Plugin',
                    author : '1902',
                    version : '1.0'
                };
            }
        });
        tinymce.PluginManager.add('wtacustomlink', tinymce.plugins.wtacustomlink);
    })();

    //load tinymce editors
    load_tiny_mce();
    
    function load_tiny_mce(){
        $('#wpwrap').find('.wtai-wp-editor-setup').each(function(){
            var id = $(this).attr('id');
            wp.editor.initialize( 
                id,
                {
                    tinymce: {
                        wpautop: true,
                        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wtacustomlink',
                        toolbar1: 'formatselect bold italic underline strikethrough numlist bullist blockquote alignleft aligncenter alignright | wtacustomlink unlink',
                        visual : true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: 'body *{box-sizing: border-box;}.wtai-highlight{background-color: #96C3F3; color: #303030; } .wtai-highlight2{background-color: #E9E2F2;color: #303030;} .wtai-highlight .wtai-highlight2{background-color: transparent} .wtai-highlight3{background-color: #fccccc;color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: #303030;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width:5px; height:16px; display:inline-block; }@keyframes changed {from {rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.5;color:#1d2327;margin: 0 0 15px; padding:0; font-weight:600;}h1{font-size: 2em;}h2{font-size: 1.5em;}h3{font-size: 1.17em;}h4{font-size: 1em;}h5{font-size: 0.83em;}h6{font-size: 0.67em;}a{color: #2271b1;}b,strong{font-weight:600;}dd, li { margin-bottom: 6px;font-size:13px; line-height: 17px; color: #303030;}ul,ol{padding:0 0 0 30px;margin-block-start: 1em;margin-block-end: 1em;}ul,ul > li{ list-style: disc;}ol,ol > li{ list-style: decimal;}del { text-decoration: line-through;}',
                        entity_encoding: 'raw'
                    },
                    quicktags: true
                }, 
                
            );
        }); 

        $('#wpwrap').find('.wtai-wp-editor-setup-cloned').each(function(){
            var id = $(this).attr('id');
            wp.editor.initialize( 
                id,
                {
                    tinymce: {
                        wpautop: true,
                        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wtacustomlink',
                        toolbar1: 'formatselect bold italic underline strikethrough numlist bullist blockquote alignleft aligncenter alignright | wtacustomlink unlink',
                        visual : true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: 'body *{box-sizing: border-box;}.wtai-highlight{background-color: #96C3F3; color: transparent; } .wtai-highlight2{background-color: #E9E2F2;color: transparent;} .wtai-highlight .wtai-highlight2{background-color: transparent} .wtai-highlight3{background-color: #fccccc;color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: transparent;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width:5px; height:16px; display:inline-block; }@keyframes changed {from {rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.5;color:#1d2327;margin: 0 0 15px; padding:0; font-weight:600;}h1{font-size: 2em;}h2{font-size: 1.5em;}h3{font-size: 1.17em;}h4{font-size: 1em;}h5{font-size: 0.83em;}h6{font-size: 0.67em;}a{color: #2271b1;}strong{font-weight:600;}.wtai-highlight-invalid-check .wtai-highlight{background-color: transparent;} .wtai-highlight-invalid-check .wtai-highlight2{background-color: transparent;}dd, li { margin-bottom: 6px;font-size:13px; line-height: 17px; color: #303030;}ul,ol{padding:0 0 0 30px;margin-block-start: 1em;margin-block-end: 1em;}ul,ul > li{ list-style: disc;}ol,ol > li{ list-style: decimal;}del { text-decoration: line-through;}',
                        entity_encoding: 'raw'
                    },
                    quicktags: true
                }, 
                
            );
        }); 
        
        $('#wpwrap').find('.wtai-wp-editor-setup-others').each(function(){
            var id = $(this).attr('id');
            wp.editor.initialize( 
                id,
                {
                    tinymce: {
                        wpautop: true,
                        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: '',
                        toolbar: false, 
                        menubar: false,
                        visual : false,
                        paste_as_text: true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: '.wtai-highlight{background-color: #96C3F3; color: #303030;} .wtai-highlight2{background-color: #E9E2F2; color: #303030;} .wtai-highlight .wtai-highlight2{background-color: transparent} .wtai-highlight3{background-color: #fccccc;color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: #303030;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width: 5px; height:16px; display:inline-block;}@keyframes changed {from {background-color:rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.2;}',
                        setup: function (editor) {
                            editor.on('keyup', function () {
                                if ( $('.wtai-bulk-process').length == 0 ) {
                                    //added condition for #53
                                    if ( $('.wtai-metabox .postbox-header .wtai-checkboxes:checked').length > 0 ) {
                                        $('.wtai-page-generate-all').removeClass('disabled');
                                        $('.wtai-generate-wrapper .toggle').removeClass('disabled');
                                    }
                                    
                                    $('.wtai-page-generate-all').removeClass('wtai-generating');

                                    $('#publishing-action .wtai-button-interchange').removeClass('disabled');
                                }
                            });
                        },
                        entity_encoding: 'raw'
                    },
                    quicktags: false
                }, 
            );
        });

        $('#wpwrap').find('.wtai-wp-editor-setup-others-cloned').each(function(){
            var id = $(this).attr('id');
            wp.editor.initialize( 
                id,
                {
                    tinymce: {
                        wpautop: true,
                        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: '',
                        toolbar: '',
                        visual : true,
                        paste_as_text: true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: '.wtai-highlight{background-color: #96C3F3; color: transparent;} .wtai-highlight2{background-color: #E9E2F2; color: transparent;} .wtai-highlight .wtai-highlight2{background-color: transparent} .wtai-highlight3{background-color: #fccccc;color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: transparent;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width: 5px; height:16px; display:inline-block;}@keyframes changed {from {background-color:rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.2;} .wtai-highlight-invalid-check .wtai-highlight{background-color: transparent;} .wtai-highlight-invalid-check .wtai-highlight2{background-color: transparent;}',
                        setup: function (editor) {
                            editor.on('keyup', function () {
                                if ( $('.wtai-bulk-process').length == 0 ) {
                                    $('.wtai-page-generate-all').removeClass('disabled');
                                    $('.wtai-page-generate-all').removeClass('wtai-generating');
                                    $('.wtai-generate-wrapper .toggle').removeClass('disabled');
                                    $('#publishing-action .wtai-button-interchange').removeClass('disabled');
                                }
                            });
                        },
                        entity_encoding: 'raw'
                    },
                    quicktags: false
                }, 
            );
        }); 
    }

    // Note: Common function ?
    (function($) {
        $.fn.hasScrollBar = function() {
            return this.get(0).scrollHeight > this.height();
        };
    })(jQuery);

    $( document ).on( 'tinymce-editor-init', function (event, editor) {
		editor.on( 'change keyup', function () {
            var type = '';
            var editor_id = '';
            if ( $('.wtai-bulk-process').length == 0) {
                editor_id = editor.id;
                type = editor_id.replace('wtai-wp-field-input-','');
                if ( $('#wtai-product-details-'+type).length > 0 && 
                    ! $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') )  {
                    $('#wtai-product-details-'+type).addClass('wtai-metabox-update');
                }   
            }

            //var new_content = wp.editor.getContent(editor.id); // Visual tab is active;
            var new_content = tinymce.get(editor.id).getContent( { format: 'raw' } ); // Visual tab is active;
            $('#'+editor.id).closest('.postbox').find('.wtai-data-new-text').remove();
            $('#'+editor.id).closest('.postbox').find('.wtai-hidden-text').append('<div class="wtai-data-new-text" style="display:none">'+new_content+'|</div>');
        
            typeCountMessage( type, editor.getContent({format: 'text'}) );
            
            try{
                getKeywordOverallDensity();

                if( $('.wtai-page-generate-all').hasClass('wtai-generating') == false ){
                    bulk_transfer_button_behavior();
                    rewrite_button_state_behavior();
                }
            }
            catch( exc ){
            }

            addHighlightKeywordsbyFieldOnKeyup(editor.id);            
		});

        editor.on('change keyup', function(){
            handle_save_button_state();
            handle_single_transfer_button_state();
            bulk_transfer_button_behavior();
        });

        editor.on('keydown', function(e){
            // Check if Ctrl+Z (Undo) is pressed
            if (e.keyCode === 90 && e.ctrlKey) {
                var undoManager = editor.undoManager;
                var historyData = undoManager.data;
                
                // Get the index of the current undo step
                var currentIndex = undoManager.index;
                // Check if the current content is equivalent to data[1]
              //  if ( currentIndex === undefined || typeof historyData[1] === 'undefined') {
                if ( currentIndex === undefined || typeof historyData[1] === 'undefined' || currentIndex >= 1 && editor.getContent() === historyData[1].content) {
                    e.preventDefault(); // Prevent the default undo behavior
                // Add custom logic for handling the scenario when current content matches data[1]
                }
            }
        });

        $(editor.getWin()).bind('scroll', function(){
            var id = editor.id;

            if( $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').length 
            ){
                var clonedId = $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').attr('id');
                var clonedEditor = tinymce.get(clonedId);
                clonedEditor.contentWindow.scrollTo(0, editor.getWin().scrollY);
            }
        });

        editor.on('click', function() {
            addHighlightKeywordsbyFieldOnKeyup(editor.id);
        });
        
        // Attach a "blur" event listener to the editor
        editor.on('blur', function() {
            addHighlightKeywordsbyFieldOnKeyup(editor.id);     
        });
        
    });

    $(window).on('resize', function(){
        addHighlightKeywords();
    });

    $(document).on('click', '.wp-switch-editor', function(){
        setTimeout( addHighlightKeywords, 500 );
    });

    $(document).on('click', '.wtai-cwe-action-title', function(e){
        e.preventDefault();
    
        if( $(this).hasClass('disabled_on_edit_button') ){
            return;
        }
    
        $(this).parent().find('.wtai-cwe-action-button.wtai-cwe-action-button-edit').trigger('click');
    });

    $(document).on('click', '.wtai-cwe-action-button', function(e){
        var event = $(this);
        var action = event.data('action');
        var event_tr = event.closest('tr');

        //bypass disabled button
        if ( event.hasClass('wtai-disabled-button') ){
            return false;
        }

        //bypass when the edit html page is currently loading
        if ( event.hasClass('disabled_on_edit_button') ){
            return false;
        }

        var post_id = event_tr.data('id');

        switch(  action ){
            case 'edit':
                $('html, body').scrollTop(0);

                topheader_post(); // maybe reposition the header on mobile when single edit product is clicked.

                $('#wpwrap').addClass('wtai-overlay');

                show_hide_global_loader('show');

                // Hide or show step options
                $('.wtai-hide-step-cb-wrap').show();
                $('.wtai-hide-step-separator').show();

                // Show restore global settings button
                $('.wtai-restore-global-settings-wrap').show();
                $('.wtai-restore-global-settings-separator').show();

                $('.wtai-post-data-json').each(function(){
                    var postfield = $(this).data('postfield');

                    var elementobject = $(this);
                    switch( postfield ){
                        case 'post_title':
                            elementobject.html( event_tr.find('.wtai_title').find('a').attr('data-category-name') );
                            break;
                        case 'post_permalink':
                            elementobject.attr( 'href', event_tr.find('.wtai_title').find('a').attr('href') );
                            elementobject.html( event_tr.find('.wtai_title').find('a').attr('href') );
                            break;
                        case 'post_id':
                            elementobject.attr( 'value', post_id );
                            break;
                        default:
                            break;
                    }
                });

                $('body').addClass('wtai-open-single-slider');

                $('#wpwrap').addClass('wtai-loader'); 
                $('.wtai-slide-right-text-wrapper').addClass('wtai-disabled-click');

                // Load category edit content;
                get_category_edit_data( post_id, 1 );

                // Load keywords data
                setTimeout(function() {
                    $('body').addClass('wtai-open-single-slider');
                
                    $('#wpwrap').addClass('wtai-loader'); 
                    $('.wtai-slide-right-text-wrapper').addClass('wtai-disabled-click');
                    
                    get_category_keyword_edit_data_ajax( post_id );
                }, 300);
            break;
        }

        e.preventDefault();
    });

    function get_category_keyword_edit_data_ajax( post_id ){
        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_category_data',
                category_id: post_id,
                wtai_nonce : wtai_nonce,
            },
            success: function(res){
                if ( res.message != '1' && res.message ){
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }

                    //display general error message during generation
                    $('<div id="message" class="wtai-nonce-error error notice is-dismissible"><p>'+res.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
                else{
                    $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html('');

                    $('.wtai-post-data-json').each(function(){
                        var postfield = $(this).data('postfield');
    
                        var elementobject = $(this);
                        switch( postfield ){
                            case 'post_title':
                                elementobject.html( res.result.post_title );
                                break;
                            case 'post_permalink':
                                elementobject.attr( 'href', res.result.post_permalink );
                                elementobject.html( res.result.post_permalink );
                                break;
                            default:
                                break;
                        }
                    });

                    get_category_keyword_edit_data ( res );

                    $('.wtai-slide-right-text-wrapper').removeClass('wtai-disabled-click');

                    getKeywordOverallDensity();

                    //handle keyword density state
                    handle_density_premium_state( res.result['is_premium'] );
                }
            }
        });
    }

    /*Topheadeer reposition in 600px below*/
    var topheader = $('.wtai-top-header');
    var topheader_top = 0;

    topheader_post();
    $(window).scroll(function() {
        topheader_post();    
    });

    $(window).resize(function() {
        topheader_post();    
    });

    function topheader_post(){
        topheader_top = topheader.offset().top;
        var scroll_top = $(window).scrollTop();

        if ( $(window).width() < 601 ) {
            
            if (topheader_top >= scroll_top && topheader_top > 46) {
                topheader.css({ top: 0 });
            } else {
                topheader.css({top: '46px' });
            }
        } 
    }

    function show_hide_global_loader( state = 'show' ){
        if( state == 'show' ){
            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');
        }
        else{
            $('.wtai-global-loader').removeClass('wtai-is-active');
            $('.wtai-ai-logo').removeClass('wtai-hide');
        }
    } 

    // getDataPerProductBlockInit();
    function get_category_edit_data( post_id, refresh_credits = 1 ){
        removeHighlightkeywords();

        initializeToolTipForGenerateFilter();
        initializeToolTipForSingleTransferButtons();

        //step guide hide state
        if( $('.wtai-hide-step-cb-wrap #wtai-hide-step-cb').is(':checked') ){
            $('.wtai-step-guideline').addClass('wtai-hide');
        }
        else{
            $('.wtai-step-guideline').removeClass('wtai-hide');
        }

        if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
            $('.wtai-edit-product-line' ).find('#message').remove();
        }  

        $('#wtai-edit-post-id').attr('value', post_id);

        // AJAX
        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_single_category_data_text',
                category_id: post_id,
                refresh_credits : refresh_credits,
                wtai_nonce : wtai_nonce,
            },
            success: function(res){
                if ( res.success != '1' && res.message ){
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }

                    //display general error message during generation
                    $('<div id="message" class="wtai-nonce-error error notice is-dismissible"><p>'+res.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
                else{
                    var is_premium = res.result.is_premium;
                    var text_source = res.result.text_source;

                    //step guide hide state
                    if( res.result['hide_step_guide'] == '1' ){
                        $('.wtai-step-guideline').addClass('wtai-hide');
                    }
                    else{
                        $('.wtai-step-guideline').removeClass('wtai-hide');
                    }

                    if( res.result['available_credit_label'] != '' ){
                        $('.wtai-credit-available-wrap .wtai-credit-available').html( res.result['available_credit_label'] );
                    }

                    var wtai_preselected_types = res.result.wtai_preselected_types;

                    //compute credits needed 
                    $('#postbox-container-2 .postbox-header .wtai-checkboxes').each(function(){
                        var cb_data_type = $(this).attr('data-type');
                        if( wtai_preselected_types.length > 0 && wtai_preselected_types.indexOf(cb_data_type) > -1 ){
                            $(this).prop('checked', true);                        
                        }
                        else{
                            $(this).prop('checked', false);
                        }
                    });

                    //set user preference tones
                    var preference_tones = res.result.preference_tones;
                    if( preference_tones.length > 0 ){
                        $('.postbox-container .wtai-product-tones-cb').prop('checked', false);

                        $('.postbox-container .wtai-custom-tone-cb').prop('checked', false);
                        $('.postbox-container .wtai-custom-tone-text').val('');
                    
                        $.each(preference_tones, function( index, tone_selection ){
                            if (tone_selection.includes('wtaCustom::')){
                                var tone_selection_arr = tone_selection.split('::');
                                $('.postbox-container .wtai-custom-tone-cb').prop('checked', true);
                                $('.postbox-container .wtai-custom-tone-text').val(tone_selection_arr[1]);

                                var custom_tone_char_length = tone_selection_arr[1].length;
                                $('.postbox-container .wtai-custom-tone-cb').closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html(custom_tone_char_length);
                            }
                            else{
                                $('.postbox-container .wtai-product-tones-cb').each(function(){
                                    if( $(this).val() == tone_selection ){
                                        $(this).prop('checked', true);
                                    }
                                });
                            }
                        });
                    }

                    var setToneCount = 0;
                    if( $('.postbox-container .wtai-product-tones-cb:checked').length ){
                        setToneCount = $('.postbox-container .wtai-product-tones-cb:checked').length;
                    }
                    else{
                        if( $('.postbox-container .wtai-custom-tone-text').val() != '' ){
                            setToneCount = 1;
                        }
                    }

                    var preference_styles = res.result.preference_styles;
                    var setStyleCount = 0;
                    if( preference_styles != '' ){
                        $('.postbox-container .wtai-product-styles-cb').prop('checked', false);

                        $('.postbox-container .wtai-custom-style-cb').prop('checked', false);
                        $('.postbox-container .wtai-custom-style-text').val('');

                        if (preference_styles.includes('wtaCustom::')){
                            var preference_styles_arr = preference_styles.split('::');
                            $('.postbox-container .wtai-custom-style-cb').prop('checked', true);
                            $('.postbox-container .wtai-custom-style-text').val(preference_styles_arr[1]);

                            var custom_style_char_length = preference_styles_arr[1].length;
                            $('.postbox-container .wtai-custom-style-cb').closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html(custom_style_char_length);

                            setStyleCount = 1;
                        }
                        else{
                            $('.postbox-container .wtai-product-styles-cb').each(function(){
                                if( $(this).val() == preference_styles ){
                                    $(this).prop('checked', true);

                                    setStyleCount = 1;
                                }
                            });
                        }
                    }
                    
                    var setToneStyleCount = setToneCount + setStyleCount;

                    if( setToneStyleCount ){
                        $('.wtai-tone-and-style-form-wrapper .wtai-tone-and-styles-select').find('.wtai-button-label').find('.wtai-button-num').text(setToneStyleCount);
                    }

                    var preference_audiences = res.result.preference_audiences;
                    var setPreferenceCount = 0;
                    if( preference_audiences.length > 0 ){
                        $('.postbox-container .wtai-product-audiences-cb').prop('checked', false);
                        
                        $.each(preference_audiences, function( index, audience_selection ){
                            $('.postbox-container .wtai-product-audiences-cb').each(function(){
                                if( $(this).val() == audience_selection ){
                                    $(this).prop('checked', true);
                                }
                            });
                        });        
                        
                        setPreferenceCount = $('.postbox-container .wtai-product-audiences-cb:checked').length;
                    }

                    $('.wtai-audiences-form-wrapper .wtai-audiences-form-select .wtai-button-label .wtai-button-num').text(setPreferenceCount);

                    //disable formal conditioons
                    set_disallowed_combinations_single();

                    //disable rewrite if this product has not been generated yet
                    $('.wtai-generate-wrapper .button-primary.toggle').removeClass('disabled');
                    if( res.result.has_platform_text == '0' ){
                        $('.wtai-generate-wrapper .button-primary.toggle').addClass('disabled');
                    }                

                    $('.wtai-review-check').attr('has-generated-text', 'no');
                    $('.wtai-review-check').prop('disabled', false);
                    $('.wtai-review-check').prop('checked', false);
                    if( res.result.has_generated_text == '1' ){
                        $('.wtai-review-check').attr('has-generated-text', 'yes');
                        $('.wtai-review-check').prop('disabled', false);

                        // && res.result.has_transferred_text != '1' && res.result.has_generated_not_reviewed_text != '1'
                        if( res.result.has_reviewed_text == '1' && ( res.result.is_all_reviewed == '1' || res.result.is_all_generated_reviewed == '1' ) ){
                            $('.wtai-review-check').prop('checked', true);
                        }
                    }     

                    if( res.result.is_all_transferred == '1' ){
                        $('.wtai-review-check').prop('disabled', true);
                        $('.wtai-review-check').prop('checked', true);
                    }

                    $('.wtai-review-check').closest('.wtai-review-wrapper').removeClass('wtai-review-wrapper-disabled');
                    if( $('.wtai-review-check').is(':disabled') ){
                        $('.wtai-review-check').closest('.wtai-review-wrapper').addClass('wtai-review-wrapper-disabled');
                    }

                    //render SKU
                    $('.wtai-header-title .wtai-product-short-title.wtai-post-data-json').val(res.result.product_short_title);

                    //check highlight pronouns
                    if( $('.wtai-highlight-incorrect-pronouns-cb').length ){
                        if( res.result.wtai_highlight_pronouns == '1' ){
                            $('.wtai-highlight-incorrect-pronouns-cb').prop('checked', true);
                        }
                        else{
                            $('.wtai-highlight-incorrect-pronouns-cb').prop('checked', false);
                        }
                    }

                    //load early data
                    if( $('.wp-list-table').attr('data-doing-prev-next') == '1' ){
                        $('.wp-heading-inline.wtai-post-title.wtai-post-data-json').html(res.result['wp_product_title']);

                        $('.wtai-header-title .wtai-post-title').css('visibility','visible');
                        $('.wp-list-table').attr('data-doing-prev-next', '');
                    }

                    $('.wtai-post-data').each(function(){
                        var postfield = $(this).data('postfield');
                        var elementobject = $(this);
                        
                        if( postfield == 'post_permalink' ){
                            elementobject.attr( 'href', res.result[postfield] );
                            elementobject.html( res.result[postfield] );
                        }
                    });

                    $('.wtai-loading-metabox').find('.wtai-generate-text').each(function(){
                        var data_object = $(this).closest('.wtai-loading-metabox');
                        var type = $(this).data('type');

                        if ( data_object.find('.wtai-api-data-'+type).find('.wtai-text-count-details').length > 0 ){
                            data_object.find('.wtai-api-data-'+type).find('.wtai-text-count-details').remove();
                        }

                        if ( data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details').length > 0 ){
                            data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details').remove();
                        }

                        var texthtml = '';
                        var overlimithtml = '';
                        var textvaluehtml = '';
                        var overlimitvaluehtml = '';
                        var generatedText = '';
                        var wpGeneratedText = '';
                        var id = '';
                        var editorTinyMCEGeneratedText = '';

                        switch( type ){
                            case 'category_description':
                                id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                if( tinymce.get(id) ) {
                                    tinymce.get(id).setContent( res.result[type] );

                                    editorTinyMCEGeneratedText = tinymce.get(id).getContent({format: 'text'});
                                }

                                generatedText = res.result[type];
                                wpGeneratedText = res.result[type+'_value'];

                                updateHiddentext(id);
                                break;
                            default:
                                id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                if( tinymce.get(id) ) {
                                    tinymce.get(id).setContent( res.result[type] );

                                    editorTinyMCEGeneratedText = tinymce.get(id).getContent({format: 'text'});
                                }
                                
                                generatedText = res.result[type];                               
                                generatedText = wtaiRemoveLastBr( generatedText );

                                //wpGeneratedText = res.result[type+'_value'].replace(/\n/g, '<br>');
                                wpGeneratedText = res.result[type+'_value'];
                                wpGeneratedText = wtaiRemoveLastBr( wpGeneratedText );

                                updateHiddentext(id);

                                data_object.find('.wtai-api-data-'+type).prop('disabled', false );
                                var count = res.result[type+'_string_count']+'/'+WTAI_OBJ.text_limit[type];
                                count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                texthtml = texthtml+count+' | ';
                                if ( parseInt(res.result[type+'_string_count']) > parseInt(WTAI_OBJ.text_limit[type]) ){
                                    overlimithtml = 'over_limit';
                                }
                                var count = res.result[type+'_value_string_count']+'/'+WTAI_OBJ.text_limit[type];
                                count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                textvaluehtml = textvaluehtml+count+' | ';
                                if ( parseInt(res.result[type+'_value_string_count']) > parseInt(WTAI_OBJ.text_limit[type]) ){
                                    overlimitvaluehtml = 'over_limit';
                                }
                                break;
                        }

                        var words = res.result[type+'_words_count'];
                        words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                        texthtml = texthtml+words;
                        
                        if( data_object.find('.wtai-text-count-details').length <= 0 ) {
                            data_object.find('.wtai-api-data-'+type).after('<span class="wtai-text-count-details '+overlimithtml+'"><span class="wtai-char-counting">'+texthtml+'</span></span>');
                        
                        } else {
                            data_object.find('.wtai-api-data-'+type).parent().find('.wtai-text-count-detailsg').addClass(overlimithtml);
                            data_object.find('.wtai-api-data-'+type).parent().find('.wtai-char-counting').html(texthtml);
                        }

                        typeCountMessage( type, editorTinyMCEGeneratedText );

                        var words = res.result[type+'_value_words_count'];
                        words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                        textvaluehtml = textvaluehtml+words;
                    
                        if( !data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details') ) {
                            data_object.find('.wtai-api-data-'+type+'_value').parent().append('<span class="wtai-text-count-details '+overlimitvaluehtml+'"><span class="wtai-char-counting">'+textvaluehtml+'</span></span>');
                        }

                        wpGeneratedText = wtaiCleanUpHtmlString( wpGeneratedText );
                        
                        data_object.find('.wtai-api-data-'+type+'_id').val(res.result[type+'_id']);
                        data_object.find('.wtai-api-data-'+type+'_value').html(wpGeneratedText);

                        var platform_string_length = data_object.find('.wtai-current-value .wtai-text-message').text().trim().length;

                        //field product status
                        data_object.find('.wtai-field-product-status').html(res.result['field_product_status']);

                        //display character and word count                   
                        data_object.find('.wtai-static-count-display .wtai-char-count').html(platform_string_length);
                        data_object.find('.wtai-static-count-display .wtai-char-count').attr('wtai-char-count-credit', res.result[type+'_platform_string_count_for_credit']);
                        data_object.find('.wtai-static-count-display .word-count').html(res.result[type+'_platform_words_count']);
                        
                        data_object.removeClass('wtai-disabled-click');
                        data_object.removeClass('wtai-loading-metabox');
                        data_object.removeClass('wtai-bulk-process');
                        data_object.removeClass('wtai-bulk-complete');

                        data_object.find('.wtai-checkboxes').removeClass('disabled');
                        data_object.find('.wtai-checkboxes').prop('disabled', false);

                        if( generatedText.trim() != '' ){
                            data_object.find('.wtai-generated-status-label').text(WTAI_OBJ.generatedStatusText);
                        }
                        else{
                            data_object.find('.wtai-generated-status-label').text(WTAI_OBJ.notGeneratedStatusText);
                        }

                        //disable field if no api id yet
                        if( '' == res.result[type+'_id']){
                            data_object.find('.wtai-generate-disable-overlay-wrap').addClass('wtai-shown');
                        }
                        else{
                            data_object.find('.wtai-generate-disable-overlay-wrap').removeClass('wtai-shown');
                        }                    

                        var last_activity = res.result[type+'_last_activity'];
                        data_object.find('.wtai-transferred-status-label').hide();
                        data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                        data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');
                        
                        if( generatedText.trim() != '' ){
                            if( last_activity != 'transfer' ){
                                if( last_activity != '' ){
                                    data_object.find('.wtai-transferred-status-label').show();
                                    data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                    data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                }
                                else{
                                    if( wpGeneratedText.trim() !== generatedText.trim() ){
                                        data_object.find('.wtai-transferred-status-label').show();
                                        data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                        data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                    }
                                }
                            }
                            else{
                                if( wpGeneratedText.trim() == '' ){
                                    data_object.find('.wtai-transferred-status-label').show();
                                    data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                    data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                }
                            }
                        }

                        if( id ){
                            addHighlightKeywordsbyFieldOnKeyup(id);  
                        }
                    });

                    // Category images display html
                    var category_image_html = res.result['category_image_html'];
                    $('.wtai-category-image-wrap').html( category_image_html );

                    // Reference product dropdown list
                    var reference_dropdown_html = res.result['reference_dropdown_html'];
                    $('.wtai-representative-product-input-items-wrap').html( reference_dropdown_html );

                    if( $('.wtai-representative-product-input-items-wrap .wtai-cat-product-item').length <= 0 ){
                        $('.wtai-rep-dp-no-products-found').show();
                    } else {
                        $('.wtai-rep-dp-no-products-found').hide();
                    }
                    
                    trigger_reference_scroll_event();

                    var representative_products_html = res.result['representative_products_html'];
                    $('.wtai-representative-product-items-list').html( representative_products_html );

                    if( $('.wtai-representative-product-items-list .wtai-representative-product-item').length <= 0 ){
                        $('.wtai-representative-product-empty').show();
                        $('.wtai-representative-product-counter-wrap').hide();
                    } else {
                        $('.wtai-representative-product-empty').hide();
                        $('.wtai-representative-product-counter-wrap').show();
                    }

                    maybe_disable_representative_product_input();
                    set_representative_product_count();

                    // other details
                    var other_details = res.result['other_details'];
                    if( other_details['enabled'] == '1' ){
                        $('#wtai-other-product-details').prop('checked', true);
                    }
                    else{
                        $('#wtai-other-product-details').prop('checked', false);
                    }

                    if( other_details['value'] != '' ){
                        $('#wtai-wp-field-input-otherproductdetails').val( other_details['value'] );

                        var otherProductDetailsCharLength = other_details['length'];
                        $('#wtai-wp-field-input-otherproductdetails').closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html(otherProductDetailsCharLength);
                    } else {
                        $('#wtai-wp-field-input-otherproductdetails').val( '' );
                    }

                    $('#wtai-wp-field-input-otherproductdetails').prop('disabled', false);

                    rewrite_button_state_behavior(); //set rewrite button state

                    handle_single_transfer_button_state();
                    bulk_transfer_button_behavior(); //state for transfer bulk button single

                    initializeToolTipForSingleTransferButtons();
                    updateToolTipForTransferSingleButton( 1 ); //update tooltip for single product button

                    if ( $('.wtai-loading-metabox').length > 0 ){
                    } else {
                        $('.wtai-slide-right-text-wrapper').find('.wtai-close').removeClass('wtai-pending');
                        $('.wtai-slide-right-text-wrapper').find('.wtai-history').removeClass('wtai-pending');
                        $('.wtai-slide-right-text-wrapper').find('.wtai-init-fields').each(function(){
                            $(this).removeClass('disabled');
                            $(this).prop('disabled', false );
                        });
                        
                        var prod_ids  = $('.wp-list-table').attr('data-ids');

                        if ( prod_ids ){
                            if( $.isNumeric( prod_ids ) ){
                                var reindex_prod = [];
                                reindex_prod.push(prod_ids);
                                prod_ids = reindex_prod;
                            } else {
                                var prod_ids = prod_ids.split(',');   
                            }
                            
                            if (  prod_ids.length > 1 ){
                                var index = 0;
                                $.each(prod_ids, function( prod_index, prod_value ){
                                    if ( parseInt(post_id) == parseInt(prod_value) ){
                                        index = prod_index;
                                        return false;
                                    }
                                });

                                if ( index > 0 ){
                                    $('.wtai-product-pager-wrapper .wtai-button-prev').removeClass('disabled');
                                } 
                                else{
                                    if( $('.prev-page.button').length > 0 ){
                                        fetchPrevPageIds();
                                    }
                                }
                                
                                if ( parseInt(parseInt(prod_ids.length) - 1) >  index ) {
                                    $('.wtai-product-pager-wrapper .wtai-button-next').removeClass('disabled');
                                    $('.wtai-product-pager-wrapper .button').parent().removeClass('wtai-page-processing');
                                } 
                                else{
                                    //load next items
                                    if( $('.next-page.button').length > 0 ){
                                        fetchNextPageIds();
                                    }
                                }
                            }
                            
                            $('.wtai-product-pager-wrapper .button').parent().removeClass('wtai-page-processing');
                            $('.wtai-keyword-analysis-button').removeClass('disabled');
                            $('.wtai-keywords-highlight').removeClass('disabled');
                        }
                    }      
                    
                    maybeDisableBulkButtons();

                    initializeDisabledGenerateFieldTooltip();

                    handle_generate_and_select_all_state( is_premium );
                    handle_generate_button_state();

                    handle_single_product_edit_state( is_premium );

                    // Check if we should display the free premium popup
                    if( res.result.free_premium_popup_html != '' ){
                        if( $('.wtai-freemium-popup-wrap').length ){
                            $('.wtai-freemium-popup-wrap').remove();
                        }
                        
                        $('body').append( res.result.free_premium_popup_html );
                        $('.wtai-freemium-popup-wrap').fadeIn();
                    }

                    getKeywordOverallDensity();

                    if( WTAI_OBJ.current_user_can_generate == '0' ){
                        $('.wtai-page-generate-all').addClass('disabled');
                        $('.wtai-keyword-analysis-button').addClass('disabled');
                    }

                    // Fresh Nonce verification
                    $('#wtai-edit-product-line-form').attr('data-product-nonce', res.result['product_edit_nonce']);

                    $('#postbox-container-2 .wtai-metabox').each(function(){
                        var data_object = $(this);
                        var type = data_object.data('type');

                        if( text_source == 'all-in-one-seo-pack' && type != 'category_description' ){
                            data_object.find('.wtai-checkboxes').addClass('disabled');
                            data_object.find('.wtai-checkboxes').prop('disabled', true);
                            data_object.find('.wtai-checkboxes').prop('checked', false);
                            data_object.addClass('wtai-disabled-seo-field');
                            data_object.addClass('closed');
                        }
                    });
                }
            }
        });
    }

    // productSingleDataResponse();
    function get_category_keyword_edit_data( response_data ){
        if ( $('.wtai-post-data').length > 0  ){
            var keywordCount = response_data.result.keywords.length;

            $('.wtai-keyword-max-count-wrap .wtai-keyword-count').html( keywordCount );

            $('.wtai-post-data').each(function(){
                var postfield = $(this).data('postfield');
                var elementobject = $(this);

                switch( postfield ){
                    case 'suggested_audience':
                        renderSuggestedAudience( response_data.result['suggested_audience'] );
                        break;
                    case 'keyword_country':
                        var product_countries_sorted = response_data.result['product_countries_sorted'];

                        var html = '';
                        if ( product_countries_sorted  ){
                            $.each(product_countries_sorted, 
                                function(index, label ) {
                                    var active = '';
                                    if ( label['code'] == response_data.result['country'] ){
                                        elementobject.val(label['product_country_id']);
                                    }
                                }
                            );
                        }
                        
                        break;
                    case 'language_formal_field':
                        //elementobject.html( response_data.result[postfield] );
                        break;
                    case 'locale':
                        elementobject.html( response_data.result[postfield] );
                        break;
                    case 'language':
                        elementobject.html( response_data.result[postfield] );
                        break;
                    case 'country':
                        elementobject.html( response_data.result['country'] );
                        break;
                   
                    case 'keyword_input_table':
                        var html = '';

                        if( response_data.result['keywords_input'].length ){                        
                            $.each(response_data.result['keywords_input'], 
                                function(index, value ) {
                                    if( value['search_vol'] === null ){
                                        value['search_vol'] = '-';
                                    }
                                    if( value['diffuculty'] === null ){
                                        value['diffuculty'] = '-';
                                    }

                                    html = html + '<tr>';
                                        html = html+'<td class="wtai-col-1">'+value['name']+'</td>';
                                        html = html+'<td class="wtai-col-2">'+value['search_vol']+'</td>';
                                        html = html+'<td class="wtai-col-3">'+value['diffuculty']+'</td>';
                                        html = html+'<td class="wtai-col-4"><span class="dashicons dashicons-minus keyword-action-button" type="remove" data-label="'+value['name']+'"></span></td>';
                                                
                                    html = html + '</tr>';
                                }
                            );
                        }
                        else{
                            html = '<tr class="bg-default">';
                            html += '<td class="wtai-col-1">-</td>';
                            html += '<td class="wtai-col-2">-</td>';
                            html += '<td class="wtai-col-3">-</td>';
                            html += '<td class="wtai-col-4">-</td>';
                            html += '</tr>';
                        }

                        
                        if( html ) {
                            elementobject.html(html);
                        }
                       
                        break;
                    case 'keyword_input_num':
                        var count = 0;
                        $.each(response_data.result['keywords_input'], function(index, value ) {
                            if ( value ){
                                count++;
                            }
                        });
                        
                        elementobject.html( count );
                        break;
                    case 'keyword_semantic':
                        var html = '';
                        $.each(response_data.result['keywords'], function(index, value ) {
                            html = html + '<div class="wtai-semantic-keywords-wrapper-list">';
                                html = html+'<div class="wtai-header-label">'+value['name']+'</div>';
                                html = html+'<div class="wtai-semantic-list">';
                                $.each( value['semantic'], function( index, value ) {
                                    var semantic_active = '';
                                    if ( value['active'] ){
                                        semantic_active = 'wtai-active';
                                    } else {
                                        semantic_active = '';
                                    }  
                                    
                                    var sk_tooltip_label = '';
                                    if( semantic_active != 'wtai-active' ){
                                        sk_tooltip_label = WTAI_OBJ.maxSemanticKeywordMessage;
                                    }

                                    html =  html+'<span class="wtai-semantic-keyword '+semantic_active+'" title="'+sk_tooltip_label+'" ><span class="wtai-keyword-name">'+value['name']+'</span> <span class="wtai-per">(0.00%)</span></span>';
                                });
                                html = html+'</div>';
                            html = html + '</div>';
                        });
                        elementobject.append(html);
                        break;
                    case 'keyword_num':
                    case 'keyword_input':
                        var count = 0;
                        $.each(response_data.result['keywords'], function(index, value ) {
                            if ( value ){
                                count++;
                            }
                        });
                        if (  postfield == 'keyword_input' ){
                            if ( count < WTAI_OBJ.keyword_max ) { 
                                elementobject.prop( 'disabled', false );
                            }
                        } else {
                            elementobject.html( count );
                        }
                        break;
                    case 'product_title_semantic':
                        var html = '';
                        $.each(response_data.result[postfield]['values'], function( index, value ) {
                            var activeClass = '';
                            $.each(response_data.result[postfield]['selected'], function( sindex, svalue ) {
                                if( svalue == value ){
                                    activeClass = 'wtai-active';
                                }
                            });

                            var sk_tooltip_label = '';
                            if( activeClass != 'wtai-active' ){
                                sk_tooltip_label = WTAI_OBJ.maxSemanticKeywordMessage;
                            }

                            html += '<span class="wtai-semantic-keyword '+activeClass+'" title="'+sk_tooltip_label+'" ><span class="wtai-keyword-name">'+value+'</span> <span class="wtai-per">(0.00%)</span></span>';
                        });
                        elementobject.html( html );
                        break;
                    case 'wtai_highlight':
                        if ( response_data.result[postfield] ) {
                            elementobject.prop('checked', true );
                        } else {
                            elementobject.prop('checked', false );
                        }

                        elementobject.prop('disabled', false );
                        elementobject.removeClass('disabled' );
                        break;
        
                    case 'keywords':
                        var html = '';
                        $.each(response_data.result[postfield], function(index, value ) {
                            html = html+'<span class="result"><span class="wtai-keyword-name">'+value['name']+'</span> <span class="wtai-per">(0.00%)</span></span>';
                       
                        });
                        elementobject.html( html );
                        break;
                }
            });

            //set active semantic keyword count
            setSemanticActiveCount();

            //get keyword overall density.
            getKeywordOverallDensity();
        }

        if( WTAI_OBJ.current_user_can_generate == '0' ){
            $('.wtai-page-generate-all').addClass('disabled');
            $('.wtai-keyword-analysis-button').addClass('disabled');
        }
    }

    // Event to remove highlight keywords
    $(document).on('wtai_remove_highlight_keywords', function(e){
        e.stopImmediatePropagation();

        removeHighlightkeywords();
    });

    function removeHighlightkeywords() {
        $('#postbox-container-2').find('.wtai-metabox').each(function(){
            var parentdiv = $(this);
            var id = parentdiv.find('.wtai-columns-3').find('.wp_editor_trigger').attr('id');

            var editor = tinymce.get(id);
            if (editor) {
                
                var cloneId = $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').attr('id');
                var clonedEditor = tinymce.get(cloneId);
                if( clonedEditor ){
                    clonedEditor.setContent( '' );
                }
            }
        });
    }

    function initializeToolTipForGenerateFilter(){
        $(document).trigger('wtai_initialize_generate_filter_tooltip');
    }

    function initializeToolTipForSingleTransferButtons(){
        try{ 
            $('.wtai-single-transfer-btn').each(function(){
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

    function updateHiddentext(id) {
        //var content = wp.editor.getContent(id); // Visual tab is active;
        var content = tinymce.get(id).getContent( { format: 'raw' } );

        $('#'+id).closest('.postbox').find('.wtai-hidden-text').remove();
        $('#'+id).closest('.postbox').append('<div class="wtai-hidden-text" style="display:none;"><div class="wtai-data-new-text" style="display:none;">' + content + '|</div><div class="wtai-data-orig-text" style="display:none;">' + content + '|</div></div>' );
        
    }

    function addHighlightKeywordsbyFieldOnKeyup(id){
        if( $('#'+id).closest('.postbox').find('.wtai-generate-textarea-highlight-wrap').length ){
            if( $('#'+id).closest('.postbox').hasClass('wtai-disabled-click') ){
                $('#'+id).closest('.postbox').find('.wtai-generate-textarea-highlight-wrap').html('');
                $('#'+id).closest('.postbox').find('.wtai-generate-textarea-highlight-wrap').removeClass('wtai-show-highlight');
                $('#'+id).closest('.postbox').find('.wtai-generate-textarea-highlight-wrap').removeClass('wtai-has-tinymce-scrollbar');

                return;
            }
            
            if( tinymce.get(id) == null ){
                return;
            }

            if( $('.wtai-highlight-premium-dummy-cb').attr('highlight-force-disable') == 'yes' ){
                return;
            }
            
            var editor = tinymce.get(id);
            var content = editor.getContent();
            var contentText = editor.getContent({format: 'text'});

            var keywords = [];
            if( $('#wtai-highlight').prop('checked') ){           
                keywords = $('.wtai-target-wtai-keywords-list-wrapper span.result > .wtai-keyword-name').map(function() {
                    return $(this).text().trim().toLowerCase();
                }).get();
            }

            var semantic = [];
            if( $('#wtai-highlight').prop('checked') ){
                semantic = $('.wtai-semantic-keywords-wrapper-list-wrapper span.wtai-semantic-keyword.wtai-active > .wtai-keyword-name').map(function() {
                    return $(this).text().trim().toLowerCase();
                }).get();
            }

            var pronouns = [];
            if( WTAI_OBJ.formalLanguageSupport == '1' &&  $('.wtai-highlight-incorrect-pronouns-cb').length && $('.wtai-highlight-incorrect-pronouns-cb').is(':checked') ){
                var pronounType = 'Informal';
                if( is_formal_tone_selected() ){
                    pronounType = 'Formal';
                }

                pronouns = get_pronouns_per_type( pronounType );
            }
            
            var contentLinesArray = wtaiGetWordsCaseInsensitiveArray( contentText ); // split text into lines

            var hasHighlight = false;
            if( keywords.length ){
                try{
                    var keywordRegex = new RegExp('(^|\\b)(' + keywords.join('|') + ')(\\b|$)', 'gi');

                    for (var i = 0; i < contentLinesArray.length; i++){
                        var matchedWord = contentLinesArray[i];
                        if (keywordRegex.test(matchedWord) && ! isValidKeywordMatch( matchedWord, keywords ) ){
                            
                            var regex = new RegExp(matchedWord, 'gi');
                            content = content.replace(regex, '<span class="wtai-highlight-invalid-check" >'+matchedWord+'</span>');
                        }
                    }

                    content = content.replace(keywordRegex, function(match) {
                        return '<span class="wtai-highlight">' + match + '</span>';
                    });

                    hasHighlight = true;
                }
                catch( keywordError ){

                }
            }

            //added try catch to avoid error in case of invalid regex
            if( semantic.length ){
                try{
                    var semanticRegex = new RegExp('(^|\\b)(' + semantic.join('|') + ')(\\b|$)', 'gi');

                    for (var i = 0; i < contentLinesArray.length; i++){
                        var matchedWord = contentLinesArray[i];
                        
                        if ( semanticRegex.test(matchedWord) && ! isValidKeywordMatch( matchedWord, semantic ) ){
                            
                            var regex = new RegExp('(^|\\b)(' + matchedWord + ')(\\b|$)', 'gi');
                            content = content.replace(regex, '<span class="wtai-highlight-invalid-check" >'+matchedWord+'</span>');
                        }
                    }

                    content = content.replace(semanticRegex, function(match) {
                        return '<span class="wtai-highlight2">' + match + '</span>';
                    });

                    hasHighlight = true;
                }
                catch( exc ){
                }
            }
            
            //informal and formal pronouns
            if( pronouns.length ){
                try{
                    var pronounsRegex = new RegExp('(^|\\b)(' + pronouns.join('|') + ')(\\b|$)', 'gi');

                    for (var i = 0; i < contentLinesArray.length; i++){
                        var matchedWord = contentLinesArray[i];
                        if (pronounsRegex.test(matchedWord) && ! isValidKeywordMatch( matchedWord, pronouns ) ){
                            var regex = new RegExp(matchedWord, 'gi');
                            content = content.replace(regex, '<span class="wtai-highlight-invalid-check" >'+matchedWord+'</span>');
                        }
                    }

                    content = content.replace(pronounsRegex, function(match) {
                        return '<span class="wtai-highlight3">' + match + '</span>';
                    });

                    hasHighlight = true;
                }
                catch( keywordError ){

                }
            }
                
            var cloneId = $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').attr('id');
            var clonedEditor = tinymce.get(cloneId);
            if( clonedEditor ){
                if( hasHighlight ){
                    clonedEditor.setContent( content );
                }
                else{
                    clonedEditor.setContent( '' );
                }
            }
        }
        else{
            var cloneId = $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').attr('id');
            var clonedEditor = tinymce.get(cloneId);
            if( clonedEditor ){
                clonedEditor.setContent( '' );
            }
        }
    }

    function typeCountMessage(type, text ){
        if( type == 'alt_image' ){
            return;
        }

        var words_count = wtaiGetWordsArray( text );

        var textLength = 0;
        if( words_count.length > 0 ){
            textLength = text.length;
        }

        switch( type ){
            case 'category_description':
            case 'product_description':
            case 'product_excerpt':
                break;
            default:
                if ( textLength > WTAI_OBJ.text_limit[type] )  {
                    $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').addClass('over_limit');
                } else {
                    $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').removeClass('over_limit');
                }

                $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.wtai-char-count').html( WTAI_OBJ.char.replace('%char%',  textLength+'/'+WTAI_OBJ.text_limit[type] ) );
                $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.wtai-char-count').attr( 'data-count', textLength ); 
                break;
        }

        $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.word-count').html(WTAI_OBJ.words.replace('%words%', words_count.length ) );
        $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper').find('.word-count').attr( 'data-count', words_count.length );        
    }

    function handle_single_transfer_button_state() {
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var id = $(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id');
            var editor = tinymce.get(id);
            var content = '';
            try{
                content = editor.getContent();
            }
            catch( err ){
            }

            var do_single_checking = true;
            if( $(this).find('.wtai-single-transfer-btn').hasClass('wtai-has-data-to-transfer') ){
                do_single_checking = false;
            }

            if( do_single_checking ){
                if ( content.trim() != '' ) {
                    var source_newvalue = $(this).find('.wtai-data-new-text').text();
                    var source_origvalue = $(this).find('.wtai-data-orig-text').text();
                    var current_value = $(this).find('.wtai-current-value p.wtai-text-message').text() + '|';
                    var current_value_raw = $(this).find('.wtai-current-value p.wtai-text-message').html().trim();

                    var raw_not_match = false;
                    if( wtaiAreHtmlStringsEqual( content, current_value_raw ) == false ){
                        raw_not_match = true;
                    }

                    //wtaiContainsHtmlUsingDOMParser( content ) && 
                    if( id == 'wtai-wp-field-input-category_description' ){
                        //console.log('is equal ' + id + " ?? " + wtaiAreHtmlStringsEqual( content, current_value_raw, true ));
                    }

                    if( wtaiAreHtmlStringsEqual( content, current_value_raw ) === true ){
                        $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    } else {
                        $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                    }

                    /*console.log(id + ' raw_not_match ' , raw_not_match);
                    console.log('current_value ' , current_value);
                    console.log('source_newvalue ' , source_newvalue);
                    console.log('source_origvalue ' , source_origvalue);
                    if ( ( source_origvalue === source_newvalue && raw_not_match === false ) || 
                        ( current_value != '' && source_newvalue === current_value ) ) {
                        $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    }
                    else{
                        //maybe enable the single transfer button
                        $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                    }*/
                }
                else{
                    $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                }
            }
            else{
                var source_newvalue = $(this).find('.wtai-data-new-text').text();
                var source_newvalue_stripped = wtaiRemoveLastPipe( $(this).find('.wtai-data-new-text').text() );
                var source_origvalue = $(this).find('.wtai-data-orig-text').text();
                var current_value = $(this).find('.wtai-current-value p.wtai-text-message').text() + '|';

                if( source_newvalue_stripped == '' ){
                    $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                }
                else{
                    if ( current_value != '' && source_newvalue === current_value ) {
                        $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    }
                    else{
                        //maybe enable the single transfer button
                        $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                    }
                }
            }

            updateToolTipForTransferSingleButton( 1 );
        });

        //Handle single transfer button for alt text
        $('#postbox-container-2').find('.wtai-image-alt-metabox').each(function() {
            var id = $(this).find('.wtai-generate-value-wrapper').find('.wtai-wp-editor-setup-alt').attr('id');
            var content = $(this).find('.wtai-generate-value-wrapper').find('.wtai-wp-editor-setup-alt').val();

            var do_single_checking = true;
            if( $(this).find('.wtai-single-transfer-btn').hasClass('wtai-has-data-to-transfer') ){
                do_single_checking = false;
            }

            if( do_single_checking ){
                
                if ( content.trim() != '' ) {
                        var source_newvalue = $(this).find('.wtai-data-new-text').html();
                        var source_origvalue = $(this).find('.wtai-data-orig-text').html();
                        var current_value = $(this).find('.wtai-current-value p').text() + '|';

                        if ( source_origvalue === source_newvalue && current_value != '' ) {
                            if( current_value === source_newvalue ){
                                $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                            }
                            else{
                                $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                            }
                        }
                        else{
                            //maybe enable the single transfer button
                            $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                        }
                }
                else{
                    $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                }
            }

            updateToolTipForTransferSingleButton( 1 );
        });
    }

    function updateToolTipForTransferSingleButton( isEnabled = 1 ){
        try{
            $('.wtai-single-transfer-btn').each(function(){
                var tooltipMessage = WTAI_OBJ.tooltipActiveTransferSingle;
                if( $(this).hasClass('wtai-disabled-button') ){
                    tooltipMessage = WTAI_OBJ.tooltipInactiveTransferSingle;
                }
    
                $(this).tooltipster('content', tooltipMessage);
    
                if( isEnabled == 1 ){
                    $(this).tooltipster('enable');
                }
                else{
                    $(this).tooltipster('disable');
                }
            });
        }
        catch( e ){
        }
    }   

    function bulk_transfer_button_behavior() {
        // Check if any editor is empty and update numEmpty
        var hasDataToTransfer = false;
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var id = $(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id');
            var cbChecked = $(this).find('.wtai-checkboxes').is(':checked');
            var editor = tinymce.get(id);
            var content = '';
            try{
                content = editor.getContent();
            }
            catch( err ){
            }
           
            if ( content.trim() != '' && cbChecked && $(this).find('.wtai-single-transfer-btn').hasClass('wtai-disabled-button') == false ) {
                hasDataToTransfer = true;
            }
            else if( content.trim() != '' && cbChecked && $(this).find('.wtai-single-transfer-btn').hasClass('wtai-has-data-to-transfer') ){
                hasDataToTransfer = true;
            }
        });

        if( hasDataToTransfer ){
            if( $('.wtai-metabox .postbox-header .wtai-checkboxes:checked').length > 0 || 
                $('.wtai-image-alt-metabox .wtai-checkboxes-alt').length > 0 ) {
                $('#publishing-action .wtai-bulk-button-text').removeClass('disabled');
            } else {
                $('#publishing-action .wtai-bulk-button-text').addClass('disabled');
            }
        }
        else{
            $('#publishing-action .wtai-bulk-button-text').addClass('disabled');
        }
    }

    function maybeDisableBulkButtons(){
        var hasSelected = false;
        $('.postbox-container .wtai-checkboxes').each(function(){
            if( $(this).is(':checked') ){
                hasSelected = true;
            }
        });

        if( $('.wtai-image-alt-metabox .wtai-checkboxes-alt:checked').length ){
            hasSelected = true;
        }

        if( hasSelected == false ){
            $('.wtai-page-generate-all').removeClass('wtai-generating');
            $('.wtai-page-generate-all').addClass('disabled');

            $('.wtai-generate-wrapper .toggle').addClass('disabled');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

            //remove bulk related classes
            $('.wtai-metabox').removeClass('wtai-bulk-complete');
            $('.wtai-metabox').removeClass('wtai-bulk-writing');
            $('.wtai-metabox').removeClass('wtai-bulk-process');

            if( $('.wtai-metabox .postbox-header .wtai-checkboxes:checked').length >= 5 ){
                $('.wtai-checkboxes-all').prop('checked', true);
            }
        }
    }

    initializeDisabledGenerateFieldTooltip();

    function initializeDisabledGenerateFieldTooltip(){
        $('.wtai-generate-disable-overlay-wrap').each(function(){
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
        });
    }    

    function handle_generate_and_select_all_state( is_premium ){
        var totalCBTypes = $('#postbox-container-2 .wtai-metabox .postbox-header .wtai-checkboxes').length;
        
        if( $('#postbox-container-2 .postbox-header .wtai-checkboxes:checked').length > 0 ){
            $('.wtai-page-generate-all').removeClass('disabled');
            $('.wtai-generate-wrapper .toggle').removeClass('disabled');
            if( $('#postbox-container-2 .postbox-header .wtai-checkboxes:checked').length >= totalCBTypes ){
                $('.wtai-checkboxes-all').prop('checked', true);
            }
            else{
                $('.wtai-checkboxes-all').prop('checked', false);
            }
        }    
    }

    function handle_generate_button_state(){
        var checkedFields = $('#postbox-container-2 .wtai-metabox .postbox-header .wtai-checkboxes:checked').length;
        var checkedAltImages = 0; // No alt text image for the category.

        var totalChecked = parseInt( checkedFields ) + parseInt( checkedAltImages );

        if( totalChecked > 0 ){
            $('.wtai-page-generate-all').removeClass('disabled');
            $('.wtai-page-generate-all').removeClass('wtai-generating');
        }
        else{
            $('.wtai-page-generate-all').addClass('disabled');
            $('.wtai-page-generate-all').removeClass('wtai-generating');
        }
    }

    // Event after reset global settings
    $(document).on('wtai_single_edit_premium_state', function(e, args){
        e.stopImmediatePropagation();

        var is_premium = args.is_premium;

        handle_single_product_edit_state( is_premium );
    });

    function handle_single_product_edit_state( is_premium ){
        WTAI_OBJ.is_premium = is_premium;

        // Bypass premium features
        if( WTAI_OBJ.current_user_can_generate == '0' ){
            $('.wtai-disable-premium-feature').removeClass('wtai-disable-premium-feature');
            return;
        }

        if( is_premium == '1' ){
            //general premium bandges
            $('.wtai-premium-wrap').addClass('wtai-hide-premium-feature');

            //ads
            $('.wtai-ads-placeholder-wrap').addClass('wtai-hide-premium-feature');

            //keyword analysis
            $('.wtai-keyword-analysis-options-wrap').removeClass('wtai-disable-premium-feature');

            //highlight cb
            $('.wtai-highlight-premium-dummy-cb-wrap').hide();
            $('.wtai-highlight-cb-wrap').show();
            $('.wtai-highlight-premium-dummy-cb').attr('highlight-force-disable', 'no');

            //reference product
            $('.wtai-reference-product-label-wrapper').removeClass('wtai-disable-premium-feature');
            $('.wtai-reference-product-wtai-select-wrapper').removeClass('wtai-disable-premium-feature');
            
            var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();
            if( generationType != 'rewrite' ){
                $('.wtai-custom-style-ref-prod').prop('disabled', false);
            }

            //rewrite
            $('.wtai-cta-radio-label-rewrite').removeClass('wtai-disable-premium-feature');
            $('.wtai-cta-radio-label-rewrite').removeClass('wtai-disable-premium-feature-beige');
            $('#wtai-cta-generate-type-rewrite').prop('disabled', false);

            //other product details
            $('.wtai-other-product-details-main-wrap').removeClass('wtai-disable-premium-feature');
            $('#wtai-woocommerce-product-attributes #wtai-other-product-details').prop('disabled', false);

            //custom tone and styles
            $('.wtai-filter-main-wrap .wtai-custom-tone-cb').prop('disabled', false);
            $('.wtai-filter-main-wrap .wtai-custom-style-cb').prop('disabled', false);
            $('.wtai-filter-main-wrap .wtai-custom-tone-text').prop('disabled', false);
            $('.wtai-filter-main-wrap .wtai-custom-style-text').prop('disabled', false);

            //suggested audience
            $('.wtai-filter-main-wrap .wtai-input-text-suggested-audiance').prop('disabled', false);
            $('.wtai-filter-main-wrap .wtai-regenerate-audience').removeClass('disabled');
            $('.wtai-suggested-audience-list-wrap').removeClass('wtai-disable-premium-feature');

            //bulk action
            $('.wtai-bulk-action-option-wrap').removeClass('wtai-disable-premium-feature');

            //body global flex class
            $('body').removeClass('wtai-premium-badge-displayed');
        }
        else{
            //general premium bandges
            $('.wtai-premium-wrap').removeClass('wtai-hide-premium-feature');

            //ads
            $('.wtai-ads-placeholder-wrap').removeClass('wtai-hide-premium-feature');

            //keyword analysis
            $('.wtai-keyword-analysis-options-wrap').addClass('wtai-disable-premium-feature');

            //highlight cb
            $('.wtai-highlight-premium-dummy-cb-wrap').show();
            $('.wtai-highlight-cb-wrap').hide();
            $('.wtai-highlight-premium-dummy-cb').attr('highlight-force-disable', 'yes');

            //reference product
            $('.wtai-reference-product-label-wrapper').addClass('wtai-disable-premium-feature');
            $('.wtai-reference-product-wtai-select-wrapper').addClass('wtai-disable-premium-feature');
            $('.wtai-custom-style-ref-prod').prop('disabled', true);
            $('.wtai-custom-style-ref-prod').prop('checked', false).trigger('change');

            //rewrite
            $('.wtai-cta-radio-label-rewrite').addClass('wtai-disable-premium-feature');
            $('.wtai-cta-radio-label-rewrite').addClass('wtai-disable-premium-feature-beige');
            $('#wtai-cta-generate-type-rewrite').prop('disabled', true);
            $('#wtai-cta-generate-type-rewrite').prop('checked', false);
            $('#wtai-cta-generate-type-generate').prop('checked', true);

            //other product details
            $('.wtai-other-product-details-main-wrap').addClass('wtai-disable-premium-feature');
            //$('#wtai-wp-field-input-otherproductdetails').val('');

            if( $('#wtai-woocommerce-product-attributes #wtai-other-product-details').is(':checked') ){
                $('#wtai-woocommerce-product-attributes #wtai-other-product-details').prop('checked', false).trigger('change');
            }

            $('#wtai-woocommerce-product-attributes #wtai-other-product-details').prop('disabled', true);

            //custom tone and styles
            if( $('.wtai-filter-main-wrap .wtai-custom-tone-cb').is(':checked') ){
                $('.wtai-filter-main-wrap .wtai-custom-tone-cb').prop('checked', false);
            }
            if( $('.wtai-filter-main-wrap .wtai-custom-style-cb').is(':checked') ){
                $('.wtai-filter-main-wrap .wtai-custom-style-cb').prop('checked', false);
            }

            setTimeout(function() {
                get_tone_style_count();
            }, 300);

            $('.wtai-filter-main-wrap .wtai-custom-tone-cb').prop('disabled', true);
            $('.wtai-filter-main-wrap .wtai-custom-style-cb').prop('disabled', true);
            $('.wtai-filter-main-wrap .wtai-custom-tone-text').prop('disabled', true);
            $('.wtai-filter-main-wrap .wtai-custom-style-text').prop('disabled', true);

            $('.wtai-filter-main-wrap .wtai-input-text-suggested-audiance').prop('disabled', true);
            $('.wtai-filter-main-wrap .wtai-regenerate-audience').addClass('disabled');
            $('.wtai-suggested-audience-list-wrap').addClass('wtai-disable-premium-feature');

            //bulk action
            $('.wtai-bulk-action-option-wrap').addClass('wtai-disable-premium-feature');

            //body global flex class
            $('body').addClass('wtai-premium-badge-displayed');

            //grid transfer
            if( $('.wp-list-table .wtai-cwe-action-button.transfer').length > 0 ){
                $('.wp-list-table .wtai-cwe-action-button.transfer').addClass('wtai-hidden-transfer-link');
            }
        }
    }

    function getKeywordOverallDensity(){
        $(document).trigger('wtai_get_keyword_overall_density');
    }    

    var numAjaxRequests = 0;
    // show loader when an AJAX request is made
    $(document).on('ajaxSend', function() {
        numAjaxRequests++;
    });

    // hide loader when all AJAX requests are completed
    $(document).on('ajaxComplete', function( event, xhr, settings ) {
        numAjaxRequests--;
        if (numAjaxRequests == 0) {
            
            setTimeout(function() {
               addHighlightKeywords();
            }, 300);
            

            $('.wtai-target-wtai-keywords-list-wrapper').removeClass('disabled');

            if( $('.wtai-keyword-analysis-button').hasClass('disabled') && WTAI_OBJ.current_user_can_generate != '0' ){
                $('.wtai-keyword-analysis-button').removeClass('disabled');
            }

            $('.wtai-keyword-filter-wrapper').find('.button-primary').removeClass('disabled');
            
            var settingsdata = settings.data;
            var closeLoader = true;
            if(/wtai_generate_queue_progress/i.test(settingsdata)){
                closeLoader = false;

                //console.log('generate queue progress is called ');
            }

            if( $('#wtai-product-generate-completed').is(':visible') ){
                closeLoader = true;

                //console.log('single generate done is visible');
            }

            if( window.wtaStreamQueueProcessing == true){
                closeLoader = false;

                //console.log('stream queue processing is true ');
            }

            if(/wtai_preprocess_images/i.test(settingsdata)){
                closeLoader = false;

                //console.log('bypass close loader when processing image ');
            }

            if(/wtai_start_ai_keyword_analysis/i.test(settingsdata)){
                closeLoader = false;

                //console.log('generate queue keyword analysis is called ');
            }

            if( window.keywordIdeasStartAnalysis == true){
                closeLoader = false;

                //console.log('stream keyword analysis is true ');
            }
            
            if( closeLoader ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');

                $('.wtai-slide-right-text-wrapper .wtai-close').removeClass('disabled');
                $('.wtai-slide-right-text-wrapper .wtai-button-prev').removeClass('disabled-nav');
                $('.wtai-slide-right-text-wrapper .wtai-button-next').removeClass('disabled-nav');
            }
        }

        if( keywordIdeasAJAX == null && addKeyWordAJAX == null ){
            if( $('.wtai-keyword-analysis-button').hasClass('disabled') && WTAI_OBJ.current_user_can_generate != '0' ){
                $('.wtai-keyword-analysis-button').removeClass('disabled');
            }
        }
    });

    $(document).on('wtai_single_edit_density_premium_state', function(e, args){
        e.stopImmediatePropagation();

        var is_premium = args.is_premium;

        handle_density_premium_state( is_premium );
    });

    function handle_density_premium_state(is_premium ){
        // Bypass premium features
        if( WTAI_OBJ.current_user_can_generate == '0' ){
            return;
        }

        if( is_premium == '1' ){
            //handle density
            $('.wtai-keyword-analysis-options-wrap .wtai-semantic-keyword.wtai-active .wtai-per').removeClass('wtai-per-force-hide');
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result .wtai-per').removeClass('wtai-per-force-hide');
        }
        else{
            //handle density
            $('.wtai-keyword-analysis-options-wrap .wtai-semantic-keyword.wtai-active .wtai-per').addClass('wtai-per-force-hide');
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result .wtai-per').addClass('wtai-per-force-hide');
        }
    }

    function addHighlightKeywords(){
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var editor = tinymce.get($(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id'));
            if( editor ){
                addHighlightKeywordsbyFieldOnKeyup(editor.id);
            }
        });
    }

    //close single slider
    $(document).on('click', '.wtai-slide-right-text-wrapper .wtai-close', function(){
      
        popupGenerateCompleted('hide'); // todo!

        if( $(this).hasClass('disabled') ){
            return;
        }
        
        //check if history is open
        if( $('body').hasClass('wtai-history-open') ){
            $('.wtai-btn-close-history').trigger('click');
            return;
        }

        //check if keyword is open
        if( $('body').hasClass('wtai-keyword-open') ){
            $('.wtai-btn-close-keyword').trigger('click');
            return;
        }

        handle_single_bulk_buttons( 'enable' );

        $('#wtai-preprocess-image-loader').hide();
        //added 2024.03.05
        $('#wpcontent').removeClass('preprocess-image');

        if ( ! $('.wtai-slide-right-text-wrapper').find('.wtai-close').hasClass('wtai-pending') ){
            var number_of_changes_unsave = checkChanges();
            if ( number_of_changes_unsave > 0 ) {
                popupUnsaved('close');
                return false;
            }

            close_category_edit_form();

            if( $('.wtai-global-loader').hasClass('wtai-is-active') ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-keyword .wtai-keyword-input').removeClass('border');
            }
        }
        else{
            close_category_edit_form();
        }
    });

    function reset_edit_form(){
        if( queueGenerateTimer ){
            clearTimeout(queueGenerateTimer);
        }

        window.wtaStreamData = [];
        window.wtaStreamQueueData = [];
        window.wtaStreamQueueProcessing = false;

        popupGenerateCompleted('hide');

        if( !$('#wtai-keywords-list').hasClass('closed') ) {
            $('#wtai-keywords-list').addClass('closed');
        }

        $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

        $('#normal-sortables').find('.wtai-metabox').addClass('wtai-loading-metabox');
        $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-loading-metabox');
        $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-disabled-click');
        $('.wtai-slide-right-text-wrapper').find('.wtai-close').addClass('wtai-pending');
        $('.wtai-slide-right-text-wrapper').find('.wtai-history').addClass('wtai-pending');

        var html = '';
        for (var i = 0; i < WTAI_OBJ.option_choices; i++) {
            html = html+'<div class="selection dontselect"><span class="text-wrapper"><p></p></span></div>';
        }
        $('.wtai-metabox').find('.wtai-columns-1').addClass(html);
                        
        bulk_transfer_button_behavior();
        reset_elements_prev_next();

        $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html('');

        $( '.wtai-slide-right-text-wrapper' ).scrollTop( 0 );
    }

    // producteditformClose();
    function close_category_edit_form(){
        reset_edit_form();

        lastGenerationTypeSelected = null;

        $('.wtai-hide-step-cb-wrap').hide();
        $('.wtai-hide-step-separator').hide();

        $('.wtai-restore-global-settings-wrap').hide();
        $('.wtai-restore-global-settings-separator').hide();

        $('#wtaCurrentPageNumber').val( $('#wtaActualCurrentPageNumber').val() );
        $('#wtaNextCategoryId').val( 0 );
        $('#wtaPrevCategoryId').val( 0 );

        var tooltipElements = $('.tooltip_hover.tooltipstered');
        if( $('#wtai-comparison-cb').is(':checked') ) {
            tooltipElements.each(function() {
                $(this).tooltipster('enable');
            });
        } else {
            tooltipElements.each(function() {
                $(this).tooltipster('disable');
            });
        }
        
        var width = $(window).width();        
        width = width - $('#adminmenuback').width();        
        $('#wpwrap').find('.wtai-wp-editor-setup').each(function(){
            var id = $(this).attr('id');            
            
            var editor = tinymce.get(id);    
            if( editor ){
                editor.setContent( '' );
            }  

            $(this).closest('.wtai-metabox').addClass('wtai-loading-metabox');

            wp.editor.remove(id);
        }); 

        $('#wpwrap').find('.wtai-wp-editor-setup-cloned').each(function(){
            var id = $(this).attr('id');     
            var clonedEditor = tinymce.get(id);    
            if( clonedEditor ){
                clonedEditor.setContent( '' );
            }   

            wp.editor.remove(id);
        }); 

        $('#wpwrap').find('.wtai-wp-editor-setup-others').each(function(){
            var id = $(this).attr('id');            
            
            var editor = tinymce.get(id);    
            if( editor ){
                editor.setContent( '' );
            }  

            $(this).closest('.wtai-metabox').addClass('wtai-loading-metabox');

            wp.editor.remove(id);
        }); 

        $('#wpwrap').find('.wtai-wp-editor-setup-others-cloned').each(function(){
            var id = $(this).attr('id');          
            var clonedEditor = tinymce.get(id);    
            if( clonedEditor ){
                clonedEditor.setContent( '' );
            }

            wp.editor.remove(id);
        }); 
       
        $('body').removeClass('wtai-history-open');
        $('body').removeClass('wtai-open-single-slider');
        
        $('.wtai-global-loader').removeClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        
        $('.wtai-page-generate-all').attr('data-rewrite', '0');
        $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

        $('#wpwrap').removeClass('wtai-loader');
        $('#wpwrap').addClass('close');

        $('.wtai-slide-right-text-wrapper').remove();

        $('#wpwrap')
        .find('#wpbody-content')
        .append(productEditFormHtml);

        load_generate_filter_tooltip();
        initializeToolTipForGenerateFilter();

        //disable save button
        $('#save-action .wtai-bulk-button-text').addClass('disabled');

        //rest keyword analysis view counter to 0
        updateKeywordAnaysisViewCount( true );

        load_tiny_mce();

        if ( $('.wtai-history').find('.wtai-calendar-field').find('.wtai-history-date-input').length > 0 ){
            $('.wtai-history').find('.wtai-calendar-field').find('.wtai-history-date-input').datepicker(date_picker_params);
        }

        popupGenerateCompleted('hide');

        $('.wtai-cwe-action-button.wtai-cwe-action-button-edit').removeClass('disabled_on_edit_button');
        $('.wtai-cwe-action-title').removeClass('disabled_on_edit_button');

        $('.wtai-job-list-wrapper').removeClass('hidden');
        $('.wtai-single-loading-header-details').addClass('hidden');
        $('.wtai-ai-logo').addClass('wtai-hide');

        setTimeout(function() {
            //$('.wtai-slide-right-text-wrapper').remove();
            $('#wpwrap').removeClass('close');
            $('#wpwrap').removeClass('wtai-overlay');
        }, 500);

        //reset list nextpage number
        $('#wtai-prev-current-page-number').val( $('#current-page-selector').val() );
        $('#wtai-next-page-number').val( $('#current-page-selector').val() );     
    }

    function popupUnsaved( type ){
        $('#wtai-product-edit-cancel').find('.wtai-exit-edit-leave').attr('data-type', type);
        if ( ! $('#wtai-product-edit-cancel').is(':visible') ) {
            $('#wpbody-content').addClass('wtai-overlay-div-2');
            $('#wtai-product-edit-cancel').show();
        }
    }

    function handle_single_bulk_buttons( state ){
        if( state == 'disable' ){
            $('.submitbox .wtai-bulk-button-text').addClass('disabled-during-generation');
        }
        else{
            $('.submitbox .wtai-bulk-button-text').removeClass('disabled-during-generation');
        }
    }   

    function updateKeywordAnaysisViewCount( reset = false ){
        var args = {
            reset : reset,
        }

        $(document).trigger('wtai_update_keyword_analysis_views_count', args);
    }

    function reset_elements_prev_next() {
        $('#wtai-restore-global-setting-completed').hide();

        $('.wtai-review-check').prop('checked', false);
        $('.target-keywords-header-left-wrapper .wtai-keyword-count').text('0');

        $('.wtai-semantic-keywords-wrapper-list .wtai-header-label.wtai-post-data-json').html('');

        $('.wtai-semantic-keywords-wrapper-list .wtai-product-title-semantic-list').html('');
        $('.wtai-data-semantic-keywords-wrapper-list-wrapper.wtai-post-data .wtai-semantic-keywords-wrapper-list').html('');
        $('.wtai-data-semantic-keywords-wrapper-list-wrapper.wtai-post-data .wtai-semantic-keywords-wrapper-list').html('');

        $('.wtai-col-left-wrapper  .wtai-target-wtai-keywords-list-wrapper').html('');
        $('#misc-publishing-actions .curtime.misc-pub-curtime #timestamp b').html('');
        $('#misc-publishing-actions .misc-pub-post-status #post-status-display').html('');
        $('#misc-publishing-actions .misc-pub-visibility #post-visibility-display').html('');
        $('.wtai-header-title .wtai-product-sku').html('');
        $('.wtai-header-title .wtai-product-short-title').val('');
        $('.wtai-custom-style-ref-prod').prop('checked', false);
        $('.wtai-tone-and-styles-select .wtai-button-label').removeClass('disabled');
        $('.wtai-custom-style-ref-prod-sel').addClass('disabled');
        
        $('.wtai-char-count').each(function(){
            $(this).html(WTAI_OBJ.char.replace('%char%', 0));
        });
        $('.word-count').each(function(){
            $(this).html(WTAI_OBJ.words.replace('%words%', 0) );
        });
        $('.wtai-text-count-details').each(function(){
            $(this).removeClass('over_limit');
        });

        $('#wtai-other-product-details').prop('checked', false);
        $('#wtai-wp-field-input-otherproductdetails').val( '' );

        removeHighlightkeywords();
        
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var editor = tinymce.get($(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id'));
            editor.setContent('');

            var current_value = $(this).find('.wtai-text-message');
            current_value.text('');
        });
    }

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

        $('.wtai-generating-cta-overlay').each(function(){
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

    $('.wtai-hide-step-cb-wrap #wtai-hide-step-cb').on('click', function(){
        var is_checked = 0;

        if( $(this).is(':checked') ){
            is_checked = 1;

            $('.wtai-step-guideline').addClass('wtai-hide');
        }
        else{
            $('.wtai-step-guideline').removeClass('wtai-hide');
        }

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_hide_category_guidelines',
                value: is_checked,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    });

    $(document).on('click', '.wtai-tone-and-styles-select .wtai-button-label', function(){
        if( $(this).hasClass('disabled') || $(this).hasClass('disabled-select') ){
            return;
        }

        $('.wtai-wp-filter .wtai-postbox-process .wtai-generate-wrapper.wtai-postbox-process-wrapper').removeClass('open');
        var button_select = $(this);
        var type_button = button_select.data('type');
        button_select.closest('.wtai-tone-and-styles-select').toggleClass('wtai-active');

        if( type_button == 'tone_and_style' ) {
            if( $('.wtai-tone-and-styles-select.wtai-audiences-form-select').hasClass('wtai-active') ) {
                $('.wtai-tone-and-styles-select.wtai-audiences-form-select').removeClass('wtai-active');
            }
        } else if ( type_button == 'audiences' ) {
            if( $('.wtai-tone-and-styles-select.wtai-tone-and-style-form-select').hasClass('wtai-active') ) {
                $('.wtai-tone-and-styles-select.wtai-tone-and-style-form-select').removeClass('wtai-active');
            }     
        }
    });

    $(document).on('click', '.wtai-alt-image', function(){
        if( $(this).closest('.wtai-cat-product-items-wrap').length > 0 ){
            return;
        }

        var imgSource = $(this).attr('data-popimage');
        $('.wtai-image-popup').find('img').attr('src',imgSource);
        $('.wtai-image-popup').toggleClass('show');
    });
    
    $(document).on('click', '.wtai-btn-close-popup', function(e) {
        $('.wtai-image-popup').find('img').attr('src','');
        $('.wtai-image-popup').removeClass('show');
        e.preventDefault();
    });
    
    $(document).on('click', '.wtai-image-popup', function(e) {
        if (e.target !== this)
            return;

        // Your code to close the popup goes here
        $('.wtai-image-popup').find('img').attr('src','');
        $(this).removeClass('show');
    });

    $(document).on( 'click', '.wtai-edit-product-line .wtai-select-all-checkbox-expand', function(e) {        
        if( $(this).hasClass('wtai-closed') ){
            $('#postbox-container-2').find('.wtai-metabox').removeClass('closed');
            $('#postbox-container-2').find('.wtai-alt-writetext-metabox').removeClass('closed');
            $(this).removeClass('wtai-closed');
            $('#wtai-keywords-list').removeClass('closed');
        }
        else{
            $('#postbox-container-2').find('.wtai-metabox').addClass('closed');
            $('#postbox-container-2').find('.wtai-alt-writetext-metabox').addClass('closed');
            $(this).addClass('wtai-closed');
            $('#wtai-keywords-list').addClass('closed');
        }

        e.preventDefault();
    });

    /*Post header*/
    $(document).on('click', '.wtai-edit-product-line .postbox-header .hndle', function(e){
        if ( $(e.target).closest('.wtai-premium-wrap').length || $(e.target).hasClass('wtai-premium-wrap') ) {
            return;
        }

        $(this).parent().find('.toggle-indicator').trigger('click');
    });

    $(document).on('click', '.toggle-indicator', function(e){
        e.stopImmediatePropagation();
        e.preventDefault();

        var parent = $(this).closest('.postbox');
        parent.toggleClass('closed');
    });

    $(document).on('focus', '.wtai-representative-product-input', function(e){
        var parent = $(this).closest('.wtai-representative-product-input-wrap');
        parent.addClass('wtai-active');
    });

    // Hide the div when clicking outside of it
    $(document).click(function(event) {
        var $target = $(event.target);
        if (!$target.closest('.wtai-representative-product-input-wrap').length) {
            $('.wtai-representative-product-input-wrap').removeClass('wtai-active');
        }
    });

    $(document).on('click', '.wtai-cat-product-items-wrap .wtai-cat-product-item', function(e){
        var parent = $(this).closest('.wtai-cat-product-items-wrap');
        var category_id = parent.attr('data-category-id');
        var current_page = parent.attr('data-current-page');
        var total_pages = parent.attr('data-total-pages');
        var dropdown_item = $(this);

        var product_id = $(this).attr('data-product-id');

        var product_length = $('.wtai-representative-product-items-list .wtai-representative-product-item').length;

        if( product_length >= WTAI_OBJ.max_representative_products ){
            $('.wtai-representative-product-input').prop('disabled', true);
            return;
        }

        var content = $(this).html();

        var has_featured_image_class = '';
        if( $(this).hasClass('wtai-has-featured-image') ){
            has_featured_image_class = 'wtai-has-featured-image';
        }

        var html = '<div class="wtai-representative-product-item wtai-representative-product-item-' + product_id + ' ' + has_featured_image_class + '" data-product-id="'+product_id+'" >';
        html += content;
        html += '<div class="wtai-remove-rep-prod" ><input class="wtai-remove-rep-prod-btn" type="button" value="&times;" /></div>';
        html += '</div>';

        $('.wtai-representative-product-items-list').append(html);
        $('.wtai-representative-product-empty').hide();

        dropdown_item.addClass('wtai-hidden');

        $('.wtai-representative-product-input-wrap').removeClass('wtai-active');

        $('.wtai-representative-product-input').val('').trigger('input');

        process_representative_product( product_id, 'add' );
    });

    $(document).on('click', '.wtai-remove-rep-prod-btn', function(e){       
        var parent_r = $(this).closest( '.wtai-representative-product-item');
        var product_id = parent_r.attr('data-product-id');

        process_representative_product( product_id, 'remove' );
    });
    
    function process_representative_product( product_id, action = 'add' ){
        var cat_product_items = $('.wtai-cat-product-items-wrap');
        var current_page = cat_product_items.attr('data-current-page');
        var total_pages = cat_product_items.attr('data-total-pages');

        var category_id = $('#wtai-edit-post-id').attr('value');

        if( action == 'remove' ){
            var parent_r = $( '.wtai-representative-product-item-' + product_id );
    
            parent_r.remove();
    
            $('.wtai-cat-product-items-wrap .wtai-cat-product-item-'+product_id).removeClass('wtai-hidden');
        }

        var product_ids = [];
        $('.wtai-representative-product-items-list .wtai-representative-product-item').each(function(){
            var product_id = $(this).attr('data-product-id');

            product_ids.push(product_id);
        });

        $('.wtai-representative-product-items-list').addClass('wtai-loading-state');
        $('.wtai-representative-product-input').prop('disabled', true);

        var wtai_nonce = get_wp_nonce();
        var data = {
            action: 'wtai_process_representative_product',
            category_id: category_id, 
            product_ids: product_ids.join(','), 
            wtai_nonce: wtai_nonce,
            current_page: current_page,
            total_pages: total_pages,
        };

        representativeProductAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
            },
            success: function( data ){ 
                $('.wtai-representative-product-items-list').removeClass('wtai-loading-state');

                var product_length = $('.wtai-representative-product-items-list .wtai-representative-product-item').length;

                if( product_length >= WTAI_OBJ.max_representative_products ){
                } else {
                    if( $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').length ){
                        $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').remove();
                    }

                    $('.wtai-representative-product-input-items-wrap').html( data.reference_dropdown_html );
                }

                if( product_length <= 0 ){
                    $('.wtai-representative-product-empty').show();
                    $('.wtai-representative-product-counter-wrap').hide();
                } else {
                    $('.wtai-representative-product-empty').hide();
                    $('.wtai-representative-product-counter-wrap').show();
                }

                maybe_disable_representative_product_input();
                set_representative_product_count();
            }
        });
    }

    function maybe_disable_representative_product_input(){
        var product_length = $('.wtai-representative-product-items-list .wtai-representative-product-item').length;

        if( product_length >= WTAI_OBJ.max_representative_products ){
            $('.wtai-representative-product-input').prop('disabled', true);
            show_rep_product_tooltip( 'show' );
        } else {
            $('.wtai-representative-product-input').prop('disabled', false);
            show_rep_product_tooltip( 'hide' );
        }        
    }

    let debounceTimer;

    $(document).on('input', '.wtai-representative-product-input', function() {
        var representative_products = [];
        $('.wtai-representative-product-items-list .wtai-representative-product-item').each(function(){
            representative_products.push( $(this).attr('data-product-id') );
        });

        var inputValue = $(this).val().toLowerCase();
        var productFound = false;
        $('.wtai-representative-product-input-items-wrap .wtai-cat-product-item').each(function() {
            var prod_id = $(this).attr('data-product-id');

            if( representative_products && representative_products.includes( prod_id ) ){
                // Exclude from search.
            } else {
                var productName = $(this).find('.wtai-cat-product-name').attr('data-product-name').toLowerCase();
                if ( productName.includes( inputValue ) ) {
                    $(this).removeClass('wtai-hidden');
                    productFound = true;
                } else {
                    $(this).addClass('wtai-hidden');
                }
            }
        });

        if( inputValue == '' ){
            productFound = false; // Return to default value.
            $('.wtai-representative-product-input-items-wrap .wtai-cat-product-item').addClass('wtai-hidden');
        }

        clearTimeout(debounceTimer);

        if( productFound ){      
            $('.wtai-rep-dp-no-products-found').hide();            
        } else {
            $('.wtai-cat-product-items-load-more').addClass('wtai-shown');
            
            if( $('.wtai-cat-product-items-load-more').length <= 0 ){
                $('.wtai-representative-product-input-items-wrap').append('<div class="wtai-cat-product-items-load-more" ></div>');
            } 

            $('.wtai-cat-product-items-load-more').addClass('wtai-loading-state');
            $('.wtai-rep-dp-no-products-found').hide();

            debounceTimer = setTimeout(() => {
                search_reference_product_items();
            }, 300); // Adjust the delay as needed
        }
    });

    function trigger_reference_scroll_event(){
        $('.wtai-representative-product-input-items-wrap').on('scroll', function() {
            var $this = $(this);
            if ( $this.scrollTop() + $this.innerHeight() >= $this[0].scrollHeight - 10 ) {
                if( $('.wtai-cat-product-items-load-more').length ){                    
                    load_more_reference_product_items();
                }
            }
        });
    }

    function load_more_reference_product_items(){
        var cat_product_items = $('.wtai-cat-product-items-wrap');
        var current_page = cat_product_items.attr('data-current-page');
        var total_pages = cat_product_items.attr('data-total-pages');

        var category_id = $('#wtai-edit-post-id').attr('value');
        var next_page = parseInt(current_page) + 1;

        $('.wtai-cat-product-items-load-more').addClass('wtai-shown');

        if( $('.wtai-cat-product-items-load-more').hasClass('wtai-loading-state') ){
            return;
        }

        var search = $('.wtai-representative-product-input').val();

        var wtai_nonce = get_wp_nonce();
        var data = {
            action: 'wtai_load_more_representative_product',
            category_id: category_id, 
            wtai_nonce: wtai_nonce,
            next_page: next_page,
            current_page: current_page,
            total_pages: total_pages,
            search: search,
        };

        representativeProductAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
                $('.wtai-cat-product-items-load-more').addClass('wtai-loading-state');
            },
            success: function( data ){ 
                $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').append( data.reference_dropdown_html );

                if( next_page >= total_pages ){
                    $('.wtai-cat-product-items-load-more').remove();
                } else {
                    $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').attr('data-current-page', next_page);
                    $('.wtai-cat-product-items-load-more').removeClass('wtai-shown');
                }

                $('.wtai-cat-product-items-load-more').removeClass('wtai-loading-state');
            }
        });
    }
    
    function load_more_reference_product_items(){
        var cat_product_items = $('.wtai-cat-product-items-wrap');
        var current_page = cat_product_items.attr('data-current-page');
        var total_pages = cat_product_items.attr('data-total-pages');

        var category_id = $('#wtai-edit-post-id').attr('value');
        var next_page = parseInt(current_page) + 1;

        $('.wtai-cat-product-items-load-more').addClass('wtai-shown');

        if( $('.wtai-cat-product-items-load-more').hasClass('wtai-loading-state') ){
            return;
        }

        var search = $('.wtai-representative-product-input').val();

        var wtai_nonce = get_wp_nonce();
        var data = {
            action: 'wtai_load_more_representative_product',
            category_id: category_id, 
            wtai_nonce: wtai_nonce,
            next_page: next_page,
            current_page: current_page,
            total_pages: total_pages,
            search: search,
        };

        representativeProductAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
                $('.wtai-cat-product-items-load-more').addClass('wtai-loading-state');
            },
            success: function( data ){ 
                $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').append( data.reference_dropdown_html );

                if( next_page >= total_pages ){
                    $('.wtai-cat-product-items-load-more').remove();
                } else {
                    $('.wtai-representative-product-input-items-wrap .wtai-cat-product-items-wrap').attr('data-current-page', next_page);
                    $('.wtai-cat-product-items-load-more').removeClass('wtai-shown');
                }

                $('.wtai-cat-product-items-load-more').removeClass('wtai-loading-state');
            }
        });
    }

    var representativeProductSearchAJAX = null;
    function search_reference_product_items(){
        var category_id = $('#wtai-edit-post-id').attr('value');

        $('.wtai-cat-product-items-load-more').addClass('wtai-shown');

        var search = $('.wtai-representative-product-input').val();

        var wtai_nonce = get_wp_nonce();
        var data = {
            action: 'wtai_search_representative_product',
            category_id: category_id, 
            wtai_nonce: wtai_nonce,
            search: search,
        };

        if( $('.wtai-cat-product-items-load-more').length <= 0 ){
            $('.wtai-representative-product-input-items-wrap').append('<div class="wtai-cat-product-items-load-more" ></div>');
        } 
        
        $('.wtai-cat-product-items-load-more').addClass('wtai-loading-state');
        $('.wtai-rep-dp-no-products-found').hide();

        if( representativeProductSearchAJAX != null ){
            representativeProductSearchAJAX.abort();
        }

        representativeProductSearchAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
            },
            success: function( data ){ 
                $('.wtai-representative-product-input-items-wrap').html( data.reference_dropdown_html );

                $('.wtai-cat-product-items-load-more').removeClass('wtai-loading-state');

                var visibleItemsLength = $('.wtai-cat-product-items-wrap .wtai-cat-product-item').not('.wtai-hidden').length;
                if( visibleItemsLength <= 0 ){
                    $('.wtai-rep-dp-no-products-found').show();
                } else {
                    $('.wtai-rep-dp-no-products-found').hide();
                }

                representativeProductSearchAJAX = null;
            }
        });
    }

    $(document).on('click', '.wtai-product-pager-wrapper .button', function(e){
        if( $(this).hasClass('disabled-nav') ){
            return;
        }

        var button = $(this);

        var number_of_changes_unsave = checkChanges();
        if ( number_of_changes_unsave > 0 ) {
            var type = '';
            if ( button.hasClass('wtai-button-prev')){
                type = 'prev';
            } else {
                type = 'next';
            }
            popupUnsaved(type);
            return false;
        } 

        prev_next_to_button(button);
        e.preventDefault();
    });

    function popupUnsaved( type ){
        $('#wtai-product-edit-cancel').find('.wtai-exit-edit-leave').attr('data-type', type);
        if ( ! $('#wtai-product-edit-cancel').is(':visible') ) {
            $('#wpbody-content').addClass('wtai-overlay-div-2');
            $('#wtai-product-edit-cancel').show();
        }
    }

    function checkChanges() {
        var number_of_changes_unsave = 0;
        $('#postbox-container-2').find('.wtai-metabox').each(
            function(){
                var parentdiv =  $(this);
                var source_newvalue = parentdiv.find('.wtai-data-new-text').html();
                var source_newvalue_stripped = wtaiRemoveLastPipe( parentdiv.find('.wtai-data-new-text').text() );
                var source_origvalue = parentdiv.find('.wtai-data-orig-text').html();

                var addChange = true;
               
                if( addChange ){
                    if ( source_origvalue === source_newvalue ) {
                    } else {
                        if( source_newvalue_stripped != '' ){
                            number_of_changes_unsave++;
                        }
                    }
                }
            }
        );

        return number_of_changes_unsave;
    }

    function prev_next_to_button(button){
        if ( ! button.hasClass('disabled') ) {
            popupGenerateCompleted('hide');

            if( $('.wtai-percentage.keyword-density-perc').length ){
                $('.wtai-percentage.keyword-density-perc').html( '&mdash;' );
                $('.wtai-percentage.wtai-semantic-keyword-density-perc').html( '&mdash;' );
            }
            
            /*if( queueGenerateTimer ){
                clearTimeout(queueGenerateTimer);
            }*/

            if( completeWriting ){
                clearInterval(completeWriting);
            }

            if( $('.wtai-global-loader').hasClass('wtai-is-active') ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
            }

            if( $('.wtai-ai-logo').hasClass('wtai-hide') ){
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }

            window.wtaStreamData = [];
            window.wtaStreamQueueData = [];
            window.wtaStreamQueueProcessing = false;

            //reset generate and transfer status
            $('.wtai-generated-status-label').text('');
            $('.wtai-transferred-status-label').hide();

            //reset text field states
            $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').removeClass('disabled');
            $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
            $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-disabled-click');
            $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-bulk-process');
            $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-bulk-complete');
            $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-loading-metabox');

            $('.wtai-page-generate-all').removeClass('disabled');
            $('.wtai-generate-wrapper .toggle').removeClass('disabled');

            //disable the save button
            $('#save-action .wtai-bulk-button-text').addClass('disabled');

            // Hide title
            $('.wp-heading-inline.wtai-post-title').css('visibility', 'hidden');

            button.parent().addClass('wtai-page-processing');
            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');
            $('.wtai-product-pager-wrapper .button').addClass('disabled');
            $('.wtai-keyword-analysis-button').addClass('disabled');
            $('.wtai-keywords-highlight').addClass('disabled');

            $('.wp-list-table').attr('data-doing-prev-next', '1');

            $('.wtai-page-generate-all').attr('data-rewrite', '0');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

            $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-styles-wrap input[type="radio"]').removeClass('warning');
            $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').removeClass('warning');
            $('.wtai-tone-and-style-form-wrapper .wtai-tone-and-styles-select').find('.wtai-button-label').removeClass('warning');

            //review check reset
            $('.wtai-review-check').prop('disabled', true );
            $('.wtai-review-check').closest('.wtai-review-wrapper').addClass('wtai-review-wrapper-disabled');

            // handling for extension reviews view
            $('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( '' );
            $('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html( '' );
            $('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').addClass('hidden');
            $('.postbox.wtai-metabox .postbox-header').removeClass('wtai-has-status');
            $('.postbox.wtai-metabox .postbox-header').removeClass('one');
            $('.postbox.wtai-metabox .postbox-header').removeClass('wtai-two');
            
            //remove product images content
            $('.wtai-category-image-wrap').html( WTAI_OBJ.empty_category_image );
            $('.wtai-representative-product-input-items-wrap').html( '' );
            $('.wtai-representative-product-items-list').html( '' );
            $('.wtai-representative-product-empty').show();
            $('.wtai-representative-product-counter-wrap').hide();
            $('.wtai-representative-product-counter-wrap .wtai-rpc-item-count').html('0');

            //maybe disable generate buttons
            maybeDisableBulkButtons();

            handle_single_bulk_buttons( 'enable' );
            
            var prod_ids  = $('.wp-list-table').attr('data-ids');

            if ( prod_ids ){
                if( $.isNumeric( prod_ids ) ){
                    var reindex_prod = [];
                    reindex_prod.push(prod_ids);
                    prod_ids = reindex_prod;
                } else {
                    var prod_ids = prod_ids.split(',');   
                }

                var active_prod_id = $('#wtai-edit-post-id').attr('value');
                var type = '';
                if ( button.hasClass('wtai-button-prev')){
                    type = 'prev';
                } else {
                    type = 'next';
                }

                var currentPageNo = $('#wtaCurrentPageNumber').val();
                var index = -1;
                if( type == 'next' ){
                    if( parseInt( $('#wtaNextCategoryId').val() ) != 0 ){
                        index = $('#wtaNextCategoryId').val();
                    } 
                    else{
                        index = getGridNextItem( prod_ids, currentPageNo, active_prod_id );

                        // Retry page 1 because next items might have not been loaded yet
                        if( index == -1 ){
                            index = getGridNextItem( prod_ids, 1, active_prod_id );
                        }
                    }
                }
                else{
                    if( parseInt( $('#wtaPrevCategoryId').val() ) != 0 ){
                        index = $('#wtaPrevCategoryId').val();
                    } 
                    else{
                        index = getGridPreviousItem( prod_ids, currentPageNo, active_prod_id );

                        // Retry page 1 because next items might have not been loaded yet
                        if( index == -1 ){
                            index = getGridPreviousItem( prod_ids, 1, active_prod_id );
                        }
                    }
                }

                $('#wtaNextCategoryId').val( '0' );
                $('#wtaPrevCategoryId').val( '0' );

                if( index == -1 ){
                    return false;
                }

                var product_id_now = index;
                
                $('#normal-sortables').find('.wtai-metabox').addClass('wtai-loading-metabox');
                $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-loading-metabox');
                $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-disabled-click');
                $('.wtai-slide-right-text-wrapper').find('.wtai-close').addClass('wtai-pending');
                $('.wtai-slide-right-text-wrapper').find('.wtai-history').addClass('wtai-pending');
                var html = '';
                for (var i = 0; i < WTAI_OBJ.option_choices; i++) {
                    html = html+'<div class="selection dontselect"><span class="text-wrapper"><p></p></span></div>';
                }
                $('.wtai-metabox').find('.wtai-columns-1').addClass(html);
                               
                bulk_transfer_button_behavior();
                reset_elements_prev_next();
                
                get_category_keyword_edit_data_ajax( product_id_now );

                get_category_edit_data( product_id_now, 1 );
            }
        }
    }

    function getGridNextItem(arr, currentPageNumber, currentItem) {
        // Calculate the start and end indices of the current page
        const itemsPerPage = $('#wtaItemsPerPage').val();
        const startIndex = (currentPageNumber - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, arr.length);

        // Find the index of the current item within the current page
        let currentIndex = -1;
        for (let i = startIndex; i < endIndex; i++) {
            if (arr[i] === currentItem) {
                currentIndex = i;
                break;
            }
        }

        // Determine the next item within the current page
        if (currentIndex !== -1 && currentIndex + 1 < endIndex) {
            $('#wtaCurrentPageNumber').val( currentPageNumber );
            return arr[currentIndex + 1];
        } else if (currentIndex !== -1 && currentIndex + 1 >= endIndex) {
            // Check next pages if we are at the end of the current page
            for (let i = endIndex; i < arr.length; i++) {
                if (i % itemsPerPage === 0) {
                    currentPageNumber++;
                }

                $('#wtaCurrentPageNumber').val( currentPageNumber );
                return arr[i];
            }
        }

        return -1;
    }

    function getGridPreviousItem(arr, currentPageNumber, currentItem) {
        const itemsPerPage = $('#wtaItemsPerPage').val();
        const startIndex = (currentPageNumber - 1) * itemsPerPage;
        const endIndex = Math.min(startIndex + itemsPerPage, arr.length);

        // Find the index of the current item within the current page
        let currentIndex = -1;
        for (let i = startIndex; i < endIndex; i++) {
            if (arr[i] === currentItem) {
                currentIndex = i;
                break;
            }
        }

        // Determine the previous item within the current page
        if (currentIndex !== -1 && currentIndex > startIndex) {
            $('#wtaCurrentPageNumber').val( currentPageNumber );
            return arr[currentIndex - 1];
        } else if (currentIndex !== -1 && currentIndex <= startIndex) {
            // Check previous pages if we are at the start of the current page
            for (let i = startIndex - 1; i >= 0; i--) {
                if (i % itemsPerPage === itemsPerPage - 1) {
                    currentPageNumber--;
                }

                $('#wtaCurrentPageNumber').val( currentPageNumber );
                return arr[i];
            }
        }
        return -1;
    }

    function fetchPrevPageIds(){
        var maxPage = parseInt( $('#wtai-next-prev-max-page-number').val() );
        var currentPage = parseInt( $('#wtai-prev-current-page-number').val() );
        
        if( maxPage >= currentPage && currentPage > 1 ){
            var prevPage = currentPage - 1;
        
            var prevProductURL = $('.prev-page.button').attr('href');
            var newURLObj = new URL(prevProductURL);
            newURLObj.searchParams.set('paged', prevPage); 

            var modifiedURL = newURLObj.toString() + '&wtaFetchPrevIds=yes';

            $.get( modifiedURL, function( data ) {
                var listDataIDs = $(data).find('.wp-list-table').attr('data-ids');
                if( listDataIDs != '' ){
                    var currentIds = $('.wp-list-table').attr('data-ids');
                    var mergedIds = listDataIDs + ',' + currentIds;

                    $('.wp-list-table').attr('data-ids', mergedIds);

                    $('.wtai-product-pager-wrapper .wtai-button-prev').removeClass('disabled');
                    $('.wtai-product-pager-wrapper .button').parent().removeClass('wtai-page-processing');

                    $('#wtai-prev-current-page-number').val( prevPage );

                    var currentIdsArray = listDataIDs.split(',');
                    var prevItem = currentIdsArray[currentIdsArray.length - 1];
                    $('#wtaPrevCategoryId').val( prevItem );
                }
            });
        }
    }

    function fetchNextPageIds(){
        var maxPage = parseInt( $('#wtai-next-prev-max-page-number').val() );
        var currentPage = parseInt( $('#wtai-next-page-number').val() );
        
        if( maxPage > currentPage ){
            var nextPage = currentPage + 1;
        
            var nextProductURL = $('.next-page.button').attr('href');
            var newURLObj = new URL(nextProductURL);
            newURLObj.searchParams.set('paged', nextPage); 

            var modifiedURL = newURLObj.toString() + '&wtaFetchNextIds=yes';

            $.get( modifiedURL, function( data ) {
                var listDataIDs = $(data).find('.wp-list-table').attr('data-ids');
                if( listDataIDs != '' ){
                    var currentIds = $('.wp-list-table').attr('data-ids');
                    var mergedIds = currentIds + ',' + listDataIDs;

                    $('.wp-list-table').attr('data-ids', mergedIds);

                    $('.wtai-product-pager-wrapper .wtai-button-next').removeClass('disabled');
                    $('.wtai-product-pager-wrapper .button').parent().removeClass('wtai-page-processing');

                    $('#wtai-next-page-number').val( nextPage );

                    var currentIdsArray = listDataIDs.split(',');
                    var prevItem = currentIdsArray[0];
                    $('#wtaNextCategoryId').val( prevItem );
                }
            });
        }
    }

    $(document).on('blur', '#wtai-wp-field-input-otherproductdetails', function(){
        save_other_details();
    });

    $(document).on('change', '#wtai-other-product-details', function(){
        save_other_details();
    });

    function save_other_details(){
        var object = $('#wtai-wp-field-input-otherproductdetails');

        //object.prop('disabled', true );

        var enabled = 0;
        if( $('#wtai-other-product-details').is(':checked') ){
            enabled = 1;
        }

        var value = $('#wtai-wp-field-input-otherproductdetails').val();
        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_othercategorydetails_text',
                category_id:  $('#wtai-edit-post-id').attr('value'),
                value: value,
                enabled: enabled,
                wtai_nonce: wtai_nonce
            },
            success: function(data) {
                if ( data.access == 1 ) {
                    //object.prop('disabled', false );
                } else {
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }
                    $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
            }
        });
    }

    // Note: Common function
    $(document).on('keydown keyup change', '.wtai-char-count-parent-wrap .wtai-max-length-field', function(){
        var xval = $(this).val();
        var charLength = xval.length;

        $(this).closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html( charLength );
    });

    // Note: Common function
    window.wtaiGetLinkPreview = function() {
        var product_url = $('.wtai-permalink-wrapper > a').attr('href');
        window.open(product_url, '_blank');
    };

    /*Preview Changes*/
    $(document).on('click', '.wtai-button-preview', function(e){
        popupGenerateCompleted('hide');

        var href = $('.wtai-permalink-wrapper > a').attr('href');
        var delimeter = '?';
        if( href.indexOf('?') > -1 ){
            delimeter = '&';
        }

        var product_url = $('.wtai-permalink-wrapper > a').attr('href') + delimeter + 'wtai-preview=true';
        
        window.open(product_url, '_blank');
        e.preventDefault();
    });

    function get_tone_style_count(){
        var tone_count = $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]:checked').length;
        var style_count = $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-styles-wrap input[type="radio"]:checked').length;

        var total_count = tone_count + style_count;

        $('.wtai-tone-and-style-form-wrapper .wtai-tone-and-styles-select').find('.wtai-button-label').find('.wtai-button-num').text(total_count);
    }

    $(document).on( 'click', '.wtai-edit-product-line .wtai-select-all-checkbox', function(e) {
        if ( ! $('.wtai-checkboxes-all').is(':disabled') ) {
            $('.wtai-edit-product-line').find('.wtai-checkboxes-all').click();
        }
        e.preventDefault();
    });

    $(document).on( 'click', '.wtai-edit-product-line .wtai-checkboxes-all', function() {
        if ( $(this).is( ':checked' ) ) {
            var checked = true;
            $('.wtai-page-generate-all').removeClass('disabled');
            $('.wtai-page-generate-all').removeClass('wtai-generating');
            $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');

            show_ongoing_generation_tooltip( 'hide' );
            rewrite_button_state_behavior();
            bulk_transfer_button_behavior();
            
        } else {
            var checked = false;
            $('.wtai-page-generate-all').addClass('disabled');

            $('#publishing-action .wtai-button-interchange').addClass('disabled');
            $('.wtai-generate-wrapper .toggle').addClass('disabled');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');
        }
       
        $('#postbox-container-2').find('.wtai-metabox').each(
            function(){
                if( $(this).hasClass('wtai-disabled-seo-field') === false ){
                    $(this).find('.postbox-header').find('.wtai-checkboxes').prop( 'checked', checked );
                }
            }
        );

        setTimeout(function(){
            record_preselected_field_types();
        }, 200);
    });

    $(document).on( 'click', '#postbox-container-2 .wtai-checkboxes', function() {
        var dataType = $(this).attr('data-type');

        if( $('#postbox-container-2 .wtai-checkboxes').is(':checked') ) {
            $('.wtai-page-generate-all').removeClass('disabled');
            $('.wtai-page-generate-all').removeClass('wtai-generating');
            $('#publishing-action .wtai-button-interchange').removeClass('disabled');
        } else {
            $('.wtai-page-generate-all').addClass('disabled');
            $('#publishing-action .wtai-button-interchange').addClass('disabled');
            $('#postbox-container-2 .wtai-checkboxes-all').prop('checked', false);
        }

        handle_save_button_state();
        handle_single_transfer_button_state();

        record_preselected_field_types();

        handle_generate_button_state();
        bulk_transfer_button_behavior();
    });

    var recordGeneratePreselectedAJAX = null;
    
    function record_preselected_field_types(){
        var selectedTypes = [];
        $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').each(function(){
            var cb = $(this);
            var type = cb.attr('data-type');

            if( cb.is(':checked') ){
                selectedTypes.push( type );
            }
        });

        var category_id = $('#wtai-edit-post-id').val();

        rewrite_button_state_behavior();
        bulk_transfer_button_behavior();

        var wtai_nonce = get_wp_nonce();

        //mayb record selected types
        var data = {
            action           : 'wtai_record_category_preselected_types',
            selectedTypes    : selectedTypes.join(','),
            category_id      : category_id,
            wtai_nonce: wtai_nonce
        };

        if( recordGeneratePreselectedAJAX !== null ){
            recordGeneratePreselectedAJAX.abort();
        }

        recordGeneratePreselectedAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data, 
            success: function() {
                recordGeneratePreselectedAJAX = null;
                handle_edit_select_all_state();
            }
        });
    }

    function handle_edit_select_all_state(){
        var cb_length = $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').length;
        if( $('#postbox-container-2').find('.wtai-metabox.wtai-disabled-seo-field').length ){
            cb_length = cb_length - $('#postbox-container-2').find('.wtai-metabox.wtai-disabled-seo-field').length;
        }

        if( cb_length == $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes:checked').length ){
            $('.wtai-edit-product-line .wtai-checkboxes-all').prop('checked', true);
        }
        else{
            $('.wtai-edit-product-line .wtai-checkboxes-all').prop('checked', false);
        }
    }

    function handle_save_button_state(){
        var number_of_changes_unsave = checkChanges();

        if( number_of_changes_unsave > 0 ){
            $('#save-action .wtai-bulk-button-text').removeClass('disabled');
        }
        else{
            $('#save-action .wtai-bulk-button-text').addClass('disabled');
        }
    }

    function show_ongoing_generation_tooltip( display = '' ){
        var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();
        var elem = 'wtai-generating-cta-overlay-rewrite';
        if( generationType == 'rewrite' ){
            elem = 'wtai-generating-cta-overlay-generate';
        }

        if( display == 'show' ){
            $('.' + elem).tooltipster('enable');
        }
        else{
            $('.' + elem).tooltipster('disable');
        }
    }

    $(document).on( 'click', '.wtai-page-generate-all', function(e) {
        var button = $(this);
        var has_error = false;
        if( $('#message.error').length ) {
            $('#message.error').remove();
        }
        if ( button.hasClass('disabled') || button.is(':disabled') ){
            return false;
        }

        $('#wtai-restore-global-setting-completed').hide();

        singleGenerationErrorFields = [];//reset error fields

        var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();

        var rewriteText = '0';
        if( 'rewrite' == generationType ){
            rewriteText = '1';
        }

        $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

        //reset if regenareted
        if ( $('.wtai-review-check').prop('checked')) {
            $('.wtai-review-check').trigger('click');
            $('.wtai-review-check').prop('disabled', true );

            $('.wtai-review-check').closest('.wtai-review-wrapper').removeClass('wtai-review-wrapper-disabled');
        }

        var hasTone = false;
        //if( rewriteText != '1'){
            if( $('input.wtai-custom-tone-cb').is(':checked') && $('.wtai-custom-tone-text').val().trim() != '' ){
                hasTone = true;
            }
            else{
                if( $('input.wtai-custom-tone-cb').is(':checked') && $('.wtai-custom-tone-text').val().trim() == '' ){
                    $('.wtai-custom-tone-text').addClass('warning');
                     
                    //added 2024.03.05
                    if( !$('.wtai-tone-and-style-form-select').find('.wtai-button-label').hasClass('warning') ){
                        $('.wtai-tone-and-style-form-select').find('.wtai-button-label').addClass('warning'); 
                    }

                    has_error = true;
                } 
                if ( $('.wtai-wp-filter').find('.wtai-product-tones-wrap input:checked').length <= 0 ){
                    has_error = true;
                } else {
                    hasTone = true;
                }
            }
        //} 

        var hasStyle = false;
        //if( rewriteText != '1'){
            if( $('input.wtai-custom-style-cb').is(':checked') && $('.wtai-custom-style-text').val().trim() != '' ){
                hasStyle = true;
            }
            else{
                if( $('input.wtai-custom-style-cb').is(':checked') && $('.wtai-custom-style-text').val().trim() == '' ){
                    $('.wtai-custom-style-text').addClass('warning');
                     //added 2024.03.05
                    if( !$('.wtai-tone-and-style-form-select').find('.wtai-button-label').hasClass('warning') ){
                        $('.wtai-tone-and-style-form-select').find('.wtai-button-label').addClass('warning'); 
                    }
                    has_error = true;
                }

                if ( $('.wtai-wp-filter').find('.wtai-product-styles-wrap input:checked').length <= 0 ){
                    has_error = true;
                } 
                else {
                    hasStyle = true;
                }
            }
        // }

        var hasRefProd = false; // No reference product for category.
        var hasRefProdCheck = false; // No reference product for category.

        var hasAudience = true;
        if( hasRefProd ){
            has_error = false;
        }

        if ( has_error ) {
            if( !hasTone && !hasRefProdCheck ) {
                $('.wtai-tone-and-style-form-select').find('.wtai-button-label').addClass('warning'); 
                $('.product_not_all_trigger.wtai-product-tones-wrap input[type="checkbox"]').addClass('warning');
            }

            if( !hasStyle && !hasRefProdCheck ) {
                $('.wtai-tone-and-style-form-select').find('.wtai-button-label').addClass('warning'); 
                $('.product_not_all_trigger.wtai-product-styles-wrap input[type="radio"]').addClass('warning');
            }

            if( !hasAudience && !hasRefProdCheck ) {
                $('.wtai-audiences-form-select').find('.wtai-button-label').addClass('warning'); 
                $('.product_not_all_trigger.wtai-product-audiences-wrap input[type="checkbox"]').addClass('warning');
            }
            
            return false;
        }
        else{
            //remove warnings tones and styles
            $('.wtai-custom-tone-text').removeClass('warning');
            $('.wtai-custom-style-text').removeClass('warning');
            $('.wtai-tone-and-style-form-select').find('.wtai-button-label').removeClass('warning'); 
            $('.wtai-product-styles-cb').removeClass('warning');
            $('.wtai-product-wrap .selectize-input').removeClass('warning');

            ///remove warnings audience
            $('.wtai-audiences-form-select').find('.wtai-button-label').removeClass('warning'); 
            $('.wtai-product-audiences-cb').removeClass('warning');
        }

        popupGenerateCompleted('hide');
        
        var number_of_changes_unsave = checkChanges();
        if ( number_of_changes_unsave > 0 && !$(this).hasClass('wtai-proceed') ) {
            $('.wtai-page-generate-all').addClass('wtai-proceed');
            popupUnsavedGenerateAll('#wtai-product-generate-forced', 'bulk', '' );
            return false;
        }

        button.addClass('disabled');
        button.addClass('wtai-generating');
        
        $('.wtai-generate-cta-radio-wrap').addClass('wtai-generation-ongoing');
        show_ongoing_generation_tooltip( 'show' );

        $('.wtai-generate-wrapper .button-primary.toggle').addClass('disabled');

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        $('.wtai-bulk-button-text').addClass('disabled');
        $('.wtai-checkboxes-all').addClass('disabled');
        $('.wtai-checkboxes-all').prop('disabled', true );

        //remove wtai-bulk-process and wtai-bulk-complete class since we made it this far
        $('.wtai-metabox').removeClass('wtai-bulk-process');
        $('.wtai-metabox').removeClass('wtai-bulk-complete');

        if ( ! button.is(':disabled') && $('.wtai-metabox.wtai-bulk-process').length  == 0 ) {
            $('#postbox-container-2').find('.selection').each(function(){
                $(this).removeClass('selected');
            });

            var category_id = $('#wtai-edit-post-id').attr('value');

            var atts = []; // No product attributes for the category.
             
            var tones = [];
            $('.wtai-product-form-container').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb:checked').each( function(){
                tones.push( $(this).val() );
            });
            
            var audiences = [];
            $('.wtai-product-form-container').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb:checked').each( function(){
                audiences.push( $(this).val() );
            });

            var styles = $('.wtai-product-form-container').find('.wtai-product-styles-wrap').find('.wtai-product-styles-cb:checked').val();
            var date = new Date();
            var offset = date.getTimezoneOffset();

            var semanticKeywords = [];
            if (  $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').length > 0 ){
                $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').each( function(){
                    semanticKeywords.push( $(this).find('.wtai-keyword-name').text() );
                });   
            }

            var keywords = [];
            if (  $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').length > 0 ){
                $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').each( function(){
                    keywords.push( $(this).find('.wtai-keyword-name').text() );
                });   
            }

            var customAudience = '';
            if( $('.wtai-product-form-container').find('.wtai-input-text-suggested-audiance').length > 0 ){
                customAudience =  $('.wtai-product-form-container').find('.wtai-input-text-suggested-audiance').val();
            }

            var customTone = '';
            if( $('.wtai-custom-tone-text').val().trim() != '' ){
                tones = [];
                customTone = $('.wtai-custom-tone-text').val();
            }

            var customStyle = '';
            if( $('.wtai-custom-style-text').val().trim() != '' ){
                styles = '';
                customStyle = $('.wtai-custom-style-text').val();
            }

            if( rewriteText == 1 ) {
                $('#wtai-product-generate-completed .wtai-loading-header-details > span').html(WTAI_OBJ.rewriteCompleteTextPopup);
            } else {
                $('#wtai-product-generate-completed .wtai-loading-header-details > span').html(WTAI_OBJ.generateCompleteTextPopup);
            }

            var referenceProductID = ''; // No reference product for the category.

            if( ! referenceProductID ){
                $('.wtai-generate-wrapper .button-primary.toggle').addClass('wtai-generating');
            }

            var includeFeaturedImage = 0;
            if( $('.wtai-category-image-selection-cb-wrap #wtai-category-image-selection-cb').is(':checked') && 
                $('#postbox-container-2 .wtai-metabox .wtai-checkboxes:checked').length > 0 ){
                includeFeaturedImage = 1;
            }

            var altimages = []; // No alt images for the category but we are supplying the representative products image ID here if there is any.

            var representative_product_ids = [];
            var representative_product_with_image_ids = [];
            if (  $('.wtai-representative-product-items-list .wtai-representative-product-item').length > 0 ){
                $('.wtai-representative-product-items-list .wtai-representative-product-item').each( function(){
                    representative_product_ids.push( $(this).attr('data-product-id') );

                    if( $(this).hasClass('wtai-has-featured-image') ){
                        representative_product_with_image_ids.push( $(this).attr('data-product-id') );
                    }
                });   
            }

            //get keyword analysis views count
            var keywordAnalysisViewsCount = $('.wtai-keyword-analysis-view').val();

            var fieldsToProcessSequence = ['page_title', 'page_description', 'category_description', 'open_graph'];
            var fieldsToProcess = [];
            var fieldsToProcessSelected = [];

            $('#postbox-container-2').find('.wtai-metabox').each(
                function(){
                    if ( $(this).find('.postbox-header').find('.wtai-checkboxes').is( ':checked' ) && $(this).hasClass('wtai-disabled-seo-field') == false ) {
                        var data_object = $(this);

                        var processFieldGeneration = true;
                        if( rewriteText == 1 ) {
                            var currentValue = data_object.find('.wtai-current-value .wtai-text-message').text();
                            if( currentValue.trim() == '' ){
                                processFieldGeneration = false;
                            }
                        }

                        if( processFieldGeneration ){
                            data_object.addClass('wtai-disabled-click'); //start of disable click
                            data_object.addClass('wtai-bulk-process');
                            data_object.addClass('wtai-metabox-update');

                            data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                            data_object.find('.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');

                            updateToolTipForTransferSingleButton( 1 );

                            var type = data_object.data('type');
                            var id = data_object.find('textarea').attr('id');
                            data_object.find('.wtai-checkboxes').prop('disabled', true );
                            
                            clearEditor(id, type);

                            removeHighlightkeywordsByField( id );

                            fieldsToProcessSelected.push( type );

                            data_object.find('.wtai-generate-disable-overlay-wrap').removeClass('wtai-shown');
                        }                        
                    }
                }
            );   

            if( ( includeFeaturedImage == 1 || representative_product_with_image_ids.length > 0 ) && button.hasClass('wtai-pre-process-image-done') == false && WTAI_OBJ.is_premium == '1' ){
                
                var loaderEstimatedTime = $('#wtai-loader-estimated-time');
                var preprocessImageLoader = $('#wtai-preprocess-image-loader');

                // Check if both elements exist
                preprocessImageLoader.show();

                //added 2024.03.05
                $('#wpcontent').addClass('preprocess-image');
                
                //split the images into batches of 10
                var altImagesBatches = [];
                var aibatchid = 0;
                var aictr = 0;
                altImagesBatches[ aibatchid ] = [];

                $.each(representative_product_with_image_ids, function( index, alt_image_id ) {
                    if( aictr == 10 ){
                        aictr = 0;
                        aibatchid++;

                        altImagesBatches[ aibatchid ] = [];
                    }

                    //console.log("batch counter " + aictr + " >> " + aibatchid);

                    altImagesBatches[ aibatchid ][ aictr ] = alt_image_id;

                    aictr++;
                });

                // counter goes here
                window.currentAltImageBatch = 0;
                window.maxAltImageBatchNo = aibatchid;
                window.altImageForUpload = representative_product_with_image_ids;
                window.altImageSuccessForUpload = [];
                window.altImageBatchForUpload = altImagesBatches;
                window.altImageIdsError = [];

                // Pre process images first.
                process_image_upload_single( category_id, altImagesBatches[0], includeFeaturedImage );

                $('.wtai-slide-right-text-wrapper .wtai-close').addClass('disabled');
                $('.wtai-slide-right-text-wrapper .wtai-button-prev').addClass('disabled-nav');
                $('.wtai-slide-right-text-wrapper .wtai-button-next').addClass('disabled-nav');

                return;
            }
            
            $('.wtai-loader-generate #wtai-preprocess-image-loader').remove();
            $('#wtai-preprocess-image-loader').hide();

            //added 2024.03.05
            $('#wpcontent').removeClass('preprocess-image');

            $('.wtai-slide-right-text-wrapper .wtai-close').removeClass('disabled');
            $('.wtai-slide-right-text-wrapper .wtai-button-prev').removeClass('disabled-nav');
            $('.wtai-slide-right-text-wrapper .wtai-button-next').removeClass('disabled-nav');

            $.each(fieldsToProcessSequence, 
                function( index, fieldSequence ) {
                    if( fieldsToProcessSelected.includes( fieldSequence ) ){
                        fieldsToProcess.push(fieldSequence);
                    }
                }
            );

            var hasProductGenerationError = false;

            var creditCountNeeded = $('.wtai-generate-wrapper .wtai-page-generate-all .wtai-credvalue').text();
            if( rewriteText == 1 ) {
                creditCountNeeded = $('.wtai-generate-wrapper .btn-rewrite-generate .wtai-credvalue').text();
            }

            var otherproductdetails = '';
            if( $('#wtai-woocommerce-product-attributes #wtai-other-product-details').is(':checked') ){
                otherproductdetails = $('#wtai-woocommerce-product-attributes #wtai-wp-field-input-otherproductdetails').val();
            }

            handle_single_bulk_buttons( 'disable' );

            setTimeout(function() {
                if ( $('#postbox-container-2').find('.wtai-bulk-process').length ) {
                    //set data and other details per field
                    if ( $('#postbox-container-2').find('.wtai-bulk-process').length ){
                        var queueAPI = 1; //set all fields to queue and not just product excerpt and product description
                        
                        var successful_image_for_upload = [];
                        var error_image_for_upload = [];
                        if( representative_product_with_image_ids.length > 0 ){
                            if( window.altImageSuccessForUpload.length != null ){
                                successful_image_for_upload = window.altImageSuccessForUpload;
                                error_image_for_upload = window.altImageIdsError;
                            }
                        } else {
                            error_image_for_upload = window.altImageIdsError;
                        }

                        var wtai_nonce = get_wp_nonce();

                        var data = {
                            action                    : 'wtai_generate_category_text',
                            category_id               : category_id,
                            browsertime               : offset,
                            attr_fields               : atts.join(','),
                            tones                     : tones.join(','),
                            audiences                 : audiences.join(','),
                            styles                    : styles,
                            otherproductdetails       : otherproductdetails,
                            options                   : WTAI_OBJ.option_choices,
                            fields                    : fieldsToProcess.join(','), 
                            save_generated            : 1,
                            customAudience            : customAudience,
                            semanticKeywords          : semanticKeywords.join(','),
                            keywords                  : keywords.join(','),
                            queueAPI                  : queueAPI,
                            customTone                : customTone,
                            customStyle               : customStyle,
                            rewriteText               : rewriteText,
                            referenceProductID        : referenceProductID,
                            keywordAnalysisViewsCount : keywordAnalysisViewsCount,
                            creditCountNeeded         : creditCountNeeded,
                            doingBulkGeneration       : '0',
                            includeFeaturedImage      : includeFeaturedImage,
                            altimages                 : successful_image_for_upload.join(','),
                            altimageserror            : error_image_for_upload.join(','),
                            representative_product_ids : representative_product_ids.join(','),
                            wtai_nonce                : wtai_nonce
                        };
                        
                        if( fieldsToProcess.length > 0 ){                        
                            $.each(fieldsToProcess, 
                                function( key_bulk, fieldType ){
                                
                                var meta_object = $('.wtai-metabox-' + fieldType);
                                var type = meta_object.data('type');

                                $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').removeClass('generated');

                                if ( meta_object.find('#'+type+'_length_min').length > 0 ){
                                    data[type+'_length_min'] = meta_object.find('#'+type+'_length_min').val();
                                }
                                if ( meta_object.find('#'+type+'_length_max').length > 0 ){
                                    data[type+'_length_max'] = meta_object.find('#'+type+'_length_max').val();
                                }

                                if( window.WTAStreamConnected ){
                                    var elemId = '';
                                    if ( meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                        elemId = meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                    } else if ( meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                        elemId = meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                    }

                                    var streamData = {
                                        data : data,
                                        elem : $('#' + elemId),
                                        elemId : elemId,
                                        textIndex : 0,
                                        doingBulkGenerate : true,
                                        type : type
                                    };

                                    window.wtaStreamData[type] = streamData;
                                }
                            });
                        }

                        $.ajax({
                            type: 'POST',
                            dataType: 'JSON',
                            url: WTAI_OBJ.ajax_url,
                            data: data, 
                            success: function(data) {
                                var is_premium = data.is_premium;

                                if( data.access ){
                                    if ( data.message ){
                                        if ( data.message == 'expire_token' ){
                                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                                $('.wtai-edit-product-line' ).find('#message').remove();
                                            }

                                            //display general error message during generation
                                            $('<div id="message" class="wtai-generation-error error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                                        }
                                        else{
                                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                                $('.wtai-edit-product-line' ).find('#message').remove();
                                            }

                                            //display general error message during generation
                                            $('<div id="message" class="wtai-generation-error error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                                            
                                            $('.wtai-metabox.wtai-bulk-process').each(function(){
                                                if( $(this).hasClass('wtai-bulk-complete') ){
                                                }
                                                else{
                                                    $(this).addClass('wtai-loading-metabox');
                                                }
                                            });

                                            $.each(fieldsToProcess, function( key_bulk, type ){
                                                var meta_object = $('.wtai-metabox-' + type);

                                                $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');

                                                if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                                                    $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                                                }

                                                $('.wtai-metabox-' + type).addClass('wtai-loading-metabox');
                                                
                                                $('.wtai-metabox-' + type).removeClass('wtai-bulk-process');
                                                $('.wtai-metabox-' + type).removeClass('wtai-bulk-complete');

                                                if( window.WTAStreamConnected ){
                                                    window.wtaStreamData[type] = [];
                                                }

                                                fetchFreshTextFromAPI( category_id, type, true );

                                                //meta_object.addClass('wtai-bulk-complete');
                                                meta_object.removeClass('wtai-disabled-click');
                                                meta_object.find('.wtai-checkboxes').prop('disabled', false);
                                            });                                          

                                            $('#wtai-preprocess-image-loader').hide();

                                            maybeDisableBulkButtons();
                                        }

                                        $('.wtai-wp-filter #wtai-checkboxes-all').removeClass('disabled');
                                        $('.wtai-wp-filter #wtai-checkboxes-all').prop('disabled', false);

                                        hasProductGenerationError = true;

                                        button.removeClass('disabled');
                                        button.removeClass('wtai-proceed');

                                        $('.wtai-page-generate-all').attr('data-rewrite', '0');

                                        if( ! hasRefProd ){
                                            $('.wtai-generate-wrapper .button-primary.toggle').removeClass('disabled');
                                        }

                                        $('.wtai-generate-wrapper .button-primary.toggle').removeClass('wtai-generating');

                                        $('.wtai-collapse-expand-wrapper .wtai-checkboxes-all').removeClass('disabled');
                                        $('.wtai-collapse-expand-wrapper .wtai-checkboxes-all').prop('disabled', false );

                                        $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
                                        show_ongoing_generation_tooltip( 'hide' );

                                        handle_single_product_edit_state( is_premium );
                                        handle_density_premium_state( is_premium );

                                        $('.wtai-slide-right-text-wrapper').animate({ scrollTop: 0 }, 'fast');

                                        $('.wtai-global-loader').removeClass('wtai-is-active');
                                        $('.wtai-ai-logo').removeClass('wtai-hide');

                                        if( completeWriting ){
                                            clearInterval(completeWriting);
                                        }
                                    } 
                                    else{
                                        if( data.results ){
                                            $.each(fieldsToProcess, function( key_bulk, type ){
                                                if( window.WTAStreamConnected ){
                                                    window.wtaStreamQueueProcessing = true;
                                                }
                                                else{
                                                    //do fallback if singalr not present
                                                    process_queue_generate( data.results.requestId, category_id, type );
                                                }
                                            });

                                            if( altimages.length > 0 ){
                                                if( window.WTAStreamConnected ){
                                                    window.wtaStreamQueueProcessing = true;
                                                }
                                            }

                                            maybeDisableBulkButtons();

                                            $('.wtai-product-generate-completed-popup .wtai-notif-error-altimage-fields').html( '' );
                                            if( data.error_alt_image_message != '' ){
                                                $('.wtai-product-generate-completed-popup .wtai-notif-error-altimage-fields').html( data.error_alt_image_message );
                                            }
                                        }
                                    }

                                    handle_single_product_edit_state( is_premium );
                                    handle_density_premium_state( is_premium );
                                } 
                                else {
                                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                        $('.wtai-edit-product-line' ).find('#message').remove();
                                    }

                                    //display general error message during generation
                                    $('<div id="message" class="wtai-generation-error error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                                }

                                $('.wtai-page-generate-all').removeClass('wtai-pre-process-image-done');
                            }
                        });
                    }

                    var processTimerCount = 0;
                    completeWriting = setInterval(function(){
                        var conditions = 0;
                        var passed_conditions = 0;
                        if( $('#postbox-container-2').find('.wtai-bulk-process').length ){
                            conditions++;

                            if( $('.wtai-bulk-process:not(.wtai-bulk-complete)').length == 0 ){
                                passed_conditions++;
                            }
                        }
                        
                        var passedAllCondition = false;
                        if( conditions == passed_conditions ){
                            passedAllCondition = true;
                        }

                        $('.wtai-global-loader').addClass('wtai-is-active');
                        $('.wtai-ai-logo').addClass('wtai-hide');

                        $('.wtai-slide-right-text-wrapper .wtai-close').addClass('disabled');
                        $('.wtai-slide-right-text-wrapper .wtai-button-prev').addClass('disabled-nav');
                        $('.wtai-slide-right-text-wrapper .wtai-button-next').addClass('disabled-nav');

                        if ( ( $('#postbox-container-2').find('.wtai-bulk-process').length && $('.wtai-bulk-process:not(.wtai-bulk-complete)').length == 0 ) && 
                            ! hasProductGenerationError && passedAllCondition ){

                            $('.wtai-bulk-process').each(
                                function(){
                                var meta_object = $(this);
                                meta_object.removeClass('wtai-bulk-complete');
                                meta_object.removeClass('wtai-bulk-writing');
                                
                                meta_object.removeClass('wtai-bulk-process');
                                meta_object.find('.wtai-checkboxes').prop('disabled', false );
                                meta_object.removeClass('wtai-disabled-click');
                            }); 
                            
                            $('.wtai-page-generate-all').removeClass('wtai-pre-process-image-done');

                            button.removeClass('disabled');
                            button.removeClass('wtai-proceed');

                            $('.wtai-checkboxes-all').removeClass('disabled');
                            $('.wtai-checkboxes-all').prop('disabled', false );
                            $('.wtai-review-check').prop('disabled', false );
                            $('.wtai-review-check').prop('checked', false );

                            clearInterval(completeWriting);

                            getKeywordOverallDensity();
                            
                            if( $('body.wtai-open-single-slider' ).length > 0 && $('#postbox-container-2').find('.wtai-disabled-click').length == 0  ){
                                $('.wtai-global-loader').removeClass('wtai-is-active');

                                setTimeout(function() {
                                    $('.wtai-global-loader').removeClass('wtai-is-active');
                                    $('.wtai-ai-logo').removeClass('wtai-hide');

                                    $('.wtai-slide-right-text-wrapper .wtai-close').removeClass('disabled');
                                    $('.wtai-slide-right-text-wrapper .wtai-button-prev').removeClass('disabled-nav');
                                    $('.wtai-slide-right-text-wrapper .wtai-button-next').removeClass('disabled-nav');

                                    popupGenerateCompleted('show', singleGenerationErrorFields);

                                    reset_image_alt_local_data();
                                    singleGenerationErrorFields = [];
                                    window.wtaStreamQueueProcessing = false;

                                    $('.wtai-page-generate-all').attr('data-rewrite', '0');

                                    handle_single_bulk_buttons( 'enable' );
                                    handle_save_button_state();
                                    handle_single_transfer_button_state();
                                    bulk_transfer_button_behavior();

                                    if( $('input.wtai-custom-style-ref-prod').is(':checked') == false ){
                                        $('.wtai-generate-wrapper .button-primary.toggle').removeClass('disabled');
                                    }
                                    else{
                                        $('.wtai-generate-wrapper .button-primary.toggle').addClass('disabled');
                                    }

                                    $('.wtai-generate-wrapper .button-primary.toggle').removeClass('wtai-generating');

                                    //reset keyword views analysis count
                                    updateKeywordAnaysisViewCount( true );
                                }, 300);  
                            }
                        }
                        else{
                            //maybe reset if idle for 3 mins
                            if( processTimerCount > 180000 && $('.wtai-global-loader').hasClass('wtai-is-active') == false && ! hasProductGenerationError ){
                                singleGenerationResetIdle();
                            }
                        }

                        processTimerCount = processTimerCount + 50;
                    }, 50 );
                }
            }, 200);    
        }
        e.preventDefault();
    });

    // trigger popupGenerateCompleted
    $(document).on('wtai_popup_generate_completed', function(e, args){
        e.stopImmediatePropagation();

        var status = args.status;
        var errorData = args.errorData;

        popupGenerateCompleted( status, errorData );
    });

    function popupGenerateCompleted(status, errorData = []){
        if( $('.wtai-metabox .postbox-header .wtai-checkboxes:checked').length > 0 ){        
            if( $('.wtai-page-generate-all').hasClass('wtai-generating') ){
                $('.wtai-page-generate-all').removeClass('wtai-generating');
            }

            if( $('.wtai-page-generate-all').hasClass('disabled') ){
                $('.wtai-page-generate-all').removeClass('disabled');
            }
        }

        $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
        show_ongoing_generation_tooltip( 'hide' );

        var all_errors_shown = false;
        var last_error = '';
        if( errorData.length > 0 && $('.wtai-metabox .wtai-checkboxes:checked').length && errorData.length == $('.wtai-metabox .wtai-checkboxes:checked').length ){
            
            all_errors_shown = true;
            last_error = errorData[errorData.length - 1].message;
        }

        $('.wtai-product-generate-completed-popup').removeClass('has-alt-text-error');
        $('.wtai-product-generate-completed-popup .wtai-notif-error-wrap').removeClass('wtai-shown');
        $('.wtai-product-generate-completed-popup .wtai-notif-error-altimage-fields').removeClass('wtai-shown');
        $('.wtai-product-generate-completed-popup .wtai-notif-error-wrap .wtai-notif-error-text-fields .wtai-notif-error-text-field' ).removeClass('wtai-shown');

        $('.wtai-loader-generate #wtai-preprocess-image-loader').remove();
        $('#wtai-preprocess-image-loader, .wtai-preprocess-image-loader').hide();
        if (  status == 'show' ) {
            if( window.altImageIdsError && window.altImageIdsError.length > 0 ){
                errorData.push( { type : 'alt_text', message : '' } );
                
                $('.wtai-product-generate-completed-popup').addClass('has-alt-text-error');
                $('.wtai-product-generate-completed-popup .wtai-notif-error-altimage-fields').addClass('wtai-shown');
            }

            if( errorData.length > 0 ){
                $.each(errorData, 
                    function( index, errorObj ) {
                        $('.wtai-product-generate-completed-popup .wtai-notif-error-wrap .wtai-notif-error-text-fields .wtai-notif-error-text-field-' + errorObj.type).addClass('wtai-shown');
                    }
                );

                $('.wtai-product-generate-completed-popup .wtai-notif-error-wrap').addClass('wtai-shown');
            }

            if( $('#wtai-loader-estimated-time').is(':visible') ) {
                $('#wtai-product-generate-completed-bulk').show();
            } 
            else {
                $('#wtai-product-generate-completed').show();
            }

            $('.wtai-global-loader').removeClass('wtai-is-active');
            $('.wtai-ai-logo').removeClass('wtai-hide');
       
           bulk_transfer_button_behavior();
           reset_image_alt_local_data();
        } else {
            if( $('#wtai-loader-estimated-time').is(':visible') ) {
                $('#wtai-product-generate-completed-bulk').hide();
            } else {
                $('#wtai-product-generate-completed').hide();
            }
        }        
    }

    function reset_image_alt_local_data(){
        window.currentAltImageBatch = 0;
        window.maxAltImageBatchNo = 0;
        window.altImageForUpload = [];
        window.altImageSuccessForUpload = [];
        window.altImageBatchForUpload = [];
        window.altImageIdsError = [];
    }

    function popupUnsavedGenerateAll(parentdiv, submittype, type){
        if (  type ){
            $(parentdiv).find('.wtai-product-generate-proceed').attr('data-type', type);
        }
        
        $(parentdiv).find('.wtai-product-generate-proceed').attr('data-submittype', submittype);
        if ( ! $(parentdiv).is(':visible') ) {
            $('#wpbody-content').addClass('wtai-overlay-div-2');
            $(parentdiv).show();
        }
    }

    function clearEditor(id, type) {
        tinymce.get(id).setContent('<span class="typing-cursor">&nbsp;</span>');
        
        if ( $('#wtai-product-details-'+type ).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').length > 0 ) {
            $('#wtai-product-details-'+type ).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').remove();
        }

        var text_html = '';
        switch( type ){
            case 'category_description':                
                text_html = '<span class="wtai-text-count-details"><span class="wtai-char-counting"><span class="word-count">'+WTAI_OBJ.words.replace('%words%', 0)+'</span></span>';
                break;
            default:
                text_html = '<span class="wtai-text-count-details"><span class="wtai-char-counting"><span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', 0)+'</span> | <span class="word-count">'+WTAI_OBJ.words.replace('%words%', 0)+'</span></span></span>';
                break;
        }
        $('#wtai-product-details-'+type ).find('.wtai-generate-value-wrapper').find('.wtai-api-data-'+type).after(text_html);

        //clear highlight
        removeHighlightkeywordsByField( id );
    }

    function removeHighlightkeywordsByField(id) {
        var editor = tinymce.get(id);
        if (editor) {
            var cloneId = $('#'+id).closest('.postbox').find('.wtai-wp-editor-cloned').attr('id');
            var clonedEditor = tinymce.get(cloneId);
            if( clonedEditor ){
                clonedEditor.setContent( '' );
            }
        }       
    }

    // Batch image upload
    function process_image_upload_single( category_id, altimages, includeFeaturedImage ){
        var wtai_nonce = get_wp_nonce();

        var date = new Date();
        var offset = date.getTimezoneOffset(); 
        var button = $('.wtai-page-generate-all');

        //console.log("ALT IMAGE UPLOAD: BATCH#: " + window.currentAltImageBatch + " > " + window.maxAltImageBatchNo + " CATEGORY ID: " + category_id + " ALT IMAGES: " + altimages.join(',') + " INCLUDE FEATURED IMAGE: " + includeFeaturedImage + " TIMEZONE OFFSET: " + offset );
        //console.log("error " + window.altImageIdsError );

        var has_normal_field_type = 0;
        if( $('#postbox-container-2 .postbox.wtai-metabox .wtai-checkboxes:checked').length > 0 ){
            has_normal_field_type = 1;
        }

        // pre process images first
        var date        = new Date();
        var offset      = date.getTimezoneOffset(); 
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_preprocess_category_images',
                category_id: category_id,
                browsertime: offset,
                product_ids: altimages.join(','),
                includeFeaturedImage: includeFeaturedImage,
                altImageIdsError: window.altImageIdsError.join(','),
                wtai_nonce: wtai_nonce,
                has_normal_field_type: has_normal_field_type,
            },
            success: function(data) {
                // record errorin uploading
                if( data.error_process.length > 0){
                    $.each(data.error_alt_images,
                        function( index, alt_image_id ) {
                            window.altImageIdsError.push( alt_image_id );
                        }
                    );

                    $.each(data.error_process,
                        function( index, error_process_data ) {
                            window.altImageIdsError.push( error_process_data.image_id );
                        }
                    );
                }

                if( data.success_ids.length > 0){
                    $.each(data.success_ids,
                        function( index, alt_image_id ) {
                            window.altImageSuccessForUpload.push( alt_image_id );
                        }
                    );
                }

                //check if queue batches already finished
                if( window.currentAltImageBatch < window.maxAltImageBatchNo ){
                    var nextBatchNo = window.currentAltImageBatch + 1;
                    var nextAltimages = window.altImageBatchForUpload[ nextBatchNo ];

                    window.currentAltImageBatch = nextBatchNo;
                    process_image_upload_single( category_id, nextAltimages, includeFeaturedImage );
                }     
                else{        
                    if( window.altImageIdsError.length > 0){
                        $('.wtai-global-loader').removeClass('wtai-is-active');
                        $('.wtai-ai-logo').removeClass('wtai-hide');

                        $('#wtai-confirmation-proceed-image-loader .wtai-error-message-container').html( data.error_message );

                        if( window.altImageSuccessForUpload.length <= 0 && has_normal_field_type == 0 ){
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process').hide();
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-cancel').hide();
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-ok-cancel').show();
                        }
                        else{
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process').show();
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-cancel').show();
                            $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-ok-cancel').hide();
                        }

                        $.each(window.altImageIdsError,
                            function( index, alt_image_id ) {
                                $('.wtai-image-alt-metabox-' + alt_image_id).addClass('wtai-error-upload');
                            }
                        );

                        $('#wtai-preprocess-image-loader').hide();
                        
                        //mcr 2024.03.06
                        var loaderEstimatedTime = $('#wtai-loader-estimated-time');
                        var confirmationProceedImageLoader = $('#wtai-confirmation-proceed-image-loader');
                    
                        // Check if both elements exist
                        if (confirmationProceedImageLoader.length > 0) {
                            confirmationProceedImageLoader.show();
                        }
                    }
                    else{
                        $('#postbox-container-2 .wtai-checkboxes-alt').prop('disabled', false);

                        $('#wtai-confirmation-proceed-image-loader .wtai-error-message-container').html( '' );
                        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process').show();
                        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-cancel').show();
                        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-ok-cancel').hide();
                        $('#wtai-confirmation-proceed-image-loader').hide();

                        $('.wtai-global-loader').addClass('wtai-is-active');
                        $('.wtai-ai-logo').addClass('wtai-hide');

                        button.addClass('wtai-pre-process-image-done');
                        button.removeClass('disabled');
                        button.removeClass('wtai-generating');
                        button.trigger('click'); //lets retrigger the click since all is well
                    }
                }
            }
        });
    }

    $(document).on('wtaGenerateTextStart', function(e, eventInfo){
        e.stopImmediatePropagation();

        var elemId = eventInfo.elemId;

        tinymce.get(elemId).setContent('<span class="typing-cursor"></span>');
    });

    $(document).on('wtaGenerateTextProcessing', function(e, eventInfo){
        e.stopImmediatePropagation();

        var elemId = eventInfo.elemId;
        var type = eventInfo.type;
        var textIndex = eventInfo.textIndex + 1;
        eventInfo.textIndex = textIndex;

        window.wtaStreamData[type] = eventInfo;

        if( $('.wtai-global-loader').hasClass('wtai-is-active') == false ){
            $('.wtai-global-loader').addClass('wtai-is-active');
        }

        if( $('.wtai-ai-logo').hasClass('wtai-hide') == false ){
            $('.wtai-ai-logo').addClass('wtai-hide');
        }

        typeCountMessage(type, tinymce.get(elemId).getContent({format: 'text'}) );
    });

    $(document).on('wtaSingleGenerateStatusUpdate', function(e, eventInfo){
        e.stopImmediatePropagation();

        var messageEntry = eventInfo.messageEntry;

        var status = messageEntry.encodedMsg.status;
        var recordId = messageEntry.encodedMsg.recordId;
        var doingBulkGenerate = eventInfo.doingBulkGenerate;
        var elemId = eventInfo.elemId;

        if( $('#wtai-edit-post-id').length > 0 && 
            recordId ){
            var type = eventInfo.type;
            var post_id = $('#wtai-edit-post-id').val();
            
            if( recordId == post_id ){
                if( doingBulkGenerate ){
                    $('#' + elemId).closest('.wtai-bulk-process').addClass('wtai-bulk-complete');
                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-checkboxes').prop('disabled', false);
                }

                var content = tinymce.get(elemId).getContent();
                if( $(content).find('.typing-cursor').length ){
                    content = content.replace(/\s*<span class="typing-cursor">.*<\/span>/g, '');
                    tinymce.get(elemId).setContent( content );
                }

                updateHiddentext(elemId);

                fetchFreshTextFromAPI( post_id, type, true, 1, 0, 1, 1 );

                maybeDisableBulkButtons();

                //reset streamdata obj
                window.wtaStreamData[type] = [];

                $('.wtai-generate-wrapper .toggle').removeClass('disabled');

                if( status == 'Failed' ){
                    singleGenerationErrorFields.push( 
                        {
                            type : type,
                            message : messageEntry.encodedMsg.error
                        } 
                    );

                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-transferred-status-label').hide();
                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');
                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-generated-status-label').text(WTAI_OBJ.notGeneratedStatusText);
                }
                else{
                    $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-generated-status-label').text(WTAI_OBJ.generatedStatusText);
                }
            }
        }   
    });

    function fetchFreshTextFromAPI( category_id, type = '', reloadstats = false, clearQueue = 0, bulk = 0, bulkRemoveDisable = 1, refresh_credits = 0 ){        
        var renderType = '';
        if( type != '' && bulk == 0 ){
            //force the box to load
            $('.wtai-metabox-' + type).addClass('wtai-loading-metabox');

            renderType = type;
        }

        if( $('body.wtai-open-single-slider').length && $('#wtai-edit-post-id').length ){
            var current_post_id = $('#wtai-edit-post-id').val();
            if( parseInt( current_post_id ) != category_id ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                return;
            }
        }

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_single_category_data_text',
                category_id: category_id, 
                clearQueue : clearQueue,
                bulkQueue : bulk,
                refresh_credits : refresh_credits,
                wtai_nonce : wtai_nonce,
            },
            success: function(res){
                if ( res.success != '1' && res.message ){
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }

                    //display general error message during generation
                    $('<div id="message" class="wtai-nonce-error error notice is-dismissible"><p>'+res.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
                else{

                    if( res.result['available_credit_label'] != '' ){
                        $('.wtai-credit-available-wrap .wtai-credit-available').html( res.result['available_credit_label'] );
                    }

                    if( bulk == 0 ){
                        $('.wtai-loading-metabox').find('.wtai-generate-text').each(function(){
                            var data_object = $(this).closest('.wtai-loading-metabox');
                            var type = $(this).data('type');

                            var doRender = true;
                            if( renderType != '' && renderType != type ){
                                doRender = false;
                            }

                            if( doRender ){                        
                                $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').addClass('generated');

                                if ( data_object.find('.wtai-api-data-'+type).find('.wtai-text-count-details').length > 0 ){
                                    data_object.find('.wtai-api-data-'+type).find('.wtai-text-count-details').remove();
                                }

                                if ( data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details').length > 0 ){
                                    data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details').remove();
                                }
                                var texthtml = '';
                                var overlimithtml = '';
                                var textvaluehtml = '';
                                var overlimitvaluehtml = '';
                                var generatedText = '';
                                var wpGeneratedText = '';
                                var id = '';

                                switch( type ){
                                    case 'category_description':
                                        id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');

                                        if( tinymce.get(id) ) {
                                            tinymce.get(id).setContent( res.result[type] );
                                        }
                                        generatedText = res.result[type];
                                        wpGeneratedText = res.result[type+'_value'];

                                        updateProductGridGenerateSave(id); 
                                        updateHiddentext(id);                                   
                                        break;
                                    default:
                                        id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');

                                        if( tinymce.get(id) ){                                    
                                            tinymce.get(id).setContent( res.result[type].replace(/\n/g, '<br>') );
                                            
                                            generatedText = res.result[type].replace(/\n/g, '<br>');
                                            generatedText = wtaiRemoveLastBr( generatedText );

                                            //wpGeneratedText = res.result[type+'_value'].replace(/\n/g, '<br>');
                                            wpGeneratedText = res.result[type+'_value'];
                                            wpGeneratedText = wtaiRemoveLastBr( wpGeneratedText );

                                            updateHiddentext(id);

                                            data_object.find('.wtai-api-data-'+type).prop('disabled', false );
                                            var count = res.result[type+'_string_count']+'/'+WTAI_OBJ.text_limit[type];
                                            count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                            texthtml = texthtml+count+' | ';
                                            if ( parseInt(res.result[type+'_string_count']) > parseInt(WTAI_OBJ.text_limit[type]) ){
                                                overlimithtml = 'over_limit';
                                            }
                                            var count = res.result[type+'_value_string_count']+'/'+WTAI_OBJ.text_limit[type];
                                            count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                            textvaluehtml = textvaluehtml+count+' | ';
                                            if ( parseInt(res.result[type+'_value_string_count']) > parseInt(WTAI_OBJ.text_limit[type]) ){
                                                overlimitvaluehtml = 'over_limit';
                                            }
                                        }
                                        
                                        break;
                                }

                                var words = res.result[type+'_words_count'];
                                words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                                texthtml = texthtml+words;
                                
                                if( data_object.find('.wtai-text-count-details').length <= 0 ) {
                                    data_object.find('.wtai-api-data-'+type).after('<span class="wtai-text-count-details '+overlimithtml+'"><span class="wtai-char-counting">'+texthtml+'</span></span>');
                                } else {
                                    data_object.find('.wtai-api-data-'+type).parent().find('.wtai-text-count-detailsg').addClass(overlimithtml);
                                    data_object.find('.wtai-api-data-'+type).parent().find('.wtai-char-counting').html(texthtml);

                                }

                                typeCountMessage( type, generatedText );

                                var words = res.result[type+'_value_words_count'];
                                words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                                textvaluehtml = textvaluehtml+words;
                                
                                if( !data_object.find('.wtai-api-data-'+type+'_value').parent().find('.wtai-text-count-details') ) {
                                    
                                    data_object.find('.wtai-api-data-'+type+'_value').parent().append('<span class="wtai-text-count-details '+overlimitvaluehtml+'"><span class="wtai-char-counting">'+textvaluehtml+'</span></span>');
                        
                                }
                                
                                data_object.find('.wtai-api-data-'+type+'_id').val(res.result[type+'_id']);
                                data_object.find('.wtai-api-data-'+type+'_value').html(wpGeneratedText);
                                
                                data_object.removeClass('wtai-disabled-click');
                                data_object.removeClass('wtai-loading-metabox');

                                if( tinymce.get(id) ) {
                                    addHighlightKeywordsbyFieldOnKeyup(id);      
                                    var editor = tinymce.get(id);

                                    setTimeout(function(){  
                                        editor.contentWindow.scrollTo(0, 0);
                                    }, 200);
                                }

                                var last_activity = res.result[type+'_last_activity'];
                                data_object.find('.wtai-transferred-status-label').hide();
                                data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');

                                //handle for transfer button
                                if( generatedText.trim() != '' ){
                                    if( last_activity != 'transfer' ){
                                        if( last_activity != '' ){
                                            data_object.find('.wtai-transferred-status-label').show();
                                            data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                            data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                        }
                                        else{
                                            if( wpGeneratedText.trim() !== generatedText.trim() ){
                                                data_object.find('.wtai-transferred-status-label').show();
                                                data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                                data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                            }
                                        }
                                    }
                                    else{
                                        if( wpGeneratedText.trim() == '' ){
                                            data_object.find('.wtai-transferred-status-label').show();
                                            data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                            data_object.find('.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn').addClass('wtai-has-data-to-transfer');
                                        }
                                    }
                                }
                            }
                        });

                        updateToolTipForTransferSingleButton( 1 );
                        bulk_transfer_button_behavior(); //state for transfer bulk button single

                        if( reloadstats ){
                            getKeywordOverallDensity();
                            maybeDisableBulkButtons();
                        }
                    }
                    else{
                        var post_ids = [category_id];

                        if( bulkRemoveDisable == 1 ){                    
                            $('#wtai-table-list-' + category_id).removeClass('wtai-processing');

                            setTimeout(function(){                        
                                $.each(post_ids, function(index, post_id ) {
                                    $('.toplevel_page_write-text-ai')
                                        .find('#wtai-table-list-'+post_id)
                                        .find('.wtai-cwe-selected')
                                        .prop('disabled', false );
                                });
                            }, 300);
                        }
                                
                        if( bulkRemoveDisable == 1 ){ 
                            $('#wtai-table-list-' + category_id).find('button.transfer_feature').removeClass('wtai-disabled-button');
                        }

                        $('.wtai-global-loader').removeClass('wtai-is-active');
                    }

                    //render data
                    var fields = ['page_title', 'page_description', 'category_description', 'open_graph' ];
                    $.each(fields, 
                        function( index, fieldName ){
                            if( $('#wtai-table-list-' + category_id).length ){
                                $('#wtai-table-list-' + category_id).find('.column-wtai_' + fieldName).attr('data-text', res.result[fieldName] );
                                $('#wtai-table-list-' + category_id).find('.column-wtai_' + fieldName).html( res.result[fieldName + '_trimmed'] );

                                if( $('.wtai-show-comparison #wtai-comparison-cb').is(':checked') ){
                                    if( res.result[fieldName + '_tooltip_enabled'] == '1' && wtaiRemoveHtmlTags( res.result[fieldName] ) != res.result[fieldName + '_trimmed'] ){
                                        if( $('#wtai-table-list-' + category_id).find('.wtai_'+fieldName).hasClass( 'tooltip_hover' ) == false ){
                                            $('#wtai-table-list-' + category_id).find('.wtai_'+fieldName).addClass('tooltip_hover');

                                            $('#wtai-table-list-' + category_id).find('.wtai_'+fieldName).tooltipster(tooltipster_var);

                                            $('#wtai-table-list-' + category_id).find('.column-'+fieldName).addClass('tooltip_hover');
                                            $('#wtai-table-list-' + category_id).find('.column-'+fieldName).tooltipster(tooltipster_var);
                                        }
                                    }
                                }
                            }
                        }
                    );

                    //autoupdate grid from edit generate
                    $('#wtai-table-list-' + category_id).find('.column-wtai_generate_date').html( res.result['generate_date'] );
                }
            }
        });  
    }    

    function updateProductGridGenerateSave(id) {
        var pid = $('#wtai-edit-post-id').attr('value');
        var content = wp.editor.getContent(id); // Visual tab is active;

        var content = wp.editor.getContent(id); // Assuming you have the correct ID
        var temporaryElement = document.createElement('div');
        temporaryElement.innerHTML = content;
        var textContent = temporaryElement.textContent || temporaryElement.innerText; // Extract text content without HTML tags
        var words = textContent.split(' '); // Split into individual words
        var first15Words = words.slice(0, 15); // Extract the first 15 words
        var shortenedParagraph = first15Words.join(' ') + '...'; // Join the words back into a shortened paragraph

        var type = id.replace('wtai-wp-field-input-','');
        $('#wtai-table-list-' + pid).find('.column-wtai_' + type).html(shortenedParagraph);
        if( type != 'page_title') {
            $('#wtai-table-list-' + pid).find('.column-wtai_' + type).attr('data-text',content);
        }
    }

    $(document).on('click', '#wtai-product-generate-completed.wtai-loader-generate .wtai-loading-button-action', function(){
        popupGenerateCompleted('hide');
    });

    $(document).on('click', '#wtai-product-generate-completed-bulk.wtai-loader-generate-bulk .wtai-loading-button-action', function(){
        popupGenerateCompleted('hide');
    });

    $(document).on('click', '.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn', function(){
        $('#wtai-restore-global-setting-completed').hide();

        var type = $(this).attr('data-type');

        if( $(this).hasClass('wtai-disabled-button') ){
            return;
        }

        if( $('#message.error').length ) {
            $('#message.error').remove();
        }

        popupGenerateCompleted('hide');

        $('.wtai-ai-logo').addClass('wtai-hide');
        $('.wtai-global-loader').addClass('wtai-is-active');

        var values = {};
        var ids = {};
        var fieldsToProcess = [];
        $('#postbox-container-2').find('.wtai-metabox-' + type).each( function(){
            var data_object = $(this);

            var saveField = true;
            var id = '';
            if( saveField ){
                var type = data_object.find('.wtai-checkboxes').data('type');
                var source_val = '';
                data_object.find('.wtai-columns-1').addClass('wtai-loading-state');
                data_object.find('.wtai-single-button-text').addClass('disabled');
                if ( data_object.find('.wtai-columns-3').find('.wp-editor-wrap').length > 0 ){
                    data_object.find('.wtai-columns-3').find('.wp-editor-wrap').addClass('wtai-loading-state');
                } else if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                    data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').addClass('wtai-loading-state');
                } else {
                    data_object.find('.wtai-columns-3').find('textarea').prop('disabled', true );
                }

                if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                    id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                    source_val =  tinymce.get(id).getContent( { format: 'raw' } ); // Visual tab is active
                } else if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                    id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                    source_val =  wp.editor.getContent(id); // Visual tab is active
                } else if ( data_object.find('.wtai-columns-3').find('textarea').length > 0 ){
                    source_val =  data_object.find('.wtai-columns-3').find('textarea').val();
                }

                var hasChange = false;
                if( data_object.find('.wtai-single-transfer-btn').hasClass('wtai-disabled-button') == false ){
                    hasChange = true;
                    fieldsToProcess.push( type );
                }

                updateHiddentext(id);

                if ( hasChange && source_val && data_object.find('.wtai-columns-3').find('input[type="hidden"]').attr('value') ) {
                    ids[type] = data_object.find('.wtai-columns-3').find('input[type="hidden"]').attr('value');
                    values[type] = source_val;

                    data_object.addClass('wtai-disabled-click');
                }
            }
        }); 

        $('.wtai-slide-right-text-wrapper .wtai-close').addClass('disabled');
        $('.wtai-slide-right-text-wrapper .wtai-button-prev').addClass('disabled-nav');
        $('.wtai-slide-right-text-wrapper .wtai-button-next').addClass('disabled-nav');

        var date = new Date();
        var offset = date.getTimezoneOffset();
        var api_publish = 1;
        var submittype = 'bulk_transfer';
        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_or_save_category_text',
                browsertime : offset,
                category_id  :  $('#wtai-edit-post-id').attr('value'),
                data_ids    : ids, 
                data_fields : values,
                publish: api_publish,
                submittype: submittype,
                wtai_nonce: wtai_nonce,
            },
            success: function( data ){
                if( data.access ){
                    if ( data.message ) {
                        if ( $('.wtai-edit-product-line' ).find('.wtai-error-notice').length > 0  ){
                            $('.wtai-edit-product-line' ).find('.wtai-error-notice').remove();
                        }

                        if ( data.message == 'expire_token' ){
                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message').remove();
                            }
                            $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        } else {
                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message').remove();
                            }
                            $('<div id="message" class="wtai-error-notice error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        }

                        $('.wtai-slide-right-text-wrapper').animate({ scrollTop: 0 }, 'fast');
                    } else {
                        if ( data.results ){
                            if ( submittype == 'bulk_transfer' ) {

                                $.each( data.results[ $('#wtai-edit-post-id').attr('value') ], function ( post_field_type, post_field_value ) {
                                    var textcount = '';
                                    var class_limit = '';
                                    switch( post_field_type ){
                                        case 'category_description':
                                            
                                            break;
                                        default:
                                            
                                            var source_string_count =  post_field_value['count'];
                                            var count = source_string_count+'/'+WTAI_OBJ.text_limit[post_field_type];
                                            count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                            if ( parseInt(source_string_count) > parseInt(WTAI_OBJ.text_limit[post_field_type]) ){
                                                class_limit = 'over_limit';
                                            }
                                            textcount = textcount+count+' | ';
                                            break;
                                    }

                                    var source_word_count =  post_field_value['words'];
                                    var words = source_word_count;
                                    words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                                    textcount = textcount+words;

                                    var content = data.results[ $('#wtai-edit-post-id').attr('value') ][post_field_type]['text'];
                                    content = $('<div>').html(content);
                                    content.find('.wtai-highlight, .wtai-highlight2').contents().unwrap();
                                    var htmlContent = content.html();

                                    var generatedText = '';
                                    if( post_field_type == 'category_description' ){
                                        generatedText = htmlContent;
                                    }
                                    else{
                                        //generatedText = htmlContent.replace(/\n/g, '<br>');
                                        generatedText = htmlContent;
                                        generatedText = wtaiRemoveLastBr( generatedText );
                                    }

                                    var generatedCharCount = 0;
                                    var generatedCharCountCredit = 0;
                                    if( post_field_type == 'category_description' ){
                                        generatedCharCount = data.results[ $('#wtai-edit-post-id').attr('value') ][post_field_type]['string_count'];
                                        generatedCharCountCredit = data.results[ $('#wtai-edit-post-id').attr('value') ][post_field_type]['string_count_credit'];
                                    }
                                    else{
                                        generatedCharCount = $('#wtai-product-details-'+post_field_type ).find('.wtai-generate-value-wrapper .wtai-char-counting .wtai-char-count').attr('data-count');
                                        generatedCharCountCredit = generatedCharCount;
                                    }
                                    
                                    var generatedWordCount = $('#wtai-product-details-'+post_field_type ).find('.wtai-generate-value-wrapper .wtai-char-counting .word-count').attr('data-count');

                                    $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-current-text').find('p').html( generatedText );
                                    $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.wtai-char-count').html( generatedCharCount );
                                    $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.wtai-char-count').attr( 'char-count-credit', generatedCharCountCredit );
                                    $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.word-count').html( generatedWordCount );
                                 
                                    updateProductGridTransfer(post_field_type,htmlContent);
                                    
                                    var id = '';
                                    if ( $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                        id = $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                    } else if ( $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                        id = $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                    }    

                                    if( id != '' ){
                                        updateHiddentext(id);
                                    }
                                });

                                rewrite_button_state_behavior();
                            } 

                            $.each( data.results, function( post_id, post_fields ){
                                $.each(post_fields, function( post_field, post_values ) {
                                    var table_row_object = $('#wtai-table-list-'+post_id);
                                    if ( post_field == 'generate_date' || post_field == 'transfer_date' ){
                                        table_row_object.find('.wtai_'+post_field).html(post_values);
                                    } else {
                                        table_row_object.find('.'+post_field).html(post_values['trim']);
                                        table_row_object.find('.'+post_field).attr('data-text', post_values['text']);
                                        if (post_field.indexOf('wtai_') !== -1) {
                                            var nonwta = post_field.replace('wtai_', '');
                                            if ( table_row_object.find('.transfer_'+nonwta).find('.transfer_feature').hasClass('wtai-disabled-button') ){
                                                table_row_object.find('.transfer_'+nonwta).find('.transfer_feature').removeClass('wtai-disabled-button');
                                            }
                                        } else {
                                            var nonwta = post_field;
                                        }

                                        //lets reenable tooltip
                                        if( post_values['trim'] != post_values['text'] ){                                       
                                            table_row_object.find('.wtai_'+post_field).html(post_values['trim']);
                                            table_row_object.find('.wtai_'+post_field).attr('data-text', post_values['text']);

                                            table_row_object.find('.column-'+post_field).html(post_values['trim']);
                                            table_row_object.find('.column-'+post_field).attr('data-text', post_values['text']);

                                            if( $('.wtai-show-comparison #wtai-comparison-cb').is(':checked') ){    
                                                if( table_row_object.find('.wtai_'+post_field).hasClass( 'tooltip_hover' ) == false ){
                                                    table_row_object.find('.wtai_'+post_field).addClass('tooltip_hover');

                                                    table_row_object.find('.wtai_'+post_field).tooltipster(tooltipster_var);

                                                    table_row_object.find('.column-'+post_field).addClass('tooltip_hover');
                                                    table_row_object.find('.column-'+post_field).tooltipster(tooltipster_var);
                                                }
                                            }
                                        }
                                    }     
                                });
                            });
                        }
                    }

                    var is_premium = data.is_premium;
                    handle_single_product_edit_state( is_premium );
                    handle_density_premium_state( is_premium );
                } else {
                    var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                    var class_name = 'error notice ';
                    if ( message ){
                        $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                    }
                }  

                $('#postbox-container-2').find('.wtai-metabox-' + type).each( function(){
                    var data_object = $(this);
                    data_object.find('.wtai-columns-1').removeClass('wtai-loading-state');
                    data_object.find('.wtai-single-button-text').removeClass('disabled');
                    if ( data_object.find('.wtai-columns-3').find('.wp-editor-wrap').length > 0 ){
                        data_object.find('.wtai-columns-3').find('.wp-editor-wrap').removeClass('wtai-loading-state');
                    } else {
                        data_object.find('.wtai-columns-3').find('textarea').prop('disabled', false );
                    }

                    data_object.removeClass('wtai-disabled-click');

                    data_object.find('.wtai-transferred-status-label').hide();
                    data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    data_object.find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');

                    // hide review status
                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( '' );
                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html('');
                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').addClass('hidden');

                    updateToolTipForTransferSingleButton( 1 );

                    
                }); 

                $('#postbox-container-2').find('.wtai-metabox' + type).each( function(){
                    var data_object = $(this);
                    if ( data_object.hasClass('wtai-metabox-update') ) {
                        data_object.removeClass('wtai-metabox-update');
                    }
                }); 

                after_transfer_review_state();

                bulk_transfer_button_behavior();
                handle_save_button_state();
            }
        });         
    });

    function updateProductGridTransfer(type,content) {
        var pid = $('#wtai-edit-post-id').attr('value');
        $('#wtai-table-list-' + pid).find('.column-' + type).html(content);
        if( type != 'page_title') {
            $('#wtai-table-list-' + pid).find('.column-' + type).attr('data-text',content);
        }
    }

    function after_transfer_review_state(){
        var transfer_ctr = 0;
        var field_ctr = 0;
        $('#postbox-container-2').find('.wtai-metabox').each( function(){
            var data_object = $(this);
            if( data_object.find('.wtai-api-data-text-id').val() != '' && data_object.find('.wtai-transferred-status-label').is(':visible') == false ){
                transfer_ctr++;
            }

            field_ctr++;
        }); 

        if( transfer_ctr == field_ctr ){
            //check mark as reviewed
            $('.wtai-review-check').prop('checked', true);
            $('.wtai-review-check').prop('disabled', true);
            $('.wtai-review-check').closest('.wtai-review-wrapper').addClass('wtai-review-wrapper-disabled');
        }
        else{
            var mark_review_state = $('.wtai-review-check').is(':checked');
            if( transfer_ctr > 0 && mark_review_state == true ){
                //check mark as reviewed
                $('.wtai-review-check').prop('checked', true);
            } else {
                //check mark as reviewed
                $('.wtai-review-check').prop('checked', false);
            }
            
            $('.wtai-review-check').prop('disabled', false);
            $('.wtai-review-check').closest('.wtai-review-wrapper').removeClass('wtai-review-wrapper-disabled');
        }
    }

    $(document).on('click', '.wtai-bulk-button-text', function(e) {
        var button = $(this);
        
        if ( button.hasClass('disabled-during-generation') ){
            return;
        }
        
        $('#wtai-restore-global-setting-completed').hide();
    
        if( $('#message-text-type-error.error').length ) {
            $('#message-text-type-error.error').remove();
        }
        if ( ! button.hasClass('disabled') ){
            $('.wtai-slide-right-text-wrapper .wtai-close').addClass('disabled');
            $('.wtai-slide-right-text-wrapper .wtai-button-prev').addClass('disabled-nav');
            $('.wtai-slide-right-text-wrapper .wtai-button-next').addClass('disabled-nav');
    
            popupGenerateCompleted('hide');
    
            /*Checking Media / Shortcode exist*/
            var submittype = button.data('typesave');
            $('.wtai-bulk-button-text').addClass('disabled');
            $('.wtai-ai-logo').addClass('wtai-hide');
            $('.wtai-global-loader').addClass('wtai-is-active');
    
            var values = {};
            var ids = {};
            var fieldsToProcess = [];
            var hasDataToProcess = false;
            $('#postbox-container-2').find('.wtai-metabox').each( function(){
                var data_object = $(this);
    
                var saveField = true;
                if ( submittype == 'bulk_transfer' ){
                    if( data_object.find('.wtai-checkboxes').is(':checked') == false ){
                        saveField = false;
                    }
                }
    
                if( saveField ){
                    var type = data_object.find('.wtai-checkboxes').data('type');
                    var source_val = '';
                    data_object.find('.wtai-columns-1').addClass('wtai-loading-state');
                    data_object.find('.wtai-single-button-text').addClass('disabled');
                    if ( data_object.find('.wtai-columns-3').find('.wp-editor-wrap').length > 0 ){
                        data_object.find('.wtai-columns-3').find('.wp-editor-wrap').addClass('wtai-loading-state');
                    } else if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                        data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').addClass('wtai-loading-state');
                    } else {
                        data_object.find('.wtai-columns-3').find('textarea').prop('disabled', true );
                    }
    
                    var id = '';
                    if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                        id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                        source_val =  wp.editor.getContent(id); // Visual tab is active
                    } else if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                        id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                        source_val =  wp.editor.getContent(id); // Visual tab is active
                    } else if ( data_object.find('.wtai-columns-3').find('textarea').length > 0 ){
                        source_val =  data_object.find('.wtai-columns-3').find('textarea').val();
                    }
    
                    hasChange = false;
                    if ( submittype == 'bulk_transfer' ){
                        hasChange = true;
                        fieldsToProcess.push( type );
                    }
                    else{
                        hasChange = true;
    
                        var source_newvalue = data_object.find('.wtai-data-new-text').text();
                        var source_newvalue_stripped = wtaiRemoveLastPipe( data_object.find('.wtai-data-new-text').text() );
                        var source_origvalue = data_object.find('.wtai-data-orig-text').text();
                        var current_value = data_object.find('.wtai-current-value p.wtai-text-message').text() + '|';
    
                        if( wtaiAreEqualIgnoringWhitespaceAndNewline( source_newvalue, current_value ) == false && source_newvalue_stripped != '' ){
                            fieldsToProcess.push( type );
                        }
                    }
    
                    updateHiddentext(id);
    
                    if ( source_val == '' ){
                        hasChange = false;
                    }
    
                    if ( hasChange && data_object.find('.wtai-columns-3').find('input[type="hidden"]').attr('value') ) {
                        ids[type] = data_object.find('.wtai-columns-3').find('input[type="hidden"]').attr('value');
                        values[type] = source_val;
    
                        data_object.addClass('wtai-disabled-click');
    
                        hasDataToProcess = true;
                    }
                }           
            }); 
    
            var date = new Date();
            var offset = date.getTimezoneOffset();
            
            var api_publish = 0;
            var do_process = false;
            if ( submittype == 'bulk_transfer' ){
                api_publish = 1;
    
                if( $('#postbox-container-2 .wtai-metabox .wtai-checkboxes:checked').length > 0 ){
                    do_process = true;
                }
            } else {
                api_publish = 0;
    
                do_process = true;
            }
            
            if( hasDataToProcess ){
                do_process = true;
            }
         
            //Save / Transfer normal text types
            //temp disable for testing
            if( do_process ){      
                var wtai_nonce = get_wp_nonce();
    
                $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: {
                    action: 'wtai_transfer_or_save_category_text',
                    browsertime : offset,
                    category_id  :  $('#wtai-edit-post-id').attr('value'),
                    data_ids    : ids, 
                    data_fields : values,
                    publish: api_publish,
                    submittype: submittype,
                    wtai_nonce: wtai_nonce,
                },
                success: function( data ){
                    if( data.access ){
                        if ( data.message ) {
                            if ( data.message == 'expire_token' ){
                                if ( $('.wtai-edit-product-line' ).find('#message-text-type-error').length > 0  ){
                                    $('.wtai-edit-product-line' ).find('#message-text-type-error').remove();
                                }
                                $('<div id="message-text-type-error" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                            } else {
                                if ( $('.wtai-edit-product-line' ).find('#message-text-type-error').length > 0  ){
                                    $('.wtai-edit-product-line' ).find('#message-text-type-error').remove();
                                }
                                $('<div id="message-text-type-error" class="error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                            }
    
                            $('.wtai-slide-right-text-wrapper').animate({ scrollTop: 0 }, 'fast');
                        } else {
                                if ( data.results ){
                                    if ( submittype == 'bulk_transfer' ) {
                                        $('#major-publishing-actions').find('.wtai-bulk-button-text ').removeClass('wtai-proceed');
    
                                        $.each( data.results[ $('#wtai-edit-post-id').attr('value') ], function ( post_field_type, post_field_value ) {
                                            var textcount = '';
                                            var class_limit = '';
                                            switch( post_field_type ){
                                                case 'category_description':
                                                    break;
                                                default:
                                                    
                                                    var source_string_count =  post_field_value['count'];
                                                    var count = source_string_count+'/'+WTAI_OBJ.text_limit[post_field_type];
                                                    count = '<span class="wtai-char-count">'+WTAI_OBJ.char.replace('%char%', count)+'</span>';
                                                    if ( parseInt(source_string_count) > parseInt(WTAI_OBJ.text_limit[post_field_type]) ){
                                                        class_limit = 'over_limit';
                                                    }
                                                    textcount = textcount+count+' | ';
                                                    break;
                                            }
    
                                            var source_word_count =  post_field_value['words'];
                                            var words = source_word_count;
                                            words = '<span class="word-count">'+WTAI_OBJ.words.replace('%words%', words)+'</span>';
                                            textcount = textcount+words;
    
                                            var content = data.results[ $('#wtai-edit-post-id').attr('value') ][post_field_type]['text'];
                                            content = $('<div>').html(content);
                                            content.find('.wtai-highlight, .wtai-highlight2').contents().unwrap();
                                            var htmlContent = content.html();
    
                                            var generatedText = '';
                                            if( post_field_type == 'category_description' ){
                                                generatedText = htmlContent;
                                            }
                                            else{
                                                //generatedText = htmlContent.replace(/\n/g, '<br>');
                                                generatedText = htmlContent;
                                                generatedText = wtaiRemoveLastBr( generatedText );
                                            }
    
                                            var generatedCharCount = 0;
                                            if( post_field_type == 'category_description' ){
                                                generatedCharCount = data.results[ $('#wtai-edit-post-id').attr('value') ][post_field_type]['string_count'];
                                            }
                                            else{
                                                generatedCharCount = $('#wtai-product-details-'+post_field_type ).find('.wtai-generate-value-wrapper .wtai-char-counting .wtai-char-count').attr('data-count');
                                            }
                                            
                                            var generatedWordCount = $('#wtai-product-details-'+post_field_type ).find('.wtai-generate-value-wrapper .wtai-char-counting .word-count').attr('data-count');
    
                                            $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-current-text').find('p').html( generatedText );
                                            $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.wtai-char-count').html( generatedCharCount );
                                            $('#wtai-product-details-'+post_field_type ).find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.word-count').html( generatedWordCount );
                                        
                                            updateProductGridTransfer(post_field_type,htmlContent);   
                                            
                                            var id = '';
                                            if ( $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                                id = $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                            } else if ( $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                                id = $('#wtai-product-details-'+post_field_type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                            }    
    
                                            if( id != '' ){
                                                updateHiddentext(id);
                                            }
                                        });
    
                                        rewrite_button_state_behavior();
    
                                        //check mark as reviewed
                                        $('.wtai-review-check').prop('checked', true);
                                        $('.wtai-review-check').prop('disabled', true);
                                        $('.wtai-review-check').closest('.wtai-review-wrapper').addClass('wtai-review-wrapper-disabled');
    
                                        //disable save button
                                        $('#save-action .wtai-bulk-button-text').addClass('disabled');
                                    }
                                    else{
                                       
                                    } 
    
                                    if(submittype == 'bulk_generated'){
                                        bulk_transfer_button_behavior();

                                        $('.wtai-bulk-button-text').removeClass('disabled');
    
                                        //check mark as reviewed
                                        $('.wtai-review-check').prop('checked', false);
                                        $('.wtai-review-check').prop('disabled', false);
                                        $('.wtai-review-check').closest('.wtai-review-wrapper').removeClass('wtai-review-wrapper-disabled');
    
                                        //disable save button
                                        $('#save-action .wtai-bulk-button-text').addClass('disabled');
                                    }
    
                                    $.each( data.results, function( post_id, post_fields ){
                                        $.each(post_fields, function( post_field, post_values ) {
    
                                            var table_row_object = $('#wtai-table-list-'+post_id);
                                            if ( post_field == 'generate_date' || post_field == 'transfer_date' ){
                                                table_row_object.find('.wtai_'+post_field).html(post_values);
                                            } else {
                                                table_row_object.find('.'+post_field).html(post_values['trim']);
                                                table_row_object.find('.'+post_field).attr('data-text', post_values['text']);
                                                if (post_field.indexOf('wtai_') !== -1) {
                                                    var nonwta = post_field.replace('wtai_', '');
                                                    
                                                    if ( table_row_object.find('.transfer_'+nonwta).find('.transfer_feature').hasClass('wtai-disabled-button') ){
                                                        table_row_object.find('.transfer_'+nonwta).find('.transfer_feature').removeClass('wtai-disabled-button');
                                                    }
                                                } else {
                                                    var nonwta = post_field;
                                                }
    
                                                //lets reenable tooltip
                                                table_row_object.find('.wtai_'+post_field).html(post_values['trim']);
                                                table_row_object.find('.wtai_'+post_field).attr('data-text', post_values['text']);
    
                                                table_row_object.find('.column-'+post_field).html(post_values['trim']);
                                                table_row_object.find('.column-'+post_field).attr('data-text', post_values['text']);
    
                                                if( post_values['trim'] != post_values['text'] ){
                                                    if( $('.wtai-show-comparison #wtai-comparison-cb').is(':checked') ){
    
                                                        if( table_row_object.find('.wtai_'+post_field).hasClass( 'tooltip_hover' ) == false ){
                                                            table_row_object.find('.wtai_'+post_field).addClass('tooltip_hover');
    
    
                                                            table_row_object.find('.wtai_'+post_field).tooltipster(tooltipster_var);
    
                                                            table_row_object.find('.column-'+post_field).addClass('tooltip_hover');
                                                            table_row_object.find('.column-'+post_field).tooltipster(tooltipster_var);
                                                        }
                                                    }
                                                }
                                            }
                                        });
                                    });
                                }
                            }
    
                            var is_premium = data.is_premium;
                            handle_single_product_edit_state( is_premium );
                            handle_density_premium_state( is_premium );
                            
                        } else {
                            var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                            var class_name = 'error notice ';
                            if ( message ){
                                $('<div id="message-text-type-error" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                            }
                        }  
    
                        $('#postbox-container-2').find('.wtai-metabox').each( function(){
                            var data_object = $(this);
                            var type = $(this).data('type');
                            data_object.find('.wtai-columns-1').removeClass('wtai-loading-state');
                            data_object.find('.wtai-single-button-text').removeClass('disabled');
                            if ( data_object.find('.wtai-columns-3').find('.wp-editor-wrap').length > 0 ){
                                data_object.find('.wtai-columns-3').find('.wp-editor-wrap').removeClass('wtai-loading-state');
                            } else {
                                data_object.find('.wtai-columns-3').find('textarea').prop('disabled', false );
                            }
    
                            data_object.removeClass('wtai-disabled-click');
                           
                            var id = '';
                            var source_val = '';
                            if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                source_val =  wp.editor.getContent(id); // Visual tab is active
                            } else if ( data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                source_val =  wp.editor.getContent(id); // Visual tab is active
                            } else if ( data_object.find('.wtai-columns-3').find('textarea').length > 0 ){
                                source_val =  data_object.find('.wtai-columns-3').find('textarea').val();
                            }
    
                            if( data_object.find('.wtai-checkboxes').is(':checked') == true ){                        
                                if ( submittype != 'bulk_transfer' ){
                                    if( fieldsToProcess.indexOf(type) != -1 ){
                                        data_object.find('.wtai-transferred-status-label').show();
                                        data_object.find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                    }
                                    else{
                                        data_object.find('.wtai-transferred-status-label').hide();
                                        data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                        data_object.find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');
                                    }
    
                                    updateToolTipForTransferSingleButton( 1 );
                                }
                                else{
                                    data_object.find('.wtai-transferred-status-label').hide();
                                    data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                    data_object.find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');
                                    
                                    // hide review status flags
                                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( '' );
                                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html('');
                                    data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').addClass('hidden');

                                    updateToolTipForTransferSingleButton( 1 );
                                }
                            }
                            else{
                                if ( submittype != 'bulk_transfer' ){
                                    if( fieldsToProcess.indexOf(type) != -1 && source_val != '' ){
                                        data_object.find('.wtai-transferred-status-label').show();
                                        data_object.find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                                    }
                                    else{
                                        data_object.find('.wtai-transferred-status-label').hide();
                                        data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                        data_object.find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');
                                    }
        
                                    updateToolTipForTransferSingleButton( 1 );
                                }
                            }
                        }); 
    
                        //check if we should disable review after transfer
                        if ( submittype == 'bulk_transfer' ){
                            after_transfer_review_state();
                        }
    
                        $('#postbox-container-2').find('.wtai-metabox').each( function(){
                            var data_object = $(this);
                            if ( data_object.hasClass('wtai-metabox-update') ) {
                                data_object.removeClass('wtai-metabox-update');
                            }
    
                            data_object.removeClass('wtai-disabled-click');    
                        }); 
    
                        //bulk transfer button behavior
                        //handle_single_transfer_button_state();
                        bulk_transfer_button_behavior();
                    }
                }); 
            }
        }
        e.preventDefault();
    });

    window.wtaiGetHistoryGlobalPopin = function() {
        if ( ! $('.wtai-table-list-wrapper').find('.wtai-history-global').hasClass('wtai-pending') ){
            $('.wtai-table-list-wrapper').find('.wtai-history-global').addClass('wtai-pending');
            if ( $('.wtai-table-list-wrapper').hasClass('wtai-history-global-open') ){
                if ( $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-wrapper').length > 0 ){
                    $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-wrapper').remove();
                }
                setTimeout(function() {
                    $('.wtai-table-list-wrapper').removeClass('wtai-history-global-open'); 
                    $('.wtai-btn-close-history-global').css('visibility','hidden');
                    $('body').removeClass('wtai-history-global-open');   
                    $('.wtai-table-list-wrapper').find('.wtai-history-global').removeClass('wtai-active');
                    $('.wtai-table-list-wrapper').find('.wtai-history-global').removeClass('wtai-pending');
                    $('.wtai-table-list-wrapper')
                        .find('.wtai-history')
                        .find('.wtai-history-content')
                        .find('.wtai-history-wrapper')
                        .remove();
                    $('.wtai-table-list-wrapper')
                        .find('.wtai-history')
                        .find('.wtai-history-filter')
                        .find('.wtai-history-author-select')
                        .find('option:not(.wtai-option-author-default)')
                        .remove();

                    //lets clear out the dates
                    $('.wtai-history.wtai-history-global .wtai-history-filter .wtai-history-date-input').val('');
                }, 300);
            } else {  
                $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                if ( $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                    $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                }
                var date = new Date();
                var offset = date.getTimezoneOffset();
                var wtai_nonce = get_wp_nonce();

                if( $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

               setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_global_category_history',
                            pageSize: WTAI_OBJ.pageSize, 
                            browsertime: offset,
                            wtai_nonce: wtai_nonce,
                        },
                        beforeSend: function(){
                            $('.wtai-table-list-wrapper').addClass('wtai-history-global-open');
                            
                            $('.wtai-global-loader').addClass('wtai-is-active');

                            setTimeout(function() {
                                $('body').addClass('wtai-history-global-open');   
                                $('.wtai-btn-close-history-global').css('visibility','visible');
                                $('.wtai-table-list-wrapper').find('.wtai-history-global').addClass('wtai-active');
                            }, 450);
                            
                            //remove select options except default
                            $('.wtai-table-list-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-filter')
                            .find('.wtai-history-author-select')
                            .find('option:not(.wtai-option-author-default)')
                            .remove();

                        },
                        success: function(data) {
                            
                            if (  $('.wtai-table-list-wrapper').hasClass('wtai-history-global-open')  ) {
                                if ( data.results && data.has_results == 'yes' ){
                                    var html = '';
                                    $.each(data.results, function(history_index, history_value) {
                                        html = html+'<div class="wtai-history-wrapper">';
                                        html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                                        html = html+'<div class="wtai-history-product-name"><a href="'+history_value['product_link']+'" class="wtai-cwe-action-button-history" data-id="'+history_value['product_id']+'" data-values="'+history_value['product_data_values']+'">'+history_value['product_name']+'</a><span class="dashicons dashicons-arrow-down-alt2"></span></div>';
                                        html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div></div><div class="wtai-history-content-container">';
                                        
                                        $.each(history_value['values'], function( index_key, value_details ) {
                                            var api_content_value = value_details['value'];
                                            api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();

                                            html = html+'<div class="wtai-history-content-row">';
                                                html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                                html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                            html = html+'</div>';
                                        });
                                        
                                        html = html+'</div></div>';
                                    });

                                    $('.wtai-history-content-corrector').html( '' );

                                    $('.wtai-table-list-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-content')
                                        .append(html);
                                    
                                }
                                else{
                                    $('.wtai-table-list-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-content')
                                        .append('<div class="wtai-no-history-found">' + WTAI_OBJ.noHistoryMessage + '</div>');
                                }
                                
                                if ( data.user_list ){
                                    var select_html = '';
                                    $.each(data.user_list, function(index, value ) {
                                        select_html = select_html+'<option value="'+value+'">'+value+'</option>';
                                    });
                                    $('.wtai-table-list-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-filter')
                                        .find('.wtai-history-author-select')
                                        .append(select_html);
                                }
    
                                if ( data.cont_token ){
                                    var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                                    $('.wtai-table-list-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-content')
                                        .append(token_html);
                                    
                                }
                                $('.wtai-table-list-wrapper').find('.wtai-history-global').removeClass('wtai-pending');    
                            } else {    
                               $('.wtai-global-loader').addClass('wtai-is-active');
                               $('.wtai-ai-logo').addClass('wtai-hide');
                                $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                                if ( $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                                    $('.wtai-table-list-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                                }
                            }   
                        }
                    });
                }, 300);
            }
            $('.wtai-table-list-wrapper').find('.wtai-history-global').removeClass('wtai-pending');
        }
    };

    $(document).on('click', '.wtai-history .wtai-history-header-container', function(e){
        $(this).closest('.wtai-history-wrapper').toggleClass('wtai-active');
        e.preventDefault();
    });

    $(document).on('click', '.wtai-history.wtai-history-global .wtai-history-filter-button', function(e){
        var event = $(this);
        if ( ! event.hasClass('disabled') ) {
            event.addClass('disabled');
            var date_from = '';
            if( event.closest('.wtai-history-filter-form').find('.wtai-history-date-from').find('.wtai-history-date-input').val() != '' ){
                date_from = event.closest('.wtai-history-filter-form').find('.wtai-history-date-from').find('.wtai-history-date-input').attr('date-format');
            }

            var date_to = '';
            if( event.closest('.wtai-history-filter-form').find('.wtai-history-date-to').find('.wtai-history-date-input').val() != '' ){
                date_to = event.closest('.wtai-history-filter-form').find('.wtai-history-date-to').find('.wtai-history-date-input').attr('date-format');
            }
            
            var author = event.closest('.wtai-history-filter-form').find('.wtai-history-date-author').find('.wtai-history-author-select').val();
            if ( date_from || date_to || author ) {
                var date = new Date();
                var offset = date.getTimezoneOffset();
                
                var wtai_nonce = get_wp_nonce();

                var data = {
                    action: 'wtai_global_category_history',
                    pageSize: WTAI_OBJ.pageSize,
                    browsertime:offset,
                    wtai_nonce:wtai_nonce,
                };
                if ( date_from ){
                    data['date_from'] = date_from;
                }

                if ( date_to ){
                    data['date_to'] = date_to;
                }

                if ( author ){
                    data['author'] = author;
                }

                if( $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: data,
                    beforeSend: function(){
                        event.closest('.wtai-history-filter-form').find('input').prop('disabled', true );
                        event.closest('.wtai-history-filter-form').find('select').prop('disabled', true );
                        event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                        if ( event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                            event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                        }
                        event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                        $('.wtai-global-loader').addClass('wtai-is-active');
                    },
                    success: function(data) {
                        var hasResults = false;

                        if ( data.results && data.has_results == 'yes' ){
                            var html = '';
                            $.each(data.results, function(history_index, history_value) {
                          
                                html = html+'<div class="wtai-history-wrapper">';
                                html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                                html = html+'<div class="wtai-history-product-name"><a href="'+history_value['product_link']+'" class="wtai-cwe-action-button-history" data-id="' + history_value['product_id'] + '" data-values="' + history_value['product_data_values'] + '">' + history_value['product_name'] + '</a><span class="dashicons dashicons-arrow-down-alt2"></span></div>';
                                html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div></div><div class="wtai-history-content-container">';
                                $.each(history_value['values'], function( index_key, value_details ) {
                                    var api_content_value = value_details['value'];
                                    api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();

                                    html = html+'<div class="wtai-history-content-row">';
                                        html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                        html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                    html = html+'</div>';
                                });
                                
                                html = html+'</div></div>';

                                hasResults = true;
                            });

                            $('.wtai-history-content-corrector').html( '' );

                            $('.wtai-table-list-wrapper')
                                .find('.wtai-history')
                                .find('.wtai-history-content')
                                .append(html);
                            
                        }
                        else{
                            $('.wtai-table-list-wrapper')
                                .find('.wtai-history')
                                .find('.wtai-history-content')
                                .append('<div class="wtai-no-history-found">' + WTAI_OBJ.noHistoryMessage + '</div>');
                        }

                        if ( data.cont_token && hasResults ){
                            var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                            $('.wtai-table-list-wrapper')
                                .find('.wtai-history')
                                .find('.wtai-history-content')
                                .append(token_html);
                        }
                       
                        event.closest('.wtai-history-filter-form').find('input').prop('disabled', false );
                        event.closest('.wtai-history-filter-form').find('select').prop('disabled', false );
                        event.removeClass('disabled');        
                    }
                });
            } else {
                event.removeClass('disabled');
            }
        }
        e.preventDefault();
    });

    $(document).on('click', '.wtai-history.wtai-history-global .wtai-token-readmore-btn',function(e){
        var event = $(this);
        if ( ! event.hasClass('disabled') ){
            var date = new Date();
            var offset = date.getTimezoneOffset();
            var wtai_nonce = get_wp_nonce();

            var data = {
                action: 'wtai_global_category_history',
                pageSize: WTAI_OBJ.pageSize, 
                browsertime: offset,
                wtai_nonce: wtai_nonce,
            };
            if ( event.data('date_from') ){
                data['date_from'] = event.data('date_from');
            }

            if ( event.data('date_to') ){
                data['date_to'] = event.data('date_to');
            }

            if ( event.data('author') ){
                data['author'] = event.data('author');
            }

            if ( event.data('token') ){
                data['continue_token'] = event.data('token');
            }
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data,
                beforeSend: function(){
                    event.addClass('disabled');
                    event.html(WTAI_OBJ.loading);
                    
                },
                success: function(data) {
                    event.remove();
                    if ( data.results ){
                        var html = '';
                        $.each(data.results, function(history_index, history_value) {
                            html = html+'<div class="wtai-history-wrapper">';
                            html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                            html = html+'<div class="wtai-history-product-name"><a href="'+history_value['product_link']+'" class="wtai-cwe-action-button-history" data-id="'+history_value['product_id']+'" data-values="'+history_value['product_data_values']+'">'+history_value['product_name']+'</a><span class="dashicons dashicons-arrow-down-alt2"></span></div>';
                            html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div></div><div class="wtai-history-content-container">';
                            $.each(history_value['values'], function( index_key, value_details ) {
                                var api_content_value = value_details['value'];
                                api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();

                                html = html+'<div class="wtai-history-content-row">';
                                    html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                    html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                html = html+'</div>';
                            });
                            
                            html = html+'</div></div>';
                        });

                        $('.wtai-history-content-corrector').html( '' );

                        $('.wtai-table-list-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append(html);
                        
                    }

                    if ( data.user_list ){
                         
                         var select_html = '';
                         $.each(data.user_list, function(index, value ) {
                            if( !$('.wtai-slide-right-text-wrapper')
                             .find('.wtai-history')
                             .find('.wtai-history-filter')
                             .find('.wtai-history-author-select')
                             .find('option[value="' + value + '"]').length ){
                                 select_html = select_html+'<option value="'+value+'">'+value+'</option>';
                             } 
                         });
                         $('.wtai-slide-right-text-wrapper')
                             .find('.wtai-history')
                             .find('.wtai-history-filter')
                             .find('.wtai-history-author-select')
                             .append(select_html);
 
                         
                         var select = $('#wtai-history-author-select');
                         var defaultOption = select.find('option:first');
                         var options = select.find('option:not(:first)');
 
                         options.sort(function(a, b) {
                         var textA = $(a).text().toUpperCase();
                         var textB = $(b).text().toUpperCase();
                             return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                         });
 
                         select.empty().append(defaultOption).append(options);
                     }

                    if ( data.cont_token ){
                        var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                        $('.wtai-table-list-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append(token_html);
                    }
                }
            });
        }
        e.preventDefault();
    });

    window.wtaiGetHistoryPopin = function() {
        if ( ! $('.wtai-slide-right-text-wrapper').find('.wtai-history').hasClass('wtai-pending') ){
            popupGenerateCompleted('hide');
            $('.wtai-slide-right-text-wrapper').find('.wtai-history').addClass('wtai-pending');
            if ( $('.wtai-slide-right-text-wrapper').hasClass('wtai-history-open') ){
                setTimeout(function() {
                    $('.wtai-slide-right-text-wrapper').removeClass('wtai-history-open'); 
                    $('.wtai-btn-close-history').hide();
                    $('body').removeClass('wtai-history-open');   
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history').removeClass('wtai-active');
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history').removeClass('wtai-pending');
                    $('.wtai-slide-right-text-wrapper')
                        .find('.wtai-history')
                        .find('.wtai-history-content')
                        .find('.wtai-history-wrapper')
                        .remove();
                    $('.wtai-slide-right-text-wrapper')
                        .find('.wtai-history')
                        .find('.wtai-history-filter')
                        .find('.wtai-history-author-select')
                        .find('option:not(.wtai-option-author-default)')
                        .remove();

                    //lets clear out the dates
                    $('.wtai-history.wtai-history-single .wtai-history-filter .wtai-history-date-input').val('');
                }, 300);
            } else {  
                $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                if ( $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                }
                var date = new Date();
                var offset = date.getTimezoneOffset();
                var wtai_nonce = get_wp_nonce();

                if( $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

               setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_single_category_history',
                            category_id: $('#wtai-edit-post-id').attr('value'),
                            pageSize: WTAI_OBJ.pageSize, 
                            browsertime: offset,
                            wtai_nonce: wtai_nonce
                        },
                        beforeSend: function(){
                            $('.wtai-slide-right-text-wrapper').addClass('wtai-history-open');
                            $('.wtai-ai-logo').addClass('wtai-hide');
                            $('.wtai-global-loader').addClass('wtai-is-active');
                            $('body').addClass('wtai-history-open');   
                            
                            $('.wtai-btn-close-history').fadeIn();
                            $('.wtai-slide-right-text-wrapper').find('.wtai-history').addClass('wtai-active');
                            
                        },
                        success: function(data) {

                            if (  $('.wtai-slide-right-text-wrapper').hasClass('wtai-history-open')  ) {
                                if ( data.results && data.has_results == 'yes' ){
                                    var html = '';
                                    $.each(data.results, function(history_index, history_value) {
                                        html = html+'<div class="wtai-history-wrapper">';
                                        html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                                        html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div><span class="dashicons dashicons-arrow-down-alt2"></span></div><div class="wtai-history-content-container">';
                                        $.each(history_value['values'], function( index_key, value_details ) {
                                            var api_content_value = value_details['value'];
                                            api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();

                                            html = html+'<div class="wtai-history-content-row">';
                                                html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                                html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                            html = html+'</div>';
                                        });
                                        
                                        html = html+'</div></div>';
                                    });

                                    $('.wtai-history-content-corrector').html( '' );

                                    $('.wtai-slide-right-text-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-content')
                                        .append(html);
                                    
                                }
                                else{
                                    $('.wtai-slide-right-text-wrapper')
                                    .find('.wtai-history')
                                    .find('.wtai-history-content')
                                    .append('<div class="wtai-no-history-found">' + WTAI_OBJ.noHistoryMessage + '</div>');
                                }

                                if ( data.user_list ){
                                    var select_html = '';
                                    $.each(data.user_list, function(index, value ) {
                                        select_html = select_html+'<option value="'+value+'">'+value+'</option>';
                                    });
                                    $('.wtai-slide-right-text-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-filter')
                                        .find('.wtai-history-author-select')
                                        .append(select_html);
                                }
                                if ( data.cont_token ){
                                    var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                                    $('.wtai-slide-right-text-wrapper')
                                        .find('.wtai-history')
                                        .find('.wtai-history-content')
                                        .append(token_html);
                                }
                              
                                $('.wtai-slide-right-text-wrapper').find('.wtai-history').removeClass('wtai-pending');    
                            } else {    
                               $('.wtai-global-loader').addClass('wtai-is-active');
                               $('.wtai-ai-logo').addClass('wtai-hide');
                                $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                                if ( $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                                    $('.wtai-slide-right-text-wrapper').find('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                                }
                            }
                        }
                    });
                }, 300);   
            }
            $('.wtai-slide-right-text-wrapper').find('.wtai-history').removeClass('wtai-pending');
        }
    };

    $(document).on('click', '.wtai-history.wtai-history-single .wtai-token-readmore-btn',function(e){
        var event = $(this);
        if ( ! event.hasClass('disabled') ){
            var date = new Date();
            var offset = date.getTimezoneOffset();
            var wtai_nonce = get_wp_nonce();
            var data = {
                action: 'wtai_single_category_history',
                category_id: $('#wtai-edit-post-id').attr('value'),
                pageSize: WTAI_OBJ.pageSize, 
                browsertime: offset,
                wtai_nonce: wtai_nonce
            };
            if ( event.data('date_from') ){
                data['date_from'] = event.data('date_from');
            }

            if ( event.data('date_to') ){
                data['date_to'] = event.data('date_to');
            }

            if ( event.data('author') ){
                data['author'] = event.data('author');
            }

            if ( event.data('token') ){
                data['continue_token'] = event.data('token');
            }

            if( $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
            }

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data,
                beforeSend: function(){
                    event.addClass('disabled');
                    event.html(WTAI_OBJ.loading);
                    
                },
                success: function(data) {
                    event.remove();

                    if ( data.results && data.has_results == 'yes' ){
                        var html = '';
                        $.each(data.results, function(history_index, history_value) {
                            html = html+'<div class="wtai-history-wrapper">';
                            html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                            html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div><span class="dashicons dashicons-arrow-down-alt2"></span></div><div class="wtai-history-content-container">';
                            $.each(history_value['values'], function( index_key, value_details ) {
                                var api_content_value = value_details['value'];
                                api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();
                                
                                html = html+'<div class="wtai-history-content-row">';
                                    html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                    html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                html = html+'</div>';
                            });
                            
                            html = html+'</div></div>';
                        });

                        $('.wtai-history-content-corrector').html( '' );

                        $('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append(html);
                        
                    }
                    else{
                        $('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append('<div class="wtai-no-history-found">' + WTAI_OBJ.noHistoryMessage + '</div>');
                    }

                    if ( data.user_list ){
                        
                        var select_html = '';
                        $.each(data.user_list, function(index, value ) {
                           if( !$('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-filter')
                            .find('.wtai-history-author-select')
                            .find('option[value="' + value + '"]').length ){
                                select_html = select_html+'<option value="'+value+'">'+value+'</option>';
                            } 
                        });
                        $('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-filter')
                            .find('.wtai-history-author-select')
                            .append(select_html);

                        
                        var select = $('#wtai-history-author-select');
                        var defaultOption = select.find('option:first');
                        var options = select.find('option:not(:first)');

                        options.sort(function(a, b) {
                        var textA = $(a).text().toUpperCase();
                        var textB = $(b).text().toUpperCase();
                            return (textA < textB) ? -1 : (textA > textB) ? 1 : 0;
                        });

                        select.empty().append(defaultOption).append(options);

                    }

                    if ( data.cont_token ){
                        var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                        $('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append(token_html);
                        
                    }
                }
            });
        }
        e.preventDefault();
    });

    $(document).on('click', '.wtai-history.wtai-history-single .wtai-history-filter-button', function(e){
        var event = $(this);
        if ( ! event.hasClass('disabled') ) {
            event.addClass('disabled');
            var date_from = '';
            if( event.closest('.wtai-history-filter-form').find('.wtai-history-date-from').find('.wtai-history-date-input').val() != '' ){
                date_from = event.closest('.wtai-history-filter-form').find('.wtai-history-date-from').find('.wtai-history-date-input').attr('date-format');
            }

            var date_to = '';
            if( event.closest('.wtai-history-filter-form').find('.wtai-history-date-to').find('.wtai-history-date-input').val() != '' ){
                date_to = event.closest('.wtai-history-filter-form').find('.wtai-history-date-to').find('.wtai-history-date-input').attr('date-format');
            }

            var author = event.closest('.wtai-history-filter-form').find('.wtai-history-date-author').find('.wtai-history-author-select').val();
            
            if ( date_from || date_to || author ) {

                var date = new Date();
                var offset = date.getTimezoneOffset();
                
                var wtai_nonce = get_wp_nonce();

                var data = {
                    action: 'wtai_single_category_history',
                    category_id: $('#wtai-edit-post-id').attr('value'),
                    pageSize: WTAI_OBJ.pageSize,
                    browsertime:offset,
                    wtai_nonce:wtai_nonce,
                };
                if ( date_from ){
                    data['date_from'] = date_from;
                }

                if ( date_to ){
                    data['date_to'] = date_to;
                }

                if ( author ){
                    data['author'] = author;
                }

                if( $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: data,
                    beforeSend: function(){
                        event.closest('.wtai-history-filter-form').find('input').prop('disabled', true );
                        event.closest('.wtai-history-filter-form').find('select').prop('disabled', true );
                        event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                        if ( event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').length > 0 ){
                            event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-token-readmore-btn').remove();
                        }
                        event.closest('.wtai-history').find('.wtai-history-content').find('.wtai-history-wrapper').remove();
                        $('.wtai-global-loader').addClass('wtai-is-active');
                        $('.wtai-ai-logo').addClass('wtai-hide');
                    },
                    success: function(data) {
                        
                        if ( data.results && data.has_results == 'yes' ){
                            var html = '';
                            $.each(data.results, function(history_index, history_value) {
                                html = html+'<div class="wtai-history-wrapper">';
                                html = html+'<div class="wtai-history-header-container"><div class="wtai-history-date">'+history_value['date']+'</div>';
                                html = html+'<div class="wtai-history-author">'+history_value['action_desc']+'</div><span class="dashicons dashicons-arrow-down-alt2"></span></div><div class="wtai-history-content-container">';
                                $.each(history_value['values'], function( index_key, value_details ) {
                                    var api_content_value = value_details['value'];
                                    api_content_value = $('.wtai-history-content-corrector').html( api_content_value ).html();

                                    html = html+'<div class="wtai-history-content-row">';
                                        html = html+'<div class="wtai-history-field">'+value_details['field']+'</div>';
                                        html = html+'<div class="wtai-history-text">'+api_content_value+'</div>';
                                    html = html+'</div>';
                                });
                                
                                html = html+'</div></div>';
                            });

                            $('.wtai-history-content-corrector').html( '' );

                            $('.wtai-slide-right-text-wrapper')
                                .find('.wtai-history')
                                .find('.wtai-history-content')
                                .append(html);                                
                        }
                        else{
                            $('.wtai-slide-right-text-wrapper')
                            .find('.wtai-history')
                            .find('.wtai-history-content')
                            .append('<div class="wtai-no-history-found">' + WTAI_OBJ.noHistoryMessage + '</div>');
                        }

                        if ( data.cont_token ){
                            var token_html = '<div class="wtai-token-readmore-wrapper"><a href="#" class="wtai-token-readmore-btn button button-primary" data-date_to="'+data.filters.endDate+'" data-author="'+data.filters.userName+'"  data-date_from="'+data.filters.startDate+'"  data-token="'+data.cont_token +'">'+WTAI_OBJ.LoadMoreHistory+'</a></div>';
                            $('.wtai-slide-right-text-wrapper')
                                .find('.wtai-history')
                                .find('.wtai-history-content')
                                .append(token_html);
                        }

                        event.closest('.wtai-history-filter-form').find('input').prop('disabled', false );
                        event.closest('.wtai-history-filter-form').find('select').prop('disabled', false );
                        event.removeClass('disabled');     
                    }
                });
            } else {
                event.removeClass('disabled');
            }
        }
        e.preventDefault();
    });

    // rewrite_toggle_credit_behavior()
    function rewrite_button_state_behavior(){
        var hasDataText = false;
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var content = $(this).find('.wtai-columns-1').find('.wtai-text-message').text();
            var cbChecked = $(this).find('.wtai-checkboxes').is(':checked');
           
            if ( content.trim() != '' && cbChecked ) {
                hasDataText = true;                
            }
        });

        if( ! hasDataText && $('#postbox-container-2').find('.wtai-metabox .wtai-checkboxes:checked').length ){
            $('#wtai-cta-generate-type-generate').prop('checked', true);
            $('#wtai-cta-generate-type-rewrite').prop('checked', false);
            
            $('#wtai-cta-generate-type-rewrite').prop('disabled', true);
            $('#wtai-cta-generate-type-rewrite').closest('label').addClass('disabled');

            $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.generateCTAText );

            setTimeout(function() {
                var tooltip_args = {
                    tooltipMessage : WTAI_OBJ.tooltipDisableRewriteMessage2,
                    showTooltip: 1
                }
                $(document).trigger('wtai_rewrite_disabled_state', tooltip_args);
            }, 300);
        }
        else{
            $('#wtai-cta-generate-type-rewrite').prop('disabled', false);
            $('#wtai-cta-generate-type-rewrite').closest('label').removeClass('disabled');

            if( window.lastGenerationTypeSelected == 'rewrite' ){
                $('#wtai-cta-generate-type-generate').prop('checked', false);
                $('#wtai-cta-generate-type-rewrite').prop('checked', true);

                $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.rewriteCTAText );
            } else {
                $('.wtai-cta-type-label').text( WTAI_FILTER_OBJ.generateCTAText );
            }

            setTimeout(function() {
                var tooltip_args = {
                    tooltipMessage : WTAI_OBJ.tooltipDisableRewriteMessage2,
                    showTooltip: 0
                }
                $(document).trigger('wtai_rewrite_disabled_state', tooltip_args);
            }, 300);
        }
    }

    $(document).on('click', '.wtai-review-check', function(){

        $('.wtai-ai-logo').addClass('wtai-hide');

        $('.wtai-review-check').prop('disabled', true );
        $('.wtai-review-check').closest('.wtai-review-wrapper').addClass('wtai-review-wrapper-disabled');

        setTimeout(function(){
            if ( ! $('.wtai-product-pager-wrapper').hasClass('wtai-page-processing') ) {
                var object = $(this);
                var value = 0;
                if ( $('.wtai-slide-right-text-wrapper').find('.wtai-review-check').is(':checked') ){
                    value = 1;
                } else {
                    value = 0;
                }
                object.prop('disabled', true );

                var date = new Date();
                var offset = date.getTimezoneOffset();

                var wtai_nonce = get_wp_nonce();

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        action: 'wtai_category_review_check',
                        category_id:  $('#wtai-edit-post-id').attr('value'),
                        value: value,
                        browsertime : offset,
                        wtai_nonce: wtai_nonce,
                    },
                    beforeSend: function() {
                        $('.wtai-global-loader').addClass('wtai-is-active');
                    },
                    success: function(data) {
                        if ( data.access == 1 ) {
                            object.prop('disabled', false );

                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message').remove();
                            }
                            
                            if( data.api_updated == '0' ){
                                $('.wtai-review-check').prop('checked', false );

                                if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                    $('.wtai-edit-product-line' ).find('#message').remove();
                                }
                                $('<div id="message" class="error-review-wrap error notice is-dismissible"><p>'+data.error_message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                            }
                        } else {
                            $('.wtai-review-check').prop('checked', false );

                            if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message').remove();
                            }
                            $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        }

                        $('.wtai-review-check').prop('disabled', false );
                        $('.wtai-review-check').closest('.wtai-review-wrapper').removeClass('wtai-review-wrapper-disabled');
                    }
                });
            }
        }, 300); 
    });

    $(document).on('click', '.wtai-review-wrapper label', function(e){
        var object = $(this);
        object.parent().find('input').click();
        e.preventDefault();
    });

    function setSemanticActiveCount(){
        $(document).trigger('wtai_set_semantic_keyword_active_count');
    }

    function get_pronouns_per_type( type = 'Informal' ){
        var pronounsTypeList = WTAI_OBJ.formalInformalPronouns;

        var pronouns = [];
        $.each(pronounsTypeList, function( indexType, pronounData ){
            //lets get the opposite, if Formal, highlight Informal and vice versa
            if( type != indexType ){
                var pronounsList = pronounData.pronouns;

                $.each(pronounsList, function( pronounIndex, pronoun ){
                    if( pronoun.trim().toLowerCase() != '' ){
                        pronouns.push( pronoun.trim().toLowerCase() );
                    }
                });   
            }
        });

        return pronouns;
    }

    function isValidKeywordMatch( matchingWord, keywords ){
        matchingWord = matchingWord.toLowerCase();

        var validPunctuations = ['.', ',', '?', '!', ':', ';', '\'s'];
        var validMatches = [];
        // loop through valid punctuations
        for( i = 0; i < validPunctuations.length; i++ ){
            var punctuation = validPunctuations[0];
            validMatches.push( matchingWord + '' + punctuation );
        }

        var isMatch = false;
        if( keywords.includes( matchingWord ) ){
            isMatch = true;
        }
        else{
            for( x = 0; x < validMatches.length; x++ ){
                var validMatch = validMatches[x];
                if( matchingWord == validMatch ){
                    isMatch = true;
                    break;
                }
            }
        }

        return isMatch;
    }

    $(document).on('click', '.wtai-highlight-incorrect-pronouns-cb', function(){
        if( $(this).is(':checked') ) {
            highlight_pronouns('true');
        } else {
            highlight_pronouns('false');
        }
    });

    function highlight_pronouns(type) {
        if( type == 'true' ) {
            value = 1;
        } 
        else {
            value = 0;
        }

        //maybe highlight keywords and pronouns
        addHighlightKeywords();

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_highlight_pronouns_category_check',
                value: value,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    }   

    /*Highlights Keyword*/
    $(document).on('click', '#wtai-highlight', function(){
        if(this.checked) {
            highlight_keywords('true');
        } else {
            highlight_keywords('false');
        }
    });

    function highlight_keywords(type) {
        if( type == 'true' ) {
            addHighlightKeywords();
            value = 1;
        } else {
            value = 0;
            removeHighlightkeywords();
        }

        var wtai_nonce = get_wp_nonce();
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_category_highlight_check',
                value: value,
                wtai_nonce: wtai_nonce
            },
            success: function() {
            }
        });
    }

    function is_formal_tone_selected(){
        var formal_tone_selected = false;
        if( $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').length ){
            $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').each(function(){
                if( $(this).is(':checked') && $(this).val().toLowerCase() == 'formal' ){
                    formal_tone_selected = true;
                }
            });
        }

        return formal_tone_selected;
    }

    $(document).on('wtaGenerateTextOthers', function(e, messageEntry){
        e.stopImmediatePropagation();

        var post_id = $('#wtai-edit-post-id').val();

        var streamStop = messageEntry.encodedMsg.stop;
        var streamRecordId = messageEntry.encodedMsg.recordId;
        var streamFieldTypeID = messageEntry.encodedMsg.streamFieldTypeID;
        var streamIndex = messageEntry.encodedMsg.index;

        if( post_id == streamRecordId ){
            //display blink cursor
            var elemID = 'wtai-wp-field-input-' + streamFieldTypeID;

            clearEditor(elemID, streamFieldTypeID);

            removeHighlightkeywordsByField( elemID );

            //disable generate button and display loader
            $('.wtai-page-generate-all').addClass('disabled');
            $('.wtai-page-generate-all').addClass('wtai-generating');
            $('.wtai-generate-cta-radio-wrap').addClass('wtai-generation-ongoing');
            show_ongoing_generation_tooltip( 'show' );

            $('.wtai-generate-wrapper .toggle').addClass('disabled');
            $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

            var data_object = $('#postbox-container-2').find('.wtai-metabox-' + streamFieldTypeID);
            
            data_object.addClass('wtai-disabled-click');
            data_object.addClass('wtai-bulk-process');
            data_object.addClass('wtai-metabox-update');

            data_object.find('.wtai-checkboxes').prop('disabled', true );

            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');
            
            if( streamStop == true ){
                $('.wtai-page-generate-all').removeClass('disabled');
                $('.wtai-page-generate-all').removeClass('wtai-generating');
                $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
                show_ongoing_generation_tooltip( 'hide' );

                $('.wtai-generate-wrapper .toggle').removeClass('disabled');

                data_object.removeClass('wtai-disabled-click');
                data_object.removeClass('wtai-bulk-process');
                data_object.removeClass('wtai-metabox-update');

                data_object.find('.wtai-checkboxes').prop('disabled', false );
                
                $('.wtai-generate-text-single-' + streamFieldTypeID).attr('disabled', false);
                $('.wtai-generate-text-single-' + streamFieldTypeID).prop('disabled', false);

                //lets call the api to get the new value
                fetchFreshTextFromAPI( streamRecordId, streamFieldTypeID, true );
            }
        }
        else{
            if( $( '#the-list #wtai-table-list-' + streamRecordId ).length ){
                if( streamIndex > 0 && streamStop != true  ){
                    $( '#the-list #wtai-table-list-' + streamRecordId ).addClass('wtai-processing');
                    $( '#the-list #wtai-table-list-' + streamRecordId ).find('.wtai-cwe-selected').prop('disabled', true);
                    $( '#the-list #wtai-table-list-' + streamRecordId ).find('.wtai-cwe-selected').prop('checked', false);
                    $( '#the-list #wtai-table-list-' + streamRecordId ).find('.wtai-cwe-action-button.generate').addClass('wtai-disabled-button' );
                    $( '#the-list #wtai-table-list-' + streamRecordId ).find('.transfer_feature').addClass('wtai-disabled-button' );

                    refresh_bulk_generate_credit_count();
                }
                
                if( streamStop == true ){
                    //this enables like magic :D
                    fetchFreshTextFromAPI( streamRecordId, streamFieldTypeID, false, 0, 1, 0 );
                }
            }
        }
    });

    $(document).on('wtaEnableWTAList', function(e, messageEntry){
        e.stopImmediatePropagation();

        var completedIds = messageEntry.encodedMsg.completedIds;
        var completed = messageEntry.encodedMsg.completed;

        if( completedIds.length && completed == '1' ){
            $.each(completedIds, function( index, productID ){
                if( $( '#the-list #wtai-table-list-' + productID ).length ){
                    fetchFreshTextFromAPI( productID, '', false, 0, 1, 1 );
                }
            });
        }
    });

    function set_disallowed_combinations_single(){
        $(document).trigger('wtai_set_disallowed_combinations_single');
    }

    function renderSuggestedAudience( response_data ){
        var args = {
            response_data : response_data,
        }

        $(document).trigger('wtai_render_suggested_audience', args);
    }

    $(document).on('click', '.wtai-cwe-action-button-history', function(e){
        var event = $(this);
        var post_id = event.data('id');
        var post_link = event.attr('href');
        var post_name = event.text();

        $('body').removeClass('wtai-history-global-open');
        $('.wtai-main-wrapper').removeClass('wtai-history-global-open');
        $('.wtai-btn-close-history-global').css('visibility','hidden');

        $('html, body').scrollTop(0);

        topheader_post(); // maybe reposition the header on mobile when single edit product is clicked.

        $('#wpwrap').addClass('wtai-overlay');

        show_hide_global_loader('show');

        // Hide or show step options
        $('.wtai-hide-step-cb-wrap').show();
        $('.wtai-hide-step-separator').show();

        // Show restore global settings button
        $('.wtai-restore-global-settings-wrap').show();
        $('.wtai-restore-global-settings-separator').show();

        $('.wtai-post-data-json').each(function(){
            var postfield = $(this).data('postfield');

            var elementobject = $(this);
            switch( postfield ){
                case 'post_title':
                    elementobject.html( post_name );
                    break;
                case 'post_permalink':
                    elementobject.attr( 'href', post_link );
                    elementobject.html( post_link );
                    break;
                case 'post_id':
                    elementobject.attr( 'value', post_id );
                    break;
                default:
                    break;
            }
        });

        $('body').addClass('wtai-open-single-slider');

        $('#wpwrap').addClass('wtai-loader'); 
        $('.wtai-slide-right-text-wrapper').addClass('wtai-disabled-click');

        // Load category edit content;
        get_category_edit_data( post_id, 1 );

        // Load keywords data
        setTimeout(function() {
            $('body').addClass('wtai-open-single-slider');
        
            $('#wpwrap').addClass('wtai-loader'); 
            $('.wtai-slide-right-text-wrapper').addClass('wtai-disabled-click');
            
            get_category_keyword_edit_data_ajax( post_id );
        }, 300);
    });

    $(document).on('click', '.action-bulk-image-process', function( e ){
        e.preventDefault();

        var button = $('.wtai-page-generate-all');

        var loaderEstimatedTime = $('#wtai-loader-estimated-time');
        var bulkpopupwrapper = loaderEstimatedTime.find('.wtai-bulk-popup-wrapper');
        
        if ( loaderEstimatedTime.is(':visible') ) {
            $(this).closest('.wtai-loader-generate').remove();
        }
        
        if ( loaderEstimatedTime.hasClass('no-pad-top') ) {
            bulkpopupwrapper.removeClass('hidden');
            loaderEstimatedTime.removeClass('no-pad-top');
            loaderEstimatedTime.hide();
        }

        $('#wtai-confirmation-proceed-image-loader').hide();

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        $('#postbox-container-2 .wtai-checkboxes-alt').prop('disabled', false);

        button.addClass('wtai-pre-process-image-done');
        button.removeClass('disabled');
        button.removeClass('wtai-generating');
        button.trigger('click'); //lets retrigger the click since all is well
    });

    $(document).on('click', '.action-bulk-image-process-cancel, .action-bulk-image-process-ok-cancel', function( e ){
        e.preventDefault();

        var loaderEstimatedTime = $('#wtai-loader-estimated-time');
        var bulkpopupwrapper = loaderEstimatedTime.find('.wtai-bulk-popup-wrapper');
        
        if ( loaderEstimatedTime.is(':visible') ) {
            $(this).closest('.wtai-loader-generate').remove();
        }
        
        if ( loaderEstimatedTime.hasClass('no-pad-top') ) {
            bulkpopupwrapper.removeClass('hidden');
            loaderEstimatedTime.removeClass('no-pad-top');
            loaderEstimatedTime.hide();
        }

        $('#wtai-preprocess-image-loader').hide();

        //added 2024.03.05
        $('#wpcontent').removeClass('preprocess-image');

        $('.wtai-slide-right-text-wrapper .wtai-close').removeClass('disabled');
        $('.wtai-slide-right-text-wrapper .wtai-button-prev').removeClass('disabled-nav');
        $('.wtai-slide-right-text-wrapper .wtai-button-next').removeClass('disabled-nav');


        reset_image_alt_local_data();

        var product_id = $('#wtai-edit-post-id').attr('value');

        //reset text field states
        $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').removeClass('disabled');
        $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
        $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-disabled-click');
        $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-bulk-process');
        $('#postbox-container-2').find('.wtai-metabox').removeClass('wtai-bulk-complete');
        $('#postbox-container-2').find('.wtai-metabox').addClass('wtai-loading-metabox');

        $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
        show_ongoing_generation_tooltip( 'hide' );

        get_category_edit_data( product_id, 0 );

        $('.wtai-image-alt-metabox').removeClass('wtai-error-upload');

        var button = $('.wtai-page-generate-all');
        button.removeClass('disabled');
        button.removeClass('wtai-generating');

        //disable grid checkbox
        if( $('#the-list tr#wtai-table-list-' + product_id).length > 0 ){
            $('#the-list tr#wtai-table-list-' + product_id).find('.wtai-cwe-selected').prop('disabled', false );
            $('#the-list tr#wtai-table-list-' + product_id).find('.wtai-cwe-action-button.generate').removeClass('wtai-disabled-button');
            $('#the-list tr#wtai-table-list-' + product_id).find('.wtai-cwe-action-button.transfer').removeClass('wtai-disabled-button');
            $('#the-list tr#wtai-table-list-' + product_id).removeClass('wtai-processing');
        }

        $('#wtai-confirmation-proceed-image-loader .wtai-error-message-container').html( '' );
        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process').show();
        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-cancel').show();
        $('#wtai-confirmation-proceed-image-loader .wtai-loading-actions-container .action-bulk-image-process-ok-cancel').hide();

        $('#postbox-container-2 .wtai-checkboxes-alt').prop('disabled', false);

        $('#wtai-confirmation-proceed-image-loader').hide();
    });

    function set_representative_product_count(){
        var count = $('.wtai-representative-product-items-list .wtai-representative-product-item').length;
        $('.wtai-representative-product-counter-wrap .wtai-rpc-item-count').html( count );
    }

    function show_rep_product_tooltip( display = '' ){
        load_representative_product_tooltip();
        
        if( display == 'show' ){
            $('.wtai-representative-product-input-wrap').tooltipster('enable');
        }
        else{
            $('.wtai-representative-product-input-wrap').tooltipster('disable');
        }
    }

    load_representative_product_tooltip();
    function load_representative_product_tooltip(){
        try{ 
            $('.wtai-representative-product-input-wrap').each(function(){
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
        catch( err ){
        }
    }

    $(document).on('change', '.wtai-category-image-selection-cb', function(){
        var is_checked = 0;
        if( $(this).is(':checked') ){
            is_checked = 1;
        }

        var wtai_nonce = get_wp_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_set_category_image_state',
                is_checked: is_checked,
                wtai_nonce: wtai_nonce
            },
            success: function(data) {
            }
        });
    });

    // Event after reset global settings
    $(document).on('wtai_reset_global_settings', function(e, data){
        e.stopImmediatePropagation();

        if ( $('#wtai-category-image-selection-cb').length ) {
            $('#wtai-category-image-selection-cb').prop('checked', true).trigger('change');
        }
    });
});