
# Change Log

Plugin Name: WriteText.ai
Plugin Description: Let AI automatically generate product descriptions and other content from your product data.

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/). 

## [1.40.4] - 2024-10-10

### Fixed

- Fix issue with credit count not updating after generating alt image text only in the product edit page.
- Fix incorrect label displayed in Transfer / Save metabox in product single page.

## [1.40.3] - 2024-10-02

### Changed

- Minor readme.txt updates.

### Fixed

- Remove other settings in footer when force plugin update is effective.

## [1.40.2] - 2024-10-01

### Changed

* Update - Minor readme.txt updates.
* Update - Popup blocker label update for the settings page.

## [1.40.1] - 2024-10-01

### Added

- Spellcheck suggestion for selected keywords.
- Product category text generation, transfer, saving and mark as reviewed.
- Representative products for product categories.
- Category grid, filter and history log.
- Added the setting for the minimum and maximum length for the category description in the Global Settings page.
- Added the setting for the minimum and maximum length for the category description in the Setup page.
- Added a Send feedback link in the footer.
- Free premium badge in the setup page and popup in other WTA pages for eligible accounts.
- Added a retry for the bulk transfer when the transfer fails because of a server related issue. Displays an error message when the maximum number of retry fails.

### Changed

- Updated the menu to include a separate link for the product text generation page.
- Updated the menu to include a link to the category text generation page.
- Updated the grid to include a headline to differentiate between Products and Categories pages.
- Updated the error message when ticking Mark as reviewed for a product without any generated text yet.
- Updated some user preference for the product category: Highlight keywords, show text on preview, pre selected text types, highlight incorrect.pronouns.
- Display country popup in category page if not yet selected.
- Updated UI for the footer.
- Various text and label updates.
- Mark as reviewed states for product and category.
- Added a custom prefix to IDs and classes specifically used by WriteText.ai to avoid conflict with other browser extensions.
- Make product featured image and alt text image as a part of the free feature.
- Update product featured image to be checked by default in the setup page.
- Update featured image to be saved per user preference instead of a product preference.
- Update popup blocker dismiss behavior.

### Fixed

- Fix issue when searching in reference product for bulk popup and product edit page.
- Fix issue with incorrect checked state in Mark as reviewed for the product edit page.
- Fix issue related to token for multisite users.

## [1.30.7] - 2024-08-29

### Added

- Added a dismissable notice to disable popup blocker in the setup, product list and settings page.

### Fixed

- Bug fixes.
- Fix issue with multisite - subdomain install token conflict.

## [1.30.6] - 2024-07-06

### Added

- Added Refresh data in Your own keywords section. The functionality will be the same as the Refresh data in Suggested keywords. Clicking either button will refresh data for both sections.

### Changed

- Updated UI for Start AI-powered keyword analysis CTA to be sticky.
- Refresh data should only appear when there is data to refresh for "Your own keywords" and "Suggested keywords" only. Refresh data will re-appear either when a new keyword is added or selected OR when data is stale. Refresh data for selected keywords and competitor keywords will always be available because SERP data can change daily.
- Updated SERP overview to display 100 records instead of 5.
- Updated the keywords to always be passed in lowercase even if the user has typed it another way.
- Display no data for results without data fetched from the API for rank, intent, search volume and difficulty columns in Keywords to be included in your text and Your Own Keywords sections.

## [1.30.5] - 2024-07-03

### Changed

- Various help text updates for the keyword analysis and remove translation files.

## [1.30.4] - 2024-07-02

### Fixed

- Fix for competitor keyword displaying an empty as of date in the help text message.

## [1.30.3] - 2024-07-02

### Changed

- Various placeholder and help text updates for the keyword analysis feature.
- Hide the keyword that is the same as the product name in the suggested ideas section.

### Fixed

- Fix the issue where the error message does not display when a stale error message is shown and a keyword that is the same as the product name is added.

## [1.30.2] - 2024-07-01

### Changed

- Remove "Rank" column in Keyword Analysis > Keywords your competitors are ranking on.
- Update selected state for the image alt text. If at least one image is selected, when moving from the previous or to the next product, all images for that product should be selected by default. 

## [1.30.1] - 2024-06-28

### Added

