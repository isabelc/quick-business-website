=== Quick Business Website ===
Author URI: http://smartestthemes.com
Plugin URI: http://smartestthemes.com/downloads/quick-business-website-plugin/
Contributors: SmartestThemes, isabel104
Donate link: http://isabelcastillo.com/donate/
Tags: business, business website, staff, services, announcements, company, quick site
Requires at least: 3.4
Tested up to: 3.6
Stable Tag: 1.3.4
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
- Table buttons in your post editor to easily create tables (makes it easy to create a pricelist)
- the wp-login.php page will show your blogname with a link to your home page, instead of "WordPress" linking to WordPress.
- Backend branding tweaks: You can change or remove the footer text in the WP Admin area. You can remove the WordPress links from Admin/tool bar.
- You can add your Google Analytics or other scripts via the options panel, without touching your code.

After you enter your business information, your site will then automatically have:

- Schema.org `LocalBusiness` microdata on the Contact page, as [recommended by Google](http://support.google.com/webmasters/bin/answer.py?hl=en&answer=99170&topic=1088472&ctx=topic), to help generate rich snippets for your business in Google search.
- an 'About' page
- the Contact page will display, in addition to the contact form, links to your business's social profiles (Facebook, Twitter, Google+, Youtube, etc.), business hours, address, phone, fax number, and email address (all optional). 

When you get your first review, your site will then automatically have:

- `aggregateRating` Schema.org microdata which will add stars to your website in Google search results, making your site stand out.


For more info, see the [FAQ](http://wordpress.org/plugins/quick-business-website/faq/) and the [plugin web page](http://smartestthemes.com/downloads/quick-business-website-plugin/).

For support, please use the [Support forum](http://wordpress.org/support/plugin/quick-business-website).

Contribute or fork it [on Github](https://github.com/isabelc/quick-business-website).


== Installation ==

1. Download the plugin file, `quick-business-website.zip`
2. Go to `Plugins -> Add New -> Upload` to upload the plugin.
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Go to the `Quick Business Website` menu page to enter your business information, and click "Save All Changes".
5. Use the 6 available QBW widgets at `Appearances --> Widgets`.

== Frequently Asked Questions ==

= Why do I get a "Not Found" error for staff, news, services, or reviews? =

If you disable either the staff, news, services, or reviews by un-checking the box in the plugin options panel "Preferences" tab, and then you later decide to enable one, you must click "Save All Changes" twice. This flushes the permalink settings. It will have the same effect as going to `Settings -> Permalinks` and clicking "Save Changes" twice. 

= Where can I request support, get help, or report an error? =

The plugin's [Support forum](http://wordpress.org/support/plugin/quick-business-website)


= How can I give back? =

[Please rate the plugin, Tweet about it, share it on Facebook](http://isabelcastillo.com/donate/), etc. Thank you. You can also follow me on your favorite social network: [Twitter](https://twitter.com/smartestthemes), [Facebook](https://www.facebook.com/SmartestThemes/), [Google Plus](https://plus.google.com/103171743862205247245/)
== Screenshots ==

1. Options panel
2. Staff page display samples showing name, job title, social links, and description
3. Reviews and review-submission form which appear on the Reviews page
4. Featured Services widget, Featured Announcements widget, and Staff widget
5. Contact page showing contact form and business info 

== Changelog ==

= 1.3.4 =
* Update: compatible with WP 3.6

= 1.3.3 = 
* Fixed - typo on displayed text.
* Fixed - 2 strings left unlocalized on last update
* Tweak - Added support links to readme.
* Removed - uneeded file in order to minify: js/ui.datepicker.js.

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
