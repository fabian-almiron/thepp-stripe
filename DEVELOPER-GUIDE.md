# Developer Guide - Hello Elementor Child Theme

## Overview

This guide is for developers who need to work with, modify, or extend the Hello Elementor Child theme. It covers development setup, code architecture, best practices, and common customization scenarios.

## Table of Contents

1. [Development Environment Setup](#development-environment-setup)
2. [Code Architecture](#code-architecture)
3. [Key Files and Functions](#key-files-and-functions)
4. [Development Workflows](#development-workflows)
5. [Customization Guide](#customization-guide)
6. [Testing and Debugging](#testing-and-debugging)
7. [Performance Optimization](#performance-optimization)
8. [Security Best Practices](#security-best-practices)
9. [Deployment Process](#deployment-process)

## Development Environment Setup

### Local Development Prerequisites

```bash
# Required software
- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.2+
- WordPress 5.0+
- Composer
- Node.js (for build tools if needed)
- Git
```

### Development Stack Options

#### Option 1: Local WP Environment
```bash
# Using Local by Flywheel, XAMPP, or similar
# Install WordPress locally
# Clone theme to wp-content/themes/
```

#### Option 2: Docker Setup
```bash
# Using WordPress Docker container
docker-compose up -d
# Mount theme directory
```

#### Option 3: Remote Development
```bash
# Use staging server with Git deployment
# Set up SSH access and file synchronization
```

### Theme Development Setup

```bash
# Clone repository
git clone [repository-url] hello-elementor-child
cd hello-elementor-child

# Install PHP dependencies
composer install

# Make development scripts executable
chmod +x sync-theme.sh
chmod +x watch-sync.sh
chmod +x watch-theme.sh

# Set up environment variables
cp .env.example .env  # If exists
```

### Development Configuration

#### wp-config.php Development Settings
```php
// Development-specific constants
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);

// Stripe test keys
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');

// Development environment flag
define('WP_ENVIRONMENT_TYPE', 'development');
```

## Code Architecture

### File Organization

```
hello-elementor-child/
├── functions.php                 # Core theme functions (2,787 lines)
├── style.css                     # Main stylesheet (1,679 lines)
├── page-templates/               # Custom page templates
│   ├── page-checkout.php        # Stripe checkout integration
│   ├── page-cart.php            # Shopping cart
│   ├── page-my-account.php      # User account management
│   └── ...
├── template-parts/               # Reusable template components
│   ├── email-templates/         # Email template system
│   ├── shop-components/         # E-commerce components
│   └── content-blocks/          # Content display blocks
├── js/                          # JavaScript functionality
│   ├── checkout.js              # Stripe payment processing
│   └── load-more.js             # Ajax pagination
├── css/                         # Additional stylesheets
├── vendor/                      # Composer dependencies
└── assets/                      # Images, fonts, etc.
```

### Core Components

#### 1. E-commerce System
```php
// Cart management (Session-based)
$_SESSION['cart'] = [
    'product_id' => [
        'product_id' => int,
        'quantity' => int,
        'stripe_price_id' => string,
        'product_type' => 'single|subscription|variation'
    ]
];
```

#### 2. Stripe Integration
```php
// Payment processing workflow
1. Cart → Checkout Form
2. JavaScript → Create Stripe Session
3. Stripe → Process Payment
4. Webhook → Fulfill Order
5. Email → Confirmation
```

#### 3. Custom Post Types
```php
// Post types with custom taxonomies
- courses (with courselevel taxonomy)
- recipes (with recipecategory taxonomy)
- products (enhanced with Stripe metadata)
```

#### 4. Ajax System
```php
// Key Ajax endpoints
- load_more_posts (pagination)
- create_stripe_checkout_session (payments)
- register_user_checkout (user registration)
- check_user_email (email verification)
```

## Key Files and Functions

### Primary Functions (functions.php)

#### Core Theme Functions
```php
// Theme setup and enqueue
function checkout_enqueue_scripts() {
    // Handles CSS/JS loading with conditional logic
    // Lines: 23-65
}

// User authentication helpers
function er_logged_in_filter($classes) {
    // Adds body classes for logged-in state
    // Lines: 73-86
}
```

#### Custom Post Types and Taxonomies
```php
// Recipe categories
function custom_recipe_taxonomy() {
    // Registers recipecategory taxonomy
    // Lines: 88-116
}

// Course levels
function custom_course_taxonomy() {
    // Registers courselevel taxonomy  
    // Lines: 119-147
}
```

#### Ajax Load More System
```php
// Enqueue load more scripts
function mytheme_enqueue_scripts() {
    // Lines: 151-182
}

// Ajax handler
function mytheme_load_more_posts() {
    // Lines: 184-268
}
```

#### Cart and Checkout Functions
```php
// User registration during checkout
function register_user_checkout() {
    // Lines: 421-509
}

// Email verification
function check_user_email() {
    // Lines: 511-530
}

// Stripe checkout session creation
function create_stripe_checkout_session() {
    // Lines: 532-789
}
```

### Template Files Deep Dive

#### page-checkout.php (628 lines)
```php
Key Features:
- Direct checkout link handling (?checkout_product_id=123)
- Session cart management
- Stripe integration setup
- User authentication flow
- Security headers and nonce generation
- Trial period calculations
- Debug logging throughout
```

#### page-my-account.php (546 lines)
```php
Key Features:
- User subscription management
- Order history display
- Account settings
- Saved content lists
- Integration with Stripe customer portal
```

#### single-product.php (618 lines)
```php
Key Features:
- Product variation handling
- Add to cart functionality
- Stripe metadata display
- Related products
- Custom product fields (ACF)
```

### JavaScript Architecture

#### checkout.js (291 lines)
```javascript
Key Components:
- Stripe.js integration
- Form validation
- User registration flow
- Error handling
- Payment processing
- Session management
```

#### load-more.js (77 lines)
```javascript
Key Components:
- Ajax pagination
- Loading states
- Filter maintenance
- Error handling
```

## Development Workflows

### Adding New Features

#### 1. New Custom Post Type
```php
// In functions.php
function register_custom_post_type() {
    $args = array(
        'public' => true,
        'label' => 'Custom Items',
        'supports' => array('title', 'editor', 'thumbnail'),
        'show_in_rest' => true,
    );
    register_post_type('custom_item', $args);
}
add_action('init', 'register_custom_post_type');
```

#### 2. New Ajax Endpoint
```php
// Ajax handler function
function handle_custom_ajax() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['nonce'], 'custom_nonce')) {
        wp_die('Security check failed');
    }
    
    // Process request
    $result = process_custom_data($_POST);
    
    // Return response
    wp_send_json_success($result);
}
add_action('wp_ajax_custom_action', 'handle_custom_ajax');
add_action('wp_ajax_nopriv_custom_action', 'handle_custom_ajax');
```

#### 3. New Template File
```php
// Create template file: page-custom.php
<?php
/**
 * Template Name: Custom Page
 */

get_header();

// Custom template logic
while (have_posts()) {
    the_post();
    // Custom content display
}

get_footer();
?>
```

### Stripe Integration Extensions

#### Adding New Product Types
```php
// Extend product type options
function add_custom_product_type($types) {
    $types['bundle'] = 'Product Bundle';
    $types['rental'] = 'Rental Item';
    return $types;
}
add_filter('product_type_options', 'add_custom_product_type');
```

#### Custom Stripe Webhooks
```php
// In stripe-webhook.php, add new event handlers
switch ($event->type) {
    case 'customer.subscription.trial_will_end':
        handle_trial_ending($event->data->object);
        break;
    case 'invoice.payment_failed':
        handle_payment_failure($event->data->object);
        break;
}
```

### Custom Field Extensions

#### Adding ACF Fields Programmatically
```php
function add_custom_product_fields() {
    if (function_exists('acf_add_local_field_group')) {
        acf_add_local_field_group(array(
            'key' => 'group_custom_product',
            'title' => 'Custom Product Fields',
            'fields' => array(
                array(
                    'key' => 'field_custom_price',
                    'label' => 'Custom Price',
                    'name' => 'custom_price',
                    'type' => 'number',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ),
                ),
            ),
        ));
    }
}
add_action('acf/init', 'add_custom_product_fields');
```

## Customization Guide

### Styling Customizations

#### Override Theme Styles
```css
/* In style.css, add custom styles after existing styles */

/* Custom component styles */
.custom-component {
    background: #f5f5f5;
    padding: 20px;
    border-radius: 8px;
}

/* Override existing styles */
.elementor-button {
    background-color: #your-brand-color !important;
}

/* Responsive customizations */
@media (max-width: 768px) {
    .custom-component {
        padding: 10px;
    }
}
```

#### Add Custom CSS File
```php
// In functions.php
function enqueue_custom_styles() {
    wp_enqueue_style(
        'custom-styles',
        get_stylesheet_directory_uri() . '/css/custom.css',
        array('hello-elementor-child-style'),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'enqueue_custom_styles');
```

### Functionality Extensions

#### Custom Shortcodes
```php
function custom_product_grid_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'limit' => 12,
        'columns' => 3,
    ), $atts);
    
    // Query products
    $products = get_posts(array(
        'post_type' => 'product',
        'posts_per_page' => $atts['limit'],
        'meta_query' => array(
            array(
                'key' => 'product_category',
                'value' => $atts['category'],
                'compare' => 'LIKE'
            )
        )
    ));
    
    // Build output
    ob_start();
    ?>
    <div class="product-grid columns-<?php echo $atts['columns']; ?>">
        <?php foreach ($products as $product): ?>
            <div class="product-item">
                <h3><?php echo $product->post_title; ?></h3>
                <!-- Product content -->
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('product_grid', 'custom_product_grid_shortcode');
```

#### Custom Widgets
```php
class Custom_Product_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'custom_product_widget',
            'Custom Product Widget',
            array('description' => 'Display featured products')
        );
    }
    
    public function widget($args, $instance) {
        // Widget output
        echo $args['before_widget'];
        // Custom widget content
        echo $args['after_widget'];
    }
    
    public function form($instance) {
        // Widget admin form
    }
    
    public function update($new_instance, $old_instance) {
        // Widget update logic
        return $new_instance;
    }
}

function register_custom_widgets() {
    register_widget('Custom_Product_Widget');
}
add_action('widgets_init', 'register_custom_widgets');
```

## Testing and Debugging

### Debug Configuration

#### Enable Comprehensive Logging
```php
// In wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SAVEQUERIES', true);
define('SCRIPT_DEBUG', true);

// Custom debug function
function debug_log($message, $context = array()) {
    if (WP_DEBUG_LOG) {
        $log_entry = sprintf(
            '[%s] %s %s',
            date('Y-m-d H:i:s'),
            $message,
            !empty($context) ? '- Context: ' . print_r($context, true) : ''
        );
        error_log($log_entry);
    }
}
```

#### Theme-Specific Debug Tools
```php
// Add debug info to pages (development only)
function add_debug_info() {
    if (WP_DEBUG && current_user_can('manage_options')) {
        global $wp_query;
        echo '<!-- Debug Info:';
        echo 'Template: ' . get_page_template();
        echo 'Post Type: ' . get_post_type();
        echo 'Query Vars: ' . print_r($wp_query->query_vars, true);
        echo '-->';
    }
}
add_action('wp_footer', 'add_debug_info');
```

### Testing Stripe Integration

#### Test Environment Setup
```php
// Use test API keys
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');
define('STRIPE_SECRET_KEY', 'sk_test_...');

// Test webhook with ngrok or similar
// ngrok http 80
// Use ngrok URL for webhook endpoint
```

#### Test Card Numbers
```javascript
const testCards = {
    visa: '4242424242424242',
    visaDebit: '4000056655665556',
    mastercard: '5555555555554444',
    amex: '378282246310005',
    decline: '4000000000000002',
    insufficientFunds: '4000000000009995',
    expiredCard: '4000000000000069',
    incorrectCVC: '4000000000000127',
    processingError: '4000000000000119',
    requiresAuth: '4000002500003155'
};
```

#### Automated Testing
```php
// PHPUnit test example
class StripeIntegrationTest extends WP_UnitTestCase {
    public function test_create_checkout_session() {
        // Mock cart data
        $cart_data = array(
            array(
                'product_id' => 123,
                'quantity' => 1,
                'stripe_price_id' => 'price_test_123'
            )
        );
        
        // Test session creation
        $session = create_test_checkout_session($cart_data);
        $this->assertNotEmpty($session->id);
        $this->assertEquals('payment', $session->mode);
    }
}
```

### Performance Testing

#### Query Optimization
```php
// Monitor database queries
function log_slow_queries() {
    global $wpdb;
    if ($wpdb->num_queries > 50) {
        error_log('High query count: ' . $wpdb->num_queries);
    }
    
    if (defined('SAVEQUERIES') && SAVEQUERIES) {
        foreach ($wpdb->queries as $query) {
            if ($query[1] > 0.01) { // Queries taking > 10ms
                error_log('Slow query: ' . $query[0] . ' (' . $query[1] . 's)');
            }
        }
    }
}
add_action('wp_footer', 'log_slow_queries');
```

#### Load Testing
```bash
# Use tools like Apache Bench or wrk
ab -n 1000 -c 10 https://yoursite.com/
```

## Performance Optimization

### Caching Strategy

#### Object Caching
```php
function get_cached_products($category = '') {
    $cache_key = 'products_' . md5($category);
    $products = wp_cache_get($cache_key);
    
    if (false === $products) {
        $products = get_posts(array(
            'post_type' => 'product',
            'meta_key' => 'category',
            'meta_value' => $category
        ));
        wp_cache_set($cache_key, $products, '', 3600); // 1 hour
    }
    
    return $products;
}
```

#### Transient Caching
```php
function get_stripe_products() {
    $transient_key = 'stripe_products_list';
    $products = get_transient($transient_key);
    
    if (false === $products) {
        // Fetch from Stripe API
        $products = \Stripe\Product::all();
        set_transient($transient_key, $products, DAY_IN_SECONDS);
    }
    
    return $products;
}
```

### Asset Optimization

#### Conditional Loading
```php
function optimize_script_loading() {
    // Only load Stripe on checkout pages
    if (!is_page('checkout')) {
        wp_dequeue_script('stripe-js');
    }
    
    // Only load load-more on archive pages
    if (!is_archive() && !is_tax()) {
        wp_dequeue_script('mytheme-load-more');
    }
}
add_action('wp_enqueue_scripts', 'optimize_script_loading', 100);
```

#### Image Optimization
```php
// Add WebP support
function add_webp_support($mimes) {
    $mimes['webp'] = 'image/webp';
    return $mimes;
}
add_filter('upload_mimes', 'add_webp_support');

// Lazy loading for custom content
function add_lazy_loading($content) {
    return str_replace('<img ', '<img loading="lazy" ', $content);
}
add_filter('the_content', 'add_lazy_loading');
```

## Security Best Practices

### Input Validation and Sanitization

```php
// Comprehensive input handling
function sanitize_checkout_data($data) {
    return array(
        'email' => sanitize_email($data['email']),
        'first_name' => sanitize_text_field($data['first_name']),
        'last_name' => sanitize_text_field($data['last_name']),
        'product_id' => intval($data['product_id']),
        'quantity' => max(1, intval($data['quantity']))
    );
}
```

### Nonce Security

```php
// Generate and verify nonces
function create_checkout_nonce() {
    return wp_create_nonce('checkout_action_' . get_current_user_id());
}

function verify_checkout_nonce($nonce) {
    return wp_verify_nonce($nonce, 'checkout_action_' . get_current_user_id());
}
```

### SQL Injection Prevention

```php
// Always use prepared statements
function get_user_orders($user_id) {
    global $wpdb;
    
    $orders = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} 
             WHERE post_type = 'order' 
             AND post_author = %d 
             ORDER BY post_date DESC",
            $user_id
        )
    );
    
    return $orders;
}
```

### File Security

```php
// Validate file uploads
function validate_file_upload($file) {
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'pdf');
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    
    if (!in_array(strtolower($file_extension), $allowed_types)) {
        return new WP_Error('invalid_file', 'File type not allowed');
    }
    
    return true;
}
```

## Deployment Process

### Pre-Deployment Checklist

```bash
# 1. Run tests
phpunit tests/

# 2. Check for debug statements
grep -r "error_log\|var_dump\|print_r" . --exclude-dir=vendor

# 3. Validate PHP syntax
find . -name "*.php" -exec php -l {} \;

# 4. Check file permissions
find . -type f -name "*.php" ! -perm 644

# 5. Verify Stripe keys are set correctly
# 6. Test email functionality
# 7. Verify SSL certificate
# 8. Check webhook endpoints
```

### Production Configuration

```php
// Production wp-config.php settings
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', false);
define('DISALLOW_FILE_EDIT', true);

// Production Stripe keys
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
define('STRIPE_SECRET_KEY', 'sk_live_...');
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');
```

### Deployment Scripts

```bash
#!/bin/bash
# deploy.sh

# 1. Backup current theme
cp -r /path/to/theme /path/to/backup/

# 2. Upload new files
rsync -av --exclude='.git' --exclude='node_modules' \
      local/theme/ server:/path/to/theme/

# 3. Update dependencies
cd /path/to/theme && composer install --no-dev

# 4. Clear caches
wp cache flush

# 5. Verify deployment
curl -s https://yoursite.com/ | grep "Hello Elementor Child"
```

### Monitoring and Maintenance

```php
// Add custom health checks
function theme_health_check() {
    $checks = array();
    
    // Check Stripe connectivity
    try {
        \Stripe\Account::retrieve();
        $checks['stripe'] = 'OK';
    } catch (Exception $e) {
        $checks['stripe'] = 'ERROR: ' . $e->getMessage();
    }
    
    // Check required pages exist
    $required_pages = array('checkout', 'cart', 'my-account');
    foreach ($required_pages as $page) {
        $page_obj = get_page_by_path($page);
        $checks['page_' . $page] = $page_obj ? 'OK' : 'MISSING';
    }
    
    return $checks;
}
```

---

*This developer guide provides comprehensive information for working with the Hello Elementor Child theme. For specific implementation questions, refer to the inline code comments and existing examples in the theme files.* 