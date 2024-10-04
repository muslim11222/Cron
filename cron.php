<?php 

/**
 * Plugin name: Wp Cron
 * Description: This plugin is responsible for generating a scheduled event to send an email.
 * Author: Muslim khan
 * Version: 1.0.0
 */

class cron_wordpress {
     public function __construct() {
          add_action('woocommerce_order_status_changed', array($this, 'order_status_changed'), 10, 4);
          add_action('cron_customer_email', array($this, 'cron_send_customer_email_callback'), 10, 1);


          add_action('phpmailer_init', array($this, 'mailtrap'));
     }

     public function order_status_changed( $order_id, $status_from, $status_to, $order ) {
          if ('processing' !== $status_to) {
               return;
          }

          //Save the status
          $meta_data = $order->get_meta( 'cron_customer_email_meta' );

          if ( !empty( $meta_data )) {
               return;
          }

          $order->update_meta_data( 'cron_customer_email_meta', 'pending' );
          $order->save();




          wp_schedule_single_event(
               time() + (60 * 60 * 24),
               'cron_customer_email',
               array($order_id)
          );
     }

     public function cron_send_customer_email_callback( $order_id ) {

          $order = wc_get_order( $order_id );

          $user = get_user_by( 'id', $order->get_customer_id());

          $body = 'We have a new customer';

         wp_mail($user->user_email, 'thanks for your order', $body);
     }


     public function mailtrap($phpmailer) {
          $phpmailer->isSMTP();
          $phpmailer->Host = 'sandbox.smtp.mailtrap.io';
          $phpmailer->SMTPAuth = true;
          $phpmailer->Port = 2525;
          $phpmailer->Username = '28fb0d0d7fd2e9';
          $phpmailer->Password = '8650a8ab504feb';
     }
}
new cron_wordpress();
