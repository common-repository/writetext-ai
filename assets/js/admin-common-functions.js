/* jshint -W065 */
/* jshint -W117 */
/* jshint -W119 */
/* jshint -W069 */
/* jshint -W024 */
/* jshint -W062 */
/* jshint -W116 */
/* jshint -W004 */
/* jshint -W083 */

// Note: Common function
function wtaiCleanUpHtmlString(html) {
    // Allowed tags and their corresponding allowed attributes
    var allowedTags = {
        'ul': [],
        'li': [],
        'ol': [],
        'p': ['style'],
        'blockquote': [],
        'strong': [],
        'em': [],
        'pre': [],
        'del': [],
        'h1': [],
        'h2': [],
        'h3': [],
        'h4': [],
        'h5': [],
        'h6': [],
        'a': ['href'],
        'hr': [],
        'br': [],
        'span': ['style'],
        'u': []
    };

    // Create a new HTML document
    var doc = document.implementation.createHTMLDocument();

    // Remove script elements
    var scripts = doc.getElementsByTagName('script');
    while (scripts[0]) {
        scripts[0].parentNode.removeChild(scripts[0]);
    }

    // Remove style elements
    var styles = doc.getElementsByTagName('style');
    while (styles[0]) {
        styles[0].parentNode.removeChild(styles[0]);
    }

    // Set the document's body innerHTML to the provided HTML
    doc.body.innerHTML = html;

    // Get all elements in the document body
    var elements = doc.body.querySelectorAll('*');

    // Loop through each element
    elements.forEach(function(element) {
        var tagName = element.tagName.toLowerCase();

        // Remove the element if it is not in the allowed tags list
        if (!(tagName in allowedTags)) {
            element.parentNode.removeChild(element);
            return;
        }

        // Remove all attributes except the allowed ones
        var attributes = element.attributes;

        for (var i = attributes.length - 1; i >= 0; i--) {
            var attributeName = attributes[i].name;

            if (allowedTags[tagName].indexOf(attributeName) !== -1) {
                // If the attribute is 'href' for <a> tag, replace it with '#'
                if (tagName === 'a' && attributeName === 'href') {
                    element.setAttribute('href', '#');
                }

                if (attributeName === 'style') {
                    var styleValue = element.getAttribute('style');
                    var allowedStyles = [];

                    // Specify allowed styles for specific tags
                    if (tagName === 'p') {
                        allowedStyles.push('text-align');
                    } else if (tagName === 'span') {
                        allowedStyles.push('text-decoration');
                        allowedStyles.push('color');
                    }

                    // Parse the current 'style' attribute and keep only allowed styles
                    var styleArray = [];
                    var styles = styleValue.split(';');
                    styles.forEach(function(style) {
                        var parts = style.split(':');
                        var key = parts[0].trim();
                        if (allowedStyles.indexOf(key) !== -1) {
                            styleArray.push(style);
                        }
                    });

                    // Set the modified 'style' attribute
                    element.setAttribute('style', styleArray.join(';'));
                }
            } else {
                element.removeAttribute(attributeName);
            }
        }

        // If <a> tag doesn't have an 'href' attribute, add 'href="#"'
        if (tagName === 'a' && !element.hasAttribute('href')) {
            element.setAttribute('href', '#');
        }
    });

    // Get the cleaned HTML from the document body
    var cleanedHtml = doc.body.innerHTML;

    return cleanedHtml;
}   

// Note: Common function
function wtaiRemoveLastBr(str) {
    var lastIndex = str.lastIndexOf("<br>");
    if (lastIndex !== -1) {
        return str.slice(0, lastIndex) + str.slice(lastIndex + 4);
    } else {
        return str;
    }
}

// Note: Common function
function wtaiRemoveTags(str) {
    if ((str===null) || (str===''))
        return false;
    else
        str = str.toString();
          
    // Regular expression to identify HTML tags in
    // the input string. Replacing the identified
    // HTML tag with a null string.
    return str.replace( /(<([^>]+)>)/ig, '');
}

