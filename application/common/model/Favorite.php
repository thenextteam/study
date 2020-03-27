<?php
namespace app\common\model;
use think\Model;
/**
 * 收藏
 */
class Favorite extends Model
{
    public function Board()
    {
        return $this->hasOne('Board','board_id','board_id');
    }

    public function Article()
    {
        return $this->hasOne('Article','art_id','art_id');
    }

    public function User()
    {
        return $this->hasOne('User','user_id','user_id');
    }
}