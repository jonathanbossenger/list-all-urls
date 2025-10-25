<?php
/**
 * Plugin Name: List all URLs
 * Plugin URI: https://jonathanbossenger.com
 * Description: Outputs an ordered list of all the website's published Post Type URLs.
 * Version: 1.0.1
 * Author: Jonathan Bossenger
 * Author URI: https://jonathanbossenger.com
 * License: GPL v2 or higher
 * License URI: License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Requires PHP: 8.0
 * Tested up to: 6.8.3
 * Requires at least: 6.8
 * Text Domain: list-all-urls
 *
 * @package ListAllURLs
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fetch all available post types. Used to generate the list of post type in the admin page
 *
 * @return string[]|WP_Post_Type[]
 */
function list_all_urls_get_all_post_types(): array {
	// Get all Custom Post Types, and ONLY Custom Post Types.
	// See http://codex.wordpress.org/Function_Reference/get_post_types.
	$args     = array(
		'public'   => true,
		'_builtin' => false,
	);
	$output   = 'objects'; // names or objects, note names is the default.
	$operator = 'and'; // one of either 'and' or 'or'.

	return get_post_types( $args, $output, $operator );
}

/**
 * Fetch all posts based on provided arguments
 *
 * @param array $arguments Arguments to customize the post retrieval.
 *
 * @return array List of posts.
 */
function list_all_urls_get_posts( array $arguments ): array {
    $default_args = array(
            'post_type'      => 'post',
            'posts_per_page' => - 1,
            'post_status'    => 'publish',
    );
    $args         = wp_parse_args( $arguments, $default_args );

    return get_posts( $args );
}

/**
 * Generate a list of URLs based on the provided arguments.
 * Depending on the makelinks argument, will either be clickable or not
 *
 * @param array $arguments Arguments to customize the URL generation.
 * @param bool  $makelinks Whether to return clickable links or plain URLs (escaped).
 *
 * @return array List of generated URLs.
 */
function list_all_urls_generate_url_list( array $arguments = array(), bool $makelinks = false ): array {
	$posts = get_posts( $arguments );

	$links = array();
	foreach ( $posts as $post ) {
		$permalink = get_permalink( $post );
		if ( $makelinks ) {
			$links[] = '<a href="' . esc_url( $permalink ) . '">' . esc_html( $permalink ) . '</a>';
		} else {
			$links[] = esc_html( $permalink );
		}
	}

	return $links;
}

add_action( 'init', 'list_all_urls_blocks_init' );
/**
 * Register the plugin blocks
 *
 * @return void
 */
function list_all_urls_blocks_init() {
	wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
}

add_action( 'rest_api_init', 'list_all_urls_register_rest_route' );
function list_all_urls_register_rest_route (): void {
    register_rest_route(
            'list-all-urls/v1',
            '/urls',
            array(
                'methods' => 'GET',
                'callback' => 'list_all_urls_rest_fetch_all_urls',
                'args' => array(
                        'type' => array(
                                'validate_callback' => function( $param ) {
                                    return is_string( $param );
                                }
                        ),
                ),
            )
    );
}

function list_all_urls_rest_fetch_all_urls( $arguments ){
    if ( isset($arguments['type'] ) ) {
        $post_type = sanitize_text_field( wp_unslash( $arguments['type'] ) );
    } else {
        $post_type = 'any';
    }
    $args = array(
        'post_type'      => $post_type,
    );
    return list_all_urls_generate_url_list( $args );
}

add_action( 'admin_menu', 'list_all_urls_plugin_menu' );
/**
 * Add plugin menu to the WordPress admin dashboard via the Tools menu
 */
function list_all_urls_plugin_menu() {
	add_management_page(
            'List All URLs',
            'List All URLs',
            'manage_options',
            'list-all-urls',
            'list_all_urls_render_admin_page'
    );
}

/**
 * Render the admin page for the plugin
 */
function list_all_urls_render_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'list-all-urls' ) );
	}
	$post_types = get_all_post_types();
	?>

	<div class="wrap">
		<h1>List All URLS</h1>

		<p><strong>Select the URLs you would like to list from the following options:</strong></p>
		<form id="myform" action="" method="post">
			<?php wp_nonce_field( 'action', 'nonce' ); ?>
			<label for="getpost-any">
                <input id="getpost-any" type="radio" name="getpost-radio" value="any"/> All URLs (pages, posts, and custom post types)
            </label>
            <br>
			<label for="getpost-page">
                <input id="getpost-page" type="radio" name="getpost-radio" value="page"/> Pages Only
            </label>
            <br>
			<label for="getpost-post">
                <input id="getpost-post" type="radio" name="getpost-radio" value="post"/> Posts Only
            </label>
            <br>
			<?php
			foreach ( $post_types as $post_type ) :
				$pt_id = 'getpost-' . $post_type->name;
				?>
				<label for="<?php echo esc_attr( $pt_id ); ?>">
                    <input id="<?php echo esc_attr( $pt_id ); ?>" type="radio" name="getpost-radio" value="<?php echo esc_attr( $post_type->name ); ?>"/> <?php echo esc_html( $post_type->labels->singular_name ); ?>
					Posts Only
                </label>
                <br>
			<?php endforeach; ?>
			<br>
			<label for="makelinks">
                <input id="makelinks" type="checkbox" name="makelinks" value="makelinks"/> Make the generated list of URLs clickable hyperlinks
            </label>
            <br>
			<br>

			<input type="submit" class="button-primary" value="Submit"/>
		</form>
		<?php
		$raw_getpost = filter_input( INPUT_POST, 'getpost-radio', FILTER_UNSAFE_RAW );
		if ( false !== $raw_getpost && null !== $raw_getpost && '' !== $raw_getpost ) {

			check_admin_referer( 'action', 'nonce' );

			$post_type = sanitize_text_field( wp_unslash( $raw_getpost ) );

			$raw_makelinks = filter_input( INPUT_POST, 'makelinks', FILTER_UNSAFE_RAW );
			$makelinks     = false;
			if ( false !== $raw_makelinks && null !== $raw_makelinks && '' !== $raw_makelinks ) {
				$makelinks = true;
			}

			$args = array(
				'post_type'      => $post_type,
				'posts_per_page' => - 1,
				'post_status'    => 'publish',
			);

			$links = list_all_urls_generate_url_list( $args, $makelinks );

			if ( $links ) {
				echo '<p><strong>Below is a list of your requested URLs:</strong></p>';
				echo '<ol>';
				foreach ( $links as $link ) {
					echo '<li>' . wp_kses_post( $link ) . '</li>';
				}
				echo '</ol>';
			}
		}
		?>
	</div>
	<?php
}
