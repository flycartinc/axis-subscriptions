<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers;

use Axisubs\Models\Plans;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;

class AdminController
{
    public function index(Http $http)
    {
        $role_names = wp_roles()->role_names;
        $pagetitle = 'Plans';
        if ($http->has('task')) {
            if ($http->get('task') == 'new') {
                $item = array();
                $pagetitle = 'Add new plan';
                return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names'));
            } else if ($http->get('task') == 'save') {
                $axisubPost = $http->get('axisubs');
                if (isset($axisubPost['plans'])) {
                    $result = Plans::savePlans($http->all());
                    $pagetitle = 'Edit Plan';
                    if ($result) {
                        Notifier::success('Saved successfully');
                        $item = Plans::loadPlan($result);
                        return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names'));
                    } else {
                        $item = $axisubPost['plans'];
                        Notifier::error('Failed to save');
                        return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names'));
                    }
                }
            } else if ($http->get('task') == 'edit') {
                if ($http->get('id')) {
                    $item = Plans::loadPlan($http->get('id'));
                    $pagetitle = 'Edit Plan';
                    return view('@Axisubs/Admin/plan/edit.twig', compact('pagetitle', 'item', 'role_names'));
                }
            }
        }
        // Load Listing layout
        $items = Plans::all();
        return view('@Axisubs/Admin/plans/list.twig', compact('pagetitle', 'items'));
    }

    public function ajaxCall(Http $http)
    {
        $task = $http->get('task');
        $planType = $http->get('type');
        $id = $http->get('id');
        $item = Plans::loadPlan($id);
        if($task == 'loadPlanFields'){
            if($planType != ''){
                return view('@Axisubs/Admin/plan/types/'.$planType.'.twig', compact('item'));
            } else {
                return '';
            }
        }
    }
}