<?php

/** @var  \Herbert\Framework\Application $container */

use Axisubs\customPostTypes\Axisubs_Plan;
use Axisubs\customPostTypes\Axisubs_Subscribe;
use Axisubs\Models\CustomPost;
//initialise product custom post type
(new Axisubs_Plan)->register();

(new Axisubs_Subscribe)->register();

CustomPost::createCustomPostItemPlan();
CustomPost::createCustomPostItemSubscribe();

