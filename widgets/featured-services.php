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
			wp_register_style('sfs',
			plugins_url('/sfs.css', __FILE__));
	} 
	/**
	 * Front-end display of widget.
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {

		
		wp_enqueue_style('sfs');
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Featured Services', 'quick-business-website' ) : $instance['title'], $instance, $this->id_base );
		echo $args['before_widget'];
		if ( $title )
			echo '<h3 class="widget-title">'. $title . '</h3>';
		if( get_option( 'qbw_enable_service_sort') == 'true'  ) {

			// custom sort order is enabled

			$query_args = array( 
				'post_type' => 'smartest_services',
				'meta_query' => array(
							array  (
								'key' => '_smab_services_featured',
								'value'=> 'on' 
							)
						),
				'orderby' => 'meta_value_num',
				'meta_key' => '_smab_service-order-number',
				'order' => 'ASC'
				);

		} else {

			// default sort order

			$query_args = array( 
				'post_type' => 'smartest_services',
				'meta_query' => array(
							array  (
								'key' => '_smab_services_featured',
								'value'=> 'on' 
								)
							)
				);

		}
		$sbffs = new WP_Query( $query_args );
		if ( $sbffs->have_posts() ) {
			while ( $sbffs->have_posts() ) {
				$sbffs->the_post();
				echo '<div class="sfswrap">';
				if ( has_post_thumbnail() ) {
					echo '<figure class="sfsfig"><a href="'.get_permalink().'" title="'. the_title_attribute( 'echo=0' ) .'">';
					$thumb = get_post_thumbnail_id(); 
					global $Quick_Business_Website;
					$smallimage = $Quick_Business_Website->vt_resize( $thumb, '', 152, 96, true); ?>
					<img src="<?php echo $smallimage['url']; ?>" alt="<?php the_title_attribute(); ?>" width="<?php echo $smallimage['width']; ?>" height="<?php echo $smallimage['height']; ?>" />
<?php
				echo '</a></figure>';
				}
						echo '<div class="sfscontent">';
							echo '<h4><a href="'.get_permalink().'" title="'. the_title_attribute( 'echo=0' ) .'">'.get_the_title().'</a></h4>';
							echo get_the_excerpt();
						echo '</div>';
				echo '</div>';	
			} // endwhile
		} else {
				$li = '<a href="'.get_post_type_archive_link( 'smartest_services' ).'">'. __('Services', 'quick-business-website'). '</a>';
				?>
				<p><?php printf(__( 'Coming soon. See all %s.', 'quick-business-website'), $li); ?></p>		
		<?php } // endif
		wp_reset_postdata();
		echo $args['after_widget'];

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
}?>