/*   $message = "Are you sure you want to delete ".get_the_title($post->ID)." ?";
    $delLink = wp_nonce_url( get_bloginfo('url') . "/wp-admin/post.php?action=delete&post=" . $post->ID, 'delete-post_' . $post->ID);
    $htmllink = "<a href='" . $delLink . "' onclick = \"if ( confirm('".$message."' ) ) { execute(); return true; } return false;\"/>".$link."</a>";
    */
document.addEventListener('DOMContentLoaded', function() {

	var delete_links = document.getElementsByClassName( sewn_post_delete.link_class );

	if ( delete_links.length )
	{
		for ( var i=0; i<delete_links.length; i++ )
		{
			var title = delete_links[i].getAttribute('data-title'),
				message = sewn_post_delete.message.replace(sewn_post_delete.replace, title);

			delete_links[i].addEventListener('click', function(e) {
				if ( confirm(message) ) {
					execute();
					return true;
				} else {
					e.preventDefault();
				}
			});
		}
	}

});
