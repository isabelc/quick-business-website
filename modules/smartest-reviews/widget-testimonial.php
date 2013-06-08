<?php
/**
 * Adds Smartest Reviews Testimonial widget
 *
 * @package		Quick Business Website
 * @subpackage 	Smartest Reviews Module
 * @extends 	WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
 */

class SmartestReviewsTestimonial extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_reviews_testimonial',
			'QBW Reviews Testimonial',
			array( 'description' => __( 'Display a random review as a testimonial.', 'smartestb' ), )
		);
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
		/* pull single review from smartest reviews table */
		function get_single_smartest_review() {
			global $wpdb;
			$single_review = $wpdb->get_var("SELECT review_text FROM wp_smareviewsb order by rand()");
			/* for multisite, use this instead of previous 2 lines 
			
						global $wpdb, $blog_id;
						$bi = get_current_blog_id();
						$pre = $wpdb->base_prefix;
						$pre2 = $pre . $bi . '_smareviewsb';
						$single_review = $wpdb->get_var("SELECT review_text FROM $pre2 order by rand()");
			
			*/		
			return wp_trim_words( $single_review, 11);
		}
		global $wpdb;
		// get the permalink by page id.
		$reviews_pageurl = get_permalink(get_option('smartest_reviews_page_id'));
		$testimon = get_single_smartest_review();
		if ($testimon == '') {
			//no review yet, lure them to leave one
			echo '<p>'. sprintf(__('Be the first to <a href="%s">leave a review...', 'smartestb'), $reviews_pageurl).'</a></p>';
		} else {
			echo  '<blockquote>'.get_single_smartest_review().'</blockquote>';
			echo '<a href="'.$reviews_pageurl.'">'.__('More...', 'smartestb').'</a>';
		}
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
			$title = __( 'Testimonials', 'smartestb' );
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