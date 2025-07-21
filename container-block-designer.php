<?php
/**
 * Container Block Designer
 *
 * @package     ContainerBlockDesigner
 * @author      Ihr Name
 * @copyright   2025 Ihre Firma
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Container Block Designer
 * Plugin URI:  https://example.com/container-block-designer
 * Description: Ein visueller Designer für custom Container-Blöcke im Gutenberg Editor
 * Version:     1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:      Ihr Name
 * Author URI:  https://example.com
 * Text Domain: container-block-designer
 * Domain Path: /languages
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Direkt-Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('CBD_VERSION', '1.0.0');
define('CBD_PLUGIN_FILE', __FILE__);
define('CBD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CBD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CBD_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Minimum PHP Version Check
if (version_compare(PHP_VERSION, '8.0', '<')) {
    add_action('admin_notices', function() {
        ?>
        <div class="notice notice-error">
            <p><?php _e('Container Block Designer benötigt PHP 8.0 oder höher.', 'container-block-designer'); ?></p>
        </div>
        <?php
    });
    return;
}

// Autoloader registrieren
spl_autoload_register(function ($class) {
    $prefix = 'ContainerBlockDesigner\\';
    $base_dir = CBD_PLUGIN_DIR . 'includes/';
    
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    
    if (file_exists($file)) {
        require $file;
    }
});

// Composer Autoloader laden (falls vorhanden)
if (file_exists(CBD_PLUGIN_DIR . 'vendor/autoload.php')) {
    require_once CBD_PLUGIN_DIR . 'vendor/autoload.php';
}

// Plugin initialisieren
add_action('plugins_loaded', function() {
    // Textdomain laden
    load_plugin_textdomain(
        'container-block-designer',
        false,
        dirname(CBD_PLUGIN_BASENAME) . '/languages'
    );
    
    // Plugin nur initialisieren wenn WordPress-Version passt
    if (version_compare(get_bloginfo('version'), '6.0', '<')) {
        add_action('admin_notices', function() {
            ?>
            <div class="notice notice-error">
                <p><?php _e('Container Block Designer benötigt WordPress 6.0 oder höher.', 'container-block-designer'); ?></p>
            </div>
            <?php
        });
        return;
    }
    
    // Haupt-Plugin-Instanz starten
    \ContainerBlockDesigner\Core\Plugin::get_instance();
});

// Aktivierungs-Hook
register_activation_hook(__FILE__, function() {
    require_once CBD_PLUGIN_DIR . 'includes/Core/Activator.php';
    \ContainerBlockDesigner\Core\Activator::activate();
});

// Deaktivierungs-Hook
register_deactivation_hook(__FILE__, function() {
    require_once CBD_PLUGIN_DIR . 'includes/Core/Deactivator.php';
    \ContainerBlockDesigner\Core\Deactivator::deactivate();
});

// Uninstall-Hook
register_uninstall_hook(__FILE__, 'cbd_uninstall');

function cbd_uninstall() {
    require_once CBD_PLUGIN_DIR . 'includes/Core/Uninstaller.php';
    \ContainerBlockDesigner\Core\Uninstaller::uninstall();
}