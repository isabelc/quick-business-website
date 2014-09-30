<?php
/**
 * Adds Staff widget to list all staff members
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
	 		'smartest_staff_list',
			__('QBW Staff List', 'quick-business-website'),
			array( 'description' => __( 'Display the full list of Staff members.', 'quick-business-website' ), )
		);
		add_action('wp_enqueue_scripts', array($this, 'smar_staff_css'));
	}
	/**
	 * Register stylesheet.
	 */
	public function smar_staff_css() {
			wp_register_style('sst',
			plugins_url('/sst.css', __FILE__));
	} 

	/**
	 * Front-end display of widget.
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		wp_enqueue_style('sst');
	
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Staff', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );

		echo $args['before_widget'];
		if ( $title )
			echo '<h3 class="widget-title">'. $title . '</h3>';
                $query_args = array(
                    'posts_per_page' => -1,
                    'post_type' => 'smartest_staff',
                    'orderby' => 'meta_value_num',
                    'meta_key' => '_smab_staff-order-number',
                    'order' => 'ASC' );
                $qbwstaff = new WP_Query( $query_args );
                if ( $qbwstaff->have_posts() ) {
                    while ( $qbwstaff->have_posts() ) {
                        $qbwstaff->the_post();
					echo '<div class="sstwrap">';
		
			if ( has_post_thumbnail() ) {
				$thumb = get_post_thumbnail_id(); 
				global $Quick_Business_Website;
				$smallimage = $Quick_Business_Website->vt_resize( $thumb, '', 48, 72, false); ?>
				<figure class="ssfig">

<?php			echo '<a href="'.get_permalink().'" title="'. the_title_attribute( 'echo=0' ) .'">'; ?>

<img src="<?php echo $smallimage['url']; ?>" alt="<?php the_title_attribute(); ?>" width="<?php echo $smallimage['width']; ?>" height="<?php echo $smallimage['height']; ?>" />
</a>

</figure>
<?php } ?>

	<div class="sstcontent">
<?php echo '<h5><a href="'.get_permalink().'" title="'. the_title_attribute( 'echo=0' ) .'">'.get_the_title().'</a></h5></div></div>';

                    } // endwhile;
                   

                } // end if have posts   
                else {
					echo '<h2>' . __('No posts found!</h2>', 'quick-business-website') . '</h2>';
                }
                wp_reset_postdata();

		echo $args['after_widget'];

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
			$title = __( 'Staff', 'quick-business-website' );
		}
	
    	?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'quick-business-website' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<?php 
	}

}
?>