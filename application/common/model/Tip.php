<?php
namespace app\common\model;
use think\Model;
/**
 * 举报表
 */
class Tip extends Model
{
    /**
     * 关联用户
     */
    public function User()
    {
        return $this->hasOne('User','user_id','user_id');
    }

    public function Board()
    {
        return $this->hasOne('Board','board_id','board_id');
    }

    public function Article()
    {
        return $this->hasOne('Article','art_id','art_id');
    }

    public function Comment()
    {
        return $this->hasOne('Comment','com_id','com_id');
    }

     /**
     * 获取状态的名称
     */
    public function getTipOpAttr($value){
        $status = array('0'=>'未处理','1'=>'审核正常','2'=>'已删除','3'=>'已锁定');
        $tipname = $status[$value];
        if(isset($tipname)){
            return $tipname;
        }
        else{
            return $status[0];
        }
    }
}