=== WriteText.ai ===
Contributors: writetextai
Donate link: https://writetext.ai/
Tags: ai, gpt, woocommerce, copywriting, product description
Requires at least: 6.0
Tested up to: 6.6.2
Stable tag: 1.40.4
Requires PHP: 7.4
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
WC requires at least: 7.0.0

Copyright © 1902 Software
WriteText.ai lets you generate product descriptions right inside WordPress. No more import-export for your generated text.

== Description ==

[youtube https://youtu.be/EiCs0_iPxIo] 

With WriteText.ai, you'll experience AI-powered copywriting with the convenience of working directly inside WooCommerce.

WriteText.ai takes your existing product attributes including your product images and uses them as a basis for generating meta titles, meta descriptions, product descriptions, and Open Graph text, so you don’t even have to worry about coming up with the right prompts. 

With WriteText.ai version 1.40, you can now also generate text for category pages.

## WriteText.ai Single

This mode enables users to define the tone, style, and target market for a specific product, do detailed keyword analysis, and select semantic keywords along with specific product attributes for consideration in the generated text. WriteText.ai generates text within approximately 50 seconds, after which users can review and adjust the text before publishing. This mode focuses on creating tailored content for specific audience needs, with an emphasis on SEO.

## WriteText.ai Bulk

For generating text for multiple products simultaneously, WriteText.ai Bulk allows for the selection of multiple products to generate text in a single operation, applying a consistent tone, style, and attributes across all selected products. This mode is optimized for speed and efficiency, streamlining the content creation process for large-scale needs.

## Languages Supported 

WriteText.ai supports multiple languages, including Danish, Swedish, Norwegian, German, French, Portuguese, Spanish, Catalan, Dutch, and Italian. This feature allows businesses to automatically generate content in the language of their WooCommerce store, broadening their market reach and enhancing customer engagement across different regions.

WriteText.ai auto-adjusts its output to your webshop's language. Should the language be unavailable, English is set as the default. The interface is currently being translated.

## Features Include

- Connection to an unlimited number of ecommerce sites and user accounts.
- Direct content transfer and publishing to WooCommerce.
- Selection of product attributes, tone, and style.
- Customizable content length, target audience, and user roles.
- Advanced image analysis particularly for products with limited data
- AI suggestions for target markets.
- Keyword analysis and semantic keyword suggestions.
- Keyword density tracking for SEO.
- Text rewriting.
- Reference product, ensuring that the tone, style and layout follows and an existing product. 
- Content review history log and bulk content management.
- Multi-store support.
- Chrome extension for content management and tagging of products for fact checking or rewriting. 

= Other Details =

WriteText.ai relies on third-party services to fulfill different functions in the plugin. Our own proprietary API ([https://writetext.ai/](https://writetext.ai/)) is used for:

- Connecting to the appropriate regional server to ensure performance
- Validating the user’s premium status or subscription and monitoring credit balance
- Calculating the credit cost for certain actions
- Getting up-to-date keyword data
- Generating semantic keywords
- Generating text
- Fetching history log

The user must have a valid account at [https://platform.writetext.ai](https://platform.writetext.ai) in order to connect to this service. This connection is done upon installation through a setup wizard. You can read the [Terms of Service](https://platform.writetext.ai/terms) and the [Privacy Notice](https://platform.writetext.ai/privacy) in our website.

SignalR is a third-party JS file that is used to send asynchronous notifications. In WriteText.ai, this is in the form of streaming text to the user as soon as the generation starts while the rest of the text is still being processed and received.
This is used to improve the user experience by providing a visual signal that progress is being made (i.e., via streaming the text) while text generation is not yet fully finished and to display notifications for certain bulk actions.

The user only needs their WriteText.ai account at [https://platform.writetext.ai](https://platform.writetext.ai) in order to connect to this service. [raw.githubusercontent.com/stefanpenner/es6-promise/master/LICENSE](https://raw.githubusercontent.com/stefanpenner/es6-promise/master/LICENSE) points to the license text for SignalR and is required to be included in the plugin as part of the license conditions.

== Installation ==
= Minimum Requirements =

- WordPress 6.0 or later. Tested up to 6.6.2.
- PHP version 7.4 or later.
- WooCommerce 7.0.0 or later. Tested up to 9.3.1.

Any one of these SEO plugins

- Yoast SEO 20.7 or later. Tested up to 23.4.
- All in One SEO 4.3.9 or later. Tested up to 4.7.1.1.
- Rank Math SEO 1.0.114 or later. Tested up to 1.0.227.1.

Any one of these language plugins 

- Polylang 3.3.2 or later.
- WPML Multilingual CMS 4.6.2 or later.

= Setup =

You can also view this guide with screenshots in our page: [https://writetext.ai/how-to-install-woocommerce](https://writetext.ai/how-to-install-woocommerce).

### How to install WriteText.ai in a WordPress single site

1. Log in to your backend
2. In your left sidebar, search for Plugins > Add New.
3. Click the “Upload Plugin” button beside Add Plugins.
4. Upload the .zip file you have downloaded and click “Install Now”.
5. Once the plugin has finished installing, click “Activate Plugin”.
6. You will be redirected to the WriteText.ai plugin setup guide. Follow the instructions in the wizard and you will be ready to generate product text in no time.

### How to install WriteText.ai in a WordPress Multisite

1. Log in to your backend
2. In your top left menu, go to My Sites > Network Admin > Plugins.
3. Click the “Add New” button beside Plugins.
4. Click the “Upload Plugin” button beside Add Plugins.
5. Upload the .zip file you have downloaded and click “Install Now”.
6. Once the plugin has finished installing, click “Network Activate”.
7. You will be redirected to the WriteText.ai plugin setup guide. Follow the instructions in the wizard and you will be ready to generate product text in no time.

== Frequently Asked Questions ==

= What basis does WriteText.ai use to generate product texts? =

Out of the box, WriteText.ai takes your product name and (if available) product attributes and uses all this information to generate text. Aside from this, you have the ability to set the tone, style, and target audience in order to influence how the text will be written.

With premium features, you can also set target keywords and select related semantic keywords, add other product details, analyze your product images, and write your own custom tone, style, and audience. WriteText.ai will take them into consideration when generating your product text.

= How accurate is the AI-generated text? =

The more information available for your product (i.e., attributes, target keywords and semantic keywords, other product details, images), the more accurate WriteText.ai can be when generating your product text. If you generate text with only the product name as the available information, WriteText.ai will still generate text but it may not be as accurate as when you have more complete product information.

= Can I be sure that the text generated is 100% unique? =

We cannot assure 100% text uniqueness as it's generated by artificial intelligence. However, given that the AI is guided by your keywords (from your keyword analysis) and chosen product attribute(s), there's a high likelihood of producing unique text. The "Other product details" feature enables you to provide written instructions to the AI, enhancing text uniqueness even further.

We recommend reviewing the text prior to publishing to ensure it aligns with your standards and desired uniqueness level.

= How can I ensure the quality of the generated text? =

In general, the more information that you provide about your product (e.g., keywords and semantic keywords, product attributes, other product details), the higher quality the text that WriteText.ai will generate for you, especially if you have a longer target length.

If you encounter nonsensical phrases in the generated text, try the following:

- Provide more context by adding target keywords and selecting semantic keywords.
- Ensure that the product attributes are properly set.
- Add more information in the "Other product details" field.
- Set a shorter maximum length for product descriptions.

= How can I ensure that the generated product text is SEO friendly? =

WriteText.ai has a Keyword Analysis tool* where you can set target keywords and get up-to-date data on search volume and competition. We recommend setting the right keywords (including choosing relevant semantic keywords) for your product so WriteText.ai can incorporate them in generating the text.

*Keyword Analysis is a premium feature.

== Screenshots ==

1. Get an overview of your products and their corresponding texts.
2. Set the tone, style, audience, and length for bulk generation.
3. Bulk transfer text for seamless publishing to WordPress.
4. Edit individual products for better customization.
5. Built-in keyword analysis tool with up-to-date search volume and difficulty data.
6. Choose from predefined tones and styles or write your own.
7. Select your audience or get AI-suggested target markets.
8. Generate SEO-optimized image alt text.
9. Get an overview of your categories.
10. Select representative products to generate contextually relevant category text.

== Changelog ==

= 1.40.4 2024-10-10 =

* Fix - Fix the issue with the credit count not updating after generating alt image text only on the product edit page.
* Fix - Fix the incorrect label displayed in the Transfer/Save metabox on the product single page.

= 1.40.3 2024-10-02 =

* Update - Minor readme.txt updates.
* Fix - Remove other settings in footer when force plugin update is effective.

= 1.40.2 2024-10-01 =

* Update - Minor readme.txt updates.
* Update - Popup blocker label update for the settings page.

= 1.40.1 2024-10-01 =

* Add - Spellcheck suggestion for selected keywords.
* Add - Product category text generation, transfer, saving and mark as reviewed.
* Add - Representative products for product categories.
* Add - Category grid, filter and history log.
* Add - Added the setting for the minimum and maximum length for the category description in the Global Settings page.
* Add - Added the setting for the minimum and maximum length for the category description in the Setup page.
* Add - Added a Send feedback link in the footer.
* Add - Free premium badge in the setup page and popup in other WTA pages for eligible accounts.
* Add - Added a retry for the bulk transfer when the transfer fails because of a server related issue. Displays an error message when the maximum number of retry fails.
* Update - Updated the menu to include a separate link for the product text generation page.
* Update - Updated the menu to include a link to the category text generation page.
* Update - Updated the grid to include a headline to differentiate between Products and Categories pages.
* Update - Updated the error message when ticking Mark as reviewed for a product without any generated text yet.
* Update - Updated some user preference for the product category: Highlight keywords, show text on preview, pre selected text types, highlight incorrect.pronouns.
* Update - Display country popup in category page if not yet selected.
* Update - Updated UI for the footer.
* Update - Various text and label updates.
* Update - Mark as reviewed states for product and category.
* Update - Added a custom prefix to IDs and classes specifically used by WriteText.ai to avoid conflict with other browser extensions.
* Update - Make product featured image and alt text image as a part of the free feature.
* Update - Update product featured image to be checked by default in the setup page.
* Update - Update featured image to be saved per user preference instead of a product preference.
* Update - Update popup blocker dismiss behavior.
* Fix - Fix issue related to token for multisite users.
* Fix - Fix issue when searching in reference product for bulk popup and product edit page.
* Fix - Fix issue with incorrect checked state in Mark as reviewed for the product edit page.

= 1.30.7 2024-08-29 =

* Add - Added a dismissable notice to disable popup blocker in the setup, product list and settings page.
* Fix - Bug fixes.
* Fix - Fix issue with multisite - subdomain install token conflict.

= 1.30.6 2024-07-06 =

* Add - Added Refresh data in Your own keywords section. The functionality will be the same as the Refresh data in Suggested keywords. Clicking either button will refresh data for both sections.
* Update - Updated UI for Start AI-powered keyword analysis CTA to be sticky.
* Update - Refresh data should only appear when there is data to refresh for "Your own keywords" and "Suggested keywords" only. Refresh data will re-appear either when a new keyword is added or selected OR when data is stale. Refresh data for selected keywords and competitor keywords will always be available because SERP data can change daily.
* Update - Updated SERP overview to display 100 records instead of 5.
* Update - Updated the keywords to always be passed in lowercase even if the user has typed it another way.
* Update - Display no data for results without data fetched from the API for rank, intent, search volume and difficulty columns in Keywords to be included in your text and Your Own Keywords sections.

= 1.30.5 2024-07-03 =

* Update - Various help text updates for the keyword analysis and remove translation files.

= 1.30.4 2024-07-02 =

* Fix - Fix for competitor keyword displaying an empty as of date in the help text message.

= 1.30.3 2024-07-02 =

* Update - Various placeholder and help text updates for the keyword analysis feature.
* Update - Hide the keyword that is the same as the product name in the suggested ideas section.
* Fix - Fix the issue where the error message does not display when a stale error message is shown and a keyword that is the same as the product name is added.

= 1.30.2 2024-07-01 =

* Update - Remove "Rank" column in Keyword Analysis > Keywords your competitors are ranking on.
* Update - Update selected state for the image alt text. If at least one image is selected, when moving from the previous or to the next product, all images for that product should be selected by default. 

= 1.30.1 2024-06-28 =

* Add - Added MO/PO translations for CA, DA, DE, ES, FR, IT, NL, NO, PT and SV languages.
* Add - New meta "wtai-fid" for field list used by the extension.
* Add - Additional sections to keyword analysis: Selected keywords, ranked keywords, competitor keywords, your own keywords and suggested keywords.
* Add - Added Start AI-powered keyword analysis action in keyword analysis. Removed Get Data CTA.
* Add - Refresh data for selected keywords, competitor keywords and suggested keywords in keyword analysis.
* Add - Sorting, filtering and load more for ranked keywords, competitor keywords and suggested keywords in keyword analysis.
* Add - Adding of selected keywords from ranked keywords, competitor keywords, your own keywords and suggested keywords in keyword analysis.
* Add - SERP data for ranked and competitor for keyword analysis.
* Add - Additional option in bulk generation to use keywords you're currently ranking on.
* Add - Additional option to add special instructions in bulk generation.
* Add - Pass productName when doing keyword analysis and refresh data.
* Update - Mark as reviewed for image alt text.
* Update - Keyword Ideas > Notice: The server is currently handling a lot of requests. Please retry after a few minutes.
* Update - Display proceed button when a normal text field is generated together with an invalid image.
* Update - Update for country selection popup to only allow one country selection instead of multiple. Country will now be mapped per language based on WP locale. The country will now be based on the locale unless the locale has no default country like "ca" language.
* Update - UI overlay for keyword analysis and history popup.
* Update - Various responsive UI fixes

= 1.20.11 2024-04-24 =

* Update - Removed the redirect to the setup page after plugin activation for WordPress version 6.5.2 and above as per the new guideline of the new WP version. See details here: https://core.trac.wordpress.org/ticket/60992

= 1.20.10 2024-04-19 =

* Add - Added tooltip and disabled state for keyword input and semantic keyword selection if the total maximum value is reached.
* Add - Added improved API logging.
* Update - Updated some code to conform to WooCommerce coding standards while still adhering to the PHP Coding Standard Sniffer.
* Fix - Fix issue with product attribute not saving in the product edit page when the featured image checkbox is selected.
* Fix - Fix issue with featured image not displaying invalid image warning.
* Fix - Fix incorrect message displayed for only one or multiple invalid image in the alt text.

= 1.20.9 2024-03-26 =

* Add - Added wtai verification meta for security purposes.
* Update - Tested with latest version of WordPress 6.4.3, Yoast SEO 22.3 and WooCommerce 8.7.0.
* Update - Last edited date, last transferred date, history dates and review date should display in server timezone and format.
* Fix - Tagging of extension review as Done during transfer for both bulk and single for some languages.
* Fix - Formatting issue of transferred meta title, meta desc and open graph wherein breaks and new line was removed.
* Fix - Credit not updating real time when only generating, rewriting, generating with reference product for only one field.
* Fix - Fix wrong field generation status for first time generated texts.
* Fix - Plugins > Setting link should go to setup page if setup is not yet done.
* Fix - If newly setup plugin, the "select all" checbox in edit page is not selected by default.
* Fix - Various minor UI issues.

= 1.20.8 2024-03-21 =

* Add - Added Settings link in the plugins page.
* Update - Minor label updates.
* Update - Do not allow empty fields in saving and transfer of text types and alt images.
* Update - Consider "imageDataExpires" in checking if image is already uploaded in the server.
* Update - Updated the available credits display source of data to "availableCredits" instead of "totalCredits".
* Update - Do not allow Transfer selected and Saving in single edit page when generation is ongoing.
* Update - Review extension API calls should use WP language code instead of the mapped language codes.
* Update - Optimize calls to /Credit endpoint used in checking the accounts available credit and if account is premium.
* Fix - Fixed issue when hiding the bulk progress bar, the current ongoing progress is not displayed.
* Fix - Fixed incorrect bulk popup message left after clicking the OK button of the bulk prgress bar in edit page.
* Fix - Handling available credits display when an error is encountered while fetching the available credit limit.
* Fix - Other minor UI fixes.

= 1.20.7 2024-03-14 =

* Update - Updated plugin root filename.

= 1.20.6 2024-03-14 =

* Update - Removed credit cost in Generate / Rewrite CTAs for both single and bulk generation.
* Update - Removed credit cost in Get Data CTA in Keyword Analysis.
* Update - Added available credit display in the sticky footer together with other links. Available credit display is updated during page reload, after each field generation in single edit page and after each progress in bulk generation.

= 1.20.5 2024-03-07 =

* Update - Updated review extension custom meta.
* Update - Updated the maximum images for alt text to 10 images for both bulk generation and single edit page generation.
* Update - Enable rewrite for selected image alt text which has current platform value.
* Fix - Fix various issues from QA related to Version 1.20.4 updates.

= 1.20.4 2024-03-05 =

* Update - Updated credit cost computation display for image alt text in bulk generation to be per product.
* Fix - Fix various issues from QA related to Version 1.20.3 updates.

= 1.20.3 2024-03-01 =

* Update - Coding Standard Update: Sanitize custom queries and pass the parameters in $wpdb:prepare statements.
* Update - Disable closing, prev and next CTAs during image processing in the single edit product page.
* Update - Disable closing, prev and next CTAs during transfer in single edit product.
* Update - Various text and label updates.
* Fix - Fix various issues from QA related to Version 1.20.2 updates.

= 1.20.2 2024-02-22 =

* Add - Include the image alt text in bulk generation.
* Add - Include the image alt text in bulk transfer.
* Add - Credit calculation in bulk generation should also consider the image alt text generation.
* Add - By batch uploading of images to the API to prevent timeout issue for bulk generation if image alt text generation and featured image is enabled.
* Update - By batch uploading of images to the API to fix timeout issue for single product edit page.
* Update - Apply unified API call for image alt text generation and normal text types generation.
* Update - Transfer should tag the extension review as Done.
* Update - UI update for the review extension status. For rewrite and for factual check should be combined.
* Update - Make image alt text and featured image a premium feature.
* Update - UI update for the single generation confirmation popup.
* Update - Update the list of items in the premium list popup to include the image alt text and feature image.
* Update - Remove disable of image alt text when reference and rewrite is selected in single edit page.
* Update - Various text and label updates.
* Fix - Fix various issues from QA related to featured image, image alt text generation and extension reviews.
* Fix - Fix issue in installation page when a plugin can't be downloaded. This should display the error: "Can't activate the plugin. Please try again or contact your site's administrator."

= 1.20.1 2024-02-12 =

* Add - Review status from browser extension in grid WriteText.ai status filter.
* Add - Display and tagging of "Done" of review status from browser extension: For rewrite and For factual checking in single edit page 
* Add - Additional featured image option in product attributes section for the setup page, settings page, bulk popup settings and single edit page.
* Add - Use featured image in generation of texts for both bulk and single edit page.
* Add - Alt text generation of text in single edit page.
* Add - Transfer/Saving alt text for images.
* Add - Uploading of image to the API for featured image and alt text image.
* Add - Notice and error handling for images uploaded used for generation and alt text.
* Add - Split alt text generation request based on maxImageAltTextPerRequest from the API.
* Add - Disable alt text for rewrite and reference product.
* Add - Image alt text checkbox state based on user preference.
* Update - Include credit alt text in credits computation based on additional credit and rules from the API.
* Update - Various tooltips and label updates to align with the new featured image and alt text feature.
* Update - Hide Show text preview on hover on tablet to mobile breakpoints.
* Update - Open WTA for all languages and display notice on WTA pages informing the user that the translation is ongoing.
* Update - Revamp UI for keyword ideas filter and sorting.
* Fix - Remove unused "toggle panel" screen reader text in single edit page.
* Fix - Fix character count display in Custom tone and style and when selecting other tone and style.

See version history here: [https://writetext.ai/version-history](https://writetext.ai/version-history)

== Upgrade Notice ==

= 1.40.4 =

Please upgrade, to ensure all plugin features works as expected.