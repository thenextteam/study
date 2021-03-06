<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Db;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use think\Session;

class LoginController extends Controller
{
    // 用户登录表单
    public function index()
    {
        //获取当前用户
        if(Session::get('UserId')){
            $nowuser = new User;
            // $nowuser::get(Session::get('UserId'));
            $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
            return $this->error('您已登录', url('Index/index'));
        }
        else{
            
                $this->assign('nowuser','');
            
        }
        return $this->fetch();
    }

    // 处理用户提交的登录数据
    public function login()
    {
        // 接收post信息
        $Request = Request::instance();
        $captcha = $Request->post('veri');
        if(!captcha_check($captcha)){
            //验证失败
            return $this->error('验证码错误');
        }

        $postData = $Request->post();

        // 直接调用M层方法，进行登录。
        if (User::login($postData['username'], $postData['password'])) {
            //用户选择了自动登录
            if($Request->post('aulo/a')!=null){
                $Key="safe";
                //将登录信息，存放在Cookie中
                $Value = serialize(array(Session::get('UserId'),Session::get('UserName'),Session::get('NickName')));            
                $Str = md5($Value.$Key);
                //有效期30天
                setcookie('Login', $Str . $Value,time()+60*60*24*30,'/');
            }
            //登录后状态变成1
            Db::table('user')->where('user_id', Session::get('UserId'))->update(['status' => 1]);
            //登录同时根据积分计算用户等级
            User::userLv(Session::get('UserId'));
            return $this->success('登录成功', url('Index/index'));
        } else {
            return $this->error('用户名或密码错误', url('index'));
        }
    }

    // 注销
    public function logOut()
    {
        if (User::logOut()) {
            return $this->success('注销成功', url('index'));
        } else {
            return $this->error('注销失败', url('index'));
        }
    }

    public function forget()
    {
        //获取当前用户
        if(Session::get('UserId')){
            $nowuser = new User;
            // $nowuser::get(Session::get('UserId'));
            $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
        }
        else{
            $this->assign('nowuser','');
        }

        return $this->fetch();
    }

    //更新
    public function save()
    {
        // 接收post信息
        $Request = Request::instance();
        $captcha = $Request->post('veri');
        if(!captcha_check($captcha)){
            //验证失败
            return $this->error('验证码错误');
        }
        $user = db('user')->where('user_name',$Request->post('username'))->find();
        if($Request->post('email')==$user['user_email']){
            $User = User::get($user['user_id']);
            //记忆旧密码
            $oldpwd = $User->user_pwd;
            //新密码
            $User->user_pwd = md5($Request->post('password'));

            if(!$User->validate(true)->save()){
                //没有修改密码
                if($oldpwd==md5($Request->post('password'))){
                    return $this->error('您输入的密码是旧密码，无修改');
                }
                return $this->error('修改失败');
            }
            return $this->success('修改成功');
        }
    }
}