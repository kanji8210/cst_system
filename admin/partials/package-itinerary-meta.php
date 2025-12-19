<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-itinerary-editor">
    <p><?php _e( 'Itinerary (stored as JSON). You can paste JSON or leave empty to edit using the itinerary builder.', 'cst_system' ); ?></p>
    <textarea name="_itinerary" id="_itinerary" rows="8" cols="80"><?php echo esc_textarea( is_array( $itinerary ) ? wp_json_encode( $itinerary ) : $itinerary ); ?></textarea>
    <p class="description"><?php _e( 'Example: [{"day":1,"activities":[{"time":"09:00","title":"Arrival","location":"Airport"}]}]', 'cst_system' ); ?></p>
</div>
