<?php 
/**
 * Contact form shortcode that can be inserted on any page or post, with both jquery client-side and php server-side validation 
 * @package		Quick Business Website
 * @subpackage	Contact Module
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */
// set some strings
if (isset($_POST['smartestb_sbfc_name']))     $value_name     = htmlentities($_POST['smartestb_sbfc_name']);
if (isset($_POST['smartestb_sbfc_email']))    $value_email    = htmlentities($_POST['smartestb_sbfc_email']);
if (isset($_POST['sbfc_response'])) $value_response = htmlentities($_POST['sbfc_response']);
if (isset($_POST['sbfc_message']))  $value_message  = htmlentities($_POST['sbfc_message']);
$value_name     = isset($value_name) ? $value_name : '';
$value_email    =  isset($value_email) ? $value_email : '';
$value_response = isset($value_response) ? $value_response : '';
$value_message  = isset($value_message) ? $value_message : '';
$sbfc_strings = array(
	'name' 	 => '<input name="smartestb_sbfc_name" id="smartestb_sbfc_name" type="text" class="required" maxlength="99" value="'. $value_name .'" placeholder="Your name" />', 
	'email'    => '<input name="smartestb_sbfc_email" id="smartestb_sbfc_email" type="text" class="required email" maxlength="99" value="'. $value_email .'" placeholder="Your email" />', 
	'response' => '<input name="sbfc_response" id="sbfc_response" type="text" class="required number" maxlength="99" value="'. $value_response .'" />',	
	'message'  => '<textarea name="sbfc_message" id="sbfc_message" class="required" minlength="4" cols="33" rows="7" placeholder="Your message">'. $value_message .'</textarea>', 
	'error'    => ''
	);
/**
 * check for malicious input
 */
function sbfc_malicious_input($input) {
	$maliciousness = false;
	$denied_inputs = array("\r", "\n", "mime-version", "content-type", "cc:", "to:");
	foreach($denied_inputs as $denied_input) {
		if(strpos(strtolower($input), strtolower($denied_input)) !== false) {
			$maliciousness = true;
			break;
		}
	}
	return $maliciousness;
}
/**
 * check for spam
 */
function sbfc_spam_question($input) {
	global $smartestb_options;
	$response = '2';
	$response = stripslashes(trim($response));
	return ($input == $response);
}
/**
 * Get ip address
 */
