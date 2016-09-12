<?php

namespace Axisubs\Helper;

use Herbert\Framework\Notifier;

class AxisubsRedirect{

    /**
     * For Redirecting the page
     * */
    public static function redirect($url = '', $message = '', $type = 'success'){
        $html = '<html><head>';
        $html .= '<meta http-equiv="content-type" content="text/html;" />';
        $html .= '<script>document.location.href=\'' . str_replace("'", "&apos;", $url) . '\';</script>';
        $html .= '</head><body></body></html>';
        echo $html;
        exit;
    }
}