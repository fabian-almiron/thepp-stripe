<?php
/*
Template Name: Checkout Success
*/

get_header();

// Ensure Stripe lib is loaded early
require_once get_stylesheet_directory() . '/vendor/autoload.php'; 

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) { // Ensure session_status() is used if available (PHP 5.4+), fallback to !session_id()
    session_start();
}

$error = ''; // Initialize error variable early
$stripe_session = null; // Initialize stripe session variable
$attempted_direct_login = false; // Initialize direct login flag
$is_subscription = false; // Initialize subscription flag

// --- BEGIN: Session Retrieval (Moved Up) ---
$session_id = isset($_GET['session_id']) ? sanitize_text_field($_GET['session_id']) : '';

if ($session_id) {
    error_log('[Checkout Success] Attempting to retrieve session ID: ' . $session_id);
    if (defined('STRIPE_SECRET_KEY')) {
        try {
            error_log('[Checkout Success] Setting API Key for session retrieval.');
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
            error_log('[Checkout Success] Retrieving session.');
            $stripe_session = \Stripe\Checkout\Session::retrieve($session_id);
            error_log('[Checkout Success] Session retrieved: ' . ($stripe_session ? $stripe_session->id : 'null'));

            // Check for subscription metadata AFTER successfully retrieving the session
            if ($stripe_session && isset($stripe_session->metadata->contains_subscription) && $stripe_session->metadata->contains_subscription === 'true') {
                $is_subscription = true;
                error_log('[Checkout Success] Subscription detected based on metadata (contains_subscription === \'true\').');
            } else {
                 $metadata_contents = 'Stripe session object not available or metadata property not set on session.';
                 $contains_subscription_value = 'Not found or not string true.';
                 if ($stripe_session && isset($stripe_session->metadata)) {
                    $metadata_contents = print_r($stripe_session->metadata, true);
                    if (isset($stripe_session->metadata->contains_subscription)) {
                        $contains_subscription_value = 'Value: ' . $stripe_session->metadata->contains_subscription . ' (Type: ' . gettype($stripe_session->metadata->contains_subscription) . ')';
                    } else {
                        $contains_subscription_value = 'contains_subscription key not set in metadata.';
                    }
                 } elseif ($stripe_session) {
                    $metadata_contents = 'Stripe session available, but ->metadata property is not set.';
                 }
                 error_log('[Checkout Success] Not a subscription or metadata check failed. is_subscription is FALSE. Metadata contents: ' . $metadata_contents . ' | Checked contains_subscription: ' . $contains_subscription_value);
                 $is_subscription = false; // Explicitly set to false
            }

        } catch (Exception $e) {
            error_log('[Checkout Success] Error retrieving Stripe session: ' . $e->getMessage());
            $error = $e->getMessage();
            $stripe_session = null; // Ensure session is null if retrieval failed
        }
    } else {
        error_log('[Checkout Success] STRIPE_SECRET_KEY constant is not defined during session retrieval!');
        $error = 'Stripe configuration error: API key not set.';
    }
} else {
    error_log('[Checkout Success] No session_id in GET parameters.');
    $error = 'Missing checkout session ID.';
}
// --- END: Session Retrieval ---


