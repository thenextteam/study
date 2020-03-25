<?php
namespace app\admin\controller;
use think\Request;

class LoginController extends \think\Controller{

    public function login(){
        
      return view('login');
    }


    public function postLogin(){
        $post = json_decode($_POST['post'], 1);
        $Admin = M('manager');
        $admin = $Admin
                 ->field('aid, username')
                 ->where('username = "'.$post['username'].'" and password ="'.md5($post['password']).'"')
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
