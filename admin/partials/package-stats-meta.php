<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-package-stats">
    <p><strong><?php _e( 'Bookings', 'cst_system' ); ?>:</strong> <?php echo intval( $booking_count ); ?></p>
    <p><strong><?php _e( 'Revenue', 'cst_system' ); ?>:</strong> <?php echo esc_html( number_format_i18n( floatval( $revenue ), 2 ) ); ?></p>
    <p><strong><?php _e( 'Average Rating', 'cst_system' ); ?>:</strong> <?php echo esc_html( $rating ); ?></p>
</div>
