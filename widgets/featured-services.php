<?php
/**
 * Adds Featured Services widget to show selected services
 *
 * @package		Quick Business Website
 * @extends		WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SmartestFeaturedServices extends WP_Widget {

	/**
	 * Register widget.
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_featured_services', // Base ID
			__('QBW Featured Services', 'smartestb'), // Name
			array( 'description' => __( 'Display selected featured services.', 'smartestb' ), ) // Args
		);
		add_action('wp_enqueue_scripts', array($this, 'featsvcs_css'));
	}

	/**
	 * Register stylesheet.
	 */
	public function featsvcs_css() {
			wp_register_style('sfs',
			plugins_url('/sfs.css', __FILE__));
			wp_enqueue_style('sfs');		
	} 


	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {


		extract( $args );
		
		// these are our widget options
		$title = apply_filters('widget_title', $instance['title']);

		echo $before_widget;
		if ( ! empty( $title ) )
			echo '<h3 class="widget-title">'. $title . '</h3>';
		
		/* loop through announcements */

		$args = array(
			'post_type' => 'smartest_services',
			'meta_query' => array(
				array (
				'key' => '_smab_services_featured',
				'value'=> 'on'
				)
			)
		);
		$sbffs = new WP_Query( $args );
		if ( $sbffs->have_posts() ) {
			while ( $sbffs->have_posts() ) {
				$sbffs->the_post();
				echo '<div id="sfswrap">';
				if ( has_post_thumbnail() ) {
					echo '<figure id="sfsfig"><a href="'.get_permalink().'" title="'.get_the_title().'">';
					$thumb = get_post_thumbnail_id(); 
					global $Quick_Business_Website;
					$smallimage = $Quick_Business_Website->vt_resize( $thumb, '', 152, 96, true); ?>
					<img src="<?php echo $smallimage['url']; ?>" width="<?php echo $smallimage['width']; ?>" />
<?php
				echo '</a></figure>';
				}
						echo '<div id="sfscontent">';
							echo '<h4><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></h4>';
							echo get_the_excerpt();
						echo '</div>';
				echo '</div>';	
			} // endwhile
		} else {
				$li = '<a href="'.get_post_type_archive_link( 'smartest_services' ).'">'. __('Services', 'smartestb'). '</a>';
				?>
				<p><?php printf(__( 'Coming soon. See all %s.', 'smartestb'), $li); ?></p>		
		<?php } // endif
		wp_reset_postdata();
		echo $after_widget;

	}// end widget

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Featured Services', 'smartestb' );
		}
		
    	?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'smartestb' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
}?>