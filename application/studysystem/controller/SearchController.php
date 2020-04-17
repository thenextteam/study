<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;      // 引入用户
// use app\common\model\Bhead;      // 引入板块大类
use app\common\model\Article;      // 引入帖子
// use app\common\model\Board;      // 引入板块
use app\common\model\Comment;      // 引入回复
use think\Session;
use think\Db;

class SearchController extends Controller
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
                    //更新登录时间
                    $IsUser = User::get($User[0]);
                    $IsUser->user_lasttime = date('Y-m-d H:i:s', time());
                    $IsUser->save();
                }
            }
            else{
                $this->assign('nowuser','');
            }
        }
        
        $Request = Request::instance();
        $seakey = $Request->param('seakey');

        //搜索类型，帖子/用户，默认帖子
        $type = $Request->param('type');
        if($type==null){$type = 'art';}
        
        if(strpos($seakey,' ')){
            $pararr = explode(' ', $seakey);
            if(sizeof($pararr)>3){
                return $this->error('搜索词超过3个！');
            }
            else{
                if(!isset($pararr[2])){
                    $pararr[2]=null;
                }
            }

            if($type=='art'){
                $Article = new Article;
                $Articles = $Article->where('art_title','like','%'.$pararr[0].'%')->where('art_title','like','%'.$pararr[1].'%')->where('art_title','like','%'.$pararr[2].'%')->whereOr(function ($query) use ($pararr) {
                    $query->where('text','like','%'.$pararr[0].'%')->where('text','like','%'.$pararr[1].'%')->where('text','like','%'.$pararr[2].'%');})->order('art_lasttime desc')->paginate(10,false,[
                    'query' => [
                        'seakey' => $seakey,
                        'type' =>'art',
                        ]
                    ]);
                $this->assign('Articles',$Articles);
                // 获取分页显示
                $page = $Articles->render();
            }
            else{
                $User = new User;
                $Users = $User->where('nick_name','like','%'.$pararr[0].'%')->where('nick_name','like','%'.$pararr[1].'%')->where('nick_name','like','%'.$pararr[2].'%')->paginate(20,false,[
                    'query' => [
                        'seakey' => $seakey,
                        'type' =>'user',
                        ]
                    ]);
                $this->assign('Users',$Users);
                // 获取分页显示
                $page = $Users->render();
            }

        }
        else{
            if($type=='art'){
                $Article = new Article;
                $Articles = $Article->where('art_title','like','%'.$seakey.'%')->whereOr('text','like','%'.$seakey.'%')->order('art_lasttime desc')->paginate(10,false,[
                    'query' => [
                        'seakey' => $seakey,
                        'type' =>'art',
                        ]
                    ]);
                $this->assign('Articles',$Articles);
                // 获取分页显示
                $page = $Articles->render();
            }
            else{
                $User = new User;
                $Users = $User->where('nick_name','like','%'.$seakey.'%')->paginate(20,false,[
                    'query' => [
                        'seakey' => $seakey,
                        'type' =>'user',
                        ]
                    ]);
                $this->assign('Users',$Users);
                // 获取分页显示
                $page = $Users->render();
            }
        }
        $this->assign('page',$page);
        $this->assign('type',$type);
        $this->assign('seakey',$seakey);
        return $this->fetch();
    }
}