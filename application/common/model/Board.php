<?php
namespace app\common\model;
use think\Model;
/**
 * 板块
 */
class Board extends Model
{
    /**
     * 关联用户表
     */
	public function User()
	{
        return $this->belongsTo('User');
	}

    /**
     * 关联帖子表
     */
    public function Article()
    {
        //hasMany(表格名，外键名)
        return $this->hasMany('article','art_board_id');
    }

    /**
     * 关联举报表
     */
    public function Tip()
    {
        //hasMany(表格名，外键名)
        return $this->hasMany('tip','board_id','board_id');
    }

    /**
     * 关联版块大类表
     */
    public function Bhead()
    {
        //hasMany(表格名，外键名)
        return $this->belongsTo('bhead','bhead_id');
    }

    /**
     * 关联分类表
     */
    public function Atype()
    {
        return $this->hasMany('atype','board_id');
    }

    /**
     * 关联版主表
     */
    public function Boardadmin()
    {
        return $this->hasMany('boardadmin','board_id','board_id');
    }
    /**
     * 获取版主名字
     * $value user_id
     */
    public function GetBAname($value)
    {
        return db('User')->where('user_id',$value)->field('nick_name')->find();
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
     * 获取有最新回复的帖子
     */
    public function LastComArt($value)
    {
        $join = [
            ['user b','a.user_id=b.user_id'],
        ];
        return db('article')->alias('a')->where('art_board_id',$value)->where('art_status',0)->order('last_com_time desc')->join($join)->find();

        // return db('Article')->where('art_board_id',$value)->order('last_com_time desc')->find();
    }
    
    /**
     * 获取该版块当天的新帖子
     * 
     */
    public function GetNewArt($value)
    {
        //首页获取所有当天帖子
        if(trim($value) == "all"){
            $Article = db('article')
                ->where('art_time','< time',date('Y-m-d', time()).'23:59:59')
                ->where('art_time','> time',date('Y-m-d', time()).'00:00:00')
                ->count('art_id');
            return $Article;
        }
        //首页获取所有昨天帖子
        else if(trim($value) == "yes"){
            $Article = db('article')
                ->where('art_time','< time',date('Y-m-d', time()-86400).'23:59:59')
                ->where('art_time','> time',date('Y-m-d', time()-86400).'00:00:00')
                ->count('art_id');
            return $Article;
        }
        else{
            //版块下当天的帖子
            $Article = db('article')
                ->where('art_board_id',$value)
                ->where('art_time','< time',date('Y-m-d', time()).'23:59:59')
                ->where('art_time','> time',date('Y-m-d', time()).'00:00:00')
                ->count('art_id');
            return $Article;
        }
    }


    /**
     * 获取帖子总浏览数
     */
    public function GetView($value)
    {
        $where['art_status'] = [ ['=',0], ['=',2] ,'or'];
        return db('article')->where('art_board_id',$value)->where($where)->sum('art_view');
    }

    /**
     * 获取版块的总回复数
     */
    public function GetComnum($value)
    {
        //获取当前版块下的所有帖子
        $where['art_status'] = [ ['=',0], ['=',2] ,'or'];
        $arts = db('article')->where('art_board_id',$value)->where($where)->select();
        $num = 0;
        //遍历每个帖子下的回复总数并进行相加
        foreach($arts as $art)
        {
            $com = db('comment')->where('art_id',$art['art_id'])->where('com_status',0)->count('com_id');
            $num = $num+$com;
        }
        return $num;
    }

    /**
     * 版块下举报数量
     */
    public function tipnum($value)
    {
        return db('Tip')->where('board_id',$value)->where('tip_op',0)->count('tip_id');
    }

    /**
     * 获取该版块所有帖子数量
     */
    public function GetAllart($value)
    {
        $where['art_status'] = [ ['=',0], ['=',2] ,'or'];
        return $this->Article()->where($where)->count('art_id');
    }
}