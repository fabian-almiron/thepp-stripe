<?php
/**
 * Template Name: Checkout
 */

// Log session cart contents immediately on page load for debugging direct checkout links
// error_log('Checkout Page Loaded - Cart: ' . print_r($_SESSION['cart'] ?? 'Not set', true)); 

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (!session_id()) {
    session_start();
}

// Handle direct checkout link parameter for subscriptions or single items
if (isset($_GET['checkout_product_id']) && ($product_id = intval($_GET['checkout_product_id'])) && $product_id > 0) {
    error_log("[Checkout Page] Direct checkout link detected for product ID: " . $product_id);
    
    // Clear existing cart first when using a direct link to ensure only this item is processed initially.
    $_SESSION['cart'] = [];

    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    if ($quantity < 1) $quantity = 1;

    // Fetch necessary product details
    $post_status = get_post_status($product_id);
    if ($post_status === 'publish') {
        // We need stripe_price_id. For subscriptions, it's direct. For others, it might be too.
        // This logic assumes the price ID needed for Stripe Checkout is stored in 'stripe_price_id' meta.
        $stripe_price_id = get_post_meta($product_id, 'stripe_price_id', true);
        $product_title = get_the_title($product_id);
        $image_url = get_the_post_thumbnail_url($product_id, 'thumbnail');
        $product_type = get_field('product_type', $product_id); // Needed for JS logic perhaps

        if (!empty($stripe_price_id)) { 
            $_SESSION['cart'][$product_id] = [ // Using product_id as key for simplicity here
                'product_id'        => $product_id,
                'quantity'          => $quantity,
                'name'              => $product_title,
                // 'price'          => 0, // Price not strictly needed for Stripe if using Price ID, but good for display
                'stripe_price_id'   => $stripe_price_id,
                'variation_name'    => '', // Assuming direct links are not for variations
                'product_type'      => $product_type, 
                'image_url'         => $image_url ? $image_url : ''
            ];
            error_log("[Checkout Page] Product ID {$product_id} (Stripe Price ID: {$stripe_price_id}) added to cart via direct link. Cart: " . print_r($_SESSION['cart'], true));
            
            // IMPORTANT: Redirect to remove the query parameters to prevent re-adding on refresh
            // and to ensure checkoutData.cart is correctly populated without re-running this block immediately before localization.
            wp_redirect(home_url('/checkout/'));
            exit;
        } else {
            error_log("[Checkout Page] Direct checkout link FAILED for {$product_id}: Could not find Stripe Price ID meta 'stripe_price_id'.");
        }
    } else {
         error_log("[Checkout Page] Direct checkout link FAILED for {$product_id}: Product not found or not published.");
    }
}

$cart = $_SESSION['cart'] ?? [];

// Generate nonces
$checkout_nonce = wp_create_nonce('checkout_nonce');
$stripe_nonce = wp_create_nonce('stripe_payment_nonce');
$check_email_nonce = wp_create_nonce('check_email_nonce'); // <-- Add nonce for email check

get_header();

// Get logged-in user information
$current_user_email = '';
$is_user_logged_in_flag = is_user_logged_in();
if ($is_user_logged_in_flag) {
    $current_user_obj = wp_get_current_user();
    if ($current_user_obj && $current_user_obj->ID != 0) {
        $current_user_email = $current_user_obj->user_email;
    }
}
 
// Include Tailwind CSS via CDN
?>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

<?php
// Force regenerate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Debug output (remove in production)
?>
<!-- Debug Info:
Session ID: <?php echo session_id(); ?>
CSRF Token: <?php echo $_SESSION['csrf_token']; ?>
-->

<?php
// Add security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https: http: data:;');

// Environment check
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || 
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false;

if ($is_local) {
    error_log('WARNING: Using live Stripe keys in development environment!');
}

// Debugging helper - Remove in production
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('Session Token: ' . $_SESSION['csrf_token']);
    error_log('Posted Token: ' . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'not set'));
}