function sbfc_get_ip_address() {
	if(isset($_SERVER)) {
		if(isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			$ip_address = $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif(isset($_SERVER["HTTP_CLIENT_IP"])) {
			$ip_address = $_SERVER["HTTP_CLIENT_IP"];
		} else {
			$ip_address = $_SERVER["REMOTE_ADDR"];
		}
	} else {
		if(getenv('HTTP_X_FORWARDED_FOR')) {
			$ip_address = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('HTTP_CLIENT_IP')) {
			$ip_address = getenv('HTTP_CLIENT_IP');
		} else {
			$ip_address = getenv('REMOTE_ADDR');
		}
	}
	return $ip_address;
}
/**
 * filter input
 */
function sbfc_input_filter() {

	if(!(isset($_POST['sbfc_key']))) { 
		return false;
	}
	$_POST['smartestb_sbfc_name']     = stripslashes(trim($_POST['smartestb_sbfc_name']));
	$_POST['smartestb_sbfc_email']    = stripslashes(trim($_POST['smartestb_sbfc_email']));
	$_POST['sbfc_message']  = stripslashes(trim($_POST['sbfc_message']));
	$_POST['sbfc_response'] = stripslashes(trim($_POST['sbfc_response']));

	global $smartestb_options, $sbfc_strings;
	$pass  = true;

	if(empty($_POST['smartestb_sbfc_name'])) {
		$pass = FALSE;
		$fail = 'empty';
		$sbfc_strings['name'] = '<input class="smartestb_sbfc_error" name="smartestb_sbfc_name" id="smartestb_sbfc_name" type="text" maxlength="99" value="'. htmlentities($_POST['smartestb_sbfc_name']) .'" placeholder="Your name" />';
	}
	if(!is_email($_POST['smartestb_sbfc_email'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$sbfc_strings['email'] = '<input class="smartestb_sbfc_error" name="smartestb_sbfc_email" id="smartestb_sbfc_email" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['smartestb_sbfc_email']) .'" placeholder="Your email" />';
	}
	if ($smartestb_options['smartestb_sbfc_captcha'] == 'true') {
		if (empty($_POST['sbfc_response'])) {
			$pass = FALSE; 
			$fail = 'empty';
			$sbfc_strings['response'] = '<input class="smartestb_sbfc_error" name="sbfc_response" id="sbfc_response" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['sbfc_response']) .'" placeholder="1 + 1 =" />';
		}
		if (!sbfc_spam_question($_POST['sbfc_response'])) {
			$pass = FALSE;
			$fail = 'wrong';
			$sbfc_strings['response'] = '<input class="smartestb_sbfc_error" name="sbfc_response" id="sbfc_response" type="text" size="33" maxlength="99" value="'. htmlentities($_POST['sbfc_response']) .'" placeholder="1 + 1 =" />';
		}
	}
	if(empty($_POST['sbfc_message'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$sbfc_strings['message'] = '<textarea class="smartestb_sbfc_error" name="sbfc_message" id="sbfc_message" cols="33" rows="7" placeholder="Your message">'. $_POST['sbfc_message'] .'</textarea>';
	}
	if(sbfc_malicious_input($_POST['smartestb_sbfc_name']) || sbfc_malicious_input($_POST['smartestb_sbfc_email'])) {
		$pass = false; 
		$fail = 'malicious';
	}
	if($pass == true) {
		return true;
	} else {
		if($fail == 'malicious') {
			$sbfc_strings['error'] = '<p id="sbfc_isa_error">' . __( 'Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases "mime-version", "content-type", "cc:" or "to:"', 'smartestb' ) . '</p>';
		} elseif($fail == 'empty') {

			$posted_msg = stripslashes($smartestb_options['smartestb_sbfc_error']);
			// in case they erase the default in admin
			$msg = ($posted_msg) ? $posted_msg : __( 'Please complete the required fields.', 'smartestb' );
			$sbfc_strings['error'] = '<p id="sbfc_isa_error">' . $msg . '</p>';
		} elseif($fail == 'wrong') {
			$sbfc_strings['error'] = '<p id="sbfc_isa_error">' . __( 'Oops. Incorrect answer for the security question. Please try again.', 'smartestb' ) . '<br />' . __( 'Hint: 1 + 1 = 2', 'smartestb' ) . '</p>';
		}
		return false;
	}
}
/**
 * shortcode to display contact form
 */
function sbfc_shortcode() {
	if (sbfc_input_filter()) {
		return sbfc_process_contact_form();
	} else {
		return sbfc_display_contact_form();
	}
}
add_shortcode('smartest_themes_contact_form','sbfc_shortcode');
/**
 * template tag to display contact form
 */
function smartest_themes_contact_form() {
	if (sbfc_input_filter()) {
		echo sbfc_process_contact_form();
	} else {
		echo sbfc_display_contact_form();
	}
}
/**
 * create contact page with working contact form
 * @uses insert_post()
 */
function sbf_create_contact_page() {
	if(get_option('smartestb_stop_contact') == 'false') {
		// CONTACT form is not disabled so do it	
		$bn = stripslashes_deep(esc_attr(get_option('smartestb_business_name')));
		$contitle = sprintf(__('Contact %s','smartestb'), $bn);
		global $Quick_Business_Website;
		$Quick_Business_Website->insert_post( 'page', esc_sql( _x('contact', 'page_slug', 'smartestb') ), 'qbw_contact_page_id', $contitle, '[smartest_themes_contact_form]' );
	}
}
add_action('after_setup_theme', 'sbf_create_contact_page');
// if contact page is disabled, delete the page
if(get_option('smartestb_stop_contact') == 'true') {
	wp_delete_post(get_option('qbw_contact_page_id'), true);
}
/**
 * enqueue CSS and validation script
 */
function sbfc_enqueue_scripts() {
	wp_register_script('sbfc-validate', plugins_url( '/sbfc-validate.js', __FILE__ ), array('jquery'));
	wp_register_style('contactstyle', plugins_url( '/contact.css', __FILE__) );
	if (is_page(get_option('qbw_contact_page_id'))){
		wp_enqueue_script('sbfc-validate');
		wp_enqueue_style('contactstyle');
	}
}
add_action('wp_enqueue_scripts', 'sbfc_enqueue_scripts');
/**
 * process contact form
 */
function sbfc_process_contact_form($content='') {
	global $smartestb_options, $sbfc_strings;
	
	$topic     = stripslashes($smartestb_options['smartestb_sbfc_subject']);
	$recipient = stripslashes($smartestb_options['smartestb_sbfc_email']);
	$recipname = stripslashes($smartestb_options['smartestb_sbfc_name']);
	$success   = stripslashes($smartestb_options['smartestb_sbfc_success']);
	// in case 4 defaults were deleted in admin
	$topic     = ! empty($topic) ? $topic : __( 'Message sent from your contact form', 'smartestb' );
	$recipient = ! empty($recipient) ? $recipient : get_bloginfo('admin_email');
	$recipname = ! empty($recipname) ? $recipname : __( 'Site Administrator', 'smartestb' );
	$success   = ! empty($success) ? $success : '<strong>' . __( 'Success! ', 'smartestb' ) . '</strong> ' . __( 'Your message has been sent.', 'smartestb');

	$name      = $_POST['smartestb_sbfc_name'];
	$email     = $_POST['smartestb_sbfc_email'];
	$recipsite = get_bloginfo('url');
	$senderip  = sbfc_get_ip_address();
	$offset    = $smartestb_options['smartestb_sbfc_offset'];
	$agent     = $_SERVER['HTTP_USER_AGENT'];
	$form      = getenv("HTTP_REFERER");
	$host      = gethostbyaddr($_SERVER['REMOTE_ADDR']);
	$date      = date("l, F jS, Y @ g:i a", time() + $offset * 60 * 60);

	$prepend = stripslashes($smartestb_options['smartestb_sbfc_prepend']);
	$append  = stripslashes($smartestb_options['smartestb_sbfc_append']);

	// Get the site domain and get rid of www.
	$sitename = strtolower( $_SERVER['SERVER_NAME'] );
	if ( substr( $sitename, 0, 4 ) == 'www.' ) {
		$sitename = substr( $sitename, 4 );
	}
	$from_email = 'wordpress@' . $sitename;

	$headers   = "MIME-Version: 1.0\n";
	$headers .= "From: " . get_bloginfo('name') . " <$from_email>\n";
	$headers .= "Reply-To: $email\n";
	$headers .= "Content-Type: text/plain; charset=\"" . get_option('blog_charset') . "\"";

	$message   = $_POST['sbfc_message'];
	$fullmsg   = ("Hello $recipname,

You are being contacted via $recipsite:

Name:     $name
Email:    $email
Message:

$message

-----------------------

Additional Information:

Site:   $recipsite
URL:    $form
Date:   $date
IP:     $senderip
Host:   $host
Agent:  $agent
");
	$fullmsg = stripslashes(strip_tags(trim($fullmsg)));
	wp_mail($recipient, $topic, $fullmsg, $headers);
	$results = ($prepend . '<div id="sbfc_isa_success"><div id="isa_success">' . $success . '</div>
<pre>Name:       '. $name    .'
Email:      '. $email   .'
Date:       '. $date .'
Message:    '. $message .'</pre><p class="sbfc_reset">[ <a href="'. $form .'">'. __( 'Click here to reset the form', 'smartestb' ) .'</a> ]</p></div>' . $append);

	echo $results;
}
/**
 * display contact form
 */
function sbfc_display_contact_form() {
	global $smartestb_options, $sbfc_strings;

	$captcha  = $smartestb_options['smartestb_sbfc_captcha'];
	$offset   = $smartestb_options['smartestb_sbfc_offset'];
	
	if ($smartestb_options['smartestb_sbfc_preform'] !== '') {
		$smartestb_sbfc_preform = $smartestb_options['smartestb_sbfc_preform'];
	} else { $smartestb_sbfc_preform = ''; }

	if ($smartestb_options['smartestb_sbfc_appform'] !== '') {
		$smartestb_sbfc_appform = $smartestb_options['smartestb_sbfc_appform'];
	} else { $smartestb_sbfc_appform = ''; }

	if ($captcha == 'true') {
		$captcha_box = '
				<fieldset class="sbfc-response">
					<label for="sbfc_response"> 1 + 1 = </label>
					'. $sbfc_strings['response'] .'
				</fieldset>';
	} else { $captcha_box = ''; }

	$sbfc_form = ($smartestb_sbfc_preform . $sbfc_strings['error'] . '
		<div id="sbfc-contactform-wrap">
			<form action="'. get_permalink() .'" method="post" id="sbfc-contactform">
				<fieldset class="sbfc-name">
					<label for="smartestb_sbfc_name">'. __( 'Name (Required)', 'smartestb' ) .'</label>
					'. $sbfc_strings['name'] .'
				</fieldset>
				<fieldset class="sbfc-email">
					<label for="smartestb_sbfc_email">'. __( 'Email (Required)', 'smartestb' ) .'</label>
					'. $sbfc_strings['email'] .'
				</fieldset>
					' . $captcha_box . '
				<fieldset class="sbfc-message">
					<label for="sbfc_message">'. __( 'Message (Required)', 'smartestb' ) .'</label>
					'. $sbfc_strings['message'] .'
				</fieldset>
				<div class="sbfc-submit">
					<input type="submit" name="Submit" id="sbfc_contact" value="' . __('Send email', 'smartestb') . '">
					<input type="hidden" name="sbfc_key" value="process">
				</div>
			</form>
		</div>
		' . $smartestb_sbfc_appform);
	return $sbfc_form;
}
?>