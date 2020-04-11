<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Board;      // 引入板块
use app\common\model\Boardadmin;      // 引入板主
use app\common\model\Article;      // 引入帖子
use app\common\model\Favorite;      // 引入收藏
use think\Session;
use think\Request;
use think\Db;

class BoardController extends Controller
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
        
        $bid = Request::instance()->param('bid/d');
        //排序方式0有最新回复1发帖时间2访问数
        $oid = Request::instance()->param('oid/d');
        $atype = Request::instance()->param('atype');
        //默认值0
        if($oid==null){
            $oid = 0;
            $orderrule = "last_com_time desc";
        }
        else if($oid==1){
            $orderrule = "art_time desc";
        }
        else if($oid==2){
            $orderrule = "art_view desc";
        }
        $Board = Board::get($bid);
        if(!$Board){
            return $this->error('版块不存在！');
        }
        //版块积分要求
        if($Board->board_th>0){
            if(!Session::get('UserId')){
                return $this->error('该版块需要积分大于'.$Board->board_th.'分，请先登录', url('Login/index'));
            }
            else if(User::get(Session::get('UserId'))->user_point<$Board->board_th){
                return $this->error('该版块需要积分大于'.$Board->board_th.'分，您的积分：'.User::get(Session::get('UserId'))->user_point.'分，不足以进入该版块');
            }
        }

        //判断atype是否属于该版块下，不是则显示全部
        $s = [];
        foreach($Board->atype as $key => $x){
            $s[$key] = $x->atype_id;
            
        }
        if(!in_array($atype,$s)){
            $atype = null;
        }

        //权限
        $auth = false;
        //获取判断是否版主
        foreach($Board->Boardadmin as $ss){
            if($ss->user_id==Session::get('UserId')){
                $auth = true;
            }
        }

        $this->assign('auth',$auth);

        //获取置顶贴
        $BoardTops = Board::get($bid)->Article()->where('art_top','>',0)->where('art_status',0)->order('art_top desc')->select();

        //正常状态
        if(isset($Board)&&($Board->board_status!="1")){
            //获取当前板块下的所有帖子
            $arts = $Board->Article;
            if($atype==null){
                $map['atype_id'] = ['>=',0];
            }
            else{
                $map['atype_id'] = $atype;
            }
            $where['art_status'] = [ ['=',0], ['=',2] ,'or'];
            $arts = $Board->Article()->where($where)->where($map)->order($orderrule)->paginate(20,false, [
                'query' => [
                    'bid' => $bid,
                    'oid' => $oid,
                    'atype' => $atype,
                    ]
                ]);
            // 获取分页显示
            $page = $arts->render();

            // 获取分类
            $this->assign('Batypes', $Board->atype);
            $this->assign('page', $page);
            $this->assign('arts',$arts);
            $this->assign('Board',$Board);
            $this->assign('BoardTops',$BoardTops);
            // var_dump($arts);
            return $this->fetch(); 
        }
    }
    //发表新帖子
    public function newArt()
    {
        // 实例化请求信息
        $Request = Request::instance();
        if(!Session::get('UserId')){
            return $this->error('请先登录');
        }
        //得到板块ID|当前登录的用户ID
        $pars = $Request->post('par');
        $pararr = explode('|', $pars);

        //判断是否进小黑屋，1则小黑屋，没有权限发帖、回帖
        $grant = Db::name('User')->where('user_id',Session::get('UserId'))->field('user_grant')->find()['user_grant'];
        if($grant==1){
            return $this->error('你没有权限！请联系管理员');
        }

        // 帖子类型arttype、标题arttitle、内容artcontent
        // 实例化回复并赋值
        
        // $x = htmlspecialchars_decode($x);
        // return strip_tags($x);
        $text = $Request->post('artcontent');

        $Article = new Article();
        $Article->user_id = Session::get('UserId');
        $Article->art_title = $Request->post('arttitle');
        $Article->art_content = $Request->post('artcontent');
        $Article->text = str_replace('&nbsp;','',strip_tags(htmlspecialchars_decode($text)));
        $Article->art_board_id = $pararr[0];
        $Article->atype_id = $Request->post('arttype');

        // 添加数据
        if(!$Article->validate(true)->save()){
            // return $this->error('发布失败：'.$Article->getError());
            return $this->error('发布失败');
        }
        return $this->success('发布成功', $Request->header('referer'));
    }

    //上传图片
    public function upload()
    {
        $files = request()->file();
        $imags = [];
        $errors = [];
        foreach($files as $file){
            // 移动图片到/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'artimgs');
            if($info){
                // 成功上传 图片地址加入到数组
                $path = '/thinkphp/public/uploads/artimgs/'.$info->getSaveName();
                array_push($imags,$path);
            }else{
                array_push($errors,$file->getError());
            }
        }

        //输出wangEditor规定的返回数据
        if(!$errors){
            $msg['errno'] = 0;
            $msg['data'] = $imags;
            return json($msg);
        }else{
            $msg['errno'] = 1;
            $msg['data'] = $imags;
            $msg['msg'] = "上传出错";
            return json($msg);
        }
    }

    //收藏版块
    public function favorite()
    {
        //验证是否登录
        if(!Session::get('UserId')){
            return $this->error('请先登录');
        }
        $Request = Request::instance();
        $bid = $Request->param('bid/d');
        $Board = Board::get($bid);
        if(!$Board){
            return $this->error('版块不存在！');
        }
        if((db('favorite')->where('user_id',Session::get('UserId'))->where('board_id',$bid)->where('art_id',0)->count('favorite_id'))>0){
            return $this->error('您已收藏此版块！');
        }
        $Favorite = new Favorite;
        $Favorite->user_id = Session::get('UserId');
        $Favorite->board_id = $bid;
        // 添加数据
        if(!$Favorite->validate(true)->save()){
            return $this->error('收藏失败');
        }
        return $this->success('收藏成功', $Request->header('referer'));
    }

    //取消收藏
    public function canfav()
    {
        $Request = Request::instance();
        $bid = $Request->param('bid');
        //防止用户乱写bid
        if(!Board::get($bid)){
            return $this->error('版块不存在！');
        }
        //在收藏表中执行删除操作
        if((db('favorite')->where('user_id',Session::get('UserId'))->where('art_id',0)->where('board_id',$bid)->delete())==0){
            return $this->error('取消收藏失败！');
        }
        return $this->success('取消收藏成功！');
        
    }
}