- Added MO/PO translations for CA, DA, DE, ES, FR, IT, NL, NO, PT and SV languages.
- New meta "wtai-fid" for field list used by the extension.
- Additional sections to keyword analysis: Selected keywords, ranked keywords, competitor keywords, your own keywords and suggested keywords.
- Added Start AI-powered keyword analysis action in keyword analysis. Removed Get Data CTA.
- Refresh data for selected keywords, competitor keywords and suggested keywords in keyword analysis.
- Sorting, filtering and load more for ranked keywords, competitor keywords and suggested keywords in keyword analysis.
- Adding of selected keywords from ranked keywords, competitor keywords, your own keywords and suggested keywords in keyword analysis.
- SERP data for ranked and competitor for keyword analysis.
- Additional option in bulk generation to use keywords you're currently ranking on.
- Additional option to add special instructions in bulk generation.
- Pass productName when doing keyword analysis and refresh data.

### Changed

- Mark as reviewed for image alt text.
- Keyword Ideas > Notice: The server is currently handling a lot of requests. Please retry after a few minutes.
- Display proceed button when a normal text field is generated together with an invalid image.
- Update for country selection popup to only allow one country selection instead of multiple. Country will now be mapped per language based on WP locale. The country will now be based on the locale unless the locale has no default country like "ca" language.
- UI overlay for keyword analysis and history popup.
- Various responsive UI fixes

## [1.20.11] - 2024-04-24

### Changed

- Removed the redirect to the setup page after plugin activation for WordPress version 6.5.2 and above as per the new guideline of the new WP version. See details here: https://core.trac.wordpress.org/ticket/60992

## [1.20.10] - 2024-04-19

### Added

- Added tooltip and disabled state for keyword input and semantic keyword selection if the total maximum value is reached.
- Added improved API logging.

### Changed

- Updated some code to conform to WooCommerce coding standards while still adhering to the PHP Coding Standard Sniffer.

### Fixed

- Fix issue with product attribute not saving in the product edit page when the featured image checkbox is selected.
- Fix issue with featured image not displaying invalid image warning.
- Fix incorrect message displayed for only one or multiple invalid image in the alt text.

## [1.20.9] - 2024-03-26

### Added

- Added wtai verification meta for security purposes.

### Changed

- Tested with latest version of WordPress 6.4.3, Yoast SEO 22.3 and WooCommerce 8.7.0.
- Last edited date, last transferred date, history dates and review date should display in server timezone and format.

### Fixed

- Tagging of extension review as Done during transfer for both bulk and single for some languages.
- Formatting issue of transferred meta title, meta desc and open graph wherein breaks and new line was removed.
- Credit not updating real time when only generating, rewriting, generating with reference product for only one field.
- Fix wrong field generation status for first time generated texts.
- Plugins > Setting link should go to setup page if setup is not yet done.
- If newly setup plugin, the "select all" checbox in edit page is not selected by default.
- Various minor UI issues.

## [1.20.8] - 2024-03-21

### Added

- Added Settings link in the plugins page.

### Changed

- Minor label updates.
- Do not allow empty fields in saving and transfer of text types and alt images.
- Consider "imageDataExpires" in checking if image is already uploaded in the server.
- Updated the available credits display source of data to "availableCredits" instead of "totalCredits".
- Do not allow Transfer selected and Saving in single edit page when generation is ongoing.
- Review extension API calls should use WP language code instead of the mapped language codes.
- Optimize calls to /Credit endpoint used in checking the accounts available credit and if account is premium.

### Fixed

- Fixed issue when hiding the bulk progress bar, the current ongoing progress is not displayed.
- Fixed incorrect bulk popup message left after clicking the OK button of the bulk prgress bar in edit page.
- Handling available credits display when an error is encountered while fetching the available credit limit.
- Other minor UI fixes.

## [1.20.7] - 2024-03-14

### Changed

- Updated plugin root filename.

## [1.20.6] - 2024-03-14

### Changed

- Removed credit cost in Generate / Rewrite CTAs for both single and bulk generation.
- Removed credit cost in Get Data CTA in Keyword Analysis.
- Added available credit display in the sticky footer together with other links. Available credit display is updated during page reload, after each field generation in single edit page and after each progress in bulk generation.

## [1.20.5] - 2024-03-07

### Changed

- Updated review extension custom meta to: <meta name="wtai-pid" data-platform="WordPress" content="{product_id}" >
- Updated the maximum images for alt text to 10 images for both bulk generation and single edit page generation.
- Enable rewrite for selected image alt text which has current platform value.

### Fixed

- Fix various issues from QA related to Version 1.20.4 updates.

## [1.20.4] - 2024-03-05

