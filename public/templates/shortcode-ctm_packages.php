<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$columns = isset( $columns ) ? intval( $columns ) : 3;
?>
<div class="ctm-packages-grid" style="display:grid;grid-template-columns:repeat(<?php echo esc_attr( $columns ); ?>,1fr);gap:18px">
<?php while ( $query->have_posts() ): $query->the_post();
    $post_id = get_the_ID();
    $tagline = get_post_meta( $post_id, 'ctm_tagline', true );
    $base_price = get_post_meta( $post_id, '_base_price', true );
    $gallery = get_post_meta( $post_id, 'ctm_gallery', true );
    $thumb = '';
    if ( is_array( $gallery ) && ! empty( $gallery ) ) {
        $g = $gallery[0];
        if ( is_numeric( $g ) ) $thumb = wp_get_attachment_image( intval( $g ), 'medium' ); else $thumb = '<img src="' . esc_url( $g ) . '" style="width:100%"/>';
    }
?>
    <article class="ctm-package-card" style="border:1px solid #eee;padding:12px;border-radius:6px;background:#fff">
        <?php if ( $thumb ): ?><div class="ctm-card-thumb" style="margin-bottom:8px"><?php echo $thumb; ?></div><?php endif; ?>
        <h3 class="ctm-card-title"><?php the_title(); ?></h3>
        <?php if ( $tagline ): ?><div class="ctm-card-tagline" style="color:#666;margin-bottom:8px"><?php echo esc_html( $tagline ); ?></div><?php endif; ?>
        <?php if ( $base_price ): ?><div class="ctm-card-price" style="font-weight:600;margin-bottom:8px"><?php echo esc_html( $base_price ); ?></div><?php endif; ?>
        <div class="ctm-card-excerpt"><?php echo wp_trim_words( get_the_content(), 20 ); ?></div>
        <p style="margin-top:12px"><a class="button" href="<?php the_permalink(); ?>"><?php _e( 'View details', 'cst_system' ); ?></a></p>
    </article>
<?php endwhile; wp_reset_postdata(); ?>
</div>
