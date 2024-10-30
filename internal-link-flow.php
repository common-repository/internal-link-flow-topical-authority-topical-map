<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @since             1.0.0
 * @package           Internal_Link_Flow
 *
 * @wordpress-plugin
 * Plugin Name:       Internal Link Flow & Topical Authority & Topical Map
 * Plugin URI:        https://nurullah.org/internal-link-flow
 * Description:       Visualize and track the internal linking structure of your page using a flow chart. Topical Map and Topical Authority
 * Version:           1.0.1
 * Author:            Nurullah SERT
 * Author URI:        https://nurullah.org
 * License:           GPL-2.0+
 * Requires PHP:      7.4
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       internallinkflow
 * Domain Path:       /lang
 */
if (!defined('ABSPATH')) : exit(); endif;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
/**
 * Current plugin path.
 * Current plugin url.
 * Current plugin version.
 *
 * Rename these constants for your plugin
 * Update version as you release new versions.
 */

define('TAILF_PATH', plugin_dir_path(__FILE__));
define('TAILF_URL', plugin_dir_url(__FILE__));
define('TAILF_VERSION', '1.0.1');
define('TAILF_VERSION_INT', '101');


if ( ! function_exists( 'ilftatm_fs' ) ) {
    // Create a helper function for easy SDK access.
    function ilftatm_fs() {
        global $ilftatm_fs;

        if ( ! isset( $ilftatm_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $ilftatm_fs = fs_dynamic_init( array(
                'id'                  => '11812',
                'slug'                => 'internal-link-flow--topical-authority--topical-map',
                'type'                => 'plugin',
                'public_key'          => 'pk_4828d3fa93b556dc42ae6c01ddff6',
                'is_premium'          => false,
                'has_addons'          => false,
                'has_paid_plans'      => false,
                'menu'                => array(
                    'first-path'     => 'admin.php?page=internal-link-flow',
                    'account'        => false,
                    'support'        => false,
                ),
            ) );
        }

        return $ilftatm_fs;
    }

    // Init Freemius.
    ilftatm_fs();
    // Signal that SDK was initiated.
    do_action( 'ilftatm_fs_loaded' );
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-react-plugin-boilerplate-activator.php
 */
function activate_tailf()
{
    require_once TAILF_PATH . 'includes/class_ifl_activator.php';
    Class_Wp_Ilf_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-react-plugin-boilerplate-deactivator.php
 */
function deactivate_tailf()
{
    require_once TAILF_PATH . 'includes/class_ifl_deactivator.php';
    Class_Wp_Ilf_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_tailf');
register_deactivation_hook(__FILE__, 'deactivate_tailf');

add_action('admin_enqueue_scripts', 'load_scripts');
function load_scripts()
{
    if ($_GET['page'] != 'internal-link-flow') {
        return;
    }
    wp_enqueue_style('tailf-style', plugin_dir_url(__FILE__) . 'build/index.css');
    wp_enqueue_script('internal-link-flow', TAILF_URL . 'build/index.js', ['jquery', 'wp-element', 'wp-api-fetch', 'wp-i18n'], wp_rand(), true);

    wp_localize_script('internal-link-flow', 'appLocalizer', [
        'apiUrl' => home_url('/wp-json'),
        'nonce' => wp_create_nonce('wp_rest'),
    ]);
}

add_action('init', 'ilf_lang');
function ilf_lang()
{
    load_plugin_textdomain('internallinkflow', false, plugin_dir_path( __FILE__ ) . 'lang');
    //wp_set_script_translations('internal-link-flow-lang', 'internal-link-flow', plugin_dir_path( __FILE__ ) . 'lang');
    //wp_set_script_translations( 'internal-link-flow-script', 'internal-link-flow', plugin_dir_path( __FILE__ ) . 'lang/' );
    //wp_set_script_translations( 'internal-link-flow', 'internallinkflow', plugin_dir_path( __FILE__ ) . 'lang' );

}


require_once TAILF_PATH . 'includes/class_ilf_create_admin_menu.php';
require_once TAILF_PATH . 'includes/class_ilf_create_flow_routes.php';