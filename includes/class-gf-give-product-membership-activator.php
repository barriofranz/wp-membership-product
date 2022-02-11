<?php

/**
 * Fired during plugin activation
 *
 * @link       https://morningstardigital.com.au/
 * @since      1.0.0
 *
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Gf_Give_Product_Membership
 * @subpackage Gf_Give_Product_Membership/includes
 * @author     Morningstar Digital <https://morningstardigital.com.au/>
 */
class Gf_Give_Product_Membership_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		global $table_prefix, $wpdb;

	    $tblname = 'give_product_membership_analytics';
	    $table = $table_prefix . "$tblname";
	    if($wpdb->get_var( "show tables like '$table'" ) != $table)
	    {
	        $sql = "CREATE TABLE `". $table . "` ( ";
	        $sql .= "  `id`  int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
	        $sql .= "  `id_user` int(11) NOT NULL default 0, ";
	        $sql .= "  `id_product` int(11) NOT NULL default 0, ";
	        $sql .= "  `id_membership` int(11) NOT NULL default 0, ";
	        $sql .= "  `datetime_given` datetime default null";
	        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	        dbDelta($sql);
	    }
	}

}
