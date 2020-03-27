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

    // 时间处理
    public function TimeHandle($targetTime)
    {
        // 今天最大时间
        $targetTime = strtotime($targetTime);
        $todayLast = strtotime(date('Y-m-d 23:59:59'));
        $agoTimeTrue = time() - $targetTime;
        $agoTime = $todayLast - $targetTime;
        $agoDay = floor($agoTime / 86400);
        
        if ($agoTimeTrue < 60) {
            $result = '刚刚';
        } elseif ($agoTimeTrue < 3600) {
            $result = (ceil($agoTimeTrue / 60)) . '分钟前';
        } elseif ($agoTimeTrue < 3600 * 12) {
            $result = (ceil($agoTimeTrue / 3600)) . '小时前';
        } elseif ($agoDay == 0) {
            $result = '今天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 1) {
            $result = '昨天 ' . date('H:i', $targetTime);
        } elseif ($agoDay == 2) {
            $result = '前天 ' . date('H:i', $targetTime);
        } elseif ($agoDay > 2 && $agoDay < 5) {
            $result = $agoDay . '天前 ' . date('H:i', $targetTime);
        } else {
            $format = date('Y') != date('Y', $targetTime) ? "Y-m-d H:i" : "m-d H:i";
            $result = date($format, $targetTime);
        }
        return $result;
    }

    /**
     * 判断是否已收藏
     * @param artid 帖子ID
     * @param userid 用户ID
     */
    public function isFav($artid,$userid)
    {
        $isfav = db('favorite')->where('user_id',$userid)->where('art_id',$artid)->count('favorite_id');
        if($isfav>0){
            //已收藏
            return 1;
        }
        //未收藏
        return 0;
    }
}