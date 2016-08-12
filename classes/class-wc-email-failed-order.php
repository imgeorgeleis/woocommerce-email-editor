<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WC_Email_Editor_Failed_Order' ) ) :

/**
 * Failed Order Email
 *
 * An email sent to the admin when payment fails to go through.
 *
 * @class       WC_Email_Failed_Order
 * @version     2.5.0
 * @package     WooCommerce/Classes/Emails
 * @author      WooThemes
 * @extends     WC_Email
 */
class WC_Email_Editor_Failed_Order extends WC_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id               = 'failed_order';
		$this->title            = __( 'Failed order', 'woocommerce' );
		$this->description      = __( 'Failed order emails are sent to chosen recipient(s) when orders have been marked failed (if they were previously processing or on-hold).', 'woocommerce' );
		$this->heading          = __( 'Failed order', 'woocommerce' );
		$this->subject          = __( '[{site_title}] Failed order ({order_number})', 'woocommerce' );
		
		$this->template_cart	= 'emails/email-cart.php';
		$this->template_addresses = 'emails/email-addresses.php';
		$this->template_html    = 'emails/admin-failed-order.php';
		$this->template_plain   = 'emails/plain/admin-failed-order.php';

		// Triggers for this email
		add_action( 'woocommerce_order_status_pending_to_failed_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_on-hold_to_failed_notification', array( $this, 'trigger' ) );

		// Call parent constructor
		parent::__construct();

		// Other settings
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Trigger.
	 *
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {
		if ( $order_id ) {
            $this->object	= wc_get_order( $order_id );
			$user			= $this->object->get_user();
			
			// Find/replace
			$this->find['site-url']				= '{site_url}';
			$this->find['order-date']			= '{order_date}';
			$this->find['order-number']			= '{order_number}';
			$this->find['order-url']			= '{order_url}';
			$this->find['order-edit-url']		= '{order_edit_url}';
			$this->find['account-url']			= '{account_url}';
			$this->find['client-name']			= '{client_name}';
			$this->find['client-first-name']	= '{client_first_name}';
			$this->find['client-last-name']		= '{client_last_name}';
			$this->find['client-email']			= '{client_email}';
			$this->find['client-tel']			= '{client_tel}';
			$this->find['billing-first-name']	= '{billing_first_name}';
			$this->find['billing-last-name']	= '{billing_last_name}';
			$this->find['addresses']			= '{addresses}';
			$this->find['cart']					= '{cart}';
			
			$this->replace['site-url']			= site_url('/');
			$this->replace['order-date']		= date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			$this->replace['order-number']		= $this->object->get_order_number();
			$this->replace['order-url']			= get_permalink($this->object->id);
			$this->replace['order-edit-url']	= get_edit_post_link($this->object->id);
			$this->replace['account-url']		= get_permalink( get_option('woocommerce_myaccount_page_id') );
			$this->replace['client-name']		= $user->data->display_name;
			$this->replace['client-first-name']	= get_user_meta($user->data->ID, 'first_name', true);
			$this->replace['client-last-name']	= get_user_meta($user->data->ID, 'last_name', true);
			$this->replace['client-email']		= $user->data->user_email;
			$this->replace['client-tel']		= get_user_meta($user->data->ID, 'billing_phone', true);
			$this->replace['billing-first-name']= $this->object->billing_first_name;
			$this->replace['billing-last-name']	= $this->object->billing_last_name;
			$this->replace['addresses']			= wc_get_template_html($this->template_addresses, array(
				'order'	=> $this->object,
				'email'	=> $this,
			));
			$this->replace['cart']				= wc_get_template_html($this->template_cart, array(
				'order'	=> $this->object,
				'email'	=> $this,
			));
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Get content html.
	 *
	 * @access public
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => false,
			'email'			=> $this,
			'email_contents'=> $this->format_string(
				apply_filters(
					'the_content',
					$this->get_option( 'email_contents', __( 'Payment for order #{order_number} from {billing_first_name} {billing_last_name} has failed. The order was as follows:', 'woocommerce' ) )
				)
			)
		) );
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => true,
			'plain_text'    => true,
			'email'			=> $this
		) );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'         => __( 'Enable/Disable', 'woocommerce' ),
				'type'          => 'checkbox',
				'label'         => __( 'Enable this email notification', 'woocommerce' ),
				'default'       => 'yes'
			),
			'recipient' => array(
				'title'         => __( 'Recipient(s)', 'woocommerce' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', 'woocommerce' ), esc_attr( get_option('admin_email') ) ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true
			),
			'subject' => array(
				'title'         => __( 'Subject', 'woocommerce' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', 'woocommerce' ), $this->subject ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true
			),
			'heading' => array(
				'title'         => __( 'Email Heading', 'woocommerce' ),
				'type'          => 'text',
				'description'   => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.', 'woocommerce' ), $this->heading ),
				'placeholder'   => '',
				'default'       => '',
				'desc_tip'      => true
			),
			'email_type' => array(
				'title'         => __( 'Email type', 'woocommerce' ),
				'type'          => 'select',
				'description'   => __( 'Choose which format of email to send.', 'woocommerce' ),
				'default'       => 'html',
				'class'         => 'email_type wc-enhanced-select',
				'options'       => $this->get_email_type_options(),
				'desc_tip'      => true
			),
			'email_contents' => array(
				'title'			=> __( 'Email Contents', 'woocommerce' ),
				'type'			=> 'wp_editor',
				'description'	=> sprintf( __('This controls the main content of the email. You can use shortcodes from the %s', 'woocommerce'), $this->get_shortcodelist()),
				'placeholder'	=> '',
				'class'			=> 'email_content wp-editor',
				'desc_tip'		=> false
			)
		);
	}
	
	/**
	 * Generates the list of all available shortcode
	 */
	public function get_shortcodelist( $list = '' ){
		// Find/replace
		$shortcodes = array(
			'{site_url}' => __('Displays website URL', 'woothemes'),
			'{order_date}' => sprintf(__('Displays the order date in %s format', 'woothemes'), date_i18n( wc_date_format(), strtotime(date('c')) )),
			'{order_number}' => __('Displays the order nummber', 'woothemes'),
			'{order_url}' => __('Displays the order URL', 'woothemes'),
			'{order_edit_url}' => __('Displays the order edit URL (WordPress Back Office URL)', 'woothemes'),
			'{account_url}' => __('Displays the My Account page URL', 'woothemes'),
			'{client_name}' => __('Displays the Customer Registered Name', 'woothemes'),
			'{client_first_name}' => __('Displays the Customer Registered First Name', 'woothemes'),
			'{client_last_name}' => __('Displays the Customer Registered Last Name', 'woothemes'),
			'{client_email}' => __('Displays the Customer Registered Email', 'woothemes'),
			'{client_tel}' => __('Displays the Customer Registered Contact Number', 'woothemes'),
			'{payment_url}' => __('Displays the payment URL', 'woothemes'),
			'{payment_method}' => __('Displays the payment method (example: paypal)', 'woothemes'),
			'{billing_first_name}' => __('Displays the customer\'s billing first name', 'woothemes'),
			'{billing_last_name}' => __('Displays the customer\'s billing last name', 'woothemes'),
			'{addresses}' => __('Displays the customer\'s billing address', 'woothemes'),
			'{cart}' => __('Displays the tabulated order details', 'woothemes')
		);
		
		foreach( $shortcodes as $code=>$desc ){
			$list .= sprintf(' <span title="%s" style="display: inline-block;background: #cccccc;padding: 0 5px;font-size: 12px;font-weight: bold;font-style: normal;color: #000;margin-right: 3px;">%s</span>', $desc, $code);
		}
		
		return $list;
	}
	
	/**
	 * Generate Textarea HTML with WP Editor
	 *
	 * @param  mixed $key
	 * @param  mixed $data
	 * @since  1.0.0
	 * @return string
	 */
	public function generate_wp_editor_html( $key, $data ) {

		$field    = $this->get_field_key( $key );
		$defaults = array(
			'title'             => '',
			'disabled'          => false,
			'class'             => '',
			'css'               => '',
			'placeholder'       => '',
			'type'              => 'text',
			'desc_tip'          => false,
			'description'       => '',
			'custom_attributes' => array()
		);

		$data = wp_parse_args( $data, $defaults );

		ob_start();
		?>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
				<?php echo $this->get_tooltip_html( $data ); ?>
			</th>
			<td class="forminp">
				<fieldset>
					<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend><?php
					
					wp_editor(
						$this->get_option( $key ),					// Content
						esc_attr( $field ),							// ID Attribute
						array(										// Settings
							'wpautop' => true,
							'media_buttons' => false,
							'textarea_name' => esc_attr( $field )
						)
					);
					
					/*?><textarea rows="3" cols="20" class="input-text wide-input <?php echo esc_attr( $data['class'] ); ?>" type="<?php echo esc_attr( $data['type'] ); ?>" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" placeholder="<?php echo esc_attr( $data['placeholder'] ); ?>" <?php disabled( $data['disabled'], true ); ?> <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo esc_textarea( $this->get_option( $key ) ); ?></textarea><?php*/
					
					echo $this->get_description_html( $data ); ?>
				</fieldset>
			</td>
		</tr>
		<?php

		return ob_get_clean();
	}
	
	/**
	 * Validate Textarea with WP Editor Field.
	 *
	 * Make sure the data is escaped correctly, etc.
	 *
	 * @param  mixed $key
	 * @since  1.0.0
	 * @return string
	 */
	public function validate_wp_editor_field( $key ) {

		$text  = $this->get_option( $key );
		$field = $this->get_field_key( $key );

		if ( isset( $_POST[ $field ] ) ) {

			$text = wp_kses( trim( stripslashes( $_POST[ $field ] ) ),
				array_merge(
					array(
						'iframe' => array( 'src' => true, 'style' => true, 'id' => true, 'class' => true )
					),
					wp_kses_allowed_html( 'post' )
				)
			);
		}

		return $text;
	}
}

endif;