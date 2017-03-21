<?php 
/**
 * Contact form shortcode that can be inserted on any page or post, with both jquery client-side and php server-side validation 
 * @package		Quick Business Website
 * @subpackage	Contact Module
 */

$value_name     = isset( $_POST['smartestb_sbfc_name'] ) ? esc_html( $_POST['smartestb_sbfc_name'] ) : '';
$value_email    = isset( $_POST['smartestb_sbfc_email'] ) ? esc_html( $_POST['smartestb_sbfc_email'] ) : '';
$value_response = isset( $_POST['sbfc_response'] ) ? esc_html( $_POST['sbfc_response'] ) : '';
$value_message  = isset( $_POST['sbfc_message'] ) ? esc_html( $_POST['sbfc_message'] ) : '';
$value_phone = isset( $_POST['sbfc_phone']) ? esc_html( $_POST['sbfc_phone'] ) : '';

$require_phone = ( get_option( 'qbw_sbfc_required_phone') == 'true' ) ? ' class="required"' : '';

$qbw_form_strings = array(
	'name' 	 => '<input name="smartestb_sbfc_name" id="smartestb_sbfc_name" type="text" class="required" maxlength="99" value="'. $value_name .'" placeholder="' . __( 'Your name', 'quick-business-website' ) . '" />', 
	'email'    => '<input name="smartestb_sbfc_email" id="smartestb_sbfc_email" type="text" class="required email" maxlength="99" value="'. $value_email .'" placeholder="' . __( 'Your email', 'quick-business-website' ) . '" />', 
	'response' => '<input name="sbfc_response" id="sbfc_response" type="text" class="required number" maxlength="99" value="'. $value_response .'" />',	
	'message'  => '<textarea name="sbfc_message" id="sbfc_message" class="required" minlength="4" cols="33" rows="7" placeholder="Your message">'. $value_message .'</textarea>',
	'phone'	=> '<input name="sbfc_phone" id="sbfc_phone" type="text" size="33" ' . $require_phone . 'maxlength="99" value="'. $value_phone.'" placeholder="Your phone" />',
	'error'    => ''
	);

/**
 * check for malicious input
 */
