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
use Axisubs\Helper\Status;
use Axisubs\Helper\Currency;

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
        $data['last_five_subscription'] = ModelDashboard::getLastFiveSubscriptions();
        $status = new Status();
        $data['status_codes'] = $status->getAllStatusCodesWithHtml();
        $currency = new Currency();
        $currencyData['code'] = $currency->getCurrencyCode();
        $currencyData['currency'] = $currency->getCurrency();
        $data['last_year_statistics'] = ModelDashboard::getLastYearStatistics();
        $data['this_year_statistics'] = ModelDashboard::getThisYearStatistics();
        $data['last_month_statistics'] = ModelDashboard::getLastMonthStatistics();
        $data['this_month_statistics'] = ModelDashboard::getThisMonthStatistics();
        $data['last_7days_statistics'] = ModelDashboard::getLastSevenDaysStatistics();
        $data['yesterday_statistics'] = ModelDashboard::getYesterdayStatistics();
        $data['today_statistics'] = ModelDashboard::getTodayStatistics();
        $data['total_active_subscriptions'] = ModelDashboard::getTotalActiveSubscriptions();
        $data['plugin_details'] = get_plugin_data(AXISUBS_PLUGIN_PATH.'plugin.php');
        
        return view('@Axisubs/Admin/dashboard/default.twig', compact('pagetitle', 'data', 'currencyData'));
    }
}