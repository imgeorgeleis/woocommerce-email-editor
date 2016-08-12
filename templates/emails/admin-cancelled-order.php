<?php
/**
 * Admin cancelled order email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/admin-cancelled-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you (the theme developer).
 * will need to copy the new files to your theme to maintain compatibility. We try to do this.
 * as little as possible, but it does happen. When this occurs the version of the template file will.
 * be bumped and the readme will list any important changes.
 *
 * @see 	    http://docs.woothemes.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates/Emails
 * @version 2.5.0
 *
 * @Edited by George on 06.20.16
 *
 */

 if ( ! defined( 'ABSPATH' ) ) {
 	exit;
 }

do_action( 'woocommerce_email_header', $email_heading );

echo $email_contents;

do_action( 'woocommerce_email_before_order_table', $order, true, false );
do_action( 'woocommerce_email_after_order_table', $order, true, false );
do_action( 'woocommerce_email_order_meta', $order, true, false );
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text );
do_action( 'woocommerce_email_footer' );