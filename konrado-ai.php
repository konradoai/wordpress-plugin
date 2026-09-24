<?php
/**
 * Plugin Name:       Konrado AI
 * Plugin URI:        https://konrado.ai
 * Description:       Adds your Konrado AI support chat to every page of your site. Paste your Widget ID under Settings > Konrado AI and you're live.
 * Version:           1.0.0
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Konrado.ai
 * Author URI:        https://konrado.ai
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       konrado-ai
 */

if (!defined('ABSPATH')) {
    exit;
}

// Where the chat is served from. A site can point at another Konrado environment by
// defining KONRADO_AI_HOST in wp-config.php, or with the `konrado_ai_host` filter.
const KONRADO_AI_DEFAULT_HOST = 'https://app.konrado.ai';

// The Widget ID is the website-chat channel's public id - the only thing the loader needs.
const KONRADO_AI_WIDGET_ID_PATTERN = '/^wgt_[0-9a-f]{32}$/';

// Identity tokens are redeemed within seconds of page load and are single-use, so five
// minutes is plenty. Konrado rejects anything without `iat`, `exp`, `jti` and `sub`.
const KONRADO_AI_TOKEN_TTL = 300;

const KONRADO_AI_OPTION_WIDGET_ID = 'konrado_ai_widget_id';
const KONRADO_AI_OPTION_IDENTIFY = 'konrado_ai_identify_users';
const KONRADO_AI_OPTION_SECRET = 'konrado_ai_signing_secret';
const KONRADO_AI_SETTINGS_PAGE = 'konrado-ai';

function konrado_ai_host()
{
    $host = defined('KONRADO_AI_HOST') ? (string) KONRADO_AI_HOST : KONRADO_AI_DEFAULT_HOST;

    return untrailingslashit((string) apply_filters('konrado_ai_host', $host));
}

function konrado_ai_widget_id()
{
    return (string) get_option(KONRADO_AI_OPTION_WIDGET_ID, '');
}

function konrado_ai_identifies_users()
{
    return get_option(KONRADO_AI_OPTION_IDENTIFY, '') === '1';
}

/* -------------------------------------------------------------------------------------------
 * Settings > Konrado AI
 * ---------------------------------------------------------------------------------------- */

add_action('admin_init', 'konrado_ai_register_settings');
add_action('admin_menu', 'konrado_ai_add_settings_page');
add_action('admin_notices', 'konrado_ai_missing_widget_id_notice');
add_action('admin_enqueue_scripts', 'konrado_ai_settings_script');
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'konrado_ai_action_links');

function konrado_ai_register_settings()
{
    register_setting('konrado_ai', KONRADO_AI_OPTION_WIDGET_ID, [
        'type'              => 'string',
        'sanitize_callback' => 'konrado_ai_sanitize_widget_id',
        'default'           => '',
    ]);
    register_setting('konrado_ai', KONRADO_AI_OPTION_IDENTIFY, [
        'type'              => 'string',
        'sanitize_callback' => 'konrado_ai_sanitize_checkbox',
        'default'           => '',
    ]);
    register_setting('konrado_ai', KONRADO_AI_OPTION_SECRET, [
        'type'              => 'string',
        'sanitize_callback' => 'konrado_ai_sanitize_secret',
        'default'           => '',
    ]);

    add_settings_section('konrado_ai_main', '', '__return_false', KONRADO_AI_SETTINGS_PAGE);

    add_settings_field(
        KONRADO_AI_OPTION_WIDGET_ID,
        __('Widget ID', 'konrado-ai'),
        'konrado_ai_render_widget_id_field',
        KONRADO_AI_SETTINGS_PAGE,
        'konrado_ai_main',
        ['label_for' => KONRADO_AI_OPTION_WIDGET_ID]
    );
    add_settings_field(
        KONRADO_AI_OPTION_IDENTIFY,
        __('Logged-in customers (optional)', 'konrado-ai'),
        'konrado_ai_render_identify_field',
        KONRADO_AI_SETTINGS_PAGE,
        'konrado_ai_main'
    );
    // The secret only means something once recognition is on, so its row starts hidden
    // until the checkbox is ticked (the settings script toggles it without a reload).
    add_settings_field(
        KONRADO_AI_OPTION_SECRET,
        __('Signing secret', 'konrado-ai'),
        'konrado_ai_render_secret_field',
        KONRADO_AI_SETTINGS_PAGE,
        'konrado_ai_main',
        [
            'label_for' => KONRADO_AI_OPTION_SECRET,
            'class'     => 'konrado-ai-secret-row' . (konrado_ai_identifies_users() ? '' : ' hidden'),
        ]
    );
}

