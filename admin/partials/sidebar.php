<?php
if (!defined('WPINC')) {
    die;
}
?>
<div id="postbox-container-1" class="postbox-container">
    <div class="meta-box-sortables">
        <div class="postbox">
            <h3><?php esc_html_e('Plugin Info', 'cbxwpslack'); ?></h3>
            <div class="inside">
                <p>Plugin Name : <?php echo $plugin_data['Title']; ?> <?php echo $plugin_data['Version']; ?></p>
                <p>Author : <?php echo $plugin_data['Author'] ?></p>
                <p>Website : <a href="http://codeboxr.com" target="_blank">Codeboxr.com</a></p>
                <p>Email : <a href="mailto:info@codeboxr.com" target="_blank">info@codeboxr.com</a></p>
                <p>Twitter : @<a href="http://twitter.com/codeboxr" target="_blank">Codeboxr</a></p>
                <p>Facebook : <a href="http://facebook.com/codeboxr" target="_blank">http://facebook.com/codeboxr</a></p>
                <p>Linkedin : <a href="www.linkedin.com/company/codeboxr" target="_blank">codeboxr</a></p>
                <p>Gplus : <a href="https://plus.google.com/+codeboxr" target="_blank">Google Plus</a></p>
            </div>
        </div>
        <div class="postbox">
            <h3><?php esc_html_e('Help & Supports', 'cbxwpslack'); ?></h3>
            <div class="inside">
                <p>Support: <a href="https://codeboxr.com/contact-us" target="_blank">Contact Us</a></p>
                <p><i class="icon-envelope"></i> <a href="mailto:info@codeboxr.com">info@codeboxr.com</a></p>
            </div>
        </div>
        <div class="postbox">
            <h3><?php esc_html_e('Codeboxr Other Plugins', 'cbxwpslack'); ?></h3>
            <div class="inside">
                <?php
                include_once(ABSPATH . WPINC . '/feed.php');
                if (function_exists('fetch_feed')) {
                    $feed = fetch_feed('http://codeboxr.com/feed?post_type=product');
                    // $feed = fetch_feed('http://feeds.feedburner.com/codeboxr'); // this is the external website's RSS feed URL
                    if (!is_wp_error($feed)) : $feed->init();
                        $feed->set_output_encoding('UTF-8'); // this is the encoding parameter, and can be left unchanged in almost every case
                        $feed->handle_content_type(); // this double-checks the encoding type
                        $feed->set_cache_duration(21600); // 21,600 seconds is six hours
                        $limit = $feed->get_item_quantity(6); // fetches the 18 most recent RSS feed stories
                        $items = $feed->get_items(0, $limit); // this sets the limit and array for parsing the feed

                        $blocks = array_slice($items, 0, 6); // Items zero through six will be displayed here

                        echo '<ul>';

                        foreach ($blocks as $block) {
                            $url = $block->get_permalink();

                            echo '<li style="clear:both;  margin-bottom:5px;"><a target="_blank" href="' . $url . '">';
                            //echo '<img style="float: left; display: inline; width:70px; height:70px; margin-right:10px;" src="http://codeboxr.com/wp-content/uploads/productshots/'.$id.'-profile.png" alt="codeboxrplugins" />';
                            echo '<strong>' . $block->get_title() . '</strong></a></li>';
                        }//end foreach

                        echo '</ul>';


                    endif;
                }
                ?>
            </div>
        </div>

        <div class="postbox">
            <h3><?php esc_html_e('Codeboxr.com Updates', 'cbxwpslack'); ?></h3>
            <div class="inside">
                <?php
                include_once(ABSPATH . WPINC . '/feed.php');
                if (function_exists('fetch_feed')) {
                    $feed = fetch_feed('https://codeboxr.com/feed');
                    // $feed = fetch_feed('http://feeds.feedburner.com/codeboxr'); // this is the external website's RSS feed URL
                    if (!is_wp_error($feed)) : $feed->init();
                        $feed->set_output_encoding('UTF-8'); // this is the encoding parameter, and can be left unchanged in almost every case
                        $feed->handle_content_type(); // this double-checks the encoding type
                        $feed->set_cache_duration(21600); // 21,600 seconds is six hours
                        $limit = $feed->get_item_quantity(6); // fetches the 18 most recent RSS feed stories
                        $items = $feed->get_items(0, $limit); // this sets the limit and array for parsing the feed

                        $blocks = array_slice($items, 0, 6); // Items zero through six will be displayed here
                        echo '<ul>';
                        foreach ($blocks as $block) {
                            $url = $block->get_permalink();
                            echo '<li><a target="_blank" href="' . $url . '">';
                            echo '<strong>' . $block->get_title() . '</strong></a></li>';
                        }//end foreach
                        echo '</ul>';
                    endif;
                }
                ?>
            </div>
        </div>

        <div class="postbox">
            <h3><?php esc_html_e('Codeboxr on facebook', 'cbxwpslack'); ?></h3>
            <div class="inside">
                <iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fcodeboxr&amp;width=260&amp;height=258&amp;show_faces=true&amp;colorscheme=light&amp;stream=false&amp;border_color&amp;header=false&amp;appId=558248797526834" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:260px; height:258px;" allowTransparency="true"></iframe>
            </div>
        </div>

    </div> <!-- .meta-box-sortables -->

</div> <!-- #postbox-container-1 .postbox-container -->