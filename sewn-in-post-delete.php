<?php

/**
 * @link              https://github.com/jupitercow/sewn-in-post-delete
 * @since             1.0.0
 * @package           Sewn_Post_Delete
 *
 * @wordpress-plugin
 * Plugin Name:       Sewn In Post Delete
 * Plugin URI:        https://wordpress.org/plugins/sewn-in-post-delete/
 * Description:       Basic infrastructure for front end users to delete their own posts.
 * Version:           1.0.1
 * Author:            Jupitercow
 * Author URI:        http://Jupitercow.com/
 * Contributor:       Jake Snyder
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sewn_post_delete
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$class_name = 'Sewn_Post_Delete';
if (! class_exists($class_name) ) :

class Sewn_Post_Delete
{
	/**
	 * The unique prefix for Sewn In.
	 *
	 * @since    2.0.0
	 * @access   protected
	 * @var      string    $prefix         The string used to uniquely prefix for Sewn In.
	 */
	protected $prefix;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Plugin settings.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $settings       The array used for settings.
	 */
	protected $settings;

	/**
	 * Class settings
	 *
	 * @author  ekaj
	 * @since	1.0.0
	 * @return	void
	 */
	public function settings()
	{
		$this->prefix      = 'sewn';
		$this->plugin_name = strtolower(__CLASS__);
		$this->version     = '1.0.1';
		$this->settings    = array(
			'request_id'   => 'delete_post',
			'nonce_delete' => $this->plugin_name,
			'link_class'   => "{$this->plugin_name}_link",
			'strings'      => array(
				'link_text'            => __( 'Delete Post', $this->plugin_name ),
				'notification_success' => __( 'Successfully deleted %s', $this->plugin_name ),
				'notification_failed'  => __( 'There was a problem deleting %s', $this->plugin_name ),
				'default_title'        => __( 'the post', $this->plugin_name ),
				'notification_delete'  => __( 'Are you sure you want to delete "%s"?', $this->plugin_name ),
				'replace_title'        => '{post_title}',
			),
		);

		$title = $this->settings['strings']['default_title'];
		if (! empty($_REQUEST['post']) && is_numeric($_REQUEST['post']) ) {
			$title = '"' . get_the_title($_REQUEST['post']) . '"';
		}
		$this->settings['notifications'] = array(
			'failed' => array(
				'key'     => 'action',
				'value'   => 'delete_failed',
				'message' => sprintf( $this->settings['strings']['notification_failed'], $title ),
				'args'    => 'error=true',
			),
			'success' => array(
				'key'     => 'action',
				'value'   => 'delete_success',
				'message' => sprintf( $this->settings['strings']['notification_success'], $title ),
				'args'    => '',
			),
		);
	}

	/**
	 * Load the plugin.
	 *
	 * @since	2.0.0
	 * @return	void
	 */
	public function run()
	{
		$this->settings();

		add_action( 'init',           array($this, 'init') );
		add_action( 'wp',             array($this, 'delete_post') );
	}

	/**
	 * Initialize the plugin once during run.
	 *
	 * @since	1.0.0
	 * @return	void
	 */
	public function init()
	{
		$this->settings = apply_filters( "{$this->prefix}/post_delete/settings", $this->settings );

		$this->plugins_loaded();

		add_action( "{$this->prefix}/post_delete/url",           array($this, 'get_url') );
		add_action( "{$this->prefix}/post_delete/get_link",      array($this, 'get_link') );
		add_action( "{$this->prefix}/post_delete/link",          array($this, 'link') );
		add_shortcode( "{$this->prefix}_post_delete_link",       array($this, 'shortcode_link') );

		add_filter( "the_content",                               array($this, 'the_content') );
		add_action( "{$this->prefix}/post_delete/notification",  array($this, 'notification') );

		// Load scripts
		add_action( 'wp_enqueue_scripts',                        array($this, 'enqueue_scripts') );
	}

	/**
	 * On plugins_loaded test if we can use sewn_notifications
	 *
	 * @author  Jake Snyder
	 * @since	1.0.0
	 * @return	void
	 */
	public function plugins_loaded()
	{
		// Have the login plugin use frontend notifictions plugin
		if ( apply_filters( "{$this->prefix}/post_delete/use_sewn_notifications", true ) )
		{
			if ( class_exists('Sewn_Notifications') ) {
				add_filter( "{$this->prefix}/notifications/queries", array($this, 'add_notifications') );
				add_filter( "{$this->prefix}/post_delete/use_sewn_notifications", '__return_true' );
			} else {
				add_filter( "{$this->prefix}/post_delete/use_sewn_notifications", '__return_false' );
			}
		}
	}

	public function the_content( $content )
	{
		if ( isset($_REQUEST['action']) && false === apply_filters( "{$this->prefix}/post_delete/use_sewn_notifications", false ) && apply_filters( "{$this->prefix}/post_delete/content_notification", true ) )
		{
			if ( 'delete_success' == $_REQUEST['action'] )
			{
				$title = $this->settings['strings']['default_title'];
				if (! empty($_REQUEST['post']) && is_numeric($_REQUEST['post']) ) {
					$title = '"' . get_the_title($_REQUEST['post']) . '"';
				}
				$content = $this->create_notification( sprintf( $this->settings['strings']['notification_success'], $title ) ) . $content;
			}
	
			if ( 'delete_failed' == $_REQUEST['action'] )
			{
				$title = $this->settings['strings']['default_title'];
				if (! empty($_REQUEST['post']) && is_numeric($_REQUEST['post']) ) {
					$title = '"' . get_the_title($_REQUEST['post']) . '"';
				}
				$content = $this->create_notification( sprintf( $this->settings['strings']['notification_failed'], $title ) ) . $content;
			}
		}
		return $content;
	}

	public function create_notification( $notification )
	{
		ob_start(); ?>
		<div class="delete_post_notification">
			<p style="background-color:lightpink; padding:.5em; text-align:center; width:100%;">
				<?php echo $notification; ?>
			</p>
		</div>
		<?php
		return ob_get_clean();
	}

	public function enqueue_scripts()
	{
		wp_enqueue_script( $this->plugin_name, plugins_url( 'assets/js/sewn-in-post-delete.js', __FILE__ ), array(), $this->version );
		$args = array(
			'message'    => sprintf( $this->settings['strings']['notification_delete'], $this->settings['strings']['replace_title'] ),
			'replace'    => $this->settings['strings']['replace_title'],
			'prefix'     => $this->prefix,
			'link_class' => $this->settings['link_class'],
		);
		wp_localize_script( $this->plugin_name, $this->plugin_name, $args );
	}

	/**
	 * Delete a post when the link is submitted and valid
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @param	array|string $args The arguments to use when creating a link
	 * @return	void
	 */
	public function delete_post()
	{
		if (! empty($_REQUEST[$this->settings['request_id']]) && (! empty($_REQUEST['nonce']) && wp_verify_nonce($_REQUEST['nonce'],$this->settings['nonce_delete'])) )
		{
			$post_id = $_REQUEST[$this->settings['request_id']];
			if ( is_numeric($post_id) && $this->current_user_can($post_id) )
			{
				$success = wp_delete_post($post_id);
				if ( $success ) {
					wp_redirect( add_query_arg( array( 'action' => 'delete_success', 'post' => $post_id ), apply_filters( "{$this->prefix}/post_delete/redirect_success", home_url() ) ) );
					die;
				} else {
					wp_redirect( add_query_arg( array( 'action' => 'delete_failed', 'post' => $post_id ), get_permalink($post_id) ) );
					die;
				}
				
			}
		}
	}

	/**
	 * Build Delete Link and return
	 *
	 * Create anchor link with the edit URI.
	 *
	 * Arguments:
	 *	post_id (int) is the id of the post you want to edit
	 *	url (string|int) is either the full url of the page where your edit form resides, or an id for the page where the edit form resides
	 *	text (string) is the link text
	 *	title (string) is the title attribute of the anchor tag
	 *  class (string) is the class(es) to add to the link
	 *  before (string) is html to show before the link
	 *  after (string) is html to show after the link
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @param	array|string $args The arguments to use when creating a link
	 * @return	void
	 */
	public function get_link( $args=array() )
	{
		$defaults = array(
			'post_id' => false,
			'text'    => $this->settings['strings']['link_text'],
			'title'   => false,
			'class'   => '',
			'before'  => '',
			'after'   => '',
		);
		$defaults = apply_filters( "{$this->prefix}/post_delete/link_defaults", $defaults );
		$args = wp_parse_args( $args, $defaults );
		extract($args);

		$output = '';

		// Get the current post id, if none is provided
		if (! $post_id && ! empty($GLOBALS['post']->ID) ) {
			$post_id = $GLOBALS['post']->ID;
		}

		$request_key = apply_filters( "{$this->prefix}/post_delete/request_id", $this->settings['request_id'] );
		if ( $this->current_user_can( $post_id ) )
		{
			// Add the link text to the title if no link title is specified
			if (! $title ) {
				$title = $text;
			}
			$output = '<a class="' . esc_attr($this->settings['link_class']) . ($class ? ' ' . esc_attr($class) : '') . '" data-title="' . esc_attr( get_the_title($post_id) ) . '" href="' . apply_filters( "{$this->prefix}/post_delete/url", $post_id ) . '" title="' . esc_attr($title) . '">' . esc_html($text) . '</a>';

			return $before . $output . $after;
		}
	}

	/**
	 * Build Delete Link
	 *
	 * Create anchor link with the delete URI.
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @param	array|string $args The arguments to use when creating a link
	 * @return	void
	 */
	public function link( $args=array() )
	{
		echo apply_filters( "{$this->prefix}/post_delete/get_link", $args );
	}

	/**
	 * Create a link to delete a post
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @type	shortcode
	 * @return	void	
	 */
	public function shortcode_link( $atts )
	{
		return apply_filters( "{$this->prefix}/post_delete/get_link", $atts );
	}

	/**
	 * Build Edit URI
	 *
	 * Create a url with GET variables to the page for editing.
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @param	int		$post_id ID of the post you want to edit
	 * @param	string	$url By default the permalink of the post that you want to edit is used, use this to send to a different page to edit the post whose id is provided
	 * @return	void
	 */
	public function get_url( $post_id=false, $url=false )
	{
		if (! $post_id && ! empty($GLOBALS['post']) ) {
			$post_id = $GLOBALS['post']->ID;
		}

		// If the url parameter is a post_id, get the url to that post
		if ( is_numeric($url) ) {
			$url = get_permalink($url);
		}

		// If no url, use the post_id to get the url to the post being edited
		if (! $url ) {
			$url = get_permalink($post_id);
		}

		return add_query_arg( array( $this->settings['request_id'] => $post_id, 'nonce' => wp_create_nonce($this->settings['nonce_delete'])), $url );
	}

	/**
	 * Adds notifications to Sewn
	 *
	 * @author  Jake Snyder
	 * @since	1.0.1
	 * @return	array $queries Messages array
	 */
	public function add_notifications( $queries )
	{
		$queries = array_merge($queries, $this->settings['notifications']);
		return $queries;
	}

	/**
	 * Check User Permissions
	 *
	 * Check permissions for current user that they are allowed to edit the post/page.
	 *
	 * @author  ekaj
	 * @since   1.0.0
	 * @return	bool True if user is allowed to delete the post
	 */
	public function current_user_can( $post_id=false )
	{
		$public_edit = apply_filters( $this->plugin_name . '/public_edit', false );
		if ( true === $public_edit )
		{
			return true;
		}
		elseif ( 'loggedin' === $public_edit && is_user_logged_in() )
		{
			return true;
		}
		elseif ( $public_edit && is_user_logged_in() )
		{
			if ( is_array($public_edit) )
			{
				foreach ( $public_edit as $cap )
				{
					if ( current_user_can($public_edit) ) {
						return true;
					}
				}
				return false;
			}
			else
			{
				return current_user_can($public_edit);
			}
		}

		if ( $post_id && is_numeric($post_id) ) {
			$post_type = get_post_type( $post_id );
		} else {
			return false;
		}

		$capability = ( 'page' == $post_type ) ? 'delete_page' : 'delete_post';

		if ( current_user_can($capability, $post_id) ) {
			return true;
		}

		return false;
	}
}

$$class_name = new $class_name;
$$class_name->run();
unset($class_name);

endif;