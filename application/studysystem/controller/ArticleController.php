<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Article;      // 引入帖子
use app\common\model\Board;      // 引入版块
use app\common\model\Comment;      // 引入回复
use app\common\model\Rremind;      // 引入回复提醒
use app\common\model\Aremind;
use think\Session;
use think\Request;
use think\Db;

class ArticleController extends Controller
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
        $id = Request::instance()->param('aid/d');
        $Article = Article::get($id);
        //正常状态
        if(isset($Article)&&$Article->art_status=="0"){
            //暂用，无条件的，每刷新一次 浏览次数+1
            $Article->art_view = $Article->art_view+1;
            $Article->save();
            
            $comments = $Article->Comment()->order('com_id')->field('text',true)->paginate(10,false, [
                'query' => [
                    'aid' => $id,
                    ]
                ]);
            // 获取分页显示
            $page = $comments->render();

            // 获取最高楼层数
            $renum = Db::name('comment')->where('art_id',$id)->where('com_status',0)->value('max(com_count)');
            if($renum==null){$renum=2;}

            $Board = Board::get($Article->art_board_id);
            //权限
            $auth = false;
            //获取判断是否版主
            foreach($Board->Boardadmin as $ss){
                if($ss->user_id==Session::get('UserId')){
                    $auth = true;
                }
            }
            $this->assign('auth',$auth);
            
            $this->assign('Article',$Article);
            $this->assign('comments',$comments);
            $this->assign('page', $page);
            return $this->fetch();
        }

        //删除状态
        else if(isset($Article)&&$Article->art_status=="1"){
            return $this->error('帖子不存在！',url('Index/index'));
        }

        //锁定状态
        else if(isset($Article)&&$Article->art_status=="2"){
            // 帖子被锁定
            // return $this->error('帖子被锁定！',url('Index/index'));
        }

        //art_id错误，访问首页
        else if(!isset($Article)){
            return $this->error('帖子不存在！',url('Index/index'));
        }
    }

    //回复功能
    public function reply()
    {
        // 实例化请求信息
        $Request = Request::instance();

        //得到帖子ID|回复的楼层ID（可为空）三个参数
        $pars=$Request->post('par');
        $pararr=explode('|', $pars);
        
        //判断是否进小黑屋，1则小黑屋，没有权限发帖、回帖
        $grant = Db::name('User')->where('user_id',Session::get('UserId'))->field('user_grant')->find()['user_grant'];
        if($grant==1){
            return $this->error('你没有权限！请联系管理员');
        }

        //获取当前帖子的最高楼层，新帖子会返回null
        $maxcount = Db::name('comment')->where('art_id',$pararr[0])->value('max(com_count)');

        //如果是0回复的新帖子，默认设置最大楼数1
        if($maxcount==null){
            $maxcount=1;
        }

        //获取楼主用户ID
        $who = Db::name('Article')->where('art_id',$pararr[0])->field('user_id')->find()['user_id'];

        //如果是回复楼层，则获取层主ID
        $countwho = null;//初始化
        if($pararr[1]!=null){
            //获取层主ID
            $countwho = Db::name('Comment')->where('com_id',$pararr[1])->field('user_id')->find()['user_id'];
        }

        $text = $Request->post('comcontent');

        // 实例化回复并赋值
        $Comment = new Comment();
        $Comment->art_id = $pararr[0];
        $Comment->user_id = Session::get('UserId');
        $Comment->re_com_id = $pararr[1];
        $Comment->com_content = $Request->post('comcontent');
        $Comment->text = str_replace('&nbsp;','',strip_tags(htmlspecialchars_decode($text)));
        $Comment->com_count = $maxcount+1;
        Db::table('article')->where('art_id', $pararr[0])->update(['last_com_time' => date("Y-m-d H:i:s")]);

        // 添加数据
        if(!$Comment->validate(true)->save()){
            return $this->error('回复失败：'.$Comment->getError());
        }

        //只有非本人回复才会提醒
        if($who!=Session::get('UserId')){
            //储存到回复提醒表
            $Rremind = new Rremind();
            $Rremind->user_id = $who;
            $Rremind->re_user_id = Session::get('UserId');
            $Rremind->art_id = $pararr[0];

            if(!$Rremind->validate(true)->save()){
                return $this->error('回复失败：'.$Rremind->getError());
            }

            //回复楼层的时候，也要通知层主
            if($pararr[1]!=null){
                //储存到回复提醒表
                $Rremind = new Rremind();
                $Rremind->user_id = $countwho;
                $Rremind->re_user_id = Session::get('UserId');
                $Rremind->art_id = $pararr[0];
    
                if(!$Rremind->validate(true)->save()){
                    return $this->error('回复失败：'.$Rremind->getError());
                }
            }
        }
        

        //只有非本人回复才会提醒
        if($who!=Session::get('UserId')){
            //发送提醒给楼主，修改用户表
            $User = User::get($who);
            $User->remind = $User->remind+1;
            $User->save();
            
            //回复楼层的时候，也要通知层主
            if($pararr[1]!=null){
                //发送提醒给楼主，修改用户表
                $User = User::get($countwho);
                $User->remind = $User->remind+1;
                $User->save();
            }
        }
        return $this->success('回复成功', $Request->header('referer'));
    }

    //更新帖子
    public function update()
    {
        $Request = Request::instance();
        $id = $Request->post('aid/d');
        
    	//传入帖子信息
    	$Article = Article::get($id);
    	if(is_null($Article)){
    		return $this->error('系统未找到id为'.$id.'的记录');
        } 
        
        $Article->art_title = $Request->post('arttitle');
        $Article->art_content = $Request->post('artcontent');
        $Article->atype_id = $Request->post('arttype');
        $Article->art_lasttime = date('Y-m-d H:i:s', time());
    	//数据更新
    	if(!$Article->validate()->save()){
    		return $this->error('修改失败'.$Article->getError());
    	}else{
    		return $this->success('修改成功',url('Article/index?aid='.$id));
        }
    }

    //版主置顶帖子
    public function topArt()
    {
        $Request = Request::instance();
        $id = str_replace('top','',$Request->post('aid'));

        $Article = Article::get($id);
        $Article->art_top = $Request->post('lv');
        if($Article->save()){
            if($Request->post('lv')>0){
                $User = User::get($Article->user_id);
                $User->remind = $User->remind+1;
                $User->save();

                $Aremind = new Aremind;
                $Aremind->user_id = $Article->user_id;
                $Aremind->ao_user_id = Session::get('UserId');
                $Aremind->art_id = $id;
                $Aremind->aremind_op = 1;
                $Aremind->save();
            }
            return $this->success('设置成功', $Request->header('referer'));
        }
        else{
            return $this->error('设置失败:' . $Article->getError());
        }
    }

    //删除帖子功能
    public function deleteArt()
    {
        $Request = Request::instance();
        $delArtId = str_replace('dele','',$Request->param('aid'));

        $Article = Article::get($delArtId);
        if($Article->art_status=="0"){
            //未删除状态，则执行删除
            $Article->art_status=1;

            // 删除
            if(!$Article->validate(true)->save()){
                return $this->error('删除失败:' . $Article->getError());
            }

            if($Article->user_id!=Session::get('UserId')){
                //版主删除，则通知帖子楼主
                $User = User::get($Article->user_id);
                $User->remind = $User->remind+1;
                $User->save();

                $Aremind = new Aremind;
                $Aremind->user_id = $Article->user_id;
                $Aremind->ao_user_id = Session::get('UserId');
                $Aremind->art_id = $delArtId;
                $Aremind->aremind_op = 0;
                $Aremind->save();
            }
            return $this->success('删除成功', $Request->header('referer'));
        }
    }

    //删除回复功能
    public function delete()
    {
        $Request = Request::instance();
        $delComId = $Request->param('cid/d');

        $Comment = Comment::get($delComId);
        if($Comment->com_status=="0"){
            //未删除状态，则执行删除
            $Comment->com_status=1;

            // 删除
            if(!$Comment->validate(true)->save()){
                return $this->error('删除失败:' . $Comment->getError());
            }

            if($Comment->user_id!=Session::get('UserId')){
                //版主删除，则通知帖子楼主
                $User = User::get($Comment->user_id);
                $User->remind = $User->remind+1;
                $User->save();

                $Aremind = new Aremind;
                $Aremind->user_id = $Comment->user_id;
                $Aremind->ao_user_id = Session::get('UserId');
                $Aremind->art_id = $Comment->art_id;
                $Aremind->com_id = $delComId;
                $Aremind->aremind_op = 3;
                $Aremind->save();
            }
            return $this->success('删除成功', $Request->header('referer'));;
        }
    }

    //编辑帖子
    public function edit()
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
        $id = $Request->param('aid/d');
 
        
        $Article = Article::get($id);
        $this->assign('Article',$Article);
        return $this->fetch();
    }
}