function konrado_ai_sanitize_widget_id($value)
{
    $value = strtolower(trim(sanitize_text_field((string) $value)));

    if ($value === '' || preg_match(KONRADO_AI_WIDGET_ID_PATTERN, $value)) {
        return $value;
    }

    // WordPress runs this callback twice on the very first save; report the problem once.
    static $reported = false;

    if (!$reported) {
        $reported = true;
        add_settings_error(
            KONRADO_AI_OPTION_WIDGET_ID,
            'konrado_ai_invalid_widget_id',
            __('That is not a valid Widget ID. It starts with "wgt_" - copy it from your Konrado dashboard.', 'konrado-ai')
        );
    }

    return konrado_ai_widget_id();
}

function konrado_ai_sanitize_checkbox($value)
{
    return $value ? '1' : '';
}

function konrado_ai_sanitize_secret($value)
{
    // The key is base64url, so anything outside that alphabet is a copy-paste artifact.
    return (string) preg_replace('/[^A-Za-z0-9_\-]/', '', (string) $value);
}

function konrado_ai_add_settings_page()
{
    add_options_page(
        __('Konrado AI', 'konrado-ai'),
        __('Konrado AI', 'konrado-ai'),
        'manage_options',
        KONRADO_AI_SETTINGS_PAGE,
        'konrado_ai_render_settings_page'
    );
}

function konrado_ai_render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <p>
            <?php esc_html_e('Your Widget ID is in the Konrado dashboard: open your agent, go to the website chat channel, then Install, and choose WordPress.', 'konrado-ai'); ?>
        </p>
        <?php if (konrado_ai_identifies_users() && get_option(KONRADO_AI_OPTION_SECRET, '') === '') : ?>
            <div class="notice notice-warning inline">
                <p><?php esc_html_e('Recognizing logged-in customers needs the signing secret. Until you add it, every visitor chats anonymously.', 'konrado-ai'); ?></p>
            </div>
        <?php endif; ?>
        <form action="options.php" method="post">
            <?php
            settings_fields('konrado_ai');
            do_settings_sections(KONRADO_AI_SETTINGS_PAGE);
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function konrado_ai_render_widget_id_field()
{
    printf(
        '<input type="text" id="%1$s" name="%1$s" value="%2$s" class="regular-text code" placeholder="wgt_..." autocomplete="off" spellcheck="false" />',
        esc_attr(KONRADO_AI_OPTION_WIDGET_ID),
        esc_attr(konrado_ai_widget_id())
    );
}

function konrado_ai_render_identify_field()
{
    printf(
        '<label for="%1$s"><input type="checkbox" id="%1$s" name="%1$s" value="1" %2$s /> %3$s</label>',
        esc_attr(KONRADO_AI_OPTION_IDENTIFY),
        checked(konrado_ai_identifies_users(), true, false),
        esc_html__('Recognize customers who are logged in to WordPress', 'konrado-ai')
    );
    echo '<p class="description">';
    esc_html_e('When a logged-in visitor opens the chat, the plugin tells the Konrado agent who they are: their WordPress user ID, e-mail and name. The agent then knows who it is talking to and does not have to ask. Useful for shops and sites with customer accounts. Visitors who are not logged in always chat anonymously.', 'konrado-ai');
    echo '</p>';
}

function konrado_ai_render_secret_field()
{
    printf(
        '<input type="password" id="%1$s" name="%1$s" value="%2$s" class="regular-text code" autocomplete="new-password" spellcheck="false" />',
        esc_attr(KONRADO_AI_OPTION_SECRET),
        esc_attr((string) get_option(KONRADO_AI_OPTION_SECRET, ''))
    );
    echo '<p class="description">';
    esc_html_e('Copy it from Identity verification on the Install page of your Konrado dashboard. The plugin signs each logged-in visitor\'s details with this key, which proves to Konrado that they come from your site and were not typed in by someone else. Keep it private, like a password.', 'konrado-ai');
    echo '</p>';
}

/**
 * Shows the Signing secret row only while the checkbox is ticked. The row's starting state is
 * rendered server-side, so this only handles clicks.
 */