### Changed

- Updated credit cost computation display for image alt text in bulk generation to be per product.

### Fixed

- Fix various issues from QA related to Version 1.20.3 updates.

## [1.20.3] - 2024-03-01

### Changed

- Coding Standard Update: Sanitize custom queries and pass the parameters in $wpdb:prepare statements.
- Disable closing, prev and next CTAs during image processing in the single edit product page.
- Disable closing, prev and next CTAs during transfer in single edit product.
- Various text and label updates.

### Fixed

- Fix various issues from QA related to Version 1.20.2 updates.

## [1.20.2] - 2024-02-22

### Added

- Include the image alt text in bulk generation.
- Include the image alt text in bulk transfer.
- Credit calculation in bulk generation should also consider the image alt text generation.
- By batch uploading of images to the API to prevent timeout issue for bulk generation if image alt text generation and featured image is enabled.

### Changed

- By batch uploading of images to the API to fix timeout issue for single product edit page.
- Apply unified API call for image alt text generation and normal text types generation.
- Transfer should tag the extension review as Done.
- UI update for the review extension status. For rewrite and for factual check should be combined.
- Make image alt text and featured image a premium feature.
- UI update for the single generation confirmation popup.
- Update the list of items in the premium list popup to include the image alt text and feature image.
- Remove disable of image alt text when reference and rewrite is selected in single edit page.
- Various text and label updates.

### Fixed

- Fix various issues from QA related to featured image, image alt text generation and extension reviews.
- Fix issue in installation page when a plugin can't be downloaded. This should display the error: "Can't activate the plugin. Please try again or contact your site's administrator."

## [1.20.1] - 2024-02-12

### Added

- Review status from browser extension in grid WriteText.ai status filter.
- Display and tagging of "Done" of review status from browser extension: For rewrite and For factual checking in single edit page 
- Additional featured image option in product attributes section for the setup page, settings page, bulk popup settings and single edit page.
- Use featured image in generation of texts for both bulk and single edit page.
- Alt text generation of text in single edit page.
- Transfer/Saving alt text for images.
- Uploading of image to the API for featured image and alt text image.
- Notice and error handling for images uploaded used for generation and alt text.
- Split alt text generation request based on maxImageAltTextPerRequest from the API.
- Disable alt text for rewrite and reference product.
- Image alt text checkbox state based on user preference.

### Changed

- Include credit alt text in credits computation based on additional credit and rules from the API.
- Various tooltips and label updates to align with the new featured image and alt text feature.
- Hide Show text preview on hover on tablet to mobile breakpoints.
- Open WTA for all languages and display notice on WTA pages informing the user that the translation is ongoing.
- Revamp UI for keyword ideas filter and sorting.

### Fixed

- Remove unused "toggle panel" screen reader text in single edit page.
- Fix character count display in Custom tone and style and when selecting other tone and style.

## [1.12.7] - 2024-02-13

### Changed

- WP Coding Standard Update: Remove inline scripts and use WP default function "wp_add_inline_script". (country list dropdown and hide guideline state)
- WP Coding Standard Update: Fix loader_cursor.gif asset call. (text generation loader cursor in textarea fields)
- WP Coding Standard Update: Host signalr.js script locally inside the plugin asset files. (streaming in bulk and single generation)

### Fixed

- Fix issue with bulk Cancel button.
- Allow empty text saving in single edit page.

## [1.12.6] - 2024-01-30

### Fixed

- Various issues from QA related to coding standards.

## [1.12.5] - 2024-01-29

### Fixed

- Various issues from QA related to coding standards.

## [1.12.4] - 2024-01-22

### Changed

- Additional WP and PHP coding standard updates.
- Update "Save" button disable state in product edit page should not consider if the field type is checked or not.
- Updated preview param from "wta-preview" to "wtai-preview" to follow coding standards.
- Updated some texts for EN labels.
- Updated header from "Activity history log" to "History log" only.

### Fixed

- Fix issue with bulk actions no options available when the user switch from Free to Premium.
- Fix Select All not triggering disabled combination state for tone and style.
- Remove current product ID in the reference product list.
- Force premium handling of other product details.

## [1.12.3] - 2024-01-15

### Changed

- Update backend logo icon for menu on both link inactive and active states.
- Label updates: Page Title = Meta title, Page Description = Meta description, Product excerpt = Product short description.

### Fixed

- Fix various new and reopened issues reported by QA related to Coding Standard update and Premium/Free features update.
- Fix language value passed for global history.

