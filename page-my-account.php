<?php
/*
Template Name: My Account
*/

// Ensure user is logged in
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(get_permalink())); // Redirect back to this page after login
    exit;
}

// Handle Change Email form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_email') {
    if (isset($_POST['_wpnonce_change_email']) && wp_verify_nonce($_POST['_wpnonce_change_email'], 'change_email_nonce_action')) {
        $current_user = wp_get_current_user();
        $new_email = isset($_POST['new_email']) ? sanitize_email($_POST['new_email']) : '';
        $current_email = $current_user->user_email;

        if (empty($new_email) || !is_email($new_email)) {
            set_transient('my_account_notice_transient', ['message' => 'Please enter a valid email address.', 'type' => 'error']);
        } elseif ($new_email === $current_email) {
            set_transient('my_account_notice_transient', ['message' => 'The new email address cannot be the same as your current one.', 'type' => 'error']);
        } elseif (email_exists($new_email)) {
            set_transient('my_account_notice_transient', ['message' => 'This email address is already in use by another account.', 'type' => 'error']);
        } else {
            // Update WordPress email
            $user_id = $current_user->ID;
            $result = wp_update_user(['ID' => $user_id, 'user_email' => $new_email]);

            if (is_wp_error($result)) {
                set_transient('my_account_notice_transient', ['message' => 'Error updating email address: ' . esc_html($result->get_error_message()), 'type' => 'error']);
            } else {
                // Attempt to update Stripe customer email
                $stripe_sdk_path = get_stylesheet_directory() . '/vendor/autoload.php';
                if (file_exists($stripe_sdk_path)) {
                    require_once $stripe_sdk_path;
                    if (defined('STRIPE_SECRET_KEY')) {
                        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);
                        try {
                            // First, find the customer by their OLD email or a stored customer ID
                            // For simplicity, we'll search by the old email. A stored ID is more robust.
                            $customers = \Stripe\Customer::search([
                                'query' => 'email:"' . $current_email . '"',
                                'limit' => 1
                            ]);
                            if (!empty($customers->data)) {
                                $customer_id = $customers->data[0]->id;
                                \Stripe\Customer::update($customer_id, ['email' => $new_email]);
                                set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated successfully. Your Stripe billing email has also been updated.', 'type' => 'success']);
                            } else {
                                set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated in WordPress, but we could not find a corresponding Stripe customer to update. Please contact support if you have an active subscription.', 'type' => 'notice']);
                            }
                        } catch (\Stripe\Exception\ApiErrorException $e) {
                            set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated in WordPress, but there was an error updating your Stripe billing email: ' . esc_html($e->getMessage()) . '. Please contact support.', 'type' => 'error']);
                        } catch (Exception $e) {
                            set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated in WordPress, but an unexpected error occurred while updating Stripe: ' . esc_html($e->getMessage()) . '. Please contact support.', 'type' => 'error']);
                        }
                    } else {
                         set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated in WordPress, but Stripe configuration is missing. Contact support.', 'type' => 'error']);
                    }
                } else {
                    set_transient('my_account_notice_transient', ['message' => 'Your email address has been updated in WordPress, but payment library not found. Contact support.', 'type' => 'error']);
                }
            }
        }
    } else {
        set_transient('my_account_notice_transient', ['message' => 'Security check failed. Please try again.', 'type' => 'error']);
    }
    wp_redirect(home_url('/my-account/'));
    exit;
}

// Handle Change Password form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'change_password') {
    if (isset($_POST['_wpnonce_change_password']) && wp_verify_nonce($_POST['_wpnonce_change_password'], 'change_password_nonce_action')) {
        $current_user = wp_get_current_user();
        $new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

        if (empty($new_password) || empty($confirm_password)) {
            set_transient('my_account_notice_transient', ['message' => 'Please enter and confirm your new password.', 'type' => 'error']);
        } elseif ($new_password !== $confirm_password) {
            set_transient('my_account_notice_transient', ['message' => 'The new passwords do not match.', 'type' => 'error']);
        } else {
            // WordPress will handle password strength requirements if enforced elsewhere
            wp_set_password($new_password, $current_user->ID);
            set_transient('my_account_notice_transient', ['message' => 'Your password has been updated successfully.', 'type' => 'success']);
            // Optionally, log the user out and force re-login for security
            // wp_logout();
            // wp_redirect(wp_login_url(home_url('/my-account/')));
            // exit;
        }
    } else {
        set_transient('my_account_notice_transient', ['message' => 'Security check failed. Please try again.', 'type' => 'error']);
    }
    wp_redirect(home_url('/my-account/'));
    exit;
}

