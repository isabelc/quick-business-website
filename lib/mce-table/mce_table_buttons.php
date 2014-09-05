<?php
/*
 * Adds lightweight MCE Table Buttons to WordPress WYSIWYG editor
 * Author: Isabel Castillo (@isabelphp / isabelcastillo.com)
 * Version: 0.9
*/
class Smartest_MCE_Table_Buttons {
	private static $instance = null;

	public static function get_instance() {
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'content_save_pre', array( $this, 'content_save_pre'), 100 );
		add_action( 'admin_footer', array( $this, 'admin_footer' ), 100 );
	}
	
	public function admin_init() {
		add_filter( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
		add_filter( 'mce_buttons_3', array( $this, 'mce_buttons_3' ) );
	}
	
	public function mce_external_plugins( $plugin_array ) {
//		$plugin_array['table'] = get_bloginfo('template_url').'/smartest-business-framework/lib/mce-table/table/editor_plugin.js';//@new @isa
		$plugin_array['table'] =  plugins_url( '/table/editor_plugin.js', __FILE__ );//@new @isa
   		return $plugin_array;
	}
	
	public function mce_buttons_3( $buttons ) {
		array_push( $buttons, 'tablecontrols' );
   		return $buttons;
	}
	
	public function admin_footer() {
		if ( ! wp_script_is( 'editor' ) )
			return;		
	?>
	<script type="text/javascript">
	jQuery(window).load(function(){ 
		jQuery('.mceToolbarRow2').each(function(){
			if(!jQuery(this).is(':visible')) jQuery(this).siblings('.mceToolbarRow3').hide();
		});
		jQuery('.mce_wp_adv').click(function(){ 
			var toolbar3 = jQuery(this).closest('table').siblings('.mceToolbarRow3');
			if ( jQuery(this).hasClass('mceButtonActive') ) toolbar3.show();
			else toolbar3.hide(); 
		});
		
	});
	</script>
	<?php
	}
	
	public function content_save_pre( $content ) {
		if ( substr( $content, -8 ) == '</table>' )
			$content = $content . "\n<br />";
		
		return $content;
	}
}
$mce_table_buttons = Smartest_MCE_Table_Buttons::get_instance();