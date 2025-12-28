<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-policies-wrapper">
    <div class="ctm-policy-section">
        <h4><?php _e( 'What to Bring', 'cst_system' ); ?></h4>
        <p class="description"><?php _e( 'List items guests should bring for this tour (e.g., sunscreen, camera, towel, water shoes).', 'cst_system' ); ?></p>
        <textarea name="_what_to_bring" id="_what_to_bring" rows="5" class="widefat ctm-policy-textarea" placeholder="<?php esc_attr_e('e.g., Sunscreen, Camera, Towel, Water shoes, Swimsuit', 'cst_system'); ?>"><?php echo esc_textarea( $what_to_bring ); ?></textarea>
    </div>
    
    <div class="ctm-policy-section">
        <h4><?php _e( 'Requirements', 'cst_system' ); ?></h4>
        <p class="description"><?php _e( 'Specify any requirements guests must meet (e.g., age restrictions, fitness level, medical conditions).', 'cst_system' ); ?></p>
        <textarea name="_requirements" id="_requirements" rows="5" class="widefat ctm-policy-textarea" placeholder="<?php esc_attr_e('e.g., Minimum age 8 years, Must be able to swim, No serious medical conditions', 'cst_system'); ?>"><?php echo esc_textarea( $requirements ); ?></textarea>
    </div>
    
    <div class="ctm-policy-section">
        <h4><?php _e( 'Cancellation Policy', 'cst_system' ); ?></h4>
        <p class="description"><?php _e( 'Define your cancellation and refund policy clearly.', 'cst_system' ); ?></p>
        <textarea name="_cancellation_policy" id="_cancellation_policy" rows="6" class="widefat ctm-policy-textarea" placeholder="<?php esc_attr_e('e.g., Full refund if cancelled 48 hours before departure. 50% refund if cancelled 24 hours before. No refund for same-day cancellations.', 'cst_system'); ?>"><?php echo esc_textarea( $cancellation_policy ); ?></textarea>
    </div>
</div>

<style>
.ctm-policies-wrapper {
    background: #fff;
    padding: 15px;
}

.ctm-policy-section {
    margin-bottom: 25px;
    padding-bottom: 20px;
    border-bottom: 1px solid #eee;
}

.ctm-policy-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.ctm-policy-section h4 {
    margin: 0 0 8px 0;
    color: #23282d;
    font-size: 14px;
    font-weight: 600;
}

.ctm-policy-section .description {
    margin: 0 0 10px 0;
    font-style: italic;
}

.ctm-policy-textarea {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
    font-size: 13px;
    line-height: 1.6;
    resize: vertical;
}

.ctm-policy-textarea:focus {
    border-color: #2271b1;
    outline: none;
    box-shadow: 0 0 0 1px #2271b1;
}
</style>
