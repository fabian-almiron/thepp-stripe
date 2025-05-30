# API Documentation - Hello Elementor Child Theme

## Overview

This document describes the API endpoints, integrations, and data structures used in the Hello Elementor Child theme. The theme integrates with Stripe for payments and provides custom WordPress Ajax endpoints for cart management and content loading.

## Table of Contents

1. [Stripe Integration](#stripe-integration)
2. [Custom WordPress Endpoints](#custom-wordpress-endpoints)
3. [Cart Management API](#cart-management-api)
4. [Email System API](#email-system-api)
5. [Load More Functionality](#load-more-functionality)
6. [Authentication & Security](#authentication--security)
7. [Data Structures](#data-structures)
8. [Error Handling](#error-handling)

## Stripe Integration

### Configuration

```php
// Required constants in wp-config.php
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
define('STRIPE_SECRET_KEY', 'sk_live_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');
```

### Stripe Checkout Session Creation

**Endpoint:** JavaScript function `createCheckoutSession()`  
**Method:** AJAX POST to WordPress admin-ajax.php  
**Action:** `create_stripe_checkout_session`

#### Request Structure
```javascript
{
    action: 'create_stripe_checkout_session',
    cart: [
        {
            product_id: 123,
            quantity: 1,
            name: "Product Name",
            stripe_price_id: "price_1234567890",
            product_type: "subscription",
            variation_name: "",
            image_url: "https://example.com/image.jpg"
        }
    ],
    customer: {
        email: "customer@example.com",
        first_name: "John",
        last_name: "Doe"
    },
    nonce: "stripe_payment_nonce_value"
}
```

#### Response Structure
```json
{
    "success": true,
    "session_id": "cs_1234567890",
    "checkout_url": "https://checkout.stripe.com/pay/cs_1234567890"
}
```

### Webhook Endpoint

**File:** `stripe-webhook.php`  
**URL:** `https://yoursite.com/stripe-webhook.php`  
**Method:** POST

#### Supported Events
- `checkout.session.completed`
- `invoice.payment_succeeded`
- `customer.subscription.created`
- `customer.subscription.updated`
- `customer.subscription.deleted`

#### Webhook Processing
```php
// Verify webhook signature
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = STRIPE_WEBHOOK_SECRET;

$event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
);
```

## Custom WordPress Endpoints

### User Registration During Checkout

**Action:** `register_user_checkout`  
**Method:** POST  
**URL:** `admin-ajax.php`

#### Request Parameters
```php
array(
    'action' => 'register_user_checkout',
    'email' => 'user@example.com',
    'first_name' => 'John',
    'last_name' => 'Doe',
    'password' => 'securepassword',
    'nonce' => wp_create_nonce('checkout_nonce')
)
```

#### Response
```json
{
    "success": true,
    "user_id": 123,
    "message": "User registered successfully"
}
```

### Email Verification Check

**Action:** `check_user_email`  
**Method:** POST  
**URL:** `admin-ajax.php`

#### Request Parameters
```php
array(
    'action' => 'check_user_email',
    'email' => 'user@example.com',
    'nonce' => wp_create_nonce('check_email_nonce')
)
```

#### Response
```json
{
    "exists": true,
    "user_id": 123
}
```

## Cart Management API

### Session-Based Cart Structure

The cart is stored in PHP sessions with the following structure:

```php
$_SESSION['cart'] = array(
    'product_id_123' => array(
        'product_id' => 123,
        'quantity' => 2,
        'name' => 'Product Name',
        'stripe_price_id' => 'price_1234567890',
        'variation_name' => 'Large',
        'product_type' => 'single',
        'image_url' => 'https://example.com/image.jpg'
    )
);
```

### Add to Cart

**Implementation:** Direct session manipulation  
**Location:** `page-checkout.php`, `functions.php`

```php
// Add item to cart
$_SESSION['cart'][$product_id] = array(
    'product_id' => $product_id,
    'quantity' => $quantity,
    'name' => get_the_title($product_id),
    'stripe_price_id' => get_post_meta($product_id, 'stripe_price_id', true),
    'variation_name' => $variation_name,
    'product_type' => get_field('product_type', $product_id),
    'image_url' => get_the_post_thumbnail_url($product_id, 'thumbnail')
);
```

### Direct Checkout Links

**URL Format:** `/checkout/?checkout_product_id=123&quantity=1`

#### Parameters
- `checkout_product_id` (required): WordPress post ID
- `quantity` (optional): Number of items (default: 1)

#### Processing Logic
```php
if (isset($_GET['checkout_product_id'])) {
    $product_id = intval($_GET['checkout_product_id']);
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    
    // Clear existing cart
    $_SESSION['cart'] = [];
    
    // Add product to cart
    // ... (product addition logic)
    
    // Redirect to clean URL
    wp_redirect(home_url('/checkout/'));
    exit;
}
```

## Email System API

### Email Template Structure

**Location:** `/template-parts/`

#### Available Templates
- `email-invoice.php` - Invoice emails
- `email-order-confirmation.php` - Order confirmations
- `email-user-signup.php` - User registration emails

### Email Sending Function

```php
function send_custom_email($template, $data, $to, $subject) {
    ob_start();
    extract($data);
    include get_stylesheet_directory() . '/template-parts/' . $template;
    $message = ob_get_clean();
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    return wp_mail($to, $subject, $message, $headers);
}
```

### Email Data Structure

```php
$email_data = array(
    'customer_name' => 'John Doe',
    'order_id' => 'ORD-123456',
    'products' => array(
        array(
            'name' => 'Product Name',
            'quantity' => 1,
            'price' => '$29.99'
        )
    ),
    'total' => '$29.99',
    'order_date' => date('F j, Y')
);
```

## Load More Functionality

### Ajax Load More Endpoint

**Action:** `load_more_posts`  
**Method:** POST  
**URL:** `admin-ajax.php`

#### Request Parameters
```javascript
{
    action: 'load_more_posts',
    query_vars: JSON.stringify(wp_query_vars),
    term_slug: 'advanced',
    taxonomy: 'courselevel',
    security: nonce_value
}
```

#### Response Structure
```json
{
    "success": true,
    "data": "<div class='post-item'>...</div>",
    "has_more": true
}
```

### JavaScript Implementation

```javascript
jQuery.ajax({
    url: mytheme_load_more_params.ajax_url,
    type: 'POST',
    data: {
        action: 'load_more_posts',
        query_vars: mytheme_load_more_params.query_vars,
        term_slug: mytheme_load_more_params.term_slug,
        taxonomy: mytheme_load_more_params.taxonomy,
        security: mytheme_load_more_params.security,
        page: current_page + 1
    },
    success: function(response) {
        if (response.success && response.data) {
            $('#posts-container').append(response.data);
            current_page++;
            
            if (!response.has_more) {
                $('#load-more-button').hide();
            }
        }
    }
});
```

## Authentication & Security

### Nonce System

All Ajax requests use WordPress nonces for security:

```php
// Create nonces
$checkout_nonce = wp_create_nonce('checkout_nonce');
$stripe_nonce = wp_create_nonce('stripe_payment_nonce');
$email_nonce = wp_create_nonce('check_email_nonce');
$loadmore_nonce = wp_create_nonce('load_more_posts');
```

### CSRF Protection

```php
// Verify nonce in Ajax handlers
if (!wp_verify_nonce($_POST['nonce'], 'checkout_nonce')) {
    wp_die('Security check failed');
}
```

### Session Security

```php
// Session token for additional security
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

### Input Sanitization

```php
// Sanitize all inputs
$email = sanitize_email($_POST['email']);
$product_id = intval($_POST['product_id']);
$text_field = sanitize_text_field($_POST['text_field']);
```

## Data Structures

### Product Meta Fields

```php
// ACF Fields for products
$product_fields = array(
    'product_type' => 'single|subscription|variation',
    'subscription_price' => 29.99,
    'subscription_free_trial_days' => 7,
    'stripe_product_id' => 'prod_1234567890',
    'stripe_price_id' => 'price_1234567890',
    'variation_product' => array(
        array(
            'product_variation' => 'Small',
            'variable_product_price' => 19.99
        ),
        array(
            'product_variation' => 'Large',
            'variable_product_price' => 29.99
        )
    )
);
```

### Course Meta Fields

```php
// ACF Fields for courses
$course_fields = array(
    'course_duration' => '2 hours',
    'course_difficulty' => 'beginner|intermediate|advanced',
    'course_video_url' => 'https://vimeo.com/123456789',
    'course_materials' => array(
        'file_url' => 'https://example.com/materials.pdf',
        'file_name' => 'Course Materials.pdf'
    )
);
```

### Recipe Meta Fields

```php
// ACF Fields for recipes
$recipe_fields = array(
    'recipe_ingredients' => 'List of ingredients...',
    'recipe_instructions' => 'Step by step instructions...',
    'prep_time' => 15,
    'cook_time' => 30,
    'servings' => 4
);
```

## Error Handling

### JavaScript Error Handling

```javascript
// Checkout error handling
function handleCheckoutError(error) {
    console.error('Checkout error:', error);
    
    if (error.response && error.response.data) {
        showErrorMessage(error.response.data.message);
    } else {
        showErrorMessage('An unexpected error occurred. Please try again.');
    }
}
```

### PHP Error Logging

```php
// Comprehensive error logging
function log_theme_error($message, $context = array()) {
    $log_message = sprintf(
        '[%s] %s - Context: %s',
        date('Y-m-d H:i:s'),
        $message,
        print_r($context, true)
    );
    
    error_log($log_message);
}
```

### Stripe Error Handling

```php
try {
    $checkout_session = \Stripe\Checkout\Session::create($session_data);
} catch (\Stripe\Exception\CardException $e) {
    // Handle card errors
    log_theme_error('Stripe card error: ' . $e->getMessage());
} catch (\Stripe\Exception\RateLimitException $e) {
    // Handle rate limiting
    log_theme_error('Stripe rate limit: ' . $e->getMessage());
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Handle invalid parameters
    log_theme_error('Stripe invalid request: ' . $e->getMessage());
} catch (Exception $e) {
    // Handle general errors
    log_theme_error('General Stripe error: ' . $e->getMessage());
}
```

## Rate Limiting

### Stripe API Limits
- 100 requests per second in live mode
- 25 requests per second in test mode

### WordPress Ajax Limits
- Implemented through nonce expiration (24 hours)
- Session-based rate limiting for cart operations

## Testing

### Test Endpoints

Use these Stripe test cards for testing:

```javascript
const testCards = {
    success: '4242424242424242',
    decline: '4000000000000002',
    requiresAuth: '4000002500003155',
    insufficientFunds: '4000000000009995'
};
```

### Test Data

```php
// Test product data
$test_product = array(
    'id' => 999,
    'name' => 'Test Product',
    'price' => 19.99,
    'stripe_price_id' => 'price_test_1234567890'
);
```

---

*This API documentation covers all major endpoints and integrations in the Hello Elementor Child theme. For additional technical details, refer to the source code and inline comments.* 