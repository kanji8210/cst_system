<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<table class="form-table">
    <tr>
        <th><label for="_cancellation_policy"><?php _e( 'Cancellation Policy', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_cancellation_policy" id="_cancellation_policy" rows="6" cols="60"><?php echo esc_textarea( $cancellation_policy ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="_requirements"><?php _e( 'Requirements', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_requirements" id="_requirements" rows="4" cols="60"><?php echo esc_textarea( $requirements ); ?></textarea>
        </td>
    </tr>
    <tr>
        <th><label for="_what_to_bring"><?php _e( 'What to Bring', 'cst_system' ); ?></label></th>
        <td>
            <textarea name="_what_to_bring" id="_what_to_bring" rows="4" cols="60"><?php echo esc_textarea( $what_to_bring ); ?></textarea>
        </td>
    </tr>
</table>
