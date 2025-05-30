# Hello Elementor Child Theme Documentation

## Overview

**Hello Elementor Child** is a comprehensive WordPress child theme built on top of the Hello Elementor base theme. This theme is specifically designed for an e-commerce educational platform featuring courses, recipes, video content, and subscription-based products with Stripe integration.

**Version:** 1.0.1  
**Author:** Fabian Almiron  
**Parent Theme:** Hello Elementor  
**WordPress Version:** 4.7+  
**PHP Version:** 5.4+  
**License:** GNU General Public License v3 or later

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [File Structure](#file-structure)
4. [Custom Post Types & Taxonomies](#custom-post-types--taxonomies)
5. [E-commerce Integration](#e-commerce-integration)
6. [Stripe Payment Integration](#stripe-payment-integration)
7. [Template Files](#template-files)
8. [JavaScript Functionality](#javascript-functionality)
9. [Custom Styling](#custom-styling)
10. [Configuration](#configuration)
11. [Development](#development)
12. [Troubleshooting](#troubleshooting)

## Features

### Core Features
- **Child Theme Architecture**: Built on Hello Elementor for safe customization
- **E-commerce Integration**: Full shopping cart and checkout system
- **Stripe Payment Processing**: Secure payment handling with webhook support
- **Subscription Management**: Recurring payments with free trial support
- **Custom Post Types**: Courses, Recipes, Products with variations
- **Video Library**: Dedicated video content management
- **User Account Management**: Custom account pages and authentication
- **Ajax Load More**: Dynamic content loading for archives
- **Responsive Design**: Mobile-first approach with fluid typography
- **Email System**: Custom email templates for orders and notifications

### Educational Platform Features
- Course management with skill levels
- Recipe categorization and filtering
- Video library organization
- Product variations and bundles
- Series and collections support

## Installation

### Prerequisites
- WordPress 4.7 or higher
- PHP 5.4 or higher
- Hello Elementor parent theme installed
- SSL certificate (required for Stripe)
- Composer (for dependency management)

### Step-by-Step Installation

1. **Install Parent Theme**
   ```bash
   # Install Hello Elementor from WordPress admin or download
   ```

2. **Upload Child Theme**
   - Download/clone this child theme
   - Upload to `/wp-content/themes/` directory
   - Or install via WordPress admin: Appearance > Themes > Add New > Upload

3. **Install Dependencies**
   ```bash
   cd /path/to/theme/
   composer install
   ```

4. **Activate Theme**
   - Go to Appearance > Themes
   - Activate "Hello Elementor Child"

5. **Configure Stripe**
   - Add Stripe API keys to `wp-config.php`:
   ```php
   define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
   define('STRIPE_SECRET_KEY', 'sk_live_...');
   ```

## File Structure

```
hello-theme-child-master/
├── css/                          # Additional stylesheets
├── js/                           # JavaScript files
│   ├── checkout.js              # Stripe checkout functionality
│   └── load-more.js             # Ajax pagination
├── template-parts/              # Template components
│   ├── email-*.php              # Email templates
│   ├── shop-accordion.php       # Product display components
│   └── *.php                    # Various template parts
├── vendor/                      # Composer dependencies
├── images/                      # Theme images and assets
├── functions.php                # Main theme functions (2,787 lines)
├── style.css                    # Main stylesheet (1,679 lines)
├── page-*.php                   # Custom page templates
├── single-*.php                 # Single post templates
├── archive-*.php                # Archive templates
├── taxonomy-*.php               # Taxonomy templates
├── composer.json                # Dependencies
├── stripe-webhook.php           # Stripe webhook handler
├── sync-theme.sh               # Development sync script
└── README.md                    # This documentation
```

## Custom Post Types & Taxonomies

### Post Types
1. **Courses** (`courses`)
   - Educational content with skill levels
   - Custom fields for pricing, duration, etc.
   - Supports video attachments

2. **Recipes** (`recipes`)
   - Cooking/baking instructions
   - Categorized by type and difficulty
   - Image galleries and ingredients

3. **Products** (enhanced)
   - E-commerce products with variations
   - Stripe integration for payments
   - Subscription and one-time purchase options

### Custom Taxonomies

#### Recipe Categories (`recipecategory`)
```php
// Registered for 'recipes' post type
// Hierarchical: Yes
// Public: Yes
// Usage: Organizing recipes by type/cuisine
```

#### Course Levels (`courselevel`)
```php
// Registered for 'courses' post type  
// Hierarchical: Yes
// Public: Yes
// Usage: Beginner, Intermediate, Advanced
```

## E-commerce Integration

### Shopping Cart System
- **Session-based cart**: Stores items in PHP sessions
- **Product variations**: Support for different product options
- **Quantity management**: Add, update, remove items
- **Direct checkout links**: URL parameters for instant purchase

### Product Types
1. **Single Products**: One-time purchase items
2. **Subscription Products**: Recurring billing with Stripe
3. **Variation Products**: Multiple options (size, type, etc.)
4. **Free Trial Subscriptions**: X days free, then recurring

### Cart Management Functions
```php
// Add item to cart
$_SESSION['cart'][$product_id] = [
    'product_id' => $product_id,
    'quantity' => $quantity,
    'name' => $product_title,
    'stripe_price_id' => $stripe_price_id,
    'variation_name' => $variation_name,
    'product_type' => $product_type,
    'image_url' => $image_url
];
```

## Stripe Payment Integration

### Configuration
The theme uses Stripe for secure payment processing. Required constants in `wp-config.php`:

```php
define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
define('STRIPE_SECRET_KEY', 'sk_live_...');
```

### Webhook Configuration
- **Endpoint**: `/stripe-webhook.php`
- **Events**: `checkout.session.completed`, `invoice.payment_succeeded`
- **Security**: Webhook signature verification

### Supported Payment Types
- One-time payments
- Recurring subscriptions
- Free trials with automatic billing
- Variable pricing products

### Stripe Checkout Flow
1. Customer fills checkout form
2. JavaScript creates Stripe Checkout session
3. Customer completes payment on Stripe
4. Webhook confirms payment and fulfills order
5. Customer receives confirmation email

## Template Files

### Page Templates
- `page-checkout.php` - Custom checkout process (628 lines)
- `page-checkout-success.php` - Order confirmation
- `page-cart.php` - Shopping cart display
- `page-my-account.php` - User account management (546 lines)
- `page-shop.php` - Product listing
- `page-video-library.php` - Video content archive
- `page-products-archive.php` - Product archive with filtering

### Single Post Templates
- `single-product.php` - Individual product display (618 lines)
- `single-courses.php` - Course detail pages
- `single-recipes.php` - Recipe display

### Archive Templates
- `archive-courses.php` - Course listings
- `archive-recipes.php` - Recipe listings
- `taxonomy-courselevel.php` - Course level filtering

## JavaScript Functionality

### Checkout System (`js/checkout.js`)
**Features:**
- Stripe payment integration
- Form validation
- User registration during checkout
- Email verification
- Session management
- Error handling

**Key Functions:**
```javascript
// Initialize Stripe
const stripe = Stripe(checkoutData.stripe_pk);

// Create checkout session
createCheckoutSession(cartData, customerData);

// Handle payment success
window.location.href = '/checkout-success/';
```

### Load More Functionality (`js/load-more.js`)
**Features:**
- Ajax pagination for archives
- Maintains filtering state
- Loading states and error handling

**Implementation:**
```javascript
// Load more posts via Ajax
jQuery.ajax({
    url: mytheme_load_more_params.ajax_url,
    type: 'POST',
    data: {
        action: 'load_more_posts',
        // ... other parameters
    }
});
```

## Custom Styling

### Typography System
- **Fluid Typography**: Responsive text sizing using `clamp()`
- **Custom Fonts**: 
  - Sofia Pro Regular (body text)
  - Le Festin Regular (decorative headers)
- **Font Loading**: Local WOFF/WOFF2 files

```css
/* Fluid typography example */
body, p, span {
    font-size: clamp(0.875rem, 0.398vw + 0.772rem, 1.25rem);
}

h1.elementor-size-xxl {
    font-size: clamp(2rem, 8.687vw + 0.263rem, 7.5rem) !important;
}
```

### Design System
- **Button Styles**: Custom underline effects
- **Color Scheme**: Neutral with accent colors
- **Responsive Layout**: Mobile-first approach
- **Component Styling**: Archive filters, accordions, cards

### CSS Organization
1. **Global Styles**: Typography, colors, base elements
2. **Component Styles**: Buttons, forms, cards
3. **Layout Styles**: Headers, navigation, footer
4. **Page-Specific**: Checkout, cart, account pages
5. **Responsive**: Mobile adaptations

## Configuration

### Required WordPress Settings
```php
// wp-config.php additions
define('STRIPE_PUBLISHABLE_KEY', 'your_stripe_publishable_key');
define('STRIPE_SECRET_KEY', 'your_stripe_secret_key');

// Optional: Environment-specific settings
define('WP_ENVIRONMENT_TYPE', 'production'); // or 'development'
```

### Theme Options
The theme uses Advanced Custom Fields (ACF) for:
- Product configuration
- Course settings
- Recipe details
- Subscription parameters

### Email Configuration
Custom email templates for:
- Order confirmations
- User signup notifications
- Invoice generation

## Development

### Development Tools
- **Sync Scripts**: `sync-theme.sh`, `watch-sync.sh`, `watch-theme.sh`
- **Debug Logging**: Extensive error logging throughout
- **Local Development**: Environment detection and appropriate settings

### Code Standards
- WordPress Coding Standards
- Secure coding practices (nonces, sanitization)
- Performance optimization
- Mobile-first responsive design

### Database Schema
The theme creates custom tables and uses:
- WordPress posts/meta for content
- Sessions for cart management
- User meta for account data
- Options for theme settings

### APIs and Integrations
- **Stripe API**: Payment processing
- **WordPress REST API**: Content management
- **Custom Ajax Endpoints**: Cart and checkout operations

## Troubleshooting

### Common Issues

#### 1. Stripe Payment Failures
- **Check API Keys**: Verify publishable and secret keys
- **SSL Certificate**: Ensure HTTPS is properly configured
- **Webhook Endpoints**: Confirm webhook URL is accessible
- **Error Logs**: Check `debug.log` for Stripe errors

#### 2. Cart Issues
- **Session Problems**: Verify PHP sessions are working
- **Product Meta**: Check ACF fields and Stripe metadata
- **Price Calculations**: Review product pricing configuration

#### 3. Email Delivery
- **SMTP Configuration**: Set up proper email delivery
- **Template Issues**: Check email template file permissions
- **WordPress Mail**: Test WordPress mail functionality

#### 4. Load More Functionality
- **Ajax Errors**: Check browser console for JavaScript errors
- **Nonce Verification**: Ensure security nonces are valid
- **Query Parameters**: Verify taxonomy and post type settings

### Debug Information
Enable WordPress debugging in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

### Performance Optimization
- **Caching**: Use WordPress caching plugins
- **Image Optimization**: Compress theme images
- **Script Loading**: Conditional script enqueuing
- **Database Queries**: Optimize custom queries

### Security Considerations
- **Input Sanitization**: All user inputs are sanitized
- **Nonce Verification**: CSRF protection on all forms
- **SQL Injection Prevention**: Prepared statements used
- **XSS Protection**: Output escaping implemented

## Support and Maintenance

### Version Control
- Keep track of customizations
- Regular backups before updates
- Test updates in staging environment

### Updates
- Monitor parent theme updates
- Test child theme compatibility
- Update Stripe SDK when needed

### Documentation Updates
This documentation should be updated when:
- New features are added
- Configuration changes are made
- API integrations are modified
- Template files are updated

---

*This documentation was generated for Hello Elementor Child Theme v1.0.1*
*Last updated: Current Date*
*For technical support, contact the theme developer.* 