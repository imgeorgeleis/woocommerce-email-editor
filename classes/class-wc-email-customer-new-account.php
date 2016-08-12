<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Email_Editor_Customer_New_Account' ) ) :

/**
 * Customer New Account.
 *
 * An email sent to the customer when they create an account.
 *
 * @class       WC_Email_Customer_New_Account
 * @version     2.3.0
 * @package     WooCommerce/Classes/Emails
 * @author      WooThemes
 * @extends     WC_Email
 */
class WC_Email_Editor_Customer_New_Account extends WC_Email {

	/**
	 * User login name.
	 *
	 * @var string
	 */
	public $user_login;

	/**
	 * User email.
	 *
	 * @var string
	 */
	public $user_email;

	/**
	 * User password.
	 *
	 * @var string
	 */
	public $user_pass;

	/**
	 * Is the password generated?
	 *
	 * @var bool
	 */
	public $password_generated;

	/**
	 * Constructor.
	 */
	function __construct() {

		$this->id             = 'customer_new_account';
		$this->customer_email = true;
		$this->title          = __( 'New account', 'woocommerce' );
		$this->description    = __( 'Customer "new account" emails are sent to the customer when a customer signs up via checkout or account pages.', 'woocommerce' );
		
		$this->template_cart	= 'emails/email-cart.php';
		$this->template_addresses = 'emails/email-addresses.php';
		$this->template_html  = 'emails/customer-new-account.php';
		$this->template_plain = 'emails/plain/customer-new-account.php';

		$this->subject        = __( 'Your account on {site_title}', 'woocommerce');
		$this->heading        = __( 'Welcome to {site_title}', 'woocommerce');

		// Call parent constuctor
		parent::__construct();
	}

	/**
	 * Trigger.
	 *
	 * @param int $user_id
	 * @param string $user_pass
	 * @param bool $password_generated
	 */
	function trigger( $user_id, $user_pass = '', $password_generated = false ) {

		if ( $user_id ) {
			$this->object             = new WP_User( $user_id );

			$this->user_pass          = $user_pass;
			$this->user_login         = stripslashes( $this->object->user_login );
			$this->user_email         = stripslashes( $this->object->user_email );
			$this->recipient          = $this->user_email;
			$this->password_generated = $password_generated;
			
			// Find/replace
			$this->find['site-url']				= '{site_url}';
			$this->find['user-login']			= '{user_login}';
			$this->find['user-pass']			= '{user_pass}';
			$this->find['password-generated']	= '{password_generated}';
			$this->find['client-name']			= '{client_name}';
			$this->find['client-first-name']	= '{client_first_name}';
			$this->find['client-last-name']		= '{client_last_name}';
			$this->find['client-email']			= '{client_email}';
			
			$this->replace['site-url']				= site_url('/');
			$this->replace['user-login']			= $this->user_login;
			$this->replace['user-pass']				= $this->user_pass;
			$this->replace['password-generated']	= $this->password_generated;
			$this->replace['client-name']			= $this->object->display_name;
			$this->replace['client-first-name']		= get_user_meta($this->object->ID, 'first_name', true);
			$this->replace['client-last-name']		= get_user_meta($this->object->ID, 'last_name', true);
			$this->replace['client-email']			= $this->user_email;
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
	function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'email_heading'      => $this->get_heading(),
			'user_login'         => $this->user_login,
			'user_pass'          => $this->user_pass,
			'blogname'           => $this->get_blogname(),
			'password_generated' => $this->password_generated,
			'sent_to_admin'      => false,
			'plain_text'         => false,
			'email'				 => $this,
			'email_contents'	 => $this->format_string(
				apply_filters(
					'the_content',
					$this->get_option(
						'email_contents',
						sprintf(
							'<p>%s</p>%s<p>%s</p>',
							__( "Thanks for creating an account on {site_title}. Your username is <strong>{user_login}</strong>.", 'woocommerce' ),
							( 'yes' === get_option( 'woocommerce_registration_generate_password' ) && $this->password_generated ) ? __( "Your password has been automatically generated: <strong>{user_pass}</strong>", 'woocommerce') : '',
							sprintf( __( 'You can access your account area to view your orders and change your password here: %s.', 'woocommerce' ), wc_get_page_permalink( 'myaccount' ) )
						)
					)
				)
			)
		) );
	}

	/**
	 * Get content plain.
	 *
	 * @access public
	 * @return string
	 */
	function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'email_heading'      => $this->get_heading(),
			'user_login'         => $this->user_login,
			'user_pass'          => $this->user_pass,
			'blogname'           => $this->get_blogname(),
			'password_generated' => $this->password_generated,
			'sent_to_admin'      => false,
			'plain_text'         => true,
			'email'			     => $this
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