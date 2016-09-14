<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Admin;

use Axisubs\Models\Site\Plans;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper\Pagination;
use Axisubs\Models\Admin\Customers;
use Axisubs\Models\Admin\Config;
use Axisubs\Models\Admin\Subscriptions;
use Axisubs\Controllers\Controller;
use Axisubs\Models\Admin\App;
use Axisubs\Models\Admin\Dashboard as ModelDashboard;

class Dashboard extends Controller
{
    public $_controller = 'Dashboard';

    /**
     * Default page
     * */
    public function index()
    {
        $config = Config::getInstance();
        $http = Http::capture();
        $pagetitle = 'Dashboard';
        $data['planCount'] = Plans::getTotal();
        $data['subscriptionCount'] = Subscriptions::getTotal();
        $data['customerCount'] = Customers::getTotal();
        $data['appCount'] = count(App::getAllApps());
        $data['todaySales'] = count(ModelDashboard::getTodaySale());
        $data['todayTarget'] = $config->getConfigData('daily_target', 5);
        $data['totalPrice'] = ModelDashboard::getTotalSalePrice();
        $data['totalTarget'] = $config->getConfigData('all_time_target', 2000);
        $data['totalPending'] = count(ModelDashboard::getTotalPending());
        return view('@Axisubs/Admin/dashboard/default.twig', compact('pagetitle', 'data'));
    }
}