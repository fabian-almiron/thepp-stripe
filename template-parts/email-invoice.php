<?php
/**
 * Invoice Email Template
 *
 * This template is used to send an invoice email to users after purchase.
 * Placeholders: $user_first_name, $blogname, $order_items (HTML string), $order_total (string, e.g., $10.00),
 * $shipping_info (string), $billing_info (string), $order_date (string), $order_id (string),
 * $is_subscription (bool), $subscription_product_name (string), $subscription_price_details (string, e.g., $10.00 / month),
 * $trial_info (string, e.g., Your free trial ends on: Date.), $renewal_info (string, e.g., Your subscription renews on: Date.)
 */

if (!defined('ABSPATH')) exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your <?php echo $is_subscription ? 'Subscription Invoice' : 'Order Invoice'; ?> from <?php echo esc_html($blogname); ?></title>
    <style>
        body {
            background: #f6f5f3;
            font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 700px;
            margin: 40px auto;
            background: #fff;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border: 1px solid #e5e1dc;
            overflow: hidden;
        }
        .email-header {
            background: #ede4de;
            padding: 32px 40px 24px 40px;
            text-align: left;
        }
        .email-header h1 {
            margin: 0;
            font-size: 2rem;
            font-weight: 400;
            color: #3c434a;
        }
        .email-body {
            padding: 40px;
            color: #222;
            font-size: 1rem;
        }
        .email-footer {
            text-align: center;
            color: #888;
            font-size: 0.95rem;
            padding: 16px 0 8px 0;
        }
        .logo {
            display: block;
            margin: 40px auto 24px auto;
            max-width: 350px;
            width: 80%;
            height: auto;
        }
        .invoice-table, .subscription-details-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        .invoice-table th, .invoice-table td, .subscription-details-table th, .subscription-details-table td {
            border: 1px solid #e5e1dc;
            padding: 10px 12px;
            text-align: left;
        }
        .invoice-table th, .subscription-details-table th {
            background: #f6f5f3;
        }
        .invoice-total, .subscription-total {
            font-weight: bold;
            font-size: 1.1rem;
            text-align: right;
            padding-top: 12px;
        }
        .info-block {
            margin-bottom: 18px;
        }
        .subscription-summary p {
            margin: 5px 0;
        }
        a {
            color: #1a0dab;
        }
    </style>
</head>
<body>
    <img src="https://thepipedpeony.com/wp-content/uploads/2023/03/piped-peony-logo-2048x452.png" alt="<?php echo esc_html($blogname); ?> Logo" class="logo">
    <div class="email-container">
        <div class="email-header">
            <h1>Your <?php echo $is_subscription ? 'Subscription Invoice' : 'Invoice'; ?> from <?php echo esc_html($blogname); ?></h1>
        </div>
        <div class="email-body">
            <p>Hi <?php echo esc_html($user_first_name); ?>,</p>
            
            <?php if ($is_subscription): ?>
                <p>Thank you for subscribing! Here are the details of your subscription:</p>
                <div class="subscription-summary info-block">
                    <?php if (!empty($trial_info)): ?>
                        <p><strong>Trial Information:</strong> <?php echo esc_html($trial_info); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($renewal_info)): ?>
                         <p><strong>Renewal Information:</strong> <?php echo esc_html($renewal_info); ?></p>
                    <?php endif; ?>
                </div>
                <p>Amount paid at signup: <?php echo esc_html($order_total); ?></p>
            <?php else: ?>
                <p>Thank you for your purchase! Here is your invoice for your recent order.</p>
            <?php endif; ?>

            <div class="info-block">
                <strong>Order Date:</strong> <?php echo esc_html($order_date); ?><br>
                
            </div>

            <?php if (!$is_subscription && !empty($order_items)): ?>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $order_items; // This is pre-formatted HTML rows for non-subscriptions ?>
                    </tbody>
                </table>
                <div class="invoice-total">
                    Total: <?php echo esc_html($order_total); ?>
                </div>
            <?php elseif ($is_subscription && !empty($order_items)): // For subscription, order_items is a single pre-formatted row from send_invoice_email ?>
                 <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Quantity</th>
                            <th>Price / Interval</th>
                            <th>Amount Paid Today</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php echo $order_items; // This is the single formatted row for the subscription ?>
                    </tbody>
                </table>
            <?php endif; ?>
            
            <?php if (!empty(trim(strip_tags($shipping_info))) && trim(strip_tags($shipping_info)) !== 'No shipping required or provided.') : ?>
            <div class="info-block">
                <strong>Shipping Information:</strong><br>
                <?php echo nl2br(esc_html($shipping_info)); ?>
            </div>
            <?php endif; ?>

            <?php if (!empty(trim(strip_tags($billing_info)))) : ?>
            <div class="info-block">
                <strong>Billing Information:</strong><br>
                <?php echo nl2br(esc_html($billing_info)); ?>
            </div>
            <?php endif; ?>
            <p>You can manage your account and subscriptions here: <a href="<?php echo esc_url(home_url('/my-account/')); ?>"><?php echo esc_url(home_url('/my-account/')); ?></a></p>
            <p>If you have any questions, please reply to this email or contact us at <a href="mailto:support@thepipedpeony.com">support@thepipedpeony.com</a>.</p>
            <p>We appreciate your business!</p>

            <p>Join our community of bakers on our Facebook page! <a href="https://www.facebook.com/groups/359977912181048">HERE</a></p>

        </div>
        <div class="email-footer">
            &copy; <?php echo date('Y'); ?> <?php echo esc_html($blogname); ?>. All rights reserved.
        </div>
    </div>
</body>
</html> 