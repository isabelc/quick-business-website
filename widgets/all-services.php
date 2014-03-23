<?php
/**
 * Adds Services widget to list all services
 *
 * @package		Quick Business Website
 * @extends		WP_Widget
 * @author		Smartest Themes <isa@smartestthemes.com>
 * @license		http://opensource.org/licenses/gpl-license.php GNU Public License
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
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$service_category_term_id = $instance['service_category'];
		$service_category = !empty($service_category_term_id) ? $service_category_term_id : '';

		echo $before_widget;
		if ( ! empty( $title ) )
			echo '<h3 class="widget-title">'. $title . '</h3>';
		
		// if cat is selected, do tax query
		if ( ! empty ($service_category) ) {

			if( get_option('smartestb_enable_service_sort') == 'true'  ) {

				// custom sort order is enabled

				$args = array( 
					'posts_per_page' => -1, 
					'post_type' => 'smartest_services',
					'tax_query' => array(
						array(
							'taxonomy' => 'smartest_service_category',
							'field' => 'id',
							'terms' => array( $service_category ),
						)
					),
					'orderby' => 'meta_value_num',
					'meta_key' => '_smab_service-order-number',
					'order' => 'ASC' );

			} else { 

				// default sort order
				$args = array( 
					'posts_per_page' => -1, 
					'post_type' => 'smartest_services',
					'tax_query' => array(
						array(
							'taxonomy' => 'smartest_service_category',
							'field' => 'id',
							'terms' => array( $service_category ),
						)
					),
					'orderby' => 'title',
					'order' => 'ASC' );

			}

		} else {

			// no tax query

			if( get_option('smartestb_enable_service_sort') == 'true'  ) {

				// custom sort order is enabled
				$args = array( 
					'posts_per_page' => -1, 
					'post_type' => 'smartest_services',
					'orderby' => 'meta_value_num',
					'meta_key' => '_smab_service-order-number',
					'order' => 'ASC' );

			} else {
				// default sort order
				$args = array( 
					'posts_per_page' => -1, 
					'post_type' => 'smartest_services',
					'orderby' => 'title',
					'order' => 'ASC' );
			}
		}
		$sbfservices = new WP_Query( $args );
		if ( $sbfservices->have_posts() ) {
			echo '<ul class="serviceslist">';
			while ( $sbfservices->have_posts() ) {
				$sbfservices->the_post();

				echo '<li><a href="'.get_permalink().'" title="'.get_the_title().'">'.get_the_title().'</a></li>';

			} // endwhile
			echo '</ul>';

		} // endif
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