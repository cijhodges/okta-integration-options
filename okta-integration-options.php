<?php
/*
	* Plugin Name: Okta Integration Options
	* Plugin URI: https://github.com/cijhodges/okta-integration-options
	* Description: Force users to authenticate through Okta Single Sign-On for the entirety or parts of your website.
	* Version: 1.0.0
	* Author: Compassion Web & Interactive
	* Author URI: https://www.compassion.com/
*/

define( 'OKTA_INTEGRATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'OKTA_INTEGRATION_URL', plugin_dir_url( __FILE__ ) );

if ( is_admin() ) {
	require_once 'models/Updater.php';

    $updater = new OktaIntegrationOptionsUpdater( __FILE__ );
    $updater->set_username( 'cijhodges' ); // set username
    $updater->set_repository( 'okta-integration-options' ); // set repo
    $updater->initialize();
}

require_once 'models/Main.php';

global $oktaIntegrationOptions;
$oktaIntegrationOptions = new OktaIntegrationOptions\Main();
