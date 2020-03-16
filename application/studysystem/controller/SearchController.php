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
            $this->assign('nowuser','');
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
                        ]
                    ]);
                $this->assign('Articles',$Articles);
                // 获取分页显示
                $page = $Articles->render();
            }
            else{
                $User = new User;
                $Users = $User->where('nick_name','like','%'.$pararr[0].'%')->where('nick_name','like','%'.$pararr[1].'%')->where('nick_name','like','%'.$pararr[2].'%')->paginate(10,false,[
                    'query' => [
                        'seakey' => $seakey,
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
                $Articles = $Article->where('art_title','like','%'.$seakey.'%')->whereOr('text','like','%'.$seakey.'%')->order('art_lasttime desc')->paginate(20,false,[
                    'query' => [
                        'seakey' => $seakey,
                        ]
                    ]);
                $this->assign('Articles',$Articles);
                // 获取分页显示
                $page = $Articles->render();
            }
            else{
                $User = new User;
                $Users = $User->where('nick_name','like','%'.$seakey.'%')->paginate(10,false,[
                    'query' => [
                        'seakey' => $seakey,
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