<?php
/**
 * Plugin Name: List all URLs
 * Plugin URI: https://jonathanbossenger.com
 * Description: Outputs an ordered list of all the website's published Custom Post Type URLs.
 * Version: 1.0.1
 * Author: Jonathan Bossenger
 * Author URI: https://jonathanbossenger.com
 * License: GPL v2 or higher
 * License URI: License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 8.0
 * Tested up to: 6.8.3
 * Text Domain: list-all-urls
 *
 * @package ListAllURLs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Fetch all post types
 *
 * @return string[]|WP_Post_Type[]
 */
function jb_lau_get_all_post_types(): array {
	// Get all Custom Post Types, and ONLY Custom Post Types. See http://codex.wordpress.org/Function_Reference/get_post_types.
	$args     = array(
		'public'   => true,
		'_builtin' => false,
	);
	$output   = 'objects'; // names or objects, note names is the default.
	$operator = 'and'; // 'and' or 'or'.

	return get_post_types( $args, $output, $operator );
}

/**
 * Generate a list of URLs based on the provided arguments
 *
 * @param array $arguments Arguments to customize the URL generation.
 * @return array List of generated URLs.
 */
function jb_lau_generate_url_list( array $arguments = array() ): array {

	$makelinks = $_POST['makelinks'] ?? false;
	$posts     = jb_lau_get_posts( $arguments );

	$links = array();
	foreach ( $posts as $post ) {
		$permalink = get_permalink( $post );
		if ( $makelinks ) {
			$links[] = '<a href="' . $permalink . '">' . $permalink . '</a>';
		} else {
			$links[] = get_permalink();
		}
	}

	return $links;
}

/**
 * Fetch all posts based on provided arguments
 *
 * @param $arguments
 * @return array List of posts
 */
function jb_lau_get_posts( $arguments ): array {
	$default_args = array(
		'post_type'      => 'post',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
	);
	$args         = wp_parse_args( $arguments, $default_args );

	return get_posts( $args );
}

add_action( 'admin_menu', 'jb_lau_plugin_menu' );

/**
 * Add plugin menu to the WordPress admin dashboard via the Tools menu
 */
function jb_lau_plugin_menu() {
    add_management_page( 'List All URLs', 'List All URLs', 'manage_options', 'list-all-urls', 'jb_lau_render_admin_page' );
}

/**
 * Render the admin page for the plugin
 */
function jb_lau_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	$post_types = jb_lau_get_all_post_types();
	?>

	<div class="wrap">
	<h1>List All URLS</h1>

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
		// Check if the form is submitted.
		if ( isset( $_POST['getpost-radio'] ) && ! empty( array( $_POST['getpost-radio'] ) ) ) {

			$post_type = sanitize_text_field( $_POST['getpost-radio'] );
			$args      = array(
				'post_type'      => $post_type,
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);

			$links = jb_lau_generate_url_list( $args );

			if ( $links ) {
				echo '<p><strong>Below is a list of your requested URLs:</strong></p>';
				echo '<ol>';
				foreach ( $links as $link ) {
					echo '<li>' . $link . '</li>';
				}
				echo '</ol>';
			}
		}
		?>
	</div>
	<?php
}