## [1.12.2] - 2024-01-11

### Changed

- Ad display for non premium user now comes from the API endpoint /web/ad/SquareWordPress.
- Highlight keywords should be enabled in the product edit page for users who accesses the site for the first time.
- All field types should be enabled in the product edit page for users who accesses the site for the first time.

### Fixed

- Fix various issues reported by QA related to Coding Standard update and Premium/Free features update.

## [1.12.1] - 2024-01-05

### Added

- Premium and free feature updates in grid and single product edit page.
- Ad display for non premium user (Note: Current ad and url is hardcoded while waiting for the API).

### Changed

- WP and PHP coding standard updates.
- Sanitize email for token api connection.
- Only check if there is an updated version of the plugin on writetext pages and plugins page.

## [1.11.2] - 2023-12-18

### Fixed

- Fix issue with bulk generation credit calculation display when clicking "Select All" in bulk popup options.
- Fix display link of notice update error message.

## [1.11.1] - 2023-12-15

### Added

- Added Restore global settings link in the footer. When clicked, user preferences like tones, styles, audiences will reset to global default value for that user.

### Changed

- Updated some texts in the Installation setup page.
- Reset all user preferences when plugin is deactivated.

### Fixed

- Fix issue when a site has been deleted in the backend, it should redirect to setup page so that user can login and request for a new access token.

## [1.10.6] - 2023-12-14

### Changed

- Strip html tags for product attributes and other product detail values sent to the API.
- FAQ link in tooltip for reference and rewrite should redirect to new tab/window.
- Remove disable state for target input length for product description and excert when reference product is selected for both bulk and single product edit.

### Fixed

- Do not allow decimal entry in target length fields in bulk, single product edit, settings and installation.

## [1.10.5] - 2023-12-13

### Changed

- Additional text in tooltip for reference product and rewrite product.
- Update input character length variable in credit computation to include the html tags in the length.
- Sanitize passed text to the API to only allow certain html tags and remove not allowed attributes.

### Fixed

- Fix various issues from QA related to 1.10.4 updates.

## [1.10.4] - 2023-12-11

### Changed

- "Mark as Reviewed" checkbox should be unchecked if at least one field is generated and NOT reviewed or if there are no generated texts yet.

### Fixed

- Fix various issues from QA related to 1.10.3 updates.

## [1.10.3] - 2023-12-10

### Changed

- Labels update in product edit page: "Transfer to live" to "Transfer", "Not transferred to live" to "Not transferred".
- Disable generate and rewrite radio selections when generation is ongoing. Also added tooltip.
- Include semantic keyword in bulk generation if it is available for that product.
- Limit allowed language to "EN" only.

### Fixed

- Fix various issues from QA related to 1.10.2 updates

## [1.10.2] - 2023-12-08

### Changed

- Save the pre selected country if the user close the dropdown without choosing any options
- Change tooptip label for transfer ( Nothing to Transfer/ Already Transferred)
- Sentence case for "Steps" and small case for "or" in product edit filter
- Disable writetext.ai field if not generated yet and add tooltip
- Do not call reviewed if last status of the field is transferred
- Label update: "rewrite existing/live text" to "rewrite existing text"
- Disable transfer if already transferred
- Various label changes in the product edit page fields
- Default country for some languages
- New and updated translations for supported language
- If there reference product is selected, disable style/tone/audience, if rewrite is selected, do not disabled style/tone/audience, only disable reference product.
- Disable min/max field for reference product both on bulk and single product page

### Fixed

- Fix various issues from QA related to product edit page new layout, tooltip, credit computation and other UI/UX fixes

## [1.10.1] - 2023-11-28

### Added

- Product attributes in single product edit should save selected attribute per product preference.
- After bulk generation, the tones, style, audience, product attributes, description min length, description max length, excerpt min length and excerpt max length should go back to default global settings.
- Additional help text in bulk generation modal: "Cancellation is dependent on the server response; it may be disabled upon initial processing or when there are only 2 products or less left in your queue".
- Tooltip for disabled tone and style in product edit, bulk popup, settings and installation setup .
- Tooltip for disabled reference product when rewrite is selected and vice versa.
- Added minimum number of days of 365 to the No Activity days filter and display a tooltip when the minimum is reached.
- Added Steps and Hide Steps functionality for product edit page.
- Added back the single transfer button in product edit page.
- Added various message texts for product field status: "generated|not generated", "Not transferred to live".
- Country selection popup on first time visit of writetext product list and settings.
- Added back the error message when activating the plugin and Wordpress version is below 6.0.
- Added back the notification in the setup page for both administrator and non administrator users when the SEO plugin suddenly changes.
- Added new user capability "writeai_select_localized_country" on initial country setup upon installation.

