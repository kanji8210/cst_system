<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// minimal shortcode template reusing variables: $post
$tagline = get_post_meta( $post->ID, 'ctm_tagline', true );
$base_price = get_post_meta( $post->ID, '_base_price', true );
$gallery = get_post_meta( $post->ID, 'ctm_gallery', true );
?>
<div class="ctm-package-shortcode">
    <h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
    <?php if ( $tagline ): ?><p class="ctm-tagline"><?php echo esc_html( $tagline ); ?></p><?php endif; ?>
    <?php if ( $gallery ): $items = is_array($gallery)?$gallery:array_map('trim',explode(',',$gallery)); ?>
        <div class="ctm-gallery-inline" style="display:flex;gap:8px;overflow-x:auto;padding:8px 0">
            <?php foreach($items as $g): if(is_numeric($g)) echo wp_get_attachment_image(intval($g),'medium'); else echo '<img src="'.esc_url($g).'" style="max-height:120px;"/>';?>
            <?php endforeach; ?>
        </div>
    <?php endif;?>
    <?php if ( $base_price ): ?><div class="ctm-price"><?php echo esc_html($base_price); ?></div><?php endif; ?>
    <div class="ctm-excerpt"><?php echo wp_trim_words( $post->post_content, 30 ); ?></div>
    <p><a class="button" href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php _e( 'View details', 'cst_system' ); ?></a></p>
</div>
