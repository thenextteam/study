<?php
namespace app\admin\controller;

class BasicController extends \think\Controller
{
  public function __construct() {
    parent::__construct(); //一定要注意这一行，这一行是为了执行父类中的构造函数，否则运行是会出错的
    $this->CheckLogin(); 
}

public function CheckLogin(){
    if(!session('admin')){
        $this->error('暂未登录，请重新登录',url('Login/login'));
    }
}
}