<?php
if ( ! defined( 'ABSPATH' ) ) exit;
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
        <td><input type="text" name="_child_age_range" id="_child_age_range" value="<?php echo esc_attr( $child_age_range ); ?>" class="regular-text" /></td>
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
        <th><label for="_seasonal_pricing"><?php _e( 'Seasonal Pricing (JSON)', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_seasonal_pricing" id="_seasonal_pricing" rows="6" cols="60"><?php echo esc_textarea( is_array( $seasonal_pricing ) ? wp_json_encode( $seasonal_pricing ) : $seasonal_pricing ); ?></textarea>
            <p class="description"><?php _e( 'JSON array of seasonal pricing entries (start_date,end_date,price).', 'cst_system' ); ?></p>
        </td>
    </tr>
    <tr>
        <th><label for="_commission_rate"><?php _e( 'Commission Rate (%)', 'cst_system' ); ?></label></th>
        <td><input type="number" step="0.01" name="_commission_rate" id="_commission_rate" value="<?php echo esc_attr( $commission_rate ); ?>" class="regular-text" /></td>
    </tr>
</table>
