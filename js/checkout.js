document.addEventListener('DOMContentLoaded', function() {
    const paymentErrorDiv = document.getElementById('payment-error');

    // --- Initial Sanity Checks for checkoutData --- 
    console.log('Starting checkout.jsDOMContentLoaded event...');

    // Check 1: checkoutData object itself
    if (typeof checkoutData === 'undefined' || checkoutData === null) {
        console.error('CRITICAL: checkoutData object is undefined. WordPress localization likely failed due to PHP errors.');
        if (paymentErrorDiv) {
            paymentErrorDiv.innerHTML = '<div class="text-red-600">Error: Checkout data is missing. Please contact support. (checkoutData undefined)</div>';
            paymentErrorDiv.style.display = 'block';
        }
        return; // Stop script execution if essential data is missing
    }
    console.log('PASSED Check 1: checkoutData object exists.', checkoutData);

    // Check 2: Stripe Publishable Key
    if (!checkoutData.stripe_pk) {
        console.error('CRITICAL: Stripe Publishable Key (stripe_pk) is missing in checkoutData.');
        if (paymentErrorDiv) {
            paymentErrorDiv.innerHTML = '<div class="text-red-600">Error: Stripe configuration is missing. Please contact support. (stripe_pk missing)</div>';
            paymentErrorDiv.style.display = 'block';
        }
        return; // Stop script execution
    }
    console.log('PASSED Check 2: Stripe Publishable Key (stripe_pk) found.');

    // Check 3: AJAX URL
    if (!checkoutData.ajax_url) {
        console.error('CRITICAL: AJAX URL (ajax_url) is missing in checkoutData. Checkout will likely fail.');
        if (paymentErrorDiv) {
            paymentErrorDiv.innerHTML = '<div class="text-red-600">Error: Server connection details missing. Please contact support. (ajax_url missing)</div>';
            paymentErrorDiv.style.display = 'block';
        }
        // It might be too critical to continue without ajax_url, consider returning if all subsequent actions depend on it.
        // return; 
    }
    console.log('PASSED Check 3: AJAX URL (ajax_url) found.');

    // IMPORTANT: No general 'checkoutData.nonce' check should be present here.
    // Specific nonces (e.g., checkoutData.checkout_nonce, checkoutData.check_email_nonce)
    // are checked right before their respective AJAX calls later in the script.
    // --- End Initial Sanity Checks --- 

    const stripe = Stripe(checkoutData.stripe_pk);
    
    const nextButton = document.getElementById('next-step');
    const prevButton = document.getElementById('prev-step'); 
    const signupSpinner = document.getElementById('signup-spinner');

    console.log('Checkout Data Initialized:', checkoutData);
    console.log('Cart data from checkoutData:', checkoutData.cart);
    
    if (checkoutData.cart && Array.isArray(checkoutData.cart)) {
        checkoutData.cart.forEach((item, index) => {
            console.log(`Cart item ${index}:`, item);
            console.log(`  Product ID: ${item.product_id}`);
            console.log(`  Variation: ${item.variation_name || 'None'}`);
            console.log(`  Stripe Price ID: ${item.stripe_price_id || 'Not set'}`);
        });
    } else if (checkoutData.cart && typeof checkoutData.cart === 'object' && Object.keys(checkoutData.cart).length > 0) {
        // Handle cases where cart might be an object of objects (if keys are product_ids like in PHP direct link handling)
        console.log('Cart is an object, iterating over values:');
        Object.values(checkoutData.cart).forEach((item, index) => {
            console.log(`Cart item ${index} (from object):`, item);
            console.log(`  Product ID: ${item.product_id}`);
            console.log(`  Variation: ${item.variation_name || 'None'}`);
            console.log(`  Stripe Price ID: ${item.stripe_price_id || 'Not set'}`);
        });
    } else {
        console.log('Cart data is empty or not in expected array/object format.');
    }

    function validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    function validatePassword(password) {
        return password.length >= 8;
    }

    function showCheckoutStepView() {
        console.log('Ensuring correct checkout view is visible based on login state.');
        const stepContent = document.getElementById('step-2');
        if (stepContent) {
            stepContent.style.display = 'block';
        } else {
            console.error('Checkout content (div#step-2) not found!');
        }
        const activeIndicator = document.querySelector('.step-indicator[data-step="2"]');
        if (activeIndicator) activeIndicator.classList.add('active-step');

        if (prevButton) prevButton.style.display = 'none'; 
        if (nextButton) nextButton.style.display = 'flex';
    }

    if (nextButton) {
        nextButton.addEventListener('click', async () => {
            console.log('Proceed button clicked.');
            
            if (paymentErrorDiv) paymentErrorDiv.style.display = 'none';

            const emailInput = document.getElementById('customer-email');
            const emailErrorDiv = document.getElementById('email-error');
            const genericEmailErrorSpan = emailErrorDiv ? emailErrorDiv.querySelector('.generic-email-error') : null;
            const existingCustomerErrorSpan = emailErrorDiv ? emailErrorDiv.querySelector('.existing-customer-error') : null;

            let userEmail = '';
            let username = '';
            let password = '';
            let isValid = true;

            if (checkoutData.is_user_logged_in) {
                console.log('User is logged in. Using provided email.');
                userEmail = checkoutData.current_user_email || (emailInput ? emailInput.value : '');
                if (!userEmail || !validateEmail(userEmail)) {
                    if(paymentErrorDiv) {
                        paymentErrorDiv.textContent = 'Error: Logged-in user email not found or invalid.';
                        paymentErrorDiv.style.display = 'block';
                    }
                    isValid = false;
                }
            } else {
                console.log('User is not logged in. Validating account creation form.');
                const usernameInput = document.getElementById('username');
                const usernameError = document.getElementById('username-error');
                const passwordInput = document.getElementById('password');
                const passwordError = document.getElementById('password-error');
                const verifyPasswordInput = document.getElementById('verify-password');
                const verifyPasswordError = document.getElementById('verify-password-error');

                if(emailErrorDiv) emailErrorDiv.style.display = 'none';
                if(genericEmailErrorSpan) genericEmailErrorSpan.textContent = '';
                if(existingCustomerErrorSpan) existingCustomerErrorSpan.style.display = 'none';
                if(usernameError) usernameError.style.display = 'none';
                if(passwordError) passwordError.style.display = 'none';
                if(verifyPasswordError) verifyPasswordError.style.display = 'none';

                userEmail = emailInput ? emailInput.value : '';
                if (!userEmail || !validateEmail(userEmail)) {
                    if(emailErrorDiv && genericEmailErrorSpan) {
                        genericEmailErrorSpan.textContent = 'Please enter a valid email address';
                        emailErrorDiv.style.display = 'block';
                    }
                    isValid = false;
                }

                username = usernameInput ? usernameInput.value : '';
                if (!username || username.length < 3) {
                    if(usernameError) { usernameError.textContent = 'Username must be at least 3 characters'; usernameError.style.display = 'block'; }
                    isValid = false;
                }

                password = passwordInput ? passwordInput.value : '';
                if (!password || !validatePassword(password)) {
                    if(passwordError) { passwordError.textContent = 'Password must be at least 8 characters'; passwordError.style.display = 'block'; }
                    isValid = false;
                }

                if (!verifyPasswordInput || password !== verifyPasswordInput.value) {
                    if(verifyPasswordError) { verifyPasswordError.textContent = 'Passwords do not match'; verifyPasswordError.style.display = 'block'; }
                    isValid = false;
                }
            }

            if (!isValid) {
                console.log('Form validation failed.');
                return; 
            }

            nextButton.disabled = true;
            if(signupSpinner) signupSpinner.style.display = 'inline-block';

            if (!checkoutData.is_user_logged_in) {
                console.log('Checking email against Stripe for new user...');
                if (!checkoutData.ajax_url || !checkoutData.check_email_nonce || !checkoutData.check_email_action) {
                    console.error('CRITICAL: Email check AJAX config (ajax_url, nonce, or action) missing.');
                    if (paymentErrorDiv) {
                        paymentErrorDiv.innerHTML = '<div class="text-red-600">Error: Email validation configuration missing. Please contact support.</div>';
                        paymentErrorDiv.style.display = 'block';
                    }
                    nextButton.disabled = false;
                    if(signupSpinner) signupSpinner.style.display = 'none';
                    return;
                }
                try {
                    const emailCheckData = new FormData();
                    emailCheckData.append('action', checkoutData.check_email_action);
                    emailCheckData.append('nonce', checkoutData.check_email_nonce);
                    emailCheckData.append('email', userEmail);

                    const checkResponse = await fetch(checkoutData.ajax_url, { method: 'POST', body: emailCheckData });
                    const checkResult = await checkResponse.json();

                    if (!checkResponse.ok || !checkResult) {
                        throw new Error(checkResult?.data?.message || 'Failed to check email status.');
                    }
                    if (!checkResult.success) {
                        throw new Error(checkResult.data?.message || 'An error occurred while checking email.');
                    }
                    if (checkResult.data.exists) {
                        console.log('Email exists in Stripe for new user.');
                        if(emailErrorDiv && existingCustomerErrorSpan) {
                            existingCustomerErrorSpan.style.display = 'block';
                            emailErrorDiv.style.display = 'block';
                            if(genericEmailErrorSpan) genericEmailErrorSpan.textContent = '';
                        }
                        nextButton.disabled = false;
                        if(signupSpinner) signupSpinner.style.display = 'none';
                        return; 
                    }
                    console.log('Email not found in Stripe (new user). Proceeding.');
                } catch (error) {
                    console.error('Error checking email via AJAX:', error);
                    if(emailErrorDiv && genericEmailErrorSpan) {
                        genericEmailErrorSpan.textContent = `Error checking email: ${error.message}. Please try again.`;
                        emailErrorDiv.style.display = 'block';
                    }
                    nextButton.disabled = false;
                    if(signupSpinner) signupSpinner.style.display = 'none';
                    return; 
                }
            }

            console.log('Creating Stripe checkout session for email:', userEmail);
            if (!checkoutData.ajax_url || !checkoutData.checkout_nonce) { 
                console.error('CRITICAL: Checkout session AJAX config (ajax_url or nonce) missing.');
                if (paymentErrorDiv) {
                    paymentErrorDiv.innerHTML = '<div class="text-red-600">Error: Cannot initiate payment. Please contact support. (config missing)</div>';
                    paymentErrorDiv.style.display = 'block';
                }
                nextButton.disabled = false;
                if(signupSpinner) signupSpinner.style.display = 'none';
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'create_checkout_session');
                formData.append('security', checkoutData.checkout_nonce);
                const csrfTokenInput = document.querySelector('input[name="csrf_token"]');
                if (csrfTokenInput) {
                    formData.append('csrf_token', csrfTokenInput.value);
                } else {
                    console.warn('CSRF token input field not found.');
                }
                
                formData.append('email', userEmail);

                if (!checkoutData.is_user_logged_in) {
                    formData.append('username', username);
                    formData.append('password', password);
                }
                
                formData.append('cart_data', JSON.stringify(checkoutData.cart || []));

                const response = await fetch(checkoutData.ajax_url, { method: 'POST', body: formData });
                const result = await response.json();
                console.log('Stripe session response:', result);

                if (!response.ok || !result || !result.success || !result.data || !result.data.url) {
                    let errorMsg = (result?.data?.error) ? result.data.error : (result?.data?.message || 'Failed to create checkout session. Please try again.');
                    throw new Error(errorMsg);
                }
                window.location.href = result.data.url; 

            } catch (error) {
                console.error('Error creating Stripe session:', error);
                if (paymentErrorDiv) { paymentErrorDiv.innerHTML = `<div class="text-red-600">Error: ${error.message}</div>`; paymentErrorDiv.style.display = 'block'; }
                if (nextButton) nextButton.disabled = false;
                if(signupSpinner) signupSpinner.style.display = 'none';
            }
        });
    } else {
        console.error('Next step button (id=next-step) not found!');
    }

    const pageCheckoutForm = document.getElementById('main-checkout-form');
    if (pageCheckoutForm) {
        pageCheckoutForm.addEventListener('submit', function(event) {
            event.preventDefault();
            if (nextButton && !nextButton.disabled) {
                 nextButton.click();
            }
        });
    }

    showCheckoutStepView(); 
}); 