// Handle Stripe Billing Portal form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'manage_stripe_billing') {
    if (isset($_POST['_wpnonce_stripe_billing']) && wp_verify_nonce($_POST['_wpnonce_stripe_billing'], 'stripe_billing_portal_nonce_action')) {
        
        $stripe_sdk_path = get_stylesheet_directory() . '/vendor/autoload.php';
        if (file_exists($stripe_sdk_path)) {
            require_once $stripe_sdk_path;
        } else {
            // Store error and redirect back
            set_transient('my_account_notice_transient', ['message' => 'Error: Payment processing library not found. Please contact support.', 'type' => 'error']);
            wp_redirect(home_url('/my-account/'));
            exit;
        }

        if (!defined('STRIPE_SECRET_KEY')) {
            set_transient('my_account_notice_transient', ['message' => 'Error: Stripe configuration is missing. Please contact support.', 'type' => 'error']);
            wp_redirect(home_url('/my-account/'));
            exit;
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;

        try {
            $customers = \Stripe\Customer::search([
                'query' => 'email:"' . $user_email . '"', // Corrected quoting
                'limit' => 1
            ]);

            if (!empty($customers->data)) {
                $customer_id = $customers->data[0]->id;
                $return_url = home_url('/my-account/');

                $session = \Stripe\BillingPortal\Session::create([
                    'customer' => $customer_id,
                    'return_url' => $return_url,
                ]);

                wp_redirect($session->url);
                exit;
            } else {
                set_transient('my_account_notice_transient', ['message' => 'We could not find a billing account associated with your email address (' . esc_html($user_email) . '). If you believe this is an error, please contact support.', 'type' => 'error']);
            }
        } catch (\Stripe\Exception\ApiErrorException $e) {
            set_transient('my_account_notice_transient', ['message' => 'Stripe API Error: ' . esc_html($e->getMessage()), 'type' => 'error']);
        } catch (Exception $e) {
            set_transient('my_account_notice_transient', ['message' => 'An unexpected error occurred: ' . esc_html($e->getMessage()), 'type' => 'error']);
        }
        // Redirect back to my-account if we fall through (e.g. customer not found or error)
        wp_redirect(home_url('/my-account/'));
        exit;
    } else {
        // Nonce verification failed
        set_transient('my_account_notice_transient', ['message' => 'Security check failed. Please try again.', 'type' => 'error']);
        wp_redirect(home_url('/my-account/'));
        exit;
    }
}

