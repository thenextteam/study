<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Bhead;      // 引入板块大类
// use app\common\model\Article;      // 引入帖子
use app\common\model\Board;      // 引入板块
// use app\common\model\Comment;      // 引入回复
use think\Session;
use think\Db;
use think\Route;

class IndexController extends Controller
{
    public function __construct()
    {
        
        //调用父类构造函数(必须)
        parent::__construct();

        // 验证用户是否登陆
        if (!User::isLogin()) {
            //return $this->error('您未登录', url('Login/index'));
        }
        else{
            
        }
    }

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
        
        $join = [
            ['user b','a.user_id=b.user_id'],
            ['board c','a.art_board_id=c.board_id'],
        ];
        //最新和热门帖子，列8条
        $newArts = Db::name('article')->alias('a')->join($join)->where('art_status',0)->order('art_time desc')->limit(8)->select();
        $hotArts = Db::name('article')->alias('a')->join($join)->where('art_status',0)->order('art_view desc')->limit(8)->select();
        //新会员
        $newUser = Db::name('user')->order('user_id desc')->find();
        //所有帖子
        $allArt = Db::name('Article')->where('art_status',0)->field(['art_content','text'],true)->count('art_id');
        //所有会员
        $allUser = Db::name('User')->count('user_id');
        //轮播
        $cars = Db::name('swiper')->limit(5)->order('sp_time desc')->select();
        $Bhead = new Bhead;
        $Board = new Board;
        $bheads = $Bhead->where('bhead_status',0)->order('bhead_top desc')->select();

        $this->assign('hotArts',$hotArts);
        $this->assign('newArts',$newArts);
        $this->assign('newUser',$newUser);
        $this->assign('allArt',$allArt);
        $this->assign('allUser',$allUser);
        $this->assign('bheads',$bheads);
        $this->assign('Board',$Board);
        $this->assign('cars',$cars);
        return $this->fetch(); 
    }


}