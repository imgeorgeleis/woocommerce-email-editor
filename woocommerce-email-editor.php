<?php
/*
 * Plugin Name: WooCommerce Email Editor
 * Plugin URI: http://www.woothemes.com/
 * Description: Allows you to edit WooCommerce emails via WYSIWYG Editor 
 * Version: 0.1
 * Author: WooThemes
 * Author URI: http://iamgeorgeleis.com
 * Requires at least: 3.8
 * Tested up to: 4.2
 * WC tested up to: 2.4
	Copyright: © 2009-2015 WooThemes.
	License: GNU General Public License v3.0
	License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Required functions
 */
if ( ! function_exists( 'woothemes_queue_update' ) ) {
	require_once( 'woo-includes/woo-functions.php' );
}

/**
 * Plugin updates
 *
 * woothemes_queue_update( plugin_basename( __FILE__ ), '147d0077e591e16db9d0d67daeb8c484', '18618' );
 * Disabled until first release
 */

if ( is_woocommerce_active() ) {
	/**
	 * Main class
	 */
	class WC_Email_Editor {

		/**
		 * Constructor
		 */
		public function __construct() {
			if ( is_admin() ) {
				// Include admin classes and hook
			}

			// Include frontend classess and hook
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
			add_action( 'woocommerce_loaded', array( $this, 'load') );
			add_action( 'woocommerce_email', array( $this, 'woocommerce_email_loaded' ) );
			add_filter( 'woocommerce_email_classes', array( $this, 'woocommerce_email_classes') );
		}

		/**
		 * Localisation
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'woocommerce-email-editor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * WP Init
		 */
		public function init(){}
		
		/**
		 * WooCommerce Loaded
		 */
		public function load(){}
		
		/**
		 * WooCommerce Email Loaded
		 */
		public function woocommerce_email_loaded( $object ){
			remove_action( 'woocommerce_email_order_details', array( $object, 'order_details' ), 10 );
			remove_action( 'woocommerce_email_order_details', array( $object, 'order_schema_markup' ), 20 );
			remove_action( 'woocommerce_email_order_meta', array( $object, 'order_meta' ), 10 );
			remove_action( 'woocommerce_email_customer_details', array( $object, 'customer_details' ), 10 );
			remove_action( 'woocommerce_email_customer_details', array( $object, 'email_addresses' ), 20 );
		}

		/**
		 * Overrides the default email settings and template
		 */
		public function woocommerce_email_classes( $email_classes ){
			// include our custom email classes
			include("classes/class-wc-email-cancelled-order.php");
			include("classes/class-wc-email-customer-completed-order.php");
			include("classes/class-wc-email-customer-invoice.php");
			include("classes/class-wc-email-customer-new-account.php");
			include("classes/class-wc-email-customer-note.php");
			include("classes/class-wc-email-customer-on-hold-order.php");
			include("classes/class-wc-email-customer-processing-order.php");
			include("classes/class-wc-email-customer-refunded-order.php");
			include("classes/class-wc-email-customer-reset-password.php");
			include("classes/class-wc-email-failed-order.php");
			include("classes/class-wc-email-new-order.php");
			
			// Prevent Redundancy
			remove_action( 'woocommerce_order_status_pending_to_cancelled_notification', array( $email_classes['WC_Email_Cancelled_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_on-hold_to_cancelled_notification', array( $email_classes['WC_Email_Cancelled_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_status_completed_notification', array( $email_classes['WC_Email_Customer_Completed_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_new_customer_note_notification', array( $email_classes['WC_Email_Customer_Note'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_classes['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_classes['WC_Email_Customer_On_Hold_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_classes['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_classes['WC_Email_Customer_Processing_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_fully_refunded_notification', array( $email_classes['WC_Email_Customer_Refunded_Order'], 'trigger_full' ), 10, 2 );
			remove_action( 'woocommerce_order_partially_refunded_notification', array( $email_classes['WC_Email_Customer_Refunded_Order'], 'trigger_partial' ), 10, 2 );
			
			remove_action( 'woocommerce_reset_password_notification', array( $email_classes['WC_Email_Customer_Reset_Password'], 'trigger' ), 10, 2 );
			
			remove_action( 'woocommerce_order_status_pending_to_failed_notification', array( $email_classes['WC_Email_Failed_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_on-hold_to_failed_notification', array( $email_classes['WC_Email_Failed_Order'], 'trigger' ) );
			
			remove_action( 'woocommerce_order_status_pending_to_processing_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_completed_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_pending_to_on-hold_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_processing_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_completed_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			remove_action( 'woocommerce_order_status_failed_to_on-hold_notification', array( $email_classes['WC_Email_New_Order'], 'trigger' ) );
			
			// add the email class to the list of email classes that WooCommerce loads
			$email_classes['WC_Email_Cancelled_Order'] = new WC_Email_Editor_Cancelled_Order();
			$email_classes['WC_Email_Customer_Completed_Order'] = new WC_Email_Editor_Customer_Completed_Order();
			$email_classes['WC_Email_Customer_Invoice'] = new WC_Email_Editor_Customer_Invoice();
			$email_classes['WC_Email_Customer_New_Account'] = new WC_Email_Editor_Customer_New_Account();
			$email_classes['WC_Email_Customer_Note'] = new WC_Email_Editor_Customer_Note();
			$email_classes['WC_Email_Customer_On_Hold_Order'] = new WC_Email_Editor_Customer_On_Hold_Order();
			$email_classes['WC_Email_Customer_Processing_Order'] = new WC_Email_Editor_Customer_Processing_Order();
			$email_classes['WC_Email_Customer_Refunded_Order'] = new WC_Email_Editor_Customer_Refunded_Order();
			$email_classes['WC_Email_Customer_Reset_Password'] = new WC_Email_Editor_Customer_Reset_Password();
			$email_classes['WC_Email_Failed_Order'] = new WC_Email_Editor_Failed_Order();
			$email_classes['WC_Email_New_Order'] = new WC_Email_Editor_New_Order();
			
			/*
			 * Custom Email
			$email_classes['WC_Email_Cancelled_Order'] = null;
			
			* Subscription add-on
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			$email_classes['WC_Email_Cancelled_Order'] = null;
			*/
			
			return $email_classes;
		}
	}

	new WC_Email_Editor();
}