// Handle Resubscribe form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resubscribe_peony_academy') {
    if (isset($_POST['_wpnonce_stripe_resubscribe']) && wp_verify_nonce($_POST['_wpnonce_stripe_resubscribe'], 'stripe_resubscribe_nonce_action')) {
        
        $stripe_sdk_path = get_stylesheet_directory() . '/vendor/autoload.php';
        if (file_exists($stripe_sdk_path)) {
            require_once $stripe_sdk_path;
        } else {
            set_transient('my_account_notice_transient', ['message' => 'Error: Payment processing library not found. Please contact support.', 'type' => 'error']);
            wp_redirect(home_url('/my-account/'));
            exit;
        }

        if (!defined('STRIPE_SECRET_KEY')) {
            set_transient('my_account_notice_transient', ['message' => 'Error: Stripe configuration is missing. Please contact support.', 'type' => 'error']);
            wp_redirect(home_url('/my-account/'));
            exit;
        }

        \Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        //$academy_price_id = 'price_1RO0cMFSIMTXJoJ29y1oJQ5b'; // The Price ID For Testing
        $academy_price_id = 'price_1RLgiKFSIMTXJoJ2u8SRItUV'; // The Price ID For Live
        try {
            // Check if user already exists as a Stripe customer
            $customers = \Stripe\Customer::search([
                'query' => 'email:"' . $user_email . '"', // Corrected quoting
                'limit' => 1
            ]);
            $customer_id = null;
            if (!empty($customers->data)) {
                $customer_id = $customers->data[0]->id;
            }

            $checkout_session_params = [
                'payment_method_types' => ['card'],
                'line_items' => [
                    [
                        'price' => $academy_price_id,
                        'quantity' => 1,
                    ]
                ],
                'mode' => 'subscription',
                'success_url' => home_url('/my-account/?resubscribe_success=true'), // Or a dedicated success page
                'cancel_url' => home_url('/my-account/?resubscribe_cancelled=true'),
                'metadata' => [
                    'user_id' => $current_user->ID,
                    'wordpress_user_id' => $current_user->ID,
                    'is_resubscription' => 'true',
                    'contains_subscription' => 'true' // Ensure webhook identifies this as a subscription
                ],
                // No trial for resubscription
            ];
            
            if ($customer_id) {
                $checkout_session_params['customer'] = $customer_id;
            } else {
                // If customer doesn't exist, Stripe Checkout will create one or prompt for email
                $checkout_session_params['customer_email'] = $user_email;
            }

            $checkout_session = \Stripe\Checkout\Session::create($checkout_session_params);

            wp_redirect($checkout_session->url);
            exit;

        } catch (\Stripe\Exception\ApiErrorException $e) {
            set_transient('my_account_notice_transient', ['message' => 'Stripe API Error: ' . esc_html($e->getMessage()), 'type' => 'error']);
        } catch (Exception $e) {
            set_transient('my_account_notice_transient', ['message' => 'An unexpected error occurred: ' . esc_html($e->getMessage()), 'type' => 'error']);
        }
        wp_redirect(home_url('/my-account/'));
        exit;
    } else {
        set_transient('my_account_notice_transient', ['message' => 'Security check failed. Please try again.', 'type' => 'error']);
        wp_redirect(home_url('/my-account/'));
        exit;
    }
}

get_header();
?>

