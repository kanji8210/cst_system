<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="form-table">
    <tr>
        <th><label for="_inclusions"><?php _e( 'Inclusions (JSON array)', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_inclusions" id="_inclusions" rows="4" cols="60"><?php echo esc_textarea( is_array( $inclusions ) ? wp_json_encode( $inclusions ) : $inclusions ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="_exclusions"><?php _e( 'Exclusions (JSON array)', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_exclusions" id="_exclusions" rows="4" cols="60"><?php echo esc_textarea( is_array( $exclusions ) ? wp_json_encode( $exclusions ) : $exclusions ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="_addons"><?php _e( 'Add-ons (JSON array)', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_addons" id="_addons" rows="4" cols="60"><?php echo esc_textarea( is_array( $addons ) ? wp_json_encode( $addons ) : $addons ); ?></textarea>
        </td>
    </tr>
</table>
