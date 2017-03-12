<?php
/**
 * Quick Business Website Options
 */
function smartestb_options(){
$slink = '<a href="'.admin_url('options-general.php').'">'. __('Settings', 'quick-business-website').'</a>';
$user_info = get_userdata(1);
if ($user_info == true) {
	$admin_name = $user_info->user_login;
} else {
	$admin_name = __( 'Site Administrator', 'quick-business-website' );
}
$currtime = date("l, F jS, Y @ g:i a");
$shortname = 'smartestb'; // @new shortname per framework
global $smartestb_options;
$smartestb_options = get_option('smartestb_options');
$options = array();
/* WELCOME */
$options[] = array( 'name' => __('Welcome','quick-business-website'),
						'type' => 'heading'
						);
$options[] = array( 'name' => __('Welcome to Quick Business Website','quick-business-website'),
						'type' => 'info',
						'std' => __( 'On the left are tabs to customize your site, but everything is optional.<br /><br />To make your website more complete, enter these two tabs on the left:<br /><br />1. The <strong>About Page</strong> tab.<br />2. The <strong>Business Info</strong> tab.<br /><br />Then, take a moment to browse all the tabs so you can see what options are available. <br /><br />To get started, first click the \'<strong>Save all Changes</strong>\' button to save the plugin defaults.', 'quick-business-website' ) );

/* Business */

$options[] = array( 'name' => __('Business Info','quick-business-website'),'class' => 'money',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Business Name','quick-business-website'),
					'desc' => __('Enter the name of your business or organization.','quick-business-website'),
					'id' => $shortname.'_business_name',
					'type' => 'text');
				
$options[] = array( 'name' => __('Business Street Address','quick-business-website'),
                    'desc' => __('The street address of your business','quick-business-website'),
                    'id' => $shortname.'_address_street',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business suite or apartment number','quick-business-website'),
                    'id' => $shortname.'_address_suite',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business city','quick-business-website'),
                    'id' => $shortname.'_address_city',
					'class' => 'half',
                    'type' => 'text');
			
$options[] = array(
                    'desc' => __('Business state: if in the U.S., enter the state that your business is in','quick-business-website'),
                    'id' => $shortname.'_address_state',
					'class' => 'half',
                    'type' => 'text');

$options[] = array(
                    'desc' => __('Business zip code','quick-business-website'),
                    'id' => $shortname.'_address_zip',
					'class' => 'half',
                    'type' => 'text');
					
$options[] = array(
                    'desc' => __('Business Country: the country that your business is in','quick-business-website'),
                    'id' => $shortname.'_address_country',
					'class' => 'half',
                    'type' => 'text');
			
$options[] = array( 'name' => __('Business Phone Number','quick-business-website'),
                    'desc' => __('Optional. Your business phone number to be displayed on your Contact page. Enter like 555-555-5555.','quick-business-website'),
                    'id' => $shortname.'_phone_number',
                    'type' => 'text');

$options[] = array( 'name' => __('Business Fax Number','quick-business-website'),
                    'desc' => __('Optional. Your business fax number to be displayed on your Contact page. Enter like 555-555-5555.','quick-business-website'),
                    'id' => $shortname.'_fax_numb',
                    'type' => 'text');

$options[] = array( 'name' => __('Display Business Email Address?','quick-business-website'),
					'desc' => sprintf(__('Check this to show your business email address on your Contact Page. You can change your email address in %s.', 'quick-business-website'), $slink ),
					'id' => $shortname.'_show_contactemail',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Google Map','quick-business-website'),
                    'desc' => sprintf(__( 'If you want to show a Google Map for your business address, paste here your HTML embed code from %s.','quick-business-website'), '<a href="http://maps.google.com/" title="Google Maps" target="_blank">Google Maps</a>' ),
                    'id' => $shortname.'_google_map',
                    'std' => '',
                    'type' => 'textarea');

					$options[] = array( 'name' => __('Business Hours','quick-business-website'),
						'desc' => __('Optional. Enter your hours here if you want to display them. Example:<br /><br />Monday - Friday: 7:30 am - 6:00<br />Saturday: 7:30 am - Noon<br /><br />', 'quick-business-website'),
					'id' => $shortname.'_hours',
					'std' => '',
					'type' => 'textarea');
/* Preferences */

$options[] = array( 'name' => __('Preferences','quick-business-website'),'class' => 'pencil',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Add Staff section?','quick-business-website'),
					'desc' => __('Check this to show your staff memebers.','quick-business-website'),
					'id' => $shortname.'_show_staff',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add News section?','quick-business-website'),
					'desc' => __('Check this to add an Announcements section.','quick-business-website'),
					'id' => $shortname.'_show_news',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add Services?','quick-business-website'),
					'desc' => __('Check this to show your services.','quick-business-website'),
					'id' => $shortname.'_show_services',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 	'desc' => sprintf( __('%s Set Custom Sort-Order? %s Check this to set a custom sort-order for services. Default sort-order is descending order by date of post.','quick-business-website'), '<strong>', '</strong>' ),
					'id' => $shortname.'_enable_service_sort',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Add Reviews Section?','quick-business-website'),
					'desc' => __('Check this to add a page to let visitors submit reviews.','quick-business-website'),
					'id' => $shortname.'_add_reviews',
					'std' => 'true',
					'type' => 'checkbox');
/* Social Media */
$options[] = array( 'name' => __('Social Media','quick-business-website'),'class' => 'smartsocial',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Facebook Page','quick-business-website'),
                    'desc' => __('The ID of your business Facebook page. Tip: the part of the address that comes after www.facebook.com/','quick-business-website'),
                    'id' => $shortname.'_business_facebook',
                    'type' => 'text');

$options[] = array( 'name' => __('Twitter','quick-business-website'),
                    'desc' => __('The username of your business Twitter profile. Tip: the part after \'@\'','quick-business-website'),
                    'id' => $shortname.'_business_twitter',
                    'type' => 'text');

$options[] = array( 'name' => __('Google Plus','quick-business-website'),
                    'desc' => __('The ID of your business Google Plus page.','quick-business-website'),
                    'id' => $shortname.'_business_gplus',
                    'type' => 'text');

$options[] = array( 'name' => __('YouTube','quick-business-website'),
                    'desc' => __('The name of your YouTube channel. Tip: Your Youtube name or ID, or the part of the address after www.youtube.com/user/','quick-business-website'),
                    'id' => $shortname.'_business_youtube',
                    'type' => 'text');
$options[] = array( 'name' => __('Another Profile','quick-business-website'),
                    'desc' => __('Add another business profile URL.  Example: http://www.linkedin.com/in/YourName','quick-business-website'),
                    'id' => $shortname.'_business_socialurl1',
                    'type' => 'text');
$options[] = array(
                    'desc' => __('Give a title for the business profile you entered above. Example: LinkedIn','quick-business-website'),
                    'id' => $shortname.'_business_sociallabel1',
                    'type' => 'text');

$options[] = array( 'name' => __('Another Profile','quick-business-website'),
                    'desc' => __('Add another business profile URL. Example: http://YourName.tumblr.com/','quick-business-website'),
                    'id' => $shortname.'_business_socialurl2',
                    'type' => 'text');
$options[] = array( 
                    'desc' => __('Give a title for the business profile you entered above. Example: Tumblr','quick-business-website'),
                    'id' => $shortname.'_business_sociallabel2',
                    'type' => 'text');

/* Branding */
$options[] = array( 'name' => __('Backend Branding','quick-business-website'),'class' => 'branding',
					'type' => 'heading');

$options[] = array( 'name' => __('Custom WP Admin Footer Text','quick-business-website'),
                    'desc' => __('By default, the text at the bottom of this page is "Thank you for creating with WordPress." Replace it with your own custom text here.','quick-business-website'),
                    'id' => $shortname.'_admin_footer',
                    'type' => 'textarea');
$options[] = array( 
                    'desc' => __('Or check here to completely remove the Admin Footer Text.','quick-business-website'),
                    'id' => $shortname.'_remove_adminfooter',
                    'type' => 'checkbox');
$options[] = array( 'name' => __('Remove WordPress Links From Admin Bar','quick-business-website'),
					'desc' => __('See the Wordpress link on the left of the bar across the top of this page? Check here to remove that link.','quick-business-website'),
					'id' => $shortname.'_remove_wplinks',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'type' => 'info',
						'std' => __('<em>Refresh this page to see the effect of these changes.</em>','quick-business-website')
						);
/* Contact form */

$options[] = array( 'name' => __( 'Contact Form','quick-business-website' ),
					'class' => 'mail',
					'type' => 'heading');
$options[] = array( 'name' => __( 'Your Name', 'quick-business-website' ),
                    'desc' => __( 'How would you like to be addressed in messages sent from the contact form?', 'quick-business-website' ),
                    'id' => $shortname.'_sbfc_name',
					'std' => $admin_name,
                    'type' => 'text');
$options[] = array( 'name' => __( 'Your Email', 'quick-business-website' ),
                    'desc' => __( 'Where would you like to receive messages sent from the contact form? If blank, the default is the admin email set in `Settings -> General`', 'quick-business-website' ),
                    'id' => $shortname.'_sbfc_email',
					'std' => '',
                    'type' => 'text');
$options[] = array( 'name' => __( 'Default Subject', 'quick-business-website' ),
                    'desc' => __( 'What should be the default subject line for the contact messages? Default is "Message sent from your contact form".', 'quick-business-website' ),
                    'id' => $shortname.'_sbfc_subject',
					'std' => __( 'Message sent from your contact form', 'quick-business-website' ),
                    'type' => 'text');
$options[] = array( 'name' => __( 'Success Message', 'quick-business-website' ),
                    'desc' => __( 'When the form is sucessfully submitted, this message will be displayed to the sender. Default is "Success! Your message has been sent."', 'quick-business-website' ),
                    'id' => $shortname.'_sbfc_success',
					'std' => '<strong>' . __( 'Success! ', 'quick-business-website' ) . '</strong> ' . __( 'Your message has been sent.', 'quick-business-website'),
                    'type' => 'textarea');
$options[] = array( 'name' => __( 'Error Message', 'quick-business-website' ),
                    'desc' => __( 'If the user skips a required field, this message will be displayed. Default is "Please complete the required fields."', 'quick-business-website' ),
                    'id' => $shortname.'_sbfc_error',
					'std' => '<strong>' . __( 'Please complete the required fields.', 'quick-business-website' ) . '</strong>',
                    'type' => 'textarea');
$options[] = array( 'name' => __( 'Enable Captcha', 'quick-business-website' ),
					'desc' => __( 'Check this box if you want to enable the captcha (challenge question/answer).', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_captcha',
					'std' => 'true',
					'type' => 'checkbox');
$options[] = array( 'name' => __( 'Time Offset', 'quick-business-website' ), 
                    'desc' => sprintf( __( 'Please specify the time offset from the "Current time" listed below. For example, +1 or -1. If no offset, enter "0" (zero).<br />Current time: %s <br /><br />', 'quick-business-website' ), $currtime ),
                    'id' => $shortname.'_sbfc_offset',
					'std' => '',
                    'type' => 'text');
$options[] = array( 'name' => __( 'Add Phone Number Field', 'quick-business-website' ),
					'desc' => __( 'Check this box to add a phone number field to the contact form.', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_include_phone',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array(
					'desc' => sprintf(__( 'Make the phone number %srequired.%s This has no effect if you do not check the box above.', 'quick-business-website' ), '<strong>', '</strong>' ),
					'id' => $shortname.'_sbfc_required_phone',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __( 'Custom content before the form', 'quick-business-website' ),
					'desc' => __( 'Add some text/markup to appear <em>before</em> the contact form (optional).', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_preform',
					'std' => '',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content after the form', 'quick-business-website' ),
					'desc' => __( 'Add some text/markup to appear <em>after</em> the contact form (optional).', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_appform',
					'std' => '<div style="clear:both;">&nbsp;</div>',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content before results', 'quick-business-website' ),
					'desc' => __( 'Add some text/markup to appear <em>before</em> the success message (optional).', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_prepend',
					'std' => '',
					'type' => 'textarea');
$options[] = array( 'name' => __( 'Custom content after results', 'quick-business-website' ),
					'desc' => '<strong>' . __( 'Custom content after results.', 'quick-business-website' ) . '</strong> ' . __( 'Add some text/markup to appear <em>after</em> the success message (optional).', 'quick-business-website' ),
					'id' => $shortname.'_sbfc_append',
					'std' => '',
					'type' => 'textarea');

/* Scripts */

$options[] = array( 'name' => __('Scripts','quick-business-website'),'class' => 'scripts',
					'type' => 'heading');
					
$options[] = array( 'name' => __('Add Analytics Code','quick-business-website'),
                    'desc' => __('Paste your analytics script here.','quick-business-website'),
                    'id' => $shortname.'_script_analytics',
                    'std' => '',
                    'type' => 'textarea');

$options[] = array( 'name' => __('Additional Scripts To Load','quick-business-website'),
                    'desc' => __('Paste any scripts here to be loaded into wp_head. Remember your script tags.','quick-business-website'),
                    'id' => $shortname.'_scripts_head',
                    'std' => '',
                    'type' => 'textarea');

/* Advanced */

$options[] = array( 'name' => __('Advanced','quick-business-website'),'class' => 'settings',
					'type' => 'heading');
				
$options[] = array( 'name' => __('Disable Contact Page','quick-business-website'),
					'desc' => sprintf( __( 'Check this to disable the Contact page. This will delete the automatically-created Contact page. You will still be able to use the shortcode to add a contact form: %s', 'quick-business-website' ), '<code>[smartest_themes_contact_form]</code>' ),
					'id' => $shortname.'_stop_contact',
					'std' => 'false',
					'type' => 'checkbox');

$options[] = array( 'name' => __('Disable News Icon','quick-business-website'),
					'desc' => __('If an Announcement (News) post does not have a featured image, a news icon will show up as its featured image on the News page (if your <code>archive.php</code> shows the thumbnail) and in the Featured Announcements widget. Check this to get rid of that icon.', 'quick-business-website'),
					'id' => $shortname.'_stop_theme_icon',
					'std' => 'false',
					'type' => 'checkbox');
$options[] = array( 'name' => __('Backwards Compatibility: Use Old Social Icons','quick-business-website'),
					'desc' => __('As of version 1.4.1, we use new icons for the social buttons that are Retina-ready for high resolution screens, and they change color to match your links color. Check this box to use the OLD social icons instead.', 'quick-business-website'),
					'id' => $shortname.'_old_social_icons',
					'std' => 'false',
					'type' => 'checkbox');
update_option('smartestb_template',$options);      
update_option('smartestb_shortname',$shortname);
}
?>