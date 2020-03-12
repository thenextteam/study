<?php
namespace app\common\model;
use think\Model;    //  导入think\Model类
/**
 * User 用户表
 */

class User extends Model
{
    /**
     * 用户登录
     * @param  string $username 用户名
     * @param  string $password 密码
     * @return bool   成功返回true，失败返回false。
     */
    static public function login($username, $password)
    {
        // 验证用户是否存在
        $map = array('user_name' => $username);
        $User = self::get($map);
        
        if (!is_null($User)) {
            // 验证密码是否正确
            if ($User->checkPassword($password)) {
                // 登录
                
                //更新登录时间
                $User->user_lasttime = date('Y-m-d H:i:s', time());
                $User->save();
                session('UserId', $User->getData('user_id'));
                session('UserName', $User->getData('user_name'));
                session('NickName', $User->getData('nick_name'));
                return true;
            }
        }
        return false;
    }

     /**
     * 密码加密算法
     * @param    string                   $password 加密前密码
     * @return   string                             加密后密码
     */
    static public function encryptPassword($password)
    {   
        if (!is_string($password)) {
            throw new \RuntimeException("传入变量类型非字符串，错误码2", 2);
        }

        // 实际的过程中，我还还可以借助其它字符串算法，来实现不同的加密。
        return md5($password);
    }

    /**
     * 验证密码是否正确
     * @param  string $password 密码
     * @return bool           
     */
    public function checkPassword($password)
    {
		if ($this->getData('user_pwd') === $this::encryptPassword($password))
		{
			return true;
		} else {
			return false;
		}
    }

     /**
     * 注销
     * @return bool  成功true，失败false。
     */
    static public function logOut()
    {
        // 销毁session中数据
        session(null);
        return true;
    }

     /**
     * 判断用户是否已登录
     * @return boolean 已登录true
     */
    static public function isLogin()
    {
    	$UserId = session('UserId');

    	if(isset($UserId)){
    		return true;
    	}
    	else{
    		return false;
    	}
    }

    /**
     * 获取等级对应的名称
     */
    public function getUserlvAttr($value){
        $status = array('1'=>'[LV.1]新来乍到','2'=>'[LV.2]新手上路I','3'=>'[LV.3]新手上路II','4'=>'[LV.4]新手上路III');
        $lvname = $status[$value];
        if(isset($lvname)){
            return $lvname;
        }
        else{
            return $status[0];
        }
    }

    /**
     * 获取签到心情名称
     */
    public function getUserMoodAttr($value){
        // $status = array('0'=>'开心','1'=>'难过','2'=>'郁闷','3'=>'无聊','4'=>'怒','5'=>'擦汗','6'=>'奋斗','7'=>'慵懒','8'=>'衰','9'=>'该用户从未签到');
        // $lvname = $status[$value];
        // if(isset($lvname)){
        //     return $lvname;
        // }
        // else{
        //     return $status[0];
        // }
    }

    /**
     * 关联用户帖子
     */
    public function Article()
    {
        return $this->hasMany('Article');
    }

    /**
     * 关联用户回复
     */
    public function Comment()
    {
        return $this->hasMany('Comment');
    }
}