<?php
/**
 * Admin: Shortcode Usage Reference Page
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Gather group slugs for the quick reference.
$groups = get_terms([ 'taxonomy' => 'staff-member-group', 'hide_empty' => false ]);
?>
<div class="wrap">
    <h1><?php esc_html_e( 'Shortcode Usage', 'ssl-rewrite' ); ?></h1>

    <h2><?php esc_html_e( 'Basic Usage', 'ssl-rewrite' ); ?></h2>
    <p><?php esc_html_e( 'Paste a shortcode into any page or post content area.', 'ssl-rewrite' ); ?></p>

    <?php if ( ! empty( $groups ) && ! is_wp_error( $groups ) ) : ?>
        <h3><?php esc_html_e( 'Your Groups', 'ssl-rewrite' ); ?></h3>
        <table class="widefat striped" style="max-width:600px;">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Group Name', 'ssl-rewrite' ); ?></th>
                    <th><?php esc_html_e( 'Slug', 'ssl-rewrite' ); ?></th>
                    <th><?php esc_html_e( 'Shortcode', 'ssl-rewrite' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $groups as $group ) : ?>
                <tr>
                    <td><?php echo esc_html( $group->name ); ?></td>
                    <td><code><?php echo esc_html( $group->slug ); ?></code></td>
                    <td><code>[simple-staff-list group="<?php echo esc_attr( $group->slug ); ?>"]</code></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2 style="margin-top:2em;"><?php esc_html_e( 'All Shortcode Attributes', 'ssl-rewrite' ); ?></h2>
    <table class="widefat striped" style="max-width:900px;">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Attribute', 'ssl-rewrite' ); ?></th>
                <th><?php esc_html_e( 'Default', 'ssl-rewrite' ); ?></th>
                <th><?php esc_html_e( 'Options', 'ssl-rewrite' ); ?></th>
                <th><?php esc_html_e( 'Description', 'ssl-rewrite' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <tr><td><code>group</code></td><td><em><?php esc_html_e('empty', 'ssl-rewrite'); ?></em></td><td><?php esc_html_e('Any group slug', 'ssl-rewrite'); ?></td><td><?php esc_html_e('Filter by group. Leave empty to show all.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>columns</code></td><td>3</td><td>1, 2, 3</td><td><?php esc_html_e('Number of columns. Automatically stacks on mobile.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>order</code></td><td>ASC</td><td>ASC, DESC</td><td><?php esc_html_e('Sort direction.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>orderby</code></td><td>menu_order</td><td>menu_order, title, date, rand</td><td><?php esc_html_e('Sort field. menu_order respects drag-to-reorder.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>image_size</code></td><td>medium</td><td>thumbnail, medium, large, full</td><td><?php esc_html_e('WordPress image size for the photo.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_photo</code></td><td>true</td><td>true, false</td><td><?php esc_html_e('Show staff photo.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_name</code></td><td>true</td><td>true, false</td><td><?php esc_html_e('Show staff name.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_position</code></td><td>true</td><td>true, false</td><td><?php esc_html_e('Show position/title.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_bio</code></td><td>true</td><td>true, false</td><td><?php esc_html_e('Show bio.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_email</code></td><td>false</td><td>true, false</td><td><?php esc_html_e('Show email (links to mailto).', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_phone</code></td><td>false</td><td>true, false</td><td><?php esc_html_e('Show phone (links to tel:).', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>show_social</code></td><td>false</td><td>true, false</td><td><?php esc_html_e('Show Facebook/Twitter links.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>link_names</code></td><td>false</td><td>true, false</td><td><?php esc_html_e('Wrap name and photo in a link to their page.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>wrap_class</code></td><td><em><?php esc_html_e('empty', 'ssl-rewrite'); ?></em></td><td><?php esc_html_e('Any CSS class name', 'ssl-rewrite'); ?></td><td><?php esc_html_e('Add a custom CSS class to the outer grid wrapper.', 'ssl-rewrite'); ?></td></tr>
            <tr><td><code>limit</code></td><td>100</td><td><?php esc_html_e('Any number', 'ssl-rewrite'); ?></td><td><?php esc_html_e('Max number of members to display.', 'ssl-rewrite'); ?></td></tr>
        </tbody>
    </table>

    <h2 style="margin-top:2em;"><?php esc_html_e( 'Examples', 'ssl-rewrite' ); ?></h2>
    <table class="widefat striped" style="max-width:900px;">
        <thead><tr><th><?php esc_html_e('Shortcode', 'ssl-rewrite'); ?></th><th><?php esc_html_e('What it does', 'ssl-rewrite'); ?></th></tr></thead>
        <tbody>
            <tr>
                <td><code>[simple-staff-list group="elders"]</code></td>
                <td><?php esc_html_e('Show Elders in 3 columns, photo + name + position + bio.', 'ssl-rewrite'); ?></td>
            </tr>
            <tr>
                <td><code>[simple-staff-list group="deacons" columns="2"]</code></td>
                <td><?php esc_html_e('Show Deacons in 2 columns.', 'ssl-rewrite'); ?></td>
            </tr>
            <tr>
                <td><code>[simple-staff-list group="staff" show_bio="false" show_email="true" show_phone="true"]</code></td>
                <td><?php esc_html_e('Show Staff without bio, but with email and phone.', 'ssl-rewrite'); ?></td>
            </tr>
            <tr>
                <td><code>[simple-staff-list columns="1" orderby="title"]</code></td>
                <td><?php esc_html_e('Show everyone in a single column, sorted alphabetically.', 'ssl-rewrite'); ?></td>
            </tr>
        </tbody>
    </table>

    <h2 style="margin-top:2em;"><?php esc_html_e( 'CSS Customization', 'ssl-rewrite' ); ?></h2>
    <p><?php esc_html_e( 'Add any of these classes to your theme\'s CSS to customize the appearance:', 'ssl-rewrite' ); ?></p>
    <pre style="background:#f6f7f7; padding:16px; max-width:600px; overflow:auto; font-size:13px;"><?php echo esc_html(
'.sslr-grid           { }   /* outer grid wrapper */
.sslr-member          { }   /* individual card */
.sslr-member__photo   { }   /* photo wrapper */
.sslr-member__photo-img { } /* the <img> tag */
.sslr-member__info    { }   /* text block */
.sslr-member__name    { }   /* name */
.sslr-member__position { }  /* position/title */
.sslr-member__bio     { }   /* bio text */
.sslr-member__email   { }   /* email */
.sslr-member__phone   { }   /* phone */
.sslr-member__social  { }   /* social links wrapper */

/* Group-specific overrides: */
.sslr-group-elders .sslr-member__name  { }
.sslr-group-deacons .sslr-member       { }
.sslr-group-staff .sslr-member__photo-img { }'
    ); ?></pre>
</div>