### Changed

- Audience should be saved per user preference in product edit page, otherwise use the global settings.
- Clicking reset tones, style and audience filter should set the options back to the global settings value.
- Ensure that bulk generate tones, style and audience should always come from global settings.
- Locale used for text translations (PO/MO files) within the WrieText.ai wp-admin area should follow the predefined user locale or the site language if no user locale was set. 
- All calls to the API (ex: text generation) should follow the site language.
- Removed links in the footer for Terms and Condition, Privacy Policy and Cookie Policy.
- Revamp UI for product edit page affecting the keywords, generate/rewrite actions and WriteText and WP generated text fields.
- Interchange fields for WriteText.ai and WordPress text fields in product list.
- Update notification message for unsupported site language for WriteText.ai which also include a link to the settings page so the user can easily update the site language if applicable.
- Plural and singular label for bulk generate progress bar for "munute" and "second" labels.
- Keyword analysis section should be expanded by default.
- Include the keyword analysis section in the "Collapse/Expand All" toggle.
- Removed "Do not show these settings when bulk-generating text" in bulk generation popup and settings.
- Update credit computation formula to: new credit cost = base / generation + (generationTier * tier level).
- For bulk transfer, when no data is transferred, should display error message.
- For bulk transfer, only data with changes should be sent to the API to not ruin the statistics for the backend.
- For bulk transfer, disable Cancel button when 2 products or less is left.
- Update error message in product edit generation when all fields failed to generate, display the message from API, if at least one item fails and some succeeds, a different error message will be displayed: "Some text has been generated but there has been an error generating other text. See error below.".
- In product edit, transfer and save should only be sent to the API if a change is made.
- Display search volume for keyword ideas as it is returned from the API without formatting.
- Various translation updates for supported languages.
- Remove "Account/Login" in login url in setup.

### Fixed

- Other Product Details in product edit page should only be sent to the API if its checkbox is enabled.
- Fix Reset link in WriteText.ai Status wherein the full row is clickable instead of just the button link
- Fix various translated text issues.
- Fix various UI issues for other languages for the list check all checkbox and history title display.
- Fix User Roles translation display in the User List and User Manage pages.
- Fix issue with Collapse/Expand All button in product edit page.
- Fix sort arrow alignment issue for Date Transferred field in list.
- Auto reconnect to signalr when user is idle and returns back to WriteText interface.

## [1.9.1] - 2023-11-06

### Added

- Additional help text under tones tooltip in product edit page regarding the "highlight potentially incorrect pronouns" option
- Added translation for new help text for the "highlight potentially incorrect pronouns" option for all supported language

### Fixed

- Locale should be based on site language instead of user defined language 
- Fix issue with WriteText Status filter in product list not displaying result because the incorrect locale is used when using Polylang

## [1.8.1] - 2023-10-27

### Added

- Added translation for NO, SV, FR, ES, PT, NL, CA and IT
- Added default min and max length for product description (75-150) and product excerpt (25-50) during initial setup and settings

### Changed

- Updated translation for DE and DA

### Fixed

- Fix Catalan (CA) language incorrect country displayed in Keyword Analysis. Spain should be selected for Catalan language.

## [1.7.6] - 2023-10-20

### Changed

- Updated translation for DE and DA
- Updated supported language to include Italian (IT) and Catalan (CA)

## [1.7.5] - 2023-10-17

### Fixed

- Fix issues related to translation and overlapping elements because of long translation texts

## [1.7.4] - 2023-10-17

### Changed

- Updated translation texts for DA and DE for various texts in single product, product listing and settings page

## [1.7.3] - 2023-10-12

### Changed

- Updated error message displayed for force plugin update to come from the API instead
- Update color hex for semantic keyword to make it more visible for other screen monitors

## [1.7.2] - 2023-10-11

### Changed

- Reposition "Highlight potentially incorrect pronouns" checkbox inside tones and style dropdown
- Update plugin notice should not block WriteText.ai list generation and settings

### Fixed

- Fix various issues related to update plugin notification
- Fix various UI issues related to formal/informal condition state
- Keywords should be priority when highlighting texts instead of the semantic keyword 

