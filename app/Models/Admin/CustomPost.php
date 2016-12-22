<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;
use Axisubs\Helper;
use Corcel\Post;
use Herbert\Framework\Models\PostMeta;
class CustomPost extends Post{
    /**
     * The table associated with the model.
     *
     * @var string
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['title'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public static function createCustomPostItemPlan(){
        $postDB = Post::where('post_type', 'axisubs_plan')->get();
        $postTable = $postDB->first();
        if($postTable == null){
            $postTable = new Post();
            $postTable->post_name = 'plans';
            $postTable->post_title = 'Plans';
            $postTable->post_type = 'axisubs_plan';
            $postTable->post_content = '[AxisubsAllPlans]';
            $postTable->save();
        }
    }

    public static function createCustomPostItemSubscribe(){
        $postDB = Post::where('post_type', 'axisubs_subscribes')->get();
        $postTable = $postDB->first();
        if($postTable == null){
            $postTable = new Post();
            $postTable->post_name = 'subscribe';
            $postTable->post_title = 'Subscribe';
            $postTable->post_type = 'axisubs_subscribes';
            $postTable->post_content = '';
            $postTable->save();
        }
    }
}