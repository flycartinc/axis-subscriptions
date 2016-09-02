<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Admin\Config as ModelConfig;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Controllers\Controller;

class Config extends Controller
{
    public $_controller = 'Config';

    /**
     * Default page
     * */
    public function index()
    {
        $all = ModelConfig::all();
        $pagetitle = "Configuration";
        return view('@Axisubs/Admin/config/edit.twig', compact('all', 'pagetitle'));
    }

    /**
     * Save
     * */
    public function save()
    {
        $http = Http::capture();
        $axisubPost = $http->get('axisubs');
        if (isset($axisubPost['config'])) {
            $result = ModelConfig::saveConfig($http->all());
            if ($result) {
                Notifier::success('Saved successfully');
            } else {
                Notifier::error('Failed to save');
            }
        }
        return $this->index();
    }
}