<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="ctm-itinerary-editor">
    <h3><?php _e( 'Itinerary Builder', 'cst_system' ); ?></h3>
    <p class="description"><?php _e( 'Define your day-by-day itinerary in JSON format. Click "Load Example" to see the structure.', 'cst_system' ); ?></p>
    
    <div class="ctm-json-controls">
        <button type="button" class="button" id="ctm-load-itinerary-example"><?php _e( 'Load Example', 'cst_system' ); ?></button>
        <button type="button" class="button" id="ctm-format-itinerary-json"><?php _e( 'Format JSON', 'cst_system' ); ?></button>
        <button type="button" class="button" id="ctm-validate-itinerary-json"><?php _e( 'Validate JSON', 'cst_system' ); ?></button>
        <span id="ctm-itinerary-validation" style="margin-left: 10px;"></span>
    </div>
    
    <textarea name="_itinerary" id="_itinerary" rows="15" cols="80" class="ctm-json-input"><?php echo esc_textarea( is_array( $itinerary ) ? wp_json_encode( $itinerary, JSON_PRETTY_PRINT ) : $itinerary ); ?></textarea>
    
    <div class="ctm-json-help">
        <details>
            <summary><strong><?php _e( 'JSON Structure Guide', 'cst_system' ); ?></strong></summary>
            <div class="ctm-json-example">
                <p><strong><?php _e( 'Example Format:', 'cst_system' ); ?></strong></p>
                <pre>[
  {
    "day": 1,
    "title": "Arrival & Beach Exploration",
    "description": "Welcome to Cayman Islands",
    "activities": [
      {
        "start_time": "09:00",
        "end_time": "10:30",
        "title": "Airport Pickup",
        "location": "Owen Roberts International Airport",
        "description": "Meet and greet with tour guide"
      },
      {
        "start_time": "11:00",
        "end_time": "13:00",
        "title": "Seven Mile Beach",
        "location": "Seven Mile Beach, Grand Cayman",
        "description": "Relax at the world-famous beach"
      }
    ]
  },
  {
    "day": 2,
    "title": "Marine Adventure",
    "description": "Explore underwater wonders",
    "activities": [
      {
        "start_time": "08:00",
        "end_time": "12:00",
        "title": "Stingray City Tour",
        "location": "Stingray City Sandbar",
        "description": "Swim with friendly stingrays"
      }
    ]
  }
]</pre>
                <p><strong><?php _e( 'Field Descriptions:', 'cst_system' ); ?></strong></p>
                <ul>
                    <li><code>day</code> - <?php _e( 'Day number (integer)', 'cst_system' ); ?></li>
                    <li><code>title</code> - <?php _e( 'Day title/theme', 'cst_system' ); ?></li>
                    <li><code>description</code> - <?php _e( 'Day overview (optional)', 'cst_system' ); ?></li>
                    <li><code>activities</code> - <?php _e( 'Array of activities for this day', 'cst_system' ); ?></li>
                    <li><code>start_time</code> - <?php _e( 'Activity start time (HH:MM format)', 'cst_system' ); ?></li>
                    <li><code>end_time</code> - <?php _e( 'Activity end time (HH:MM format)', 'cst_system' ); ?></li>
                    <li><code>location</code> - <?php _e( 'Activity location (optional)', 'cst_system' ); ?></li>
                </ul>
            </div>
        </details>
    </div>
</div>
