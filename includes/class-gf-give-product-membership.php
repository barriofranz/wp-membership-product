<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://morningstardigital.com.au/
 * @since      1.0.0
 *
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 * @author     Morningstar Digital <https://morningstardigital.com.au/>
 */
define( 'WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION', '1.0.0' ); // WRCS: DEFINED_VERSION.
class Gf_Give_Product_Membership {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Gf_Give_Product_Membership_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'GF_GIVE_PRODUCT_MEMBERSHIP_VERSION' ) ) {
			$this->version = GF_GIVE_PRODUCT_MEMBERSHIP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'gf-give-product-membership';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();


		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'admin_init', array( $this, 'init' ), 20 );

			// Load chosen scripts.
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_chosen_scripts' ), 20 );

			// Update menu.
			add_action( 'admin_menu', array( $this, 'add_menu_item' ) );

			// Admin notices.
			add_action( 'admin_notices', array( $this, 'admin_notice' ) );

			// Notice on customers order screen.
			add_action( 'woocommerce_view_order', array( $this, 'display_given_status' ) );

			// Notice on edit order screen.
			add_action( 'woocommerce_admin_order_data_after_order_details', array( $this, 'display_given_status_admin' ) );

			// Add AJAX functionality.
			add_action( 'wp_ajax_give_products_json_search_products_and_variations', array( $this, 'json_search_products_and_variations' ) );

			// Add Given Order email.
			// add_action( 'woocommerce_email_classes', array( $this, 'add_emails' ), 20, 1 );

			// Add screen id.
			add_filter( 'woocommerce_screen_ids', array( $this, 'woocommerce_screen_ids' ) );

			// Includes.
			add_action( 'init', array( $this, 'includes' ) );

			// add_action( 'woocommerce_email', array( $this, 'gf_gpm_disable_completed_order_email' ) );

			// add_action( 'woocommerce_email_recipient_completed_order', array( $this, 'gpm_disable_order_complete_email' ), 15, 2 );
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Gf_Give_Product_Membership_Loader. Orchestrates the hooks of the plugin.
	 * - Gf_Give_Product_Membership_i18n. Defines internationalization functionality.
	 * - Gf_Give_Product_Membership_Admin. Defines all hooks for the admin area.
	 * - Gf_Give_Product_Membership_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gf-give-product-membership-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-gf-give-product-membership-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-gf-give-product-membership-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-gf-give-product-membership-public.php';

		$this->loader = new Gf_Give_Product_Membership_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Gf_Give_Product_Membership_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Gf_Give_Product_Membership_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Gf_Give_Product_Membership_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Gf_Give_Product_Membership_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Gf_Give_Product_Membership_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}




	/**
	 * Class instance.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Main plugin file.
	 *
	 * @var string
	 */
	public static $plugin_file = __FILE__;

	/**
	 * Initialize the plugin.
	 *
	 * @since 1.0
	 */


	/**
	 * Load necessary files.
	 */
	public function includes() {
		// include_once __DIR__ . '/includes/class-wc-give-products-privacy.php';
	}

	/**
	 * Init.
	 *
	 * @since 1.0
	 */
	public function init() {
		// Make sure this processing runs on the right page.
		if ( isset( $_GET['page'] ) && 'give_products_membership' === $_GET['page'] ) {
			// echo '<pre>-';print_r($_GET['action'] );echo '</pre>';
			// echo '<pre>-';print_r($_GET['membership_id'] );echo '</pre>';
			// echo '<pre>-';print_r($_GET['products'] );echo '</pre>';
			// echo '<pre>-';print_r($_GET['give_products_membership_nonce'] );echo '</pre>';
			// echo '<pre>-';var_dump(wp_verify_nonce( $_GET['give_products_membership_nonce'], 'give_products_membership' ) );echo '</pre>';
			// die();
			// Process any post data.
			if ( isset( $_GET['action'] ) &&
				'give' === $_GET['action'] &&
				isset( $_GET['membership_id'] ) &&
				$_GET['membership_id'] &&
				isset( $_GET['products'] ) &&
				isset( $_GET['give_products_membership_nonce'] ) &&
				wp_verify_nonce( $_GET['give_products_membership_nonce'], 'give_products_membership' )
			) {

				ob_end_flush();
				ob_start();
				@ini_set('zlib.output_compression', 0);
				@ini_set('implicit_flush', 1);
				@ob_end_clean();
				set_time_limit(10);
				global $table_prefix, $wpdb;

				$id_product = $_GET['products'];
	            $id_membership = $_GET['membership_id'];
	            $productArr = [$id_product];
	            $users = self::get_active_members_for_membership($id_membership);

	            foreach ($users as $user) {
	                $has_bought_items = self::has_bought_items($user->user_id, $productArr);

	                if($has_bought_items == false) {
	                    $this->create_order( $user->user_id, $productArr, $id_membership );
	                    $wpdb->insert($wpdb->prefix . 'give_product_membership_analytics', array(
	                        'id_user' => $user->user_id,
	                        'id_product' => $id_product,
	                        'id_membership' => $id_membership,
	                        'datetime_given' => date('Y-m-d H:i:s'),
	                    ));
	                }
					// if($user->user_id == 1872) {
						// echo '<pre>';print_r($user->user_id);echo '</pre>';
						// echo '<pre>';print_r($user->user_email);echo '</pre>';
						// echo '<pre>';var_dump($has_bought_items);echo '</pre>';
						// $this->create_order( $user->user_id, $productArr, $id_membership );
					// }
	                ob_flush();
	                flush();
	                set_time_limit(10);
	            }

				ob_end_flush();

			} elseif ( isset( $_GET['action'] ) ) {
				// Error.
				$url = add_query_arg( array( 'post_type' => 'product', 'page' => 'give_products_membership', 'message' => '2' ), admin_url( 'edit.php' ) );
				wp_safe_redirect( $url );
			}
		}

	}

	public static function has_bought_items($customer_id, $prod_arr) {
		$bought = false;

		// Set HERE ine the array your specific target product IDs
		// $prod_arr = array( '21', '67' );

		// Get all customer orders
		$customer_orders = get_posts( array(
			'numberposts' => -1,
			'meta_key'    => '_customer_user',
			'meta_value'  => $customer_id,
			'post_type'   => 'shop_order', // WC orders post type
			'post_status' => 'wc-completed' // Only orders with status "completed"
		) );
		foreach ( $customer_orders as $customer_order ) {
			// Updated compatibility with WooCommerce 3+
			// $order_id = method_exists( $order, 'get_id' ) ? $order->get_id() : $order->id;
			$order = wc_get_order( $customer_order );

			// Iterating through each current customer products bought in the order
			foreach ($order->get_items() as $item) {
				// WC 3+ compatibility
				if ( version_compare( WC_VERSION, '3.0', '<' ) )
					$product_id = $item['product_id'];
				else
					$product_id = $item->get_product_id();

				// Your condition related to your 2 specific products Ids
				if ( in_array( $product_id, $prod_arr ) )
					$bought = true;
			}
		}
		// return "true" if one the specifics products have been bought before by customer
		return $bought;
	}

	/**
	 * Enqueue JS & CSS for Chosen select boxes.
	 *
	 * @since 1.0
	 */
	public function enqueue_chosen_scripts() {
		global $current_screen;
		global $woocommerce;

		if ( 'product_page_give_products_membership' === $current_screen->base ) {
			// Chosen JS.
			// wp_enqueue_script( 'ajax-chosen' );
			// wp_enqueue_script( 'chosen' );

			// Chosen CSS.
			// wp_enqueue_style( 'woocommerce_admin_styles', $woocommerce->plugin_url() . '/assets/css/admin.css', array(), WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION );

			// wp_enqueue_script( 'woocommerce_admin_enhanced_select', $woocommerce->plugin_url() . '/assets/js/admin/wc-enhanced-select.js', array(), WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION );

			$css = plugins_url('gf-give-product-membership/public/css/gf-give-product-membership-public.css');
			wp_enqueue_style( 'gf_gpm_css', $css, array(), WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION );

			$js = plugins_url('gf-give-product-membership/public/js/gf-give-product-membership-public.js');
			wp_enqueue_script( 'gf_gpm_js', $js, array(), WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION );
			wp_localize_script( 'gf_gpm_js', 'ajaxArr', array( 'ajaxDatasource' => admin_url( 'admin-ajax.php' )));

			wp_enqueue_script( 'apexcharts', 'https://www.gstatic.com/charts/loader.js', array(), WC_GIVE_PRODUCTS_MEMBERSHIP_VERSION );


		}
	}


	// public function enqueue_scripts() {
	//
	// 	/**
	// 	 * This function is provided for demonstration purposes only.
	// 	 *
	// 	 * An instance of this class should be passed to the run() function
	// 	 * defined in Gf_Splitorder_Loader as all of the hooks are defined
	// 	 * in that particular class.
	// 	 *
	// 	 * The Gf_Splitorder_Loader will then create the relationship
	// 	 * between the defined hooks and the functions defined in this
	// 	 * class.
	// 	 */
	// 	wp_register_script( "gf_var_scripts", plugin_dir_url( __FILE__ ) . 'js/gf-splitorder-public.js', array('jquery') );
  	// 	wp_localize_script( 'gf_var_scripts', 'gfAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));
	// 	wp_enqueue_script( 'gf_var_scripts' );
	//
	// 	// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gf-splitorder-public.js', array( 'jquery' ), $this->version, false );
	//
	// }

	/**
	 * Add Give Products to admin menu.
	 *
	 * @since 1.0
	 */
	public function add_menu_item() {
		if ( is_admin() ) {
			add_submenu_page( 'edit.php?post_type=product', __( 'Give Products Membership', 'woocommerce-give-products-membership' ), __( 'Give Products Membership', 'woocommerce-give-products-membership' ), 'edit_users', 'give_products_membership', array( $this, 'display_page' ) );
		}
		return false;
	}


	/**
	 * Display Give Products admin page.
	 *
	 * @since 1.0
	 */
	public function display_page() {
		// Display page content.
		echo sprintf(
			'
			<div class="wrap">
			<h2>%s</h2>

			<div class="md-panel">

			<p>%s</p>
			<form action="" method="get" id="gf-gpm-do-gift-form">
			<input type="hidden" name="post_type" value="product"/>
			<input type="hidden" name="page" value="give_products_membership"/>
			<input type="hidden" name="action" value="give"/>',
			esc_html__( 'Give Products Membership', 'woocommerce-give-products-membership' ),
			wp_kses_post( __( '<b>Select a membership plan</b>:', 'woocommerce-give-products' ) )
		);

		$args = array(
			'post_type' => 'wc_membership_plan',
			'numberposts' => 100,
		);
		$memberships = get_posts($args);

		?>
		<select id="membership_id" style="width: 30%;" class="wc-customer-search" name="membership_id" data-allow_clear="true" data-placeholder="<?php esc_attr_e( 'Search for a membership plan', 'woocommerce-give-products' ); ?>">
		<?php
		echo '<option value="0" disabled selected>Select membership here...</option>';
		foreach ($memberships as $memb) {
			if ( $memb->post_status == 'publish' ) {
				echo '<option value="' . esc_attr( intval( $memb->ID ) ) . '" >' . esc_html( $memb->post_title ) . '</option>' . "\n";
			}
		}
		?>
		</select>
		<?php

		echo '<p>' . wp_kses_post( __( '<b>Select products</b>:', 'woocommerce-give-products-membership' ) ) . '</p>';
		$args = [
			'numberposts' => 500,
			'post_type' => 'product',
			// 'category' => array('fables-episode'),
		];
		$products = wc_get_products( $args );

		?>
		<select class="wc-product-search"  style="width: 30%;" name="products" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'woocommerce-give-products' ); ?>" data-allow_clear="true" data-action="woocommerce_json_search_products_and_variations">
		<?php
			echo '<option value="0" disabled selected>Select product here...</option>';
			foreach ($products as $prod) {
				if ( $prod->get_status() == 'publish' ) {
					echo '<option value="' . esc_attr( intval( $prod->get_id() ) ) . '" >' . esc_html( $prod->get_name() ) . '</option>' . "\n";
				}
			}
		?>
		</select>
		<?php
		wp_nonce_field( 'give_products_membership', 'give_products_membership_nonce' );
		echo '<p><input id="gf-gpm-do-gift" disabled="disabled" type="submit" value="' . __( 'Give product(s)', 'woocommerce-give-products' ) . '" class="button-primary"/></p>
			</form>
		</div>
		</div>';

		include_once __DIR__ . '/../public/partials/analytics-panel.php';

		// wp_nonce_field( 'give_products_membership', 'give_products_membership_nonce' );

	}

	/**
	 * Set order address details, for use with the WooCommerce 2.6 compatibility.
	 *
	 * @param object $order
	 * @param int $user_id
	 * @return void
	 */
	public function maybe_set_order_address_details( $order, $user_id ) {
		$keys = array(
			'billing_first_name',
			'billing_last_name',
			'billing_address_1',
			'billing_address_2',
			'billing_city',
			'billing_state',
			'billing_postcode',
			'billing_country',
			'billing_email',
			'billing_phone',
			'shipping_first_name',
			'shipping_last_name',
			'shipping_address_1',
			'shipping_address_2',
			'shipping_city',
			'shipping_state',
			'shipping_postcode',
			'shipping_country',
			'shipping_email',
			'shipping_phone',
		);

		$meta_values = get_user_meta( intval( $user_id ) );

		/*
		 * Backwards compatibility for WooCommerce 2.6. Note: This method works with 3.0
		 * and higher as well, yet should not be used, in favour of the CRUD helpers in the "else" clause.
		 */
		if ( version_compare( '3.0', WC_VERSION, '<' ) ) {
			$billing_address = array();
			$shipping_address = array();

			foreach ( $keys as $k ) {
				if (
					isset( $meta_values[ $k ] ) &&
					false !== strpos( $k, 'billing_' )
				) {
					$index = str_replace( 'billing_', '', $k );
					$billing_address[ $index ] = $meta_values[ $k ][0];
				}

				if (
					isset( $meta_values[ $k ] ) &&
					false !== strpos( $k, 'shipping_' )
				) {
					$index = str_replace( 'shipping_', '', $k );
					$shipping_address[ $index ] = $meta_values[ $k ][0];
				}
			}

			$order->set_address( $billing_address, 'billing' );
			$order->set_address( $shipping_address, 'shipping' );
		} else {
			// WooCommerce 3.0 and beyond, using CRUD helpers.
			foreach ( $keys as $k ) {
				if ( isset( $meta_values[ $k ] ) && method_exists( $order, 'set_' . $k ) ) {
					call_user_func_array( array( $order, 'set_' . $k ), array( $meta_values[ $k ][0] ) );
				}
			}

			$order->save();
		}
	}


	// function gf_gpm_disable_completed_order_email( $email_class ) {

		// remove_action( 'woocommerce_order_status_completed_notification', array( $email_class->emails['WC_Email_Customer_Completed_Order'], 'trigger' ) );

	// }


	public function gpm_disable_for_gifts( $enabled, $order )
	{
		if( isset($order)
			&& $order !== null
			&& count( $order->get_items() ) > 0
		) {

			foreach( $order->get_items() as $item ) {

				if ( 'line_item' == $item['type'] ) {

					$product = $order->get_product_from_item( $item );
					foreach ( array( 'fables-episode' ) as $category ) {
						if ( has_term( $category, 'product_cat', $product->get_post_data() ) ) {
							return false;
						}
					}
				}
			}
		}

		return $enabled;
	}

	/**
	 * Create new order based on selection.
	 *
	 * @param int   $user_id  User ID.
	 * @param array $products List of products.
	 * @since 1.0
	 */
	 public function create_order( $user_id, $products, $membership_id, $doRedirect = false) {
		global $woocommerce;

		// Get customer info.
		$user = get_userdata( $user_id );

		if ( ! empty( $products ) ) {

			// Create new order.
			$args = array(
				'customer_id'   => $user_id,
			);
			$order = wc_create_order( $args );

			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
				$order_id = $order->get_id();
				$products = ! empty( $products ) ? $products : array();
			} else {
				$order_id = $order->id;
				$products = isset( $products[0] ) ? explode( ',', $products[0] ) : array();
			}

			// Set the billing and shipping address details, if they exist for the selected customer.
			$this->maybe_set_order_address_details( $order, $user_id );

			// Set _given_order post meta to true.
			update_post_meta( $order_id, '_gf_gpm_given_order', 'yes' );

			// Track the order status - if we have products that need to be shipped we should change this to processing.
			$order_status = 'completed';
			// add_filter( 'woocommerce_email_enabled_customer_completed_order', '__return_false' );
			add_filter( 'woocommerce_email_enabled_customer_completed_order', array( $this, 'gpm_disable_for_gifts' ), 10, 2 );

			// Loop through each product we want to give away.
			foreach ( $products as $key => $value ) {

				// Get product data.
				$product = wc_get_product( $value );

				if ( $product ) {

					// Add product to order.
					$item              = array(
						'order_item_name' => $product->get_title(),
					);
					$item_id           = wc_add_order_item( $order_id, $item );
					$price_without_tax = version_compare( WC_VERSION, '3.0', '<' ) ? $product->get_price_excluding_tax() : wc_get_price_excluding_tax( $product );

					$product_id = $product->get_id();
					if ( is_callable( array( $product, 'get_type' ) ) && $product->get_type() === 'variation' ) {
						$product_id = $product->get_parent_id();
					}

					// Now add all of the product meta.
					wc_add_order_item_meta( $item_id, '_qty', 1 );
					wc_add_order_item_meta( $item_id, '_tax_class', $product->get_tax_class() );
					wc_add_order_item_meta( $item_id, '_product_id', $product_id );
					wc_add_order_item_meta( $item_id, '_variation_id', ( version_compare( WC_VERSION, '3.0', '<' ) && isset( $product->variation_id ) ) ? $product->variation_id : $product->get_id() );
					wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $price_without_tax ) );
					wc_add_order_item_meta( $item_id, '_line_subtotal_tax', '' );
					wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( 0 ) );
					wc_add_order_item_meta( $item_id, '_line_tax', '' );
					wc_add_order_item_meta( $item_id, '_line_tax_data', array( 'total' => array(), 'subtotal' => array() ) );

					// Store variation data in meta.
					if ( method_exists( $product, 'get_variation_attributes' ) ) {
						$variation_data = $product->get_variation_attributes();
						if ( $variation_data && is_array( $variation_data ) ) {
							foreach ( $variation_data as $key => $value ) {
								wc_add_order_item_meta( $item_id, str_replace( 'attribute_', '', $key ), $value );
							}
						}
					}

					// See if the product needs to be shipped.
					// if ( ( 'completed' === $order_status ) && $product->needs_shipping() ) {
						// $order_status = 'processing';
					// }
				} // End if.
			} // End foreach.

			// Update the order status.
			$args = array(
				'order_id' => $order_id,
				'status'   => $order_status,
			);

			// Update the order.
			$order = wc_update_order( $args );

			// Add a note that this product was gifted.
			$order->add_order_note( __( 'This order was gifted for membership_id '. $membership_id . '.', 'woocommerce-give-products' ) );

			if ( version_compare( WC_VERSION, '3.0', '<' ) ) {
				$order->reduce_order_stock();
			} else {
				wc_reduce_stock_levels( $order->get_id() );
			}

			// Give download permissions.
			wc_downloadable_product_permissions( $order_id );

			// Init the WooCommerce email classes.
			$woocommerce->mailer();

			// do_action( 'woocommerce_order_given', $order_id );
			do_action( 'gf_gpm_after_give_product', $order_id );

			// if ($doRedirect) {
			// 	$redirect = esc_url_raw( $_SERVER['HTTP_REFERER'] );
			//
			// 	// Add a success message.
			// 	$redirect = add_query_arg( 'message', '1', $redirect );
			// 	$redirect = add_query_arg( 'order_id', $order_id, $redirect );
			// 	$redirect = remove_query_arg( 'action', $redirect );
			//
			// 	wp_safe_redirect( $redirect );
			// }


		} // End if.

	}


	/**
	 * Add new WC emails.
	 *
	 * @param array $email_classes List of classes.
	 * @since 1.0
	 */
	public function add_emails( $email_classes ) {

		$email_classes['WC_Given_Order'] = include __DIR__ . '/includes/emails/class-wc-given-order.php';
		return $email_classes;
	}


	/**
	 * Display notice when products are given.
	 *
	 * @since 1.0
	 */
	public function admin_notice() {
		global $current_screen;
		if ( 'product_page_give_products' === $current_screen->base ) {
			if ( isset( $_GET['message'] ) ) {
				switch ( $_GET['message'] ) {
					case 1:
						$display_name = 'selected user';
						if ( isset( $_GET['userid'] ) && $_GET['userid'] > 0 ) {
							$user         = get_userdata( $_GET['userid'] );
							$display_name = $user->data->display_name;
						}
						$order_id  = '';
						$order_url = '';
						if ( isset( $_GET['order_id'] ) ) {
							$order_id  = $_GET['order_id'];
							$order_url = get_option( 'siteurl' ) . '/wp-admin/post.php?post=' . intval( $order_id ) . '&action=edit';
						}
						/* translators: 1: display name 2: order url 3: order id */
						$format = __( 'Product(s) given to %1$s in <a href="%2$s">order %3$s</a>.', 'woocommerce-give-products' );
						$format = sprintf( $format, $display_name, $order_url, $order_id );
						echo '<div class="updated"><p>' . wp_kses_post( $format ) . '</p></div>';
						break;
					case 2:
						$format = __( 'Make sure you select both a user to receive the gift and a product to give.', 'woocommerce-give-products' );
						echo '<div class="error"><p>' . wp_kses_post( $format ) . '</p></div>';
						break;
					case 3:
						$format = __( 'Processing error - please try again.', 'woocommerce-give-products' );
						echo '<div class="error"><p>' . wp_kses_post( $format ) . '</p></div>';
						break;
					default: break;
				}
			}
		}
	}


	/**
	 * Display message on front-end order view.
	 *
	 * @param int $order_id Order ID.
	 * @since 1.0
	 */
	public function display_given_status( $order_id ) {
		if ( $order_id ) {
			if ( 'yes' === get_post_meta( $order_id, '_wcgp_given_order', true ) ) {
				/* translators: 1: blog info name */
				echo "<div class='given_order'>" . sprintf( esc_html__( 'The products in this order were given to you by %s.', 'woocommerce-give-products' ), get_bloginfo( 'name' ) ) . '</div>';
			}
		}
	}


	/**
	 * Display message on back-end order view
	 *
	 * @param WC_Order $order Order object.
	 * @since 1.0
	 */
	public function display_given_status_admin( $order ) {
		if ( $order ) {
			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
				$order_id = $order->get_id();
			} else {
				$order_id = $order->id;
			}

			if ( 'yes' === get_post_meta( $order_id, '_wcgp_given_order', true ) ) {
				echo "<p class='form-field form-field-wide'>" . esc_html__( 'This order was given free of charge', 'woocommerce-give-products' ) . '</p>';
			}
		}
	}


	/**
	 * Display message on back-end order view.
	 *
	 * @since 1.0
	 */
	public function json_search_products_and_variations() {
		$posts = array();

		check_ajax_referer( 'search-customers', 'security' );

		$term = (string) urldecode( stripslashes( strip_tags( $_GET['term'] ) ) );

		if ( empty( $term ) ) {
			die();
		}

		$post_types = array( 'product', 'product_variation' );

		if ( is_numeric( $term ) ) {

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post__in'       => array( 0, $term ),
				'fields'         => 'ids',
			);

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'post_parent'    => $term,
				'fields'         => 'ids',
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

		} else {

			$args = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				's'              => $term,
				'fields'         => 'ids',
			);

			$args2 = array(
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'     => '_sku',
						'value'   => $term,
						'compare' => 'LIKE',
					),
				),
				'fields'         => 'ids',
			);

			$posts = array_unique( array_merge( get_posts( $args ), get_posts( $args2 ) ) );

		} // End if.

		$found_products = array();

		foreach ( $posts as $post ) {

			$sku = get_post_meta( $post, '_sku', true );

			if ( isset( $sku ) && $sku ) {
				$sku = ' (SKU: ' . $sku . ')';
			}

			$post_type = get_post_type( $post );

			if ( 'product_variation' === $post_type ) {
				$variation = new WC_Product_Variation( $post );
				$atts      = $variation->get_variation_attributes();
				$attlist   = '';
				foreach ( $atts as $att ) {
					if ( '' !== $attlist ) {
						$attlist .= ', ';
					}
					$attlist .= $att;
				}
				$title = str_replace( 'Variation #' . $post . ' of ', '', get_the_title( $post ) ) . ': ' . ucwords( $attlist );
			} else {
				$title = get_the_title( $post );
			}

			$found_products[ $post ] = $title . ' &ndash; #' . $post . $sku;

		}

		echo wp_json_encode( $found_products );
		die();
	}

	/**
	 * Screen IDS
	 *
	 * @param  array $ids List of Screen IDs.
	 * @return array
	 */
	public function woocommerce_screen_ids( $ids ) {
		return array_merge(
			$ids,
			array(
				'product_page_give_products',
			)
		);
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 * @since  1.0
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	public static function get_active_members_for_membership($id){
		global $wpdb;

		// Getting all User IDs and data for a membership plan
		return $wpdb->get_results( "
			SELECT DISTINCT um.user_id, u.user_email, u.display_name, p2.post_title, p2.post_type
			FROM {$wpdb->prefix}posts AS p
			LEFT JOIN {$wpdb->prefix}posts AS p2 ON p2.ID = p.post_parent
			LEFT JOIN {$wpdb->prefix}users AS u ON u.id = p.post_author
			LEFT JOIN {$wpdb->prefix}usermeta AS um ON u.id = um.user_id
			WHERE p.post_type = 'wc_user_membership'
			AND p.post_status IN ('wcm-active', 'wcm-free_trial')
			AND p2.post_type = 'wc_membership_plan'
			AND p2.ID = '$id'
		");
	}

	public static function getWPCurrentYM()
	{
		if (get_option('timezone_string') != '') {
			$timezone = get_option('timezone_string');
		} else if (get_option('gmt_offset') != '') {
			$timezone = get_option('gmt_offset');

			$tzArr = explode('.',$timezone);
			$tz = abs($tzArr[0]);
			$tzbk = $tzArr[0];
			if(isset($tzArr[1])) {
				if($tzArr[1]<=10) {
					$newTz1 = $tzArr[1] / 10;
				} else {
					$newTz1 = $tzArr[1] / 100;
				}
				$tz1 = $newTz1 * 60;
				$tz = str_pad($tz, 2, "0", STR_PAD_LEFT);
				$tz .= str_pad($tz1, 2, "0" ,STR_PAD_LEFT);
				$timezone = $tz;
			} else {
				$tz = str_pad($tz, 2, "0", STR_PAD_LEFT);
				$tz = str_pad($tz, 4, "0", STR_PAD_RIGHT);
				$timezone = $tz;
			}

			if ($tzbk < 0) {
				$timezone = '-'.$timezone;
			} else {
				$timezone = '+'.$timezone;
			}
		}
		$now = new DateTime(null, new DateTimeZone($timezone));
		return $now->format('Y_m');
	}

	// function gpm_disable_order_complete_email( $recipient, $order ) {
	// 	if( is_admin() ) return $recipient; // (Mandatory to avoid backend errors)

		// global $table_prefix, $wpdb;
		// $gpmObj = new Gf_Give_Product_Membership();
		// if ($gpmObj->checkIfSubs($order->get_id()))
		// {
		// 	return '';
		// }
		// return $recipient;

	// }

	public function checkIfSubs($order_id)
	{
		global $table_prefix, $wpdb;
		$completed_order = new WC_Order($order_id);

		$hasSubscription = false;
		$id_membership = 0;
		$hasPhys = false;
		foreach($completed_order->get_items() as $item_key => $item){
			$product = $item->get_product();
			$subsType = [
				'subscription_variation',
				'variable-subscription',
				'simple-subscription',
				'subscription',
			];
			if ( $product && in_array($product->get_type(), $subsType) ) {
				$hasSubscription = true;
			} else if( $product && ($product->get_virtual() == false) ) {
				$hasPhys = true;
			}
		}


		if( $hasSubscription == true &&  $hasPhys == false)
		{
			return true;
		}

		return false;
	}
}
