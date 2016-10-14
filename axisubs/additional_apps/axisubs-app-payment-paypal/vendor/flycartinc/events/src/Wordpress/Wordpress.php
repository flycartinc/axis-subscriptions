<?php
namespace Events\Wordpress;

class Wordpress {

    public static function AddAction( $tag, $callback, $priority = 10, $accepted_args = 1 )
    {
        add_action( $tag, $callback, $priority, $accepted_args );
    }

    public static function TriggerAction( $tag, $args = array() )
    {
        if ( !empty( $args ) ) {
            $args = array_filter( $args );
            do_action( $tag, $args );
        } else {
            do_action( $tag );
        }
    }

    public static function AddFilter( $tag, $callback, $priority = 10, $accepted_args = 1 )
    {
        add_filter ( $tag, $callback, $priority, $accepted_args );
    }

    public static function ApplyFilter( $tag, $args ){
        return apply_filters( $tag, $args );
    }
    
}