=== Quick Business Website ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=R7BHLMCQ437SS
Tags: business, business website, company, quick website, quick site, staff, services, announcements
Requires at least: 4.0
Tested up to: 4.8-alpha-40306
Stable tag: 2.0
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Business website to showcase your services, staff, announcements, contact form, reviews and rich snippets for Google search.

== Description ==

Get a complete business website up with a few clicks. When you activate this plugin, your website will immediately have: 

- an Announcements section to post sales, events, promotions, news, or anything
- a section to showcase your Staff, with links to their social media profiles
- a section to showcase your Services
- a Reviews page for visitors to review your business
- a Contact page with a working contact form
- 6 Quick Business Website widgets
- the wp-login.php page will show your site's own name with a link to your home page, instead of "WordPress" linking to WordPress.
- Backend branding tweaks: You can change or remove the footer text in the WP admin area. You can remove the WordPress links from Admin/tool bar.

After you enter your business information, your site will then automatically have:

- Structured data for `LocalBusiness` on the Home page and Contact page in JSON-LD format as [recommended by Google](http://support.google.com/webmasters/bin/answer.py?hl=en&answer=99170&topic=1088472&ctx=topic) to help generate rich snippets for your business in Google search.
- the Contact page will display, in addition to the contact form, links to your business's social profiles (Facebook, Twitter, Google+, Youtube, etc.), business hours, address, phone, fax number, and email address (all optional). 

When you get your first review, your site will then automatically have:

- Structured data for Reviews and for `aggregateRating` which will add stars to your website in Google search results, making your site stand out.

For more info, see the [Documentation](https://isabelcastillo.com/free-plugins/quick-business-website).

== Installation ==

1.  In your WordPress dashboard, go to "Plugins -> Add New", and search for "Quick Business Website".
2.  Click to install and then Activate the plugin.
3. In your WordPress dashboard, go to the "Quick Business Website" page to enter your business information, and click "Save All Changes".
4. You can use the 6 available QBW widgets at "Appearances --> Widgets".

For more detailed instructions, see the [Documentation](https://isabelcastillo.com/free-plugins/quick-business-website#docs-subheading-doc).

== Frequently Asked Questions ==

= Why do I get a "Not Found" error for staff, news, services, or reviews? =

If you disable either the staff, news, services, or reviews by un-checking the box in the plugin options panel "Preferences" tab, and then you later decide to enable one, you must click "Save All Changes" twice. This flushes the permalink settings. It will have the same effect as going to `Settings -> Permalinks` and clicking "Save Changes" twice. 

== Screenshots ==

1. Staff page shows each member's name, job title, and social media links
2. The options panel
3. Reviews and review-submission form which appear on the Reviews page
4. Featured Services widget, Featured Announcements widget, and Staff widget
5. Contact page showing contact form and business info including address, phone, hours, and social media links

== Changelog ==

= 2.0 = 
* New - Staff and Services archive pages have a much improved, grid-style display.
* New - Redesigned and improved options panel.
* New - Mobile responsive styling for the options page and the admin Reviews pages.
* New - Updated links to plugin documentation.
* New - Added a new option to elect to "Delete All Data On Uninstall."
* New - Added option to customize the form field labels, e.g. Name, Email, Message. The contact form submission button text can also be customized in the options panel.
* New - Menu items for custom post types are no longer automatically added. See the documentation page for how to add the menu items to your menu.
* New - Removed the Scripts tab from our options. The option to add an Analytics script, or any JavaScript, is no longer supported.
* New - Removed the Google Map from the Contact page. The option to add a Google Map is no longer supported by this plugin.
* New - Removed the logo upload option in the Backend Branding tab. This logo was displayed above our plugin options panel. Now, we will use the built-in site logo option. If a site didn't upload a site logo via its own theme, we will try to use the site icon, if it has one. Otherwise, no image will be shown above our options panel.
* New - Removed the About page tab from our options since users can simply add their own about page. If a user has added an image to our About page tab, that image is inserted into the about page content so that nothing is lost.
* New - Renamed the contact form shortcode.
* New - When a visitor submits a review, they will no longer be required to begin their website with http. Leaving out the http will no longer cause an error on the Reviews form.
* New - Replace all microdata with JSON-LD format structured data.
* New - Redirect to the settings page upon plugin activation for easier setup.
* Fix: Review stars were not showing on admin--on Review page in admin.
* Fix: Custom field values on reviews form were not being saved.
* Fix: Fix ugly link to admin in the reviews notification email that goes to the administrator.
* Fix: Default options were not saved upon initial activation, which is why users had to first click "Save all Changes" at first. This is no longer required as the issue is fixed.
* Fix: Fixed a bug in which the contact page layout was messed up while the success message was displayed after a use submitted the contact form.
* Tweak - Changed the Announcements dash menu icon.
* Tweak - Social media icons now have their brand colors. This applies to those using the newer default icons, not for those using the old legacy icons as those were already in color.
* Tweak - Removed "Quick Business Website" branding on options panel.
* Tweak - Update the QBW settings page URL.
* Tweak - Renamed CSS files and moved all CSS files into css directory.
* API - The plugin textdomain is now loaded on the init action rather than the plugins_loaded action.
* API - Removed unused JavaScript and jQuery scripts.
* API - Changed our options prefix.
* API - Delete options on uninstall rather than deactivation.
* API - Removed vt_resize() function in favor of the_post_thumbnail().
* API - Simplified metabox class.
* API - Set default services sort order when sort setting is enabled.
* API - Refactor names for reviews functions, classes and constants. New PHP constructors for reviews and reviews-admin.
* API - Use core gravatar function for Reviewer image, which shows on the admin side.
* API - Remove save_post action.
* API - New function `qbw_get_business_name()`.
* API - Don't store plugin version as option.
* API - Replace regex with esc_js() for escaping inline JavaScript.
* API - Delete custom post types, taxonomies, and terms on uninstall, if enabled.

= 1.5.1 =
* Fix - Removed one PHP notice.
* Tweak - Add clash notice for those using new Smartest Themes.

= 1.5 =
* New - Removed the MCE table-editing buttons from the WP editor.
* Fix - Added missing alt and height attributes to images in Featured Services widget, Featured News widget, and Staff widget.
* Fix - The CSS id #sstcontent has changed to a class. If you have any custom CSS that targets #sstcontent, you must change it to .sstcontent.
* Fix - The CSS id #sstwrap has changed to a class. If you have any custom CSS that targets #sstwrap, you must change it to .sstwrap.
* Fix - The CSS id #ssfig has changed to a class. If you have any custom CSS that targets #ssfig, you must change it to .ssfig.
* Fix - The CSS id #sfswrap has changed to a class. If you have any custom CSS that targets #sfswrap, you must change it to .sfswrap.
* Fix - The CSS id #sfsfig has changed to a class. If you have any custom CSS that targets #sfsfig, you must change it to .sfsfig.
* Fix - The CSS id #sfscontent has changed to a class. If you have any custom CSS that targets #sfscontent, you must change it to .sfscontent.
* Fix - The CSS id #sfawrap has changed to a class. If you have any custom CSS that targets #sfawrap, you must change it to .sfawrap.
* Fix - The CSS id #sfafig has changed to a class. If you have any custom CSS that targets #sfafig, you must change it to .sfafig.
* Fix - The CSS id #sfacontent has changed to a class. If you have any custom CSS that targets #sfacontent, you must change it to .sfacontent.
* Tweak - Changed Service Categories menu label to properly read Service Categories instead of just Categories.
* Tweak - Changed the Reviews microdata property from dateCreated to datePublished. 
* Tweak - Only load reviews stylesheet on the reviews page, rather than only when reviews are enabled. Testimonials widget used anywhere will remain unaffected.
* Maintenance - Removed PHP notices from widgets that appeared while adding widgets in the live customizer.
* Maintenance - Removed PHP notices from Reviews list.
* Maintenance - Updated Font Awesome icons version.
* Maintenance - unused vcard selector from reviews.css.

= 1.4.3 =
* Maintenance: replaced mysql_real_escape_string with esc_sql for compliance with PHP 5.5+ and WP 3.9.

= 1.4.2 =
* New: textdomain has changed to quick-business-website. You must update your language files with the new filename for translations to work.
* New: option to add phone number field to Contact form, with option to make it required.
* New: Do singleton of the plugin main class and mce table buttons class.
* Tweak: Updated description for Delete About page to stress that is will permanently delete the About page from the website.
* Maintenance: tested and passed for WP 3.9 compatibility.

= 1.4.1 =
* Fix: pending reviews showed up in some rare cases.
* New: added option to enable sort order for Services with backwards compatibility for those without it.
* New: Backend Branding let's you upload your own logo
* New: Font Awesome retina-ready icons for social buttons, with option to use old icons instead.
* New: menu dashicons for services, staff, announcements.
* New: .pot file for localization.
* Tweak: better style for Reviews form input fields.
* Tweak: enqueue the widget stylesheets only when widget is being used on a page.
* Tweak: removed generator tag from head for less markup.
* Tweak: added width and height to wp-login page text logo.
* Tweak: darker color for options fonts.
* Maintenance: fixed alignment for checkboxes in theme options.
* Maintenance: updated plugin URI.

= 1.4 =
* New: added services categories taxonomy.
* New: option on All Services Widget to limit by category.
* New: dynamic menu will automatically populate service sub-menu with service category terms.
* Tweak: on smartest reviews home page aggregate rating microdata, changed reviewCount to ratingCount.
* Tweak: added line breaks to Reviews page business address.
* Bug fix: Staff sort order number will populate default number in order to avoid leaving staff out of list because of missing sort order number.
* Tweak: better CSS for staff widget and for Contact page.
* Tweak: changed email headers in contact module to send from site and added Reply to: visitor.
* Tweak: Changed query_posts to new WP_Query in all widgets.
* Tested for WP 3.8 compatibility.

= 1.3.9 =
* New: option to show a different amount of testimonials instead of only showing 1.
* Tweak: Moved contact form script and style register outside the Contact page conditional so may be enqueued on any page, such as when using the shortcode.
* Maintenance: Removed unused variable from contact.php.

= 1.3.8 =
* Bug fix: missing Linkedin icon is restored.
* Bug fix: old Reviews didn't display in admin backend if Reviews page id had been changed, or if plugin had been deactivated and reactivated.
* Bug fix: ob_get_clean() in smartest-reviews.php needed conditional wrap.
* Tweak: moved 'smartest-reviews' register_script outside the Reviews page conditional so people can enqueue it on other pages.
* Tweak: better mobile CSS for Contact form and Reviews form.
* Maintenance: removed deprecated functions, PHP notices and warnings.

= 1.3.7 =
* Bug fix: fixed typo in query meta value for staff widget.
* Bug fix: staff sort order query was messing up custom nav menus.
* Tweak: changed staff widget query_posts to new WP_Query.
* Minified all CSS.

= 1.3.6 =
* Bug fix: code tag was left opened and messed up options panel.

= 1.3.5 =
* New: Testimonials widget works on multisite now.
* New: added link to Instruction Guides in readme and on Support tab of options panel.
* Bug fix: priority logic for grabbing contact form delivery email was off.
* Bug fix: 1 Smartest Reviews microdata declaration was broken.
* Bug fix: metabox class was causing conflict with some plugins, which in some cases would break the image uploader for inserting media into posts.
* Tweak: removed uneeded colopicker CSS and JS and datepicker.css
* Tweak: new donate link.
* Tweak: removed padding from Feat. Announcements widget title.
* Updated js/ajaxupload.php
* Updated helper text for 'Disable News Icon' option.

= 1.3.4 =
* Update: compatible with WP 3.6

= 1.3.3 = 
* Fixed: typo on displayed text.
* Fixed: 2 strings left unlocalized on last update
* Tweak: Added support links to readme.
* Removed: uneeded file in order to minify: ui.datepicker.js.

= 1.3.2 = 
* New - added sort order for staff, effecting on staff archive and widget.
* Tweak - using get_post_type_archive_link() instead of hardcoded url for news, services, and staff menu items

= 1.3.1 =
* Removed _vti_cnf files. 
* Tested up to WP 3.5.2.
* Tweak - update CSS to hide regular post meta on staff and services pages on Twenty Twelve.
* Tweak - active menu item will be highlighted for staff, news, and services on Twenty Twelve.
* New - add Linkedin to staff meta.

= 1.3 =
* Fixed typo, icon element should be i

= 1.2 =
* Tweak - made contact form responsive
* Changed support links

= 1.1 =
* Removed unnecessary javascript files.

= 1.0 =
* Initial release.

== Upgrade Notice ==

= 2.0 =
Staff and Services archive pages have a much improved, grid-style display.