## [1.7.1] - 2023-10-09

### Added

- Added stable plugin version and display an update error if the currently installed plugin is outdated.

### Fixed

- Fix various issues related to formal/informal tones and styles
- Fix various UI issues, prev and next behavior and country selected in keyword if locale has "informal"

## [1.6.1] - 2023-10-03

### Added

- Added condition for Formal/Informal tone, style and audience both for single product and bulk generation
- Added "Highlight potentially incorrect pronouns"
- Added support for other DE language codes: de_CH, de_CH_informal, de_AT
- Added translations for some DE texts

### Fixed

- Added handling when clicking Edit and Next Prev when there is an ongoing product generation. Generate button should be disabled and loading progress should be displayed on the product page.

## [1.5.1] - 2023-09-28

### Added

- Added handling for error display in case a field fails to generate in the complete generation popup
- Added handling for single product generation complete and failed status via signalr
- In single product generation, page title, page description and open graph generation should be queued in the background
- Internal: added "writetextai-requestid" in internal logging

### Changed

- Send only one request for all fields to generate in product single edit page
- Translation updates for DE and added support for de_DE_formal
- Removed call for /text/Credit/isAvailable/. This should now be handle in /generate/v2 instead
- Tones, Styles and Audience should show in locale language instead of EN

### Fixed

- Fixed various issues from QA related to credits

## [1.4.2] - 2023-09-25

### Added

- Added checking if credit for generation is still sufficient for the user's available credit limit

### Changed

- Updated various help text related for min and max length 

### Fixed

- Fix various issues from QA related to credit count display and computation

## [1.4.1] - 2023-09-15

### Added

- Added max limit for selected semantic keyword
- Added input textbox limit for keyword, custom tone, custom style, custom audience and other product details based on /rules fetched from the API
- Added char count limit display for keyword, custom tone, custom style, custom audience and other product details 
- Display char and word count for WordPress field values
- Display char and word count in product description and excerpt fields if reference product is selected in product edit page

### Changed

- Updated credit display count for single product edit page generation, rewrite and generation with reference product  
- Updated credit display count for bulk generation with and without reference product
- Do not display generation complete popup if error encountered during generation
- Display error message if terms and condition is not agreed when doing text generation, bulk generation and keyword get data

## [1.3.3] - 2023-09-08

### Fixed

- Fix issue with checking of api token expiration called multiple times per second on some installations
- Fix issue with wta-preview making calls to the api connection eventhough preview is not called which may cause flooding calls when called by bot crawlers

## [1.3.2] - 2023-09-06

### Changed

- Update PO translation file for da_DK
- Reposition Show Comparison Hover in Grid list

### Fixed

- Ensure correct price format for product attributes when site has DK language 
- Fix UI when there are long translation text for Grid > Status Filter

## [1.3.1] - 2023-08-29

### Added

- Select All checkbox for Bulk Transfer Grid
- Min and Max value for min and max length fields should be fetched from the API instead
- Added readme.txt

### Changed

- Text updates for Audience help text, warning notice when site language does not belong to supported languages and Rewrite Help text
- Remove user preference saving of reference product
    
### Fixed

- Fix various issues from QA related to Reference Product

## [1.2.1] - 2023-08-23

### Added

- Pass keyword analysis viewed count to generate text endpoint
- Pass reference product record id when generating text with reference product
- Added reference product in bulk generation popup
- Added support for languages: da, de, no, sv, fr, es, pt, is, nl
- Added WriteTextAI-PHPVersion in api call headers

### Changed

- Optimize fetching of reference products dropdown
- Change UX behavior for WriteText.ai status instead of disabled at first, it should be enabled and the radio button WriteText.ai status should be checked after
    
### Fixed

- Various fixes of issues from QA related to WriteText.ai filter status, product attributes and Save button state

## [1.1.1] - 2023-08-17
  
### Added

- Terms of Service, Privay Policy and Cookie Policy link on the plugin
- Adding plugin version and WordPress version on all API calls for future support

### Changed

- Disable Mark as Reviewed after transfer
    
### Fixed

- Inconsistent icon in dashboard menu for WriteText.ai grid and settings
- Various fixes for SKU display in the edit page and searching in grid list
- Various fixes for WriteText.ai grid list filter
- Fix product attribute with special character not included during product generation

## [1.0.1] - 2023-08-14
  
First major release of WriteText.ai for beta testers