// --- BEGIN: Attempt direct login ONLY for subscriptions ---
if ($is_subscription && $stripe_session && isset($_SESSION['latest_registration_token']) && !empty($_SESSION['latest_registration_token'])) {
    // Compare token from session with token from Stripe metadata (retrieved above)
    if (isset($stripe_session->metadata->registration_token) &&
        $stripe_session->metadata->registration_token === $_SESSION['latest_registration_token']) {

        error_log('[Checkout Success] Subscription detected & token matches. Attempting direct login.');
        // Tokens match! Attempt to log in the user based on email from Stripe session.
        $customer_email = $stripe_session->customer_email;
        if ($customer_email) {
            $user = get_user_by('email', $customer_email);
            if ($user && !is_user_logged_in()) { // Check if not already logged in
                wp_set_current_user($user->ID, $user->user_login);
                wp_set_auth_cookie($user->ID);
                $attempted_direct_login = true;
                error_log('[Checkout Success] Successfully auto-logged in user ID: ' . $user->ID . ' for subscription.');
            } elseif ($user && is_user_logged_in()) {
                $attempted_direct_login = true; // Already logged in, counts as success for this logic
                error_log('[Checkout Success] User ID: ' . $user->ID . ' was already logged in for subscription.');
            } else if (!$user) {
                 error_log('[Checkout Success] Auto-login failed: User not found for email: ' . $customer_email);
            }
        } else {
             error_log('[Checkout Success] Auto-login failed: No customer email in Stripe session.');
        }
    } else {
        error_log('[Checkout Success] Subscription detected but registration token mismatch or missing.');
    }
    // Clear the session token regardless of outcome to prevent reuse
    unset($_SESSION['latest_registration_token']);

} elseif ($is_subscription) {
     error_log('[Checkout Success] Subscription detected but no registration token found in PHP session, skipping direct login attempt.');
}
// --- END: Attempt direct login ---


// Clear the cart after successful checkout
unset($_SESSION['cart']);

// No need for the main retrieval block anymore, it's handled above.
// $line_items retrieval remains the same, using $stripe_session if available.
$line_items = null;
$session = $stripe_session; // Assign to $session for compatibility with later code

