<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 12:52 PM
 */
namespace Axisubs\Controllers\Site;

use Axisubs\Models\Site\Plans;
use Axisubs\Helper\Currency;
use Herbert\Framework\Http;
use Herbert\Framework\Notifier;
use Axisubs\Helper;
use Axisubs\Controllers\Controller;
use Axisubs\Helper\ManageUser;
use Axisubs\Helper\Countries;

class Profile extends Controller{

    public $_controller = 'Profile';
    public $_path = 'Site';

    /**
     * Show My Profile Default layout
     * */
    public function index(){
        $site_url = get_site_url();
        $pagetitle = "Subscriptions";
        $subscribtions_url = get_site_url().'/index.php?axisubs_subscribes=subscribe';
        $user = Plans::getUserDetails();
        $wp_user = ManageUser::getInstance()->getUserDetails();
        $wp_userD = array();
        $wp_userD['ID'] = $wp_user->ID;
        $wp_userD['user_name'] = $wp_user->user_login;
        $wp_userD['first_name'] = $wp_user->first_name;
        $wp_userD['last_name'] = $wp_user->last_name;
        if(isset($wp_user->data->user_email)) {
            $wp_userD['email'] = $wp_user->data->user_email;
        }
        $custProvince = $custCountry = '';
        if(!empty($user)){
            $custPrefix = $user->ID.'_'.$user->post_type.'_';
            $custProvince = $user->meta[$custPrefix.'province'];
            $custCountry =$user->meta[$custPrefix.'country'];
        }
        $modelZone = $this->getModel('Zones', 'Admin');
        $data['country'] = Countries::getCountriesSelectBox($custCountry, 'axisubs[subscribe][country]', 'axisubs_subscribe_country', 'required');
        $data['province'] = $modelZone->getProvinceSelectBox($custCountry, $custProvince, 'axisubs[subscribe][province]', 'axisubs_subscribe_province', 'required');
        return view('@Axisubs/Site/myprofile/myprofile.twig', compact('pagetitle', 'data', 'subscribtions_url', 'site_url', 'user', 'wp_userD'));
    }
}