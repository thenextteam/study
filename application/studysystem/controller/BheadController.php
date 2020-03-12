<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\Bhead;      // 引入板块大类
use think\Request;
use think\Session;
use think\Db;

class BheadController extends Controller
{
    public function index()
    {
        $hid = Request::instance()->param('hid/d');
        if($hid==null){
            $hid=0;
        }
        $Bhead = Bhead::get($hid);
        // $allBoard=Db::name('board')->order('board_top desc')->select();
        $allBoard=$Bhead->board()->order('board_top desc')->select();
        $this->assign('Bhead',$Bhead);
        $this->assign('allBoard',$allBoard);
        return $this->fetch();
    }
}