if ($session && empty($error)) { // Only try to get line items if session was retrieved successfully
    error_log('[Checkout Success] Attempting to retrieve line items for session: ' . $session->id);
    try {
        // API key should be set from the session retrieval block above
        // Re-setting just in case, although likely redundant if using the same script execution
         if (defined('STRIPE_SECRET_KEY')) {
            \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
             $line_items = \Stripe\Checkout\Session::allLineItems($session->id, ['limit' => 100]);
             error_log('[Checkout Success] Line items retrieved successfully.');

             // ---- BEGIN SEND INVOICE EMAIL ----
             // Check if email has already been sent for this session using a transient
             $email_sent_transient_key = 'invoice_sent_' . $session->id;
             if (!get_transient($email_sent_transient_key)) {
                 error_log('[Checkout Success] Preparing to send email for session: ' . $session->id . ($is_subscription ? ' (Subscription Invoice)' : ' (Order Confirmation)'));
                 
                 // Gather data for email
                 // Try customer_details->email first, then customer_email
                 $customer_email = null;
                 if (isset($session->customer_details) && !empty($session->customer_details->email)) {
                     $customer_email = $session->customer_details->email;
                 } elseif (isset($session->customer_email) && !empty($session->customer_email)) {
                     $customer_email = $session->customer_email;
                 }

                 if ($customer_email) {
                    // Try to get user's first name
                    $user = get_user_by('email', $customer_email);
                    $user_first_name = $user ? $user->first_name : '';
                    if (!$user_first_name && $user && $user->display_name) {
                        $user_first_name = $user->display_name;
                    }
                    // Fallback to extracting from email if first name is empty
                    if (empty($user_first_name) && strpos($customer_email, '@') !== false) {
                        $name_part = substr($customer_email, 0, strpos($customer_email, '@'));
                        $name_part = str_replace(['.', '_', '-'], ' ', $name_part);
                        $user_first_name = ucwords($name_part);
                    } 
                    if(empty($user_first_name)) {
                        $user_first_name = 'Valued Customer'; // Generic fallback
                    }

                    $blogname = get_bloginfo('name');
                    $order_date = current_time('F j, Y, g:i a'); // Current time as order date
                    $order_id = $session->id; // Use Checkout Session ID as Order ID
                    $order_total = '$' . number_format($session->amount_total / 100, 2);

                    // Format line items into HTML table rows
                    $order_items_html = '';
                    foreach ($line_items->data as $item) {
                        $unit_price = ($item->quantity > 0) ? ($item->amount_total / 100 / $item->quantity) : 0;
                        $order_items_html .= sprintf(
                            '<tr><td>%s</td><td>%d</td><td>$%s</td><td>$%s</td></tr>',
                            esc_html($item->description),
                            esc_html($item->quantity),
                            number_format($unit_price, 2),
                            number_format($item->amount_total / 100, 2)
                        );
                    }

                    // Format addresses (handle potential nulls)
                    $shipping_address = $session->shipping_details && $session->shipping_details->address ? $session->shipping_details->address : null;
                    $shipping_info = $session->shipping_details ? esc_html($session->shipping_details->name) . "\n" : '';
                    if ($shipping_address) {
                         $shipping_info .= implode("\n", array_filter([
                            $shipping_address->line1,
                            $shipping_address->line2,
                            trim(sprintf('%s, %s %s', $shipping_address->city, $shipping_address->state, $shipping_address->postal_code)),
                            $shipping_address->country
                        ]));
                    } else {
                        $shipping_info = 'No shipping required or provided.';
                    }

                    $billing_address = $session->customer_details && $session->customer_details->address ? $session->customer_details->address : null;
                    $billing_info = $session->customer_details ? esc_html($session->customer_details->name) . "\n" : ''; // Billing name might be null
                     if (empty(trim($billing_info))) {
                         $billing_info = esc_html($session->customer_details->email) . "\n"; // Use email if name missing
                     }
                     if ($billing_address) {
                         $billing_info .= implode("\n", array_filter([
                            $billing_address->line1,
                            $billing_address->line2,
                            trim(sprintf('%s, %s %s', $billing_address->city, $billing_address->state, $billing_address->postal_code)),
                            $billing_address->country
                        ]));
                    } else {
                         $billing_info .= 'Billing address not provided.'; // Append if name was present
                    }
                    

                    // Prepare variables for the template
                    $template_vars = [
                        'user_first_name' => $user_first_name,
                        'blogname'        => $blogname,
                        'order_items'     => $order_items_html,
                        'order_total'     => $order_total,
                        'shipping_info'   => $shipping_info,
                        'billing_info'    => $billing_info,
                        'order_date'      => $order_date,
                        'order_id'        => $order_id
                    ];

                    // Load the template file into a string
                    ob_start();
                    // Pass vars to the template scope
                    extract($template_vars);
                    
                    // Conditionally load the correct email template
                    if ($is_subscription) {
                        // REMOVED: include(locate_template('template-parts/email-invoice.php'));
                        // $email_subject_type = 'Invoice';
                        // No email will be sent here for subscriptions now
                        error_log('[Checkout Success] Subscription detected. Email sending skipped in page-checkout-success.php (handled by webhook or other process).');
                        $email_body = null; // Ensure email_body is null so no email is sent
                    } else {
                        include(locate_template('template-parts/email-order-confirmation.php'));
                        $email_subject_type = 'Order Confirmation';
                        $email_body = ob_get_clean();
                    }
                    
                    // Only proceed to send if email_body is not null (i.e., it was a non-subscription)
                    if ($email_body) {
                        // Set email headers
                        $headers = ['Content-Type: text/html; charset=UTF-8'];
                        $headers[] = 'From: The Piped Peony <noreply@thepipedpeony.com>';
                       // $headers[] = 'Bcc: ninthsouthdigital@gmail.com';
                       // $headers[] = 'Bcc: dara@thepipedpeony.com';
                       // $headers[] = 'Bcc: tim@thepipedpeony.com';

                        // Send the email to the customer and BCC to test address
                        $subject = sprintf('Your %s from %s (Order %s)', $email_subject_type, $blogname, $order_id);
                        // $headers[] = 'Bcc: dara@thepipedpeony.com';
                        // $headers[] = 'Bcc: tim@thepipedpeony.com';
                        $headers[] = 'Bcc: ninthsouthdigital@gmail.com';
                        if (wp_mail($customer_email, $subject, $email_body, $headers)) {
                            error_log('[Checkout Success] ' . $email_subject_type . ' email sent successfully to: ' . $customer_email . ' (BCC:  tim@thepipedpeony.com)');
                        } else {
                            error_log('[Checkout Success] Failed to send ' . $email_subject_type . ' email to: ' . $customer_email . ' (BCC: tim@thepipedpeony.com)');
                        }
                    }
                 } else {
                      error_log('[Checkout Success] Cannot send email: Customer email not found in Stripe session (checked customer_details->email and customer_email).');
                 }
             } else {
                 error_log('[Checkout Success] Email already sent for session: ' . $session->id . ' (transient found) - Type: ' . ($is_subscription ? 'Subscription Invoice' : 'Order Confirmation'));
             }
             // ---- END SEND INVOICE EMAIL ----

         } else {
             error_log('[Checkout Success] STRIPE_SECRET_KEY not defined for line item retrieval!');
             if(empty($error)) $error = 'Stripe configuration error: API key not set.';
         }
    } catch (Exception $e) {
        error_log('[Checkout Success] Error retrieving Stripe line items: ' . $e->getMessage());
        // Dont overwrite previous $error unless it's empty
        if(empty($error)) $error = $e->getMessage();
    }
} elseif (!$session && empty($error)) {
    error_log('[Checkout Success] Cannot retrieve line items because session is null and no error was previously set.');
    $error = 'Unable to retrieve session details.'; // Provide a generic error
} elseif ($error) {
    error_log('[Checkout Success] Cannot retrieve line items due to previous error: ' . $error);
}

