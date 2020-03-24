<?php
namespace app\common\model;
use think\Db;
use think\Model;
use think\Session;    //  导入think\Model类
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
        Db::table('user')->where('user_id', Session::get('UserId'))->update(['status' => 0]);
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

    /**
     * 关联好友表
     */
    public function Friend()
    {
        return $this->hasMany('Friend','user_id','user_id');
    }

    /**
     * 获取用户心情
     */
    public function getSign($value)
    {
        $signid = $this->where('user_id',$value)->field('sign_id')->find()['sign_id'];
        $usermood = db('Sign')->where('sign_id',$signid)->field('user_mood')->find()['user_mood'];

        $status = array('0'=>'开心','1'=>'难过','2'=>'郁闷','3'=>'无聊','4'=>'怒','5'=>'擦汗','6'=>'奋斗','7'=>'慵懒','8'=>'衰','9'=>'该用户从未签到');
        $umname = $status[$usermood];
        if(isset($umname)){
            return $umname;
        }
        else{
            return $status[9];
        }
    }

    /**
     * 添加好友
     * @param  string $user 被申请的用户ID
     * @param  string $friuser 申请添加好友的用户ID
     * @return bool   返回true。
     */
    static public function newfriend($user, $friuser)
    {
        //如果记录已存在，不允许重复申请
        if(db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',1)->count('friend_id')>0){
            return 'ismut';
        }
        
        $Friend = new Friend;
        $Friend->user_id = $user;
        $Friend->friend_user_id = $friuser;

        //还要验证是否已经有对方向自己的申请记录是的话ismut变为1
        $isfripo_num = db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',0)->count('friend_id');
        if($isfripo_num>0){
            //对方已经向自己提出申请，将那条记录改为1，即自己是对方的好友了

            //申请通过就更新用户自己的分组和对方的分组
            $user_gid = Db::table('fgroupname')->where('user_id',$user)->find();//自己的
            $fri_gid = Db::table('fgroupname')->where('user_id',$friuser)->find();//对方的


            db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',0)->update(['ismut' => '1','gid'=>$fri_gid['id']]);
            //自己这条记录也设置1，即对方是自己的好友了
            $Friend->ismut = 1;
            if ($gid['groupname'] = "好友"){
                $Friend->gid = $user_gid['id'];
            }
        }
        $Friend->save();
        return true;
    }
}