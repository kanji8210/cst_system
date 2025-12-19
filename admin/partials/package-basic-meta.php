<?php
// File: admin/partials/package-basic-meta.php
?>

<div class="ctm-form-grid">
    <div class="ctm-form-group">
        <label for="_package_code"><?php _e('Package Code', 'cayman-tours-manager'); ?></label>
        <input type="text" id="_package_code" name="_package_code" 
               value="<?php echo esc_attr($package_code); ?>" 
               class="regular-text">
        <button type="button" id="generate-package-code" class="button button-small">
            <?php _e('Generate', 'cayman-tours-manager'); ?>
        </button>
        <p class="description"><?php _e('Unique identifier for this package', 'cayman-tours-manager'); ?></p>
    </div>
    
    <div class="ctm-form-group">
        <label for="_duration_type"><?php _e('Duration Type', 'cayman-tours-manager'); ?></label>
        <select id="_duration_type" name="_duration_type">
            <option value="hours" <?php selected($duration_type, 'hours'); ?>>
                <?php _e('Hours', 'cayman-tours-manager'); ?>
            </option>
            <option value="days" <?php selected($duration_type, 'days'); ?>>
                <?php _e('Days', 'cayman-tours-manager'); ?>
            </option>
        </select>
    </div>
    
    <div class="ctm-form-group">
        <label for="_duration_value"><?php _e('Duration Value', 'cayman-tours-manager'); ?></label>
        <input type="number" id="_duration_value" name="_duration_value" 
               value="<?php echo esc_attr($duration_value); ?>" 
               min="1" step="1" class="small-text">
        <span id="duration-unit">
            <?php echo ($duration_type == 'hours') ? __('hours', 'cayman-tours-manager') : __('days', 'cayman-tours-manager'); ?>
        </span>
    </div>
</div>

<div class="ctm-form-grid">
    <div class="ctm-form-group">
        <label for="_difficulty"><?php _e('Difficulty Level', 'cayman-tours-manager'); ?></label>
        <select id="_difficulty" name="_difficulty">
            <option value="easy" <?php selected($difficulty, 'easy'); ?>>
                <?php _e('Easy', 'cayman-tours-manager'); ?>
            </option>
            <option value="moderate" <?php selected($difficulty, 'moderate'); ?>>
                <?php _e('Moderate', 'cayman-tours-manager'); ?>
            </option>
            <option value="strenuous" <?php selected($difficulty, 'strenuous'); ?>>
                <?php _e('Strenuous', 'cayman-tours-manager'); ?>
            </option>
        </select>
    </div>
    
    <div class="ctm-form-group">
        <label for="_min_age"><?php _e('Minimum Age', 'cayman-tours-manager'); ?></label>
        <input type="number" id="_min_age" name="_min_age" 
               value="<?php echo esc_attr($min_age); ?>" 
               min="0" step="1" class="small-text">
    </div>
    
    <div class="ctm-form-group">
        <label for="_max_participants"><?php _e('Maximum Participants', 'cayman-tours-manager'); ?></label>
        <input type="number" id="_max_participants" name="_max_participants" 
               value="<?php echo esc_attr($max_participants); ?>" 
               min="1" step="1" class="small-text">
    </div>
</div>

<div class="ctm-form-group">
    <label for="_meeting_point"><?php _e('Meeting Point', 'cayman-tours-manager'); ?></label>
    <input type="text" id="_meeting_point" name="_meeting_point" 
           value="<?php echo esc_attr($meeting_point); ?>" 
           class="regular-text" 
           placeholder="<?php esc_attr_e('e.g., George Town Cruise Terminal', 'cayman-tours-manager'); ?>">
</div>

<input type="hidden" id="_itinerary" name="_itinerary" value="">