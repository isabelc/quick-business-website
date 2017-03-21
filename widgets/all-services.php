<?php
/**
 * Adds Services widget to list all services
 *
 * @package		Quick Business Website
 */

class SmartestServices extends WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'smartest_services_list',
			__('QBW Services List', 'quick-business-website'),
			array( 'description' => __( 'Display the full list of Services.', 'quick-business-website' ), )
		);
	}
	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Services', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );
		$service_category_term_id = $instance['service_category'];
		$service_category = !empty($service_category_term_id) ? $service_category_term_id : '';

		echo $args['before_widget'];
		if ( $title ) {
			echo '<h3 class="widget-title">'. esc_html( $title ) . '</h3>';
		}
		
		// Basic query args for default sort order
		$query_args = array( 
			'posts_per_page' => -1, 
			'post_type' => 'smartest_services',
			'orderby' => 'title',
			'order' => 'ASC' );

		// Check if custom sort order is enabled for Services
		if ( get_option( 'qbw_enable_service_sort') == 'true' ) {
		
			$query_args['orderby'] = 'meta_value_num';
			$query_args['meta_key'] = '_smab_service-order-number';
		}

		// if cat is selected, add the tax query args
		if ( ! empty ($service_category) ) {

			$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'smartest_service_category',
							'field' => 'id',
							'terms' => array( $service_category ),
						)
			);

		}

		$sbfservices = new WP_Query( $query_args );
		if ( $sbfservices->have_posts() ) {
			echo '<ul class="serviceslist">';
			while ( $sbfservices->have_posts() ) {
				$sbfservices->the_post();

				echo '<li><a href="' . esc_url( get_permalink() ) . '">' . esc_html( get_the_title() ) . '</a></li>';

			}
			echo '</ul>';

		}
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
		$instance['title'] = strip_tags($new_instance['title'] );
		$instance['service_category'] = $new_instance['service_category'];
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
			$title = __( 'Services', 'quick-business-website' );
		}
		if ( isset( $instance[ 'service_category' ] ) ) {
			$instance_service_category = $instance[ 'service_category' ];
		} else {
			$instance_service_category = '';
		} ?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'quick-business-website' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		 <p><label for="<?php echo $this->get_field_id( 'service_category' ); ?>"><?php _e( 'Optional. Only show services of this category:', 'quick-business-website' ); ?></label>
		<select class="widefat" name="<?php echo $this->get_field_name( 'service_category' ); ?>" id="<?php echo $this->get_field_id( 'service_category' ); ?>">
		<option value="" <?php if (empty($instance_service_category)) echo 'selected="selected"'; ?>>
		</option>';
		<?php $service_cats = get_terms('smartest_service_category');
		foreach ( $service_cats as $service_cat ) {
			$sele = ( $service_cat->term_id == $instance_service_category ) ? 'selected="selected"' : '';
			$option = '<option value="' . $service_cat->term_id . '" ' . $sele . '>';
			$option .= $service_cat->name;
			$option .= '</option>';
			echo $option;
		} ?>
		</select>
		</p>
		<?php 
	}
} ?>