<?php
/**
 * Adds Reviews Testimonial widget
 *
 * @package		Quick Business Website
 * @subpackage 	Reviews Module
 */
class QBW_Reviews_Testimonial extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'smartest_reviews_testimonial',
			'QBW Reviews Testimonial',
			array( 'description' => __( 'Display a random review as a testimonial.', 'quick-business-website' ), )
		);
	}
	/**
	 * Front-end display of widget.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Testimonials', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );
		$number = empty( $instance['number'] ) ? 1 : $instance['number'];
		$number = (int) $number;
		
		echo $args['before_widget'];
		if ( $title ) {
			echo '<h3 class="widget-title">'. esc_html( $title ) . '</h3>';
		}
		global $wpdb;
		// get the permalink by page id.
		$reviews_pageurl = esc_url( get_permalink( get_option( 'qbw_reviews_page_id' ) ) );
		$pre = $wpdb->base_prefix;
		if ( is_multisite() ) { 
			global $blog_id;
			$bi = get_current_blog_id();
			$pre2 = $pre . $bi . '_smareviewsb';
		} else {
			// not Multisite
			$pre2 = $pre . 'smareviewsb';
		}

		$getreviews = $wpdb->get_results("SELECT review_text FROM $pre2 WHERE status = 1 LIMIT 0,$number");

		if ( empty( $getreviews ) ) {
			//no review yet, lure them to leave one
			echo '<p>' . sprintf(__('Be the first to <a href="%s">leave a review...', 'quick-business-website'),
					$reviews_pageurl ) . '</a></p>';
		} else {
			foreach ( $getreviews as $getreview ) {
				$reviewBody = esc_html( $getreview->review_text );
				echo '<blockquote>' . wp_trim_words( $reviewBody, 20) . '</blockquote><br />';
			}
			echo '<a href="' . $reviews_pageurl . '">' . __('More...', 'quick-business-website') . '</a>';
		}
		echo $args['after_widget'];
	}

	/**
	 * Sanitize widget form values as they are saved.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = sanitize_text_field( $new_instance['title'] );
		$instance['number'] = intval( $new_instance['number'] );
		return $instance;
	}

	/**
	 * Back-end widget form.
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		} else {
			$title = __( 'Testimonials', 'quick-business-website' );
		}
 		if ( isset( $instance[ 'number' ] ) ) {
			$number = $instance[ 'number' ];
		} else {
			$number = 1; 
		} ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'quick-business-website' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'How many testimonials to show:', 'quick-business-website' ); ?></label> 
		<input type="text" class="widefat" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" value="<?php echo esc_attr( $number ); ?>" />
	</p>
<?php }
} ?>