function konrado_ai_settings_script($hook_suffix)
{
    if ($hook_suffix !== 'settings_page_' . KONRADO_AI_SETTINGS_PAGE) {
        return;
    }

    wp_register_script('konrado-ai-settings', false, [], '1.0.0', true);
    wp_enqueue_script('konrado-ai-settings');
    wp_add_inline_script('konrado-ai-settings', sprintf(
        "document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById(%s);
            var row = document.querySelector('.konrado-ai-secret-row');
            if (!toggle || !row) { return; }
            toggle.addEventListener('change', function () { row.classList.toggle('hidden', !toggle.checked); });
        });",
        wp_json_encode(KONRADO_AI_OPTION_IDENTIFY)
    ));
}

function konrado_ai_missing_widget_id_notice()
{
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;

    if (!current_user_can('manage_options') || konrado_ai_widget_id() !== '' || !$screen || $screen->id !== 'plugins') {
        return;
    }

    printf(
        '<div class="notice notice-warning"><p>%s <a href="%s">%s</a></p></div>',
        esc_html__('Konrado AI is active, but the chat will not appear until you add your Widget ID.', 'konrado-ai'),
        esc_url(admin_url('options-general.php?page=' . KONRADO_AI_SETTINGS_PAGE)),
        esc_html__('Add it now', 'konrado-ai')
    );
}

function konrado_ai_action_links($links)
{
    array_unshift($links, sprintf(
        '<a href="%s">%s</a>',
        esc_url(admin_url('options-general.php?page=' . KONRADO_AI_SETTINGS_PAGE)),
        esc_html__('Settings', 'konrado-ai')
    ));

    return $links;
}

/* -------------------------------------------------------------------------------------------
 * The chat on the site
 * ---------------------------------------------------------------------------------------- */

add_action('wp_footer', 'konrado_ai_print_loader', 100);

function konrado_ai_print_loader()
{
    $widget_id = konrado_ai_widget_id();

    if ($widget_id === '') {
        return;
    }

    $attributes = [
        'async'                  => true,
        'src'                    => konrado_ai_host() . '/website-chat/v1/loader.js',
        'data-konrado-widget-id' => $widget_id,
    ];

    // Signed-out visitors, and sites without a secret, get the plain anonymous tag.
    $token = konrado_ai_identity_token();

    if ($token !== '') {
        $attributes['data-konrado-user-token'] = $token;
    }

    wp_print_script_tag($attributes);
}

/**
 * A short-lived, single-use HS256 token for the logged-in WordPress user, or '' when there is
 * no one to identify. Konrado verifies it with the same secret key.
 */
function konrado_ai_identity_token()
{
    $secret = (string) get_option(KONRADO_AI_OPTION_SECRET, '');

    if (!konrado_ai_identifies_users() || $secret === '' || !is_user_logged_in()) {
        return '';
    }

    $user = wp_get_current_user();

    if (!$user || !$user->exists()) {
        return '';
    }

    // Konrado reads `sub` as the customer's id in the organization's help desk (WHMCS and
    // the like) and gives the agent read-only access to THAT client's invoices, services and
    // tickets. A WordPress user id is not a help-desk id - user 5 here is somebody else's
    // client 5 there - so it is sent prefixed, matching no help-desk client, and the agent
    // only learns the name and e-mail. A site whose WordPress users map to help-desk clients
    // returns the real client id from the `konrado_ai_customer_id` filter.
    $customer_id = (string) apply_filters('konrado_ai_customer_id', 'wp:' . $user->ID, $user);

    if ($customer_id === '') {
        return '';
    }

    $now = time();
    $claims = array_filter([
        'sub'   => $customer_id,                                           // your id for this customer
        'email' => $user->user_email !== '' ? $user->user_email : null,    // shown to the agent
        'name'  => $user->display_name !== '' ? $user->display_name : null, // shown to the agent
        'iat'   => $now,                                                   // required
        'exp'   => $now + KONRADO_AI_TOKEN_TTL,                            // redeemed on page load
        'jti'   => bin2hex(random_bytes(16)),                              // random per render
    ], static function ($value) {
        return $value !== null;
    });

    $payload = konrado_ai_base64url(wp_json_encode(['alg' => 'HS256', 'typ' => 'JWT']))
        . '.' . konrado_ai_base64url(wp_json_encode($claims));

    return $payload . '.' . konrado_ai_base64url(hash_hmac('sha256', $payload, $secret, true));
}

function konrado_ai_base64url($data)
{
    return rtrim(strtr(base64_encode((string) $data), '+/', '-_'), '=');
}
