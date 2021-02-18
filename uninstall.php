<?php
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

global $wpdb;

$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}okta_integration_options" );
