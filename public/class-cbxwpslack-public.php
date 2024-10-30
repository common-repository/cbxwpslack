<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       info@codeboxr.com
 * @since      1.0.0
 *
 * @package    Cbxwpslack
 * @subpackage Cbxwpslack/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Cbxwpslack
 * @subpackage Cbxwpslack/public
 * @author     Codeboxr <info@codeboxr.com>
 */
class Cbxwpslack_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {



		wp_enqueue_style( 'cbxwpslack-public', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpslack-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {



		wp_enqueue_script( 'cbxwpslack-public', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpslack-public.js', array( 'jquery' ), $this->version, false );



	}

	/**
	 * Check the cbxwpslackout outgoing hook response
	 */
	public function template_redirect(){

		/*
		 *
		//debug data

		$myfile = fopen("newfile.txt", "w") or die("Unable to open file!");
		fwrite($myfile, print_r($_POST, true));
		fclose($myfile);

		Array
		(
			[token] => dSpxMroKXmtTlMUqPDfu3tgK
			[team_id] => T0MT1FUU9
		    [team_domain] => cbxwpslack
		    [service_id] => 39997520567
            [channel_id] => C0MSYEMB7
		    [channel_name] => general
		    [timestamp] => 1462859520.000064
            [user_id] => U0MT1RMA6
		    [user_name] => ahmedsajjad724
		    [text] => search sabuj
		    [trigger_word] => search
        )
		*/


		$cbxwpslackoutapp = (isset($_GET['cbxwpslackoutapp']) && isset($_GET['token']) && intval($_GET['cbxwpslackoutapp']) == 1 && $_GET['token'] != '' )? true: false;


		if($cbxwpslackoutapp){

			$token 			= esc_attr($_POST['token']);
			$team_id 		= esc_attr($_POST['team_id']);
			$team_domain 	= esc_attr($_POST['team_domain']);
			$service_id 	= intval($_POST['service_id']);
			$channel_name 	= esc_attr($_POST['channel_name']);
			$timestamp 		= esc_attr($_POST['timestamp']);
			$user_name 		= esc_attr($_POST['user_name']);
			$text 			= esc_attr($_POST['text']);
			$trigger_word   = esc_attr($_POST['trigger_word']);


			$text_array = explode(" ",$text);
			if(!isset($text_array[1])){
				$return["text"] = __('Search keyword missing', 'cbxwpslack');
				echo  wp_json_encode($return);
				die();
			}else{
				unset($text_array[0]);
				$keyword = implode(' ',$text_array);
			}



			if(isset($token) && $token != null && isset($team_id) && $team_id != null && isset($team_domain) && $team_domain != null && isset($service_id) && $service_id != null && isset($channel_name) && $channel_name != null && isset($timestamp) && $timestamp != null && isset($user_name) && $user_name != null && isset($text) && $text != null && isset($trigger_word) && $trigger_word != null)
				$args = array (
					'post_type'              => array( 'cbxwpslackout' ),
					'post_status'            => array( 'published' ),
					'posts_per_page'         => 1,
					'meta_query'             => array(
						'relation' 		=> 'AND',
						array(
							'key'       => '_cbxwpslackout_channel',
							'value'     => '#'.$channel_name,
							'compare'   => '=',
							'type'      => 'CHAR',
						),
						array(
							'key'       => '_cbxwpslackout_triggerword',
							'value'     => $trigger_word,
							'compare'   => 'LIKE',
							'type'      => 'CHAR',
						),
						array(
							'key'       => '_cbxwpslackout_teamdomain',
							'value'     => $team_domain,
							'compare'   => '=',
							'type'      => 'CHAR',
						),
						array(
							'key'       => '_cbxwpslackout_token',
							'value'     => $token,
							'compare'   => '=',
							'type'      => 'CHAR',
						)
					),
				);


			$query = new WP_Query( $args );


			$cbxwpslack_admin = new CBXWPSslack_Admin($this->plugin_name, $this->version);

			$all_defined_events = $cbxwpslack_admin->cbxwpslackout_events(); //holds all out event types and their title, class/object and method to call for action

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID(); //got a slack outgoing type post

					//now check the event type and move forward
					$post_meta = get_post_meta($post_id,'_cbxwpslackout',true);

					//check if posttype enale or not
					if($post_meta['enableout'] == 0 ){
						continue;
					}

					if(isset($post_meta['event']) && $post_meta['event'] != '' && isset($all_defined_events[$post_meta['event']]) ){

						$event 			= $post_meta['event'];
						$event_info  	= $all_defined_events[$event];
						$object 		= isset($event_info['class']) ? $event_info['class']: '';
						$method 		= isset($event_info['class']) ? $event_info['method']: '';
						if($object != '' && $method != '' && is_callable(array($object, $method))){
							call_user_func_array(array($object, $method), array($keyword, $post_meta));
						}
					}

				}
			}

			//$this->cbxwpslack_outgoing_post_search($keyword, $post_meta);


			wp_reset_postdata();

			die();

		}
	}


	/**
	 * Outgoing event method for post searchs
	 *
	 * @param $keyword
	 * @param $posttypes
	 */
	public function cbxwpslack_outgoing_post_search($keyword, $post_meta){

		$settings_api = new CBXWPSlack_Settings_API();
		$number_of_itemstoshow = $settings_api->get_option('cbxwpslack_show_searchnumber','cbxwpslackout_general',5);

		$post_types = isset($post_meta['posttypes']) ? $post_meta['posttypes']: array();

		if(sizeof($post_types) == 0) return '';

		$args = array(
			'post_type'              => $post_types,
			's' 		             => $keyword,
			'posts_per_page'         => intval($number_of_itemstoshow),
		);


		$query = new WP_Query( $args );

		$attachments = array();
		$return["text"] = sprintf(__('Results found %s', 'cbxwpslack'), $query->found_posts);

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$attachments[] = apply_filters('cbxwpslackout_attachments', array(
					"fallback" 		=> get_the_title(),
					"title" 		=> get_the_title(),
					"title_link" 	=> get_permalink(),
				),$post_types);

			}
		}

		wp_reset_postdata();

		$return["attachments"] = $attachments;

		echo  wp_json_encode($return);

	}

}
