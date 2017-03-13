<?php
/**
 * Adds Featured Announcements widget to show selected announcements @todo update all widget css filenames. and classnames!
 *
 * @package		Quick Business Website
 */

class SmartestFeaturedAnnounce extends WP_Widget {
	/**
	 * Register widget
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_featured_announce',
			__('QBW Featured Announcements', 'quick-business-website'),
			array( 'description' => __( 'Display selected featured announcements.', 'quick-business-website' ), )
		);
		add_action('wp_enqueue_scripts', array($this, 'featnews_css'));
	}
	/**
	 * Register stylesheet.
	 */
	public function featnews_css() {
			wp_register_style('sfa', 
			plugins_url('/sfa.css', __FILE__));
	} 

	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		wp_enqueue_style('sfa');
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Featured News', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );
		
		echo $args['before_widget'];
		if ( $title )
			echo '<h3 class="widget-title">' . $title . '</h3>';
		$query_args = array(
			'post_type' => 'smartest_news',
			'meta_query' => array(
				array (
					'key' => '_smab_news_featured',
					'value'=> 'on'
				)
			)
		);
		$sbffa = new WP_Query( $query_args );
		if ( $sbffa->have_posts() ) {
			while ( $sbffa->have_posts() ) {
				$sbffa->the_post();
				echo '<div class="sfawrap">';
				if ( has_post_thumbnail() ) {
					echo '<figure class="sfafig"><a href="'.get_permalink().'" title="' . the_title_attribute( 'echo=0' ) . '">';
					the_post_thumbnail( 'thumbnail', array( 'class' => 'qbw-fa-thumb' ) );
					echo '</a></figure>';
						
				} else {
				
					// if not stopped with option qbw_stop_theme_icon @todo remove icons maybe
					if(get_option( 'qbw_stop_theme_icon') != 'true') {
							echo '<figure class="img-indent"><a href="'.get_permalink().'" title="' . the_title_attribute( 'echo=0' ) . '">'; ?>
							<img class="icon" src="<?php echo plugins_url('/images/news.svg', dirname(__FILE__)); ?>" width="40px" height="40" />
							<?php echo '</a></figure>';
					}
	
				}
				echo '<div class="sfacontent">';
				echo '<h4><a href="'.get_permalink().'" title="' . the_title_attribute( 'echo=0' ) . '">'.get_the_title().'</a></h4>';
				echo '<p>'. get_the_excerpt(). '</p>';
				echo '<a class="button" href="'.get_permalink().'" title="' . the_title_attribute( 'echo=0' ) . '">Read More</a>';
				echo '</div>';
				echo '</div>';	
		 
			} // endwhile;
		} else { 
				$li = '<a href="'.get_post_type_archive_link( 'smartest_news' ).'">'. __('News', 'quick-business-website'). '</a>';
				?>
				<p><?php printf(__( 'Coming soon. See all %s.', 'quick-business-website'), $li); ?></p>		
		<?php }
		wp_reset_postdata();
		echo $args['after_widget'];
	}// end widget

	/**
	 * Sanitize widget form values as they are saved.
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
			$title = __( 'Featured News', 'quick-business-website' );
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