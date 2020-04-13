<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Bhead;      // 引入板块大类
use think\Request;
use think\Session;
use think\Db;

class BheadController extends Controller
{
    public function index()
    {
        //获取当前用户
        if(Session::get('UserId')){
            $nowuser = new User;
            // $nowuser::get(Session::get('UserId'));
            $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
        }
        else{
            $Key="safe";
            //如果Session中没有登录信息，尝试从Cookie中加载用户信息
            if(isset($_COOKIE['Login'])){
                $Value = $_COOKIE['Login'];
                // 去掉魔术引号
                if (get_magic_quotes_gpc()) {
                    $Value = stripslashes($Value);
                }
                $Str = substr($Value,0,32);
                $Value = substr($Value,32);
                //校验
                if (md5($Value . $Key) == $Str) {
                    $User = unserialize($Value);
                    session('UserId', $User[0]);
                    session('UserName', $User[1]);
                    session('NickName', $User[2]);
                    $nowuser = new User;
                    $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
                }
            }
            else{
                $this->assign('nowuser','');
            }
        }
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