<?php
/**
 * Email Order Items
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     2.1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$item_loop = 0;
$item_count = count($items);

foreach ( $items as $item_id => $item ) :
	$_product     = apply_filters( 'woocommerce_order_item_product', $order->get_product_from_item( $item ), $item );
	$item_meta    = new WC_Order_Item_Meta( $item['item_meta'], $_product );

	if ( apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {

	
		$the_meta_args = get_post_meta($order->id, 'my_cart_meta', true);
					
		if($the_meta_args) {
			$arg_pa_more = explode ('|',$the_meta_args);
		$final_args = explode ('}',$arg_pa_more[($item_loop+1)]);
			
			if($final_args[4] != 'N/A') {
				$add_ons = explode('+', $final_args[4]);
				$arry_len = count($add_ons)-1;
				$the_final_add_ons = array_slice($add_ons,0, $arry_len);
		
				$final_add_ons = implode('<br/>',$the_final_add_ons);
				$price_prod = floatval (get_post_meta($_product->id, '_price', true));
				
				foreach ($the_final_add_ons as $add_on) {
					$add_on_price = explode('$',$add_on);
					$add_ons_price = $price_prod + floatval ($add_on_price[1]);
					$price_prod = $add_ons_price;
				}
				
				$product_price = $add_ons_price;
				
			} else {
				$final_add_ons = '';
				$product_price = floatval (get_post_meta($_product->id, '_price', true));;
			}
			
			$targets = 'Country: '.$final_args[1].'<br/>Category: '.$final_args[2];
			$URL = $final_args[3];
			$AddOns = $final_add_ons;
			$notes = $final_args[5];
			
			
		} else {
			$targets = 'N/A';
			$URL = 'N/A';
			$AddOns = 'N/A';
			$notes = 'N/A';
		}
		
		
		
		
		$cart_items = $item_count;
		//echo var_dump($subtotal_num);
		if( $order->get_used_coupons() ) {
			foreach( $order->get_used_coupons() as $coupon) {
				
				$args = array("post_type" => "shop_coupon", "s" => $coupon);
				
				$the_query = new WP_Query( $args );
				if($the_query->have_posts()) {
					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						
						$amt = get_post_meta(get_the_ID(), 'coupon_amount', true);
					
						$disc_type = get_post_meta(get_the_ID(), 'discount_type', true);
						
						if($disc_type == 'recurring_percent') {
							$current_price = $product_price;
							$percentage = floatval($amt)/100;
							$discount = floatval($percentage)*floatval($amt_in_cart);
							$the_discount_part = floatval($discount)/floatval($cart_items);
							$product_price = floatval($current_price) - floatval($the_discount_part);
							
						} else {
							$current_price = $product_price;
							$the_discount = floatval($amt_in_cart)-floatval($amt);
							$the_discount_part = floatval($discount)/floatval($cart_items);
							$product_price = floatval($current_price) - floatval($the_discount_part);
						}
						
						
		
					}
				}
				
			}
		}
		
		?>
		<tr>
			<td style="  vertical-align: top !important; text-align:left; vertical-align:middle; border: 1px solid #eee; word-wrap:break-word;"><?php

				// Show title/image etc
				if ( $show_image ) {
					echo apply_filters( 'woocommerce_order_item_thumbnail', '<img src="' . ( $_product->get_image_id() ? current( wp_get_attachment_image_src( $_product->get_image_id(), 'thumbnail') ) : wc_placeholder_img_src() ) .'" alt="' . __( 'Product Image', 'woocommerce' ) . '" height="' . esc_attr( $image_size[1] ) . '" width="' . esc_attr( $image_size[0] ) . '" style="vertical-align:middle; margin-right: 10px;" />', $item );
				}

				// Product name
				echo apply_filters( 'woocommerce_order_item_name', $item['name'], $item );

				// SKU
				if ( $show_sku && is_object( $_product ) && $_product->get_sku() ) {
					echo ' (' . $_product->get_sku() . ')';
				}

				// allow other plugins to add additional product information here
				do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order );

				// Variation
				// if ( $item_meta->meta ) {
					// echo '<br/><small>' . nl2br( $item_meta->display( true, true, '_', "\n" ) ) . '</small>';
				// }

				// File URLs
				if ( $show_download_links && is_object( $_product ) && $_product->exists() && $_product->is_downloadable() ) {

					$download_files = $order->get_item_downloads( $item );
					$i              = 0;

					foreach ( $download_files as $download_id => $file ) {
						$i++;

						if ( count( $download_files ) > 1 ) {
							$prefix = sprintf( __( 'Download %d', 'woocommerce' ), $i );
						} elseif ( $i == 1 ) {
							$prefix = __( 'Download', 'woocommerce' );
						}

						echo '<br/><small>' . $prefix . ': <a href="' . esc_url( $file['download_url'] ) . '" target="_blank">' . esc_html( $file['name'] ) . '</a></small>';
					}
				}

				// allow other plugins to add additional product information here
				do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order );

			?></td>
			<td style="  vertical-align: top !important;text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $targets; ?></td>
			<td style="  vertical-align: top !important;text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $URL ?></td>
			<td style="  vertical-align: top !important;text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo $AddOns ?></td>
			<td style="  vertical-align: top !important;text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo nl2br(stripslashes(urldecode($notes))) ?></td>
			<td style="  vertical-align: top !important;text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo '$'.number_format($product_price,2); ?></td>
		</tr>
		<?php
	}

	if ( $show_purchase_note && is_object( $_product ) && ( $purchase_note = get_post_meta( $_product->id, '_purchase_note', true ) ) ) : ?>
		<tr>
			<td colspan="3" style="text-align:left; vertical-align:middle; border: 1px solid #eee;"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
		</tr>
	<?php endif; ?>

<?php $item_loop++; endforeach; ?>
