<?php
/**
 * Created by PhpStorm.
 * User: aron-destiny
 * Date: 12/7/16
 * Time: 1:25 PM
 */

namespace Axisubs\Models\Admin;

use Herbert\Framework\Models\Post;
use Herbert\Framework\Models\PostMeta;

class Config extends Post
{
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


    /**
     * Settings constructor.
     */

    public static function all($columns = ['*'])
    {
        $item = parent::all()->where('post_type', 'axisubs_config')->first();
        if(!empty($item)) {
            if ($item->meta() != null) {
                $item->meta = $item->meta()->pluck('meta_value', 'meta_key')->toArray();
            }
        }
        return $item;
    }

    // save Config
    public static function saveConfig($post)
    {
        $postDB = \Corcel\Post::where('post_type', 'axisubs_config')->get();
        $postTable = $postDB->first();
        if ($postDB->count() == 0) {
            $postTable = new Post();
            $postTable->post_name = 'config';
            $postTable->post_title = 'Config';
            $postTable->post_type = 'axisubs_config';
            $postTable->save();
        }
        foreach ($post['axisubs']['config'] as $key => $val) {
            $key = $postTable->ID . '_axisubs_config_' . $key;
            $postTable->meta->$key = $val;
        }
        $result = $postTable->save();

        return $result;
    }
}