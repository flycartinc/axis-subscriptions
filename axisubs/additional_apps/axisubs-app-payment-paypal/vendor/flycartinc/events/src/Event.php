<?php
namespace Events;
use Events\Wordpress\Wordpress;

class Event
{
    public static $platform = 'wordpress';

    public static function listen( $tag, $callback, $priority, $type = 'action' )
    {
        if ( self::$platform === 'wordpress' )
        {
            if ( $type == 'filter')
            {
                Wordpress::AddFilter($tag, $callback, $priority);
            }
            else
            {
                Wordpress::AddAction($tag, $callback, $priority);
            }
        }
    }

    public static function trigger( $tag, $args, $type = 'action' )
    {
        if ( self::$platform === 'wordpress' )
        {
            if ( $type == 'filter' )
            {
                return Wordpress::ApplyFilter($tag, $args);
            }
            else
            {
                Wordpress::TriggerAction($tag, $args);
            }
        }
    }

}