<?php
/**
 * Render file for the List All URLs block.
 */
$block_attributes = get_block_wrapper_attributes();
$urlsAbility = wp_get_ability( 'list-all-urls/urls' );
$urls = $urlsAbility->execute( array( 'makelinks' => $attributes['makeLinks'] ) );
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