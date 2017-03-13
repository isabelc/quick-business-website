<?php
/**
 * Get reviews from visitors, and aggregate ratings and stars for your business in search results.
 * 
 *  Adds Microdata markup (Schema.org) for rich snippets. Includes Testimonial widget. Optional: pulls aggregaterating to home page. Option to not pull it to home page, and just have a reviews page. Requires Smartest Themes for full functionality.
 * 
 * @package		Quick Business Website
 * @subpackage	Smartest Reviews Module
 */
class SMARTESTReviewsBusiness {

	var $dbtable = 'smareviewsb';
	var $force_active_page = false;
	var $got_aggregate = false;
	var $options = array();
	var $p = '';
	var $page = 1;
	var $plugin_version = '0.0.0';
	var $shown_form = false;
	var $shown_hcard = false;
	var $status_msg = '';

	function SMARTESTReviewsBusiness() {
		global $wpdb;

        define('IN_SMAR', 1);
        
        /* uncomment the below block to display strict/notice errors */
        /*
        restore_error_handler();
        error_reporting(E_ALL);
        ini_set('error_reporting', E_ALL);
        ini_set('html_errors',TRUE);
        ini_set('display_errors',TRUE);
        */
        $this->dbtable = $wpdb->prefix . $this->dbtable;
		$this->plugin_version = get_option('qbw_smartestb_plugin_version');
        add_action('the_content', array( $this, 'do_the_content'), 10); /* prio 10 prevents a conflict with some odd themes */
        add_action('init', array( $this, 'init'));
        add_action('admin_init', array( $this, 'admin_init'));
		add_action( 'widgets_init', array( $this, 'register_widget'));
		add_action('template_redirect',array( $this, 'template_redirect')); /* handle redirects and form posts, and add style/script if needed */
	    add_action('admin_menu', array( $this, 'addmenu'));
        add_action('wp_ajax_update_field', array( $this, 'admin_view_reviews'));
	    add_action('save_post', array( $this, 'admin_save_post'), 10, 2);
		add_action( 'admin_init', array( $this, 'create_reviews_page'));//isa, admin_init in frame but for stand-alone plugin hook to after_setup_theme
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts'));
		add_action('admin_enqueue_scripts', array( $this, 'admin_scripts'));
    }

    function addmenu() {
        add_options_page(__('Reviews', 'quick-business-website'), '<img src="' . $this->get_reviews_module_url() . 'star.png" />&nbsp;'. __('Reviews', 'quick-business-website'), 'manage_options', 'smar_options', array(&$this, 'admin_options'));
		if(get_option( 'qbw_add_reviews') == 'true') {       
			add_menu_page(__('Reviews', 'quick-business-website'), __('Reviews', 'quick-business-website'), 'edit_others_posts', 'smar_view_reviews', array(&$this, 'admin_view_reviews'), $this->get_reviews_module_url() . 'star.png', 62);
		}
   }
    function admin_options() {
        global $SMARTESTReviewsBusinessAdmin;
        $this->include_admin();
        $SMARTESTReviewsBusinessAdmin->real_admin_options();
    }
   function admin_save_post($post_id, $post) {
       global $SMARTESTReviewsBusinessAdmin;
        $this->include_admin();
       $SMARTESTReviewsBusinessAdmin->real_admin_save_post($post_id);
    }
    function admin_view_reviews() {
        global $SMARTESTReviewsBusinessAdmin;
        $this->include_admin();
        $SMARTESTReviewsBusinessAdmin->real_admin_view_reviews();
    }
    function get_jumplink_for_review($review,$page) {
       /* $page will be 1 for shortcode usage since it pulls most recent, which SHOULD all be on page 1 */
       $link = get_permalink( get_option( 'qbw_reviews_page_id' ) );
        if (strpos($link,'?') === false) {
            $link = trailingslashit($link) . "?smarp=$page#hreview-$review->id";
        } else {
            $link = $link . "&smarp=$page#hreview-$review->id";
        }
        return $link;
    }
    function get_options() {
        $home_domain = @parse_url(get_home_url());
        $home_domain = $home_domain['scheme'] . "://" . $home_domain['host'] . '/';
        $default_options = array(
            'act_email' => '',
            'act_uniq' => '',
            'activate' => 0,
            'ask_custom' => array(),
            'ask_fields' => array('fname' => 1, 'femail' => 1, 'fwebsite' => 0, 'ftitle' => 0, 'fage' => 0, 'fgender' => 0),
            'dbversion' => 0,
            'field_custom' => array(),
            'form_location' => 0,
            'goto_leave_text' => __('Click here to submit your review.', 'quick-business-website'),
            'goto_show_button' => 1,
            'leave_text' => __('Submit your review', 'quick-business-website'),
            'require_custom' => array(),
            'require_fields' => array('fname' => 1, 'femail' => 1, 'fwebsite' => 0, 'ftitle' => 0, 'fage' => 0, 'fgender' => 0),
            'reviews_per_page' => 10,
            'show_custom' => array(),
            'show_fields' => array('fname' => 1, 'femail' => 0, 'fwebsite' => 0, 'ftitle' => 1, 'fage' => 0, 'fgender' => 0),
            'show_hcard_on' => 1, 'biz_declare' => 1,
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
                $this->p->$c = trim(stripslashes($val));
            }
        }

