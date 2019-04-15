<?php
/**
 * Plugin Name: CH Product Upload
 * Plugin URI: https://crowdyhouse.com
 * Description: A Custom Product uploader for Woocomerce, WC Vendors and Crowdyhouse.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

// Define WC_PLUGIN_FILE.
if ( ! defined( 'CH_UPLOAD_PLUGIN_FILE' ) ) {
    define( 'CH_UPLOAD_PLUGIN_FILE', __FILE__ );
}

// Include the main WooCommerce class.
if ( ! class_exists( 'CH_Upload' ) ) {
    include_once dirname( __FILE__ ) . '/includes/class-ch-upload.php';
}

// Global for backwards compatibility.
$GLOBALS['ch_upload'] = new CH_Upload;