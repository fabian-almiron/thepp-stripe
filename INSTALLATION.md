# Installation Guide - Hello Elementor Child Theme

## Prerequisites

Before installing this theme, ensure you have:

- **WordPress**: Version 4.7 or higher
- **PHP**: Version 5.4 or higher (7.4+ recommended)
- **Hello Elementor Parent Theme**: Must be installed first
- **SSL Certificate**: Required for Stripe payment processing
- **Composer**: For PHP dependency management
- **FTP/File Manager Access**: To upload theme files

## Step 1: Install Parent Theme

### Option A: WordPress Admin Dashboard
1. Go to **Appearance > Themes > Add New**
2. Search for "Hello Elementor"
3. Click **Install** and then **Activate**

### Option B: Manual Upload
1. Download Hello Elementor from WordPress.org
2. Upload to `/wp-content/themes/hello-elementor/`
3. Activate via WordPress admin

## Step 2: Download Child Theme

### From Repository
```bash
git clone [repository-url] hello-elementor-child
```

### Or Download ZIP
1. Download the theme ZIP file
2. Extract to local computer

## Step 3: Upload Child Theme

### Option A: WordPress Admin Upload
1. Go to **Appearance > Themes > Add New**
2. Click **Upload Theme**
3. Choose the child theme ZIP file
4. Click **Install Now**

### Option B: FTP Upload
1. Upload extracted folder to `/wp-content/themes/`
2. Ensure folder is named `hello-elementor-child` or similar

### Option C: File Manager
1. Access your hosting file manager
2. Navigate to `/wp-content/themes/`
3. Upload and extract the theme files

## Step 4: Install Dependencies

### Using Composer (Recommended)
```bash
cd /path/to/wordpress/wp-content/themes/hello-elementor-child/
composer install
```

### Manual Installation (Alternative)
If Composer isn't available:
1. Download Stripe PHP library
2. Place in `/vendor/stripe/` directory
3. Ensure autoload files are present

## Step 5: Configure Stripe

### Add API Keys to wp-config.php
```php
// Add before "/* That's all, stop editing! */"
define('STRIPE_PUBLISHABLE_KEY', 'pk_test_...');  // Test key
define('STRIPE_SECRET_KEY', 'sk_test_...');       // Test key

// For production, use live keys:
// define('STRIPE_PUBLISHABLE_KEY', 'pk_live_...');
// define('STRIPE_SECRET_KEY', 'sk_live_...');
```

