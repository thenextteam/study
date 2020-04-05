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
        $status = array(
            '1'=>'[LV.1]新来乍到',
            '2'=>'[LV.2]新手上路I',
            '3'=>'[LV.3]新手上路II',
            '4'=>'[LV.4]新手上路III',
            '5'=>'[LV.5]新进会员I',
            '6'=>'[LV.6]新进会员II',
            '7'=>'[LV.7]新进会员III',
            '8'=>'[LV.8]论坛高手I',
            '9'=>'[LV.9]论坛高手II',
            '10'=>'[LV.10]论坛高手III',
            '11'=>'[LV.11]大佬之路I',
            '12'=>'[LV.12]大佬之路II',
            '13'=>'[LV.13]大佬之路III',
            '14'=>'[LV.14]大佬之路IV',
            '15'=>'[LV.15]论坛大佬',
        );
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
     * 关联收藏表
     */
    public function Favorite()
    {
        return $this->hasMany('Favorite','user_id','user_id');
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
     * @param  string $friuser 被申请的用户ID
     * @param  string $user 申请添加好友的用户ID
     * @return bool/string   正确执行则返回true，否则返回"ismut"表示已存在。
     */
    static public function newfriend($friuser, $user)
    {
        //如果记录已存在，不允许重复申请
        if(db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',1)->count('friend_id')>0){
            return 'ismut';
        }
        
        $Friend = new Friend;
        $Friend->user_id = $friuser;
        $Friend->friend_user_id = $user;

        //还要验证是否已经有对方向自己的申请记录是的话ismut变为1
        $isfripo_num = db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',0)->count('friend_id');
        if($isfripo_num>0){
            //对方已经向自己提出申请，将那条记录改为1，即自己是对方的好友了

            //申请通过就更新用户自己的分组和对方的分组
            $user_gid = Db::table('fgroupname')->where('user_id',$user)->find();//自己的
            $fri_gid = Db::table('fgroupname')->where('user_id',$friuser)->find();//对方的


            db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',0)->update(['ismut' => '1','gid'=>$user_gid['id']]);
            //自己这条记录也设置1，即对方是自己的好友了
            $Friend->ismut = 1;
            if ($gid['groupname'] = "好友"){
                $Friend->gid = $fri_gid['id'];
            }
        }
        $Friend->save();
        return true;
    }

    /**
     * 根据积分升级
     * @param  string $value 用户ID
     */
    static public function userLv($value)
    {
        $point = db('user')->where('user_id',$value)->field('user_point')->find()['user_point'];
        $lv =db('user')->where('user_id',$value)->field('user_lv')->find()['user_lv'];
        if($point>=200&&$point<500&&$lv<2){
            db('user')->where('user_id',$value)->update(['user_lv' => 2]);
        }
        else if($point>=500&&$point<850&&$lv<3){
            db('user')->where('user_id',$value)->update(['user_lv' => 3]);
        }
        else if($point>=850&&$point<1250&&$lv<4){
            db('user')->where('user_id',$value)->update(['user_lv' => 4]);
        }
        else if($point>=1250&&$point<1700&&$lv<5){
            db('user')->where('user_id',$value)->update(['user_lv' => 5]);
        }
        else if($point>=1700&&$point<2200&&$lv<6){
            db('user')->where('user_id',$value)->update(['user_lv' => 6]);
        }
        else if($point>=2200&&$point<2750&&$lv<7){
            db('user')->where('user_id',$value)->update(['user_lv' => 7]);
        }
        else if($point>=2750&&$point<3350&&$lv<8){
            db('user')->where('user_id',$value)->update(['user_lv' => 8]);
        }
        else if($point>=3350&&$point<4000&&$lv<9){
            db('user')->where('user_id',$value)->update(['user_lv' => 9]);
        }
        else if($point>=4000&&$point<4700&&$lv<10){
            db('user')->where('user_id',$value)->update(['user_lv' => 10]);
        }
        else if($point>=4700&&$point<5450&&$lv<11){
            db('user')->where('user_id',$value)->update(['user_lv' => 11]);
        }
        else if($point>=5450&&$point<6250&&$lv<12){
            db('user')->where('user_id',$value)->update(['user_lv' => 12]);
        }
        else if($point>=6250&&$point<7100&&$lv<13){
            db('user')->where('user_id',$value)->update(['user_lv' => 13]);
        }
        else if($point>=7100&&$point<9000&&$lv<14){
            db('user')->where('user_id',$value)->update(['user_lv' => 14]);
        }
        else if($point>=9000&&$lv<15){
            db('user')->where('user_id',$value)->update(['user_lv' => 15]);
        }
    }

    //备用暂留
    // /**
    //  * 添加好友
    //  * @param  string $user 被申请的用户ID
    //  * @param  string $friuser 申请添加好友的用户ID
    //  * @return bool   返回true。
    //  */
    // static public function newfriend($user, $friuser)
    // {
    //     //如果记录已存在，不允许重复申请
    //     if(db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',1)->count('friend_id')>0){
    //         return 'ismut';
    //     }

    //     $Friend = new Friend;
    //     $Friend->user_id = $user;
    //     $Friend->friend_user_id = $friuser;

    //     //还要验证是否已经有对方向自己的申请记录是的话ismut变为1
    //     $isfripo_num = db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',0)->count('friend_id');
    //     if($isfripo_num>0){
    //         //对方已经向自己提出申请，将那条记录改为1，即自己是对方的好友了
    //         db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',0)->update(['ismut' => '1']);
    //         //自己这条记录也设置1，即对方是自己的好友了
    //         $Friend->ismut = 1;
    //     }
    //     $Friend->save();
    //     return true;
    // }
}