// Note: Common function
function wtaiGetWordsArray(text = '' ){
        
    text = wtaiRemoveTags(text);

    if( ! text ){
        return [];
    }
    
    var words = text.toLowerCase().match(/\b(?:[\w'‘’′]+(?:[.-][\w'‘’′]+)*|[\w'‘’′]+)\b/g);

    if (words === null) return [];

    return words;
}

// Note: Common function
function wtaiGetWordsCaseInsensitiveArray(text){
    text = text.replace(/'/g, ''); //lets remove all single quote

    var words = text.match(/\b(?:\w+(?:[.-]\w+)*|\w+)\b/g);
    if (words === null) return [];

    return words;
}

// Note: Common function
function wtaiTypeWriterHTMLBox( elem, string, i = 0, speed = 50 ) {
    if (i < string.length) {
        elem.html( elem.html() + string.charAt(i) );
        i++;
        setTimeout(wtaiTypeWriterHTMLBox, speed, elem, string, i, speed );
    }
}

// Note: Common function
function wtaiRemoveLastPipe(str) {
    if (str.endsWith('|')) {
        return str.slice(0, str.lastIndexOf('|'));
    }
    return str;
}

function wtaiTypeWriterTextBox( elem, string, i = 0, speed = 50 ) {
    if (i < string.length) {
        elem.val( elem.val() + string.charAt(i) );
        i++;
        setTimeout(wtaiTypeWriterTextBox, speed, elem, string, i, speed );
    }
    else{
        elem.trigger('change');
    }
}

function wtaiRemoveHtmlTags(str) {
    return str.replace(/<\/?[^>]+(>|$)/g, "");
}

function wtaiAreEqualIgnoringWhitespaceAndNewline(str1, str2) {
    // Remove all whitespace and newline characters from both strings
    const cleanStr1 = str1.replace(/[\s\n]/g, '');
    const cleanStr2 = str2.replace(/[\s\n]/g, '');

    // Compare the cleaned strings
    return cleanStr1 === cleanStr2;
}

function wtaiContainsHtmlUsingDOMParser(str) {
    const parser = new DOMParser();
    const doc = parser.parseFromString(str, 'text/html');
    return doc.body.children.length > 0;
}

function wtaifindDifferencesInHtmlStrings(str1, str2) {
    let diffHTML = '';
    let maxLength = Math.max(str1.length, str2.length);

    for (let i = 0; i < maxLength; i++) {
        if (str1[i] !== str2[i]) {
            diffHTML += `<span class="diff">${str2[i] ? str2[i] : ''}</span>`;
        } else {
            diffHTML += str2[i] ? str2[i] : '';
        }
    }

    return diffHTML;
}

function wtaiAreHtmlStringsEqual(str1, str2, display_log = false) {
    // Helper function to normalize the HTML content except leading and trailing spaces
    const normalizeHtml = (str) => {
        const ensureSemicolons = (styleString) => {
            return styleString
                .split(';')
                .map(rule => rule.trim())
                .filter(rule => rule !== '')
                .map(rule => rule.endsWith(';') ? rule : rule + ';')
                .join(' ');
        };

        return str
            .replace(/&nbsp;/g, ' ')
            .replace(/<br\s*\/?>/gi, "<br>")
            .replace(/\s*(<br>)\s*/gi, "$1")
            .replace(/(\s*)(<\/?\w+[^>]*>)(\s*)/gi, (match, leading, tag, trailing) => leading + tag + trailing) // Preserve spaces inside tags
            .replace(/>\s+</g, '><')
            .replace(/\s+/g, " ")
            .replace(/style="([^"]*)"/gi, (match, p1) => {
                const normalizedStyles = ensureSemicolons(p1);
                return `style="${normalizedStyles}"`;
            })
            .replace(/<(\w+)[^>]*>(\s|&nbsp;)*<\/\1>/gi, '')
            .trim();
    };

    const preserveLeadingTrailingSpaces = (str) => {
        const leadingSpaces = str.match(/^\s*/)[0];
        const trailingSpaces = str.match(/\s*$/)[0];
        const normalizedHtml = normalizeHtml(str);
        return leadingSpaces + normalizedHtml + trailingSpaces;
    };

    const normalizedStr1 = preserveLeadingTrailingSpaces(str1);
    const normalizedStr2 = preserveLeadingTrailingSpaces(str2);

    if (display_log == true) {
        console.log('normalizedStr1: ', normalizedStr1);
        console.log('normalizedStr2: ', normalizedStr2);
    }

    return normalizedStr1 === normalizedStr2;
}

function wtaiFormatDateToYYYYMMDD(date) {
    if (!date) return ''; // Return an empty string if the date is null or undefined

    var year = date.getFullYear();
    var month = (date.getMonth() + 1).toString().padStart(2, '0'); // Months are 0-indexed
    var day = date.getDate().toString().padStart(2, '0');

    return `${year}-${month}-${day}`;
}