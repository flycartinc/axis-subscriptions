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
     * Cart constructor.
     */
    public function __construct()
    {

    }

    /**
     *  To Trigger the Installation of Supportive plugins.
     */
    public static function installAdditionalPlugins()
    {
        $oldfolderpath = __DIR__ . '/../../../additional_apps/';
        $newfolderpath = __DIR__ . '/../../../../';
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
            'axisubs-app-payment-paypal' => 'axisubs-app-payment-paypal/plugin.php'
        ];
    }

    /**
     * To Verify the Existence of File.
     *
     * @param $folders
     */
    public static function verifyExistence(&$folders)
    {
        $path = __DIR__ . '/../../../../';
        foreach ($folders as $index => $folder) {
            if (!file_exists($path . $folder)) {
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