<?php
namespace app\common\model;
use think\Model;
/**
 * 好友
 */
class Friend extends Model
{
    public function FriendUser($value)
    {
        $User = User::get($value);
        return $User;
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