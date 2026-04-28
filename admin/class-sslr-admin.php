<?php
/**
 * Admin functionality: meta boxes, custom columns, save, drag-to-reorder.
 *
 * @package SSL_Rewrite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SSLR_Admin {

	public function init(): void {
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
		add_action( 'admin_menu',            [ $this, 'register_menu' ] );

		// Post edit screen.
		add_filter( 'enter_title_here',      [ $this, 'change_title_placeholder' ] );
		add_action( 'add_meta_boxes',        [ $this, 'add_meta_boxes' ] );
		add_action( 'save_post',             [ $this, 'save_meta' ] );

		// List table columns.
		add_filter( 'manage_staff-member_posts_columns',        [ $this, 'custom_columns' ] );
		add_action( 'manage_posts_custom_column',               [ $this, 'render_column' ], 10, 2 );
		add_filter( 'manage_edit-staff-member_sortable_columns', [ $this, 'sortable_columns' ] );

		// AJAX: drag-to-reorder.
		add_action( 'wp_ajax_sslr_update_order', [ $this, 'ajax_update_order' ] );
	}

	// ─── Enqueue ──────────────────────────────────────────────────────────────

	public function enqueue( string $hook ): void {
		$screen = get_current_screen();
		if ( ! $screen || 'staff-member' !== $screen->post_type ) {
			return;
		}

		wp_enqueue_style(
			'sslr-admin',
			SSLR_URL . 'admin/css/sslr-admin.css',
			[],
			SSLR_VERSION
		);

		// Only load sortable JS on the list table.
		if ( 'edit.php' === $hook || ( isset( $_GET['page'] ) && 'sslr-order' === $_GET['page'] ) ) {
			wp_enqueue_script(
				'sslr-order',
				SSLR_URL . 'admin/js/sslr-order.js',
				[ 'jquery', 'jquery-ui-sortable' ],
				SSLR_VERSION,
				true
			);
			wp_localize_script( 'sslr-order', 'sslrOrder', [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'sslr-update-order' ),
			] );
		}
	}

	// ─── Admin Menu ───────────────────────────────────────────────────────────

	public function register_menu(): void {
		add_submenu_page(
			'edit.php?post_type=staff-member',
			__( 'Staff Order', 'ssl-rewrite' ),
			__( 'Order', 'ssl-rewrite' ),
			'edit_pages',
			'sslr-order',
			[ $this, 'render_order_page' ]
		);

		add_submenu_page(
			'edit.php?post_type=staff-member',
			__( 'Shortcode Usage', 'ssl-rewrite' ),
			__( 'Usage', 'ssl-rewrite' ),
			'edit_pages',
			'sslr-usage',
			[ $this, 'render_usage_page' ]
		);
	}

	// ─── Title placeholder ────────────────────────────────────────────────────

	public function change_title_placeholder( string $title ): string {
		$screen = get_current_screen();
		if ( $screen && 'staff-member' === $screen->post_type ) {
			return __( 'Staff Member Name', 'ssl-rewrite' );
		}
		return $title;
	}

	// ─── Meta Boxes ───────────────────────────────────────────────────────────

	public function add_meta_boxes(): void {
		add_meta_box(
			'sslr-staff-info',
			__( 'Staff Member Info', 'ssl-rewrite' ),
			[ $this, 'render_info_meta_box' ],
			'staff-member',
			'normal',
			'high'
		);
		add_meta_box(
			'sslr-staff-bio',
			__( 'Staff Member Bio', 'ssl-rewrite' ),
			[ $this, 'render_bio_meta_box' ],
			'staff-member',
			'normal',
			'high'
		);
	}

	public function render_info_meta_box( WP_Post $post ): void {
		$title = (string) get_post_meta( $post->ID, '_staff_member_title', true );
		$email = (string) get_post_meta( $post->ID, '_staff_member_email', true );
		$phone = (string) get_post_meta( $post->ID, '_staff_member_phone', true );
		$fb    = (string) get_post_meta( $post->ID, '_staff_member_fb',    true );
		$tw    = (string) get_post_meta( $post->ID, '_staff_member_tw',          true );
		$contact_url = (string) get_post_meta( $post->ID, '_staff_member_contact_url', true );

		wp_nonce_field( 'sslr_save_meta', 'sslr_meta_nonce' );
		?>
		<div class="sslr-meta-wrap">
			<div class="sslr-meta-row">
				<label for="sslr_title"><?php esc_html_e( 'Position / Title', 'ssl-rewrite' ); ?></label>
				<input type="text" id="sslr_title" name="_staff_member_title"
					value="<?php echo esc_attr( $title ); ?>"
					placeholder="<?php esc_attr_e( 'e.g. Elder, Lead Pastor, Director', 'ssl-rewrite' ); ?>">
			</div>
			<div class="sslr-meta-row">
				<label for="sslr_email"><?php esc_html_e( 'Email', 'ssl-rewrite' ); ?></label>
				<input type="email" id="sslr_email" name="_staff_member_email"
					value="<?php echo esc_attr( $email ); ?>"
					placeholder="<?php esc_attr_e( 'name@example.com', 'ssl-rewrite' ); ?>">
			</div>
			<div class="sslr-meta-row">
				<label for="sslr_phone"><?php esc_html_e( 'Phone', 'ssl-rewrite' ); ?></label>
				<input type="text" id="sslr_phone" name="_staff_member_phone"
					value="<?php echo esc_attr( $phone ); ?>"
					placeholder="<?php esc_attr_e( '(555) 555-5555', 'ssl-rewrite' ); ?>">
			</div>
			<div class="sslr-meta-row">
				<label for="sslr_fb"><?php esc_html_e( 'Facebook URL', 'ssl-rewrite' ); ?></label>
				<input type="url" id="sslr_fb" name="_staff_member_fb"
					value="<?php echo esc_attr( $fb ); ?>"
					placeholder="https://facebook.com/yourpage">
			</div>
			<div class="sslr-meta-row">
				<label for="sslr_tw"><?php esc_html_e( 'Twitter Username', 'ssl-rewrite' ); ?></label>
				<input type="text" id="sslr_tw" name="_staff_member_tw"
					value="<?php echo esc_attr( $tw ); ?>"
					placeholder="<?php esc_attr_e( 'username (no @)', 'ssl-rewrite' ); ?>">
			</div>
			<div class="sslr-meta-row">
				<label for="sslr_contact_url"><?php esc_html_e( 'Contact Form URL', 'ssl-rewrite' ); ?></label>
				<input type="url" id="sslr_contact_url" name="_staff_member_contact_url"
					value="<?php echo esc_attr( $contact_url ); ?>"
					placeholder="https://form.jotform.com/...">
				<p class="description"><?php esc_html_e( 'Paste your Jotform or other contact form URL here.', 'ssl-rewrite' ); ?></p>
			</div>
			<?php do_action( 'sslr_after_admin_fields', $post->ID ); ?>
		</div>
		<?php
	}

	public function render_bio_meta_box( WP_Post $post ): void {
		$bio = (string) get_post_meta( $post->ID, '_staff_member_bio', true );

		wp_editor( $bio, '_staff_member_bio', [
			'textarea_rows' => 8,
			'media_buttons' => false,
			'tinymce'       => true,
			'quicktags'     => true,
		] );

		echo '<p class="description" style="margin-top:8px;">' . esc_html__( 'HTML is allowed in the bio.', 'ssl-rewrite' ) . '</p>';
	}

	// ─── Save Meta ────────────────────────────────────────────────────────────

	public function save_meta( int $post_id ): void {
		// Verify nonce.
		if (
			! isset( $_POST['sslr_meta_nonce'] )
			|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['sslr_meta_nonce'] ) ), 'sslr_save_meta' )
		) {
			return;
		}

		// Don't save on autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type.
		if ( get_post_type( $post_id ) !== 'staff-member' ) {
			return;
		}

		// Check capability.
		if ( ! current_user_can( 'edit_page', $post_id ) ) {
			return;
		}

		$fields = [
			'_staff_member_title'       => 'sanitize_text_field',
			'_staff_member_email'       => 'sanitize_email',
			'_staff_member_phone'       => 'sanitize_text_field',
			'_staff_member_fb'          => 'esc_url_raw',
			'_staff_member_tw'          => 'sanitize_text_field',
			'_staff_member_contact_url' => 'esc_url_raw',
		];

		foreach ( $fields as $key => $sanitizer ) {
			if ( isset( $_POST[ $key ] ) ) {
				update_post_meta( $post_id, $key, $sanitizer( wp_unslash( $_POST[ $key ] ) ) );
			}
		}

		// Bio uses wp_kses for HTML sanitization.
		if ( isset( $_POST['_staff_member_bio'] ) ) {
			update_post_meta(
				$post_id,
				'_staff_member_bio',
				wp_kses_post( wp_unslash( $_POST['_staff_member_bio'] ) )
			);
		}

		do_action( 'sslr_save_staff_member', $post_id, $_POST );
	}

	// ─── Custom Columns ───────────────────────────────────────────────────────

	public function custom_columns( array $cols ): array {
		return [
			'cb'                  => '<input type="checkbox" />',
			'id'                  => __( 'ID', 'ssl-rewrite' ),
			'photo'               => __( 'Photo', 'ssl-rewrite' ),
			'title'               => __( 'Name', 'ssl-rewrite' ),
			'_staff_member_title' => __( 'Position', 'ssl-rewrite' ),
			'_staff_member_email' => __( 'Email', 'ssl-rewrite' ),
			'_staff_member_phone' => __( 'Phone', 'ssl-rewrite' ),
			'groups'              => __( 'Groups', 'ssl-rewrite' ),
		];
	}

	public function sortable_columns( array $cols ): array {
		$cols['title']               = 'title';
		$cols['_staff_member_title'] = '_staff_member_title';
		return $cols;
	}

	public function render_column( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'id':
				echo esc_html( (string) $post_id );
				break;
			case 'photo':
				if ( has_post_thumbnail( $post_id ) ) {
					echo get_the_post_thumbnail( $post_id, [ 60, 60 ] );
				}
				break;
			case '_staff_member_title':
				echo esc_html( (string) get_post_meta( $post_id, '_staff_member_title', true ) );
				break;
			case '_staff_member_email':
				$email = (string) get_post_meta( $post_id, '_staff_member_email', true );
				if ( $email ) {
					printf(
						'<a href="mailto:%s">%s</a>',
						esc_attr( $email ),
						esc_html( $email )
					);
				}
				break;
			case '_staff_member_phone':
				echo esc_html( (string) get_post_meta( $post_id, '_staff_member_phone', true ) );
				break;
			case 'groups':
				$terms = get_the_terms( $post_id, 'staff-member-group' );
				if ( is_array( $terms ) ) {
					$names = array_map( fn( $t ) => esc_html( $t->name ), $terms );
					echo implode( ', ', $names );
				}
				break;
		}
	}

	// ─── AJAX: Update Order ───────────────────────────────────────────────────

	public function ajax_update_order(): void {
		check_ajax_referer( 'sslr-update-order', 'nonce' );

		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_send_json_error( 'Insufficient permissions.' );
		}

		if ( empty( $_POST['order'] ) || ! is_array( $_POST['order'] ) ) {
			wp_send_json_error( 'Invalid order data.' );
		}

		foreach ( $_POST['order'] as $menu_order => $raw_post_id ) {
			$post_id    = (int) preg_replace( '/[^0-9]/', '', $raw_post_id );
			$menu_order = (int) $menu_order;

			if ( $post_id > 0 && 'staff-member' === get_post_type( $post_id ) ) {
				wp_update_post( [
					'ID'         => $post_id,
					'menu_order' => $menu_order,
				] );
			}
		}

		wp_send_json_success( 'Order saved.' );
	}

	// ─── Admin Pages ──────────────────────────────────────────────────────────

	public function render_order_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'ssl-rewrite' ) );
		}
		include SSLR_PATH . 'admin/partials/order-page.php';
	}

	public function render_usage_page(): void {
		if ( ! current_user_can( 'edit_pages' ) ) {
			wp_die( esc_html__( 'You do not have permission to view this page.', 'ssl-rewrite' ) );
		}
		include SSLR_PATH . 'admin/partials/usage-page.php';
	}
}
