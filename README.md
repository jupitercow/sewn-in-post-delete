# Sewn In Post Delete

* * *

http://wordpress.org/plugins/sewn-in-post-delete/

## Installation

1. Install plugin either via the WordPress.org plugin directory, or by uploading the files to your server.
2. Activate the plugin via the Plugins admin page.

## SHORTCODE

```php
[sewn_post_delete_link] // Loads current post for editing
```

```php
[sewn_post_delete_link text="Bye bye post" before="" after="" title="" class=""] // Will change the link text to "Bye bye post"
```

### Attributes

These are the same arguments for in template action below.

text = link text
before = html to show before the link
after = html to show after the link
title = the link title, defaults to link text
class = extra classes to add to the link

## IN TEMPLATE

This will show the link to users have the ability to use it.

```php
do_action('sewn/post_delete/link');
```

```php
do_action('sewn/post_delete/link', array('text'=>"Bye bye post", 'before'=>'', 'after'=>'', title=>'', 'class'=>''));
```