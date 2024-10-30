<?php

	/**
	 * The admin-specific functionality of the plugin.
	 *
	 * @link       info@codeboxr.com
	 * @since      1.0.0
	 *
	 * @package    Cbxwpslack
	 * @subpackage Cbxwpslack/admin
	 */
	class CBXWPSslack_Admin {

		/**
		 * The ID of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $plugin_name The ID of this plugin.
		 */
		private $plugin_name;

		/**
		 * The plugin basename of the plugin.
		 *
		 * @since    1.0.0
		 * @access   protected
		 * @var      string $plugin_basename The plugin basename of the plugin.
		 */
		protected $plugin_basename;

		/**
		 * The version of this plugin.
		 *
		 * @since    1.0.0
		 * @access   private
		 * @var      string $version The current version of this plugin.
		 */
		private $version;

		/**
		 * Initialize the class and set its properties.
		 *
		 * @since    1.0.0
		 *
		 * @param      string $plugin_name The name of this plugin.
		 * @param      string $version     The version of this plugin.
		 */
		public function __construct( $plugin_name, $version ) {

			$this->plugin_name = $plugin_name;
			$this->version     = $version;

			$this->plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $plugin_name . '.php' );

			$this->settings_api = new CBXWPSlack_Settings_API();

		}

		/**
		 * Ajax for sending test notification
		 */
		public function cbxwpslack_test_notification() {

			check_ajax_referer( 'cbxwpslack', 'security' );
			$message    = ( isset( $_POST['message'] ) && $_POST['message'] != null ) ? $_POST['message'] : '';
			$serviceurl = ( isset( $_POST['serviceurl'] ) && $_POST['serviceurl'] != null ) ? $_POST['serviceurl'] : '';
			$channel    = ( isset( $_POST['channel'] ) && $_POST['channel'] != null ) ? $_POST['channel'] : '';
			$username   = ( isset( $_POST['username'] ) && $_POST['username'] != null ) ? $_POST['username'] : '';
			$iconemoji  = ( isset( $_POST['iconemoji'] ) && $_POST['iconemoji'] != null ) ? $_POST['iconemoji'] : '';

			cbxwp_post_to_slack( $message, $serviceurl, $channel, $username, $iconemoji );
		}

		public function setting_init() {
			//set the settings
			$this->settings_api->set_sections( $this->get_settings_sections() );
			$this->settings_api->set_fields( $this->get_settings_fields() );
			//initialize settings
			$this->settings_api->admin_init();

		}

		public function get_settings_sections() {
			$sections = array(
				array(
					'id'    => 'cbxwpslack_integrations',
					'title' => esc_html__( 'Custom Integrations', 'cbxwpslack' )
				),
				array(
					'id'    => 'cbxwpslackout_general',
					'title' => esc_html__( 'Slack Out General Setting', 'cbxwpslack' )
				)

			);

			$sections = apply_filters( 'cbxwpslack_sections', $sections );

			return $sections;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		public function get_settings_fields() {

			$settings_fields = array(
				'cbxwpslackout_general' => array(
					array(
						'name'     => 'cbxwpslack_show_searchnumber',
						'label'    => esc_html__( 'Number of items', 'cbxwpslack' ),
						'desc'     => esc_html__( 'Number of items to show on search response.', 'cbxwpslack' ),
						'type'     => 'number',
						'default'  => '5',
						'desc_tip' => true,
					)
				),
				'cbxwpslack_integrations' =>  array(

				)

			);

			$settings_fields = apply_filters( 'cbxwpslack_fields', $settings_fields );

			return $settings_fields;
		}


		/**
		 * Add Setting menu
		 */
		public function admin_menu_slack_setting() {
			$this->plugin_screen_hook_suffix_settings = add_submenu_page(
				'edit.php?post_type=cbxwpslack', esc_html__( 'CBX WP Slack Setting', 'cbxwpslack' ), esc_html__( 'Setting', 'cbxwpslack' ), 'manage_options', 'cbxwpslacksetting', array(
				$this,
				'display_plugin_admin_settings'
			) );
		}

		public function display_plugin_admin_settings() {
			global $wpdb;

			$plugin_data = get_plugin_data( plugin_dir_path( __DIR__ ) . '/../' . $this->plugin_basename );

			include( 'partials/admin-settings-display.php' );
		}

		/**
		 * Slack Out Enable/Disable
		 */
		public function cbxwpslackout_enable_disable() {

			check_ajax_referer( 'cbxwpslack', 'security' );

			$enable      = ( isset( $_POST['enableout'] ) && $_POST['enableout'] != null ) ? intval( $_POST['enableout'] ) : 0;
			$post_id     = ( isset( $_POST['postid'] ) && $_POST['postid'] != null ) ? intval( $_POST['postid'] ) : 0;
			$fieldValues = get_post_meta( $post_id, '_cbxwpslackout', true );
			if ( $post_id > 0 ) {

				$fieldValues['enableout'] = $enable;

				update_post_meta( $post_id, '_cbxwpslackout', $fieldValues );
			}

			echo $enable;

			wp_die();
		}

		/**
		 * Slack In Enable/Disable
		 */
		public function cbxwpslack_enable_disable() {

			check_ajax_referer( 'cbxwpslack', 'security' );


			$enable      = ( isset( $_POST['enable'] ) && $_POST['enable'] != null ) ? intval( $_POST['enable'] ) : 0;
			$post_id     = ( isset( $_POST['postid'] ) && $_POST['postid'] != null ) ? intval( $_POST['postid'] ) : 0;
			$fieldValues = get_post_meta( $post_id, '_cbxwpslack', true );
			if ( $post_id > 0 ) {
				$fieldValues['enable'] = $enable;

				update_post_meta( $post_id, '_cbxwpslack', $fieldValues );
			}

			echo $enable;

			wp_die();
		}


		/**
		 * Defination of all slack out events
		 *
		 * @return array
		 */
		public function cbxwpslackout_events() {
			$events = array(
				'cbxpostsearch' => array(
					'title'  => __( 'Post Search', 'cbxwpslack' ),
					'class'  => new Cbxwpslack_Public( $this->plugin_name, $this->version ),
					'method' => 'cbxwpslack_outgoing_post_search'
				),
			);


			return apply_filters( 'cbxwpslackout_events', $events );
		}

		/**
		 * Store all enents/hooks to post on slack.
		 *
		 * @return mixed|void
		 */
		public function cbxwpslack_events() {

			$events = array(
				'post_published'     => array(
					'title'         => esc_html__( 'Core: When a post is published', 'cbxwpslack' ),
					//will be shown in post edit screen
					'hook'          => 'publish_post',
					//as defined by wordpress
					'accepted_args' => 2,
					//
					'priority'      => 10,
					//if not used then we will use 10
					'category'      => array(
						'section' => 'general',
						'title'   => esc_html__( 'General', 'cbxwpslack' )
					),
					'message'       => function ( $ID, $post ) {
						$author_id = $post->post_author;
						$author    = get_user_by( 'ID', $author_id );

						$author_name = $author->display_name;
						$author_url  = get_edit_user_link( $author_id );

						$title     = $post->post_title;
						$permalink = get_permalink( $ID );

						$message = sprintf( __( 'Post "<%s|%s>" written by User <%s|%s>', 'cbxwpslack' ), $permalink, $title, $author_url, $author_name );

						return $message;

					}
				),
				'post_deleted'       => array(
					'title'         => esc_html__( 'Core: When a post is trashed', 'cbxwpslack' ),
					//will be shown in post edit screen
					'hook'          => 'wp_trash_post',
					//action name
					'accepted_args' => 1,
					//
					'priority'      => 10,
					//if not used then we will use 10
					'category'      => array(
						'section' => 'general',
						'title'   => esc_html__( 'General', 'cbxwpslack' )
					),
					'message'       => function ( $ID ) {
						$permalink = get_permalink( $ID );
						$message   = sprintf( __( 'Post "<%s|%s>" has been trashed.', 'cbxwpslack' ), $permalink, get_the_title( $ID ) );

						return $message;
					}
				),
				'user_register'      => array(
					'title'         => esc_html__( 'Core: New User Registration', 'cbxwpslack' ),
					//will be shown in post edit screen
					'hook'          => 'user_register',
					//action name
					'accepted_args' => 1,
					//
					'priority'      => 10,
					//if not used then we will use 10
					'category'      => array(
						'section' => 'general',
						'title'   => esc_html__( 'General', 'cbxwpslack' )
					),
					'message'       => function ( $user_id ) {
						$user           = get_userdata( $user_id );
						$edit_user_link = get_edit_user_link( $user_id );
						$message        = sprintf( __( 'New User "<%s|%s>" has registered', 'cbxwpslack' ), $edit_user_link, $user->first_name . ' ' . $user->last_name );

						return $message;
					}
				),
				'buddypress_newpost' => array(
					'title'         => esc_html__( 'Buddpress: New Status', 'cbxwpslack' ),
					//will be shown in post edit screen
					'hook'          => 'bp_activity_add',
					//action name ///bp-activity/bp-activity-functions.php :: Trac Source Line: 827
					'accepted_args' => 1,
					//
					'priority'      => 10,
					//if not used then we will use 10
					'category'      => array(
						'section' => 'communication',
						'title'   => esc_html__( 'Communication', 'cbxwpslack' )
					),
					'message'       => function ( $r ) {
						$user = get_userdata( $r['user_id'] );
						//$edit_user_link = get_edit_user_link( $user_id );
						$message = sprintf( __( 'Buddypress New Status: %s -- by  "<%s|%s>"', 'cbxwpslack' ), $r['content'], $r['primary_link'], $user->first_name . ' ' . $user->last_name );

						return $message;
					}
				),
				'bbpress_newtopic'   => array(
					'title'         => esc_html__( 'bbPress: New Topic', 'cbxwpslack' ),
					//will be shown in post edit screen
					'hook'          => 'bbp_new_topic',
					'accepted_args' => 5,
					//
					'priority'      => 10,
					//if not used then we will use 10
					'category'      => array(
						'section' => 'communication',
						'title'   => __( 'Communication', 'cbxwpslack' )
					),
					'message'       => function ( $topic_id = 0, $forum_id = 0, $anonymous_data = false, $author_id = 0, $is_edit = false ) {

						$post = get_post( $topic_id );

						$type      = $post->post_type;
						$permalink = get_permalink( $topic_id );
						$user      = get_userdata( $author_id );

						//$edit_user_link = bbp_user_profile_url( bbp_get_current_user_id() );

						$message = sprintf( __( 'bbPRess New Topic: "<%s|%s>" -- by  %s', 'cbxwpslack' ), $permalink, $post->post_title, $user->first_name . ' ' . $user->last_name );

						return $message;
					}
				),
				/* 'cf7formsubmit'   => array(
					 'title'         => __('Contactform 7 Form Submit', 'cbxwpslack'), //will be shown in post edit screen
					 'hook'          => 'wpcf7_submit', //action name
					 'accepted_args' => 2, //
					 'priority'      => 10, //if not used then we will use 10
					 'category'      => array(
						 'section' => 'forms',
						 'title'   => __('Forms', 'cbxwpslack')
					 ),
					 'message'       => function ($form_obj, $result) {
						 $user = get_userdata( $user_id );
						 $edit_user_link = get_edit_user_link( $user_id );
						 $message = sprintf(__('New User "<%s|%s>" has registered', 'cbxwpslack'), $edit_user_link, $user->first_name.' '. $user->last_name);
						 return $message;
					 }
				 ),*/

			);

			return apply_filters( 'cbxwpslack_events', $events );

		}


		/**
		 * Add Action for calling Manage events
		 */
		public function call_cbxwpslack() {

			$this->manage_events();
		}

		/**
		 * Handle/Manage all events/hooks thats are stored to post on slack.
		 */
		public function manage_events() {

			global $post;


			$posts_per_page = 5;
			$posts_per_page = apply_filters( 'cbxwpslack_count', $posts_per_page );


			$cbxwpslack_events = $this->cbxwpslack_events();

			$all_events = get_posts( array(
				'post_type'      => 'cbxwpslack',
				'nopaging'       => true,
				'posts_per_page' => $posts_per_page,
			) );

			foreach ( $all_events as $event ) {
				//for each slack
				$setting = get_post_meta( $event->ID, '_cbxwpslack', true );

				$enable = isset( $setting['enable'] ) ? intval( $setting['enable'] ) : 0;

				if ( isset( $setting['event'] ) && $setting['event'] != null && $enable ) {

					foreach ( $setting['event'] as $targetted_hook => $value ) {
						if ( $value == 'on' ) {
							$message    = isset( $cbxwpslack_events[ $targetted_hook ]['message'] ) ? $cbxwpslack_events[ $targetted_hook ]['message'] : ''; //it could be a dynamic anon function or  method or string
							$priority   = isset( $cbxwpslack_events[ $targetted_hook ]['priority'] ) ? intval( $cbxwpslack_events[ $targetted_hook ]['priority'] ) : 10;
							$arg_number = isset( $cbxwpslack_events[ $targetted_hook ]['accepted_args'] ) ? intval( $cbxwpslack_events[ $targetted_hook ]['accepted_args'] ) : 1;
							$obj        = $this;

							$hook_callback = function () use ( $setting, $message, $obj ) {

								if ( is_callable( $message ) ) {
									//for function
									$msg = call_user_func_array( $message, func_get_args() );
								} elseif ( is_string( $message ) ) {
									//for string
									$msg = $message;
								} else {
									$msg = '';
								}
								//send notification
								cbxwp_post_to_slack( $msg, $setting['serviceurl'], $setting['channel'], $setting['username'], $setting['iconemoji'] );
							};

							//var_dump($cbxwpslack_events[$targetted_hook]['hook']);
							if ( isset( $cbxwpslack_events[ $targetted_hook ]['hook'] ) ) {
								add_action( $cbxwpslack_events[ $targetted_hook ]['hook'], $hook_callback, $priority, $arg_number );
							}

						}
					}
				}
			}

			wp_reset_postdata();
		}

		/**
		 * Register the stylesheets for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_styles( $hook ) {


			$screen = get_current_screen();
			wp_register_style( 'chosen-min', plugin_dir_url( __FILE__ ) . '../assets/css/chosen.min.css', array(), $this->version, 'all' );
			wp_register_style( 'switchery-min', plugin_dir_url( __FILE__ ) . '../assets/css/switchery.min.css', array(), $this->version, 'all' );

			wp_register_style( 'cbxwpslack-admin', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpslack-admin.css', array(), $this->version, 'all' );


			if ( $screen->id == 'cbxwpslack' ) {
				wp_enqueue_style( 'cbxwpslack-admin' );
			}

			global $post_type;

			if ( $hook == 'edit.php' && ( 'cbxwpslack' == $post_type || 'cbxwpslackout' == $post_type ) ) {

				wp_enqueue_style( 'switchery-min' );
			}

			if ( ( $post_type == 'cbxwpslackout' || $post_type == 'cbxwpslack' ) && ( $hook == 'post.php' || $hook == 'post-new.php' ) ) {
				wp_enqueue_style( 'switchery-min' );
			}

		}

		/**
		 * Register the JavaScript for the admin area.
		 *
		 * @since    1.0.0
		 */
		public function enqueue_scripts( $hook ) {

			global $post_type;

			$page = isset( $_GET['page'] ) ? $_GET['page'] : '';

			wp_register_script( 'switchery', plugin_dir_url( __FILE__ ) . '../assets/js/switchery.js', array( 'jquery' ), $this->version, true );
			wp_register_script( 'chosen-jquery', plugin_dir_url( __FILE__ ) . '../assets/js/chosen.jquery.min.js', array( 'jquery' ), $this->version, true );
			wp_register_script( 'cbxwpslack-admin', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpslack-admin.js', array(
				'jquery',
				'switchery',
				'chosen-jquery'
			), $this->version, true );


			if ( $post_type == 'cbxwpslack' || $post_type == 'cbxwpslackout' ) {


				//wp_enqueue_script('clipboard-min');

				$cbxwpslack_translation = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'cbxwpslack' ),
					'message' => sprintf( __( 'Test Message from <%s|CBXWPslack> Plugin.', 'cbxwpslack' ), 'https://codeboxr.com/product/cbx-wp-slack' ),
					'success' => esc_html__( 'Test notification sent successfully!', 'cbxwpslack' ),
					'test_noti_noparam' => esc_html__( 'Please fill Service url, channel and username and save once, then test', 'cbxwpslack' )
				);
				wp_localize_script( 'cbxwpslack-admin', 'cbxwpslack', $cbxwpslack_translation );

				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'switchery' );
				wp_enqueue_script( 'chosen-jquery' );
				wp_enqueue_script( 'cbxwpslack-admin' );

			}

			if ( $post_type == 'cbxwpslackout' ) {


			}

			if ( $page == 'cbxwpslacksetting' ) {
				wp_register_style( 'cbxwpslack-setting', plugin_dir_url( __FILE__ ) . '../assets/css/cbxwpslack-setting.css', array(), $this->version, 'all' );
				wp_register_script( 'cbxwpslack-setting', plugin_dir_url( __FILE__ ) . '../assets/js/cbxwpslack-setting.js', array(
					'jquery',
					'wp-color-picker',
					'chosen-jquery'
				), $this->version, true );

				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_style( 'cbxwpslack-setting' );

				wp_enqueue_media();
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_script( 'chosen-jquery' );
				wp_enqueue_script( 'cbxwpslack-setting' );
			}


		}

		/**
		 * Register Custom Post Type "cbxwpslack"
		 */
		public function create_cbxwpslack() {

			$labels = array(
				'name'               => _x( 'Slacks In', 'Post Type General Name', 'cbxwpslack' ),
				'singular_name'      => _x( 'Slack In', 'Post Type Singular Name', 'cbxwpslack' ),
				'menu_name'          => __( 'CBX WPSlack', 'cbxwpslack' ),
				'parent_item_colon'  => __( 'Parent Slacks In:', 'cbxwpslack' ),
				'all_items'          => __( 'All Slacks In', 'cbxwpslack' ),
				'view_item'          => __( 'View Slack In', 'cbxwpslack' ),
				'add_new_item'       => __( 'Add New Slack In', 'cbxwpslack' ),
				'add_new'            => __( 'Add New', 'cbxwpslack' ),
				'edit_item'          => __( 'Edit Slack In', 'cbxwpslack' ),
				'update_item'        => __( 'Update Slack In', 'cbxwpslack' ),
				'search_items'       => __( 'Search Slack In', 'cbxwpslack' ),
				'not_found'          => __( 'Not found', 'cbxwpslack' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'cbxwpslack' ),
			);
			$args   = array(
				'label'               => __( 'cbxwpslack', 'cbxwpslack' ),
				'description'         => __( 'Slack Incoming', 'cbxwpslack' ),
				'labels'              => $labels,
				'supports'            => array( 'title' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				//'menu_position'       => 5,
				'menu_icon'           => 'dashicons-list-view',
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => 'post',
			);
			register_post_type( 'cbxwpslack', $args );


			$labels = array(
				'name'               => _x( 'Slacks Out', 'Post Type General Name', 'cbxwpslack' ),
				'singular_name'      => _x( 'Slack Out', 'Post Type Singular Name', 'cbxwpslack' ),
				//'menu_name'          => __('CBX WPSlack', 'cbxwpslack'),
				'parent_item_colon'  => __( 'Parent Slacks Out:', 'cbxwpslack' ),
				'all_items'          => __( 'All Slacks Out', 'cbxwpslack' ),
				'view_item'          => __( 'View Slack Out', 'cbxwpslack' ),
				'add_new_item'       => __( 'Add New Slack Out', 'cbxwpslack' ),
				'add_new'            => __( 'Add New', 'cbxwpslack' ),
				'edit_item'          => __( 'Edit Slack Out', 'cbxwpslack' ),
				'update_item'        => __( 'Update Slack Out', 'cbxwpslack' ),
				'search_items'       => __( 'Search Slack Out', 'cbxwpslack' ),
				'not_found'          => __( 'Not found', 'cbxwpslack' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'cbxwpslack' ),
			);
			$args   = array(
				'label'               => __( 'cbxwpslackout', 'cbxwpslack' ),
				'description'         => __( 'Slack Incoming', 'cbxwpslack' ),
				'labels'              => $labels,
				'supports'            => array( 'title' ),
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => 'edit.php?post_type=cbxwpslack',
				// 'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				//'menu_position'       => 5,
				'menu_icon'           => 'dashicons-list-view',
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
				'capability_type'     => 'post',
			);
			register_post_type( 'cbxwpslackout', $args );

		}

		/**
		 * @return mixed|void
		 */
		public function cbxwpslack_events_sections() {

			$sections = array(
				'general' => esc_html__( 'General Events', 'cbxwpslack' ),
			);

			return apply_filters( 'cbxwpslack_event_sections', $sections );

		}

		/**
		 * @param string $current
		 */
		public function page_sections_tab( $current = 'general' ) {
			$tabs   = array();
			$events = $this->cbxwpslack_events();

			foreach ( $events as $key => $value ) {
				if ( ! array_key_exists( $value['category']['section'], $tabs ) ) {
					$tabs[ $value['category']['section'] ] = $value['category']['title'];
				}
			}

			$html = '<h2 class="nav-tab-wrapper">';
			foreach ( $tabs as $key => $value ) {
				$class = ( $key == $current ) ? 'nav-tab-active' : '';
				$html  .= '<a class="nav-tab ' . $class . '" href="#">' . $value . '</a>';
			}
			$html .= '</h2>';
			echo $html;
		}

		/**
		 * Adding meta box under cbxslack custom post types
		 */
		public function add_meta_boxes() {

			//slack incoming metabox
			add_meta_box(
				'cbxwpslackmetabox', __( 'CBX Slack IN Settings', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetabox_display'
			), 'cbxwpslack', 'normal', 'high'
			);

			add_meta_box(
				'cbxwpslackmetaboxevents', __( 'CBX Slack Events', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetaboxevents_display'
			), 'cbxwpslack', 'normal', 'high'
			);

			add_meta_box(
				'cbxwpslackmetaboxtest', __( 'CBX Slack Test', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetaboxside_display'
			), 'cbxwpslack', 'side', 'low'
			);

			//slack outgoing metabox
			add_meta_box(
				'cbxwpslackmetaboxout', __( 'CBX Slack Out Settings', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetaboxout_display'
			), 'cbxwpslackout', 'normal', 'high'
			);

			add_meta_box(
				'cbxwpslackmetaboxoutevents', __( 'CBX Slack Out Events', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetaboxoutevents_display'
			), 'cbxwpslackout', 'normal', 'high'
			);

			add_meta_box(
				'cbxwpslackmetaboxoutposttypes', __( 'CBX Slack Out Post Types', 'cbxwpslack' ), array(
				$this,
				'cbxwpslackmetaboxoutposttypes_display'
			), 'cbxwpslackout', 'normal', 'high'
			);

		}

		/**
		 * Return post types list, if plain is true then send as plain array , else array as post type groups
		 *
		 * @param bool|false $plain
		 *
		 * @return array
		 */
		function cbxwpslackout_posttypes( $plain = false ) {
			$post_type_args = array(
				'builtin' => array(
					'options' => array(
						'public'   => true,
						'_builtin' => true,
						'show_ui'  => true,
					),
					'label'   => __( 'Built in post types', 'cbxwpslack' ),
				)
			);

			$post_type_args = apply_filters( 'cbxwpslack_post_types', $post_type_args );

			$output    = 'objects'; // names or objects, note names is the default
			$operator  = 'and'; // 'and' or 'or'
			$postTypes = array();

			foreach ( $post_type_args as $postArgType => $postArgTypeArr ) {
				$types = get_post_types( $postArgTypeArr['options'], $output, $operator );

				if ( ! empty( $types ) ) {
					foreach ( $types as $type ) {
						$postTypes[ $postArgType ]['label']                = $postArgTypeArr['label'];
						$postTypes[ $postArgType ]['types'][ $type->name ] = $type->labels->name;
					}
				}
			}


			if ( $plain ) {
				$plain_list = array();
				if ( isset( $postTypes['builtin']['types'] ) ) {

					foreach ( $postTypes['builtin']['types'] as $key => $name ) {
						$plain_list[] = $key;
					}
				}

				if ( isset( $postTypes['custom']['types'] ) ) {

					foreach ( $postTypes['custom']['types'] as $key => $name ) {
						$plain_list[] = $key;
					}
				}

				return $plain_list;
			} else {
				return $postTypes;
			}
		}

		/**
		 * @param $post
		 */
		public function cbxwpslackmetaboxoutposttypes_display( $post ) {

			$fieldValues           = get_post_meta( $post->ID, '_cbxwpslackout', true );
			$fieldValues_posttypes = array();
			if ( $fieldValues != null ) {
				$fieldValues_posttypes = $fieldValues['posttypes'];
			}


			$posttypes = $this->cbxwpslackout_posttypes();

			?>

			<div id='sections'>
				<section class="cbxslackoutpostytypes">
					<select name="cbxwpslackmetaboxout[posttypes][]" id="cbxwpslackoutmetaboxout_fields_posttypes"
							multiple="multiple" class="chosen">
						<?php foreach ( $posttypes

							as $key => $value ) { ?>
						<optgroup label="<?php echo $value['label']; ?>">
							<?php foreach ( $value['types'] as $k => $v ) { ?>
								<option
									value="<?php echo $k; ?>" <?php if ( sizeof( $fieldValues_posttypes ) > 0 && in_array( $k, $fieldValues_posttypes ) ) {
									echo "selected";
								} ?>><?php echo $v; ?></option>
							<?php }
								} ?>
					</select>
				</section>
			</div>

		<?php }

		/**
		 * Outgoing slack post type meta box for event display
		 *
		 * @param $post
		 */
		public function cbxwpslackmetaboxoutevents_display( $post ) {

			$fieldValues = get_post_meta( $post->ID, '_cbxwpslackout', true );

			$event = '';
			if ( $fieldValues != null ) {
				$event = $fieldValues['event'];
			}

			$events = $this->cbxwpslackout_events();


			?>

			<div id='sections'>
				<section class="cbxslackouteventsection">
					<?php foreach ( $events as $eventkey => $value ) { ?>
						<input id="cbxwpslackoutmetaboxout_fields_events_<?php echo $eventkey; ?>"
							   type="radio" value="<?php echo $eventkey; ?>"
							   name="cbxwpslackmetaboxout[event]"/ <?php checked( $eventkey, $event ); ?>>
						<?php echo $value['title']; ?><br />
					<?php } ?>
				</section>
			</div>

		<?php }

		/**
		 * Outgoing slack post type meta
		 *
		 * @param $post
		 */
		public function cbxwpslackmetaboxout_display( $post ) {

			$fieldValues = get_post_meta( $post->ID, '_cbxwpslackout', true );

			$feildvalue_channel     = get_post_meta( $post->ID, '_cbxwpslackout_channel', true );
			$feildvalue_triggerword = get_post_meta( $post->ID, '_cbxwpslackout_triggerword', true );
			$feildvalue_teamdomain  = get_post_meta( $post->ID, '_cbxwpslackout_teamdomain', true );
			$feildvalue_token       = get_post_meta( $post->ID, '_cbxwpslackout_token', true );

			wp_nonce_field( 'cbxwpslackmetaboxout', 'cbxwpslackmetaboxout[nonce]' );


			$channel     = isset( $feildvalue_channel ) ? html_entity_decode( $feildvalue_channel ) : '';
			$triggerword = isset( $feildvalue_triggerword ) ? html_entity_decode( $feildvalue_triggerword ) : '';
			$teamdomain  = isset( $feildvalue_teamdomain ) ? html_entity_decode( $feildvalue_teamdomain ) : '';
			$token       = isset( $feildvalue_token ) ? html_entity_decode( $feildvalue_token ) : '';


			$enable = isset( $fieldValues['enableout'] ) ? intval( $fieldValues['enableout'] ) : 0;

			echo '<div id="cbxwpslackmetaboxout_wrapper">';
			?>

			<table class="form-table">
				<tbody>
				<tr valign="top">
					<th scope="row"><label
							for="cbxdynamicsidebarmetaboxout_fields_class"><?php echo __( 'Slack Enable/Disable', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<legend class="screen-reader-text"><span>input type="radio"</span></legend>
						<label title='g:i a'>
							<input type="radio" name="cbxwpslackmetaboxout[enableout]"
								   value="0" <?php checked( $enable, '0', true ); ?> />
							<span><?php esc_attr_e( 'No', 'cbxwpslack' ); ?></span>
						</label><br>
						<label title='g:i a'>
							<input type="radio" name="cbxwpslackmetaboxout[enableout]"
								   value="1" <?php checked( $enable, '1', true ); ?> />
							<span><?php esc_attr_e( 'Yes', 'cbxwpslack' ); ?></span>
						</label>

					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetaboxout_fields_channel"><?php echo __( 'Channel', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetaboxout_fields_before_channel" class="regular-text" type="text"
							   name=cbxwpslackmetaboxout[channel]" placeholder="#general"
							   value="<?php echo htmlentities( $channel ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetaboxout_fields_channel"><?php echo __( 'Trigger Word', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetaboxout_fields_before_triggerword" class="regular-text" type="text"
							   name=cbxwpslackmetaboxout[triggerword]" placeholder="Triggerword"
							   value="<?php echo htmlentities( $triggerword ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetaboxout_fields_teamdomain"><?php echo __( 'Team Domain', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetaboxout_fields_before_teamdomain" class="regular-text" type="text"
							   name=cbxwpslackmetaboxout[teamdomain]" placeholder="Teamdomain"
							   value="<?php echo htmlentities( $teamdomain ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetaboxout_fields_token"><?php echo __( 'Token', 'cbxwpslack' ); ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetaboxout_fields_token" class="regular-text" type="text"
							   name="cbxwpslackmetaboxout[token]" placeholder="Token"
							   value="<?php echo htmlentities( $token ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetaboxout_fields_token"><?php echo __( 'Url', 'cbxwpslack' ); ?></label>
					</th>
					<td>

						<?php
							$tolen_for_url = ( $token != '' ) ? $token : 'TOKEN_HERE';
							$token_url     = site_url() . '?cbxwpslackoutapp=1&token=' . $tolen_for_url;
						?>
						<input type="text" class="regular-text" value="<?php echo $token_url ?>" id="cbxslacktokenurl">
						<a href="#" title="<?php esc_html_e('Click to copy url', 'cbxwpslack'); ?>" class="button" id="cbxslacktokenurltriger"><?php esc_html_e('Click to copy url', 'cbxwpslack'); ?></a>
					</td>
				</tr>
				</tbody>
			</table>

			<?php
			echo '</div>';
		}


		/**
		 * Incoming slack post admin meta
		 *
		 * @param $post
		 */
		public function cbxwpslackmetaboxside_display( $post ) {

			$fieldValues = get_post_meta( $post->ID, '_cbxwpslack', true );
			$serviceurl  = ( isset( $fieldValues['serviceurl'] ) && $fieldValues['serviceurl'] != null ) ? $fieldValues['serviceurl'] : '';
			$channel     = ( isset( $fieldValues['channel'] ) && $fieldValues['channel'] != null ) ? $fieldValues['channel'] : '';
			$username    = ( isset( $fieldValues['username'] ) && $fieldValues['username'] != null ) ? $fieldValues['username'] : '';

			$iconemoji = ( isset( $fieldValues['iconemoji'] ) && $fieldValues['iconemoji'] != null ) ? $fieldValues['iconemoji'] : '';
			$enable    = isset( $fieldValues['enable'] ) ? intval( $fieldValues['enable'] ) : 0; //by default disable

			$cbx_ajax_icon = plugins_url( 'cbxwpslack/assets/images/busy.gif' );

			echo sprintf( '<a data-busy="0" href="" class="button-primary cbxwpslack_test" data-serviceurl="%s" data-channel="%s" data-username="%s" data-iconemoji="%s">'.__( 'Send Test Notification', 'cbxwpslack' ).'</a>', $serviceurl, $channel, $username, $iconemoji ) . '<span data-busy="0" class="cbxwpslack_ajax_icon"><img
                            src="' . $cbx_ajax_icon . '"/></span>';

		}


		/**
		 * Displaying Meta boxes Header
		 *
		 * @param $post
		 */
		public function cbxwpslackmetaboxevents_display( $post ) {

			$sections = array();

			$fieldValues = get_post_meta( $post->ID, '_cbxwpslack', true );

			//saved events
			$cbxwpslack_event = isset( $fieldValues['event'] ) ? $fieldValues['event'] : null;

			//all events
			$events = $this->cbxwpslack_events();

			foreach ( $events as $key => $value ) {
				if ( ! array_key_exists( $value['category']['section'], $sections ) ) {
					$sections[ $value['category']['section'] ] = $value['category']['title'];
				}

			}
			//show tabs
			$this->page_sections_tab( 'general' );
			?>
			<div id='sections'>
				<?php foreach ( $sections as $key => $value ) { ?>
					<section class="cbxslackeventsection">
						<?php foreach ( $events as $eventkey => $params ) { ?>
							<?php if ( $params['category']['section'] == $key ) { ?>
								<input id="cbxwpslackmetabox_fields_events_<?php echo $eventkey; ?>"
									   type="checkbox"
									   name="cbxwpslackmetabox[event][<?php echo $eventkey; ?>]" <?php ( isset( $cbxwpslack_event[ $eventkey ] ) ) ? checked( $cbxwpslack_event[ $eventkey ], 'on' ) : '' ?>/>
								<?php _e( $params['title'], 'cbxwpslack' ); ?><br />
							<?php } ?>
						<?php } ?>
					</section>
				<?php } ?>

			</div>

		<?php }

		/**
		 * Displaying Meta boxes
		 *
		 * @param $post
		 */
		public function cbxwpslackmetabox_display( $post ) {

			$fieldValues = get_post_meta( $post->ID, '_cbxwpslack', true );

			wp_nonce_field( 'cbxwpslackmetabox', 'cbxwpslackmetabox[nonce]' );

			$serviceurl = isset( $fieldValues['serviceurl'] ) ? html_entity_decode( $fieldValues['serviceurl'] ) : '';
			$channel    = isset( $fieldValues['channel'] ) ? html_entity_decode( $fieldValues['channel'] ) : '';
			$username   = isset( $fieldValues['username'] ) ? html_entity_decode( $fieldValues['username'] ) : '';
			$iconemoji  = isset( $fieldValues['iconemoji'] ) ? html_entity_decode( $fieldValues['iconemoji'] ) : '';

			$enable = isset( $fieldValues['enable'] ) ? intval( $fieldValues['enable'] ) : 0;

			echo '<div id="cbxwpslackmetabox_wrapper">';
			?>

			<table class="form-table">
				<tbody>
				<tr valign="top">


					<th scope="row"><label
							for="cbxdynamicsidebarmetabox_fields_class"><?php echo __( 'Slack Enable/Disable', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<legend class="screen-reader-text"><span>input type="radio"</span></legend>
						<label title='g:i a'>
							<input type="radio" name="cbxwpslackmetabox[enable]"
								   value="0" <?php checked( $enable, '0', true ); ?> />
							<span><?php esc_attr_e( 'No', 'cbxwpslack' ); ?></span>
						</label><br>
						<label title='g:i a'>
							<input type="radio" name="cbxwpslackmetabox[enable]"
								   value="1" <?php checked( $enable, '1', true ); ?> />
							<span><?php esc_attr_e( 'Yes', 'cbxwpslack' ); ?></span>
						</label>

					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetabox_fields_serviceurl"><?php echo __( 'Service Url', 'cbxwpslack' ); ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetabox_fields_serviceurl" class="regular-text" type="text"
							   name="cbxwpslackmetabox[serviceurl]" placeholder="incoming-webhook-url"
							   value="<?php echo htmlentities( $serviceurl ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxwpslackmetabox_fields_channel"><?php echo __( 'Channel', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxwpslackmetabox_fields_before_channel" class="regular-text" type="text"
							   name=cbxwpslackmetabox[channel]" placeholder="#general"
							   value="<?php echo htmlentities( $channel ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxdynamicsidebarmetabox_fields_username"><?php echo __( 'Username', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxdynamicsidebarmetabox_fields_username" class="regular-text" type="text"
							   name="cbxwpslackmetabox[username]" placeholder="username"
							   value="<?php echo htmlentities( $username ); ?>" />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label
							for="cbxdynamicsidebarmetabox_fields_iconemoji"><?php echo __( 'Icon Emoji', 'cbxwpslack' ) ?></label>
					</th>
					<td>
						<input id="cbxdynamicsidebarmetabox_fields_iconemoji" class="regular-text" type="text"
							   name="cbxwpslackmetabox[iconemoji]" placeholder=":rocket:"
							   value="<?php echo htmlentities( $iconemoji ); ?>" />
					</td>
				</tr>

				</tbody>
			</table>

			<?php
			echo '</div>';

		}//end display metabox

		/**
		 * Saving post with post meta.
		 *
		 * @param        int $post_id The ID of the post being save
		 * @param            bool                Whether or not the user has the ability to save this post.
		 */
		public function save_post( $post_id, $post ) {


			$post_type = 'cbxwpslack';

			$post_type_out = 'cbxwpslackout';


			if ( $post_type != $post->post_type && $post_type_out != $post->post_type ) {
				return;
			}

			if ( ! empty( $_POST['cbxwpslackmetabox'] ) ) {

				$postData = $_POST['cbxwpslackmetabox'];

				$saveableData = array();

				if ( $this->user_can_save( $post_id, 'cbxwpslackmetabox', $postData['nonce'] ) ) {

					$saveableData['serviceurl'] = esc_attr( $postData['serviceurl'] );
					$saveableData['channel']    = esc_attr( $postData['channel'] );
					$saveableData['username']   = esc_attr( $postData['username'] );
					$saveableData['iconemoji']  = esc_attr( $postData['iconemoji'] );
					$saveableData['enable']     = intval( $postData['enable'] );
					$saveableData['event']      = $postData['event']; //arrat

					update_post_meta( $post_id, '_cbxwpslack', $saveableData );
				}
			}

			if ( ! empty( $_POST['cbxwpslackmetaboxout'] ) ) {

				$postData     = $_POST['cbxwpslackmetaboxout'];
				$saveableData = array();

				if ( $this->user_can_save( $post_id, 'cbxwpslackmetaboxout', $postData['nonce'] ) ) {

					$saveableData['event']     = esc_attr( $postData['event'] );
					$saveableData['posttypes'] = $postData['posttypes'];
					$saveableData['enableout'] = intval( $postData['enableout'] );

					//individually save data
					update_post_meta( $post_id, '_cbxwpslackout_channel', esc_attr( $postData['channel'] ) );
					update_post_meta( $post_id, '_cbxwpslackout_triggerword', esc_attr( $postData['triggerword'] ) );
					update_post_meta( $post_id, '_cbxwpslackout_teamdomain', esc_attr( $postData['teamdomain'] ) );
					update_post_meta( $post_id, '_cbxwpslackout_token', esc_attr( $postData['token'] ) );

					//array serialized save data
					update_post_meta( $post_id, '_cbxwpslackout', $saveableData );
				}
			}
		}

		/**
		 * Determines whether or not the current user has the ability to save meta data associated with this post.
		 *
		 * @param $post_id
		 * @param $action
		 * @param $nonce
		 *
		 * @return bool
		 */
		public function user_can_save( $post_id, $action, $nonce ) {

			$is_autosave    = wp_is_post_autosave( $post_id );
			$is_revision    = wp_is_post_revision( $post_id );
			$is_valid_nonce = ( isset( $nonce ) && wp_verify_nonce( $nonce, $action ) );

			// Return true if the user is able to save; otherwise, false.
			return ! ( $is_autosave || $is_revision ) && $is_valid_nonce;

		}// end user_can_save

		/**
		 * Listing of incoming posts Column Header
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		public function out_columns_header( $columns ) {

			unset( $columns['date'] );

			$columns['enable'] = __( 'Status', 'cbxwpslack' );

			return $columns;

		}

		/**
		 * Listing of incoming each row of post type.
		 *
		 * @param $column
		 * @param $post_id
		 */
		public function out_custom_column_row( $column, $post_id ) {
			$setting = get_post_meta( $post_id, '_cbxwpslackout', true );

			switch ( $column ) {

				case 'enable':
					//integration of lcswitch https://github.com/LCweb-ita/LC-switch
					$enable = ! empty( $setting['enableout'] ) ? intval( $setting['enableout'] ) : 0;
					echo '<input data-postid="' . $post_id . '" ' . ( ( $enable == 1 ) ? ' checked="checked" ' : '' ) . ' type="checkbox"  value="' . $enable . '" class="js-switch cbxslackjsout-switch" autocomplete="off" />';
					break;
			}
		}

		/**
		 * Listing of incoming posts Column Header
		 *
		 * @param $columns
		 *
		 * @return mixed
		 */
		public function columns_header( $columns ) {

			unset( $columns['date'] );

			$columns['serviceurl'] = esc_html__( 'Incoming Web-Hook', 'cbxwpslack' );
			$columns['channel']    = esc_html__( 'Channel', 'cbxwpslack' );
			$columns['username']   = esc_html__( 'User Name', 'cbxwpslack' );
			$columns['iconemoji']  = esc_html__( 'Icon Emoji', 'cbxwpslack' );
			$columns['enable']     = esc_html__( 'Status', 'cbxwpslack' );

			return $columns;

		}

		/**
		 * Listing of incoming each row of post type.
		 *
		 * @param $column
		 * @param $post_id
		 */
		public function custom_column_row( $column, $post_id ) {
			$setting = get_post_meta( $post_id, '_cbxwpslack', true );

			switch ( $column ) {
				case 'serviceurl':
					echo ! empty( $setting['serviceurl'] ) ? sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $setting['serviceurl'] ), esc_html( $setting['serviceurl'] ) ) : '';
					break;
				case 'channel':
					echo ! empty( $setting['channel'] ) ? $setting['channel'] : '';
					break;
				case 'username':
					echo ! empty( $setting['username'] ) ? $setting['username'] : '';
					break;
				case 'iconemoji':
					echo ! empty( $setting['iconemoji'] ) ? $setting['iconemoji'] : '';
					break;
				case 'enable':
					//integration of lcswitch https://github.com/LCweb-ita/LC-switch
					$enable = ! empty( $setting['enable'] ) ? intval( $setting['enable'] ) : 0;
					echo '<input data-postid="' . $post_id . '" ' . ( ( $enable == 1 ) ? ' checked="checked" ' : '' ) . ' type="checkbox"  value="' . $enable . '" class="js-switch cbxslackjs-switch" autocomplete="off" />';
					break;
			}
		}


		public function remove_menus() {

			$button_count = wp_count_posts( 'cbxwpslack' );


			//remove add button option if already one button is created //maximum 5
			if ( $button_count->publish > 4 ) {
				do_action( 'cbxwpslack_remove', $this );

			}


		}

		public function cbxwpslack_remove_core() {
			remove_submenu_page( 'edit.php?post_type=cbxwpslack', 'post-new.php?post_type=cbxwpslack' );        //remove add feedback menu

			$result    = stripos( $_SERVER['REQUEST_URI'], 'post-new.php' );
			$post_type = isset( $_REQUEST['post_type'] ) ? esc_attr( $_REQUEST['post_type'] ) : '';

			if ( $result !== false ) {
				if ( $post_type == 'cbxwpslack' ) {
					wp_redirect( get_option( 'siteurl' ) . '/wp-admin/edit.php?post_type=cbxwpslack&cbxwpslack_error=true' );
				}

			}
		}

		/**
		 * Showing Admin notice
		 *
		 */
		function permissions_admin_notice() {
			echo "<div id='permissions-warning' class='error fade'><p><strong>" . sprintf( __( 'Sorry, you can not create more than 5 slacks in free verion, <a target="_blank" href="%s">Grab Pro</a>', 'cbxwpslack' ), 'http://codeboxr.com/product/cbx-wp-slack' ) . "</strong></p></div>";
		}

		/**
		 * Admin notice if user try to create new button in free version
		 */
		function cbxwpslack_notice() {
			if ( isset( $_GET['cbxwpslack_error'] ) ) {
				add_action( 'admin_notices', array( $this, 'permissions_admin_notice' ) );
			}
		}


		/**
		 * Add Setting links in plugin listing
		 *
		 * @param $links
		 *
		 * @return mixed
		 */
		public function add_cbxwpslack_settings_link( $links ) {
			//$settings_link = '<a href="options-general.php?page=wpfixedverticalfeedbackbutton">'.__('Settings','wpfixedverticalfeedbackbuttonaddon').'</a>';
			//array_unshift($links, $settings_link);
			/*$support_link = '<a target="_blank" href="http://codeboxr.com/product/cbx-wp-slack">' . esc_html__('Support', 'cbxwpslack') . '</a>';
			array_unshift($links, $support_link);
			*/


			$new_links['settings'] = '<a href="' . admin_url( 'edit.php?post_type=cbxwpslack&page=cbxwpslacksetting' ) . '">' . esc_html__( 'Settings', 'cbxwpslack' ) . '</a>';

			return array_merge( $new_links, $links );

			return $links;
		}


		/**
		 * Add support link to plugin description in /wp-admin/plugins.php
		 *
		 * @param  array  $plugin_meta
		 * @param  string $plugin_file
		 *
		 * @return array
		 */
		public function support_link( $plugin_meta, $plugin_file ) {

			if ( $this->plugin_basename == $plugin_file ) {
				$plugin_meta[] = sprintf(
					'<a target="_blank" href="%s">%s</a>', 'https://codeboxr.com/documentation-for-cbx-wp-slack/', esc_html__( 'Documentation', 'cbxwpslack' )
				);

				$plugin_meta[] = sprintf(
					'<a target="_blank" href="%s">%s</a>', 'https://codeboxr.com/product/cbx-wp-slack', esc_html__( 'Get Pro', 'cbxwpslack' )
				);

			}

			return $plugin_meta;
		}
	}
