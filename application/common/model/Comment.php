<?php
namespace app\common\model;
use think\Model;
/**
 * 回复
 */
class Comment extends Model
{
    /**
     * 关联用户表
     */
	public function User()
	{
        return $this->belongsTo('User');
	}

    /**
     * 关联评分表
     */
    public function Grade()
    {
        //hasMany(表格名，外键名)
        return $this->hasMany('Grade','com_id','com_id');
    }
}