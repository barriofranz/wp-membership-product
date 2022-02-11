<?php

// echo str_replace("\\","/",dirname(__FILE__)) . "/../../../wp-load.php";die();
require_once( str_replace("\\","/",dirname(__FILE__)) . "/../../../wp-load.php");
require_once 'includes/class-gf-give-product-membership.php';
// global $table_prefix, $wpdb;

// $membership_id = $argv[1];
// $products = $argv[2];

// ob_end_flush();
// ob_start();
// @ini_set('zlib.output_compression', 0);
// @ini_set('implicit_flush', 1);
// @ob_end_clean();
set_time_limit(10);

// error_reporting(E_ALL);
// ini_set("display_errors", 1);

// aatbl

$sql = 'insert into aatbl values (1,1)';

// $result = $wpdb->insert('aatbl', [
//     'col1' => 1, 'col2'=>2
// ]);
// $result = $wpdb->get_results($sql);

// ob_end_flush();
