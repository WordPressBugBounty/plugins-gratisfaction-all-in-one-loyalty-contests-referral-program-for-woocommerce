<?php

/**
 * Uninstalls Gratisfaction
 *
 * Uninstalling removes all user roles, product data, and options.
 *
 * @author  Gratisfaction
 * @package GRWOO
 * @since   3.0
 */

// Check that we should be doing this
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit; // Exit if accessed directly
}
//TODO:store api url into DB
$api_url = 'https://gratisfaction.appsmav.com/newapi/v2/';

try
{
    // Delete stored informations
    $id_shop = get_option('grconnect_shop_id', 0);
    $id_site = get_option('grconnect_appid', 0);
    $payload = get_option('grconnect_payload', 0 );
    delete_option('grconnect_shop_id');
    delete_option('grconnect_appid');
    delete_option('grconnect_payload');
    delete_option('grconnect_admin_email');

    $param = array('app'=>'gr', 'plugin_type'=>'WP', 'status'=>'delete', 'id_shop'=>$id_shop, 'id_site'=>$id_site, 'payload'=>$payload);
    $url = $api_url . 'pluginStatus';

    wp_remote_post($url, array('body' => $param, 'timeout' => 10));

}
catch(Exception $e){}
