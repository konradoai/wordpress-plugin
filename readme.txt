=== Konrado AI ===
Contributors: konradoai
Tags: chatbot, ai, customer support, live chat, chat
Requires at least: 5.7
Tested up to: 7.1
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add your Konrado AI support agent to every page of your WordPress site. No code to paste.

== Description ==

Konrado AI answers your customers' questions around the clock, using your knowledge base, your policies and your integrations. This plugin puts the Konrado chat on your WordPress site.

* One setting: paste your Widget ID and save.
* The chat looks and behaves the way you configured it in the Konrado dashboard - colors, greeting, languages and position are managed there, not in WordPress.
* Optional: tick "Recognize customers who are logged in to WordPress" and add your signing secret, and the agent knows which logged-in visitor it is talking to (for example your WooCommerce customers).

This plugin loads the chat from Konrado's servers (app.konrado.ai). The chat processes the messages your visitors send. See the [Konrado.ai privacy policy](https://konrado.ai/privacy-policy/) and [terms of service](https://konrado.ai/terms-of-service/).

== Installation ==

1. In WordPress, go to Plugins > Add New Plugin > Upload Plugin, choose the konrado-ai-wordpress.zip file you downloaded from the Konrado dashboard, then click Install Now and Activate.
2. In the Konrado dashboard, open your agent's website chat channel, go to Install and choose WordPress. Copy your Widget ID (it starts with wgt_).
3. In WordPress, go to Settings > Konrado AI, paste the Widget ID and click Save Changes.
4. Open your site. The chat button appears in the bottom corner.

To turn the chat on or off, or to change how it looks, use the Konrado dashboard. You never need to edit your theme.

== Frequently Asked Questions ==

= Where do I find my Widget ID? =

In the Konrado dashboard: open your agent, go to the website chat channel, then Install, and choose WordPress.

= Can the chat recognize my logged-in users? =

Yes. In the Konrado dashboard, generate a secret key under Identity verification on the Install page. In WordPress, go to Settings > Konrado AI, tick "Recognize customers who are logged in to WordPress" and paste the key into the Signing secret field that appears. For every logged-in WordPress user the plugin then signs a short-lived token with their user ID, e-mail and display name, and Konrado checks the signature with the same key. Visitors who are not logged in keep chatting anonymously. Treat the secret like a password.

= My WordPress users are also clients in my help desk (WHMCS etc.). Can the agent see their account? =

Not by default. Konrado treats the customer id in the token as a help-desk client id, and a WordPress user id is a different number, so the plugin sends it as `wp:<user id>` and the agent only learns the name and e-mail. If you store each user's help-desk client id, return it from the `konrado_ai_customer_id` filter:

`add_filter('konrado_ai_customer_id', function ($id, $user) { return get_user_meta($user->ID, 'whmcs_client_id', true) ?: ''; }, 10, 2);`

Returning an empty string sends no identity for that user.

= I use a page cache. Anything to watch out for? =

Identity tokens are valid for five minutes and can be used once, so pages served to logged-in users must not be cached. Most caching plugins already skip logged-in users by default. Anonymous visitors are not affected.

= The chat does not appear. =

Check that the Widget ID is saved under Settings > Konrado AI, that the channel is turned on in the Konrado dashboard, and - if you restricted the chat to allowed domains - that your site's domain is on the list.

== Changelog ==

= 1.0.0 =
* First release.
