<?php
// File: admin/partials/package-title-description-meta.php
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="ctm-title-description-wrapper">
    <div class="ctm-form-group" style="margin-bottom: 20px;">
        <label for="_package_title" style="font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px;">
            <?php _e('Package Name/Title', 'cayman-tours-manager'); ?>
            <span style="color: #d63638;">*</span>
        </label>
        <input type="text" 
               id="_package_title" 
               name="_package_title" 
               value="<?php echo esc_attr($package_title); ?>" 
               class="widefat" 
               style="font-size: 16px; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;"
               placeholder="<?php esc_attr_e('e.g., Stingray City Adventure Tour', 'cayman-tours-manager'); ?>"
               required>
        <p class="description" style="margin-top: 6px;">
            <?php _e('Enter a descriptive and compelling name for your package. This will be displayed to customers.', 'cayman-tours-manager'); ?>
        </p>
    </div>
    
    <div class="ctm-form-group">
        <label for="_package_description" style="font-weight: 600; font-size: 14px; display: block; margin-bottom: 8px;">
            <?php _e('Full Description', 'cayman-tours-manager'); ?>
            <span style="color: #d63638;">*</span>
        </label>
        <p class="description" style="margin-bottom: 8px;">
            <?php _e('Provide a detailed description of the package, including what makes it special, what guests will experience, and any important highlights.', 'cayman-tours-manager'); ?>
        </p>
        <?php
        $settings = array(
            'textarea_name' => '_package_description',
            'media_buttons' => true,
            'textarea_rows' => 12,
            'teeny'         => false,
            'quicktags'     => true,
            'tinymce'       => array(
                'toolbar1' => 'formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,unlink,wp_adv',
                'toolbar2' => 'strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help'
            )
        );
        wp_editor($package_description, '_package_description', $settings);
        ?>
    </div>
</div>

<style>
.ctm-title-description-wrapper {
    background: #fff;
    padding: 15px;
}

#_package_title:focus {
    border-color: #2271b1;
    outline: none;
    box-shadow: 0 0 0 1px #2271b1;
}

.ctm-title-description-wrapper label span {
    font-weight: 400;
    font-size: 13px;
}
</style>
