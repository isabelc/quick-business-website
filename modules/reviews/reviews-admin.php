<?php
/**
 * QBW Reviews Admin Interface
 * 
 * @package		Quick Business Website
 * @subpackage	Reviews Module
 */
class QBW_Reviews_Admin {
	var $parentClass = '';
	function __construct( $parentClass ) {
			define( 'QBW_REVIEWS_ADMIN', 1 );
			
			$this->parentClass = &$parentClass;
			foreach ($this->parentClass as $col => $val) {
				$this->$col = &$this->parentClass->$col;
			}
			
	}
	function real_admin_init() {
			$this->parentClass->init();
			 register_setting( 'smar_options', 'smar_options' );
	}
	function real_admin_save_post($post_id) {
			global $meta_box,$wpdb;

			// check autosave
			if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
				return $post_id;
			}

			// check permissions
		  if ( isset($this->p->post_type) && $this->p->post_type == 'page' ) {
				if (!current_user_can('edit_page', $post_id)) {
					return $post_id;
				}
			} elseif (!current_user_can('edit_post', $post_id)) {
				return $post_id;
			}

			if ( isset($meta_box) && isset($meta_box['fields']) && is_array($meta_box['fields']) )
			{
				foreach ($meta_box['fields'] as $field) {
					
					if ( isset($this->p->post_title) ) {
						$old = get_post_meta($post_id, $field['id'], true);
						
						if (isset($this->p->$field['id'])) {
							$new = $this->p->$field['id'];
							if ($new && $new != $old) {
								update_post_meta($post_id, $field['id'], $new);
							} elseif ($new == '' && $old) {
								delete_post_meta($post_id, $field['id'], $old);
							}
						} else {
							delete_post_meta($post_id, $field['id'], $old);
						}
					}
					
				}
			}
			return $post_id;
	}
	function createUpdateReviewTable() {
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			$sql = "CREATE TABLE $this->dbtable (
					  id int(11) NOT NULL AUTO_INCREMENT,
					  date_time datetime NOT NULL,
					  reviewer_name varchar(150) DEFAULT NULL,
					  reviewer_email varchar(150) DEFAULT NULL,
					  reviewer_ip varchar(15) DEFAULT NULL,
					  review_title varchar(150) DEFAULT NULL,
					  review_text text,
					  review_response text,
					  status tinyint(1) DEFAULT '0',
					  review_rating tinyint(2) DEFAULT '0',
					  reviewer_url varchar(255) NOT NULL,
					  page_id int(11) NOT NULL DEFAULT '0',
					  custom_fields text,
					  PRIMARY KEY  (id),
					  KEY status (status),
					  KEY page_id (page_id)
					  )";
			
			dbDelta($sql);
		}

	function enqueue_admin_stuff() {
		$pluginurl = $this->parentClass->get_reviews_module_url();
		if (isset($this->p->page) && ( $this->p->page == 'qbw_view_reviews' || $this->p->page == 'smar_options' ) ) {
			wp_register_script('qbw-reviews-admin',$pluginurl . 'reviews-admin.js',array('jquery'));
			wp_register_style('qbw-reviews-admin',$pluginurl . 'reviews-admin.css');  
			wp_enqueue_script('qbw-reviews-admin');
			wp_enqueue_style('qbw-reviews-admin');
		}
	}

	function update_options() {
		/* we still process and validate this internally, instead of using the Settings API */
		global $wpdb;
		$msg ='';
		$this->security();
		if (isset($this->p->optin)) {      			
			if ($this->options['activate'] == 0) {
				$this->options['activate'] = 1;
				$this->options['act_email'] = $this->p->email;
				update_option('smar_options', $this->options);
				$msg = 'Thank you. Please configure below.';
			}
		} else {
			check_admin_referer('smar_options-options'); /* nonce check */
			$updated_options = $this->options;
			/* reset these to 0 so we can grab the settings below */
			$updated_options['ask_fields']['fname'] = 0;
			$updated_options['ask_fields']['femail'] = 0;
			$updated_options['ask_fields']['fwebsite'] = 0;
			$updated_options['ask_fields']['ftitle'] = 0;
			$updated_options['require_fields']['fname'] = 0;
			$updated_options['require_fields']['femail'] = 0;
			$updated_options['require_fields']['fwebsite'] = 0;
			$updated_options['require_fields']['ftitle'] = 0;
			$updated_options['show_fields']['fname'] = 0;
			$updated_options['show_fields']['femail'] = 0;
			$updated_options['show_fields']['fwebsite'] = 0;
			$updated_options['show_fields']['ftitle'] = 0;
			$updated_options['ask_custom'] = array();
			$updated_options['field_custom'] = array();
			$updated_options['require_custom'] = array();
			$updated_options['show_custom'] = array();
		
			/* quick update of all options needed */
			foreach ($this->p as $col => $val)
			{
				if (isset($this->options[$col]))
				{
					switch($col)
					{
						case 'field_custom': /* we should always hit field_custom before ask_custom, etc */
							foreach ($val as $i => $name) { $updated_options[$col][$i] = ucwords( strtolower( $name ) ); } /* we are so special */
							break;
						case 'ask_custom':
						case 'require_custom':
						case 'show_custom':
							foreach ($val as $i => $v) { $updated_options[$col][$i] = 1; } /* checkbox array with ints */
							break;
						case 'ask_fields':
						case 'require_fields':
						case 'show_fields':
							foreach ($val as $v) { $updated_options[$col]["$v"] = 1; } /* checkbox array with names */
							break;
						default:
							$updated_options[$col] = $val; /* a non-array normal field */
							break;
					}
				}
			}
			
			/* prevent E_NOTICE warnings */
			if (!isset($this->p->goto_show_button)) { $this->p->goto_show_button = 0; }
			if (!isset($this->p->show_hcard_on)) { $this->p->show_hcard_on = 0; }
			if (!isset($this->p->biz_declare)) { $this->p->biz_declare = 0; }

			/* some int validation */
			$updated_options['form_location'] = intval($this->p->form_location);
			$updated_options['goto_show_button'] = intval($this->p->goto_show_button);
			$updated_options['reviews_per_page'] = intval($this->p->reviews_per_page);
			$updated_options['show_hcard_on'] = intval($this->p->show_hcard_on);
			$updated_options['biz_declare'] = intval($this->p->biz_declare);
			
			if ($updated_options['reviews_per_page'] < 1) { $updated_options['reviews_per_page'] = 10; }
			$msg .= 'Your settings have been saved.';
			update_option('smar_options', $updated_options);
		}

		return $msg;
	}

	function show_options() {
		$su_checked = '';
		if ($this->options['show_hcard_on']) {
			$su_checked = 'checked';
		} 

		$bizdeclare_checked = '';
		if ($this->options['biz_declare']) {
			$bizdeclare_checked = 'checked';
		}
		
		$goto_show_button_checked = '';
		if ($this->options['goto_show_button']) {
			$goto_show_button_checked = 'checked';
		}
		
		$af = array('fname' => '','femail' => '','fwebsite' => '','ftitle' => '');
		if ($this->options['ask_fields']['fname'] == 1) { $af['fname'] = 'checked'; }
		if ($this->options['ask_fields']['femail'] == 1) { $af['femail'] = 'checked'; }
		if ($this->options['ask_fields']['fwebsite'] == 1) { $af['fwebsite'] = 'checked'; }
		if ($this->options['ask_fields']['ftitle'] == 1) { $af['ftitle'] = 'checked'; }

		$rf = array('fname' => '','femail' => '','fwebsite' => '','ftitle' => '');
		if ($this->options['require_fields']['fname'] == 1) { $rf['fname'] = 'checked'; }
		if ($this->options['require_fields']['femail'] == 1) { $rf['femail'] = 'checked'; }
		if ($this->options['require_fields']['fwebsite'] == 1) { $rf['fwebsite'] = 'checked'; }
		if ($this->options['require_fields']['ftitle'] == 1) { $rf['ftitle'] = 'checked'; }
		
		$sf = array('fname' => '','femail' => '','fwebsite' => '','ftitle' => '');
		if ($this->options['show_fields']['fname'] == 1) { $sf['fname'] = 'checked'; }
		if ($this->options['show_fields']['femail'] == 1) { $sf['femail'] = 'checked'; }
		if ($this->options['show_fields']['fwebsite'] == 1) { $sf['fwebsite'] = 'checked'; }
		if ($this->options['show_fields']['ftitle'] == 1) { $sf['ftitle'] = 'checked'; }
		
		echo '
		<div class="postbox" style="width:700px;"><div id="smar_ad">
			   <form method="post" action=""><div style="background:#eaf2fa;padding:6px;border-top:1px solid #ccc;border-bottom:1px solid #ccc;">
						<legend>'. __('General Settings', 'quick-business-website').'</legend>
					</div>                    
<div style="padding:10px;"><input id="show_hcard_on" name="show_hcard_on" type="checkbox" '.$su_checked.' value="1" />&nbsp;
<label for="show_hcard_on">'. __('Enable Aggregate Rating on Home Page.', 'quick-business-website').'</label>
<br /><br /> <small>'. __('This will pull data from your Reviews page, then add `aggregateRating` Schema.org Microdata to your home page.', 'quick-business-website'). '</small><br /><br /><input id="biz_declare" name="biz_declare" type="checkbox" '.$bizdeclare_checked.' value="1" />&nbsp;
<label for="biz_declare">'. __('Declare LocalBusiness Type Microdata on Home page.', 'quick-business-website').'</label>
						<br /><br />
						<small>'. __('Add Schema.org LocalBusiness type declaration on home page. Don\'t check this if you added your own Microdata type and you only want to add on the aggregate rating.', 'quick-business-website').'</small><br />
						<div class="submit" style="padding:10px 0px 0px 0px;"><input type="submit" class="button-primary" value="'. __('Save Changes', 'quick-business-website') .'" name="Submit"></div>