function qbw_contact_malicious_input($input) {
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
function qbw_contact_spam_question($input) {
	$response = '2';
	$response = stripslashes(trim($response));
	return ($input == $response);
}
/**
 * Get ip address
 */
function qbw_contact_get_ip_address() {
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
function qbw_contact_input_filter() {
	if ( ! ( isset( $_POST['sbfc_key'] ) ) ) { 
		return false;
	}

	$_POST['smartestb_sbfc_name'] = stripslashes(trim($_POST['smartestb_sbfc_name']));
	$_POST['smartestb_sbfc_email'] = stripslashes(trim($_POST['smartestb_sbfc_email']));
	$_POST['sbfc_message'] = stripslashes(trim($_POST['sbfc_message']));
	$_POST['sbfc_response'] = stripslashes(trim($_POST['sbfc_response']));
	$_POST['sbfc_phone'] = isset($_POST['sbfc_phone']) ? stripslashes(trim($_POST['sbfc_phone'])) : '';

	global $qbw_options, $qbw_form_strings;
	$pass  = true;

	if(empty($_POST['smartestb_sbfc_name'])) {
		$pass = FALSE;
		$fail = 'empty';

		$qbw_form_strings['name'] = '<input class="smartestb_sbfc_error" name="smartestb_sbfc_name" id="smartestb_sbfc_name" type="text" maxlength="99" value="'. esc_html( $_POST['smartestb_sbfc_name'] ) .'" placeholder="' . __( 'Your name', 'quick-business-website' ) . '" />';
	}
	if(!is_email($_POST['smartestb_sbfc_email'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$qbw_form_strings['email'] = '<input class="smartestb_sbfc_error" name="smartestb_sbfc_email" id="smartestb_sbfc_email" type="text" size="33" maxlength="99" value="'. esc_html( $_POST['smartestb_sbfc_email'] ) .'" placeholder="' . __( 'Your email', 'quick-business-website' ) . '" />';
	}
	if ($qbw_options['qbw_sbfc_captcha'] == 'true') {
		if (empty($_POST['sbfc_response'])) {
			$pass = FALSE; 
			$fail = 'empty';
			$qbw_form_strings['response'] = '<input class="smartestb_sbfc_error" name="sbfc_response" id="sbfc_response" type="text" size="33" maxlength="99" value="'. esc_html( $_POST['sbfc_response'] ) .'" placeholder="1 + 1 =" />';
		}
		if (!qbw_contact_spam_question($_POST['sbfc_response'])) {
			$pass = FALSE;
			$fail = 'wrong';
			$qbw_form_strings['response'] = '<input class="smartestb_sbfc_error" name="sbfc_response" id="sbfc_response" type="text" size="33" maxlength="99" value="'. esc_html( $_POST['sbfc_response'] ) .'" placeholder="1 + 1 =" />';
		}
	}
	if(empty($_POST['sbfc_message'])) {
		$pass = FALSE; 
		$fail = 'empty';
		$qbw_form_strings['message'] = '<textarea class="smartestb_sbfc_error" name="sbfc_message" id="sbfc_message" cols="33" rows="7" placeholder="Your message">'. esc_textarea( $_POST['sbfc_message'] ) .'</textarea>';
	}
	if ($qbw_options['qbw_sbfc_required_phone'] == 'true') {
		if (empty($_POST['sbfc_phone'])) {
			$pass = FALSE; 
			$fail = 'empty';
			$qbw_form_strings['phone'] = '<input class="smartestb_sbfc_error" name="sbfc_phone" id="sbfc_phone" type="text" size="33" maxlength="99" value="'. esc_html( $_POST['sbfc_phone'] ) . '" />';
		}
	}
	if(qbw_contact_malicious_input($_POST['smartestb_sbfc_name']) || qbw_contact_malicious_input($_POST['smartestb_sbfc_email'])) {
		$pass = false; 
		$fail = 'malicious';
	}
	if ( $pass == true ) {
		return true;
	} else {
		if($fail == 'malicious') {
			$qbw_form_strings['error'] = '<p id="qbw_error">' . __( 'Please do not include any of the following in the Name or Email fields: linebreaks, or the phrases "mime-version", "content-type", "cc:" or "to:"', 'quick-business-website' ) . '</p>';
		} elseif( 'empty' == $fail ) {
			$posted_msg = stripslashes( $qbw_options['qbw_sbfc_error'] );
			// in case they erase the default
			$msg = ( $posted_msg ) ? esc_html( $posted_msg ) : __( 'Please complete the required fields.', 'quick-business-website' );
			$qbw_form_strings['error'] = '<p id="qbw_error">' . $msg . '</p>';
		} elseif( $fail == 'wrong' ) {
			$qbw_form_strings['error'] = '<p id="qbw_error">' . __( 'Oops. Incorrect answer for the security question. Please try again.', 'quick-business-website' ) . '<br />' . __( 'Hint: 1 + 1 = 2', 'quick-business-website' ) . '</p>';
		}
		return false;
	}
}
/**
 * shortcode to display contact form
 */
function qbw_contact_shortcode() {
	if ( qbw_contact_input_filter() ) {
		return qbw_process_contact_form();
	} else {
		return qbw_contact_display_contact_form();
	}
}
add_shortcode( 'qbw_contact_form','qbw_contact_shortcode' );
/**
 * @todo at some point, remove this shortcode
 * @deprecated since version 2.0
 */
add_shortcode( 'smartest_themes_contact_form','qbw_contact_shortcode' );
/**
 * template tag to display contact form
 * @todo at some point, remove this function
 * @deprecated since version 2.0
 */
function smartest_themes_contact_form() {
	if ( qbw_contact_input_filter() ) {
		echo qbw_process_contact_form();
	} else {
		echo qbw_contact_display_contact_form();
	}
}
/**
 * create contact page with working contact form
 * @uses insert_post()
 */
function qbw_contact_create_contact_page() {
	if ( get_option( 'qbw_stop_contact') == 'false' ) {
		$bn = qbw_get_business_name();
		$contitle = sprintf( __( 'Contact %s','quick-business-website'), esc_html( $bn ) );
		global $Quick_Business_Website;
		$Quick_Business_Website->insert_post( esc_sql( _x('contact', 'page_slug', 'quick-business-website') ), 'qbw_contact_page_id', $contitle, '[qbw_contact_form]' );
	}
} 
add_action('after_setup_theme', 'qbw_contact_create_contact_page');
// if contact page is disabled, delete the page
if(get_option( 'qbw_stop_contact') == 'true') {
	wp_delete_post(get_option('qbw_contact_page_id'), true);
}
/**
 * enqueue CSS and validation script
 */
function qbw_contact_enqueue_scripts() {
	wp_register_script( 'qbw-contact-validate', QUICKBUSINESSWEBSITE_URL . 'modules/contact/validate.js', array('jquery' ) );
	wp_register_style( 'qbw-contact', QUICKBUSINESSWEBSITE_URL . 'modules/contact/contact.css' );
	if ( is_page( get_option( 'qbw_contact_page_id' ) ) ) {
		wp_enqueue_script( 'qbw-contact-validate' );
		wp_enqueue_style( 'qbw-contact' );
	}
}
add_action('wp_enqueue_scripts', 'qbw_contact_enqueue_scripts');
/**
 * process contact form
 */
function qbw_process_contact_form( $content='' ) {
	global $qbw_options;

	// Gather settings

	$subject   = stripslashes( $qbw_options['qbw_sbfc_subject'] );
	$recipient = stripslashes( $qbw_options['qbw_sbfc_email'] );
	$success   = stripslashes( $qbw_options['qbw_sbfc_success'] );
	
	// in case defaults were deleted
	$subject   = ! empty( $subject ) ? esc_html( $subject ) : __( 'Message sent from your contact form', 'quick-business-website' );
	$recipient = ! empty( $recipient ) ? $recipient : get_bloginfo('admin_email');
	$recipient = sanitize_email( $recipient );
	$success   = ! empty( $success ) ?
				wpautop( qbw_kses( $success ) ) :
				'<strong>' . __( 'Success! ', 'quick-business-website' ) . '</strong> ' . __( 'Your message has been sent.', 'quick-business-website');

	$append    = isset( $qbw_options['qbw_sbfc_append'] ) ?
				stripslashes( wpautop( qbw_kses( $qbw_options['qbw_sbfc_append'], true ) ) ) :
				'';
	$include_phone = isset( $qbw_options['qbw_sbfc_include_phone'] ) ? $qbw_options['qbw_sbfc_include_phone'] : '';

	// Gather form data
	
	$from_name = isset( $_POST['smartestb_sbfc_name'] ) ? esc_html( $_POST['smartestb_sbfc_name'] ) : '';
	$email     = isset( $_POST['smartestb_sbfc_email'] ) ? sanitize_email( $_POST['smartestb_sbfc_email'] ) : '';
	$message = isset( $_POST['sbfc_message'] ) ? esc_html( $_POST['sbfc_message'] ) : '';
	// Only show phone in message if it's enabled
	$phone = '';
	$i18n_phone = '';
	if ( 'true' == $include_phone ) {
		$phone = isset( $_POST['sbfc_phone'] ) ? esc_html( $_POST['sbfc_phone'] ) : '';
		$i18n_phone = __( 'Phone:', 'quick-business-website' );
	}

	$recipsite = esc_url( get_bloginfo('url') );
	$senderip  = esc_html( qbw_contact_get_ip_address() );
	$agent     = esc_html( $_SERVER['HTTP_USER_AGENT'] );
	$url       = esc_url( getenv("HTTP_REFERER") );
	$host      = esc_url( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );
	$date      = date_i18n( get_option( 'date_format' ) ) . ' @ ' . date_i18n( get_option( 'time_format' ) );
	$date      = esc_html( $date ); 
	
	// localize
	$i18n_hello = __( 'Hello', 'quick-business-website' );
	$i18n_intro = sprintf( __( 'You are being contacted via %s:', 'quick-business-website' ), $recipsite ); 
	$i18n_name = __( 'Name:', 'quick-business-website' );
	$i18n_email = __( 'Email:', 'quick-business-website' );
	$i18n_msg = __( 'Message:', 'quick-business-website' );
	$i18n_addtl_info = __( 'Additional Information:', 'quick-business-website' );
	$i18n_url = __( 'URL:', 'quick-business-website' );
	$i18n_date = __( 'Date:', 'quick-business-website' );
	$i18n_ip = __( 'IP:', 'quick-business-website' );
	$i18n_host = __( 'Host:', 'quick-business-website' );
	$i18n_agent = __( 'Agent:', 'quick-business-website' );

$fullmsg   = ("$i18n_hello,

$i18n_intro

$i18n_name		$from_name
$i18n_email	$email
$i18n_phone	$phone
$i18n_msg

$message

-----------------------

$i18n_addtl_info

$i18n_url    $url
$i18n_date   $date
$i18n_ip     $senderip
$i18n_host   $host
$i18n_agent  $agent
");
	$fullmsg = stripslashes( strip_tags( trim( $fullmsg ) ) );
	

	/************************************************************
	*
	* @todo must reinstate wp_mail after debuggin
	*
	************************************************************/
	
	//wp_mail( $recipient, $subject, $fullmsg );
	
	$results = '<div id="sbfc_success"><div id="qbw_success">' . $success .
	'</div><pre>' . $i18n_name . ' ' . $from_name . '
' . $i18n_email . ' ' . $email   . '
' . $i18n_phone . ' ' . $phone   . '
' . $i18n_msg . ' ' . $message . '</pre><p class="sbfc_reset">[ <a href="' . $url . '">' .
 __( 'Click here to reset form', 'quick-business-website' ) .'</a> ]</p></div>' . $append;

	return $results;
}
/**
 * display contact form
 */
function qbw_contact_display_contact_form() {
	global $qbw_options, $qbw_form_strings;
	$captcha_box = '';
	$phone_field = '';

	$captcha = $qbw_options['qbw_sbfc_captcha'];
	$include_phone = isset( $qbw_options['qbw_sbfc_include_phone'] ) ? $qbw_options['qbw_sbfc_include_phone'] : '';
	$label_name = isset( $qbw_options['qbw_sbfc_label_name'] ) ?
				esc_html( $qbw_options['qbw_sbfc_label_name'] ) :
				__( 'Name (Required)', 'quick-business-website' );

	$label_email = isset( $qbw_options['qbw_sbfc_label_email'] ) ?
				esc_html( $qbw_options['qbw_sbfc_label_email'] ) :
				__( 'Email (Required)', 'quick-business-website' );

	$label_message = isset( $qbw_options['qbw_sbfc_label_msg'] ) ?
				esc_html( $qbw_options['qbw_sbfc_label_msg'] ) :
				__( 'Message (Required)', 'quick-business-website' );

	$label_submit = isset( $qbw_options['qbw_sbfc_label_submit'] ) ?
				esc_html( $qbw_options['qbw_sbfc_label_submit'] ) :
				__('Send email', 'quick-business-website');

	$preform = isset( $qbw_options['qbw_sbfc_preform'] ) ?
				stripslashes( wpautop( qbw_kses( $qbw_options['qbw_sbfc_preform'], true ) ) ) :
				'';

	$appform = isset( $qbw_options['qbw_sbfc_appform'] ) ?
				stripslashes( wpautop( qbw_kses( $qbw_options['qbw_sbfc_appform'], true ) ) ) :
				'';

	if ( $captcha == 'true' ) {
		$captcha_box = '<fieldset class="sbfc-response">
						<label for="sbfc_response"> 1 + 1 = </label>' .
						$qbw_form_strings['response'] .
						'</fieldset>';
	}
	
	if ( 'true' == $include_phone ) {
		$phone_field = '<fieldset class="sbfc-phone">
			<label for="smartestb_sbfc_phone">'. __( 'Phone', 'quick-business-website' ) .'</label>
			'. $qbw_form_strings['phone'] .
			'</fieldset>';
	}

	$sbfc_form = ($preform . $qbw_form_strings['error'] .
	 '<div id="sbfc-contactform-wrap">
			<form action="'. esc_url( get_permalink() ) .'" method="post" id="sbfc-contactform">
				<fieldset class="sbfc-name">
					<label for="smartestb_sbfc_name">'. $label_name .'</label>
					'. $qbw_form_strings['name'] .'
				</fieldset>
				<fieldset class="sbfc-email">
					<label for="smartestb_sbfc_email">'. $label_email .'</label>
					'. $qbw_form_strings['email'] .'
				</fieldset>
					' . $captcha_box . $phone_field . '
				<fieldset class="sbfc-message">
					<label for="sbfc_message">'. $label_message .'</label>
					'. $qbw_form_strings['message'] .'
				</fieldset>
				<div class="sbfc-submit">
					<input type="submit" name="Submit" id="sbfc_contact" value="' . $label_submit . '">
					<input type="hidden" name="sbfc_key" value="process">
				</div>
			</form>
		</div>
		' . $appform);
	return $sbfc_form;
}

/**
 * Add JSON-LD structured data to the contact page.
 */
function qbw_contactpage_structured_data() {
	if ( ! is_page( get_option( 'qbw_contact_page_id' ) ) ) {
		return;
	}

	$metadata = array(
		'@context' => 'http://schema.org',
		'@type' => 'ContactPage',
		'mainEntity' => qbw_business_structured_data()
	);

	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
	<?php
}

add_action( 'wp_head', 'qbw_contactpage_structured_data' );
?>