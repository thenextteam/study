<?php
namespace app\admin\controller;
use think\Request;
use app\common\model\Admin;

class LoginController extends \think\Controller{

    public function index(){
        
      return view('login');
    }


    public function postLogin(){
        $post = json_decode($_POST['post'], 1);
        $Admin = new Admin();
        $admin = $Admin
                 ->field('admin_id, admin_username')
                 ->where('admin_username = "'.$post['username'].'" and admin_pwd ="'.$post['password'].'"')
                 ->find();

        if($admin){
            session('admin',$admin);
            $data = [];
            $data['msg'] = '登录成功';
            $data['status']=1;
            $data['url']=url('admin/Index/index');
            echo json_encode($data);
        }else{
            echo json_encode(['msg'=>'用户名或密码错误','status'=>0]);
        }         
        
    }

}
