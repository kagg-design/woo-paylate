<?php
/**
 * WC_PayLate_Plugin class file.
 *
 * @package woo-paylate
 */

/**
 * Class WC_PayLate_Plugin
 */
class WC_PayLate_Plugin {

	/**
	 * Required plugins.
	 *
	 * @var array
	 */
	protected $required_plugins = [];

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
	 * WC_PayLate_Plugin constructor.
	 */
	public function __construct() {
		$this->required_plugins = [
			[
				'plugin' => 'woocommerce/woocommerce.php',
				'name'   => 'WooCommerce',
				'slug'   => 'woocommerce',
				'class'  => 'WooCommerce',
				'active' => false,
			],
		];

		$options           = get_option( 'woocommerce_paylate_gateway_settings' );
		self::$log_enabled = $options['log_enabled'];
	}

	/**
	 * Maybe run plugin.
	 */
	public function maybe_run() {
		add_action( 'admin_init', [ $this, 'check_requirements' ] );

		if ( ! $this->requirements_met() ) {
			return;
		}

		add_action( 'plugins_loaded', [ $this, 'bootstrap' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( WOO_PAYLATE_FILE ), [ $this, 'add_settings_link' ] );
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );
		add_action( 'init', [ $this, 'check_for_paylate' ] );
	}

	/**
	 * Bootstrap plugin on 'plugins_loaded' event.
	 */
	public function bootstrap() {
		static $gateway;

		if ( ! isset( $gateway ) ) {
			$gateway = new WC_PayLate_Gateway();
		}
	}

	/**
	 * Check plugin requirements. If not met, show message and deactivate plugin.
	 */
	public function check_requirements() {
		if ( $this->requirements_met() ) {
			return;
		}

		add_action( 'admin_notices', [ $this, 'show_plugin_not_found_notice' ] );

		if ( ! is_plugin_active( plugin_basename( WOO_PAYLATE_FILE ) ) ) {
			return;
		}

		deactivate_plugins( plugin_basename( WOO_PAYLATE_FILE ) );

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		add_action( 'admin_notices', [ $this, 'show_deactivate_notice' ] );
	}

	/**
	 * Check if plugin requirements met.
	 *
	 * @return bool Requirements met.
	 */
	private function requirements_met() {
		$all_active = true;

		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		foreach ( $this->required_plugins as $key => $required_plugin ) {
			if ( is_plugin_active( $required_plugin['plugin'] ) ) {
				$this->required_plugins[ $key ]['active'] = true;
			} else {
				$all_active = false;
			}
		}

		return $all_active;
	}

	/**
	 * Show required plugins not found message.
	 */
	public function show_plugin_not_found_notice() {
		$message = __(
			'Gateway for PayLate on WooCommerce plugin requires the following plugins installed and activated: ',
			'woo-paylate'
		);

		$message_parts = [];
		foreach ( $this->required_plugins as $required_plugin ) {
			if ( ! $required_plugin['active'] ) {
				$href = '/wp-admin/plugin-install.php?tab=plugin-information&plugin=';

				$href .= $required_plugin['slug'] . '&TB_iframe=true&width=640&height=500';

				$message_parts[] =
					'<em><a href="' . $href . '" class="thickbox">' . $required_plugin['name'] . '</a></em>';
			}
		}

		$count = count( $message_parts );

		foreach ( $message_parts as $key => $message_part ) {
			if ( 0 !== $key ) {
				if ( ( $count - 1 ) === $key ) {
					$message .= ' and ';
				} else {
					$message .= ', ';
				}
			}

			$message .= $message_part;
		}

		$message .= '.';

		$this->admin_notice( $message, 'notice notice-error is-dismissible' );
	}

	/**
	 * Show a notice to inform the user that the plugin has been deactivated.
	 */
	public function show_deactivate_notice() {
		$this->admin_notice(
			__( 'Gateway for PayLate on WooCommerce plugin has been deactivated.', 'woo-paylate' ),
			'notice notice-info is-dismissible'
		);
	}

	/**
	 * Show admin notice.
	 *
	 * @param string $message Message to show.
	 * @param string $class   Message class: notice notice-success notice-error notice-warning notice-info
	 *                        is-dismissible.
	 */
	private function admin_notice( $message, $class ) {
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<p>
				<span style="display: block; margin: 0.5em 0.5em 0 0; clear: both;">
				<?php echo wp_kses( $message, wp_kses_allowed_html( 'post' ) ); ?>
				</span>
			</p>
		</div>
		<?php
	}

	/**
	 * Load plugin text domain.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'woo-paylate',
			false,
			dirname( plugin_basename( WOO_PAYLATE_FILE ) ) . '/languages/'
		);
	}

	/**
	 * Add link to plugin setting page on plugins page.
	 *
	 * @param array $links Plugin links.
	 *
	 * @return array Plugin links
	 */
	public function add_settings_link( $links ) {
		$action_links = [
			'settings' =>
				'<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=paylate_gateway' ) .
				'" aria-label="' . esc_attr__( 'View Gateway for PayLate on WooCommerce settings', 'woo-paylate' ) .
				'">' . esc_html__( 'Settings', 'woo-paylate' ) . '</a>',
		];

		return array_merge( $action_links, $links );
	}

	/**
	 * Function to check if request to WordPress is related to this Gateway for PayLate on WooCommerce plugin.
	 */
	public function check_for_paylate() {
		/**
		 * If POST contains application_id - it is request from PayLate service.
		 * We have to start gateways and invoke check action.
		 */
		if ( isset( $_POST['application_id'] ) ) {
			// Start the gateways.
			WC()->payment_gateways();
			do_action( 'check_paylate_gateway' );
		}

		/**
		 * If GET contains paylate_gateway - it is self hook from this plugin.
		 * We have to create form and make POST request to the PayLate service.
		 */
		if ( isset( $_GET['paylate_gateway'] ) ) {
			if (
				isset( $_GET['_wpnonce'] ) &&
				wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'paylate_gateway' )
			) {
				$port = filter_input( INPUT_GET, 'port', FILTER_VALIDATE_INT );
				$port = $port ? ':' . $port : '';
				?>
				<form
					id="paylate_form"
					action="https://paylate.ru<?php echo esc_html( $port ); ?>/bypartner"
					method="post">
					<?php
					foreach ( $_GET as $a => $b ) {
						if ( ( 'paylate_gateway' !== $a ) && ( '_wpnonce' !== $a ) ) {
							?>
							<input
								type="hidden" name="<?php echo esc_attr( $a ); ?>"
								value="<?php echo esc_attr( $b ); ?>">
							<?php
						}
					}
					?>
					<input type="hidden" name="action" value="by_partner"/>
				</form>

				<script type="text/javascript">
					document.getElementById( 'paylate_form' ).submit();
				</script>
				<?php
				die();
			}

			wp_safe_redirect( home_url() );
			die();
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
}
