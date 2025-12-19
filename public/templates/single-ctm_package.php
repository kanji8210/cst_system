<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Simple single template for ctm_package.
 * Falls back to theme when absent.
 */
global $post;
if ( ! $post || $post->post_type !== 'ctm_package' ) {
    return;
}

$tagline = get_post_meta( $post->ID, 'ctm_tagline', true );
$base_price = get_post_meta( $post->ID, '_base_price', true );
$gallery = get_post_meta( $post->ID, 'ctm_gallery', true );
// itinerary may be stored as JSON string in 'ctm_itinerary' or array in '_itinerary'
$itinerary_raw = get_post_meta( $post->ID, '_itinerary', true );
if ( empty( $itinerary_raw ) ) {
    $itinerary_json = get_post_meta( $post->ID, 'ctm_itinerary', true );
    if ( $itinerary_json ) {
        $itinerary = json_decode( $itinerary_json, true );
    } else {
        $itinerary = array();
    }
} else {
    $itinerary = is_string( $itinerary_raw ) ? json_decode( $itinerary_raw, true ) : $itinerary_raw;
}

$schedule_raw = get_post_meta( $post->ID, '_schedule_template', true );
$schedule = array();
if ( $schedule_raw ) {
    if ( is_string( $schedule_raw ) ) {
        $tmp = json_decode( $schedule_raw, true );
        if ( is_array( $tmp ) ) $schedule = $tmp;
    } elseif ( is_array( $schedule_raw ) ) {
        $schedule = $schedule_raw;
    }
}

?><div class="ctm-package-single">
    <h1 class="ctm-package-title"><?php echo esc_html( get_the_title( $post ) ); ?></h1>

    <?php if ( $tagline ): ?>
        <p class="ctm-package-tagline"><?php echo esc_html( $tagline ); ?></p>
    <?php endif; ?>

    <div class="ctm-package-content">
        <?php echo apply_filters( 'the_content', $post->post_content ); ?>
    </div>

    <?php if ( $base_price ): ?>
        <div class="ctm-package-price"><strong><?php _e( 'From:', 'cst_system' ); ?></strong> <?php echo esc_html( $base_price ); ?></div>
    <?php endif; ?>

    <?php if ( $gallery ):
        $items = is_array( $gallery ) ? $gallery : array_map('trim', explode(',', $gallery));
        if ( ! empty( $items ) ): ?>
            <div class="ctm-package-gallery">
                <?php foreach ( $items as $g ):
                    // try as attachment id
                    if ( is_numeric( $g ) ) {
                        echo wp_get_attachment_image( intval( $g ), 'large' );
                    } else {
                        echo '<img src="' . esc_url( $g ) . '" alt="">';
                    }
                endforeach; ?>
            </div>
    <?php endif; endif; ?>

    <?php if ( ! empty( $itinerary ) && is_array( $itinerary ) ): ?>
        <section class="ctm-package-itinerary">
            <h2><?php _e( 'Itinerary', 'cst_system' ); ?></h2>
            <?php foreach ( $itinerary as $day ):
                $day_label = isset( $day['day'] ) ? 'Day ' . intval( $day['day'] ) : '';
                $acts = isset( $day['activities'] ) && is_array( $day['activities'] ) ? $day['activities'] : array();
                ?>
                <div class="ctm-it-day">
                    <?php if ( $day_label ): ?><h3><?php echo esc_html( $day_label ); ?></h3><?php endif; ?>
                    <ul>
                        <?php if ( empty( $acts ) ): ?>
                            <li><?php _e( 'No activities defined.', 'cst_system' ); ?></li>
                        <?php else: foreach ( $acts as $act ): ?>
                            <li>
                                <?php if ( ! empty( $act['time'] ) ): ?><span class="ctm-act-time"><?php echo esc_html( $act['time'] ); ?></span><?php endif; ?>
                                <strong class="ctm-act-title"><?php echo esc_html( $act['title'] ?? '' ); ?></strong>
                                <?php if ( ! empty( $act['location'] ) ): ?><em class="ctm-act-loc"> — <?php echo esc_html( $act['location'] ); ?></em><?php endif; ?>
                                <?php if ( ! empty( $act['desc'] ) ): ?><div class="ctm-act-desc"><?php echo wp_kses_post( $act['desc'] ); ?></div><?php endif; ?>
                            </li>
                        <?php endforeach; endif; ?>
                    </ul>
                </div>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>

    <?php if ( ! empty( $schedule ) && is_array( $schedule ) ): ?>
        <section class="ctm-package-schedule">
            <h2><?php _e( 'Schedule', 'cst_system' ); ?></h2>
            <ul>
                <?php foreach ( $schedule as $s ): ?>
                    <li>
                        <?php if ( ! empty( $s['time'] ) ): ?><span class="ctm-sch-time"><?php echo esc_html( $s['time'] ); ?></span><?php endif; ?>
                        <strong class="ctm-sch-title"><?php echo esc_html( $s['title'] ?? '' ); ?></strong>
                        <?php if ( ! empty( $s['description'] ) ): ?><div class="ctm-sch-desc"><?php echo wp_kses_post( $s['description'] ); ?></div><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>
    <?php endif; ?>

