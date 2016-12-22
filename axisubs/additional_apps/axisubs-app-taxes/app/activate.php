<?php

/** @var  \Herbert\Framework\Application $container */
/** @var  \Herbert\Framework\Http $http */
/** @var  \Herbert\Framework\Router $router */
/** @var  \Herbert\Framework\Enqueue $enqueue */
/** @var  \Herbert\Framework\Panel $panel */
/** @var  \Herbert\Framework\Shortcode $shortcode */
/** @var  \Herbert\Framework\Widget $widget */

use Illuminate\Database\Capsule\Manager as Capsule;

if(!(Capsule::schema()->hasTable('axisubs_taxrates'))){
    Capsule::schema()->create('axisubs_taxrates', function($table)
    {
        $table->increments('axisubs_taxrate_id');
        $table->string('tax_rate_country');
        $table->string('tax_rate_state');
        $table->string('tax_rate');
        $table->string('tax_rate_name');
        $table->integer('tax_rate_priority');
        $table->integer('tax_rate_compound');
        $table->integer('tax_rate_shipping');
        $table->integer('tax_rate_order');
        $table->string('tax_rate_class');
    });
}

if(!(Capsule::schema()->hasTable('axisubs_taxratelocations'))){
    Capsule::schema()->create('axisubs_taxratelocations', function($table)
    {
        $table->increments('axisubs_taxratelocation_id');
        $table->string('location_code');
        $table->integer('tax_rate_id');
        $table->string('location_type');
    });
}