<?php

namespace app\studysystem\controller;
//命名空间，也说明了文件所在的文件夹
use think\Controller;
use think\Session;
use think\Request;
use think\Db;

class FindController extends Controller
{
    public function index()
    {
        $group = Db::table('groups')->select();
        $this->assign('group', $group);
        return $this->fetch('Find/find');
    }

    public function joinGroup(){
        $gid =  $_POST['gid'];

    }
}