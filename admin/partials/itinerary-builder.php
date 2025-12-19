<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! current_user_can( 'edit_posts' ) ) wp_die( 'Insufficient permissions' );

// List packages and provide links to the itinerary tab in the package editor (custom UI)
$packages = get_posts( array( 'post_type' => 'ctm_package', 'post_status' => array( 'publish','draft' ), 'numberposts' => -1 ) );
?>
<div class="wrap">
    <h1>Itinerary Builder</h1>
    <p>Select a package to open the visual itinerary editor.</p>

    <?php if ( empty( $packages ) ): ?>
        <p><?php _e( 'No packages found. Create a package first.', 'cst_system' ); ?></p>
    <?php else: ?>
        <table class="widefat fixed">
            <thead>
                <tr>
                    <th><?php _e( 'Title', 'cst_system' ); ?></th>
                    <th><?php _e( 'Status', 'cst_system' ); ?></th>
                    <th><?php _e( 'Actions', 'cst_system' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $packages as $p ): ?>
                    <tr>
                        <td><?php echo esc_html( get_the_title( $p ) ); ?></td>
                        <td><?php echo esc_html( get_post_status( $p ) ); ?></td>
                        <td>
                            <?php
                            // Link to the custom Add/Edit package page with post_id and anchor to itinerary tab
                            $url = admin_url( 'admin.php?page=cst_package_add&post_id=' . intval( $p->ID ) ) . '#tab-itinerary';
                            printf( '<a class="button" href="%s">%s</a>', esc_url( $url ), esc_html__( 'Open Itinerary Editor', 'cst_system' ) );
                            // Also provide link to native post editor for this package
                            $edit_link = get_edit_post_link( $p->ID );
                            if ( $edit_link ) {
                                printf( ' <a class="button" href="%s">%s</a>', esc_url( $edit_link ), esc_html__( 'Open Post Editor', 'cst_system' ) );
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
