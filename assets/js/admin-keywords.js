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
    var semanticKeywordSelectAJAX = null;
    var keywordIdeasSortFilterAJAX = null;
    var keywordStartAnalysisAJAX = null;
    var addKeyWordAJAX = null;
    var keywordIdeasAJAX = null;

    // hide loader when all AJAX requests are completed
    $(document).on('ajaxComplete', function( event, xhr, settings ) {
        if( keywordIdeasAJAX == null && addKeyWordAJAX == null ){
            if( $('.wtai-keyword-analysis-button').hasClass('disabled') && WTAI_OBJ.current_user_can_generate != '0' ){
                $('.wtai-keyword-analysis-button').removeClass('disabled');
            }
        }
    });

    // Old implementation.
    window.KeywordIdeas = function(element, event){
        event.preventDefault();

        if( $(element).hasClass('disabled') ){
            return;
        }

        $(element).addClass('disabled');

        $('.keyword-ideas-show-more-btn').attr('data-page-no', '1');
        $('.keyword-ideas-show-more-wrap').hide();

        if( $('.wtai-keyword .wtai-error-msg').length ) {
            $('.wtai-keyword .wtai-error-msg').remove();
        }

        $('.wtai-keyword-analysis-button').addClass('disabled');

        getKeyWordIdeas('yes', '');
    };

    // Moved to admin-keyword.js.
    window.wtaiGetKeywordPopin = function() {
        if ( ! $('.wtai-slide-right-text-wrapper').find('.wtai-keyword-analysis-button').hasClass('wtai-pending') ){
            popupGenerateCompleted('hide');

            $('.wtai-slide-right-text-wrapper').find('.wtai-keyword-analysis-button').addClass('wtai-pending');
            if ( ! $('.wtai-slide-right-text-wrapper').hasClass('wtai-keyword-open') ){
                $('.wtai-keyword-analysis-content-bottom-section').animate({ scrollTop: 0 }, 'fast');

                $('.wtai-slide-right-text-wrapper').addClass('wtai-keyword-open');
                $('body').addClass('wtai-keyword-open');   
                
                var keywordAnalysisOpenCount = $('.wtai-keyword-analysis-view').val();
                if( parseInt( keywordAnalysisOpenCount ) == 0 ){
                    getKeyWordIdeas('no', 'yes', 'no', 'no', 'no', 'yes');
                }                
            } else {
                $('.wtai-slide-right-text-wrapper').removeClass('wtai-keyword-open');
                $('body').removeClass('wtai-keyword-open'); 

                maybeDisableGetDataButton();
            }

            $('.wtai-slide-right-text-wrapper').find('.wtai-keyword-analysis-button').removeClass('wtai-pending');

            maybeDisableKeywordInput();
            updateKeywordCount();

            updateKeywordAnaysisViewCount( false );
        }
    };

    function popupGenerateCompleted( status, errorData ){
        var args = {
            status : status,
            errorData : errorData,
        }

        $(document).trigger('wtai_popup_generate_completed', args);
    }

    function getKeyWordIdeas( refresh, nogenerate, append = 'no', from_analysis_stream = 'no', from_analysis_error = 'no', initial_load = 'no' ){
        if (  ! $('.wtai-keyword-filter-wrapper').find('.button-primary').hasClass('disabled') ) {
            if( from_analysis_stream == 'yes' ){
                if( window.keywordIdeasSource != 'refresh' ){
                    var current_progress = parseInt( $('.wtai-keyword-analysis-progress-loader').attr('data-progress') );
                    current_progress = current_progress + 1;
                    $('.wtai-keyword-analysis-progress-loader').attr('data-progress', current_progress)
                    
                    progressbar_keyword_analysis( 'show', current_progress, WTAI_KEYWORD_OBJ.finalKeywordAnalysisMessage, true );
                }
                else{
                    var refresh_type = window.keywordIdeasSourceType;
                    var current_progress = 0;
                    if( refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
                        current_progress = parseInt( $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis .wtai-keyword-analysis-progress-loader-mini').attr('data-progress') );
                    }
                    else{
                        current_progress = parseInt( $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-progress-loader-mini').attr('data-progress') );
                    }
                    
                    current_progress = current_progress + 1;
                    $('.wtai-keyword-analysis-progress-loader-mini').attr('data-progress', current_progress)

                    progressbar_keyword_analysis_mini( 'show', current_progress, WTAI_KEYWORD_OBJ.finalRefreshKeywordAnalysisMessage, true, refresh_type );
                }
            }
            else{
                $('.wtai-global-loader').addClass('wtai-is-active');
                $('.wtai-ai-logo').addClass('wtai-hide');
            }
            
            $('.wtai-target-wtai-keywords-list-wrapper').addClass('disabled');
            $('.wtai-keyword-input').prop('disabled', true );

            // initialize keywords section
            if( from_analysis_stream != 'yes' ){
                $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-loader').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-empty-label').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').html('');
                $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').addClass('hidden');
            }

            var input_value = $('.wtai-keyword').find('.wtai-keyword-input').val();
            
            if ( input_value ) {
                updateKeywordSinglePageDrawer ( input_value );
                updateKeywordSinglePageLeftCol();
                updateKeywordSinglePage ( input_value , 'add', '' );

                $('.wtai-keyword').find('.wtai-keyword-input').val('');
                $('.wtai-keyword-input-filter-wrap .wtai-char-count').text('0');
            }

            var sa_keywords = [];
            var k = 0;
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
                var keyword = $(this).find('.wtai-keyword-name').text();
                if( keyword ){
                    sa_keywords[k] = keyword;
                    k++;
                }
            });

            var manual_keywords = [];
            var k = 0;
            $('.wtai-keyword-table-your-keywords tbody tr').each(function(){
                var keyword = $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();
                if( keyword ){
                    manual_keywords[k] = keyword;
                    k++;
                }
            });

            if( keywordIdeasAJAX != null ){
                keywordIdeasAJAX.abort();
            }

            var language_code = $('.wtai-keyword-location-code').val();

            var wtai_nonce = get_wp_nonce();

            var data = {
                action: 'wtai_keyword_ideas',
                record_id: $('#wtai-edit-post-id').attr('value'), 
                record_type: $('#wtai-record-type').val(), 
                language_code: language_code, 
                keywords: sa_keywords.join('|'), 
                manual_keywords: manual_keywords.join('|'), 
                refresh: refresh, 
                nogenerate: nogenerate, 
                volumeFilter: '', 
                volumeSort: '',
                difficultyFilter: '', 
                difficultySort: '',
                keywordsSort: '',
                sorting: '',
                pageNo: 1,
                wtai_nonce: wtai_nonce
            };

            $('.wtai-keyword-ideas-table').removeClass('wtai-has-data');
            $('.your-keyword-table tbody tr').removeClass('wtai-has-data');

            keywordIdeasAJAX = $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_KEYWORD_OBJ.ajax_url,
                data: data,
                beforeSend: function() {
                    $('.wtai-keyword-filter-wrapper').find('.button-primary').addClass('disabled');
                },
                success: function( data ){
                    if( data.result['analysis_request_id'] != '' && from_analysis_stream != 'yes' ){
                        $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);

                        progressbar_keyword_analysis( 'show', 1, WTAI_KEYWORD_OBJ.ongoingKeywordAnalysisMessage, false );

                        $('.wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    }
                    else{
                        if( refresh == 'yes' ){
                            if( data.available_credit_label != '' ){
                                $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                            }
                        }

                        if( from_analysis_stream == 'yes' ){
                            if( window.keywordIdeasSource != 'refresh' ){
                                render_keyword_html_sections( data, 'all', initial_load );
                            }
                            else{
                                var refresh_type = window.keywordIdeasSourceType;
                                render_keyword_html_sections( data, refresh_type );
                            }
                        }
                        else{
                            render_keyword_html_sections( data, 'all' );
                        }

                        var hasStaleData = false;
                        var hasIdeas = false;

                        if( from_analysis_error != 'yes' ){                        
                            if( data.result['keyword_ideas'].length > 0 ){
                                hasIdeas = true;
                                
                                if( data.result.stale == '1' ){
                                    var divAlert = '<div class="wtai-error-msg"><div>' + WTAI_KEYWORD_OBJ.keyword_ideas_stale_msg + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                                    if( !$('.wtai-keyword .wtai-error-msg').length ) {
                                        $(divAlert).appendTo('.wtai-keyword');
                                    } else {
                                        $('.wtai-keyword .wtai-error-msg').remove();
                                        $(divAlert).appendTo('.wtai-keyword');
                                        $('.wtai-keyword .wtai-error-msg').fadeIn();
                                    }

                                    hasStaleData = true;
                                }

                                if( hasIdeas == true){
                                    $('.wtai-keyword-ideas-table').addClass('wtai-has-data');
                                }
                            }
                            else{
                                if( refresh == 'yes' ){
                                    var keywordErrorMessage = WTAI_KEYWORD_OBJ.keyword_ideas_msg;
                                    if( data.error != '' ){
                                        keywordErrorMessage = data.error;
                                    }

                                    var divAlert = '<div class="wtai-error-msg"><div>' + keywordErrorMessage + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                                    if( !$('.wtai-keyword .wtai-error-msg').length ) {
                                        $(divAlert).appendTo('.wtai-keyword');
                                    } else {
                                        $('.wtai-keyword .wtai-error-msg').remove();
                                        $(divAlert).appendTo('.wtai-keyword');
                                        $('.wtai-keyword .wtai-error-msg').fadeIn();
                                    }                                    
                                }
                            }
                        }

                        maybeDisableKeywordInput();
                        updateKeywordCount();
                        maybeDisableGetDataButton();

                        //force disable get data button so user can refresh
                        if( hasStaleData ){
                            $('.wtai-keyword-button .wtai-keyword-getdata-button').removeClass('disabled');
                        }
                        
                        $('.wtai-target-wtai-keywords-list-wrapper').removeClass('disabled');          
                        
                        keywordIdeasAJAX = null;

                        // Update credit;
                        if( data.available_credit_label != '' ){
                            $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                        }

                        // Handle premium state;
                        var is_premium = data.result['is_premium'];
                        handle_single_product_edit_state( is_premium );
                        handle_density_premium_state( is_premium );

                        // Reenable the start keyword analysis button
                        if( from_analysis_stream == 'yes' ){
                            // Display popup success
                            if( $('.wtai-keyword .wtai-error-msg.wtai-keyword-analysis-notice.hidden').length ) {
                                $('.wtai-keyword .wtai-error-msg.wtai-keyword-analysis-notice').removeClass('hidden');
                            }

                            progressbar_keyword_analysis( 'hide', 1, '' );
                            progressbar_keyword_analysis_mini( 'hide', 1, '' );

                            window.keywordIdeasQueueRequestId = '';
                            window.keywordIdeasSource = 'all';
                            window.keywordIdeasStartAnalysis = false;
                            window.keywordIdeasSourceType = 'all';                        

                            handle_cta_states_for_keyword_analysis( 'enabled' );
                        }

                        show_hide_global_loader('hide');
                    }
                }
            });            
        }
    }

    function maybeDisableGetDataButton(){
        if( $('.wtai-keyword.wtai-keyword-single .wtai-target-wtai-keywords-list-wrapper .result').length <= 0 ){
            $('.wtai-keyword-button .wtai-keyword-getdata-button').addClass('disabled');
        }
        else{
            //check if there are ideas displayed, if not check if we should disable it
            var yourKeywordRowLength = $('.your-keyword-table tbody tr').length;
            var yourKeywordRowWithDataLength = $('.your-keyword-table tbody tr.wtai-has-data').length;

            if( yourKeywordRowLength != yourKeywordRowWithDataLength ){
                $('.wtai-keyword-button .wtai-keyword-getdata-button').removeClass('disabled');
            }
            else{
                $('.wtai-keyword-button .wtai-keyword-getdata-button').addClass('disabled');
            }
        }
    }

    function maybeDisableKeywordInput(){
        var maxKeyword = $('.wtai-keyword-filter-wrapper #maxnum_keywords').val();
        var keywordNum = $('.wtai-keyword-table-your-keywords tbody tr').length;

        if ( parseInt( keywordNum ) >= parseInt( maxKeyword ) ) {
            $('.wtai-keyword').find('.wtai-keyword-input').val('');
            $('.wtai-keyword').find('.wtai-keyword-input').prop('disabled', true);

            load_keyword_filter_tooltip();
            show_keyword_input_tooltip('show');

            $('.wtai-your-keyword-ideas .keyword-action-button').addClass('disabled');
        }
        else{
            $('.wtai-keyword-input').prop('disabled', false );
            load_keyword_filter_tooltip();
            show_keyword_input_tooltip('hide');

            $('.wtai-your-keyword-ideas .keyword-action-button').removeClass('disabled');
        }
    }

    function updateKeywordCount(){
        //update count in drawer
        var sa_keywords = [];
        $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                sa_keywords.push( keyword );
            }
        });

        var keywordCount = sa_keywords.length;

        $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-count').text( keywordCount );

        //update count in left post data column
        var sa_keywords = [];
        $('.postbox-container .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                sa_keywords.push( keyword );
            }
        });

        var keywordCount = sa_keywords.length;
        $('.wtai-keyword-max-count-wrap-left .wtai-keyword-count').text( keywordCount );
    }

    // Event to update keyword analysis view count
    $(document).on('wtai_update_keyword_analysis_views_count', function(e, args){
        e.stopImmediatePropagation();

        var reset = args.reset;

        updateKeywordAnaysisViewCount( reset );
    });

    function updateKeywordAnaysisViewCount( reset = false ){
        if( reset ){
            $('.wtai-keyword-analysis-view').val(0);
        }
        else{
            var current_count = $('.wtai-keyword-analysis-view').val();
            current_count = parseInt(current_count) + 1;

            $('.wtai-keyword-analysis-view').val(current_count);
        }
    }

    function progressbar_keyword_analysis( state = '', progress = 0, message = '', force_done = false ){
        if( window.keywordIdeasSource == 'refresh' ){
            return;
        }

        var max_progress = $('.wtai-keyword-analysis-progress-loader').attr('data-max-progress');

        if( state == 'show' ){
            var per = ( parseInt( progress ) / parseInt( max_progress ) ) * 100;
            if( per > 100 || force_done ){
                per = 100;
            }

            $('.wtai-keyword-analysis-progress-loader .wtai-keyword-analysis-progress-loader-text').html( message );
            $('.wtai-keyword-analysis-progress-loader .wtai-main-loading').css( 'width', per + '%' );
            $('.wtai-keyword-analysis-progress-loader-overlay').show();
            $('.wtai-keyword-analysis-progress-loader').show();
            $('.wtai-keyword-analysis-progress-loader').addClass('loader-is-visible');
        }
        else{
            $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);
            $('.wtai-keyword-analysis-progress-loader .wtai-keyword-analysis-progress-loader-text').html( '' );
            $('.wtai-keyword-analysis-progress-loader .wtai-main-loading').css( 'width', '0%' );
            $('.wtai-keyword-analysis-progress-loader-overlay').hide();
            $('.wtai-keyword-analysis-progress-loader').hide();
            $('.wtai-keyword-analysis-progress-loader').removeClass('loader-is-visible');
        }
    }

    function progressbar_keyword_analysis_mini( state = '', progress = 0, message = '', force_done = false, refresh_type = '' ){
        var parent = null;
        if( refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
            if( refresh_type == 'competitor-keywords' ){
                if( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-count').length && parseInt( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-count').text() ) > 0 ){
                    parent = $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis');
                }
                else{
                    parent = $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords');
                }
            } else{
                parent = $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis');
            }
        }
        else{
            if( $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').length > 0 ){
                parent = $('.wtai-keyword-analysis-content-wrap.wtai-keyword-ideas-group');
            }
            else{
                parent = $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords');
            }
        }

        var max_progress = parent.find('.wtai-keyword-analysis-progress-loader').attr('data-max-progress');

        if( state == 'show' ){
            if( refresh_type == 'your-keywords' || refresh_type == 'suggested-keywords' ){
                if( $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').length > 0 && $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords').find('.wtai-keyword-analysis-toggle').hasClass('wtai-state-hidden') ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords').find('.wtai-keyword-analysis-toggle').trigger('click');
                }

                if( $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords').find('.wtai-keyword-analysis-toggle').hasClass('wtai-state-hidden') ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords').find('.wtai-keyword-analysis-toggle').trigger('click');
                }
            }
            else{
                if( parent.find('.wtai-keyword-analysis-toggle').hasClass('wtai-state-hidden') ){
                    parent.find('.wtai-keyword-analysis-toggle').trigger('click');
                }
            }

            if( isNaN( progress ) ){
                progress = 0;
            }

            if( isNaN( max_progress ) ){
                if( refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
                    max_progress = 7;
                }
                else{
                    max_progress = 3;
                }
            }

            var per = ( parseInt( progress ) / parseInt( max_progress ) ) * 100;

            if( per > 100 || force_done ){
                per = 100;
            }

            parent.find('.wtai-keyword-analysis-progress-loader-mini .wtai-keyword-analysis-progress-loader-text').html( message );
            parent.find('.wtai-keyword-analysis-progress-loader-mini .wtai-main-loading').css( 'width', per + '%' );
            parent.find('.wtai-keyword-analysis-progress-loader-mini').show();
        }
        else{
            $('.wtai-keyword-analysis-progress-loader-mini').attr('data-progress', 1);
            $('.wtai-keyword-analysis-progress-loader-mini .wtai-keyword-analysis-progress-loader-text').html( '' );
            $('.wtai-keyword-analysis-progress-loader-mini .wtai-main-loading').css( 'width', '0%' );
            $('.wtai-keyword-analysis-progress-loader-mini').hide();
        }
    }

    function handle_cta_states_for_keyword_analysis( state = '' ){
        if( state == 'disabled' ){
            $('.wtai-start-ai-analysis-btn').addClass('disabled');
            $('.wtai-keyword-analysis-refresh-cta').addClass('disabled');
            $('.wtai-sort-ideas-select').addClass('disabled');
            $('.wtai-keyword-action-button-v2').removeClass('wtai-not-allowed');
            $('.wtai-keyword-action-button-v2').addClass('disabled');
            $('.wtai-load-more-cta').addClass('disabled');
            $('.wtai-volume-difficulty-ico').addClass('disabled');

            $('.wtai-keyword-action-trash').removeClass('wtai-not-allowed');
            $('.wtai-keyword-action-trash').addClass('disabled');

            $('body.wtai-keyword-open .wtai-main-wrapper .wtai-top-header .wtai-history-single-btn').addClass('disabled');
            $('.wtai-main-wrapper .wtai-close').addClass('disabled');
            $('.wtai-slide-right-text-wrapper  .wtai-btn-close-keyword').addClass('disabled');
            $('.wtai-keyword-analysis-popin-right').addClass('wtai-process-ongoing');
        }
        else{
            $('.wtai-start-ai-analysis-btn').removeClass('disabled');
            $('.wtai-keyword-analysis-refresh-cta').removeClass('disabled');
            $('.wtai-sort-ideas-select').removeClass('disabled');
            $('.wtai-keyword-action-button-v2').removeClass('disabled');
            $('.wtai-load-more-cta').removeClass('disabled');
            $('.wtai-volume-difficulty-ico').removeClass('disabled');

            var max_selected_keyword = parseInt( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-max-count').text() );

            if( $('.wtai-keyword-table-selected-keywords tbody.wtai-keyword-tbody tr.wtai-keyword-tr').length < max_selected_keyword ){
                $('.wtai-keyword-action-button-add').removeClass('disabled');
                $('.wtai-keyword-action-button-add').removeClass('wtai-not-allowed');
            }
            else{
                $('.wtai-keyword-action-button-add').addClass('disabled');
                $('.wtai-keyword-action-button-add').addClass('wtai-not-allowed');
            }

            $('.wtai-keyword-action-trash').each(function(){
                var tr = $(this).closest('tr');
                if( tr.find('.wtai-keyword-action-button-v2').attr('data-type') == 'remove' ){
                    $(this).addClass('disabled');
                    $(this).addClass('wtai-not-allowed');
                }
                else{
                    $(this).removeClass('disabled');
                }
            });

            $('body.wtai-keyword-open .wtai-main-wrapper .wtai-top-header .wtai-history-single-btn').removeClass('disabled');
            $('.wtai-main-wrapper .wtai-close').removeClass('disabled');
            $('.wtai-slide-right-text-wrapper  .wtai-btn-close-keyword').removeClass('disabled');
            $('.wtai-keyword-analysis-popin-right').removeClass('wtai-process-ongoing');

            $('.wtai-keyword .wtai-keyword-action-button-v2').removeClass('wtai-loader');
            $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');
        }

        maybeDisableKeywordInput();

        setTimeout(function() {
            show_keyword_actions_tooltip();
        }, 300);
    }

    // Moved to admin-keyword.js.
    function table_sort_comparer(index) {
        return function(a, b) {
            var valA = table_get_cell_value(a, index);
            var valB = table_get_cell_value(b, index);
            return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.localeCompare(valB);
        };
    }

    // Moved to admin-keyword.js.
    function table_get_cell_value(row, index) {
        return $(row).children('td').eq(index).attr('data-value');
    }

    // Moved to admin-keyword.js.
    function table_assign_page_numbers(rows) {
        var firstPageCount = 5;
        var subsequentPageCount = 10;
        var pageNumber = 1;
        var item_ctr = 0;
        for (var i = 0; i < rows.length; i++) {
            if( $(rows[i]).hasClass('wtai-no-match') ){
                $(rows[i]).attr('data-page-no', 0);
                $(rows[i]).attr('data-index-no', '');
                continue;
            }

            if (item_ctr < firstPageCount) {
                pageNumber = 1;
            } else {
                pageNumber = Math.floor((item_ctr - firstPageCount) / subsequentPageCount) + 2;
            }

            $(rows[i]).attr('data-page-no', pageNumber);
            $(rows[i]).attr('data-index-no', i);

            item_ctr++;
        }
    }

    var saveKeywordIdeasSortFilterAJAX = null;
    function save_keyword_analysis_sort_filter( keyword_type ){
        var main_wrapper_class = '';
        if( keyword_type == 'ranked' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords';
        }
        if( keyword_type == 'competitor' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords';
        }
        if( keyword_type == 'suggested' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords';
        }

        if( $(main_wrapper_class).length <= 0 ){
            return;
        }

        $(main_wrapper_class).find('.wtai-keyword-table-parent-wrap').addClass('wtai-loading-state');

        if( saveKeywordIdeasSortFilterAJAX != null ){
            saveKeywordIdeasSortFilterAJAX.abort();
        }

        var parent_wrap = $(main_wrapper_class);
        var table_parent = parent_wrap.find('.wtai-keyword-table');

        var sort_type = table_parent.attr('data-sort-field');
        var sort = table_parent.attr('data-sort');

        var sort_direction = sort;
        if( sort_type == 'relevance' ){
            sort_direction = 'asc';
        }

        var difficultyFilter = [];
        table_parent.find('.wtai-difficulty-filter').each(function(){
            if( $(this).is(':checked') ){
                difficultyFilter.push( $(this).val() );
            }
        });

        var volumeFilter = 'all';
        if( table_parent.find('.wtai-volume-filter:checked').length ){
            volumeFilter = table_parent.find('.wtai-volume-filter:checked').val();
        }

        var product_id = $('#wtai-edit-post-id').attr('value');

        var wtai_nonce = get_wp_nonce();

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        var data = {
            action: 'wtai_keyword_analysis_save_sort_filter',
            record_id: $('#wtai-edit-post-id').attr('value'), 
            record_type: $('#wtai-record-type').val(),
            wtai_nonce: wtai_nonce,
            sort_type: sort_type,
            sort_direction: sort_direction,
            keyword_type: keyword_type,
            difficultyFilter: difficultyFilter.join('|'),
            volumeFilter: volumeFilter,
        };

        saveKeywordIdeasSortFilterAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
                // initialize keywords section
                
            },
            success: function( data ){ 
                saveKeywordIdeasSortFilterAJAX = null;
                $(main_wrapper_class).find('.wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');
            }
        });
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

    load_keyword_actions_tooltip();
    function load_keyword_actions_tooltip(){
        try{ 
            $('.wtai-keyword-action-trash').each(function(){
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

        try{ 
            $('.wtai-keyword-analysis-popin-right .wtai-keyword-action-button-v2').each(function(){
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

    function show_keyword_actions_tooltip(){
        $('.wtai-keyword-table-your-keywords .wtai-keyword-action-trash').each(function(){
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

            if( $(this).hasClass('wtai-not-allowed') ){
                $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordTrashDisabledTooltip);
                $(this).tooltipster('content', WTAI_KEYWORD_OBJ.keywordTrashDisabledTooltip);
            } else {
                $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordTrashTooltip);
                $(this).tooltipster('content', WTAI_KEYWORD_OBJ.keywordTrashTooltip);
            }

            $(this).hover(function(){                
                if( $(this).hasClass('wtai-not-allowed') ){
                    $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.keywordTrashDisabledTooltip);
                } else {
                    $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.keywordTrashTooltip);
                }

                $(this).removeAttr('title');
            }, function(){
                $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordTrashTooltip);
                $(this).removeAttr('tooltip-data');
            });

            $(this).tooltipster('enable');
        });

        $('.wtai-keyword-analysis-popin-right .wtai-keyword-action-button-v2').each(function(){
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

            if( $(this).hasClass('wtai-not-allowed') ){
                $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordPlusDisabledTooltip);
                $(this).tooltipster('content', WTAI_KEYWORD_OBJ.keywordPlusDisabledTooltip);
            } else {
                if( $(this).hasClass('dashicons-minus') ){
                    $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordMinusTooltip);
                    $(this).tooltipster('content', WTAI_KEYWORD_OBJ.keywordMinusTooltip);
                } else {
                    $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.keywordPlusTooltip);
                    $(this).tooltipster('content', WTAI_KEYWORD_OBJ.keywordPlusTooltip);
                }
            }

            $(this).hover(function(){
                if( $(this).hasClass('wtai-not-allowed') ){
                    $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.keywordPlusDisabledTooltip);
                } else {
                    if( $(this).hasClass('dashicons-minus') ){
                        $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.keywordMinusTooltip);
                    } else{
                        $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.keywordPlusTooltip);
                    }
                }

                $(this).removeAttr('title');
            }, function(){
                $(this).attr('data-tooltip', $(this).attr('tooltip-data'));
                $(this).removeAttr('tooltip-data');
            });

            $(this).tooltipster('enable');
        });
    }

    $(document).on('click', '.wtai-keyword-spellcheck-link', function( e ){
        e.preventDefault();

        var spellcheck_link = $(this);
        var table_parent = $(this).closest('.wtai-keyword-table-parent-wrap');
        var td_parent = $(this).closest('.wtai-col-keyword');
        var tr_parent = $(this).closest('.wtai-keyword-tr');

        td_parent.addClass('wtai-loading-state');

        var language_code = $('.wtai-keyword-location-code').val();
        var correct_keyword = $(this).text().toLowerCase().trim();
        var incorrect_keyword = $(this).closest('tr').find('.wtai-column-keyword-name-text').text().toLowerCase().trim();

        var wtai_nonce = get_wp_nonce();

        handle_cta_states_for_keyword_analysis( 'disabled' );

        //get other existing keywords
        var existing_keywords = [];
        var k = 0;
        $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                if( keyword == incorrect_keyword ){
                    existing_keywords[k] = correct_keyword;
                } else {
                    existing_keywords[k] = keyword;
                }
                
                k++;
            }
        });

        var update_manual_keyword = 0;
        $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').each(function(){
            var keyword = $(this).find('.wtai-column-keyword-name-text').text();
            if( keyword == incorrect_keyword ){
                update_manual_keyword = 1;
            }
        });

        var data = {
            action: 'wtai_apply_spellcheck_keyword',
            record_id: $('#wtai-edit-post-id').attr('value'), 
            record_type: $('#wtai-record-type').val(),
            correct_keyword: correct_keyword, 
            incorrect_keyword: incorrect_keyword, 
            existing_keywords: existing_keywords.join('|'), 
            wtai_nonce: wtai_nonce,
            language_code: language_code,
            update_manual_keyword: update_manual_keyword,
        };

        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
            },
            success: function( data ){
                // Hide spellcheck wrap
                spellcheck_link.closest('.wtai-keyword-spellcheck-wrap').hide();

                // Replace keyword name with the correct keyword
                spellcheck_link.closest('tr').find('.wtai-column-keyword-name .wtai-column-keyword-name-text').html( correct_keyword );

                // Update CTA data-keyword
                spellcheck_link.closest('tr').find('.wtai-keyword-action-button-v2').attr( 'data-keyword', correct_keyword );

                // Correct keyword name in the left column keyword section
                $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
                    var keyword = $(this).find('.wtai-keyword-name').text();
                    if( keyword ){
                        if( keyword == incorrect_keyword ){
                            $(this).find('.wtai-keyword-name').text( correct_keyword );
                        }
                    }
                });

                if( parseInt( update_manual_keyword ) == 1 ){
                    // Correct keyword name in the your keywords section
                    $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').each(function(){
                        var keyword = $(this).find('.wtai-column-keyword-name-text').text();
                        if( keyword == incorrect_keyword ){
                            $(this).find('.wtai-column-keyword-name-text').text( correct_keyword );
                        }
                    });
                }

                // Update the semantic table data
                var keyword_semantic = '';
                $.each(['keyword_input', 'keyword_ideas'], function (index,keyword_type ) {                     
                    $.each(data.result[keyword_type], function(index, value ) {
                        if ( value ){
                            //semantic
                            keyword_semantic = keyword_semantic + '<div class="wtai-semantic-keywords-wrapper-list">';
                            keyword_semantic = keyword_semantic+'<div class="wtai-header-label">'+value['name']+'</div>';
                            keyword_semantic = keyword_semantic+'<div class="wtai-semantic-list">';
                            $.each( value['semantic'], function( index, value ) {
                                var semantic_active = '';
                                if ( value['active'] ){
                                    semantic_active = 'wtai-active';
                                } else {
                                    semantic_active = '';
                                }  
                                
                                var perSemantic = '(0.00%)';
                                if( $('.wtai-data-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').length ){
                                    $('.wtai-data-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').each(function(){
                                        var semanticKeywordExisting = $(this).find('.wtai-keyword-name').text();

                                        if( value['semantic'] == semanticKeywordExisting ){
                                            perSemantic = $(this).find('.wtai-per').text();
                                        }
                                    });
                                }

                                var sk_tooltip_label = '';
                                if( semantic_active != 'wtai-active' ){
                                    sk_tooltip_label = WTAI_KEYWORD_OBJ.maxSemanticKeywordMessage;
                                }

                                keyword_semantic =  keyword_semantic+'<span class="wtai-semantic-keyword '+semantic_active+'" title="'+sk_tooltip_label+'" ><span class="wtai-keyword-name">'+value['name']+'</span> <span class="wtai-per">'+perSemantic+'</span></span>';
                            });
                            keyword_semantic = keyword_semantic+'</div>';
                            keyword_semantic = keyword_semantic +'</div>';
                        }
                    });                  
                });

                $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html('');
                $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html( keyword_semantic );

                //set semantic active count
                setSemanticActiveCount();

                td_parent.removeClass('wtai-loading-state');
                handle_cta_states_for_keyword_analysis( 'enabled' );
            }
        });
    });

    // Event to get/set semantic keyword count
    $(document).on('wtai_set_semantic_keyword_active_count', function(e){
        e.stopImmediatePropagation();

        setSemanticActiveCount();
    });

    function setSemanticActiveCount(){
        var activeSemanticCount = $('.wtai-semantic-keywords-wrapper .wtai-semantic-keyword.wtai-active').length;
        var maxSemanticCount = parseInt( $('.wtai-semantic-keyword-counter-wrap .wtai-max-count').text() );

        $('.wtai-semantic-keyword-counter-wrap .wtai-active-count').html( activeSemanticCount );

        load_keyword_filter_tooltip();

        if( parseInt( activeSemanticCount ) >= parseInt( maxSemanticCount ) ){
            $('.wtai-semantic-keyword-counter-wrap').addClass('max-reached');
            $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').addClass('max-reached');
            $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').removeClass('max-reached');

            show_semantic_keyword_tooltip('show');
        }
        else{
            $('.wtai-semantic-keyword-counter-wrap').removeClass('max-reached');
            $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').removeClass('max-reached');

            show_semantic_keyword_tooltip('hide');
        }
    }

    function updateKeywordSinglePageDrawer( value ) {
        if( value == '-' || value == '*' || value == '#' || value == '$' || value == '@' || value == '.' ){
            return;
        }

        var maxKeyword = $('.wtai-keyword-filter-wrapper #maxnum_keywords').val();
        var keywordNum = $('.wtai-keyword-table-your-keywords tbody tr').length;

        if ( parseInt( keywordNum ) < parseInt( maxKeyword ) ) {
            $('.wtai-keyword').find('.wtai-keyword-input').prop('disabled', false);
            show_keyword_input_tooltip('hide');

            value = value.toLowerCase(); // keywords should always be lowercase.

            var isDuplicate = false;
            var divAlert = '';
            $('.wtai-keyword-table-your-keywords tbody tr').each(function() {
                if ( $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text().toLowerCase().trim() === value.toLowerCase().trim() ) {
                    isDuplicate = true;
                    divAlert = '<div class="wtai-error-msg"><div>' + WTAI_KEYWORD_OBJ.keyword_exist_msg + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                    if( !$('.wtai-keyword .wtai-error-msg').length ) {
                        $(divAlert).appendTo('.wtai-keyword');
                    } else {
                        $('.wtai-keyword .wtai-error-msg').remove();
                        $(divAlert).appendTo('.wtai-keyword');
                        $('.wtai-keyword .wtai-error-msg').fadeIn();
                    }

                    return false; 
                }
            });

            var product_name = $('#wtai-edit-product-line-form .wtai-post-title').text();
            var product_name_short = $('#wtai-edit-product-line-form .wtai-product-short-title').val();

            if( value.toLowerCase().trim() == product_name.toLowerCase().trim() || value.toLowerCase().trim() == product_name_short.toLowerCase().trim() ){
                isDuplicate = true;

                divAlert = '<div class="wtai-error-msg"><div>' + WTAI_KEYWORD_OBJ.productNameNotAllowedMsg + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                if( !$('.wtai-keyword .wtai-error-msg').length ) {
                    $(divAlert).appendTo('.wtai-keyword');
                } else {
                    $('.wtai-keyword .wtai-error-msg').remove();
                    $(divAlert).appendTo('.wtai-keyword');
                    $('.wtai-keyword .wtai-error-msg').fadeIn();
                }

                return false; 
            }
           
            if (!isDuplicate) {
                //appends the keyword name on the list
                //$('.wtai-keyword-selection .wtai-target-wtai-keywords-list-wrapper').append('<span class="result new"><span class="wtai-keyword-name">'+value+'</span> <span class="wtai-per">(0.00%)</span></span>');
                
                var keyword_input_table = '';
                var keyword_input_table_tbody = $('.wtai-keyword-table-your-keywords tbody');
                keyword_input_table = '<tr class="wtai-has-data wtai-keyword-tr" >';
                    keyword_input_table += "<td class='wtai-col-trash' ><a href='#' class='wtai-keyword-action-trash' data-tooltip-disabled='"+WTAI_KEYWORD_OBJ.keywordTrashDisabledTooltip+"' data-tooltip='"+WTAI_KEYWORD_OBJ.keywordTrashTooltip+"' >&nbsp;</a></td>";
                    keyword_input_table += '<td class="wtai-col-keyword"><span class="wtai-column-keyword-name-text">'+value+'</span></td>';
                    keyword_input_table += '<td class="wtai-col-volume">-</td>';
                    keyword_input_table += '<td class="wtai-col-difficulty">-</td>';
                    keyword_input_table += '<td class="wtai-col-action"><span class="dashicons dashicons-plus-alt2 wtai-keyword-action-button-add wtai-keyword-action-button-v2" data-keyword-type="manual" data-type="add_to_selected" data-keyword="'+value+'" title="'+WTAI_KEYWORD_OBJ.keywordPlusDisabledTooltip+'" ></span></td>';
                keyword_input_table += '</tr>';

                //remove empty keyword before adding
                $('.wtai-keyword-table-your-keywords tbody tr td.wtai-col-keyword').each(function(){
                    if ( $(this).find('.wtai-column-keyword-name-text').text() == '-' ){
                        $(this).parent().remove();
                    }
                });

                $(keyword_input_table).appendTo( keyword_input_table_tbody );   

                // lets show the selected keywords table
                if( $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').hasClass('hidden') ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');
                    //$('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                }

                //do semantic keywrods generation here
                //updateKeywordSinglePage( value, 'add' ); //moved to another call to action in version 1.30.1
                process_manual_keyword( value, 'add' );
            }
            else {
                $('.wtai-keyword').find('.wtai-keyword-input').val('');
            }
           
        } else {
            $('.wtai-keyword').find('.wtai-keyword-input').val('');
            $('.wtai-keyword').find('.wtai-keyword-input').prop('disabled', true);
            show_keyword_input_tooltip('show');
        }

        maybeDisableKeywordInput();
        updateKeywordCount();
    }

    function updateKeywordSinglePageLeftCol() {
        var keywords_new = '';
        //todo, new source of keywords
        $('.wtai-keyword-table-selected-keywords tbody.wtai-keyword-tbody tr.wtai-keyword-tr').each(function(){
            if( $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').length ){            
                var keyword = $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();
                var per = '(0.00%)';

                if( $( '.wtai-col-left-wrapper .wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result' ).length ){
                    $( '.wtai-col-left-wrapper .wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result' ).each(function(){
                        var keywordExist = $(this).find('.wtai-keyword-name').text();

                        if( keyword == keywordExist ){
                            per = $(this).find('.wtai-per').text();
                        }
                    });
                }

                keywords_new += '<span class="result"><span class="wtai-keyword-name">'+keyword+'</span> <span class="wtai-per">'+per+'</span></span>';
            }
        });

        $( '.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper' ).html(keywords_new);
    }

    function process_manual_keyword( value = '', type = 'add' ){
        if( value == ''){
            return;
        }

        if( $('.wtai-keyword .wtai-error-msg').length ) {
            $('.wtai-keyword .wtai-error-msg').remove();
        }

        $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-table-parent-wrap').addClass('wtai-loading-state');

        $('.wtai-keyword').find('.wtai-keyword-input').prop('disabled', true);

        show_hide_global_loader('show');
        handle_cta_states_for_keyword_analysis( 'disabled' );

        update_manual_input_count();

        var wtai_nonce = get_wp_nonce();
        var language_code = $('.wtai-keyword-location-code').val();

        var data = {
            action: 'wtai_process_manual_keyword',
            record_id: $('#wtai-edit-post-id').attr('value'), 
            record_type: $('#wtai-record-type').val(),
            keyword: value, 
            type: type, 
            language_code: language_code, 
            wtai_nonce: wtai_nonce
        };

        keywordIdeasAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
                $('.wtai-keyword-filter-wrapper').find('.button-primary').addClass('disabled');
            },
            success: function( data ){ 
                show_hide_global_loader('hide');
                $('.wtai-keyword').find('.wtai-keyword-input').prop('disabled', false);
                handle_cta_states_for_keyword_analysis( 'enabled' );
                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');

                if( data.show_suggested_refresh == '1'){
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    
                    if( type == 'add' ){
                        $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    }
                    
                    $('#wtai-analysis-data-available-flag').val('1');
                }
                else{
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }       
                
                if( $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').length <= 0 ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }

                if( $('#wtai-analysis-data-available-flag') == '1' ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                }
            }
        });
    }

    $(document).on('click', '.wtai-keyword-action-trash', function( e ){
        e.preventDefault();

        var parent = $(this).closest('tr');
        var keyword = parent.find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();

        if( $(this).hasClass('disabled') ){
            return;
        }        
        
        parent.remove();

        // Check if table has data, if not, lets hide the table and display the empty table message
        if( $('.wtai-keyword-table-your-keywords tbody tr').length <= 0 ){
            $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
            $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
        }

        process_manual_keyword( keyword, 'remove' );
    });

    function update_manual_input_count(){
        var keyword_count = 0;
        if( $('.wtai-keyword-table-your-keywords tbody tr').length ){
            keyword_count = $('.wtai-keyword-table-your-keywords tbody tr').length;
        }

        $('.keyword-manual-max-count-wrap-popin .wtai-keyword-count').text( keyword_count );
    }

    $(document).on('click', '.wtai-keyword .wtai-keyword-action-button-v2', function(e){
        var btn = $(this);
        var keyword = $(this).attr('data-keyword');
        var type = $(this).attr('data-type');
        var keyword_type = $(this).attr('data-keyword-type');
        var parent_tr = $(this).closest('tr');

        if( btn.hasClass('disabled') ){
            return;
        }

        if( $('.wtai-keyword .wtai-error-msg').length ) {
            $('.wtai-keyword .wtai-error-msg').remove();
        }

        btn.addClass('wtai-loader');

        var keyword_selected_wrapper = $('.wtai-keyword-table.wtai-keyword-table-selected-keywords');
        
        if( type == 'add_to_selected' ){
            // Add checking if keyword already exists maybe?
            var max_selected_keyword = parseInt( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-max-count').text() );
            var selected_keyword_count = $('.wtai-keyword-table-selected-keywords tbody.wtai-keyword-tbody tr.wtai-keyword-tr').length;

            if( selected_keyword_count >= max_selected_keyword ){
                return;
            }

            var product_name = $('#wtai-edit-product-line-form .wtai-post-title').text();
            var product_name_short = $('#wtai-edit-product-line-form .wtai-product-short-title').val();

            if( keyword.toLowerCase().trim() == product_name.toLowerCase().trim() || keyword.toLowerCase().trim() == product_name_short.toLowerCase().trim() ){
                divAlert = '<div class="wtai-error-msg"><div>' + WTAI_KEYWORD_OBJ.productNameNotAllowedMsgFromPlus + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                if( !$('.wtai-keyword .wtai-error-msg').length ) {
                    $(divAlert).appendTo('.wtai-keyword');
                } else {
                    $('.wtai-keyword .wtai-error-msg').fadeIn();
                }

                btn.removeClass('wtai-loader');

                return false; 
            }

            // Check if keyword already exists, if yes display error message
            var isDuplicate = false;
            $('.wtai-keyword-table-selected-keywords tbody tr').each(function() {
                if ( $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text().toLowerCase().trim() === keyword.toLowerCase().trim() ) {
                    divAlert = '<div class="wtai-error-msg"><div>' + WTAI_KEYWORD_OBJ.keyword_exist_msg + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                    if( !$('.wtai-keyword .wtai-error-msg').length ) {
                        $(divAlert).appendTo('.wtai-keyword');
                    } else {
                        $('.wtai-keyword .wtai-error-msg').fadeIn();
                    }

                    btn.removeClass('wtai-loader');
                    isDuplicate = true;

                    return false; 
                }
            });

            if( isDuplicate ){
                btn.removeClass('wtai-loader');
                return;
            }

            var rank = '-';
            var intent = '-';
            var serp_info = '';
            var keyword_name_class = '';
            if( keyword_type == 'ranked' ){
                if( parent_tr.find('.wtai-col-rank').length ){
                    rank = parent_tr.find('.wtai-col-rank').text();
                }
                
                if( parent_tr.find('.wtai-col-intent').length ){
                    intent = parent_tr.find('.wtai-col-intent').text();
                }

                if( parent_tr.find('.wtai-keyword-serp-wrap').length ){
                    serp_info = '<div class="wtai-keyword-serp-wrap wtai-tooltiptext wtai-keyword-serp-wrap-ranked" >' + parent_tr.find('.wtai-keyword-serp-wrap').html() + '<div>';
                    keyword_name_class = ' wtai-column-keyword-name-tooltip tooltip ';
                }
            }

            var volume = parent_tr.find('.wtai-col-volume').text();
            var difficulty = parent_tr.find('.wtai-col-difficulty').text();

            var tr_selected_html = '<tr class="wtai-has-data wtai-keyword-tr">';
                tr_selected_html += '<td class="wtai-col-keyword"><div class="wtai-column-keyword-name '+keyword_name_class+'"><span class="wtai-column-keyword-name-text" >'+keyword+'</span>' + serp_info + '</div></td>';
                tr_selected_html += '<td class="wtai-col-rank">'+rank+'</td>';
                tr_selected_html += '<td class="wtai-col-intent">'+intent+'</td>';
                tr_selected_html += '<td class="wtai-col-volume">'+volume+'</td>';
                tr_selected_html += '<td class="wtai-col-difficulty">'+difficulty+'</td>';
                tr_selected_html += '<td class="wtai-col-action">';
                    tr_selected_html += '<span class="dashicons dashicons-minus wtai-keyword-action-button-v2" data-keyword-type="selected" data-type="remove" data-keyword="' + keyword + '"></span>';
                tr_selected_html += '</td>';
            tr_selected_html += '</tr>';

            keyword_selected_wrapper.find('tbody.wtai-keyword-tbody').append( tr_selected_html );
            
            // lets show the selected keywords table
            if( $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').hasClass('hidden') ){
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');

                if( keyword_type == 'suggested' || keyword_type == 'competitor' || $('#wtai-analysis-data-available-flag').val() == '1' ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                }                
            }

            if( $('#wtai-analysis-data-available-flag').val() == '1' && ( keyword_type == 'ranked' || keyword_type == 'suggested' || keyword_type == 'competitor' ) ){
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
            }

            // Lets highlight the current tr row
            parent_tr.addClass('wtai-tr-selected');

            // Lets switch the action to remove
            btn.removeClass('dashicons-plus-alt2');
            btn.removeClass('disabled');
            btn.removeClass('wtai-not-allowed');
            btn.removeClass('wtai-keyword-action-button-add');
            btn.addClass('dashicons-minus');
            btn.attr('data-type', 'remove');

            if( parent_tr.find('.wtai-keyword-action-trash').length ){
                parent_tr.find('.wtai-keyword-action-trash').addClass('disabled');
                parent_tr.find('.wtai-keyword-action-trash').addClass('wtai-not-allowed');
            }
            else{
                parent_tr.find('.wtai-keyword-action-trash').removeClass('wtai-not-allowed');
            }

            // Add keyword to the API
            updateKeywordSinglePageLeftCol();
            updateKeywordSinglePage( keyword , 'add' );

            // Update keyword count
            //updateKeywordCount();
        }
        else{
            if( keyword_type == 'selected' ){
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-table-parent-wrap').addClass('wtai-loading-state');
                
                parent_tr.remove();

                /*if( keyword_selected_wrapper.find('tbody.wtai-keyword-tbody').find('tr.wtai-keyword-tr').length <= 0 ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }*/

                // Lets look for the keyword in other sections and then change the label 
                // ranked
                $('.wtai-keyword-table-ranked-keywords tbody tr.wtai-keyword-tr').each(function(){
                    var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                    if( keyword_name.toLowerCase() == keyword.toLowerCase() ){
                        $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                        $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                        $(this).removeClass('wtai-tr-selected');
                    }
                });

                // competitor
                $('.wtai-keyword-table-competitor-keywords tbody tr.wtai-keyword-tr').each(function(){
                    var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();
                    
                    if( keyword_name.toLowerCase() == keyword.toLowerCase() ){
                        $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                        $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                        $(this).removeClass('wtai-tr-selected');
                    }
                });

                // manual
                $('.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').each(function(){
                    var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                    if( keyword_name.toLowerCase() == keyword.toLowerCase() ){
                        $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                        $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');
                        $(this).find('.wtai-keyword-action-trash').removeClass('disabled');
                        $(this).find('.wtai-keyword-action-trash').removeClass('wtai-not-allowed');

                        $(this).removeClass('wtai-tr-selected');
                    }
                });

                // suggested
                $('.wtai-keyword-table-suggested-keywords tbody tr.wtai-keyword-tr').each(function(){
                    var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                    if( keyword_name.toLowerCase() == keyword.toLowerCase() ){
                        $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                        $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                        $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                        $(this).removeClass('wtai-tr-selected');
                    }
                });
            }
            else{
                // Lets look for the keyword in the selected keywords table
                keyword_selected_wrapper.find('tbody.wtai-keyword-tbody tr.wtai-keyword-tr').each(function(){
                    var selected_keyword = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();
                    if( selected_keyword.toLowerCase() == keyword.toLowerCase() ){
                        $(this).remove();
                    }
                });

                // Lets remove highlight the current tr row
                parent_tr.removeClass('wtai-tr-selected');

                // Lets switch the action to remove
                btn.removeClass('dashicons-minus');
                btn.addClass('dashicons-plus-alt2');
                btn.addClass('wtai-keyword-action-button-add');
                btn.attr('data-type', 'add_to_selected');
            }

            if( $('#wtai-analysis-data-available-flag').val() == '1' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
            }

            updateKeywordSinglePageLeftCol();
            updateKeywordSinglePage( '' , 'add' );

            // Update keyword count
            updateKeywordCount();
        }

        // Update button states if disabled or not
        updateKeywordButtonStates();

        maybeDisableKeywordInput();

        e.preventDefault();
    });

    function updateKeywordButtonStates(){
        var max_keyword_count = parseInt( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-max-count').text() );
        var add_keyword_count = parseInt( $('.wtai-keyword-table.wtai-keyword-table-selected-keywords').find('tbody.wtai-keyword-tbody tr.wtai-keyword-tr').length );

        if( add_keyword_count >= max_keyword_count ){
            $('.wtai-keyword-table .wtai-keyword-action-button-v2.wtai-keyword-action-button-add').addClass('disabled');
            $('.wtai-keyword-table .wtai-keyword-action-button-v2.wtai-keyword-action-button-add').addClass('wtai-not-allowed');
        }
        else{
            $('.wtai-keyword-table .wtai-keyword-action-button-v2.wtai-keyword-action-button-add').removeClass('disabled');
            $('.wtai-keyword-table .wtai-keyword-action-button-v2.wtai-keyword-action-button-add').removeClass('wtai-not-allowed');
        }        
    }

    $(document).on('click', '.wtai-keyword-analysis-content-wrap .wtai-load-more-cta', function(e){
        e.preventDefault();

        if( $(this).hasClass('disabled') ){
            return;
        }

        var btn = $(this);
        var parent_wrap = btn.closest('.wtai-keyword-analysis-content-wrap');
        var table_wrap = parent_wrap.find('.wtai-keyword-table');
        var keyword_type = btn.attr('data-keyword-type');
        var current_page_no = parseInt( btn.attr('data-current-page-no') );
        var max_page_no = parseInt( btn.attr('data-total-pages') );
        var next_page_no = current_page_no + 1;

        btn.addClass('wtai-loading-state');

        if( keyword_type == 'suggested' ){
            // load next pages via ajax for suggested
            if( parseInt( current_page_no ) == 1 && table_wrap.find('tr.wtai-keyword-tr.wtai-keyword-tr-hidden').length > 0 ){
                table_wrap.find('tr.wtai-keyword-tr.wtai-keyword-tr-hidden').removeClass('wtai-keyword-tr-hidden');
                btn.removeClass('wtai-loading-state');
            }
            else{
                btn.attr('data-current-page-no', next_page_no);

                keyword_analysis_sort_filter_ajax( keyword_type, 'yes', 'no' );
            }
        }
        else{
            var show_load_more = false;
            if( next_page_no <= max_page_no ){
                show_load_more = true;

                if( table_wrap.find('tr.wtai-keyword-tr[data-page-no="'+next_page_no+'"]').length <= 0 ){
                    show_load_more = false;
                }
            }

            // Check if next page really has items
            var succeeding_page = next_page_no + 1;
            if( table_wrap.find('tr.wtai-keyword-tr[data-page-no="'+succeeding_page+'"]').length <= 0 ){
                show_load_more = false;
            }

            if( show_load_more ){
                table_wrap.find('tr.wtai-keyword-tr[data-page-no="'+next_page_no+'"]').removeClass('wtai-keyword-tr-hidden');
                btn.attr('data-current-page-no', next_page_no);
    
                if( next_page_no == max_page_no ){
                    btn.closest('.wtai-load-more-wrap').addClass('hidden');
    
                    parent_wrap.find('.wtai-keyword-ideas-no-more-data-wrap').show();
                }
            }
            else{
                btn.closest('.wtai-load-more-wrap').addClass('hidden');
    
                parent_wrap.find('.wtai-keyword-ideas-no-more-data-wrap').show();
            }

            btn.removeClass('wtai-loading-state');
        }        
    }); 

    function keyword_analysis_sort_filter_ajax( keyword_type, load_more = 'no', save_filter_and_sort = 'yes' ){
        var table_parent = $('.wtai-keyword-table-'+keyword_type+'-keywords');

        if( table_parent.length <= 0 ){
            return;
        }

        if( load_more == 'no' ){
            table_parent.closest('.wtai-keyword-table-parent-wrap').addClass('wtai-loading-state');
        }

        var main_wrapper_class = '';
        if( keyword_type == 'ranked' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords';
        }
        if( keyword_type == 'competitor' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords';
        }
        if( keyword_type == 'suggested' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords';
        }

        show_hide_global_loader('show');

        if( keywordIdeasSortFilterAJAX != null ){
            keywordIdeasSortFilterAJAX.abort();
        }

        var parent_wrap = table_parent.closest('.wtai-keyword-analysis-content-wrap');

        var sort_type = table_parent.attr('data-sort-field');
        var sort = table_parent.attr('data-sort');

        var sort_direction = sort;
        if( sort_type == 'relevance' ){
            sort_direction = 'asc';
        }

        var difficultyFilter = [];
        table_parent.find('.wtai-difficulty-filter').each(function(){
            if( $(this).is(':checked') ){
                difficultyFilter.push( $(this).val() );
            }
        });

        var volumeFilter = 'all';
        if( table_parent.find('.wtai-volume-filter:checked').length ){
            volumeFilter = table_parent.find('.wtai-volume-filter:checked').val();
        }

        if( sort_type == 'relevance' && volumeFilter == 'all' && difficultyFilter.length >= 3 && save_filter_and_sort != 'yes' && load_more != 'yes' ){
            $(main_wrapper_class).find('.wtai-load-more-cta').removeClass('wtai-loading-state');
            table_parent.closest('.wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');
            return;
        }

        var language_code = $('.wtai-keyword-location-code').val();

        var sa_keywords = [];
        var k = 0;
        $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                sa_keywords[k] = keyword;
                k++;
            }
        });

        var manual_keywords = [];
        var k = 0;
        $('.wtai-keyword-table-your-keywords tbody tr').each(function(){
            var keyword = $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();
            if( keyword ){
                manual_keywords[k] = keyword;
                k++;
            }
        });

        if( load_more == 'no' ){
            parent_wrap.find('.wtai-load-more-cta').attr('data-current-page-no', 1);
        }

        var pageNo = parent_wrap.find('.wtai-load-more-cta').attr('data-current-page-no');

        var product_id = $('#wtai-edit-post-id').attr('value');

        var has_custom_filter = false;

        if( volumeFilter == 'all' && difficultyFilter.length >= 3 ){
            table_parent.find('.wtai-volume-difficulty-ico').removeClass('wtai-active');
        }
        else{
            has_custom_filter = true;
        }

        handle_cta_states_for_keyword_analysis( 'disabled' );

        var wtai_nonce = get_wp_nonce();

        var data = {
            action: 'wtai_keyword_analysis_sort_filter',
            record_id: $('#wtai-edit-post-id').attr('value'), 
            record_type: $('#wtai-record-type').val(),
            keywords: sa_keywords.join('|'), 
            manual_keywords: manual_keywords.join('|'), 
            language_code: language_code, 
            wtai_nonce: wtai_nonce,
            sort_type: sort_type,
            sort_direction: sort_direction,
            keyword_type: keyword_type,
            difficultyFilter: difficultyFilter.join('|'),
            volumeFilter: volumeFilter,
            pageNo: pageNo,
            save_filter_and_sort: save_filter_and_sort,
        };

        keywordIdeasSortFilterAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: data,
            beforeSend: function() {
                // initialize keywords section
                
            },
            success: function( data ){ 
                show_hide_global_loader('hide');

                // render the html
                var html = data.result.html;
                var tbody = $(html).find('tbody').html();
                var loadmorehtml = $(html).find('.wtai-keyword-list-bottom-data').html();

                if( load_more == 'yes' ){
                    table_parent.find('tbody').append( tbody );
                }
                else{
                    table_parent.find('tbody').html( tbody );
                }

                if( main_wrapper_class != '' ){
                    $(main_wrapper_class).find('.wtai-keyword-list-bottom-data').html( loadmorehtml );
                }

                $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-wrap').hide();

                if( $(main_wrapper_class).find('.wtai-load-more-wrap').text().trim() == '' && 
                    $(main_wrapper_class).find('table tr.wtai-keyword-tr').length > 10 ){

                    $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-wrap').show();
                    $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();
                }

                console.log('has_custom_filter ' + has_custom_filter);

                if( $(main_wrapper_class).find('table tr.wtai-keyword-tr').length < 5 && has_custom_filter ){
                    $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-wrap').hide();
                    $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').show();
                } else{
                    if( $(main_wrapper_class).find('.wtai-load-more-wrap').text().trim() == '' ){
                        $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-wrap').show();

                        if( ! has_custom_filter ){
                            $(main_wrapper_class).find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();
                        }
                    }
                }
                
                handle_cta_states_for_keyword_analysis( 'enabled' );

                shouldActivateKeywordFilter( keyword_type );

                keywordIdeasSortFilterAJAX = null;

                $(main_wrapper_class).find('.wtai-load-more-cta').removeClass('wtai-loading-state');
                table_parent.closest('.wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');
            }
        });
    }

    $(document).on('wtaProcessKeywordAnalysis', function(e, messageEntry){
        e.stopImmediatePropagation();

        if( $('#wtai-edit-post-id').length ){
            var currentRequestId = window.keywordIdeasQueueRequestId;
            var currentproductId = $('#wtai-edit-post-id').attr('value');
            var status = messageEntry.encodedMsg.status;
            var recordId = messageEntry.encodedMsg.recordId;
            var requestId = messageEntry.encodedMsg.requestId;
            var task_status_code = messageEntry.encodedMsg.task_status_code;
            var status_display = messageEntry.encodedMsg.status_display;

            if( currentRequestId == requestId || currentproductId == recordId ){
                show_hide_global_loader('show');

                if( status == 'Completed' ){
                    $('.wtai-keyword-analysis-progress-loader .wtai-keyword-analysis-progress-loader-text').html( '' );
                    $('.wtai-keyword-analysis-progress-loader').hide();

                    var displayMsg = '';
                    if( status_display != null && status_display != '' ){
                        displayMsg = status_display;
                    
                        // Display completed message
                        var divAlert = '<div class="wtai-error-msg success-completed wtai-keyword-analysis-notice hidden"><div>' + displayMsg + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                        if( ! $('.wtai-keyword .wtai-error-msg').length ) {
                            $(divAlert).appendTo('.wtai-keyword');
                        } else {
                            $('.wtai-keyword .wtai-error-msg').remove();
                            $(divAlert).appendTo('.wtai-keyword');
                            //$('.wtai-keyword .wtai-error-msg').fadeIn();
                        }
                    }

                    getKeyWordIdeas( 'no', 'yes', '', 'yes' );
                }
                else if( status == 'Failed' ){
                    var errorMsg = '';
                    if( ( status_display != null && status_display != '' ) || task_status_code == '50000' ){
                        errorMsg = status_display;
                    }
                    else if( status_display == null || status_display == '' ){
                        errorMsg = WTAI_KEYWORD_OBJ.startKeywordAnalysisErrorMessage;
                    }
                    else{
                        errorMsg = WTAI_KEYWORD_OBJ.generalErrorMessage;
                    }

                    if( task_status_code == '40202' || task_status_code == '40209' || task_status_code == '50301' ){
                        errorMsg = WTAI_KEYWORD_OBJ.keywordTooManyRequestError;
                    }

                    // Display failed message
                    var divAlert = '<div class="wtai-error-msg wtai-keyword-analysis-notice hidden"><div>' + errorMsg + '<span class="btn-close-wtai-error-msge"></span></div></div>';

                    if( ! $('.wtai-keyword .wtai-error-msg').length ) {
                        $(divAlert).appendTo('.wtai-keyword');
                    } else {
                        $('.wtai-keyword .wtai-error-msg').remove();
                        $(divAlert).appendTo('.wtai-keyword');
                        //$('.wtai-keyword .wtai-error-msg').fadeIn();
                    }

                    $('.wtai-keyword-analysis-progress-loader .wtai-keyword-analysis-progress-loader-text').html( '' );
                    $('.wtai-keyword-analysis-progress-loader').hide();

                    getKeyWordIdeas( 'no', 'yes', '', 'yes' );
                }
                else if( status == 'Running' ){
                    if( $('.wtai-keyword .wtai-error-msg').length ) {
                        $('.wtai-keyword .wtai-error-msg').remove();
                    }

                    if( window.keywordIdeasSource != 'refresh' ){
                        if( $('.wtai-keyword-analysis-progress-loader').hasClass('loader-is-visible') == false ){
                            $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);
    
                            progressbar_keyword_analysis( 'show', 1, WTAI_KEYWORD_OBJ.startKeywordAnalysisMessage, false );
    
                            $('.wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
    
                            handle_cta_states_for_keyword_analysis( 'disabled' );                    
    
                            // initialize keywords section
                            $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-loader').removeClass('hidden');
                            $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-empty-label').addClass('hidden');
                            $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').html('');
                            $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').addClass('hidden');
                        }

                        var current_progress = parseInt( $('.wtai-keyword-analysis-progress-loader').attr('data-progress') );
                        current_progress = current_progress + 1;
                        $('.wtai-keyword-analysis-progress-loader').attr('data-progress', current_progress)

                        progressbar_keyword_analysis( 'show', current_progress, status_display, false );
                    }
                    else{
                        var refresh_type = window.keywordIdeasSourceType;
                        var current_progress = 0;
                        if( refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
                            current_progress = parseInt( $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis .wtai-keyword-analysis-progress-loader-mini').attr('data-progress') );
                        }
                        else{
                            current_progress = parseInt( $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-progress-loader-mini').attr('data-progress') );
                        }
                        
                        current_progress = current_progress + 1;
                        $('.wtai-keyword-analysis-progress-loader-mini').attr('data-progress', current_progress)

                        progressbar_keyword_analysis_mini( 'show', current_progress, status_display, false, refresh_type );
                    }
                }
            }
        }
    });

    // Sorting of keyword table version 1.30
    $(document).on('click', '.wtai-sort-ideas-select', function(){
        if( $(this).hasClass('disabled') ){
            return;
        }

        $(this).closest('.wtai-keyword-table-parent-wrap').addClass('wtai-loading-state');

        var th_index =  $(this).closest('th');
        var table_parent = $(this).closest('.wtai-keyword-table');
        var keyword_type = table_parent.attr('data-keyword-type');
        var sort_type = $(this).attr('data-type');
        var sort = table_parent.attr('data-sort');

        var sort_direction = 'asc';
        if( sort == 'asc' ){
            sort_direction = 'desc';
        }
        if( sort_type == 'relevance' ){
            sort_direction = 'asc';
        }

        // Sort state
        $('.wtai-sort-ideas-select').removeClass('wtai-active-sort');
        $('.wtai-sort-ideas-select').removeClass('asc');
        $('.wtai-sort-ideas-select').removeClass('desc');
        $(this).addClass('wtai-active-sort');
        $(this).addClass(sort_direction);

        table_parent.attr('data-sort-field', sort_type);
        table_parent.attr('data-sort', sort_direction);

        table_parent.find('.wtai-keyword-by-relevance-label').hide();
        if( sort_type == 'relevance' ){
            table_parent.find('.wtai-keyword-by-relevance-label').show();
        }

        var difficultyFilter = [];
        table_parent.find('.wtai-difficulty-filter').each(function(){
            if( $(this).is(':checked') ){
                difficultyFilter.push( $(this).val() );
            }
        });

        var volumeFilter = 'all';
        if( table_parent.find('.wtai-volume-filter:checked').length ){
            volumeFilter = table_parent.find('.wtai-volume-filter:checked').val();
        }

        if( keyword_type == 'ranked' || keyword_type == 'competitor' ){
            var parent_wrap = table_parent.closest('.wtai-keyword-analysis-content-wrap');

            parent_wrap.find('.wtai-load-more-wrap').addClass('hidden');
            parent_wrap.find('.wtai-keyword-ideas-no-more-data-wrap').hide();
            parent_wrap.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();

            // DO offline table sorting here
            sort_static_keyword_table( table_parent, th_index, sort_direction );

            save_keyword_analysis_sort_filter( keyword_type );

            $(this).closest('.wtai-keyword-table-parent-wrap').removeClass('wtai-loading-state');

            return;
        }

        keyword_analysis_sort_filter_ajax( keyword_type, 'no', 'yes' );
    });

    $(document).on('click', '.wtai-semantic-list .wtai-semantic-keyword', function(e){
        e.preventDefault();

        var isCurrentActive = $(this).hasClass('wtai-active');

        //check if max keyword selected
        var activeSemanticCount = $('.wtai-semantic-keywords-wrapper .wtai-semantic-keyword.wtai-active').length;
        var maxSemanticCount = parseInt( $('.wtai-semantic-keyword-counter-wrap .wtai-max-count').text() );
        if( activeSemanticCount == maxSemanticCount && isCurrentActive ){
            //backward compat
            $(this).removeClass('wtai-active');
        }
        else if( activeSemanticCount == maxSemanticCount && ! isCurrentActive ){
            return;
        }
        else if( activeSemanticCount > maxSemanticCount ){
            //backward compat
            $(this).removeClass('wtai-active');
        }
        else{
            $(this).toggleClass('wtai-active');
        }

        var date = new Date();
        var offset = date.getTimezoneOffset();

        //tag semantic as selected
        var post_id = $('#wtai-edit-post-id').val();
        var keyword = $('.wp-heading-inline.wtai-post-title').text();

        var semantic_keywords = [];
        var k = 0;
        $('.wtai-semantic-keywords-wrapper-list .wtai-semantic-list .wtai-semantic-keyword.wtai-active').each(function(){
            var se_keyword = $(this).find('.wtai-keyword-name').text();
            if( se_keyword ){
                semantic_keywords[k] = se_keyword;
                k++;
            }
        });

        //set active semantic keyword count
        setSemanticActiveCount();

        var wtai_nonce = get_wp_nonce();

        var data =  {
            action: 'wtai_select_semantic_keyword',
            record_id: post_id,
            record_type: $('#wtai-record-type').val(),
            browsertime : offset,
            semantic_keywords : semantic_keywords.join('|'),
            wtai_nonce: wtai_nonce
        };
        if ( $(this).closest('.wtai-product-title-semantic-list').length == 0 ){
            data['keyword'] = keyword;
        }

        if( semanticKeywordSelectAJAX !== null ){
            semanticKeywordSelectAJAX.abort();
        }

        semanticKeywordSelectAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: data,
            success: function() {
                getKeywordOverallDensity();

                semanticKeywordSelectAJAX = null;
            }
        });
    });

    function get_wp_nonce(){
        var nonce = $('#wtai-edit-product-line-form').attr('data-product-nonce');
        return nonce;
    }    

    load_keyword_filter_tooltip();
    function load_keyword_filter_tooltip(){
        try{ 
            $('.wtai-keyword-input').each(function(){
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

                if( $(this).prop('disabled') == true ){
                    $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.maxManualKeywordTooltipMessage);
                    $(this).tooltipster('content', WTAI_KEYWORD_OBJ.maxManualKeywordTooltipMessage);
                } else {
                    $(this).attr('data-tooltip', WTAI_KEYWORD_OBJ.manualKeywordTooltipMessage);
                    $(this).tooltipster('content', WTAI_KEYWORD_OBJ.manualKeywordTooltipMessage);
                }

                $(this).hover(function(){
                    $(this).attr('tooltip-data', $(this).attr('title'));
                    $(this).removeAttr('title');
                }, function(){
                    $(this).attr('title', $(this).attr('tooltip-data'));
                    $(this).removeAttr('tooltip-data');
                });

                $(this).hover(function(){
                    if( $(this).prop('disabled') == true ){
                        $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.maxManualKeywordTooltipMessage);
                    } else {
                        $(this).attr('tooltip-data', WTAI_KEYWORD_OBJ.manualKeywordTooltipMessage);
                    }
    
                    $(this).removeAttr('title');
                }, function(){
                    $(this).attr('data-tooltip', $(this).attr('tooltip-data'));
                    $(this).removeAttr('tooltip-data');
                });

                //disable this by default
                if( $(this).prop('disabled') == true ){
                    $(this).tooltipster('enable');
                } else {
                    $(this).tooltipster('disable');
                }
            });
        }
        catch( err ){
        }

        try{ 
            $('.wtai-semantic-keyword-counter-wrap').each(function(){
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

    function show_keyword_input_tooltip( display = '' ){
        if( display == 'show' ){
            $('.wtai-keyword-input').tooltipster('enable');
        }
        else{
            $('.wtai-keyword-input').tooltipster('disable');
        }
    }

    function show_semantic_keyword_tooltip( display = '' ){
        if( display == 'show' ){
            $('.wtai-semantic-keyword-counter-wrap').tooltipster('enable');
        }
        else{
            $('.wtai-semantic-keyword-counter-wrap').tooltipster('disable');
        }

        $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').each(function(){
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

            if( display == 'show' && $(this).hasClass('max-reached') ){
                //disable this by default
                $(this).tooltipster('enable');
            }
            else{
                $(this).tooltipster('disable');
            }
        });
    }

    function render_keyword_html_sections( data, refresh_type = '', initial_load = 'no' ){
        // Display data in selected keyword popin
        if( refresh_type == 'all' || refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){        
            $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-loader').addClass('hidden');
            if( data.result['selected_keywords_html'] != '' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').html(data.result['selected_keywords_html']);

                if( data.result['display_selected_keywords'] == '1' ) {
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');

                    if( data.result['show_competitor_refresh'] == '1' ) {
                        $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                        $('#wtai-analysis-data-available-flag').val('1');
                    }
                    else{
                        $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    }
                }
                else{
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }
                
            } else {
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').html('');

                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
            }
        }

        if( refresh_type == 'all' ){
            // Display data in ranked keyword popin
            $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-loader').addClass('hidden');
            if( data.result['ranked_keywords_html'] != '' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-data').html(data.result['ranked_keywords_html']);
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');

                // Sort results
                var table_parent_rank = $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-data .wtai-keyword-table');
                keyword_analysis_filter( table_parent_rank, 'no' );

            } else {
                var empty_rank_message = WTAI_KEYWORD_OBJ.emptyRankMessage;
                if ( data.result['done_ranked_analysis'] == '1' ) {
                    empty_rank_message = WTAI_KEYWORD_OBJ.emptyRankMessageWithAnalysis;
                    empty_rank_message = empty_rank_message.replace("%s", data.result['ranked_last_date_retrieval']);
                }

                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-empty-label').html( empty_rank_message );

                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-data').html('');
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
            }
        }

        if( refresh_type == 'all' || refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
            // Display data in competitor keyword popin
            $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-loader').addClass('hidden');
            if( data.result['competitor_keywords_html'] != '' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-data').html(data.result['competitor_keywords_html']);
                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');
                
                if( data.result['show_competitor_refresh'] == '1' ) {
                    $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    $('#wtai-analysis-data-available-flag').val('1');
                }
                else{
                    $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }

                // Sort results
                var table_parent_competitor = $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-data .wtai-keyword-table');
                keyword_analysis_filter( table_parent_competitor, 'no' );
            } else {
                var empty_competitor_message = WTAI_KEYWORD_OBJ.emptyCompetitorMessage;
                if ( data.result['done_analysis'] == '1' && data.result['competitor_last_date_retrieval'] != '' ) {
                    empty_competitor_message = WTAI_KEYWORD_OBJ.emptyCompetitorMessageWithAnalysis;
                    empty_competitor_message = empty_competitor_message.replace("%s", data.result['competitor_last_date_retrieval']);
                }

                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-empty-label').html( empty_competitor_message );

                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-data').html('');
                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-api-data').addClass('hidden');

                if( data.result['show_competitor_refresh'] == '1' ) {
                    $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    $('#wtai-analysis-data-available-flag').val('1');
                }
                else{
                    $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }
            }
        }

        if( refresh_type == 'all' || refresh_type == 'suggested-keywords' || refresh_type == 'your-keywords' ){
            // Display data in manual keyword popin
            $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-loader').addClass('hidden');
            if( data.result['manual_keywords_html'] != '' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').html(data.result['manual_keywords_html']);

                if( data.result['display_manual_keywords'] == '1' ) {
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');

                    if( data.result['show_suggested_refresh'] == '1' && data.result['display_ideas_refresh'] == '1' ) {
                        $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    }
                    else{
                        $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    }
                }
                else{                    
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }
            } else {
                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').html('');

                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-your-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
            }

            // Display data in suggested keyword popin
            $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-loader').addClass('hidden');
            if( data.result['suggested_keywords_html'] != '' ) {
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data').html(data.result['suggested_keywords_html']);
                if( data.result['display_suggested_keywords'] == '1' ) {
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data').removeClass('hidden');

                    if( data.result['show_suggested_refresh'] == '1' && data.result['display_suggested_refresh'] == '1' ) {
                        $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                        $('#wtai-analysis-data-available-flag').val('1');
                    }
                    else{
                        $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    }

                    // Sort results
                    //var table_parent_suggested = $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data .wtai-keyword-table');
                    //keyword_analysis_filter( table_parent_suggested, 'no' );
                    show_or_hide_suggested_ideas();
                }
                else{
                    var empty_suggested_message = WTAI_KEYWORD_OBJ.emptySuggestedMessage;
                    if ( data.result['done_analysis'] == '1' ) {
                        empty_suggested_message = WTAI_KEYWORD_OBJ.emptySuggestedMessageWithAnalysis;
                        empty_suggested_message = empty_suggested_message.replace("%s", data.result['suggested_last_date_retrieval']);
                    }

                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').html( empty_suggested_message );

                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').show();
                    $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data').addClass('hidden');

                    if( data.result['show_suggested_refresh'] == '1' && data.result['display_suggested_refresh'] == '1' ) {
                        $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                        $('#wtai-analysis-data-available-flag').val('1');
                    }
                    else{
                        $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                    }
                }
            } else {
                var empty_suggested_message = WTAI_KEYWORD_OBJ.emptySuggestedMessage;
                if ( data.result['done_analysis'] == '1' ) {
                    empty_suggested_message = WTAI_KEYWORD_OBJ.emptySuggestedMessageWithAnalysis;
                    empty_suggested_message = empty_suggested_message.replace("%s", data.result['suggested_last_date_retrieval']);
                }

                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').html( empty_suggested_message );
                
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-empty-label').show();
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data').html('');
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
            }
        }

        setTimeout(function() {
            show_keyword_actions_tooltip();
        }, 300);
    }

    function handle_single_product_edit_state( is_premium ){
        var args = {
            is_premium : is_premium
        };

        $(document).trigger('wtai_single_edit_premium_state', args);
    }

    function handle_density_premium_state( is_premium ){
        var args = {
            is_premium : is_premium
        };

        $(document).trigger('wtai_single_edit_density_premium_state', args);
    }
    
    // UPDATE FOR KEYWORD 1.30.1
    function updateKeywordSinglePage( value , type ) {
        var keyword_used_for_action = value;
        var keyword_action = type;

        $('.wtai-target-wtai-keywords-list-wrapper').addClass('disabled');
        $('.wtai-keyword-analysis-button').addClass('disabled');
        $('#wtai-highlight').addClass('disabled');

        //get other existing keywords
        var existing_keywords = [];
        var k = 0;
        $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                existing_keywords[k] = keyword;
                k++;
            }
        });

        if( addKeyWordAJAX != null ){
            addKeyWordAJAX.abort();
        }

        var wtai_nonce = get_wp_nonce();

        $('.wtai-global-loader').addClass('wtai-is-active');
        $('.wtai-ai-logo').addClass('wtai-hide');

        handle_cta_states_for_keyword_analysis( 'disabled' );
        
        addKeyWordAJAX = $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: WTAI_KEYWORD_OBJ.ajax_url,
            data: {
                action: 'wtai_keyword_text',
                type: type,
                record_id:  $('#wtai-edit-post-id').attr('value'),
                record_type: $('#wtai-record-type').val(),
                value: value,
                existing_keywords: existing_keywords.join('|'),
                wtai_nonce: wtai_nonce
            },
            success: function(data) {
                if ( data.access == 1 ) {
                    var total_count = 0;
                    var keyword_semantic = '';
                    var keyword_list = '';
                    $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html('');
                    $.each(['keyword_input', 'keyword_ideas'], function (index,keyword_type ) { 
                        var count = 0;
                        
                        $.each(data.result[keyword_type], function(index, value ) {
                            if ( value ){
                                count++;
                                total_count++;
                                keyword_list = keyword_list+'<span class="result">'+value['name']+'</span>';
                               
                                //semantic
                                keyword_semantic = keyword_semantic + '<div class="wtai-semantic-keywords-wrapper-list">';
                                keyword_semantic = keyword_semantic+'<div class="wtai-header-label">'+value['name']+'</div>';
                                keyword_semantic = keyword_semantic+'<div class="wtai-semantic-list">';
                                $.each( value['semantic'], function( index, value ) {
                                    var semantic_active = '';
                                    if ( value['active'] ){
                                        semantic_active = 'wtai-active';
                                    } else {
                                        semantic_active = '';
                                    }  
                                    
                                    var perSemantic = '(0.00%)';
                                    if( $('.wtai-data-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').length ){
                                        $('.wtai-data-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword').each(function(){
                                            var semanticKeywordExisting = $(this).find('.wtai-keyword-name').text();

                                            if( value['semantic'] == semanticKeywordExisting ){
                                                perSemantic = $(this).find('.wtai-per').text();
                                            }
                                        });
                                    }

                                    var sk_tooltip_label = '';
                                    if( semantic_active != 'wtai-active' ){
                                        sk_tooltip_label = WTAI_KEYWORD_OBJ.maxSemanticKeywordMessage;
                                    }

                                    keyword_semantic =  keyword_semantic+'<span class="wtai-semantic-keyword '+semantic_active+'" title="'+sk_tooltip_label+'" ><span class="wtai-keyword-name">'+value['name']+'</span> <span class="wtai-per">'+perSemantic+'</span></span>';
                                });
                                keyword_semantic = keyword_semantic+'</div>';
                                keyword_semantic = keyword_semantic +'</div>';
                            }
                        });

                        if ( keyword_type == 'keyword_input' ){
                            $('.wtai-keyword-count-input-num').html( count );
                        }                        
                    });
                   
                    $('.wtai-data-semantic-keywords-wrapper-list-wrapper').html( keyword_semantic );

                    $('.wtai-keyword-count-num').html( total_count ); 

                    if ( parseInt(total_count) < WTAI_KEYWORD_OBJ.keyword_max ){
                        $('.wtai-keyword-input').prop('disabled', false );
                        show_keyword_input_tooltip('hide');
                    } else if ( parseInt(total_count) == WTAI_KEYWORD_OBJ.keyword_max ) { 
                        $('.wtai-keyword-input').prop('disabled', true );
                        show_keyword_input_tooltip('show');
                    }

                    // Correct button for keyword
                    if( keyword_action == 'remove' ){                    
                        // Lets look for the keyword in other sections and then change the label 
                        // ranked
                        $('.wtai-keyword-table-ranked-keywords tbody tr.wtai-keyword-tr').each(function(){
                            var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                            if( keyword_name.toLowerCase() == keyword_used_for_action.toLowerCase() ){
                                $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                                $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                                $(this).removeClass('wtai-tr-selected');
                            }
                        });

                        // competitor
                        $('.wtai-keyword-table-competitor-keywords tbody tr.wtai-keyword-tr').each(function(){
                            var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();
                            
                            if( keyword_name.toLowerCase() == keyword_used_for_action.toLowerCase() ){
                                $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                                $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                                $(this).removeClass('wtai-tr-selected');
                            }
                        });

                        // manual
                        $('.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').each(function(){
                            var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                            if( keyword_name.toLowerCase() == keyword_used_for_action.toLowerCase() ){
                                $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                                $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');
                                $(this).find('.wtai-keyword-action-trash').removeClass('disabled');
                                $(this).find('.wtai-keyword-action-trash').removeClass('wtai-not-allowed');

                                $(this).removeClass('wtai-tr-selected');
                            }
                        });

                        // suggested
                        $('.wtai-keyword-table-suggested-keywords tbody tr.wtai-keyword-tr').each(function(){
                            var keyword_name = $(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text();

                            if( keyword_name.toLowerCase() == keyword_used_for_action.toLowerCase() ){
                                $(this).find('.wtai-keyword-action-button-v2').removeClass('dashicons-minus');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('dashicons-plus-alt2');
                                $(this).find('.wtai-keyword-action-button-v2').addClass('wtai-keyword-action-button-add');
                                $(this).find('.wtai-keyword-action-button-v2').attr('data-type', 'add_to_selected');

                                $(this).removeClass('wtai-tr-selected');
                            }
                        });                        
                    }

                    if( $('#wtai-analysis-data-available-flag').val() == '1' ){
                        $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords .wtai-keyword-analysis-refresh-cta-wrap').removeClass('hidden');
                    }  

                    //reload suggested audience
                    reloadSuggestedAudience( 0 );

                    getKeywordOverallDensity();
                } else {
                    if ( $('.wtai-edit-product-line' ).find('#message').length > 0  ){
                        $('.wtai-edit-product-line' ).find('#message').remove();
                    }
                    $('<div id="message" class="error notice is-dismissible"><p>'+WTAI_KEYWORD_OBJ.access_denied+' </p></div>').insertAfter( $('.wtai-edit-product-line' ).find('.wp-header-end') );
                }
                

               //for the update keywords only
               $('.wtai-target-wtai-keywords-list-wrapper').removeClass('disabled');

               $('#wtai-highlight').removeClass('disabled');
               $('.wtai-global-loader').removeClass('wtai-is-active');
               $('.wtai-ai-logo').removeClass('wtai-hide');
               maybeDisableKeywordInput();
               updateKeywordCount();
               handle_cta_states_for_keyword_analysis( 'enable' );

               show_keyword_actions_tooltip();

               //set semantic active count
               setSemanticActiveCount();

               addKeyWordAJAX = null;

               if( $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-table-parent-wrap').find('tbody.wtai-keyword-tbody').find('tr.wtai-keyword-tr').length <= 0 ){
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-empty-label').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-api-data').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap.wtai-selected-keywords .wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');
                }
            }            
        });
    }

    $(document).on('click', '.wtai-keyword-analysis-toggle', function(){
        var parent = $(this).closest('.wtai-keyword-analysis-content-wrap');
        var state = $(this).attr('data-state');
        $(this).removeClass('wtai-state-shown');
        $(this).removeClass('wtai-state-hidden');
        if( state == 'shown' ){
            $(this).attr('data-state', 'hidden');
            $(this).addClass('wtai-state-hidden');
            parent.find('.wtai-keyword-analysis-content-data').hide();
        }
        else{
            $(this).attr('data-state', 'shown');
            $(this).addClass('wtai-state-shown');
            parent.find('.wtai-keyword-analysis-content-data').show();
        }
    });

    $(document).on('click', '.wtai-start-ai-analysis-btn', function(){
        if (  ! $(this).hasClass('disabled') ) {
            if( keywordStartAnalysisAJAX != null ){
                keywordStartAnalysisAJAX.abort();
            }

            if( $('.wtai-keyword .wtai-error-msg').length ) {
                $('.wtai-keyword .wtai-error-msg').remove();
            }

            $('.wtai-keyword-analysis-content-bottom-section').animate({ scrollTop: 0 }, 'fast');

            show_hide_global_loader('show');

            var btn = $(this);

            var refresh = "yes";
            var nogenerate = "no";
            var language_code = $('.wtai-keyword-location-code').val();

            $('.wtai-keyword-table .wtai-volume-filter-all').prop( 'checked', true );
            $('.wtai-keyword-table .wtai-difficulty-filter').prop( 'checked', true );
            $('.wtai-keyword-table .volume-sort-desc').prop( 'checked', false );
            $('.wtai-keyword-table .difficulty-sort-low').prop( 'checked', false );

            var sa_keywords = [];
            var k = 0;
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
                var keyword = $(this).find('.wtai-keyword-name').text();
                if( keyword ){
                    sa_keywords[k] = keyword;
                    k++;
                }
            });

            var manual_keywords = [];
            var k = 0;
            $('.wtai-keyword-table-your-keywords tbody tr').each(function(){
                var keyword = $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();
                if( keyword ){
                    manual_keywords[k] = keyword;
                    k++;
                }
            });

            window.keywordIdeasStartAnalysis = true;
            window.keywordIdeasSource = 'all';
            window.keywordIdeasSourceType = 'all';
            
            var wtai_nonce = get_wp_nonce();
            
            $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);

            progressbar_keyword_analysis( 'show', 1, WTAI_KEYWORD_OBJ.startKeywordAnalysisMessage, false );

            $('.wtai-keyword-analysis-refresh-cta-wrap').addClass('hidden');

            var data = {
                action: 'wtai_start_ai_keyword_analysis',
                record_id: $('#wtai-edit-post-id').attr('value'), 
                record_type: $('#wtai-record-type').val(),
                keywords: sa_keywords.join('|'), 
                manual_keywords: manual_keywords.join('|'), 
                language_code: language_code, 
                refresh: refresh, 
                nogenerate: nogenerate,
                wtai_nonce: wtai_nonce,
                refresh_type: 'all',
            };

            keywordStartAnalysisAJAX = $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_KEYWORD_OBJ.ajax_url,
                data: data,
                beforeSend: function() {
                    handle_cta_states_for_keyword_analysis( 'disabled' );                    

                    // initialize keywords section
                    $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-loader').removeClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-empty-label').addClass('hidden');
                    $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').html('');
                    $('.wtai-keyword-analysis-content-wrap .wtai-keyword-analysis-api-data').addClass('hidden');
                },
                success: function( data ){ 
                    if( data.result.analysis_request_id != '' ){
                        // Update credit;
                        if( data.available_credit_label != '' ){
                            $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                        }

                        // Handle premium state;
                        var is_premium = data.result['is_premium'];
                        handle_single_product_edit_state( is_premium );
                        handle_density_premium_state( is_premium );

                        window.keywordIdeasStartAnalysis = true;
                        window.keywordIdeasQueueRequestId = data.result.analysis_request_id;
                        window.keywordIdeasSource = 'all';
                        window.keywordIdeasSourceType = 'all';
                    }
                    else{  
                        window.keywordIdeasQueueRequestId = '';
                        window.keywordIdeasSource = 'all';
                        window.keywordIdeasStartAnalysis = false;
                        window.keywordIdeasSourceType = 'all'; 
                                            
                        handle_cta_states_for_keyword_analysis( 'enabled' );

                        //display new fetched data
                        if( data.result && data.result.status_code == '20000' ){
                            render_keyword_html_sections( data, 'all' );
                        } else {
                            var keywordErrorMessage = WTAI_KEYWORD_OBJ.keyword_ideas_msg;
                            if( data.error != '' ){
                                keywordErrorMessage = data.error;
                            }
                            
                            var divAlert = '<div class="wtai-error-msg"><div>' + keywordErrorMessage + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                            if( !$('.wtai-keyword .wtai-error-msg').length ) {
                                $(divAlert).appendTo('.wtai-keyword');
                            } else {
                                $('.wtai-keyword .wtai-error-msg').remove();
                                $(divAlert).appendTo('.wtai-keyword');
                                $('.wtai-keyword .wtai-error-msg').fadeIn();
                            }

                            $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);

                            progressbar_keyword_analysis( 'hide', 1, WTAI_KEYWORD_OBJ.startKeywordAnalysisMessage, false );
                            
                            // display error
                            getKeyWordIdeas( 'no', 'yes', 'no', 'no', 'yes' );
                        }

                        // Update credit;
                        if( data.available_credit_label != '' ){
                            $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                        }

                        // Handle premium state;
                        var is_premium = data.result['is_premium'];
                        handle_single_product_edit_state( is_premium );
                        handle_density_premium_state( is_premium );
                    }

                    keywordStartAnalysisAJAX = null;
                }
            });
        }
    });

    $(document).on('click', '.wtai-keyword-analysis-refresh-cta', function(){
        if (  ! $(this).hasClass('disabled') ) {
            if( keywordStartAnalysisAJAX != null ){
                keywordStartAnalysisAJAX.abort();
            }

            if( $('.wtai-keyword .wtai-error-msg').length ) {
                $('.wtai-keyword .wtai-error-msg').remove();
            }

            $('.wtai-global-loader').addClass('wtai-is-active');
            $('.wtai-ai-logo').addClass('wtai-hide');

            var btn = $(this);
            var refresh = "yes";
            var nogenerate = "no";
            var language_code = $('.wtai-keyword-location-code').val();
            var refresh_type = $(this).attr('data-type');

            var main_wrap = $(this).closest('.wtai-keyword-analysis-content-wrap');

            main_wrap.find('.wtai-keyword-table .wtai-volume-filter-all').prop( 'checked', true );
            main_wrap.find('.wtai-keyword-table .wtai-difficulty-filter').prop( 'checked', true );
            main_wrap.find('.wtai-keyword-table .volume-sort-desc').prop( 'checked', false );
            main_wrap.find('.wtai-keyword-table .difficulty-sort-low').prop( 'checked', false );

            window.keywordIdeasStartAnalysis = true;
            window.keywordIdeasSource = 'refresh';
            window.keywordIdeasSourceType = refresh_type;

            $('.wtai-keyword-analysis-progress-loader').attr('data-progress', 1);

            progressbar_keyword_analysis_mini( 'show', 1, WTAI_KEYWORD_OBJ.refreshingKeywordAnalysisMessage, false, refresh_type );

            var sa_keywords = [];
            var k = 0;
            $('.wtai-keyword-analysis-options-wrap .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
                var keyword = $(this).find('.wtai-keyword-name').text();
                if( keyword ){
                    sa_keywords[k] = keyword;
                    k++;
                }
            });

            var manual_keywords = [];
            var k = 0;
            $('.wtai-keyword-table-your-keywords tbody tr').each(function(){
                var keyword = $(this).find('td.wtai-col-keyword .wtai-column-keyword-name-text').text();
                if( keyword ){
                    manual_keywords[k] = keyword;
                    k++;
                }
            });

            var wtai_nonce = get_wp_nonce();

            handle_cta_states_for_keyword_analysis( 'disabled' );

            var data = {
                action: 'wtai_start_ai_keyword_analysis',
                record_id: $('#wtai-edit-post-id').attr('value'), 
                record_type: $('#wtai-record-type').val(), 
                keywords: sa_keywords.join('|'), 
                manual_keywords: manual_keywords.join('|'), 
                language_code: language_code, 
                refresh: refresh, 
                nogenerate: nogenerate,
                wtai_nonce: wtai_nonce,
                refresh_type: refresh_type,
            };

            keywordStartAnalysisAJAX = $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: WTAI_KEYWORD_OBJ.ajax_url,
                data: data,
                beforeSend: function() {
                    // initialize keywords section
                    if( refresh_type == 'selected-keywords' || refresh_type == 'competitor-keywords' ){
                        if( refresh_type == 'competitor-keywords' ){
                            if( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-count').length && parseInt( $('.wtai-keyword-max-count-wrap-popin .wtai-keyword-count').text() ) > 0 ){
                                $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-empty-label').addClass('hidden');
                                $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-api-data').html('');
                                $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-api-data').addClass('hidden');
                            }
                            else{
                                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords').find('.wtai-keyword-analysis-empty-label').addClass('hidden');
                                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords').find('.wtai-keyword-analysis-api-data').html('');
                                $('.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords').find('.wtai-keyword-analysis-api-data').addClass('hidden');
                            }
                        } else{
                            $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-empty-label').addClass('hidden');
                            $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-api-data').html('');
                            $('.wtai-keyword-analysis-content-wrap.wtai-has-competitive-analysis').find('.wtai-keyword-analysis-api-data').addClass('hidden');
                        }
                    }
                    else{
                        if( refresh_type == 'suggested-keywords' ){
                            main_wrap.find('.wtai-keyword-analysis-api-data').html('');
                        }

                        if( $('.wtai-keyword-table.wtai-keyword-table-your-keywords tbody tr.wtai-keyword-tr').length > 0 ){
                            $('.wtai-keyword-analysis-content-wrap.wtai-keyword-ideas-group').find('.wtai-keyword-analysis-empty-label').addClass('hidden');
                        }
                        
                        $('.wtai-keyword-analysis-content-wrap.wtai-keyword-ideas-group').find('.wtai-keyword-analysis-api-data').addClass('hidden');
                    }
                },
                success: function( data ){ 
                   

                    if( data.result.detailed_result.queueRequestId != '' && data.error == '' ){
                        if( data.available_credit_label != '' ){
                            $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                        }

                        // Handle premium state;
                        var is_premium = data.result['is_premium'];
                        handle_single_product_edit_state( is_premium );
                        handle_density_premium_state( is_premium );
                        
                        window.keywordIdeasStartAnalysis = true;
                        window.keywordIdeasQueueRequestId = data.result.detailed_result.queueRequestId;
                        window.keywordIdeasSource = 'refresh';
                        window.keywordIdeasSourceType = refresh_type;
                    }
                    else{     
                        window.keywordIdeasQueueRequestId = '';
                        window.keywordIdeasSource = 'all';
                        window.keywordIdeasStartAnalysis = false;
                        window.keywordIdeasSourceType = 'all'; 

                        handle_cta_states_for_keyword_analysis( 'enabled' );

                        //display new fetched data
                        if( data.result && data.result.status_code == '20000' ){
                            render_keyword_html_sections( data, refresh_type );
                        } else {
                            progressbar_keyword_analysis( 'hide', 1, '' );
                            progressbar_keyword_analysis_mini( 'hide', 1, '' );

                            var keywordErrorMessage = WTAI_KEYWORD_OBJ.keyword_ideas_msg;
                            if( data.error != '' ){
                                keywordErrorMessage = data.error;
                            }
                            
                            var divAlert = '<div class="wtai-error-msg"><div>' + keywordErrorMessage + '<span class="wtai-btn-close-error-msge"></span></div></div>';

                            if( !$('.wtai-keyword .wtai-error-msg').length ) {
                                $(divAlert).appendTo('.wtai-keyword');
                            } else {
                                $('.wtai-keyword .wtai-error-msg').remove();
                                $(divAlert).appendTo('.wtai-keyword');
                                $('.wtai-keyword .wtai-error-msg').fadeIn();
                            }
                            
                            // display error
                            getKeyWordIdeas( 'no', 'yes', 'no', 'no', 'yes' );
                        }

                        // Update credit;
                        if( data.available_credit_label != '' ){
                            $('.wtai-credit-available-wrap .wtai-credit-available').html( data.available_credit_label );
                        }

                        // Handle premium state;
                        var is_premium = data.result['is_premium'];
                        handle_single_product_edit_state( is_premium );
                        handle_density_premium_state( is_premium );
                    }

                    keywordStartAnalysisAJAX = null;
                }
            });
        }
    });

    var wtaKeywordScrollTimer = null;
    var wtaDoingKeywordScroll = false;
    $('.wtai-slide-right-text-wrapper .wtai-keyword').scroll( function(){     
        wtaDoingKeywordScroll = true;

        if(wtaKeywordScrollTimer !== null) {
            clearTimeout(wtaKeywordScrollTimer);        
        }

        wtaKeywordScrollTimer = setTimeout(function() {
            wtaDoingKeywordScroll = false;
        }, 500);
    });
    $(document).on('mouseup', function(e){
        var kw_con = $('.wtai-keyword');
        var kw_btn_lp = $('.wtai-link-preview');
        var kw_btn_hist = $('.wtai-history');
        var kw_btn_close = $('.wtai-slide-right-text-wrapper .wtai-close');

        if ( !kw_btn_hist.is(e.target) && !kw_btn_close.is(e.target) && kw_con.has(e.target).length === 0 && $('body').hasClass('wtai-keyword-open') &&
            !kw_btn_lp.is(e.target) && kw_btn_lp.has(e.target).length === 0 && $('body').hasClass('wtai-keyword-open') && wtaDoingKeywordScroll == false ){
            
            $('.wtai-btn-close-keyword').trigger('click');
        }
    });

    /*Close keyword popup*/
    $(document).on('click', '.wtai-slide-right-text-wrapper  .wtai-btn-close-keyword', function(){ 
        if( $('.wtai-keyword-analysis-popin-right').hasClass('wtai-process-ongoing') ){
            return;
        }       

        $('.wtai-slide-right-text-wrapper').removeClass('wtai-keyword-open');
        $('body').removeClass('wtai-keyword-open'); 
        $('.wtai-keyword .wtai-keyword-input').removeClass('wtai-border');
          
        $('.wtai-keyword .wtai-error-msg').remove();
    });

    // Event to get keyword density
    $(document).on('wtai_get_keyword_overall_density', function(e){
        e.stopImmediatePropagation();

        getKeywordOverallDensity();
    });

    function getKeywordOverallDensity(){
        var density = 0;
        var semanticDensity = 0;
        var keywords = [];
        var k = 0;
        $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                keywords[k] = keyword;
                k++;
            }
        });

        var semantic_keywords = [];
        var k = 0;
        $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').each(function(){
            var keyword = $(this).find('.wtai-keyword-name').text();
            if( keyword ){
                semantic_keywords[k] = keyword;
                k++;
            }
        });

        if( keywords.length || semantic_keywords.length ){
            //get concatted strings
            var allParagraphs = '';
            var totalWordCount = 0;
            $('#postbox-container-2').find('.wtai-metabox').each(function(){
                var data_object = $(this);
                var type = data_object.data('type');
                var content = '';
                //limit density computation for product excerpt and description only
                if ( type == 'product_description' || type == 'product_excerpt' || type == 'category_description' ){
                    
                    switch( type ){
                        case 'product_description':
                        case 'product_excerpt':
                        case 'category_description':
                            var id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup').attr('id');                            
                            if ( id ){
                                if( tinymce.get(id) ){
                                    content = tinymce.get(id).getContent({format: 'text'});
                                }
                            }
                        break;    
                        default:
                            var id = data_object.find('.wtai-columns-3').find('.wtai-wp-editor-setup-others').attr('id');
                            if ( id ){
                                if( tinymce.get(id) ){
                                    content = tinymce.get(id).getContent({format: 'text'});
                                }
                            }
                            break;
                    }

                    var words_count_val = 0;
                    if( $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper .wtai-char-counting').find('.word-count').length ){
                        words_count_val = $('#wtai-product-details-'+type).find('.wtai-generate-value-wrapper .wtai-char-counting').find('.word-count').attr('data-count');

                        if( isNaN( words_count_val ) ){
                            words_count_val = 0;
                        }
                    }

                    totalWordCount += parseInt( words_count_val );

                    allParagraphs += ' ' + content;
                }                
            });

            //process per keyword density
            if( keywords.length ){
                $('.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result').each(function(){
                    var keyword = $(this).find('.wtai-keyword-name').text();
                    if( keyword ){
                        var keywordSingleArray = [ keyword ];
                        var keyworddensity = computeDensity( keywordSingleArray, allParagraphs, totalWordCount );

                        $(this).find('.wtai-per').html( '(' + keyworddensity + '%' + ')' );
                    }
                });    
            }

            if( semantic_keywords.length ){
                $('.wtai-semantic-keywords-wrapper-list-wrapper .wtai-semantic-keyword.wtai-active').each(function(){
                    var keyword = $(this).find('.wtai-keyword-name').text();
                    if( keyword ){
                        var keywordSingleArray = [ keyword ];
                        var keyworddensity = computeDensity( keywordSingleArray, allParagraphs, totalWordCount );

                        $(this).find('.wtai-per').html( '(' + keyworddensity + '%' + ')' );
                    }
                });    
            }

            if( $('.wtai-percentage.keyword-density-perc').length ){
                if( keywords.length ){
                    density = computeDensity( keywords, allParagraphs, totalWordCount );
                }

                if( semantic_keywords.length ){
                    semanticDensity = computeDensity( semantic_keywords, allParagraphs, totalWordCount );
                }
            }
        }

        if( $('.wtai-percentage.keyword-density-perc').length ){
            if( isNaN( density ) ){
                density = 0;
            }
    
            if( isNaN( semanticDensity ) ){
                semanticDensity = 0;
            }
    
            $('.wtai-percentage.keyword-density-perc').html( density + '%' );
            $('.wtai-percentage.wtai-semantic-keyword-density-perc').html( semanticDensity + '%' );
        }
    }

    function computeDensity( keywords, allParagraphs, totalWordCount ){
        var sortedKeywords = sortKeywords(keywords);

        var totalCount = 0;
        paragraphCopy = allParagraphs;

        sortedKeywords.map((word) => {
            try{
                var regex = new RegExp(word, 'mgi');
                var matches = paragraphCopy.match(regex);
                paragraphCopy = paragraphCopy.replace(regex, '');
                
                if (matches) {
                    totalCount += matches.length * wtaiGetWordsArray(word).length;
                }
            }
            catch(e){
            }
        });

        var density = 0;
        if( totalCount > 0 && totalWordCount > 0 ){
            density = ( totalCount / totalWordCount ) * 100;
        }

        density = parseFloat(density,10).toFixed(2);
        
        return density;
    }

    function sortKeywords( keywords ){
        var sortedKeywords = [];
        $.each(  keywords, function(k, keyword ){
            var words = wtaiGetWordsArray( keyword );
            var indexK = words.length;
            sortedKeywords[k] = {
                'name' : keyword,
                'index' : indexK
            };
        });

        var list = sortedKeywords.sort((a,b) => b.index - a.index).map((keyword) => keyword.name);

        return list;
    }

    // Moved to admin-keyword.js.
    $(document).on('blur', '.wtai-keyword .wtai-keyword-input', function(e){
        this.value = this.value.toLowerCase();
    });

    // Moved to admin-keyword.js.
    $(document).on('change', '.wtai-keyword .wtai-keyword-input', function(e){
        this.value = this.value.toLowerCase();
    });

    // Moved to admin-keyword.js.
    $(document).on('keypress', '.wtai-keyword .wtai-keyword-input', function(e){
        this.value = this.value.toLowerCase();

        var char = String.fromCharCode(e.which);
        if (/[><"/]/.test(char)) {
            e.preventDefault();
        }

        if ( e.keyCode === 44 || e.keyCode === 13 ) {
            var value = $(this).val().trim();

            if( value == '-' || value == '*' || value == '#' || value == '$' || value == '@' || value == '.' || value == '>'  || value == '<' || value == '|' || value == '%' || value == '"' || value == '\'' ){
                value = '';
            }

            if ( value !== '' ){
                $(this).removeClass('wtai-border');
                updateKeywordSinglePageDrawer( value );
                updateKeywordSinglePageLeftCol();
                $(this).val('');
                $('.wtai-keyword-input-filter-wrap .wtai-char-count').text('0');

            } else {
                $(this).val('');
                $(this).addClass('wtai-border');
            }

            maybeDisableGetDataButton();
            
            e.preventDefault();
        }
    });

    // Moved to admin-keyword.js.
    // UPDATE FOR KEYWORD 1.30.1
    $(document).on('click', '.wtai-keyword-selection .wtai-target-wtai-keywords-list-wrapper .result', function(){
        var inbox_event = $(this);
        var value = inbox_event.find('.wtai-keyword-name').html();

        inbox_event.remove();

        $('.wtai-keyword-table-selected-keywords tbody.wtai-keyword-tbody tr').each(function() {
            if ($(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text() === value) {
                $(this).remove();
            } 
        });

        //update semantic keywords
        updateKeywordSinglePageLeftCol();
        updateKeywordSinglePage( '' , 'add' );

        maybeDisableKeywordInput();
        updateKeywordCount();
        maybeDisableGetDataButton();
    });

    // Moved to admin-keyword.js.
    // UPDATE FOR KEYWORD 1.30.1
    $(document).on('click', '.wtai-target-keywords-wrapper .wtai-target-wtai-keywords-list-wrapper .result', function(){
        var inbox_event = $(this);
        var value = inbox_event.find('.wtai-keyword-name').html();

        inbox_event.remove();

        $('.wtai-keyword-table-selected-keywords tbody.wtai-keyword-tbody tr').each(function() {
            if ($(this).find('.wtai-col-keyword .wtai-column-keyword-name-text').text() === value) {
                $(this).remove();
            } 
        });

        $('.wtai-keyword-selection .wtai-target-wtai-keywords-list-wrapper .result').each(function() {
            if ( $(this).find('.wtai-keyword-name').text() == value ) {
                $(this).remove();
            }
        });

        removeHighlightkeywords();
        updateKeywordSinglePage( value , 'remove' );
    });

    $(document).on('click', '#message .notice-dismiss', function(e){
        $(this).parent().remove();
        e.preventDefault();
    });

    $(document).on('click', '.wtai-btn-close-error-msge', function(){
        $(this).closest('.wtai-error-msg').addClass('fadeOut');
        setTimeout(function(){
            $('.wtai-keyword .wtai-error-msg').remove();
        }, 500);
    });

    $(document).on('click', '.keyword-ideas-show-more-btn', function(e){
        e.preventDefault();

        var pageNo = $(this).attr('data-page-no');
        var nextPageNo = parseInt( pageNo ) + 1;
        $(this).attr('data-page-no', nextPageNo);

        getKeyWordIdeas( 'no', 'yes', 'yes' );
    });

    // Moved to admin-keyword.js.
    function show_or_hide_suggested_ideas(){
        var wrapper = $('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords');
        if( wrapper.find('.wtai-keyword-table tbody tr').length <= 0 ){
            wrapper.find('.wtai-keyword-ideas-no-more-data-wrap').hide();
            wrapper.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();
            wrapper.find('.wtai-keyword-analysis-empty-label').show();
        }
        else{
            var has_custom_filter = false;
            if( wrapper.find('.wtai-volume-difficulty-ico').hasClass('wtai-active') ){
                has_custom_filter = true;
            }

            wrapper.find('.wtai-keyword-ideas-no-more-data-wrap').hide();
            wrapper.find('.wtai-keyword-analysis-empty-label').hide();
            wrapper.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();

            if( wrapper.find('.wtai-load-more-wrap').text().trim() == '' && wrapper.find('table tr.wtai-keyword-tr').length > 10 && has_custom_filter ){
                wrapper.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').show();
            } else {
                if( wrapper.find('.wtai-load-more-wrap').text().trim() == '' ){
                    wrapper.find('.wtai-keyword-ideas-no-more-data-wrap').show();
                }
            }
        }
    }

    // Moved to admin-keyword.js.
    function sort_static_keyword_table( table_parent, th_index, sort_direction ){
        var parent_wrap = table_parent.closest('.wtai-keyword-analysis-content-wrap');

        var table_rows = table_parent.find('tbody tr.wtai-keyword-tr').toArray().sort(table_sort_comparer(th_index.index()));
        if ( sort_direction == 'desc' ) {
            table_rows = table_rows.reverse();
        }

        table_parent.find('tbody.wtai-keyword-tbody').html("");
        for (var i = 0; i < table_rows.length; i++) {
            table_parent.find('tbody.wtai-keyword-tbody').append(table_rows[i]);
        }

        table_assign_page_numbers(table_rows);

        table_parent.find('tbody tr.wtai-keyword-tr').addClass('wtai-keyword-tr-hidden');

        var has_data_found = false;
        table_parent.find('tbody tr.wtai-keyword-tr').each(function(index){
            if ( parseInt( $(this).attr('data-page-no') ) == 1 ) {
                $(this).removeClass('wtai-keyword-tr-hidden');
                has_data_found = true;
            }
        });

        parent_wrap.find('.wtai-keyword-ideas-no-more-data-wrap').hide();

        // Check if page 1 is found
        if( has_data_found == false ){
            parent_wrap.find('.wtai-load-more-wrap').addClass('hidden');
            parent_wrap.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').show();
        }
        
        // Lets check if page 2 is available
        if( table_parent.find('tbody tr.wtai-keyword-tr[data-page-no="2"]').length ){
            parent_wrap.find('.wtai-load-more-wrap').removeClass('hidden');
            parent_wrap.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').hide();
        }
        else{
            parent_wrap.find('.wtai-load-more-wrap').addClass('hidden');
            parent_wrap.find('.wtai-keyword-ideas-no-more-data-custom-filter-wrap').show();
        }

        //reset current page to one for the load more
        table_parent.closest('.wtai-keyword-analysis-content-wrap').find('.wtai-load-more-cta').attr('data-current-page-no', 1);
    }

    // Moved to admin-keyword.js.
    $(document).on('click', '.wtai-volume-difficulty-ico', function(){
        if( $(this).hasClass('disabled') ){
            return;
        }       

        $(this).closest('.wtai-sort-volume-difficulty-select').find('.wtai-volume-difficulty-dropdown').toggleClass('wtai-active');

        if( $(this).closest('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords').length 
            && $(this).closest('.wtai-sort-volume-difficulty-select').find('.wtai-volume-difficulty-dropdown').hasClass('wtai-active') == true ){
            
            var suggested_height = $(this).closest('.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords').height();
            if( parseFloat( suggested_height ) < 300 ){
                $('.wtai-keyword-analysis-content-bottom-section').addClass('add-spacer');
            }
            else{
                $('.wtai-keyword-analysis-content-bottom-section').removeClass('add-spacer');
            }
        }
        else{
            $('.wtai-keyword-analysis-content-bottom-section').removeClass('add-spacer');
        }
    });

    // Moved to admin-keyword.js.
    $(document).on('click', '.wtai-difficulty-filter-all', function(){
        var checkAll = $(this).is(':checked');
        $('.wtai-difficulty-filter').prop('checked', checkAll);

        $('.keyword-ideas-show-more-btn').attr('data-page-no', '1');

        getKeyWordIdeas('no', 'yes');        
    });

    // Moved to admin-keyword.js.
    $(document).on('click', '.wtai-difficulty-filter', function(){
        if( $('.wtai-difficulty-filter:checked').length >= 3 ){
            $('.wtai-difficulty-filter-all').prop('checked', true);
        }
        else{
            $('.wtai-difficulty-filter-all').prop('checked', false);
        }        
    });

    // Moved to admin-keyword.js.
    $(document).on('click', '.wtai-difficulty-filter, .wtai-volume-filter', function(){
        var table_parent = $(this).closest('.wtai-keyword-table');

        wtaKeywordScrollTimer = setTimeout(function() {
            keyword_analysis_filter( table_parent, 'yes' );
        }, 500);
    });

    // Moved to admin-keyword.js.
    function keyword_analysis_filter( table_parent, save_filter_and_sort ){
        var keyword_type = table_parent.attr('data-keyword-type');

        var difficultyFilter = [];
        table_parent.find('.wtai-difficulty-filter').each(function(){
            if( $(this).is(':checked') ){
                difficultyFilter.push( $(this).val() );
            }
        });

        // Lets consider it as display all.
        if( table_parent.find('.wtai-difficulty-filter:checked').length <= 0 ){
            table_parent.find('.wtai-difficulty-filter').each(function(){
                difficultyFilter.push( $(this).val() );
            });
        }

        var volumeFilter = 'all';
        if( table_parent.find('.wtai-volume-filter:checked').length ){
            volumeFilter = table_parent.find('.wtai-volume-filter:checked').val();
        }

        if( keyword_type == 'ranked' || keyword_type == 'competitor' ){
            if( difficultyFilter.length <= 0 ){
                difficultyFilter.push( 'NONE_SELECTED' );
            }

            var table_rows = table_parent.find('tbody tr.wtai-keyword-tr').toArray();

            //lets look at the table row data
            for (var i = 0; i < table_rows.length; i++) {
                var row_elem = $(table_rows[i]);
                var volume_filter_value = row_elem.find('td.wtai-col-volume').attr('data-filter-value');
                var difficulty_filter_value = row_elem.find('td.wtai-col-difficulty').attr('data-filter-value');

                var volume_filter_passed = false;
                if( volumeFilter != 'all' ){
                    if( volumeFilter == '0-10000' && volume_filter_value >= 0 && volume_filter_value <= 10000 ){
                        volume_filter_passed = true;
                    }
                    else if( volumeFilter == '10001-50000' && volume_filter_value >= 10001 && volume_filter_value <= 50000 ){
                        volume_filter_passed = true;
                    } else if( volumeFilter == '50001' && volume_filter_value >= 50001 ){
                        volume_filter_passed = true;
                    }
                }
                else{
                    volume_filter_passed = true;
                }

                if( difficultyFilter.includes(difficulty_filter_value) && volume_filter_passed ){
                    // filter passed
                    $(table_rows[i]).removeClass('wtai-no-match');
                }
                else{
                    $(table_rows[i]).addClass('wtai-no-match');
                }

            }

            // maybe sort the fields if sort was selected
            var sort_field = table_parent.attr('data-sort-field');
            var sort_direction = table_parent.attr('data-sort');
            var th_index = null;
            if( sort_field == 'relevance' ){
                th_index = table_parent.find('thead th.wtai-col-keyword');
            }
            else{
                th_index = table_parent.find('thead th.wtai-col-' + sort_field);
            }

            sort_static_keyword_table( table_parent, th_index, sort_direction );

            if( save_filter_and_sort == 'yes' ){
                save_keyword_analysis_sort_filter( keyword_type );
            }

            shouldActivateKeywordFilter( keyword_type );
        }
        else{
            //suggested
            keyword_analysis_sort_filter_ajax( keyword_type, 'no', save_filter_and_sort );
        }
    }

    // Moved to admin-keyword.js.
    function shouldActivateKeywordFilter( keyword_type ){
        var main_wrapper_class = '';
        if( keyword_type == 'ranked' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-current-rank-keywords';
        }
        if( keyword_type == 'competitor' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-competitor-keywords';
        }
        if( keyword_type == 'suggested' ){
            main_wrapper_class = '.wtai-keyword-analysis-content-wrap.wtai-suggested-keywords';
        }

        if( main_wrapper_class == '' ){
            return;
        }

        var parent_wrap = $(main_wrapper_class);

        if( parent_wrap.length <= 0 ){
            return;
        }

        parent_wrap.find('.wtai-volume-difficulty-ico').removeClass('wtai-active');
        
        var hasCustomFilter = false;
        
        if( parent_wrap.find('.wtai-difficulty-filter:checked').length < 3 ){
            hasCustomFilter = true;
        }
        if( parent_wrap.find('.wtai-volume-filter:checked').val() != 'all' ){
            hasCustomFilter = true;
        }

        if( hasCustomFilter == true ) {
            parent_wrap.find('.wtai-volume-difficulty-ico').addClass('wtai-active');
            parent_wrap.find('.wtai-keyword-by-relevance-label').hide();
        }
        else{
            parent_wrap.find('.wtai-volume-difficulty-ico').removeClass('wtai-active');
            parent_wrap.find('.wtai-keyword-by-relevance-label').show();
        }

        return hasCustomFilter;
    }

    function reloadSuggestedAudience( clearAllText ){
        var args = {
            clearAllText: clearAllText
        }

        $(document).trigger('wtai_reload_suggested_audience', args);
    }

    function removeHighlightkeywords() {
        $(document).trigger('wtai_remove_highlight_keywords');
    }
});