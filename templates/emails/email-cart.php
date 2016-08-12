<table cellspacing="0" cellpadding="6" style="width: 100%; border: none" bordercolor="#eee">
	<thead>
		<tr>
			<th scope="col" style="width: 10%; text-align:left; border: 1px solid #eee;"><?php _e( 'Traffic Package', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 15%; text-align:left; border: 1px solid #eee;"><?php _e( 'Targets', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 15%; text-align:left; border: 1px solid #eee;"><?php _e( 'URL', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 15%; text-align:left; border: 1px solid #eee;"><?php _e( 'Add Ons', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 40%; text-align:left; border: 1px solid #eee;"><?php _e( 'Notes', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 15%; text-align:left; border: 1px solid #eee;"><?php _e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php echo $order->email_order_items_table( false, true ); ?>
	</tbody>
	<tfoot>
		<?php
			if ( $totals = $order->get_order_item_totals() ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?><tr>
						<td colspan="4" style=" <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>   border: none;"></td>
						<th scope="row" style="text-align:left;  <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>   border: none;   padding: 0; vertical-align: bottom;"><?php $test  = $total['label']; echo $total['label']; ?></th>
						<td style="text-align:left; <?php if ( $i == 1 ) echo 'border-top-width: 4px;'; ?>   border: none;   padding: 0; vertical-align: bottom;"">
							<?php 
							$value_meta = explode('>',$total['value']);
							if($test = 'Subtotal') {
								echo $value_meta[1];
							}
							
							if($test = 'Discount') {
								echo $value_meta[0].$value_meta[1];
							}
							?>
						</td>
					</tr><?php
				}
			}
		?>
	</tfoot>
</table>