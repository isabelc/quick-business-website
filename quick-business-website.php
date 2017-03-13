<?php
/*
Plugin Name: Quick Business Website
Plugin URI: http://smartestthemes.com/docs/category/quick-business-website-wordpress-plugin/
Description: Business website to showcase your services, staff, announcements, a working contact form, and reviews.
Version: 2.0.alpha.3
Author: Isabel Castillo
Author URI: https://isabelcastillo.com
License: GPL2
Text Domain: quick-business-website
Domain Path: lang
Copyright 2013 - 2017 Isabel Castillo

This file is part of Quick Business Website.

Quick Business Website is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

Quick Business Website is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Quick Business Website; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
if ( ! class_exists( 'Quick_Business_Website' ) ) {
class Quick_Business_Website {
	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
			if ( ! defined( 'QUICKBUSINESSWEBSITE_PATH' ) ) {
				define( 'QUICKBUSINESSWEBSITE_PATH', plugin_dir_path( __FILE__) );
			}
			if ( ! defined( 'QUICKBUSINESSWEBSITE_URL' ) ) {
				define( 'QUICKBUSINESSWEBSITE_URL', plugin_dir_url( __FILE__) );
			}
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'plugins_loaded', array( $this, 'load' ) );
			add_action( 'wp_ajax_smartestb_ajax_post_action', array( $this, 'ajax_callback' ) );
			add_action( 'admin_menu', array( $this, 'add_admin' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
			add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 2, 2 );
			add_action( 'login_head', array( $this, 'login_logo' ) );
			add_filter( 'login_headerurl',
			    create_function(false,"return get_home_url();"));
			add_filter( 'login_headertitle', array( $this, 'wp_login_title' ) );
			add_action( 'after_setup_theme', array( $this, 'after_setup' ) );
			add_action( 'init', array( $this, 'create_business_cpts') );
			add_action( 'init', array( $this, 'set_taxonomies' ), 0 );
			add_action( 'init', array( $this, 'textdomain' ) );
			add_action( 'init', array( $this, 'metaboxes') );
			add_filter( 'enter_title_here', array( $this, 'change_enter_title') );
			add_action( 'widgets_init', array( $this, 'register_widgets') );
			add_action( 'wp_head', array( $this, 'add_customscripts' ), 12 );
			add_filter( 'manage_edit-smartest_staff_columns', array( $this, 'smar_manage_edit_staff_columns') );
			add_action( 'manage_smartest_staff_posts_custom_column', array( $this, 'smar_manage_staff_columns' ), 10, 2 );
			add_filter( 'manage_edit-smartest_services_columns', array( $this, 'smar_manage_edit_services_columns') );
			add_action( 'manage_smartest_services_posts_custom_column', array( $this, 'smar_manage_services_columns' ), 10, 2 );
			add_filter( 'manage_edit-smartest_news_columns', array( $this, 'smar_manage_edit_news_columns') );
			add_action( 'manage_smartest_news_posts_custom_column', array( $this, 'smar_manage_news_columns' ), 10, 2 );
			add_filter( 'admin_footer_text', array( $this, 'remove_footer_admin') ); 
			add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar') ); 
			add_action( 'wp_enqueue_scripts', array( $this, 'framework_enq') ); 
			add_filter ( 'the_content',  array( $this, 'staff_meta_content_filter' ) );
			add_filter ( 'the_content',  array( $this, 'contact_content_filter' ), 50 );
			add_filter( 'parse_query', array( $this, 'sort_staff' ) );
			if ( get_option( 'qbw_enable_service_sort') == 'true'  ) { 
				add_filter( 'parse_query', array( $this, 'sort_services' ) );
			}
			add_action( 'admin_init', array( $this, 'upgrade_options' ) );

    }

	/** 
	* Only upon plugin activation, setup options and flush rewrite rules for custom post types.
	*
	* @since 1.0
	*/
	public static function activate() {
		add_action( 'admin_head', array( __CLASS__, 'option_setup' ) );
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	/** 
	* display settings link on plugin page
	*
	* @since 1.0
	* @return void
	*/
	function settings_link($actions, $file) {
	$qbw_path    = plugin_basename(__FILE__);
	if(false !== strpos($file, $qbw_path))
	 $actions['settings'] = '<a href="admin.php?page=quickbusinesswebsite">'. __('Settings', 'quick-business-website'). '</a>';
	return $actions; 
	}
	/**
	* Include plugin options
	*
	* @since 1.0
	* @return void
	*/
	public function load() {
		include QUICKBUSINESSWEBSITE_PATH . 'inc/options.php';
		add_action( 'init', 'qbw_options' );
	}
	/**
	* Load textdomain
	*
	* @since 2.0
	*/
	public function textdomain() {
		load_plugin_textdomain( 'quick-business-website', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}	
	/** 
	* Store plugin name as options
	*
	* @since 1.0
	* @return void
	*/
	public function admin_init(){
		$plugin_data = get_plugin_data( __FILE__, false );
		update_option( 'qbw_smartestb_plugin_version', $plugin_data['Version'] );
	}
	/**  
	* Setup options panel
	*
	* @since 1.0
	*/
	public function option_setup(){
		//Update EMPTY options
		$qbw_array = array();
		add_option( 'qbw_options',$qbw_array );
		$template = get_option( 'qbw_template' );
		$saved_options = get_option( 'qbw_options');
		foreach($template as $option) {
			if($option['type'] != 'heading'){
				$id = isset($option['id']) ? $option['id'] : '';
				$std = isset($option['std']) ? $option['std'] : '';
				$db_option = get_option($id);
				if(empty($db_option)){
					if(is_array($option['type'])) {
						foreach($option['type'] as $child){
							$c_id = $child['id'];
							$c_std = $child['std'];
							update_option($c_id,$c_std);
							$qbw_array[$c_id] = $c_std; 
						}
					} else {
						update_option($id,$std);
						$qbw_array[$id] = $std;
					}
				}
				else { //So just store the old values over again.
					$qbw_array[$id] = $db_option;
				}
			}
		}
		update_option( 'qbw_options',$qbw_array );
	}// end option_setup

	/** 
	*  add admin options page
	*
	* @since 1.0
	*/
	public function add_admin() {
	    global $query_string;
	    $title = __( 'Quick Business Website', 'quick-business-website' );
	    if ( isset( $_REQUEST['page'] ) && 'quickbusinesswebsite' == $_REQUEST['page'] ) {
			if ( isset( $_REQUEST['smartestb_save'] ) && 'reset' == $_REQUEST['smartestb_save'] ) {
	
				$options = get_option( 'qbw_template'); 
				$this->reset_options( $options, 'quickbusinesswebsite' );
				header( "Location: admin.php?page=quickbusinesswebsite&reset=true" );
				die;
			}
	    }
		$sto = add_menu_page( sprintf( __( '%s Options', 'quick-business-website' ), $title ), $title, 'activate_plugins', 'quickbusinesswebsite', array( $this, 'options_page' ), 'dashicons-welcome-widgets-menus', 45);
		add_action( 'admin_head-'. $sto, array( $this, 'frame_load' ) );
		$this->add_admin_menu_separator(44);
	
	}
	/**
	 * Load admin CSS
	 */
	public function load_admin_scripts() {
		global $pagenow;
		$page = isset( $_GET['page'] ) ? strtolower( sanitize_text_field( $_GET['page'] ) ) : false;
		if ( 'admin.php' == $pagenow && 'quickbusinesswebsite' == $page ) {
			wp_register_style( 'qbw-admin', QUICKBUSINESSWEBSITE_URL . 'css/qbw-admin.css' );
			wp_enqueue_style( 'qbw-admin' );
		}
	
		// metabox style
		wp_register_style( 'qbw-metabox', QUICKBUSINESSWEBSITE_URL . 'css/qbw-metabox.css' );
		
	}
	/**
	 * Add link to plugin options to admin tool bar. Also, remove WordPress links from admin/tool bar, if enabled for branding.
	 * @since 1.0
	 */
	public function admin_bar() {
	    global $wp_admin_bar;
	    $wp_admin_bar->add_menu( array(
	        'parent' => 'appearance',
	        'id' => 'qbw-options',
	        'title' => __( 'Quick Business Website Options', 'quick-business-website' ),
	        'href' => admin_url( 'admin.php?page=quickbusinesswebsite' )
	    ) );
		if ( get_option( 'qbw_remove_wplinks' ) == 'true' ) {
			$wp_admin_bar->remove_menu( 'wp-logo' );
		}

	}

	/**
	 * Reset options page
	 * @since 1.0
	 */
	public function reset_options( $options, $page = '' ){
		global $wpdb;
		$query_inner = '';
		$count = 0;
		
		$excludes = array( 'blogname' , 'blogdescription' );
		
		foreach( $options as $option ) {

			if(isset($option['id'])){ 
				$count++;
				$option_id = $option['id'];
				$option_type = $option['type'];
				
				//Skip assigned id's
				if(in_array($option_id,$excludes)) { continue; }
				
				if($count > 1){ $query_inner .= ' OR '; }
				if($option_type == 'multicheck'){
					$multicount = 0;
					foreach($option['options'] as $option_key => $option_option){
						$multicount++;
						if($multicount > 1){ $query_inner .= ' OR '; }
						$query_inner .= "option_name = '" . $option_id . "_" . $option_key . "'";
						
					}
					
				} else if(is_array($option_type)) {
					$type_array_count = 0;
					foreach($option_type as $inner_option){
						$type_array_count++;
						$option_id = $inner_option['id'];
						if($type_array_count > 1){ $query_inner .= ' OR '; }
						$query_inner .= "option_name = '$option_id'";
					}
					
				} else {
					$query_inner .= "option_name = '$option_id'";
				}
			}
		}
		
		//When Options page is reset - Add the qbw_options option
		if ( 'quickbusinesswebsite' == $page ) {
			$query_inner .= " OR option_name = 'qbw_options'";
		}
		$query = "DELETE FROM $wpdb->options WHERE $query_inner";
		$wpdb->query($query);
			
	} // end reset_options
	/** 
	 * output options page
	 *
	 * @since 1.0
	 */
	public function options_page(){
	    $options = get_option( 'qbw_template');      
		?>
	<div class="wrap" id="smartestb_container">
	<div id="smartestb-popup-save" class="smartestb-save-popup"><div class="smartestb-save-save"><?php _e('Options Updated', 'quick-business-website'); ?></div></div>
	<div id="smartestb-popup-reset" class="smartestb-save-popup"><div class="smartestb-save-reset"><?php _e('Options Reset', 'quick-business-website'); ?></div></div>
	    <form action="" enctype="multipart/form-data" id="smartestbform">
	        <div id="header">
	           <div class="logo">
				<?php if ( $custom_logo_id = get_theme_mod( 'custom_logo' ) ) {
					echo wp_get_attachment_image( $custom_logo_id, 'full' );
				} ?>
	          </div>
	             <div class="theme-info">
					<span class="theme" style="margin-top:10px;"><?php _e('Quick Business Website', 'quick-business-website'); ?>
					</span>
				</div>
				<div class="clear"></div>
			</div>
	        <?php $return = $this->machine($options); ?>
			<div id="support-links">
	<!--[if IE]>
	<div class="ie">
	<![endif]-->
				<ul>
<!-- @todo url... -->
<li class="smar-ui-icon"><a href="#@todo" target="_blank" rel="nofollow">
<div class="dashicons dashicons-book-alt"></div> <?php _e( 'Documentation', 'quick-business-website' ); ?></a></li>

<li class="smar-ui-icon"><a href="https://wordpress.org/support/plugin/quick-business-website/reviews/" target="_blank" title="Rate This Plugin">
<div class="dashicons dashicons-star-filled"></div> <?php _e( 'Rate This Plugin', 'quick-business-website' ); ?></a></li>

<li class="right"><img style="display:none" src="<?php echo QUICKBUSINESSWEBSITE_URL; ?>images/loading-top.gif" class="ajax-loading-img ajax-loading-img-top" alt="Working..." />
	<input type="submit" value="<?php _e('Save All Changes', 'quick-business-website'); ?>" class="button submit-button" /></li>
				</ul> 
	<!--[if IE]>
	</div>
	<![endif]-->
			</div>
	        <div id="main">
		        <div id="smartestb-nav">
					<ul><?php echo $return[1] ?></ul>		
				</div>
				<div id="content">
		         <?php echo $return[0]; /* Settings */ ?>
		        </div>
		        <div class="clear"></div>
	        </div>
	        <!--[if IE]>
			<div class="ie">
			<![endif]-->
	        <div class="save_bar_top">
	        <img style="display:none" src="<?php echo QUICKBUSINESSWEBSITE_URL; ?>images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
	        <input type="submit" value="<?php _e('Save All Changes', 'quick-business-website'); ?>" class="button submit-button" />        
	        </form>
	     
	        <form action="<?php echo esc_html( $_SERVER['REQUEST_URI'] ) ?>" method="post" style="display:inline" id="smartestbform-reset">
	            <span class="submit-footer-reset">
	            <input name="reset" type="submit" value="<?php _e('Reset Options', 'quick-business-website'); ?>" class="button submit-button reset-button" onclick="return confirm(localized_label.reset);" />
	            <input type="hidden" name="smartestb_save" value="reset" /> 
	            </span>
	        </form>
	       
	        </div>
	        <!--[if IE 6]>
			</div>
			<![endif]-->
	<div style="clear:both;"></div>    
	</div><!--wrap-->
	
	 <?php
	}// end options_page

	/** 
	 * Enqueue admin scripts
	 *
	 * @since 1.0
	 */
	public function frame_load() {
		add_action('admin_head', 'qbw_admin_head');
		wp_enqueue_script('jquery-ui-core');// @test need
	
		function qbw_admin_head() {
			/**
			 * Localized string for js
			 */
			$okr = __('Click OK to reset back to default settings. All custom QBW plugin settings will be lost!', 'quick-business-website');
			 // deliver the vars to js
				?>
			<script>
				var localized_label = {
					reset : "<?php echo $okr ?>",
				}
			</script>
			
			<script type="text/javascript">
				jQuery(document).ready(function(){
	
					jQuery('.group').hide();
					jQuery('.group:first').fadeIn();
					
					jQuery('.group .collapsed').each(function(){
						jQuery(this).find('input:checked').parent().parent().parent().nextAll().each( 
							function(){
	           					if (jQuery(this).hasClass('last')) {
	           						jQuery(this).removeClass('hidden');
	           						return false;
	           					}
	           					jQuery(this).filter('.hidden').removeClass('hidden');
	           				});
	           		});
	          					
					jQuery('.group .collapsed input:checkbox').click(unhideHidden);
					
					function unhideHidden(){
						if (jQuery(this).attr('checked')) {
							jQuery(this).parent().parent().parent().nextAll().removeClass('hidden');
						}
						else {
							jQuery(this).parent().parent().parent().nextAll().each( 
								function(){
	           						if (jQuery(this).filter('.last').length) {
	           							jQuery(this).addClass('hidden');
										return false;
	           						}
	           						jQuery(this).addClass('hidden');
	           					});
	           					
						}
					}
				
					jQuery('#smartestb-nav li:first').addClass('current');
					jQuery('#smartestb-nav li a').click(function(evt){
					
							jQuery('#smartestb-nav li').removeClass('current');
							jQuery(this).parent().addClass('current');
							
							var clicked_group = jQuery(this).attr('href');
			 
							jQuery('.group').hide();
							
								jQuery(clicked_group).fadeIn();
			
							evt.preventDefault();
							
						});
					
					if('<?php if(isset($_REQUEST['reset'])) { echo $_REQUEST['reset'];} else { echo 'false';} ?>' == 'true'){
						
						var reset_popup = jQuery('#smartestb-popup-reset');
						reset_popup.fadeIn();
						window.setTimeout(function(){
							   reset_popup.fadeOut();                        
							}, 2000);
							//alert(response);
						
					}
						
				//Update Message popup
				jQuery.fn.center = function () {
					this.animate({"top":( jQuery(window).height() - this.height() - 200 ) / 2+jQuery(window).scrollTop() + "px"},100);
					this.css("left", 250 );
					return this;
				}
			
				
				jQuery('#smartestb-popup-save').center();
				jQuery('#smartestb-popup-reset').center();
				jQuery(window).scroll(function() { 
				
					jQuery('#smartestb-popup-save').center();
					jQuery('#smartestb-popup-reset').center();
				
				});
			
				//Save everything else
				jQuery('#smartestbform').submit(function(){
					
						function newValues() {
						  var serializedValues = jQuery("#smartestbform").serialize();
						  return serializedValues;
						}
						jQuery(":checkbox, :radio").click(newValues);
						jQuery("select").change(newValues);
						jQuery('.ajax-loading-img').fadeIn();
						var serializedReturn = newValues();
						 
						var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
					
						 //var data = {data : serializedReturn};
						var data = {
							<?php if ( isset( $_REQUEST['page'] ) && 'quickbusinesswebsite' == $_REQUEST['page'] ) { ?>
							type: 'options',
							<?php } ?>
	
							action: 'smartestb_ajax_post_action',
							data: serializedReturn
						};
						
						jQuery.post(ajax_url, data, function(response) {
							var success = jQuery('#smartestb-popup-save');
							var loading = jQuery('.ajax-loading-img');
							loading.fadeOut();  
							success.fadeIn();
							window.setTimeout(function(){
							   success.fadeOut(); 
							   
													
							}, 2000);
						});
						
						return false; 
						
					});   	 	
					
				});
			</script>
			
		<?php }
	}// end frame_load

	/**
	 * ajax callback
	 * @since 1.0
	 */
	public function ajax_callback() {
		global $wpdb;
		$save_type = $_POST['type'];
		
		if ( $save_type == 'options' ) {
	
			$data = $_POST['data'];
			parse_str($data,$output);
	        	$options = get_option( 'qbw_template');
			foreach($options as $option_array){
				$id = isset($option_array['id']) ? $option_array['id'] : '';
				$old_value = get_option($id);
				$new_value = '';
				
				if(isset($output[$id])){
					$new_value = $output[$option_array['id']];
				}
		
				if(isset($option_array['id'])) { // Non - Headings...
					
					$type = $option_array['type'];
				
					if ( is_array($type)){
						foreach($type as $array){
							if($array['type'] == 'text'){
								$id = $array['id'];
								$new_value = $output[$id];
								update_option( $id, stripslashes($new_value));// isa, may conflict w url inputs that need slashes
							}
						}                 
					}
					elseif( $new_value == '' && $type == 'checkbox' ) { // Checkbox Save
						update_option( $id,'false' );
					}
					elseif ( $new_value == 'true' && $type == 'checkbox' ) { // Checkbox Save
						update_option( $id,'true' );
					}
					else {
						update_option( $id, stripslashes( $new_value ) );
					}
				}	
			}
			/* Create, Encrypt and Update the Saved Settings */
			$query_inner = '';
			$count = 0;
	
			print_r($options);
			foreach($options as $option){
				
				if(isset($option['id'])){ 
					$count++;
					$option_id = $option['id'];
					$option_type = $option['type'];
					
					if($count > 1){ $query_inner .= ' OR '; }
					
					if(is_array($option_type)) {
					$type_array_count = 0;
					foreach($option_type as $inner_option){
						$type_array_count++;
						$option_id = $inner_option['id'];
						if($type_array_count > 1){ $query_inner .= ' OR '; }
						$query_inner .= "option_name = '$option_id'";
						}
					}
					else {
					
						$query_inner .= "option_name = '$option_id'";
						
					}
				}
				
			}
			
			$query = "SELECT * FROM $wpdb->options WHERE $query_inner";
					
			$results = $wpdb->get_results($query);
			
			$output = "<ul>";
			
			foreach ($results as $result){
					$name = $result->option_name;
					$value = $result->option_value;
					
					if(is_serialized($value)) {
						
						$value = unserialize($value);
						$qbw_array_option = $value;
						$temp_options = '';
						foreach($value as $v){
							if(isset($v))
								$temp_options .= $v . ',';
							
						}	
						$value = $temp_options;
						$qbw_array[$name] = $qbw_array_option;
					} else {
						$qbw_array[$name] = $value;
					}
					
					$output .= '<li><strong>' . $name . '</strong> - ' . $value . '</li>';
			}
			$output .= "</ul>";
			
			update_option( 'qbw_options',$qbw_array);
			update_option( 'qbw_settings_encode',$output);
			// this makes it finally flush, but only if you save twice. Isa
			flush_rewrite_rules();
		}
		die();
	}

	/** 
	 * Generate the options
	 * @since 1.0
	 */
	public function machine( $options ) {
	    $counter = 0;
		$menu = '';
		$output = '';
		foreach ( $options as $value ) {
			$counter++;
			$val = '';
			//Start Heading
			if ( $value['type'] != "heading" ) {
			 	$class = ''; if(isset( $value['class'] )) { $class = $value['class']; }
				$output .= '<div class="section section-'.$value['type'].' '. $class .'">'."\n";
				if ( !empty($value['name']) ) {
					$output .= '<h3 class="heading">'. $value['name'] .'</h3>'."\n";
				}
				$output .= '<div class="option">'."\n" . '<div class="controls">'."\n";
			} 
			 //End Heading
			$select_value = '';                                   
			switch ( $value['type'] ) {
			
			case 'text':
			if ( ! empty( $value['std'] ) ) {
				$val = esc_attr($value['std']);
			}
				$std = esc_attr(get_option($value['id']));
				if ( $std != "") { $val = $std; }
				$output .= '<input class="smartestb-input" name="'. $value['id'] .'" id="'. $value['id'] .'" type="'. $value['type'] .'" value="'. stripslashes($val) .'" />';
			break;
			
			case 'select':
	
				$output .= '<select class="smartestb-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';
			
				$select_value = get_option($value['id']);
				 
				foreach ($value['options'] as $option) {
					
					$selected = '';
					
					 if($select_value != '') {
						 if ( $select_value == $option) { $selected = ' selected="selected"';} 
				     } else {
						 if ( isset($value['std']) )
							 if ($value['std'] == $option) { $selected = ' selected="selected"'; }
					 }
					  
					 $output .= '<option'. $selected .'>';
					 $output .= $option;
					 $output .= '</option>';
				 
				 } 
				 $output .= '</select>';
				
			break;
			case 'textarea':
				$cols = '8';
				$ta_value = '';
				if(isset($value['std'])) {
					$ta_value = $value['std']; 
					if(isset($value['options'])){
						$ta_options = $value['options'];
						if(isset($ta_options['cols'])){
						$cols = $ta_options['cols'];
						} else { $cols = '8'; }
					}
					
				}
					$std = esc_attr(get_option($value['id']));
					if( $std != "") { $ta_value = esc_attr( $std ); }
					$output .= '<textarea class="smartestb-input" name="'. $value['id'] .'" id="'. $value['id'] .'" cols="'. $cols .'" rows="8">'.stripslashes($ta_value).'</textarea>';
			break;
			case "checkbox": 
			if ( ! empty( $value['std'] ) ) {
					$std = $value['std'];
			}
			   $saved_std = get_option($value['id']);
			   $checked = '';
				if(!empty($saved_std)) {
					if($saved_std == 'true') {
					$checked = 'checked="checked"';
					}
					else{
					   $checked = '';
					}
				}
				elseif( $std == 'true') {
				   $checked = 'checked="checked"';
				}
				else {
					$checked = '';
				}
				$output .= '<input type="checkbox" class="checkbox smartestb-input" name="'.  $value['id'] .'" id="'. $value['id'] .'" value="true" '. $checked .' />';
	
			break;
			case "info":
				$default = $value['std'];
				$output .= $default;
			break;                                   
			case "heading":
				if($counter >= 2){
				   $output .= '</div>'."\n";
				}
				$jquery_click_hook = preg_replace('#[^A-Za-z0-9]#', '', strtolower($value['name']) );
				$jquery_click_hook = "smartestb-option-" . $jquery_click_hook;
						$menu .= '<li><a ';
						if ( !empty( $value['class'] ) ) {
							$menu .= 'class="'.  $value['class'] .'" ';
						}
						$menu .= 'title="'.  $value['name'] .'" href="#'.  $jquery_click_hook  .'"><span class="smartestb-nav-icon"></span>'.  $value['name'] .'</a></li>';
				$output .= '<div class="group" id="'. $jquery_click_hook  .'"><h2>'.$value['name'].'</h2>'."\n";
			break;                                  
			} 
			
			// if TYPE is an array, formatted into smaller inputs... ie smaller values
			if ( is_array($value['type'])) {
				foreach($value['type'] as $array){
				
						$id =   $array['id']; 
						$std =   $array['std'];
						$saved_std = get_option($id);
						if($saved_std != $std && !empty($saved_std) ){$std = $saved_std;} 
						$meta =   $array['meta'];
						if($array['type'] == 'text') { // Only text at this point
							 $output .= '<input class="input-text-small smartestb-input" name="'. $id .'" id="'. $id .'" type="text" value="'. $std .'" />';  
							 $output .= '<span class="meta-two">'.$meta.'</span>';
						}
					}
			}
			if ( $value['type'] != "heading" ) { 
				if ( $value['type'] != "checkbox" ) 
					{ 
					$output .= '<br/>';
					}
				if(!isset($value['desc'])){ $explain_value = ''; } else{ $explain_value = $value['desc']; } 
				$output .= '</div><div class="explain">'. $explain_value .'</div>'."\n";
				$output .= '<div class="clear"> </div></div></div>'."\n";
				}
		   
		}
	    $output .= '</div>';
	    return array($output,$menu);
	}// end machine

	/** 
	 * Create Admin Menu Separator
	 * @since 1.0
	 */
	function add_admin_menu_separator($position) {
		global $menu;
		$index = 0;
	
		foreach($menu as $offset => $section) {
			if (substr($section[2],0,9)=='separator')
			    $index++;
			if ($offset>=$position) {
				$menu[$position] = array('','read',"separator{$index}",'','wp-menu-separator');
				break;
		    }
		}
	
		ksort( $menu );
	}
	
	/** 
	 * Style the custom text logo on wp-login.php
	 * @since 1.0
	 */
	public function login_logo() {
			echo'<style type="text/css">.login h1 a {background-position: center top;text-indent: 0px;text-align:center; background-image:none;text-decoration:none;width: 326px;height: 70px;}</style>';
	}
	/** 
	 * Get the business name. To be used as link title on wp-login.php
	 * @since 1.0
	 */
	public function wp_login_title() {
		$bn = stripslashes_deep(esc_attr(get_option( 'qbw_business_name')));
		if ( empty($bn) )
			$bn = get_bloginfo('name');
		return $bn;
	}
	
	/** 
	 * Create a page, post, or custom post
	 *
	 * @param mixed $potype post type of the new page
	 * @param mixed $slug Slug for the new page
	 * @param mixed $option Option name to store the page's ID
	 * @param string $page_title (default: '') Title for the new page
	 * @param string $page_content (default: '') Content for the new page
	 * @param int $post_parent (default: 0) Parent for the new page
	 * @since 1.0
	 */
	
	public function insert_post($potype, $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
		global $wpdb;
		$option_value = get_option( $option );

		if ( $option_value > 0 && get_post( $option_value ) )
			return;
		$page_data = array(
	        'post_status' 		=> 'publish',
	        'post_type' 		=> $potype,// was 'page',
	        'post_author' 		=> 1,
	        'post_name' 		=> $slug,
	        'post_title' 		=> $page_title,
	        'post_content' 		=> $page_content,
	        'post_parent' 		=> $post_parent,
	        'comment_status' 	=> 'closed'
	    );
	    $page_id = wp_insert_post( $page_data );
	
	    update_option( $option, $page_id );
	
	} // end insert_post
	/** 
	 * Activate Smartest Reviews.
	 *	 
	 * @uses insert_post()
	 * @since 1.0
	 */
	public function after_setup() {
		if ( ! class_exists( 'SMARTESTReviewsBusiness' ) && ( get_option( 'qbw_add_reviews' ) == 'true')) {
			include_once QUICKBUSINESSWEBSITE_PATH . 'modules/smartest-reviews/smartest-reviews.php';
		}
	}

	/** 
	 * Add CPTs conditionally, if enabled. Adds smartest_staff, smartest_services, smartest_news. Also, delete About page if disabled, delete Reviews page if disabled.
	 * @since 1.0
	 */
	
	public function create_business_cpts() {
		$staff = get_option( 'qbw_show_staff');
		$news = get_option( 'qbw_show_news');
		$services = get_option( 'qbw_show_services');
		
				// if add staff enabled
				
				if( $staff == 'true'  ) { 
	
					//register cpt staff
			    	$args = array(
			        	'label' => __('Staff','quick-business-website'),
			        	'singular_label' => __('Staff','quick-business-website'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('staff', 'quick-business-website'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Staff','quick-business-website' ),
							'singular_name' => __( 'Staff','quick-business-website' ),
							'add_new' => __( 'Add New','quick-business-website' ),
							'add_new_item' => __( 'Add New Staff','quick-business-website' ),
							'all_items' => __( 'All Staff','quick-business-website' ),
							'edit' => __( 'Edit','quick-business-website' ),
							'edit_item' => __( 'Edit Staff','quick-business-website' ),
							'new_item' => __( 'New Staff','quick-business-website' ),
							'view' => __( 'View Staff','quick-business-website' ),
							'view_item' => __( 'View Staff','quick-business-website' ),
							'search_items' => __( 'Search Staff','quick-business-website' ),
							'not_found' => __( 'No staff found','quick-business-website' ),
							'not_found_in_trash' => __( 'No staff found in Trash','quick-business-website' ),
							'parent' => __( 'Parent Staff','quick-business-website' ),
						),
			        	'supports' => array('title','editor','thumbnail','excerpt'),
					'has_archive' => true,
					'menu_icon' => 'dashicons-groups',
			        );
	
		    	register_post_type( 'smartest_staff' , $args );
	
				}// end if show staff enabled
				//if show news enabled, create news cpt
				if($news == 'true') { 
					//register cpt news
			    	$args = array(
			        	'label' => __('Announcements','quick-business-website'),
			        	'singular_label' => __('Announcement','quick-business-website'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('news','quick-business-website'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Announcements','quick-business-website' ),
							'singular_name' => __( 'Announcement','quick-business-website' ),
							'add_new' => __( 'Add New','quick-business-website' ),
							'add_new_item' => __( 'Add New Announcement','quick-business-website' ),
							'all_items' => __( 'All Announcements','quick-business-website' ),
							'edit' => __( 'Edit','quick-business-website' ),
							'edit_item' => __( 'Edit Announcement','quick-business-website' ),
							'new_item' => __( 'New Announcement','quick-business-website' ),
							'view' => __( 'View Announcement','quick-business-website' ),
							'view_item' => __( 'View Announcement','quick-business-website' ),
							'search_items' => __( 'Search Announcements','quick-business-website' ),
							'not_found' => __( 'No announcement found','quick-business-website' ),
							'not_found_in_trash' => __( 'No announcements found in Trash','quick-business-website' ),
							'parent' => __( 'Parent Announcement','quick-business-website' ),
						),
			        	'supports' => array('title','editor','thumbnail'),
					'has_archive' => true,
					'menu_icon' => 'dashicons-exerpt-view'
	
			        );
	
		    	register_post_type( 'smartest_news' , $args );
	
				}// end if show news enabled
	
				//if show services enabled, create services cpt
				if($services == 'true') { 
					//register cpt services
			    	$args = array(
			        	'label' => __('Services','quick-business-website'),
			        	'singular_label' => __('Service','quick-business-website'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('services','quick-business-website'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Services','quick-business-website' ),
							'singular_name' => __( 'Service','quick-business-website' ),
							'add_new' => __( 'Add New','quick-business-website' ),
							'all_items' => __( 'All Services','quick-business-website' ),
							'add_new_item' => __( 'Add New Service','quick-business-website' ),
							'edit' => __( 'Edit','quick-business-website' ),
							'edit_item' => __( 'Edit Service','quick-business-website' ),
							'new_item' => __( 'New Service','quick-business-website' ),
							'view' => __( 'View Services','quick-business-website' ),
							'view_item' => __( 'View Service','quick-business-website' ),
							'search_items' => __( 'Search Services','quick-business-website' ),
							'not_found' => __( 'No services found','quick-business-website' ),
							'not_found_in_trash' => __( 'No services found in Trash','quick-business-website' ),
							'parent' => __( 'Parent Service','quick-business-website' ),
						),
			        	'supports' => array('title','editor','thumbnail'),
					'has_archive' => true,
					'menu_icon' => 'dashicons-portfolio'
			        );
		    	register_post_type( 'smartest_services' , $args );
				}// end if show services enabled

		// If Reviews are disabled, delete the page
		if ( get_option( 'qbw_add_reviews' ) == 'false' ) {
			wp_delete_post(get_option('qbw_reviews_page_id'), true);
		}

	} // end create_business_cpts
	

	/**
	 * Registers custom taxonomy for services
	 * @since 1.4
	 * @return void
	 */
	function set_taxonomies() {
		$category_labels = array(
			'name' => __( 'Service Categories', 'quick-business-website' ),
			'singular_name' =>__( 'Service Category', 'quick-business-website' ),
			'search_items' => __( 'Search Service Categories', 'quick-business-website' ),
			'all_items' => __( 'All Service Categories', 'quick-business-website' ),
			'parent_item' => __( 'Parent Service Category', 'quick-business-website' ),
			'parent_item_colon' => __( 'Parent Service Category:', 'quick-business-website' ),
			'edit_item' => __( 'Edit Service Category', 'quick-business-website' ),
			'update_item' => __( 'Update Service Category', 'quick-business-website' ),
			'add_new_item' => __( 'Add New Service Category', 'quick-business-website' ),
			'new_item_name' => __( 'New Service Category Name', 'quick-business-website' ),
			'menu_name' => __( 'Service Categories', 'quick-business-website' ),
		);
		
		$category_args = apply_filters( 'smartestb_service_category_args', array(
			'hierarchical'		=> true,
			'labels'			=> apply_filters('smartestb_service_category_labels', $category_labels),
			'show_ui'           => true,
			'show_admin_column' => true,
			'query_var' => true,
			'rewrite'			=> array(
								'slug'		=> 'services/category',
								'with_front'	=> false,
								'hierarchical'	=> true ),
		)
		);
		register_taxonomy( 'smartest_service_category', array('smartest_services'), $category_args );
		register_taxonomy_for_object_type( 'smartest_service_category', 'smartest_services' );
	}

	/** 
	 * Custom metaboxes and fields for staff cpt: order number, occupational title & social links. For services and news: featured.
	 */
	public function metaboxes() {
		$prefix = '_smab_';
		$meta_boxes[] = array(
			'id'         => 'staff_details',
			'title'      => __('Details', 'quick-business-website'),
			'pages'      => array( 'smartest_staff', ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __( 'Job Title', 'quick-business-website' ),
					'desc' => __( 'The staff member\'s job title. Optional', 'quick-business-website' ),
					'id'   => $prefix . 'staff_job_title',
					'type' => 'text_medium',
				),
				array(
					'name' => __( 'Sort Order Number', 'quick-business-website' ),
					'desc' => __( 'Give this person a number to order them on the staff list. Number 1 appears 1st on the list, while greater numbers appear lower. Numbers do not have to be consecutive; for example, you could number them like, 10, 20, 35, 45, etc. This would leave room in between to insert new staff members later without having to change everyone\'s current number.', 'quick-business-website' ),
					'id'   => $prefix . 'staff-order-number',
					'type' => 'text',
					'std' => 9999
				),
				array(
					'name' => __('Facebook Profile ID', 'quick-business-website'),
					'desc' => __('The staff member\'s Facebook profile ID. Optional', 'quick-business-website'),
					'id'   => $prefix . 'staff_facebook',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Twitter Username', 'quick-business-website'),
					'desc' => __('The staff member\'s Twitter username. Optional', 'quick-business-website'),
					'id'   => $prefix . 'staff_twitter',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Google Plus Profile ID', 'quick-business-website'),
					'desc' => __('The staff member\'s Google Plus profile ID. Optional', 'quick-business-website'),
					'id'   => $prefix . 'staff_gplus',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Linkedin Profile', 'quick-business-website'),
					'desc' => __('The part of the profile address after "www.linkedin.com/". Optional', 'quick-business-website'),
					'id'   => $prefix . 'staff_linkedin',
					'type' => 'text_medium',
				),
			)
		);
		$meta_boxes[] = array(
			'id'         => 'featured_svcs',
			'title'      => __('Featured Services', 'quick-business-website'),
			'pages'      => array( 'smartest_services', ), // Post type
			'context'    => 'side',
			'priority'   => 'default',//high, core, default, low
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __('Feature this?', 'quick-business-website'),
					'desc' => __('Check this box to feature this service in the list of featured services on the home page and in the Featured Services widget.', 'quick-business-website'),
					'id'   => $prefix . 'services_featured',
					'type' => 'checkbox',
				),
			)
		);
	if( get_option( 'qbw_enable_service_sort') == 'true'  ) {// @test need?
		$meta_boxes[] = array(
			'id'         => 'services-sort-order',
			'title'      => __( 'Set a Sort-Order', 'quick-business-website' ),
			'pages'      => array( 'smartest_services' ),
			'context'    => 'normal',
			'priority'   => 'high',//high, core, default, low
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __( 'Sort Order Number', 'quick-business-website' ),
					'desc' => __( 'Give this service a number to order it on the services list. Number 1 appears 1st on the list, while greater numbers appear lower. Numbers do not have to be consecutive; for example, you could number them like, 10, 20, 35, 45, etc. This would leave room in between to insert new services later without having to change all current numbers.', 'quick-business-website' ),
					'id'   => $prefix . 'service-order-number',
					'type' => 'text',
					'std' => 9999
				),
			)
		);
	}
		$meta_boxes[] = array(
			'id'         => 'featured_news',
			'title'      => __('Featured News', 'quick-business-website'),
			'pages'      => array( 'smartest_news', ),
			'context'    => 'side',
			'priority'   => 'default',
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __('Feature this?', 'quick-business-website'),
					'desc' => __('Check this box to feature this announcement in the Featured Announcements widget.', 'quick-business-website'),
					'id'   => $prefix . 'news_featured',
					'type' => 'checkbox',
				),
			)
		);

		if ( ! class_exists( 'QBW_Metabox' ) ) {
			require_once QUICKBUSINESSWEBSITE_PATH . 'inc/class-qbw-metabox.php';
		}

		foreach ( $meta_boxes as $meta_box ) {
			$my_box = new QBW_Metabox( $meta_box );
		}

	} // end metaboxes()

	/** 
	 * Do 'Enter Staff member's name here' instead of 'Enter title here' for smartest_staff custom post type
	 * @since 1.0
	 */
	public function change_enter_title( $title ){
		$screen = get_current_screen();
		if  ( 'smartest_staff' == $screen->post_type ) {
			$title = __('Enter staff member\'s name here', 'quick-business-website');} return $title;
	}

	/** 
	 * register widgets
	 * @since 1.0
	 */
	public function register_widgets() {
	
		if( get_option( 'qbw_show_news') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/announcements.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-announcements.php';
			register_widget('SmartestAnnouncements'); register_widget('SmartestFeaturedAnnounce');
		}
		if( get_option( 'qbw_show_services') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/all-services.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-services.php';
			register_widget('SmartestServices'); register_widget('SmartestFeaturedServices'); 
		}
		if( get_option( 'qbw_show_staff') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/staff.php';
			register_widget('SmartestStaff'); 
		}
	
	} // end register_widgets
	/** 
	 * insert custom scripts from options
	 * @since 1.0
	 */
	public function add_customscripts() {
		$gascript =  get_option( 'qbw_script_analytics');
		$oscripts =  get_option( 'qbw_scripts_head');
		if (isset($gascript) && $gascript != '') {
			echo stripslashes($gascript)."\r\n";
		}
		if (isset($oscripts) && $oscripts != '') {
			echo stripslashes($oscripts)."\r\n";
		}
	
	} // end add_customscripts
	/**
	 * Add job title column to staff admin
	 * @since 1.0
	 */
	public function smar_manage_edit_staff_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Name', 'quick-business-website'),
			'jobtitle' => __('Job Title', 'quick-business-website'),
			'date' => __('Date', 'quick-business-website')
		);
	
		return $columns;
	}
	/** 
	 * Add data to job title column in staff admin
	 * @since 1.0
	 */
	public function smar_manage_staff_columns( $column, $post_id ) {
		global $post;
		switch( $column ) {
			case 'jobtitle' :
				$jobtitle = get_post_meta( $post_id, '_smab_staff_job_title', true );
					 echo $jobtitle;
				break;
			default :
				break;
		}
	}
	/** 
	 * Add featured service column to services admin
	 * @since 1.0
	 */
	public function smar_manage_edit_services_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', 'quick-business-website'),
			'taxonomy-smartest_service_category' => __('Service Categories', 'quick-business-website'),
			'featureds' => __('Featured', 'quick-business-website'),
			'date' => __('Date', 'quick-business-website')
		);
		return $columns;
	}
	/** 
	 * Add data to featured services column in services admin
	 * @since 1.0
	 */
	public function smar_manage_services_columns( $column, $post_id ) {
		global $post;
		switch( $column ) {
			case 'featureds' :
				$sf = get_post_meta( $post_id, '_smab_services_featured', true );
				
				if ( $sf )
					_e('Featured', 'quick-business-website');
				break;
			default :
				break;
		}
	}
	/** 
	 * Add featured news column to news admin
	 * @since Quick Business Website 1.0
	 */
	public function smar_manage_edit_news_columns( $columns ) {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'title' => __('Title', 'quick-business-website'),
			'featuredn' => __('Featured', 'quick-business-website'),
			'date' => __('Date', 'quick-business-website')
		);
		return $columns;
	}
	/** 
	 * Add data to featured news column in news admin
	 * @since Quick Business Website 1.0
	 */
	public function smar_manage_news_columns( $column, $post_id ) {
		global $post;
		switch( $column ) {
			case 'featuredn' :
				$sf = get_post_meta( $post_id, '_smab_news_featured', true );
				if ( $sf )
					_e('Featured', 'quick-business-website');
				break;
			default :
				break;
		}
	}
	/** 
	 * Replace WP footer with own custom text, if enabled
	 * @since Quick Business Website 1.0
	 */
	public function remove_footer_admin () {
		if ( (get_option( 'qbw_admin_footer') != '') &&  (get_option( 'qbw_remove_adminfooter') == 'false')) {
			echo get_option( 'qbw_admin_footer');
		} elseif ( get_option( 'qbw_remove_adminfooter') == 'true' ) {
			echo '';
		} else {
			echo 'Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.';
		}
	}
	/** 
	 * Register front-end stylesheet
	 * @since 1.0
	 */
	public function framework_enq() {
		wp_register_style( 'qbw', QUICKBUSINESSWEBSITE_URL . 'css/qbw.css' );
		wp_enqueue_style( 'qbw' );
		wp_enqueue_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css');// @todo need
	}

	/**
	 * Add job title and social links to content for 'staff' custom post type
	 *
	 * @uses is_single()
	 * @uses get_post_type()
	 * @uses get_post_meta()
	 * @since Quick Business Website 1.0
	 */
	public function staff_meta_content_filter( $content ) {
	    if ( is_single() && ( 'smartest_staff' == get_post_type() ) ) {
			global $post;
			$staffcontent = '<div id="staff-meta">';
			if (get_post_meta($post->ID, '_smab_staff_job_title', true)) {
				$staffcontent .= '<h5>' . get_post_meta($post->ID, '_smab_staff_job_title', true) . '</h5>';
			}
			if (get_option( 'qbw_old_social_icons') == 'false') {
				$twit = 'fa-twitter';
				$goog = 'fa-google';
				$face = 'fa-facebook';
				$link = 'fa-linkedin';
			} else {
				$twit = 'item-1';
				$goog = 'item-2';
				$face = 'item-3';
				$link = 'item-4';
			}
			$staffcontent .= '<ul id="qbw-staff-socials">';
			if (get_post_meta($post->ID, '_smab_staff_twitter', true)) {
					$staffcontent .= '<li><a class="' . $twit. '" href="https://twitter.com/' . get_post_meta($post->ID, '_smab_staff_twitter', true) . '" title="'. __('Twitter', 'quick-business-website') . '"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_gplus', true)) {
					$staffcontent .= '<li><a class="' . $goog .'" href="https://plus.google.com/' . get_post_meta($post->ID, '_smab_staff_gplus', true) . '" title="'. __('Google Plus', 'quick-business-website') . '" rel="author"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_facebook', true)) {
					$staffcontent .= '<li><a class="' . $face. '" href="https://facebook.com/' . get_post_meta($post->ID, '_smab_staff_facebook', true) . '" title="'. __('Facebook', 'quick-business-website') . '"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_linkedin', true)) {
					$staffcontent .= '<li><a class="' . $link .'" href="http://www.linkedin.com/' . get_post_meta($post->ID, '_smab_staff_linkedin', true) . '" title="'. __('LinkedIn', 'quick-business-website') . '"></a></li>';
			}
			$staffcontent .= '</ul></div>' . $content;
			return $staffcontent;

		} else {
			// regular content
			return $content;
		}
	}// end staff_meta_content_filter
	/**
	 * Add business info to content on Contact page
	 *
	 * @since 1.0
	 * @uses is_page()
	 */
	public function contact_content_filter( $content ) {
		if( is_page( get_option('qbw_contact_page_id') ) ) {
			$contactcontent = '<div id="qbw-col-wrap"><div class="qbw-one-half">' . $content . '</div><div id="qbw-contact-info" class="qbw-one-half"  itemscope itemtype="http://schema.org/LocalBusiness">';
			// social box
			$contactcontent .= '<ul id="qbw-staff-socials">';
			if (get_option( 'qbw_old_social_icons') == 'false') {
				$twit = 'fa-twitter';
				$goog = 'fa-google';
				$face = 'fa-facebook';
				$yout = 'fa-youtube';
			} else {
				$twit = 'item-1';
				$goog = 'item-2';
				$face = 'item-3';
				$yout = 'youtube';
			}
			if ( get_option( 'qbw_business_twitter') ) {
				$contactcontent .= '<li><a class="' . $twit . '" href="https://twitter.com/' . get_option( 'qbw_business_twitter') . '" title="'. __('Twitter', 'quick-business-website') . '"></a></li>';
			} 
			if ( get_option( 'qbw_business_gplus') ) {
				$contactcontent .= '<li><a class="' . $goog . '" href="https://plus.google.com/' . get_option( 'qbw_business_gplus') . '" title="'. __('Google Plus', 'quick-business-website') . '" rel="publisher"></a></li>';
			} 
			if ( get_option( 'qbw_business_facebook') ) {
				$contactcontent .= '<li><a class="' . $face . '" href="https://facebook.com/' . get_option( 'qbw_business_facebook') . '" title="'. __('Facebook', 'quick-business-website') . '"></a></li>';
			}
			if ( get_option( 'qbw_business_youtube') ) {
				$contactcontent .= '<li><a class="' . $yout. '" href="https://youtube.com/user/' . get_option( 'qbw_business_youtube') . '" title="'. __('Youtube', 'quick-business-website') . '"></a></li>';
			}
			if ( get_option( 'qbw_business_socialurl1') ) {
				$contactcontent .= '<li><a class="item-add" target="_blank" href="'. get_option( 'qbw_business_socialurl1') . '" title="' . __( 'Connect', 'quick-business-website' ) . '">' . get_option( 'qbw_business_sociallabel1') . '</a></li>';
			} 
			if ( get_option( 'qbw_business_socialurl2') ) {
				$contactcontent .= '<li><a class="item-add" target="_blank" href="'. get_option( 'qbw_business_socialurl2') . '" title="' . __( 'Connect', 'quick-business-website' ) . '">' . get_option( 'qbw_business_sociallabel2') . '</a></li>';
			} 
			$contactcontent .= '</ul><strong><span itemprop="name">' . get_option( 'qbw_business_name') . '</span></strong><br /><br />';
			if (get_option( 'qbw_hours')) { 
				$contactcontent .= '<div id="qbw-contact-hours"><strong>Business Hours: </strong><br />' . wpautop(get_option( 'qbw_hours')) . '</div>';
			} 
			if ( get_option( 'qbw_google_map') ) {
				$contactcontent .= '<div id="qbw-goomap">'. get_option( 'qbw_google_map'). '</div>';
			}
			if(get_option( 'qbw_address_street')) { // do addy box
				$contactcontent .= '<p id="qbw-addy-box" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' . get_option( 'qbw_address_street') . '</span>&nbsp;';

			}
			if (get_option( 'qbw_address_suite') != '') {
				$contactcontent .= ' ' . get_option( 'qbw_address_suite') . '&nbsp;';
			}
			if (get_option( 'qbw_address_city') != '') {
				$contactcontent .='<br /><span itemprop="addressLocality">' . get_option( 'qbw_address_city') . '</span>';
			}
			if ( (get_option( 'qbw_address_city') != '') && (get_option( 'qbw_address_state') != '') ) {
				$contactcontent .= ', ';
			}
			if (get_option( 'qbw_address_state') != '') {
				$contactcontent .='<span itemprop="addressRegion">' . get_option( 'qbw_address_state') . '</span>&nbsp;';
				}
			if (get_option( 'qbw_address_zip') != '') {
				$contactcontent .=' <span class="postal-code" itemprop="postalCode">' . get_option( 'qbw_address_zip') . '</span>&nbsp;';
			}
			if (get_option( 'qbw_address_country') != '') {
				$contactcontent .='<br /><span itemprop="addressCountry">' . get_option( 'qbw_address_country') . '</span>&nbsp;';
			}
			if(get_option( 'qbw_address_street')) {
				$contactcontent .= '</p>'; // close #qbw-addy-box
			} // end addy-box
			if ( get_option( 'qbw_phone_number') || get_option( 'qbw_fax_numb') || ( get_option( 'qbw_show_contactemail') == 'true' ) ) {
				$contactcontent .= '<p>';
			
				if ( get_option( 'qbw_phone_number') ) {
					$contactcontent .= '' . __('Telephone:', 'quick-business-website') . ' <span itemprop="telephone">'. get_option( 'qbw_phone_number') . '</span>';
				}
				if ( get_option( 'qbw_fax_numb') ) {
					$contactcontent .= '<br />' . __('FAX:', 'quick-business-website') . ' <span itemprop="faxNumber">' . get_option( 'qbw_fax_numb') . '</span>';
				
				} 
				if ( get_option( 'qbw_show_contactemail') == 'true' ) {
					$contactcontent .= '<br />' . __('Email:', 'quick-business-website') . ' <a href="mailto:' . get_bloginfo('admin_email') . '"><span itemprop="email">' . get_bloginfo('admin_email') . '</span></a><br />';
				}
				$contactcontent .= '</p>';
			}
			$contactcontent .= '</div></div>';
			return $contactcontent;
			
		} else {
			// regular content
			return $content;
		}

	}// end contact_content_filter

	/**
	 * Sort staff archive by staff order number key
	 *
	 * @uses is_post_type_archive()
	 * @since 1.3.2
	 */

	public function sort_staff($query) {
		if( !is_admin() && is_post_type_archive('smartest_staff') && $query->is_main_query() && isset( $query->query_vars['meta_key'] ) ) {
			$query->query_vars['orderby'] = 'meta_value_num';
			$query->query_vars['meta_key'] = '_smab_staff-order-number';
			$query->query_vars['order'] = 'ASC';
		}
		return $query;
	}    

	/**
	 * Sort services archive by service order number key
	 *
	 * @uses is_admin()
	 * @uses is_post_type_archive()
	 * @uses is_main_query()
	 * @since 1.4.1
	 */
	function sort_services($query) {
		if( !is_admin() &&
		( 
		( is_post_type_archive('smartest_services') || is_tax( 'smartest_service_category' ) ) &&
		$query->is_main_query()
		)
		&& isset( $query->query_vars['meta_key'] ) ) {
		$query->query_vars['orderby'] = 'meta_value_num';
		$query->query_vars['meta_key'] = '_smab_service-order-number';
		$query->query_vars['order'] = 'ASC';
		}
		return $query;
	}
	/**
	 * Upgrade options
	 * @since 2.0
	 * @todo At some point in the future, remove this and delete the qbw_upgrade_two option on uninstall.
	 */
	public function upgrade_options() {
		// Run this update only once
		if ( get_option( 'qbw_upgrade_two' ) != 'completed' ) {

			global $wpdb;
			$old_prefixes = array( 'smartestb_', 'smartest_' );
			foreach ( $old_prefixes as $old_prefix ) {
				$value = $old_prefix . '%';
				// get all options with our old prefix
				$query = $wpdb->get_results(
					$wpdb->prepare("select * from " . $wpdb->options . " where option_name like %s", $value )
				);

				// Migrate options to new name
				if ( ! empty( $query[0] ) ) {
					$len = strlen( $old_prefix );
					foreach ( $query as $option ) {
						// remove old_prefix
						$basename = substr( $option->option_name, $len );
						// save option with new prefix
						update_option( 'qbw_' . $basename, $option->option_value );
						// delete old option
						delete_option( $option->option_name );
					}
				}
			}

			// delete uneeded options
			delete_option( 'qbw_smartestb_plugin_name' );
			delete_option( 'qbw_stop_theme_icon' );
			
			/************************************************************
			*
			* For backwards compatibility, prepend our About page with the
			* About page image if they had one
			* since our About page option is now removed.
			*
			************************************************************/
			// if they had an About page image and the About page was not disabled
			if ( ( $img_url = get_option( 'qbw_about_picture') ) && get_option( 'qbw_stop_about' ) != 'true' ) {

				// only if our About page exists
				$about_page_id = get_option( 'qbw_about_page_id' );
				if ( ! empty( $about_page_id ) ) {

					$content = get_post_field( 'post_content', $about_page_id );

					// get the image HTML
					$prepend = '<div id="qbw-about"><figure id="qbw-about-pic"><a href="' . $img_url . '"><img src="' . $img_url . '" alt="' . the_title_attribute('echo=0') . '" /></a></figure></div>';

						$about_page = array(
							'ID' => $about_page_id,
							'post_content' => $prepend . $content,
						);
						// Update the page
						wp_update_post( $about_page );
				}
			}

			// Set flag to run upgrade only once
			update_option( 'qbw_upgrade_two', 'completed' );
		}

	}

}
}
if ( 
	( defined('THEME_FRAMEWORK') && ( THEME_FRAMEWORK == 'Smartest Business Framework' ) )
	|| 
	( defined('SMARTEST_FRAMEWORK') && ( SMARTEST_FRAMEWORK == 'Business Framework' ) )
	) {
		$msg =  '<strong>' . __( 'You cannot activate Quick Business Website', 'quick-business-website') . '</strong> ' . __( 'plugin when using Smartest Themes because they clash. But Smartest Themes have everything the Quick Business Website plugin has, and more. QBW plugin will not be activated! To use the plugin, please change your Theme, first.', 'quick-business-website');
		wp_die($msg, 'Plugin Clashes With Theme', array('back_link' => true));
} else {
	register_activation_hook(__FILE__, array('Quick_Business_Website', 'activate'));
	$Quick_Business_Website = Quick_Business_Website::get_instance();

	/**
 	 * Include Contact form with both jquery client-side and php server-side validation
	 * @since 1.0
	 */
	include QUICKBUSINESSWEBSITE_PATH . 'modules/contact/contact.php';
}
