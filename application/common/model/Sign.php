<?php
namespace app\common\model;
use think\Model;
/**
 * 签到表
 */
class Sign extends Model
{
    /**
     * 计算用户的总签到数
     */
    public function getAllSign($value){
        return db('sign')->where('user_id',$value)->count('sign_id');
    }
}