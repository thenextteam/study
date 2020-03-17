<?php
namespace app\common\model;
use think\Model;
/**
 * 帖子
 */
class Article extends Model
{
    /**
     * 关联用户表
     */
    public function User()
    {
        return $this->belongsTo('User');
    }

    /**
     * 关联回复表
     */
    public function Comment()
    {
        //hasMany(表格名，外键名)
        return $this->hasMany('Comment','art_id');
    }

    /**
     * 关联板块表
     */
    public function Board()
    {
        return $this->belongsTo('Board','art_board_id');
    }

    /**
     * 关联类别表
     */
    public function Atype()
    {
        return $this->hasOne('Atype','atype_id','atype_id');
    }

    /**
     * 关联评分表
     */
    public function Grade()
    {
        //hasMany(表格名，外键名)
        return $this->hasMany('Grade','art_id','art_id');
    }

    /**
     * 获取最新回复
     */
    public function GetLastCom($value)
    {
        if(isset($value)){
            $LastCom = db('Comment')->where('art_id',$value)->where('com_status','0')->order('com_id desc')->find();
            if(isset($LastCom)){return $LastCom;}
        }
    }

    /**
     * 获取回复数
     */
    public function GetComNum($value)
    {
        if(isset($value)){
            $ComNum = db('Comment')->where('art_id',$value)->where('com_status','0')->count('com_id');
            return $ComNum;
        }
    }

    /**
     * 计算第几页
     */
    public function getPage($value)
    {
        return ceil($value/10);
    }
}