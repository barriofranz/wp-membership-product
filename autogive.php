<?php

$token = $_GET['token'];
$id_membership = isset( $_GET['id_membership'] ) ? $_GET['id_membership'] : 0;
$year_month = isset( $_GET['year_month'] ) ? $_GET['year_month'] : '';
if($token == md5('morningstardigital_oSk3v@n8B')) {
    require_once(dirname(__FILE__) . "/../../../wp-load.php");
    require_once './includes/class-gf-give-product-membership.php';
    global $table_prefix, $wpdb;

    // ob_end_flush();
    ob_start();
    @ini_set('zlib.output_compression', 0);
    @ini_set('implicit_flush', 1);
    @ob_end_clean();
    set_time_limit(10);

    if($year_month != '') {
        $productTag = 'autogive_' . $year_month;
    } else {
        // $now = new DateTime(null, new DateTimeZone('Australia/Melbourne'));
		// $yearmonth = $now->format('Y_m');
        $yearmonth = Gf_Give_Product_Membership::getWPCurrentYM();
        $productTag = 'autogive_' . $yearmonth;
    }

    // $productTag = 'autogive_2021_07'; // for testing

    $membershipArgs = [];
    if($id_membership != 0 ) {
        $membershipArgs['post__in'] = [$id_membership];
    }

    $memberships = wc_memberships_get_membership_plans($membershipArgs);

    $gpmObj = new Gf_Give_Product_Membership();
    foreach ($memberships as $membership) {

        //get product of the month
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

        $users = $gpmObj->get_active_members_for_membership($membership->id);

        foreach ($products as $product) {
            $id_product = $product->ID;
            $id_membership = $membership->id;
            $productArr = [$id_product];

            foreach ($users as $user) {
                $has_bought_items = $gpmObj->has_bought_items($user->user_id, $productArr);
                if($has_bought_items == false) {
                    $gpmObj->create_order( $user->user_id, $productArr, $membership->id );
                    $wpdb->insert($wpdb->prefix . 'give_product_membership_analytics', array(
                        'id_user' => $user->user_id,
                        'id_product' => $id_product,
                        'id_membership' => $membership->id,
                        'datetime_given' => date('Y-m-d H:i:s'),
                    ));
                    // sleep(1);
                }

                ob_flush();
                flush();
                set_time_limit(10);
            }

        }
    }
    ob_end_flush();

    die();
}

?>
