<?php
namespace app\common\model;
use think\Model;
/**
 * 回复
 */
class Comment extends Model
{
	public function User()
	{
        return $this->belongsTo('User');
	}
}