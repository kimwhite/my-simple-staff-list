# Simple Staff List Rewrite

A modern rewrite of the Simple Staff List plugin.  
PHP 8.x compatible · Mobile-responsive CSS Grid · BEM class names · Secure

---

## Installation

1. **Keep the old plugin active while you switch** — your data is safe.
2. Upload the `simple-staff-list-rewrite` folder to `/wp-content/plugins/`.
3. Activate **Simple Staff List Rewrite** in WP Admin → Plugins.
4. **Deactivate** (do NOT delete) the old Simple Staff List plugin.
5. Go to Settings → Permalinks and click **Save Changes** to flush rewrite rules.
6. Update your shortcodes (see below).

> **Your existing data is fully preserved.** The rewrite uses the same
> `staff-member` post type and `_staff_member_*` meta keys as the original plugin.

---

## Shortcode

Replace your old `[simple-staff-list]` calls. The same attribute works:

```
[simple-staff-list group="elders"]
[simple-staff-list group="deacons" columns="2"]
[simple-staff-list group="staff" columns="3" show_bio="false" show_email="true"]
```

### All attributes

| Attribute      | Default    | Options                          | Description                                |
|----------------|------------|----------------------------------|--------------------------------------------|
| group          | (empty)    | Any group slug                   | Filter by group slug                       |
| columns        | 3          | 1, 2, 3                          | Grid columns. Auto-stacks on mobile.       |
| order          | ASC        | ASC, DESC                        | Sort direction                             |
| orderby        | menu_order | menu_order, title, date, rand    | Sort field                                 |
| image_size     | medium     | thumbnail, medium, large, full   | WP image size                              |
| show_photo     | true       | true, false                      | Show staff photo                           |
| show_name      | true       | true, false                      | Show name                                  |
| show_position  | true       | true, false                      | Show position/title                        |
| show_bio       | true       | true, false                      | Show bio                                   |
| show_email     | false      | true, false                      | Show email (mailto link)                   |
| show_phone     | false      | true, false                      | Show phone (tel: link)                     |
| show_social    | false      | true, false                      | Show Facebook/Twitter                      |
| link_names     | false      | true, false                      | Link name+photo to member's page           |
| wrap_class     | (empty)    | Any CSS class name               | Extra class on the outer grid wrapper      |
| limit          | 100        | Any number                       | Max members to show                        |

---

## CSS Customization

The plugin outputs minimal styles intentionally so your theme is in control.
Add overrides to your theme's stylesheet:

```css
/* Photo style — change border-radius to 0 for square photos */
.sslr-member__photo-img {
    border-radius: 50%;
    max-width: 160px;
}

/* Name style */
.sslr-member__name {
    font-size: 1.1em;
    color: #333;
}

/* Group-specific override */
.sslr-group-elders .sslr-member {
    background: #f9f9f9;
    padding: 1rem;
    border-radius: 8px;
}
```

---

## Developer Hooks

### Filters

```php
// Modify WP_Query args before execution
add_filter( 'sslr_query_args', function( $args, $config ) {
    // e.g. change posts_per_page
    return $args;
}, 10, 2 );

// Change image size per-member
add_filter( 'sslr_image_size', function( $size, $post_id ) {
    return 'thumbnail';
}, 10, 2 );

// Custom placeholder when no photo exists
add_filter( 'sslr_no_photo_html', function( $html, $post, $meta ) {
    return '<img src="/path/to/placeholder.jpg" class="sslr-member__photo-img">';
}, 10, 3 );
```

### Actions

```php
// Add extra fields after the standard info block
add_action( 'sslr_after_member_info', function( $post, $meta, $config ) {
    echo '<p class="sslr-member__custom">Custom content here</p>';
}, 10, 3 );

// Add extra admin fields on the edit screen
add_action( 'sslr_after_admin_fields', function( $post_id ) {
    // Output your custom field HTML here
} );

// Hook into save to persist custom fields
add_action( 'sslr_save_staff_member', function( $post_id, $post_data ) {
    // Save your custom fields here
}, 10, 2 );
```

---

## Responsive Breakpoints

| Screen width  | 1-col shortcode | 2-col shortcode | 3-col shortcode |
|---------------|-----------------|-----------------|-----------------|
| < 560px       | 1 column        | 1 column        | 1 column        |
| 560px–859px   | 1 column        | 2 columns       | 2 columns       |
| 860px+        | 1 column        | 2 columns       | 3 columns       |

---

## Changelog

### 1.0.0
- Complete rewrite for PHP 8.x compatibility
- CSS Grid layout replacing floats
- Mobile-first responsive breakpoints
- BEM CSS class names
- Per-field show/hide via shortcode attributes
- `columns` shortcode attribute (1, 2, or 3)
- Proper nonce verification on all form saves and AJAX
- `wp_kses_post()` for bio sanitization
- `sanitize_url()` replacing deprecated `esc_url_raw()`
- No more CSS stored in wp_options
- Drag-to-reorder admin page preserved and improved
- Full shortcode reference in admin Usage page
