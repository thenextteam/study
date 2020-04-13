<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Db;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use think\Session;

class RegisterController extends Controller
{
    // 用户注册
    public function index()
    {
        //获取当前用户
        if(Session::get('UserId')){
            $nowuser = new User;
            // $nowuser::get(Session::get('UserId'));
            $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
            return $this->error('请先注销当前账号，再进行注册', url('Index/index'));
        }
        else{
            $this->assign('nowuser','');
        }
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
        //默认用户在im上的状态为hide
        $captcha = $Request->post('veri');
        if(!captcha_check($captcha)){
            //验证失败
            return $this->error('验证码错误');
        }

        // 添加数据
        if(!$User->validate(true)->save()){
            return $this->error('注册失败：'.$User->getError());
        }
        //获取新注册用户id
        $u_id = Db::table('user')->where('user_name',$User->user_name)->find();
        $data = ['groupname' => '好友','user_id'=>$u_id['user_id']];
        $gdata = ['gid' => 1,'uid'=>$u_id['user_id']];
        //插入默认im好友分组及用户id以及加入总群
        Db::table('groupmember')->insert($gdata);
        Db::table('fgroupname')->insert($data);
        return $this->success('注册成功', 'Login/index');
    }

    //对比是否有相同的用户名
    public function sameuser()
    {
        if(request()->isAjax()){
            $uname = input('uname');
            $User = new User;
            $ucount = $User->where('user_name',$uname)->count('user_id');
            if($ucount>0){
                return true;
            }
            return false;
        }
    }
}