// Add this near where you calculate total_price
$total_price = 0;
$has_trial = false;
$trial_days = 0;

// Debug cart contents
error_log('Cart Contents: ' . print_r($_SESSION['cart'], true));

// Debug ACF fields (replace the debug_acf_fields() call)
foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $product_type = get_field('product_type', $product_id);
    $trial_days = get_field('subscription_free_trial_days', $product_id);
    $subscription_price = get_field('subscription_price', $product_id);
    
    error_log("DEBUG ACF Fields for post " . $product_id);
    error_log("Product Type: " . $product_type);
    error_log("Free Trial Days: " . $trial_days);
    error_log("Subscription Price: " . $subscription_price);
    error_log("All Fields: " . print_r(get_fields($product_id), true));

    // Add debug for Stripe IDs
    $stripe_product_id = get_post_meta($product_id, 'stripe_product_id', true);
    $stripe_price_id = get_post_meta($product_id, 'stripe_price_id', true);
    error_log("Stripe Product ID: " . $stripe_product_id);
    error_log("Stripe Price ID: " . $stripe_price_id);

    // If it's a variation product, also debug variation Stripe IDs
    if ($product_type === 'variation') {
        $variations = get_field('variation_product', $product_id);
        if ($variations) {
            foreach ($variations as $variation) {
                $variation_name = $variation['product_variation'];
                $stripe_variation_product_id = get_post_meta($product_id, 'stripe_variation_product_id_' . $variation_name, true);
                $stripe_variation_price_id = get_post_meta($product_id, 'stripe_variation_price_id_' . $variation_name, true);
                error_log("Variation: " . $variation_name);
                error_log("Variation Stripe Product ID: " . $stripe_variation_product_id);
                error_log("Variation Stripe Price ID: " . $stripe_variation_price_id);
            }
        }
    }
}

foreach ($_SESSION['cart'] as $index => $item) {
    $product_id = $item['product_id'];
    debug_acf_fields($product_id); // Add this debug call
    
    $price = 0;
    $product_type = get_field('product_type', $product_id);
    
    if ($product_type === 'subscription') {
        $trial_days = intval(get_field('subscription_free_trial_days', $product_id));
        error_log("Processing subscription product {$product_id} with {$trial_days} trial days");
        
        // Get subscription product's price ID
        $stripe_price_id = get_post_meta($product_id, 'stripe_price_id', true);
        $item['stripe_price_id'] = $stripe_price_id;
        $_SESSION['cart'][$index]['stripe_price_id'] = $stripe_price_id;
        
        if ($trial_days > 0) {
            $has_trial = true;
            $price = 0;
            // Get WordPress timezone
            $wp_timezone = wp_timezone();
            // Create DateTime object with WordPress timezone
            $today = new DateTime('now', $wp_timezone);
            // Add trial days
            $renewal_date = $today->modify("+{$trial_days} days")->format('F j, Y');
            error_log("Free trial product found. Renewal date: {$renewal_date}");
        } else {
            $price = floatval(get_field('subscription_price', $product_id));
        }
    } elseif ($item['variation']) {
        foreach (get_field('variation_product', $item['product_id']) as $variation) {
            if ($variation['product_variation'] === $item['variation']) {
                $price = (float) $variation['variable_product_price'];
                // Price ID is already correctly set in the $item array from the session
                break;
            }
        }
    } else {
        if ($product_type === 'single') {
            $price = floatval(get_field('product_price', $product_id));
            // For single products, use the main product's price ID
            $stripe_price_id = get_post_meta($item['product_id'], 'stripe_price_id', true);
            $item['stripe_price_id'] = $stripe_price_id;
            $_SESSION['cart'][$index]['stripe_price_id'] = $stripe_price_id;
        }
    }
    
    $line_total = $price * $item['quantity'];
    error_log("Line total for product {$product_id}: {$line_total}");
    $total_price += $line_total;
}