        foreach ($_POST as $c => $val) {
            if (is_array($val)) {
                $this->p->$c = $val;
            } else {
                $this->p->$c = trim(stripslashes($val));
            }
        }
    }
    function check_migrate() {
        global $wpdb;
        $migrated = false;
        /* remove me after official release */
        $current_dbversion = intval(str_replace('.', '', $this->options['dbversion']));
        $plugin_db_version = intval(str_replace('.', '', $this->plugin_version));
        if ($current_dbversion == $plugin_db_version) {
            return false;
        }
        global $SMARTESTReviewsBusinessAdmin;
        $this->include_admin(); /* include admin functions */
        $SMARTESTReviewsBusinessAdmin->createUpdateReviewtable(); /* creates AND updates table */
        /* initial installation */
        if ($current_dbversion == 0) {
           $this->options['dbversion'] = $plugin_db_version;
            $current_dbversion = $plugin_db_version;
            update_option('smar_options', $this->options);
            return false;
        }
        /* check for upgrades if needed */
        /* upgrade to 2.0.0 */
        if ($current_dbversion < 200) {
            /* change all current reviews to use the selected page id */
            $pageID = intval(get_option( 'qbw_reviews_page_id' ));
            $wpdb->query("UPDATE `$this->dbtable` SET `page_id`=$pageID WHERE `page_id`=0");
            $this->options['dbversion'] = 200;
            $current_dbversion = 200;
            update_option('smar_options', $this->options);
            $migrated = true;
        }
        /* done with all migrations, push dbversion to current version */
        if ($current_dbversion != $plugin_db_version || $migrated == true) {
            $this->options['dbversion'] = $plugin_db_version;
            $current_dbversion = $plugin_db_version;
            update_option('smar_options', $this->options);
            global $SMARTESTReviewsBusinessAdmin;
            $this->include_admin(); /* include admin functions */
            $SMARTESTReviewsBusinessAdmin->force_update_cache(); /* update any caches */
            return true;
        }
        return false;
    }

    function is_active_page() {
        global $post;
        $has_shortcode = $this->force_active_page;
        if ( $has_shortcode !== false ) {
            return 'shortcode';
        }
        if ( !isset($post) || !isset($post->ID) || intval($post->ID) == 0 ) {
            return false; /* we can only use if we have a valid post ID */
        }
        if (!is_singular()) {
            return false; /* not on a single post/page view */
        }
        return false;
    }
	function template_redirect() {
		/* do this in template_redirect so we can try to redirect cleanly */
        global $post;
        if (!isset($post) || !isset($post->ID)) {
            $post = new stdClass();
            $post->ID = 0;
        }
        if (isset($_COOKIE['smar_status_msg'])) {
            $this->status_msg = $_COOKIE['smar_status_msg'];
            if ( !headers_sent() ) {
                setcookie('smar_status_msg', '', time() - 3600); /* delete the cookie */
                unset($_COOKIE['smar_status_msg']);
            }
        }
        $GET_P = "submitsmar_$post->ID";
        if ($post->ID > 0 && isset($this->p->$GET_P) && $this->p->$GET_P == $this->options['submit_button_text'])
        {
            $msg = $this->add_review($post->ID);
            $has_error = $msg[0];
            $status_msg = $msg[1];
            $url = get_permalink($post->ID);
            $cookie = array('smar_status_msg' => $status_msg);
            $this->smar_redirect($url, $cookie);
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
    function get_aggregate_reviews($pageID) {
        if ($this->got_aggregate !== false) {
            return $this->got_aggregate;
        }
        global $wpdb;
        $pageID = get_option( 'qbw_reviews_page_id' );
        $row = $wpdb->get_results("SELECT COUNT(*) AS `total`,AVG(review_rating) AS `aggregate_rating`,MAX(review_rating) AS `max_rating` FROM `$this->dbtable` WHERE `status`=1");

        /* make sure we have at least one review before continuing below */
        if ($wpdb->num_rows == 0 || $row[0]->total == 0) {
            $this->got_aggregate = array("aggregate" => 0, "max" => 0, "total" => 0, "text" => __('Reviews for my site', 'quick-business-website'));
            return false;
        }
        $aggregate_rating = $row[0]->aggregate_rating;
        $max_rating = $row[0]->max_rating;
        $total_reviews = $row[0]->total;
        $row = $wpdb->get_results("SELECT `review_text` FROM `$this->dbtable` WHERE `page_id`=$pageID AND `status`=1 ORDER BY `date_time` DESC");
        $sample_text = ! empty( $row[0]->review_text ) ? substr($row[0]->review_text, 0, 180) : '';
        $this->got_aggregate = array("aggregate" => $aggregate_rating, "max" => $max_rating, "total" => $total_reviews, "text" => $sample_text);
        return true;
    }
    function get_reviews( $startpage, $perpage, $status ) {
        global $wpdb;
        $startpage = $startpage - 1; /* mysql starts at 0 instead of 1, so reduce them all by 1 */
        if ($startpage < 0) { $startpage = 0; }
        $limit = 'LIMIT ' . $startpage * $perpage . ',' . $perpage;
        if ($status == -1) {
            $qry_status = '1=1';
        } else {
            $qry_status = "`status`=$status";
        }

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
            `page_id`,
            `custom_fields`
            FROM `$this->dbtable` WHERE $qry_status ORDER BY `date_time` DESC $limit
            ");
        $total_reviews = $wpdb->get_results("SELECT COUNT(*) AS `total` FROM `$this->dbtable` WHERE $qry_status");
        $total_reviews = $total_reviews[0]->total;
        return array($reviews, $total_reviews);
    }

    function aggregate_footer() {// for home page
        $qbw_options = get_option( 'qbw_options');
		// gather agg data
		$postID = get_option( 'qbw_reviews_page_id' );
		$arr_Reviews = $this->get_reviews('', $this->options['reviews_per_page'], 1);
	 	$reviews = $arr_Reviews[0];// 12.5 prob dont need
		$total_reviews = intval($arr_Reviews[1]);
		$this->get_aggregate_reviews($postID);
        $best_score = 5;
        $average_score = number_format($this->got_aggregate["aggregate"], 1);
	    $aggregate_footer_output = '';
		/* only if set to agg Business ratings on front page and is front page & if home page is static then show. */
		if ( ($this->options['show_hcard_on'] == 1) && is_front_page() && (get_option('show_on_front') == 'page') ) {
						$show = true;
					}
else {$show = false; }

       	if ($show) { /* we append like this to prevent newlines and wpautop issues */

				// if set to declare business schema type, do it
            	if ( $this->options['biz_declare'] == 1 ) {
						$isabiz_declare = ' itemscope itemtype="http://schema.org/LocalBusiness"';// isa depend
		                $aggregate_footer_output = '<div id="smar_respond_1"><div id="smar_hcard_s"' . $isabiz_declare . ' class="isa_vcard">';


	$bn = stripslashes_deep(esc_attr($qbw_options['qbw_business_name']));if(!$bn) {$bn = get_bloginfo('name'); }

              $aggregate_footer_output .= '<a itemprop="name" href="' . site_url('/')
 . '">' . $bn . '</a><br />';
		                if (// isa depend addy
			                        $qbw_options['qbw_address_street'] != '' || 
			                        $qbw_options['qbw_address_city'] != '' ||
			                        $qbw_options['qbw_address_state'] != '' ||
			                        $qbw_options['qbw_address_zip'] != '' ||
			                        $qbw_options['qbw_address_country'] != ''
			                   )
			                {
			                    $aggregate_footer_output .= '<span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">';
			                    if ($qbw_options['qbw_address_street'] != '') {
			                        $aggregate_footer_output .= '<span itemprop="streetAddress">' . $qbw_options['qbw_address_street'] . '</span>&nbsp;';
			                    }

								if ($qbw_options['qbw_address_suite'] != '') {
											                        $aggregate_footer_output .= ' ' . $qbw_options['qbw_address_suite'] . '&nbsp;';
											                    }

			                    if ($qbw_options['qbw_address_city'] != '') {
			                        $aggregate_footer_output .='<span itemprop="addressLocality">' . $qbw_options['qbw_address_city'] . '</span>,&nbsp;';
			                    }
			                    if ($qbw_options['qbw_address_state'] != '') {
			                        $aggregate_footer_output .='<span itemprop="addressRegion">' . $qbw_options['qbw_address_state'] . '</span>,&nbsp;';
			                    }
			                    if ($qbw_options['qbw_address_zip'] != '') {
			                        $aggregate_footer_output .='<span class="postal-code" itemprop="postalCode">' . $qbw_options['qbw_address_zip'] . '</span>&nbsp;';
			                    }
			                    if ($qbw_options['qbw_address_country'] != '') {
			                        $aggregate_footer_output .='<span itemprop="addressCountry">' . $qbw_options['qbw_address_country'] . '</span>&nbsp;';
			                    }
			
			                    $aggregate_footer_output .= '</span>';
			                }
			
			                if ( $qbw_options['qbw_phone_number'] != '') {// isa depend
			                    $aggregate_footer_output .= '<br />&nbsp;&bull;&nbsp<span itemprop="telephone">' . $qbw_options['qbw_phone_number'] . '</span>';
			                }


					} else { // end if biz_declare, do else
							$aggregate_footer_output = '<div id="smar_respond_1"><div id="smar_hcard_s" class="isa_vcard">';
						}

					// do agg rating for both scenarios

					$aggregate_footer_output .= '<br /><span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" id="hreview-smar-aggregate"> '. __('Average rating:', 'quick-business-website'). ' <span itemprop="ratingValue" class="average">' . $average_score . '</span> ' . __('out of', 'quick-business-website'). ' <span itemprop="bestRating">' . $best_score . ' </span> '. __('based on', 'quick-business-website').' <span itemprop="ratingCount">' . $this->got_aggregate["total"] . ' </span>';
				
					if($this->got_aggregate["total"] == 1)
					    $basedon = __('review.', 'quick-business-website');
					else
					    $basedon = __('reviews.', 'quick-business-website');
					$aggregate_footer_output .= sprintf(__('%s', 'quick-business-website'), $basedon). '</span>';
	                $aggregate_footer_output .= '</div></div><!-- end agg footer -->';
            
			}// end if $show
		        return $aggregate_footer_output;
    }
    function iso8601($time=false) {
        if ($time === false)
            $time = time();
        $date = date('Y-m-d\TH:i:sO', $time);
        return (substr($date, 0, strlen($date) - 2) . ':' . substr($date, -2));
    }

    function pagination($total_results, $reviews_per_page) {
        global $post; /* will exist if on a post */

        $out = '';
        $uri = false;
        $pretty = false;

        $range = 2;
        $showitems = ($range * 2) + 1;

        $paged = $this->page;
        if ($paged == 0) { $paged = 1; }
        
        if (!isset($this->p->review_status)) { $this->p->review_status = 0; }

        $pages = ceil($total_results / $reviews_per_page);

        if ($pages > 1) {
            if (is_admin()) {
                $url = '?page=smar_view_reviews&amp;review_status=' . $this->p->review_status . '&amp;';
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
                $out .= '<a href="' . $url2 . '">&laquo;</a>';
            }

            if ($paged > 1 && $showitems < $pages) {
                $out .= '<a href="' . $url . 'smarp=' . ($paged - 1) . '">&lsaquo;</a>';
            }

            for ($i = 1; $i <= $pages; $i++) {
                if ($i == $paged) {
                    $out .= '<span class="smar_current">' . $paged . '</span>';
                } else if (!($i >= $paged + $range + 1 || $i <= $paged - $range - 1) || $pages <= $showitems) {
                    if ($i == 1) {
                        if ($uri && $pretty) {
                            $url2 = $uri;
                        } /* not in admin AND using pretty permalinks */ else {
                            $url2 = $url;
                        }
                        $out .= '<a href="' . $url2 . '" class="smar_inactive">' . $i . '</a>';
                    } else {
                        $out .= '<a href="' . $url . 'smarp=' . $i . '" class="smar_inactive">' . $i . '</a>';
                    }
                }
            }

            if ($paged < $pages && $showitems < $pages) {
                $out .= '<a href="' . $url . 'smarp=' . ($paged + 1) . '">&rsaquo;</a>';
            }
            if ($paged < $pages - 1 && $paged + $range - 1 < $pages && $showitems < $pages) {
                $out .= '<a href="' . $url . 'smarp=' . $pages . '">&raquo;</a>';
            }
            $out .= '</div>';
            $out .= '<div class="smar_clear smar_pb5"></div>';

            return $out;
        }
    }
        
    function output_reviews_show($inside_div, $postid, $perpage, $max, $hide_custom = 0, $hide_response = 0, $snippet_length = 0, $show_morelink = '') {
	if ($max != -1) {
            $thispage = 1;
        } else {
            $thispage = $this->page;
        }
       $arr_Reviews = $this->get_reviews($thispage, $perpage, 1);
        $reviews = $arr_Reviews[0];
        $total_reviews = intval($arr_Reviews[1]);
        $reviews_content = '';
        $hidesummary = '';
        $title_tag = $this->options['title_tag'];

		$qbw_options = get_option( 'qbw_options');
		$bn = stripslashes_deep(esc_attr($qbw_options['qbw_business_name']));if(!$bn) {$bn = get_bloginfo('name'); }

        /* trying to access a page that does not exists -- send to main page */
        if ( isset($this->p->smarp) && $this->p->smarp != 1 && count($reviews) == 0 ) {
            $url = get_permalink(get_option( 'qbw_reviews_page_id' ));
            $this->smar_redirect($url);
        }
        
        if ($postid == 0) {
            /* NOTE: if using shortcode to show reviews for all pages, could do weird things when using product type */
            $postid = $reviews[0]->page_id;
        }

        if (!$inside_div) {// isa depend itemtype, phone, addy

            $reviews_content .= '<!-- no inside div --><div id="smar_respond_1"';
				$reviews_content .= ' itemscope itemtype="http://schema.org/LocalBusiness">
							<span class="isa_vcard" id="hreview-smar-hcard-for-' . $review->id . '">
                                <a itemprop="name" href="' . site_url('/') . '">' . $bn . '</a>
                                <span itemprop="telephone">' . $qbw_options['qbw_phone_number'] . '</span>
                                <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                    <span itemprop="streetAddress">' . $qbw_options['qbw_address_street'] . ' ' .$qbw_options['qbw_address_suite'] . '</span>
                                    <span itemprop="addressLocality">' . $qbw_options['qbw_address_city'] . '</span>
                                    <span itemprop="addressRegion">' . $qbw_options['qbw_address_state'] . '</span> <span itemprop="postalCode">' . $qbw_options['qbw_address_zip'] . '</span>
                                    <span itemprop="addressCountry">' . $qbw_options['qbw_address_country'] . '</span></span></span><hr />';
        }
        if (count($reviews) == 0) {
            $reviews_content .= '<p>'. __('There are no reviews yet. Be the first to leave yours!', 'quick-business-website').'</p>';
        } elseif ($qbw_options['qbw_add_reviews'] == 'false') {
				$reviews_content .= '<p>'.__('Reviews are not available.', 'quick-business-website').'</p>';
        } else {// isa depend itemtype, phone, addy
	   		$postid = get_option( 'qbw_reviews_page_id' );
            $this->get_aggregate_reviews($postid);
            $summary = $this->got_aggregate["text"];
            $best_score = 5;
            $average_score = number_format($this->got_aggregate["aggregate"], 1);
			$reviews_content .= '<div itemscope itemtype="http://schema.org/LocalBusiness"><br />
							<span class="isa_vcard">
                                <a itemprop="name" href="' . site_url('/') . '">' . $bn . '</a><br />
                                <span itemprop="telephone">' . $qbw_options['qbw_phone_number'] . '</span><br />
                                <span itemprop="address" itemscope itemtype="http://schema.org/PostalAddress">
                                    <span itemprop="streetAddress">' . $qbw_options['qbw_address_street'] . ' ' .$qbw_options['qbw_address_suite'] . '</span><br />
                                    <span itemprop="addressLocality">' . $qbw_options['qbw_address_city'] . '</span>
                                    <span itemprop="addressRegion">' . $qbw_options['qbw_address_state'] . '</span> <span itemprop="postalCode">' . $qbw_options['qbw_address_zip'] . '</span>
                                    <span itemprop="addressCountry">' . $qbw_options['qbw_address_country'] . '</span>
                                </span>
                            </span><hr />';
		
            foreach ($reviews as $review) {
                
                if ($snippet_length > 0)
                {
                    $review->review_text = $this->trim_text_to_word($review->review_text,$snippet_length);
                }
                
                $hide_name = '';
                if ($this->options['show_fields']['fname'] == 0) {
                    $review->reviewer_name = __('Anonymous', 'quick-business-website');
                    $hide_name = 'smar_hide';
                }
                if ($review->reviewer_name == '') {
                    $review->reviewer_name = __('Anonymous', 'quick-business-website');
                }

                if ($this->options['show_fields']['fwebsite'] == 1 && $review->reviewer_url != '') {
                    $review->review_text .= '<br /><small><a href="' . $review->reviewer_url . '">' . $review->reviewer_url . '</a></small>';
                }
                if ($this->options['show_fields']['femail'] == 1 && $review->reviewer_email != '') {
                    $review->review_text .= '<br /><small>' . $review->reviewer_email . '</small>';
                }
                if ($this->options['show_fields']['ftitle'] == 1) {
                    /* do nothing */
                } else {
                    $review->review_title = substr($review->review_text, 0, 150);
                    $hidesummary = 'smar_hide';
                }
                
                if ($show_morelink != '') {
                    $review->review_text .= " <a href='".$this->get_jumplink_for_review($review,1)."'>$show_morelink</a>";
                }
                
                $review->review_text = nl2br($review->review_text);
                $review_response = '';
                
                if ($hide_response == 0)
                {
                    if (strlen($review->review_response) > 0) {
                        $review_response = '<p class="response"><strong>'.__('Response:', 'quick-business-website').'</strong> ' . nl2br($review->review_response) . '</p>';
                    }
                }

                $custom_shown = '';
                if ($hide_custom == 0)
                {
                    $custom_fields_unserialized = @unserialize($review->custom_fields);
                    if (!is_array($custom_fields_unserialized)) {
                        $custom_fields_unserialized = array();
                    }
					
                    foreach ($this->options['field_custom'] as $i => $val) {  
						if ( $val ) {
							if ( ! empty($custom_fields_unserialized[$val]) ) {
							
								$show = $this->options['show_custom'][$i];
								if ($show == 1 && $custom_fields_unserialized[$val] != '') {
									$custom_shown .= "<div class='smar_fl'>" . $val . ': ' . $custom_fields_unserialized[$val] . '&nbsp;&bull;&nbsp;</div>';
								}
							
							}
						}
                    }//foreach ($this->options['field_custom
                    $custom_shown = preg_replace("%&bull;&nbsp;</div>$%si","</div><div class='smar_clear'></div>",$custom_shown);
                }// if 0 hide

                $name_block = '' .'<div class="smar_fl smar_rname">' .'<abbr title="' . $this->iso8601(strtotime($review->date_time)) . '" itemprop="datePublished">' . date("M d, Y", strtotime($review->date_time)) . '</abbr>&nbsp;' .'<span class="' . $hide_name . '">'. __('by', 'quick-business-website').'</span>&nbsp;' . '<span class="isa_vcard" id="hreview-smar-reviewer-' . $review->id . '">' . '<span class="' . $hide_name . '" itemprop="author">' . $review->reviewer_name . '</span>' . '</span>' . '<div class="smar_clear"></div>' .
 $custom_shown . '</div>';

                    $reviews_content .= '<div itemprop="review" itemscope itemtype="http://schema.org/Review" id="hreview-' . $review->id . '"><' . $title_tag . ' itemprop="description" class="summary ' . $hidesummary . '">' . $review->review_title . '</' . $title_tag . '><div class="smar_fl smar_sc"><div class="smar_rating">' . $this->output_rating($review->review_rating, false) . '</div></div>' . $name_block . '<div class="smar_clear smar_spacing1"></div><blockquote itemprop="reviewBody" class="description"><p>' . $review->review_text . ' '.__('Rating:', 'quick-business-website').' <span itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating"><span itemprop="ratingValue">'.$review->review_rating.'</span></span>  '.__('out of 5.', 'quick-business-website').'</p></blockquote>' . $review_response . '</div><hr />';

            }//  foreach ($reviews as $review)

                $reviews_content .= '<span itemprop="aggregateRating" itemscope itemtype="http://schema.org/AggregateRating" id="hreview-smar-aggregate">'. __('Average rating:', 'quick-business-website').' <span itemprop="ratingValue">' . $average_score . '</span> '. __('out of', 'quick-business-website').' <span itemprop="bestRating">' . $best_score . ' </span> '. __('based on', 'quick-business-website').' <span itemprop="reviewCount">' . $this->got_aggregate["total"] . '</span> ';

					if($this->got_aggregate["total"] == 1)
					    $basedon = __('review.', 'quick-business-website');
					else
					    $basedon = __('reviews.', 'quick-business-website');
					$reviews_content .= sprintf(__('%s', 'quick-business-website'), $basedon). '</span>';

 // add closing wrapper div for business microdata type
			$reviews_content .= '</div><!-- for business microdata type, only if there are rev -->';

			}//if else if (count($reviews

        if (!$inside_div) {
            $reviews_content .= '</div><!-- smar_respond_1 -->';
        }
        return array($reviews_content, $total_reviews);
    }
    
    /* trims text, but does not break up a word */
    function trim_text_to_word($text,$len) {
        if(strlen($text) > $len) {
          $matches = array();
          preg_match("/^(.{1,$len})[\s]/i", $text, $matches);
          $text = $matches[0];
        }
        return $text.'... ';
    }

/**
 * If enabled in settings, create reviews page, storing page id.
 * @uses insert_post()
 */
function create_reviews_page() {
	if ( get_option( 'qbw_add_reviews' ) == 'true' ) {
		global $Quick_Business_Website;
		$Quick_Business_Website->insert_post('page', esc_sql( _x('reviews', 'page_slug', 'quick-business-website') ), 'qbw_reviews_page_id', __('Reviews', 'quick-business-website'), '[SMAR_INSERT]' );
	}

}
function do_the_content($original_content) {
        global $post;
        
        $using_shortcode_insert = false;
        if ($original_content == 'shortcode_insert') {
            $original_content = '';
            $using_shortcode_insert = true;
        }
        
        $the_content = '';
        $is_active_page = $this->is_active_page();
        
        /* return normal content if this is not an enabled page, or if this is a post not on single post view */
        if (!$is_active_page) {/* 12.13 think this is inserted into post with no wrap */
           $the_content .= $this->aggregate_footer(); /* check if we need to show something in the footer then */
            return $original_content . $the_content;
        }
        
        $the_content .= '<div id="smar_respond_1"><!-- do the content -->'; /* start the div */
        $inside_div = true;
       
        if ($this->options['form_location'] == 0) {
            $the_content .= $this->show_reviews_form();
        }

	        $ret_Arr = $this->output_reviews_show( $inside_div, $post->ID, $this->options['reviews_per_page'], -1 );

	      


        $the_content .= $ret_Arr[0];
        $total_reviews = $ret_Arr[1];
        
        $the_content .= $this->pagination($total_reviews, $this->options['reviews_per_page']);

        if ($this->options['form_location'] == 1) {
            $the_content .= $this->show_reviews_form();
        }
        
        $the_content .= $this->aggregate_footer(); /* check if we need to show something in the footer also */
        
        $the_content .= '</div><!-- do the content -->'; /* smar_respond_1 */

        $the_content = preg_replace('/\n\r|\r\n|\n|\r|\t/', '', $the_content); /* minify to prevent automatic line breaks, not removing double spaces */

        return $original_content . $the_content;
}

    function output_rating($rating, $enable_hover) {
        $out = '';
        $rating_width = 20 * $rating; /* 20% for each star if having 5 stars */
        $out .= '<div class="sp_rating">';
        if ($enable_hover) {
            $out .= '<div class="status"><div class="score"><a class="score1">1</a><a class="score2">2</a><a class="score3">3</a><a class="score4">4</a><a class="score5">5</a></div></div>';
        }

        $out .= '<div class="base"><div class="average" style="width:' . $rating_width . '%"></div></div>';
        $out .= '</div>';

        return $out;
    }

    function show_reviews_form() {
        global $post, $current_user;

        $fields = '';
        $out = '';
        $req_js = "<script type='text/javascript'>";

        if ( isset($_COOKIE['smar_status_msg']) ) {
            $this->status_msg = $_COOKIE['smar_status_msg'];
        }
        
        if ($this->status_msg != '') {
            $req_js .= "smar_del_cookie('smar_status_msg');";
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
            $fields .= '<tr><td><label for="' . $rand_prefixes[0] . '-fname" class="comment-field">'. __('Name:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[0] . '-fname" name="' . $rand_prefixes[0] . '-fname" value="' . $this->p->fname . '" /></td></tr>';
        }
        if ($this->options['ask_fields']['femail'] == 1) {
            if ($this->options['require_fields']['femail'] == 1) {
                $req = '*';
            } else {
                $req = '';
            }
            $fields .= '<tr><td><label for="' . $rand_prefixes[1] . '-femail" class="comment-field">'. __('Email:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[1] . '-femail" name="' . $rand_prefixes[1] . '-femail" value="' . $this->p->femail . '" /></td></tr>';
        }
        if ($this->options['ask_fields']['fwebsite'] == 1) {
            if ($this->options['require_fields']['fwebsite'] == 1) {
                $req = '*';
            } else {
                $req = '';
            }
            $fields .= '<tr><td><label for="' . $rand_prefixes[2] . '-fwebsite" class="comment-field">'. __('Website:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[2] . '-fwebsite" name="' . $rand_prefixes[2] . '-fwebsite" value="' . $this->p->fwebsite . '" /></td></tr>';
        }
        if ($this->options['ask_fields']['ftitle'] == 1) {
            if ($this->options['require_fields']['ftitle'] == 1) {
                $req = '*';
            } else {
                $req = '';
            }
            $fields .= '<tr><td><label for="' . $rand_prefixes[3] . '-ftitle" class="comment-field">'. __('Review Title:', 'quick-business-website').' ' . $req . '</label></td><td><input class="text-input" type="text" id="' . $rand_prefixes[3] . '-ftitle" name="' . $rand_prefixes[3] . '-ftitle" maxlength="150" value="' . $this->p->ftitle . '" /></td></tr>';
        }

        $custom_fields = array(); /* used for insert as well */
        $custom_count = count($this->options['field_custom']); /* used for insert as well */
        for ($i = 0; $i < $custom_count; $i++) {
            $custom_fields[$i] = $this->options['field_custom'][$i];
        }

        foreach ($this->options['ask_custom'] as $i => $val) {
            if ( isset($this->options['ask_custom'][$i]) ) {
                if ($val == 1) {
                    if ($this->options['require_custom'][$i] == 1) {
                        $req = '*';
                    } else {
                        $req = '';
                    }

                    $custom_i = "custom_$i";
                    if (!isset($this->p->$custom_i)) { $this->p->$custom_i = ''; }
                    $fields .= '<tr><td><label for="custom_' . $i . '" class="comment-field">' . $custom_fields[$i] . ': ' . $req . '</label></td><td><input class="text-input" type="text" id="custom_' . $i . '" name="custom_' . $i . '" maxlength="150" value="' . $this->p->$custom_i . '" /></td></tr>';
                }
            } 
        }

        $some_required = '';
        
        foreach ($this->options['require_fields'] as $col => $val) {
            if ($val == 1) {
                $col = str_replace("'","\'",$col);
                $req_js .= "smar_req.push('$col');";
                $some_required = '<small>* '. __('Required Field', 'quick-business-website').'</small>';
            }
        }

        foreach ($this->options['require_custom'] as $i => $val) {
            if ($val == 1) {
                $req_js .= "smar_req.push('custom_$i');";
                $some_required = '<small>* '. __('Required Field', 'quick-business-website').'</small>';
            }
        }
        
        $req_js .= "</script>\n";
        
        if ($this->options['goto_show_button'] == 1) {
            $button_html = '<div class="smar_status_msg">' . $this->status_msg . '</div>'; /* show errors or thank you message here */
            $button_html .= '<p><a id="smar_button_1" href="javascript:void(0);">' . $this->options['goto_leave_text'] . '</a></p>';
            $out .= $button_html;
        }

        /* different output variables make it easier to debug this section */
        $out .= '<div id="smar_respond_2">' . $req_js . '
                    <form class="smarcform" id="smar_commentform" method="post" action="javascript:void(0);">
                        <div id="smar_div_2">
                            <input type="hidden" id="frating" name="frating" />
                            <table id="smar_table_2">
                                <tbody>
                                    <tr><td colspan="2"><div id="smar_postcomment">' . $this->options["leave_text"] . '</div></td></tr>
                                    ' . $fields;

        $out2 = '   
            <tr>
                <td><label class="comment-field">'. __('Rating:', 'quick-business-website').'</label></td>
                <td><div class="smar_rating">' . $this->output_rating(0, true) . '</div></td>
            </tr>';

        $out3 = '
                            <tr><td colspan="2"><label for="' . $rand_prefixes[5] . '-ftext" class="comment-field">'. __('Review:', 'quick-business-website').'</label></td></tr>
                            <tr><td colspan="2"><textarea id="' . $rand_prefixes[5] . '-ftext" name="' . $rand_prefixes[5] . '-ftext" rows="8" cols="50">' . $this->p->ftext . '</textarea></td></tr>
                            <tr>
                                <td colspan="2" id="smar_check_confirm">
                                    ' . $some_required . '
                                    <div class="smar_clear"></div>    
                                    <input type="checkbox" name="' . $rand_prefixes[6] . '-fconfirm1" id="fconfirm1" value="1" />
                                    <div class="smar_fl"><input type="checkbox" name="' . $rand_prefixes[7] . '-fconfirm2" id="fconfirm2" value="1" /></div><div class="smar_fl" style="margin:-2px 0px 0px 5px"><label for="fconfirm2">'. __('Check this box to confirm you are human.', 'quick-business-website').'</label></div>
                                    <div class="smar_clear"></div>
                                    <input type="checkbox" name="' . $rand_prefixes[8] . '-fconfirm3" id="fconfirm3" value="1" />
                                </td>
                            </tr>
                            <tr><td colspan="2"><input id="smar_submit_btn" name="submitsmar_' . $post->ID . '" type="submit" value="' . $this->options['submit_button_text'] . '" /></td></tr>
                        </tbody>
                    </table>
                </div>
            </form>';

        $out4 = '<hr /></div>';
        $out4 .= '<div class="smar_clear smar_pb5"></div>';

        return $out . $out2 . $out3 . $out4;
    }

    function add_review($pageID) {
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
        $ip = $_SERVER['REMOTE_ADDR'];
        
        if (!isset($this->p->fname)) { $this->p->fname = ''; }
        if (!isset($this->p->femail)) { $this->p->femail = ''; }
        if (!isset($this->p->fwebsite)) { $this->p->fwebsite = ''; }
        if (!isset($this->p->ftitle)) { $this->p->ftitle = ''; }
        if (!isset($this->p->ftext)) { $this->p->ftext = ''; }
        if (!isset($this->p->femail)) { $this->p->femail = ''; }
        if (!isset($this->p->fwebsite)) { $this->p->fwebsite = ''; }
        if (!isset($this->p->frating)) { $this->p->frating = 0; } /* default to 0 */
        if (!isset($this->p->fconfirm1)) { $this->p->fconfirm1 = 0; } /* default to 0 */
        if (!isset($this->p->fconfirm2)) { $this->p->fconfirm2 = 0; } /* default to 0 */
        if (!isset($this->p->fconfirm3)) { $this->p->fconfirm3 = 0; } /* default to 0 */
        
        $this->p->fname = trim(strip_tags($this->p->fname));
        $this->p->femail = trim(strip_tags($this->p->femail));
        $this->p->ftitle = trim(strip_tags($this->p->ftitle));
        $this->p->ftext = trim(strip_tags($this->p->ftext));
        $this->p->frating = intval($this->p->frating);

        /* begin - server-side validation */
        $errors = '';

        foreach ($this->options['require_fields'] as $col => $val) {
            if ($val == 1) {
                if (!isset($this->p->$col) || $this->p->$col == '') {
                    $nice_name = ucfirst(substr($col, 1));
                    $errors .= __('You must include your', 'quick-business-website').' ' . $nice_name . '.<br />';
                }
            }
        }

        $custom_fields = array(); /* used for insert as well */
        $custom_count = count($this->options['field_custom']); /* used for insert as well */
        for ($i = 0; $i < $custom_count; $i++) {
            $custom_fields[$i] = $this->options['field_custom'][$i];
        }

        foreach ($this->options['require_custom'] as $i => $val) {
            if ($val == 1) {
                $custom_i = "custom_$i";
                if (!isset($this->p->$custom_i) || $this->p->$custom_i == '') {
                    $nice_name = $custom_fields[$i];
                    $errors .= __('You must include your', 'quick-business-website').' ' . $nice_name . '.<br />';
                }
            }
        }
        
        /* only do regex matching if not blank */
        if ($this->p->femail != '' && $this->options['ask_fields']['femail'] == 1) {
            if (!preg_match('/^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/', $this->p->femail)) {
                $errors .= __('The email address provided is not valid.', 'quick-business-website').'<br />';
            }
        }

        /* only do regex matching if not blank */
        if ($this->p->fwebsite != '' && $this->options['ask_fields']['fwebsite'] == 1) {
            if (!preg_match('/^\S+:\/\/\S+\.\S+.+$/', $this->p->fwebsite)) {
                $errors .= __('The website provided is not valid. Be sure to include http://', 'quick-business-website').'<br />';
            }
        }

        if (intval($this->p->fconfirm1) == 1 || intval($this->p->fconfirm3) == 1) {
            $errors .= __('You have triggered our anti-spam system. Please try again. Code 001.', 'quick-business-website').'<br />';
        }

        if (intval($this->p->fconfirm2) != 1) {
            $errors .= __('You have triggered our anti-spam system. Please try again. Code 002', 'quick-business-website').'<br />';
        }

        if ($this->p->frating < 1 || $this->p->frating > 5) {
            $errors .= __('You have triggered our anti-spam system. Please try again. Code 003', 'quick-business-website').'<br />';
        }

       if (strlen(trim($this->p->ftext)) < 5) {
            $errors .= __('You must include a review. Please make reviews at least 5 letters.', 'quick-business-website').'<br />';
        }

        /* returns true for errors */
        if ($errors) {
            return array(true, "<div>$errors</div>");
        }
        /* end - server-side validation */

        $custom_insert = array();

		 $this->options['ask_custom'] = array(0, 1, 2, 3, 4, 5);
		
        for ($i = 0; $i < $custom_count; $i++) {		
            if ($this->options['ask_custom'][$i] == 1) {
                $name = $custom_fields[$i];
                $custom_i = "custom_$i";				
                if ( isset($this->p->$custom_i) ) {
                    $custom_insert[$name] = ucfirst($this->p->$custom_i);
                }
            }
        }
        $custom_insert = serialize($custom_insert);
        $query = $wpdb->prepare("INSERT INTO `$this->dbtable` 
                (`date_time`, `reviewer_name`, `reviewer_email`, `reviewer_ip`, `review_title`, `review_text`, `status`, `review_rating`, `reviewer_url`, `custom_fields`, `page_id`) 
                VALUES (%s, %s, %s, %s, %s, %s, %d, %d, %s, %s, %d)", $date_time, $this->p->fname, $this->p->femail, $ip, $this->p->ftitle, $this->p->ftext, 0, $this->p->frating, $this->p->fwebsite, $custom_insert, $pageID);

        $wpdb->query($query);

		$qbw_options = get_option( 'qbw_options');
		$bn = stripslashes_deep($qbw_options['qbw_business_name']);if(!$bn) {$bn = get_bloginfo('name'); }
        $admin_linkpre = get_admin_url().'admin.php?page=smar_view_reviews';
        $admin_link = sprintf(__('Link to admin approval page: %s', 'quick-business-website'), $admin_linkpre);
		$ac = sprintf(__('A new review has been posted on %1$s\'s website.','quick-business-website'),$bn) . "\n\n" .
	__('You will need to login to the admin area and approve this review before it will appear on your site.','quick-business-website') . "\n\n" .$admin_link;

        @wp_mail(get_bloginfo('admin_email'), $bn.': '. sprintf(__('New Review Posted on %1$s', 'quick-business-website'), 
								date('m/d/Y h:i e') ), $ac );

        /* returns false for no error */
        return array(false, '<div>'.__('Thank you for your comments. All submissions are moderated and if approved, yours will appear soon.', 'quick-business-website').'</div>');
    }
    function smar_redirect($url, $cookie = array()) {
        $headers_sent = headers_sent();
        if ($headers_sent == true) {
            /* use JS redirect and add cookie before redirect */
            /* we do not html comment script blocks here - to prevent any issues with other plugins adding content to newlines, etc */
            $out = '<html><head><title>'.__('Redirecting', 'quick-business-website').'...</title></head><body><div style="clear:both;text-align:center;padding:10px;">' .
                    __('Processing... Please wait...', 'quick-business-website') .
                    '<script type="text/javascript">';
            foreach ($cookie as $col => $val) {
                $val = preg_replace("/\r?\n/", "\\n", addslashes($val));
                $out .= "document.cookie=\"$col=$val\";";
            }
            $out .= "window.location='$url';";
            $out .= "</script>";
            $out .= "</div></body></html>";
            echo $out;
        } else {
            foreach ($cookie as $col => $val) {
                setcookie($col, $val); /* add cookie via headers */
            }
		if (ob_get_length()) ob_end_clean();
            wp_redirect($url); /* nice redirect */
        }
        
        exit();
    }
    function init() { /* used for admin_init also */
        $this->make_p_obj(); /* make P variables object */
        $this->get_options(); /* populate the options array */
        $this->check_migrate(); /* call on every instance to see if we have upgraded in any way */

        if ( !isset($this->p->smarp) ) { $this->p->smarp = 1; }
        
        $this->page = intval($this->p->smarp);
        if ($this->page < 1) { $this->page = 1; }
        
        add_shortcode( 'SMAR_INSERT', array(&$this, 'shortcode_smar_insert') );
    }
    function shortcode_smar_insert() {
        $this->force_active_page = 1;
        return $this->do_the_content('shortcode_insert');        
    }

	function enqueue_scripts() {
		if( get_option( 'qbw_add_reviews') == 'true'  ) { // isa depend
			wp_register_style('smartest-reviews', $this->get_reviews_module_url() . 'smartest-reviews.css', array(), $this->plugin_version);
			wp_register_script('smartest-reviews', $this->get_reviews_module_url() . 'smartest-reviews.js', array('jquery'), $this->plugin_version);

			if( is_page(get_option( 'qbw_reviews_page_id' ))) {
			
				wp_enqueue_style('smartest-reviews');
		        wp_enqueue_script('smartest-reviews');
				$loc = array(
					'hidebutton' => __('Click here to hide form', 'quick-business-website'),
					'email' => __('The email address provided is not valid.', 'quick-business-website'),
					'name' => __('You must include your ', 'quick-business-website'),
					'review' => __('You must include a review. Please make reviews at least 4 letters.', 'quick-business-website'),
					'human' => __('You must confirm that you are human.', 'quick-business-website'),
					'code2' => __('Code 2.', 'quick-business-website'),
					'code3' => __('Code 3.', 'quick-business-website'),
					'rating' => __('Please select a star rating from 1 to 5.', 'quick-business-website'),
					'website' => __('The website provided is not valid. Be sure to include', 'quick-business-website')
					);
				wp_localize_script( 'smartest-reviews', 'smartlocal', $loc);
			}

		}
	}
	/**
	 * widget
	 */
	function register_widget() {
		if( get_option( 'qbw_add_reviews') == 'true'  ) {
			register_widget('SmartestReviewsTestimonial');
		}
	}
    function include_admin() {
        global $SMARTESTReviewsBusinessAdmin;
		require_once QUICKBUSINESSWEBSITE_PATH . 'modules/smartest-reviews/smartest-reviews-admin.php';
    }
    function admin_init() {
        global $SMARTESTReviewsBusinessAdmin;
        $this->include_admin(); /* include admin functions */
        $SMARTESTReviewsBusinessAdmin->real_admin_init();
    }
	function admin_scripts() {
		global $SMARTESTReviewsBusinessAdmin;
        $SMARTESTReviewsBusinessAdmin->enqueue_admin_stuff();
	}
	function get_reviews_module_url() {
        return QUICKBUSINESSWEBSITE_URL . 'modules/smartest-reviews/';
    }
}
if (!defined('IN_SMAR')) {global $SMARTESTReviewsBusiness;
$SMARTESTReviewsBusiness = new SMARTESTReviewsBusiness();
add_action ('after_setup_theme', array(&$SMARTESTReviewsBusiness,'activate'));
}
/* get widget */
include_once('widget-testimonial.php');
?>