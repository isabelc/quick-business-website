<?php
/**
 * Get reviews from visitors, and aggregate ratings and stars for your business in search results.
 * 
 * Adds Structured data (Schema.org) for rich snippets.
 * Includes Testimonial widget. 
 * Optional: pulls aggregaterating to home page. Option to not pull it to home page, 
 * and just have a reviews page.
 * 
 * @package		Quick Business Website
 * @subpackage	Reviews Module
 */
class QBW_Reviews {
	var $dbtable = 'smareviewsb';
	var $got_aggregate = false;
	var $options = array();
	var $p = '';
	var $page = 1;
	var $shown_form = false;
	var $shown_hcard = false;
	var $status_msg = '';

	function __construct() {
		global $wpdb;

		define( 'QBW_REVIEWS', 1 );
		$this->dbtable = $wpdb->prefix . $this->dbtable;

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'widgets_init', array( $this, 'register_widget' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		add_action( 'admin_menu', array( $this, 'addmenu' ) );
		add_action( 'wp_ajax_update_field', array( $this, 'admin_view_reviews' ) );
		add_action( 'admin_init', array( $this, 'create_reviews_page' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'wp_head', array( $this, 'frontpage_structured_data' ) );
	}

	function addmenu() {
		if ( get_option( 'qbw_add_reviews') == 'true') {
			add_options_page(__('Reviews', 'quick-business-website'), __('Reviews', 'quick-business-website'), 'manage_options', 'smar_options', array( $this, 'admin_options' ) );
			add_menu_page( __('Reviews', 'quick-business-website'), __('Reviews', 'quick-business-website'), 'edit_others_posts', 'qbw_view_reviews', array( $this, 'admin_view_reviews' ), $this->get_reviews_module_url() . 'star.png', 62);
		}
   }
	function admin_options() {
		global $QBW_Reviews_Admin;
		$this->include_admin();
		$QBW_Reviews_Admin->real_admin_options();
	}
	function admin_view_reviews() {
		global $QBW_Reviews_Admin;
		$this->include_admin();
		$QBW_Reviews_Admin->real_admin_view_reviews();
	}
	function get_jumplink_for_review( $review, $page ) {
		$link = get_permalink( get_option( 'qbw_reviews_page_id' ) );
		$link = trailingslashit( $link ) . "?smarp=$page#hreview-$review->id";
		return $link;
	}
	function get_options() {
		$default_options = array(
			'act_email' => '',
			'act_uniq' => '',
			'activate' => 0,
			'ask_custom' => array(),
			'ask_fields' => array('fname' => 1, 'femail' => 1, 'fwebsite' => 0, 'ftitle' => 0, 'fage' => 0, 'fgender' => 0),
			'field_custom' => array(),
			'form_location' => 0,
			'goto_leave_text' => __( 'Click here to add your review.', 'quick-business-website' ),
			'leave_text' => __( 'Submit your review', 'quick-business-website' ),
			'goto_show_button' => 1,
			'require_custom' => array(),
			'require_fields' => array('fname' => 1, 'femail' => 1, 'fwebsite' => 0, 'ftitle' => 0, 'fage' => 0, 'fgender' => 0),
			'reviews_per_page' => 10,
			'show_custom' => array(),
			'show_fields' => array('fname' => 1, 'femail' => 0, 'fwebsite' => 0, 'ftitle' => 1, 'fage' => 0, 'fgender' => 0),
			'submit_button_text' => __('Submit your review', 'quick-business-website'),
			'title_tag' => 'h2'
		);
		 $this->options = get_option('smar_options', $default_options);
		/* magically easy migrations to newer versions */
		$has_new = false;
		foreach ($default_options as $col => $def_val) {
			if (!isset($this->options[$col])) {
				$this->options[$col] = $def_val;
				$has_new = true;
			}
			if (is_array($def_val)) {
				foreach ($def_val as $acol => $aval) {
					if (!isset($this->options[$col][$acol])) {
						$this->options[$col][$acol] = $aval;
						$has_new = true;
					}
				}
			}
		}
		if ($has_new) {
			update_option('smar_options', $this->options);
		}
	}
	function make_p_obj() {
		$this->p = new stdClass();
		foreach ($_GET as $c => $val) {
			if (is_array($val)) {
				$this->p->$c = $val;
			} else {
				$this->p->$c = sanitize_text_field( stripslashes( $val ) );
			}
		}

		foreach ($_POST as $c => $val) {
			if (is_array($val)) {
				$this->p->$c = $val;
			} else {
				$this->p->$c = sanitize_text_field( stripslashes( $val ) );
			}
		}
	}
	/**
	 * Create the Reviews table if it has not been created.
	 */
	private function check_table() {
		if ( get_option( 'qbw_reviews_table_created' ) != 'completed' ) {
			global $QBW_Reviews_Admin;
			$this->include_admin();
			$QBW_Reviews_Admin->createUpdateReviewTable();// @test on new install


			/************************************************************
			*
			* @todo now now Test this by :
			1. back up the plugin.
			2. delete (uninstall) the plugin.
			3. re-install the plugin fresh.
			4. Make sure that reviews table was created well.
			5. make sure the old reviews were deleted on uninstall.
			
			*
			************************************************************/
			

			update_option( 'qbw_reviews_table_created', 'completed' );
		}
	}

	public function template_redirect() {
		/* do this in template_redirect so we can try to redirect cleanly */
		global $post;
		if (!isset($post) || !isset($post->ID)) {
			return;
		}
		if ( isset( $_COOKIE['smar_status_msg'] ) ) {
			$this->status_msg = $_COOKIE['smar_status_msg'];
			if ( !headers_sent() ) {
				setcookie('smar_status_msg', '', time() - 3600); /* delete the cookie */
				unset($_COOKIE['smar_status_msg']);
			}
		}
		$GET_P = "qbw_submit_review";
		$reviews_page_id = get_option( 'qbw_reviews_page_id' );
		if ( $post->ID == $reviews_page_id && isset( $this->p->$GET_P ) && $this->p->$GET_P == $this->options['submit_button_text'] ) {
			$msg = $this->add_review();
			$url = get_permalink( $reviews_page_id );
			$cookie = array( 'smar_has_error' => $msg[0], 'smar_status_msg' => $msg[1] );
			$this->smar_redirect( $url, $cookie );
		}
	}
	function rand_string($length) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$str = '';

		$size = strlen($chars);
		for ($i = 0; $i < $length; $i++) {
			$str .= $chars[rand(0, $size - 1)];
		}
		return $str;
	}
	/**
	 * Sets the got_aggregate property.
	 */
	private function get_aggregate_reviews() {
		if ( $this->got_aggregate !== false ) {
			return $this->got_aggregate;
		}
		global $wpdb;
		$row = $wpdb->get_results("SELECT COUNT(*) AS `total`,AVG(review_rating) AS `aggregate_rating`,MAX(review_rating) AS `max_rating` FROM `$this->dbtable` WHERE `status`=1");

		/* make sure we have at least one review before continuing below */
		if ($wpdb->num_rows == 0 || $row[0]->total == 0) {
			$this->got_aggregate = array( "aggregate" => 0, "max" => 0, "total" => 0 );
			return false;
		}
		$aggregate_rating = $row[0]->aggregate_rating;
		$max_rating = $row[0]->max_rating;// max GIVEN rating, not max allowed rating
		$total_reviews = $row[0]->total;
		
		$this->got_aggregate = array( "aggregate" => $aggregate_rating, "max" => $max_rating, "total" => $total_reviews );
		return true;
	}

	/**
	 * Get reviews from the databse.
	 */
	function get_reviews( $startpage, $perpage, $status ) {
		global $wpdb;
		$startpage = $startpage - 1; /* mysql starts at 0 instead of 1, so reduce them all by 1 */
		if ($startpage < 0) { $startpage = 0; }
		$limit = 'LIMIT ' . $startpage * $perpage . ',' . $perpage;
		if ( $status == -1 ) { // All reviews including pending and approved
			$reviews = $wpdb->get_results("SELECT 
				`id`,
				`date_time`,
				`reviewer_name`,
				`reviewer_email`,
				`review_title`,
				`review_text`,
				`review_response`,
				`review_rating`,
				`reviewer_url`,
				`reviewer_ip`,
				`status`,
				`custom_fields`
				FROM `$this->dbtable` ORDER BY `date_time` DESC $limit
				");
			$total_reviews = $wpdb->get_results("SELECT COUNT(*) AS `total` FROM `$this->dbtable`");

		} else {
			$status = esc_sql( $status );
			$reviews = $wpdb->get_results( $wpdb->prepare(
				"SELECT 
				`id`,
				`date_time`,
				`reviewer_name`,
				`reviewer_email`,
				`review_title`,
				`review_text`,
				`review_response`,
				`review_rating`,
				`reviewer_url`,
				`reviewer_ip`,
				`status`,
				`custom_fields`
				FROM `$this->dbtable` WHERE `status` = %s ORDER BY `date_time` DESC $limit
				", $status ) );

			$total_reviews = $wpdb->get_results( $wpdb->prepare( "SELECT COUNT(*) AS `total` FROM `$this->dbtable` WHERE `status` = %s", $status ) );

		}

		$total_reviews = $total_reviews[0]->total;
		return array( $reviews, $total_reviews );
	}

	/**
	 * Add JSON-LD structured data to the front page, if enabled.
	 */
	public function frontpage_structured_data() {
		if ( ! is_front_page() ) {
			return;
		}
		$metadata = qbw_business_structured_data();

		$this->get_aggregate_reviews();

		$average_score = number_format( $this->got_aggregate["aggregate"], 1 );
	
		// Add aggregate rating
		$metadata['aggregateRating'] = array(
			'@type' => 'AggregateRating',
			'ratingValue' => $average_score,
			'bestRating' => 5,
			'reviewCount' => $this->got_aggregate["total"]
		);
		?>
		<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
		<?php
	}

	public function iso8601($time=false) {
		if ($time === false)
			$time = time();
		$date = date('Y-m-d\TH:i:sO', $time);
		return (substr($date, 0, strlen($date) - 2) . ':' . substr($date, -2));
	}

	public function pagination( $total_results, $reviews_per_page ) {
		global $post; /* will exist if on a post */

		$out = '';
		$uri = false;
		$pretty = false;

		$range = 2;
		$showitems = ($range * 2) + 1;

		$paged = $this->page;
		if ( $paged == 0 ) {
			$paged = 1;
		}
		
		if ( ! isset( $this->p->review_status ) ) {
			$this->p->review_status = 0;
		}

		$pages = ceil($total_results / $reviews_per_page);

		if ($pages > 1) {
			if (is_admin()) {
				$url = '?page=qbw_view_reviews&amp;review_status=' . $this->p->review_status . '&amp;';
			} else {
				$uri = trailingslashit(get_permalink($post->ID));
				if (strpos($uri, '?') === false) {
					$url = $uri . '?';
					$pretty = true;
				} /* page is using pretty permalinks */ else {
					$url = $uri . '&amp;';
					$pretty = false;
				} /* page is using get variables for pageid */
			}

			$out .= '<div id="smar_pagination"><div id="smar_pagination_page">'. __('Page: ', 'quick-business-website'). '</div>';

			if ($paged > 2 && $paged > $range + 1 && $showitems < $pages) {
				if ($uri && $pretty) {
					$url2 = $uri;
				} /* not in admin AND using pretty permalinks */ else {
					$url2 = $url;
				}
				$out .= '<a href="' . esc_url( $url2 ) . '">&laquo;</a>';
			}

			if ($paged > 1 && $showitems < $pages) {
				$url_pieces = $url . 'smarp=' . ($paged - 1);
				$out .= '<a href="' . esc_url( $url_pieces ) . '">&lsaquo;</a>';
			}

			for ($i = 1; $i <= $pages; $i++) {
				if ($i == $paged) {
					$out .= '<span class="smar_current">' . esc_html( $paged ) . '</span>';
				} else if (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems) {
					if ($i == 1) {
						if ($uri && $pretty) {
							$url2 = $uri;
						} /* not in admin AND using pretty permalinks */ else {
							$url2 = $url;
						}
						$out .= '<a href="' . esc_url( $url2 ) . '" class="smar_inactive">' . $i . '</a>';
					} else {
						$url_pieces = $url . 'smarp=' . $i;
						$out .= '<a href="' . esc_url( $url_pieces ) . '" class="smar_inactive">' . $i . '</a>';
					}
				}
			}

			if ($paged < $pages && $showitems < $pages) {
				$url_pieces = $url . 'smarp=' . ($paged + 1);
				$out .= '<a href="' . esc_url( $url_pieces ) . '">&rsaquo;</a>';
			}
			if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages) {
				$url_pieces = $url . 'smarp=' . $pages;
				$out .= '<a href="' . esc_url( $url_pieces ) . '">&raquo;</a>';
			}
			$out .= '</div>';
			$out .= '<div class="smar_clear smar_pb5"></div>';

			return $out;
		}
	}

	/**
	 * Returns the Reviews page content in an array
	 */
	public function reviews_html( $perpage ) {
		$arr_Reviews = $this->get_reviews( $this->page, $perpage, 1 );
		$reviews = $arr_Reviews[0];
		$total_reviews = intval( $arr_Reviews[1] );
		$reviews_content = '';
		$hidesummary = '';
		$title_tag = esc_attr( $this->options['title_tag'] );

		$qbw_options = get_option( 'qbw_options');

		/* trying to access a page that does not exists -- send to main page */
		if ( isset($this->p->smarp) && $this->p->smarp != 1 && count($reviews) == 0 ) {
			$url = get_permalink( get_option( 'qbw_reviews_page_id' ) );
			$this->smar_redirect( $url );
		}
		if ( count( $reviews ) == 0 ) {
			$reviews_content .= '<p>'. __('There are no reviews yet. Be the first to leave yours!', 'quick-business-website').'</p>';
		} elseif ( $qbw_options['qbw_add_reviews'] == 'false' ) {
				$reviews_content .= '<p>'.__('Reviews are not available.', 'quick-business-website').'</p>';
		} else {
			$this->get_aggregate_reviews();
			$average_score = number_format( $this->got_aggregate["aggregate"], 1 );

			// Gather the LocalBusiness structured data
			$metadata = qbw_business_structured_data();

			foreach ( $reviews as $review ) {
				
				$reviewBody = esc_html( $review->review_text );

				$hide_name = '';
				if ($this->options['show_fields']['fname'] == 0) {
					$review->reviewer_name = __('Anonymous', 'quick-business-website');
					$hide_name = 'smar_hide';
				}
				if ($review->reviewer_name == '') {
					$review->reviewer_name = __('Anonymous', 'quick-business-website');
				}

				if ($this->options['show_fields']['fwebsite'] == 1 && $review->reviewer_url != '') {
					$reviewBody .= '<br /><small><a href="' . esc_url( $review->reviewer_url ) . '">' . $review->reviewer_url . '</a></small>';
				}
				if ($this->options['show_fields']['femail'] == 1 && $review->reviewer_email != '') {
					$reviewBody .= '<br /><small>' . antispambot( esc_html( $review->reviewer_email ) ) . '</small>';
				}
				if ($this->options['show_fields']['ftitle'] == 1) {
					/* do nothing */
				} else {
					$review->review_title = substr( strip_tags( $reviewBody ), 0, 150 );
					$hidesummary = 'smar_hide';
				}
				
				$reviewBody = nl2br( $reviewBody );
				$review_response = '';
				
				if ( strlen( $review->review_response ) > 0 ) {
					$review_response = '<p class="response"><strong>'.__('Response:', 'quick-business-website').'</strong> ' . nl2br( esc_html( $review->review_response ) ) . '</p>';
				}

				$custom_shown = '';
				$custom_fields_unserialized = maybe_unserialize( $review->custom_fields );
				if ( ! is_array( $custom_fields_unserialized ) ) {
					$custom_fields_unserialized = array();
				}

				foreach ($this->options['field_custom'] as $i => $val) {  
					if ( $val ) {
						if ( ! empty($custom_fields_unserialized[$val]) ) {
							
							$show = $this->options['show_custom'][$i];
							if ($show == 1 && $custom_fields_unserialized[$val] != '') {
								$custom_shown .= "<small class='smar_fl'>" . esc_html( $val ) . ': ' . esc_html( $custom_fields_unserialized[ $val ] ) . '&nbsp;&bull;&nbsp;</small>';
							}
							
						}
					}
				}
				$custom_shown = preg_replace("%&bull;&nbsp;</small>$%si","</small><div class='smar_clear'></div>",$custom_shown);

				// gather the Reviews structured data
				$datePublished = $this->iso8601( strtotime( $review->date_time ) );
				$author = $review->reviewer_name;
				$description = $review->review_title;
				$ratingValue = $review->review_rating;
		

				$reviews_content .= '<div id="hreview-' . esc_attr( $review->id ) . '"><' . $title_tag . ' class="summary ' . $hidesummary . '">' . esc_html( $description ) . '</' . $title_tag . '><div class="smar_fl smar_sc"><div class="smar_rating">' . $this->output_rating( $review->review_rating, false ) . '</div></div>';

				// The name block
				$reviews_content .= '<div class="smar_fl smar_rname"><span id="qbw-reviewer-' . esc_attr( $review->id ) . '">' .
					'<span class="' . $hide_name . '">' . esc_html( $author ) . ' &nbsp; &nbsp; </span>' .
					'<small><time datetime="' . esc_attr( $datePublished ) . '">' .
					esc_html( date_i18n( get_option( 'date_format' ), strtotime( $review->date_time ) ) ) .
					'</time></small>' .
					 '</span><div class="smar_clear"></div></div>';

				// Review content
				$reviews_content .= '<div class="smar_clear smar_spacing1"></div><blockquote class="description"><p>' . $reviewBody . '</p></blockquote>' .
					$custom_shown . $review_response . '</div><hr />';

				// Add structured data for each review to the metadata array
				$metadata['review'][] = array(
					'@type' => 'Review',
					'datePublished' => $datePublished,
					'author' => array(
						'@type' => 'Person',
						'name' => $author
					),
					'description' => $description,
					'reviewBody' => $reviewBody,
					'reviewRating' => array(
						'@type' => 'Rating',
						'ratingValue' => $ratingValue
					)
				);

			}//  foreach ($reviews as $review)

			// aggregate rating
			$reviews_content .= '<span id="qbw-reviews-aggregate">'. 
								sprintf( esc_html( _n( 'Average rating: %1$s out of 5 based on %2$d review.', 'Average rating: %1$s out of 5 based on %2$d reviews.', $this->got_aggregate["total"], 'quick-business-website' ) ),
									$average_score,
									$this->got_aggregate["total"] ) .
								'</span>';
				
			// Add structured data for aggregate rating to the metadata array
			$metadata['aggregateRating'] = array(
				'@type' => 'AggregateRating',
				'ratingValue' => $average_score,
				'bestRating' => 5,
				'reviewCount' => $this->got_aggregate["total"]
			);

			// Add the reviews structured data in JSON-LD format
			?>
			<script type="application/ld+json"><?php echo wp_json_encode( $metadata ); ?></script>
			<?php
		}

		return array( $reviews_content, $total_reviews );
	}
	
	/**
	 * If enabled in settings, create reviews page, storing page id.
	 * @uses insert_post()
	 */
	public function create_reviews_page() {
		if ( get_option( 'qbw_add_reviews' ) == 'true' ) {
			global $Quick_Business_Website;
			$Quick_Business_Website->insert_post( esc_sql( _x('reviews', 'page_slug', 'quick-business-website') ), 'qbw_reviews_page_id', __('Reviews', 'quick-business-website'), '[SMAR_INSERT]' );
		}
	}
	/**
	 * Returns the HTML string for the rating stars
	 */
	public function output_rating( $rating, $enable_hover ) {
		$out = '';
		$rating_width = 20 * $rating; /* 20% for each star if having 5 stars */
		$out .= '<div class="sp_rating">';
		if ( $enable_hover ) {
			$out .= '<div class="status"><div class="score"><a class="score1">1</a><a class="score2">2</a><a class="score3">3</a><a class="score4">4</a><a class="score5">5</a></div></div>';
		}

		$out .= '<div class="base"><div class="average" style="width:' . esc_attr( $rating_width ) . '%"></div></div>';
		$out .= '</div>';

		return $out;
	}

	/**
	 * Get the HTML for the reviews form
	 */
	private function show_reviews_form() {
		$fields = '';
		$out = '';
		$req_js = "<script type='text/javascript'>";
		$status_msg_html = '';

		if ( isset( $_COOKIE['smar_status_msg'] ) ) {
			$this->status_msg = $_COOKIE['smar_status_msg'];
		}

		if ( $this->status_msg != '' ) {
			$req_js .= "smar_del_cookie('smar_status_msg');";
			$status_msg_html = '<div class="smar_status_msg"><div id="qbw_' .
							( empty( $_COOKIE['smar_has_error'] ) ? 'success' : 'error' ) . '">' .
							esc_html( $this->status_msg ) .
							'</div></div>';

		}

		/* a silly and crazy but effective antispam measure.. bots wont have a clue */
		$rand_prefixes = array();
		for ($i = 0; $i < 15; $i++) {
			$rand_prefixes[] = $this->rand_string(mt_rand(1, 8));
		}
		
		if (!isset($this->p->fname)) { $this->p->fname = ''; }
		if (!isset($this->p->femail)) { $this->p->femail = ''; }
		if (!isset($this->p->fwebsite)) { $this->p->fwebsite = ''; }
		if (!isset($this->p->ftitle)) { $this->p->ftitle = ''; }
		if (!isset($this->p->ftext)) { $this->p->ftext = ''; }

		if ($this->options['ask_fields']['fname'] == 1) {
			if ($this->options['require_fields']['fname'] == 1) {
				$req = '*';
			} else {
				$req = '';
			}
			$fields .= '<tr><td><label for="' . $rand_prefixes[0] . '-fname" class="comment-field">'. __('Name:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[0] . '-fname" name="' . $rand_prefixes[0] . '-fname" value="' . esc_attr( $this->p->fname ) . '" /></td></tr>';
		}
		if ($this->options['ask_fields']['femail'] == 1) {
			if ($this->options['require_fields']['femail'] == 1) {
				$req = '*';
			} else {
				$req = '';
			}
			$fields .= '<tr><td><label for="' . $rand_prefixes[1] . '-femail" class="comment-field">'. __('Email:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[1] . '-femail" name="' . $rand_prefixes[1] . '-femail" value="' . esc_attr( $this->p->femail ) . '" /></td></tr>';
		}
		if ($this->options['ask_fields']['fwebsite'] == 1) {
			if ($this->options['require_fields']['fwebsite'] == 1) {
				$req = '*';
			} else {
				$req = '';
			}
			$fields .= '<tr><td><label for="' . $rand_prefixes[2] . '-fwebsite" class="comment-field">'. __('Website:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[2] . '-fwebsite" name="' . $rand_prefixes[2] . '-fwebsite" value="' . esc_attr( $this->p->fwebsite ) . '" /></td></tr>';
		}
		if ($this->options['ask_fields']['ftitle'] == 1) {
			if ($this->options['require_fields']['ftitle'] == 1) {
				$req = '*';
			} else {
				$req = '';
			}
			$fields .= '<tr><td><label for="' . $rand_prefixes[3] . '-ftitle" class="comment-field">'. __('Review Title:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[3] . '-ftitle" name="' . $rand_prefixes[3] . '-ftitle" maxlength="150" value="' . esc_attr( $this->p->ftitle ) . '" /></td></tr>';
		}

		$custom_fields = array();
		$custom_count = count( $this->options['field_custom'] );
		for ($i = 0; $i < $custom_count; $i++) {
			$custom_fields[$i] = $this->options['field_custom'][$i];
		}

		foreach ($this->options['ask_custom'] as $i => $val) {
			if ( isset($this->options['ask_custom'][$i]) ) {
				if ($val == 1) {
					if ( isset( $this->options['require_custom'][$i] ) && 1 == $this->options['require_custom'][$i] ) {
						$req = '*';
					} else {
						$req = '';
					}

					$custom_i = "custom_$i";
					if ( ! isset( $this->p->$custom_i ) ) {
						$this->p->$custom_i = '';
					}
					$fields .= '<tr><td><label for="custom_' . esc_attr( $i ) . '" class="comment-field">' . esc_html( $custom_fields[$i] ) . ': ' . $req . '</label></td><td><input class="text-input" type="text" id="custom_' . esc_attr( $i ) . '" name="custom_' . esc_attr( $i ) . '" maxlength="150" value="' . esc_attr( $this->p->$custom_i ) . '" /></td></tr>';
				}
			} 
		}

		$some_required = '';
		
		foreach ( $this->options['require_fields'] as $col => $val ) {
			if ( $val == 1 ) {
				$col = esc_js( $col );
				$req_js .= "smar_req.push('$col');";
				$some_required = '<small>* '. __('Required Field', 'quick-business-website').'</small>';
			}
		}

		foreach ( $this->options['require_custom'] as $i => $val ) {
			if ( $val == 1 ) {
				$i = (int) $i;
				$req_js .= "smar_req.push('custom_$i');";
				$some_required = '<small>* '. __('Required Field', 'quick-business-website').'</small>';
			}
		}
		
		$req_js .= "</script>";
		
		// add the form
		if ( $this->options['goto_show_button'] == 1 ) {
			// show errors or thank you message
			$out .= $status_msg_html;
			// Show button to show form
			$out .= '<p><a id="smar_button_1" href="javascript:void(0);">' . esc_html( $this->options['goto_leave_text'] ) . '</a></p>';
		}

		/* different output variables make it easier to debug this section */
		$out .= '<div id="smar_respond_2">' . $req_js . '
					<form class="smarcform" id="smar_commentform" method="post" action="javascript:void(0);">
						<div id="smar_div_2">
							<input type="hidden" id="frating" name="frating" />
							<table id="smar_table_2">
								<tbody>
									<tr><td colspan="2"><div id="smar_postcomment">' .
									esc_html( $this->options["leave_text"] ) .
									'</div></td></tr>' . $fields;
		$out2 = '<tr>
				<td><label class="comment-field">'. __('Rating:', 'quick-business-website').'</label></td>
				<td><div class="smar_rating">' . $this->output_rating( 0, true ) . '</div></td>
			</tr>';

		$out3 = '<tr><td colspan="2"><label for="' . $rand_prefixes[5] . '-ftext" class="comment-field">'. __('Review:', 'quick-business-website').'</label></td></tr>
				<tr><td colspan="2"><textarea id="' . $rand_prefixes[5] . '-ftext" name="' . $rand_prefixes[5] . '-ftext" rows="8" cols="50">' . esc_textarea( $this->p->ftext ) . '</textarea></td></tr>
				<tr>
					<td colspan="2" id="smar_check_confirm">' . $some_required .
					'<div class="smar_clear"></div>    
						<input type="checkbox" name="' . $rand_prefixes[6] . '-fconfirm1" id="fconfirm1" value="1" />
						<div class="smar_fl"><input type="checkbox" name="' . $rand_prefixes[7] . '-fconfirm2" id="fconfirm2" value="1" /></div><div class="smar_fl" style="margin:-2px 0px 0px 5px"><label for="fconfirm2">'. __('Check this box to confirm you are human.', 'quick-business-website').'</label></div>
						<div class="smar_clear"></div>
						<input type="checkbox" name="' . $rand_prefixes[8] . '-fconfirm3" id="fconfirm3" value="1" />
					</td>
				</tr>
				<tr><td colspan="2"><input id="smar_submit_btn" name="qbw_submit_review" type="submit" value="' . esc_attr( $this->options['submit_button_text'] ) . '" /></td></tr>
			</tbody>
			</table>
			</div>
			</form>';

		$out4 = '<hr /></div>';
		$out4 .= '<div class="smar_clear smar_pb5"></div>';

		return $out . $out2 . $out3 . $out4;
	}

	/**
	 * Insert a review into the database and send email notification of they review to the admin.
	 */
	function add_review() {
		global $wpdb;

		/* begin - some antispam magic */
		$this->newp = new stdClass();

		foreach ($this->p as $col => $val) {
			$pos = strpos($col, '-');
			if ($pos !== false) {
				$col = substr($col, $pos + 1); /* off by one */
			}
			$this->newp->$col = $val;
		}

		$this->p = $this->newp;
		unset($this->newp);
		/* end - some antispam magic */

		/* some sanitation */
		$date_time = date('Y-m-d H:i:s');
		$ip = sanitize_text_field( $_SERVER['REMOTE_ADDR'] );
		
		if (!isset($this->p->fname)) { $this->p->fname = ''; }
		if (!isset($this->p->femail)) { $this->p->femail = ''; }
		if (!isset($this->p->fwebsite)) { $this->p->fwebsite = ''; }
		if (!isset($this->p->ftitle)) { $this->p->ftitle = ''; }
		if (!isset($this->p->ftext)) { $this->p->ftext = ''; }
		if (!isset($this->p->frating)) { $this->p->frating = 0; } /* default to 0 */
		if (!isset($this->p->fconfirm1)) { $this->p->fconfirm1 = 0; } /* default to 0 */
		if (!isset($this->p->fconfirm2)) { $this->p->fconfirm2 = 0; } /* default to 0 */
		if (!isset($this->p->fconfirm3)) { $this->p->fconfirm3 = 0; } /* default to 0 */
		
		$this->p->fname = sanitize_text_field( $this->p->fname );
		$this->p->femail = sanitize_email( $this->p->femail );
		$this->p->fwebsite = sanitize_text_field( $this->p->fwebsite );
		$this->p->ftitle = sanitize_text_field( $this->p->ftitle );
		$this->p->ftext = sanitize_text_field( $this->p->ftext );
		$this->p->frating = intval($this->p->frating);

		/* begin - server-side validation */
		$errors = '';

		foreach ($this->options['require_fields'] as $col => $val) {
			if ($val == 1) {
				if (!isset($this->p->$col) || $this->p->$col == '') {
					$nice_name = ucfirst(substr($col, 1));
					$errors .= __('You must include your', 'quick-business-website').' ' . $nice_name . '. ';
				}
			}
		}

		$custom_fields = array();
		$custom_count = count($this->options['field_custom']);
		for ($i = 0; $i < $custom_count; $i++) {
			$custom_fields[$i] = $this->options['field_custom'][$i];
		}

		foreach ($this->options['require_custom'] as $i => $val) {
			if ($val == 1) {
				$custom_i = "custom_$i";
				if (!isset($this->p->$custom_i) || $this->p->$custom_i == '') {
					$nice_name = $custom_fields[$i];
					$errors .= __('You must include your', 'quick-business-website').' ' . $nice_name . '. ';
				}
			}
		}
		
		if ( $this->p->femail != '' && $this->options['ask_fields']['femail'] == 1 ) {
			if ( ! is_email( $this->p->femail ) ) {
				$errors .= __('The email address provided is not valid.', 'quick-business-website') . ' ';
			}
		}

		if (intval($this->p->fconfirm1) == 1 || intval($this->p->fconfirm3) == 1) {
			$errors .= __('You have triggered our anti-spam system. Please try again. Code 001.', 'quick-business-website').' ';
		}

		if (intval($this->p->fconfirm2) != 1) {
			$errors .= __('You have triggered our anti-spam system. Please try again. Code 002', 'quick-business-website').' ';
		}

		if ($this->p->frating < 1 || $this->p->frating > 5) {
			$errors .= __('You have triggered our anti-spam system. Please try again. Code 003', 'quick-business-website').' ';
		}

		if ( strlen( $this->p->ftext ) < 5 ) {
			$errors .= __('You must include a review. Please make reviews at least 5 letters.', 'quick-business-website').' ';
		}

		/* returns true for errors */
		if ( $errors ) {
			return array( true, $errors );
		}
		/* end server-side validation */

		$custom_insert = array();
		for ( $i = 0; $i < $custom_count; $i++ ) {
			if ( ! empty( $this->options['ask_custom'][$i] ) ) {
				$name = sanitize_text_field( $custom_fields[$i] );
				$custom_i = "custom_$i";		
				if ( isset($this->p->$custom_i) ) {
					$custom_insert[ $name ] = ucfirst( sanitize_text_field( $this->p->$custom_i ) );
				}
			}
		}
		$custom_insert = serialize($custom_insert);
		$wpdb->query( $wpdb->prepare( "INSERT INTO `$this->dbtable` (`date_time`, `reviewer_name`, `reviewer_email`, `reviewer_ip`, `review_title`, `review_text`, `status`, `review_rating`, `reviewer_url`, `custom_fields`) VALUES (%s, %s, %s, %s, %s, %s, %d, %d, %s, %s)", $date_time, $this->p->fname, $this->p->femail, $ip, $this->p->ftitle, $this->p->ftext, 0, $this->p->frating, $this->p->fwebsite, $custom_insert )
		);

		$bn = strip_tags( qbw_get_business_name() );
		$url = get_admin_url() . 'admin.php?page=qbw_view_reviews';
		$admin_link = '<a href="' . esc_url( $url ) . '">' . esc_html( $url ) . '</a>';
		$message = sprintf( __( 'A new review has been posted on %1$s\'s website.', 'quick-business-website' ),
					$bn ) .
					"\n\n" .
					__('You will need to login to the admin area and approve this review before it will appear on your site.','quick-business-website') . "\n\n" .
					sprintf( __('Link to admin approval page: %s', 'quick-business-website'), $admin_link );

		/************************************************************
		*
		* @todo must reinstate wp_mail after testing
		*
		************************************************************/
		
		// wp_mail( get_bloginfo('admin_email'), $bn.': '. sprintf(__('New Review Posted on %1$s', 'quick-business-website'), date('m/d/Y h:i e') ), $message );


		/* returns false for no error */
		return array( false, __('Thank you for your comments. All submissions are moderated and if approved, yours will appear soon.', 'quick-business-website' ) );
	}
	function smar_redirect( $url, $cookie = array() ) {
		$headers_sent = headers_sent();
		if ( true == $headers_sent ) {

			/* use JS redirect and add cookie before redirect */
			$out = '<html><head><title>'.__('Redirecting', 'quick-business-website').'...</title></head><body><div style="clear:both;text-align:center;padding:10px;">' .
					__('Processing... Please wait...', 'quick-business-website') .
					'<script type="text/javascript">';
			foreach ( $cookie as $col => $val ) {
				$out .= "document.cookie='" . esc_js( $col ) . "=" . esc_js( $val ) . "';";
			}
			$out .= "window.location='" . esc_js( $url ) . "';";
			$out .= "</script>";
			$out .= "</div></body></html>";
			echo $out;

		} else {
			foreach ($cookie as $col => $val) {
				setcookie( $col, $val ); /* add cookie via headers */
			}
			if ( ob_get_length() ) {
				ob_end_clean();
			}
			
			wp_redirect( esc_url_raw( $url ) );
		}
		
		exit();
	}
	function init() { /* used for admin_init also */
		$this->make_p_obj(); /* make P variables object */
		$this->get_options(); /* populate the options array */
		$this->check_table();

		if ( !isset($this->p->smarp) ) { $this->p->smarp = 1; }
		
		$this->page = intval($this->p->smarp);
		if ($this->page < 1) { $this->page = 1; }
		
		add_shortcode( 'SMAR_INSERT', array( $this, 'shortcode_smar_insert') );
	}
	/**
	 * The shortcode to display the Reviews page
	 */
	public function shortcode_smar_insert() {
		$reviews = '<div id="smar_respond_1">';
	   
		if ( 0 == $this->options['form_location'] ) {
			$reviews .= $this->show_reviews_form();
		}

		$reviews_arr = $this->reviews_html( $this->options['reviews_per_page'] );

		$reviews .= $reviews_arr[0];
		$total_reviews = $reviews_arr[1];
		
		$reviews .= $this->pagination( $total_reviews, $this->options['reviews_per_page'] );

		if ( 1 == $this->options['form_location'] ) {
			$reviews .= $this->show_reviews_form();
		}
		
		$reviews .= '</div><!-- #smar_respond_1 -->';

		$reviews = preg_replace('/\n\r|\r\n|\n|\r|\t/', '', $reviews); /* minify to prevent automatic line breaks, not removing double spaces */

		return $reviews;
	}

	function enqueue_scripts() {
		if ( get_option( 'qbw_add_reviews') == 'true'  ) {
			wp_register_style('qbw-reviews', $this->get_reviews_module_url() . 'reviews.css', array() );
			wp_register_script('qbw-reviews', $this->get_reviews_module_url() . 'reviews.js', array('jquery') );

			if ( is_page(get_option( 'qbw_reviews_page_id' ))) {
			
				wp_enqueue_style('qbw-reviews');
				wp_enqueue_script('qbw-reviews');
				$loc = array(
					'hidebutton' => __('Click here to hide form', 'quick-business-website'),
					'email' => __('The email address provided is not valid.', 'quick-business-website'),
					'name' => __('You must include your ', 'quick-business-website'),
					'review' => __('You must include a review. Please make reviews at least 4 letters.', 'quick-business-website'),
					'human' => __('You must confirm that you are human.', 'quick-business-website'),
					'code2' => __('Code 2.', 'quick-business-website'),
					'code3' => __('Code 3.', 'quick-business-website'),
					'rating' => __('Please select a star rating from 1 to 5.', 'quick-business-website')
					);
				wp_localize_script( 'qbw-reviews', 'smartlocal', $loc);
			}

		}
	}
	/**
	 * widget
	 */
	function register_widget() {
		if( get_option( 'qbw_add_reviews') == 'true'  ) {
			register_widget('QBW_Reviews_Testimonial');
		}
	}
	function include_admin() {
		global $QBW_Reviews_Admin;
		require_once QUICKBUSINESSWEBSITE_PATH . 'modules/reviews/reviews-admin.php';
	}
	function admin_init() {
		global $QBW_Reviews_Admin;
		$this->include_admin(); /* include admin functions */
		$QBW_Reviews_Admin->real_admin_init();
	}
	function admin_scripts() {
		global $QBW_Reviews_Admin;
		$QBW_Reviews_Admin->enqueue_admin_stuff();
	}
	function get_reviews_module_url() {
		return QUICKBUSINESSWEBSITE_URL . 'modules/reviews/';
	}
}
if ( ! defined( 'QBW_REVIEWS' ) ) {
	global $QBW_Reviews;
	$QBW_Reviews = new QBW_Reviews();
	add_action( 'after_setup_theme', array( $QBW_Reviews, 'activate' ) );
}
/* get widget */
include_once('widget-testimonial.php');
?>