?>
<style>
    body {
        background-image: url(https://thepipedpeony.com/wp-content/uploads/2023/04/header-supply.svg);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
    }

    .main-container {
        /* Responsive margins and padding */
        margin: 1rem; /* Default for small screens */
        padding: 1.5rem; /* Default for small screens */
        background-color: white;
        text-align: left;
    }

    @media (min-width: 640px) { /* sm breakpoint */
        .main-container {
            margin: 2rem;
            padding: 2.5rem;
        }
    }

    @media (min-width: 1024px) { /* lg breakpoint */
        .main-container {
            margin: 4rem;
            padding: 4rem;
        }
    }
</style>
<div class="container mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 md:py-20 lg:py-24">
    <div class="max-w-2xl main-container mx-auto text-center">
        <h1 class="text-2xl sm:text-3xl font-bold mb-6 sm:mb-8">Thank You for Your Purchase!</h1>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 sm:p-6 md:p-8 mb-6 sm:mb-8">
            <div class="text-green-600 mb-4">

                <p class="text-lg sm:text-xl">Your order has been processed successfully!</p>
            </div>
            <?php if ($error): ?>
                <p class="text-red-600 mb-4">Unable to retrieve order details: <?php echo esc_html($error); ?></p>
            <?php elseif ($session && $line_items && !empty($line_items->data)):
            ?>
                <div class="mb-4 text-left">
                    <h3 class="text-lg font-semibold mb-2">Order Summary</h3>

                    <!-- List View (for all screen sizes) -->
                    <div class="block space-y-3"> <!-- Removed sm:hidden -->
                        <?php foreach ($line_items->data as $item): ?>
                            <div class="border rounded-md p-3 bg-gray-50">
                                <div class="font-medium text-gray-800"><?php echo esc_html($item->description); ?></div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <span class="font-semibold">Quantity:</span> <?php echo esc_html($item->quantity); ?><br>
                                    <span class="font-semibold">Unit Price:</span> $<?php echo number_format($item->amount_total / 100 / $item->quantity, 2); ?><br>
                                    <span class="font-semibold">Subtotal:</span> $<?php echo number_format($item->amount_total / 100, 2); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="flex justify-between font-semibold mt-4">
                        <span>Total:</span>
                        <span>$<?php echo number_format($session->amount_total / 100, 2); ?></span>
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        <div>Paid by: <span class="font-medium"><?php echo esc_html($session->customer_email ?: ($session->customer_details->email ?? 'N/A')); ?></span></div>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-gray-600 mb-4">Could not display order details.</p> <?php // Changed message ?>
            <?php endif; ?>

            <!-- Dynamic message area for login status - ONLY FOR SUBSCRIPTIONS -->
            <?php if ($is_subscription): ?>
                <div id="success-page-dynamic-message" class="mt-6 mb-4 text-lg">
                    <?php if ($attempted_direct_login && is_user_logged_in()): ?>
                         <p class="text-green-600 font-semibold">Your account is ready and you are now logged in!</p><p class="mt-2">You can <a href="<?php echo home_url("/my-account/"); ?>" class="text-blue-600 hover:underline">access your account dashboard here</a>.</p>
                    <?php else: ?>
                        <p class="text-blue-600">Finalizing your account setup, please wait...</p> <?php // Default message while JS runs ?>
                    <?php endif; ?>
                </div>
                 <p class="text-gray-600 mb-4">You will receive an email with your account details shortly.</p>
            <?php else: ?>
                 <p class="text-gray-600 mb-4">You will receive an email with your order confirmation shortly.</p> <?php // Different message for non-subscriptions ?>
            <?php endif; ?>
        </div>
        <div class="space-y-4 md:space-y-0 md:space-x-4 flex flex-col md:flex-row justify-center items-center">
            <a href="/shop" class="w-full md:w-auto inline-block bg-blue-600 text-white px-8 py-3 rounded-lg hover:bg-blue-700 mb-2 md:mb-0">Continue Shopping</a>
            <?php // Optionally add a link to my-account if they might have an existing account ?>
            <?php if (!is_user_logged_in()): ?>
                 <a href="<?php echo wp_login_url(home_url("/my-account/")); ?>" class="w-full md:w-auto inline-block text-blue-600 hover:underline">Log In to Your Account</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>

<?php // Only include JS for account status check if it's a subscription ?>
<?php if ($is_subscription): ?>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function() {
    const dynamicMessageContainer = document.getElementById('success-page-dynamic-message');
    const genericMessage = document.querySelector('#success-page-dynamic-message + .text-gray-600.mb-4'); // The "You will receive an email..." message

    // If direct login already succeeded, we might not need the AJAX check,
    // but we run it anyway for consistency and to handle edge cases where direct login fails but webhook succeeds quickly.
    // if (!dynamicMessageContainer || <?php echo json_encode($attempted_direct_login && is_user_logged_in()); ?>) {
    //     console.log('Skipping AJAX check: Already logged in via direct attempt or container missing.');
    //     return;
    // }

     if (!dynamicMessageContainer) return;

    // Try checking status after a short delay to allow webhook to potentially complete
    setTimeout(function() {
        fetch("<?php echo admin_url('admin-ajax.php'); ?>", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'check_login_status_after_checkout' // This action likely needs context (e.g., user email or order ID) if not using session
            })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
             // Update message based on AJAX response ONLY IF direct login didn't already show success
            if (!<?php echo json_encode($attempted_direct_login && is_user_logged_in()); ?>) {
                if (data.success && data.data.logged_in) {
                    dynamicMessageContainer.innerHTML = '<p class="text-green-600 font-semibold">Your account is ready and you are now logged in!</p><p class="mt-2">You can <a href="<?php echo home_url("/my-account/"); ?>" class="text-blue-600 hover:underline">access your account dashboard here</a>.</p>';
                    if(genericMessage) genericMessage.textContent = "Your order and account details have been emailed to you.";
                } else {
                    dynamicMessageContainer.innerHTML = '<p class="text-orange-600">Your order is complete! Please <a href="<?php echo wp_login_url(home_url("/my-account/")); ?>" class="text-blue-600 hover:underline">log in here</a> to access your account, or check your email for account details.</p><p class="mt-2 text-sm">If you just created an account, it might take a moment to activate. Try refreshing in a few seconds.</p>';
                 }
            } else {
                 console.log('AJAX check completed, but message already set by successful direct login.');
            }
        })
        .catch(error => {
            console.error('Error checking login status via AJAX:', error);
             // Show error only if direct login didn't already succeed
             if (!<?php echo json_encode($attempted_direct_login && is_user_logged_in()); ?>) {
                dynamicMessageContainer.innerHTML = '<p class="text-red-600">There was an issue finalizing your account setup. Please <a href="<?php echo wp_login_url(home_url("/my-account/")); ?>" class="text-blue-600 hover:underline">try logging in</a> or check your email.</p>';
             }
        });
    }, 2500); // 2.5 second delay - adjust as needed
});
</script>
<?php endif; ?>
