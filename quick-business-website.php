<?php
/*
Plugin Name: Quick Business Website
Plugin URI: https://isabelcastillo.com/free-plugins/quick-business-website
Description: Business website to showcase your services, staff, announcements, a working contact form, and reviews.
Version: 2.0.1.alpha2
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
		add_action( 'admin_init', array( $this, 'upgrade_options' ) );
		add_action( 'admin_init', array( $this, 'activation_redirect' ) );
		add_action( 'admin_init', array( $this, 'create_pages' ) );
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
		add_filter( 'manage_edit-smartest_staff_columns', array( $this, 'smar_manage_edit_staff_columns') );
		add_action( 'manage_smartest_staff_posts_custom_column', array( $this, 'smar_manage_staff_columns' ), 10, 2 );
		add_filter( 'manage_edit-smartest_services_columns', array( $this, 'smar_manage_edit_services_columns') );
		add_action( 'manage_smartest_services_posts_custom_column', array( $this, 'smar_manage_services_columns' ), 10, 2 );
		add_filter( 'manage_edit-smartest_news_columns', array( $this, 'smar_manage_edit_news_columns') );
		add_action( 'manage_smartest_news_posts_custom_column', array( $this, 'smar_manage_news_columns' ), 10, 2 );
		add_filter( 'admin_footer_text', array( $this, 'remove_footer_admin') ); 
		add_action( 'wp_before_admin_bar_render', array( $this, 'admin_bar') ); 
		add_action( 'wp_enqueue_scripts', array( $this, 'framework_enq') ); 
		add_filter( 'the_content',  array( $this, 'staff_meta_content_filter' ) );
		add_filter( 'the_content',  array( $this, 'contact_content_filter' ), 50 );
		add_filter( 'parse_query', array( $this, 'sort_staff' ) );
		if ( get_option( 'qbw_enable_service_sort') == 'true' ) { 
			add_filter( 'parse_query', array( $this, 'sort_services' ) );
		}
		add_action( 'qbw_settings_checkbox_save', array( $this, 'set_services_sort_order' ), 10, 2 );
		add_filter( 'qbw_sanitize_setting', array( $this, 'sanitize_setting' ), 10, 2 );
    }

	/** 
	* Only upon plugin activation, setup options and soft flush rewrite rules for custom post types.
	*
	* @since 1.0
	*/
	public static function activate() {
		self::option_setup();
		add_option( 'qbw_do_activation_redirect', true );
		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );// soft flush
	}

	/** 
	* display settings link on plugin page
	*
	* @since 1.0
	* @return void
	*/
	function settings_link( $actions, $file ) {
		$qbw_path = plugin_basename(__FILE__);
		if ( false !== strpos( $file, $qbw_path ) ) {
			$actions['settings'] = '<a href="' . esc_url( admin_url( 'admin.php?page=quickbusinesswebsite' ) ) . '">' . __( 'Settings', 'quick-business-website' ) . '</a>';
		}
		return $actions; 
	}
	
	/**
	 * Redirect to QBW settings page upon plugin activation
	 */
	public function activation_redirect() {
		if ( get_option( 'qbw_do_activation_redirect', false ) ) {
			delete_option( 'qbw_do_activation_redirect' );
			wp_safe_redirect( admin_url( 'admin.php?page=quickbusinesswebsite' ) );
			exit;
		}
	}

	/**
	 * Create Staff and Services pages if enabled in settings.
	 * @uses insert_post()
	 * @since 2.0
	 */
	public function create_pages() {
		if ( get_option( 'qbw_show_staff' ) == 'true' ) {
			$this->insert_post( esc_sql( _x('staff', 'page_slug', 'quick-business-website') ), 'qbw_staff_page_id', __( 'Staff', 'quick-business-website' ), '[qbw_staff]' );
		}

		if ( get_option( 'qbw_show_services' ) == 'true' ) {
			$this->insert_post( esc_sql( _x('services', 'page_slug', 'quick-business-website') ), 'qbw_services_page_id', __( 'Services', 'quick-business-website' ), '[qbw_services]' );
		}

	}

	/**
	* Include plugin options
	*
	* @since 1.0
	* @return void
	*/
	public function load() {
		include_once QUICKBUSINESSWEBSITE_PATH . 'inc/options.php';
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
	* Setup default options. This is run only on activation.
	*
	* @since 1.0
	*/
	public static function option_setup() {
		$qbw_array = array();
		// Set default options
		include_once QUICKBUSINESSWEBSITE_PATH . 'inc/options.php';
		qbw_options();
		$template = get_option( 'qbw_template' );
		foreach ( $template as $option ) {
			if ( $option['type'] != 'heading' && $option['type'] != 'info' ) {
				$id = isset( $option['id'] ) ? $option['id'] : '';
				$std = isset( $option['std'] ) ? $option['std'] : '';
				$db_option = get_option( $id );
				// if option is empty, set the default value
				if ( empty( $db_option ) ) {
					// Only if we have any defaults
					if ( '' != $std ) {
						$std = apply_filters( 'qbw_sanitize_setting', $std, $option );
						update_option( $id, $std );
						$qbw_array[ $id ] = $std;
					}

				} else {
					// sanitize old values for good measure
					if ( ! empty ( $id ) ) {
						$db_option = apply_filters( 'qbw_sanitize_setting', $db_option, $option );
						$qbw_array[ $id ] = $db_option;
					}
				}
			}
		} // end foreach option

		update_option( 'qbw_options', $qbw_array );
	}

	/** 
	* add admin options page
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
	
		// Register metabox style. Will be loaded only on our admin page
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
	public function reset_options( $options, $page = '' ) {
		if ( 'quickbusinesswebsite' != $page ) {
			return;
		}
		$qbw_array = array();
		foreach ( $options as $option ) {
			if ( $option['type'] != 'heading' && $option['type'] != 'info' ) {
				$id = isset( $option['id'] ) ? $option['id'] : '';
				$std = isset( $option['std'] ) ? $option['std'] : '';

				if ( '' != $std ) { // Save any defaults
					$std = apply_filters( 'qbw_sanitize_setting', $std, $option );
					update_option( $id, $std );
					$qbw_array[ $id ] = $std;
				} else { // Delete the rest
					delete_option( $id );
					$qbw_array[ $id ] = '';
				}

			}
		} // end foreach option

		global $wp_rewrite;
		$wp_rewrite->flush_rules( false );// soft flush
	}

	/** 
	 * The callback to display the options page
	 *
	 * @since 1.0
	 */
	public function options_page() {
	    $options = get_option( 'qbw_template'); ?>
	<div class="wrap" id="smartestb_container">
	<div id="smartestb-popup-save" class="smartestb-save-popup"><div class="smartestb-save-save"><?php _e('Options Updated', 'quick-business-website'); ?></div></div>
	<div id="smartestb-popup-reset" class="smartestb-save-popup"><div class="smartestb-save-reset"><?php _e('Options Reset', 'quick-business-website'); ?></div></div>
	    <form action="" enctype="multipart/form-data" id="smartestbform">
	        <div id="header">
	           <div class="logo">
				<?php if ( $custom_logo_id = get_theme_mod( 'custom_logo' ) ) {
					echo wp_get_attachment_image( $custom_logo_id, 'full' );
				} elseif ( $site_icon = get_site_icon_url( 32 ) ) {
					echo '<img src="' . esc_url( $site_icon ) . '" width="32" height="32" alt="site icon" />';
				} ?>
	          </div>
				<div class="clear"></div>
			</div>
	        <?php $return = $this->machine( $options ); ?>
			<div id="support-links">
			<ul>
			<li class="smar-ui-icon"><a href="https://isabelcastillo.com/free-plugins/quick-business-website#docs-subheading-doc" target="_blank" rel="nofollow" class="support-button"><div class="dashicons dashicons-book-alt"></div> <?php _e( 'Documentation', 'quick-business-website' ); ?></a></li>

			<li class="smar-ui-icon"><a href="https://wordpress.org/support/plugin/quick-business-website/reviews/" target="_blank"><div class="dashicons dashicons-star-filled"></div> <?php _e( 'Feedback', 'quick-business-website' ); ?></a></li>

			<li class="right"><img style="display:none" src="<?php echo QUICKBUSINESSWEBSITE_URL; ?>images/loading-top.gif" class="ajax-loading-img ajax-loading-img-top" alt="Working..." />
				<input type="submit" value="<?php _e('Save All Changes', 'quick-business-website'); ?>" class="button submit-button" /></li>
			</ul>
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
	        <div class="save_bar_top">
	        <img style="display:none" src="<?php echo QUICKBUSINESSWEBSITE_URL; ?>images/loading-bottom.gif" class="ajax-loading-img ajax-loading-img-bottom" alt="Working..." />
	        <input type="submit" value="<?php _e('Save All Changes', 'quick-business-website'); ?>" class="button submit-button" />        
	        </form>
	     
	        <form action="<?php echo esc_attr( $_SERVER['REQUEST_URI'] ) ?>" method="post" style="display:inline" id="smartestbform-reset">
	            <span class="submit-footer-reset">
	            <input name="reset" type="submit" value="<?php _e( 'Reset Options', 'quick-business-website' ); ?>" class="button submit-button reset-button" onclick="return confirm( localized_label.reset );" /><input type="hidden" name="smartestb_save" value="reset" /> 
	            </span>
	        </form>
	       
	        </div>
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
	
		function qbw_admin_head() {
			/**
			 * Localized string for js
			 */
			$okr = __('Click OK to reset back to default settings. All custom QBW plugin settings will be lost!', 'quick-business-website');
			?>
			<script type="text/javascript">
				var localized_label = {
					reset : "<?php echo $okr ?>",
				}			
				jQuery(document).ready(function(){
	
					jQuery('.group').hide();
					jQuery('.group:first').fadeIn();
					
					jQuery('#smartestb-nav li:first').addClass('current');
					jQuery('#smartestb-nav li a').click(function(evt){
					
							jQuery('#smartestb-nav li').removeClass('current');
							jQuery(this).parent().addClass('current');
							
							var clicked_group = jQuery(this).attr('href');
			 
							jQuery('.group').hide();
							
								jQuery(clicked_group).fadeIn();
			
							evt.preventDefault();
							
					});
					
					// Don't let the options panel be shorter than the tabs menu.					
					var ul = jQuery( '#smartestb-nav ul' );
					jQuery( '#smartestb_container #content' ).css( 'min-height', ul.height() );

					if ('<?php if(isset($_REQUEST['reset'])) { echo $_REQUEST['reset'];} else { echo 'false';} ?>' == 'true'){
						
						var reset_popup = jQuery('#smartestb-popup-reset');
						reset_popup.fadeIn();
						window.setTimeout(function(){
							   reset_popup.fadeOut();                        
							}, 2000);
						
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
				jQuery('#smartestbform').submit(function() {
					
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
	 * Sanitize settings before saving
	 * @param string $value The new value to be saved
	 * @param array $option Array of option details including id and type
	 */
	public function sanitize_setting( $value = '', $option ) {
		if ( '' == $value ) {
			return $value;
		}

		if ( 'text' == $option['type'] ) {
			$value = ( 'qbw_sbfc_email' == $option['id'] ) ?
					sanitize_email( $value ) :
					sanitize_text_field( $value );

		} elseif ( 'textarea' == $option['type'] ) {

			// No tags allowed on these ids
 			if ( in_array( $option['id'], array( 'qbw_sbfc_error', 'qbw_hours' ) ) ) {
 				$value = implode( "\n", array_map( 'sanitize_text_field', explode( "\n", $value ) ) );
 			}

			// For some ids, only allow limited tags
 			elseif ( in_array( $option['id'], array( 'qbw_admin_footer', 'qbw_sbfc_success' ) ) ) {
 				$value = qbw_kses( $value );
 			}

			// For some ids, allow also images
 			elseif ( in_array( $option['id'], array( 'qbw_sbfc_preform', 'qbw_sbfc_appform', 'qbw_sbfc_append' ) ) ) {
 				$value = qbw_kses( $value, true );
 			}

		} // end textarea

		return $value;

	}

	/**
	 * Make sure all services have a sort order when the sort setting is enabled.
	 * @param string $id The ID of the setting being saved
	 * @param string $value The setting value
	 * @since 2.0
	 */
	public function set_services_sort_order( $id, $value ) {
		// Is the services sort setting being enabled?
		if ( 'qbw_enable_service_sort' == $id && 'true' == $value ) {
			$args = array( 'post_type' => 'smartest_services', 'posts_per_page' => -1 );
			$all_services = get_posts( $args );
			foreach ( $all_services as $service ) {
				$sort_order_value_check = get_post_meta( $service->ID, '_smab_service-order-number', true );
				// if sort order value is not set, assign a default value of 99999
				if ( '' == $sort_order_value_check ) {
					update_post_meta( $service->ID, '_smab_service-order-number', 99999 );
				}
			}
			wp_reset_postdata();
		}
	}

	/**
	 * Ajax handler to save settings.
	 * @since 1.0
	 */
	public function ajax_callback() {
		global $wpdb;
		$save_type = $_POST['type'];
		
		if ( 'options' == $save_type ) {
	
			$data = $_POST['data'];
			parse_str( $data, $output );
	        $options = get_option( 'qbw_template' );
			foreach ( $options as $option_array ) {
				$id = isset( $option_array['id'] ) ? $option_array['id'] : '';
				$old_value = get_option( $id );
				$new_value = '';
				
				if ( isset( $output[ $id ] ) ) {
					$new_value = $output[ $option_array['id'] ];
				}
		
				if ( isset( $option_array['id'] ) ) { // Non - Headings...
					
					if ( 'checkbox' == $option_array['type'] ) { // Checkbox Save
						if ( 'true' == $new_value ) {
							update_option( $id, 'true' );
							do_action( 'qbw_settings_checkbox_save', $id, $new_value );
						} else {
							update_option( $id, 'false' );
						}
					}
					else {
						$new_value = apply_filters( 'qbw_sanitize_setting', $new_value, $option_array );
						update_option( $id, $new_value );
					}
					
				}	
			}
			/* Update the options array setting (qbw_options) */
			$query_inner = '';
			$count = 0;
			$select_prepare_values = array();
	
			foreach ( $options as $option ) {
				
				if ( isset( $option['id'] ) ) { 
					$count++;
					$option_id = $option['id'];
					
					if ( $count > 1 ) {
						$query_inner .= ' OR ';
					}
					
					$query_inner .= 'option_name = %s';
					$select_prepare_values[] = $option_id;
				}
				
			}
			
			$query = "SELECT * FROM $wpdb->options WHERE $query_inner";
			$results = $wpdb->get_results( $wpdb->prepare( $query, $select_prepare_values ) );
			
			foreach ( $results as $result ) {
					$name = $result->option_name;
					$value = $result->option_value;
					
					if ( is_serialized( $value ) ) {
						$qbw_array[ $name ] = unserialize( $value );// array
					} else {
						$qbw_array[ $name ] = $value;
					}
			}
			
			update_option( 'qbw_options', $qbw_array );
			global $wp_rewrite;
			$wp_rewrite->flush_rules( false );// soft flush

		}
		die();
	}

	/** 
	 * Generate the HTML for the options and the options menu
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
			 	$class = '';
			 	if ( isset( $value['class'] ) ) {
			 		$class = $value['class'];
			 	}
				$output .= '<div class="section section-' . esc_attr( $value['type'] ) . ' ' . $class . '">';
				if ( ! empty( $value['name'] ) ) {
					$output .= '<h3 class="heading">'. esc_html( $value['name'] ) .'</h3>';
				}
				$output .= '<div class="option"><div class="controls">';
			} 
			 //End Heading
			$select_value = '';                                   
			switch ( $value['type'] ) {
			
			case 'text':
				if ( ! empty( $value['std'] ) ) {
					$val = esc_attr( $value['std'] );
				}
				$std = esc_attr(get_option($value['id']));
				if ( $std != "") {
					$val = $std;
				}
				$output .= '<input class="smartestb-input" name="'. esc_attr( $value['id'] ) .'" id="' . esc_attr( $value['id'] ) .'" type="'. esc_attr( $value['type'] ) .'" value="'. stripslashes( $val ) .'" />';
			break;
			
			case 'select':
	
				$output .= '<select class="smartestb-input" name="'. esc_attr( $value['id'] ) .'" id="' . esc_attr( $value['id'] ) .'">';
			
				$select_value = get_option($value['id']);
				 
				foreach ($value['options'] as $option) {
					
					$selected = '';
					
					 if ( $select_value != '' ) {
						 if ( $select_value == $option) {
						 	$selected = ' selected="selected"';
						 } 
				     } else {
						if ( isset($value['std']) ) {
							 if ($value['std'] == $option) {
							 	$selected = ' selected="selected"';
							 }
						}
					 }
					  
					 $output .= '<option'. $selected .'>';
					 $output .= esc_html( $option );
					 $output .= '</option>';
				 
				 } 
				 $output .= '</select>';
				
			break;
			case 'textarea':
				$cols = '8';
				$ta_value = '';
				if ( isset( $value['std'] ) ) {
					$ta_value = $value['std'];
					if(isset($value['options'])){
						$ta_options = $value['options'];
						if ( isset( $ta_options['cols'] ) ) {
							$cols = $ta_options['cols'];
						} else {
							$cols = '8';
						}
					}
					
				}
					$std = esc_attr( get_option( $value['id'] ) );
					if ( $std != "" ) {
						$ta_value = esc_attr( $std );
					}
					$output .= '<textarea class="smartestb-input" name="' . esc_attr( $value['id'] ) .'" id="'. esc_attr( $value['id'] ) .'" cols="'. $cols .'" rows="8">'.stripslashes($ta_value).'</textarea>';
			break;
			case 'checkbox': 
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
				$output .= '<input type="checkbox" class="checkbox smartestb-input" name="' . esc_attr( $value['id'] ) . '" id="' . esc_attr( $value['id'] ) .'" value="true" '. $checked .' />';
	
			break;
			case "info":
				$default = $value['std'];
				$output .= $default;
			break;
			case "heading":
				if ( $counter >= 2 ) {
				   $output .= '</div>';
				}
				$jquery_click_hook = preg_replace('#[^A-Za-z0-9]#', '', strtolower($value['name']) );
				$jquery_click_hook = "smartestb-option-" . $jquery_click_hook;
				$menu .= '<li><a ';
				if ( !empty( $value['class'] ) ) {
					$menu .= 'class="'.  esc_attr( $value['class'] ) .'" ';
				}
				$menu .= 'title="'. esc_attr( $value['name'] ) .'" href="#'. esc_attr( $jquery_click_hook ) . '"><span class="smartestb-nav-icon"></span>'. esc_html( $value['name'] ) . '</a></li>';
				$output .= '<div class="group" id="' . esc_attr( $jquery_click_hook ) . '"><h2>' . esc_html( $value['name'] ) . '</h2>';
			break;                                  
			} 
			
			if ( $value['type'] != "heading" ) {
				if ( $value['type'] != "checkbox" ) { 
					$output .= '<br/>';
				}
				
				$explain_value = isset( $value['desc'] ) ? $value['desc'] : '';

				$output .= '</div><div class="explain">' . $explain_value . '</div>';
				$output .= '<div class="clear"> </div></div></div>';
			}
		   
		}
	    $output .= '</div>';
	    return array( $output, $menu );
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
	 * Get the business name as an attribute. To be used as link title attribute on wp-login.php
	 * @since 1.0
	 */
	public function wp_login_title() {
		return esc_attr( qbw_get_business_name() );
	}
	
	/** 
	 * Create a page of the page type
	 *
	 * @param mixed $slug Slug for the new page
	 * @param mixed $option Option name to store the page's ID
	 * @param string $page_title (default: '') Title for the new page
	 * @param string $page_content (default: '') Content for the new page
	 * @param int $post_parent (default: 0) Parent for the new page
	 * @since 1.0
	 */
	
	public function insert_post( $slug, $option, $page_title = '', $page_content = '', $post_parent = 0 ) {
		$option_value = get_option( $option );

		// Don't do anything if post exists.
		if ( $option_value > 0 && get_post( $option_value ) ) {
			return;
		}
		$page_data = array(
	        'post_status' 		=> 'publish',
	        'post_type' 		=> 'page',
	        'post_author' 		=> 1,
	        'post_name' 		=> $slug,
	        'post_title' 		=> $page_title,
	        'post_content' 		=> $page_content,
	        'post_parent' 		=> $post_parent,
	        'comment_status' 	=> 'closed'
	    );
	    $page_id = wp_insert_post( $page_data );
	
	    update_option( $option, $page_id );
	
	}
	/** 
	 * Activate Reviews.
	 *	 
	 * @since 1.0
	 */
	public function after_setup() {
		if ( ! class_exists( 'QBW_Reviews' ) && ( get_option( 'qbw_add_reviews' ) == 'true' ) ) {
			include_once QUICKBUSINESSWEBSITE_PATH . 'modules/reviews/reviews.php';
		}
	}

	/** 
	 * Creates our custom post types, if enabled. Also, deletes our custom pages for things that are disabled.
	 *
	 * @since 1.0
	 */
	public function create_business_cpts() {
		$staff = get_option( 'qbw_show_staff');
		$news = get_option( 'qbw_show_news');
		$services = get_option( 'qbw_show_services');

		// if show news enabled, create news cpt
		if ( $news == 'true') {
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
				'menu_icon' => 'dashicons-megaphone'
	        );
	
		   	register_post_type( 'smartest_news', $args );
	
		}
	
		//if show services enabled, create services cpt
		if ( $services == 'true' ) { 
			    	$args = array(
			        	'label' => __('Services','quick-business-website'),
			        	'singular_label' => __('Service','quick-business-website'),
			        	'public' => true,
			        	'show_ui' => true,
			        	'capability_type' => 'post',
			        	'hierarchical' => false,
			        	'rewrite' => array( 'with_front' => false, 'slug' => 'services' ),
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
	    	register_post_type( 'smartest_services', $args );
		} else {
		
			// Services are disabled so delete the Services page
			wp_delete_post( get_option( 'qbw_services_page_id' ), true);

		}

		// if add staff enabled
		if ( $staff == 'true'  ) {
	    	$args = array(
	        	'label' => __('Staff','quick-business-website'),
	        	'singular_label' => __('Staff','quick-business-website'),
	        	'public' => true,
	        	'show_ui' => true,
	        	'capability_type' => 'post',
	        	'hierarchical' => false,
	        	'rewrite' => array( 'with_front' => false, 'slug' => 'staff' ),
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
	
		   	register_post_type( 'smartest_staff', $args );
	
		} else {

			// Staff is disabled so delete the Staff page
			wp_delete_post( get_option( 'qbw_staff_page_id' ), true);

		}

		// If Reviews are disabled, delete the page
		if ( get_option( 'qbw_add_reviews' ) == 'false' ) {
			wp_delete_post( get_option( 'qbw_reviews_page_id' ), true);
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
			'pages'      => array( 'smartest_staff' ), // Post type
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
			'pages'      => array( 'smartest_services' ),// Post type
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
	if ( get_option( 'qbw_enable_service_sort') == 'true'  ) {
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
			'pages'      => array( 'smartest_news' ),
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
			$title = __('Enter staff member\'s name here', 'quick-business-website');
		}
		return $title;
	}

	/** 
	 * register widgets
	 * @since 1.0
	 */
	public function register_widgets() {
		if ( get_option( 'qbw_show_news') == 'true' ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/announcements.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-announcements.php';
			register_widget('SmartestAnnouncements'); register_widget('SmartestFeaturedAnnounce');
		}
		if ( get_option( 'qbw_show_services') == 'true' ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/all-services.php';
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/featured-services.php';
			register_widget('SmartestServices'); register_widget('SmartestFeaturedServices'); 
		}
		if ( get_option( 'qbw_show_staff') == 'true' ) { 
			include QUICKBUSINESSWEBSITE_PATH . 'widgets/staff.php';
			register_widget('SmartestStaff'); 
		}
	
	}
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
					 echo esc_html( $jobtitle );
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
		$custom_footer = get_option( 'qbw_admin_footer' );
		$remove_footer = get_option( 'qbw_remove_adminfooter');

		if ( $custom_footer && ( 'false' == $remove_footer ) ) {
			echo qbw_kses( $custom_footer );
		} elseif ( 'true' == $remove_footer ) {
			echo '';
		} else {
			echo 'Thank you for creating with <a href="https://wordpress.org/">WordPress</a>.';
		}
	}
	/** 
	 * Register front-end stylesheet
	 * @since 1.0
	 */
	public function framework_enq() {
		wp_register_style( 'qbw', QUICKBUSINESSWEBSITE_URL . 'css/qbw.css' );
		wp_enqueue_style( 'qbw' );
		wp_enqueue_style('font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.css');
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
	    	$meta = qbw_get_staff_meta();
	    	return $meta . $content;
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
		if ( ! is_page( get_option( 'qbw_contact_page_id' ) ) ) {
			return $content;
		}

		$contactcontent = '<div id="qbw-col-wrap"><div class="qbw-one-half">' . $content . '</div><div id="qbw-contact-info" class="qbw-one-half">';
		// social box
		$contactcontent .= '<ul id="qbw-staff-socials">';
		if ( get_option( 'qbw_old_social_icons' ) == 'false') {
			$twit = 'fa fa-twitter-square';
			$goog = 'fa fa-google-plus-square';
			$face = 'fa fa-facebook-square';
			$yout = 'fa fa-youtube-square';
		} else {
			$twit = 'item-1';
			$goog = 'item-2';
			$face = 'item-3';
			$yout = 'youtube';
		}
		$link = 'fa fa-linkedin-square';
		$inst = 'fa fa-instagram';

		$keys = array( 'qbw_business_twitter',
			'qbw_business_gplus',
			'qbw_business_instagram',
			'qbw_business_linkedin',
			'qbw_business_facebook',
			'qbw_business_youtube',
			'qbw_business_socialurl1',
			'qbw_business_sociallabel1',
			'qbw_business_socialurl2',
			'qbw_business_sociallabel2',
			'qbw_business_name',
			'qbw_address_street',
			'qbw_address_suite',
			'qbw_address_city',
			'qbw_address_state',
			'qbw_address_zip',
			'qbw_address_country',
			'qbw_phone_number',
			'qbw_fax_numb',
			'qbw_hours',
			'qbw_phone_number',
			'qbw_fax_numb',
			'qbw_show_contactemail',
		);

		$options = get_option( 'qbw_options' );

		// Escape urls
		foreach ( $keys as $key ) {
			if ( in_array( $key, array( 'qbw_business_socialurl1', 'qbw_business_socialurl2' ) ) ) {
				${$key} = isset( $options[ $key ] ) ?
						esc_url( $options[ $key ] ) :
						'';
			} else {
				${$key} = isset( $options[ $key ] ) ?
						stripslashes( $options[ $key ] ) :
						'';
			}
		}

		if ( $qbw_business_twitter ) {
			$uri = 'https://twitter.com/' . $qbw_business_twitter;
			$contactcontent .= '<li><a class="' . $twit . '" href="' . esc_url( $uri ) . '" title="'. __('Twitter', 'quick-business-website') . '"></a></li>';
		}
		if ( $qbw_business_gplus ) {
			$uri = 'https://plus.google.com/' . $qbw_business_gplus;
			$contactcontent .= '<li><a class="' . $goog . '" href="' . esc_url( $uri ) . '" title="'. __('Google Plus', 'quick-business-website') . '" rel="publisher"></a></li>';
		} 
		if ( $qbw_business_facebook ) {
			$uri = 'https://facebook.com/' . $qbw_business_facebook;
			$contactcontent .= '<li><a class="' . $face . '" href="' . esc_url( $uri ) . '" title="'. __('Facebook', 'quick-business-website') . '"></a></li>';
		}
		if ( $qbw_business_instagram ) {
			$uri = 'http://www.instagram.com/' . $qbw_business_instagram;
			$contactcontent .= '<li><a class="' . $inst . '" href="' . esc_url( $uri ) . '" title="'. __('Instagram', 'quick-business-website') . '"></a></li>';
		}

		if ( $qbw_business_linkedin ) {
			$uri = 'http://www.linkedin.com/' . $qbw_business_linkedin;
			$contactcontent .= '<li><a class="' . $link . '" href="' . esc_url( $uri ) . '" title="'. __('Instagram', 'quick-business-website') . '"></a></li>';
		}

		if ( $qbw_business_youtube ) {
			$uri = 'https://youtube.com/user/' . $qbw_business_youtube;
			$contactcontent .= '<li><a class="' . $yout. '" href="' . esc_url( $uri ) . '" title="'. __('Youtube', 'quick-business-website') . '"></a></li>';
		}
		if ( $qbw_business_socialurl1 ) {
			$contactcontent .= '<li><a class="item-add" target="_blank" href="'. $qbw_business_socialurl1 . '" title="' . __( 'Connect', 'quick-business-website' ) . '">' . $qbw_business_sociallabel1 . '</a></li>';
		} 
		if ( $qbw_business_socialurl2 ) {
			$contactcontent .= '<li><a class="item-add" target="_blank" href="'. $qbw_business_socialurl2 . '" title="' . __( 'Connect', 'quick-business-website' ) . '">' . $qbw_business_sociallabel2 . '</a></li>';
		} 
		$contactcontent .= '</ul><strong><span>' . $qbw_business_name . '</span></strong><br />';

		if ( $qbw_address_street ) {
			$contactcontent .= '<p id="qbw-addy-box">' . $qbw_address_street . '&nbsp;';
		}
		if ( $qbw_address_suite ) {
			$contactcontent .= ' ' . $qbw_address_suite . '&nbsp;';
		}
		if ( $qbw_address_city ) {
			$contactcontent .= '<br />' . $qbw_address_city;
		}
		if ( $qbw_address_city && $qbw_address_state ) {
			$contactcontent .= ', ';
		}
		if ( $qbw_address_state ) {
			$contactcontent .= $qbw_address_state . '&nbsp;';
		}
		if ( $qbw_address_zip ) {
			$contactcontent .= ' <span class="postal-code">' . $qbw_address_zip . '</span>&nbsp;';
		}
		if ( $qbw_address_country ) {
			$contactcontent .= '<br /><span class="qbw-country">' . $qbw_address_country . '</span>&nbsp;';
		}
		if ( $qbw_address_street ) {
			$contactcontent .= '</p>'; // close #qbw-addy-box
		}		

		if ( $qbw_hours ) {
			$contactcontent .= '<div id="qbw-contact-hours"><strong>Business Hours: </strong><br />' . wpautop( $qbw_hours ) . '</div>';
		} 

		if ( $qbw_phone_number || $qbw_fax_numb || ( 'true' == $qbw_show_contactemail ) ) {
			$contactcontent .= '<p>';
			if ( $qbw_phone_number ) {
				$contactcontent .= '' . __('Telephone:', 'quick-business-website') . ' <span class="qbw-phone">'. $qbw_phone_number . '</span>';
			}
			if ( $qbw_fax_numb ) {
				$contactcontent .= '<br />' . __('FAX:', 'quick-business-website') . ' <span class="qbw-fax">' . $qbw_fax_numb . '</span>';
			
			} 
			if ( 'true' == $qbw_show_contactemail ) {
				$email = antispambot( esc_html( get_bloginfo( 'admin_email' ) ) );
				$contactcontent .= '<br />' . __('Email:', 'quick-business-website') . ' <a href="mailto:' . $email . '">' . $email . '</a><br />';

			}
			$contactcontent .= '</p>';
		}

		$contactcontent .= '</div></div>';

		return $contactcontent;

	}// end contact_content_filter

	/**
	 * Sort staff archive by staff order number key
	 *
	 * @uses is_post_type_archive()
	 * @since 1.3.2
	 */

	public function sort_staff( $query ) {
		if ( ! is_admin() && $query->is_main_query() && isset( $query->query_vars['meta_key'] ) && is_post_type_archive('smartest_staff') ) {
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
	function sort_services( $query ) {
		if ( is_admin() ) {
			return $query;
		}

		if ( ! $query->is_main_query() ) {
			return $query;
		}

		if ( isset( $query->query_vars['meta_key'] ) &&
			( is_post_type_archive('smartest_services') || is_tax( 'smartest_service_category' ) )
		) {
			$query->query_vars['orderby'] = 'meta_value_num';
			$query->query_vars['meta_key'] = '_smab_service-order-number';
			$query->query_vars['order'] = 'ASC';
		}
		return $query;
	}

	/**
	 * Upgrade options for version 2.0
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
					$prepend = '<div id="qbw-about"><figure id="qbw-about-pic"><a href="' . esc_url( $img_url ) . '"><img src="' . esc_url( $img_url ) . '" alt="' . the_title_attribute('echo=0') . '" /></a></figure></div>';

						$about_page = array(
							'ID' => $about_page_id,
							'post_content' => $prepend . $content,
						);
						// Update the page
						wp_update_post( $about_page );
				}
			}

			/************************************************************
			*
			* For backwards compatibility, update the Contact From shortcode
			*
			************************************************************/

			// if our Contact page exists and is not disabled
			if ( ( $contact_page_id = get_option( 'qbw_contact_page_id') ) && get_option( 'qbw_stop_contact' ) != 'true' ) {

				$content = get_post_field( 'post_content', $contact_page_id );

				// Update the shortcode
				$updated_content = str_replace( '[smartest_themes_contact_form]', '[qbw_contact_form]', $content );

				$contact_page = array(
					'ID' => $contact_page_id,
					'post_content' => $updated_content
				);
				// Update the page
				wp_update_post( $contact_page );
			}

			// Flush rewrite rules once since the Staff and Services custom post type archives changed
			flush_rewrite_rules();

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
 	 * Include Helper functions
	 * @since 2.0
	 */
	include QUICKBUSINESSWEBSITE_PATH . 'inc/helper-functions.php';
	/**
 	 * Include Shortcodes
	 * @since 2.0
	 */	
	include QUICKBUSINESSWEBSITE_PATH . 'inc/shortcodes.php';

	/**
 	 * Include Contact form with both jQuery client-side and PHP server-side validation
	 * @since 1.0
	 */
	include QUICKBUSINESSWEBSITE_PATH . 'modules/contact/contact.php';
}
