<div align="center">

<picture>
  <source media="(prefers-color-scheme: dark)" srcset=".github/assets/konrado-logo-on-dark.svg">
  <img src=".github/assets/konrado-logo-on-light.svg" alt="Konrado.AI" width="260">
</picture>

<h1>Konrado AI for WordPress</h1>

<p>
Put your Konrado AI support agent on every page of your WordPress site. Paste one ID, save, and visitors can chat with it.
</p>

<p>
<a href="#get-started"><b>Get started</b></a> ·
<a href="#settings">Settings</a> ·
<a href="#recognize-logged-in-customers"><b>Logged-in customers</b></a> ·
<a href="#faq">FAQ</a> ·
<a href="https://docs.konrado.ai/integrations/wordpress"><b>Docs</b></a>
</p>

<p>
<a href="https://github.com/konradoai/wordpress-plugin/archive/refs/heads/main.zip"><img src="https://img.shields.io/badge/version-1.0.0-10D0A1" alt="Version 1.0.0"></a>
<a href="#requirements"><img src="https://img.shields.io/badge/WordPress-5.7%2B-21759B?logo=wordpress&logoColor=white" alt="WordPress 5.7+"></a>
<a href="#requirements"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?logo=php&logoColor=white" alt="PHP 7.4+"></a>
<a href="#license"><img src="https://img.shields.io/badge/license-GPLv2%2B-0b7285" alt="GPLv2 or later"></a>
</p>

</div>

---

## In short

**Konrado.AI** answers your customers from your knowledge base, day and night. **This plugin** puts that chat on your WordPress site.

- **One setting.** Paste your Widget ID under **Settings > Konrado AI**. No theme files, no code.
- **Managed in Konrado.** Colors, greeting, languages and position are set in the Konrado dashboard and apply on the next page load.
- **Knows your customers, if you want it to.** Tick one checkbox and the agent knows which logged-in customer it is talking to.

You need a Konrado.AI account with a Live chat channel. Don't have one yet? [Start at konrado.ai](https://konrado.ai).

---

## Get started

### 1. Download the plugin

In the Konrado dashboard, open your agent and go to **Live chat > Install**. Choose **WordPress** and click **Download plugin (.zip)**.

Or download it straight from here: **[main.zip](https://github.com/konradoai/wordpress-plugin/archive/refs/heads/main.zip)**.

### 2. Install it in WordPress

Go to **Plugins > Add New Plugin > Upload Plugin**, choose the ZIP, then click **Install Now** and **Activate**.

### 3. Paste your Widget ID

Go to **Settings > Konrado AI**, paste the Widget ID from the Install page (it starts with `wgt_`) and click **Save Changes**.

What you should see: the chat button in the bottom corner of your site. If it does not appear, check that the chat is turned on under **Live chat > Status** in Konrado.

The full guide, with screenshots of every step, is at **[docs.konrado.ai/integrations/wordpress](https://docs.konrado.ai/integrations/wordpress)**.

---

## Settings

| Setting | Required | What it does |
|:---|:---|:---|
| **Widget ID** | Yes | Connects the site to your Konrado Live chat. Starts with `wgt_`. |
| **Recognize customers who are logged in to WordPress** | No | Tells the agent who a logged-in visitor is. Off by default. |
| **Signing secret** | With the checkbox | Appears once the checkbox is ticked. Proves to Konrado that the customer details come from your site. |

The plugin adds a single script to the footer of every page. Deleting the plugin removes its settings.

---

## Recognize logged-in customers

Useful for shops and sites with customer accounts, such as WooCommerce.

1. In Konrado, on the **Install** page, generate a secret key under **Identity verification** and copy it.
2. In WordPress, tick **Recognize customers who are logged in to WordPress** and paste the key into **Signing secret**.

For every logged-in visitor, the plugin signs their name and e-mail with that key. Konrado checks the signature, so the agent knows who it is talking to and does not have to ask. Visitors who are not logged in always chat anonymously.

| Visitor | What the agent knows |
|:---|:---|
| Logged in, correct key | Their verified name and e-mail |
| Not logged in | Nothing, the chat is anonymous |
| Logged in, wrong or missing key | Nothing, the chat falls back to anonymous |

Treat the key like a password. Anyone who has it can pose as your customers to the agent.

---

## FAQ

<details>
<summary><b>Where do I find my Widget ID?</b></summary>

In the Konrado dashboard: open your agent, go to **Live chat > Install** and choose **WordPress**. The ID is in step 3, with a copy button.
</details>

<details>
<summary><b>Can the agent see my customers' orders or invoices?</b></summary>

Not through this plugin on its own. Konrado treats the customer id in the signed token as a client id in your help desk (WHMCS and similar), so the plugin sends it as `wp:<user id>` and it never matches a help-desk client by accident. The agent gets the name and e-mail only.

If your WordPress users are also help-desk clients, a developer can hand over the real client id:

```php
add_filter('konrado_ai_customer_id', function ($id, $user) {
    return get_user_meta($user->ID, 'whmcs_client_id', true) ?: '';
}, 10, 2);
```

Returning an empty string sends no identity for that user.
</details>

<details>
<summary><b>I use a page cache. Anything to watch out for?</b></summary>

Signed tokens last five minutes and work once, so pages for logged-in users must not be cached. Most caching plugins already skip logged-in users. Anonymous visitors are not affected.
</details>

<details>
<summary><b>WordPress asks for FTP details when I upload the plugin.</b></summary>

That is a hosting setting: WordPress cannot write to its own plugins folder. Unzip the plugin into `wp-content/plugins/` with your hosting file manager or FTP, then activate it under **Plugins**.
</details>

<details>
<summary><b>How do I update the plugin?</b></summary>

Download the ZIP again and upload it under **Plugins > Add New Plugin > Upload Plugin**. WordPress offers to replace the installed version. Your settings are kept.
</details>

---

## Requirements

WordPress 5.7 or later and PHP 7.4 or later. Tested up to WordPress 7.1.

---

## License

GPLv2 or later. See the plugin header in [`konrado-ai.php`](konrado-ai.php).
