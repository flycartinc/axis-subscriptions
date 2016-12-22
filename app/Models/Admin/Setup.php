<?php

namespace Axisubs\Models\Admin;

use Corcel\Post as Post;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Class Setup
 * @package Axisubs\Models
 */
class Setup extends Post
{

    /**
     * constructor.
     */
    public function __construct()
    {

    }

    /**
     *  To Trigger the Installation of Supportive plugins.
     */
    public static function installAdditionalPlugins()
    {
        $oldfolderpath = AXISUBS_PLUGIN_PATH . 'additional_apps/';
        $newfolderpath = WP_PLUGIN_DIR;
        if (self::full_copy($oldfolderpath, $newfolderpath)) {
            self::activatePlugins();
        }
    }

    /**
     * To Return List of Supportive plugins.
     * @return array
     */
    public static function getPluginList()
    {
        return [
            'axisubs-app-payment-paypal' => 'axisubs-app-payment-paypal/plugin.php',
            'axisubs-app-coupon' => 'axisubs-app-coupon/plugin.php',
            'axisubs-app-content-restriction' => 'axisubs-app-content-restriction/plugin.php',
            'axisubs-app-payment-stripe' => 'axisubs-app-payment-stripe/plugin.php',
            'axisubs-app-taxes' => 'axisubs-app-taxes/plugin.php'
        ];
    }

    /**
     * To Verify the Existence of File.
     *
     * @param $folders
     */
    public static function verifyExistence(&$folders)
    {
        $path = WP_PLUGIN_DIR;
        foreach ($folders as $index => $folder) {
            if (!file_exists($path .'/'. $folder)) {
                unset($folders[$index]);
            }
        }
    }

    /**
     * To Activate supportive plugins by updating the wordpress option.
     */
    public static function activatePlugins()
    {
        $wordpress_plugin = get_option('active_plugins');
        $corePlugins = self::getPluginList();
        self::verifyExistence($corePlugins);
        foreach ($corePlugins as $index => $plugin) {
            if (!in_array($plugin, $wordpress_plugin)) {
                $wordpress_plugin[] = $plugin;
            }
        }
        update_option('active_plugins', $wordpress_plugin);
    }

    /**
     * Copy the folder and files
     * */
    public static function full_copy($source, $target)
    {
        try {
            if (is_dir($source)) {
                @mkdir($target);
                $d = dir($source);
                while (FALSE !== ($entry = $d->read())) {
                    if ($entry == '.' || $entry == '..') {
                        continue;
                    }
                    $Entry = $source . '/' . $entry;
                    if (is_dir($Entry)) {
                        self::full_copy($Entry, $target . '/' . $entry);
                        continue;
                    }
                    copy($Entry, $target . '/' . $entry);
                }

                $d->close();
            } else {
                copy($source, $target);
            }
        } catch (\Exception $e) {
            //
        }
        return true;
    }
}
