=== Quick Business Website ===
Contributors: isabel104
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=isa%40smartestthemes%2ecom
Tags: business, business website, company, quick website, quick site, staff, services, announcements
Requires at least: 3.6
Tested up to: 4.8-alpha-40290
Stable tag: 1.5.1
License: GNU Version 2 or Any Later Version
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Business website to showcase your services, staff, announcements, a working contact form, and reviews.

== Description ==

Get a complete business website up with a few clicks. When you activate this plugin, your website will immediately have: 

- a Reviews page for visitors to review your business
- a Contact page with a working contact form
- a section to showcase your Staff, with links to their social profiles
- a section to showcase your Services
- an Announcements section to post sales, events, news, or anything
- 6 Quick Business Website widgets
- the wp-login.php page will show your blogname with a link to your home page, instead of "WordPress" linking to WordPress.
- Backend branding tweaks: You can upload your own logo for your clients to see in the backend. You can change or remove the footer text in the WP Admin area. You can remove the WordPress links from Admin/tool bar.
- You can add your Google Analytics or other scripts via the options panel, without touching your code.

After you enter your business information, your site will then automatically have:

- Schema.org `LocalBusiness` microdata on the Contact page, as [recommended by Google](http://support.google.com/webmasters/bin/answer.py?hl=en&answer=99170&topic=1088472&ctx=topic), to help generate rich snippets for your business in Google search.
- an 'About' page
- the Contact page will display, in addition to the contact form, links to your business's social profiles (Facebook, Twitter, Google+, Youtube, etc.), business hours, address, phone, fax number, and email address (all optional). 

When you get your first review, your site will then automatically have:

- `aggregateRating` Schema.org microdata which will add stars to your website in Google search results, making your site stand out.

For more info, see the [Documentation](https://isabelcastillo.com/docs/quick-business-website).


== Installation ==

1.  In your WordPress dashboard, go to "Plugins -> Add New", and search for "Quick Business Website".
2.  Click to install and then Activate the plugin.
3. In your WordPress dashboard, go to the "Quick Business Website" page to enter your business information, and click "Save All Changes".
4. You can use the 6 available QBW widgets at "Appearances --> Widgets".

For more step-by-step instructions, see the [Documentation](https://isabelcastillo.com/docs/quick-business-website).

== Frequently Asked Questions ==

= Why do I get a "Not Found" error for staff, news, services, or reviews? =

If you disable either the staff, news, services, or reviews by un-checking the box in the plugin options panel "Preferences" tab, and then you later decide to enable one, you must click "Save All Changes" twice. This flushes the permalink settings. It will have the same effect as going to `Settings -> Permalinks` and clicking "Save Changes" twice. 

== Screenshots ==

1. Options panel
2. Staff page display samples showing name, job title, social links, and description
3. Reviews and review-submission form which appear on the Reviews page
4. Featured Services widget, Featured Announcements widget, and Staff widget
5. Contact page showing contact form and business info 

== Changelog ==

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
