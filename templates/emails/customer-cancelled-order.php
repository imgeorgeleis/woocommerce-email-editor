<?php
/**
 * Customer processing order email
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/*
 * Added by George
 * 06/09/16
 */
do_action('woocommerce_email_header', $email_heading);

echo $email_contents;

do_action( 'woocommerce_email_footer' );