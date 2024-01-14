<?php
/**
 * WC_PayLate_Gateway class file.
 *
 * @package woo-paylate
 */

/**
 * Class WC_PayLate_Gateway
 */
class WC_PayLate_Gateway extends WC_Payment_Gateway {
	/**
	 * Default port.
	 */
	const DEFAULT_PORT = '443';

	/**
	 * Default test port.
	 */
	const DEFAULT_TEST_PORT = '21443';

	/**
	 * Plugin id.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Plugin icon.
	 *
	 * @var string
	 */
	public $icon;

	/**
	 * Plugin method title - required by WooCommerce.
	 *
	 * @var string
	 */
	public $method_title;

	/**
	 * Plugin method description - required by WooCommerce.
	 *
	 * @var string
	 */
	public $method_description;

	/**
	 * Plugin has fields ( = false ).
	 *
	 * @var string
	 */
	public $has_fields;

	/**
	 * Plugin supports ( = products ).
	 *
	 * @var string
	 */
	public $supports;

	/**
	 * Whether logging is enabled.
	 *
	 * @var bool
	 */
	public static $log_enabled = false;

	/**
	 * WC_Logger Logger instance.
	 *
	 * @var bool
	 */
	public static $log = false;

	/**
	 * Test mode (yes or no).
	 *
	 * @var string
	 */
	protected $test_mode;

	/**
	 * Port number given by PayLate service.
	 *
	 * @var string
	 */
	protected $port;

	/**
	 * Client ID  given by PayLate service.
	 *
	 * @var string
	 */
	protected $client_id;

	/**
	 * Login given by PayLate service.
	 *
	 * @var string
	 */
	protected $login;

	/**
	 * Password given by PayLate service.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Test order id.
	 */
	const TEST_ORDER_ID = 235;

	/**
	 * WC_PayLate_Gateway constructor.
	 */
	public function __construct() {
		$this->id                 = 'paylate_gateway';
		$this->icon               = WOO_PAYLATE_URL . '/images/paylate-logo-32x32.png';
		$this->method_title       = __( 'PayLate', 'woo-paylate' );
		$this->method_description = __( 'WooCommerce gateway to make payments via PayLate service', 'woo-paylate' );
		$this->has_fields         = false;
		$this->supports           = [ 'products' ];

		$this->init_form_fields();
		$this->init_settings();

		// Load settings.
		$this->enabled     = $this->get_option( 'enabled' );
		$this->title       = $this->get_option( 'title' );
		$this->description = $this->get_option( 'description' );
		self::$log_enabled = $this->get_option( 'log_enabled' );

		if ( 'yes' === $this->test_mode ) {
			$this->port      = $this->get_option( 'test_port', $this->form_fields['test_port']['default'] );
			$this->client_id = $this->get_option( 'test_client_id' );
			$this->login     = $this->get_option( 'test_login' );
			$this->password  = $this->get_option( 'test_password' );
		} else {
			$this->port      = $this->get_option( 'port', $this->form_fields['port']['default'] );
			$this->client_id = $this->get_option( 'client_id' );
			$this->login     = $this->get_option( 'login' );
			$this->password  = $this->get_option( 'password' );
		}

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_filter( 'script_loader_tag', [ $this, 'script_loader_tag_filter' ], 10, 2 );
		add_shortcode( 'paylate_widget', [ $this, 'paylate_widget_shortcode' ] );
		add_shortcode( 'paylate_buy_button', [ $this, 'paylate_buy_button_shortcode' ] );
		add_action( 'check_paylate_gateway', [ $this, 'check_response' ] );
		add_action( 'admin_menu', [ $this, 'add_settings_page' ], 100 );

		// Save settings.
		if ( is_admin() ) {
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
		}
	}

