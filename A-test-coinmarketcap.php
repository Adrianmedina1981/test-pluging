<?php 
 
/**
 * Plugin Name: Buccano Princital Integracion BsaleMarket
 * Plugin URI: https://www.buccano.cl/
 * Description: Este plugin ha sido desarrollado por Adrian Medina para Princital
 * Version: 0.0.1
 * Author: Adrian Medina
 * 
 * Requires at least: 4.9.2
 * 
 *
 */
defined( 'ABSPATH' ) or die( '¡By By!' );
global $wpdb;
 
// Campos adicionales al formulario de registro
/**
 */

function wp_cmkcap_add_admin_bar_menu( $wp_admin_bar ) {
	$parent_node = array(
	   'id'    => 'wp_cmkcap_admin_bar',
	   'title' => 'Cryptocurrencies',
	);
 
	$wp_admin_bar->add_node( $parent_node );
 
	$api_endpoint = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/listings/latest';
	$api_args     = array( 'headers' => array( 'X-CMC_PRO_API_KEY' => 'cfc3fcfa-0d81-456a-bbbe-df6b07e7c8a0' ) );
	$queries      = array(
	   'start'   => 1,
	   'limit'   => 5,
	   'convert' => 'MXN',
	);
 
	$api_endpoint = add_query_arg( $queries, $api_endpoint );
	$response     = wp_remote_get( $api_endpoint, $api_args );
 
	if ( is_wp_error( $response ) ) {
	   $child_node = array(
		  'id'     => 'internal-error',
		  'title'  => 'Internal Error - Notify the administrator.',
		  'parent' => 'wp_cmkcap_admin_bar',
	   );
 
	   $wp_admin_bar->add_node( $child_node );
 
	   return;
	}
 
	$http_code = wp_remote_retrieve_response_code( $response );
	$cryptos   = json_decode( wp_remote_retrieve_body( $response ), true );
 
	if ( 200 !== $http_code ) {
	   $message    = 404 === $http_code ? $cryptos['message'] : $cryptos['status']['error_message'];
	   $child_node = array(
		  'id'     => 'api-error',
		  'title'  => 'API Error: ' . $message,
		  'parent' => 'wp_cmkcap_admin_bar',
	   );
 
	   $wp_admin_bar->add_node( $child_node );
 
	   return;
	}
 
	foreach ( $cryptos['data'] as $crypto_data ) {
	   $price      = number_format( $crypto_data['quote'][ $queries['convert'] ]['price'] );
	   $child_node = array(
		  'id'     => 'cryptocurrency-' . $crypto_data['id'],
		  'title'  => $crypto_data['name'] . ' / $' . $price . ' ' . $queries['convert'],
		  'parent' => 'wp_cmkcap_admin_bar',
	   );
 
	   $wp_admin_bar->add_node( $child_node );
	}
 }
 
 add_action( 'admin_bar_menu', 'wp_cmkcap_add_admin_bar_menu', 999 );


?>
