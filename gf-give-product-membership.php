<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://morningstardigital.com.au/
 * @since             1.0.0
 * @package           Gf_Give_Product_Membership
 *
 * @wordpress-plugin
 * Plugin Name:       GF Give product membership
 * Plugin URI:        https://morningstardigital.com.au/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.1.1
 * Author:            Morningstar Digital
 * Author URI:        https://morningstardigital.com.au/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gf-give-product-membership
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'GF_GIVE_PRODUCT_MEMBERSHIP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gf-give-product-membership-activator.php
 */
function activate_gf_give_product_membership() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gf-give-product-membership-activator.php';
	Gf_Give_Product_Membership_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gf-give-product-membership-deactivator.php
 */
function deactivate_gf_give_product_membership() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gf-give-product-membership-deactivator.php';
	Gf_Give_Product_Membership_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_gf_give_product_membership' );
register_deactivation_hook( __FILE__, 'deactivate_gf_give_product_membership' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gf-give-product-membership.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gf_give_product_membership() {

	$plugin = new Gf_Give_Product_Membership();
	$plugin->run();
	add_action( 'plugins_loaded', array( 'GF_Give_Product_Membership', 'get_instance' ) );
}
run_gf_give_product_membership();


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


add_action("wp_ajax_getGpmDatasource", "getGpmDatasource"); //  set cookie when checking checkbox
function getGpmDatasource()
{
	global $wpdb; // this is how you get access to the database
	// echo json_encode([]);
	// die();
	$daterangeFrom = $_POST['daterangeFrom'];
	$daterangeTo = $_POST['daterangeTo'];
	$id_membership = (int)$_POST['id_membership'];
	$id_product = (int)$_POST['id_product'];

	$args = array(
		'post_type' => 'wc_membership_plan',
	);
	$memberships = get_posts($args);
	$membs = [];
	$membsIds = [];
	foreach ($memberships as $key => $mm) {
		$membs[$mm->ID] = $mm;
		$membsIds[] = $mm->ID;
	}

	$args = [];
	$prods = [];
	$products = wc_get_products( $args );
	foreach ($products as $key => $pp) {
		$prods[$pp->get_id()] = $pp;
	}

	$dateRangeCondition = '';
	if($daterangeFrom != '') {
		$dateRangeCondition .= "AND DATE_FORMAT(datetime_given, '%Y-%m') >= DATE_FORMAT('".$daterangeFrom."', '%Y-%m') ";
	}

	if($daterangeTo != '') {
		$dateRangeCondition .= "AND  DATE_FORMAT(datetime_given, '%Y-%m') <= DATE_FORMAT('".$daterangeTo."', '%Y-%m') ";
	}

	if( $id_membership == 0 && $id_product == 0 ) {
		$sql = 'SELECT count(id_product) as count, DATE_FORMAT(datetime_given, "%Y-%m") as date_g, id_membership  FROM '.$wpdb->prefix.'give_product_membership_analytics';
		$sql .= ' WHERE 1 = 1 ';
		$sql .= $dateRangeCondition;
		$sql .= "AND id_membership IN ('" . implode("','",$membsIds) . "')";
		$sql .= ' GROUP BY id_membership, DATE_FORMAT(datetime_given, "%Y-%m")
		ORDER BY date_g';

		$result = $wpdb->get_results($sql);

			// ['date', 'Given products', 'ss']
		$output = [];
		$first = ['date'];
		$temp = [];
		foreach ($result as $res) { // init first
			if(!in_array($membs[$res->id_membership]->post_title, $first )) {
				$first[] = $membs[$res->id_membership]->post_title;
			}
		}
		foreach ($result as $res) { // init first
			if(!array_key_exists($res->date_g, $temp )) {
				$temp[$res->date_g][0] = $res->date_g;
			}

			$pos = array_search($membs[$res->id_membership]->post_title, $first);
			$temp[$res->date_g][$pos] = (int)$res->count;
		}


		foreach ($temp as $tkey => &$tt) {
			ksort($tt);
			// if($tkey == '0') {
			// 	continue;
			// }
			foreach ($first as $fkey => $ff) {
				if(!array_key_exists($fkey, $tt)) {
					$tt[$fkey] = 0;
				}
			}

		}
		// echo '<pre>';print_r($temp);echo '</pre>';die();
		$output[] = $first;
		foreach ($temp as $tt2) {
			$output[] = $tt2;
		}


		// echo '<pre>';print_r($output);echo '</pre>';die();
	} else if( $id_membership > 0 && $id_product == 0) {
		$sql = 'SELECT count(id_product) as count, DATE_FORMAT(datetime_given, "%Y-%m") as date_g FROM '.$wpdb->prefix.'give_product_membership_analytics';
		$sql .= ' WHERE id_membership = ' . $id_membership;
		$sql .= $dateRangeCondition;
		$sql .= "AND id_membership IN ('" . implode("','",$membsIds) . "')";
		$sql .= ' GROUP BY DATE_FORMAT(datetime_given, "%Y-%m")
		ORDER BY date_g';
		$result = $wpdb->get_results($sql);

		$output = [['date', $membs[$id_membership]->post_title]];
		foreach ($result as $res) {
			$output[] = [
				$res->date_g,
				(int)$res->count,
			];
		}

	} else if( $id_membership ==  0 && $id_product > 0) {
		$sql = 'SELECT count(id_product) as count, DATE_FORMAT(datetime_given, "%Y-%m") as date_g FROM '.$wpdb->prefix.'give_product_membership_analytics';
		$sql .= ' WHERE id_product = ' . $id_product;
		$sql .= $dateRangeCondition;
		$sql .= "AND id_membership IN ('" . implode("','",$membsIds) . "')";
		$sql .= ' GROUP BY DATE_FORMAT(datetime_given, "%Y-%m")
		ORDER BY date_g';
		$result = $wpdb->get_results($sql);

		$output = [['date', $prods[$id_product]->get_name()]];
		foreach ($result as $res) {
			$output[] = [
				$res->date_g,
				(int)$res->count,
			];
		}

	} else if( $id_membership >  0 && $id_product > 0) {
		$sql = 'SELECT count(id_product) as count, DATE_FORMAT(datetime_given, "%Y-%m") as date_g FROM '.$wpdb->prefix.'give_product_membership_analytics';
		$sql .= ' WHERE id_membership = ' . $id_membership;
		$sql .= ' AND id_product = ' . $id_product;
		$sql .= $dateRangeCondition;
		$sql .= "AND id_membership IN ('" . implode("','",$membsIds) . "')";
		$sql .= ' GROUP BY DATE_FORMAT(datetime_given, "%Y-%m")
		ORDER BY date_g';
		$result = $wpdb->get_results($sql);

		$output = [['date', $membs[$id_membership]->post_title]];
		foreach ($result as $res) {
			$output[] = [
				$res->date_g,
				(int)$res->count,
			];
		}

	}

	if(count($output) == 1) {
		echo json_encode([]);
		die();
	}

	echo json_encode($output);
	die();
}


function add_gmp_custom_email_1_woocommerce_email( $email_classes ) {

    // include our custom email class
    require( 'includes/class-gf-customemail1.php' );

    // add the email class to the list of email classes that WooCommerce loads
    $email_classes['GF_Customemail1'] = new GF_Customemail1();

    return $email_classes;

}
add_filter( 'woocommerce_email_classes', 'add_gmp_custom_email_1_woocommerce_email' );




// add_action( 'woocommerce_before_cart', 'md_subscription_autogive_product_after_payment_complete');
add_action( 'woocommerce_payment_complete', 'md_subscription_autogive_product_after_payment_complete', 2, 1);
// add_action( 'woocommerce_thankyou', 'md_subscription_autogive_product_after_payment_complete' );
function md_subscription_autogive_product_after_payment_complete($order_id)
{
	global $table_prefix, $wpdb;

	$gpmObj = new Gf_Give_Product_Membership();
	if ($gpmObj->checkIfSubs($order_id)) {
		$order = wc_get_order( $order_id );
		$order->update_status( 'completed' );
	}

}



add_action( 'woocommerce_order_status_completed', 'gpm_giveproduct_after_order_complete');
function gpm_giveproduct_after_order_complete($order_id)
{
	global $table_prefix, $wpdb;
	// return;
	$gpmObj = new Gf_Give_Product_Membership();
	if ($gpmObj->checkIfSubs($order_id))
	{
		$completed_order = new WC_Order($order_id);
		$user_id = $completed_order->get_customer_id();

		// $now = new DateTime(null, new DateTimeZone('Australia/Melbourne'));
		// $yearmonth = $now->format('Y_m');
		$yearmonth = Gf_Give_Product_Membership::getWPCurrentYM();
		$productTag = 'autogive_' . $yearmonth;
		//get product of the month

		$membershipArgs = [];
		// $id_membership = $membership->id;
		// if($id_membership != 0 ) {
		//     $membershipArgs['post__in'] = [$id_membership];
		// }

		$memberships = wc_memberships_get_membership_plans($membershipArgs);

		$gpmObj = new Gf_Give_Product_Membership();
		foreach ($memberships as $membership) {

			$users = $gpmObj->get_active_members_for_membership($membership->id);
			$userInThisSubs = false;
			foreach ($users as $user) {
				if($user->user_id == $user_id) {
					$userInThisSubs = true;
					break;
				}

			}
			if($userInThisSubs == false) {
				continue;
			}

			$id_membership = $membership->id;
			$productArgs = array(
				'post_type' => 'product',
				'post_status' => 'publish',
				'meta_key' => '_md_subscription_ym_flag_data_' . $membership->id,
				'meta_query' => array(
					array(
						'key' => '_md_subscription_ym_flag_data_' . $membership->id,
						'value' => $productTag,
						// 'value' => '%'.$productTag.'%',
						'compare' => 'LIKE',
					)
				)
			);
			$query = new WP_Query($productArgs);
			$products = $query->posts;

			$gpmObj = new Gf_Give_Product_Membership();
			foreach ($products as $product) {
				$id_product = $product->ID;
				$id_membership = $id_membership;
				$productArr = [$id_product];

				$has_bought_items = $gpmObj->has_bought_items($user_id, $productArr);
				if($has_bought_items == false) {
					$gpmObj->create_order( $user_id, $productArr, $id_membership );
					$wpdb->insert($wpdb->prefix . 'give_product_membership_analytics', array(
						'id_user' => $user_id,
						'id_product' => $id_product,
						'id_membership' => $id_membership,
						'datetime_given' => date('Y-m-d H:i:s'),
					));
					// sleep(1);
				}

			}// foreach product
		}// foreach memberships
	}
}

add_action( 'woocommerce_order_status_processing', 'gpm_prevent_email_if_subs');
function gpm_prevent_email_if_subs($order_id)
{
	global $table_prefix, $wpdb;
	$gpmObj = new Gf_Give_Product_Membership();
	if ($gpmObj->checkIfSubs($order_id))
	{
		add_filter( 'woocommerce_email_enabled_customer_processing_order', '__return_false' );
	}

}


add_action( 'wp_ajax_gpm_manual_gift', 'gpm_manual_gift' );
function gpm_manual_gift() {
	global $wpdb; // this is how you get access to the database
	echo "<pre>";print_r($_POST);echo "</pre>";
	$membership_id = $_POST['membership_id'];
	$products = $_POST['products'];


	// ob_end_flush();
	// ob_start();
	@ini_set('zlib.output_compression', 0);
	@ini_set('implicit_flush', 1);
	// @ob_end_clean();
	set_time_limit(10);

	$cmd = "php " . str_replace("\\","/",ABSPATH) . "wp-content/plugins/gf-give-product-membership/manualgive.php '".$membership_id."' '" . $products . "'";
	// $cmd = "php -v";
	$out = shell_exec($cmd);
echo "<pre>";print_r($cmd);echo "</pre>";
echo "<pre>";var_dump($out);echo "</pre>";
die();
	// global $table_prefix, $wpdb;
	//
	// $id_product = $_GET['products'];
	// $id_membership = $_GET['membership_id'];
	// $productArr = [$id_product];
	// $users = self::get_active_members_for_membership($id_membership);
	//
	// foreach ($users as $user) {
	// 	$has_bought_items = self::has_bought_items($user->user_id, $productArr);
	//
	// 	if($has_bought_items == false) {
	// 		$this->create_order( $user->user_id, $productArr, $id_membership );
	// 		$wpdb->insert($wpdb->prefix . 'give_product_membership_analytics', array(
	// 			'id_user' => $user->user_id,
	// 			'id_product' => $id_product,
	// 			'id_membership' => $id_membership,
	// 			'datetime_given' => date('Y-m-d H:i:s'),
	// 		));
	// 	}
	//
	// 	ob_flush();
	// 	flush();
	// 	set_time_limit(10);
	// }
	//
	// ob_end_flush


	wp_die(); // this is required to terminate immediately and return a proper response
}
