<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Parse existing data
$inclusions_array = array();
$exclusions_array = array();
$addons_array = array();

if ( ! empty( $inclusions ) ) {
    if ( is_array( $inclusions ) ) {
        $inclusions_array = $inclusions;
    } else {
        $decoded = json_decode( $inclusions, true );
        if ( is_array( $decoded ) ) {
            $inclusions_array = $decoded;
        }
    }
}

if ( ! empty( $exclusions ) ) {
    if ( is_array( $exclusions ) ) {
        $exclusions_array = $exclusions;
    } else {
        $decoded = json_decode( $exclusions, true );
        if ( is_array( $decoded ) ) {
            $exclusions_array = $decoded;
        }
    }
}

if ( ! empty( $addons ) ) {
    if ( is_array( $addons ) ) {
        $addons_array = $addons;
    } else {
        $decoded = json_decode( $addons, true );
        if ( is_array( $decoded ) ) {
            $addons_array = $decoded;
        }
    }
}
?>

<div class="ctm-inclusions-wrapper">
    <h3><?php _e( 'Inclusions', 'cst_system' ); ?></h3>
    <div id="ctm-inclusions-list">
        <?php if ( ! empty( $inclusions_array ) ): ?>
            <?php foreach ( $inclusions_array as $item ): ?>
                <div class="ctm-list-item">
                    <input type="text" class="ctm-item-input" name="_inclusions_items[]" value="<?php echo esc_attr( $item ); ?>" placeholder="<?php esc_attr_e( 'e.g., Hotel accommodation', 'cst_system' ); ?>">
                    <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ctm-list-item">
                <input type="text" class="ctm-item-input" name="_inclusions_items[]" value="" placeholder="<?php esc_attr_e( 'e.g., Hotel accommodation', 'cst_system' ); ?>">
                <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" class="button ctm-add-item" data-target="ctm-inclusions-list"><?php _e( 'Add Inclusion', 'cst_system' ); ?></button>
</div>

<div class="ctm-exclusions-wrapper" style="margin-top: 20px;">
    <h3><?php _e( 'Exclusions', 'cst_system' ); ?></h3>
    <div id="ctm-exclusions-list">
        <?php if ( ! empty( $exclusions_array ) ): ?>
            <?php foreach ( $exclusions_array as $item ): ?>
                <div class="ctm-list-item">
                    <input type="text" class="ctm-item-input" name="_exclusions_items[]" value="<?php echo esc_attr( $item ); ?>" placeholder="<?php esc_attr_e( 'e.g., Airfare', 'cst_system' ); ?>">
                    <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ctm-list-item">
                <input type="text" class="ctm-item-input" name="_exclusions_items[]" value="" placeholder="<?php esc_attr_e( 'e.g., Airfare', 'cst_system' ); ?>">
                <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" class="button ctm-add-item" data-target="ctm-exclusions-list"><?php _e( 'Add Exclusion', 'cst_system' ); ?></button>
</div>

<div class="ctm-addons-wrapper" style="margin-top: 20px;">
    <h3><?php _e( 'Add-ons', 'cst_system' ); ?></h3>
    <div id="ctm-addons-list">
        <?php if ( ! empty( $addons_array ) ): ?>
            <?php foreach ( $addons_array as $item ): ?>
                <div class="ctm-list-item">
                    <input type="text" class="ctm-item-input ctm-addon-name" name="_addons_name[]" value="<?php echo esc_attr( isset( $item['name'] ) ? $item['name'] : ( is_string( $item ) ? $item : '' ) ); ?>" placeholder="<?php esc_attr_e( 'Add-on name', 'cst_system' ); ?>" style="width: 40%;">
                    <input type="number" step="0.01" class="ctm-item-input ctm-addon-price" name="_addons_price[]" value="<?php echo esc_attr( isset( $item['price'] ) ? $item['price'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Price', 'cst_system' ); ?>" style="width: 20%;">
                    <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="ctm-list-item">
                <input type="text" class="ctm-item-input ctm-addon-name" name="_addons_name[]" value="" placeholder="<?php esc_attr_e( 'Add-on name', 'cst_system' ); ?>" style="width: 40%;">
                <input type="number" step="0.01" class="ctm-item-input ctm-addon-price" name="_addons_price[]" value="" placeholder="<?php esc_attr_e( 'Price', 'cst_system' ); ?>" style="width: 20%;">
                <button type="button" class="button ctm-remove-item"><?php _e( 'Remove', 'cst_system' ); ?></button>
            </div>
        <?php endif; ?>
    </div>
    <button type="button" class="button ctm-add-item" data-target="ctm-addons-list" data-type="addon"><?php _e( 'Add Add-on', 'cst_system' ); ?></button>
</div>