error_log("Final total price: {$total_price}");

// Calculate $has_subscription BEFORE localizing script
$has_subscription = false; // Initialize the variable
foreach ($cart as $item) {
    $product_id = $item['product_id'];
    $product_type = get_field('product_type', $product_id);
    if ($product_type === 'subscription') {
        $has_subscription = true;
        break;
    }
}

// Enqueue and Localize Script - SINGLE CONSOLIDATED CALL
wp_enqueue_script('checkout-js', get_template_directory_uri() . '/js/checkout.js', array('jquery'), '1.0.0', true);
wp_localize_script('checkout-js', 'checkoutData', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'checkout_nonce' => $checkout_nonce,            // Nonce for the main checkout action
    'check_email_nonce' => $check_email_nonce,      // Nonce for the email check action
    'check_email_action' => 'check_stripe_customer_email', // Action name for email check
    'cart' => $cart,                               // Current cart contents
    'stripe_pk' => STRIPE_PUBLISHABLE_KEY,         // Stripe Publishable Key
    'hasTrial' => $has_trial,                      // Whether cart contains a trial
    'trialDays' => $trial_days,                    // Number of trial days (if applicable)
    'hasSubscription' => $has_subscription,         // Whether cart contains any subscription
    'is_user_logged_in' => $is_user_logged_in_flag, // New: Pass logged-in status
    'current_user_email' => $current_user_email   // New: Pass current user email
    // Add stripe_nonce if needed directly in JS, though usually handled server-side
));

// Add this temporarily at the top of your page to check session functionality
echo '<!-- Session writable: ' . is_writable(session_save_path()) . ' -->';
echo '<!-- Session save path: ' . session_save_path() . ' -->';

// Add this near the top of your checkout page, after get_header():
$checkout_nonce = wp_create_nonce('checkout_nonce');

// Create a fresh nonce specifically for the Stripe payment
$stripe_nonce = wp_create_nonce('stripe_payment_nonce');

// Include Stripe PHP library
require_once get_template_directory() . '/vendor/autoload.php';

// Set your secret key. Remember to switch to your live secret key in production!
// See your keys here: https://dashboard.stripe.com/apikeys
\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY); // Use the key from wp-config.php

// Add CSRF token to the form
?>
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
<?php

// Debugging output to verify server-side logic
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['start_checkout'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        wp_send_json_error(['error' => 'Invalid security token']);
        exit;
    }

    // Verify nonce
    if (!isset($_POST['checkout_nonce']) || !wp_verify_nonce($_POST['checkout_nonce'], 'checkout_nonce')) {
        wp_send_json_error(['error' => 'Invalid nonce']);
        exit;
    }

    error_log('Form submitted: Proceed to Checkout button clicked.');
    error_log('CSRF Token: ' . (isset($_POST['csrf_token']) ? $_POST['csrf_token'] : 'not set'));
    $cart = $_SESSION['cart'] ?? [];
    $line_items = [];
    $has_trial = false;

    foreach ($cart as $item) {
        $product_id = $item['product_id'];
        $product_type = get_field('product_type', $product_id);
        $trial_days = intval(get_field('subscription_free_trial_days', $product_id));

        if ($product_type === 'subscription' && $trial_days > 0) {
            $has_trial = true;
            break;
        }
    }

    if ($has_trial) {
        error_log('Creating Setup Intent for trial subscription.');
        // Create a Setup Intent for trial subscriptions
        $setup_intent = \Stripe\SetupIntent::create([
            'payment_method_types' => ['card'],
        ]);

        // Pass the client secret to the frontend
        echo '<script>var setupIntentClientSecret = "' . $setup_intent->client_secret . '";</script>';
    } else {
        error_log('Creating regular Checkout session.');
        // Create a regular Checkout session
        foreach ($cart as $item) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => get_the_title($item['product_id']),
                    ],
                    'unit_amount' => $item['price'] * 100, // Convert to cents
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $checkout_session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => home_url('/checkout-success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => home_url('/checkout-cancel'),
        ]);

        echo json_encode(['url' => $checkout_session->url]);
        exit;
    }
}
?>

