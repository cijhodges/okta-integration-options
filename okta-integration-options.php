<?php
/*
	* Plugin Name: Okta Integration Options
	* Plugin URI: https://github.com/cijhodges/okta-integration-options
	* Description: Force users to authenticate through Okta Single Sign-On for the entirety or parts of your website.
	* Version: 0.0.1
	* Author: Compassion Web & Interactive
	* Author URI: https://www.compassion.com/
*/

define( 'OKTA_INTEGRATION_PATH', plugin_dir_path( __FILE__ ) );
define( 'OKTA_INTEGRATION_URL', plugin_dir_url( __FILE__ ) );
require_once 'models/Main.php';

global $oktaIntegrationOptions;
$oktaIntegrationOptions = new OktaIntegrationOptions\Main();
