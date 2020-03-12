<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型

class LoginController extends Controller
{
    // 用户登录表单
    public function index()
    {
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
        };

        $postData = $Request->post();

        // 直接调用M层方法，进行登录。
        if (User::login($postData['username'], $postData['password'])) {
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

}