### Obtain Stripe Keys
1. Create account at [stripe.com](https://stripe.com)
2. Go to **Developers > API Keys**
3. Copy **Publishable key** and **Secret key**
4. Use test keys for development, live keys for production

## Step 6: Set Up Stripe Webhooks

### Create Webhook Endpoint
1. In Stripe Dashboard, go to **Developers > Webhooks**
2. Click **Add endpoint**
3. Enter endpoint URL: `https://yoursite.com/stripe-webhook.php`
4. Select events:
   - `checkout.session.completed`
   - `invoice.payment_succeeded`
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`

### Get Webhook Secret
1. Click on your created webhook
2. Copy the **Signing secret**
3. Add to wp-config.php:
```php
define('STRIPE_WEBHOOK_SECRET', 'whsec_...');
```

## Step 7: Activate Child Theme

1. Go to **Appearance > Themes**
2. Find "Hello Elementor Child"
3. Click **Activate**

## Step 8: Configure Required Plugins

### Required Plugins
- **Elementor**: Page builder (free version sufficient)
- **Advanced Custom Fields (ACF)**: For custom fields
- **WP Crontrol**: For scheduled tasks (optional)

### Recommended Plugins
- **WooCommerce**: If extending e-commerce features
- **Yoast SEO**: For search optimization
- **UpdraftPlus**: For backups

## Step 9: Create Required Pages

Create these pages in WordPress admin:

### Essential Pages
1. **Checkout** (`/checkout/`)
   - Page Template: Checkout
   
2. **Cart** (`/cart/`)
   - Page Template: Cart
   
3. **My Account** (`/my-account/`)
   - Page Template: My Account
   
4. **Checkout Success** (`/checkout-success/`)
   - Page Template: Checkout Success
   
5. **Shop** (`/shop/`)
   - Page Template: Shop

### Additional Pages
1. **Video Library** (`/video-library/`)
2. **Products Archive** (`/products/`)
3. **All Series** (`/all-series/`)
4. **Saved List** (`/saved-list/`)

## Step 10: Configure Advanced Custom Fields

### Required Field Groups

#### Product Fields
```
Group: Product Settings
Fields:
- product_type (Select: single, subscription, variation)
- subscription_price (Number)
- subscription_free_trial_days (Number)
- stripe_product_id (Text)
- stripe_price_id (Text)
- variation_product (Repeater)
  - product_variation (Text)
  - variable_product_price (Number)
```

#### Course Fields
```
Group: Course Settings
Fields:
- course_duration (Text)
- course_difficulty (Select)
- course_video_url (URL)
- course_materials (File)
```

#### Recipe Fields
```
Group: Recipe Settings
Fields:
- recipe_ingredients (Textarea)
- recipe_instructions (Textarea)
- prep_time (Number)
- cook_time (Number)
- servings (Number)
```

## Step 11: Set Up Email Configuration

### WordPress Mail
Ensure WordPress can send emails:
1. Install SMTP plugin (WP Mail SMTP recommended)
2. Configure with your email provider
3. Test email functionality

### Custom Email Templates
The theme includes custom email templates in `/template-parts/`:
- `email-invoice.php`
- `email-order-confirmation.php`
- `email-user-signup.php`

## Step 12: Development Setup (Optional)

### Local Development
```bash
# Make sync scripts executable
chmod +x sync-theme.sh
chmod +x watch-sync.sh
chmod +x watch-theme.sh

# Use for development syncing
./watch-theme.sh
```

### Debug Configuration
Add to wp-config.php for development:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
```

## Step 13: Security Configuration

### File Permissions
Set proper file permissions:
```bash
find /wp-content/themes/hello-elementor-child/ -type f -exec chmod 644 {} \;
find /wp-content/themes/hello-elementor-child/ -type d -exec chmod 755 {} \;
chmod 600 wp-config.php
```

### Security Headers
The theme automatically adds security headers, but ensure your server supports:
- SSL/HTTPS
- Modern PHP version
- Secure server configuration

## Step 14: Testing Installation

### Verify Theme Activation
1. Check frontend loads correctly
2. Verify child theme is active in admin
3. Confirm parent theme styles are inherited

### Test Core Functionality
1. **Cart System**: Add products to cart
2. **Checkout**: Test payment process (use Stripe test cards)
3. **User Registration**: Create test account
4. **Email System**: Verify emails are sent
5. **Load More**: Test ajax pagination on archives

### Test Stripe Integration
Use Stripe test cards:
- **Success**: 4242424242424242
- **Decline**: 4000000000000002
- **3D Secure**: 4000002500003155

## Troubleshooting Installation

### Common Issues

#### Theme Files Not Loading
- Check file permissions
- Verify theme folder structure
- Ensure WordPress can access files

#### Stripe Errors
- Verify API keys are correct
- Check SSL certificate is valid
- Confirm webhook endpoint is accessible

#### Email Not Sending
- Test WordPress mail function
- Configure SMTP plugin
- Check server email configuration

#### Cart Not Working
- Verify PHP sessions are enabled
- Check session directory permissions
- Review error logs for session issues

### Getting Help
1. Check error logs in `/wp-content/debug.log`
2. Enable debug mode for detailed errors
3. Verify all prerequisites are met
4. Contact theme developer if issues persist

## Next Steps

After successful installation:
1. Review [README.md](README.md) for full documentation
2. Configure theme settings and customizations
3. Set up content (products, courses, recipes)
4. Test all functionality thoroughly
5. Set up regular backups
6. Monitor performance and security

---

*Installation complete! Your Hello Elementor Child theme is now ready for use.* 