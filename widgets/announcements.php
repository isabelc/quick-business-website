<?php
/**
 * Adds Announcements widget
 * 
 * @package		Quick Business Website
 * @extends		WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SmartestAnnouncements extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_announcements', // Base ID
			__('QBW Announcements', 'quick-business-website'), // Name
			array( 'description' => __( 'Display the latest Announcements.', 'quick-business-website' ), ) // Args
		);
		add_action('wp_enqueue_scripts', array($this, 'ann_css'));
	}

	/**
	 * Register stylesheet.
	 */
	public function ann_css() {
			wp_register_style('san', 
			plugins_url('/sa.css', __FILE__));
	} 

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		wp_enqueue_style('san');
		$title = apply_filters('widget_title', $instance['title']);
		$number = $instance['number'];
		echo $before_widget;
		if ( ! empty( $title ) )
			echo '<h3 class="widget-title">'. $title . '</h3>';
		 $args = array(
			'posts_per_page' => $number,
			'post_type' => 'smartest_news',
			'order' => 'DESC' );
		$sbfnews = new WP_Query( $args );
		if ( $sbfnews->have_posts() ) {
			echo '<ul>';
			while ( $sbfnews->have_posts() ) {
				$sbfnews->the_post();
				echo '<li><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a><br />';
				$datetime = get_the_date('Y-m-d');
				printf ( '<time datetime="%s">%s</time>', $datetime, get_the_date() );
				echo '</li>';	
			} // endwhile
			echo '</ul>';
			$li = '<a href="'.get_post_type_archive_link( 'smartest_news' ).'">'. __('All Announcements', 'quick-business-website'). '</a>';
			?> <p><?php printf(__( '%s', 'quick-business-website'), $li); ?></p>

		<?php } else { ?>
				<p><?php _e('Coming soon.', 'quick-business-website'); ?></p>		
		<?php }
		wp_reset_postdata();
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
		$instance['number'] = strip_tags( $new_instance['number'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'Latest News', 'quick-business-website' );
		}

		if ( isset( $instance[ 'number' ] ) ) {
			$number = $instance[ 'number' ];
		}
		else {
			$number = 3;
		}

/* Default Widget Settings */
    	?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'quick-business-website' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'How many recent announcements to show:', 'quick-business-website' ); ?></label> 
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $number ); ?>" />
	</p>

		<?php 
	}

}
?>