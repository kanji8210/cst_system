<?php
if ( ! defined( 'ABSPATH' ) ) exit;
// Use theme header/footer so CPT displays like normal posts
get_header();
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

// Get policies and requirements
$what_to_bring = get_post_meta( $post->ID, '_what_to_bring', true );
$requirements = get_post_meta( $post->ID, '_requirements', true );
$cancellation_policy = get_post_meta( $post->ID, '_cancellation_policy', true );

// Get CTA settings
$cta_primary_text = get_post_meta( $post->ID, '_cta_primary_text', true );
$cta_primary_type = get_post_meta( $post->ID, '_cta_primary_type', true );
$cta_primary_value = get_post_meta( $post->ID, '_cta_primary_value', true );
$cta_secondary_text = get_post_meta( $post->ID, '_cta_secondary_text', true );
$cta_secondary_type = get_post_meta( $post->ID, '_cta_secondary_type', true );
$cta_secondary_value = get_post_meta( $post->ID, '_cta_secondary_value', true );
$cta_message = get_post_meta( $post->ID, '_cta_message', true );

// Set defaults
if ( empty( $cta_primary_text ) ) $cta_primary_text = __( 'Express Interest', 'cst_system' );
if ( empty( $cta_primary_type ) ) $cta_primary_type = 'form';
if ( empty( $cta_secondary_text ) ) $cta_secondary_text = __( 'Contact Us to Book', 'cst_system' );
if ( empty( $cta_secondary_type ) ) $cta_secondary_type = 'email';

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

    <!-- Requirements & Policies Section -->
    <?php if ( $what_to_bring || $requirements || $cancellation_policy ): ?>
        <section class="ctm-package-policies">
            <?php if ( $what_to_bring ): ?>
                <div class="ctm-policy-block">
                    <h3><?php _e( 'What to Bring', 'cst_system' ); ?></h3>
                    <div class="ctm-policy-content"><?php echo nl2br( esc_html( $what_to_bring ) ); ?></div>
                </div>
            <?php endif; ?>

            <?php if ( $requirements ): ?>
                <div class="ctm-policy-block">
                    <h3><?php _e( 'Requirements', 'cst_system' ); ?></h3>
                    <div class="ctm-policy-content"><?php echo nl2br( esc_html( $requirements ) ); ?></div>
                </div>
            <?php endif; ?>

            <?php if ( $cancellation_policy ): ?>
                <div class="ctm-policy-block">
                    <h3><?php _e( 'Cancellation Policy', 'cst_system' ); ?></h3>
                    <div class="ctm-policy-content"><?php echo nl2br( esc_html( $cancellation_policy ) ); ?></div>
                </div>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <!-- Call to Action Section -->
    <section class="ctm-package-cta">
        <?php if ( $cta_message ): ?>
            <p class="ctm-cta-message"><?php echo esc_html( $cta_message ); ?></p>
        <?php endif; ?>
        
        <div class="ctm-cta-buttons">
            <?php
            // Primary CTA Button
            $primary_url = '#';
            $primary_target = '';
            $primary_onclick = '';
            
            switch ( $cta_primary_type ) {
                case 'form':
                    $primary_onclick = "event.preventDefault(); document.getElementById('ctm-interest-modal').style.display = 'block';";
                    break;
                case 'email':
                    $primary_url = 'mailto:' . esc_attr( $cta_primary_value );
                    break;
                case 'phone':
                    $primary_url = 'tel:' . esc_attr( $cta_primary_value );
                    break;
                case 'whatsapp':
                    $whatsapp_number = preg_replace( '/[^0-9]/', '', $cta_primary_value );
                    $primary_url = 'https://wa.me/' . $whatsapp_number;
                    $primary_target = '_blank';
                    break;
                case 'url':
                    $primary_url = esc_url( $cta_primary_value );
                    $primary_target = '_blank';
                    break;
            }
            ?>
            <a href="<?php echo esc_url( $primary_url ); ?>" 
               class="button button-primary ctm-cta-primary"
               <?php if ( $primary_target ): ?>target="<?php echo esc_attr( $primary_target ); ?>"<?php endif; ?>
               <?php if ( $primary_onclick ): ?>onclick="<?php echo esc_attr( $primary_onclick ); ?>"<?php endif; ?>>
                <?php echo esc_html( $cta_primary_text ); ?>
            </a>

            <?php
            // Secondary CTA Button
            $secondary_url = '#';
            $secondary_target = '';
            $secondary_onclick = '';
            
            switch ( $cta_secondary_type ) {
                case 'form':
                    $secondary_onclick = "event.preventDefault(); document.getElementById('ctm-interest-modal').style.display = 'block';";
                    break;
                case 'email':
                    $secondary_url = 'mailto:' . esc_attr( $cta_secondary_value );
                    break;
                case 'phone':
                    $secondary_url = 'tel:' . esc_attr( $cta_secondary_value );
                    break;
                case 'whatsapp':
                    $whatsapp_number = preg_replace( '/[^0-9]/', '', $cta_secondary_value );
                    $secondary_url = 'https://wa.me/' . $whatsapp_number;
                    $secondary_target = '_blank';
                    break;
                case 'url':
                    $secondary_url = esc_url( $cta_secondary_value );
                    $secondary_target = '_blank';
                    break;
            }
            ?>
            <a href="<?php echo esc_url( $secondary_url ); ?>" 
               class="button ctm-cta-secondary"
               <?php if ( $secondary_target ): ?>target="<?php echo esc_attr( $secondary_target ); ?>"<?php endif; ?>
               <?php if ( $secondary_onclick ): ?>onclick="<?php echo esc_attr( $secondary_onclick ); ?>"<?php endif; ?>>
                <?php echo esc_html( $cta_secondary_text ); ?>
            </a>
        </div>
    </section>

