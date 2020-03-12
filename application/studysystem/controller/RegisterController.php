<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型

class RegisterController extends Controller
{
    // 用户注册
    public function index()
    {
        return $this->fetch();
    }

    // 注册
    public function register()
    {
        // 接收post信息
        $Request = Request::instance();
        // var_dump($Request);
        // 实例化用户并赋值
        $User = new User();
        $User->user_name = $Request->post('user');
        $User->user_email = $Request->post('email');
        $User->user_pwd = md5($Request->post('pwd'));
        $captcha = $Request->post('veri');
        if(!captcha_check($captcha)){
            //验证失败
            return $this->error('验证码错误');
        }

        // 添加数据
        if(!$User->validate(true)->save()){
            return $this->error('注册失败：'.$User->getError());
        }
        return $this->success('注册成功', 'Login/index');
    }
}