<div class="container padder my-account-page-container">
    <div class="my-account-content">
        
        <?php
        // Display notices stored in transient
        $notice = get_transient('my_account_notice_transient');
        if ($notice && is_array($notice) && !empty($notice['message'])) {
            $notice_class = $notice['type'] === 'error' ? 'woocommerce-error' : 'woocommerce-message';
            echo '<div class="' . esc_attr($notice_class) . '" role="alert">' . wp_kses_post($notice['message']) . '</div>';
            delete_transient('my_account_notice_transient');
        }
        ?>

        <div class="my-account-welcome entry-content"> <?php // Added entry-content for standard WP styling if needed ?>
             <h2>My Account</h2>
             <?php $current_user = wp_get_current_user(); ?>
             <p>
                Hello, <strong><?php echo esc_html($current_user->first_name ? $current_user->first_name : $current_user->user_login); ?></strong>
                (not <?php echo esc_html($current_user->display_name ? $current_user->display_name : $current_user->user_login); ?>? 
                <a href="<?php echo esc_url(wp_logout_url(home_url('/my-account/'))); ?>">Log out</a>)
            </p>
            <p>From your account dashboard you can view your recent orders, manage your subscription, update your shipping and billing addresses, and edit your password and account details.</p>
        </div>
        
        <hr class="my-account-divider">

        <?php 
        $current_user_obj = wp_get_current_user();
        if ($current_user_obj && 
            !in_array('administrator', (array) $current_user_obj->roles) && 
            !in_array('subscriber', (array) $current_user_obj->roles)) :

            $is_reactivating = in_array('subscriber_inactive', (array) $current_user_obj->roles);
        ?>
        <section class="stripe-subscribe-section"> <?php // Using a slightly more general class name, or keep stripe-resubscribe-section ?>
            <?php if ($is_reactivating) : ?>
                <h3>Reactivate Your Subscription</h3>
                <p>Your Piped Peony Academy subscription is currently inactive. Click the button below to reactivate it and regain access to all premium content.</p>
                <?php $button_text = "Resubscribe to The Piped Peony Academy"; ?>
            <?php else: // For other non-subscribers like 'customer', 'pending' (who are verified and logged in) ?>
                <h3>Subscribe to The Piped Peony Academy</h3>
                <p>Gain access to all premium content by subscribing to The Piped Peony Academy.</p>
                <?php $button_text = "Subscribe to The Piped Peony Academy"; ?>
            <?php endif; ?>
            <form action="<?php echo esc_url(get_permalink()); ?>" method="POST" class="stripe-subscribe-form">
                <input type="hidden" name="action" value="resubscribe_peony_academy" />
                <?php wp_nonce_field('stripe_resubscribe_nonce_action', '_wpnonce_stripe_resubscribe'); ?>
                <button type="submit" class="button btn-primary"><?php echo esc_html($button_text); ?></button>
            </form>
        </section>
        <hr class="my-account-divider">
        <?php endif; ?>

        <div class="my-account-tabs">
            <ul class="nav nav-tabs">
                <li class="nav-item"><a class="nav-link active" href="#manage-billing-tab" data-bs-toggle="tab">Manage Subscription & Billing</a></li>
                <li class="nav-item"><a class="nav-link" href="#account-details-tab" data-bs-toggle="tab">Account Details</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="manage-billing-tab">
                    <section class="stripe-billing-portal-section">
                        <h3>Manage Subscription & Billing</h3>
                        <p>Click the button below to securely manage your subscription, update payment methods, and view your billing history with Stripe.</p>
                        <form action="<?php echo esc_url(get_permalink()); ?>" method="POST" class="stripe-billing-form">
                            <input type="hidden" name="action" value="manage_stripe_billing" />
                            <?php wp_nonce_field('stripe_billing_portal_nonce_action', '_wpnonce_stripe_billing'); ?>
                            <button type="submit" class="button btn-primary">Access Billing Portal</button>
                        </form>
                    </section>
                </div>
                <div class="tab-pane" id="account-details-tab">
                    <section class="account-details-section">
                        <h3>Account Details</h3>
                        <div class="account-details-forms">
                            <div class="account-form-wrapper">
                                <h4>Change Email Address</h4>
                                <form action="<?php echo esc_url(get_permalink()); ?>" method="POST" class="account-detail-form">
                                    <input type="hidden" name="action" value="change_email" />
                                    <?php wp_nonce_field('change_email_nonce_action', '_wpnonce_change_email'); ?>
                                    <p>
                                        <label for="current_email">Current Email Address</label>
                                        <input type="email" id="current_email" name="current_email" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" disabled />
                                    </p>
                                    <p>
                                        <label for="new_email">New Email Address</label>
                                        <input type="email" id="new_email" name="new_email" required />
                                    </p>
                                    <p>
                                        <button type="submit" class="button btn-primary">Update Email</button>
                                    </p>
                                </form>
                            </div>

                            <div class="account-form-wrapper">
                                <h4>Change Password</h4>
                                <form action="<?php echo esc_url(get_permalink()); ?>" method="POST" class="account-detail-form">
                                    <input type="hidden" name="action" value="change_password" />
                                    <?php wp_nonce_field('change_password_nonce_action', '_wpnonce_change_password'); ?>
                                    <p>
                                        <label for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" required />
                                    </p>
                                    <p>
                                        <label for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required />
                                    </p>
                                    <p>
                                        <button type="submit" class="button btn-primary">Update Password</button>
                                    </p>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        
    </div>
</div>

