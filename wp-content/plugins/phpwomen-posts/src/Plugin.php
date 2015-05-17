<?php
namespace PHPWomen\Posts;

use PHPWomen\Posts\MigrationInterface;

class Plugin
{
    const PLUGIN_VERSION = 1; // update to trigger next migrations
    static $classes = array(
        'Lib\PostType'
    );
    protected $pluginDir = '';

    public function __construct($pluginDir)
    {
        $this->pluginDir = $pluginDir;
        register_activation_hook(__FILE__, array($this, 'activation'));
        add_action('plugins_loaded', array($this, 'activation'));

        foreach (self::$classes as $class) {
            $class = __NAMESPACE__ . "\\" . $class;
            $create = true;
            if (method_exists($class, 'createCheck')) {
                $create = call_user_func(array($class, 'createCheck'));
            }

            if ($create) {
                new $class($pluginDir);
            }
        }

        $frontendFunctionsFile = $pluginDir . 'src/Front/Functions.php';
        if (file_exists($frontendFunctionsFile)) {
            require_once $frontendFunctionsFile;
        }
    }


    public function activation()
    {
        /** @var \WPDB $wpdb **/
        global $wpdb;

        $version = get_option(__NAMESPACE__ . '_VERSION', 0);
        $action = null;
        if ($version == self::PLUGIN_VERSION) {
            return;
        }

        if (!function_exists('dbDelta')) {
            require ABSPATH . 'wp-admin/includes/upgrade.php';
        }

        if ($version < self::PLUGIN_VERSION) {
            for ($i = $version; $i <= self::PLUGIN_VERSION; $i++) {
                $classname = implode("\\", array(__NAMESPACE__, "DB", "Migrate" . $i));
                if (class_exists($classname) && $classname instanceof MigrationInterface) {
                    call_user_func(array($classname, 'update'), $wpdb);
                }
            }
        } elseif ($version > self::PLUGIN_VERSION) {
            for ($i = self::PLUGIN_VERSION; $i >= $version; $i--) {
                $classname = implode("\\", array(__NAMESPACE__, "DB", "Migrate" . $i));
                if (class_exists($classname) && $classname instanceof MigrationInterface) {
                    call_user_func(array($classname, 'rollback'), $wpdb);
                }
            }
        }

        update_option(__NAMESPACE__ . '_VERSION', self::PLUGIN_VERSION);
    }
}
