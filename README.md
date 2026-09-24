# Konrado AI for WordPress

Adds your [Konrado.AI](https://konrado.ai) support chat to every page of your WordPress site.
No code to paste and no theme editing.

## Install

1. In the Konrado dashboard, open your agent, go to **Live chat > Install**, choose **WordPress**
   and download the plugin.
2. In WordPress, go to **Plugins > Add New Plugin > Upload Plugin**, choose the ZIP, then click
   **Install Now** and **Activate**.
3. Go to **Settings > Konrado AI**, paste your Widget ID and click **Save Changes**.

The chat appears on your site. Its look, greeting and languages are set in the Konrado dashboard.

## Recognize logged-in customers (optional)

Tick **Recognize customers who are logged in to WordPress** and paste the secret key from
**Identity verification** on the Install page. For each logged-in visitor the plugin signs a
short-lived token with their name and e-mail, so the agent knows who it is talking to. Visitors
who are not logged in chat anonymously. Keep the key private.

The customer id is sent as `wp:<user id>`. If your WordPress users are also clients in your help
desk (for example WHMCS), return their client id from the `konrado_ai_customer_id` filter:

```php
add_filter('konrado_ai_customer_id', function ($id, $user) {
    return get_user_meta($user->ID, 'whmcs_client_id', true) ?: '';
}, 10, 2);
```

## Requirements

WordPress 5.7 or later, PHP 7.4 or later.

## License

GPLv2 or later.
