<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-cta-wrapper">
    <p class="description" style="margin-bottom: 20px;">
        <?php _e( 'Configure the call-to-action buttons and contact information displayed on the package page.', 'cst_system' ); ?>
    </p>
    
    <div class="ctm-cta-section">
        <h4><?php _e( 'Primary CTA Button', 'cst_system' ); ?></h4>
        
        <div class="ctm-form-group">
            <label for="_cta_primary_text"><?php _e( 'Button Text', 'cst_system' ); ?></label>
            <input type="text" 
                   id="_cta_primary_text" 
                   name="_cta_primary_text" 
                   value="<?php echo esc_attr( $cta_primary_text ); ?>" 
                   class="regular-text"
                   placeholder="<?php esc_attr_e( 'e.g., Express Interest', 'cst_system' ); ?>">
            <p class="description"><?php _e( 'Text displayed on the primary action button.', 'cst_system' ); ?></p>
        </div>
        
        <div class="ctm-form-group">
            <label for="_cta_primary_type"><?php _e( 'Action Type', 'cst_system' ); ?></label>
            <select id="_cta_primary_type" name="_cta_primary_type" class="regular-text">
                <option value="form" <?php selected( $cta_primary_type, 'form' ); ?>><?php _e( 'Show Interest Form', 'cst_system' ); ?></option>
                <option value="email" <?php selected( $cta_primary_type, 'email' ); ?>><?php _e( 'Send Email', 'cst_system' ); ?></option>
                <option value="phone" <?php selected( $cta_primary_type, 'phone' ); ?>><?php _e( 'Call Phone Number', 'cst_system' ); ?></option>
                <option value="whatsapp" <?php selected( $cta_primary_type, 'whatsapp' ); ?>><?php _e( 'WhatsApp Message', 'cst_system' ); ?></option>
                <option value="url" <?php selected( $cta_primary_type, 'url' ); ?>><?php _e( 'Custom URL', 'cst_system' ); ?></option>
            </select>
        </div>
        
        <div class="ctm-form-group">
            <label for="_cta_primary_value"><?php _e( 'Action Value', 'cst_system' ); ?></label>
            <input type="text" 
                   id="_cta_primary_value" 
                   name="_cta_primary_value" 
                   value="<?php echo esc_attr( $cta_primary_value ); ?>" 
                   class="regular-text"
                   placeholder="<?php esc_attr_e( 'Email, phone, URL, or WhatsApp number', 'cst_system' ); ?>">
            <p class="description"><?php _e( 'Enter the email address, phone number, URL or WhatsApp number based on action type selected.', 'cst_system' ); ?></p>
        </div>
    </div>
    
    <div class="ctm-cta-section">
        <h4><?php _e( 'Secondary CTA Button', 'cst_system' ); ?></h4>
        
        <div class="ctm-form-group">
            <label for="_cta_secondary_text"><?php _e( 'Button Text', 'cst_system' ); ?></label>
            <input type="text" 
                   id="_cta_secondary_text" 
                   name="_cta_secondary_text" 
                   value="<?php echo esc_attr( $cta_secondary_text ); ?>" 
                   class="regular-text"
                   placeholder="<?php esc_attr_e( 'e.g., Contact Us to Book', 'cst_system' ); ?>">
            <p class="description"><?php _e( 'Text displayed on the secondary action button.', 'cst_system' ); ?></p>
        </div>
        
        <div class="ctm-form-group">
            <label for="_cta_secondary_type"><?php _e( 'Action Type', 'cst_system' ); ?></label>
            <select id="_cta_secondary_type" name="_cta_secondary_type" class="regular-text">
                <option value="email" <?php selected( $cta_secondary_type, 'email' ); ?>><?php _e( 'Send Email', 'cst_system' ); ?></option>
                <option value="phone" <?php selected( $cta_secondary_type, 'phone' ); ?>><?php _e( 'Call Phone Number', 'cst_system' ); ?></option>
                <option value="whatsapp" <?php selected( $cta_secondary_type, 'whatsapp' ); ?>><?php _e( 'WhatsApp Message', 'cst_system' ); ?></option>
                <option value="url" <?php selected( $cta_secondary_type, 'url' ); ?>><?php _e( 'Custom URL', 'cst_system' ); ?></option>
                <option value="form" <?php selected( $cta_secondary_type, 'form' ); ?>><?php _e( 'Show Contact Form', 'cst_system' ); ?></option>
            </select>
        </div>
        
        <div class="ctm-form-group">
            <label for="_cta_secondary_value"><?php _e( 'Action Value', 'cst_system' ); ?></label>
            <input type="text" 
                   id="_cta_secondary_value" 
                   name="_cta_secondary_value" 
                   value="<?php echo esc_attr( $cta_secondary_value ); ?>" 
                   class="regular-text"
                   placeholder="<?php esc_attr_e( 'Email, phone, URL, or WhatsApp number', 'cst_system' ); ?>">
            <p class="description"><?php _e( 'Enter the email address, phone number, URL or WhatsApp number based on action type selected.', 'cst_system' ); ?></p>
        </div>
    </div>
    
    <div class="ctm-cta-section">
        <h4><?php _e( 'Additional Information', 'cst_system' ); ?></h4>
        
        <div class="ctm-form-group">
            <label for="_cta_message"><?php _e( 'Custom Message', 'cst_system' ); ?></label>
            <textarea id="_cta_message" 
                      name="_cta_message" 
                      rows="3" 
                      class="widefat"
                      placeholder="<?php esc_attr_e( 'e.g., Have questions? Our team is ready to help you plan the perfect adventure!', 'cst_system' ); ?>"><?php echo esc_textarea( $cta_message ); ?></textarea>
            <p class="description"><?php _e( 'Optional message displayed near the CTA buttons.', 'cst_system' ); ?></p>
        </div>
        
        <div class="ctm-form-group">
            <label>
                <input type="checkbox" 
                       id="_cta_show_availability" 
                       name="_cta_show_availability" 
                       value="1"
                       <?php checked( $cta_show_availability, '1' ); ?>>
                <?php _e( 'Show availability calendar/dates', 'cst_system' ); ?>
            </label>
        </div>
    </div>
</div>

<style>
.ctm-cta-wrapper {
    background: #fff;
    padding: 15px;
}

.ctm-cta-section {
    margin-bottom: 25px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #e5e5e5;
    border-radius: 4px;
}

.ctm-cta-section:last-child {
    margin-bottom: 0;
}

.ctm-cta-section h4 {
    margin: 0 0 15px 0;
    color: #23282d;
    font-size: 14px;
    font-weight: 600;
    padding-bottom: 10px;
    border-bottom: 2px solid #2271b1;
}

.ctm-form-group {
    margin-bottom: 15px;
}

.ctm-form-group:last-child {
    margin-bottom: 0;
}

.ctm-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 5px;
    color: #1d2327;
}

.ctm-form-group input[type="text"],
.ctm-form-group select,
.ctm-form-group textarea {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 13px;
}

.ctm-form-group input[type="text"]:focus,
.ctm-form-group select:focus,
.ctm-form-group textarea:focus {
    border-color: #2271b1;
    outline: none;
    box-shadow: 0 0 0 1px #2271b1;
}

.ctm-form-group .description {
    margin: 5px 0 0 0;
    font-style: italic;
    color: #646970;
}
</style>
