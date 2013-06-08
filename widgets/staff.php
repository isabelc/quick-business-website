<?php
/**
 * Adds Staff widget to list all staff members
 *
 * @package		Quick Business Website
 * @extends		WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SmartestStaff extends WP_Widget {
	/**
	 * Register widget.
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_staff_list', // Base ID
			__('QBW Staff List', 'smartestb'), // Name
			array( 'description' => __( 'Display the full list of Staff members.', 'smartestb' ), )
		);
		add_action('wp_enqueue_scripts', array($this, 'smar_staff_css'));
	}
	/**
	 * Register stylesheet.
	 */
	public function smar_staff_css() {
			wp_register_style('sst',
			plugins_url('/sst.css', __FILE__));
			wp_enqueue_style('sst');		
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
		
		/* loop through staff */

		query_posts( array( 'posts_per_page' => -1, 'post_type' => 'smartest_staff', 'orderby' => 'title', 'order' => 'ASC' ) );
		if (have_posts()) : 
			while (have_posts()) : the_post(); 

				echo '<div id="sstwrap">';

				if ( has_post_thumbnail() ) {

				$thumb = get_post_thumbnail_id(); 
				global $Quick_Business_Website;
				$smallimage = $Quick_Business_Website->vt_resize( $thumb, '', 48, 72, false); ?>
				<figure id="ssfig">

<?php			echo '<a href="'.get_permalink().'" title="'.get_the_title().'">'; ?>

<img src="<?php echo $smallimage['url']; ?>" width="<?php echo $smallimage['width']; ?>" />
</a>

</figure>
<?php } ?>

	<div id="sstcontent">
<?php echo '<h5><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></h5></div></div>';
			endwhile;
		endif; 
		wp_reset_query();

		echo $after_widget;

	}// end widget

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags($new_instance['title'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 * @see WP_Widget::form()
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Staff', 'smartestb' );
		}
	
    	?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'smartestb' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php 
	}

}
?>