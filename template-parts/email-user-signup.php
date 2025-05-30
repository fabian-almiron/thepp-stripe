<?php
/**
 * User Signup Email Template
 *
 * This template is used to send a welcome email to users when they sign up.
 * Placeholders: $user_first_name, $blogname, $user_login, $set_password_url, $additional_content, $password_generated
 */

if (!defined('ABSPATH')) exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to <?php echo esc_html($blogname); ?></title>
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
        a {
            color: #1a0dab;
        }
    </style>
</head>
<body>
    <img src="https://thepipedpeony.com/wp-content/uploads/2023/03/piped-peony-logo-2048x452.png" alt="The Piped Peony" class="logo">
    <div class="email-container">
        <div class="email-header">
            <h1>Welcome to <?php echo esc_html($blogname); ?></h1>
        </div>
        <div class="email-body">
            <p>Hi <?php echo esc_html($user_first_name); ?>,</p>
            <p>Thanks for creating an account on <?php echo esc_html($blogname); ?>. Your username is <strong><?php echo esc_html($user_login); ?></strong>.
            You can access your account area to view orders, change your password, and more at: <a href="<?php echo esc_url(home_url('/my-account/')); ?>"><?php echo esc_url(home_url('/my-account/')); ?></a></p>
            <?php if ($password_generated && $set_password_url) : ?>
            <?php endif; ?>
            <p>Join our community of bakers on our Facebook page! <a href="https://www.facebook.com/groups/359977912181048">HERE</a></p>
            <p>We look forward to seeing you soon.</p>
            <?php if (!empty($additional_content)) echo wp_kses_post(wpautop(wptexturize($additional_content))); ?>
        </div>
        <div class="email-footer">
            <?php echo esc_html($blogname); ?>
        </div>
    </div>
</body>
</html> 