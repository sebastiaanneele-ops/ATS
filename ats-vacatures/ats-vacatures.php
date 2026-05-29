<?php
/**
 * Plugin Name:       ATS Vacatures
 * Description:        Toont vacatures uit het ATS (Applicant Tracking System) op je WordPress-site en stuurt sollicitaties door.
 * Version:           0.1.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Personeel Partners
 * Text Domain:       ats-vacatures
 *
 * @package ATS_Vacatures
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Geen directe toegang.
}

define( 'ATS_VACATURES_VERSION', '0.1.0' );
define( 'ATS_VACATURES_DIR', plugin_dir_path( __FILE__ ) );
define( 'ATS_VACATURES_URL', plugin_dir_url( __FILE__ ) );

require_once ATS_VACATURES_DIR . 'includes/class-ats-settings.php';
require_once ATS_VACATURES_DIR . 'includes/class-ats-api-client.php';
require_once ATS_VACATURES_DIR . 'includes/class-ats-shortcodes.php';
require_once ATS_VACATURES_DIR . 'includes/class-ats-rest-proxy.php';

/**
 * Initialiseer de plugin.
 */
function ats_vacatures_init() {
	new ATS_Vacatures_Settings();
	new ATS_Vacatures_Shortcodes();
	new ATS_Vacatures_Rest_Proxy();
}
add_action( 'plugins_loaded', 'ats_vacatures_init' );