</div>
<style>
.ctm-package-single{max-width:880px;margin:0 auto;padding:18px}
.ctm-package-gallery img{max-width:100%;height:auto;margin-bottom:8px}
.ctm-package-price{margin:12px 0;font-size:1.1em}
</style>

<!-- Express Interest modal -->
<div id="ctm-interest-modal" style="display:none;position:fixed;left:0;top:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:9999">
    <div style="background:#fff;max-width:520px;margin:60px auto;padding:18px;position:relative">
        <button id="ctm-interest-close" style="position:absolute;right:8px;top:8px" class="button-link">Close</button>
        <h3><?php _e( 'Express Interest', 'cst_system' ); ?></h3>
        <form id="ctm-interest-form">
            <?php echo wp_nonce_field( 'ctm_interest_nonce', 'ctm_interest_nonce_field', true, false ); ?>
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $post->ID ); ?>" />
            <p><label><?php _e( 'Your name', 'cst_system' ); ?><br/><input type="text" name="name" required /></label></p>
            <p><label><?php _e( 'Email', 'cst_system' ); ?><br/><input type="email" name="email" required /></label></p>
            <p><label><?php _e( 'Travellers', 'cst_system' ); ?><br/><input type="number" name="travellers" min="1" value="1" /></label></p>
            <p><label><?php _e( 'Preferred dates / notes', 'cst_system' ); ?><br/><input type="text" name="dates" placeholder="e.g. 2026-06-01 to 2026-06-07" /></label></p>
            <p><button type="submit" class="button button-primary"><?php _e( 'Submit interest', 'cst_system' ); ?></button></p>
            <div id="ctm-interest-message" style="display:none"></div>
        </form>
    </div>
</div>

<script>
(function(){
    const btn = document.createElement('button');
    btn.className = 'button button-primary';
    btn.textContent = 'Express Interest';
    btn.style.marginTop = '12px';
    btn.addEventListener('click', function(e){ e.preventDefault(); document.getElementById('ctm-interest-modal').style.display = 'block'; });
    const container = document.querySelector('.ctm-package-single');
    if (container) container.insertBefore(btn, container.firstChild);

    document.getElementById('ctm-interest-close').addEventListener('click', function(e){ e.preventDefault(); document.getElementById('ctm-interest-modal').style.display = 'none'; });

    document.getElementById('ctm-interest-form').addEventListener('submit', function(e){
        e.preventDefault();
        const form = this;
        const data = new FormData(form);
        // add nonce field name expected by handler
        data.append('nonce', document.getElementById('ctm-interest-form').querySelector('input[name="ctm_interest_nonce"]').value || document.querySelector('input[name="ctm_interest_nonce_field"]').value );
        data.append('action','ctm_submit_interest');

        fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: data }).then(r=>r.json()).then(function(res){
            const msg = document.getElementById('ctm-interest-message');
            msg.style.display = 'block';
            if (res.success) {
                msg.textContent = 'Thanks — we have received your interest.';
                form.reset();
            } else {
                msg.textContent = 'Error: ' + (res.data || 'unknown');
            }
        });
    });
})();
</script>
