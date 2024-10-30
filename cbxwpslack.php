<?php
    /**
     * The plugin bootstrap file
     *
     * @link              info@codeboxr.com
     * @since             1.0.0
     * @package           Cbxwpslack
     *
     * @wordpress-plugin
     * Plugin Name:       CBX WP Slack
     * Plugin URI:        https://codeboxr.com/product/cbx-wp-slack
     * Description:       Slack Integration for wordpress
     * Version:           1.2.3
     * Author:            Codeboxr
     * Author URI:        https://codeboxr.com
     * License:           GPL-2.0+
     * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
     * Text Domain:       cbxwpslack
     * Domain Path:       /languages
     */

    // If this file is called directly, abort.
    if (!defined('WPINC')) {
        die;
    }


defined('CBXWPSLACK_PLUGIN_NAME') or define('CBXWPSLACK_PLUGIN_NAME', 'cbxwpslack');
defined('CBXWPSLACK_PLUGIN_VERSION') or define('CBXWPSLACK_PLUGIN_VERSION', '1.2.3');
defined('CBXWPSLACK_BASE_NAME') or define('CBXWPSLACK_BASE_NAME', plugin_basename(__FILE__));
defined('CBXWPSLACK_ROOT_PATH') or define('CBXWPSLACK_ROOT_PATH', plugin_dir_path(__FILE__));
defined('CBXWPSLACK_ROOT_URL') or define('CBXWPSLACK_ROOT_URL', plugin_dir_url(__FILE__));



/**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-cbxwpslack-activator.php
     */
    function activate_cbxwpslack() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-cbxwpslack-activator.php';
        Cbxwpslack_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-cbxwpslack-deactivator.php
     */
    function deactivate_cbxwpslack() {
        require_once plugin_dir_path(__FILE__) . 'includes/class-cbxwpslack-deactivator.php';
        Cbxwpslack_Deactivator::deactivate();
    }

    register_activation_hook(__FILE__, 'activate_cbxwpslack');
    register_deactivation_hook(__FILE__, 'deactivate_cbxwpslack');

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path(__FILE__) . 'includes/class-cbxwpslack.php';

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */

    /**
     * Main Method for Posting a notification to Slack from WordPress environment
     *
     * @param $message
     * @param $sevice_url
     * @param $channel
     * @param $username
     * @param $icon_emoji
     *
     * @return bool|WP_Error
     */
    function cbxwp_post_to_slack($message, $sevice_url, $channel, $username = 'cbxwpslack', $icon_emoji = ':rocket:') {

        $slack_endpoint = $sevice_url;
        $data           = array(
            'payload' => json_encode(array(
                                         "channel"    => $channel,
                                         "text"       => $message,
                                         "username"   => $username,
                                         "icon_emoji" => $icon_emoji
                                     )
            )
        );

        $posting_to_slack = wp_remote_post($slack_endpoint, array(
                                                              'method'      => 'POST',
                                                              'timeout'     => 30,
                                                              'redirection' => 5,
                                                              'httpversion' => '1.0',
                                                              'blocking'    => true,
                                                              'headers'     => array(),
                                                              'body'        => $data,
                                                              'cookies'     => array()
                                                          )
        );

        //write_log($posting_to_slack);

        if (is_wp_error($posting_to_slack)) {
	        //write_log($posting_to_slack->get_error_message());
            echo sprintf(__('Error Found ( %s )', $posting_to_slack->get_error_message()));
        } else {
            $status  = intval(wp_remote_retrieve_response_code($posting_to_slack));
            $message = wp_remote_retrieve_body($posting_to_slack);
            if (200 !== $status) {
                return new WP_Error('unexpected_response', $message);
            } else if (200 !== $status) {
                return true;
            }
        }
    }

    function run_cbxwpslack() {

        $plugin_base = plugin_basename(__FILE__);

        $plugin = new Cbxwpslack($plugin_base);
        $plugin->run();

    }

    run_cbxwpslack();

/*
add_filter('cbxwpslack_events', 'my_custom_slack_event');

function my_custom_slack_event($events){
    $myevent = array();
    $myevent['uniqueeventname'] = array(
        'title'         => __('My Custom event when post published', 'your_lang_domain'), //will be shown in post edit screen
        'hook'          => 'publish_post', //as defined by wordpress
        'accepted_args' => 2, //
        'priority'      => 10, //if not used then we will use 10
        'category'      => array(
            'section' => 'customcategory', //event category
            'title'   => __('My Custom Tab', 'your_lang_domain')
        ),
        'message' => __('Hei, New post published !', 'your_lang_domain')
    );

    return array_merge($events, $myevent);
}
*/
/*
add_filter('cbxwpslack_events', 'my_custom_slack_event2');

function my_custom_slack_event2($events){
    $myevent = array();
    $myevent['uniqueeventname'] = array(
        'title'         => __('My Custom event when post published', 'your_lang_domain'), //will be shown in post edit screen
        'hook'          => 'publish_post', //as defined by wordpress
        'accepted_args' => 2, //
        'priority'      => 10, //if not used then we will use 10
        'category'      => array(
            'section' => 'customcategory', //event category
            'title'   => __('My Custom Tab', 'your_lang_domain')
        ),
        'message'       => function ($ID, $post) {
            $author_id    = $post->post_author;
            $author = get_user_by( 'ID', $author_id );

            $author_name = $author->display_name;
            $author_url     = get_edit_user_link($author_id);

            $title     = $post->post_title;
            $permalink = get_permalink($ID);

            $message   = sprintf(__('Post "<%s|%s>" written by User <%s|%s>', 'your_lang_domain'), $permalink, $title, $author_url, $author_name);

            return $message;

        }
    );

    return array_merge($events, $myevent);
}
*/