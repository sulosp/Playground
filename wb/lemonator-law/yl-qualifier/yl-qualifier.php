<?php
/**
 * YL Qualifier Form — theme module (not a plugin).
 *
 * Install: copy the whole `yl-qualifier` folder into your active (child) theme, then add
 * to the theme's functions.php:
 *
 *   require get_stylesheet_directory() . '/yl-qualifier/yl-qualifier.php';
 *
 * Embed without a shortcode (pick one):
 *
 *   A) config.local.php — set embed_page_slug to your page slug (FTP only, no WP admin)
 *   B) Theme template — <?php echo yl_qualifier_render(); ?>
 *   C) Page template — assign "Qualifier Form" in Page → Template
 *
 * Shortcode (optional): [yl_qualifier_form]
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'YL_QUALIFIER_LOADED' ) ) {
	return;
}

define( 'YL_QUALIFIER_LOADED', true );
define( 'YL_QUALIFIER_VERSION', '1.2.9' );
define( 'YL_QUALIFIER_PATH', trailingslashit( dirname( __FILE__ ) ) );
define( 'YL_QUALIFIER_URL', trailingslashit( get_stylesheet_directory_uri() ) . basename( dirname( __FILE__ ) ) . '/' );

$config_local = YL_QUALIFIER_PATH . 'config.local.php';
$yl_qualifier_local_config = array();
if ( file_exists( $config_local ) ) {
	$loaded = include $config_local;
	if ( is_array( $loaded ) ) {
		$yl_qualifier_local_config = $loaded;
	}
}

final class YL_Qualifier_Form {

	private static $should_enqueue           = false;
	private static $assets_enqueued          = false;
	private static $local_config             = null;
	private static $response_flush_registered = false;

	public static function init() {
		add_shortcode( 'yl_qualifier_form', array( __CLASS__, 'render_shortcode' ) );
		add_action( 'wp', array( __CLASS__, 'detect_form_on_page' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'maybe_enqueue_assets' ), 20 );
		add_action( 'wp_footer', array( __CLASS__, 'maybe_enqueue_assets' ), 1 );
		add_action( 'wp_ajax_yl_qualifier_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'wp_ajax_nopriv_yl_qualifier_submit', array( __CLASS__, 'handle_submit' ) );
		add_action( 'admin_post_yl_qualifier_submit', array( __CLASS__, 'handle_admin_post' ) );
		add_action( 'admin_post_nopriv_yl_qualifier_submit', array( __CLASS__, 'handle_admin_post' ) );
		add_action( 'init', array( __CLASS__, 'maybe_handle_front_submit' ), 1 );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest_routes' ) );
		add_filter( 'theme_page_templates', array( __CLASS__, 'register_page_template' ) );
		add_filter( 'page_template', array( __CLASS__, 'maybe_auto_page_template' ) );
		add_filter( 'template_include', array( __CLASS__, 'load_page_template' ) );
		add_filter( 'the_content', array( __CLASS__, 'maybe_embed_in_content' ), 999 );
		add_action( 'init', array( __CLASS__, 'register_post_type' ) );
		add_filter( 'manage_yl_submission_posts_columns', array( __CLASS__, 'submission_columns' ) );
		add_action( 'manage_yl_submission_posts_custom_column', array( __CLASS__, 'submission_column_content' ), 10, 2 );
		add_action( 'admin_notices', array( __CLASS__, 'maybe_show_plugin_conflict_notice' ) );

		// Theme module takes over if the old plugin is still active.
		add_action( 'plugins_loaded', array( __CLASS__, 'remove_legacy_plugin_handlers' ), 99 );
		self::remove_legacy_plugin_handlers();
	}

	private static function remove_legacy_plugin_handlers() {
		if ( ! class_exists( 'YL_Qualifier_Plugin' ) ) {
			return;
		}

		remove_action( 'wp_ajax_yl_qualifier_submit', array( 'YL_Qualifier_Plugin', 'handle_submit' ) );
		remove_action( 'wp_ajax_nopriv_yl_qualifier_submit', array( 'YL_Qualifier_Plugin', 'handle_submit' ) );
	}

	public static function maybe_show_plugin_conflict_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! class_exists( 'YL_Qualifier_Plugin' ) ) {
			return;
		}

		echo '<div class="notice notice-warning"><p><strong>YL Qualifier:</strong> The old plugin is still active. Deactivate <em>YL Qualifier Form</em> in Plugins — you are using the theme version.</p></div>';
	}

	public static function register_post_type() {
		register_post_type(
			'yl_submission',
			array(
				'labels'              => array(
					'name'          => __( 'Qualifier Leads', 'yl-qualifier' ),
					'singular_name' => __( 'Qualifier Lead', 'yl-qualifier' ),
					'menu_name'     => __( 'Qualifier Leads', 'yl-qualifier' ),
					'all_items'     => __( 'All Leads', 'yl-qualifier' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-clipboard',
				'menu_position'       => 26,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'        => true,
				'supports'            => array( 'title' ),
				'exclude_from_search' => true,
			)
		);
	}

	/**
	 * @param array<string, string> $columns List table columns.
	 * @return array<string, string>
	 */
	public static function submission_columns( $columns ) {
		return array(
			'cb'       => $columns['cb'] ?? '<input type="checkbox" />',
			'title'    => __( 'Reference', 'yl-qualifier' ),
			'yl_name'  => __( 'Name', 'yl-qualifier' ),
			'yl_email' => __( 'Email', 'yl-qualifier' ),
			'yl_phone' => __( 'Phone', 'yl-qualifier' ),
			'yl_vehicle' => __( 'Vehicle', 'yl-qualifier' ),
			'date'     => $columns['date'] ?? __( 'Date', 'yl-qualifier' ),
		);
	}

	/**
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public static function submission_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'yl_name':
				echo esc_html( get_post_meta( $post_id, '_yl_first_name', true ) . ' ' . get_post_meta( $post_id, '_yl_last_name', true ) );
				break;
			case 'yl_email':
				echo esc_html( get_post_meta( $post_id, '_yl_email', true ) );
				break;
			case 'yl_phone':
				echo esc_html( get_post_meta( $post_id, '_yl_phone', true ) );
				break;
			case 'yl_vehicle':
				$vehicle = trim(
					get_post_meta( $post_id, '_yl_make', true ) . ' ' .
					get_post_meta( $post_id, '_yl_model', true ) . ' ' .
					get_post_meta( $post_id, '_yl_vehicle_year', true )
				);
				echo esc_html( $vehicle );
				break;
		}
	}

	public static function detect_form_on_page() {
		if ( is_page() && 'yl-qualifier/page-templates/qualifier-form.php' === get_page_template_slug() ) {
			self::$should_enqueue = true;
			return;
		}

		if ( self::is_embed_page() ) {
			self::$should_enqueue = true;
			return;
		}

		if ( ! is_singular() ) {
			return;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return;
		}

		if ( has_shortcode( $post->post_content, 'yl_qualifier_form' ) ) {
			self::$should_enqueue = true;
			return;
		}

		if ( self::post_contains_shortcode( $post->ID ) ) {
			self::$should_enqueue = true;
		}
	}

	/**
	 * Detect shortcode in block editor and Elementor page data.
	 *
	 * @param int $post_id Post ID.
	 */
	private static function post_contains_shortcode( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		if ( function_exists( 'parse_blocks' ) && has_blocks( $post->post_content ) ) {
			if ( self::blocks_contain_shortcode( parse_blocks( $post->post_content ) ) ) {
				return true;
			}
		}

		if ( get_post_meta( $post_id, '_elementor_edit_mode', true ) ) {
			$elementor_data = get_post_meta( $post_id, '_elementor_data', true );
			if ( is_string( $elementor_data ) && false !== strpos( $elementor_data, 'yl_qualifier_form' ) ) {
				return true;
			}
		}

		return false;
	}

	public static function register_rest_routes() {
		register_rest_route(
			'yl-qualifier/v1',
			'/submit',
			array(
				'methods'             => 'POST',
				'callback'            => array( __CLASS__, 'handle_rest_submit' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public static function handle_rest_submit( $request ) {
		$params = self::get_request_params( $request );
		$result = self::process_submission( $params );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return rest_ensure_response(
			array(
				'success' => true,
				'data'    => $result,
			)
		);
	}

	/**
	 * @param WP_REST_Request $request REST request.
	 * @return array<string, mixed>
	 */
	private static function get_request_params( $request ) {
		$params = $request->get_json_params();
		if ( is_array( $params ) && ! empty( $params ) ) {
			return wp_unslash( $params );
		}

		$body_params = $request->get_body_params();
		if ( is_array( $body_params ) && ! empty( $body_params ) ) {
			return wp_unslash( $body_params );
		}

		if ( ! empty( $_POST ) ) {
			return wp_unslash( $_POST );
		}

		$query_params = $request->get_params();
		if ( is_array( $query_params ) ) {
			return wp_unslash( $query_params );
		}

		return array();
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks Parsed block list.
	 */
	private static function blocks_contain_shortcode( $blocks ) {
		foreach ( $blocks as $block ) {
			if ( isset( $block['blockName'] ) && 'core/shortcode' === $block['blockName'] ) {
				$content = $block['innerHTML'] ?? '';
				if ( has_shortcode( $content, 'yl_qualifier_form' ) ) {
					return true;
				}
			}

			if ( ! empty( $block['innerBlocks'] ) && self::blocks_contain_shortcode( $block['innerBlocks'] ) ) {
				return true;
			}
		}

		return false;
	}

	public static function register_page_template( $templates ) {
		$templates['yl-qualifier/page-templates/qualifier-form.php'] = __( 'Qualifier Form', 'yl-qualifier' );
		return $templates;
	}

	/**
	 * Auto-assign Qualifier Form template when embed_mode is "template" in config.local.php.
	 *
	 * @param string $template Current page template slug.
	 * @return string
	 */
	public static function maybe_auto_page_template( $template ) {
		if ( ! self::is_embed_page() || 'template' !== self::get_local_setting( 'embed_mode', 'replace' ) ) {
			return $template;
		}

		self::$should_enqueue = true;
		return 'yl-qualifier/page-templates/qualifier-form.php';
	}

	/**
	 * Inject form HTML on a configured page (no shortcode required).
	 *
	 * @param string $content Page content.
	 * @return string
	 */
	public static function maybe_embed_in_content( $content ) {
		if ( ! is_singular( 'page' ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		if ( ! self::is_embed_page() ) {
			return $content;
		}

		$mode = self::get_local_setting( 'embed_mode', 'replace' );
		if ( 'template' === $mode ) {
			return $content;
		}

		self::$should_enqueue = true;
		$form = self::get_form_html( self::default_form_atts() );

		if ( 'append' === $mode ) {
			return $content . $form;
		}

		return $form;
	}

	/**
	 * Whether the current page is configured for auto-embed in config.local.php.
	 */
	public static function is_embed_page() {
		if ( ! is_singular( 'page' ) ) {
			return false;
		}

		global $post;
		if ( ! $post instanceof WP_Post ) {
			return false;
		}

		$page_id = (int) self::get_local_setting( 'embed_page_id', 0 );
		if ( $page_id > 0 && (int) $post->ID === $page_id ) {
			return true;
		}

		$slug = (string) self::get_local_setting( 'embed_page_slug', '' );
		if ( '' !== $slug && $post->post_name === $slug ) {
			return true;
		}

		return false;
	}

	/**
	 * Default shortcode / embed attributes from config.local.php.
	 *
	 * @return array<string, string>
	 */
	public static function default_form_atts() {
		return array(
			'to'            => self::get_local_setting( 'mail_to', 'sulosp1992@gmail.com' ),
			'contact_email' => self::get_local_setting( 'mail_to', 'sulosp1992@gmail.com' ),
			'contact_label' => self::get_local_setting( 'mail_to', 'sulosp1992@gmail.com' ),
			'webhook'       => self::get_local_setting( 'webhook', '' ),
			'send_email'    => self::should_send_notification_email() ? 'yes' : 'no',
			'from'          => self::get_local_setting( 'mailtrap_from_email', '' ),
			'from_name'     => self::get_local_setting( 'mailtrap_from_name', '' ),
		);
	}

	/**
	 * Ensure CSS/JS load on pages that call get_form_html() directly.
	 */
	public static function mark_for_enqueue() {
		self::$should_enqueue = true;
	}

	public static function load_page_template( $template ) {
		if ( ! is_page() ) {
			return $template;
		}

		$slug = get_page_template_slug();
		if ( 'yl-qualifier/page-templates/qualifier-form.php' !== $slug ) {
			return $template;
		}

		$file = YL_QUALIFIER_PATH . 'page-templates/qualifier-form.php';
		return file_exists( $file ) ? $file : $template;
	}

	public static function register_assets() {
		wp_register_style(
			'yl-qualifier',
			YL_QUALIFIER_URL . 'assets/qualifier.css',
			array(),
			YL_QUALIFIER_VERSION
		);

		wp_register_script(
			'yl-qualifier',
			YL_QUALIFIER_URL . 'assets/qualifier.js',
			array(),
			YL_QUALIFIER_VERSION,
			true
		);
	}

	public static function maybe_enqueue_assets() {
		if ( ! self::$should_enqueue || self::$assets_enqueued ) {
			return;
		}

		self::$assets_enqueued = true;

		wp_enqueue_style( 'yl-qualifier' );
		wp_enqueue_script( 'yl-qualifier' );

		wp_localize_script(
			'yl-qualifier',
			'ylQualifier',
			array(
				'ajaxUrl'   => admin_url( 'admin-ajax.php?action=yl_qualifier_submit' ),
				'restUrl'   => rest_url( 'yl-qualifier/v1/submit' ),
				'postUrl'   => admin_url( 'admin-post.php' ),
				'frontUrl'  => self::get_front_submit_url(),
				'nonce'     => wp_create_nonce( 'yl_qualifier_submit' ),
				'restNonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * @param array|string $atts Shortcode attributes.
	 */
	public static function render_shortcode( $atts ) {
		try {
			$atts = shortcode_atts(
				self::default_form_atts(),
				$atts,
				'yl_qualifier_form'
			);

			self::$should_enqueue = true;

			return self::get_form_html( $atts );
		} catch ( Throwable $exception ) {
			error_log( 'YL Qualifier render error: ' . $exception->getMessage() );
			return '<!-- yl-qualifier render error -->';
		}
	}

	/**
	 * Sanitized view variables for templates/form.php.
	 *
	 * @param array<string, string> $atts Form attributes.
	 * @return array<string, mixed>
	 */
	private static function prepare_form_view_vars( $atts ) {
		if ( self::should_send_notification_email() && ( empty( $atts['send_email'] ) || 'no' === $atts['send_email'] ) ) {
			$atts['send_email'] = 'yes';
		}

		$mail_to = sanitize_email( $atts['to'] ?? '' );
		if ( ! is_email( $mail_to ) ) {
			$mail_to = sanitize_email( self::get_local_setting( 'mail_to', 'sulosp1992@gmail.com' ) );
		}

		$contact_email = sanitize_email( $atts['contact_email'] ?? '' );
		if ( ! is_email( $contact_email ) ) {
			$contact_email = $mail_to;
		}

		$contact_label = sanitize_text_field( $atts['contact_label'] ?? '' );
		if ( '' === $contact_label ) {
			$contact_label = $contact_email;
		}

		$webhook_url = isset( $atts['webhook'] ) ? esc_url_raw( $atts['webhook'] ) : '';
		$webhook_url = apply_filters( 'yl_qualifier_webhook_url', $webhook_url, $atts );

		$send_email = isset( $atts['send_email'] ) && 'yes' === $atts['send_email'];
		$send_email = (bool) apply_filters( 'yl_qualifier_send_email', $send_email, $atts );

		$mail_from      = isset( $atts['from'] ) ? sanitize_email( $atts['from'] ) : '';
		$mail_from_name = isset( $atts['from_name'] ) ? sanitize_text_field( $atts['from_name'] ) : '';

		return array(
			'mail_to'        => $mail_to,
			'contact_email'  => $contact_email,
			'contact_label'  => $contact_label,
			'webhook_url'    => $webhook_url,
			'send_email'     => $send_email,
			'mail_from'      => $mail_from,
			'mail_from_name' => $mail_from_name,
			'ajax_url'       => admin_url( 'admin-ajax.php?action=yl_qualifier_submit' ),
			'rest_url'       => rest_url( 'yl-qualifier/v1/submit' ),
			'post_url'       => admin_url( 'admin-post.php' ),
			'front_url'      => self::get_front_submit_url(),
			'nonce'          => wp_create_nonce( 'yl_qualifier_submit' ),
			'rest_nonce'     => wp_create_nonce( 'wp_rest' ),
		);
	}

	public static function get_form_html( $atts ) {
		$form_template = YL_QUALIFIER_PATH . 'templates/form.php';
		if ( ! file_exists( $form_template ) ) {
			return '<!-- yl-qualifier: form template missing -->';
		}

		try {
			$yl_form = self::prepare_form_view_vars( $atts );

			ob_start();
			include $form_template;
			$html = ob_get_clean();
		} catch ( Throwable $exception ) {
			error_log( 'YL Qualifier form render error: ' . $exception->getMessage() );
			return '<!-- yl-qualifier form render error -->';
		}

		return '<!-- yl-qualifier v' . esc_html( YL_QUALIFIER_VERSION ) . ' -->' . $html;
	}

	public static function handle_submit() {
		self::respond_with_json( $_POST );
	}

	public static function handle_admin_post() {
		self::respond_with_json( $_POST );
	}

	/**
	 * Front-end submit endpoint (avoids wp-admin / Wordfence blocks on admin-ajax.php).
	 * POST to home_url( '/?yl_qualifier_submit=1' ).
	 */
	public static function maybe_handle_front_submit() {
		if ( empty( $_REQUEST['yl_qualifier_submit'] ) ) {
			return;
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( (string) $_SERVER['REQUEST_METHOD'] ) : 'GET';
		if ( 'POST' !== $method ) {
			return;
		}

		$post = $_POST;
		if ( empty( $post ) ) {
			$raw = file_get_contents( 'php://input' );
			if ( is_string( $raw ) && '' !== $raw ) {
				$json = json_decode( $raw, true );
				if ( is_array( $json ) ) {
					$post = $json;
				}
			}
		}

		self::respond_with_json( $post );
	}

	/**
	 * Public front-end URL that does not go through /wp-admin/.
	 *
	 * @return string
	 */
	private static function get_front_submit_url() {
		return add_query_arg( 'yl_qualifier_submit', '1', home_url( '/' ) );
	}

	/**
	 * @param array<string, mixed> $post Request data.
	 */
	private static function respond_with_json( $post ) {
		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		ob_start();

		try {
			$result = self::process_submission( $post );
		} catch ( Throwable $exception ) {
			ob_end_clean();
			error_log( 'YL Qualifier submit error: ' . $exception->getMessage() );
			self::output_json_error(
				defined( 'WP_DEBUG' ) && WP_DEBUG
					? 'Submit error: ' . $exception->getMessage()
					: 'Something went wrong. Please refresh the page and try again.',
				500
			);
			return;
		}

		// Discard any SMTP debug output from plugins during processing.
		ob_end_clean();

		if ( is_wp_error( $result ) ) {
			$status = 500;
			$error_data = $result->get_error_data();
			if ( is_array( $error_data ) && isset( $error_data['status'] ) ) {
				$status = (int) $error_data['status'];
			}

			self::output_json_error( $result->get_error_message(), $status );
			return;
		}

		self::output_json_success( $result );
	}

	/**
	 * Send the HTTP response to the browser before slow work (SMTP) runs on shutdown.
	 */
	public static function flush_response_to_client() {
		static $flushed = false;

		if ( $flushed ) {
			return;
		}

		$flushed = true;

		while ( ob_get_level() > 0 ) {
			ob_end_flush();
		}

		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request();
			return;
		}

		flush();
	}

	/**
	 * @param array<string, mixed> $data Success payload.
	 */
	private static function output_json_success( $data ) {
		status_header( 200 );
		nocache_headers();
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo wp_json_encode(
			array(
				'success' => true,
				'data'    => $data,
			)
		);
		self::flush_response_to_client();
		exit;
	}

	/**
	 * @param string $message Error message.
	 * @param int    $status  HTTP status code.
	 */
	private static function output_json_error( $message, $status ) {
		status_header( $status );
		nocache_headers();
		header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
		echo wp_json_encode(
			array(
				'success' => false,
				'data'    => array( 'message' => $message ),
			)
		);
		self::flush_response_to_client();
		exit;
	}

	/**
	 * @param array<string, mixed> $post Raw request data.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function process_submission( $post ) {
		$nonce = isset( $post['nonce'] ) ? sanitize_text_field( wp_unslash( $post['nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'yl_qualifier_submit' ) ) {
			return new WP_Error(
				'invalid_nonce',
				'Security check failed. Please refresh the page and try again.',
				array( 'status' => 403 )
			);
		}

		$data   = self::sanitize_submission( $post );
		$errors = self::validate_submission( $data );

		if ( ! empty( $errors ) ) {
			return new WP_Error(
				'validation_failed',
				implode( ' ', $errors ),
				array( 'status' => 422 )
			);
		}

		$reference = ! empty( $data['reference_number'] )
			? sanitize_text_field( $data['reference_number'] )
			: self::generate_reference();

		$data['reference_number'] = $reference;

		$to = isset( $post['mail_to'] ) ? sanitize_email( wp_unslash( $post['mail_to'] ) ) : '';
		if ( ! is_email( $to ) ) {
			$to = apply_filters( 'yl_qualifier_mail_to', 'sulosp1992@gmail.com', $data );
		}
		if ( ! is_email( $to ) ) {
			$to = get_option( 'admin_email' );
		}

		$stored_id = self::store_submission( $data );

		$webhook_url = '';
		if ( ! empty( $post['webhook_url'] ) ) {
			$webhook_url = esc_url_raw( wp_unslash( $post['webhook_url'] ) );
		}
		$webhook_url = apply_filters( 'yl_qualifier_webhook_url', $webhook_url, $data );

		$send_email = ! empty( $post['send_email'] ) && '1' === (string) $post['send_email'];
		$send_email = apply_filters( 'yl_qualifier_send_email', $send_email, $data );

		$webhook_sent = false;
		if ( $webhook_url ) {
			$webhook_sent = self::send_webhook( $webhook_url, $data );
		}

		$mail_queued = false;
		$use_wp_mail = (bool) self::get_local_setting( 'use_wp_mail', false );
		$try_email   = $send_email || self::is_mailtrap_ready( array() ) || $use_wp_mail;

		if ( $try_email ) {
			self::queue_notification_email( $to, $reference, $data, $post, $use_wp_mail );
			$mail_queued = true;
		}

		return array(
			'reference'    => $reference,
			'message'      => 'Submission received.',
			'stored'       => (bool) $stored_id,
			'webhook_sent' => (bool) $webhook_sent,
			'mail_queued'  => $mail_queued,
		);
	}

	/**
	 * Send notification after the JSON response (avoids SMTP debug breaking AJAX).
	 *
	 * @param string               $to          Recipient.
	 * @param string               $reference   Reference number.
	 * @param array<string,string> $data        Submission data.
	 * @param array<string, mixed> $post        Raw POST.
	 * @param bool                 $use_wp_mail Use wp_mail instead of Mailtrap API.
	 */
	private static function queue_notification_email( $to, $reference, $data, $post, $use_wp_mail ) {
		if ( ! self::$response_flush_registered ) {
			add_action( 'shutdown', array( __CLASS__, 'flush_response_to_client' ), 0 );
			self::$response_flush_registered = true;
		}

		add_action(
			'shutdown',
			function () use ( $to, $reference, $data, $post, $use_wp_mail ) {
				try {
					if ( self::is_mailtrap_ready( array() ) && ! $use_wp_mail ) {
						self::send_mailtrap_api( $to, $reference, $data, $post );
						return;
					}

					$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
					if ( is_email( $data['email'] ) ) {
						$headers[] = 'Reply-To: ' . $data['email'];
					}
					self::send_notification_mail( $to, $reference, $data, $headers );
				} catch ( Throwable $exception ) {
					error_log( 'YL Qualifier deferred mail error: ' . $exception->getMessage() );
				}
			},
			999
		);
	}

	/**
	 * Read a value from config.local.php (no WP admin needed).
	 *
	 * @param string $key     Config key.
	 * @param mixed  $default Default if missing.
	 * @return mixed
	 */
	private static function get_local_config() {
		if ( null !== self::$local_config ) {
			return self::$local_config;
		}

		global $yl_qualifier_local_config;
		self::$local_config = is_array( $yl_qualifier_local_config ) ? $yl_qualifier_local_config : array();

		return self::$local_config;
	}

	/**
	 * @param string $key     Config key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	private static function get_local_setting( $key, $default = '' ) {
		$config = self::get_local_config();
		if ( ! array_key_exists( $key, $config ) ) {
			return $default;
		}

		$value = $config[ $key ];
		if ( is_bool( $value ) || is_numeric( $value ) ) {
			return $value;
		}

		if ( is_string( $value ) && '' === $value ) {
			return $default;
		}

		return $value;
	}

	/**
	 * Whether notification email should be attempted on submit.
	 */
	private static function should_send_notification_email() {
		if ( self::is_mailtrap_ready( array() ) ) {
			return true;
		}

		if ( self::get_local_setting( 'use_wp_mail', false ) ) {
			return true;
		}

		return (bool) apply_filters( 'yl_qualifier_send_email', false, array() );
	}

	/**
	 * Whether Mailtrap HTTP API is configured (token + verified from address).
	 *
	 * @param array<string, mixed> $context Shortcode attrs or empty.
	 */
	public static function is_mailtrap_ready( $context ) {
		$token = self::get_mailtrap_token();
		$from  = self::get_mailtrap_from_email( $context );

		return ! empty( $token ) && is_email( $from );
	}

	/**
	 * @return string API token from wp-config or filter (never hardcode in theme files).
	 */
	private static function get_mailtrap_token() {
		$token = '';

		if ( defined( 'YL_MAILTRAP_API_TOKEN' ) && YL_MAILTRAP_API_TOKEN ) {
			$token = YL_MAILTRAP_API_TOKEN;
		}

		if ( empty( $token ) ) {
			$token = (string) self::get_local_setting( 'mailtrap_token', '' );
		}

		return apply_filters( 'yl_qualifier_mailtrap_api_token', $token );
	}

	/**
	 * @param array<string, mixed> $context Shortcode attrs or POST context.
	 */
	private static function get_mailtrap_from_email( $context ) {
		$from = '';

		if ( ! empty( $context['from'] ) ) {
			$from = sanitize_email( $context['from'] );
		}

		if ( ! is_email( $from ) && defined( 'YL_MAILTRAP_FROM_EMAIL' ) ) {
			$from = sanitize_email( YL_MAILTRAP_FROM_EMAIL );
		}

		if ( ! is_email( $from ) ) {
			$from = sanitize_email( (string) self::get_local_setting( 'mailtrap_from_email', '' ) );
		}

		if ( ! is_email( $from ) ) {
			$from = sanitize_email( get_option( 'admin_email' ) );
		}

		return apply_filters( 'yl_qualifier_mailtrap_from_email', $from, $context );
	}

	/**
	 * @param array<string, mixed> $context Shortcode attrs or POST context.
	 */
	private static function get_mailtrap_from_name( $context ) {
		$name = '';

		if ( ! empty( $context['from_name'] ) ) {
			$name = sanitize_text_field( $context['from_name'] );
		}

		if ( '' === $name && defined( 'YL_MAILTRAP_FROM_NAME' ) ) {
			$name = sanitize_text_field( YL_MAILTRAP_FROM_NAME );
		}

		if ( '' === $name ) {
			$name = sanitize_text_field( (string) self::get_local_setting( 'mailtrap_from_name', '' ) );
		}

		if ( '' === $name ) {
			$name = get_bloginfo( 'name' );
		}

		return apply_filters( 'yl_qualifier_mailtrap_from_name', $name, $context );
	}

	/**
	 * Send notification via Mailtrap Email Sending API (HTTPS, not SMTP).
	 *
	 * @param string               $to      Recipient.
	 * @param string               $reference Reference number.
	 * @param array<string,string> $data    Submission data.
	 * @param array<string, mixed> $post    Raw POST for from overrides.
	 */
	private static function send_mailtrap_api( $to, $reference, $data, $post ) {
		$token = self::get_mailtrap_token();
		if ( empty( $token ) ) {
			return false;
		}

		$context = array(
			'from'      => isset( $post['mail_from'] ) ? sanitize_email( wp_unslash( $post['mail_from'] ) ) : '',
			'from_name' => isset( $post['mail_from_name'] ) ? sanitize_text_field( wp_unslash( $post['mail_from_name'] ) ) : '',
		);

		$from_email = self::get_mailtrap_from_email( $context );
		$from_name  = self::get_mailtrap_from_name( $context );

		if ( ! is_email( $from_email ) || ! is_email( $to ) ) {
			error_log( 'YL Qualifier Mailtrap: invalid from or to address.' );
			return false;
		}

		$payload = array(
			'from'     => array(
				'email' => $from_email,
				'name'  => $from_name,
			),
			'to'       => array(
				array( 'email' => $to ),
			),
			'subject'  => 'New Lemon Law Qualifier - ' . $reference,
			'text'     => self::build_admin_message( $data ),
			'category' => 'yl-qualifier',
		);

		if ( is_email( $data['email'] ) ) {
			$payload['headers'] = array(
				'Reply-To' => $data['email'],
			);
		}

		$payload = apply_filters( 'yl_qualifier_mailtrap_payload', $payload, $data, $to );

		$response = wp_remote_post(
			'https://send.api.mailtrap.io/api/send',
			array(
				'timeout' => 20,
				'headers' => array(
					'Authorization' => 'Bearer ' . $token,
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
					'User-Agent'    => 'WordPress/YL-Qualifier/' . YL_QUALIFIER_VERSION,
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'YL Qualifier Mailtrap API error: ' . $response->get_error_message() );
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$body = wp_remote_retrieve_body( $response );

		if ( $code < 200 || $code >= 300 ) {
			error_log( 'YL Qualifier Mailtrap API HTTP ' . $code . ': ' . wp_strip_all_tags( $body ) );
			return false;
		}

		return true;
	}

	/**
	 * POST submission JSON to an external webhook (Zapier, Make, Google Apps Script, etc.).
	 *
	 * @param string               $url  Webhook URL.
	 * @param array<string, mixed> $data Submission data.
	 */
	private static function send_webhook( $url, $data ) {
		if ( empty( $url ) ) {
			return false;
		}

		$payload = apply_filters( 'yl_qualifier_webhook_payload', $data, $url );

		$response = wp_remote_post(
			$url,
			array(
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
			)
		);

		if ( is_wp_error( $response ) ) {
			error_log( 'YL Qualifier webhook error: ' . $response->get_error_message() );
			return false;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		return $code >= 200 && $code < 300;
	}

	/**
	 * Send email without letting SMTP debug output break the AJAX JSON response.
	 *
	 * @param string               $to        Recipient.
	 * @param string               $reference Reference number.
	 * @param array<string,string> $data      Submission data.
	 * @param array<int,string>    $headers   Mail headers.
	 */
	private static function send_notification_mail( $to, $reference, $data, $headers ) {
		ob_start();

		try {
			$sent = wp_mail(
				$to,
				'New Lemon Law Qualifier - ' . $reference,
				self::build_admin_message( $data ),
				$headers
			);
		} catch ( Throwable $exception ) {
			error_log( 'YL Qualifier mail error: ' . $exception->getMessage() );
			$sent = false;
		}

		$debug_output = ob_get_clean();
		if ( $debug_output ) {
			error_log( 'YL Qualifier mail debug output suppressed: ' . wp_strip_all_tags( $debug_output ) );
		}

		return ! empty( $sent );
	}

	/**
	 * Save submission as a private post in WordPress admin.
	 *
	 * @param array<string, string> $data Sanitized submission data.
	 * @return int Post ID, or 0 on failure.
	 */
	private static function store_submission( $data ) {
		$full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );

		$post_id = wp_insert_post(
			array(
				'post_type'   => 'yl_submission',
				'post_status' => 'publish',
				'post_title'  => $data['reference_number'] . ' — ' . $full_name,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			error_log( 'YL Qualifier store error: ' . $post_id->get_error_message() );
			self::store_submission_fallback( $data );
			return 0;
		}

		foreach ( $data as $key => $value ) {
			update_post_meta( $post_id, '_yl_' . $key, $value );
		}

		update_post_meta( $post_id, '_yl_submitted_at', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_yl_submitted_ip', isset( $_SERVER['REMOTE_ADDR'] )
			? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
			: '' );

		return (int) $post_id;
	}

	/**
	 * Fallback storage if custom post type insert fails.
	 *
	 * @param array<string, string> $data Sanitized submission data.
	 */
	private static function store_submission_fallback( $data ) {
		$entries = get_option( 'yl_qualifier_submissions', array() );
		if ( ! is_array( $entries ) ) {
			$entries = array();
		}

		$entries[] = array_merge(
			$data,
			array( 'submitted_at' => current_time( 'mysql' ) )
		);

		if ( count( $entries ) > 100 ) {
			$entries = array_slice( $entries, -100 );
		}

		update_option( 'yl_qualifier_submissions', $entries, false );
	}

	private static function sanitize_submission( $post ) {
		$fields = array(
			'reference_number'   => 'text',
			'model'              => 'text',
			'vehicle_year'       => 'text',
			'make'               => 'text',
			'vehicle_type'       => 'text',
			'acquisition_method' => 'text',
			'issue_description'  => 'textarea',
			'repair_attempts'    => 'text',
			'under_warranty'     => 'text',
			'first_name'         => 'text',
			'last_name'          => 'text',
			'phone'              => 'text',
			'email'              => 'email',
			'city'               => 'text',
			'zip'                => 'text',
		);

		$clean = array();

		foreach ( $fields as $key => $type ) {
			$raw = isset( $post[ $key ] ) ? wp_unslash( $post[ $key ] ) : '';

			if ( 'email' === $type ) {
				$clean[ $key ] = sanitize_email( $raw );
			} elseif ( 'textarea' === $type ) {
				$clean[ $key ] = sanitize_textarea_field( $raw );
			} else {
				$clean[ $key ] = sanitize_text_field( $raw );
			}
		}

		return $clean;
	}

	private static function validate_submission( $data ) {
		$errors = array();

		$required = array(
			'model',
			'vehicle_year',
			'make',
			'vehicle_type',
			'acquisition_method',
			'issue_description',
			'repair_attempts',
			'under_warranty',
			'first_name',
			'last_name',
			'phone',
			'email',
		);

		foreach ( $required as $field ) {
			if ( empty( $data[ $field ] ) ) {
				$errors[] = 'Please complete all required fields.';
				break;
			}
		}

		if ( ! empty( $data['email'] ) && ! is_email( $data['email'] ) ) {
			$errors[] = 'Please enter a valid email address.';
		}

		return array_unique( $errors );
	}

	private static function generate_reference() {
		return 'LMN-' . gmdate( 'ymd' ) . '-' . strtoupper( bin2hex( random_bytes( 2 ) ) );
	}

	private static function label_map() {
		return array(
			'vehicle_type'       => array(
				'car'         => 'Car',
				'truck-suv'   => 'Truck / SUVs',
				'motorcycles' => 'Motorcycles',
				'boats'       => 'Boats',
				'aircraft'    => 'Aircraft',
				'rvs'         => 'RVs',
			),
			'acquisition_method' => array(
				'purchased-financed' => 'Purchased (financed)',
				'purchased-full'     => 'Purchased (Paid in Full)',
				'leased'             => 'Leased',
				'not-sure'           => 'Not Sure',
			),
			'under_warranty'     => array(
				'yes'      => 'Yes',
				'no'       => 'No',
				'not-sure' => 'Not Sure',
			),
		);
	}

	private static function display_value( $key, $value ) {
		$maps = self::label_map();
		if ( isset( $maps[ $key ][ $value ] ) ) {
			return $maps[ $key ][ $value ];
		}
		return $value;
	}

	private static function build_admin_message( $data ) {
		$full_name = trim( $data['first_name'] . ' ' . $data['last_name'] );

		$lines = array(
			'Reference: ' . $data['reference_number'],
			'',
			'Contact',
			'Name: ' . $full_name,
			'Phone: ' . $data['phone'],
			'Email: ' . $data['email'],
			'City: ' . ( $data['city'] ? $data['city'] : '—' ),
			'Zip: ' . ( $data['zip'] ? $data['zip'] : '—' ),
			'',
			'Vehicle',
			'Make: ' . $data['make'],
			'Model: ' . $data['model'],
			'Year: ' . $data['vehicle_year'],
			'Type: ' . self::display_value( 'vehicle_type', $data['vehicle_type'] ),
			'Acquisition: ' . self::display_value( 'acquisition_method', $data['acquisition_method'] ),
			'Repair attempts: ' . $data['repair_attempts'],
			'Under warranty: ' . self::display_value( 'under_warranty', $data['under_warranty'] ),
			'',
			'Issue',
			$data['issue_description'],
		);

		return implode( "\n", $lines );
	}
}

YL_Qualifier_Form::init();

/**
 * Render the qualifier form in a theme template (no shortcode).
 *
 * Example in page-qualifier.php:
 *   <?php echo yl_qualifier_render(); ?>
 *
 * @param array<string, string> $atts Optional overrides (to, contact_email, webhook, etc.).
 * @return string Form HTML.
 */
function yl_qualifier_render( $atts = array() ) {
	YL_Qualifier_Form::mark_for_enqueue();
	return YL_Qualifier_Form::get_form_html(
		shortcode_atts( YL_Qualifier_Form::default_form_atts(), $atts, 'yl_qualifier_form' )
	);
}
