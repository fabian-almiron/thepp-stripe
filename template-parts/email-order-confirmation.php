<?php
/**
 * Order Confirmation Email Template
 *
 * This template is used to send an order confirmation email to users after a one-time purchase.
 * Placeholders: $user_first_name, $blogname, $order_items, $order_total, $shipping_info, $billing_info, $order_date, $order_id
 */

if (!defined('ABSPATH')) exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Order Confirmation from <?php echo esc_html($blogname); ?></title>
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
            background: #ede4de; /* Consider a slightly different color for confirmation vs invoice if desired */
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
        .order-table { /* Renamed from invoice-table for clarity */
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        .order-table th, .order-table td {
            border: 1px solid #e5e1dc;
            padding: 10px 12px;
            text-align: left;
        }
        .order-table th {
            background: #f6f5f3;
        }
        .order-total { /* Renamed for clarity */
            font-weight: bold;
            font-size: 1.1rem;
            text-align: right;
            padding-top: 12px;
        }
        .info-block {
            margin-bottom: 18px;
        }
        a {
            color: #1a0dab; /* Standard link color */
        }
    </style>
</head>
<body>
    <img src="https://thepipedpeony.com/wp-content/uploads/2023/03/piped-peony-logo-2048x452.png" alt="<?php echo esc_html($blogname); ?> Logo" class="logo">
    <div class="email-container">
        <div class="email-header">
            <h1>Your Order Confirmation</h1>
        </div>
        <div class="email-body">
            <p>Hi <?php echo esc_html($user_first_name); ?>,</p>
            <p>Thank you for your order! We've received it and are getting it ready for you. Here's a summary of your purchase:</p>
            <div class="info-block">
                <strong>Order Date:</strong> <?php echo esc_html($order_date); ?><br>
            </div>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $order_items; // Should be rows of <tr><td>...</td>...</tr> ?>
                </tbody>
            </table>
            <div class="order-total">
                Grand Total: <?php echo esc_html($order_total); ?>
            </div>
            
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

            <p>If you have any questions about your order, please reply to this email or contact us at <a href="mailto:support@thepipedpeony.com">support@thepipedpeony.com</a>.</p>
            <p>We appreciate your business!</p>

            <p>Join our community of bakers on our Facebook page! <a href="https://www.facebook.com/groups/359977912181048">HERE</a></p>

        </div>
        <div class="email-footer">
            &copy; <?php echo date('Y'); ?> <?php echo esc_html($blogname); ?>. All rights reserved.
        </div>
    </div>
</body>
</html> 