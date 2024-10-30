=== CBX WP Slack ===
Contributors: manchumahara,codeboxr
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=NWVPKSXP6TCDS
Tags: notification, slack, communication, slack for wordpress
Requires at least: 3.3
Tested up to: 5.2.2
Stable tag: 1.2.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Slack Notification for Wordpress

== Description ==

CBX WPSlack plugin gives easy and quick solution for notification for Slack. This plugin has notification on wordpress core events as well as it gives hooks for other plugin to create notification.

This plugin uses custom post type and each post type can be configured for different notification event.

[Learn more](https://codeboxr.com/product/cbx-wp-slack)

**Features of the plugin included for incoming web hook**

* Creating Unlimited number of slack incoming posts.
* Just add a new slack incoming post and fill up 'incoming-webhooks'(can be found in slack app),'channel'(specific channel name),'username'(bot name) and 'icon-emoji'(icon that shows up in slack dashboard)
* Each incoming post has its own slack settings that will work individually and fire on selected wordpress events to send a slack notification.
* Test Environment for each post to send test notification.
* Event Category, Categoriezed notification events
* Any type of hook/events can be added by filtering 'cbxwpslack_events'. Check here for details documentation http://codeboxr.com/product/cbx-wp-slack
* Well formatted notification for slack

**Event Lists**

* Core Wordpress
    * Core: When a post is published
    * Core: When a post is trashed
    * Core: New User Registration
* Communiction
    * Buddpress: New Status
    * bbPress: New Topic

**Features of the plugin included for outgoing webhook**

* Creating Unlimited number of slack outgoing posts.
* Just add a new slack outgoing post and fill up 'channel'(specific channel name),'Trigger Word'(same as slack outgoing Trigger Word) 'Team Domain'(slack team domain name) and Token(same as slack outgoing token)
* In outgoing webhook configuration the URL(s) should be "http://yourdomain.com/?cbxwpslackoutapp=1&token=SLACK OUTGOING WEBHOOK TOKEN"
* Each outgoing post has its own slack setting that will work individually and fire on selected wordpress events to send a slack response.

**Languages**

CBX WPSlack has been translated into the following languages:

1. British English

Pro addons available, [learn more](https://codeboxr.com/product/cbx-wp-slack)

Pro Addons

**Pro feature for incoming webhook**

* Support for woocommerce and easy digital download [plugins](https://codeboxr.com/product/cbx-wp-slack)
* woocommerce - When an order is completed
* woocommerce - When an order is cancelled
* woocommerce - When an order is refunded
* woocommerce - When Back Order is occurred
* woocommerce - Low stock
* woocommerce - Out of Stock
* EDD - Purchase Complete
* EDD - Payment Delete


**Pro feature for outgoing webhook**
* Custom Post type support
* Special support for Woocommerce and Easy Digital Downloads plugin in post search event

== Installation ==

1. Upload `cbxwpslack.php` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You'll now see a menu called "CBX WPSlack" in left menu of wordpress dashboard, start from there

== Screenshots ==
1. Plugin listing
2. Custom Post Type setting
3. Events
4. Test Mode
5. Slack notification

== Changelog ==
= 1.2.3 =
* Minor improvements

= 1.2.2 =
* Minor improvements

= 1.2.0 =
* Added Outgoing Webhook

= 1.1.0 =
* Added buddypress and bbpress in core
* new events in core
* witohut showing events groups as plugin name, changed it as plugin type, example communication, ecommerce for better grouping

= 1.0.0 =
* Initial public release