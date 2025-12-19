<?php
/**
 * Package creation admin UI (tabs) - Essentials, Logistics, Pricing (basic)
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// If editing an existing package via ?post_id= or id param
$post_id = isset( $_GET['post_id'] ) ? intval( $_GET['post_id'] ) : 0;
$package = null;
if ( $post_id ) {
    $package = get_post( $post_id );
}

?>
<div class="wrap">
    <h1><?php echo $package ? 'Edit Package' : 'Add New Package'; ?></h1>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <?php wp_nonce_field( 'ctm_save_package', 'ctm_package_nonce' ); ?>
        <input type="hidden" name="action" value="ctm_save_package" />
        <?php if ( $package ): ?><input type="hidden" name="post_id" value="<?php echo esc_attr( $package->ID ); ?>" /><?php endif; ?>

        <h2 class="nav-tab-wrapper">
            <a class="nav-tab nav-tab-active" href="#tab-essentials">Essentials</a>
            <a class="nav-tab" href="#tab-logistics">Logistics</a>
            <a class="nav-tab" href="#tab-pricing">Pricing</a>
        </h2>

        <div id="tab-essentials" class="ctm-tab" style="display:block">
            <table class="form-table">
                <tr>
                    <th><label for="title">Package Title</label></th>
                    <td><input type="text" name="title" id="title" value="<?php echo $package ? esc_attr( $package->post_title ) : ''; ?>" class="regular-text" required></td>
                </tr>
                <tr>
                    <th><label for="ctm_tagline">Short Tagline</label></th>
                    <td><input type="text" name="ctm_tagline" id="ctm_tagline" value="<?php echo $package ? esc_attr( get_post_meta( $package->ID, 'ctm_tagline', true ) ) : ''; ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="content">Full Description</label></th>
                    <td><?php
                        $content = $package ? $package->post_content : '';
                        wp_editor( $content, 'content', array( 'textarea_name' => 'content', 'media_buttons' => true ) );
                    ?></td>
                </tr>
                <tr>
                    <th><label for="ctm_package_type">Package Type</label></th>
                    <td>
                        <select name="ctm_package_type" id="ctm_package_type">
                            <?php $types = array( 'Adventure','Luxury','Family','Romantic','Group' );
                            $val = $package ? get_post_meta( $package->ID, 'ctm_package_type', true ) : '';
                            foreach ( $types as $t ) {
                                printf( '<option value="%s" %s>%s</option>', esc_attr( $t ), selected( $val, $t, false ), esc_html( $t ) );
                            }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="ctm_gallery">Featured Image / Gallery (URLs or IDs comma-separated)</label></th>
                    <td><input type="text" name="ctm_gallery" id="ctm_gallery" value="<?php echo $package ? esc_attr( get_post_meta( $package->ID, 'ctm_gallery', true ) ) : ''; ?>" class="regular-text">
                    <p class="description">You can paste URLs or attachment IDs separated by commas. Media Library integration coming next.</p></td>
                </tr>
            </table>
        </div>

        <div id="tab-logistics" class="ctm-tab" style="display:none">
            <table class="form-table">
                <tr>
                    <th><label for="ctm_duration_type">Duration Type</label></th>
                    <td>
                        <select name="ctm_duration_type" id="ctm_duration_type">
                            <?php $dtypes = array( 'Hours','Days','Multi-day' ); $dval = $package ? get_post_meta( $package->ID, 'ctm_duration_type', true ) : ''; foreach ( $dtypes as $dt ) printf('<option value="%s" %s>%s</option>', esc_attr($dt), selected($dval,$dt,false), esc_html($dt)); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="ctm_duration_value">Total Time</label></th>
                    <td><input type="text" name="ctm_duration_value" id="ctm_duration_value" value="<?php echo $package ? esc_attr( get_post_meta( $package->ID, 'ctm_duration_value', true ) ) : ''; ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>

        <div id="tab-pricing" class="ctm-tab" style="display:none">
            <table class="form-table">
                <tr>
                    <th><label for="ctm_base_price">Base Price (adult)</label></th>
                    <td><input type="number" step="0.01" name="ctm_base_price" id="ctm_base_price" value="<?php echo $package ? esc_attr( get_post_meta( $package->ID, 'ctm_base_price', true ) ) : ''; ?>" class="regular-text"></td>
                </tr>
            </table>
        </div>

        <div id="tab-itinerary" class="ctm-tab" style="display:none">
            <h3>Itinerary (Drag &amp; drop to reorder days and activities)</h3>
            <p><button id="ctm-add-day" class="button">Add Day</button></p>

            <div id="ctm-days">
                <?php
                // Load existing itinerary if present
                $existing_it = array();
                if ( $package ) {
                    $raw = get_post_meta( $package->ID, 'ctm_itinerary', true );
                    if ( $raw ) {
                        $decoded = json_decode( $raw, true );
                        if ( is_array( $decoded ) ) $existing_it = $decoded;
                    }
                }

                if ( empty( $existing_it ) ) {
                    // render one empty day
                    $existing_it = array( array( 'day' => 1, 'activities' => array() ) );
                }

                foreach ( $existing_it as $didx => $day ) :
                    ?>
                    <div class="ctm-day" data-day="<?php echo esc_attr( $day['day'] ?? ( $didx+1 ) ); ?>">
                        <h4 class="day-handle">Day <?php echo esc_html( $day['day'] ?? ( $didx+1 ) ); ?></h4>
                        <div class="ctm-activities">
                            <?php if ( ! empty( $day['activities'] ) ):
                                foreach ( $day['activities'] as $act ): ?>
                                    <div class="ctm-activity">
                                        <span class="act-handle">☰</span>
                                        <p><label>Time <input class="act-time" name="act_time[]" value="<?php echo esc_attr( $act['time'] ); ?>" /></label></p>
                                        <p><label>Title <input class="act-title" name="act_title[]" value="<?php echo esc_attr( $act['title'] ); ?>" /></label></p>
                                        <p><label>Description <textarea class="act-desc" name="act_desc[]"><?php echo esc_textarea( $act['desc'] ); ?></textarea></label></p>
                                        <p><label>Location <input class="act-location" name="act_location[]" value="<?php echo esc_attr( $act['location'] ); ?>" /></label></p>
                                        <p><label>Included <input class="act-included" name="act_included[]" value="<?php echo esc_attr( $act['included'] ); ?>" /></label></p>
                                        <p><a class="button ctm-remove-activity">Remove</a></p>
                                    </div>
                                <?php endforeach; endif; ?>
                        </div>
                        <p><a class="button ctm-add-activity">Add Activity</a></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <input type="hidden" id="ctm_itinerary_input" name="ctm_itinerary" value="<?php echo $package ? esc_attr( get_post_meta( $package->ID, 'ctm_itinerary', true ) ) : ''; ?>" />

            <!-- templates -->
            <script type="text/html" id="ctm-day-template">
                <div class="ctm-day" data-day="__DAY__">
                    <h4 class="day-handle">Day __DAY__</h4>
                    <div class="ctm-activities"></div>
                    <p><a class="button ctm-add-activity">Add Activity</a></p>
                </div>
            </script>

            <script type="text/html" id="ctm-activity-template">
                <div class="ctm-activity">
                    <span class="act-handle">☰</span>
                    <p><label>Time <input class="act-time" name="act_time[]" value="" /></label></p>
                    <p><label>Title <input class="act-title" name="act_title[]" value="" /></label></p>
                    <p><label>Description <textarea class="act-desc" name="act_desc[]"></textarea></label></p>
                    <p><label>Location <input class="act-location" name="act_location[]" value="" /></label></p>
                    <p><label>Included <input class="act-included" name="act_included[]" value="" /></label></p>
                    <p><a class="button ctm-remove-activity">Remove</a></p>
                </div>
            </script>

        </div>

        <?php submit_button( $package ? 'Update Package' : 'Create Package' ); ?>
    </form>

    <script>
    (function(){
        const tabs = document.querySelectorAll('.nav-tab');
        tabs.forEach(tab=>tab.addEventListener('click',function(e){
            e.preventDefault();
            document.querySelectorAll('.nav-tab').forEach(t=>t.classList.remove('nav-tab-active'));
            this.classList.add('nav-tab-active');
            document.querySelectorAll('.ctm-tab').forEach(c=>c.style.display='none');
            const id = this.getAttribute('href').substring(1);
            document.getElementById(id).style.display = 'block';
        }));
    })();
    </script>

</div>
