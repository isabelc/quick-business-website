<?php
/**
 * Adds Featured Services widget to show selected services
 *
 * @package		Quick Business Website
 */

class SmartestFeaturedServices extends WP_Widget {
	public function __construct() {
		parent::__construct(
	 		'smartest_featured_services',
			__('QBW Featured Services', 'quick-business-website'),
			array( 'description' => __( 'Display selected featured services.', 'quick-business-website' ), )
		);
		add_action('wp_enqueue_scripts', array($this, 'featsvcs_css'));
	}
	/**
	 * Register stylesheet.
	 */
	public function featsvcs_css() {
		wp_register_style( 'qbw-feat-services', QUICKBUSINESSWEBSITE_URL . 'css/qbw-feat-services.css' );
	} 
	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		wp_enqueue_style( 'qbw-feat-services' );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Featured Services', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'];
		if ( $title ) {
			echo '<h3 class="widget-title">'. esc_html( $title ) . '</h3>';
		}

		// default sort order
		$query_args = array( 
			'post_type' => 'smartest_services',
			'no_found_rows' => true,
			'meta_query' => array(
						array  (
							'key' => '_smab_services_featured',
							'value'=> 'on' 
							)
						)
			);		


		// Check if custom sort order is enabled
		if ( get_option( 'qbw_enable_service_sort') == 'true' ) {
		
			$query_args['orderby'] = 'meta_value_num';
			$query_args['meta_key'] = '_smab_service-order-number';
			$query_args['order'] = 'ASC';
		}

		$sbffs = new WP_Query( $query_args );
		if ( $sbffs->have_posts() ) {
			while ( $sbffs->have_posts() ) {
				$sbffs->the_post();
				echo '<div class="sfswrap">';
				if ( has_post_thumbnail() ) {
					echo '<figure class="sfsfig"><a href="' . esc_url( get_permalink() ) . '">';
					the_post_thumbnail( 'thumbnail', array( 'class' => 'qbw-fs-thumb' ) );
					echo '</a></figure>';
				}
				echo '<div class="sfscontent">';
				echo '<h4><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></h4>';
				echo get_the_excerpt();
				echo '</div>';
				echo '</div>';	
			} // endwhile
		} else {
				$li = '<a href="' . esc_url( get_post_type_archive_link( 'smartest_services' ) ) . '">' .  __('Services', 'quick-business-website') . '</a>';
				?>
				<p><?php printf(__( 'Coming soon. See all %s.', 'quick-business-website'), $li); ?></p>		
		<?php } // endif
		wp_reset_postdata();
		echo $args['after_widget'];

	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = sanitize_text_field($new_instance['title'] );
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
			$title = __( 'Featured Services', 'quick-business-website' );
		} ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'quick-business-website' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php 
	}
} ?>