<?php
get_footer();
?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.my-account-tabs .nav-tabs .nav-link');
    const tabPanes = document.querySelectorAll('.my-account-tabs .tab-content .tab-pane');

    // Function to activate a tab
    function activateTab(targetTabId) {
        tabs.forEach(tab => {
            if (tab.getAttribute('href') === targetTabId) {
                tab.classList.add('active');
                tab.setAttribute('aria-selected', 'true');
            } else {
                tab.classList.remove('active');
                tab.setAttribute('aria-selected', 'false');
            }
        });

        tabPanes.forEach(pane => {
            if ('#' + pane.getAttribute('id') === targetTabId) {
                pane.classList.add('active', 'show');
            } else {
                pane.classList.remove('active', 'show');
            }
        });
    }

    // Event listeners for tab clicks
    tabs.forEach(tab => {
        tab.addEventListener('click', function (event) {
            event.preventDefault();
            const targetTabId = this.getAttribute('href');
            activateTab(targetTabId);

            // Store the active tab in localStorage
            localStorage.setItem('activeMyAccountTab', targetTabId);
        });
    });

    // Check for active tab in localStorage on page load
    const activeTab = localStorage.getItem('activeMyAccountTab');
    if (activeTab) {
        activateTab(activeTab);
    } else {
        // Default to the first tab if nothing is stored
        if (tabs.length > 0) {
            activateTab(tabs[0].getAttribute('href'));
        }
    }
});
</script>
<style>

.container {
    max-width: 1255px;
    margin: auto;
    padding: 50px 15px;
}
/* Basic Tab Styling */
.my-account-tabs .nav-tabs {
    list-style: none;
    padding-left: 0;
    margin-bottom: 0;
    border-bottom: 1px solid #ddd;
    display: flex;
    margin-left: 0;
    align-items: center;
}


.my-account-tabs .nav-tabs .nav-item {
    margin-bottom: -1px; /* Overlap border */
}

.my-account-tabs .nav-tabs .nav-link {
    display: block;
    border: 1px solid transparent;

}



.my-account-tabs .nav-tabs .nav-link:hover, .my-account-tabs .nav-tabs .nav-link:focus {
    border-color: #e9ecef #e9ecef #ddd;
    text-decoration: none;
    padding: 1.3rem 2rem !important;
}

.my-account-tabs .nav-tabs .nav-link.active {
    color: #495057;
    background-color: #fff;
    border-color: #ddd #ddd #fff;
}

.my-account-tabs .tab-content > .tab-pane {
    display: none;
}


.my-account-tabs .tab-content > .tab-pane.active {
    display: block;
    padding: 15px;
    border: 1px solid #ddd;
    border-top: none;
    border-bottom-left-radius: .25rem;
    border-bottom-right-radius: .25rem;
}

/* Simple layout for forms within tabs */
.account-details-forms {
    display: flex;
    flex-wrap: wrap; /* Allow wrapping on smaller screens */
    gap: 30px; /* Space between form wrappers */
}

.account-form-wrapper {
    flex: 1; /* Each form wrapper takes equal space */
    min-width: 280px; /* Minimum width before wrapping */
    border: 1px solid #eee;
    padding: 20px;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.account-form-wrapper h4 {
    margin-top: 0;
    margin-bottom: 15px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 10px;
}

.account-detail-form p {
    margin-bottom: 15px;
}

.account-detail-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.account-detail-form input[type="email"],
.account-detail-form input[type="password"] {
    width: 100%;
    padding: 8px;
    border: 1px solid #ccc;
    border-radius: 4px;
    box-sizing: border-box;
}

.account-detail-form input[disabled] {
    background-color: #e9ecef;
    opacity: 1;
}

/* Responsive adjustments for smaller screens */
@media (max-width: 768px) {
    .account-details-forms {
        flex-direction: column; /* Stack forms vertically */
    }
    .my-account-tabs .nav-tabs {
        flex-direction: column; /* Stack tab navigation */
    }
    .my-account-tabs .nav-tabs .nav-item {
        margin-bottom: 0;
    }
    .my-account-tabs .nav-tabs .nav-link {
        border-radius: .25rem; /* Adjust radius for stacked tabs */
        margin-bottom: 2px;
    }
    .my-account-tabs .nav-tabs .nav-link.active {
        border-color: #ddd; /* Consistent border for active stacked tab */
    }
     .my-account-tabs .tab-content > .tab-pane.active {
        border-top: 1px solid #ddd; /* Add top border back when tabs are stacked */
    }
}

</style>