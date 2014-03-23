<?php
/**
 * Quick Business Website Options
 * @package	Quick Business Website
 * @author	Smartest Themes <isa@smartestthemes.com>
 * @license	http://opensource.org/licenses/gpl-license.php GNU Public License
 */
function smartestb_options(){
$slink = '<a href="'.admin_url('options-general.php').'">'. __('Settings', 'smartestb').'</a>';
$user_info = get_userdata(1);
if ($user_info == true) {
	$admin_name = $user_info->user_login;
} else {
	$admin_name = __( 'Site Administrator', 'smartestb' );
}
$currtime = date("l, F jS, Y @ g:i a");
$shortname = 'smartestb'; // @new shortname per framework
global $smartestb_options;
$smartestb_options = get_option('smartestb_options');
$options = array();
/* WELCOME */
$options[] = array( 'name' => __('Welcome','smartestb'),
						'type' => 'heading'
						);
$options[] = array( 'name' => __('Welcome to Quick Business Website by Smartest Themes!','smartestb'),
						'type' => 'info',
						'std' => __( 'On the left are tabs to customize your site, but everything is optional.<br /><br />To make your website more complete, enter these two tabs on the left:<br /><br />1. The <strong>About Page</strong> tab.<br />2. The <strong>Business Info</strong> tab.<br /><br />Then, take a moment to browse all the tabs so you can see what options are available. <br /><br />To get started, first click the \'<strong>Save all Changes</strong>\' button to save the plugin defaults.', 'smartestb' ) );

/* Business */

$options[] = array( 'name' => __('Business Info','smartestb'),'class' => 'money',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Business Name','smartestb'),
					'desc' => __('Enter the name of your business or organization.','smartestb'),
					'id' => $shortname.'_business_name',
					'type' => 'text');
				
$options[] = array( 'name' => __('Business Street Address','smartestb'),
                    'desc' => __('The street address of your business','smartestb'),
                    'id' => $shortname.'_address_street',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business suite or apartment number','smartestb'),
                    'id' => $shortname.'_address_suite',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business city','smartestb'),
                    'id' => $shortname.'_address_city',
					'class' => 'half',
                    'type' => 'text');
			
$options[] = array(
                    'desc' => __('Business state: if in the U.S., enter the state that your business is in','smartestb'),
                    'id' => $shortname.'_address_state',
					'class' => 'half',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business zip code','smartestb'),
                    'id' => $shortname.'_address_zip',
					'class' => 'half',
                    'type' => 'text');
					
$options[] = array(
                    'desc' => __('Business Country: the country that your business is in','smartestb'),
                    'id' => $shortname.'_address_country',
					'class' => 'half',
                    'type' => 'text');
			
$options[] = array( 'name' => __('Business Phone Number','smartestb'),
                    'desc' => __('Optional. Your business phone number to be displayed on your Contact page. Enter like 555-555-5555.','smartestb'),
                    'id' => $shortname.'_phone_number',
                    'type' => 'text');

$options[] = array( 'name' => __('Business Fax Number','smartestb'),
                    'desc' => __('Optional. Your business fax number to be displayed on your Contact page. Enter like 555-555-5555.','smartestb'),
                    'id' => $shortname.'_fax_numb',
                    'type' => 'text');

$options[] = array( 'name' => __('Display Business Email Address?','smartestb'),
					'desc' => sprintf(__('Check this to show your business email address on your Contact Page. You can change your email address in %s.', 'smartestb'), $slink ),
					'id' => $shortname.'_show_contactemail',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Google Map','smartestb'),
                    'desc' => sprintf(__( 'If you want to show a Google Map for your business address, paste here your HTML embed code from %s.','smartestb'), '<a href="http://maps.google.com/" title="Google Maps" target="_blank">Google Maps</a>' ),
                    'id' => $shortname.'_google_map',
                    'std' => '',
                    'type' => 'textarea');

					$options[] = array( 'name' => __('Business Hours','smartestb'),
						'desc' => __('Optional. Enter your hours here if you want to display them. Example:<br /><br />Monday - Friday: 7:30 am - 6:00<br />Saturday: 7:30 am - Noon<br /><br />', 'smartestb'),
					'id' => $shortname.'_hours',
					'std' => '',
					'type' => 'textarea');
/* Preferences */

$options[] = array( 'name' => __('Preferences','smartestb'),'class' => 'pencil',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Add Staff section?','smartestb'),
					'desc' => __('Check this to show your staff memebers.','smartestb'),
					'id' => $shortname.'_show_staff',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add News section?','smartestb'),
					'desc' => __('Check this to add an Announcements section.','smartestb'),
					'id' => $shortname.'_show_news',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add Services?','smartestb'),
					'desc' => __('Check this to show your services.','smartestb'),
					'id' => $shortname.'_show_services',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 	'desc' => sprintf( __('%s Set Custom Sort-Order? %s Check this to set a custom sort-order for services. Default sort-order is descending order by date of post.','smartestb'), '<strong>', '</strong>' ),
					'id' => $shortname.'_enable_service_sort',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add Reviews Section?','smartestb'),
					'desc' => __('Check this to add a page to let visitors submit reviews.','smartestb'),
					'id' => $shortname.'_add_reviews',
					'std' => 'true',
					'type' => 'checkbox');
/*  About Page */
$options[] = array( 'name' => __('About Page','smartestb'),'class' => 'aboutcircle',
					'type' => 'heading');
$options[] = array( 'name' => __('About Your Business','smartestb'),
						'desc' => __('The \'About Page\' is a page about your business. Type what you want your visitors to read here. It may be a history, a sales pitch, or anything you like. To enlarge the text area, drag the lower right corner down.', 'smartestb'),
					'id' => $shortname.'_about_page',
					'std' => '',
					'type' => 'textarea');
$options[] = array( 'name' => __('About Page Picture','smartestb'),
					'desc' => __('Upload a picture for your About page, or specify the image address of an online picture, like http://yoursite.com/picture.png','smartestb'),
					'id' => $shortname.'_about_picture',
					'std' => '',
					'type' => 'upload');
$options[] = array( 'name' => __('Disable About Page','smartestb'),
					'desc' => __('Check this to disable the About page altogether. Beware: this will permanently delete the About page from your website.', 'smartestb'),
					'id' => $shortname.'_stop_about',
					'std' => 'false',
					'type' => 'checkbox');

/* Social Media */
$options[] = array( 'name' => __('Social Media','smartestb'),'class' => 'smartsocial',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Facebook Page','smartestb'),
                    'desc' => __('The ID of your business Facebook page. Tip: the part of the address that comes after www.facebook.com/','smartestb'),
                    'id' => $shortname.'_business_facebook',
                    'type' => 'text');

$options[] = array( 'name' => __('Twitter','smartestb'),
                    'desc' => __('The username of your business Twitter profile. Tip: the part after \'@\'','smartestb'),
                    'id' => $shortname.'_business_twitter',
                    'type' => 'text');

$options[] = array( 'name' => __('Google Plus','smartestb'),
                    'desc' => __('The ID of your business Google Plus page.','smartestb'),
                    'id' => $shortname.'_business_gplus',
                    'type' => 'text');

$options[] = array( 'name' => __('YouTube','smartestb'),
                    'desc' => __('The name of your YouTube channel. Tip: Your Youtube name or ID, or the part of the address after www.youtube.com/user/','smartestb'),
                    'id' => $shortname.'_business_youtube',
                    'type' => 'text');
$options[] = array( 'name' => __('Another Profile','smartestb'),
                    'desc' => __('Add another business profile URL.  Example: http://www.linkedin.com/in/YourName','smartestb'),
                    'id' => $shortname.'_business_socialurl1',
                    'type' => 'text');
$options[] = array(
                    'desc' => __('Give a title for the business profile you entered above. Example: LinkedIn','smartestb'),
                    'id' => $shortname.'_business_sociallabel1',
                    'type' => 'text');

$options[] = array( 'name' => __('Another Profile','smartestb'),
                    'desc' => __('Add another business profile URL. Example: http://YourName.tumblr.com/','smartestb'),
                    'id' => $shortname.'_business_socialurl2',
                    'type' => 'text');
$options[] = array( 
                    'desc' => __('Give a title for the business profile you entered above. Example: Tumblr','smartestb'),
                    'id' => $shortname.'_business_sociallabel2',
                    'type' => 'text');

/* Branding */
$options[] = array( 'name' => __('Backend Branding','smartestb'),'class' => 'branding',
					'type' => 'heading');
$options[] = array( 'name' => __('Replace This Page\'s Logo','smartestb'),
					'desc' => __('See the "Smartest Themes" logo at the top of this page? Upload a logo here to replace this page\'s logo. Or specify the image address of your online logo, like http://yoursite.com/logo.png','smartestb'),
					'id' => $shortname.'_options_logo',
					'std' => '',
					'type' => 'upload');
$options[] = array( 'name' => __('Custom WP Admin Footer Text','smartestb'),
                    'desc' => __('By default, the text at the bottom of this page is "Thank you for creating with WordPress." Replace it with your own custom text here.','smartestb'),
                    'id' => $shortname.'_admin_footer',
                    'type' => 'textarea');
$options[] = array( 
                    'desc' => __('Or check here to completely remove the Admin Footer Text.','smartestb'),
                    'id' => $shortname.'_remove_adminfooter',
                    'type' => 'checkbox');
$options[] = array( 'name' => __('Remove WordPress Links From Admin Bar','smartestb'),
					'desc' => __('See the Wordpress link on the left of the bar across the top of this page? Check here to remove that link.','smartestb'),
					'id' => $shortname.'_remove_wplinks',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'type' => 'info',
						'std' => __('<em>Refresh this page to see the effect of these changes.</em>','smartestb')
						);
/* Contact form */

$options[] = array( 'name' => __( 'Contact Form','smartestb' ),
					'class' => 'mail',
					'type' => 'heading');
$options[] = array( 'name' => __( 'Your Name', 'smartestb' ),
                    'desc' => __( 'How would you like to be addressed in messages sent from the contact form?', 'smartestb' ),
                    'id' => $shortname.'_sbfc_name',
					'std' => $admin_name,
                    'type' => 'text');


$options[] = array( 'name' => __( 'Your Email', 'smartestb' ),
                    'desc' => __( 'Where would you like to receive messages sent from the contact form? If blank, the default is the admin email set in `Settings -> General`', 'smartestb' ),
                    'id' => $shortname.'_sbfc_email',
					'std' => '',
                    'type' => 'text');

$options[] = array( 'name' => __( 'Default Subject', 'smartestb' ),
                    'desc' => __( 'What should be the default subject line for the contact messages? Default is "Message sent from your contact form".', 'smartestb' ),
                    'id' => $shortname.'_sbfc_subject',
					'std' => __( 'Message sent from your contact form', 'smartestb' ),
                    'type' => 'text');

$options[] = array( 'name' => __( 'Success Message', 'smartestb' ),
                    'desc' => __( 'When the form is sucessfully submitted, this message will be displayed to the sender. Default is "Success! Your message has been sent."', 'smartestb' ),
                    'id' => $shortname.'_sbfc_success',
					'std' => '<strong>' . __( 'Success! ', 'smartestb' ) . '</strong> ' . __( 'Your message has been sent.', 'smartestb'),
                    'type' => 'textarea');
$options[] = array( 'name' => __( 'Error Message', 'smartestb' ),
                    'desc' => __( 'If the user skips a required field, this message will be displayed. Default is "Please complete the required fields."', 'smartestb' ),
                    'id' => $shortname.'_sbfc_error',
					'std' => '<strong>' . __( 'Please complete the required fields.', 'smartestb' ) . '</strong>',
                    'type' => 'textarea');

$options[] = array( 'name' => __( 'Enable Captcha', 'smartestb' ),
					'desc' => __( 'Check this box if you want to enable the captcha (challenge question/answer).', 'smartestb' ),
					'id' => $shortname.'_sbfc_captcha',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __( 'Time Offset', 'smartestb' ), 
                    'desc' => sprintf( __( 'Please specify the time offset from the "Current time" listed below. For example, +1 or -1. If no offset, enter "0" (zero).<br />Current time: %s <br /><br />', 'smartestb' ), $currtime ),
                    'id' => $shortname.'_sbfc_offset',
					'std' => '',
                    'type' => 'text');
$options[] = array( 'name' => __( 'Custom content before the form', 'smartestb' ),
					'desc' => __( 'Add some text/markup to appear <em>before</em> the contact form (optional).', 'smartestb' ),
					'id' => $shortname.'_sbfc_preform',
					'std' => '',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content after the form', 'smartestb' ),
					'desc' => __( 'Add some text/markup to appear <em>after</em> the contact form (optional).', 'smartestb' ),
					'id' => $shortname.'_sbfc_appform',
					'std' => '<div style="clear:both;">&nbsp;</div>',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content before results', 'smartestb' ),
					'desc' => __( 'Add some text/markup to appear <em>before</em> the success message (optional).', 'smartestb' ),
					'id' => $shortname.'_sbfc_prepend',
					'std' => '',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content after results', 'smartestb' ),
					'desc' => '<strong>' . __( 'Custom content after results.', 'smartestb' ) . '</strong> ' . __( 'Add some text/markup to appear <em>after</em> the success message (optional).', 'smartestb' ),
					'id' => $shortname.'_sbfc_append',
					'std' => '',
					'type' => 'textarea');

/* Scripts */

$options[] = array( 'name' => __('Scripts','smartestb'),'class' => 'scripts',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Add Analytics Code','smartestb'),
                    'desc' => __('Paste your analytics script here.','smartestb'),
                    'id' => $shortname.'_script_analytics',
                    'std' => '',
                    'type' => 'textarea');

$options[] = array( 'name' => __('Additional Scripts To Load','smartestb'),
                    'desc' => __('Paste any scripts here to be loaded into wp_head. Remember your script tags.','smartestb'),
                    'id' => $shortname.'_scripts_head',
                    'std' => '',
                    'type' => 'textarea');

/* Advanced */

$options[] = array( 'name' => __('Advanced','smartestb'),'class' => 'settings',
					'type' => 'heading');
				
$options[] = array( 'name' => __('Disable Contact Page','smartestb'),
					'desc' => sprintf( __( 'Check this to disable the Contact page. This will delete the automatically-created Contact page. You will still be able to use the shortcode to add a contact form: %s', 'smartestb' ), '<code>[smartest_themes_contact_form]</code>' ),
					'id' => $shortname.'_stop_contact',
					'std' => 'false',
					'type' => 'checkbox');

$options[] = array( 'name' => sprintf( __( 'Disable Extra Items on %s and %s', 'smartestb' ), '<code>wp_nav_menu</code>', '<code>wp_page_menu</code>' ),
					'desc' => sprintf( __('Check this to stop inserting extra menu items, such as "Staff", "Services", and "News", into %s and %s.', 'smartestb'), '<code>wp_nav_menu</code>', '<code>wp_page_menu</code>' ),
					'id' => $shortname.'_stop_menuitems',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Disable News Icon','smartestb'),
					'desc' => __('If an Announcement (News) post does not have a featured image, a news icon will show up as its featured image on the News page (if your <code>archive.php</code> shows the thumbnail) and in the Featured Announcements widget. Check this to get rid of that icon.', 'smartestb'),
					'id' => $shortname.'_stop_theme_icon',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Backwards Compatibility: Use Old Social Icons','smartestb'),
					'desc' => __('As of version 1.4.1, we use new icons for the social buttons that are Retina-ready for high resolution screens, and they change color to match your links color. Check this box to use the OLD social icons instead.', 'smartestb'),
					'id' => $shortname.'_old_social_icons',
					'std' => 'false',
					'type' => 'checkbox');
update_option('smartestb_template',$options);      
update_option('smartestb_shortname',$shortname);
}
?>