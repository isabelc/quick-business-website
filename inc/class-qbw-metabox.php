<?php
/**
 * Create meta boxes
 */
class QBW_Metabox {
	protected $_meta_box;

	function __construct( $meta_box ) {
		if ( ! is_admin() ) return;

		$this->_meta_box = $meta_box;

		add_action( 'admin_menu', array( &$this, 'add' ) );
		add_action( 'save_post', array( &$this, 'save' ) );

	}

	// Add metaboxes
	function add() {
		$this->_meta_box['context'] = empty($this->_meta_box['context']) ? 'normal' : $this->_meta_box['context'];
		$this->_meta_box['priority'] = empty($this->_meta_box['priority']) ? 'high' : $this->_meta_box['priority'];

		foreach ( $this->_meta_box['pages'] as $page ) {
			add_meta_box( $this->_meta_box['id'], $this->_meta_box['title'], array( &$this, 'show' ), $page, $this->_meta_box['context'], $this->_meta_box['priority'] );
		}
	}

	// Show fields
	function show() {
		global $post;

		wp_enqueue_style( 'qbw-metabox' );

		// Use nonce for verification
		echo '<input type="hidden" name="wp_meta_box_nonce" value="', wp_create_nonce( basename(__FILE__) ), '" />';
		echo '<table class="form-table cmb_metabox">';

		foreach ( $this->_meta_box['fields'] as $field ) {
			// Set up blank or default values for empty ones
			if ( !isset( $field['name'] ) ) $field['name'] = '';
			if ( !isset( $field['desc'] ) ) $field['desc'] = '';
			if ( !isset( $field['std'] ) ) $field['std'] = '';

			$meta = get_post_meta( $post->ID, $field['id'], true );

			echo '<tr>';

			if ( $field['type'] == "title" ) {
				echo '<td colspan="2">';
			} else {
				if( $this->_meta_box['show_names'] == true ) {
					echo '<th style="width:18%"><label for="', esc_attr( $field['id'] ), '">', esc_html( $field['name'] ), '</label></th>';
				}
				echo '<td>';
			}

			switch ( $field['type'] ) {

				case 'text':
					echo '<input type="text" name="', esc_attr( $field['id'] ), '" id="', esc_attr( $field['id'] ), '" value="', '' !== $meta ? esc_attr( $meta ) : esc_attr( $field['std'] ), '" />','<p class="cmb_metabox_description">', esc_html( $field['desc'] ), '</p>';
					break;
				case 'text_medium':
					echo '<input class="cmb_text_medium" type="text" name="', esc_attr( $field['id'] ), '" id="', esc_attr( $field['id'] ), '" value="', '' !== $meta ? esc_attr( $meta ) : esc_attr( $field['std'] ), '" /><span class="cmb_metabox_description">', esc_html( $field['desc'] ), '</span>';
					break;
				case 'checkbox':
					echo '<input type="checkbox" name="', esc_attr( $field['id'] ), '" id="', esc_attr( $field['id'] ), '"', $meta ? ' checked="checked"' : '', ' />';
					echo '<span class="cmb_metabox_description">', esc_html( $field['desc'] ), '</span>';
					break;
				case 'title':
					echo '<h5 class="cmb_metabox_title">', esc_html( $field['name'] ), '</h5>';
					echo '<p class="cmb_metabox_description">', esc_html( $field['desc'] ), '</p>';
					break;
				default:
					do_action('cmb_render_' . $field['type'] , $field, $meta);
			}

			echo '</td>','</tr>';
		}
		echo '</table>';
	}

	// Save data from metabox
	function save( $post_id )  {

		// verify nonce
		if ( ! isset( $_POST['wp_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['wp_meta_box_nonce'], basename(__FILE__) ) ) {
			return $post_id;
		}

		// check autosave
		if ( defined('DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// check permissions
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		foreach ( $this->_meta_box['fields'] as $field ) {
			$name = $field['id'];

			$old = get_post_meta( $post_id, $name, true );
			$new = isset( $_POST[$field['id']] ) ? $_POST[$field['id']] : null;

			$new = apply_filters( 'cmb_validate_' . $field['type'], $new, $post_id, $field );

			if ( '' !== $new && $new != $old  ) {
				$new = sanitize_text_field( $new );
				update_post_meta( $post_id, $name, $new );
			} elseif ( '' == $new ) {
				delete_post_meta( $post_id, $name );
			}

		}
	}
}
