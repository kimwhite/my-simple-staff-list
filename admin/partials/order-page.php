<?php
/**
 * Admin: Staff Order Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$members = get_posts([
    'post_type'      => 'staff-member',
    'posts_per_page' => 200,
    'post_status'    => 'publish',
    'orderby'        => 'menu_order',
    'order'          => 'ASC',
]);
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Staff Member Order', 'ssl-rewrite' ); ?></h1>
    <p><?php esc_html_e( 'Drag and drop to reorder staff members. Order applies across all groups — use the shortcode group="" attribute to display a specific group.', 'ssl-rewrite' ); ?></p>

    <?php if ( empty( $members ) ) : ?>
        <p><?php esc_html_e( 'No staff members found. Add some first!', 'ssl-rewrite' ); ?></p>
    <?php else : ?>
        <ul id="sslr-sortable">
            <?php foreach ( $members as $member ) :
                $position = get_post_meta( $member->ID, '_staff_member_title', true );
                $thumb    = get_the_post_thumbnail( $member->ID, [ 36, 36 ] );
                $groups   = get_the_terms( $member->ID, 'staff-member-group' );
                $group_names = '';
                if ( is_array( $groups ) ) {
                    $group_names = implode( ', ', array_map( fn($t) => esc_html( $t->name ), $groups ) );
                }
            ?>
                <li data-post-id="<?php echo esc_attr( (string) $member->ID ); ?>">
                    <span class="dashicons dashicons-menu"></span>
                    <?php if ( $thumb ) echo $thumb; ?>
                    <strong><?php echo esc_html( $member->post_title ); ?></strong>
                    <?php if ( $position ) : ?>
                        <span style="color:#666; font-size:12px;">— <?php echo esc_html( $position ); ?></span>
                    <?php endif; ?>
                    <?php if ( $group_names ) : ?>
                        <span style="margin-left:auto; font-size:12px; color:#999;"><?php echo esc_html( $group_names ); ?></span>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>

        <div class="sslr-save-order">
            <button id="sslr-save-order" class="button button-primary" disabled>
                <?php esc_html_e( 'Save Order', 'ssl-rewrite' ); ?>
            </button>
            <span id="sslr-order-notice" class="sslr-order-notice"></span>
        </div>
    <?php endif; ?>
</div>
