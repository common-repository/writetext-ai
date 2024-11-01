/* jshint -W065 */
/* jshint -W117 */
/* jshint -W119 */
/* jshint -W069 */
/* jshint -W024 */
/* jshint -W062 */
/* jshint -W116 */
/* jshint -W004 */

jQuery(document).ready(function( $ ){
    window.WTAStreamConnected = false;
    window.wtaStreamData = [];
    window.wtaStreamQueueData = [];

    var wtaStream = function(){
        var connection = null;
        var disconnected = false;
        var accessToken = WTAI_STREAMING_OBJ.accessToken;
        var currentUserEmail = WTAI_STREAMING_OBJ.userEmail;
        var retryReconnectAttempt = 0;
        var enableMaxRetryAttempt = false;
        var autoReconnect = true;
        var maxRetryAttempt = 10;
        var enableStreamingDebug = WTAI_STREAMING_OBJ.enableStreamingDebug;
        var streamDebugField = WTAI_STREAMING_OBJ.streamDebugField;
        var countArray = [];
        var recordedCountArray = [];
        var recordedPartialTextArray = [];
        var recordedDataArray = [];
        var stopTagFlagArray = [];
        var entitiesEncoded = {
            '&lt;': '<',
            '&gt;': '>',
            '&amp;': '&',
            '&quot;': '"',
            '&#39;': '\''
        };
        var reservedTags = [
            'ul', 'li', 'ol', 'p', 'blockquote', 'strong','em','pre', 'del', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'a', 'span', 'u', 'hr', 'br', '!--more--'
        ];
        var  timerId;
        
        var init = function(){
            connectTostream();
            initCountArray();
        };

        var maybeReconnect = function(){
            if( true === disconnected && null !== connection && 'Connected' !== connection.connectionState && 'Connecting' !== connection.connectionState ){
                //connectTostream();
                connection.start();
            }            
        };

        function connectTostream(){
            connection = new signalR.HubConnectionBuilder().withUrl( WTAI_STREAMING_OBJ.connectionBaseURL + 'text/hub/notification', { accessTokenFactory: () => accessToken }).build();
            connection.start()
            .then(() => onConnected(connection))
            .catch(error => onConnectionError(error));

            bindConnectionMessageGenerate(connection);

            window.wtaStreamData = [];
            window.wtaStreamQueueData = [];
        }

        function onConnected(connection) {
            console.log('connection started');

            if( '1' === enableStreamingDebug ){
                console.log('connection started');
            }

            disconnected = false;
            
            connection.send('broadcastMessage', '_SYSTEM_', 'Connected');
    
            window.WTAStreamConnected = true;

            if( $('.api-connection-dot').length ){
                $('.api-connection-dot').removeClass('connected');
                $('.api-connection-dot').removeClass('disconnected');
                $('.api-connection-dot').addClass('connected');

                $('.api-connection-dot').attr('title', WTAI_STREAMING_OBJ.connectedText);
            }

            $(document).trigger('wtaStreamingConnected');
        }

        function onConnectionError(error) {
            if (error && error.message) {
                console.error(error.message);
            }

            disconnected = true;
            
            window.WTAStreamConnected = false;
            window.wtaStreamData = [];
            window.wtaStreamQueueData = [];

            if( $('.api-connection-dot').length ){
                $('.api-connection-dot').removeClass('connected');
                $('.api-connection-dot').removeClass('disconnected');
                $('.api-connection-dot').addClass('disconnected');

                $('.api-connection-dot').attr('title', WTAI_STREAMING_OBJ.disconnectedText);
            }

            if( autoReconnect ){                
                var doConnect = true;
                if( enableMaxRetryAttempt ){            
                    if( retryReconnectAttempt >= maxRetryAttempt ){
                        doConnect = false;
                    }
                }

                if( doConnect ){
                    if( 0 === retryReconnectAttempt ){
                        connectTostream();
                    }
                    else{
                        setTimeout(function() {
                            connectTostream(); //lets attempt to connect every 1 seconds
                        }, 1000);
                    }

                    retryReconnectAttempt++;
                } 
            }
        }

        function createMessageEntry(encodedName, encodedMsg) {
            encodedMsg = jQuery.parseJSON( encodedMsg );
    
            var entry = {
                'encodedName' : encodedName,
                'encodedMsg' : encodedMsg
            };
    
            return entry;
        }

        function getFieldType( field, generation_type ){
            if( generation_type == 'Category' ){
                if( field == 'page_title' ){
                    field = 'category_page_title';
                }
                if( field == 'page_description' ){
                    field = 'category_page_description';
                }
                if( field == 'open_graph' ){
                    field = 'category_open_graph';
                }
            }

            var textType = '';
            switch( field ){
                case 'page_title':
                    textType = 'page title';
                break;
                case 'category_page_title':
                    textType = 'category page title';
                break;
                case 'page_description':
                    textType = 'page description';
                break;
                case 'category_page_description':
                    textType = 'category page description';
                break;
                case 'category_description':
                    textType = 'category description';
                break;
                case 'product_description':
                    textType = 'product description';
                break;
                case 'product_excerpt':
                    textType = 'excerpt';
                break;
                case 'open_graph':
                    textType = 'open graph text';
                break;
                case 'category_open_graph':
                    textType = 'category open graph text';
                break;
                case 'image':
                    textType = 'image';
                break;
                case 'alt_text':
                    textType = 'alt text';
                break;
            }
    
            return textType;
        }

        function getFieldTypeID( field ){
            var textType = '';
            switch( field ){
                case 'page title':
                    textType = 'page_title';
                break;
                case 'category page title':
                    textType = 'page_title';
                break;
                case 'page description':
                    textType = 'page_description';
                break;
                case 'category page description':
                    textType = 'page_description';
                break;
                case 'category description':
                    textType = 'category_description';
                break;
                case 'product description':
                case 'description':
                    textType = 'product_description';
                break;
                case 'excerpt':
                    textType = 'product_excerpt';
                break;
                case 'open graph text':
                    textType = 'open_graph';
                break;
                case 'category open graph text':
                    textType = 'open_graph';
                break;
                case 'image':
                    textType = 'image';
                break;
                case 'alt text':
                    textType = 'alt_text';
                break;
            }
    
            return textType;
        }

        function bindConnectionMessageGenerate(connection) {
            if( connection === null ){
                //bypass
            }            
    
            var messageCallback = function (name, message) {
                if (!message) return;
    
                var encodedName = name;
                var encodedMsg = message.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                var messageEntry = createMessageEntry(encodedName, encodedMsg);

                var streamType = messageEntry.encodedMsg.type;
                var streamFieldType = messageEntry.encodedMsg.field;
                var streamStop = messageEntry.encodedMsg.stop;
                var streamPartialText = messageEntry.encodedMsg.partialText;
                var streamRecordId = messageEntry.encodedMsg.recordId;
                var streamStoreId = messageEntry.encodedMsg.storeId;
                var streamIndex = messageEntry.encodedMsg.index;
                var streamGenerationType = messageEntry.encodedMsg.generationType;

                var streamFieldTypeID = getFieldTypeID( streamFieldType );

                messageEntry.encodedMsg.streamFieldTypeID = streamFieldTypeID;

                var streamData = window.wtaStreamData;     

                if( streamType == '0' ){         
                    if( enableStreamingDebug == '1' ){                    
                        //console.log( 'single generate text > ' + streamFieldTypeID );
                        //console.log( messageEntry );
                    }

                    var match_stream_field = false;
                    if( streamData[streamFieldTypeID] ){
                        match_stream_field = true;
                    }
                                                    
                    if( streamData && streamFieldTypeID && match_stream_field ){
                        var eventInfo = streamData[streamFieldTypeID];
                        
                        var eventData = eventInfo.data;
                        var elemId = eventInfo.elemId;
                        var productId = eventData.product_id;
                        var field = eventInfo.type;
                        var fieldType = getFieldType( field, streamGenerationType );

                        if( 'Category' == streamGenerationType ){
                            productId = eventData.category_id;
                        }
            
                        if( streamType == 0 && streamFieldType == fieldType && productId == streamRecordId && WTAI_STREAMING_OBJ.storeID == streamStoreId ){

                            if( streamPartialText !== null && streamIndex !== null ){   

                                //streamPartialText = stripExcludedTags( streamPartialText );                                      
                                //streamPartialText = maybeDecodePartialText( streamPartialText );

                                if( streamPartialText == ' ' ){
                                    streamPartialText = '&nbsp;';
                                }

                                //record partialtext so that if the index was interchanged, we can fetch the proper text
                                recordedCountArray[streamFieldTypeID][streamIndex] = streamPartialText;
                                
                                recordedDataArray[streamFieldTypeID][streamIndex] = {
                                    eventInfo : eventInfo, 
                                    messageEntry : messageEntry,
                                    elemId : elemId
                                };

                                var textIndex = parseInt( countArray[streamFieldTypeID] );

                                var doType = true;
                                if( textIndex != streamIndex ){
                                    if( recordedCountArray[streamFieldTypeID][textIndex] !== undefined ){
                                        //lets assigned previously streamed text
                                        streamPartialText = recordedCountArray[streamFieldTypeID][textIndex];

                                        if( streamFieldTypeID == streamDebugField && enableStreamingDebug == '1' ){
                                            //console.log( 'ADVANCED OR LATE? ' + streamFieldTypeID + ' >> ' + streamIndex + ' >> ' + textIndex + ' >> ' + streamPartialText );
                                        }
                                    }
                                    else{
                                        doType = false;

                                        if( streamFieldTypeID == streamDebugField && enableStreamingDebug == '1' ){
                                            //console.log( 'NOT MATCH? ' + streamFieldTypeID + ' >> ' + streamIndex + ' >> ' + textIndex + ' >> ' + streamPartialText );
                                        }
                                    }
                                }

                                recordedPartialTextArray[streamFieldTypeID][streamIndex] = streamPartialText;

                                if( doType ){    
                                    if( streamFieldTypeID == streamDebugField && enableStreamingDebug == '1' ){
                                        //console.log( streamFieldTypeID + ' >> ' + streamIndex + ' >> ' + textIndex + ' >> ' + streamPartialText );
                                    }

                                    if( streamIndex == 0 ){
                                        //console.log('START ========================== ' + streamFieldTypeID );

                                        $(document).trigger('wtaGenerateTextStart', eventInfo, messageEntry);
                                    }

                                    if( streamPartialText !== null && streamIndex !== null && streamStop == false ){
                                        //write text
                                        if( streamPartialText == '&nbsp;' && streamFieldTypeID == streamDebugField && enableStreamingDebug == '1' ){
                                            //console.log('type space ' + streamPartialText);
                                        }

                                        var streamPartialTextParsed = maybeEncodePartialText( streamPartialText ); 
                                        
                                        var prevPartialText = '';
                                        if( streamIndex > 0 ){
                                            prevPartialText = recordedPartialTextArray[streamFieldTypeID][streamIndex - 1];
                                            prevPartialText = maybeEncodePartialText( prevPartialText ); 
                                        }
                                        
                                        var openFound = false;
                                        var closeFound = false;
                                        if( streamFieldTypeID == 'product_description' || streamFieldTypeID == 'product_excerpt' || streamFieldTypeID == 'category_description' ){      
                                            if( />|&gt;|> &nbsp;/.test(prevPartialText) == true && /<|&lt;|&nbsp;</.test(prevPartialText) == false ){
                                                closeFound = true;
                                            }

                                            if( /<|&lt;|&nbsp;</.test(streamPartialTextParsed) == true
                                            ){
                                                openFound = true;
                                            }         
                                        }

                                        if( openFound ){
                                            stopTagFlagArray[streamFieldTypeID] = true;
                                        }
                                        else{
                                            if( stopTagFlagArray[streamFieldTypeID] == true ){
                                                if( closeFound == true ){
                                                    stopTagFlagArray[streamFieldTypeID] = false;
                                                }
                                                else{
                                                    stopTagFlagArray[streamFieldTypeID] = true;
                                                }
                                            }
                                            else{
                                                stopTagFlagArray[streamFieldTypeID] = false;
                                            }
                                        }

                                        var doWrite = true;
                                        if( stopTagFlagArray[streamFieldTypeID] == true ){
                                            doWrite = false;

                                            if( openFound == false && />|&gt;|> &nbsp;/.test(streamPartialTextParsed) == true ){
                                                doWrite = true;

                                                streamPartialText = streamPartialText.replace(/> &nbsp;/g, '');
                                                streamPartialText = streamPartialText.replace(/>/g, '');
                                            }

                                            if( openFound ){
                                                var streamPartialTextParts = streamPartialText.split('');
                                                var textBeforeOpen = '';
                                                for( var p = 0; p < streamPartialTextParts.length; p++ ){
                                                    if( streamPartialTextParts[p] == '<' ){
                                                        break;
                                                    }

                                                    textBeforeOpen += streamPartialTextParts[p];
                                                }

                                                if( textBeforeOpen != '' && textBeforeOpen != '&nbsp;' && ! isReservedTag( textBeforeOpen ) ){
                                                    textBeforeOpen = textBeforeOpen.replace(/>/g, '');
                                                    
                                                    writeText( textBeforeOpen, elemId, streamFieldTypeID );
                                                }

                                                //console.log("open parts: " + textBeforeOpen + " | " + streamPartialTextParts );
                                            }
                                        }
                                        else{
                                            if( isReservedTag( streamPartialText ) ){
                                                doWrite = false;
                                            }
                                        }
                                        
                                        //console.log(streamFieldTypeID + ": stream partial encode: " + streamPartialTextParsed + " | PREV: " + prevPartialText + " | openfound: " + openFound + " | closefound: " + closeFound + " | do write: " + doWrite );

                                        //console.log("current partial text : " + origstreamPartialText + " | prev text : " + prevPartialText +  " | do write : " + doWrite);

                                        if( $('.wtai-global-loader').hasClass('wtai-is-active') == false ){
                                            $('.wtai-global-loader').addClass('wtai-is-active');
                                        }

                                        if( doWrite ){
                                            //console.log("WRITE: current partial text : " + streamPartialText + " | "  + streamPartialTextParsed + " | prev text : " + prevPartialText +  " | do write : " + doWrite);
                                            if( streamFieldTypeID == 'product_description' || streamFieldTypeID == 'product_excerpt' || streamFieldTypeID == 'category_description' ){ 
                                                streamPartialText = streamPartialText.replace(/>/g, '');
                                            }

                                            writeText( streamPartialText, elemId, streamFieldTypeID );                                            
                                            
                                            $(document).trigger('wtaGenerateTextProcessing', eventInfo, messageEntry);
                                        }
                                    }

                                    //record current index so we can have a sequential indexing of text
                                    countArray[streamFieldTypeID] = textIndex + 1;
                                }                                        
                            }

                            if( streamStop == true ){
                                var lastTypeIndex = parseInt( countArray[streamFieldTypeID] ) - 1;
                                var maxTypeIndex = recordedCountArray[streamFieldTypeID].length - 1;

                                //lets type skipped texts at the end
                                if( lastTypeIndex < maxTypeIndex ){
                                    for( var b = lastTypeIndex; b <= maxTypeIndex; b++ ){
                                        if( recordedCountArray[streamFieldTypeID][b] !== undefined ){
                                            var bStreamPartialText = recordedCountArray[streamFieldTypeID][b];
                                            var bElemID = recordedDataArray[streamFieldTypeID][b].elemId;
                                            var bEventInfo = recordedDataArray[streamFieldTypeID][b].eventInfo;
                                            var bMessageEntry = recordedDataArray[streamFieldTypeID][b].messageEntry;

                                            var bPrevPartialText = '';
                                            if( b > 0 ){
                                                bPrevPartialText = recordedPartialTextArray[streamFieldTypeID][b - 1];
                                                bPrevPartialText = maybeEncodePartialText( bPrevPartialText ); 
                                            }

                                            var bOrigstreamPartialText = bStreamPartialText;
                                            bOrigstreamPartialText = maybeEncodePartialText( bOrigstreamPartialText ); 

                                            var openFound = false;
                                            var closeFound = false;
                                            if( streamFieldTypeID == 'product_description' || streamFieldTypeID == 'product_excerpt' || streamFieldTypeID == 'category_description' ){      
                                                if( />|&gt;|> &nbsp;/.test(bPrevPartialText) == true && /<|&lt;|&nbsp;</.test(bPrevPartialText) == false ){
                                                    closeFound = true;
                                                }

                                                if( /<|&lt;|&nbsp;</.test(bOrigstreamPartialText) == true
                                                ){
                                                    openFound = true;
                                                }         
                                            }

                                            if( openFound ){
                                                stopTagFlagArray[streamFieldTypeID] = true;
                                            }
                                            else{
                                                if( stopTagFlagArray[streamFieldTypeID] == true ){
                                                    if( closeFound == true ){
                                                        stopTagFlagArray[streamFieldTypeID] = false;
                                                    }
                                                    else{
                                                        stopTagFlagArray[streamFieldTypeID] = true;
                                                    }
                                                }
                                                else{
                                                    stopTagFlagArray[streamFieldTypeID] = false;
                                                }
                                            }

                                            var doWrite = true;
                                            if( stopTagFlagArray[streamFieldTypeID] == true ){
                                                doWrite = false;           
                                                
                                                if( openFound == false && />|&gt;|> &nbsp;/.test(bStreamPartialText) == true){
                                                    doWrite = true;

                                                    bStreamPartialText = bStreamPartialText.replace(/> &nbsp;/g, '');
                                                    bStreamPartialText = bStreamPartialText.replace(/>/g, '');
                                                }

                                                if( openFound ){
                                                    var bStreamPartialTextParts = bStreamPartialText.split('');
                                                    var bTextBeforeOpen = '';
                                                    for( var p = 0; p < bStreamPartialTextParts.length; p++ ){
                                                        if( bStreamPartialTextParts[p] == '<' ){
                                                            break;
                                                        }
    
                                                        bTextBeforeOpen += bStreamPartialTextParts[p];
                                                    }
    
                                                    if( bTextBeforeOpen != '' && bTextBeforeOpen != '&nbsp;' && ! isReservedTag( bTextBeforeOpen ) ){
                                                        bTextBeforeOpen = bTextBeforeOpen.replace(/>/g, '');

                                                        writeText( bTextBeforeOpen, bElemID, streamFieldTypeID );
                                                    }
                                                    //console.log("SS open parts: " + bTextBeforeOpen + " | " + bStreamPartialTextParts );
                                                }
                                            }
                                            else{
                                                if( isReservedTag( bStreamPartialText ) ){
                                                    doWrite = false;
                                                }
                                            }

                                            //console.log("SS: " + streamFieldTypeID + ": stream partial encode: " + bStreamPartialText + " | PREV: " + bPrevPartialText + " | openfound: " + openFound + " | closefound: " + closeFound + " | do write: " + doWrite );

                                            //console.log("SPILLED COFFEE :D | current partial text : " + bOrigstreamPartialText + " | prev text : " + bPrevPartialText +  " | do write : " + doWrite);

                                            if( $('.wtai-global-loader').hasClass('wtai-is-active') == false ){
                                                $('.wtai-global-loader').addClass('wtai-is-active');
                                            }

                                            if( doWrite ){
                                                //console.log("WRITE SPILLED COFFEE :D | current partial text : " + bStreamPartialText + " | " + bOrigstreamPartialText + " | prev text : " + bPrevPartialText +  " | do write : " + doWrite);
                                                if( streamFieldTypeID == 'product_description' || streamFieldTypeID == 'product_excerpt' || streamFieldTypeID == 'category_description' ){    
                                                    bStreamPartialText = bStreamPartialText.replace(/>/g, '');
                                                }

                                                writeText( bStreamPartialText, bElemID, streamFieldTypeID );
                                            
                                                $(document).trigger('wtaGenerateTextProcessing', bEventInfo, bMessageEntry);
                                            }

                                            if( streamFieldTypeID == 'page_title' && enableStreamingDebug == '1' ){
                                                //console.log('write this: ' + b + ' >> ' + bStreamPartialText);
                                            }
                                        }
                                    }
                                }

                                if( streamFieldTypeID == streamDebugField && enableStreamingDebug == '1' ){
                                    //console.log('stream array: ' + streamFieldTypeID);
                                    //console.log('lastTypeIndex: ' + lastTypeIndex + ' || ' + maxTypeIndex);
                                    //console.log(recordedCountArray[streamFieldTypeID]);
                                    //console.log(countArray[streamFieldTypeID]);
                                }

                                //lets reset the stream once done
                                countArray[streamFieldTypeID] = 0;
                                recordedCountArray[streamFieldTypeID] = [];
                                recordedPartialTextArray[streamFieldTypeID] = [];
                                recordedDataArray[streamFieldTypeID] = [];
                                stopTagFlagArray[streamFieldTypeID] = false;

                                //moved this to type 2 loop
                                //$(document).trigger('wtaGenerateTextStop', eventInfo, messageEntry);
                            }
                        }
                    }
                    else{
                        //listen if someone else is generating this product, we must disable the generate button
                        if( streamRecordId && WTAI_STREAMING_OBJ.storeID == streamStoreId ){
                            $(document).trigger('wtaGenerateTextOthers', messageEntry);

                            //console.log("other product is streaming " + streamRecordId + " " + streamFieldTypeID );
                            if( enableStreamingDebug == '1' ){
                                //console.log("other product is streaming " + streamRecordId + " " + streamFieldTypeID );
                            }
                        }
                    }
                }
                else if( streamType == '1' ){
                    if( enableStreamingDebug == '1' ){
                        //console.log( 'start bulk generate text' );
                        //console.log( messageEntry );
                    }

                    var requestID = messageEntry.encodedMsg.id;

                    //console.log( 'start bulk generate text ' + requestID );
                    //console.log( messageEntry );

                    if( requestID.includes( 'Ideas' ) && messageEntry.encodedMsg.status == 'Failed' ) {
                        // Skip, this is not a bulk generation
                        // Start keyword streaming.
                        //console.log( 'final keyword streaming: ' );
                        //console.log( messageEntry );
                        messageEntry.encodedMsg.requestId = messageEntry.encodedMsg.id; 
                        $(document).trigger('wtaProcessKeywordAnalysis', messageEntry);
                    }
                    else{
                        if( messageEntry.encodedMsg.userName == currentUserEmail ){
                            throttleFunction(bulkGenerateText, 200, messageEntry);
                        }
                        else{
                            $(document).trigger('wtaEnableWTAList', messageEntry);
                        }
                    }
                    //console.log( "current user email " + currentUserEmail + " > " + messageEntry.encodedMsg.userName);
                }
                else if( streamType == '2' ){
                    //console.log( 'single generate result status: ' + streamFieldTypeID );
                    //console.log( messageEntry );
                    //console.log( streamData );

                    if( streamFieldTypeID == 'alt_text' ){
                        //console.log('process alt text complete ');
                        $(document).trigger('wtaSingleGenerateImageAltText', messageEntry);
                    }
                    else{
                        var match_stream_field = false;
                        if( streamData[streamFieldTypeID] ){
                            match_stream_field = true;
                        }
                        
                        if( streamData && streamFieldTypeID && match_stream_field ){
                            var eventInfo = streamData[streamFieldTypeID];

                            eventInfo.messageEntry = messageEntry;

                            //console.log('status update called', eventInfo);
    
                            $(document).trigger('wtaSingleGenerateStatusUpdate', eventInfo);
                        }
                    }        
                }
                else if( streamType == '3' ){
                    // Start keyword streaming.
                    //console.log( 'start keyword streaming: ' );
                    //console.log( messageEntry );
                    $(document).trigger('wtaProcessKeywordAnalysis', messageEntry);
                }
            };

            connection.on('broadcastMessage', messageCallback);
            connection.onclose(onConnectionError);
        }

        function bulkGenerateText( messageEntry ){
            //console.log( 'start bulk generate text' );
            //console.log( messageEntry );

            $(document).trigger('wtaBulkGenerateStatusUpdate', messageEntry);
        }

        function throttleFunction(func, delay, ...args) {
            // If setTimeout is already scheduled, no need to do anything
            if (timerId) {
                return;
            }
        
            // Schedule a setTimeout after delay seconds
            timerId  =  setTimeout(function () {
                func(...args);
                
                // Once setTimeout function execution is finished, timerId = undefined so that in <br>
                // the next scroll event function execution can be scheduled by the setTimeout
                timerId  =  undefined;
            }, delay);
        }

        function initCountArray(){
            countArray['page_title'] = 0;
            countArray['page_description'] = 0;
            countArray['product_description'] = 0;
            countArray['category_description'] = 0;
            countArray['product_excerpt'] = 0;
            countArray['open_graph'] = 0;

            recordedCountArray['page_title'] = [];
            recordedCountArray['page_description'] = [];
            recordedCountArray['product_description'] = [];
            recordedCountArray['category_description'] = [];
            recordedCountArray['product_excerpt'] = [];
            recordedCountArray['open_graph'] = [];

            recordedPartialTextArray['page_title'] = [];
            recordedPartialTextArray['page_description'] = [];
            recordedPartialTextArray['product_description'] = [];
            recordedPartialTextArray['category_description'] = [];
            recordedPartialTextArray['product_excerpt'] = [];
            recordedPartialTextArray['open_graph'] = [];

            recordedDataArray['page_title'] = [];
            recordedDataArray['page_description'] = [];
            recordedDataArray['product_description'] = [];
            recordedDataArray['category_description'] = [];
            recordedDataArray['product_excerpt'] = [];
            recordedDataArray['open_graph'] = [];

            stopTagFlagArray['page_title'] = false;
            stopTagFlagArray['page_description'] = false;
            stopTagFlagArray['product_description'] = false;
            stopTagFlagArray['category_description'] = false;
            stopTagFlagArray['product_excerpt'] = false;
            stopTagFlagArray['open_graph'] = false;
        }

        function maybeEncodePartialText( text ){
            if ( ! text ){
                return '';
            }
            
            // Create a regular expression to match any of the encoded entities
            var entityRegex = new RegExp(Object.keys(entitiesEncoded).join('|'), 'g');

            // Replace the encoded entities with their corresponding characters
            var decodedString = text.replace(entityRegex, match => entitiesEncoded[match]);

            return decodedString;
        }

        function writeText( streamPartialText, elemId, streamFieldTypeID ){
            var content = tinymce.get(elemId).getContent( { format: 'raw' } );

            if( '1' === enableStreamingDebug && streamFieldTypeID === streamDebugField ){
                //console.log(streamFieldTypeID + ' >> ' + content);
            }

            if( $(content).find('.typing-cursor').length ){
                content = content.replace(/\s*<span class="typing-cursor">.*<\/span>/g, '');
                content = content.replace('<p>', '').replace('</p>', '');
            }

            content += streamPartialText + '<span class="typing-cursor">&nbsp;</span>';
            content = content.replace(/\n/g, '<br/>');
    
            var editor = tinymce.get(elemId);
            editor.setContent( content );
            editor.contentWindow.scrollTo(0, editor.contentWindow.document.body.scrollHeight);
        }

        function isReservedTag( wordToCheck ){
            // Check if the word does not match any element in the allowedElements array
            var match = reservedTags.includes(wordToCheck);

            return match;
        }

        return {
            'init' : init,
            'maybeReconnect' : maybeReconnect
        };
    }();

    wtaStream.init();

    window.onfocus=function() {
        wtaStream.maybeReconnect();
    };

    window.onmousemove=function() {
        wtaStream.maybeReconnect();
    };
});