	/**
	 * Initialize form fields on admin page.
	 */
	public function init_form_fields() {
		$this->form_fields = [
			'enabled'     => [
				'title'   => __( 'Enable', 'woo-paylate' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable PayLate Gateway', 'woo-paylate' ),
				'default' => 'yes',
			],
			'title'       => [
				'title'       => __( 'Title', 'woo-paylate' ),
				'type'        => 'text',
				'description' => __( 'Payment method title that the customer will see on checkout.', 'woo-paylate' ),
				'default'     => __( 'PayLate Gateway', 'woo-paylate' ),
				'desc_tip'    => true,
			],
			'description' => [
				'title'       => __( 'Description', 'woo-paylate' ),
				'type'        => 'textarea',
				'description' => __( 'Payment method description that the customer will see on checkout.', 'woo-paylate' ),
				'default'     => __( 'Pay via PayLate Gateway', 'woo-paylate' ),
				'desc_tip'    => true,
			],
			'log_enabled' => [
				'title'       => __( 'Log', 'woo-paylate' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woo-paylate' ),
				'default'     => 'no',
				'description' =>
					__( 'Log events, such as PayLate responses, inside', 'woo-paylate' ) . ' <code>' .
					WC_Log_Handler_File::get_log_file_path( 'paylate' ) . '</code>',
			],
			'test_mode'   => [
				'title'       => __( 'Test mode', 'woo-paylate' ),
				'type'        => 'checkbox',
				'description' => __( 'Fields below have different values in test mode.', 'woo-paylate' ),
				'label'       => __( 'Enable test mode', 'woo-paylate' ),
				'default'     => 'no',
			],
		];

		$this->test_mode = $this->get_option( 'test_mode' );
		if ( 'yes' === $this->test_mode ) {
			$var_form_fields = [
				'test_port'      => [
					'title'       => __( 'Test Port', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Test Port number given by PayLate service.', 'woo-paylate' ),
					'default'     => self::DEFAULT_TEST_PORT,
					'desc_tip'    => true,
				],
				'test_client_id' => [
					'title'       => __( 'Test Client ID', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Test Client ID given by PayLate service.', 'woo-paylate' ),
					'default'     => '1524072013',
					'desc_tip'    => true,
				],
				'test_login'     => [
					'title'       => __( 'Test Login', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Test Login for PayLate service.', 'woo-paylate' ),
					'default'     => 'test',
					'desc_tip'    => true,
				],
				'test_password'  => [
					'title'       => __( 'Test Password', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Test Password for PayLate service.', 'woo-paylate' ),
					'default'     => 'test',
					'desc_tip'    => true,
				],
			];
		} else {
			$var_form_fields = [
				'port'      => [
					'title'       => __( 'Port', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Port number given by PayLate service.', 'woo-paylate' ),
					'default'     => self::DEFAULT_PORT,
					'desc_tip'    => true,
				],
				'client_id' => [
					'title'       => __( 'Client ID', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Client ID given by PayLate service.', 'woo-paylate' ),
					'default'     => '',
					'desc_tip'    => true,
				],
				'login'     => [
					'title'       => __( 'Login', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Login for PayLate service.', 'woo-paylate' ),
					'default'     => '',
					'desc_tip'    => true,
				],
				'password'  => [
					'title'       => __( 'Password', 'woo-paylate' ),
					'type'        => 'text',
					'description' => __( 'Password for PayLate service.', 'woo-paylate' ),
					'default'     => '',
					'desc_tip'    => true,
				],
			];
		}

		$this->form_fields = array_merge( $this->form_fields, $var_form_fields );
	}

	/**
	 * Process payment via PayLate interface.
	 *
	 * @param int $order_id Order id in WooCommerce.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = wc_get_order( $order_id );

		$subtotal = 0;

		// Get all products.
		$goods    = [];
		$all_cats = [];
		foreach ( $order->get_items( [ 'line_item', 'fee', 'coupon' ] ) as $item ) {
			$cur_item = [];

			if ( 'fee' === $item['type'] ) {
				$cur_item['Name']  = __( 'Fee', 'woo-paylate' );
				$cur_item['Price'] = $item['line_total'];
				$cur_item['Count'] = 1;
				$goods[]           = $cur_item;

				$subtotal += $item['line_total'];
			} elseif ( 'coupon' === $item['type'] ) {
				$cur_item['Name']  = __( 'Coupon', 'woo-paylate' );
				$cur_item['Price'] = (string) ( $item['discount'] * - 1 );
				$cur_item['Count'] = 1;
				$goods[]           = $cur_item;

				$subtotal -= $item['discount'];
			} else {
				/**
				 * Order item.
				 *
				 * @var WC_Order_Item_Product $item
				 */
				$product = $item->get_product();
				$cat_ids = $product->get_category_ids();
				$cats    = [];

				foreach ( $cat_ids as $cat_id ) {
					$cat_name   = get_term_by( 'id', $cat_id, 'product_cat' )->name;
					$cats[]     = $cat_name;
					$all_cats[] = $cat_name;
				}

				$cats                 = implode( ',', $cats );
				$cur_item['Name']     = $item['name'];
				$cur_item['Category'] = $cats;
				$cur_item['Price']    = $order->get_item_subtotal( $item, false );
				$cur_item['Count']    = $item['qty'];

				// phpcs:disable Squiz.PHP.CommentedOutCode.Found
				// $cur_item['fio']      = $item['qty'];
				// $cur_item['passport'] = $item['qty'];
				// phpcs:enable Squiz.PHP.CommentedOutCode.Found

				$subtotal = $subtotal + $order->get_item_subtotal( $item, false ) * $item['qty'];
				$goods[]  = $cur_item;
			}
		}

		$all_cats = implode( ',', $all_cats );

		$chosen_methods          = WC()->session->get( 'chosen_shipping_methods' );
		$chosen_shipping_no_ajax = $chosen_methods[0];
		$chosen_rate             =
			(object) WC()->session->get( 'shipping_for_package_0' )['rates'][ $chosen_shipping_no_ajax ];
		$label                   = $chosen_rate->get_label();

		$shipping_total = wc()->cart->get_shipping_total();

		$shipping          = [];
		$shipping['Name']  = $label;
		$shipping['Price'] = $shipping_total;
		$shipping['Count'] = 1;

		$goods[] = $shipping;

		if ( 'yes' === $this->test_mode ) {
			update_option( 'woocommerce_paylate_gateway_order_id', $order_id );
			$order_id = self::TEST_ORDER_ID;
		}

		return [
			'result'   => 'success',
			'redirect' => $this->get_payment_link( $goods, $order_id, $all_cats ),
		];
	}

	/**
	 * Get payment link.
	 *
	 * @param array  $goods    Ordered products.
	 * @param int    $order_id Order id.
	 * @param string $all_cats List of ordered product categories, separated by comma.
	 *
	 * @return string Payment link.
	 */
	private function get_payment_link( $goods, $order_id, $all_cats ) {
		$goods_encoded = wp_json_encode( $goods, JSON_UNESCAPED_UNICODE );

		$token = md5( $this->login . md5( $this->password ) . $order_id );

		if ( self::$log_enabled ) {
			$message = "\n" . __( 'Request to PayLate', 'woo-paylate' ) . "\n";

			$message .= 'port="' . $this->port . '"' . "\n";
			$message .= 'client_id="' . $this->client_id . '"' . "\n";
			$message .= 'order_id="' . $order_id . '"' . "\n";
			$message .= 'category="' . $all_cats . '"' . "\n";
			$message .= 'goods="' . $goods_encoded . '"' . "\n";
			$message .= 'token="' . $token . '"' . "\n";
			self::log( $message );
		}

		$nonce = wp_create_nonce( 'paylate_gateway' );

		if ( isset( $_SERVER['HTTPS'] ) ) {
			$scheme = sanitize_text_field( wp_unslash( $_SERVER['HTTPS'] ) );
		} else {
			$scheme = '';
		}

		if ( ( $scheme ) && ( 'off' !== $scheme ) ) {
			$scheme = 'https';
		} else {
			$scheme = 'http';
		}

		$link = site_url( '', $scheme );

		$link .= '?paylate_gateway';

		$query = "&port=$this->port&client_id=$this->client_id&order_id=$order_id&category=$all_cats";

		$query .= "&goods=$goods_encoded&token=$token";
		$query .= "&_wpnonce=$nonce";

		parse_str( $query, $parsed_query );
		$parsed_query = rawurlencode_deep( $parsed_query );

		return add_query_arg( $parsed_query, $link );
	}

	/**
	 * Function to check response from PayLate service.
	 *
	 * @return void|null
	 */
	public function check_response() {
		// Require functions returning messages from codes.
		require_once WOO_PAYLATE_PATH . '/includes/wc-gateway-paylate-codes.php';

		// No nonce can be here.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['application_id'] ) ) {
			return null;
		}

		isset( $_POST['order_id'] ) ? $order_id = (int) $_POST['order_id'] : $order_id = 0;

		isset( $_POST['state'] ) ? $state = (int) $_POST['state'] : $state = 0;

		isset( $_POST['sum'] ) ? $sum = sanitize_text_field( wp_unslash( $_POST['sum'] ) ) : $sum = '';

		isset( $_POST['token'] ) ? $token = sanitize_text_field( wp_unslash( $_POST['token'] ) ) : $token = '';

		$application_id = sanitize_text_field( wp_unslash( $_POST['application_id'] ) );
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( self::$log_enabled ) {
			$message = "\n" . __( 'Response from PayLate', 'woo-paylate' ) . "\n";

			$message .= 'order_id="' . $order_id . '"' . "\n";
			$message .= 'state="' . $state . '" - ' . wc_paylate_get_result_message( $state ) . "\n";
			$message .= 'sum="' . $sum . '"' . "\n";
			$message .= 'token="' . $token . '"' . "\n";
			$message .= 'application_id="' . $application_id . '"' . "\n";
			self::log( $message );
		}

		$our_token = md5( $this->login . md5( $this->password ) . $order_id );

		if ( 'yes' === $this->test_mode ) {
			$order_id = get_option( 'woocommerce_paylate_gateway_order_id' );
		}

		$return = true;

		if ( 0 === $order_id ) {
			$return = false;
		}

		$post = get_post( $order_id );

		if ( $return && ( ! $post ) ) {
			$return = false;
		}

		if ( $return && ( 'shop_order' !== $post->post_type ) ) {
			$return = false;
		}

		$order = wc_get_order( $order_id );

		if (
			$return &&
			( $order->has_status( 'completed' ) || $order->has_status( 'processing' ) )
		) {
			$return = false;
		}

		if ( $return && ( $our_token !== $token ) ) {
			$order->add_order_note( __( 'Bad token by PayLate', 'woo-paylate' ) );
			$return = null;
		}

		if ( $return && ( $order->calculate_totals() !== (float) $sum ) ) {
			$order->add_order_note( __( 'Wrong sum by PayLate', 'woo-paylate' ) );
			$return = null;
		}

		switch ( $state ) {
			case - 1:
				if ( $return ) {
					$order->add_order_note( wc_paylate_get_result_message( $state ) );
					$order->update_status(
						'cancelled',
						__( 'Order cancelled by customer.', 'woocommerce' )
					);
					echo "RESULT:1\nDESCR:" . esc_html__( 'Order is cancelled', 'woo-paylate' );
					die;
				}

				echo "RESULT:0\nDESCR:";
				esc_html_e(
					'Your order was cancelled, please create a new one',
					'woo-paylate'
				);
				die;

			case 0:
				if ( $return ) {
					$order->add_order_note( wc_paylate_get_result_message( $state ) );
					echo "RESULT:1\nDESCR:" . esc_html__( 'Order is actual', 'woo-paylate' );
					die;
				}

				echo "RESULT:0\nDESCR:";
				esc_html_e(
					'Your order was cancelled, please create a new one',
					'woo-paylate'
				);
				die;

			case 1:
				if ( $return ) {
					$order->add_order_note(
						wc_paylate_get_result_message( $state ) . "\n" .
						'application_id=' . $application_id
					);

					$order->payment_complete();
					$order->update_status( 'completed' );

					WC()->cart->empty_cart();
					echo "RESULT:1\nDESCR:" . esc_html__( 'Order is completed', 'woo-paylate' );
					die;
				}

				echo "RESULT:0\nDESCR:";
				esc_html_e(
					'Your order was cancelled, please create a new one',
					'woo-paylate'
				);
				die;

			default:
				$note = __( 'PayLate unknown error: ', 'woo-paylate' ) . '<br>';

				$note .= 'state=' . $state . ';<br>';
				$order->add_order_note( $note );

				return null;
		}
	}

	/**
	 * Logging method.
	 *
	 * @param string $message Log message.
	 * @param string $level   Optional. Default 'info'
	 *                        emergency|alert|critical|error|warning|notice|info|debug.
	 */
	public static function log( $message, $level = 'info' ) {
		if ( self::$log_enabled ) {
			if ( empty( self::$log ) ) {
				self::$log = wc_get_logger();
			}
			self::$log->log( $level, $message, [ 'source' => 'paylate' ] );
		}
	}

	/**
	 * Enqueue plugin scripts.
	 */
	public function enqueue_scripts() {
		// Script for paylate_buy_button_shortcode.
		wp_enqueue_script(
			'wc-paylate-partner',
			'https://paylate.ru/js/partner_im.js',
			[],
			WOO_PAYLATE_VERSION,
			false
		);

		// Script for paylate_widget_shortcode.
		wp_enqueue_script(
			'wc-paylate-widget',
			'https://paylate.ru/widget/js/widget.js',
			[],
			WOO_PAYLATE_VERSION,
			true
		);

		// This plugin styles.
		wp_enqueue_style(
			'wc-paylate',
			WOO_PAYLATE_URL . '/css/style.css',
			[],
			WOO_PAYLATE_VERSION
		);
	}

	/**
	 * Filter script tag and add charset.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Script handle.
	 *
	 * @return string
	 */
	public function script_loader_tag_filter( $tag, $handle ) {
		$handles = [
			'wc-paylate-partner',
			'wc-paylate-widget',
		];
		if ( in_array( $handle, $handles, true ) ) {
			$tag = str_replace( '></script>', ' charset="utf-8"></script>', $tag );
		}

		return $tag;
	}

	/**
	 * Add settings page to the menu.
	 */
	public function add_settings_page() {
		$parent_slug = 'woocommerce';
		$page_title  = __( 'PayLate', 'woo-paylate' );
		$menu_title  = __( 'PayLate', 'woo-paylate' );
		$capability  = 'manage_options';
		$menu_slug   = 'wc-paylate';
		$function    = [ $this, 'woocommerce_paylate_settings_page' ];
		add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
	}

	/**
	 * Empty settings page.
	 */
	public function woocommerce_paylate_settings_page() {
		?>
		<div class="wrap">
			<h2 id="title">
				<?php
				// Admin panel title.
				esc_html_e( 'Gateway for PayLate on WooCommerce Plugin Options', 'woo-paylate' );
				?>
			</h2>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paylate_gateway' ) ); ?>">
					<?php
					esc_html_e( 'Please follow this link to see plugin options.', 'woo-paylate' );
					?>
				</a>
			</p>
		</div>
		<?php
	}

	/**
	 * Widget shortcode.
	 *
	 * @param array $atts Shortcode parameters.
	 *
	 * @return string Button html.
	 */
	public function paylate_widget_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'class'       => '',
				'data-button' => '',
			],
			$atts
		);

		$class = $atts['class'];

		if ( ! $class ) {
			$class = 'wc-paylate-btn';
		}

		ob_start();

		echo '<a class="paylate-mini-widget ' . esc_attr( $class ) . '"';
		$data_button = (int) $atts['data-button'];

		if ( $data_button >= 1 && $data_button <= 4 ) {
			echo ' data-button="' . esc_html( $data_button ) . '"';
		}

		echo ' href="#">' . esc_html__( 'How to buy in installments', 'woo-paylate' ) . '</a>';

		return ob_get_clean();
	}

	/**
	 * Buy button shortcode.
	 *
	 * @param array $atts Shortcode parameters.
	 *
	 * @return string Button html.
	 */
	public function paylate_buy_button_shortcode( $atts ) {
		$atts = shortcode_atts(
			[
				'name'     => '',
				'category' => '',
				'price'    => 0,
				'count'    => 1,
				'fio'      => '',
				'passport' => '',
				'type'     => 0,
			],
			$atts
		);

		$atts['price'] = (int) $atts['price'];
		if ( 0 > $atts['price'] ) {
			$atts['price'] = 0;
		}

		$atts['count'] = (int) $atts['count'];
		if ( 0 > $atts['count'] ) {
			$atts['count'] = 0;
		}

		$atts['type'] = (int) $atts['type'];
		if ( 0 > $atts['type'] || 3 < $atts['type'] ) {
			$atts['type'] = 0;
		}

		if ( '' === $atts['name'] || 0 === $atts['price'] || 0 === $atts['count'] ) {
			return __( 'Invalid parameter: name, price or count', 'woo-paylate' );
		}

		$cur_item['Name'] = $atts['name'];
		if ( '' !== $atts['category'] ) {
			$cur_item['Category'] = $atts['category'];
		}
		$cur_item['Price'] = $atts['price'];
		$cur_item['Count'] = $atts['count'];
		if ( '' !== $atts['fio'] ) {
			$cur_item['fio'] = $atts['fio'];
		}
		if ( '' !== $atts['passport'] ) {
			$cur_item['passport'] = $atts['passport'];
		}

		$goods[] = $cur_item;

		if ( 'yes' === $this->test_mode ) {
			$order_id = self::TEST_ORDER_ID;
		} else {
			$order_id = 0;
		}

		$all_cats = $atts['category'];
		$token    = md5( $this->login . md5( $this->password ) . $order_id );

		ob_start();

		?>
		<script>
			// Установка параметров.
			const SetPayLate = {
				client_id: '<?php echo esc_html( $this->client_id ); ?>',
				order_id: <?php echo esc_html( $order_id ); ?>,
				category: '<?php echo esc_html( $all_cats ); ?>',
				goods: '<?php echo wp_json_encode( $goods, JSON_UNESCAPED_UNICODE ); ?>',
				autostart: false,
				token: '<?php echo esc_html( $token ); ?>',
				image_type: <?php echo esc_html( $atts['type'] ); ?>
			};

			// Отображение кнопки
			PayLateButton( SetPayLate );
		</script>
		<?php

		return ob_get_clean();
	}
}
