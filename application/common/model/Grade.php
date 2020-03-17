<?php
namespace app\common\model;
use think\Model;
/**
 * 评分表
 */
class Grade extends Model
{
    /**
     * 关联用户表
     */
	public function User()
	{
        return $this->hasOne('User','user_id','user_id');
    }
}