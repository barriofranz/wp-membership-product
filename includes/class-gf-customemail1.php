<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A custom Expedited Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class GF_Customemail1 extends WC_Email {


    public function __construct()
    {
        // set ID, this simply needs to be a unique name
        $this->id = 'wc_md_new_order';

        // this is the title in WooCommerce Email settings
        $this->title = 'MD Custom Email';

        // this is the description in WooCommerce email settings
        $this->description = 'MD custom email for now';

        // these are the default heading and subject lines that can be overridden using the settings
        $this->heading = 'MD Custom Email';
        $this->subject = 'MD Custom Email';

        // these define the locations of the templates that this email should use, we'll just use the new order template since this email is similar
        $this->template_html  = 'emails/custom_email_tpl.php';
        $this->template_plain = 'emails/plain/custom_email_tpl.php';

        // Trigger on new paid orders
        // add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'trigger' ) );
        add_action( 'gf_gpm_after_give_product',  array( $this, 'trigger' ) );

        // Call parent constructor to load any other defaults not explicity defined here
        parent::__construct();

        // this sets the recipient to the settings defined below in init_form_fields()
        // $this->recipient = $this->get_option( 'recipient' );

        // if none was entered, just use the WP admin email as a fallback
        if ( ! $this->recipient )
            $this->recipient = get_option( 'admin_email' );
    }

    // public function hook_email_header() {
    //     add_action( 'woocommerce_email_header', $this->mm_email_header, 10, 2 );
    // }
    //
    // function mm_email_header( $email_heading, $email ) {
    // 	echo "<p> Thanks for shopping with us. We appreciate you and your business!</p>";
    // }

    public function trigger( $order_id )
    {

       // bail if no order ID is present
       if ( ! $order_id )
           return;

       // setup order object
       $this->object = new WC_Order( $order_id );

       // bail if shipping method is not expedited
       // if ( ! in_array( $this->object->get_shipping_method(), array( 'Three Day Shipping', 'Next Day Shipping' ) ) )
       //     return;
       // echo '<pre>';print_r(strtotime($this->object->get_date_created()));echo '</pre>';die();
       // replace variables in the subject/headings
       $this->find[] = '{order_date}';
       $this->replace[] = date_i18n( woocommerce_date_format(), strtotime( $this->object->get_date_created() ) );

       $this->find[] = '{order_number}';
       $this->replace[] = $this->object->get_order_number();

       if ( ! $this->is_enabled() || ! $this->get_recipient() )
           return;


		$this->recipient = $this->object->get_billing_email(); // Or $order->get_user_id();

       // woohoo, send the email!
       $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

   }

   public function get_content_html() {
       ob_start();

       $customer_id = $this->object->get_customer_id();
       $customer = new WC_Customer($customer_id);

       $products = [];
       foreach ($this->object->get_items() as $item) {
           $temp = [];
           $product_id = $item->get_product_id();

           $product = new WC_Product($product_id);
           $product_desc = $product->get_description();
           $product_name = $product->get_name();
           $product_url = get_permalink($product_id);
           // $get_downloads = $product->get_downloads();
           // echo '<pre>';print_r($get_downloads);echo '</pre>';die();


           $temp['product_url'] = $product_url;
           $temp['product_desc'] = $product_desc;
           $temp['product_name'] = $product_name;
           // $temp['sku'] = $sku;
           // $temp['regular_price'] = $regular_price;
           // $temp['price'] = $price;
           // $temp['formatted_price'] = ($regular_price > $price) ? wc_format_sale_price($regular_price, $price) : wc_price($price);
           $temp['product_img'] = get_the_post_thumbnail_url($product_id);

           $products[] = $temp;

       }

       // $this->hook_email_header();
       woocommerce_get_template( $this->template_html, array(
           'email' => $customer->get_email(),
           'products' => $products,
           'order'         => $this->object,
           'email_heading' => $this->get_heading()
       ) );
       return ob_get_clean();
   }

   public function get_content_plain() {
        ob_start();
        woocommerce_get_template( $this->template_plain, array(
            'order'         => $this->object,
            'email_heading' => $this->get_heading()
        ) );
        return ob_get_clean();
    }

    public function init_form_fields() {

        $this->form_fields = array(
            'enabled'    => array(
                'title'   => 'Enable/Disable',
                'type'    => 'checkbox',
                'label'   => 'Enable this email notification',
                'default' => 'yes'
            ),
            'recipient'  => array(
                'title'       => 'Recipient(s)',
                'type'        => 'text',
                'description' => sprintf( 'Enter recipients (comma separated) for this email. Defaults to <code>%s</code>.', esc_attr( get_option( 'admin_email' ) ) ),
                'placeholder' => '',
                'default'     => ''
            ),
            'subject'    => array(
                'title'       => 'Subject',
                'type'        => 'text',
                'description' => sprintf( 'This controls the email subject line. Leave blank to use the default subject: <code>%s</code>.', $this->subject ),
                'placeholder' => '',
                'default'     => ''
            ),
            'heading'    => array(
                'title'       => 'Email Heading',
                'type'        => 'text',
                'description' => sprintf( __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading: <code>%s</code>.' ), $this->heading ),
                'placeholder' => '',
                'default'     => ''
            ),
            'email_type' => array(
                'title'       => 'Email type',
                'type'        => 'select',
                'description' => 'Choose which format of email to send.',
                'default'     => 'html',
                'class'       => 'email_type',
                'options'     => array(
                    'plain'     => 'Plain text',
                    'html'      => 'HTML', 'woocommerce',
                    'multipart' => 'Multipart', 'woocommerce',
                )
            )
        );
    }
} // end \WC_Expedited_Order_Email class

function md_email_product_item1_hook($products)
{
    foreach ($products as $product) {

        ?>
            <div style="
                display:block;
                background-color:#d96d0e;
                text-align: center;
                font-family: Helvetica;
                font-size: 20px;
                font-style: normal;
                font-weight: bold;
                line-height: 125%;
                letter-spacing: normal;
                padding: 15px;
                color: #fff;
            ">
                <?= $product['product_name'] ?> Now available for download.
            </div>

            <div style="
                display:block;
                text-align: center;
            ">
                <img
                style="width: 100%;"
                src="<?= $product['product_img'] ?>">
            </div>

            <div style="
                display:block;
                background-color:#404040;
                text-align: center;
                font-family: Helvetica;
                font-size: 16px;
                font-style: normal;
                font-weight: normal;
                line-height: 125%;
                letter-spacing: normal;
                padding: 15px;
                color: #fff;
            ">
                <?= $product['product_desc'] ?>
                <br>
                <br>
                <a href="https://ghostfiregaming.com/my-account"
                    style="
                        background-color: #d96d0e;
                        border: 2px solid #d96d0e;
                        border-radius: 0;
                        color: #fff;
                        display: inline-block;
                        font-size: 14px;
                        font-weight: 700;
                        font-family: Helvetica;
                        letter-spacing: .5px;
                        line-height: 1 !important;
                        min-width: 120px;
                        padding: 1em;
                        text-align: center;
                        text-transform: uppercase;
                        text-decoration: none;
                    "
                    >Link to product
                </a>

            </div>
        <?php

// [product_url] => http://localhost/wpgf1/product/fable-apr-2021/
// [product_desc] => Fable (Apr 2021) sample desc here
// [product_name] => Fable (Apr 2021)
// [product_img] => http://localhost/wpgf1/wp-content/uploads/2021/06/okaycat.jpg
    }
}
add_action('md_email_product_item1', 'md_email_product_item1_hook', 20, 1);