</div>         <div style="background:#eaf2fa;padding:6px;border-top:1px solid #ccc;border-bottom:1px solid #ccc;"><legend>'. __('Review Page Settings', 'quick-business-website'). '</legend></div>
					<div style="padding:10px;padding-bottom:10px;"><label for="reviews_per_page">'. __('Reviews shown per page: ', 'quick-business-website') . '</label><input style="width:40px;" type="text" id="reviews_per_page" name="reviews_per_page" value="'.$this->options['reviews_per_page'].'" />
						<br /><br />
						<label for="form_location">'. __('Location of Review Form: ', 'quick-business-website'). '</label>
						<select id="form_location" name="form_location">
							<option ';if ($this->options['form_location'] == 0) { echo "selected"; } echo ' value="0">'. __('Above Reviews', 'quick-business-website'). '</option>
							<option ';if ($this->options['form_location'] == 1) { echo "selected"; } echo ' value="1">'. __('Below Reviews', 'quick-business-website'). '</option>                     </select>
						<br /><br />
						<label>'. __('Fields to ask for on review form: ', 'quick-business-website'). '</label>
						<input data-what="fname" id="ask_fname" name="ask_fields[]" type="checkbox" '.$af['fname'].' value="fname" />&nbsp;<label for="ask_fname"><small>'. __('Name', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input data-what="femail" id="ask_femail" name="ask_fields[]" type="checkbox" '.$af['femail'].' value="femail" />&nbsp;<label for="ask_femail"><small>'. __('Email', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input data-what="fwebsite" id="ask_fwebsite" name="ask_fields[]" type="checkbox" '.$af['fwebsite'].' value="fwebsite" />&nbsp;<label for="ask_fwebsite"><small>'. __('Website', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input data-what="ftitle" id="ask_ftitle" name="ask_fields[]" type="checkbox" '.$af['ftitle'].' value="ftitle" />&nbsp;<label for="ask_ftitle"><small>'. __('Review Title', 'quick-business-website'). '</small></label>
						<br /><br />
						<label>'. __('Fields to require on review form: ', 'quick-business-website'). '</label>
						<input id="require_fname" name="require_fields[]" type="checkbox" '.$rf['fname'].' value="fname" />&nbsp;<label for="require_fname"><small>'. __('Name', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="require_femail" name="require_fields[]" type="checkbox" '.$rf['femail'].' value="femail" />&nbsp;<label for="require_femail"><small>'. __('Email', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="require_fwebsite" name="require_fields[]" type="checkbox" '.$rf['fwebsite'].' value="fwebsite" />&nbsp;<label for="require_fwebsite"><small>'. __('Website', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="require_ftitle" name="require_fields[]" type="checkbox" '.$rf['ftitle'].' value="ftitle" />&nbsp;<label for="require_ftitle"><small>'. __('Review Title', 'quick-business-website'). '</small></label>
						<br /><br />
						<label>'. __('Fields to show on each approved review: ', 'quick-business-website'). '</label>
						<input id="show_fname" name="show_fields[]" type="checkbox" '.$sf['fname'].' value="fname" />&nbsp;<label for="show_fname"><small>'. __('Name', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="show_femail" name="show_fields[]" type="checkbox" '.$sf['femail'].' value="femail" />&nbsp;<label for="show_femail"><small>'. __('Email', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="show_fwebsite" name="show_fields[]" type="checkbox" '.$sf['fwebsite'].' value="fwebsite" />&nbsp;<label for="show_fwebsite"><small>'. __('Website', 'quick-business-website'). '</small></label>&nbsp;&nbsp;&nbsp;
						<input id="show_ftitle" name="show_fields[]" type="checkbox" '.$sf['ftitle'].' value="ftitle" />&nbsp;<label for="show_ftitle"><small>'. __('Review Title', 'quick-business-website'). '</small></label>
						<br />
						<small>'. __('It is usually NOT a good idea to show email addresses publicly.', 'quick-business-website'). '</small>
						<br /><br />
						<label>'. __('Custom fields on review form: ', 'quick-business-website'). '</label>(<small>'. __('You can type in the names of any additional fields you would like here.', 'quick-business-website'). '</small>)
						<div style="font-size:10px;padding-top:6px;">
						';
						for ($i = 0; $i < 6; $i++) /* 6 custom fields */
						{
							if ( !isset($this->options['ask_custom'][$i]) ) { $this->options['ask_custom'][$i] = 0; }
							if ( !isset($this->options['require_custom'][$i]) ) { $this->options['require_custom'][$i] = 0; }
							if ( !isset($this->options['show_custom'][$i]) ) { $this->options['show_custom'][$i] = 0; }
							
							if ($this->options['ask_custom'][$i] == 1) { $caf = 'checked'; } else { $caf = ''; }
							if ($this->options['require_custom'][$i] == 1) { $crf = 'checked'; } else { $crf = ''; }
							if ($this->options['show_custom'][$i] == 1) { $csf = 'checked'; } else { $csf = ''; }
							$name_value = isset( $this->options['field_custom'][$i] ) ?
							$this->options['field_custom'][$i] :
							'';

							echo '
							<label for="field_custom'.$i.'">'. __('Field Name: ', 'quick-business-website'). '</label><input id="field_custom'.$i.'" name="field_custom['.$i.']" type="text" value="' . esc_html( $name_value ) . '" />&nbsp;&nbsp;&nbsp;
							<input '.$caf.' class="custom_ask" data-id="'.$i.'" id="ask_custom'.$i.'" name="ask_custom['.$i.']" type="checkbox" value="1" />&nbsp;<label for="ask_custom'.$i.'">'. __('Ask', 'quick-business-website'). '</label>&nbsp;&nbsp;&nbsp;
							<input '.$crf.' class="custom_req" data-id="'.$i.'" id="require_custom'.$i.'" name="require_custom['.$i.']" type="checkbox" value="1" />&nbsp;<label for="require_custom'.$i.'">'. __('Require', 'quick-business-website'). '</label>&nbsp;&nbsp;&nbsp;
							<input '.$csf.' class="custom_show" data-id="'.$i.'" id="show_custom'.$i.'" name="show_custom['.$i.']" type="checkbox" value="1" />&nbsp;<label for="show_custom'.$i.'">'. __('Show', 'quick-business-website'). '</label><br />
							';
						}
						echo '
						</div>
						<br /><br />
						<label for="title_tag">'. __('Heading to use for Review Titles: ', 'quick-business-website'). '</label>
						<select id="title_tag" name="title_tag">
							<option ';if ($this->options['title_tag'] == 'h2') { echo "selected"; } echo ' value="h2">H2</option>
							<option ';if ($this->options['title_tag'] == 'h3') { echo "selected"; } echo ' value="h3">H3</option>
							<option ';if ($this->options['title_tag'] == 'h4') { echo "selected"; } echo ' value="h4">H4</option>
							<option ';if ($this->options['title_tag'] == 'h5') { echo "selected"; } echo ' value="h5">H6</option>
							<option ';if ($this->options['title_tag'] == 'h6') { echo "selected"; } echo ' value="h6">H7</option>
						</select>
						<br /><br />
						<label for="goto_show_button">'. __('Show review form: ', 'quick-business-website'). '</label><input type="checkbox" id="goto_show_button" name="goto_show_button" value="1" '.$goto_show_button_checked.' />
						<br />
						<small>'. __('If this option is unchecked, there will be no visible way for visitors to submit reviews.', 'quick-business-website'). '</small>
						<br /><br />
						<label for="goto_leave_text">'. __('Button text used to show review form: ', 'quick-business-website'). '</label><input style="width:250px;" type="text" id="goto_leave_text" name="goto_leave_text" value="'.$this->options['goto_leave_text'].'" />
						<br /><br />
						<label for="submit_button_text">'. __('Text to use for review form submit button: ', 'quick-business-website'). '</label><input style="width:200px;" type="text" id="submit_button_text" name="submit_button_text" value="'.$this->options['submit_button_text'].'" />
						<br />
						<div class="submit" style="padding:10px 0px 0px 0px;"><input type="submit" class="button-primary" value="'. __('Save Changes', 'quick-business-website'). '" name="Submit"></div>
					</div>';
					settings_fields("smar_options");
					echo '
				</form>
				<br />
			</div>
		</div>';

	}
	
	function security() {
		if (!current_user_can('manage_options'))
		{
			wp_die( __('You do not have sufficient permissions to access this page.','quick-business-website') );
		}
	}
	
	function real_admin_options() {
		$this->security();

		$msg = '';
		
		// make sure the db is created
		global $wpdb;
		$exists = $wpdb->get_var("SHOW TABLES LIKE '$this->dbtable'");
		if ($exists != $this->dbtable) {
			$exists = $wpdb->get_var("SHOW TABLES LIKE '$this->dbtable'");
			if ($exists != $this->dbtable) {
				print "<br /><br /><br /><p class='warning'>". __('COULD NOT CREATE DATABASE TABLE, PLEASE REPORT THIS ERROR', 'quick-business-website'). "</p>";
			}
		}
		
		if (!isset($this->p->Submit)) { $this->p->Submit = ''; }
		
		if ($this->p->Submit == __('Save Changes', 'quick-business-website')) {
			$msg = $this->update_options();
			$this->parentClass->get_options();
		}
		
		if (isset($this->p->email)) {
			$msg = $this->update_options();
			$this->parentClass->get_options();
		}
		
		echo '
		<div id="smar_respond_1" class="wrap">
			<h2>' . __( 'Reviews - Options', 'quick-business-website' ). '</h2>';
			if ($msg) { echo '<h3 style="color:#a00;">'.$msg.'</h3>'; }
			echo '<div class="metabox-holder">';

		$this->show_options();
		echo '<br /></div>';
	}
	
	function real_admin_view_reviews() {      
		global $wpdb;
		
		if (!isset($this->p->s)) { $this->p->s = ''; }
		$this->p->s_orig = $this->p->s;
		
		if (!isset($this->p->review_status)) { $this->p->review_status = 0; }
		$this->p->review_status = intval($this->p->review_status);
		
		/* begin - actions */
		if (isset($this->p->action)) {
		
			if (isset($this->p->r)) {
				$this->p->r = intval($this->p->r);

				switch ($this->p->action) {
					case 'deletereview':
						$wpdb->query("DELETE FROM `$this->dbtable` WHERE `id`={$this->p->r} LIMIT 1");
						break;
					case 'trashreview':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=2 WHERE `id`={$this->p->r} LIMIT 1");
						break;
					case 'approvereview':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=1 WHERE `id`={$this->p->r} LIMIT 1");
						break;
					case 'unapprovereview':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=0 WHERE `id`={$this->p->r} LIMIT 1");
						break;
					case 'update_field':
						
						ob_end_clean();
						
						if (!is_array($this->p->json)) {
							header('HTTP/1.1 403 Forbidden');
							echo json_encode(array("errors" => __('Bad Request', 'quick-business-website')));
							exit(); 
						}
						
						$show_val = '';
						$update_col = false;
						$update_val = false;
						foreach ($this->p->json as $col => $val) {
							switch ($col) {
								case 'date_time':
									$d = date("m/d/Y g:i a",strtotime($val));
									if (!$d || $d == '01/01/1970 12:00 am') {
										header('HTTP/1.1 403 Forbidden');
										echo json_encode(array("errors" => __('Bad Date Format', 'quick-business-website')));
										exit(); 
									}
									$show_val = $d;
									$d2 = date("Y-m-d H:i:s",strtotime($val));
									$update_col = mysql_real_escape_string($col);
									$update_val = mysql_real_escape_string($d2);
									break;
									
								default:
									if ($val == '') {
										header('HTTP/1.1 403 Forbidden');
										echo json_encode(array("errors" => __('Bad Value', 'quick-business-website')));
										exit(); 
									}
									/* for storing in DB - fix with IE 8 workaround */
									$val = str_replace( array("<br />","<br/>","<br>") , "\n" , $val );	
									if (substr($col,0,7) == 'custom_') /* updating custom fields */
									{
										$custom_fields = array(); /* used for insert as well */
										$custom_count = count($this->options['field_custom']); /* used for insert as well */
										for ($i = 0; $i < $custom_count; $i++)
										{
											$custom_fields[$i] = $this->options['field_custom'][$i];
										}

										$custom_num = substr($col,7); /* gets the number after the _ */
										/* get the old custom value */
										$old_value = $wpdb->get_results("SELECT `custom_fields` FROM `$this->dbtable` WHERE `id`={$this->p->r} LIMIT 1");										
										if ($old_value && $wpdb->num_rows)
										{
											$old_value = @unserialize($old_value[0]->custom_fields);
											if (!is_array($old_value)) { $old_value = array(); }
											$custom_name = $custom_fields[$custom_num];
											$old_value[$custom_name] = $val;
											$new_value = serialize($old_value);											
											$update_col = mysql_real_escape_string('custom_fields');
											$update_val = mysql_real_escape_string($new_value);
										}
									}
									else /* updating regular fields */
									{									
										$update_col = mysql_real_escape_string($col);
										$update_val = mysql_real_escape_string($val);
									}

									$show_val = $val;
									
									break;
							}
							
						}
						
						if ($update_col !== false && $update_val !== false) {
							$query = "UPDATE `$this->dbtable` SET `$update_col`='$update_val' WHERE `id`={$this->p->r} LIMIT 1";
							$wpdb->query($query);
							echo $show_val;
						}
						
						exit();
						break;
				}
			}
			
			if ( isset($this->p->delete_reviews) && is_array($this->p->delete_reviews) && count($this->p->delete_reviews) ) {
				
				foreach ($this->p->delete_reviews as $i => $rid) {
					$this->p->delete_reviews[$i] = intval($rid);
				}
				
				if (isset($this->p->act2)) { $this->p->action = $this->p->action2; }
				
				switch ($this->p->action) {
					case 'bapprove':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=1 WHERE `id` IN(".implode(',',$this->p->delete_reviews).")");
						break;
					case 'bunapprove':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=0 WHERE `id` IN(".implode(',',$this->p->delete_reviews).")");
						break;
					case 'btrash':
						$wpdb->query("UPDATE `$this->dbtable` SET `status`=2 WHERE `id` IN(".implode(',',$this->p->delete_reviews).")");
						break;
					case 'bdelete':
						$wpdb->query("DELETE FROM `$this->dbtable` WHERE `id` IN(".implode(',',$this->p->delete_reviews).")");
						break;
				}
			}
			
			$this->parentClass->smar_redirect("?page=qbw_view_reviews&review_status={$this->p->review_status}");
		}
		/* end - actions */
		
		/* begin - searching */
		if ($this->p->review_status == -1) {
			$sql_where = '-1=-1';
		} else {
			$sql_where = 'status='.$this->p->review_status;
		}
		
		$and_clause = '';
		if ($this->p->s != '') { /* searching */
			$this->p->s = '%'.$this->p->s.'%';
			$sql_where = '-1=-1';
			$this->p->review_status = -1;
			$and_clause = "AND (`reviewer_name` LIKE %s OR `reviewer_email` LIKE %s OR `reviewer_ip` LIKE %s OR `review_text` LIKE %s OR `review_response` LIKE %s OR `reviewer_url` LIKE %s)";
			$and_clause = $wpdb->prepare($and_clause,$this->p->s,$this->p->s,$this->p->s,$this->p->s,$this->p->s,$this->p->s);
			$query = "SELECT 
				`id`,
				`date_time`,
				`reviewer_name`,
				`reviewer_email`,
				`reviewer_ip`,
				`review_title`,
				`review_text`,
				`review_response`,
				`review_rating`,
				`reviewer_url`,
				`status`,
				`page_id`,
				`custom_fields`
				FROM `$this->dbtable` WHERE $sql_where $and_clause ORDER BY `id` DESC"; 
			
			$reviews = $wpdb->get_results($query);
			$total_reviews = 0; /* no pagination for searches */
		}
		/* end - searching */
		else
		{
			$arr_Reviews = $this->parentClass->get_reviews($this->page,$this->options['reviews_per_page'],$this->p->review_status);
			$reviews = $arr_Reviews[0];
			$total_reviews = $arr_Reviews[1];
		}
		
		$status_text = "";
		switch ($this->p->review_status)
		{
			case -1:
				$status_text = __('Submitted', 'quick-business-website');
				break;
			case 0:
				$status_text = __('Pending', 'quick-business-website');
				break;
			case 1:
				$status_text = __('Approved', 'quick-business-website');
				break;
			case 2:
				$status_text = __('Trashed', 'quick-business-website');
				break;
		}
		
		$pending_count = $wpdb->get_results("SELECT COUNT(*) AS `count_pending` FROM `$this->dbtable` WHERE `status`=0");
		$pending_count = $pending_count[0]->count_pending;
		
		$approved_count = $wpdb->get_results("SELECT COUNT(*) AS `count_approved` FROM `$this->dbtable` WHERE `status`=1");
		$approved_count = $approved_count[0]->count_approved;

		$trash_count = $wpdb->get_results("SELECT COUNT(*) AS `count_trash` FROM `$this->dbtable` WHERE `status`=2");
		$trash_count = $trash_count[0]->count_trash;
		?>
		<div id="smar_respond_1" class="wrap">
			<div class="icon32" id="icon-edit-comments"><br /></div>
			<h2><?php _e('Reviews', 'quick-business-website'); ?> - <?php echo sprintf(__('%s Reviews', 'quick-business-website'), $status_text); ?></h2>
			  <ul class="subsubsub">
				<li class="all"><a <?php if ($this->p->review_status == -1) { echo 'class="current"'; } ?> href="?page=qbw_view_reviews&amp;review_status=-1"><?php _e('All', 'quick-business-website'); ?></a> |</li>
				<li class="moderated"><a <?php if ($this->p->review_status == 0) { echo 'class="current"'; } ?> href="?page=qbw_view_reviews&amp;review_status=0"><?php _e('Pending ', 'quick-business-website'); ?>
					<span class="count">(<span class="pending-count"><?php echo $pending_count;?></span>)</span></a> |
				</li>
				<li class="approved"><a <?php if ($this->p->review_status == 1) { echo 'class="current"'; } ?> href="?page=qbw_view_reviews&amp;review_status=1"><?php _e('Approved', 'quick-business-website'); ?>
					<span class="count">(<span class="pending-count"><?php echo $approved_count;?></span>)</span></a> |
				</li>
				<li class="trash"><a <?php if ($this->p->review_status == 2) { echo 'class="current"'; } ?> href="?page=qbw_view_reviews&amp;review_status=2"><?php _e('Trash', 'quick-business-website'); ?>
					<span class="count">(<span class="pending-count"><?php echo $trash_count;?></span>)</span></a>
				</li>
			  </ul>

			  <form method="GET" action="" id="search-form" name="search-form">
				  <p class="search-box">
					  <?php if ($this->p->s_orig): ?><span style='color:#c00;font-weight:bold;'><?php _e('RESULTS FOR: ', 'quick-business-website'); ?></span><?php endif; ?>
					  <label for="comment-search-input" class="screen-reader-text"><?php _e('Search Reviews:', 'quick-business-website'); ?></label> 
					  <input type="text" value="<?php echo $this->p->s_orig; ?>" name="s" id="comment-search-input" />
					  <input type="hidden" name="page" value="qbw_view_reviews" />
					  <input type="submit" class="button" value="<?php _e('Search Reviews', 'quick-business-website'); ?>" />
				  </p>
			  </form>

			  <form method="POST" action="?page=qbw_view_reviews" id="comments-form" name="comments-form">
			  <input type="hidden" name="review_status" value="<?php echo $this->p->review_status; ?>" />
			  <div class="tablenav">
				<div class="alignleft actions">
					  <select name="action">
							<option selected="selected" value="-1"><?php _e('Bulk Actions', 'quick-business-website'); ?></option>
							<option value="bunapprove"><?php _e('Unapprove', 'quick-business-website'); ?></option>
							<option value="bapprove"><?php _e('Approve', 'quick-business-website'); ?></option>
							<option value="btrash"><?php _e('Move to Trash', 'quick-business-website'); ?></option>
							<option value="bdelete"><?php _e('Delete Forever', 'quick-business-website'); ?></option>
					  </select>&nbsp;
					  <input type="submit" class="button-secondary apply" name="act" value="<?php _e('Apply', 'quick-business-website'); ?>" id="doaction" /></div><br class="clear" /></div> <div class="clear"></div><table cellspacing="0" class="widefat comments fixed"><thead><tr><th style="" class="manage-column column-cb check-column" id="cb" scope="col"><input type="checkbox" /></th><th style="" class="manage-column column-author" id="author" scope="col"><?php _e('Author', 'quick-business-website'); ?></th><th style="" class="manage-column column-comment" id="comment" scope="col"><?php _e('Review', 'quick-business-website'); ?></th></tr>
				</thead>
				<tfoot>
				  <tr>
					<th style="" class="manage-column column-cb check-column" scope="col"><input type="checkbox" /></th>
					<th style="" class="manage-column column-author" scope="col"><?php _e('Author', 'quick-business-website'); ?></th>
					<th style="" class="manage-column column-comment" scope="col"><?php _e('Review', 'quick-business-website'); ?></th>
				  </tr>
				</tfoot>
				<tbody class="list:comment" id="the-comment-list">
				  <?php
				  if (count($reviews) == 0) {
					  ?>
						<tr><td colspan="3" align="center"><br />
<?php echo sprintf(__('There are no %s reviews yet.', 'quick-business-website'), $status_text); ?> <br /><br /></td></tr>
<?php		}
				  foreach ($reviews as $review) {                    
					  $rid = $review->id;
					  $update_path = get_admin_url()."admin-ajax.php?page=qbw_view_reviews&r=$rid&action=update_field";
					  $review->review_title = stripslashes($review->review_title);
					  $review->review_text = stripslashes($review->review_text);
					  $review->review_response = stripslashes($review->review_response);
					  $review->reviewer_name = stripslashes($review->reviewer_name);
					  if ($review->reviewer_name == '') { $review->reviewer_name = __('Anonymous', 'quick-business-website'); }
					  $review_text = nl2br($review->review_text);
					  $review_text = str_replace( array("\r\n","\r","\n") , "" , $review_text );
					  $review_response = nl2br($review->review_response);
					  $review_response = str_replace( array("\r\n","\r","\n") , "" , $review_response );
					  $page = get_post($review->page_id); ?>
					  <tr class="approved" id="review-<?php echo $rid;?>">
						<th class="check-column" scope="row"><input type="checkbox" value="<?php echo $rid;?>" name="delete_reviews[]" /></th>
						<td class="author column-author">
							<?php echo get_avatar( sanitize_email( $review->reviewer_email ), 32 ); ?> &nbsp;<span style="font-weight:bold;" class="best_in_place" data-url='<?php echo $update_path; ?>' data-object='json' data-attribute='reviewer_name'><?php echo $review->reviewer_name; ?></span>
							<br />
							<?php if ( ! empty( $review->reviewer_url ) ) { ?>
								<a href="<?php echo $review->reviewer_url; ?>"><?php echo $review->reviewer_url; ?></a><br />
							<?php } ?>
							<a href="mailto:<?php echo $review->reviewer_email; ?>"><?php echo $review->reviewer_email; ?></a><br />
							<a href="?page=qbw_view_reviews&amp;s=<?php echo $review->reviewer_ip; ?>"><?php echo $review->reviewer_ip; ?></a><br />
							<?php
							$custom_count = count($this->options['field_custom']); /* used for insert as well */
							$custom_unserialized = @unserialize($review->custom_fields);
							if ($custom_unserialized !== false)
							{							
								for ($i = 0; $i < $custom_count; $i++)
								{
									$custom_field_name = $this->options['field_custom'][$i];
									if ( isset($custom_unserialized[$custom_field_name]) ) {
										$custom_value = $custom_unserialized[$custom_field_name];
										if ($custom_value != '')
										{
											echo "$custom_field_name: <span class='best_in_place' data-url='$update_path' data-object='json' data-attribute='custom_$i'>$custom_value</span><br />";
										}
									}
								}
							}
							?>
							<div style="margin-left:-4px;">
								<div style="height:22px;" class="best_in_place" 
									 data-collection='[[1,"Rated 1 Star"],[2,"Rated 2 Stars"],[3,"Rated 3 Stars"],[4,"Rated 4 Stars"],[5,"Rated 5 Stars"]]' 
									 data-url='<?php echo $update_path; ?>' 
									 data-object='json'
									 data-attribute='review_rating' 
									 data-callback='make_stars_from_rating'
									 data-type='select'><?php echo $this->parentClass->output_rating($review->review_rating,false); ?></div>
							</div>
						</td>
						<td class="comment column-comment">
						  <div class="smar-submitted-on">
							<span class="best_in_place" data-url='<?php echo $update_path; ?>' data-object='json' data-attribute='date_time'><?php 
							echo date_i18n( get_option( 'date_format' ), strtotime( $review->date_time ) ) . ' at ' . date_i18n( get_option( 'time_format' ), strtotime( $review->date_time ) );// @test locale ?>
							</span>
							<?php if ($review->status == 1) : ?>[<a target="_blank" href="<?php echo $this->parentClass->get_jumplink_for_review($review,$this->page); ?>"><?php _e('View Live Review', 'quick-business-website'); ?></a>]<?php endif; ?>
						  </div>
						  <p>
							  <span style="font-size:13px;font-weight:bold;"><?php _e('Title:', 'quick-business-website'); ?>&nbsp;</span>
							  <span style="font-size:14px; font-weight:bold;" 
									class="best_in_place" 
									data-url='<?php echo $update_path; ?>' 
									data-object='json'
									data-attribute='review_title'><?php echo $review->review_title; ?></span>
							  <br /><br />
							  <div class="best_in_place" 
									data-url='<?php echo $update_path; ?>' 
									data-object='json'
									data-attribute='review_text' 
									data-callback='callback_review_text'
									data-type='textarea'><?php echo $review_text; ?></div>
							 <div style="font-size:13px;font-weight:bold;">
								 <br />
								 <?php _e('Official Response:', 'quick-business-website'); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
								 <span style="font-size:11px;font-style:italic;"><?php _e('Leave this blank if you do not want it to be public', 'quick-business-website'); ?></span>
							 </div>
							 <div class="best_in_place" 
									data-url='<?php echo $update_path; ?>'
									data-object='json'
									data-attribute='review_response' 
									data-callback='callback_review_text'
									data-type='textarea'><?php echo $review_response; ?></div>
						  </p>
						  <div class="row-actions">
							<span class="approve <?php if ($review->status == 0 || $review->status == 2) { echo 'smar_show'; } else { echo 'smar_hide'; }?>"><a title="Mark as Approved"
							href="?page=qbw_view_reviews&amp;action=approvereview&amp;r=<?php echo $rid;?>&amp;review_status=<?php echo $this->p->review_status;?>">
							<?php _e('Mark as Approved', 'quick-business-website'); ?></a>&nbsp;|&nbsp;</span>
							<span class="unapprove <?php if ($review->status == 1 || $review->status == 2) { echo 'smar_show'; } else { echo 'smar_hide'; }?>"><a title="Mark as Unapproved"
							href="?page=qbw_view_reviews&amp;action=unapprovereview&amp;r=<?php echo $rid;?>&amp;review_status=<?php echo $this->p->review_status;?>">
							<?php _e('Mark as Unapproved', 'quick-business-website'); ?></a><?php if ($review->status != 2): ?>&nbsp;|&nbsp;<?php endif; ?></span>
							<span class="trash <?php if ($review->status == 2) { echo 'smar_hide'; } else { echo 'smar_show'; }?>"><a title="Move to Trash" 
							href= "?page=qbw_view_reviews&amp;action=trashreview&amp;r=<?php echo $rid;?>&amp;review_status=<?php echo $this->p->review_status;?>">
							<?php _e('Move to Trash', 'quick-business-website'); ?></a><?php if ($review->status != 2): ?>&nbsp;|&nbsp;<?php endif; ?></span>
							<span class="trash <?php if ($review->status == 2) { echo 'smar_hide'; } else { echo 'smar_show'; }?>"><a title="Delete Forever" 
							href= "?page=qbw_view_reviews&amp;action=deletereview&amp;r=<?php echo $rid;?>&amp;review_status=<?php echo $this->p->review_status;?>">
							<?php _e('Delete Forever', 'quick-business-website'); ?></a></span>
						  </div>
						</td>
					  </tr>
				  <?php
				  }
				  ?>
				</tbody>
			  </table>

			  <div class="tablenav">
				<div class="alignleft actions" style="float:left;">
					  <select name="action2">
							<option selected="selected" value="-1"><?php _e('Bulk Actions', 'quick-business-website'); ?></option>
							<option value="bunapprove"><?php _e('Unapprove', 'quick-business-website'); ?></option>
							<option value="bapprove"><?php _e('Approve', 'quick-business-website'); ?></option>
							<option value="btrash"><?php _e('Move to Trash', 'quick-business-website'); ?></option>
							<option value="bdelete"><?php _e('Delete Forever', 'quick-business-website'); ?></option>
					  </select>&nbsp;
					  <input type="submit" class="button-secondary apply" name="act2" value="<?php _e('Apply', 'quick-business-website'); ?>" id="doaction2" />
				</div>
				<div class="alignleft actions" style="float:left;padding-left:20px;"><?php echo $this->parentClass->pagination($total_reviews, $this->options['reviews_per_page']); ?></div>  
				<br class="clear" />
			  </div>
			</form>

			<div id="ajax-response"></div>
		  </div>
		<?php
	}
}
if ( ! defined( 'QBW_REVIEWS_ADMIN' ) ) {
	global $QBW_Reviews, $QBW_Reviews_Admin;
	$QBW_Reviews_Admin = new QBW_Reviews_Admin( $QBW_Reviews );
}
?>