<?php
/**
 * Render file for the List All URLs block.
 */
$block_attributes = get_block_wrapper_attributes();
$urls = list_all_urls_generate_url_list( array( 'post_type' => 'any' ), true );
$urlList = '';
foreach ( $urls as $url ) {
	$urlList .= '<li>' .  wp_kses_post( $url ) . '</li>';
}
?>
<div <?php echo $block_attributes; ?>>
	<ul>
        <?php echo $urlList; ?>
	</ul>
</div>