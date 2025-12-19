<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Simple list of interest submissions grouped by package
$packages = get_posts( array( 'post_type' => 'ctm_package', 'posts_per_page' => -1 ) );
?>
<div class="wrap">
    <h1><?php _e( 'Interest Submissions', 'cst_system' ); ?></h1>

    <p><?php _e( 'Submissions from the public expressing interest in packages.', 'cst_system' ); ?></p>

    <?php foreach ( $packages as $p ):
        $subs = get_post_meta( $p->ID, '_ctm_interest_submissions', true );
        if ( empty( $subs ) || ! is_array( $subs ) ) continue;
        ?>
        <h2><?php echo esc_html( $p->post_title ); ?> — <?php echo intval( count( $subs ) ); ?> submissions</h2>
        <table class="widefat fixed striped">
            <thead><tr><th><?php _e( 'Time', 'cst_system' ); ?></th><th><?php _e( 'Name', 'cst_system' ); ?></th><th><?php _e( 'Email', 'cst_system' ); ?></th><th><?php _e( 'Travellers', 'cst_system' ); ?></th><th><?php _e( 'Dates', 'cst_system' ); ?></th></tr></thead>
            <tbody>
                <?php foreach ( $subs as $s ): ?>
                    <tr>
                        <td><?php echo esc_html( $s['time'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $s['name'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $s['email'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $s['travellers'] ?? '' ); ?></td>
                        <td><?php echo esc_html( $s['start_date'] ?? $s['dates'] ?? '' ); ?><?php if ( ! empty( $s['end_date'] ) ) echo ' — ' . esc_html( $s['end_date'] ); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <form method="post" style="margin-top:8px" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <?php wp_nonce_field( 'ctm_clear_interest', 'ctm_clear_interest_nonce' ); ?>
            <input type="hidden" name="action" value="ctm_clear_interest">
            <input type="hidden" name="post_id" value="<?php echo esc_attr( $p->ID ); ?>">
            <button class="button button-secondary" type="submit"><?php _e( 'Clear submissions for this package', 'cst_system' ); ?></button>
        </form>
    <?php endforeach; ?>
</div>
