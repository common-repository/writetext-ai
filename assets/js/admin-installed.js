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
    var transferGridAJAX = null;
    var queueGenerateTimer;
    var pollBackgroundTimer;
    var bulkGenerateAJAX = null;
    var reloadDataAJAX = null;
    var pollBackgroundAJAX = null;
    var bulkTransferCancelled = false;
    var bulkGenerateOneOngoing = false;
    var bulkGenerateTextFieldSaveAJAX = null;
    var bulkTransferTextFieldSaveAJAX = null;
    var referenceProductLazyLoadAJAX = null;
    var referenceProductBulkLazyLoadAJAX = null;
    var productEditFormHtml = '';
    var singleGenerationErrorFields = [];
    var completeWriting = null;
    var recordSingleProductAttr = null;
    var lastGenerationTypeSelected = null;
    var ajaxBulkRequests = [];

    // Initialize image alt text objects
    reset_image_alt_local_data();
    reset_image_bulk_alt_local_data();

    function addBodyClass() {
        var screenWidth = $(window).width();
        var userAgent = navigator.userAgent.toLowerCase();
        var bodyClass = '';

        if (userAgent.indexOf('ipad') > -1) {
           bodyClass = 'wtai-iPad';
        } else if (userAgent.indexOf('tablet') > -1) {
           bodyClass = 'wtai-tablet';
        } else if (userAgent.indexOf('mobile') > -1) {
           bodyClass = 'wtai-mobile';
        }

        if (screenWidth >= 768 && screenWidth < 1024) {
            bodyClass = 'wtai-iPad';
        } else if (screenWidth < 768) {
            bodyClass = 'wtai-mobile';
        }

        $('body').addClass(bodyClass);
    }

    // Initial call on document ready
    addBodyClass();

    // Update class on window resize
    $(window).resize(function () {
        // Remove existing classes first
        $('body').removeClass('wtai-iPad');
        $('body').removeClass('wtai-tablet');
        $('body').removeClass('wtai-mobile');
        // Call the function to add the appropriate class based on the new width
        addBodyClass();
    });

    if( $('.wtai-slide-right-text-wrapper').length ){
        productEditFormHtml = '<div class="wtai-slide-right-text-wrapper wtai-main-wrapper">' + $('.wtai-slide-right-text-wrapper').html() + '</div>';
    }

    $(document).on('click', function(e){
        if ( $(e.target).closest('.wtai-sort-ideas-btn.wtai-sort-style2').length == 0  ) {
            if ( $('.wtai-sort-idea-filter-wrap').hasClass('wtai-active') ) {
                $('.wtai-sort-idea-filter-wrap').removeClass('wtai-active');
            }
        }
        //added by mcr
        if ($(e.target).closest('.wtai-sort-volume-difficulty-select').length == 0) {
            if ($('.wtai-volume-difficulty-dropdown').hasClass('wtai-active')) {
                $('.wtai-volume-difficulty-dropdown').removeClass('wtai-active');
            }
        }
        if ( $(e.target).closest('.wtai-slide-right-text-wrapper').length == 0 && $(e.target).closest('#wtai-product-edit-cancel').length == 0 && 
            $(e.target).closest('#wtai-product-generate-forced').length == 0 && $(e.target).closest('#wtai-product-generate-completed').length == 0 && 
            $(e.target).closest('#wtai-loader-estimated-time').length == 0 && $(e.target).closest('#wpwrap').length > 0 && 
            $('#wpwrap').hasClass('wtai-loader') && ! $('body').hasClass('wtai-history-open') ) {
        }

        if( ( $(e.target).closest('#wtai-sel-writetext-status').length <= 0 ) && $(e.target).closest('#wpwrap').length && $('.wtai-status-checkbox-options').hasClass('wtai-open') ) {
            $('#wtai-sel-writetext-status > div.wtai-filter-select').trigger('click');
        }  
        
        if ( $(e.target).closest('.wtai-postbox-process-wrapper').length == 0  ) {
            if ( $('.wtai-postbox-process-wrapper').hasClass('open') ) {
                $('.wtai-postbox-process-wrapper').removeClass('open');
            }
        }
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

    $(document).on('mouseup', function(e){
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

        var tooltip = $('.wtai-tooltip');
        if ( !tooltip.is(e.target) && tooltip.has(e.target).length === 0 ) {
            $('.wtai-tooltip').removeClass('hover');
        }

        var review_checking = $('.wtai-rewrite-checking-label');
        if ( !review_checking.is(e.target) && review_checking.has(e.target).length === 0 ) {
            $('.wtai-rewrite-checking-label').removeClass('hover');
        }

    });
    
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
            var wtai_nonce = get_product_bulk_nonce();
            if (  typeof value === 'undefined'  ) {
                var row_id = rowevent.data('id');
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        action: 'wtai_get_generated_tooltip_text',
                        product_id: row_id,
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

    $('.tooltip-help-transfer').each(function(){
        if( $(this).hasClass('enabled_button') ) {
            $(this).tooltipster({
                'theme': 'tooltipform-default',
                'position': 'top',
                'arrow': true,
                debug: false,
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
        } else {
            $(this).attr('disabled','true');
        }
        $(this).hover(function(){
            $(this).attr('tooltip-data', $(this).attr('title'));
            $(this).removeAttr('title');
          }, function(){
            $(this).attr('title', $(this).attr('tooltip-data'));
            $(this).removeAttr('tooltip-data');
        });
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

    //bulk-tooltip-generate-filter
    load_bulk_generate_filter_tooltip();
    function load_bulk_generate_filter_tooltip(){
        try{ 
            $('.bulk-tooltip-generate-filter').each(function(){
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
                    //content: '...'
                });

                //disable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){
        }

        try{ 
            $('.wtai-featured-product-image-label').each(function(){
                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': 'top',
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
                    //content: '...'
                });

                //disable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){
        }
    }

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

    $(document).on('click', '.wtai-history .wtai-history-header-container', function(e){
        $(this).closest('.wtai-history-wrapper').toggleClass('wtai-active');
        e.preventDefault();
    });

    $(document).on('click', '.wtai-product-pager-wrapper .button', function(e){
        if( $(this).hasClass('disabled-nav') ){
            return;
        }

        var button = $(this);

        var number_of_changes_unsave = checkChanges( 'nav' );
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
                    rewrite_toggle_credit_behavior();
                }
            }
            catch( exc ){
            }

            addHighlightKeywordsbyFieldOnKeyup(editor.id);            
		});

        editor.on('change keyup', function( e ){
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

    function handle_single_transfer_button_state() {
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var id = $(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id');
            var field_type = $(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('data-postfield');
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
                    if( wtaiContainsHtmlUsingDOMParser( content ) && wtaiAreHtmlStringsEqual( content, current_value_raw ) == false ){
                        raw_not_match = true;
                    }

                    if( raw_not_match ){
                        //maybe enable the single transfer button
                        $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                    } else {                    
                        if ( ( source_origvalue === source_newvalue && raw_not_match === false ) || 
                            ( current_value != '' && source_newvalue === current_value ) ) {
                            $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                        }
                        else{
                            //maybe enable the single transfer button
                            $(this).find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                        }
                    }
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

        // Add alt images for checking too
        $('#postbox-container-2').find('.wtai-image-alt-metabox').each(function() {
            //changed .wtai-columns-3 to .wtai-generate-value-wrapper
            var id = $(this).find('.wtai-generate-value-wrapper').find('.wtai-wp-editor-setup-alt').attr('id');
            var cbChecked = $(this).find('.wtai-checkboxes-alt').is(':checked');
            var content = $('#'+id).val();
          
            if ( $('#'+id).length && content.trim() != '' && cbChecked && $(this).find('.wtai-single-transfer-btn').hasClass('wtai-disabled-button') == false ) {
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

    $(document).on('submit', '#wtai-edit-product-line-form',function(e) {
        e.preventDefault();
    });

    $('#wtai-frm-search-products').on('submit', function(event){
        // Prevent the default form submission
        event.preventDefault();

        // Create an empty array to store the encoded parameters
        var encodedParams = [];

        // Loop through each input element in the form
        $(this).find('input').each(function(){
            if( $(this).attr('name') !== undefined ){
                // Encode the parameter name and value, and push to the array
                encodedParams.push(
                    encodeURIComponent($(this).attr('name')) +
                    '=' +
                    encodeURIComponent($(this).val())
                );
            }
        });

        // Construct the encoded URL parameters
        var encodedQueryString = encodedParams.join('&');

        // Get the form's action URL
        var actionUrl = $(this).attr('action');

        // Append the encoded query string to the action URL
        var finalUrl = actionUrl + '&' + encodedQueryString;

        // Redirect to the final URL
        window.location.href = finalUrl;
    });

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

                var wtai_nonce = get_product_edit_nonce();

                // Get alt image ids
                var alt_image_data = [];
                if( $('.wtai-api-data-image_alt_text_id').length ){
                    $('.wtai-api-data-image_alt_text_id').each(function(){
                        var parent_mb = $(this).closest('.wtai-image-alt-metabox');
                        var image_id = parent_mb.attr('data-id');
                        var text_id = $(this).val();
                        var data_image_alt = {
                            'image_id' : image_id,
                            'text_id' : text_id,
                        };

                        alt_image_data.push( data_image_alt );
                    });
                }

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        action: 'wtai_product_review_check',
                        product_id:  $('#wtai-edit-post-id').attr('value'),
                        value: value,
                        browsertime : offset,
                        wtai_nonce: wtai_nonce,
                        alt_image_data: alt_image_data,
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
                                object.prop('checked', false );
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

    $(document).on('blur', '#wtai-wp-field-input-otherproductdetails', function(){
        var object = $(this);
        var value = object.val();
        object.prop('disabled', true );

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_otherproductdetails_text',
                product_id:  $('#wtai-edit-post-id').attr('value'),
                value: value,
                wtai_nonce: wtai_nonce
            },
            success: function(data) {
                if ( data.access == 1 ) {
                    if ( value ){
                        object.closest('li').find('.wtai-attr-checkboxes').prop('checked', true );
                    } else if ( ! value ){
                        object.closest('li').find('.wtai-attr-checkboxes').prop('checked', false );
                    }
                    object.prop('disabled', false );
                } else {
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }
                    $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
            }
        });
    });

    $(document).on('click', '#message .notice-dismiss', function(e){
        $(this).parent().remove();
        e.preventDefault();
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
   
    $(document).on('change', '#bulk-action-selector-top, #bulk-action-selector-bottom' , function() { 
        if ( $(this).val() == 'wtai_bulk_generate' ) {
            if ( $('.bulk-generate-action').length > 0){
                $('.bulk-generate-action').remove();
                $( '.actions.bulkactions input' ).css('display','inline-block');
            }
            var wtai_bulk_generate_ppopup = $('#wtai-bulk-generate-ppopup').val();
            if( wtai_bulk_generate_ppopup ) {
                if ($('.bulk-generate-action').length == 0 ){
                    var bulkGenerateCredit = getBulkGenerateCreditCount();

                    var credLabel = WTAI_OBJ.creditLabelPlural;
                    if( parseInt( bulkGenerateCredit ) == 1 ){
                        credLabel = WTAI_OBJ.creditLabelSingular;
                    }

                    html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" onclick="wtaiGoBulkGenerateDirect(this, event)" class="button action bulk-generate-action direct">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
                    $( '.actions.bulkactions input#doaction' ).after( html );
                } else {
                    $('.bulk-generate-action').css('display','inline');    
                }
            } else {
                if ($('.bulk-generate-action').length == 0 ){
                    var bulkGenerateCredit = getBulkGenerateCreditCount();

                    var credLabel = WTAI_OBJ.creditLabelPlural;
                    if( parseInt( bulkGenerateCredit ) == 1 ){
                        credLabel = WTAI_OBJ.creditLabelSingular;
                    }

                    html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" data-modal="wtai-bulk-generate-modal" onclick="wtaiGetProductAttr(this, this.event)" class="button action bulk-generate-action modal">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
                    $( '.actions.bulkactions input#doaction' ).after( html );
                    setTimeout(function(){
                        var checkbox = $('#TB_ajaxContent').find('.wtai-product-attr-container').find('.wtai-product-attr-cb:checked').length;
                        if ( checkbox > 0  ){
                            $('#TB_ajaxContent').find('#wtai-generate-bulk-btn').removeAttr('disabled');
                        }
                    }, 1000);
                } else {
                    $('.bulk-generate-action').css('display','inline');    
                }
            }

            $( '.actions.bulkactions input' ).css('display','none');
        } else  if ( $(this).val() == 'wtai_bulk_transfer' ) {
            if ( $('.bulk-generate-action').length > 0){
                $('.bulk-generate-action').remove();
                $( '.actions.bulkactions input' ).css('display','inline');
            }
            if ($('.bulk-generate-action').length == 0 ){
                html ='<a href="#" data-title="'+WTAI_OBJ.bulk_transfer+'" data-modal="wtai-bulk-transfer-modal" onclick=wtaiGetProductAttr(this) class="button action bulk-generate-action">'+$('#doaction').val()+'</a>';
                $( '.actions.bulkactions input#doaction' ).after( html );
                setTimeout(function(){
                    var checkbox = $('#TB_ajaxContent').find('.wtai-product-attr-container').find('.wtai-product-attr-cb:checked').length;
                    if ( checkbox > 0  ){
                        $('#TB_ajaxContent').find('#wtai-generate-bulk-btn').removeAttr('disabled');
                    }
                }, 1000);
            } else {
                $('.bulk-generate-action').css('display','inline');    
            }
            
            $( '.actions.bulkactions input' ).css('display','none');
        } else {
            $('.actions.bulkactions input' ).css('display','inline');
            $('.bulk-generate-action').css('display','none');
        }
    });

    $(document).on( 'click', '.wtai-metabox .selection', function(e) {
        var btn_event_select = $(this);
        if ( ! btn_event_select.hasClass('dontselect') ){
            if ( ! btn_event_select.hasClass('selected') ){
                if ( btn_event_select.closest('.wtai-metabox').find('.spinner.wtai-is-active').length == 0 ) {
                    btn_event_select.parent().find('.selection').removeClass('selected');
                    btn_event_select.addClass('selected'); 
                }
            }
        }
        btn_event_select.closest('.wtai-metabox').find('.wtai-checkboxes').prop('checked', true);
        $('.wtai-page-generate-all').removeClass('disabled');
        $('.wtai-page-generate-all').removeClass('wtai-generating');
        $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
        show_ongoing_generation_tooltip( 'hide' );

        $('.wtai-generate-wrapper .toggle').removeClass('disabled');

        $('#publishing-action .wtai-button-interchange').removeClass('disabled');
        e.preventDefault();
    });

    $(document).on( 'click', '.wtai-metabox .selection .button-add', function(e) {
        var btn_event_select = $(this).parent();
        var message = btn_event_select.find('p');
        var type = btn_event_select.closest('.postbox').data('type');
        if ( btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
            var id = btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
            tinymce.get(id).setContent( message.html().replace(/\n/g, '<br>') );
        } else if ( btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
            var id = btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
            tinymce.get(id).setContent( message.html().replace(/\n/g, '<br>') );
        } else if ( btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('textarea').length > 0 ) {
            btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('textarea').val(message.html());
        } else {
            btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('input.input-text').attr( 'value', message.html() );
        }

        if ( btn_event_select.find('.text-wrapper').find('.wtai-text-count-details').length > 0 ) {
            switch( type ){
                case 'product_description':
                case 'product_excerpt':
                    if ( btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-text-count-details').length > 0 ) {
                        btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-text-count-details').eq(0).remove();
                    }
                    btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-api-data-'+type).after(btn_event_select.find('.text-wrapper').find('.wtai-text-count-details').prop('outerHTML'));
                    break;
                default:
                    if ( btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-text-count-details').length > 0 ) {
                        btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-text-count-details').eq(0).remove();
                    }
                    btn_event_select.closest('.wtai-col-row-wrapper').find('.wtai-columns-3').find('.wtai-api-data-'+type).after(btn_event_select.find('.text-wrapper').find('.wtai-text-count-details').prop('outerHTML'));
                    break;
            }
        }   
        e.preventDefault();
    });

    $(document).on( 'click', '.wtai-metabox .wtai-btn-text-submit', function(e) {
        var btnevent = $(this);
        if ( !  btnevent.hasClass('disabled')   ){
            btnevent.addClass('disabled');
            var btnwrapper = btnevent.closest('.wtai-col-row-wrapper');
            var message = '';
            if ( btnwrapper.find('.selection.selected').find('p').length > 0  ) {
                message = btnwrapper.find('.selection.selected').find('p').html();
            } else if ( btnwrapper.find('.selection.selected').length > 0  ) {
                message = btnwrapper.find('.selection.selected').html();
            } else {
                message = '';
            }
            if ( message ){
                var type = btnevent.data('type');
                var field_type = btnevent.closest('.postbox').find('.wtai-generate-text').data('type');
                var text_input = false;
                var mce_editor = false;
                var mce_editor_sel = '';
                var id = '';
                if ( btnwrapper.find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                    id = btnwrapper.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                    mce_editor_sel = tinymce.get(id);
                    mce_editor = true;
                } else if ( btnwrapper.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                    id = btnwrapper.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                    mce_editor_sel = tinymce.get(id);
                    mce_editor = true;
                } else if ( btnwrapper.find('.wtai-columns-3').find('textarea').length > 0 ){
                    var source_input = btnwrapper.find('.wtai-columns-3').find('#wtai-wp-field-input-'+field_type );
                } else {
                    var source_input = btnwrapper.find('.wtai-columns-3').find('#wtai-wp-field-input-'+field_type );
                    text_input = true;
                }

                switch( type ){
                    case 'replace':
                        var date = new Date();
                        var offset = date.getTimezoneOffset();
                        $.ajax({
                            type: 'POST',
                            dataType: 'JSON',
                            url: WTAI_OBJ.ajax_url,
                            data: {
                                action: 'wtai_store_text',
                                browsertime: offset,
                                product_id:  $('#wtai-edit-post-id').attr('value'),
                                message_value: message, 
                                fields : field_type,
                                textid : btnevent.closest('.postbox').find('#wtai-wp-field-input-'+field_type+'_id').attr('value')
                            },
                            beforeSend: function() {
                                if ( mce_editor_sel ){
                                    btnevent.closest('.postbox').find('.wp-editor-wrap').addClass('wtai-loading-state');
                                } else {
                                    source_input.prop('disabled', true );
                                }
                            },
                            success: function(data) {
                                if ( data.results == 1 ) {
                                    if ( mce_editor_sel ){
                                        tinymce.get(id).setContent( message.replace(/\n/g, '<br/>') );
                                    } else {
                                        source_input.val(message);
                                    }

                                    if ( text_input ){
                                        source_input.attr( 'value', message );
                                    }
                                } else {
                                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                        $('.wtai-edit-product-line' ).find('#message').remove();
                                    }
                                    $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                                }
                                btnevent.removeClass('disabled');
                                if ( mce_editor_sel ){
                                    btnevent.closest('.postbox').find('.wp-editor-wrap').removeClass('wtai-loading-state');
                                } else {
                                    source_input.prop('disabled', false );
                                }
                            }
                        });
                        break;
                }
            }
        }
        e.preventDefault();
    });

    $(document).on( 'click', '.postbox  .handlediv', function(e) {
        var btnevent = $(this);
        var btnwrapper = btnevent.closest('.postbox');
        btnwrapper.toggleClass('closed');
        e.preventDefault();
    });    

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
            rewrite_toggle_credit_behavior();
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
                $(this).find('.postbox-header').find('.wtai-checkboxes').prop( 'checked', checked );
            }
        );

        //alt text
        $('#postbox-container-2').find('.wtai-alt-writetext-metabox').each(
            function(){
                if( $(this).hasClass('has-no-image') ){
                    $(this).find('.postbox-header').find('.wtai-checkboxes').prop( 'checked', false );
                    $(this).find('.inside').find('.wtai-checkboxes-alt').prop( 'checked', false );
                } else {
                    if( $(this).find('.inside').find('.wtai-checkboxes-alt').prop('disabled') == false ){
                        $(this).find('.postbox-header').find('.wtai-checkboxes').prop( 'checked', checked );
                        $(this).find('.inside').find('.wtai-checkboxes-alt').prop( 'checked', checked );
                    }
                }
            }
        );

        setTimeout(function(){
            record_preselected_field_types();
        }, 200);
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

    $(document).on( 'click', '.wtai-single-button-text', function(e){
        var button = $(this);
        var type = button.data('type');
        var submittype = button.data('submittype');
        var parentdiv = button.closest('.postbox').attr('id');
       
        if( $('#message.error').length ) {
            $('#message.error').remove();
        }
            
        if ( ! button.hasClass('disabled') ){
            var btnwrapper = button.closest('.postbox');
            var field_type = btnwrapper.find('.wtai-generate-text').data('type');
            var product_id = $('#wtai-edit-post-id').attr('value');
            var source_val = '';
            var id = '';

            if ( button.closest('.postbox').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                id = button.closest('.postbox').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                source_val =  wp.editor.getContent(id,{format:'html'}); // Visual tab is active
            } else if ( button.closest('.postbox').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                id = button.closest('.postbox').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                source_val =  wp.editor.getContent(id,{format:'html'}); // Visual tab is active
            } else if ( button.closest('.postbox').find('.wtai-columns-3').find('textarea').length > 0 ){
                source_val =  button.closest('.postbox').find('.wtai-columns-3').find('textarea').val();
            }

            var source_input = button.closest('.postbox').find('.wtai-columns-3').find('#wtai-wp-field-input-'+field_type );
            var source_newvalue = button.closest('.postbox').find('.wtai-data-new-text').html();
            var source_origvalue = button.closest('.postbox').find('.wtai-data-orig-text').html();

            if ( submittype == 'generate' && !button.closest('.postbox').hasClass('wtai-proceed') ) {
               
                // Compare the two contents
                if ( source_origvalue === source_newvalue ) {
                    // The content has not been changed
                } else {
                    button.closest('.postbox').addClass('wtai-proceed');
                    popupUnsavedGenerate( parentdiv,'single', type );
                    return false;
                }
            
            }  else {
                button.closest('.postbox').removeClass('wtai-proceed');
            }

            var date = new Date();
            var offset = date.getTimezoneOffset();
            var data = {};
            
            switch( submittype ){
                case 'generate':
                    
                    var queueAPI = 0;
                    if( field_type == 'product_excerpt' || field_type == 'product_description' ){
                        queueAPI = 1;
                    }

                    var wtai_nonce = get_product_edit_nonce();

                    data = {
                        action              : 'wtai_generate_text',
                        product_id          : product_id,
                        browsertime         : offset,
                        options             : WTAI_OBJ.option_choices,
                        fields              : type, 
                        save_generated      : 1,
                        queueAPI            : queueAPI,
                        doingBulkGeneration : '0',
                        wtai_nonce          : wtai_nonce
                    };
                   
                    if ( $('#wtai-woocommerce-product-attributes').length > 0 ){
                        var atts = [];
                        $('#wtai-woocommerce-product-attributes').find('.wtai-attr-checkboxes').each( function(){
                            if ( $(this).is( ':checked' ) ) {
                                atts.push( $(this).data('apiname') );
                            }   
                        });  
                        data['attr_fields'] =  atts.join(',');
                    }

                    if (  $('.wtai-product-form-container').find('.wtai-product-audiences-wrap').length > 0 ){
                        var audiences = [];
                        $('.wtai-product-form-container').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb:checked').each( function(){
                            if ( $(this).is( ':checked' ) ) {
                                audiences.push( $(this).val() );
                            }   
                        });   
                        data['audiences'] =  audiences.join(',');
                    }

                    if( $('.wtai-product-form-container').find('.wtai-input-text-suggested-audiance').length > 0 ){
                        data['customAudience'] =  $('.wtai-product-form-container').find('.wtai-input-text-suggested-audiance').val();
                    }

                    if (  $('.wtai-product-form-container').find('.wtai-product-tones-wrap').length > 0 ){
                        var tones = [];
                        $('.wtai-product-form-container').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb:checked').each( function(){
                            tones.push( $(this).val() );
                        }); 
                        data['tones'] =  tones.join(',');
                    }
        
                    if (  $('.wtai-product-form-container').find('.wtai-product-styles-wrap').length > 0 ){
                        data['styles'] =  $('.wtai-product-form-container').find('.wtai-product-styles-wrap').find('.wtai-product-styles-cb:checked').val();
                    }

                    if (  $('#wtai-wp-field-input-otherproductdetails').length > 0 ){
                        data['otherproductdetails'] =  $('#wtai-wp-field-input-otherproductdetails').val();
                    }
                    if ( btnwrapper.find('#'+type+'_length_min').length > 0 ){
                        data[type+'_length_min'] = btnwrapper.find('#'+type+'_length_min').val();
                    }
                    if ( btnwrapper.find('#'+type+'_length_max').length > 0 ){
                        data[type+'_length_max'] = btnwrapper.find('#'+type+'_length_max').val();
                    }

                    if (  $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').length > 0 ){
                        var semanticKeywords = [];
                        $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').each( function(){
                            semanticKeywords.push( $(this).find('.wtai-keyword-name').text() );
                        });   
                        data['semanticKeywords'] =  semanticKeywords.join(',');
                    }

                    if (  $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').length > 0 ){
                        var keywords = [];
                        $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').each( function(){
                            keywords.push( $(this).find('.wtai-keyword-name').text() );
                        });   
                        data['keywords'] =  keywords.join(',');
                    }

                    $(this).closest('.wtai-metabox').find('.selection').removeClass('selected');
                    
                    break;
                default:
                    var api_publish = 0;
                    if ( submittype == 'transfer' ){
                        api_publish = 1;
                    } else {
                        api_publish = 0;
                    }

                    var wtai_nonce = get_product_edit_nonce();

                    data = {
                        action: 'wtai_store_single_text',
                        browsertime: offset,
                        product_id:  product_id,
                        message_value: source_val, 
                        fields : field_type,
                        textid : button.closest('.postbox').find('#wtai-wp-field-input-'+field_type+'_id').attr('value'), 
                        publish: api_publish,
                        wtai_nonce: wtai_nonce,
                    };

                    break;
            }
       
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data, 
                beforeSend: function() {
                    $('.wtai-global-loader').addClass('wtai-is-active');
                    if ( submittype == 'generate' ){
                        
                        var id = button.closest('.postbox').find('textarea').attr('id');
                        clearEditor(id, type);

                        removeHighlightkeywordsByField(id);

                        if( window.WTAStreamConnected ){
                            var streamData = {
                                data : data,
                                elem : button.closest('.postbox').find('textarea'),
                                elemId : id,
                                textIndex : 0,
                                doingBulkGenerate : false
                            };

                            window.wtaStreamData[type] = streamData;
                        }

                        $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').removeClass('generated');
                    }
                    
                    button.closest('.postbox').addClass('wtai-disabled-click');                    
                },
                success: function(data) {
                    if( data.access ){
                        if ( data.message ){
                            if ( data.message == 'expire_token' ){
                                if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                    $('.wtai-edit-product-line' ).find('#message').remove();
                                }
                                $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                              
                            } 
                            else {
                                if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                                    $('.wtai-edit-product-line' ).find('#message').remove();
                                }
                                $('<div id="message" class="error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                               
                                $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');
                                if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                                    $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                                }

                                fetchFreshTextFromAPI( product_id, type, true );
                                
                                var id = button.closest('.postbox').find('textarea').attr('id');
                                updateHiddentext(id);
                                
                                if( window.WTAStreamConnected ){
                                    window.wtaStreamData[type] = [];
                                    window.wtaStreamQueueData[type] = [];
                                }
                            }
                        } else {
                            switch( submittype ){
                                case 'generate':
                                   
                                    if( queueAPI == 0 ){                                               
                                        $('#wtai-product-details-'+type).find('.inside').find('#wtai-wp-field-input-'+type+'_id').attr('value', data.results[product_id][type]['text_id'] );

                                        if( window.WTAStreamConnected ){
                                            var id = '';
                                            if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                                id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');

                                                tinymce.get(id).setContent( '' );
                                                $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').addClass('generated');
                                                tinymce.get(id).setContent( data.results[product_id][type]['text'].replace(/\n/g, '<br/>') );

                                            } else if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                                id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                                
                                                tinymce.get(id).setContent( '' );
                                                $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').addClass('generated');
                                                tinymce.get(id).setContent( data.results[product_id][type]['text'].replace(/\n/g, '<br/>') );
                                            }

                                            if( id ){                                        
                                                $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');
                                                if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                                                    $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                                                }

                                                typeCountMessage(type, tinymce.get(id).getContent({format: 'text'}) );

                                                updateHiddentext(id);
                                                try{
                                                    getKeywordOverallDensity();
                                                }
                                                catch( exc ){
                                                }
                                                
                                            }

                                            window.wtaStreamData[type] = [];
                                        }
                                        else{
                                            if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                                var id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                                typeWriter( id, data.results[product_id][type]['text'].replace(/\n/g, '<br/>'), 0, 50, type );

                                            } else if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                                var id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                                typeWriter( id, data.results[product_id][type]['text'].replace(/\n/g, '<br/>'), 0, 50, type );
                                            }
                                        }
                                        
                                        putGridAutomatic( product_id, type, data.results[product_id][type]['text'], data.results[product_id][type]['trim'], '' );
                                    }
                                    else{
                                        if( window.WTAStreamConnected == true ){
                                            var streamQueueData = {
                                                requestId : data.results.requestId,
                                                product_id : product_id,
                                                type : type,
                                                bulk : 0
                                            };

                                            window.wtaStreamQueueProcessing = true;
                                            window.wtaStreamQueueData[data.results.requestId] = streamQueueData;
                                        }
                                        else{
                                            //do fallback if singalr not present
                                            process_queue_generate( data.results.requestId, product_id, type );
                                        }
                                    }
                                    break;
                                default: 
                                    if ( data.results ){
                                        if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                            var id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                        } else if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                            var id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                        }      

                                        updateHiddentext(id);

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
                                                }     
                                            });
                                        });
                                    }
                                    if ( submittype == 'transfer' ) {
                                        var content = source_input.val();
                                        content = $('<div>').html(content);
                                        content.find('.wtai-highlight, .wtai-highlight2').contents().unwrap();
                                        var htmlContent = content.html();

                                        button.closest('.postbox').find('.wtai-current-value-wrapper').find('.wtai-current-text').find('p').html( htmlContent );
                                    } else if ( $('.wtai-review-check').prop('checked') ) {
                                        $('.wtai-review-check').click();
                                    }
                                    
                                    
                                    button.closest('.postbox').removeClass('wtai-disabled-click');
                                    
                                    if ( btnwrapper.hasClass('wtai-metabox-update') ){
                                        btnwrapper.removeClass('wtai-metabox-update');
                                    }

                                    break;
                            }
                        }
                        
                    } else {
                        if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                            $('.wtai-edit-product-line' ).find('#message').remove();
                        }
                        $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        button.closest('.postbox').removeClass('wtai-disabled-click');

                        if ( btnwrapper.hasClass('wtai-metabox-update') ){
                            btnwrapper.removeClass('wtai-metabox-update');
                        }
                    }                    
                }
            });
        }
       
        e.preventDefault();
    });

    
    $(document).on( 'click', '.product-description-select', function() {
        $('.product-description-select').removeAttr('checked');
        $(this).attr('checked', 'checked' );
        $(this).prop('checked', true );
    });

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

        var hasRefProd = false;
        var hasRefProdCheck = false;
        if( rewriteText != '1'){    
            var ischecked = $('input.wtai-custom-style-ref-prod').is(':checked') ;
            if ( ischecked ) {
                hasRefProdCheck = true;
            }
            if( ischecked && $('.wtai-custom-style-ref-prod-sel').val().trim() == '' ){
                $('.wtai-ref-product-form-postbox-wrapper .selectize-input').addClass('warning');
                has_error = true;
            } else if( ischecked && $('.wtai-custom-style-ref-prod-sel').val().trim() != '' ){
                $('.wtai-ref-product-form-postbox-wrapper .selectize-input').removeClass('warning');
                hasRefProd = true;
            }
        }

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
        
        var number_of_changes_unsave = checkChanges( 'generate' );
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
            var product_id = $('#wtai-edit-post-id').attr('value');
            var atts = [];
            $('#wtai-woocommerce-product-attributes').find('.wtai-attr-checkboxes').each( function(){
                if ( $(this).is( ':checked' ) ) {
                    atts.push( $(this).data('apiname') );
                }   
            });   
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

            var referenceProductID = '';
            if( $('#wtai-custom-style-ref-prod').is(':checked')) {
                var referenceProductData = $('select.wtai-custom-style-ref-prod-sel').val();
                var refProductArr = referenceProductData.split('-');
                referenceProductID = refProductArr[0];
            }

            if( ! referenceProductID ){
                $('.wtai-generate-wrapper .button-primary.toggle').addClass('wtai-generating');
            }

            var includeFeaturedImage = 0;
            if( $('.wtai-product-attr-image-wrap #wtai-product-main-image').is(':checked') && 
                $('#postbox-container-2 .wtai-metabox .wtai-checkboxes:checked').length > 0 ){
                includeFeaturedImage = 1;
            }

            var altimages = [];
            $('.wtai-product-alt-images-main-wrap .wtai-checkboxes-alt').each(function(){
                if( $(this).is(':checked') && $(this).prop('disabled') == false ){
                    altimages.push( $(this).val() );
                }
            });

            //get keyword analysis views count
            var keywordAnalysisViewsCount = $('.wtai-keyword-analysis-view').val();

            var fieldsToProcessSequence = ['page_title', 'page_description', 'product_description', 'product_excerpt', 'open_graph'];
            var fieldsToProcess = [];
            var fieldsToProcessSelected = [];

            $('#postbox-container-2').find('.wtai-metabox').each(
                function(){
                    if ( $(this).find('.postbox-header').find('.wtai-checkboxes').is( ':checked' ) ) {
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

            // Loader for alt text generation

            if( altimages.length > 0 ){
                $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled', true);
                $('.wtai-alt-writetext-metabox').addClass('wtai-disabled-click');
                
                $('.wtai-image-alt-metabox').each(function(){
                    if( $(this).find('.wtai-checkboxes-alt').is(':checked') && $(this).hasClass('wtai-error-upload') == false ){
                        $(this).find('.wtai-wp-editor-setup-alt').prop('disabled', true);
                        $(this).find('.wtai-checkboxes-alt').prop('disabled', true);
                        $(this).find('.wtai-wp-editor-setup-alt').val('');
                        $(this).find('.wtai-typing-cursor-alt-wrap').addClass('wtai-shown');
                        $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                        $(this).find('.wtai-generate-disable-overlay-wrap').removeClass('wtai-shown');
                        $(this).addClass('wtai-loading-state');
                    }
                    else if( $(this).find('.wtai-checkboxes-alt').is(':checked') && $(this).hasClass('wtai-error-upload') == true ){
                        $(this).find('.wtai-wp-editor-setup-alt').prop('disabled', true);
                        $(this).find('.wtai-checkboxes-alt').prop('disabled', true);
                        $(this).find('.wtai-wp-editor-setup-alt').val('');
                        $(this).find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                        $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                        $(this).find('.wtai-generate-disable-overlay-wrap').addClass('wtai-shown');
                        $(this).removeClass('wtai-loading-state');
                    }
                });
            }

            if( ( includeFeaturedImage == 1 || altimages.length > 0 ) && button.hasClass('wtai-pre-process-image-done') == false ){
                $('.wtai-image-alt-metabox').removeClass('wtai-error-upload');
                $('#postbox-container-2 .wtai-checkboxes-alt').prop('disabled', true);
                
                var loaderEstimatedTime = $('#wtai-loader-estimated-time');
                var preprocessImageLoader = $('#wtai-preprocess-image-loader');

                // Check if both elements exist
                if (preprocessImageLoader.length > 0 && loaderEstimatedTime.length > 0) {
                    if (loaderEstimatedTime.is(':visible') ) {
                        preprocessImageLoader.clone().appendTo(loaderEstimatedTime).show();
                    } else {
                        preprocessImageLoader.show();
                    }
                } 

                //added 2024.03.05
                $('#wpcontent').addClass('preprocess-image');
                
                //split the images into batches of 10
                var altImagesBatches = [];
                var aictr = 0;
                var aibatchid = 0;
                altImagesBatches[ aibatchid ] = [];

                $.each(altimages, function( index, alt_image_id ) {
                    if( aictr == 10 ){
                        aictr = 0;
                        aibatchid++;

                        altImagesBatches[ aibatchid ] = [];
                    }

                    altImagesBatches[ aibatchid ][ aictr ] = alt_image_id;

                    aictr++;
                });

                // counter goes here
                window.currentAltImageBatch = 0;
                window.maxAltImageBatchNo = aibatchid;
                window.altImageForUpload = altimages;
                window.altImageSuccessForUpload = [];
                window.altImageBatchForUpload = altImagesBatches;
                window.altImageIdsError = [];

                // Pre process images first.
                
                process_image_upload_single( product_id, altImagesBatches[0], includeFeaturedImage );

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

            $('.postbox.wtai-alt-writetext-metabox').attr('data-message', '');
            $('#wtai-product-generate-completed .wtai-loading-header-details').find('.alt-image-notice').html( '' );

            handle_single_bulk_buttons( 'disable' );

            setTimeout(function() {
                if ( $('#postbox-container-2').find('.wtai-bulk-process').length || altimages.length > 0 ) {
                    //set data and other details per field
                    if ( $('#postbox-container-2').find('.wtai-bulk-process').length || altimages.length > 0 ){
                        var queueAPI = 1; //set all fields to queue and not just product excerpt and product description
                        
                        var successful_image_for_upload = [];
                        var error_image_for_upload = [];
                        if( altimages.length > 0 ){
                            if( window.altImageSuccessForUpload.length != null ){
                                successful_image_for_upload = window.altImageSuccessForUpload;
                                error_image_for_upload = window.altImageIdsError;
                            }
                        } else {
                            if( includeFeaturedImage == 1 ){
                                error_image_for_upload = window.altImageIdsError;
                            }
                        }

                        var wtai_nonce = get_product_edit_nonce();

                        var data = {
                            action                    : 'wtai_generate_text',
                            product_id                : product_id,
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

                                                fetchFreshTextFromAPI( product_id, type, true );

                                                //meta_object.addClass('wtai-bulk-complete');
                                                meta_object.removeClass('wtai-disabled-click');
                                                meta_object.find('.wtai-checkboxes').prop('disabled', false);
                                            });

                                            if( altimages.length > 0 ){
                                                $('.wtai-image-alt-metabox').removeClass('wtai-loading-state');
                                                $('.wtai-image-alt-metabox').removeClass('wtai-bulk-complete');
                                                $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
                                                $('.wtai-alt-writetext-metabox').removeClass('wtai-disabled-click');
                                                $('.wtai-image-alt-metabox .wtai-wp-editor-setup-alt').prop('disabled', false);

                                                $('.wtai-page-generate-all').removeClass('wtai-pre-process-image-done');

                                                fetchFreshImageAltTextFromAPI( altimages.join(','), '0', '1' );                                                
                                            }

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
                                    } 
                                    else{
                                        if( data.results ){
                                            if( queueAPI == 0 ){
                                                var doProcessTextGeneration = true; //debug signal r set to false
                                                if( $('body.wtai-open-single-slider').length && $('#wtai-edit-post-id').length ){

                                                    var current_post_id = $('#wtai-edit-post-id').val();
                                                    if( parseInt( current_post_id ) != product_id ){
                                                        $('.wtai-global-loader').removeClass('wtai-is-active');

                                                        doProcessTextGeneration = false;
                                                    }
                                                }

                                                if( doProcessTextGeneration ){       
                                                    $.each(fieldsToProcess, function( key_bulk, type ){      
                                                        var meta_object = $('.wtai-metabox-' + type);

                                                        $('#wtai-product-details-'+type).find('.wtai-generate-textarea-wrap .mce-edit-area').addClass('generated');

                                                        $('#wtai-product-details-'+type).find('.inside').find('#wtai-wp-field-input-'+type+'_id').attr('value', data.results[product_id][type]['text_id'] );
                                                        
                                                        if( window.WTAStreamConnected ){                                                        
                                                            var id = '';
                                                            if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                                                id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                                                if( tinymce.get(id) ){
                                                                    tinymce.get(id).setContent( data.results[product_id][type]['text'].replace(/\n/g, '<br/>') );
                                                                }
                                                            } else if ( $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                                                id = $('#wtai-product-details-'+type).find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                                                if( tinymce.get(id) ){
                                                                    tinymce.get(id).setContent( data.results[product_id][type]['text'].replace(/\n/g, '<br/>') );
                                                                }
                                                            }

                                                            if( tinymce.get(id) ){                                     
                                                                $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');

                                                                if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                                                                    $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                                                                }

                                                                typeCountMessage(type, tinymce.get(id).getContent({format: 'text'}) );
                                                                
                                                                updateProductGridGenerateSave(id); 

                                                                fetchFreshTextFromAPI( product_id, type, true );
                                                            
                                                                updateHiddentext(id);
                                                                try{
                                                                    getKeywordOverallDensity();
                                                                }
                                                                catch( exc ){
                                                                }
                                                            }

                                                            window.wtaStreamData[type] = [];
                                                        }
                                                        else{ 
                                                            if ( meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').length > 0 ){
                                                                var id = meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');
                                                            } else if ( meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').length > 0 ){
                                                                var id = meta_object.find('.inside').find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                                                            }

                                                            $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');
                                                            if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                                                                $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
                                                            }
                                                            
                                                            typeWriterBulk( id, data.results[product_id][type]['text'].replace(/\n/g, '<br/>'), 0, 100, type );
                                                            putGridAutomatic( product_id, type, data.results[product_id][type]['text'], data.results[product_id][type]['trim'], '' );

                                                            meta_object.addClass('wtai-bulk-writing');
                                                            meta_object.addClass('wtai-bulk-complete');
                                                            meta_object.removeClass('wtai-disabled-click');
                                                            meta_object.find('.wtai-checkboxes').prop('disabled', false);
                                                            updateHiddentext(id);
                                                        }
                                                    });
                                                }
                                            }
                                            else{
                                                $.each(fieldsToProcess, function( key_bulk, type ){
                                                    if( window.WTAStreamConnected ){
                                                        window.wtaStreamQueueProcessing = true;
                                                    }
                                                    else{
                                                        //do fallback if singalr not present
                                                        process_queue_generate( data.results.requestId, product_id, type );
                                                    }
                                                });

                                                if( altimages.length > 0 ){
                                                    if( window.WTAStreamConnected ){
                                                        window.wtaStreamQueueProcessing = true;
                                                    }
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
                        if( altimages.length > 0 ){
                            conditions++;

                            if( $('.wtai-image-alt-metabox.wtai-loading-state:not(.wtai-bulk-complete)').length == 0 ){
                                passed_conditions++;
                            }
                        }

                        var passedAllCondition = false;
                        if( conditions == passed_conditions ){
                            passedAllCondition = true;
                        }

                        if ( ( ( $('#postbox-container-2').find('.wtai-bulk-process').length && $('.wtai-bulk-process:not(.wtai-bulk-complete)').length == 0 ) || 
                            ( altimages.length > 0 && $('.wtai-image-alt-metabox.wtai-loading-state:not(.wtai-bulk-complete)').length == 0 ) ) && 
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
                            
                            //remove completed class
                            $('.wtai-image-alt-metabox').removeClass('wtai-loading-state');
                            $('.wtai-image-alt-metabox').removeClass('wtai-bulk-complete');
                            $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
                            $('.wtai-alt-writetext-metabox').removeClass('wtai-disabled-click');
                            $('.wtai-image-alt-metabox .wtai-wp-editor-setup-alt').prop('disabled', false);

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
                                    popupGenerateCompleted('show', singleGenerationErrorFields);

                                    reset_image_alt_local_data();
                                    singleGenerationErrorFields = [];
                                    window.wtaStreamQueueProcessing = false;

                                    $('.wtai-image-alt-metabox').removeClass('wtai-error-upload');

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
            var wtai_nonce = get_product_edit_nonce();

            $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_store_bulk_text',
                browsertime : offset,
                product_id  :  $('#wtai-edit-post-id').attr('value'),
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
                                            case 'product_description':
                                            case 'product_excerpt':
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
                                        if( post_field_type == 'product_description' || post_field_type == 'product_excerpt' ){
                                            generatedText = htmlContent;
                                        }
                                        else{
                                            //generatedText = htmlContent.replace(/\n/g, '<br>');
                                            generatedText = htmlContent;
                                            generatedText = wtaiRemoveLastBr( generatedText );
                                        }

                                        var generatedCharCount = 0;
                                        if( post_field_type == 'product_description' || post_field_type == 'product_excerpt' ){
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

                                    rewrite_toggle_credit_behavior();

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

                                updateToolTipForTransferSingleButton( 1 );
                                
                                // hide review status flags
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( '' );
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html('');
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').addClass('hidden');
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
                    if ( submittype != 'bulk_transfer' ){
                        handle_single_transfer_button_state();
                    }

                    bulk_transfer_button_behavior();
                }
            }); 
        }

        // Save / Transfer alt image texts
        var altValues = [];
        $('#postbox-container-2').find('.wtai-image-alt-metabox').each( function(){
            var data_object = $(this);

            var saveField = false;
            if ( submittype == 'bulk_transfer' ){
                if( data_object.find('.wtai-checkboxes-alt').is(':checked') == true ){
                    saveField = true;
                }
            }
            else{
                saveField = true;

                /*var source_newvalue = data_object.find('.wtai-data-new-text').html();
                var source_origvalue = data_object.find('.wtai-data-orig-text').html();
                if ( source_origvalue === source_newvalue ) {
                } else {
                    saveField = true;
                }*/
            }

            var attachment_id = $(this).attr('data-id');
            var text_id = data_object.find('.wtai-api-data-image_alt_text_id').val();
            var alt_text = data_object.find('.wtai-wp-editor-setup-alt').val();

            if( saveField && text_id != '' && alt_text != '' ){
                var value = {
                    attachment_id: attachment_id,
                    text_id: text_id,
                    alt_text: alt_text,
                }

                altValues.push( value );
            }
        }); 

        if( altValues ){
            var product_id = $(this).attr('data-product-id');

            var submit_type = '';
            if ( submittype == 'bulk_transfer' ){
                submit_type = 'transfer';
            }
            handle_alt_text_save_and_transfer( product_id, altValues, api_publish, submit_type );
        }
    }
    e.preventDefault();
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

$(document).on('click', '.wtai-cwe-action-title', function(e){
    e.preventDefault();

    if( $(this).hasClass('disabled_on_edit_button') ){
        return;
    }

    $(this).parent().find('.wtai-cwe-action-button.wtai-cwe-action-button-edit').trigger('click');
});

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

$(document).on('click', '.wtai-cwe-action-button', function(e){
        $('#wtai-restore-global-setting-completed').hide();

        var event = $(this);
        var action = event.data('action');
        var event_tr = event.closest('tr');
        if ( event_tr.find('.wtai-cwe-selected').is(':disabled') ){
            return false;
        }
       
        //bypass disabled button
        if ( event.hasClass('wtai-disabled-button') ){
            return false;
        }

        //bypass when the edit html page is currently loading
        if ( event.hasClass('disabled_on_edit_button') ){
            return false;
        }
        
        if( window.WTAStreamConnected == true ){
            if( $('.api-connection-dot').length ){
                $('.api-connection-dot').removeClass('connected');
                $('.api-connection-dot').removeClass('disconnected');
                $('.api-connection-dot').addClass('connected');

                $('.api-connection-dot').attr('title', WTAI_STREAMING_OBJ.connectedText);
            }
        }

        var post_id = event_tr.data('id');

        switch(  action ){
            case 'edit':
                $('html, body').scrollTop(0);

                topheader_post(); // maybe reposition the header on mobile when single edit product is clicked.

                //clear queue processing whenever we open a new product

                window.wtaStreamData = [];
                window.wtaStreamQueueData = [];
                window.wtaStreamQueueProcessing = false;

                // Reset stream data for keyword analysis
                window.keywordIdeasStartAnalysis = false;
                window.keywordIdeasQueueRequestId = '';
                window.keywordIdeasSource = 'all';
                window.keywordIdeasSourceType = 'all';
                
                $('#wpwrap').addClass('wtai-overlay');
                
                bulk_popup_position('single');

                $('.wtai-global-loader').addClass('wtai-is-active');
                $('.wtai-ai-logo').addClass('wtai-hide');

                var data = event_tr.data('values');

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
                            elementobject.html( event_tr.find('.wtai_title').find('a').html() );
                            break;
                        case 'product_sku':
                            elementobject.html( event_tr.find('.wtai_title').find('a').attr('data-sku') );
                            break;
                        case 'post_permalink':
                            elementobject.attr( 'href', event_tr.find('.wtai_title').find('a').attr('href') );
                            elementobject.html( event_tr.find('.wtai_title').find('a').attr('href') );
                            break;
                        case 'post_id':
                            elementobject.attr( 'value', post_id );
                            break;
                    
                        default:
                            elementobject.html( data[postfield] );
                            break;
                    }
                });
                
                setTimeout(function() {
                    var wtai_nonce = get_product_edit_nonce();

                    $('body').addClass('wtai-open-single-slider');
                
                    $('#wpwrap').addClass('wtai-loader'); 
                    $('.wtai-slide-right-text-wrapper').addClass('wtai-disabled-click');
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_product_data',
                            product_id: post_id,
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
                                productSingleDataResponse ( res );

                                $('.wtai-slide-right-text-wrapper').removeClass('wtai-disabled-click');

                                getKeywordOverallDensity();

                                //handle keyword density state
                                handle_density_premium_state( res.result['is_premium'] );
                            }
                        }
                    });
                }, 300);
            
                getDataPerProductBlockInit( post_id, 1 );
                bulk_transfer_button_behavior();
                break;
            case 'generate':
                maybe_disable_bulk_actions( '1' );

                $('#TB_closeWindowButton').click();

                var date = new Date();
                var offset = date.getTimezoneOffset();

                restartPollBackgroundTimer();
                maybe_remove_done_bulk_container();

                //lets load temp notif data
                if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                    //lets load a temp container
                    loader_generate_temp_container( 1, [post_id] );
                }

                bulkGenerateOneOngoing = true;
                bulkGenerateOneOngoingID = post_id;

                var wtai_nonce = get_product_bulk_nonce();

                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        browsertime: offset,
                        action: 'wtai_generate_text',
                        product_id: post_id, 
                        single_result: 1,
                        save_generated: 1,
                        no_settings_save: 1, 
                        queueAPI: 1,
                        bulkOneOnly: 1,
                        doingBulkGeneration: '1',
                        wtai_nonce : wtai_nonce
                    },
                    beforeSend: function() {
                        event_tr.find('.wtai-cwe-selected').prop('disabled', true );
                        event_tr.find('.wtai-cwe-selected').prop('checked', false );
                        event_tr.addClass('wtai-processing');
                    },
                    success: function( data ){
                        var closeProcessing = false;
                        if( data.access ){
                            if ( data.message ) {
                                if ( data.message == 'expire_token' ){
                                    if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                        $('.wtai-table-list-wrapper' ).find('#message').remove();
                                    }
                                    $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                                } else {
                                    if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                        $('.wtai-table-list-wrapper' ).find('#message').remove();
                                    }
                                    $('<div id="message" class="error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                                }
                                closeProcessing = true;
                            } 
                            else{
                                if( window.WTAStreamConnected == false ){
                                    process_bulk_generate('');
                                }
                            }
                        } else {
                            var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                            var class_name = 'error notice ';
                            if ( message ){
                                $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                            }
                            closeProcessing = true;
                        }  

                        if( closeProcessing ){
                            event_tr.find('.wtai-cwe-selected').prop('disabled', false );
                            event_tr.removeClass('wtai-processing');
                        }
                    }
                }); 
                
                break;         
            case 'transfer':
                tb_show('<h2>'+WTAI_OBJ.bulk_transfer+'</h2><p>'+WTAI_OBJ.attribute_guide+'</p>','#TB_inline?&width=525&inlineId=wtai-bulk-transfer-modal');
                $('#TB_window').addClass('wtai-tb-window-modal-transfer');
                $('#TB_window').find('#TB_ajaxWindowTitle').css({'width':'492px'});
                $('#TB_window').find('.button-primary').data('id', post_id );
                $('#TB_window').find('.button-primary').attr('id', 'transfer-single-btn' );
                break;                
        }
        
        e.preventDefault();
    });

    $(document).on('click', '.wtai-cwe-action-button-history', function(e){
        var event = $(this);
        var post_id = event.data('id');
        var post_link = event.attr('href');
        var post_name = event.text();
        var data = event.data('values');
        $('body').removeClass('wtai-history-global-open');
        $('.wtai-main-wrapper').removeClass('wtai-history-global-open');
        $('.wtai-btn-close-history-global').css('visibility','hidden');
        $('.wtai-post-data-json').each(function(){
            var postfield = $(this).data('postfield');
            var elementobject = $(this);
            switch( postfield ){
                case 'post_title':
                    elementobject.html(post_name);
                    break;
                case 'post_permalink':
                    elementobject.attr( 'href', post_link );
                    elementobject.html( post_link);
                    break;
                case 'post_id':
                    elementobject.attr( 'value', post_id );
                    break;
                default:
                    elementobject.html( data[postfield] );
                    break;
            }
        });
     
        $('#wpwrap').find('.wtai-wp-editor-setup').each(function(){
            var id = $(this).attr('id');
            wp.editor.initialize( 
                id,
                {
                    tinymce: {
                        wpautop: true,
                        plugins : 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern',
                        toolbar1: 'formatselect bold italic underline strikethrough numlist bullist blockquote alignleft aligncenter alignright | link unlink',
                        visual : true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: '.wtai-highlight{background-color: #96C3F3; color: #303030; } .wtai-highlight2{background-color: #E9E2F2;color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: #303030;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width:5px; height:16px; display:inline-block; }@keyframes changed {from {rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}'
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
                        content_style: 'body *{box-sizing: border-box;}.wtai-highlight{background-color: #96C3F3; color: transparent; } .wtai-highlight2{background-color: #E9E2F2;color: transparent;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: transparent;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width:5px; height:16px; display:inline-block; }@keyframes changed {from {rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.2;}'
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
                        toolbar: '',
                        visual : true,
                        selector: 'textarea',
                        content_css: false,
                        content_style: '.wtai-highlight{background-color: #96C3F3; color: #303030;} .wtai-highlight2{background-color: #E9E2F2; color: #303030;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: #303030;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width: 5px; height:16px; display:inline-block;}@keyframes changed {from {background-color:rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}',
                        setup: function (editor) {
                            editor.on('keyup', function () {
                                if ( $('.wtai-bulk-process').length == 0 ) {
                                    $('.wtai-page-generate-all').removeClass('disabled');
                                    $('.wtai-page-generate-all').removeClass('wtai-generating');
                                    $('.wtai-generate-wrapper .toggle').removeClass('disabled');
                                    $('#publishing-action .wtai-button-interchange').removeClass('disabled');
                                }
                            });
                        }
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
                        selector: 'textarea',
                        content_css: false,
                        content_style: '.wtai-highlight{background-color: #96C3F3; color: transparent;} .wtai-highlight2{background-color: #E9E2F2; color: transparent;} body { background-color:transparent;font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif; font-size: 13px; line-height: 17px; color: transparent;} .emoji { width:1em!important;height:1em!important; } p { margin:0 0 18px;} .typing-cursor { background: url(' + WTAI_OBJ.WTAI_DIR_URL + 'assets/images/loader_cursor.gif) 0 center no-repeat; width: 5px; height:16px; display:inline-block;}@keyframes changed {from {background-color:rgba(19, 94, 150, 0.125);}to {background-color: #fff;}}body.bgdone{animation-name: changed;animation-duration: 1.5s;animation-iteration-count: 1;animation-timing-function: ease-out;}blockquote{display: block;margin: 1em;}h1,h2,h3,h4,h5,h6{line-height:1.2;}',
                        setup: function (editor) {
                            editor.on('keyup', function () {
                                if ( $('.wtai-bulk-process').length == 0 ) {
                                    $('.wtai-page-generate-all').removeClass('disabled');
                                    $('.wtai-page-generate-all').removeClass('wtai-generating');
                                    $('.wtai-generate-wrapper .toggle').removeClass('disabled');
                                    $('#publishing-action .wtai-button-interchange').removeClass('disabled');
                                }
                            });
                        }
                    },
                    quicktags: false
                }, 
            );
        });
        
        $('#wpwrap').addClass('wtai-overlay');
        setTimeout(function() {
            $('body').addClass('wtai-open-single-slider');
            $('.wtai-ai-logo').addClass('wtai-hide');
            $('#wpwrap').addClass('wtai-loader'); 
        }, 300);

        var wtai_nonce = get_product_edit_nonce();
        
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_product_data',
                product_id: post_id,
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
                    productSingleDataResponse( res );
                }
            }
        });
        
        getDataPerProductBlockInit( post_id, 0 );
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
                
                var wtai_nonce = get_product_edit_nonce();

                var data = {
                    action: 'wtai_single_product_history',
                    product_id: $('#wtai-edit-post-id').attr('value'),
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

    $(document).on('click', '.wtai-history.wtai-history-single .wtai-token-readmore-btn',function(e){
        var event = $(this);
        if ( ! event.hasClass('disabled') ){
            var date = new Date();
            var offset = date.getTimezoneOffset();
            var wtai_nonce = get_product_edit_nonce();
            var data = {
                action: 'wtai_single_product_history',
                product_id: $('#wtai-edit-post-id').attr('value'),
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
                
                var wtai_nonce = get_product_bulk_nonce();

                var data = {
                    action: 'wtai_global_product_history',
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
                            
                        } else {
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
            var wtai_nonce = get_product_bulk_nonce();

            var data = {
                action: 'wtai_global_product_history',
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

            if( $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
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
                        
                    } else {
                        $('.wtai-table-list-wrapper')
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

    //close single slider
    $(document).on('click', '.wtai-slide-right-text-wrapper .wtai-close', function(){
      
        popupGenerateCompleted('hide');

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
            var number_of_changes_unsave = checkChanges( 'nav' );
            if ( number_of_changes_unsave > 0 ) {
                popupUnsaved('close');
                return false;
            }
            producteditformClose();

            if( $('.wtai-global-loader').hasClass('wtai-is-active') ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-keyword .wtai-keyword-input').removeClass('border');
            }

            window.wtaStreamData = [];
            window.wtaStreamQueueData = [];
            window.wtaStreamQueueProcessing = false;
        }
        else{
            producteditformClose();
        }
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
                var wtai_nonce = get_product_edit_nonce();

                if( $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-slide-right-text-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

               setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_single_product_history',
                            product_id: $('#wtai-edit-post-id').attr('value'),
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
                                    
                                } else {
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
                var wtai_nonce = get_product_bulk_nonce();

                if( $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').length ){
                    $('.wtai-table-list-wrapper').find('.wtai-history-content .wtai-no-history-found').remove();
                }

               setTimeout(function() {
                    $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_global_product_history',
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
                                    
                                } else {
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

    function getDataPerProductBlockInit( post_id, refresh_credits = 1 ){        
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

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_single_product_data_text',
                product_id: post_id,
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
                    $('.wtai-header-title .wtai-product-sku.wtai-post-data-json').html(res.result.product_sku);
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

                    //load reference product list 
                    setDynamicReferenceProducts( res.result.product_reference_id_data, 0 );

                    //load early data
                    if( $('.wp-list-table').attr('data-doing-prev-next') == '1' ){
                        $('.wp-heading-inline.wtai-post-title.wtai-post-data-json').html(res.result['wp_product_title']);
                        $('#misc-publishing-actions .curtime.misc-pub-curtime #timestamp').html(res.result['post_publish_date']);
                        $('#misc-publishing-actions .misc-pub-post-status #post-status-display').html(res.result['post_status']);
                        $('#misc-publishing-actions .misc-pub-visibility #post-visibility-display').html(res.result['post_visibility']);
                        $('.wtai-header-title .wtai-post-title').css('visibility','visible');
                        $('.wtai-header-title .wtai-product-sku').css('visibility','visible');
                        $('.wp-list-table').attr('data-doing-prev-next', '');
                    }

                    $('.wtai-post-data').each(function(){
                        var postfield = $(this).data('postfield');
                        var elementobject = $(this);
                        if( postfield == 'otherproductdetails' ){
                            elementobject.val( res.result[postfield] );
                            elementobject.prop( 'disabled', false );

                            var otherProductDetailsCharLength = res.result[postfield].length;
                            elementobject.closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html(otherProductDetailsCharLength);
                        }
                        if( postfield == 'otherproductdetails_checked' ){
                            if ( res.result['otherproductdetails'] ) {
                                elementobject.prop('checked', true );
                            } else {
                                elementobject.prop('checked', false );
                            }
                        }
                        if( postfield == 'product_attr' ){
                            elementobject.find('li:not(.text)').remove();
                            elementobject.prepend( res.result[postfield] );
                        }
                        if( postfield == 'post_permalink' ){
                            elementobject.attr( 'href', res.result[postfield] );
                            elementobject.html( res.result[postfield] );
                        }
                    });

                    $('#wtai-woocommerce-product-attributes .postbox-content ul li input').css('visibility','visible');
                    $('#wtai-woocommerce-product-attributes .postbox-content ul li label').css('visibility','visible');
                    $('#wtai-woocommerce-product-attributes .postbox-content ul li .wtai-otherproddetails-container').css('visibility','visible');

                    //load product attrbites checked
                    var preference_product_attributes = res.result.preference_product_attributes;

                    if( preference_product_attributes.length > 0 ){
                        $('#wtai-woocommerce-product-attributes.postbox.wtai-metabox .wtai-attr-checkboxes').prop('checked', false);
                        
                        $.each(preference_product_attributes, function( index, prod_attr_pref ){
                            $('#wtai-woocommerce-product-attributes.postbox.wtai-metabox .wtai-attr-checkboxes').each(function(){
                                if( $(this).attr('data-apiname') == prod_attr_pref ){
                                    $(this).prop('checked', true);
                                }
                            });
                        });                            
                    }

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
                            case 'product_description':
                            case 'product_excerpt':
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

                        //display extension review status
                        var extension_reviews_html = res.result[type+'_extension_reviews_html'];

                        data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( '' );
                        data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html('');
                        data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').addClass('hidden');

                        var extension_review_count = 0;
                        if( extension_reviews_html.length !== 0 ){
                            if( extension_reviews_html.popupinfo_html != '' ){
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-comment-form').html( extension_reviews_html.popupinfo_html );
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite .wtai-extension-review-label').html( extension_reviews_html.status_label_global );
                                data_object.find('.wtai-status-postheader .wtai-status-label.wtai-status-label-rewrite').removeClass('hidden');

                                extension_review_count++;
                            }
                        }

                        data_object.find('.postbox-header').removeClass('wtai-has-status');
                        data_object.find('.postbox-header').removeClass('one');
                        data_object.find('.postbox-header').removeClass('wtai-two');

                        if( extension_review_count == 1 ){
                            data_object.find('.postbox-header').addClass('wtai-has-status');
                            data_object.find('.postbox-header').addClass('one');
                        }
                        else if( extension_review_count == 2 ){
                            data_object.find('.postbox-header').addClass('wtai-has-status');
                            data_object.find('.postbox-header').addClass('wtai-two');
                        }
                    });

                    // Product images display html
                    var main_image_product_html = res.result['main_image_product_html'];
                    $('.wtai-product-attr-image-wrap').html( main_image_product_html );

                    var alt_images_html = res.result['alt_images_html'];
                    var product_has_image = res.result['product_has_image'];
                    $('.wtai-product-alt-images-main-wrap').html( alt_images_html );

                    // Enable or disable image alt field.
                    $('.wtai-image-alt-metabox .wtai-wp-editor-setup-alt').prop('disabled', false);
                    $('.wtai-image-alt-metabox').removeClass('wtai-bulk-complete');
                    $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
                    $('.wtai-alt-writetext-metabox').removeClass('wtai-disabled-click');

                    // update count for alt texts
                    $('.wtai-image-alt-metabox').each(function(){
                        var image_id = $(this).attr('data-id');
                        var image_elem_id = $(this).find('.wtai-wp-editor-setup-alt').attr('id');
                        var alt_text = $(this).find('.wtai-wp-editor-setup-alt').val();
                        var current_alt_text = $(this).find('.wtai-current-value-wrapper .wtai-current-text .wtai-current-value p').text();

                        updateHiddentextTexarea( image_elem_id );
                        typeCountMessageAltImage( image_id, alt_text );

                        // Update current image count
                        var words_count_alt = wtaiGetWordsArray( current_alt_text );

                        var textLengthAlt = 0;
                        var wordsLengthAlt = 0;
                        if( words_count_alt.length > 0 ){
                            textLengthAlt = current_alt_text.length;
                            wordsLengthAlt = words_count_alt.length;
                        }

                        $(this).find('.wtai-current-value-wrapper .wtai-static-count-display .wtai-char-count').text( textLengthAlt );
                        $(this).find('.wtai-current-value-wrapper .wtai-static-count-display .word-count').text( wordsLengthAlt );

                        if( alt_text == '' ){
                            $(this).find('.wtai-generated-status-label').html( WTAI_OBJ.notGeneratedStatusText );
                            $(this).find('.wtai-alt-transferred-status-label').addClass('wtai-hide-not-transferred-label');
                            $(this).find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                        }
                    });

                    $('.postbox.wtai-alt-writetext-metabox').removeClass('wtai-has-image');
                    $('.postbox.wtai-alt-writetext-metabox').removeClass('has-no-image');
                    if( product_has_image == '1' ){
                        $('.postbox.wtai-alt-writetext-metabox').addClass('wtai-has-image');
                        $('.wtai-tooltip .wtai-tooltiptext.wtai-alt-image-text-tooltip').removeClass('bottompos');
                    }
                    else{
                        $('.postbox.wtai-alt-writetext-metabox').addClass('has-no-image');
                        $('.wtai-tooltip .wtai-tooltiptext.wtai-alt-image-text-tooltip').addClass('bottompos');
                    }

                    //trigger change of generate all button credit count
                    updateGenerateAllButtonCreditCount();

                    rewrite_toggle_credit_behavior(); //set credit count for rewrite

                    handle_single_transfer_button_state();
                    bulk_transfer_button_behavior(); //state for transfer bulk button single

                    initializeToolTipForSingleTransferButtons();
                    updateToolTipForTransferSingleButton( 1 ); //update tooltip for single product button

                    setTimeout(function() {
                        updateToolTipForReferenceProduct( '...', 0, 'full' );      
                    }, 300);

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
                    //disable_alt_images_for_reference_and_rewrite();

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
                    //commented out by mcr 2024.03.04
                    //adjust_current_text_max_width();
                    //adjust_current_text_max_width_altimage();

                    // Fresh Nonce verification
                    $('#wtai-edit-product-line-form').attr('data-product-nonce', res.result['product_edit_nonce']);
                }
            }
        });  
    }
    
    $(document).on('click', '.wtai-product-attr-cb', function(){
        if($('.wtai-product-attr-cb:checked').length > 0 ) {
            $('#TB_ajaxContent').find('button.button-primary').prop('disabled', false);
            $('#bulk-update-product-attr').prop('disabled', false);
        } else {
            $('#TB_ajaxContent').find('button.button-primary').prop('disabled', true);
            $('#bulk-update-product-attr').prop('disabled', true);
        }
    });

    $(document).on('click', '.wtai-action-bulk-ok-all', function(e){
        e.preventDefault();

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        var productIds = [];
        $('.wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container').each(function(){
            var job_status = $(this).data('data-job-status');
            var rowProductIds = $(this).data('data-product-ids');
            if( rowProductIds != '' && job_status == 'done' ){
                rowProductIdsArray = rowProductIds.split(',');
                $.each(rowProductIdsArray, function( index, productID ) {
                    productIds.push( productID );
                });
            }
        });

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        restartPollBackgroundTimer();

        var requestIDs = [];
        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container').each(function(){
            if( $(this).find('.wtai-bulk-generate-submit').length > 0 ){
                var reqID = $(this).find('.wtai-bulk-generate-submit').attr('data-request-id');
                requestIDs.push( reqID );
            }
        });

        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_bulk_dismiss_all',
                show_hidden: show_hidden,  
                requestID: '',  
                requestIDs: requestIDs.join(','),
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                if( data.disable_bulk_generate == '0' ){
                    $('#bulk-action-selector-top').prop('disabled', false );//enable this for your own user account
                    $('.bulkactions .action').removeClass('disabled'); //added
                    $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                    $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');
                }

                $.each( productIds, function( index, product_id ){
                    $('#the-list #wtai-table-list-' + product_id).removeClass('wtai-processing');
                    $('#the-list #wtai-table-list-' + product_id + ' .wtai-cwe-selected').prop('disabled', false);
                });

                if( data.html != '' ){
                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( data.html );
                    $('#wtai-loader-estimated-time').show();
                }         
                
                $('.wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container').each(function(){
                    var job_status = $(this).data('data-job-status');
                    if( job_status == 'done' ){
                        $(this).remove();
                    }
                });

                handleDismissAllDisplay();
                handleLoaderNotifShowDisplay();
                shouldHideGenerateLoader();

                maybeReenablePendingBulkIds(data.all_pending_ids);

                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }
        });
    });

    function okBulkGenerateOK( requestID ){
        restartPollBackgroundTimer();

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_bulk_success',
                show_hidden: show_hidden,  
                requestID: requestID,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    }

    $(document).on('click', '.wtai-bulk-generate-submit', function(e){
        e.preventDefault();
         
        if( $(this).hasClass('disabled') ){
            return;
        }
        
        $('.wtai-ai-logo').addClass('wtai-hide');
        $('.wtai-global-loader').addClass('wtai-is-active');

        var parentLoader = $(this).closest('.wtai-loading-estimate-time-container');
        var productIds = parentLoader.attr('data-product-ids').split(',');
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        restartPollBackgroundTimer();

        var requestID = $(this).attr('data-request-id');
        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_bulk_success',
                show_hidden: show_hidden,  
                requestID: requestID,
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                if( data.is_own_request == '1' ){
                    $('#bulk-action-selector-top').prop('disabled', false );//enable this for your own user account
                    $('.bulkactions .action').removeClass('disabled'); //added
                    $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                    $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');
                }

                $.each( productIds, function( index, product_id ){
                    $('#the-list #wtai-table-list-' + product_id).removeClass('wtai-processing');
                    $('#the-list #wtai-table-list-' + product_id + ' .wtai-cwe-selected').prop('disabled', false);
                });

                if( data.html != '' ){
                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( data.html );
                    $('#wtai-loader-estimated-time').show();

                    handleLoaderNotifShowDisplay();
                }
                
                parentLoader.remove();
                shouldHideGenerateLoader();
                handleDismissAllDisplay();

                maybeReenablePendingBulkIds(data.all_pending_ids);
                
                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }   
        });
    });

    function shouldHideGenerateLoader(){
        var preprocessImageLoader = $('#wtai-loader-estimated-time').find('#wtai-preprocess-image-loader');
        var confirmationProceedImageLoader = $('#wtai-loader-estimated-time').find('#wtai-confirmation-proceed-image-loader');
        var pagegenerateall = $('.wtai-page-generate-all').hasClass('wtai-generating');
        if( preprocessImageLoader.length > 0 || confirmationProceedImageLoader.length > 0 ){
            if( pagegenerateall ){
                $('#wtai-loader-estimated-time').find('.wtai-bulk-popup-wrapper').addClass('hidden');
                $('#wtai-loader-estimated-time').addClass('no-pad-top');
            }

            if( $('.wtai-loading-estimate-time-container').length <= 0 ){
                $('#wtai-loader-estimated-time').hide();
            }
            if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html().trim() == '' ){
                $('#wtai-loader-estimated-time').hide();
            }
        } else {
            if( $('.wtai-loading-estimate-time-container').length <= 0 ){
                $('#wtai-loader-estimated-time').hide();
            }
            if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html().trim() == '' ){
                $('#wtai-loader-estimated-time').hide();
            }
        }
    }

    $(document).on('click', '.bulk-generate-cancel', function(e){
        if( $(this).hasClass('disabled') ){
            return; //bypass cancellation if disabled
        }

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        var parentLoader = $(this).closest('.wtai-loading-estimate-time-container');
        var request_id = $(this).attr('data-request-id');

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        restartPollBackgroundTimer();

        if( bulkGenerateAJAX != null ){
            bulkGenerateAJAX.abort();
        }

        // Abort all background requests
        $.each(ajaxBulkRequests, function(index, request) {
            request.abort();
        });

        parentLoader.find('.wtai-loading-details-container').addClass('wtai-bulk-cancelling');
        parentLoader.find('.wtai-loading-header-text').text( WTAI_OBJ.bulkCancellingText );
        $(this).addClass('disabled');

        var wtai_nonce = get_product_bulk_nonce();
        
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_bulk_cancel',  
                requestID: request_id,  
                show_hidden: show_hidden,
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                $('#bulk-action-selector-top').prop('disabled', false );//enable this for your own user account
                $('.bulkactions .action').removeClass('disabled'); //added
                $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');

                if( data.html != '' ){
                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( data.html );
                    $('#wtai-loader-estimated-time').show();

                    handleLoaderNotifShowDisplay();
                }

                parentLoader.remove();
                shouldHideGenerateLoader();

                maybeReenablePendingBulkIds(data.all_pending_ids);

                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }
        });
        
        e.preventDefault();
    });

    $(document).on('click', '.wtai-action-bulk-transfer',function(e){
        e.preventDefault();

        if( $( this ).hasClass('disabled') ){
            return;
        }

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        var parentLoader = $(this).closest('.wtai-loading-estimate-time-container');
        var productIds = parentLoader.attr('data-product-ids').split(',');
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        restartPollBackgroundTimer();

        var wtai_nonce = get_product_bulk_nonce();
        
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_bulk_success', 
                show_hidden: 'show_hidden',
                wtai_nonce: wtai_nonce,
            },
            success: function( data ){
                if( data.is_own_request == '1' ){
                    $('#bulk-action-selector-top').prop('disabled', false );//enable this for your own user account
                    $('.bulkactions .action').removeClass('disabled'); //added
                    $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                    $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');
                }
                
                $.each( productIds, function( index, product_id ){
                    $('#the-list #wtai-table-list-' + product_id).removeClass('wtai-processing');
                    $('#the-list #wtai-table-list-' + product_id + ' .wtai-cwe-selected').prop('disabled', false);
                });

                if( data.html != '' ){
                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( data.html );
                    $('#wtai-loader-estimated-time').show();

                    handleLoaderNotifShowDisplay();
                }

                parentLoader.remove();
                shouldHideGenerateLoader();
                handleDismissAllDisplay();

                maybeReenablePendingBulkIds(data.all_pending_ids);

                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }
        }); 
    });

    $(document).on('click', '.wtai-action-bulk-transfer-cancel',function(e){
        e.preventDefault();

        if( $( this ).hasClass('disabled') ){
            return;
        }

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');
        if( transferGridAJAX !== null ){
            transferGridAJAX.abort();
        }    

        var parentLoader = $(this).closest('.wtai-loading-estimate-time-container');
        var productIds = parentLoader.attr('data-product-ids').split(',');
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        restartPollBackgroundTimer();

        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_bulk_cancel',
                show_hidden: show_hidden,
                wtai_nonce: wtai_nonce,
            },
            success: function( data ){
                bulkTransferCancelled = true;

                $('#the-list').find('tr').find('.wtai-cwe-selected').prop('disabled', false );
                $('#the-list').find('tr').removeClass('wtai-processing');
                $('#the-list').find('tr').removeClass('wtai-processing-transfer');

                $('#bulk-action-selector-top').prop('disabled', false ); //enable this for your own user account
                $('.bulkactions .action').removeClass('disabled'); //added
                $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');

                $.each( productIds, function( index, product_id ){
                    $('#the-list #wtai-table-list-' + product_id).removeClass('wtai-processing');
                    $('#the-list #wtai-table-list-' + product_id + ' .wtai-cwe-selected').prop('disabled', false);
                });

                if( data.html != '' ){
                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( data.html );
                    $('#wtai-loader-estimated-time').show();
                    
                    handleLoaderNotifShowDisplay();
                }

                parentLoader.remove();
                shouldHideGenerateLoader();

                maybeReenablePendingBulkIds(data.all_pending_ids);

                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');
            }
        });        
    });

    function transferCancelSilent(){        
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        var wtai_nonce = get_product_bulk_nonce();
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_bulk_cancel',
                show_hidden: show_hidden,
                wtai_nonce: wtai_nonce,
            },
            success: function(){
                bulkTransferCancelled = true;                
            }
        });        
    }

    $(document).on('click', '#transfer-single-btn', function(e){
        if (  $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb:checked').length  == 0 ){
            $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb').css('border', '1px solid red');
        } else {
            $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb').removeAttr('style');
            var event_btn   = $(this);
            var attr        = [];
            var post_id    = event_btn.data('id');
            $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').each(function(){
                if ( $(this).find('.wtai-product-attr-cb').prop('checked') ) {
                    attr.push( $(this).find('.wtai-product-attr-cb').val() ) ;
                }
            });

            if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0 ) {
                $('.wtai-table-list-wrapper' ).find('#message').remove();
            }

            var totalToTransfer = 1;

            $('#TB_window').find('#TB_closeWindowButton').click();

            if( transferGridAJAX !== null ){
                transferGridAJAX.abort();
            }            

            var hasDoneTransfer = false;
            if( $('.wtai-loading-estimate-time-container-user-'+WTAI_OBJ.current_user_id+'.wtai-loading-estimate-time-container-transfer.wtai-done').length ){
                hasDoneTransfer = true;
            }

            restartPollBackgroundTimer();
            maybe_remove_done_bulk_container();

            if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                //lets load a temp container
                var postIDs = [post_id];
                loader_transfer_temp_container( totalToTransfer, postIDs );
            }

            //set it back to false since a new transfer is initiated
            bulkTransferCancelled = false;       
            var date = new Date();
            var offset = date.getTimezoneOffset();
            
            if( hasDoneTransfer ){
                process_bulk_transfer_with_success( offset, attr, [post_id], true, post_id );
            }
            else{
                process_bulk_transfer_single( offset, attr, post_id );
            }          
        }
        e.preventDefault();
    });

    $(document).on('click', '.transfer_feature', function() {
        var button = $(this);
        if ( button.closest('tr').find('.wtai-cwe-selected').is(':disabled') || 
            button.closest('tbody').hasClass('no_transfer') ) {  
            return false;
        }
        var post_id = button.closest('tr').data('id');
        var type = button.data('type');
        var date = new Date();
        var offset = date.getTimezoneOffset();

        if( transferGridAJAX !== null ){
            transferGridAJAX.abort();
        }

        maybe_disable_bulk_actions( '0' );

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        var wtai_nonce = get_product_bulk_nonce();

        transferGridAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                browsertime: offset,
                action: 'wtai_transfer_grid_text',
                product_id: post_id, 
                fields: type, 
                trim_text: 15,
                show_hidden: show_hidden,
                bulk: 0,
                isDoingBulkTransfer : '0',
                wtai_nonce : wtai_nonce,
            },
            beforeSend: function() {
                $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', true );
                $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('checked', false );
                $('#the-list').find('#wtai-table-list-'+post_id).addClass('wtai-processing');

                button.addClass('wtai-disabled-button');
            },
            success: function( data ){
                if( data.access ){
                    if ( data.message ) {
                        if ( data.message == 'expire_token' ){
                            if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0 ) {
                                $('.wtai-table-list-wrapper' ).find('#message').remove();
                            }
                            $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                        } else {
                            if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0 ) {
                                $('.wtai-table-list-wrapper' ).find('#message').remove();
                            }
                            $('<div id="message" class="error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                        }
                    } else {    
                        if ( data.results ){
                            $.each( data.results, function( post_id, post_fields ){
                                $.each(post_fields, function( post_field, post_values ) {
                                    if ( post_field == 'wtai_transfer_date' ) {
                                        $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values);
                                    } else {
                                        $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values['trim']);
                                        $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).attr('data-text', post_values['text']);
                                    }         
                                });
                                $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                            });
                        }
                    }
                } else {
                    var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                    var class_name = 'error notice ';
                    if ( message ){
                        $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                    }
                }  

                button.removeClass('wtai-disabled-button');

                $('#bulk-action-selector-top').prop('disabled', false );
                $('.bulkactions .action').removeClass('disabled'); //added

                $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
                $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');
                $('.wtai-cwe-action-button.generate').removeClass('wtai-disabled-button' );
                $('.wtai-cwe-action-button.transfer').removeClass('wtai-disabled-button' );
                $('button.transfer_feature').removeClass('wtai-disabled-button' );
                button.closest('tr').find('.wtai-cwe-selected').prop('disabled', false );
            }
        }); 
   });

    $(document).on('click', '#wtai-attention-ok-btn', function(){
        tb_remove();
    });
 
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
            var number_of_changes_unsave = checkChanges( 'nav' );
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
 
    $(window).on('beforeunload', function() {
        var number_of_changes_unsave = checkChanges( 'nav' );

        if ( $('#wpwrap').hasClass('wtai-loader') ) {
            //if ( $('.wtai-metabox.wtai-metabox-update').length > 0 ) { 
            if( $('.wtai-global-loader').hasClass('wtai-is-active') || number_of_changes_unsave > 0 ) {    
                var message = WTAI_OBJ.confirm_leave;
                return message;
            }  
        }
    });

    window.wtaiGetProductAttr = function(element) {
        if( $(element).hasClass('disabled') ){
            return;
        }

        if( $('.wtai-bulk-transfer-error-notice').length ){
            $('.wtai-bulk-transfer-error-notice').remove();
        }

        if( $('.wtai-bulk-generate-error').length ){
            $('.wtai-bulk-generate-error').remove();
        }

        if ( $('.wtai-cwe-selected:checked').length > 0 ) {
            if ( $('.wtai-product-attr-cb:checked').length > 0 ){
                $('#bulk-update-product-attr').prop('disabled', false);
                $('#TB_ajaxContent').find('button.button-primary').prop('disabled', false);
            }
            
            if ( $(element).data('modal') == 'wtai-bulk-transfer-modal' ){
                tb_show('<h2>'+WTAI_OBJ.bulk_transfer+'</h2><p>'+WTAI_OBJ.attribute_guide+'</p>','#TB_inline?&width=525&inlineId=wtai-bulk-transfer-modal');
                $('#TB_window').addClass('wtai-tb-window-modal-transfer');
                $('#TB_window').find('#TB_ajaxWindowTitle').css({'width':'492px'});
                $('#TB_window').find('.button-primary').attr('id', 'wtai-transfer-bulk-btn' );
            } else {
                if( $('#wtai-generate-bulk-btn .wtai-credvalue').length ){
                    var bulkGenerateCredit = getBulkGenerateCreditCount();

                    var credLabel = WTAI_OBJ.creditLabelPlural;
                    if( parseInt( bulkGenerateCredit ) == 1 ){
                        credLabel = WTAI_OBJ.creditLabelSingular;
                    }

                    $('#wtai-generate-bulk-btn .wtai-credvalue').text( bulkGenerateCredit );
                    $('#wtai-generate-bulk-btn .wtai-cred-label').text( credLabel );
                }

                setTimeout(function() {
                    load_bulk_generate_filter_tooltip();
                }, 100);

                //set disabled combination
                setTimeout(function() {
                    set_disallowed_combinations_bulk();
                    maybe_display_featured_image_tooltip( false );
                }, 500);

                var bulk_message = WTAI_OBJ.attribute_guide_generate;

                reset_image_bulk_alt_local_data();
                
                tb_show('<h2>'+$(element).data('title')+'</h2><p class="wtai-bulk-message-notice" >'+bulk_message+'</p>','#TB_inline?&width=1090&inlineId='+$(element).data('modal'));
                
                $('#TB_window').addClass('wtai-tb-window-modal-generate');

                setTimeout(function() {
                    if( $('.wtai-bulk-custom-style-ref-prod').is(':checked') && $('select.wtai-bulk-custom-style-ref-product-select').val() == '' ){
                        $('.wtai-bulk-custom-style-ref-prod').trigger('click');
                    }

                    var refreshReferenceProducts = true;
                    if( $('.wtai-bulk-custom-style-ref-prod').is(':checked') ){
                        refreshReferenceProducts = false;
                    }

                    if( refreshReferenceProducts ){
                        // Lets refresh the bulk reference list.
                        setDynamicBulkReferenceProducts();
                    }
                }, 50);
            }
           
        } else {
            tb_show('<h2>'+WTAI_OBJ.attentionTextString+'</h2>', '#TB_inline?width=468&inlineId=wtai-attention-modal');
            $('#TB_window').addClass('modal_attention');
            $('#TB_overlay').addClass('remove-overlay');
        }
    };

    function process_bulk_transfer_with_success( offset, attr, total, single = false, singlePostID = 0 ){
        restartPollBackgroundTimer();
        
        if( single == false ){
            var post_ids = [];
            $('#the-list tr .wtai-cwe-selected:checked').each(function(){
                var postID = $(this).attr('data-post-id');
                post_ids.push(postID);
            });

            if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                //lets load a temp container
                loader_transfer_temp_container( total, post_ids );
            }
        }

        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_bulk_success', 
                show_hidden: 'show_hidden',
                wtai_nonce: wtai_nonce,
            },
            success: function(){
                if( single == true ){
                    process_bulk_transfer_single( offset, attr, singlePostID );
                }
                else{
                    process_bulk_transfer( offset, attr, total );
                }
            }
        });
    }

    let bulkTransferAttempts = 0;
    let bulkTransferMaxRetries = 5;
    let bulkTransferCurrentDelay = 0;
    function process_bulk_transfer( offset, attr, total, initial_check = '1' ){
        if( bulkTransferCancelled ){
            //trigger transfer cancel again to ensure the updated pending ids is reflected
            transferCancelSilent();
            return;
        }

        if( transferGridAJAX !== null ){
            transferGridAJAX.abort();
        }

        var row_transfer = $('.wtai-processing-transfer').first();
        var post_id = row_transfer.data('id');

        var post_ids = [];
        $('#the-list tr .wtai-cwe-selected:checked').each(function(){
            var postID = $(this).attr('data-post-id');
            post_ids.push(postID);
        });

        maybe_disable_bulk_actions( '1' );

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
            //lets load a temp container
            loader_transfer_temp_container( total, post_ids );
        }

        $('.wtai-bulk-action-option-wrap #bulk-action-selector-top').prop('disabled', true);

        if( $('.wtai-bulk-transfer-error-notice').length ){
            $('.wtai-bulk-transfer-error-notice').remove();
        }

        var wtai_nonce = get_product_bulk_nonce();
        
        transferGridAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                browsertime: offset,
                action: 'wtai_transfer_grid_text',
                product_id: post_id, 
                all_product_ids: post_ids.join(','), 
                fields: attr.join(','), 
                trim_text: 15,  
                show_hidden: show_hidden,  
                bulk : 1,
                initial_check : initial_check,
                isDoingBulkTransfer : '1',
                wtai_nonce:wtai_nonce,
            },
            success: function( data ){
                if( data.access ){
                    if( data.no_data_to_transfer == '1' ){
                        var class_name = 'error notice wtai-bulk-transfer-error-notice ';
                        if ( data.message != '' ){
                            $('<div id="message" class="'+class_name+' is-dismissible"><p>'+data.message+'</p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                        }

                        $('.wtai-action-bulk-transfer').trigger('click');
                    }
                    else{                    
                        if ( data.message ) {
                            $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', false );
                            $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                            $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing-transfer');
                        } 
                        else {
                            if ( data.results ){
                                $.each( data.results, function( post_id, post_fields ){
                                    $.each(post_fields, function( post_field, post_values ) {
                                        if ( post_field == 'wtai_transfer_date' ) {
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values);
                                        } 
                                        else if( post_field == 'alt_text' ){
                                            // Do nothing.
                                        }
                                        else {
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values['trim']);
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).attr('data-text', post_values['text']);
                                        }         
                                    });

                                    $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', false );
                                    $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                                    $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing-transfer');
                                });
                            }
                            else{
                                $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', false );
                                $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                                $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing-transfer');
                            }
                        }

                        if( data.html != '' ){
                            displayLoaderNotif( data.html, data.jobs_user_ids, data.job_loader_data, data.has_error );

                            if( data.job_loader_data[1] ){
                                var job_loader_data = data.job_loader_data;

                                $.each( job_loader_data, function( index, job_data ){
                                    var completed_ids = job_data.job_data.completed_ids;
                                    var all_product_ids = job_data.job_data.product_ids;
                                    var pending_ids = all_product_ids.filter(id => !completed_ids.includes(id));

                                    $.each( completed_ids, function( index, completed_id ){
                                        $('#the-list').find('#wtai-table-list-'+completed_id).find('.wtai-cwe-selected').prop('disabled', false );
                                        $('#the-list').find('#wtai-table-list-'+completed_id).removeClass('wtai-processing');
                                        $('#the-list').find('#wtai-table-list-'+completed_id).removeClass('wtai-processing-transfer');
                                    });

                                    maybeReenablePendingBulkIds(pending_ids);
                                });
                            }
                        }

                        if ( $('.wtai-processing-transfer').length  > 0 ){
                            process_bulk_transfer( offset, attr, total, '0' );
                        } else {
                            $('.wtai-bulk-action-option-wrap #bulk-action-selector-top').prop('disabled', false);
                        }
                    }
                } 
                else {
                    var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                    var class_name = 'error notice ';
                    if ( message ){
                        $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                    }
                }  
            },
            error: function(xhr, status, error) {
                bulkTransferAttempts++;

                if( bulkTransferAttempts <= 1 ){
                    bulkTransferCurrentDelay = 5000;
                } else if( bulkTransferAttempts == 2 ) {
                    bulkTransferCurrentDelay = bulkTransferCurrentDelay + 10000;
                } else {
                    bulkTransferCurrentDelay = bulkTransferCurrentDelay + 15000;
                }

                if ( bulkTransferAttempts <= bulkTransferMaxRetries ) {
                    console.log(`Bulk transfer attempt ${bulkTransferAttempts} failed. Retrying in ${bulkTransferCurrentDelay / 1000} seconds...`);

                    setTimeout(() => process_bulk_transfer( offset, attr, total, '0' ), bulkTransferCurrentDelay); // Increase delay by 5 seconds
                } else {
                    console.log('Bulk transfer max retries reached. Request failed.');

                    var class_name = 'error notice wtai-bulk-transfer-error-notice ';
                    $('<div id="message" class="'+class_name+' is-dismissible"><p>'+WTAI_OBJ.generalErrorMessage+'</p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );

                    $('.wtai-action-bulk-transfer').trigger('click');
                }
            }
        }); 
    }

    function process_bulk_transfer_single( offset, attr, post_id ){
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        if( $('.wtai-bulk-transfer-error-notice').length ){
            $('.wtai-bulk-transfer-error-notice').remove();
        }
        
        var wtai_nonce = get_product_bulk_nonce();

        transferGridAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                browsertime: offset,
                action: 'wtai_transfer_grid_text',
                product_id: post_id, 
                all_product_ids: post_id, 
                fields: attr.join(','), 
                trim_text: 15,
                show_hidden: show_hidden,
                bulk: 1,
                initial_check: 1,
                isDoingBulkTransfer : '1',
                wtai_nonce:wtai_nonce,
            },
            beforeSend: function() {
                $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', true );
                $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('checked', false );
                $('#the-list').find('#wtai-table-list-'+post_id).addClass('wtai-processing');
                $('#TB_window').find('#TB_closeWindowButton').click();

                maybe_disable_bulk_actions( '1' );
            },
            success: function( data ){
                if( data.access ){
                    if( data.no_data_to_transfer == '1' ){
                        var class_name = 'error notice wtai-bulk-transfer-error-notice';
                        if ( data.message != '' ){
                            $('<div id="message" class="'+class_name+' is-dismissible"><p>'+data.message+'</p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                        }

                        $('.wtai-action-bulk-transfer').trigger('click');
                    }
                    else{
                        if ( data.message ) {
                            $('#the-list').find('#wtai-table-list-'+post_id).find('.wtai-cwe-selected').prop('disabled', false );
                            $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                        } 
                        else {    
                            if ( data.results ){
                                $.each( data.results, function( post_id, post_fields ){
                                    $.each(post_fields, function( post_field, post_values ) {
                                        if ( post_field == 'wtai_transfer_date' ) {
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values);
                                        } else {
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).html(post_values['trim']);
                                            $('#the-list').find('#wtai-table-list-'+post_id).find('.'+post_field).attr('data-text', post_values['text']);
                                        }         
                                    });
                                    $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                                });                               
                            }
                            else{
                                $('#the-list').find('#wtai-table-list-'+post_id).removeClass('wtai-processing');
                            }
                        }
                        
                        if( data.html != '' ){
                            displayLoaderNotif( data.html, data.jobs_user_ids, data.job_loader_data, data.has_error );

                            maybeReenablePendingBulkIds([]);
                        }

                        if ( $('.wtai-processing-transfer').length  > 0 ){
                            process_bulk_transfer( offset, attr, 1 );
                        } 
                    }
                } 
                else {
                    var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                    var class_name = 'error notice ';
                    if ( message ){
                        $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                    }
                }  
            }
        }); 
    }    

    window.wtaiBulkTransfer = function(element, event){
        event.preventDefault();

        if ( $('#TB_window').find('.button-primary').attr('id') == 'wtai-transfer-bulk-btn') {
            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');
            
            if (  $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb:checked').length  == 0 ){
                $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb').css('border', '1px solid red');
            } else {
                //set it back to false since a new transfer is initiated
                bulkTransferCancelled = false;    

                if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0 ) {
                    $('.wtai-table-list-wrapper' ).find('#message').remove();
                }

                $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').find('.wtai-product-attr-cb').removeAttr('style');
                var attr        = [];
                $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').each(function(){
                    if ( $(this).find('.wtai-product-attr-cb').prop('checked') ) {
                        attr.push($(this).find('.wtai-product-attr-cb').val());
                    }
                });

                var post_ids = [];
                $('#the-list').find('tr').each(function(){
                    if ( $(this).find('.wtai-cwe-selected').prop('checked') ) {
                        post_ids.push($(this).find('.wtai-cwe-selected').data('post-id'));
                        $(this).prop('disabled', true );
                        $(this).closest('tr').addClass('wtai-processing');
                        $(this).closest('tr').addClass('wtai-processing-transfer');
                    }
                });

                $('#TB_window').find('#TB_closeWindowButton').click();

                var date = new Date();
                var offset = date.getTimezoneOffset();

                var hasDoneTransfer = false;
                if( $('.wtai-loading-estimate-time-container-user-'+WTAI_OBJ.current_user_id+'.wtai-loading-estimate-time-container-transfer.wtai-done').length ){
                    hasDoneTransfer = true;
                }
                
                maybe_remove_done_bulk_container();

                if( hasDoneTransfer ){
                    process_bulk_transfer_with_success( offset, attr, post_ids.length, false );
                }
                else{
                    process_bulk_transfer( offset, attr, post_ids.length );
                }
            }
        }
    };

    window.wtaiBulkGenerate = function(element, event) {
        event.preventDefault();

        var event_btn   = $(this);
        var post_ids    = [];
        $('#the-list').find('tr').each(function(){
            if ( $(this).find('.wtai-cwe-selected').prop('checked') ) {
                post_ids.push($(this).find('.wtai-cwe-selected').data('post-id'));
            }
        });

        if( $('.wtai-bulk-generate-error').length ){
            $('.wtai-bulk-generate-error').remove();
        }

        $('#wtai-product-generate-completed-bulk').hide();
        
        event_btn.prop('disabled', true);

        var creditCountNeeded = $('#wtai-generate-bulk-btn .wtai-credvalue').text();
        
        var queueAPI = 0;
        var bulkOneOnly = 0;
        if( post_ids.length == 1 ){
            queueAPI = 1;
            bulkOneOnly = 1;
        }

        var includeRankedKeywords = '0';
        if( $('#wtai-use-ranking-keywords').is(':checked') ){
            includeRankedKeywords = '1';
        }

        var specialInstructions = '';
        if( $('#wtai-bulk-other-details').length && $('#wtai-bulk-other-details').val().trim() != '' ){
            specialInstructions = $('#wtai-bulk-other-details').val();
        }

        var wtai_nonce = get_product_bulk_nonce();

        var no_error = false;
        var date = new Date();
        var offset = date.getTimezoneOffset();        
        var data = {
            browsertime: offset,
            action: 'wtai_generate_text',
            product_id: post_ids.join(','), 
            save_generated: 1,
            queueAPI: queueAPI,
            bulkOneOnly: bulkOneOnly,
            creditCountNeeded: creditCountNeeded,
            doingBulkGeneration: '1',
            wtai_nonce : wtai_nonce,
            includeRankedKeywords : includeRankedKeywords,
            specialInstructions : specialInstructions,
        };

        var hasAltTextSelected = false;
        var textfields = [];
        if ( $('#TB_window').find('.wtai-product-textfields-container').length > 0 ){
            if ( $('#TB_window').find('.wtai-product-textfields-container').find('.wtai-product-attr-cb:checked').length > 0 ){
                $('#TB_window').find('.wtai-product-textfields-container').find('.wtai-product-attr-cb').each(function(){
                    $(this).removeAttr('style');
                });
                
                $('#TB_window').find('.wtai-product-textfields-container').find('.wtai-product-attr-cb:checked').each(function(){
                    if( $(this).val() == 'alt_text' ){
                        hasAltTextSelected = true;
                    }
                    else{
                        textfields.push($(this).val());
                    }
                });
                data['fields'] =  textfields.join(',');
            } else {
                $('#TB_window').find('.wtai-product-textfields-container').find('.wtai-product-attr-cb').each(function(){
                    $(this).css('border', '1px solid red');
                });
                no_error = true;

            } 
        }

        var includeFeaturedImage = 0;
        if ( $('#TB_window').find('.wtai-product-attr-container').length > 0 ){
            var attr        = [];
            $('#TB_window').find('.wtai-product-attr-container').find('.wtai-product-attr-item').each(function(){
                if ( $(this).find('.wtai-product-attr-cb').prop('checked') ) {
                    if( $(this).find('.wtai-product-attr-cb').val() == 'wtai-featured-product-image' ){
                        includeFeaturedImage = 1;
                    }
                    else{
                        attr.push($(this).find('.wtai-product-attr-cb').val());
                    }
                }
            });
            data['attr_fields'] =  attr.join(',');
        }

        data['includeFeaturedImage'] = includeFeaturedImage;

        if ( $('#TB_window').find('.wtai-product-tones-wrap').length > 0 ){
            if ( $('#TB_window').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb:checked').length > 0 ){
                $('#TB_window').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb').each(function(){
                    $(this).removeAttr('style');
                });
                var tones = [];
                $('#TB_window').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb:checked').each(function(){
                    tones.push($(this).val());
                });
                data['tones'] =  tones.join(',');
            } else {
                $('#TB_window').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb').each(function(){
                    $(this).css('border', '1px solid red');
                });
                no_error = true;
            } 
        }
        
        if ( $('#TB_window').find('.wtai-product-audiences-wrap').length > 0 ){            
            if ( $('#TB_window').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb:checked').length > 0 ){
                $('#TB_window').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb').each(function(){
                    $(this).removeAttr('style');
                });
                var audiences = [];
                $('#TB_window').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb:checked').each(function(){
                    audiences.push($(this).val());
                });
                data['audiences'] =  audiences.join(',');
            }        
        }

        if ( $('#TB_window').find('.wtai-product-styles-wrap').length > 0 ){
            data['styles'] = $('#TB_window').find('.wtai-product-styles-wrap').find('.wtai-product-styles-cb:checked').val();
        }

        if ( $('#TB_window').find('#wtai-product-description-length-min').length > 0 ){
            data['product_description_length_min'] = $('#TB_window').find('#wtai-product-description-length-min').val();
        }

        if ( $('#TB_window').find('#wtai-product-description-length-max').length > 0 ){
            data['product_description_length_max'] = $('#TB_window').find('#wtai-product-description-length-max').val();
        }
        
        if ( $('#TB_window').find('#wtai-product-excerpt-length-min').length > 0 ){
            data['product_excerpt_length_min'] = $('#TB_window').find('#wtai-product-excerpt-length-min').val();
        }

        if ( $('#TB_window').find('#wtai-product-excerpt-length-max').length > 0 ){
            data['product_excerpt_length_max'] = $('#TB_window').find('#wtai-product-excerpt-length-max').val();
        }

        var referenceProductID = '';
        if( $('#wtai-bulk-custom-style-ref-prod').is(':checked')) {
            var referenceProductData = $('select.wtai-bulk-custom-style-ref-product-select').val();

            if( referenceProductData == '' ){
                $('.wtai-bulk-custom-style-ref-product-select .selectize-input').addClass('warning');

                no_error = true;
            }
            else{
                var referenceProductArr = referenceProductData.split('-');
                referenceProductID = referenceProductArr[0];

                data['referenceProductID'] = referenceProductID;
                $('.wtai-bulk-custom-style-ref-product-select .selectize-input').removeClass('warning');
            }
        }

        var altimages = [];
        if( hasAltTextSelected ){
            if( $('.wp-list-table .wtai-cwe-selected:checked').length > 0 ){
                $('.wp-list-table .wtai-cwe-selected:checked').each(function() {
                    var altImageIds = $(this).closest('tr').attr('data-image-ids');
                    var altImageIdsArray = [];
                    if( altImageIds != '' ){
                        altImageIdsArray = altImageIds.split(',');
                        $.each(altImageIdsArray, function( index, alt_image_id ) {
                            altimages.push( alt_image_id );
                        });
                    }
                });
            }

            var successful_image_for_upload = [];
            var error_image_for_upload = [];
            if( altimages.length > 0 ){
                if( window.altImageSuccessForUploadBulk != undefined ){ 
                    successful_image_for_upload = window.altImageSuccessForUploadBulk;
                }
                if( window.altImageIdsErrorBulk != undefined ){ 
                    error_image_for_upload = window.altImageIdsErrorBulk;
                }
            }

            data['altimages'] = successful_image_for_upload.join(',');
            data['altimageserror'] = error_image_for_upload.join(',');
        }

        // Display no alt text error message.
        if( hasAltTextSelected && textfields.length <= 0 && altimages.length <= 0 ){
            $('#TB_closeWindowButton').click();

            if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                $('.wtai-table-list-wrapper' ).find('#message').remove();
            }
            $('<div id="message" class="wtai-bulk-generate-error error notice is-dismissible"><p>'+WTAI_OBJ.noAltTextImageToGenerate+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );

            return;
        }

        $('#wtai-loader-estimated-time #wtai-preprocess-image-loader').remove();

        if ( ! no_error ){
            if( ( includeFeaturedImage == 1 || altimages.length > 0 ) && $('#wtai-generate-bulk-btn').hasClass('wtai-pre-process-image-done') == false ){
                $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                $('#wtai-loader-estimated-time').hide();

                maybeReenablePendingBulkIds( [] );
                maybe_disable_bulk_actions( '1' );

                $('#the-list').find('tr').each(function(){
                    if ( $(this).find('.wtai-cwe-selected').prop('checked') ) {
                        $(this).find('.wtai-cwe-selected').prop('disabled', true );
                        $(this).addClass('wtai-processing');
                    }
                });

                restartPollBackgroundTimer();
                maybe_remove_done_bulk_container();

                $('.thickbox-loading.wtai-tb-window-modal-generate').hide();
                $('.TB_overlayBG').hide();

                maybe_display_featured_image_tooltip( false );

                var loaderEstimatedTime = $('#wtai-loader-estimated-time');
                var preprocessImageLoader = $('#wtai-preprocess-image-loader');               

                // Check if both elements exist
                if (preprocessImageLoader.length > 0 && loaderEstimatedTime.length > 0) {
                    if (loaderEstimatedTime.is(':visible') ) {
                        preprocessImageLoader.clone().appendTo(loaderEstimatedTime).show();
                    } else {
                        preprocessImageLoader.show();
                    }
                } 

                //added 2024.03.05
                $('#wpcontent').addClass('preprocess-image');
                
                //split the images into batches of 10
                var altImagesBatches = [];
                var aictr = 0;
                var aibatchid = 0;
                altImagesBatches[ aibatchid ] = [];

                $.each(altimages, function( index, alt_image_id ) {
                    if( aictr == 10 ){
                        aictr = 0;
                        aibatchid++;

                        altImagesBatches[ aibatchid ] = [];
                    }

                    altImagesBatches[ aibatchid ][ aictr ] = alt_image_id;

                    aictr++;
                });

                // counter goes here
                window.currentAltImageBatchBulk = 0;
                window.maxAltImageBatchNoBulk = aibatchid;
                window.altImageForUploadBulk = altimages;
                window.altImageSuccessForUploadBulk = [];
                window.altImageBatchForUploadBulk = altImagesBatches;
                window.altImageIdsErrorBulk = [];

                // Pre process images first.
                process_image_upload_bulk( post_ids.join(','), altImagesBatches[0], includeFeaturedImage );

                return;
            }

            $('#wtai-preprocess-image-loader').hide();
            //added 2024.03.05
            $('#wpcontent').removeClass('preprocess-image');

            //from above
            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');

            $('#the-list').find('tr').each(function(){
                if ( $(this).find('.wtai-cwe-selected').prop('checked') ) {
                    $(this).find('.wtai-cwe-selected').prop('disabled', true );
                    $(this).addClass('wtai-processing');
                }
            });
            
            $('#TB_closeWindowButton').click();

            restartPollBackgroundTimer();
            maybe_remove_done_bulk_container();

            //lets load temp notif data
            if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                //lets load a temp container
                loader_generate_temp_container( post_ids.length, post_ids );
            }

            bulkGenerateOngoing = true;
            bulkGenerateOngoingIDs = post_ids;

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data,
                beforeSend: function() {
                    maybe_disable_bulk_actions( '1' );
                    $('#TB_closeWindowButton').click();
                },
                success: function( data ){
                    $('#wtai-generate-bulk-btn').removeClass('wtai-pre-process-image-done');

                    if( data.access ){
                        if ( data.message ) {
                            if ( data.message == 'expire_token' ){
                                if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                    $('.wtai-table-list-wrapper' ).find('#message').remove();
                                }
                                $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                            } else {
                                if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                    $('.wtai-table-list-wrapper' ).find('#message').remove();
                                }
                                $('<div id="message" class="wtai-bulk-generate-error error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                            }

                            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                            $('#wtai-loader-estimated-time').hide();
                            maybeReenablePendingBulkIds( [] );
                        } 
                        else {
                            if( window.WTAStreamConnected == false ){
                                if ( typeof data.results.requestId !== 'undefined' ) {
                                    process_bulk_generate( data.results.requestId );
                                } 
                                else if ( post_ids.length == 1 ){
                                    process_bulk_generate( '' );
                                }
                            }
                        }
                    } 
                    else {
                        var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                        var class_name = 'error notice wtai-bulk-generate-error ';
                        if ( message ){
                            $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        }

                        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                        $('#wtai-loader-estimated-time').hide();
                        maybeReenablePendingBulkIds( [] );
                    }                      
                }
            }); 
        }
    };
    
    loaderBulkLoaded();

    function loaderBulkLoaded(){
        if( WTAI_OBJ.loadBackgroundJobs == '1' ){
            pollBackgroundJobs();
        }

        if( $('.wtai-bulkactions-wrap').length ){
            var disable_bulk_action = $('.wtai-bulkactions-wrap').attr('data-disable-bulk-action');
            if( disable_bulk_action == '1' ){
                maybe_disable_bulk_actions( '1' );
            }
        }
    }

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
                    producteditformClose();
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

    /*Close History popup*/
    if( $('.wtai-btn-close-history').length ) {
        $('.wtai-btn-close-history').on('click',function(){
            $('.wtai-slide-right-text-wrapper .wtai-history').trigger('click');
            $(this).hide();
        });     
    }

    /*Writetext Status*/
    function getURLParameterValue(parameterName) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(parameterName);
    }

    $('#wtai-sel-writetext-status > div.wtai-filter-select').on('click',function(){
        $(this).siblings('.wtai-status-checkbox-options').toggleClass('wtai-open');
    });
    var paramValue = getURLParameterValue('wtai_writetext_status');
    var status = $('#wtai-sel-writetext-status input[name="wtai_writetext_status"]').val();
    if (paramValue ) {
        status = paramValue;
    }
    write_filter_status(status);

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

        //added for image alt text
        if( dataType == 'image_alt_text' ){
            if( $(this).is(':checked') && $(this).closest('.postbox').hasClass('wtai-alt-writetext-metabox') ) {
                $('#postbox-container-2 .wtai-checkboxes-alt').prop('checked', true);
            } else {
                $('#postbox-container-2 .wtai-checkboxes-alt').prop('checked', false);
            }
        }

        handle_save_button_state();
        handle_single_transfer_button_state();
        record_preselected_field_types();
        handle_generate_button_state();
        bulk_transfer_button_behavior();
    });

    //Image alt text checkbox
    $(document).on( 'change', '#postbox-container-2 .wtai-checkboxes-alt', function() {
        var checkedCount = $('#postbox-container-2 .wtai-checkboxes-alt:checked').length;
        var itemTotalCount = $('#postbox-container-2 .wtai-checkboxes-alt').length;

        if( checkedCount == itemTotalCount ){
            $(this).closest('.postbox').find('.wtai-checkboxes').prop('checked', true);
        } else {
            $(this).closest('.postbox').find('.wtai-checkboxes').prop('checked', false);
        }

        handle_save_button_state();
        handle_single_transfer_button_state();
        bulk_transfer_button_behavior();
        record_preselected_field_types();
        handle_generate_button_state();
    });


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

    function popupUnsaved( type ){
        $('#wtai-product-edit-cancel').find('.wtai-exit-edit-leave').attr('data-type', type);
        if ( ! $('#wtai-product-edit-cancel').is(':visible') ) {
            $('#wpbody-content').addClass('wtai-overlay-div-2');
            $('#wtai-product-edit-cancel').show();
        }
    }
    
    function popupUnsavedGenerate(parentdiv, submittype, type){
        if (  type ) {
            $('#' + parentdiv + ' .wtai-product-generate-forced').find('.wtai-product-generate-proceed').attr('data-type', type);
        }
        $('#' + parentdiv + ' .wtai-product-generate-forced').find('.wtai-product-generate-proceed').attr('data-submittype', submittype);
        if ( ! $('#' + parentdiv + ' .wtai-product-generate-forced').is(':visible') ) {
            $('#' + parentdiv + ' .wtai-product-generate-forced').show();
        }
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

        //maybe show tooltip for rewrite disabled when ref product is selected
        if( $('#wtai-custom-style-ref-prod').is(':checked') ){
            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage1, 1 );
            }, 300);
        }

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

    function html_tooltip (gen_label, gen_text, trans_label, trans_text) {
        var html = '<div class="wtai-tooltip-transfer-text wtai-tooltiptext"><span class="wtai-label">' + gen_label + '</span><p>' + gen_text + '</p></div><div class="wtai-tooltip-generate-text wtai-tooltiptext"><span class="wtai-label">' + trans_label + '</span><p>' + trans_text + '</p></div>';
        return html;
    }

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

    function prev_next_to_button(button){
        if ( ! button.hasClass('disabled') ) {
            popupGenerateCompleted('hide');

            if( $('.wtai-percentage.keyword-density-perc').length ){
                $('.wtai-percentage.keyword-density-perc').html( '&mdash;' );
                $('.wtai-percentage.wtai-semantic-keyword-density-perc').html( '&mdash;' );
            }
            
            if( queueGenerateTimer ){
                clearTimeout(queueGenerateTimer);
            }

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

            // Reset stream data for keyword analysis
            window.keywordIdeasStartAnalysis = false;
            window.keywordIdeasQueueRequestId = '';
            window.keywordIdeasSource = 'all';
            window.keywordIdeasSourceType = 'all';

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
            $('.wtai-product-attr-image-wrap').html( '' );
            $('.wtai-product-alt-images-main-wrap').html( '' );

            //alt image id states
            $('.wtai-image-alt-metabox').removeClass('wtai-bulk-complete');
            $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled', false);
            $('.wtai-alt-writetext-metabox').removeClass('wtai-disabled-click');

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

                var bulkIds = $('.wp-list-table').attr('data-bulk-ids').split(',');

                if ( bulkIds.length > 0 ){
                    $.each(  bulkIds, function(index, pending_product_id ){
                        var index = $.inArray(pending_product_id, prod_ids);    
                        if (index > -1) { 
                            prod_ids.splice(index, 1); 
                        }
                    });
                }

                var active_prod_id = $('#wtai-edit-post-id').attr('value');
                var type = '';
                if ( button.hasClass('wtai-button-prev')){
                    type = 'prev';
                } else {
                    type = 'next';
                }
                var index = $.inArray(active_prod_id, prod_ids);
                if ( type == 'prev' && index == 0 ) {
                    return false;
                } else if ( type == 'next' && prod_ids.length == parseInt(parseInt(index) + 1) ) {
                    return false;
                } 

                var active_index = 0;
                if (  type == 'prev' ) {
                    active_index = index - 1;
                } else {
                    active_index = index + 1;
                }
                var product_id_now = prod_ids[active_index];
                
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

                var wtai_nonce = get_product_edit_nonce();
                
                $.ajax({
                    type: 'POST',
                    dataType: 'JSON',
                    url: WTAI_OBJ.ajax_url,
                    data: {
                        action: 'wtai_product_data',
                        product_id: product_id_now,
                        wtai_nonce : wtai_nonce,
                    },
                    beforeSend: function() {
                        $('.wtai-header-title .wtai-post-title').css('visibility','hidden');
                        $('.wtai-header-title .wtai-product-sku').css('visibility','hidden');
                        $('#wtai-woocommerce-product-attributes .postbox-content ul li input').css('visibility','hidden');
                        $('#wtai-woocommerce-product-attributes .postbox-content ul li label').css('visibility','hidden');
                        $('#wtai-woocommerce-product-attributes .postbox-content ul li .wtai-otherproddetails-container').css('visibility','hidden');

                        $('.wtai-col-right-wrapper.wtai-semantic-keywords-wrapper .wtai-semantic-keywords-wrapper-list-wrapper').css('visibility', 'hidden');
                        $('.wtai-col-right-wrapper.wtai-semantic-keywords-wrapper .wtai-semantic-keywords-wrapper-list-wrapper').css('height', '44px');
                        $('.wtai-semantic-keywords-wrapper-list .wtai-product-title-semantic-list').css('visibility', 'hidden');
                        $('.wtai-data-semantic-keywords-wrapper-list-wrapper.wtai-post-data .wtai-semantic-keywords-wrapper-list').css('visibility', 'hidden');
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
                                        $('.wtai-header-title .wtai-post-title').css('visibility','visible');
                                        elementobject.html(  res.result[postfield]  );
                                        break;
                                    case 'product_sku':
                                        $('.wtai-header-title .wtai-product-sku').css('visibility','visible');
                                        elementobject.html(  res.result[postfield]  );
                                        break;
                                    case 'product_short_title':
                                        elementobject.val(  res.result[postfield]  );
                                        break;
                                    case 'post_permalink':
                                        elementobject.attr( 'href', res.result[postfield] );
                                        elementobject.html( res.result[postfield] );
                                        break;
                                    case 'post_id':
                                        elementobject.attr( 'value', product_id_now );
                                        break;
                                    default:
                                        elementobject.html( res.result[postfield] );
                                        break;
                                }
                            });

                            $('#wtai-woocommerce-product-attributes .postbox-content ul li input').css('visibility','visible');
                            $('#wtai-woocommerce-product-attributes .postbox-content ul li label').css('visibility','visible');
                            $('#wtai-woocommerce-product-attributes .postbox-content ul li .wtai-otherproddetails-container').css('visibility','visible');

                            $('.wtai-col-right-wrapper.wtai-semantic-keywords-wrapper .wtai-semantic-keywords-wrapper-list-wrapper').css('visibility', 'visible');
                            $('.wtai-col-right-wrapper.wtai-semantic-keywords-wrapper .wtai-semantic-keywords-wrapper-list-wrapper').css('height', 'auto');
                            $('.wtai-semantic-keywords-wrapper-list .wtai-product-title-semantic-list').css('visibility', 'visible');
                            $('.wtai-data-semantic-keywords-wrapper-list-wrapper.wtai-post-data .wtai-semantic-keywords-wrapper-list').css('visibility', 'visible');
                            
                            productSingleDataResponse( res );
                            getKeywordOverallDensity();

                            $('.wtai-slide-right-text-wrapper').removeClass('wtai-overlay');
                        
                            $('body').removeClass('wtai-history-open'); 
                        }
                    }
                });

                getDataPerProductBlockInit( product_id_now, 1 );
            }
        }
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
                }
            });
        }
    }

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

    function producteditformClose(){
        reset_edit_form();

        lastGenerationTypeSelected = null;

        $('.wtai-hide-step-cb-wrap').hide();
        $('.wtai-hide-step-separator').hide();

        // Show restore global settings button
        $('.wtai-restore-global-settings-wrap').hide();
        $('.wtai-restore-global-settings-separator').hide();

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
        
        bulk_popup_position('grid');

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

    function process_bulk_generate( bulkRequestID = '', from_stream = 0, refresh_credits = 0 ){
        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        var date        = new Date();
        var timedate    = date.toLocaleString();
        var offset      = date.getTimezoneOffset();     
        
        if( bulkGenerateAJAX != null ){
            if( from_stream == 0 ){
                bulkGenerateAJAX.abort();
            }
        }

        var wtai_nonce = get_product_bulk_nonce();
        
        bulkGenerateAJAX = $.ajax({
            type: 'GET',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_bulk_progress',
                browsertime: offset,
                timedate: timedate,
                trim_text: 15,
                bulkRequestID: bulkRequestID,
                show_hidden: show_hidden,
                wtai_nonce: wtai_nonce,
                refresh_credits: refresh_credits,
            },
            success: function(data) {
                if ( data.message ) {
                    if ( data.message == 'expire_token' ){
                        if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                            $('.wtai-table-list-wrapper' ).find('#message').remove();
                        }
                        $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                    } else {
                        if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                            $('.wtai-table-list-wrapper' ).find('#message').remove();
                        }
                        $('<div id="message" class="wtai-bulk-generate-error error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                    }

                    $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                    $('#wtai-loader-estimated-time').hide();
                    maybeReenablePendingBulkIds( [] );
                } 
                else{                
                    if( data.available_credit_label != '' ){
                        $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                    }

                    //display loader progress bars here
                    if( data.html ){                    
                        displayLoaderNotif( data.html, data.jobs_user_ids, data.job_loader_data, data.has_error );

                        //display generated texts
                        if ( data.generated_texts ){
                            $.each( data.generated_texts, function( post_id, post_fields ){
                                $.each(post_fields, function( post_field, post_values ) {
                                    var table_row_object = $('#wtai-table-list-'+post_id);
                                    if ( post_field == 'generate_date' || post_field == 'transfer_date' ){
                                        table_row_object.find('.wtai_'+post_field).html(post_values);

                                    }
                                    else {
                                        
                                        table_row_object.find('.wtai_'+post_field).html(post_values['trim']);
                                        table_row_object.find('.wtai_'+post_field).attr('data-text', post_values['text']);

                                        if( post_values['trim'] != post_values['text'] ){
                                            if( $('.wtai-show-comparison #wtai-comparison-cb').is(':checked') ){
                                                if( table_row_object.find('.wtai_'+post_field).hasClass( 'tooltip_hover' ) == false ){
                                                    table_row_object.find('.wtai_'+post_field).addClass('tooltip_hover');

                                                    table_row_object.find('.wtai_'+post_field).tooltipster(tooltipster_var);
                                                }
                                            }
                                        }

                                        table_row_object.find('.transfer_'+post_field).find('.transfer_feature').removeClass('hidden');
                                        table_row_object.find('.transfer_'+post_field).find('.transfer_feature').prop('disabled', false);
                                    }     
                                });                                  
                            });
                        }

                        if( does_user_have_ongoing_jobs() == false ){
                            bulkGenerateOneOngoing = false;
                            bulkGenerateOneOngoingID = 0;
                            bulkGenerateOngoing = false;
                            bulkGenerateOngoingIDs = [];

                            reset_bulk_options( data.default_style, data.default_tones, data.default_audiences, data.default_product_attributes, data.default_desc_min, data.default_desc_max, data.default_excerpt_min, data.default_excerpt_max );

                            var is_premium = data.is_premium;
                            handle_single_product_edit_state( is_premium );
                            handle_density_premium_state( is_premium );

                            reset_image_bulk_alt_local_data();

                            $('.wtai-global-loader').removeClass('wtai-is-active');
                            $('.wtai-ai-logo').removeClass('wtai-hide');
                        }

                        maybeReenablePendingBulkIds( data.all_pending_ids );

                        //lets fallback to default implementation if not connected
                        if( window.WTAStreamConnected == false ){
                            bulkGenerateTimer = setTimeout( process_bulk_generate, 3000, bulkRequestID );
                        }
                    }
                    else{
                        if( bulkGenerateOneOngoing == false ){
                            $('#the-list tr.wtai-processing').removeClass('wtai-processing');
                            $('#the-list .wtai-cwe-selected').prop('disabled', false);
                            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                            maybeReenablePendingBulkIds( [] );
                            shouldHideGenerateLoader();
                        }
                    }       
                    
                    if( $('.wtai-list-table .wtai-cwe-selected:checked').length <= 0 ){
                        $('.wtai-list-table .column-cb.check-column input[type="checkbox"]').prop('checked', false);
                    }
                }

                bulkGenerateAJAX = null;//clear ajax object
            }
        });

        // Add the request to the array
        ajaxBulkRequests.push( bulkGenerateAJAX );
    }

    function does_user_have_ongoing_jobs(){
        var userHasOngoingJobs = false;
        var currentUserId = WTAI_OBJ.current_user_id;
        if( $( '.wtai-loading-estimate-time-container-user-' + currentUserId + '.wtai-ongoing' ).length > 0 ){
            userHasOngoingJobs = true;
        }

        return userHasOngoingJobs;
    }

    function maybe_remove_done_bulk_container(){
        var currentUserId = WTAI_OBJ.current_user_id;

        if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-loading-estimate-time-container-user-' + currentUserId + ' .wtai-bulk-generate-submit').length ){
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-loading-estimate-time-container-user-' + currentUserId + ' .wtai-bulk-generate-submit').each(function(){
                $(this).closest('.wtai-loading-estimate-time-container').remove();

                var requestID = $(this).attr('data-request-id');
                okBulkGenerateOK( requestID );
            });
        }

        if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-loading-estimate-time-container-user-' + currentUserId + ' .wtai-action-bulk-transfer').length ){
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-loading-estimate-time-container-user-' + currentUserId + ' .wtai-action-bulk-transfer').each(function(){
                $(this).closest('.wtai-loading-estimate-time-container').remove();
            });            
        }
    }
    
    function process_queue_generate( requestID, product_id, type, bulk = 0 ){
        if( $('.wtai-global-loader').hasClass('wtai-is-active') == false ){
            $('.wtai-global-loader').addClass('wtai-is-active');   
        }
        if( $('.wtai-ai-logo').hasClass('wtai-hide') == false ){
            $('.wtai-ai-logo').addClass('wtai-hide');   
        }
        var date        = new Date();
        var timedate    = date.toLocaleString();
        var offset      = date.getTimezoneOffset();     
        var wtai_nonce  = get_product_bulk_nonce();  
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_generate_queue_progress',
                browsertime: offset,
                timedate: timedate,
                trim_text: 15,
                requestID: requestID,
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                var setToFinished = false;

                var queuedIds = data.queuedIds;
                if( queuedIds !== undefined && queuedIds.includes( product_id ) ){                
                    if( data.error == '1' ){
                        //meaning queue id is already gone
                        if( data.http_header == '404' ){
                            setToFinished = true;
                        }
                        else{
                            setToFinished = true;

                            //maybe fallback error
                            $('.wtai-global-loader').removeClass('wtai-is-active');
                            $('.wtai-ai-logo').addClass('wtai-hide');   
                        }
                    }
                    else{
                        if( data.completed == '1' ){
                            setToFinished = true;
                        }
                        else{
                            queueGenerateTimer = setTimeout( process_queue_generate, 5000, requestID, product_id, type, bulk );
                        }
                    }                    
                }
                else{
                    setToFinished = true;
                }

                if( setToFinished ){
                    if( bulk == 1 ){
                        fetchFreshTextFromAPI( product_id, '', true, 1, 1 );

                        $('.wtai-global-loader').removeClass('wtai-is-active');    
                        $('.wtai-ai-logo').removeClass('wtai-hide');
                    }
                    else{
                        fetchFreshTextFromAPI( product_id, type, true, 1 );

                        $('.wtai-global-loader').removeClass('wtai-is-active');    
                        $('.wtai-ai-logo').removeClass('wtai-hide');

                        var meta_object = $('.wtai-metabox-' + type);
                        
                        meta_object.removeClass('queue-ongoing');

                        meta_object.addClass('queue-done');
                        meta_object.addClass('wtai-bulk-process');
                        meta_object.addClass('wtai-bulk-complete');

                        meta_object.find('.wtai-checkboxes').prop('disabled', false);

                        maybeDisableBulkButtons();
                    }
                }
            }
        });
    }

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

    $(document).on('wtaBulkGenerateStatusUpdate', function(e, messageEntry){
        e.stopImmediatePropagation();

        var streamId = messageEntry.encodedMsg.id;

        process_bulk_generate( streamId, 1, 1 );
    });

    $(document).on('wtaSingleGenerateImageAltText', function(e, messageEntry){
        e.stopImmediatePropagation();

        var status = messageEntry.encodedMsg.status;
        var recordId = messageEntry.encodedMsg.recordId;

        if( status == 'Completed' ){
            // Lets tag the failed ones as done since this images cannot be generate anyways.
            if( $('.wtai-image-alt-metabox.wtai-error-upload').length ){
                $('.wtai-image-alt-metabox.wtai-error-upload').each(function(){
                    var meta_object = $(this);

                    var image_id = meta_object.attr('data-id');

                    var alt_text = meta_object.find('.wtai-wp-editor-setup-alt').val();

                    var image_elem_id = meta_object.find('.wtai-wp-editor-setup-alt').attr('id');

                    meta_object.find('.wtai-wp-editor-setup-alt').prop('disabled', false);
                    meta_object.find('.wtai-checkboxes-alt').prop('disabled', false);
                    meta_object.find('.wtai-wp-editor-setup-alt').val('');
                    meta_object.find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                    meta_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                    meta_object.find('.wtai-alt-transferred-status-label').addClass('wtai-hide-not-transferred-label');
                    meta_object.find('.wtai-generated-status-label').html( WTAI_OBJ.notGeneratedStatusText );
                    meta_object.find('.wtai-generate-disable-overlay-wrap').addClass( 'wtai-shown' );
                    meta_object.removeClass('wtai-loading-state');
                    meta_object.addClass('wtai-bulk-complete');

                    $('#'+image_elem_id).val(alt_text);

                    updateHiddentextTexarea( image_elem_id );
                    typeCountMessageAltImage( image_id, alt_text );
                });
            }

            fetchFreshImageAltTextFromAPI( recordId, '1', '0', '1', '1' );
        }
    });

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
    
    function clearEditor(id, type) {
        tinymce.get(id).setContent('<span class="typing-cursor">&nbsp;</span>');
        
        if ( $('#wtai-product-details-'+type ).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').length > 0 ) {
            $('#wtai-product-details-'+type ).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').remove();
        }

        var text_html = '';
        switch( type ){
            case 'product_description':
            case 'product_excerpt':
                
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

    function typeWriter( id, text, i, speed, type ) {
        var words = text.split( ' ' );
        var cursor = '<span class="typing-cursor">&nbsp;</span>';
        if ( i < words.length) {
            var value = [];
            for (var ctr = 0; ctr < parseInt(i+1); ctr++) {
                var space  = '';
                if ( ctr == words.length ){
                    space  = '';
                } else {
                    space  = ' ';
                }
                value.push(words[ctr]+space);
            }

            var value_join = value.join(' ');

            if ( parseInt(i+1) == words.length ) {
                cursor = '';
                tinymce.get(id).getBody().classList.add('bgdone');
                setTimeout(function() {
                    tinymce.get(id).getBody().classList.remove('bgdone');
                }, 1500);
            }
            var value_join = value.join(' ') + cursor;
            tinymce.get(id).setContent( value_join );
            typeCountMessage(type, tinymce.get(id).getContent({format: 'text'}) );
            updateHiddentext(id);

            var editor = tinymce.get(id);
            editor.contentWindow.scrollTo(0, editor.contentWindow.document.body.scrollHeight);
            i++;
            setTimeout(typeWriter, speed, id, text, i, speed, type );
        } else {
            $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');
            if ( $('#wtai-product-details-'+type).hasClass('wtai-metabox-update') ){
                $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
            }
            cursor = '';

            addHighlightKeywordsbyFieldOnKeyup(id);

            getKeywordOverallDensity();
        }
    }

    function typeWriterBulk( id, text, i, speed, type ) {
        var words = text.split( ' ' );
        var cursor = '<span class="typing-cursor">&nbsp;</span>';
        if (i < words.length) {
            var value = [];
            for (var ctr = 0; ctr < parseInt(i+1); ctr++) {
                var space  = '';
                if ( ctr == words.length ){
                    space  = '';
                    var cursor = '';
                } else {
                    space  = ' ';
                    
                }
                value.push(words[ctr]+space);
            }
            if ( parseInt(i+1) == words.length ) {
                cursor = '';
                tinymce.get(id).getBody().classList.add('bgdone');
                setTimeout(function() {
                    tinymce.get(id).getBody().classList.remove('bgdone');
                }, 500);
            }
            var value_join = value.join(' ') + cursor;
            tinymce.get(id).setContent( value_join );
            typeCountMessage( type, tinymce.get(id).getContent({format: 'text'}) );

            updateHiddentext(id);
            
            var editor = tinymce.get(id);
            editor.contentWindow.scrollTo(0, editor.contentWindow.document.body.scrollHeight);
            i++;
            if ( $('.wtai-bulk-process:not(.wtai-bulk-writing)').length == 0 ){
                speed = 50;
            }
            setTimeout(typeWriterBulk, speed, id, text, i, speed, type );
            
        } else if ( ! $('#wtai-product-details-'+type).hasClass('wtai-bulk-complete') ){
            $('#wtai-product-details-'+type).removeClass('wtai-metabox-update');
            $('#wtai-product-details-'+type).removeClass('wtai-disabled-click');
            $('#wtai-product-details-'+type).addClass('wtai-bulk-complete');

            getKeywordOverallDensity();
        }
    }

    function typeWriterBulkImageAlt( id, text, i, speed, type ) {
        var words = text.split( ' ' );
        var cursor = '|';
        if (i < words.length) {
            var value = [];
            for (var ctr = 0; ctr < parseInt(i+1); ctr++) {
                var space  = '';
                if ( ctr == words.length ){
                    space  = '';
                    var cursor = '';
                } else {
                    space  = ' ';
                    
                }
                value.push(words[ctr]+space);
            }
            
            var value_join = value.join(' ') + cursor;

            $('#'+id).val(value_join);

            // TODO: add count message
            //typeCountMessage( type, tinymce.get(id).getContent({format: 'text'}) );
            //updateHiddentext(id);
            
            // TODO: Scroll to top after generation
            //var editor = tinymce.get(id);
            //editor.contentWindow.scrollTo(0, editor.contentWindow.document.body.scrollHeight);
            
            i++;
            
            setTimeout(typeWriterBulkImageAlt, speed, id, text, i, speed, type );
        }
    }

    function typeCountMessageAltImage( image_id, text ){
        var type = 'image_alt_text';
        var words_count = wtaiGetWordsArray( text );

        var textLength = 0;
        if( words_count.length > 0 ){
            textLength = text.length;
        }

        var parent_elem = '.wtai-image-alt-metabox-' + image_id;

        if ( textLength > WTAI_OBJ.text_limit[type] )  {
            $(parent_elem).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').addClass('over_limit');
        } else {
            $(parent_elem).find('.wtai-generate-value-wrapper').find('.wtai-text-count-details').removeClass('over_limit');
        }

        $(parent_elem).find('.wtai-generate-value-wrapper').find('.wtai-char-count').html( WTAI_OBJ.char.replace('%char%',  textLength+'/'+WTAI_OBJ.text_limit[type] ) );
        $(parent_elem).find('.wtai-generate-value-wrapper').find('.wtai-char-count').attr( 'data-count', textLength ); 

        $(parent_elem).find('.wtai-generate-value-wrapper').find('.word-count').html(WTAI_OBJ.words.replace('%words%', words_count.length ) );
        $(parent_elem).find('.wtai-generate-value-wrapper').find('.word-count').attr( 'data-count', words_count.length );        
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

        display_reference_product_count();
    }

    function putGridAutomatic( product_id, type, text, trim, not_generate ){
        if ( $('#wtai-table-list-'+product_id).length > 0 ) {
            var prefix = '';
            if ( not_generate ){
                prefix = '';
            } else {
                prefix = 'wtai_';
            }
            $('#wtai-table-list-'+product_id).find('.'+prefix+type).html(trim);
            $('#wtai-table-list-'+product_id).find('.'+prefix+type).attr('data-text',text);

            if ( $('#wtai-table-list-'+product_id).find('.transfer_'+type).find('.transfer_feature').hasClass('wtai-disabled-button') ) {
                $('#wtai-table-list-'+product_id).find('.transfer_'+type).find('.transfer_feature').removeClass('wtai-disabled-button');
                $('#wtai-table-list-'+product_id).find('.transfer_'+type).find('.transfer_feature').removeClass('enabled_button');
            }   
        }
    }

    window.wtaiGetLinkPreview = function() {
        var product_url = $('.wtai-permalink-wrapper > a').attr('href');
        window.open(product_url, '_blank');
    };

    function checkChanges( checkType ) {
        var number_of_changes_unsave = 0;
        $('#postbox-container-2').find('.wtai-metabox').each(
            function(){
                var parentdiv =  $(this);
                var source_newvalue = parentdiv.find('.wtai-data-new-text').html();
                var source_newvalue_stripped = wtaiRemoveLastPipe( parentdiv.find('.wtai-data-new-text').text() );
                var source_origvalue = parentdiv.find('.wtai-data-orig-text').html();

                var addChange = true;

                /*if ( checkType == 'generate' ) {
                    if( parentdiv.find('.postbox-header').find('.wtai-checkboxes').is( ':checked' ) ){
                    }
                    else{
                        addChange = false;
                    }
                }*/
               
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

        // Check if there are unsaved changes in alt images
        if( $('.wtai-product-alt-images-main-wrap .wtai-image-alt-metabox .wtai-checkboxes-alt').length ){
            $('.wtai-product-alt-images-main-wrap .wtai-image-alt-metabox').each(
                function(){
                    var parentdiv =  $(this);
                    var source_newvalue = parentdiv.find('.wtai-data-new-text').html();
                    var source_newvalue_stripped = wtaiRemoveLastPipe( parentdiv.find('.wtai-data-new-text').html() );
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
        }

        return number_of_changes_unsave;
    }

    function updateHiddentext(id) {
        //var content = wp.editor.getContent(id); // Visual tab is active;
        var content = tinymce.get(id).getContent( { format: 'raw' } );

        $('#'+id).closest('.postbox').find('.wtai-hidden-text').remove();
        $('#'+id).closest('.postbox').append('<div class="wtai-hidden-text" style="display:none;"><div class="wtai-data-new-text" style="display:none;">' + content + '|</div><div class="wtai-data-orig-text" style="display:none;">' + content + '|</div></div>' );
        
    }

    function updateHiddentextTexarea(id) {
        var content = $('#'+id).val();

        $('#'+id).closest('.wtai-image-alt-metabox').find('.wtai-hidden-text').remove();
        $('#'+id).closest('.wtai-image-alt-metabox').append('<div class="wtai-hidden-text" style="display:none;"><div class="wtai-data-new-text" style="display:none;">' + content + '|</div><div class="wtai-data-orig-text" style="display:none;">' + content + '|</div></div>' );
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
    
    function updateProductGridTransfer(type,content) {
        var pid = $('#wtai-edit-post-id').attr('value');
        $('#wtai-table-list-' + pid).find('.column-' + type).html(content);
        if( type != 'page_title') {
            $('#wtai-table-list-' + pid).find('.column-' + type).attr('data-text',content);
        }
    }

    function productSingleDataResponse ( response_data ){
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

    /*Post header*/
    $(document).on('click', '.wtai-edit-product-line .postbox-header .hndle', function(e){
        if ( $(e.target).closest('.wtai-premium-wrap').length || $(e.target).hasClass('wtai-premium-wrap') ) {
            return;
        }

        $(this).parent().find('.toggle-indicator').trigger('click');
    });


    /*Highlights Keyword*/
    $(document).on('click', '#wtai-highlight', function(){
        if(this.checked) {
            highlight_keywords('true');
        } else {
            highlight_keywords('false');
        }
    });

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

    $(document).on('wtaGenerateTextStop', function(e, eventInfo){
        e.stopImmediatePropagation();

        var elemId = eventInfo.elemId;
        var doingBulkGenerate = eventInfo.doingBulkGenerate;

        if( doingBulkGenerate ){
            $('#' + elemId).closest('.wtai-bulk-process').addClass('wtai-bulk-complete');
            $('#' + elemId).closest('.wtai-bulk-process').find('.wtai-checkboxes').prop('disabled', false);
        }

        //lets make sure the cursor is not visible anymore
        var content = tinymce.get(elemId).getContent();
        if( $(content).find('.typing-cursor').length ){
            content = content.replace(/\s*<span class="typing-cursor">.*<\/span>/g, '');
            tinymce.get(elemId).setContent( content );
        }

        updateHiddentext(elemId);
    });

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
                    fetchFreshTextFromAPI( productID, '', false, 0, 1, 1, 1 );
                }
            });
        }
    });

    $(document).on('wtaStreamingConnected', function(e){
        e.stopImmediatePropagation();

        loaderBulkLoaded();
    });

    function addHighlightKeywords(){
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var editor = tinymce.get($(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id'));
            if( editor ){
                addHighlightKeywordsbyFieldOnKeyup(editor.id);
            }
        });
    }

    function highlight_keywords(type) {
        if( type == 'true' ) {
            addHighlightKeywords();
            value = 1;
        } else {
            value = 0;
            removeHighlightkeywords();
        }

        var wtai_nonce = get_product_edit_nonce();
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_highlight_check',
                value: value,
                wtai_nonce: wtai_nonce
            },
            success: function() {
            }
        });
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

            if( window.wtaStreamQueueProcessing == true){
                closeLoader = false;

                //console.log('stream queue processing is true ');
            }

            if( $('#wtai-product-generate-completed-bulk').is(':visible') || $('#wtai-product-generate-completed').is(':visible') ){
                closeLoader = true;

                //console.log('single generate done is visible');
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

            if( $('.wtai-loading-estimate-time-container.wtai-ongoing').length && $('body.wtai-open-single-slider').length <= 0 ){
                closeLoader = false;
            }

            if( $('.wtai-loading-estimate-time-container.wtai-done').length && $('.wtai-loading-estimate-time-container.wtai-ongoing').length <= 0 && $('body.wtai-open-single-slider').length <= 0 ){
                closeLoader = true;

                //console.log('force close bulk loader ');
            }
            
            if( closeLoader ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                $('.wtai-ai-logo').removeClass('wtai-hide');

                $('.wtai-slide-right-text-wrapper .wtai-close').removeClass('disabled');
                $('.wtai-slide-right-text-wrapper .wtai-button-prev').removeClass('disabled-nav');
                $('.wtai-slide-right-text-wrapper .wtai-button-next').removeClass('disabled-nav');
            }
        }
    });

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

        removeHighlightkeywords();
        
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var editor = tinymce.get($(this).find('.wtai-columns-3').find('.wp_editor_trigger').attr('id'));
            editor.setContent('');

            var current_value = $(this).find('.wtai-text-message');
            current_value.text('');
        });
    }

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

    function fetchFreshTextFromAPI( product_id, type = '', reloadstats = false, clearQueue = 0, bulk = 0, bulkRemoveDisable = 1, refresh_credits = 0 ){        
        var renderType = '';
        if( type != '' && bulk == 0 ){
            //force the box to load
            $('.wtai-metabox-' + type).addClass('wtai-loading-metabox');

            renderType = type;
        }

        if( $('body.wtai-open-single-slider').length && $('#wtai-edit-post-id').length ){
            var current_post_id = $('#wtai-edit-post-id').val();
            if( parseInt( current_post_id ) != product_id ){
                $('.wtai-global-loader').removeClass('wtai-is-active');
                return;
            }
        }

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_single_product_data_text',
                product_id: product_id, 
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
                                    case 'product_description':
                                    case 'product_excerpt':
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
                        var post_ids = [product_id];

                        if( bulkRemoveDisable == 1 ){                    
                            $('#wtai-table-list-' + product_id).removeClass('wtai-processing');

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
                            $('#wtai-table-list-' + product_id).find('button.transfer_feature').removeClass('wtai-disabled-button');
                        }

                        $('.wtai-global-loader').removeClass('wtai-is-active');
                    }

                    //render data
                    var fields = ['page_title', 'page_description', 'product_description', 'product_excerpt', 'open_graph' ];
                    $.each(fields, 
                        function( index, fieldName ){
                            if( $('#wtai-table-list-' + product_id).length ){
                                $('#wtai-table-list-' + product_id).find('.column-wtai_' + fieldName).attr('data-text', res.result[fieldName] );
                                $('#wtai-table-list-' + product_id).find('.column-wtai_' + fieldName).html( res.result[fieldName + '_trimmed'] );
                            }
                        }
                    );

                    //autoupdate grid from edit generate
                    $('#wtai-table-list-' + product_id).find('.column-wtai_generate_date').html( res.result['generate_date'] );
                }
            }
        });  
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

    var recordGeneratePreselectedAJAX = null;
    $(document).on('change', '.wtai-metabox .postbox-header .wtai-checkboxes', function(){
        record_preselected_field_types();
    });

    function record_preselected_field_types(){
        var selectedTypes = [];
        $('#postbox-container-2').find('.wtai-metabox .postbox-header .wtai-checkboxes').each(function(){
            var cb = $(this);
            var type = cb.attr('data-type');

            if( cb.is(':checked') ){
                selectedTypes.push( type );
            }
        });

        if( $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes:checked').length > 0 ){
            selectedTypes.push( 'image_alt_text' );
        }

        var product_id = $('#wtai-edit-post-id').val();

        // Get all selected image ids for this product
        var selectedImageIds = [];
        $('#postbox-container-2').find('.wtai-image-alt-metabox .wtai-checkboxes-alt').each(function(){
            var cb = $(this);

            if( cb.is(':checked') ){
                selectedImageIds.push( $(this).val() );
            }
        });

        var totalCheckedVariable = 5;
        if( $('.wtai-image-alt-metabox .wtai-checkboxes-alt').length > 0 ){
            totalCheckedVariable++;
        }

        if( $('.wtai-metabox .postbox-header .wtai-checkboxes:checked, .wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes:checked').length >= totalCheckedVariable ){
            $('.wtai-checkboxes-all').prop('checked', true);
        }
        else{
            $('.wtai-checkboxes-all').prop('checked', false);
        }

        //set credits per checked text types
        var isRefchecked = $('input.wtai-custom-style-ref-prod').is(':checked');
        if( isRefchecked && $('.wtai-custom-style-ref-prod-sel').val().trim() != '' ){
            updateReferenceButtonCreditCount();
        }
        else{
            updateGenerateAllButtonCreditCount();
        }

        rewrite_toggle_credit_behavior();
        bulk_transfer_button_behavior();

        var wtai_nonce = get_product_edit_nonce();

        //mayb record selected types
        var data = {
            action           : 'wtai_record_generate_preselected_types',
            selectedTypes    : selectedTypes.join(','),
            product_id       : product_id,
            selectedImageIds : selectedImageIds.join(','),
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
            }
        });
    }

    $(document).on('change', '#wtai-comparison-cb', function(){
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

        var wtai_nonce = get_product_bulk_nonce();
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_comparison_user_check',
                value: value,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    });

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

    function pollBackgroundJobs(){
        if( window.WTAStreamConnected == true ){
            if ( $('.wtai-processing-transfer').length  > 0 ){
                return;
            }

            if( $('.wtai-page-generate-all').hasClass('wtai-generating') ){
                return;
            }

            //bypass if bulk generate already ongoing
            if( bulkGenerateAJAX != null ){
                return;
            }

            if( $('#wtai-loader-estimated-time .wtai-loading-estimate-time-container-temp.wtai-loading-estimate-time-container-generate').length > 0 ){
                return;
            }

            var data = {
                action : 'wtai_poll_background_jobs'
            };

            if( pollBackgroundAJAX != null ){
                pollBackgroundAJAX.abort();
            }

            pollBackgroundAJAX = $.ajax({
                type: 'GET',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data, 
                success: function(data) {
                    if( data.bulk_jobs.length > 0 ){
                        var hasOngoingBulkAction = 0;
                        var hasJobQueue = 0;
                        var hasJobTransferQueue = 0;
                        if( data.has_generate == '1' ){
                            if( data.has_ongoing_generate == '1' ){
                                hasOngoingBulkAction = 1;
                                process_bulk_generate('');
                            }
                            else{
                                refresh_loader_html();
                            }

                            hasJobQueue = 1;
                        }
                        else if( data.has_transfer == '1' ){
                            if( data.transfer_pending_ids.length > 0 && data.transfer_pending_ids[0] != '' ){
                                hasOngoingBulkAction = 1;
                                if( data.own_transfer_job == '1' ){    
                                    if( $('#wtai-loader-estimated-time').is(':visible') == false ) {
                                        $.each(data.transfer_product_ids, function( index, post_id ) {
                                            $('#wtai-table-list-' + post_id).find('.wtai-cwe-selected').prop('checked', true);
                                        });

                                        $.each(data.transfer_pending_ids, function( index, post_id ) {
                                            $('#wtai-table-list-' + post_id).addClass('wtai-processing');
                                            $('#wtai-table-list-' + post_id).addClass('wtai-processing-transfer');
                                        });

                                        var attr        = [];
                                        $('#wtai-bulk-transfer-modal').find('.wtai-product-attr-container').find('.wtai-product-attr-item').each(function(){
                                            if ( $(this).find('.wtai-product-attr-cb').prop('checked') ) {
                                                attr.push( $(this).find('.wtai-product-attr-cb').val() ) ;
                                            }
                                        });

                                        var hasDoneTransfer = false;
                                        if( $('.wtai-loading-estimate-time-container-user-'+WTAI_OBJ.current_user_id+'.wtai-loading-estimate-time-container-transfer.wtai-done').length ){
                                            hasDoneTransfer = true;
                                        }

                                        restartPollBackgroundTimer();
                                        maybe_remove_done_bulk_container();

                                        if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                                            //lets load a temp container
                                            loader_transfer_temp_container( data.transfer_product_ids.length, data.transfer_pending_ids );
                                        }
                                        
                                        var date = new Date();
                                        var offset = date.getTimezoneOffset();
                                        process_bulk_transfer( offset, attr, data.transfer_product_ids.length );
                                    } else {
                                        refresh_loader_html();
                                    }
                                }
                                else{
                                    refresh_loader_html();
                                }
                            }
                            else{
                                refresh_loader_html();
                            }

                            hasJobTransferQueue = 1;
                            hasJobQueue = 1;
                        }
                        
                        if( hasJobQueue == 1 ){                        
                            var timer = 60000;
                            if( hasJobTransferQueue == 1 ){
                                timer = 15000;
                            }

                            pollBackgroundTimer = setTimeout( pollBackgroundJobs, timer ); //lets poll every 15/60 seconds
                        }
                        else{
                            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                            $('#wtai-loader-estimated-time').hide();
                            maybeReenablePendingBulkIds( data.pending_bulk_ids );
                        }
                    }
                    else{
                        //lets poll for jobs every 1 minute
                        refresh_loader_empty_html( data.pending_bulk_ids  );

                        //mayb hide and clear bulk popup if it is existing
                        pollBackgroundTimer = setTimeout( pollBackgroundJobs, 60000 ); //lets poll every 60 seconds
                    }

                    pollBackgroundAJAX = null;
                }
            });
        }
    }

    function refresh_loader_empty_html( all_pending_ids ){
        displayLoaderNotif( '', [], [], '0' );
        shouldHideGenerateLoader();
        maybeReenablePendingBulkIds( all_pending_ids );
    }

    function refresh_loader_html(){
        //bypass if bulk generate already ongoing
        if( bulkGenerateAJAX != null ){
            return;
        }

        var show_hidden = 'yes';
        if( $('.wtai-loading-estimate-time-container-others.hidden').length > 0 ){
            show_hidden = 'no';
        }

        var wtai_nonce = get_product_bulk_nonce();

        var data = {
            action : 'wtai_reload_loader_data',
            show_hidden : show_hidden,
            wtai_nonce : wtai_nonce,
        };

        if( reloadDataAJAX != null ){
            reloadDataAJAX.abort();
        }        

        reloadDataAJAX = $.ajax({
            type: 'GET',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data, 
            success: function(data) {
                if( data.html != '' ){
                    displayLoaderNotif( data.html, data.jobs_user_ids, data.job_loader_data, data.has_error );
                }
                else{
                    shouldHideGenerateLoader();
                }

                maybeReenablePendingBulkIds( data.all_pending_ids );

                reloadDataAJAX = null;
            }
        });
    }

    $(document).on('click', '.wtai-btn-close-error-msge', function(){
        $(this).closest('.wtai-error-msg').addClass('fadeOut');
        setTimeout(function(){
            $('.wtai-keyword .wtai-error-msg').remove();
        }, 500);
    });

    function dont_show_bulk_generate_popup(status) {
        if( status == 'true' ) {
            value = 1;
        } else {
            value = 0;
        }

        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_bulk_generate_popup_check',
                value: value,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    }

    $(document).on('click', '#dont_show_bulk_generate_popup', function(){
        if(this.checked) {
            $('.wtai-product-textfields-container .wtai-product-attr-item .wtai-product-attr-cb').each(function(){
                if( $(this).is(':checked') == false ){
                    $(this).trigger('click');
                }
            });
            $('.wtai-product-textfields-container .wtai-product-attr-item .wtai-product-attr-cb').prop('disabled', true);
            $('.wtai-product-textfields-container .wtai-label-select-all-wrap .wtai-product-cb-all').prop('disabled', true);

            var bulkGenerateCredit = getBulkGenerateCreditCount();

            var credLabel = WTAI_OBJ.creditLabelPlural;
            if( parseInt( bulkGenerateCredit ) == 1 ){
                credLabel = WTAI_OBJ.creditLabelSingular;
            }
            
            dont_show_bulk_generate_popup('true');

            $('.bulk-generate-action.modal').remove();
            html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" onclick="wtaiGoBulkGenerateDirect(this, event)" class="button action bulk-generate-action direct">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
            $( '.actions.bulkactions input#doaction' ).after( html );
            $('#wtai-bulk-generate-ppopup').val(1);
        } 
        else {
            $('.wtai-product-textfields-container .wtai-product-attr-item .wtai-product-attr-cb').prop('disabled', false);
            $('.wtai-product-textfields-container .wtai-label-select-all-wrap .wtai-product-cb-all').prop('disabled', false);
            dont_show_bulk_generate_popup('false');

            $('.bulk-generate-action.direct').remove();
            if ($('.bulk-generate-action').length == 0 ){
                var bulkGenerateCredit = getBulkGenerateCreditCount();

                var credLabel = WTAI_OBJ.creditLabelPlural;
                if( parseInt( bulkGenerateCredit ) == 1 ){
                    credLabel = WTAI_OBJ.creditLabelSingular;
                }

                html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" data-modal="wtai-bulk-generate-modal" onclick="wtaiGetProductAttr(this, this.event)" class="button action bulk-generate-action modal">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
                $( '.actions.bulkactions input#doaction' ).after( html );
                setTimeout(function(){
                    var checkbox = $('#TB_ajaxContent').find('.wtai-product-attr-container').find('.wtai-product-attr-cb:checked').length;
                    if ( checkbox > 0  ){
                        $('#TB_ajaxContent').find('#wtai-generate-bulk-btn').removeAttr('disabled');
                    }
                }, 1000);
            } 
            else {
                $('.bulk-generate-action').css('display','inline');    
            }
        }

        $( '.actions.bulkactions input' ).css('display','none');
    });

    window.wtaiGoBulkGenerateDirect  = function(element, event) {
        event.preventDefault();

        var no_error = true;
        var errors = [];
        if( $('#wtai-bulk-generate-ppopup').attr('data-tones') == '' ){
            no_error = false;
            errors.push( '<p>' + WTAI_OBJ.bulkDirectTonesError + '</p>' );
        }

        if( $('.wtai-bulk-generate-error').length ){
            $('.wtai-bulk-generate-error').remove();
        }

        if( ! no_error ){
            if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                $('.wtai-table-list-wrapper' ).find('#message').remove();
            }
            $('<div id="message" class="error notice is-dismissible">'+errors.join('')+'</div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
            return;
        }

        var event_btn   = $(this);
        var post_ids    = [];
        $('#the-list').find('tr').each(function(){
            if ( $(this).find('.wtai-cwe-selected').prop('checked') ) {
                post_ids.push($(this).find('.wtai-cwe-selected').data('post-id'));
                $(this).find('.wtai-cwe-selected').prop('disabled', true );
                $(this).addClass('wtai-processing');
            }
        });
        
        $('.wtai-global-loader').addClass('wtai-is-active');
        event_btn.prop('disabled', true);

        var queueAPI = 0;
        var bulkOneOnly = 0;
        if( post_ids.length == 1 ){
            queueAPI = 1;
            bulkOneOnly = 1;
        }

        var creditCountNeeded = $('.bulk-generate-action.direct .wtai-credvalue').text();
        
        var wtai_nonce = get_product_bulk_nonce();

        var date = new Date();
        var offset = date.getTimezoneOffset();        
        var data = {
            browsertime: offset,
            action: 'wtai_generate_text',
            product_id: post_ids.join(','), 
            save_generated: 1,
            queueAPI: queueAPI,
            bulkOneOnly: bulkOneOnly,
            creditCountNeeded: creditCountNeeded,
            doingBulkGeneration: '1',
            wtai_nonce : wtai_nonce
        };

        data['fields'] =  $('#wtai-bulk-generate-ppopup').attr('data-textfields');
        data['attr_fields'] =  $('#wtai-bulk-generate-ppopup').attr('data-productattr');
        data['tones'] =  $('#wtai-bulk-generate-ppopup').attr('data-tones');

        if( $('#wtai-bulk-generate-ppopup').attr('data-audiences') != '' ){
            data['audiences'] =  $('#wtai-bulk-generate-ppopup').attr('data-audiences');
        }
        
        data['styles'] = $('#wtai-bulk-generate-ppopup').attr('data-style');
        data['product_description_length_min'] = $('#wtai-bulk-generate-ppopup').attr('data-pdesc_length_min');
        data['product_description_length_max'] = $('#wtai-bulk-generate-ppopup').attr('data-pdesc_length_max');
        data['product_excerpt_length_min'] = $('#wtai-bulk-generate-ppopup').attr('data-pexcerpt_length_min');
        data['product_excerpt_length_max'] = $('#wtai-bulk-generate-ppopup').attr('data-pexcerpt_length_max');

        if ( no_error ){
            restartPollBackgroundTimer();
            maybe_remove_done_bulk_container();

            //lets load temp notif data
            if( $('.wtai-loading-estimate-time-container-user-' + WTAI_OBJ.current_user_id ).length <= 0 ){
                //lets load a temp container
                loader_generate_temp_container( post_ids.length, post_ids );
            }

            bulkGenerateOngoing = true;
            bulkGenerateOngoingIDs = post_ids;

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: data,
                beforeSend: function() {
                    maybe_disable_bulk_actions( '1' );

                    $('#TB_closeWindowButton').click();
                },
                success: function( data ){
                    if( data.access ){
                        if ( data.message ) {
                            if ( data.message == 'expire_token' ){
                                if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                    $('.wtai-table-list-wrapper' ).find('#message').remove();
                                }
                                $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                            } else {
                                if ( $('.wtai-table-list-wrapper' ).find('#message').length > 0  ){
                                    $('.wtai-table-list-wrapper' ).find('#message').remove();
                                }
                                $('<div id="message" class="wtai-bulk-generate-error error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-table-list-wrapper' ).find('.wtai-title-header') );
                            }

                            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                            $('#wtai-loader-estimated-time').hide();
                            maybeReenablePendingBulkIds( [] );
                        } else {
                            if( window.WTAStreamConnected == false ){
                                if ( typeof data.results.requestId !== 'undefined' ) {
                                    process_bulk_generate( data.results.requestId );
                                } 
                                else if ( post_ids.length == 1 ){
                                    process_bulk_generate( '' );
                                }
                            }
                        }
                    } else {
                        var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                        var class_name = 'error notice wtai-bulk-generate-error ';
                        if ( message ){
                            $('<div id="message" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        }

                        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html('');
                        $('#wtai-loader-estimated-time').hide();
                        maybeReenablePendingBulkIds( [] );
                    }                      
                }
            }); 
        }
    };

    function maybe_disable_bulk_actions( disable_transfer_feature = '1' ){
        $('#bulk-action-selector-top').prop('disabled', true );
        $('.bulkactions .action').addClass('disabled'); //added

        $('.wtai-cwe-action-button.generate').addClass('wtai-disabled-button' );
        $('.wtai-cwe-action-button.transfer').addClass('wtai-disabled-button' );
        $('.wtai-bulkactions-wrap #doaction').addClass('disabled');
        $('.wtai-bulkactions-wrap .bulk-generate-action').addClass('disabled');

        if( disable_transfer_feature == '1' ){
            $('button.transfer_feature').addClass('wtai-disabled-button' );
        }
    }   

    $(document).on('click', '.wtai-loading-actions-show-hide-cta', function(e){
        e.preventDefault();

        var type = $(this).attr('data-type');
        if( type == 'show' ){
            $('.wtai-bulk-minimized-wrapper').addClass('hidden');
            $('.wtai-bulk-popup-wrapper').removeClass('hidden');
        }
        else{
            $('.wtai-bulk-popup-wrapper').addClass('hidden');
            $('.wtai-bulk-minimized-wrapper').removeClass('hidden');
        }
    });
    
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


    $(document).on('click', '#wtai-product-generate-completed.wtai-loader-generate .wtai-loading-button-action', function(){
        popupGenerateCompleted('hide');
    });
    $(document).on('click', '#wtai-product-generate-completed-bulk.wtai-loader-generate-bulk .wtai-loading-button-action', function(){
        popupGenerateCompleted('hide');
    });
    function loader_transfer_temp_container( total, postIDs ){
        var tempHtml = WTAI_OBJ.transfer_temp_html;

        tempHtml = tempHtml.replace( '{{startProductCount}}', 0 );
        tempHtml = tempHtml.replace( '{{endProductCount}}', total );
        tempHtml = tempHtml.replace( '{{dataProductIDs}}', postIDs.join(',') );

        //display temp bulk
        if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').length ){
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').prepend( tempHtml );
        }
        else{
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper').prepend( '<div class="wtai-job-list-wrapper">' + tempHtml + '</div>' );
        }      

        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-done');
        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').addClass('wtai-ongoing');
        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label .wtai-bulk-generate-check-label-wrap').html( WTAI_OBJ.bulkGeneratempOngoingText );

        $('#wtai-loader-estimated-time .wtai-bulk-minimized-wrapper').addClass('hidden');
        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper').removeClass('hidden');

        $('#wtai-loader-estimated-time').show();

        handleLoaderNotifShowDisplay();
    }

    function loader_generate_temp_container( total, postIDs ){
        var tempHtml = WTAI_OBJ.generate_temp_html;
        tempHtml = tempHtml.replace( '{{startProductCount}}', 0 );
        tempHtml = tempHtml.replace( '{{endProductCount}}', total );
        tempHtml = tempHtml.replace( '{{dataProductIDs}}', postIDs.join(',') );

        //display temp bulk
        if( $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').length ){
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').prepend( tempHtml );
        }
        else{
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper').prepend( '<div class="wtai-job-list-wrapper">' + tempHtml + '</div>' );
        }      

        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-done');
        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').addClass('wtai-ongoing');
        $('#wtai-loader-estimated-time .wtai-bulk-minimized-label .wtai-bulk-generate-check-label-wrap').html( WTAI_OBJ.bulkGeneratempOngoingText );

        $('#wtai-loader-estimated-time .wtai-bulk-minimized-wrapper').addClass('hidden');
        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper').removeClass('hidden');

        $('#wtai-loader-estimated-time').show();

        handleLoaderNotifShowDisplay();
    }

    function handleLoaderNotifShowDisplay(){
        shouldHideGenerateLoader();
    }

    function displayLoaderNotif( html, jobs_user_ids, job_loader_data, has_error ){
        
        if( $('.wtai-loading-estimate-time-container-temp').length > 0 ){
            $('.wtai-loading-estimate-time-container-temp').removeClass('wtai-loading-estimate-time-container-temp');
        }        

        if( $('body.wtai-open-single-slider').length > 0 ){
            var singleOpen = 'single';
            if( $('.wtai-bulk-minimized-wrapper').hasClass('hidden') ){
                singleOpen = 'grid';
            }

            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( html );
            bulk_popup_position(singleOpen);
        }
        else{
            $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper').html( html );
        }

        //handle minimize message and icon
        if(  $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container.wtai-ongoing').length > 0 ){
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-bulk-error');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-done');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').addClass('wtai-ongoing');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label .wtai-bulk-generate-check-label-wrap').html( WTAI_OBJ.bulkGeneratempOngoingText );
        }
        else{
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-ongoing');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-bulk-error');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').addClass('wtai-done');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label .wtai-bulk-generate-check-label-wrap').html( WTAI_OBJ.bulkGeneratempDoneText );
        }

        if( has_error == '1' ){
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-ongoing');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').removeClass('wtai-done');
            $('#wtai-loader-estimated-time .wtai-bulk-minimized-label').addClass('wtai-bulk-error');
        }

        handleDismissAllDisplay();

        $('#wtai-loader-estimated-time').show();

        handleLoaderNotifShowDisplay();
    }

    function handleDismissAllDisplay(){
        //display ok all if applicable
        var okCtr = 0;
        $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container').each(function(){
            if( $(this).find('.wtai-bulk-generate-submit').length > 0 ){
                okCtr++;
            }
            if( $(this).find('.wtai-action-bulk-transfer').length > 0 && $(this).find('.wtai-action-bulk-transfer').is(':visible') ){
                okCtr++;
            }
        });
 
        if(  $('#wtai-loader-estimated-time .wtai-bulk-popup-wrapper .wtai-job-list-wrapper .wtai-loading-estimate-time-container.wtai-done').length > 1 || okCtr > 1 ){
            $('#wtai-loader-estimated-time .wtai-ok-all-wrap').removeClass('hidden');
        }
        else{
            $('#wtai-loader-estimated-time .wtai-ok-all-wrap').addClass('hidden');
        }
    }

    function restartPollBackgroundTimer(){
        if( typeof pollBackgroundTimer != 'undefined' ){
            clearTimeout( pollBackgroundTimer );
        }

        if (  $('#wtai-loader-estimated-time').is(':visible') ) {
            pollBackgroundTimer = setTimeout( pollBackgroundJobs, 5000 );
        }

        //lets abort current ajax for bulk processes
        if( pollBackgroundAJAX != null ){
            pollBackgroundAJAX.abort();
        }

        if( reloadDataAJAX != null ){
            reloadDataAJAX.abort();
        }   

        if( bulkGenerateAJAX != null ){
            bulkGenerateAJAX.abort();
        }

        if( transferGridAJAX != null ){
            transferGridAJAX.abort();
        }
    }

    function maybeReenablePendingBulkIds( pendingPostIds ){
        if( does_user_have_ongoing_jobs() ){
            return;//bypass enable of generate or transfer buttons
        }

        if( pendingPostIds != null && pendingPostIds.length > 0 ){
            $('#the-list tr').each(function(){
                var postID = $(this).attr('data-id');

                if( pendingPostIds.includes( postID ) ){
                    $(this).find('.wtai-cwe-selected').prop('checked', false );
                    $(this).find('.wtai-cwe-selected').prop('disabled', true );
                    $(this).find('.wtai-cwe-action-button.generate').addClass('wtai-disabled-button');
                    $(this).find('.wtai-cwe-action-button.transfer').addClass('wtai-disabled-button');
                    $(this).find('button.transfer_feature').removeClass('wtai-disabled-button' );
                    $(this).addClass('wtai-processing');
                    $(this).addClass('wtai-processing-transfer');

                    //maybe handle if it is for transfer then we should add class processing to it
                }
                else{
                    $(this).find('.wtai-cwe-selected').prop('disabled', false );
                    $(this).find('.wtai-cwe-action-button.generate').removeClass('wtai-disabled-button');
                    $(this).find('.wtai-cwe-action-button.transfer').removeClass('wtai-disabled-button');
                    $(this).find('button.transfer_feature').removeClass('wtai-disabled-button' );
                    $(this).removeClass('wtai-processing');
                    $(this).removeClass('wtai-processing-transfer');
                }
            });

            $('.wp-list-table').attr('data-bulk-ids', pendingPostIds.join(','));
        }
        else{
            //enable bulk actions
            //remove disabled bulk buttons
            $('#bulk-action-selector-top').prop('disabled', false );
            $('.bulkactions .action').removeClass('disabled'); //added
            
            $('.wtai-bulkactions-wrap #doaction').removeClass('disabled');
            $('.wtai-bulkactions-wrap .bulk-generate-action').removeClass('disabled');
            $('#the-list tr .wtai-cwe-selected').prop('disabled', false );

            $('.wtai-cwe-action-button.generate').removeClass('wtai-disabled-button');
            $('.wtai-cwe-action-button.transfer').removeClass('wtai-disabled-button');
            $('button.transfer_feature').removeClass('wtai-disabled-button' );
            $('#the-list tr').removeClass('wtai-processing');
            $('#the-list tr').removeClass('wtai-processing-transfer');

            $('.wp-list-table').attr('data-bulk-ids', '');
        }        

        if( $('#the-list .wtai-cwe-selected:checked').length <= 0 ){
            $('.cb-select-all-1').prop('checked', false);
        }
    }
    function bulk_popup_position(pos){
        if( pos == 'single' ) {
            $('.wtai-bulk-minimized-wrapper').removeClass('hidden');
            $('.wtai-bulk-popup-wrapper').addClass('hidden');
        }
    }

    $(document).on('click', '.wtai-bulk-retry-link', function( e ){
        e.preventDefault();

        $('.wtai-global-loader').addClass('wtai-is-active');
        var requestID = $(this).attr('data-request-id');
        var wtai_nonce = get_product_bulk_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_retry_bulk_generate',
                requestID: requestID,
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                if( data.html ){
                    displayLoaderNotif( data.html, data.jobs_user_ids, data.job_loader_data, data.has_error );
                }

                if( data.product_ids ){
                    $('#the-list tr').removeClass('wtai-processing');
                    $('#the-list tr .wtai-cwe-selected').prop('disabled', false);
                    $.each( data.product_ids, function( index_product_id, product_id ){
                        $('#the-list #wtai-table-list-' + product_id).addClass('wtai-processing');
                        $('#the-list #wtai-table-list-' + product_id + ' .wtai-cwe-selected').prop('disabled', true);
                    });
                }

                $('.wtai-global-loader').removeClass('wtai-is-active');
            }
        });
    });

    $(document).on('click', '#TB_window.wtai-tb-window-modal-generate .wtai-product-textfields-container .wtai-product-attr-item', function(){
        bulkGenerateSaveTextFieldUserpreference();
    });

    function bulkGenerateSaveTextFieldUserpreference(){
        var fields = [];
        $('#TB_window.wtai-tb-window-modal-generate .wtai-product-textfields-container .wtai-product-attr-item').each(function(){
            var cb = $(this).find('.wtai-product-attr-cb');
            if( cb.is(':checked') == true ){
                fields.push( cb.val() );
            }
        });

        WTAI_OBJ.userGenerateTextFields = fields;

        if( bulkGenerateTextFieldSaveAJAX != null ){
            bulkGenerateTextFieldSaveAJAX.abort();
        }

        var wtai_nonce = get_product_bulk_nonce();

        bulkGenerateTextFieldSaveAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_save_bulk_generate_text_field_user_preference',
                fields: fields.join(','),
                wtai_nonce: wtai_nonce
            },
            success: function() {
                
            }
        });
    }

    $(document).on('click', '#TB_window.wtai-tb-window-modal-transfer .wtai-product-attr-container .wtai-product-attr-item', function(){
        save_transfer_fields_selected();
    });

    function save_transfer_fields_selected(){
        var fields = [];
        $('#TB_window.wtai-tb-window-modal-transfer .wtai-product-attr-container .wtai-product-attr-item').each(function(){
            var cb = $(this).find('.wtai-product-attr-cb');
            if( cb.is(':checked') == true ){
                fields.push( cb.val() );
            }
        });

        if( bulkTransferTextFieldSaveAJAX != null ){
            bulkTransferTextFieldSaveAJAX.abort();
        }

        var wtai_nonce = get_product_bulk_nonce();

        bulkTransferTextFieldSaveAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_save_bulk_transfer_text_field_user_preference',
                fields: fields.join(','),
                wtai_nonce: wtai_nonce
            },
            success: function() {
                
            }
        });
    }

    $('#current-page-selector').keypress(function(event){
        var pageNumber = $(this).val();
        if (event.keyCode === 13) {
            $('form.wtai-wp-table-list-filter').find('#wtai-filter-paged').val( pageNumber );
            $('form.wtai-wp-table-list-filter').submit();
        }
      });

    //check if url has specific parameters
    var urlParams = new URLSearchParams(window.location.search);
    var catParam = urlParams.get('cat');
    var post_statusParam = urlParams.get('post_status');
    if( catParam && !post_statusParam ) {
        $('.wtai-status-view').find('a').removeClass('current');
    }
    
    //Fixed Bulk action in firefox
    if( $('#bulk-action-selector-top').length > 0 ) {
        var wtai_bulk_generate_ppopup = $('#wtai-bulk-generate-ppopup').val();
        var selectedValue = $('#bulk-action-selector-top').val();
        switch( selectedValue ){
            case 'wtai_bulk_generate':
                var bulkGenerateCredit = getBulkGenerateCreditCount();

                var credLabel = WTAI_OBJ.creditLabelPlural;
                if( parseInt( bulkGenerateCredit ) == 1 ){
                    credLabel = WTAI_OBJ.creditLabelSingular;
                }

                $('#doaction').hide();
                if ( wtai_bulk_generate_ppopup ){
                    html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" onclick="wtaiGoBulkGenerateDirect(this, event)" class="button action bulk-generate-action direct">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
                } else {
                    html ='<a href="#" data-title="'+WTAI_OBJ.bulk_generate+'" data-modal="wtai-bulk-generate-modal" onclick="wtaiGetProductAttr(this, this.event)" class="button action bulk-generate-action modal">'+$('#doaction').val()+'<span class="wtai-credit-cost-wrap" style="display: none;" > (<span class="wtai-credvalue">' + bulkGenerateCredit + '</span> <span class="wtai-cred-label" >'+credLabel+'</span>)</span></a>';
                }
                $( '.actions.bulkactions input#doaction' ).after( html );
                break;
            case 'wtai_bulk_transfer':
                $('#doaction').hide();
                html ='<a href="#" data-title="'+WTAI_OBJ.bulk_transfer+'" data-modal="wtai-bulk-transfer-modal" onclick=wtaiGetProductAttr(this) class="button action bulk-generate-action">'+$('#doaction').val()+'</a>';
                $( '.actions.bulkactions input#doaction' ).after( html );
                break;
            default:
                $('#doaction').show();
                break;
        }
    }
    
    if( $('.wtai-keyword-location-code').length > 0 && false ) {        
        $(document).on('change', '.wtai-keyword-location-code', function(){
            if( $('.wtai-target-wtai-keywords-list-wrapper span').length > 0 ){
                $('.wtai-keyword-button .wtai-keyword-getdata-button').removeClass('disabled');
            }

            var product_id = $('#wtai-edit-post-id').attr('value');
            var location_code = $(this).val();

            if( $('.wtai-keyword .wtai-error-msg').length ) {
                $('.wtai-keyword .wtai-error-msg').remove();
            }

            var wtai_nonce = get_product_edit_nonce();

            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_OBJ.ajax_url,
                data: {
                    action: 'wtai_save_product_keyword_location_code',
                    product_id:  product_id,
                    location_code: location_code,
                    wtai_nonce: wtai_nonce
                },
                success: function() {
                    
                }
            });
        });
    }    

    $(document).on('click', '.wtai-generate-wrapper.wtai-postbox-process-wrapper .toggle', function(){
        if( $(this).hasClass('disabled') || $(this).hasClass('wtai-generating') ){
            return;
        }
        $(this).parent().toggleClass('open');
    });
    
    //get bulk generate credit count
    function getBulkGenerateCreditCount(){
        var selectedBulkProducts = $('.wtai-table-list-wrapper #the-list .wtai-cwe-selected:checked').length;
        var creditCounts = WTAI_OBJ.creditCounts;
        var generationCreditCounts = creditCounts['generationParsed'];
        var generateTextFields = WTAI_OBJ.userGenerateTextFields;
        var generationLimitVars = WTAI_OBJ.generationLimitVars;

        var wordsPerCredit = parseInt( generationLimitVars.wordsPerCredit );

        //check if we have a reference product
        var referenceProductID = '';
        var refDescCharLength = 0;
        var refExcerptCharLength = 0;
        var refDescWordLength = 0;
        var refExcerptWordLength = 0;
        var bulk_generation_type = 'generate';
        if( $('#wtai-bulk-custom-style-ref-prod').is(':checked')) {
            var referenceProductData = $('select.wtai-bulk-custom-style-ref-product-select').val();

            if( referenceProductData != '' ){
                var referenceProductArr = referenceProductData.split('-');
                referenceProductID = referenceProductArr[0];

                refDescCharLength = referenceProductArr[5];
                refExcerptCharLength = referenceProductArr[6];

                refDescWordLength = referenceProductArr[3];
                refExcerptWordLength = referenceProductArr[4];

                bulk_generation_type = 'reference';
            }
        }

        var totalCreditBulk = 0;
        var altTextSelected = false;
        if( generateTextFields.length > 0 ){
            if( bulk_generation_type == 'generate' ){
                $.each(generateTextFields, function( index, text_type ){
                    if( text_type == 'product_description' || text_type == 'product_excerpt' ){
                        //get max word count for product description and product excerpt
                        var max_word_length = 0;
                                                
                        if ( $('#' + text_type + '_length_max.wtai-bulk-product-max-length').length > 0 ){
                            max_word_length = $('#' + text_type + '_length_max.wtai-bulk-product-max-length').val();
                        }
                        else{
                            if( text_type == 'product_description' ){
                                max_word_length = generationLimitVars.prodDescMaxWordLength;
                            }
                            else if( text_type == 'product_excerpt' ){
                                max_word_length = generationLimitVars.prodExcerptMaxWordLength;
                            }
                        }
                        
                        max_word_length = parseInt( max_word_length );

                        var creditMultiplier = parseInt( generationCreditCounts[text_type] ); //this is the generation/base
                        var generationTier = parseInt( creditCounts['generationTierParsed'][text_type] ); 
                        var creditNeededForMax = Math.ceil( max_word_length / wordsPerCredit ); //this is the tier level

                        //new formula: new credit cost = base / generation + (generationTier * tier level)
                        var initialCreditNeeded = creditMultiplier + ( generationTier * creditNeededForMax );
                        totalCreditBulk += initialCreditNeeded;
                    }
                    else if( text_type == 'alt_text' ){
                        altTextSelected = true;
                    }
                    else{                        
                        var creditMultiplier = parseInt( generationCreditCounts[text_type] );

                        totalCreditBulk += creditMultiplier;
                    }
                });
            }
            else if( bulk_generation_type == 'reference' ){
                $.each(generateTextFields, function( index, text_type ){
                    if( text_type == 'product_description' || text_type == 'product_excerpt' ){
                        //get max word count for product description and product excerpt
                        var max_word_length = 0;
                        if ( $('#' + text_type + '_length_max.wtai-bulk-product-max-length').length > 0 ){
                            max_word_length = $('#' + text_type + '_length_max.wtai-bulk-product-max-length').val();
                        }
                        else{
                            if( text_type == 'product_description' ){
                                max_word_length = generationLimitVars.prodDescMaxWordLength;
                            }
                            else if( text_type == 'product_excerpt' ){
                                max_word_length = generationLimitVars.prodExcerptMaxWordLength;
                            }
                        }
                        
                        max_word_length = parseInt( max_word_length );

                        var inputWordCount = 0;
                        var field_char_length = 0;
                        if( text_type == 'product_description' ){
                            field_char_length = refDescCharLength;
                            inputWordCount = refDescWordLength;
                        }
                        else if( text_type == 'product_excerpt' ){
                            field_char_length = refExcerptCharLength;
                            inputWordCount = refExcerptWordLength;
                        }

                        var creditMultiplier = parseInt( generationCreditCounts[text_type] ); //this is the generation/base
                        var generationTier = parseInt( creditCounts['generationTierParsed'][text_type] ); 
                        var creditNeededForMax = getReferenceCreditCount( field_char_length, max_word_length, inputWordCount ); //this is the tier level

                        //new formula: new credit cost = base / generation + (generationTier * tier level)
                        var initialCreditNeeded = creditMultiplier + ( generationTier * creditNeededForMax );
                        totalCreditBulk += initialCreditNeeded;
                    }
                    else if( text_type == 'alt_text' ){
                        altTextSelected = true;                        
                    }
                    else{
                        var creditMultiplier = parseInt( generationCreditCounts[text_type] );

                        totalCreditBulk += creditMultiplier;
                    }
                });
            }
        }
                
        var totalCreditsNeeded = 0;
        if( selectedBulkProducts > 0 && isNaN( totalCreditBulk ) == false ){
            totalCreditsNeeded = parseInt( selectedBulkProducts ) * parseInt( totalCreditBulk );
        }

        if( altTextSelected ){        
            // Compute credit needed when alt text for images is available
            var creditsPerAltText = parseInt( generationLimitVars.altText );
            var maxImageAltTextPerRequest = parseInt( generationLimitVars.maxImageAltTextPerRequest );
            if( $('.wp-list-table .wtai-cwe-selected:checked').length > 0 ){
                $('.wp-list-table .wtai-cwe-selected:checked').each(function() {
                    var totalAltImages = 0;
                    var totalAltRequest = 0;
                    var totalAltCreditsNeeded = 0;
                    var selected_post_id = $(this).attr('data-post-id');
                    var altImageIds = $(this).closest('tr').attr('data-image-ids');
                    var altImageIdsArray = [];
                    if( altImageIds != '' ){
                        altImageIdsArray = altImageIds.split(',');

                        totalAltImages += altImageIdsArray.length;

                        totalAltRequest = Math.ceil(totalAltImages / maxImageAltTextPerRequest);
                        totalAltCreditsNeeded = totalAltRequest * creditsPerAltText;
                    }

                    totalCreditsNeeded += totalAltCreditsNeeded;
                });                
            }            
        }

        return totalCreditsNeeded;
    }

    $('.wtai-table-list-wrapper #the-list .wtai-cwe-selected').on('click', function( e ){
        e.stopImmediatePropagation();
        e.stopPropagation();
        
        if( $('.bulk-generate-action .wtai-credvalue').length ){
            var bulkGenerateCredit = getBulkGenerateCreditCount();
            var credLabel = WTAI_OBJ.creditLabelPlural;
            if( parseInt( bulkGenerateCredit ) == 1 ){
                credLabel = WTAI_OBJ.creditLabelSingular;
            }
            $('.bulk-generate-action .wtai-credvalue').text( bulkGenerateCredit );
            $('.bulk-generate-action .wtai-cred-label').text( credLabel );
        }        

        var total_item_count = $('.wtai-table-list-wrapper #the-list .wtai-cwe-selected').length;
        var total_checked_count = $('.wtai-table-list-wrapper #the-list .wtai-cwe-selected:checked').length;

        setTimeout(function(){
            if( total_checked_count < total_item_count ){
                $('.wtai-list-table thead td.check-column input[type="checkbox"]').prop('checked', false);
                $('.wtai-list-table thead td.check-column input[type="checkbox"]').addClass('cb-all-not-checked');
            }
            else{
                $('.wtai-list-table thead td.check-column input[type="checkbox"]').prop('checked', true);
                $('.wtai-list-table thead td.check-column input[type="checkbox"]').addClass('cb-all-checked');
            }
        }, 100); 
    });

    $('.wtai-product-textfields-container .wtai-product-attr-item').on('click', function(){
        if( $('#wtai-generate-bulk-btn .wtai-credvalue').length ){
            
            var textFields = [];
            $('.wtai-product-textfields-container .wtai-product-attr-item').each(function(){
                if( $(this).find('.wtai-product-attr-cb').is(':checked') ){
                    textFields.push( $(this).find('.wtai-product-attr-cb').val() );
                }
            });

            var bulkGenerateCredit = 0;
            if( textFields.length > 0 ){
                WTAI_OBJ.userGenerateTextFields = textFields;

                var bulkGenerateCredit = getBulkGenerateCreditCount();
            }

            var credLabel = WTAI_OBJ.creditLabelPlural;
            if( parseInt( bulkGenerateCredit ) == 1 ){
                credLabel = WTAI_OBJ.creditLabelSingular;
            }
            
            $('.bulk-generate-action .wtai-credvalue').text( bulkGenerateCredit );
            $('#wtai-generate-bulk-btn .wtai-credvalue').text( bulkGenerateCredit );

            $('.bulk-generate-action .wtai-cred-label').text( credLabel );
            $('#wtai-generate-bulk-btn .wtai-cred-label').text( credLabel );
        }        
    });

    $('#cb-select-all-1').on('click', function(){
        setTimeout(function() {
            if( $('.bulk-generate-action .wtai-credvalue').length ){
                var bulkGenerateCredit = getBulkGenerateCreditCount();

                var credLabel = WTAI_OBJ.creditLabelPlural;
                if( parseInt( bulkGenerateCredit ) == 1 ){
                    credLabel = WTAI_OBJ.creditLabelSingular;
                }
                
                $('.bulk-generate-action .wtai-credvalue').text( bulkGenerateCredit );
                $('.bulk-generate-action .wtai-cred-label').text( credLabel );
            }    
        }, 300);
    }); 

    $(document).on('click', '.btn-rewrite-generate', function(e){
        e.preventDefault();

        $('.wtai-page-generate-all').attr('data-rewrite', '1');
        $('.wtai-generate-wrapper .wtai-toggle-wrapper').removeClass('open');

        $('.wtai-page-generate-all').trigger('click');
    }); 

    var oldSelectizeValue;
    var customRefProductChangeEvent = function(){
        $('.wtai-custom-style-ref-prod-sel').addClass('disabled');

        return function() {
            var referenceProduct = arguments[0]; 
            
            if( referenceProduct ){
                $('.wtai-custom-style-ref-prod-sel').removeClass('disabled');
                $('.wtai-custom-style-ref-prod').prop('disabled', false);
                $('.wtai-custom-style-ref-prod').prop('checked', true).trigger('change');
            } 

            var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();

            if( generationType == 'rewrite' ){
                $('.wtai-custom-style-ref-prod-sel').addClass('disabled');
                $('.wtai-custom-style-ref-prod').prop('disabled', true);
                $('.wtai-custom-style-ref-prod').prop('checked', false);
            }

            //display of reference count
            display_reference_product_count();

            //update credit count based on reference product
            updateReferenceButtonCreditCount();
            
            oldSelectizeValue = null;
            this.blur();
        };
    };

    var customRefProductChangeEventBulk = function(){
        $('.wtai-bulk-custom-style-ref-product-select').addClass('disabled');
        return function() {
            var referenceProduct = arguments[0]; 
            if( referenceProduct ){
               $('.wtai-bulk-custom-style-ref-product-select').removeClass('disabled');
               $('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('label').addClass('disabled');
               $('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').addClass('disabled');
               $('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').attr('disabled','disabled');
            } 

            //update bulk credit count based on reference product
            updateReferenceBulkCreditCounts();
            
            oldSelectizeValue = null;
            this.blur();
        };
    };

    Selectize.define( 'preserve_on_blur', function( options ) {
        var self = this;
    
        options.text = options.text || function(option) {
            return option[this.settings.labelField];
        };
        
        this.onBlur = ( function() {
            var original = self.onBlur;  
            
            return function( e ) {
                // Capture the current input value
                var $input = this.$control_input;
                var inputValue = $input.val();
                
                // Do the default actions
                original.apply( this, e );
                
                // Set the value back                    
                this.setTextboxValue( inputValue );
            };                                
        } )();
    } );

    Selectize.define('infinite_scroll', function() {
        var self = this, page = 1;
        
        self.infinitescroll = {
            onScroll: function() {
                var scrollBottom = self.$dropdown_content[0].scrollHeight - (self.$dropdown_content.scrollTop() + self.$dropdown_content.height());
                
                if(scrollBottom < 400){
                    var query = JSON.stringify({
                        search: self.lastValue,
                        page: page
                    });
            
                    self.$dropdown_content.off('scroll');
                    self.onSearchChange(query);
                } 
            }
        };
    
        self.onFocus = (function() {
            var original = self.onFocus;
    
            return function() {
                var query = JSON.stringify({
                    search: self.lastValue,
                    page: page
                });
    
                original.apply(self, arguments);
                self.onSearchChange(query);
            };
        })();
    
        self.onKeyUp = function(e) {
            var self = this;
    
            if (self.isLocked) return e && e.preventDefault();

            var value = self.$control_input.val() || '';
    
            if (self.lastValue !== value) {
                var query = JSON.stringify({
                    search: value,
                    page: page = 1
                });
    
                self.lastValue = value;
                self.onSearchChange(query);
                self.refreshOptions();
                self.clearOptions();
                self.trigger('type', value);
            }
        };
    
       self.on('load',function(){
            page++;
            self.$dropdown_content.on('scroll', self.infinitescroll.onScroll);
        });

    });

    Selectize.define( 'no_results', function( options ) {
        var self = this;
      
        options = $.extend({
          message: WTAI_OBJ.searchNoResult,
      
          html: function(data) {
            return (
              '<div class="selectize-dropdown ' + data.classNames + '">' +
                '<div class="selectize-dropdown-content">' +
                  '<div class="no-results">' + data.message + '</div>' +
                '</div>' +
              '</div>'
            );
          }
        }, options );
      
        self.displayEmptyResultsMessage = function () {
          this.$empty_results_container.css('top', this.$control.outerHeight());
          this.$empty_results_container.css('width', this.$control.outerWidth());
          this.$empty_results_container.show();
          this.$control.addClass('dropdown-active');
        };
      
        self.refreshOptions = (function () {
          var original = self.refreshOptions;
      
          return function () {
            original.apply(self, arguments);
            if (this.hasOptions || !this.lastQuery) {
              this.$empty_results_container.hide();
            } else {
              this.displayEmptyResultsMessage();
            }
          };
        })();
      
        self.onKeyDown = (function () {
          var original = self.onKeyDown;
      
          return function ( e ) {
            original.apply( self, arguments );
            if ( e.keyCode === 27 ) {
              this.$empty_results_container.hide();
            }
          };
        })();
      
        self.onBlur = (function () {
          var original = self.onBlur;
      
          return function () {
            original.apply( self, arguments );
            this.$empty_results_container.hide();
            this.$control.removeClass('dropdown-active');
          };
        })();
      
        self.setup = (function() {
          var original = self.setup;
          return function() {
            original.apply(self, arguments);
            self.$empty_results_container = $(options.html($.extend({
              classNames: self.$input.attr('class')
            }, options)));
            self.$empty_results_container.insertBefore(self.$dropdown);
            self.$empty_results_container.hide();
          };
        })();
    });
    
    $(document).on('click', function(e) {
        var container = $('.wtai-select-wrapper');
        if (!$(e.target).closest(container).length) {
            $('.selectize-control .selectize-input').removeClass('dropdown-active');
        }
    });

    function setDynamicReferenceProducts( productReferenceSelectedData, productReferenceSelected ){
        $('select.wtai-custom-style-ref-prod-sel').selectize()[0].selectize.destroy();
        var wtai_nonce = get_product_edit_nonce();

        /* Initialize select*/
        var totalCount = 0, page = 0, perPage = 50, maxPage = 0;
        var customRefProductSelect = $('select.wtai-custom-style-ref-prod-sel').selectize({
            allowEmptyOption: true,
            normalize: true,
            plugins: ['infinite_scroll', 'preserve_on_blur', 'no_results'],
            sortField: 'text',
            onChange : customRefProductChangeEvent('onChange'),
            load: function(query, callback) {
                if( referenceProductLazyLoadAJAX != null ){
                    return;
                } 

                query = JSON.parse(query);
                page = query.page || 1;

                if( query.search != '' ){
                    page = 1;
                    maxPage = 1;
                    totalCount = 0;
                }

                var loadMore = false;
                if( page <= maxPage ){
                    loadMore = true;
                }

                if(!totalCount || totalCount > ( (page - 1) * perPage) ){   
                    var product_id = 0;
                    if( $('#wtai-edit-post-id').length ){
                        product_id = $('#wtai-edit-post-id').attr('value');
                    }

                    //setTimeout(function() {
                        $('.selectize-dropdown.wtai-custom-style-ref-prod-sel').addClass('wtai-selectize-loading');
                    //}, 300 );
                    
                    referenceProductLazyLoadAJAX = $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_search_reference_product',
                            product_id: product_id,
                            reference_product_id: productReferenceSelected,
                            term: query.search,
                            per_page: perPage,
                            page: query.page,
                            wtai_nonce: wtai_nonce,
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res) {
                            totalCount = res.total_count;
                            maxPage = res.max_page;

                            callback(res.products);     

                            referenceProductLazyLoadAJAX = null;

                            $('.wtai-reference-product-wrapper').attr('data-max-page', res.max_page);

                            $('.selectize-dropdown.wtai-custom-style-ref-prod-sel').removeClass('wtai-selectize-loading');
                        }
                    });
                } 
                else{
                    callback();

                    $('.selectize-dropdown.wtai-custom-style-ref-prod-sel').removeClass('wtai-selectize-loading');
                }
            },
            // workaround
            onFocus: function () {
                oldSelectizeValue = this.getValue();
                this.clear(true);
            },
            onBlur: function () {
                var currentValue = this.getValue();
                
                if ( oldSelectizeValue && currentValue.length == 0 && oldSelectizeValue != currentValue ) {
                    this.setValue(oldSelectizeValue, true);
                }
            }
        });

        //lets preload selectize data
        var product_id = 0;
        if( $('#wtai-edit-post-id').length ){
            product_id = $('#wtai-edit-post-id').attr('value');
        }
        
        referenceProductLazyLoadAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_search_reference_product',
                product_id: product_id,
                reference_product_id: productReferenceSelected,
                term: '',
                per_page: perPage,
                page: 1,
                wtai_nonce: wtai_nonce,
            },
            error: function() {
                
            },
            success: function(res) {
                totalCount = res.total_count;
                maxPage = res.max_page;

                $('.wtai-reference-product-wrapper').attr('data-max-page', res.max_page);

                /* Initialize select*/
                var $select = customRefProductSelect;

                var control = $select[0].selectize;
                control.clear();
                control.clearOptions();
                control.refreshOptions(); // Refresh the dropdown
                control.refreshItems(); // Refresh the selected items
                
                /* Add options and item and then refresh state*/                    
                control.addOption(res.products);

                control.refreshState();      
                
                referenceProductLazyLoadAJAX = null;
            }
        });
    }

    $('.wtai-wp-table-list-filter #wtai-filter-submit').on('click', function(){
        $('#current-page-selector').val(1);
        $('#wtai-filter-paged').val(1);
    });

    function rewrite_toggle_credit_behavior(){
        
        var hasDataText = false;
        $('#postbox-container-2').find('.wtai-metabox').each(function() {
            var content = $(this).find('.wtai-columns-1').find('.wtai-text-message').text();
            var cbChecked = $(this).find('.wtai-checkboxes').is(':checked');
           
            if ( content.trim() != '' && cbChecked ) {
                hasDataText = true;                
            }
        });

        // If alt text is not empty and checked, lets also assume that data is available for rewrite.
        $('#postbox-container-2').find('.wtai-image-alt-metabox').each(function() {
            var cbChecked = $(this).find('.wtai-checkboxes-alt').is(':checked');
            var content = $(this).find('.wtai-current-value').find('.wtai-text-message').text();

            if ( content.trim() != '' && cbChecked ) {
                hasDataText = true;                
            }
        });

        if( ! hasDataText && $('#postbox-container-2').find('.wtai-metabox .wtai-checkboxes:checked').length ){
            $('#wtai-cta-generate-type-generate').prop('checked', true);
            $('#wtai-cta-generate-type-rewrite').prop('checked', false);
            
            $('#wtai-cta-generate-type-rewrite').prop('disabled', true);
            $('#wtai-cta-generate-type-rewrite').closest('label').addClass('disabled');

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage2, 1 );
            }, 300);
        }
        else{
            if( $('#wtai-cta-generate-type-rewrite').is(':checked') == false ){
            }
            
            if( $('.wtai-filter-main-wrap .wtai-custom-style-ref-prod').is(':checked') ){

            }
            else{
                if( $('#wtai-cta-generate-type-rewrite').closest('label').hasClass('wtai-disable-premium-feature') == false ){
                    $('#wtai-cta-generate-type-rewrite').prop('disabled', false);
                    $('#wtai-cta-generate-type-rewrite').closest('label').removeClass('disabled');
                }

                $('.wtai-postbox-process-style-tone-wrapper .wtai-button-label').removeClass('disabled-select');
            }   

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage2, 0 );
            }, 300);
        }

        var cta_type = $('.wtai-cta-radio:checked').val();

        if( hasDataText && cta_type == 'rewrite' ){
            //set credits per checked text types
            updateRewriteButtonCreditCount();
        } 
        else{
            var isRefchecked = $('input.wtai-custom-style-ref-prod').is(':checked');
            if( isRefchecked && $('.wtai-custom-style-ref-prod-sel').val().trim() != '' ){
                updateReferenceButtonCreditCount();
            }
            else{
                updateGenerateAllButtonCreditCount();
            }
        }

        if( lastGenerationTypeSelected == 'rewrite' ){
            if( $('#wtai-cta-generate-type-rewrite').is(':disabled') == false ){
                $('#wtai-cta-generate-type-generate').prop('checked', false);
                $('#wtai-cta-generate-type-rewrite').prop('checked', true);
            }
        }

        var hasSelected = false;
        $('.postbox-container .wtai-checkboxes').each(function(){
            var data_type = $(this).attr('data-type');
            if( $(this).is(':checked') && data_type != 'image_alt_text' ){
                hasSelected = true;
            }
        });

        if( ! hasSelected && ! hasDataText ){
            $('#wtai-cta-generate-type-generate').prop('checked', true);
            $('#wtai-cta-generate-type-rewrite').prop('checked', false);
            
            $('.wtai-page-generate-all').attr('data-rewrite', '0');
            $('.wtai-cta-type-label').text( WTAI_OBJ.generateCTAText );

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage2, 1 );
            }, 300);
        }

        //lets ensure the label of the button is updated
        var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();

        if( generationType == 'rewrite' ){
            $('.wtai-cta-type-label').text( WTAI_OBJ.rewriteCTAText );
        }
        else{
            $('.wtai-cta-type-label').text( WTAI_OBJ.generateCTAText );
        }

        var disableRewrite = false;
        if( $('.wtai-filter-main-wrap .wtai-custom-style-ref-prod').is(':checked') ){
            disableRewrite = true;

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage1, 1 );
            }, 300);
        }
        else if( ! hasDataText ){
            disableRewrite = true;

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage2, 1 );
            }, 300);
        }

        if( disableRewrite ){
            $('#wtai-cta-generate-type-rewrite').prop('disabled', true);
            $('#wtai-cta-generate-type-rewrite').closest('label').addClass('disabled');

            setTimeout(function() {
                toggleRewriteDisabledTooltipState( WTAI_OBJ.tooltipDisableRewriteMessage2, 1 );
            }, 300);
        }
        else{
            $('#wtai-cta-generate-type-rewrite').prop('disabled', false);
            $('#wtai-cta-generate-type-rewrite').closest('label').removeClass('disabled');
        }

        generationType = $('input[name="wtai_cta_generate_type"]:checked').val();

        if( generationType == 'rewrite' ){
            $('.wtai-custom-style-ref-prod').prop('checked', false);
            $('.wtai-custom-style-ref-prod').prop('disabled', true);
            $('.wtai-custom-style-ref-prod').closest('label').addClass('disabled-label');
            $('.wtai-custom-style-ref-prod-sel').addClass('disabled');


            updateToolTipForReferenceProduct( WTAI_OBJ.tooltipDisableReferenceMessage2, 1, 'full' );
        }
        else{
            $('.wtai-custom-style-ref-prod').prop('disabled', false);
            $('.wtai-custom-style-ref-prod').closest('label').removeClass('disabled-label');
            $('.wtai-custom-style-ref-prod-sel').removeClass('disabled');

            updateToolTipForReferenceProduct( '...', 0, 'full' );      
        }
    }

    $('.wtai-no-activity-days').on('click', function(){
        if( $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').is(':checked') == false ){
            $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').prop('checked', true);
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }

        $('.wtai-custom-status-cb').prop('checked', false);
        $('.wtai-custom-reviewer-status-cb').prop('checked', false);
    });

    $('.wtai-no-activity-days').on('keyup', function(){
        if( $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').is(':checked') == false ){
            $(this).closest('.wtai-activity-wrapper').find('input[type="radio"]').prop('checked', true);
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }

        $('.wtai-custom-status-cb').prop('checked', false);
        $('.wtai-custom-reviewer-status-cb').prop('checked', false);

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
        $('.wtai-custom-reviewer-status-cb').prop('checked', false);

        setTimeout(() => {
            validate_activity_max_days();
        }, 200);
    });

    if( $('.wtai-status-checkbox-options').length > 0 ){
        var numfields = $('.wtai-status-checkbox-options .wtai-col-1 input:checked').length;
        var activeType = $('.wtai-status-checkbox-options .wtai-col-2 input:checked').val();
        if ( numfields == 5 && activeType == 'all') {
            $('#wtai-sel-writetext-status .wtai-filter-option-label').removeClass('wtai-notdefault');
        } else {
            $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
        }
        $('.wtai-status-checkbox-options input').on('change', function() {
            
            var numfields = $('.wtai-status-checkbox-options .wtai-col-1 input:checked').length;
            var activeType = $('.wtai-status-checkbox-options .wtai-col-2 input:checked').val();
            if ( numfields == 5 && activeType == 'all') {
                $('#wtai-sel-writetext-status .wtai-filter-option-label').removeClass('wtai-notdefault');
            } else {
                $('#wtai-sel-writetext-status .wtai-filter-option-label').addClass('wtai-notdefault');
            }
        });
    }

    $(document).on('click', '.wtai-product-cb-all', function(){
        if( $(this).closest('.wtai-product-all-trigger').hasClass('wtai-product-textfield-wrap') ){
            setTimeout(function(){
                bulkGenerateSaveTextFieldUserpreference();

                updateReferenceBulkCreditCounts();
            }, 300);
        }
    });

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

    $(document).on('change', '.wtai-bulk-custom-style-ref-prod', function(){
        //added by mcr
        $(this).closest('.wtai-reference-product-wrapper').find('.items').removeClass('warning');

        if ( $(this).is(':checked') ){
            $(this).closest('.wtai-reference-product-wrapper').find('.wtai-bulk-custom-style-ref-product-select').removeClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('label').addClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').addClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').attr('disabled','disabled');

            setTimeout( function(){
                try{
                    if ($('.wtai-product-tonestyles-container-bulk').hasClass('tooltipstered')) {
                        $('.wtai-product-tonestyles-container-bulk').each(function(){
                            $(this).tooltipster('enable');
                        });
                    }
                }
                catch(err) {
                }
            }, 300 );
        } else {

            $(this).closest('.wtai-reference-product-wrapper').find('.wtai-bulk-custom-style-ref-product-select').addClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('label').removeClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').removeClass('disabled');
            $(this).closest('#TB_ajaxContent').find('.wtai-product-tonestyles-container').find('input').removeAttr('disabled');

            setTimeout( function(){
                try{
                    if ($('.wtai-product-tonestyles-container-bulk').hasClass('tooltipstered')) {
                        $('.wtai-product-tonestyles-container-bulk').each(function(){
                            $(this).tooltipster('disable');
                        });
                    }
                }
                catch(err) {
                }
            }, 300 );
        } 

        //update bulk reference credit counts
        updateReferenceBulkCreditCounts();
    });

    $(document).on('click', '#wtai-woocommerce-product-attributes .wtai-post-data label.details', function(){
        if( $(this).closest('li').find('input[type="checkbox"]').length ){
            $(this).closest('li').find('input[type="checkbox"]').trigger('click');
        }
    });

    function handle_save_button_state(){
        var number_of_changes_unsave = checkChanges('generate');

        if( number_of_changes_unsave > 0 ){
            $('#save-action .wtai-bulk-button-text').removeClass('disabled');
        }
        else{
            $('#save-action .wtai-bulk-button-text').addClass('disabled');
        }
    }

    setDynamicBulkReferenceProducts();
    function setDynamicBulkReferenceProducts(){
        var wtai_nonce = get_product_bulk_nonce();

        $('select.wtai-bulk-custom-style-ref-product-select').selectize()[0].selectize.destroy();

        /* Initialize select*/
        var totalCount = 0, page = 0, perPage = 50, maxPage = 0;
        var customBulkRefProductSelect = $('select.wtai-bulk-custom-style-ref-product-select').selectize({
            allowEmptyOption: true,
            normalize: true,
            plugins: ['infinite_scroll', 'preserve_on_blur', 'no_results'],
            sortField: 'text',
            onChange : customRefProductChangeEventBulk('onChange'),
            load: function(query, callback) {
                if( referenceProductBulkLazyLoadAJAX != null ){
                    return;
                }

                query = JSON.parse(query);
                page = query.page || 1;

                if( query.search != '' ){
                    page = 1;
                    totalCount = 0;
                    maxPage = 1;
                }
                
                var loadMore = false;
                if( page <= maxPage ){
                    loadMore = true;
                }

                if(!totalCount || totalCount > ( (page - 1) * perPage) ){     
                   $('.selectize-dropdown.wtai-bulk-custom-style-ref-product-select').addClass('wtai-selectize-loading');

                    referenceProductBulkLazyLoadAJAX = $.ajax({
                        type: 'POST',
                        dataType: 'JSON',
                        url: WTAI_OBJ.ajax_url,
                        data: {
                            action: 'wtai_search_reference_product',
                            product_id: 0,
                            reference_product_id: 0,
                            term: query.search,
                            per_page: perPage,
                            page: query.page,
                            wtai_nonce: wtai_nonce,
                        },
                        error: function() {
                            callback();
                        },
                        success: function(res) {
                            totalCount = res.total_count;
                            maxPage = res.max_page;
                            
                            callback(res.products);   
                            
                            referenceProductBulkLazyLoadAJAX = null;

                            $('.selectize-dropdown.wtai-bulk-custom-style-ref-product-select').removeClass('wtai-selectize-loading');
                        }
                    });
                } else {
                    callback();
                    
                    $('.selectize-dropdown.wtai-bulk-custom-style-ref-product-select').removeClass('wtai-selectize-loading');
                }
            },
            // workaround
            onFocus: function () {
                oldSelectizeValue = this.getValue();
                this.clear(true);
            },
            onBlur: function () {
                var currentValue = this.getValue();

                if ( oldSelectizeValue && currentValue.length == 0 && oldSelectizeValue != currentValue ) {
                    this.setValue(oldSelectizeValue, true);
                }
            }
        });

        referenceProductBulkLazyLoadAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_search_reference_product',
                product_id: 0,
                reference_product_id: 0,
                term: '',
                per_page: perPage,
                page: 1,
                wtai_nonce: wtai_nonce,
            },
            error: function() {
                try{
                    callback();
                }
                catch( err ){
                }
            },
            success: function(res) {
                totalCount = res.total_count;
                maxPage = res.max_page;

                /* Initialize select*/
                var $select = customBulkRefProductSelect;

                var control = $select[0].selectize;
                control.clear();
                control.clearOptions();
                control.refreshOptions(); // Refresh the dropdown
                control.refreshItems(); // Refresh the selected items
                
                /* Add options and item and then refresh state*/                    
                control.addOption(res.products);

                control.refreshState();      
                
                referenceProductBulkLazyLoadAJAX = null;
            }
        });
    }

    //get credit count product edit single
    function getCreditCount( type = 'generate', refDescCharLength = 0, refExcerptCharLength = 0, refDescWordLength = 0, refExcerptWordLength = 0 ){
        var generationLimitVars = WTAI_OBJ.generationLimitVars;
        var creditNeeded = 0;
        var creditCounts = WTAI_OBJ.creditCounts;

        var wordsPerCredit = parseInt( generationLimitVars.wordsPerCredit );

        if( type == 'generate' ){
            //calculate credit count for normal generation
            $('.wtai-metabox .postbox-header .wtai-checkboxes').each(function(){
                var data_type = $(this).attr('data-type');
                var isChecked = $(this).is(':checked');

                if( isChecked ){
                    if( isNaN( creditCounts['generationParsed'][data_type] ) == false ){
                        if( data_type == 'product_description' || data_type == 'product_excerpt' ){
                            //get max word count for product description and product excerpt
                            var max_word_length = $('.wtai-metabox #' + data_type + '_length_max').val();
                            max_word_length = parseInt( max_word_length );

                            var creditMultiplier = parseInt( creditCounts['generationParsed'][data_type] ); //this is the generation/base
                            var generationTier = parseInt( creditCounts['generationTierParsed'][data_type] ); 
                            var creditNeededForMax = Math.ceil( max_word_length / wordsPerCredit ); //this is the tier level

                            //new formula: new credit cost = base / generation + (generationTier * tier level)
                            var initialCreditNeeded = creditMultiplier + ( generationTier * creditNeededForMax );
                            creditNeeded += initialCreditNeeded;
                        }
                        else{
                            creditNeeded += parseInt( creditCounts['generationParsed'][data_type] );
                        }
                    }
                }                
            });
        }
        else if( type == 'rewrite' ){
            //calculate credit count for rewrite generation
            $('#postbox-container-2').find('.wtai-metabox').each(function() {
                var data_type = $(this).attr('data-type');
                var content = $(this).find('.wtai-columns-1').find('.wtai-text-message').html(); //lets include the html tags in count
                var isChecked = $(this).find('.wtai-checkboxes').is(':checked');
            
                if ( content.trim() != '' && isChecked ) {
                    if( isNaN( creditCounts['generationParsed'][data_type] ) == false ){
                        if( data_type == 'product_description' || data_type == 'product_excerpt' ){
                            //get max word count for product description and product excerpt
                            var max_word_length = $('.wtai-metabox #' + data_type + '_length_max').val();
                            max_word_length = parseInt( max_word_length );

                            //var field_char_length = content.length;
                            var field_char_length = $(this).find('.wtai-static-count-display .wtai-char-count').attr('wtai-char-count-credit');
                            var inputWordCount = $(this).find('.wtai-static-count-display .word-count').text();

                            var creditMultiplier = parseInt( creditCounts['generationParsed'][data_type] ); //this is the generation/base
                            var generationTier = parseInt( creditCounts['generationTierParsed'][data_type] );
                            var creditNeededForMax = getReferenceCreditCount( field_char_length, max_word_length, inputWordCount ); //this is the tier level
                            
                            //new formula: new credit cost = base / generation + (generationTier * tier level)
                            var initialCreditNeeded = creditMultiplier + ( generationTier * creditNeededForMax );
                            creditNeeded += initialCreditNeeded;
                        }
                        else{
                            creditNeeded = parseInt( creditNeeded ) + parseInt( creditCounts['generationParsed'][data_type] );
                        }
                    }
                }
            });
        }
        else if( type == 'reference' ){
            //calculate credit count for generation with reference product
            $('#postbox-container-2').find('.wtai-metabox').each(function() {
                var data_type = $(this).attr('data-type');
                var isChecked = $(this).find('.wtai-checkboxes').is(':checked');
            
                if ( isChecked ) {
                    if( isNaN( creditCounts['generationParsed'][data_type] ) == false ){
                        if( data_type == 'product_description' || data_type == 'product_excerpt' ){
                            //get max word count for product description and product excerpt
                            var max_word_length = $('.wtai-metabox #' + data_type + '_length_max').val();
                            max_word_length = parseInt( max_word_length );

                            var inputWordCount = 0;
                            var field_char_length = 0;
                            if( data_type == 'product_description' ){
                                field_char_length = refDescCharLength;
                                inputWordCount = refDescWordLength;
                            }
                            else if( data_type == 'product_excerpt' ){
                                field_char_length = refExcerptCharLength;
                                inputWordCount = refExcerptWordLength;
                            }                            

                            var creditMultiplier = parseInt( creditCounts['generationParsed'][data_type] ); //this is the generation/base
                            var generationTier = parseInt( creditCounts['generationTierParsed'][data_type] );
                            var creditNeededForMax = getReferenceCreditCount( field_char_length, max_word_length, inputWordCount ); //this is the tier level

                            //new formula: new credit cost = base / generation + (generationTier * tier level)
                            var initialCreditNeeded = creditMultiplier + ( generationTier * creditNeededForMax );
                            creditNeeded += initialCreditNeeded;
                        }
                        else{
                            creditNeeded = parseInt( creditNeeded ) + parseInt( creditCounts['generationParsed'][data_type] );
                        }
                    }
                }
            });
        }

        // Compute credit needed when alt text for images is available
        var creditsPerAltText = parseInt( generationLimitVars.altText );
        var maxImageAltTextPerRequest = parseInt( generationLimitVars.maxImageAltTextPerRequest );
        var totalAltImages = 0;
        if( $('.wtai-image-alt-metabox .wtai-checkboxes-alt:checked').length > 0 ){
            $('.wtai-image-alt-metabox .wtai-checkboxes-alt:checked').each(function() {
                if( $( this ).prop('disabled') == false ){
                    totalAltImages++;
                }
            });
        }

        var totalAltRequest = Math.ceil(totalAltImages / maxImageAltTextPerRequest);
        var totalAltCreditsNeeded = totalAltRequest * creditsPerAltText;

        creditNeeded += totalAltCreditsNeeded;

        return creditNeeded;
    }

    //debug credit matrix count
    function getReferenceCreditCount( field_char_length = 0, max_word_length = 0, input_word_count = 0 ){
        field_char_length = parseInt( field_char_length );
        var referenceCreditMatrix = getReferenceCreditMatrix( input_word_count );

        var fieldCreditCount = 0;
        
        //lets check matrix for the credit needed
        for( var i = 0; i < referenceCreditMatrix.length; i++ ){
            var matrix = referenceCreditMatrix[i];
            var inputCharLength = matrix.inputCharLength;

            var wordLength = matrix.wordLength;
            var creditCount = matrix.creditCount;

            var nextInputCharLength = 0;
            var nextWordLength = 0;
            var nextMatrixIndex = i+1;
            if( nextMatrixIndex < referenceCreditMatrix.length ){
                var nextMatrix = referenceCreditMatrix[nextMatrixIndex];

                nextInputCharLength = nextMatrix.inputCharLength;
                nextWordLength = nextMatrix.wordLength;
            }
            
            if( ( field_char_length <= inputCharLength && field_char_length > nextInputCharLength ) || 
                ( max_word_length <= wordLength && max_word_length > nextWordLength )
            ){
                fieldCreditCount = creditCount;
                break;
            }
        }

        var maxCreditAllowed = getMaxCreditAllowed();
        if( parseInt( fieldCreditCount ) > parseInt( maxCreditAllowed ) ){
            fieldCreditCount = maxCreditAllowed;
        }

        return fieldCreditCount;
    }

    function getMaxCreditAllowed(){
        var generationLimitVars = WTAI_OBJ.generationLimitVars;

        var input_word_count = parseInt( input_word_count );
        var wordsPerCredit = parseInt( generationLimitVars.wordsPerCredit );
        var maxReferenceTextLength = parseInt( generationLimitVars.maxReferenceTextLength );
        var additionalReferenceTextLength = parseInt( generationLimitVars.additionalReferenceTextLength );
        var maxOutputWords = parseInt( generationLimitVars.maxOutputWords );

        var maxLoop = Math.ceil( maxOutputWords / wordsPerCredit );
        var referenceCreditMatrix = [];

        var inputCharLength = 0;
        var wordLength = 0;
        var matrixIndex = 0;
        for( var c = 1; c <= maxLoop; c++ ){
            if( c == 1 ){
                inputCharLength = inputCharLength + maxReferenceTextLength;
            }
            else{
                inputCharLength = inputCharLength + additionalReferenceTextLength;
            }

            wordLength += wordsPerCredit;

            referenceCreditMatrix[matrixIndex] = {
                'creditCount': c,
                'inputCharLength': inputCharLength,
                'wordLength': wordLength
            };

            matrixIndex++;
        }

        //lets sort the matrix in descending order
        referenceCreditMatrix.sort((a, b) => b.creditCount - a.creditCount);

        return referenceCreditMatrix[0].creditCount;
    }

    function getReferenceCreditMatrix( input_word_count = 0 ){
        var generationLimitVars = WTAI_OBJ.generationLimitVars;

        var input_word_count = parseInt( input_word_count );
        var wordsPerCredit = parseInt( generationLimitVars.wordsPerCredit );
        var maxReferenceTextLength = parseInt( generationLimitVars.maxReferenceTextLength );
        var additionalReferenceTextLength = parseInt( generationLimitVars.additionalReferenceTextLength );
        var maxOutputWords = parseInt( generationLimitVars.maxOutputWords );

        var maxInputWords = 0; //max should always be limited to max output words

        if( input_word_count > maxOutputWords ){
            maxInputWords = input_word_count;
        }
        else{
            maxInputWords = maxOutputWords;
        }

        var maxLoop = Math.ceil( maxInputWords / wordsPerCredit );
        var referenceCreditMatrix = [];

        var inputCharLength = 0;
        var wordLength = 0;
        var matrixIndex = 0;
        for( var c = 1; c <= maxLoop; c++ ){
            if( c == 1 ){
                inputCharLength = inputCharLength + maxReferenceTextLength;
            }
            else{
                inputCharLength = inputCharLength + additionalReferenceTextLength;
            }

            wordLength += wordsPerCredit;

            referenceCreditMatrix[matrixIndex] = {
                'creditCount': c,
                'inputCharLength': inputCharLength,
                'wordLength': wordLength
            };

            matrixIndex++;
        }

        //lets sort the matrix in descending order
        referenceCreditMatrix.sort((a, b) => b.creditCount - a.creditCount);

        return referenceCreditMatrix;
    }

    $(document).on('change', '.wtai-single-product-max-length', function(){
        var max_length = $(this).val();
        max_length = parseInt( max_length );

        //trigger change of generate all button credit count
        updateGenerateAllButtonCreditCount();

        //trigger change for rewrite 
        updateRewriteButtonCreditCount();

        //trigger change for reference product
        updateReferenceButtonCreditCount();
    });

    function updateGenerateAllButtonCreditCount(){
        var cta_type = $('.wtai-cta-radio:checked').val();
        if( cta_type == 'rewrite' ){
            updateRewriteButtonCreditCount();
            return;
        }

        //set credits per checked text types
        var creditNeeded = getCreditCount('generate', 0, 0, 0, 0);
        $('.wtai-page-generate-all').find('.wtai-credvalue').html(creditNeeded);

        if( creditNeeded == 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelSingular);
        }
        else if( creditNeeded > 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelPlural);
        }
    }

    function updateRewriteButtonCreditCount(){
        var cta_type = $('.wtai-cta-radio:checked').val();

        if( cta_type == 'generate' ){
            updateGenerateAllButtonCreditCount();
            return;
        }

        //set credits per checked text types
        var creditValue = getCreditCount('rewrite', 0, 0, 0, 0);

        $('.wtai-page-generate-all').find('.wtai-credvalue').html( creditValue );
        if( creditValue == 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelSingular);
        }
        else if( creditValue > 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelPlural);
        }
    }

    function updateReferenceButtonCreditCount(){
        var hasRefProd = false;
        var ischecked = $('input.wtai-custom-style-ref-prod').is(':checked');
        if( ischecked && $('.wtai-custom-style-ref-prod-sel').val().trim() != '' ){
            hasRefProd = true;
        }

        if( ! hasRefProd ){
            updateGenerateAllButtonCreditCount(); //reset to normal generate credit count
            return;
        }

        var refProduct = $('.wtai-custom-style-ref-prod-sel').val();
        var refProductArr = refProduct.split('-');

        var refProductDescCharLength = refProductArr[5]; //this includes html tags count
        var refProductExcerptCharLength = refProductArr[6]; //this includes html tags count

        var refProductDescWordLength = refProductArr[3];
        var refProductExcerptWordLength = refProductArr[4];

        //set credits per checked text types
        var creditNeeded = getCreditCount('reference', refProductDescCharLength, refProductExcerptCharLength, refProductDescWordLength, refProductExcerptWordLength);
        $('.wtai-page-generate-all').find('.wtai-credvalue').html(creditNeeded);

        if( creditNeeded == 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelSingular);
        }
        else if( creditNeeded > 1 ){
            $('.wtai-page-generate-all').find('.wtai-cred-label').html(WTAI_OBJ.creditLabelPlural);
        }
    }

    $(document).on('keypress', '.wtai-bulk-product-max-length, .wtai-single-product-max-length', function(event){
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if(keycode == '13'){
            var max = $(this).attr('data-maxtext');
            var xval = $(this).val();

            if( isNaN( xval ) ){
                xval = 0;
            }

            if( parseInt( xval ) > parseInt( max ) ){
                $(this).val( max );
                $(this).trigger('change');
            }
        }
    });

    $(document).on('change', '.wtai-bulk-product-max-length', function(){
        //trigger change of bulk generate all button credit count
        updateReferenceBulkCreditCounts();
    });

    function updateReferenceBulkCreditCounts(){
        var bulkGenerateCredit = getBulkGenerateCreditCount();

        var credLabel = WTAI_OBJ.creditLabelPlural;
        if( parseInt( bulkGenerateCredit ) == 1 ){
            credLabel = WTAI_OBJ.creditLabelSingular;
        }

        $('.bulk-generate-action .wtai-credvalue').text( bulkGenerateCredit );
        $('#wtai-generate-bulk-btn .wtai-credvalue').text( bulkGenerateCredit );

        $('.bulk-generate-action .wtai-cred-label').text( credLabel );
        $('#wtai-generate-bulk-btn .wtai-cred-label').text( credLabel );
    }

    //set semantic keyword active count
    function setSemanticActiveCount(){
        $(document).trigger('wtai_set_semantic_keyword_active_count');
    }

    // Note: Common function
    $(document).on('keydown keyup change', '.wtai-char-count-parent-wrap .wtai-max-length-field', function(){
        var xval = $(this).val();
        var charLength = xval.length;

        $(this).closest('.wtai-char-count-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count').html( charLength );
    });

    // Note: Common function
    $(document).on('keydown keyup change', '.wtai-char-count-bulk-parent-wrap .wtai-max-length-field', function(){
        var xval = $(this).val();
        var charLength = xval.length;

        $(this).closest('.wtai-char-count-bulk-parent-wrap').find('.wtai-char-count-wrap .wtai-char-count-bulk').html( charLength );
    });

    function singleGenerationResetIdle(){
        if( $('body.wtai-open-single-slider').length ){
            if( $('.wtai-metabox.wtai-bulk-process').length ){
                $('.wtai-metabox.wtai-bulk-process').each(function(){
                    var meta_object = $(this);
                    meta_object.removeClass('wtai-bulk-complete');
                    meta_object.removeClass('wtai-bulk-writing');
                    
                    meta_object.removeClass('wtai-bulk-process');
                    meta_object.find('.wtai-checkboxes').prop('disabled', false );
                    meta_object.removeClass('wtai-disabled-click');
                });

                $('.wtai-page-generate-all').removeClass('disabled');
                $('.wtai-page-generate-all').removeClass('wtai-generating');
                $('.wtai-generate-cta-radio-wrap').removeClass('wtai-generation-ongoing');
                show_ongoing_generation_tooltip( 'hide' );
            }
        }
    }

    function set_disallowed_combinations_bulk(){
        var disallowed_combinations = WTAI_OBJ.disallowedCombinations;

        var checked_ids = [];

        //applies to tones and audiences
        if( $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="checkbox"]').length ){
            $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="checkbox"]').each(function(){
                if( $(this).is(':checked') && $(this).val() != 'wtaCustom' ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        //applies to style
        if( $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').length ){
            $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').each(function(){
                if( $(this).is(':checked') && $(this).val() != 'wtaCustom' ){
                    checked_ids.push( $(this).val() );
                }
            });
        }

        $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="checkbox"]').closest('label').removeClass('disabled-label');
        $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="checkbox"]').prop('disabled', false);

        $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').closest('label').removeClass('disabled-label');
        $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').prop('disabled', false);

        //disable the tooltip first
        setTimeout( function(){
            try{        
                if($('.thickbox-loading.wtai-tb-window-modal-generate .bulk-tooltip-generate-filter').hasClass('tooltipstered')) {
                    $('.thickbox-loading.wtai-tb-window-modal-generate .bulk-tooltip-generate-filter').each(function(){
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
                        if( $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').length ){
                            $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    
                                    $(this).closest('label').addClass('disabled-label');

                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 300 );
                                }
                            });
                        }
                    }
                    else if( combType == 'style' ){
                        if( $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').length ){
                            $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    
                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 300 );
                                }
                            });
                        }
                    }
                    else if( combType == 'audience' ){
                        if( $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').length ){
                            $('.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container .wtai-product-tones-wrap .wtai-product-audiences-wrap input[type="checkbox"]').each(function(){
                                if( $(this).val() == combID ){
                                    $(this).prop('checked', false);
                                    $(this).prop('disabled', true);
                                    $(this).closest('label').addClass('disabled-label');
                                    
                                    var $inputcb = $(this);
                                    setTimeout( function(){
                                        $inputcb.closest('label').tooltipster('enable');
                                    }, 300 );
                                }
                            });
                        }
                    }
                });
            }
        });
    }

    $(document).on('click', '.thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="checkbox"], .thickbox-loading.wtai-tb-window-modal-generate .wtai-product-tonestyles-container input[type="radio"]', function(){
        set_disallowed_combinations_bulk();
    });

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

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_highlight_pronouns_check',
                value: value,
                wtai_nonce: wtai_nonce,
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

    $(document).on('click', '#wtai-woocommerce-product-attributes.postbox.wtai-metabox .wtai-attr-checkboxes', function(){
        var product_id = 0;
        if( $('#wtai-edit-post-id').length ){
            product_id = $('#wtai-edit-post-id').attr('value');
        }

        if( product_id == 0 ){
            return;
        }

        var product_attributes = [];
        $('#wtai-woocommerce-product-attributes.postbox.wtai-metabox .wtai-attr-checkboxes').each(function(){
            if( $(this).is(':checked') ){
                product_attributes.push( $(this).attr('data-apiname') );
            }
        });

        var wtai_nonce = get_product_edit_nonce();

        //mayb record selected types
        var data = {
            action        : 'wtai_record_single_product_attribute_preference',
            product_attributes : product_attributes.join(','),
            product_id : product_id,
            wtai_nonce : wtai_nonce
        };

        recordSingleProductAttr = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: data, 
            success: function() {
                recordSingleProductAttr = null;
            }
        });
    });

    function reset_bulk_options( default_style = '', default_tones = '', default_audiences = '', default_product_attributes = '', default_desc_min = 0, default_desc_max = 0, default_excerpt_min = 0, default_excerpt_max = 0 ){        
        //product attributes
        if ( $('#wtai-bulk-generate-modal').find('.wtai-bulk-prod-attribute-wrapper').length > 0 ){
            $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-attr-item .wtai-product-attr-cb').prop('checked', false);
            $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-cb-all').prop('checked', false);

            if( default_product_attributes ){
                var default_product_attributes_arr = default_product_attributes.split(',');

                $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-attr-item .wtai-product-attr-cb').each(function(){
                    var cb_value = $(this).val();
                    var cb = $(this);
                    $.each(default_product_attributes_arr, function( index, product_attr ){
                        if( product_attr == cb_value ){
                            cb.prop('checked', true);
                        }
                    });
                });

                if( $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-attr-item .wtai-product-attr-cb').length == $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-attr-item .wtai-product-attr-cb:checked').length ){
                    $('#wtai-bulk-generate-modal .wtai-bulk-prod-attribute-wrapper .wtai-product-cb-all').prop('checked', true);
                }
            }
        }

        //tones
        if ( $('#wtai-bulk-generate-modal').find('.wtai-product-tones-wrap').length > 0 ){
            $('#wtai-bulk-generate-modal').find('.wtai-product-tones-wrap').find('.wtai-product-tones-cb').prop('checked', false);
            $('#wtai-bulk-generate-modal').find('.wtai-product-tones-wrap').find('.wtai-product-cb-all').prop('checked', false);

            if( default_tones ){
                var default_tones_arr = default_tones.split(',');

                $('#wtai-bulk-generate-modal .wtai-product-tones-wrap .wtai-product-tones-cb').each(function(){
                    var cb_value = $(this).val();
                    var cb = $(this);
                    $.each(default_tones_arr, function( index, tone ){
                        if( tone == cb_value ){
                            cb.prop('checked', true);
                        }
                    });
                });

                if( $('#wtai-bulk-generate-modal .wtai-product-tones-wrap .wtai-product-tones-cb').length == $('#wtai-bulk-generate-modal .wtai-product-tones-wrap .wtai-product-tones-cb:checked').length ){
                    $('#wtai-bulk-generate-modal').find('.wtai-product-tones-wrap').find('.wtai-product-cb-all').prop('checked', true);
                }
            }
        }

        //audiences
        if ( $('#wtai-bulk-generate-modal').find('.wtai-product-audiences-wrap').length > 0 ){
            $('#wtai-bulk-generate-modal').find('.wtai-product-audiences-wrap').find('.wtai-product-audiences-cb').prop('checked', false);
            $('#wtai-bulk-generate-modal').find('.wtai-product-audiences-wrap').find('.wtai-product-cb-all').prop('checked', false);

            if( default_audiences ){
                var default_audiences_arr = default_audiences.split(',');

                $('#wtai-bulk-generate-modal .wtai-product-audiences-wrap .wtai-product-audiences-cb').each(function(){
                    var cb_value = $(this).val();
                    var cb = $(this);
                    $.each(default_audiences_arr, function( index, audience ){
                        if( audience == cb_value ){
                            cb.prop('checked', true);
                        }
                    });
                });

                if( $('#wtai-bulk-generate-modal .wtai-product-audiences-wrap .wtai-product-audiences-cb').length == $('#wtai-bulk-generate-modal .wtai-product-audiences-wrap .wtai-product-audiences-cb:checked').length ){
                    $('#wtai-bulk-generate-modal').find('.wtai-product-audiences-wrap').find('.wtai-product-cb-all').prop('checked', true);
                }
            }
        }

        //styles
        if ( $('#wtai-bulk-generate-modal').find('.wtai-product-styles-wrap').length > 0 ){
            $('#wtai-bulk-generate-modal').find('.wtai-product-styles-wrap').find('.wtai-product-styles-cb').prop('checked', false);

            if( default_style ){
                var default_style_arr = default_style.split(',');

                $('#wtai-bulk-generate-modal .wtai-product-styles-wrap .wtai-product-styles-cb').each(function(){
                    var cb_value = $(this).val();
                    var cb = $(this);
                    $.each(default_style_arr, function( index, style ){
                        if( style == cb_value ){
                            cb.prop('checked', true);
                        }
                    });
                });
            }
        }

        //reset default lengths for excerpt and description
        if( $('#wtai-bulk-generate-modal').find('#wtai-product-description-length-min').length ){
            $('#wtai-bulk-generate-modal').find('#wtai-product-description-length-min').val( default_desc_min ).trigger('change');
        }
        if( $('#wtai-bulk-generate-modal').find('#wtai-product-description-length-max').length ){
            $('#wtai-bulk-generate-modal').find('#wtai-product-description-length-max').val( default_desc_max ).trigger('change');
        }
        if( $('#wtai-bulk-generate-modal').find('#wtai-product-excerpt-length-min').length ){
            $('#wtai-bulk-generate-modal').find('#wtai-product-excerpt-length-min').val( default_excerpt_min ).trigger('change');
        }
        if( $('#wtai-bulk-generate-modal').find('#wtai-product-excerpt-length-max').length ){
            $('#wtai-bulk-generate-modal').find('#wtai-product-excerpt-length-max').val( default_excerpt_max ).trigger('change');
        }

        if( $('#wtai-bulk-generate-modal').find('.wtai-bulk-custom-style-ref-prod').length ){
            $('#wtai-bulk-generate-modal').find('.wtai-bulk-custom-style-ref-prod').prop('checked', false);

            $('.wtai-reference-product-wrapper').find('.wtai-bulk-custom-style-ref-product-select').addClass('disabled');
            $('#wtai-bulk-generate-modal').find('.wtai-product-tonestyles-container').find('label').removeClass('disabled');
            $('#wtai-bulk-generate-modal').find('.wtai-product-tonestyles-container').find('input').removeClass('disabled');
            $('#wtai-bulk-generate-modal').find('.wtai-product-tonestyles-container').find('input').removeAttr('disabled');

            $('select.wtai-bulk-custom-style-ref-product-select')[0].selectize.clear();
        }

        // Reset special instructions
        if ( $('#wtai-bulk-generate-modal').find('#wtai-bulk-other-details').length > 0 ){
            $('#wtai-bulk-generate-modal').find('#wtai-bulk-other-details').val('').trigger('change');
        }

        // Reset include ranked keywords
        if ( $('#wtai-bulk-generate-modal').find('#wtai-use-ranking-keywords').length > 0 ){
            $('#wtai-bulk-generate-modal').find('#wtai-use-ranking-keywords').prop('checked', false);
        }

        setTimeout( function(){
            try{
                if ($('.wtai-product-tonestyles-container-bulk').hasClass('tooltipstered')) {
                    $('.wtai-product-tonestyles-container-bulk').each(function(){
                        $(this).tooltipster('disable');
                    });
                }
            }
            catch(err) {
            }
        }, 300 );
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

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_user_hide_guidelines',
                value: is_checked,
                wtai_nonce: wtai_nonce,
            },
            success: function() {
            }
        });
    });

    $(document).on('click', '.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-btn', function(){
        if( $(this).hasClass('wtai-single-transfer-alt-btn') ){
            return;
        }

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
                    source_val =  wp.editor.getContent(id); // Visual tab is active
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
        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_store_bulk_text',
                browsertime : offset,
                product_id  :  $('#wtai-edit-post-id').attr('value'),
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
                                        case 'product_description':
                                        case 'product_excerpt':
                                            
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
                                    if( post_field_type == 'product_description' || post_field_type == 'product_excerpt' ){
                                        generatedText = htmlContent;
                                    }
                                    else{
                                        //generatedText = htmlContent.replace(/\n/g, '<br>');
                                        generatedText = htmlContent;
                                        generatedText = wtaiRemoveLastBr( generatedText );
                                    }

                                    var generatedCharCount = 0;
                                    var generatedCharCountCredit = 0;
                                    if( post_field_type == 'product_description' || post_field_type == 'product_excerpt' ){
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

                                rewrite_toggle_credit_behavior();
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

    $(document).on('click', '.wtai-single-transfer-btn-wrapper button.wtai-single-transfer-alt-btn', function(){
        popupGenerateCompleted('hide');
        $('#wtai-restore-global-setting-completed').hide();

        if( $(this).hasClass('wtai-disabled-button') ){
            return;
        }

        if( $('#message-alt-error.error').length ) {
            $('#message-alt-error.error').remove();
        }        

        $('.wtai-ai-logo').addClass('wtai-hide');
        $('.wtai-global-loader').addClass('wtai-is-active');

        $('.wtai-slide-right-text-wrapper .wtai-close').addClass('disabled');
        $('.wtai-slide-right-text-wrapper .wtai-button-prev').addClass('disabled-nav');
        $('.wtai-slide-right-text-wrapper .wtai-button-next').addClass('disabled-nav');

        var data_object = $(this).closest('.wtai-image-alt-metabox');
        var attachment_id = $(this).attr('data-image-id');
        var product_id = $(this).attr('data-product-id');
        var text_id = $(this).attr('data-id');
        var alt_text = data_object.find('.wtai-wp-editor-setup-alt').val();

        if( alt_text != '' ){
            var values = [];
            var value = {
                attachment_id: attachment_id,
                text_id: text_id,
                alt_text: alt_text,
            }
            values.push(value);
            
            var api_publish = 1;
            var submittype = 'transfer';

            handle_alt_text_save_and_transfer( product_id, values, api_publish, submittype );
        }
    });

    function handle_alt_text_save_and_transfer( product_id, values, api_publish, submittype ){
        var date = new Date();
        var offset = date.getTimezoneOffset();

        $('#postbox-container-2 .wtai-alt-writetext-metabox').addClass('wtai-disabled-click');
        
        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_transfer_image_alt_text',
                browsertime : offset,
                product_id  :  product_id,
                data_values: values,
                publish: api_publish,
                submittype: submittype,
                wtai_nonce: wtai_nonce,
            },
            success: function( data ){
                $('#postbox-container-2 .wtai-alt-writetext-metabox').removeClass('wtai-disabled-click');

                if( data.access ){
                    if ( data.message ) {
                        if ( data.message == 'expire_token' ){
                            if ( $('.wtai-edit-product-line' ).find('#message-alt-error').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message-alt-error').remove();
                            }
                            $('<div id="message-alt-error" class="error notice is-dismissible"><p>'+WTAI_OBJ.expire_token+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        } else {
                            if ( $('.wtai-edit-product-line' ).find('#message-alt-error').length > 0  ){
                                $('.wtai-edit-product-line' ).find('#message-alt-error').remove();
                            }
                            $('<div id="message-alt-error" class="error notice is-dismissible"><p>'+data.message+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                        }

                        $('.wtai-slide-right-text-wrapper').animate({ scrollTop: 0 }, 'fast');
                    } else {
                        if ( data.results ){
                            if( submittype == 'transfer' ){
                                $.each(values, function( value_index, alt_data ) {
                                    var attachment_id = alt_data.attachment_id;
                                    var text_id = alt_data.text_id;
                                    var generated_text = alt_data.alt_text;

                                    var data_object = $('.wtai-image-alt-metabox-' + attachment_id);

                                    // Transfer to WP platform
                                    var generatedCharCount = 0;
                                    var generatedCharCountCredit = 0;
                                    
                                    generatedCharCount = data_object.find('.wtai-generate-value-wrapper .wtai-char-counting .wtai-char-count').attr('data-count');
                                    generatedCharCountCredit = generatedCharCount;
                                    
                                    var generatedWordCount = data_object.find('.wtai-generate-value-wrapper .wtai-char-counting .word-count').attr('data-count');

                                    data_object.find('.wtai-current-value-wrapper').find('.wtai-current-text').find('p').html( generated_text );
                                    data_object.find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.wtai-char-count').html( generatedCharCount );
                                    data_object.find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.wtai-char-count').attr( 'char-count-credit', generatedCharCountCredit );
                                    data_object.find('.wtai-current-value-wrapper').find('.wtai-static-count-display').find('.word-count').html( generatedWordCount );
                                    
                                    data_object.find('.wtai-alt-transferred-status-label').hide();
                                    data_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                    data_object.find('.wtai-single-transfer-btn').removeClass('wtai-has-data-to-transfer');

                                    var image_elem_id = data_object.find('.wtai-wp-editor-setup-alt').attr('id');
                                    updateHiddentextTexarea( image_elem_id );
                                });
                                
                                updateToolTipForTransferSingleButton( 1 );
                            }
                            else{
                                $.each(values, function( value_index, alt_data ) {
                                    var attachment_id = alt_data.attachment_id;
                                    var data_object = $('.wtai-image-alt-metabox-' + attachment_id);
                                    var image_elem_id = data_object.find('.wtai-wp-editor-setup-alt').attr('id');

                                    updateHiddentextTexarea( image_elem_id );
                                });
                            }

                            bulk_transfer_button_behavior();
                            handle_save_button_state();
                            after_transfer_review_state();
                        }
                    }
                } else {
                    var message = '<p>'+WTAI_OBJ.access_denied+'</p>';    
                    var class_name = 'error notice ';
                    if ( message ){
                        $('<div id="message-alt-error" class="'+class_name+' is-dismissible">'+message+'</div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                    }
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

        // Count alt text image if transferred.
        $('#postbox-container-2').find('.wtai-image-alt-metabox').each( function(){
            var data_object = $(this);
            if( data_object.find('.wtai-api-data-image_alt_text_id').val() != '' && data_object.find('.wtai-alt-transferred-status-label').is(':visible') == false ){
                transfer_ctr++;
            }

            field_ctr++;
        }); 

        //console.log( 'transfer counter ' + transfer_ctr );
        //console.log( 'field counter ' + field_ctr );

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
            
            //grid transfer
            if( $('.wp-list-table .wtai-cwe-action-button.wtai-cwe-action-button-transfer').length > 0 ){
                $('.wp-list-table .wtai-cwe-action-button.wtai-cwe-action-button-transfer').removeClass('wtai-hidden-transfer-link');
            }
            else{
                $('.wp-list-table tr td.has-row-actions .row-actions').append('<span class="transfer"> | <a href="#" class="wtai-cwe-action-button wtai-cwe-action-button-transfer" data-action="transfer">' + WTAI_OBJ.transfer_btn_label + '</a></span>');
            }
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

    function get_tone_style_count(){
        var tone_count = $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-tones-wrap input[type="checkbox"]:checked').length;
        var style_count = $('.wtai-wp-filter .wtai-product-tonestyles-container .wtai-product-styles-wrap input[type="radio"]:checked').length;

        var total_count = tone_count + style_count;

        $('.wtai-tone-and-style-form-wrapper .wtai-tone-and-styles-select').find('.wtai-button-label').find('.wtai-button-num').text(total_count);
    }

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
            $('.wtai-keyword-analysis-options-wrap .wtai-semantic-keyword.active .wtai-per').removeClass('wtai-per-force-hide');
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result .wtai-per').removeClass('wtai-per-force-hide');
        }
        else{
            //handle density
            $('.wtai-keyword-analysis-options-wrap .wtai-semantic-keyword.active .wtai-per').addClass('wtai-per-force-hide');
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result .wtai-per').addClass('wtai-per-force-hide');
        }
    }

    //20240119
    $('.wtai-rewrite-checking-label').on('click',function(){
        $(this).toggleClass('hover');
    });

    $(document).on('click', '.wtai-alt-image', function(){
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

    $(document).on('click', '.wtai-submit-extension-review-btn', function(e){
        e.preventDefault();

        var btn = $(this);
        var field_type = btn.attr('data-type');
        var product_id = btn.attr('data-product-id');

        var review_ids = [];
        $(this).closest('.wtai-status-popup-info-main').find('.wtai-ext-review-id').each(function(){
            review_ids.push( $(this).val() );
        });

        var wtai_nonce = get_product_edit_nonce();

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_tag_extension_review_as_done',
                field_type: field_type,  
                product_id: product_id,  
                review_ids: review_ids.join(','),
                wtai_nonce: wtai_nonce
            },
            success: function(data) {
                btn.closest('.wtai-status-postheader').find('.wtai-rewrite-checking-label').removeClass('hover');
                btn.closest('.wtai-status-postheader').find('.wtai-rewrite-checking-label').addClass('hidden');
            }
        });
    });

    //Keyword filter
    $('.wtai-sort-ideas-select-volume').on('click', function(){
        var checkedValue = $('input[name=volume_sort]:checked').val();
        $('.wtai-sort-ideas-select-difficulty').removeClass('high');
        $('.wtai-sort-ideas-select-difficulty').removeClass('low');
        if( checkedValue == 'desc' ) {
            $('.wtai-volume-difficulty-dropdown').find('.volume-sort-asc').trigger('click');
            $(this).removeClass('desc');
            $(this).addClass('asc');
        } else {
            $('.wtai-volume-difficulty-dropdown').find('.volume-sort-desc').trigger('click');
            $(this).removeClass('asc');
            $(this).addClass('desc');
        }
    });
    $('.wtai-sort-ideas-select-difficulty').on('click', function(){
        var checkedValue = $('input[name=difficulty_sort]:checked').val();
        $('.wtai-sort-ideas-select-volume').removeClass('desc');
        $('.wtai-sort-ideas-select-volume').removeClass('asc');
        if( checkedValue == 'low' ) {
            $('.wtai-volume-difficulty-dropdown').find('.difficulty-sort-high').trigger('click');
            $(this).removeClass('low');
            $(this).addClass('high');
        } else {
            $('.wtai-volume-difficulty-dropdown').find('.difficulty-sort-low').trigger('click');
            $(this).removeClass('high');
            $(this).addClass('low');
        }
    });

    initializeGeneralTooltipBulk();
    function initializeGeneralTooltipBulk(){
        try{
            $('.wtai-product-tonestyles-container-bulk').each(function(){
                $(this).tooltipster({
                    'theme': 'tooltipform-default',
                    'position': "top",
                    'arrow': true,
                    debug: false,
                    contentAsHTML: true,
                    trigger: 'custom',
                    triggerOpen: {
                        mouseenter: true,
                        click: true,
                        touchstart: true,
                        tap: true,
                    },
                    triggerClose: {
                        mouseleave: true,
                        tap: true,
                        touchleave: true,
                        scroll: true
                    }
                });

                $(this).hover(function(){
                    $(this).attr("tooltip-data", $(this).attr("title"));
                    $(this).removeAttr("title");
                }, function(){
                    $(this).attr("title", $(this).attr("tooltip-data"));
                    $(this).removeAttr("tooltip-data");
                });

                //disable this by default
                $(this).tooltipster('disable');
            });
        }
        catch( err ){

        }
    }

    $(document).on('click', '.wtai-cta-radio-option-label', function( e ){
        if( $(this).hasClass('disabled') ){
            e.preventDefault();
            // Stop the event from propagating to the associated input
            e.stopPropagation();
        }
    });

    function handle_generate_button_state(){
        var checkedFields = $('#postbox-container-2 .wtai-metabox .postbox-header .wtai-checkboxes:checked').length;
        var checkedAltImages = $('.wtai-image-alt-metabox .wtai-checkboxes-alt:checked').length;

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

    function handle_generate_and_select_all_state( is_premium ){
        var totalCBTypes = $('#postbox-container-2 .wtai-metabox .postbox-header .wtai-checkboxes').length;
        if( $('.wtai-image-alt-metabox .wtai-checkboxes-alt').length > 0 && $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('disabled') == false && is_premium == '1' ){
            totalCBTypes++;
        }

        if( $('.wtai-image-alt-metabox .wtai-checkboxes-alt').length <= 0 || 
            $('.wtai-image-alt-metabox .wtai-checkboxes-alt:checked').length < $('.wtai-image-alt-metabox .wtai-checkboxes-alt').length ){
            $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('checked', false);
        }
        else{
            $('.wtai-alt-writetext-metabox .postbox-header .wtai-checkboxes').prop('checked', true);
        }

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

    $(document).on('keyup', '.wtai-image-alt-metabox .wtai-wp-editor-setup-alt', function(){
        var parent = $(this).closest('.wtai-image-alt-metabox');
        var image_id = parent.attr('data-id');
        var image_elem_id = $(this).attr('id');
        var alt_text = $(this).val();

        $('#'+image_elem_id).closest('.wtai-image-alt-metabox').find('.wtai-data-new-text').remove();
        $('#'+image_elem_id).closest('.wtai-image-alt-metabox').find('.wtai-hidden-text').append('<div class="wtai-data-new-text" style="display:none">'+alt_text+'|</div>');
        
        typeCountMessageAltImage( image_id, alt_text );

        handle_save_button_state();
        handle_single_transfer_button_state();
        bulk_transfer_button_behavior();
    });

    $(document).on('change', '.wtai-bulk-prod-attribute-wrapper .wtai-product-attr-cb', function( e ){
        maybe_display_featured_image_tooltip( false );
    });

    function  maybe_display_featured_image_tooltip( show ){
        var is_featured_image_checked = false;
        $('.wtai-bulk-prod-attribute-wrapper .wtai-product-attr-cb:checked').each(function(){
            if( $(this).val() == 'wtai-featured-product-image' ){
                is_featured_image_checked = true;
            }
        });

        if( is_featured_image_checked ){
            $('.wtai-featured-product-image-label').tooltipster('enable');
        }
        else{
            $('.wtai-featured-product-image-label').tooltipster('disable');
        }

        if( show ){
            $('.wtai-featured-product-image-label').tooltipster('show');
        }
        else{
            $('.wtai-featured-product-image-label').tooltipster('hide');
        }
    }

    function disable_alt_images_for_reference_and_rewrite(){
        var is_disabled = false;
        if( $('#wtai-custom-style-ref-prod').is(':checked') ){
            is_disabled = true;
        }

        var generationType = $('input[name="wtai_cta_generate_type"]:checked').val();
        if( generationType == 'rewrite' ){
            is_disabled = true;
        }

        if( is_disabled ){
            $('.wtai-image-alt-metabox .wtai-checkboxes-alt').prop('disabled', true);
            $('.wtai-alt-writetext-metabox .wtai-checkboxes').prop('disabled', true);
        }
        else{
            $('.wtai-image-alt-metabox .wtai-checkboxes-alt').prop('disabled', false);
            $('.wtai-alt-writetext-metabox .wtai-checkboxes').prop('disabled', false);
        }
    }

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

        getDataPerProductBlockInit( product_id, 0 );

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

    $('.wtai-action-bulk-image-process-bulk-cancel, .wtai-action-bulk-image-process-bulk-ok-cancel').on('click', function( e ){
        e.preventDefault();

        reset_image_alt_local_data();
        reset_image_bulk_alt_local_data();

        maybeReenablePendingBulkIds([]);
        
        $('#wtai-preprocess-image-loader').hide();
        //added 2024.03.05
        $('#wpcontent').removeClass('preprocess-image');
        
        $('#TB_closeWindowButton').click();
        $('#wtai-confirmation-proceed-image-bulk-loader').hide();
    });

    $('.wtai-action-bulk-image-process-bulk').on('click', function( e ){
        e.preventDefault();        

        var button = $('#wtai-generate-bulk-btn');

        $('#wtai-confirmation-proceed-image-bulk-loader').hide();

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        button.prop('disabled', false);
        button.addClass('wtai-pre-process-image-done');
        button.removeClass('disabled');
        button.trigger('click'); //lets retrigger the click since all is well
    });

    // Update class on window resize
    //commented by mcr 2024.03.04
    $(window).resize(function () {
        //adjust_current_text_max_width();
        //adjust_current_text_max_width_altimage();
    });

    function adjust_current_text_max_width(){
        if( $('#postbox-container-2 .postbox.wtai-metabox .wtai-col-row-wrapper .wtai-current-value-wrapper').length > 0 ){        
            var lowest_width = 99999999;
            $('#postbox-container-2 .postbox.wtai-metabox .wtai-col-row-wrapper .wtai-current-value-wrapper').css('max-width' , '100%');
            $('#postbox-container-2 .postbox.wtai-metabox .wtai-col-row-wrapper .wtai-current-value-wrapper').each(function(){
                var width = $(this).width();

                if( width < lowest_width ){
                    lowest_width = width;
                }
            });

            if( lowest_width > 0 ){
                $('#postbox-container-2 .postbox.wtai-metabox .wtai-col-row-wrapper .wtai-current-value-wrapper').css('max-width' , lowest_width + 'px');

            }
        }

        if( $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').length > 0 ){
            var lowest_width = 99999999;
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').css('max-width' , '100%');
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').each(function(){
                var width = $(this).width();

                if( width < lowest_width ){
                    lowest_width = width;
                }
            });

            if( lowest_width > 0 ){
                $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').css('max-width' , lowest_width + 'px');

            }

            //adjust header for alt text so it will be aligned always
            var screen_width = $(window).width();
            
            // Make with of both textarea the same
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').find('.wtai-generate-textarea-wrap').css('width', 'auto');
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').find('.wtai-current-text').css('width', 'auto');

            if( screen_width > 1300 ){
                $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').each(function(){
                var generate_width = $(this).find('.wtai-generate-textarea-wrap').width();
                var current_width = $(this).find('.wtai-current-text').width();
                var ave_width = ( parseFloat( generate_width ) + parseFloat( current_width ) ) / 2;

                $(this).find('.wtai-generate-textarea-wrap').css('width', ave_width + 'px');
                $(this).find('.wtai-current-text').css('width', ave_width + 'px');
                });
            }
        }
    }

    function adjust_current_text_max_width_altimage(){
  
        if( $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').length > 0 ){
            var lowest_width = 99999999;
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').css('max-width' , '100%');
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').each(function(){
                var width = $(this).width();

                if( width < lowest_width ){
                    lowest_width = width;
                }
            });

            if( lowest_width > 0 ){
                $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox .wtai-current-value-wrapper').css('max-width' , lowest_width + 'px');

                //console.log( 'lowest width alt text: ' + lowest_width );
            }

            //adjust header for alt text so it will be aligned always
            var screen_width = $(window).width();
            
            // Make with of both textarea the same
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').find('.wtai-generate-textarea-wrap').css('width', 'auto');
            $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').find('.wtai-current-text').css('width', 'auto');

            if( screen_width > 1300 ){
                $('#postbox-container-2 .postbox .wtai-col-row-wrapper.wtai-image-alt-metabox').each(function(){
                var generate_width = $(this).find('.wtai-generate-textarea-wrap').width();
                var current_width = $(this).find('.wtai-current-text').width();
                var ave_width = ( parseFloat( generate_width ) + parseFloat( current_width ) ) / 2;

                $(this).find('.wtai-generate-textarea-wrap').css('width', ave_width + 'px');
                $(this).find('.wtai-current-text').css('width', ave_width + 'px');
                });
            }
        }
    }


    // Batch image upload
    function process_image_upload_single( product_id, altimages, includeFeaturedImage ){
        var wtai_nonce = get_product_edit_nonce();

        var date = new Date();
        var offset = date.getTimezoneOffset(); 
        var button = $('.wtai-page-generate-all');

        if( window.currentAltImageBatch == 0 ){        
            $('.wtai-image-alt-metabox').removeClass('wtai-error-upload');
            $('#postbox-container-2 .wtai-checkboxes-alt').prop('disabled', true);
        }

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
                action: 'wtai_preprocess_images',
                product_id: product_id,
                browsertime: offset,
                altimages: altimages.join(','),
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
                    process_image_upload_single( product_id, nextAltimages, includeFeaturedImage );
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
                        if (confirmationProceedImageLoader.length > 0 && loaderEstimatedTime.length > 0) {
                            if (loaderEstimatedTime.is(':visible') ) {
                                if( loaderEstimatedTime.find('#wtai-preprocess-image-loader').length ) {
                                    loaderEstimatedTime.find('#wtai-preprocess-image-loader').remove();
                                }
                                confirmationProceedImageLoader.clone().appendTo(loaderEstimatedTime).show();
                            } else {
                                confirmationProceedImageLoader.show();
                            }
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

    // Batch image upload
    function process_image_upload_bulk( product_id, altimages, includeFeaturedImage ){
        var wtai_nonce = get_product_bulk_nonce();

        var date = new Date();
        var offset = date.getTimezoneOffset(); 

        // pre process images first
        var date        = new Date();
        var offset      = date.getTimezoneOffset(); 
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_preprocess_images',
                product_id: product_id,
                browsertime: offset,
                altimages: altimages.join(','),
                includeFeaturedImage: includeFeaturedImage,
                altImageIdsError: window.altImageIdsErrorBulk.join(','),
                wtai_nonce: wtai_nonce,
            },
            success: function(data) {
                // record errorin uploading
                if( data.error_process.length > 0){
                    $.each(data.error_process,
                        function( index, alt_image_id ) {
                            window.altImageIdsErrorBulk.push( alt_image_id );
                        }
                    );

                    $.each(data.error_process,
                        function( index, error_process_data ) {
                            window.altImageIdsErrorBulk.push( error_process_data.image_id );
                        }
                    );
                }

                if( data.success_ids.length > 0){
                    $.each(data.success_ids,
                        function( index, alt_image_id ) {
                            window.altImageSuccessForUploadBulk.push( alt_image_id );
                        }
                    );
                }

                //check if queue batches already finished
                if( window.currentAltImageBatchBulk < window.maxAltImageBatchNoBulk ){
                    var nextBatchNo = window.currentAltImageBatchBulk + 1;
                    var nextAltimages = window.altImageBatchForUploadBulk[ nextBatchNo ];

                    window.currentAltImageBatchBulk = nextBatchNo;
                    process_image_upload_bulk( product_id, nextAltimages, includeFeaturedImage );
                }     
                else{               
                    if( window.altImageIdsErrorBulk.length > 0){
                        $('.wtai-global-loader').removeClass('wtai-is-active');
                        $('.wtai-ai-logo').removeClass('wtai-hide');

                        $('#wtai-confirmation-proceed-image-bulk-loader .wtai-error-message-container').html( data.error_message );

                        if( window.altImageSuccessForUploadBulk.length <= 0 ){
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk').hide();
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk-cancel').hide();
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk-ok-cancel').show();

                        }
                        else{
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk').show();
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk-cancel').show();
                            $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container .wtai-action-bulk-image-process-bulk-ok-cancel').hide();
                        }

                        $('#wtai-confirmation-proceed-image-bulk-loader').show();
                    }
                    else{
                        $('#wtai-confirmation-proceed-image-bulk-loader .wtai-error-message-container').html( '' );
                        $('#wtai-confirmation-proceed-image-bulk-loader .wtai-loading-actions-container').show();
                        $('#wtai-confirmation-proceed-image-bulk-loader').hide();
                        
                        $('.wtai-global-loader').addClass('wtai-is-active');
                        $('.wtai-ai-logo').addClass('wtai-hide');

                        $('#wtai-generate-bulk-btn').prop('disabled', false);

                        $('#wtai-generate-bulk-btn').addClass('wtai-pre-process-image-done');
                        $('#wtai-generate-bulk-btn').removeClass('disabled');
                        $('#wtai-generate-bulk-btn').trigger('click'); //lets retrigger the click since all is well
                    }
                }
            }
        });
    }

    function fetchFreshImageAltTextFromAPI( recordId, from_generate, is_error = '0', refresh_credits = '0' ){
        var wtai_nonce = get_product_edit_nonce();

        // Process alt text concurrent with the text generation.
        var date       = new Date();
        var offset     = date.getTimezoneOffset();    
        var product_id = $('#wtai-edit-post-id').attr('value');   
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_OBJ.ajax_url,
            data: {
                action: 'wtai_get_alt_text',
                browsertime: offset,
                altimages: recordId,
                product_id: product_id,
                wtai_nonce: wtai_nonce,
                refresh_credits: refresh_credits,
            },
            success: function(data) {
                if( data.available_credit_label != '' ){
                    $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                }

                // todo: transfer this to completed message
                //$('.wtai-alt-writetext-metabox').attr( 'data-message', data.results.message_notice );

                $('.wtai-image-alt-metabox').each(function(){
                    var meta_object = $(this);
                    var image_id = meta_object.attr('data-id');

                    if( meta_object.find('.wtai-checkboxes-alt').is(':checked') ){
                        
                        var alt_data = null;

                        if( data.results[image_id] != null ){
                            alt_data = data.results[image_id];
                        }

                        if( meta_object.hasClass('wtai-error-upload') == false && alt_data != null && alt_data.altText != null ){
                            var alt_text = alt_data.altText.value;
                            var api_id = alt_data.altText.id;

                            var image_elem_id = meta_object.find('.wtai-wp-editor-setup-alt').attr('id');

                            meta_object.find('.wtai-wp-editor-setup-alt').prop('disabled', false);
                            meta_object.find('.wtai-checkboxes-alt').prop('disabled', false);
                            meta_object.find('.wtai-wp-editor-setup-alt').val('');
                            meta_object.find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                            meta_object.find('.wtai-single-transfer-btn').removeClass('wtai-disabled-button');
                            meta_object.find('.wtai-alt-transferred-status-label').removeClass('wtai-hide-not-transferred-label');
                            meta_object.find('.wtai-generated-status-label').html( WTAI_OBJ.generatedStatusText );
                            meta_object.find('#wtai-wp-field-input-image_alt_text_id').val( api_id );
                            meta_object.removeClass('wtai-loading-state');

                            if( is_error != '1' ){
                                meta_object.addClass('wtai-bulk-complete');
                            }
                            
                            if( from_generate == '1' ){
                                meta_object.find('.wtai-alt-transferred-status-label').show();
                            }

                            $('#'+image_elem_id).val(alt_text);

                            updateHiddentextTexarea( image_elem_id );
                            typeCountMessageAltImage( image_id, alt_text );
                        }
                        else{
                            if( from_generate != '1' ){
                                var alt_text = meta_object.find('.wtai-wp-editor-setup-alt').val();

                                var image_elem_id = meta_object.find('.wtai-wp-editor-setup-alt').attr('id');

                                meta_object.find('.wtai-wp-editor-setup-alt').prop('disabled', false);
                                meta_object.find('.wtai-checkboxes-alt').prop('disabled', false);
                                meta_object.find('.wtai-wp-editor-setup-alt').val('');
                                meta_object.find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                                meta_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                meta_object.find('.wtai-alt-transferred-status-label').addClass('wtai-hide-not-transferred-label');
                                meta_object.find('.wtai-generated-status-label').html( WTAI_OBJ.notGeneratedStatusText );
                                meta_object.find('.wtai-generate-disable-overlay-wrap').addClass( 'wtai-shown' );
                                meta_object.removeClass('wtai-loading-state');
                                meta_object.addClass('wtai-bulk-complete');

                                $('#'+image_elem_id).val(alt_text);

                                updateHiddentextTexarea( image_elem_id );
                                typeCountMessageAltImage( image_id, alt_text );
                            }
                        }
                    }
                    else{
                        if( from_generate != '1' ){
                            meta_object.addClass('wtai-bulk-complete');

                            var alt_data = null;

                            if( data.results[image_id] != null ){
                                alt_data = data.results[image_id];
                            }

                            if( meta_object.hasClass('wtai-error-upload') == false && alt_data != null ){
                                var alt_text = alt_data.altText.value;
                                var api_id = alt_data.altText.id;
    
                                var image_elem_id = meta_object.find('.wtai-wp-editor-setup-alt').attr('id');
        
                                meta_object.find('.wtai-wp-editor-setup-alt').prop('disabled', false);
                                meta_object.find('.wtai-checkboxes-alt').prop('disabled', false);
                                meta_object.find('.wtai-wp-editor-setup-alt').val('');
                                meta_object.find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                                meta_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                meta_object.find('.wtai-alt-transferred-status-label').addClass('wtai-hide-not-transferred-label');
                                meta_object.find('.wtai-generated-status-label').html( WTAI_OBJ.generatedStatusText );
                                meta_object.find('#wtai-wp-field-input-image_alt_text_id').val( api_id );
                                meta_object.removeClass('wtai-loading-state');
                                meta_object.addClass('wtai-bulk-complete');
    
                                $('#'+image_elem_id).val(alt_text);
    
                                updateHiddentextTexarea( image_elem_id );
                                typeCountMessageAltImage( image_id, alt_text );
                            }
                            else{
                                var alt_text = meta_object.find('.wtai-wp-editor-setup-alt').val();

                                var image_elem_id = meta_object.find('.wtai-wp-editor-setup-alt').attr('id');

                                meta_object.find('.wtai-wp-editor-setup-alt').prop('disabled', false);
                                meta_object.find('.wtai-checkboxes-alt').prop('disabled', false);
                                meta_object.find('.wtai-wp-editor-setup-alt').val('');
                                meta_object.find('.wtai-typing-cursor-alt-wrap').removeClass('wtai-shown');
                                meta_object.find('.wtai-single-transfer-btn').addClass('wtai-disabled-button');
                                meta_object.find('.wtai-alt-transferred-status-label').addClass('wtai-hide-not-transferred-label');
                                meta_object.find('.wtai-generated-status-label').html( WTAI_OBJ.notGeneratedStatusText );
                                meta_object.find('.wtai-generate-disable-overlay-wrap').addClass( 'wtai-shown' );
                                meta_object.removeClass('wtai-loading-state');
                                meta_object.addClass('wtai-bulk-complete');

                                $('#'+image_elem_id).val(alt_text);

                                updateHiddentextTexarea( image_elem_id );
                                typeCountMessageAltImage( image_id, alt_text );
                            }

                            handle_single_transfer_button_state();
                        }
                    }
                });   
                
                //lets re enable the list
                if( from_generate == '1' && recordId != '' ){
                    var recordIdArray = recordId.split(',');

                    setTimeout(function(){                        
                        $.each(recordIdArray, function(index, post_id ) {
                            $('#wtai-table-list-' + post_id).removeClass('wtai-processing');
                            $('#wtai-table-list-' + post_id).find('button.transfer_feature').removeClass('wtai-disabled-button');

                            $('.toplevel_page_write-text-ai')
                                .find('#wtai-table-list-'+post_id)
                                .find('.wtai-cwe-selected')
                                .prop('disabled', false );
                        });
                    }, 300);
                }
            }
        });
    }

    function reset_image_alt_local_data(){
        window.currentAltImageBatch = 0;
        window.maxAltImageBatchNo = 0;
        window.altImageForUpload = [];
        window.altImageSuccessForUpload = [];
        window.altImageBatchForUpload = [];
        window.altImageIdsError = [];
    }

    function reset_image_bulk_alt_local_data(){
        window.currentAltImageBatchBulk = 0;
        window.maxAltImageBatchNoBulk = 0;
        window.altImageForUploadBulk = [];
        window.altImageSuccessForUploadBulk = [];
        window.altImageBatchForUploadBulk = [];
        window.altImageIdsErrorBulk = [];
    }

    $(document).on('click', '.wtai-bulk-prod-attribute-wrapper .wtai-featured-product-image-label', function(){
        setTimeout(function() {
            maybe_display_featured_image_tooltip( true );
        }, 500);
    });

    function refresh_bulk_generate_credit_count(){
        if( $('.bulk-generate-action .wtai-credvalue').length ){
            var bulkGenerateCredit = getBulkGenerateCreditCount();
            var credLabel = WTAI_OBJ.creditLabelPlural;
            if( parseInt( bulkGenerateCredit ) == 1 ){
                credLabel = WTAI_OBJ.creditLabelSingular;
            }
            $('.bulk-generate-action .wtai-credvalue').text( bulkGenerateCredit );
            $('.bulk-generate-action .wtai-cred-label').text( credLabel );
        } 
    }

    $(window).bind('tb_unload', function () {
        maybe_display_featured_image_tooltip( false );
    });

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
    
    function handle_single_bulk_buttons( state ){
        if( state == 'disable' ){
            $('.submitbox .wtai-bulk-button-text').addClass('disabled-during-generation');
        }
        else{
            $('.submitbox .wtai-bulk-button-text').removeClass('disabled-during-generation');
        }
    }

    function get_product_edit_nonce(){
        var nonce = $('#wtai-edit-product-line-form').attr('data-product-nonce');
        return nonce;
    }

    function get_product_bulk_nonce(){
        var nonce = $('.wtai-list-table').attr('data-product-nonce');
        return nonce;
    }

    $(document).on('click', '#wtai-select-all-transfer', function(){
        setTimeout(function() {
            save_transfer_fields_selected();
        }, 300);
    });

    $(document).on('click', '.wtai-keyword-analysis-content-title', function( e ){
        if ( $(e.target).closest('.wtai-keyword-tooltip').length || $(e.target).hasClass('wtai-keyword-tooltip') ) {
            return;
        }

        if( $(this).closest('.wtai-keyword-analysis-content-wrap').find('.wtai-keyword-analysis-toggle').length ){
            $(this).closest('.wtai-keyword-analysis-content-wrap').find('.wtai-keyword-analysis-toggle').trigger('click');
        }
    });

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

    // Event after reset global settings
    $(document).on('wtai_reset_global_settings', function(e, data){
        e.stopImmediatePropagation();

        if ( $('#wtai-product-main-image').length ) {
            if( data.check_featured_image == '1' ){
                $('#wtai-product-main-image').prop('checked', true);
            } else {
                $('#wtai-product-main-image').prop('checked', false);
            }
        }

        reset_bulk_options( data.default_style, data.default_tones, data.default_audiences, data.default_product_attributes, data.default_desc_min, data.default_desc_max, data.default_excerpt_min, data.default_excerpt_max );
    });

    function set_disallowed_combinations_single(){
        $(document).trigger('wtai_set_disallowed_combinations_single');
    }

    function updateToolTipForReferenceProduct( tooltipMessage, isEnabled = 1, display = 'full' ){
        var args = {
            tooltipMessage : tooltipMessage,
            isEnabled : isEnabled,
            display : display,
        }

        $(document).trigger('wtai_update_tooltip_for_reference_product', args);
    }

    function display_reference_product_count(){
        $(document).trigger('wtai_display_reference_product_count');
    }

    function toggleRewriteDisabledTooltipState( tooltipMessage = '', showTooltip = 1 ){
        var args = {
            tooltipMessage : tooltipMessage,
            showTooltip : showTooltip,
        }

        $(document).trigger('wtai_toggle_rewrite_disabled_state', args);
    }

    function renderSuggestedAudience( response_data ){
        var args = {
            response_data : response_data,
        }

        $(document).trigger('wtai_render_suggested_audience', args);
    }

    function updateKeywordAnaysisViewCount( reset = false ){
        var args = {
            reset : reset,
        }

        $(document).trigger('wtai_update_keyword_analysis_views_count', args);
    }
});

