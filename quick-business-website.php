<?php
/*
Plugin Name: Quick Business Website
Plugin URI: http://smartestthemes.com/downloads/quick-business-website-plugin/
Description: Business website to showcase your services, staff, announcements, a working contact form, and reviews.
Version: 1.4
Author: Smartest Themes
Author URI: http://smartestthemes.com
License: GPL2
Text Domain: smartestb
Domain Path: lang
Copyright 2013 Smartest Themes(email : isa@smartestthemes.com)

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
if(!class_exists('Quick_Business_Website')) {
class Quick_Business_Website{
    public function __construct() {
			if( ! defined('QUICKBUSINESSWEBSITE_PATH')) {
				define( 'QUICKBUSINESSWEBSITE_PATH', plugin_dir_path(__FILE__) );
			}
			add_action( 'admin_init', array( $this, 'admin_init' ) );
			add_action( 'plugins_loaded', array( $this, 'load' ) );
			add_action( 'wp_ajax_smartestb_ajax_post_action', array( $this, 'ajax_callback' ) );
			add_action( 'admin_menu', array( $this, 'add_admin' ) );
			add_filter('plugin_action_links', array( $this, 'settings_link' ), 2, 2);
			add_action( 'login_head', array( $this, 'login_logo' ) );
			add_filter( 'login_headerurl',
			    create_function(false,"return get_home_url();"));
			add_filter( 'login_headertitle', array( $this, 'wp_login_title' ) );
			add_action( 'after_setup_theme', array( $this, 'after_setup' ) );
			add_action( 'init', array( $this, 'create_business_cpts') );
			add_action( 'init', array( $this, 'set_taxonomies' ), 0 );
			add_filter( 'cmb_meta_boxes', array( $this, 'metaboxes') );
			add_action( 'init', array( $this, 'initialize_cmb_meta_boxes' ), 9999 );
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
			add_action( 'wp_head', array( $this, 'version' ) );
			add_filter ( 'the_content',  array( $this, 'staff_meta_content_filter' ) );
			add_filter ( 'the_content',  array( $this, 'contact_content_filter' ), 50 );
			add_filter ( 'the_content',  array( $this, 'about_content_filter' ) );
			// Adds CPT archives menu items to wp_menu_nav and wp_page_menu, if not disabled. Priority matters.
			if(get_option('smartestb_stop_menuitems') == 'false') {
				add_filter( 'wp_nav_menu_items', array( $this, 'staff_menu_link' ), 10, 2);
				add_filter( 'wp_nav_menu_items', array( $this, 'services_menu_link' ), 15, 2);//prioritize this next
				add_filter( 'wp_nav_menu_items', array( $this, 'news_menu_link' ), 20, 2);//prioritize this next
				add_filter( 'wp_page_menu', array( $this, 'page_menu_staff' ), 95 );
				add_filter( 'wp_page_menu', array( $this, 'page_menu_services' ), 100 );
				add_filter( 'wp_page_menu', array( $this, 'page_menu_news' ), 105 );
			}
			add_filter( 'parse_query', array( $this, 'sort_staff' ) );
    } // end __contruct

	/** 
	* Only upon plugin activation, setup options, delete Old Contact page to not clash with new one, and flush rewrite rules for custom post types.
	*
	* @since 1.0
	*/
	public static function activate() { 
		$del_old_page = wp_delete_post(get_option('smartest_contact_page_id'), true);
		add_action( 'admin_head', array( __CLASS__, 'option_setup' ) );
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}
	/** 
	* Upon plugin deactivation, delete created pages and options
	*
	* @since 1.0
	*/
	public static function deactivate() { 
		wp_delete_post(get_option('smartest_reviews_page_id'), true);
		wp_delete_post(get_option('qbw_contact_page_id'), true);
		wp_delete_post(get_option('smartest_about_page_id'), true);
		delete_option( 'smartestb_options' );
		delete_option('smartest_reviews_page_id');
		delete_option('qbw_contact_page_id');
		delete_option('smartest_about_page_id');
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
	 $actions['settings'] = '<a href="admin.php?page=smartestbthemes">'. __('Settings', 'smartestb'). '</a>';
	return $actions; 
	}
	/** 
	* Include plugin options.
	*
	* @since 1.0
	* @return void
	*/
	public function load() {
		include QUICKBUSINESSWEBSITE_PATH . 'inc/options.php';
		add_action( 'init', 'smartestb_options' );
	}

	/** 
	* Store plugin name and version as options
	*
	* @since 1.0
	* @return void
	*/
	public function admin_init(){
		$plugin_data = get_plugin_data( __FILE__, false );
		update_option( 'qbw_smartestb_plugin_version', $plugin_data['Version'] );
		update_option( 'qbw_smartestb_plugin_name', $plugin_data['Name'] );
	}

	/** 
	* Add meta generator tag with plugin name and version to head
	*
	* @since 1.0
	* @return string meta element name=generator
	*/
	public function version(){
		echo '<meta name="generator" content="' . get_option( 'qbw_smartestb_plugin_name' ) . ' ' . get_option( 'qbw_smartestb_plugin_version' ) . '" />' . "\n";
	}

	/**  
	* Setup options panel
	*
	* @since 1.0
	*/
	public function option_setup(){
		//Update EMPTY options
		$smartestb_array = array();
		add_option('smartestb_options',$smartestb_array);
		$template = get_option('smartestb_template');
		$saved_options = get_option('smartestb_options');
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
							$smartestb_array[$c_id] = $c_std; 
						}
					} else {
						update_option($id,$std);
						$smartestb_array[$id] = $std;
					}
				}
				else { //So just store the old values over again.
					$smartestb_array[$id] = $db_option;
				}
			}
		}
		update_option('smartestb_options',$smartestb_array);
	}// end option_setup

	/** 
	*  add admin options page
	*
	* @since 1.0
	*/
	public function add_admin() {
	    global $query_string;
	    $title = __('Quick Business Website', 'smartestb');      
	    if ( isset($_REQUEST['page']) && $_REQUEST['page'] == 'smartestbthemes' ) {
			if (isset($_REQUEST['smartestb_save']) && 'reset' == $_REQUEST['smartestb_save']) {
	
				$options =  get_option('smartestb_template'); 
				$this->reset_options($options,'smartestbthemes');
				header("Location: admin.php?page=smartestbthemes&reset=true");
				die;
			}
	    }
	
		if(get_option('framework_smartestb_backend_icon')) { $icon = get_option('framework_smartestb_backend_icon'); } 
			else { 
				$icon = plugins_url( 'images/smartestb-icon.png' , __FILE__ );
				}
		
		$sto=add_menu_page( sprintf(__('%s Options', 'smartestb'), $title), $title, 'activate_plugins', 'smartestbthemes', array($this, 'options_page'), $icon, 45);
		add_action( 'admin_head-'. $sto, array($this, 'frame_load') );
		$this->add_admin_menu_separator(44);
	
	} // end add_admin

	/**
	 * Add link to plugin options to admin tool bar. Also, remove WordPress links from admin/tool bar, if enabled for branding.
	 * @since 1.0
	 */
	public function admin_bar() {
		$label =  get_option( 'qbw_smartestb_plugin_name' );
	    global $wp_admin_bar;
	    $wp_admin_bar->add_menu( array(
	        'parent' => 'appearance',
	        'id' => 'qbw-options',
	        'title' => $label. __(' Options', 'smartestb'),
	        'href' => admin_url( 'admin.php?page=smartestbthemes')
	    ) );
		if ( get_option('smartestb_remove_wplinks') == 'true' ) {
			$wp_admin_bar->remove_menu('wp-logo');
		}

	}// end admin_bar

	/**
	 * Reset options page
	 * @since 1.0
	 */

	public function reset_options($options,$page = ''){
	
		global $wpdb;
		$query_inner = '';
		$count = 0;
		
		$excludes = array( 'blogname' , 'blogdescription' );
		
		foreach($options as $option){
				
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
		
		//When Options page is reset - Add the smartestb_options option
		if($page == 'smartestbthemes'){
			$query_inner .= " OR option_name = 'smartestb_options'";
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
	    $options = get_option('smartestb_template');      
		$fDIR = plugins_url( '/', __FILE__ );?>
	<div class="wrap" id="smartestb_container">
	<div id="smartestb-popup-save" class="smartestb-save-popup"><div class="smartestb-save-save"><?php _e('Options Updated', 'smartestb'); ?></div></div>
	<div id="smartestb-popup-reset" class="smartestb-save-popup"><div class="smartestb-save-reset"><?php _e('Options Reset', 'smartestb'); ?></div></div>
	    <form action="" enctype="multipart/form-data" id="smartestbform">
	        <div id="header">
	           <div class="logo">
				<a href="http://smartestthemes.com" title="Smartest Themes">
			<?php echo apply_filters('smartestb_options_branding', '<img alt="Smartest Themes" src="'. $fDIR. 'images/st_logo_admin.png" />'); ?>
				</a>
	          </div>
	             <div class="theme-info">
					<span class="theme" style="margin-top:10px;"><?php _e('Quick Business Website', 'smartestb'); ?>
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
			<li id="smar-ui-icon"><a href="http://wordpress.org/support/view/plugin-reviews/quick-business-website" target="_blank" title="Rate This Plugin"><i class="ui-icon-star"></i>Rate This Plugin</a></li>
            <li class="right"><img style="display:none" src="<?php echo $fDIR; ?>images/loading-top.gif" class="ajax-loading-img ajax-loading-img-top" alt="Working..." />
	<input type="submit" value="<?php _e('Save All Changes', 'smartestb'); ?>" class="button submit-button" /></li>
				</ul> 
	<!--[if IE]>
	</div>
	<![endif]-->
			</div>
	        <div id="main">
		        <div id="smartestb-nav">
					<ul>
						<?php echo $return[1] ?>
						<li><a class="theme-support" title="Support" href="#smartestb-option-themesupport"><span class="smartestb-nav-icon"></span><?php _e('Plugin Support', 'smartestb'); ?></a></li>
						
					</ul>		
				</div>
				<div id="content">
		         <?php echo $return[0]; /* Settings */ ?>
		         <!-- ADD SUPPORT SECTION -->
		         <div class="group" id="smartestb-option-themesupport" style="display:block;">
		         <h2><?php _e('Plugin Support', 'smartestb'); ?></h2>
		         <div class="section support-section">
		         <p class="support-content"><?php _e('Stuck?  Need some help?  Found a bug?', 'smartestb'); ?></p>
		         </div>
		         <div class="support-divider"></div>
		         <div class="section support-section">
		         <div class="support-section-icon comments_blue_75"></div>
<!-- 		         <h4 class="support-section-title"><?php _e('Support Forum', 'smartestb'); ?></h4> -->
		         <p class="support-content"><?php _e('Get help or report a bug at the forum. There we focus on answering your questions and helping you to use the default functionality of this plugin.', 'smartestb'); ?></p>
	<div class="section support-section">
		         <a class="support-button" target="_blank" title="Support Forum" href="http://wordpress.org/support/plugin/quick-business-website"><?php _e('Go To Support Forum', 'smartestb'); echo ' &raquo;'; ?> </a>
		         </div>
		         <div class="clear"></div>
		         </div>
		         <div class="support-divider"></div>



<div class="section support-section">
		         <div class="support-section-icon info_75"></div>
<!-- 		         <h4 class="support-section-title"><?php _e('Instruction Guides', 'smartestb'); ?></h4> -->
		         <p class="support-content"><?php _e('The Instruction Guides give detailed instructions for certain tasks.', 'smartestb'); ?></p>
	<div class="section support-section">
		         <a class="support-button" target="_blank" title="Instruction Guides" href="http://smartestthemes.com/docs/category/quick-business-website---wordpress-plugin/"><?php _e('Go To Instruction Guides', 'smartestb'); echo ' &raquo;'; ?> </a>
		         </div>
		         <div class="clear"></div>
		         </div>
		         <div class="support-divider"></div>



		         </div><!-- END SUPPORT SECTION -->
		        </div>
		        <div class="clear"></div>
	        </div>
	        <!--[if IE]>
			<div class="ie">
			<![endif]-->
	        <div class="save_bar_top">
	        <img style="display:none" src="<?php echo $fDIR; ?>images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
	        <input type="submit" value="<?php _e('Save All Changes', 'smartestb'); ?>" class="button submit-button" />        
	        </form>
	     
	        <form action="<?php echo esc_html( $_SERVER['REQUEST_URI'] ) ?>" method="post" style="display:inline" id="smartestbform-reset">
	            <span class="submit-footer-reset">
	            <input name="reset" type="submit" value="<?php _e('Reset Options', 'smartestb'); ?>" class="button submit-button reset-button" onclick="return confirm(localized_label.reset);" />
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
		$fr = plugins_url( '/', __FILE__ );
		add_action('admin_head', 'smartestb_admin_head');
		wp_enqueue_script('jquery-ui-core');
		wp_register_script('jquery-input-mask', $fr. 'js/jquery.maskedinput-1.3.1.min.js', array( 'jquery' ));
		wp_enqueue_script('jquery-input-mask');
	
		function smartestb_admin_head() { 
			$fr = plugins_url( '/', __FILE__ );
			?>
			<link rel="stylesheet" type="text/css" href="<?php echo $fr; ?>admin-style.css" media="screen" />
			<script>jQuery(document).ready(function(){
				
				//JQUERY DATEPICKER
				jQuery('.smartestb-input-calendar').each(function (){
					jQuery('#' + jQuery(this).attr('id')).datepicker({showOn: 'button', buttonImage: '<?php echo $fr; ?>images/calendar.gif', buttonImageOnly: true});
				});
				
				//JQUERY TIME INPUT MASK
				jQuery('.smartestb-input-time').each(function (){
					jQuery('#' + jQuery(this).attr('id')).mask("99-9999999");
				});
				
				//Color Picker
				<?php $options = get_option('smartestb_template');
				
				foreach($options as $option){ 
				if($option['type'] == 'color'){//12.12
	
						$option_id = $option['id'];
						$color = get_option($option_id);
	
					?>
					 jQuery('#<?php echo $option_id; ?>_picker').children('div').css('backgroundColor', '<?php echo $color; ?>');    
					 jQuery('#<?php echo $option_id; ?>_picker').ColorPicker({
						color: '<?php echo $color; ?>',
						onShow: function (colpkr) {
							jQuery(colpkr).fadeIn(500);
							return false;
						},
						onHide: function (colpkr) {
							jQuery(colpkr).fadeOut(500);
							return false;
						},
						onChange: function (hsb, hex, rgb) {
							//jQuery(this).css('border','1px solid red');
							jQuery('#<?php echo $option_id; ?>_picker').children('div').css('backgroundColor', '#' + hex);
							jQuery('#<?php echo $option_id; ?>_picker').next('input').attr('value','#' + hex);
							
						}
					  });
				  <?php } } ?>
			 
	});
				</script>
	
	<?php	//AJAX Upload
	
			/**
			 * Set localized php vars for js
			 */
			$upl = __('Uploading', 'smartestb');
			$upi = __('Upload Image', 'smartestb');
			$okr = __('Click OK to reset back to default settings. All custom QBW plugin settings will be lost!', 'smartestb');
			 // deliver the vars to js
				?>
			<script>
				var localized_label = {
						uploading : "<?php echo $upl ?>",
						uploadimage : "<?php echo $upi ?>",
						reset : "<?php echo $okr ?>",
				}
			</script>
			
			<script type="text/javascript" src="<?php echo $fr; ?>js/ajaxupload.js"></script>
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
					
					jQuery('.smartestb-radio-img-img').click(function(){
						jQuery(this).parent().parent().find('.smartestb-radio-img-img').removeClass('smartestb-radio-img-selected');
						jQuery(this).addClass('smartestb-radio-img-selected');
						
					});
					jQuery('.smartestb-radio-img-label').hide();
					jQuery('.smartestb-radio-img-img').show();
					jQuery('.smartestb-radio-img-radio').hide();
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
			
				//AJAX Upload
				jQuery('.image_upload_button').each(function(){
				
				var clickedObject = jQuery(this);
				var clickedID = jQuery(this).attr('id');	
				new AjaxUpload(clickedID, {
					  action: '<?php echo admin_url("admin-ajax.php"); ?>',
					  name: clickedID, // File upload name
					  data: { // Additional data to send
							action: 'smartestb_ajax_post_action',
							type: 'upload',
							data: clickedID },
					  autoSubmit: true, // Submit file after selection
					  responseType: false,
					  onChange: function(file, extension){},
					  onSubmit: function(file, extension){
							clickedObject.text(localized_label.uploading); // change button text, when user selects file	
							this.disable(); // If you want to allow uploading only 1 file at time, you can disable upload button
							interval = window.setInterval(function(){
								var text = clickedObject.text();
								if (text.length < 13){	clickedObject.text(text + '.'); }
								else { clickedObject.text(localized_label.uploading); } 
							}, 200);
					  },
					  onComplete: function(file, response) {
					   
						window.clearInterval(interval);
						clickedObject.text(localized_label.uploadimage);
						this.enable(); // enable upload button
						
						// If there was an error
						if(response.search('Upload Error') > -1){
							var buildReturn = '<span class="upload-error">' + response + '</span>';
							jQuery(".upload-error").remove();
							clickedObject.parent().after(buildReturn);
						
						}
						else{
							var buildReturn = '<img class="hide smartestb-option-image" id="image_'+clickedID+'" src="'+response+'" alt="" />';
	
							jQuery(".upload-error").remove();
							jQuery("#image_" + clickedID).remove();	
							clickedObject.parent().after(buildReturn);
							jQuery('img#image_'+clickedID).fadeIn();
							clickedObject.next('span').fadeIn();
							clickedObject.parent().prev('input').val(response);
						}
					  }
					});
				
				});
				
				//AJAX Remove (clear option value)
				jQuery('.image_reset_button').click(function(){
				
						var clickedObject = jQuery(this);
						var clickedID = jQuery(this).attr('id');
						var theID = jQuery(this).attr('title');	
		
						var ajax_url = '<?php echo admin_url("admin-ajax.php"); ?>';
					
						var data = {
							action: 'smartestb_ajax_post_action',
							type: 'image_reset',
							data: theID
						};
						
						jQuery.post(ajax_url, data, function(response) {
							var image_to_remove = jQuery('#image_' + theID);
							var button_to_hide = jQuery('#reset_' + theID);
							image_to_remove.fadeOut(500,function(){ jQuery(this).remove(); });
							button_to_hide.fadeOut();
							clickedObject.parent().prev('input').val('');
		
						});
						
						return false; 
						
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
							<?php if(isset($_REQUEST['page']) && $_REQUEST['page'] == 'smartestbthemes'){ ?>
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
		if($save_type == 'upload'){
			
			$clickedID = $_POST['data']; // Acts as the name
			$filename = $_FILES[$clickedID];
	       	$filename['name'] = preg_replace('/[^a-zA-Z0-9._\-]/', '', $filename['name']); 
			
			$override['test_form'] = false;
			$override['action'] = 'wp_handle_upload';    
			$uploaded_file = wp_handle_upload($filename,$override);
			 
					$upload_tracking[] = $clickedID;
					update_option( $clickedID , $uploaded_file['url'] );
					
			 if(!empty($uploaded_file['error'])) {echo __('Upload Error: ', 'smartestb') . $uploaded_file['error']; }
			 else { echo $uploaded_file['url']; } // Is the Response
		}
		elseif($save_type == 'image_reset'){
				
				$id = $_POST['data']; // Acts as the name
				global $wpdb;
				$query = "DELETE FROM $wpdb->options WHERE option_name LIKE '$id'";
				$wpdb->query($query);
		
		}	
		elseif ($save_type == 'options') {
	
			$data = $_POST['data'];
			parse_str($data,$output);
	        	$options = get_option('smartestb_template');
			foreach($options as $option_array){
				$id = isset($option_array['id']) ? $option_array['id'] : '';
				$old_value = get_option($id);
				$new_value = '';
				
				if(isset($output[$id])){
					$new_value = $output[$option_array['id']];
				}
		
				if(isset($option_array['id'])) { // Non - Headings...
					
					//Import of prior saved options
					if($id == 'framework_smartestb_import_options'){
						
						//Decode and over write options.
						$new_import = $new_value;
						$new_import = unserialize($new_import);
						if(!empty($new_import)) {
							foreach($new_import as $id2 => $value2){
								if(is_serialized($value2)) {
									update_option($id2,unserialize($value2));
								} else {
									update_option($id2,$value2);
								}
							}
						}
						
					} else {
				
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
						elseif($new_value == '' && $type == 'checkbox'){ // Checkbox Save
							
							update_option($id,'false');
						}
						elseif ($new_value == 'true' && $type == 'checkbox'){ // Checkbox Save
							
							update_option($id,'true');
	
						}
						elseif($type == 'multicheck'){ // Multi Check Save
							
							$option_options = $option_array['options'];
							
							foreach ($option_options as $options_id => $options_value){
								
								$multicheck_id = $id . "_" . $options_id;
								
								if(!isset($output[$multicheck_id])){
								  update_option($multicheck_id,'false');
								}
								else{
								   update_option($multicheck_id,'true'); 
								}
							}
						} 
						elseif($type != 'upload_min'){
						
							update_option($id,stripslashes($new_value));
						}
					}
				}	
			}
		}
		
		
		if( $save_type == 'options'){
			/* Create, Encrypt and Update the Saved Settings */
			global $wpdb;
			$smartestb_options = array();
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
						$smartestb_array_option = $value;
						$temp_options = '';
						foreach($value as $v){
							if(isset($v))
								$temp_options .= $v . ',';
							
						}	
						$value = $temp_options;
						$smartestb_array[$name] = $smartestb_array_option;
					} else {
						$smartestb_array[$name] = $value;
					}
					
					$output .= '<li><strong>' . $name . '</strong> - ' . $value . '</li>';
			}
			$output .= "</ul>";
			
			update_option('smartestb_options',$smartestb_array);
			update_option('smartestb_settings_encode',$output);
			// this makes it finally flush, but only if you save twice. Isa
			flush_rewrite_rules();
		}
		die();
	}

	/** 
	 * Generate the options
	 * @since 1.0
	 */
	public function machine($options) {
	    $counter = 0;
		$menu = '';
		$output = '';
		foreach ($options as $value) {
		   
			$counter++;
			$val = '';
			//Start Heading
			 if ( $value['type'] != "heading" )
			 {
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
		if( !empty($value['std']) ) {
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
			case 'select2':
				$output .= '<select class="smartestb-input" name="'. $value['id'] .'" id="'. $value['id'] .'">';
				$select_value = get_option($value['id']);
				foreach ($value['options'] as $option => $name) {
					$selected = '';
					 if($select_value != '') {
						 if ( $select_value == $option) { $selected = ' selected="selected"';} 
				     } else {
						 if ( isset($value['std']) )
							 if ($value['std'] == $option) { $selected = ' selected="selected"'; }
					 }
					  
					 $output .= '<option'. $selected .' value="'.$option.'">';
					 $output .= $name;
					 $output .= '</option>';
				 
				 } 
				 $output .= '</select>';
			break;
			case 'calendar':
				$val = $value['std'];
				$std = get_option($value['id']);
				if ( $std != "") { $val = $std; }
	            $output .= '<input class="smartestb-input-calendar" type="text" name="'.$value['id'].'" id="'.$value['id'].'" value="'.$val.'">';
			break;
			case 'time':
				$val = $value['std'];
				$std = get_option($value['id']);
				if ( $std != "") { $val = $std; }
				$output .= '<input class="smartestb-input-time" name="'. $value['id'] .'" id="'. $value['id'] .'" type="text" value="'. $val .'" />';
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
			case "radio":
				 $select_value = get_option( $value['id']);
				 foreach ($value['options'] as $key => $option) 
				 { 
	
					 $checked = '';
					   if($select_value != '') {
							if ( $select_value == $key) { $checked = ' checked'; } 
					   } else {
						if ($value['std'] == $key) { $checked = ' checked'; }
					   }
					$output .= '<input class="smartestb-input smartestb-radio" type="radio" name="'. $value['id'] .'" value="'. $key .'" '. $checked .' />' . $option .'<br />';
				
				}
			break;
			case "radio2":
				$select_value = get_option( $value['id']);
				foreach ($value['options'] as $key => $option)
				{
				$checked = '';
				if($select_value != '') {
				if ( $select_value == $option[2]) { $checked = ' checked'; }
				} else {
				$std_radio2 = isset($value['std']) ? $value['std'] : '';
				if ($option[2] == $std_radio2 ) { $checked = ' checked'; }
				}
				$output .= '<input class="smartestb-input smartestb-radio" type="radio" name="'. $value['id'] .'" value="'. $option[2] .'" '. $checked .' />' . $option[0];
				// image
				$output .= '<img alt="demo" class="demoimg" src="' . $option[1] . '" />';
				$output .= '<br />';
				}
			break;
			case "checkbox": 
		if( !empty($value['std']) ) {
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
			case "multicheck":
				$std =  $value['std'];         
				foreach ($value['options'] as $key => $option) {
				$smartestb_key = $value['id'] . '_' . $key;
				$saved_std = get_option($smartestb_key);
				if(!empty($saved_std)) 
				{ 
					  if($saved_std == 'true'){
						 $checked = 'checked="checked"';  
					  } 
					  else{
						  $checked = '';     
					  }    
				} 
				elseif( $std == $key) {
				   $checked = 'checked="checked"';
				}
				else {
					$checked = '';                                                                                    }
				$output .= '<input type="checkbox" class="checkbox smartestb-input" name="'. $smartestb_key .'" id="'. $smartestb_key .'" value="true" '. $checked .' /><label for="'. $smartestb_key .'">'. $option .'</label><br />';
											
				}
			break;
			case "upload":
				$output .= $this->the_uploader($value['id'],$value['std'],null);
			break;
			case "upload_min":
				$output .= $this->the_uploader($value['id'],$value['std'],'min');
			break;
			case "color":
				$val = $value['std'];
				$stored  = get_option( $value['id'] );
				if ( $stored != "") { $val = $stored; }
				$output .= '<div id="' . $value['id'] . '_picker" class="colorSelector"><div></div></div>';
				$output .= '<input class="smartestb-color" name="'. $value['id'] .'" id="'. $value['id'] .'" type="text" value="'. $val .'" />';
			break;   
	
			case "images":
				$i = 0;
				$select_value = get_option( $value['id']);
					   
				foreach ($value['options'] as $key => $option) 
				 { 
				 $i++;
	
					 $checked = '';
					 $selected = '';
					   if($select_value != '') {
							if ( $select_value == $key) { $checked = ' checked'; $selected = 'smartestb-radio-img-selected'; } 
					    } else {
							if ($value['std'] == $key) { $checked = ' checked'; $selected = 'smartestb-radio-img-selected'; }
							elseif ($i == 1  && !isset($select_value)) { $checked = ' checked'; $selected = 'smartestb-radio-img-selected'; }
							elseif ($i == 1  && $value['std'] == '') { $checked = ' checked'; $selected = 'smartestb-radio-img-selected'; }
							else { $checked = ''; }
						}	
					
					$output .= '<span>';
					$output .= '<input type="radio" id="smartestb-radio-img-' . $value['id'] . $i . '" class="checkbox smartestb-radio-img-radio" value="'.$key.'" name="'. $value['id'].'" '.$checked.' />';
					$output .= '<div class="smartestb-radio-img-label">'. $key .'</div>';
					$output .= '<img src="'.$option.'" alt="" class="smartestb-radio-img-img '. $selected .'" onClick="document.getElementById(\'smartestb-radio-img-'. $value['id'] . $i.'\').checked = true;" />';
					$output .= '</span>';
					
				}
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
	 * Smartest Themes Uploader
	 * @since 1.0
	 */

	public function the_uploader($id,$std,$mod){
		$uploader = '';
	    $upload = get_option($id);
		
		if($mod != 'min') { 
				$val = $std;
	            if ( get_option( $id ) != "") { $val = get_option($id); }
	            $uploader .= '<input class="smartestb-input" name="'. $id .'" id="'. $id .'_upload" type="text" value="'. $val .'" />';
		}
		
		$uploader .= '<div class="upload_button_div"><span class="button image_upload_button" id="'.$id.'">'. __('Upload Image', 'smartestb'). '</span>';
		
		if(!empty($upload)) {$hide = '';} else { $hide = 'hide';}
		
		$uploader .= '<span class="button image_reset_button '. $hide.'" id="reset_'. $id .'" title="' . $id . '">'. __('Remove', 'smartestb'). '</span>';
		$uploader .='</div>' . "\n";
	    $uploader .= '<div class="clear"></div>' . "\n";
		if(!empty($upload)){
	    	$uploader .= '<a class="smartestb-uploaded-image" href="'. $upload . '">';
	    	$uploader .= '<img class="smartestb-option-image" id="image_'.$id.'" src="'.$upload.'" alt="" />';
	    	$uploader .= '</a>';
			}
		$uploader .= '<div class="clear"></div>' . "\n"; 
		return $uploader;
	} // end the_uploader

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
	}// end add_admin_menu_separator
	
	/** 
	 * Style the custom text logo on wp-login.php
	 * @since 1.0
	 */

	public function login_logo() {
			echo'<style type="text/css">.login h1 a {background-position: center top;text-indent: 0px;text-align:center; background-image:none;text-decoration:none;color:#000000;}</style>';
	}
	/** 
	 * Show the business name as link title on wp-login.php
	 * @since 1.0
	 */
	public function wp_login_title() {
		$bn = stripslashes_deep(esc_attr(get_option('smartestb_business_name')));
		return $bn;
	}// end login_logo
	
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
		$page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' LIMIT 1;");
		if ( $page_found ) :
			if ( ! $option_value )
				update_option( $option, $page_found );
			return;
		endif;
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
	 * Create about page, storing page id in variable, and Activate Smartest Reviews.
	 *	 
	 * @uses insert_post()
	 * @since 1.0
	 */
	public function after_setup() {
	
		$bn = stripslashes_deep(esc_attr(get_option('smartestb_business_name')));if(!$bn) {$bn = get_bloginfo('name'); }
		$atitle = sprintf(__('About %s','smartestb'), $bn);

		// if not disabled in options 
		if(get_option('smartestb_stop_about') == 'false') {
			$this->insert_post( 'page', esc_sql( _x('about', 'page_slug', 'smartestb') ), 'smartest_about_page_id', $atitle, '' );		}
		// Activate Smartest Reviews
		if (!class_exists('SMARTESTReviewsBusiness') && (get_option('smartestb_add_reviews') == 'true')) {
			include_once QUICKBUSINESSWEBSITE_PATH . 'modules/smartest-reviews/smartest-reviews.php';
		}
	
	} // end after_setup
	/* 
	 * Resize images dynamically using wp built in functions
	 * Victor Teixeira
	 * Modified by Isabel Castillo
	 *
	 * php 5.2+
	 *
	 * Example of use:
	 * $thumb = get_post_thumbnail_id(); 
	 * $image = vt_resize( $thumb, '', 140, 110, true );// or image url for 2nd param
	 * 
	 * echo $image['url']; ? >" width="< ? p h p  echo $image[width]; ? >" height=" < ? p h p echo $image[height]; ? >" />
	 *
	 * @param int $attach_id
	 * @param string $img_url
	 * @param int $width
	 * @param int $height
	 * @param bool $crop
	 * @return array
	 * @since 1.0
	 */
	public function vt_resize( $attach_id = null, $img_url = null, $width, $height, $crop = false ) {
	
		// this is an attachment, so we have the ID
		if ( $attach_id ) {
		
			$image_src = wp_get_attachment_image_src( $attach_id, 'full' );
			$file_path = get_attached_file( $attach_id );
		
		// this is not an attachment, let's use the image url
		} else if ( $img_url ) {
		
			$file_path = parse_url( $img_url );
				$file_path = rtrim( ABSPATH, '/' ).$file_path['path'];//isa use this path instead
				
				$orig_size = getimagesize( $file_path );
				
				$image_src[0] = $img_url;
				$image_src[1] = $orig_size[0];
				$image_src[2] = $orig_size[1];
	
		}
		
		$file_info = pathinfo( $file_path );
		$extension = '.'. $file_info['extension'];
	
		// the image path without the extension
		$no_ext_path = $file_info['dirname'].'/'.$file_info['filename'];
	
		$cropped_img_path = $no_ext_path.'-'.$width.'x'.$height.$extension;
	
		// checking if the file size is larger than the target size
		// if it is smaller or the same size, stop right here and return
		if ( $image_src[1] > $width || $image_src[2] > $height ) {
	
			// the file is larger, check if the resized version already exists (for $crop = true but will also work for $crop = false if the sizes match)
			if ( file_exists( $cropped_img_path ) ) {
	
				$cropped_img_url = str_replace( basename( $image_src[0] ), basename( $cropped_img_path ), $image_src[0] );
				
				$vt_image = array (
					'url' => $cropped_img_url,
					'width' => $width,
					'height' => $height
				);
				
				return $vt_image;
			}
			if ( $crop == false ) {
			
				// calculate the size proportionaly
				$proportional_size = wp_constrain_dimensions( $image_src[1], $image_src[2], $width, $height );
				$resized_img_path = $no_ext_path.'-'.$proportional_size[0].'x'.$proportional_size[1].$extension;			
				// checking if the file already exists
				if ( file_exists( $resized_img_path ) ) {
				
					$resized_img_url = str_replace( basename( $image_src[0] ), basename( $resized_img_path ), $image_src[0] );
	
					$vt_image = array (
						'url' => $resized_img_url,
						'width' => $proportional_size[0],
						'height' => $proportional_size[1]
					);
					
					return $vt_image;
				}
			}
			// no cache files - let's finally resize it
	
	$editor = wp_get_image_editor( $file_path );// replace image_resize
	if ( is_wp_error( $editor ) )
	    return $editor;
	$editor->set_quality( 100 );
	$resized = $editor->resize( $width, $height, $crop );
	$dest_file = $editor->generate_filename( NULL, NULL );
	$saved = $editor->save( $dest_file );
	if ( is_wp_error( $saved ) )
	    return $saved;
	$new_img_path=$dest_file;
	/* --- end new replacement for image_resize 3.11  --------------*/
	
			$new_img_size = getimagesize( $new_img_path );
			$new_img = str_replace( basename( $image_src[0] ), basename( $new_img_path ), $image_src[0] );
	
			// resized output
			$vt_image = array (
				'url' => $new_img,
				'width' => $new_img_size[0],
				'height' => $new_img_size[1]
			);
			
			return $vt_image;
		}
	
		// default output - without resizing
		$vt_image = array (
			'url' => $image_src[0],
			'width' => $image_src[1],
			'height' => $image_src[2]
		);
		
		return $vt_image;
	}// end vt_resize

	/** 
	 * Add CPTs conditionally, if enabled. Adds smartest_staff, smartest_services, smartest_news. Also, delete About page if disabled, delete Reviews page if disabled.
	 * @since 1.0
	 */
	
	public function create_business_cpts() {
		$staff = get_option('smartestb_show_staff');
		$news = get_option('smartestb_show_news');
		$services = get_option('smartestb_show_services');
		
				// if add staff enabled
				
				if( $staff == 'true'  ) { 
	
					//register cpt staff
			    	$args = array(
			        	'label' => __('Staff','smartestb'),
			        	'singular_label' => __('Staff','smartestb'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('staff', 'smartestb'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Staff','smartestb' ),
							'singular_name' => __( 'Staff','smartestb' ),
							'add_new' => __( 'Add New','smartestb' ),
							'add_new_item' => __( 'Add New Staff','smartestb' ),
							'all_items' => __( 'All Staff','smartestb' ),
							'edit' => __( 'Edit','smartestb' ),
							'edit_item' => __( 'Edit Staff','smartestb' ),
							'new_item' => __( 'New Staff','smartestb' ),
							'view' => __( 'View Staff','smartestb' ),
							'view_item' => __( 'View Staff','smartestb' ),
							'search_items' => __( 'Search Staff','smartestb' ),
							'not_found' => __( 'No staff found','smartestb' ),
							'not_found_in_trash' => __( 'No staff found in Trash','smartestb' ),
							'parent' => __( 'Parent Staff','smartestb' ),
						),
			        	'supports' => array('title','editor','thumbnail','excerpt'),
						'has_archive' => true,
	
			        );
	
		    	register_post_type( 'smartest_staff' , $args );
	
				}// end if show staff enabled
				//if show news enabled, create news cpt
				if($news == 'true') { 
					//register cpt news
			    	$args = array(
			        	'label' => __('Announcements','smartestb'),
			        	'singular_label' => __('Announcement','smartestb'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('news','smartestb'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Announcements','smartestb' ),
							'singular_name' => __( 'Announcement','smartestb' ),
							'add_new' => __( 'Add New','smartestb' ),
							'add_new_item' => __( 'Add New Announcement','smartestb' ),
							'all_items' => __( 'All Announcements','smartestb' ),
							'edit' => __( 'Edit','smartestb' ),
							'edit_item' => __( 'Edit Announcement','smartestb' ),
							'new_item' => __( 'New Announcement','smartestb' ),
							'view' => __( 'View Announcement','smartestb' ),
							'view_item' => __( 'View Announcement','smartestb' ),
							'search_items' => __( 'Search Announcements','smartestb' ),
							'not_found' => __( 'No announcement found','smartestb' ),
							'not_found_in_trash' => __( 'No announcements found in Trash','smartestb' ),
							'parent' => __( 'Parent Announcement','smartestb' ),
						),
			        	'supports' => array('title','editor','thumbnail'),
						'has_archive' => true
	
			        );
	
		    	register_post_type( 'smartest_news' , $args );
	
				}// end if show news enabled
	
				//if show services enabled, create services cpt
				if($services == 'true') { 
					//register cpt services
			    	$args = array(
			        	'label' => __('Services','smartestb'),
			        	'singular_label' => __('Service','smartestb'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array(
								'slug' => __('services','smartestb'),
								'with_front' => false,
	
						),
			        	'exclude_from_search' => false,
		        		'labels' => array(
							'name' => __( 'Services','smartestb' ),
							'singular_name' => __( 'Service','smartestb' ),
							'add_new' => __( 'Add New','smartestb' ),
							'all_items' => __( 'All Services','smartestb' ),
							'add_new_item' => __( 'Add New Service','smartestb' ),
							'edit' => __( 'Edit','smartestb' ),
							'edit_item' => __( 'Edit Service','smartestb' ),
							'new_item' => __( 'New Service','smartestb' ),
							'view' => __( 'View Services','smartestb' ),
							'view_item' => __( 'View Service','smartestb' ),
							'search_items' => __( 'Search Services','smartestb' ),
							'not_found' => __( 'No services found','smartestb' ),
							'not_found_in_trash' => __( 'No services found in Trash','smartestb' ),
							'parent' => __( 'Parent Service','smartestb' ),
						),
			        	'supports' => array('title','editor','thumbnail'),
						'has_archive' => true,
	
			        );
	
		    	register_post_type( 'smartest_services' , $args );
	
				}// end if show services enabled
	
		if(get_option('smartestb_stop_about') == 'true') {
			wp_delete_post(get_option('smartest_about_page_id'), true);
		}
		if(get_option('smartestb_add_reviews') == 'false') {
			wp_delete_post(get_option('smartest_reviews_page_id'), true);
		}

	} // end create_business_cpts
	

	/**
	 * Registers custom taxonomy for services
	 * @since 1.4
	 * @return void
	 */
	function set_taxonomies() {
		$category_labels = array(
			'name' => __( 'Categories', 'smartestb' ),
			'singular_name' =>__( 'Category', 'smartestb' ),
			'search_items' => __( 'Search Categories', 'smartestb' ),
			'all_items' => __( 'All Categories', 'smartestb' ),
			'parent_item' => __( 'Parent Category', 'smartestb' ),
			'parent_item_colon' => __( 'Parent Category:', 'smartestb' ),
			'edit_item' => __( 'Edit Category', 'smartestb' ),
			'update_item' => __( 'Update Category', 'smartestb' ),
			'add_new_item' => __( 'Add New Category', 'smartestb' ),
			'new_item_name' => __( 'New Category Name', 'smartestb' ),
			'menu_name' => __( 'Categories', 'smartestb' ),
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
	 * Adds Staff archives link menu item to wp_menu_nav.
	 * @since 1.0
	 */
	public function staff_menu_link($items, $args) {
			$newitems = $items;
			if( get_option('smartestb_show_staff') == 'true' ) {
			        $newitems .= '<li class="staff"><a title="'. __('Staff', 'smartestb'). '" href="'. get_post_type_archive_link( 'smartest_staff' ) .'">'. __('Staff', 'smartestb'). '</a></li>';
		    }
			return $newitems;
	}
	/**  
	 * Adds Services archives link menu item to wp_menu_nav.
	 * @since 1.0
	 */

	public function services_menu_link($items, $args) {
			$newitems = $items;
			if( get_option('smartestb_show_services') == 'true' ) {
			        $newitems .= '<li class="services"><a title="'. __('Services', 'smartestb'). '" href="'. get_post_type_archive_link( 'smartest_services' ) .'">'. __('Services', 'smartestb'). '</a>';

			// if service cat tax terms exist, do sub-menu
			$service_cats = get_terms('smartest_service_category');
			$count = count($service_cats);
			if ( $count > 0 ){
				$newitems .= '<ul class="sub-menu">';
				foreach ( $service_cats as $service_cat ) {
					$newitems .= '<li><a title="' . esc_attr( $service_cat->name ) . '" href="'. get_term_link( $service_cat ) .'">' . $service_cat->name . '</a></li>';	
				}
				$newitems .= '</ul>';
			}
			$newitems .= '</li>';
		    }
		    return $newitems;
	}
	/** 
	 * Adds News archives link menu item to wp_menu_nav.
	 * @since 1.0
	 */


	public function news_menu_link($items, $args) {
			$newitems = $items;
		    if( get_option('smartestb_show_news') == 'true' ) {
		        $newitems .= '<li class="news"><a title="'. __('News', 'smartestb'). '" href="'. get_post_type_archive_link( 'smartest_news' ) .'">'. __('News', 'smartestb'). '</a></li>';
			 }
		    return $newitems;
	}
	/** 
	 * Adds Staff archives link menu item to wp_page_menu.
	 * @since 1.0
	 */
	public function page_menu_staff( $menu ) {
		$newmenu = $menu;
		if( get_option('smartestb_show_staff') == 'true' ) {
			$newitems = '<li class="staff"><a title="'. __('Staff', 'smartestb') . '" href="'. get_post_type_archive_link( 'smartest_staff' ) .'">'. __('Staff', 'smartestb'). '</a></li>';
		    $newmenu = str_replace( '</ul></div>', $newitems . '</ul></div>', $newmenu );
		    }
	    return $newmenu;
	}

	/** 
	 * Adds Services archives link menu item to wp_page_menu.
	 * @since 1.0
	 */

	public function page_menu_services( $menu ) {
		$newmenu = $menu;
		if( get_option('smartestb_show_services') == 'true' ) {
			$newitems = '<li class="services"><a title="' . __('Services', 'smartestb') . '" href="'. get_post_type_archive_link( 'smartest_services' ) .'">'. __('Services', 'smartestb'). '</a>';
			// if service cat tax terms exist, do sub-menu
			$service_cats = get_terms('smartest_service_category');
			$count = count($service_cats);
			if ( $count > 0 ){
				$newitems .= '<ul class="sub-menu">';
				foreach ( $service_cats as $service_cat ) {
					$newitems .= '<li><a title="' . esc_attr( $service_cat->name ) . '" href="'. get_term_link( $service_cat ) .'">' . $service_cat->name . '</a></li>';	
				}
				$newitems .= '</ul>';
			}
			$newitems .= '</li>';
		    $newmenu = str_replace( '</ul></div>', $newitems . '</ul></div>', $newmenu );
	    }
	    return $newmenu;
	}
	/**  
	 * Adds News archives link menu item to wp_page_menu.
	 * @since 1.0
	 */

	public function page_menu_news( $menu ) {
		$newmenu = $menu;
	    if( get_option('smartestb_show_news') == 'true' ) {
	        $newitems = '<li id="testing" class="news"><a title="' . __('News', 'smartestb') . '" href="'. get_post_type_archive_link( 'smartest_news' ) .'">'. __('News', 'smartestb'). '</a></li>';
		    $newmenu = str_replace( '</ul></div>', $newitems . '</ul></div>', $newmenu );
		 }
	    return $newmenu;
	}

	/** 
	 * Custom metaboxes and fields for staff cpt: order number, occupational title & social links. For services and news: featured.
	 * @param  array $meta_boxes
	 * @return array
	 * @since 1.0
	 */
	public function metaboxes( array $meta_boxes ) {
		$prefix = '_smab_';
		$meta_boxes[] = array(
			'id'         => 'staff_details',
			'title'      => __('Details', 'smartestb'),
			'pages'      => array( 'smartest_staff', ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __( 'Job Title', 'smartestb' ),
					'desc' => __( 'The staff member\'s job title. Optional', 'smartestb' ),
					'id'   => $prefix . 'staff_job_title',
					'type' => 'text_medium',
				),
				array(
					'name' => __( 'Sort Order Number', 'smartestb' ),
					'desc' => __( 'Give this person a number to order them on the list on the staff page and in the staff widget. Number 1 appears 1st on the list, while greater numbers appear lower. Numbers do not have to be consecutive; for example, you could number them like, 10, 20, 35, 45, etc. This would help to leave room in between to insert new staff members later without having to change everyone\'s current number.', 'smartestb' ),
					'id'   => $prefix . 'staff-order-number',
					'type' => 'text',
					'std' => 9999
				),
				array(
					'name' => __('Facebook Profile ID', 'smartestb'),
					'desc' => __('The staff member\'s Facebook profile ID. Optional', 'smartestb'),
					'id'   => $prefix . 'staff_facebook',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Twitter Username', 'smartestb'),
					'desc' => __('The staff member\'s Twitter username. Optional', 'smartestb'),
					'id'   => $prefix . 'staff_twitter',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Google Plus Profile ID', 'smartestb'),
					'desc' => __('The staff member\'s Google Plus profile ID. Optional', 'smartestb'),
					'id'   => $prefix . 'staff_gplus',
					'type' => 'text_medium',
				),
				array(
					'name' => __('Linkedin Profile', 'smartestb'),
					'desc' => __('The part of the profile address after "www.linkedin.com/". Optional', 'smartestb'),
					'id'   => $prefix . 'staff_linkedin',
					'type' => 'text_medium',
				),
			)
		);
		$meta_boxes[] = array(
			'id'         => 'featured_svcs',
			'title'      => __('Featured Services', 'smartestb'),
			'pages'      => array( 'smartest_services', ), // Post type
			'context'    => 'side',
			'priority'   => 'default',//high, core, default, low
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __('Feature this?', 'smartestb'),
					'desc' => __('Check this box to feature this service in the list of featured services on the home page and in the Featured Services widget.', 'smartestb'),
					'id'   => $prefix . 'services_featured',
					'type' => 'checkbox',
				),
			)
		);
		$meta_boxes[] = array(
			'id'         => 'featured_news',
			'title'      => __('Featured News', 'smartestb'),
			'pages'      => array( 'smartest_news', ), // Post type
			'context'    => 'side',
			'priority'   => 'default',//high, core, default, low
			'show_names' => true,
			'fields'     => array(
				array(
					'name' => __('Feature this?', 'smartestb'),
					'desc' => __('Check this box to feature this announcement in the Featured Announcements widget.', 'smartestb'),
					'id'   => $prefix . 'news_featured',
					'type' => 'checkbox',
				),
			)
		);
		return $meta_boxes;
	} // end metaboxes()



	/** 
	 * Initialize the metabox class.
	 * @since 1.0
	 */
	public function initialize_cmb_meta_boxes() {
		if ( ! class_exists( 'cmb_Meta_Box' ) )
			require_once QUICKBUSINESSWEBSITE_PATH . 'lib/metabox/init.php';
	}

	/** 
	 * Do 'Enter Staff member's name here' instead of 'Enter title here' for smartest_staff custom post type
	 * @since 1.0
	 */
	public function change_enter_title( $title ){
		$screen = get_current_screen();
		if  ( 'smartest_staff' == $screen->post_type ) {
			$title = __('Enter staff member\'s name here', 'smartestb');} return $title;
	}

	/** 
	 * register widgets
	 * @since 1.0
	 */
	public function register_widgets() {
	
		if( get_option('smartestb_show_news') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/announcements.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-announcements.php';
			register_widget('SmartestAnnouncements'); register_widget('SmartestFeaturedAnnounce');
		}
		if( get_option('smartestb_show_services') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/all-services.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-services.php';
			register_widget('SmartestServices'); register_widget('SmartestFeaturedServices'); 
		}
		if( get_option('smartestb_show_staff') == 'true'  ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/staff.php';
			register_widget('SmartestStaff'); 
		}
	
	} // end register_widgets
	/** 
	 * insert custom scripts from options
	 * @since 1.0
	 */
	public function add_customscripts() {
		$gascript =  get_option('smartestb_script_analytics');
		$oscripts =  get_option('smartestb_scripts_head');
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
			'title' => __('Name', 'smartestb'),
			'jobtitle' => __('Job Title', 'smartestb'),
			'date' => __('Date', 'smartestb')
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
			'title' => __('Title', 'smartestb'),
			'taxonomy-smartest_service_category' => __('Categories', 'smartestb'),
			'featureds' => __('Featured', 'smartestb'),
			'date' => __('Date', 'smartestb')
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
					_e('Featured', 'smartestb');
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
			'title' => __('Title', 'smartestb'),
			'featuredn' => __('Featured', 'smartestb'),
			'date' => __('Date', 'smartestb')
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
					_e('Featured', 'smartestb');
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
		if ( (get_option('smartestb_admin_footer') != '') &&  (get_option('smartestb_remove_adminfooter') == 'false')) {
			echo get_option('smartestb_admin_footer');
		} elseif ( get_option('smartestb_remove_adminfooter') == 'true' ) {
			echo '';
		} else {
			echo 'Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.';
		}
	}
	/** 
	 * Register stylesheet and responsive script
	 * @since 1.0
	 */
	public function framework_enq() {
		wp_register_style( 'frame', plugins_url( 'css/frame.css' , __FILE__ ) );
		wp_enqueue_style( 'frame' );
		wp_register_script( 'responsive', plugins_url( 'js/responsive.js' , __FILE__ ), array('jquery') );
		// not on reviews page
		if( !is_page( get_option('smartest_reviews_page_id') ) ) {	
			wp_enqueue_script('responsive');
		}
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
			$staffcontent .= '<ul id="qbw-staff-socials">';
			if (get_post_meta($post->ID, '_smab_staff_twitter', true)) {
					$staffcontent .= '<li><a class="item-1" href="https://twitter.com/' . get_post_meta($post->ID, '_smab_staff_twitter', true) . '" title="'. __('Twitter', 'smartestb') . '"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_gplus', true)) {
					$staffcontent .= '<li><a class="item-2" href="https://plus.google.com/' . get_post_meta($post->ID, '_smab_staff_gplus', true) . '" title="'. __('Google Plus', 'smartestb') . '" rel="author"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_facebook', true)) {
					$staffcontent .= '<li><a class="item-3" href="https://facebook.com/' . get_post_meta($post->ID, '_smab_staff_facebook', true) . '" title="'. __('Facebook', 'smartestb') . '"></a></li>';
			} if (get_post_meta($post->ID, '_smab_staff_linkedin', true)) {
					$staffcontent .= '<li><a class="item-4" href="http://www.linkedin.com/' . get_post_meta($post->ID, '_smab_staff_linkedin', true) . '" title="'. __('LinkedIn', 'smartestb') . '"></a></li>';
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

			global $smartestb_options;

			$contactcontent = '<div class="qbw-one-half">' . $content . '</div><div id="qbw-contact-info" class="qbw-one-half"  itemscope itemtype="http://schema.org/LocalBusiness">';

			// social box
			$contactcontent .= '<ul id="qbw-staff-socials">';
			if ( get_option('smartestb_business_twitter') ) {
				$contactcontent .= '<li><a class="item-1" href="https://twitter.com/' . get_option('smartestb_business_twitter') . '" title="'. __('Twitter', 'smartestb') . '"></a></li>';
			} 
			if ( get_option('smartestb_business_gplus') ) {
				$contactcontent .= '<li><a class="item-2" href="https://plus.google.com/' . get_option('smartestb_business_gplus') . '" title="'. __('Google Plus', 'smartestb') . '" rel="publisher"></a></li>';
			} 
			if ( get_option('smartestb_business_facebook') ) {
				$contactcontent .= '<li><a class="item-3" href="https://facebook.com/' . get_option('smartestb_business_facebook') . '" title="'. __('Facebook', 'smartestb') . '"></a></li>';
			}
			if ( get_option('smartestb_business_youtube') ) {
				$contactcontent .= '<li><a class="youtube" href="https://youtube.com/user/' . get_option('smartestb_business_youtube') . '" title="'. __('Youtube', 'smartestb') . '"></a></li>';
			}
			if ( get_option('smartestb_business_socialurl1') ) {
				
				$contactcontent .= '<li><a class="item-add" target="_blank" href="'. get_option('smartestb_business_socialurl1') . '" title="' . __( 'Connect', 'smartestb' ) . '">' . get_option('smartestb_business_sociallabel1') . '</a></li>';
			} 
			if ( get_option('smartestb_business_socialurl2') ) {
				$contactcontent .= '<li><a class="item-add" target="_blank" href="'. get_option('smartestb_business_socialurl2') . '" title="' . __( 'Connect', 'smartestb' ) . '">' . get_option('smartestb_business_sociallabel2') . '</a></li>';
			} 
			$contactcontent .= '</ul><span itemprop="name">' . get_option('smartestb_business_name') . '</span><br />';
			if (get_option('smartestb_hours')) { 
				$contactcontent .= '<div id="qbw-contact-hours"><strong>Business Hours: </strong><br />' . wpautop(get_option('smartestb_hours')) . '</div>';
			} 
			if ( get_option('smartestb_google_map') ) {
				$contactcontent .= '<div id="qbw-goomap">'. get_option('smartestb_google_map'). '</div>';
			}
			if(get_option('smartestb_address_street')) { // do addy box
				$contactcontent .= '<p id="qbw-addy-box" itemprop="address" itemscope itemtype="http://schema.org/PostalAddress"><span itemprop="streetAddress">' . get_option('smartestb_address_street') . '</span>&nbsp;';

			}
			if (get_option('smartestb_address_suite') != '') {
				$contactcontent .= ' ' . get_option('smartestb_address_suite') . '&nbsp;';
			}
			if (get_option('smartestb_address_city') != '') {
				$contactcontent .='<br /><span itemprop="addressLocality">' . get_option('smartestb_address_city') . '</span>';
			}
			if ( (get_option('smartestb_address_city') != '') && (get_option('smartestb_address_state') != '') ) {
				$contactcontent .= ', ';
			}
			if (get_option('smartestb_address_state') != '') {
				$contactcontent .='<span itemprop="addressRegion">' . get_option('smartestb_address_state') . '</span>&nbsp;';
				}
			if (get_option('smartestb_address_zip') != '') {
				$contactcontent .=' <span class="postal-code" itemprop="postalCode">' . get_option('smartestb_address_zip') . '</span>&nbsp;';
			}
			if (get_option('smartestb_address_country') != '') {
				$contactcontent .='<br /><span itemprop="addressCountry">' . get_option('smartestb_address_country') . '</span>&nbsp;';
			}
			if(get_option('smartestb_address_street')) {
				$contactcontent .= '</p>'; // close #qbw-addy-box
			} // end addy-box
			if ( get_option('smartestb_phone_number') || get_option('smartestb_fax_numb') || ( get_option('smartestb_show_contactemail') == 'true' ) ) {
				$contactcontent .= '<p>';
			
				if ( get_option('smartestb_phone_number') ) {
					$contactcontent .= '' . __('Telephone:', 'smartestb') . ' <span itemprop="telephone">'. get_option('smartestb_phone_number') . '</span>';
				}
				if ( get_option('smartestb_fax_numb') ) {
					$contactcontent .= '<br />' . __('FAX:', 'smartestb') . ' <span itemprop="faxNumber">' . get_option('smartestb_fax_numb') . '</span>';
				
				} 
				
				if ( get_option('smartestb_show_contactemail') == 'true' ) {
					$contactcontent .= '<br />' . __('Email:', 'smartestb') . ' <a href="mailto:' . get_bloginfo('admin_email') . '"><span itemprop="email">' . get_bloginfo('admin_email') . '</span></a><br />';
				}
	

				$contactcontent .= '</p>';
			}

			$contactcontent .= '</div>';// close #qbw-contact-info.qbw-one-half 2nd column 
			return $contactcontent;
			
		} else {
			// regular content
			return $content;
		}

	}// end contact_content_filter

	/**
	 * Add About page content to about page
	 *
	 * @uses is_page()
	 * @uses wpautop()
	 * @since 1.0
	 */
	public function about_content_filter( $content ) {
	
		if( is_page( get_option('smartest_about_page_id') ) ) {

			global $smartestb_options;

			$aboutcontent = '<div id="qbw-about">';
			
			if ( get_option('smartestb_about_picture') ) { 
				$img_url = get_option('smartestb_about_picture');
				$aboutcontent .= '<figure id="qbw-about-pic"><a href="' . $img_url . '" title="' . the_title_attribute('echo=0') . '" ><img src="' . $img_url . '" alt="' . the_title_attribute('echo=0') . '" /></a></figure>';
			}
			if ( get_option('smartestb_about_page') ) {
				$text = stripslashes_deep(get_option('smartestb_about_page'));
				$aboutcontent .= wpautop($text); 
			}
			$aboutcontent .= '</div>' . $content ;
			return $aboutcontent;
		} else {
			// regular content
			return $content;
		}

	}// end about_content_filter

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
}
}
if ( defined('THEME_FRAMEWORK') && ( THEME_FRAMEWORK == 'Smartest Business Framework' ) ) {
		$msg =  '<strong>' . __( 'You cannot activate Quick Business Website', 'smartestb') . '</strong> ' . __( 'plugin when using Smartest Themes because they clash. But Smartest Themes have everything the Quick Business Website plugin has, and more. QBW plugin will not be activated! To use the plugin, please change your Theme, first.', 'smartestb');
		wp_die($msg, 'Plugin Clashes With Theme', array(back_link => true));
} else {
 	register_deactivation_hook(__FILE__, array('Quick_Business_Website', 'deactivate')); 
	register_activation_hook(__FILE__, array('Quick_Business_Website', 'activate'));
	$Quick_Business_Website = new Quick_Business_Website();

	/**
 	 * Include Contact form with both jquery client-side and php server-side validation
	 * @since 1.0
	 */
	include QUICKBUSINESSWEBSITE_PATH . 'modules/contact/contact.php';
	/**
 	 * Include the MCE table buttons library to add table-editing buttons to the WP editor
	 * @since 1.0
	 */
	include QUICKBUSINESSWEBSITE_PATH . 'lib/mce-table/mce_table_buttons.php';
}
