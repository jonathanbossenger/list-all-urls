<?php
/**
 * Plugin Name: List all URLs
 * Plugin URI: https://jonathanbossenger.com
 * Description: Creates a page in the admin panel under Settings > List All URLs that outputs an ordered list of all of the website's published URLs.
 * Version: 1.0.0
 * Author: Jonathan Bossenger
 * Author URI: https://jonathanbossenger.com
 * License: GPL v2 or higher
 * License URI: License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Tested up to: 6.8.1
 * Text Domain: list-all-urls
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fetch all post types
 * @return string[]|WP_Post_Type[]
 */
function jb_lau_get_all_post_types(){
    // Get all Custom Post Types, and ONLY Custom Post Types. See http://codex.wordpress.org/Function_Reference/get_post_types
    $args       = array(
        'public'   => true,
        '_builtin' => false
    );
    $output     = 'objects'; // names or objects, note names is the default.
    $operator   = 'and'; // 'and' or 'or'

	return get_post_types( $args, $output, $operator );
}

// See http://codex.wordpress.org/Administration_Menus for more info on the process of creating an admin page
add_action( 'admin_menu', 'jb_lau_plugin_menu' );

function jb_lau_plugin_menu() {
	add_options_page( 'List All URLs', 'List All URLs', 'manage_options', 'list-all-urls', 'jb_lau_render_admin_page' );
}

function jb_lau_render_admin_page() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
    $post_types = jb_lau_get_all_post_types();
    ?>

    <p><strong>Select the URLs you would like to list from the following options:</strong></p>
    <form id = "myform" action = "" method = "post">
        <input type="radio" name="getpost-radio" value="any"/> All URLs (pages, posts, and custom post types)<br>
        <input type="radio" name="getpost-radio" value="page"/> Pages Only<br>
        <input type="radio" name="getpost-radio" value="post"/> Posts Only<br>
	    <?php
	    foreach ( $post_types as $post_type ) {
		    echo '<input type="radio" name="getpost-radio" value="' . $post_type->name . '"/> ' . $post_type->labels->singular_name . ' Posts Only<br>';
	    }
	    ?>
        <br>
        <input type="checkbox" name="makelinks" value="makelinks"  /> Make the generated list of URLs clickable hyperlinks <br>
        <br>

        <input type="submit" class="button-primary" value="Submit"/>
    </form>

    <?php

	// Check if the form is submitted
	if ( isset( $_POST['getpost-radio'] ) && ! empty( [ $_POST['getpost-radio'] ] ) ) {

	    $post_type = sanitize_text_field( $_POST['getpost-radio'] );
	    $args = array(
		    'post_type'      => $post_type,
		    'posts_per_page' => -1,
		    'post_status'    => 'publish',
	    );

	    $links = jb_lau_generate_url_list(  $args );

	    if ( $links ) {
		    echo '<p><strong>Below is a list of your requested URLs:</strong></p>';
		    echo '<ol>';
		    foreach ( $links as $link ) {
			    echo '<li>' . $link . '</li>';
		    }
		    echo '</ol>';
	    }
    }

} // end jb_lau_render_admin_page()

function jb_lau_generate_url_list( $arguments = array() ) {

	// Set the default arguments
	$default_args = array(
		'post_type'      => 'post',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);
	$args = wp_parse_args( $arguments, $default_args );

    $the_query = new WP_Query( $args );

    $links = array(); // Creating array variable to house all URLs
    // Loop through the posts and create an array of URLs
    while ( $the_query->have_posts() ) {
        $the_query->the_post();
        if ( isset( $_POST['makelinks'] ) ) {
            $links[] = '<a href="' . get_permalink() . '">' . get_permalink() . '</a>';
        } else {
            $links[] = get_permalink();
        }
    }

    return $links;
}