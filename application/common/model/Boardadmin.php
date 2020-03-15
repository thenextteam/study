<?php
namespace app\common\model;
use think\Model;
/**
 * 板主
 */
class Boardadmin extends Model
{
    
    /**
     * 关联用户表
     */
	public function User()
	{
        return $this->belongsTo('User');
	}
}