<style>

.step-content {
    display: block;
    margin: auto;
    border: 0.1px solid #d4d4d4;
    padding: 40px;
    margin-top: 60px;
    background-color: white;
}

.main-content-bg {
    background-image: url("https://thepipedpeony.com/wp-content/uploads/2023/04/header-supply.svg");
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
    
.co-container {
    max-width: 590px;
}

button#next-step {
    width: 45%;
}

button#prev-step {
    width: 45%;
}
    .fade-enter-active, .fade-leave-active {
        transition: opacity 0.5s ease-in-out;
    }
    .fade-enter, .fade-leave-to {
        opacity: 0;
    }

    .step-indicator.active-step {
    background-color: #f2eae5;
    border: 1px solid;
}

.cart-item-c a:hover {
    color: gray;
    text-decoration: none;
}

.cart-item-c a {
    color: black;
    text-decoration: none;
    font-weight: 500;
}

.step-indicator {
    padding: 14px 50px;
    background-color: #f3f4f6;
    border-radius: 0;
    display: none;
}


.active-step {
    font-weight: bold;
    color: #010101;
}
    .border-b-2 {
        border-bottom: 1px solid #d5d5d5 !important;
    }
    button.text-red-500.hover\:text-red-700.ml-4 {
        border: none;
        font-size: 12px;
    }
    button.text-red-500.hover\:text-red-700.ml-4:hover {
        background-color: transparent;
        color: rgb(209, 0, 0) !important;
    }
    .qntt-input {
        width: 70px !important;
    }

    label {
    display: none;
    line-height: 1;
    vertical-align: middle;
    font-family: 'Sofia Pro Regular';
}

.slide-enter-active, .slide-leave-active {
    transition: transform 0.5s ease-in-out, opacity 0.5s ease-in-out;
}
.slide-enter, .slide-leave-to {
    transform: translateX(100%);
    opacity: 0;
}
.slide-leave {
    transform: translateX(-100%);
}

input {
    height: 50px !important;
}

.payment-form-container {
    border: 1px solid;
    padding: 20px;
    max-width: 670px;
    margin-top: 15px;
}

/* Add this to your existing styles */
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.alert {
    padding: 1rem;
    margin-bottom: 1rem;
    border: 1px solid transparent;
    border-radius: 0.25rem;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

#spinner {
    display: inline-block;
    margin-left: 0.5rem;
}
</style>