</div>
<style>
.ctm-package-single{max-width:880px;margin:0 auto;padding:18px}
.ctm-package-gallery img{max-width:100%;height:auto;margin-bottom:8px}
.ctm-package-price{margin:12px 0;font-size:1.1em}

/* Policies Section */
.ctm-package-policies{
    margin: 30px 0;
    padding: 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.ctm-policy-block{
    margin-bottom: 20px;
}

.ctm-policy-block:last-child{
    margin-bottom: 0;
}

.ctm-policy-block h3{
    margin: 0 0 10px 0;
    color: #2271b1;
    font-size: 1.2em;
    border-bottom: 2px solid #2271b1;
    padding-bottom: 5px;
}

.ctm-policy-content{
    line-height: 1.6;
    color: #333;
}

/* CTA Section */
.ctm-package-cta{
    margin: 40px 0 20px;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    text-align: center;
    color: #fff;
}

.ctm-cta-message{
    font-size: 1.1em;
    margin: 0 0 20px;
    color: #fff;
}

.ctm-cta-buttons{
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

.ctm-cta-primary,
.ctm-cta-secondary{
    padding: 15px 30px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 6px;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
}

.ctm-cta-primary{
    background: #fff;
    color: #667eea;
    border: 2px solid #fff;
}

.ctm-cta-primary:hover{
    background: transparent;
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.ctm-cta-secondary{
    background: transparent;
    color: #fff;
    border: 2px solid #fff;
}

.ctm-cta-secondary:hover{
    background: #fff;
    color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

@media (max-width: 600px) {
    .ctm-cta-buttons {
        flex-direction: column;
    }
    
    .ctm-cta-primary,
    .ctm-cta-secondary {
        width: 100%;
    }
}
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
            <p>
                <label><?php _e( 'Preferred start date', 'cst_system' ); ?><br/>
                    <input type="date" name="start_date" />
                </label>
            </p>
            <p>
                <label><?php _e( 'Preferred end date', 'cst_system' ); ?><br/>
                    <input type="date" name="end_date" />
                </label>
            </p>
            <p><label><?php _e( 'Notes', 'cst_system' ); ?><br/><input type="text" name="dates" placeholder="Optional notes or alternative dates" /></label></p>
            <p><button type="submit" class="button button-primary"><?php _e( 'Submit interest', 'cst_system' ); ?></button></p>
            <div id="ctm-interest-message" style="display:none"></div>
        </form>
    </div>
</div>

<script>
(function(){
    var ajaxurl = '<?php echo admin_url( "admin-ajax.php" ); ?>';
    
    // Close modal handler
    var closeBtn = document.getElementById('ctm-interest-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', function(e){ 
            e.preventDefault(); 
            document.getElementById('ctm-interest-modal').style.display = 'none'; 
        });
    }

    // Form submission handler
    var form = document.getElementById('ctm-interest-form');
    if (form) {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const data = new FormData(this);
            var n = document.querySelector('#ctm-interest-form input[name="ctm_interest_nonce"]') || document.querySelector('#ctm-interest-form input[name="ctm_interest_nonce_field"]');
            if ( n ) data.append('nonce', n.value );
            data.append('action','ctm_submit_interest');

            fetch(ajaxurl, { method: 'POST', credentials: 'same-origin', body: data }).then(r=>r.json()).then(function(res){
                const msg = document.getElementById('ctm-interest-message');
                msg.style.display = 'block';
                if (res.success) {
                    msg.textContent = 'Thanks — we have received your interest.';
                    msg.style.color = '#28a745';
                    form.reset();
                    setTimeout(function() {
                        document.getElementById('ctm-interest-modal').style.display = 'none';
                    }, 2000);
                } else {
                    msg.textContent = 'Error: ' + (res.data || 'unknown');
                    msg.style.color = '#dc3545';
                }
            }).catch(function(err) {
                const msg = document.getElementById('ctm-interest-message');
                msg.style.display = 'block';
                msg.textContent = 'Error submitting form. Please try again.';
                msg.style.color = '#dc3545';
            });
        });
    }
})();
</script>

<?php
get_footer();

?>
