<?php
/**
 * Handles the [simple-staff-list] shortcode.
 *
 * Shortcode attributes:
 *   group        - (string)  Group slug. e.g. group="elders"
 *   id           - (int)     Single staff member post ID.
 *   columns      - (int)     Grid columns: 1, 2, or 3. Default: 3.
 *   order        - (string)  ASC or DESC. Default: ASC.
 *   orderby      - (string)  menu_order, title, date, rand. Default: menu_order.
 *   image_size   - (string)  WP image size. Default: medium.
 *   show_photo   - (bool)    Show staff photo. Default: true.
 *   show_name    - (bool)    Show staff name. Default: true.
 *   show_position - (bool)   Show position/title. Default: true.
 *   show_bio     - (bool)    Show bio. Default: true.
 *   show_email   - (bool)    Show email. Default: false.
 *   show_phone   - (bool)    Show phone. Default: false.
 *   show_social  - (bool)    Show Facebook/Twitter links. Default: false.
 *   link_names   - (bool)    Wrap name in link to single page. Default: false.
 *   wrap_class   - (string)  Extra CSS class on the outer wrapper.
 *   limit        - (int)     Max staff to show. Default: 100.
 *
 * Usage examples:
 *   [simple-staff-list group="elders"]
 *   [simple-staff-list group="deacons" columns="2" show_email="true"]
 *   [simple-staff-list group="staff" columns="3" show_bio="false" show_phone="true"]
 *
 * @package SSL_Rewrite
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SSLR_Shortcode {

	/** Default shortcode attribute values. */
	private const DEFAULTS = [
		'group'          => '',
		'id'             => '',
		'columns'        => '3',
		'order'          => 'ASC',
		'orderby'        => 'menu_order',
		'image_size'     => 'medium',
		'show_photo'     => 'true',
		'show_name'      => 'true',
		'show_position'  => 'true',
		'show_bio'       => 'true',
		'show_email'     => 'false',
		'show_phone'     => 'false',
		'show_social'    => 'false',
		'show_contact'   => 'false',
		'contact_label'    => 'Email Us',
		'contact_position' => 'below_info',
		'link_names'     => 'false',
		'wrap_class'     => '',
		'limit'          => '100',
	];

	public function init(): void {
		add_shortcode( 'simple-staff-list', [ $this, 'render' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	public function enqueue_styles(): void {
		wp_enqueue_style(
			'sslr-public',
			SSLR_URL . 'public/css/sslr-public.css',
			[],
			SSLR_VERSION
		);
	}

	/**
	 * Main shortcode callback.
	 */
	public function render( array $atts = [] ): string {
		$atts = shortcode_atts( self::DEFAULTS, $atts, 'simple-staff-list' );

		// Sanitize and normalize attributes.
		$config = $this->normalize_atts( $atts );

		// Build the query.
		$members = $this->query_members( $config );

		if ( empty( $members ) ) {
			return '';
		}

		return $this->build_output( $members, $config );
	}

	// ─── Attribute normalization ───────────────────────────────────────────────

	private function normalize_atts( array $atts ): array {
		$columns = (int) $atts['columns'];
		if ( $columns < 1 || $columns > 3 ) {
			$columns = 3;
		}

		$order = strtoupper( sanitize_text_field( $atts['order'] ) );
		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			$order = 'ASC';
		}

		$allowed_orderby = [ 'menu_order', 'title', 'date', 'rand' ];
		$orderby = sanitize_key( $atts['orderby'] );
		if ( ! in_array( $orderby, $allowed_orderby, true ) ) {
			$orderby = 'menu_order';
		}

		$allowed_image_sizes = get_intermediate_image_sizes();
		$allowed_image_sizes[] = 'full';
		$image_size = sanitize_key( $atts['image_size'] );
		if ( ! in_array( $image_size, $allowed_image_sizes, true ) ) {
			$image_size = 'medium';
		}

		$limit = (int) $atts['limit'];
		if ( $limit < 1 || $limit > 500 ) {
			$limit = 100;
		}

		$contact_label = sanitize_text_field( $atts['contact_label'] );
		if ( '' === $contact_label ) {
			$contact_label = 'Email Us';
		}

		return [
			'group'         => sanitize_text_field( strtolower( $atts['group'] ) ),
			'id'            => (int) $atts['id'],
			'columns'       => $columns,
			'order'         => $order,
			'orderby'       => $orderby,
			'image_size'    => $image_size,
			'show_photo'    => $this->to_bool( $atts['show_photo'] ),
			'show_name'     => $this->to_bool( $atts['show_name'] ),
			'show_position' => $this->to_bool( $atts['show_position'] ),
			'show_bio'      => $this->to_bool( $atts['show_bio'] ),
			'show_email'    => $this->to_bool( $atts['show_email'] ),
			'show_phone'    => $this->to_bool( $atts['show_phone'] ),
			'show_social'   => $this->to_bool( $atts['show_social'] ),
			'show_contact'  => $this->to_bool( $atts['show_contact'] ),
			'contact_label'    => $contact_label,
			'contact_position' => in_array( $atts['contact_position'], ['below_info','below_photo'], true ) ? $atts['contact_position'] : 'below_info',
			'link_names'    => $this->to_bool( $atts['link_names'] ),
			'wrap_class'    => sanitize_html_class( $atts['wrap_class'] ),
			'limit'         => $limit,
		];
	}

	/** Converts "true"/"false"/1/0 strings to actual bool. */
	private function to_bool( mixed $value ): bool {
		if ( is_bool( $value ) ) {
			return $value;
		}
		$value = strtolower( trim( (string) $value ) );
		return in_array( $value, [ 'true', '1', 'yes' ], true );
	}

	// ─── Query ────────────────────────────────────────────────────────────────

	private function query_members( array $config ): array {
		$args = [
			'post_type'      => 'staff-member',
			'posts_per_page' => $config['limit'],
			'post_status'    => 'publish',
			'orderby'        => $config['orderby'],
			'order'          => $config['order'],
		];

		if ( '' !== $config['group'] ) {
			$args['tax_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery
				[
					'taxonomy' => 'staff-member-group',
					'field'    => 'slug',
					'terms'    => $config['group'],
				],
			];
		}

		if ( $config['id'] > 0 && 'staff-member' === get_post_type( $config['id'] ) ) {
			$args['p'] = $config['id'];
		}

		/**
		 * Filter the query args before execution.
		 * @param array $args   WP_Query args.
		 * @param array $config Normalized shortcode config.
		 */
		$args = apply_filters( 'sslr_query_args', $args, $config );

		$query = new WP_Query( $args );
		$posts = $query->posts;
		wp_reset_postdata();

		return is_array( $posts ) ? $posts : [];
	}

	// ─── Output builder ───────────────────────────────────────────────────────

	private function build_output( array $members, array $config ): string {
		$col_class  = 'sslr-cols-' . $config['columns'];
		$group_class = '' !== $config['group'] ? ' sslr-group-' . sanitize_html_class( $config['group'] ) : '';
		$extra_class = '' !== $config['wrap_class'] ? ' ' . $config['wrap_class'] : '';

		$html  = '<div class="sslr-grid ' . esc_attr( $col_class . $group_class . $extra_class ) . '">' . "\n";

		foreach ( $members as $post ) {
			$html .= $this->render_member( $post, $config );
		}

		$html .= '</div>' . "\n";

		return $html;
	}

	private function render_member( WP_Post $post, array $config ): string {
		$meta = $this->get_meta( $post->ID );

		ob_start();
		?>
		<div class="sslr-member" id="sslr-member-<?php echo esc_attr( (string) $post->ID ); ?>">

			<?php if ( $config['show_photo'] ) : ?>
				<div class="sslr-member__photo">
					<?php echo $this->get_photo_html( $post, $meta, $config ); ?>
					<?php if ( $config['show_contact'] && 'below_photo' === $config['contact_position'] && '' !== $meta['contact_url'] ) : ?>
						<div class="sslr-member__contact sslr-member__contact--below-photo">
							<a class="sslr-contact-link" href="<?php echo esc_url( $meta['contact_url'] ); ?>" target="_blank" rel="noopener noreferrer">
								<?php
								$first_name = explode( ' ', trim( $post->post_title ) )[0];
								echo esc_html( sprintf( __( 'Contact %s', 'ssl-rewrite' ), $first_name ) );
								?>
							</a>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="sslr-member__info">

				<?php if ( $config['show_name'] ) : ?>
					<p class="sslr-member__name">
						<?php if ( $config['link_names'] ) : ?>
							<a href="<?php echo esc_url( get_permalink( $post->ID ) ); ?>">
								<?php echo esc_html( $post->post_title ); ?>
							</a>
						<?php else : ?>
							<?php echo esc_html( $post->post_title ); ?>
						<?php endif; ?>
					</p>
				<?php endif; ?>

				<?php if ( $config['show_position'] && '' !== $meta['title'] ) : ?>
					<p class="sslr-member__position"><?php echo esc_html( $meta['title'] ); ?></p>
				<?php endif; ?>

				<?php if ( $config['show_bio'] && '' !== $meta['bio'] ) : ?>
					<div class="sslr-member__bio">
						<?php echo wp_kses_post( wpautop( $meta['bio'] ) ); ?>
					</div>
				<?php endif; ?>

				<?php if ( $config['show_email'] && '' !== $meta['email'] ) : ?>
					<p class="sslr-member__email">
						<a href="mailto:<?php echo esc_attr( antispambot( $meta['email'] ) ); ?>">
							<?php echo esc_html( antispambot( $meta['email'] ) ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( $config['show_phone'] && '' !== $meta['phone'] ) : ?>
					<p class="sslr-member__phone">
						<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $meta['phone'] ) ); ?>">
							<?php echo esc_html( $meta['phone'] ); ?>
						</a>
					</p>
				<?php endif; ?>

				<?php if ( $config['show_contact'] && 'below_info' === $config['contact_position'] && '' !== $meta['contact_url'] ) : ?>
				<div class="sslr-member__contact">
					<a class="sslr-contact-link" href="<?php echo esc_url( $meta['contact_url'] ); ?>" target="_blank" rel="noopener noreferrer">
						<?php
						$first_name   = explode( ' ', trim( $post->post_title ) )[0];
						$button_label = sprintf(
							/* translators: %s = staff member first name */
							__( 'Contact %s', 'ssl-rewrite' ),
							$first_name
						);
						echo esc_html( $button_label );
					?>
					</a>
				</div>
			<?php endif; ?>

				<?php if ( $config['show_social'] ) : ?>
					<?php echo $this->get_social_html( $meta, $post->post_title ); ?>
				<?php endif; ?>

				<?php
				/**
				 * Hook: sslr_after_member_info
				 * Add extra content after the standard info block.
				 * @param WP_Post $post   The staff member post.
				 * @param array   $meta   Staff member meta data.
				 * @param array   $config Shortcode configuration.
				 */
				do_action( 'sslr_after_member_info', $post, $meta, $config );
				?>

			</div><!-- .sslr-member__info -->

		</div><!-- .sslr-member -->
		<?php

		return (string) ob_get_clean();
	}

	// ─── Helpers ──────────────────────────────────────────────────────────────

	private function get_meta( int $post_id ): array {
		return [
			'title' => (string) get_post_meta( $post_id, '_staff_member_title', true ),
			'email' => (string) get_post_meta( $post_id, '_staff_member_email', true ),
			'phone' => (string) get_post_meta( $post_id, '_staff_member_phone', true ),
			'bio'   => (string) get_post_meta( $post_id, '_staff_member_bio',   true ),
			'fb'    => (string) get_post_meta( $post_id, '_staff_member_fb',    true ),
			'tw'          => (string) get_post_meta( $post_id, '_staff_member_tw',          true ),
			'contact_url' => (string) get_post_meta( $post_id, '_staff_member_contact_url', true ),
		];
	}

	private function get_photo_html( WP_Post $post, array $meta, array $config ): string {
		if ( ! has_post_thumbnail( $post->ID ) ) {
			/**
			 * Filter the placeholder HTML shown when no photo exists.
			 * Return an empty string to show nothing.
			 */
			return (string) apply_filters( 'sslr_no_photo_html', '', $post, $meta );
		}

		$image_size = apply_filters( 'sslr_image_size', $config['image_size'], $post->ID );

		$img = get_the_post_thumbnail( $post->ID, $image_size, [
			'class' => 'sslr-member__photo-img',
			'alt'   => esc_attr( $post->post_title . ( '' !== $meta['title'] ? ' – ' . $meta['title'] : '' ) ),
		] );

		if ( $config['link_names'] ) {
			$img = '<a href="' . esc_url( get_permalink( $post->ID ) ) . '" tabindex="-1" aria-hidden="true">' . $img . '</a>';
		}

		return $img;
	}

	private function get_social_html( array $meta, string $name ): string {
		$links = [];

		if ( '' !== $meta['fb'] ) {
			$fb_url  = esc_url( $meta['fb'] );
			$links[] = sprintf(
				'<a class="sslr-social sslr-social--facebook" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s on Facebook">Facebook</a>',
				$fb_url,
				esc_attr( $name )
			);
		}

		if ( '' !== $meta['tw'] ) {
			$tw_handle = ltrim( $meta['tw'], '@' );
			$tw_url    = esc_url( 'https://twitter.com/' . $tw_handle );
			$links[]   = sprintf(
				'<a class="sslr-social sslr-social--twitter" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s on Twitter">Twitter</a>',
				$tw_url,
				esc_attr( $name )
			);
		}

		if ( empty( $links ) ) {
			return '';
		}

		return '<div class="sslr-member__social">' . implode( ' ', $links ) . '</div>';
	}
}