<!-- Add this hidden input to your form -->
<input type="hidden" name="checkout_security" value="<?php echo esc_attr($checkout_nonce); ?>">
<div class="main-content-bg">
<div class="container padder_lg mx-auto px-4 co-container">
    <form method="post" id="main-checkout-form">
        <!-- Step Indicators -->
        <div id="payment-error" class="text-red-600 mt-4 p-4 bg-red-50 rounded-lg" style="display: none;"></div>

        <?php if ($is_user_logged_in_flag && !empty($current_user_email)) : ?>
            <!-- Logged-in user view -->
            <div class="flex justify-center mb-8">
                <div class="flex flex-col items-center space-y-2 md:flex-row md:space-y-0 md:space-x-4">
                    <div class="step-indicator flex items-center space-x-2" data-step="2"> <!-- Reusing data-step="2", JS might need to adapt if steps change -->
                        <i class="fas fa-credit-card"></i>
                        <span>Confirm Purchase</span>
                    </div>
                </div>
            </div>

            <div id="step-2" class="step-content">
                <h3 class="text-3xl text-center font-bold mb-8">Confirm Your Purchase</h3>
                <div class="max-w-md mx-auto">
                    <p class="mb-4 text-center">You are proceeding as: <strong><?php echo esc_html($current_user_email); ?></strong></p>
                    <p class="mb-6 text-center text-sm text-gray-600">Your subscription will be linked to this account. Click "Proceed to Payment" to continue.</p>
                    <!-- Hidden email field for JS to pick up and send to create_checkout_session -->
                    <input type="hidden" id="customer-email" value="<?php echo esc_attr($current_user_email); ?>">
                    <!-- Username and password fields are omitted -->
                </div>
            </div>

            <div class="flex justify-center mt-8">
                <button type="button" id="next-step" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 w-1/2 flex items-center justify-center">
                    <span>Proceed to Payment</span>
                    <svg id="signup-spinner" class="animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>

        <?php else : ?>
            <!-- Existing "Create Account" view for logged-out users -->
            <div class="flex justify-center mb-8">
                <div class="flex flex-col items-center space-y-2 md:flex-row md:space-y-0 md:space-x-4">
                    <div class="step-indicator flex items-center space-x-2" data-step="2">
                        <i class="fas fa-user"></i>
                        <span>Create Account</span>
                    </div>
                </div>
            </div>

            <div id="step-2" class="step-content">
                <h3 class="text-3xl text-center font-bold mb-8">Create Account</h3>
                <div class="max-w-md mx-auto">
                    <div class="mb-4">
                        <label class="input-label" for="customer-email">Email</label>
                        <input 
                            type="email" 
                            id="customer-email" 
                            class="input-field" 
                            required 
                            placeholder="your@email.com"
                        >
                        <div id="email-error" class="text-red-600 text-sm mt-1" style="display: none;">
                            <span class="generic-email-error"></span>
                            <span class="existing-customer-error" style="display: none;">
                                An account with this email already exists in our payment system. Please <a href="<?php echo wp_login_url(get_permalink()); ?>" class="font-bold underline">log in</a> to manage your subscription or use a different email address.
                            </span>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="input-label" for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            class="input-field" 
                            required 
                            placeholder="Username"
                        >
                        <div id="username-error" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>
                    <div class="mb-4">
                        <label class="input-label" for="password">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            class="input-field" 
                            required 
                            placeholder="Password"
                        >
                        <div id="password-error" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>
                    <div class="mb-4">
                        <label class="input-label" for="verify-password">Verify Password</label>
                        <input 
                            type="password" 
                            id="verify-password" 
                            class="input-field" 
                            required 
                            placeholder="Verify Password"
                        >
                        <div id="verify-password-error" class="text-red-600 text-sm mt-1" style="display: none;"></div>
                    </div>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="flex justify-center mt-8">
                <button type="button" id="next-step" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 w-1/2 flex items-center justify-center">
                    <span>Sign Up</span>
                    <svg id="signup-spinner" class="animate-spin ml-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" style="display: none;">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>
</div>

<style>
.step {
    @apply px-4 py-2 bg-gray-200;
}
.step.active {
    @apply bg-blue-600 text-white;
}
.hidden {
    display: none;
}
</style>

<?php
// Add this near the top of your file
if (WP_DEBUG) {
    error_log('POST Data: ' . print_r($_POST, true));
    error_log('Session Data: ' . print_r($_SESSION, true));
    
    // Display detailed cart information
    if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
        error_log('==== CART CONTENTS ====');
        foreach ($_SESSION['cart'] as $index => $item) {
            error_log("Item #$index");
            error_log("Product ID: " . $item['product_id']);
            error_log("Quantity: " . $item['quantity']);
            error_log("Variation: " . ($item['variation'] ?? 'None'));
            error_log("Stripe Product ID: " . ($item['stripe_product_id'] ?? 'Not set'));
            error_log("Stripe Price ID: " . ($item['stripe_price_id'] ?? 'Not set'));
            error_log("---------------------");
        }
    }
}

get_footer();

