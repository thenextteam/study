<?php
namespace app\common\model;
use think\Model;
/**
 * 帖子删除·置顶提醒
 */
class Aremind extends Model
{
    /**
     * 关联版块表
     */
    public function Board()
    {
        return $this->hasOne('Board','board_id','board_id');
    }
    //获取被回复人
    public function User(){
        return $this->belongsTo('User','user_id');
    } 
    //获取操作人（版主）
    public function aoUser(){
        return $this->belongsTo('User','ao_user_id');
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
}