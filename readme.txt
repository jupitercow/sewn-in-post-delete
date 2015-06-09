=== Plugin Name ===
Contributors: jcow, ekaj
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=jacobsnyder%40gmail%2ecom&lc=US&item_name=Jacob%20Snyder&currency_code=USD&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: gravity forms, delete posts, frontend, front end
Requires at least: 3.6.1
Tested up to: 4.2.2
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A very basic framework for deleting posts on the front end. Uses a nonce for security and checks capabilities to what a user has access to.

== Description ==

Adds a shortcode and action to add a "Delete Post" link to posts so user's can delete them. By default it requires the user to have the "delete_post" capability for the post in question, but if you want anarchy, you can customize that all the way to loggedin or even public...

= SHORTCODE =

`[sewn_post_delete_link] // Loads current post for editing`

`[sewn_post_delete_link text="Bye bye post" before="" after="" title="" class=""] // Will change the link text to "Bye bye post"`

== Attributes ==

These are the same arguments for in template action below.

text = link text
before = html to show before the link
after = html to show after the link
title = the link title, defaults to link text
class = extra classes to add to the link

= IN TEMPLATE =

This will show the link to users have the ability to use it.

`do_action('sewn/post_delete/link');`

`do_action('sewn/post_delete/link', array('text'=>"Bye bye post", 'before'=>'', 'after'=>'', title=>'', 'class'=>''));`

== Installation ==

* Install plugin either via the WordPress.org plugin directory, or by uploading the files to your server.
* Activate the plugin via the Plugins admin page.


== Frequently Asked Questions ==

= None yet. =

== Screenshots ==

1. The basic shortcode in the backend.
2. The default delete link on the frontend.

== Changelog ==

= 1.0.0 - 2015-06-08 =

- Initial launch


== Upgrade Notice ==

= 1.0.0 =
This is the first version in the Wordpress repository.
