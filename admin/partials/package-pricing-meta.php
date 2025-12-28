<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Parse seasonal pricing
$seasonal_pricing_array = array();
if ( ! empty( $seasonal_pricing ) ) {
    if ( is_array( $seasonal_pricing ) ) {
        $seasonal_pricing_array = $seasonal_pricing;
    } else {
        $decoded = json_decode( $seasonal_pricing, true );
        if ( is_array( $decoded ) ) {
            $seasonal_pricing_array = $decoded;
        }
    }
}
?>
<table class="form-table">
    <tr>
        <th><label for="_base_price"><?php _e( 'Base Price', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_base_price" id="_base_price" value="<?php echo esc_attr( $base_price ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="_child_price"><?php _e( 'Child Price', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_child_price" id="_child_price" value="<?php echo esc_attr( $child_price ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="_child_age_range"><?php _e( 'Child Age Range', 'cst_system' ); ?></label></th>
        <td><input type="text" name="_child_age_range" id="_child_age_range" value="<?php echo esc_attr( $child_age_range ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g., 3-12 years', 'cst_system' ); ?>" /></td>
    </tr>
    <tr>
        <th><label for="_infant_price"><?php _e( 'Infant Price', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_infant_price" id="_infant_price" value="<?php echo esc_attr( $infant_price ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label for="_single_supplement"><?php _e( 'Single Supplement', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_single_supplement" id="_single_supplement" value="<?php echo esc_attr( $single_supplement ); ?>" class="regular-text" /></td>
    </tr>
    <tr>
        <th><label><?php _e( 'Seasonal Pricing', 'cst_system' ); ?></label></th>
        <td>
            <div id="ctm-seasonal-pricing-list">
                <?php if ( ! empty( $seasonal_pricing_array ) ): ?>
                    <?php foreach ( $seasonal_pricing_array as $season ): ?>
                        <div class="ctm-seasonal-item">
                            <input type="text" class="ctm-season-name" name="_seasonal_name[]" value="<?php echo esc_attr( isset( $season['name'] ) ? $season['name'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Season name', 'cst_system' ); ?>" style="width: 20%;">
                            <input type="date" class="ctm-season-start" name="_seasonal_start[]" value="<?php echo esc_attr( isset( $season['start_date'] ) ? $season['start_date'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Start date', 'cst_system' ); ?>">
                            <input type="date" class="ctm-season-end" name="_seasonal_end[]" value="<?php echo esc_attr( isset( $season['end_date'] ) ? $season['end_date'] : '' ); ?>" placeholder="<?php esc_attr_e( 'End date', 'cst_system' ); ?>">
                            <input type="number" step="0.01" class="ctm-season-price" name="_seasonal_price[]" value="<?php echo esc_attr( isset( $season['price'] ) ? $season['price'] : '' ); ?>" placeholder="<?php esc_attr_e( 'Price', 'cst_system' ); ?>" style="width: 15%;">
                            <button type="button" class="button ctm-remove-season"><?php _e( 'Remove', 'cst_system' ); ?></button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <button type="button" class="button ctm-add-season"><?php _e( 'Add Seasonal Pricing', 'cst_system' ); ?></button>
            <p class="description"><?php _e( 'Define pricing for specific date ranges (e.g., high season, low season).', 'cst_system' ); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="_commission_rate"><?php _e( 'Commission Rate (%)', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_commission_rate" id="_commission_rate" value="<?php echo esc_attr( $commission_rate ); ?>" class="regular-text" /></td>
    </tr>
</table>
