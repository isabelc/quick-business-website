<?php
/**
 * Adds Featured Announcements widget to show selected announcements
 *
 * @package		Quick Business Website
 * @extends		WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SmartestFeaturedAnnounce extends WP_Widget {

	/**
	 * Register widget
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_featured_announce',
			__('QBW Featured Announcements', 'smartestb'),
			array( 'description' => __( 'Display selected featured announcements.', 'smartestb' ), )
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

		extract( $args );
		wp_enqueue_style('sfa');
		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( ! empty( $title ) )
			echo '<h3 class="widget-title">'. $title . '</h3>';
		$args = array(
			'post_type' => 'smartest_news',
			'meta_query' => array(
				array (
					'key' => '_smab_news_featured',
					'value'=> 'on'
				)
			)
		);
		$sbffa = new WP_Query( $args );
		if ( $sbffa->have_posts() ) {
			while ( $sbffa->have_posts() ) {
				$sbffa->the_post();
				echo '<div id="sfawrap">';
				if ( has_post_thumbnail() ) {
				
					$thumb = get_post_thumbnail_id();
					global $Quick_Business_Website;
					$smallimage = $Quick_Business_Website->vt_resize( $thumb, '', 40, 65, true);
					echo '<figure id="sfafig"><a href="'.get_permalink().'" title="'.get_the_title().'">';
					?>
					<img class="thumb" src="<?php echo $smallimage['url']; ?>" width="<?php echo $smallimage['width']; ?>" />
		
					<?php echo '</a></figure>';
						
				} else {
					// if not stopped with option smartestb_stop_theme_icon
					if(get_option('smartestb_stop_theme_icon') != 'true') {
							$smallimage = array('url' => get_template_directory_uri(). '/images/newsicon.png', 'width' => '40px', 'cl' => 'icon');
							echo '<figure class="img-indent"><a href="'.get_permalink().'" title="'.get_the_title().'">'; ?>
							<img class="icon" src="<?php echo plugins_url('/images/news.svg', dirname(__FILE__)); ?>" width="40px" />
							<?php echo '</a></figure>';
					}
	
				}
				echo '<div id="sfacontent">';
				echo '<h4><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></h4>';
				echo '<p>'. get_the_excerpt(). '</p>';
				echo '<a class="button" href="'.get_permalink().'" title="'.get_the_title().'">Read More</a>';
				echo '</div>';
				echo '</div>';	
		 
			} // endwhile;
		} else { 
				$li = '<a href="'.get_post_type_archive_link( 'smartest_news' ).'">'. __('News', 'smartestb'). '</a>';
				?>
				<p><?php printf(__( 'Coming soon. See all %s.', 'smartestb'), $li); ?></p>		
		<?php }
		wp_reset_postdata();
		echo $after_widget;
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
			$title = __( 'Featured News', 'smartestb' );
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