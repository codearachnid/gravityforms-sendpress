<?php
/*
Plugin Name: Gravity Forms + SendPress Email Signup
Plugin URI:
Description: Signup Integration for Gravity Forms + SendPress
Author: Timothy Wood @codearachnid
Version: 1.0.0
Author URI:
*/

if ( ! defined( 'ABSPATH' ) && ! class_exists( 'GFForms' ) ) {
	die();
}

require_once 'field.actions.php';
require_once 'field.class.php';
