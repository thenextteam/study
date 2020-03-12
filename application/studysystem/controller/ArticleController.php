<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\Article;      // 引入帖子
use app\common\model\Comment;      // 引入回复
use think\Session;
use think\Request;
use think\Db;

class ArticleController extends Controller
{
    public function index()
    {
        $id = Request::instance()->param('aid/d');
        $Article = Article::get($id);
        //正常状态
        if(isset($Article)&&$Article->art_status=="0"){
            //暂用，无条件的，每刷新一次 浏览次数+1
            $Article->art_view = $Article->art_view+1;
            $Article->save();
            // $comments = $Article->Comment()->where('com_status',0)->order('com_id')->paginate(10,false, [
            $comments = $Article->Comment()->order('com_id')->paginate(10,false, [
                'query' => [
                    'aid' => $id,
                    ]
                ]);
            // 获取分页显示
            $page = $comments->render();

            // 获取最高楼层数
            $renum = Db::name('comment')->where('art_id',$id)->where('com_status',0)->value('max(com_count)');
            if($renum==null){$renum=2;}
            
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

        //得到帖子ID|当前登录的用户ID|回复的楼层ID（可为空）三个参数
        $pars=$Request->post('par');
        $pararr=explode('|', $pars);

        //获取当前帖子的最高楼层，新帖子会返回null
        $maxcount = Db::name('comment')->where('art_id',$pararr[0])->value('max(com_count)');

        //如果是0回复的新帖子，默认设置最大楼数1
        if($maxcount==null){
            $maxcount=1;
        }

        // 实例化回复并赋值
        $Comment = new Comment();
        $Comment->art_id = $pararr[0];
        $Comment->user_id = $pararr[1];
        $Comment->re_com_id = $pararr[2];
        $Comment->com_content = $Request->post('comcontent');
        $Comment->com_count = $maxcount+1;
        Db::table('article')->where('art_id', $pararr[0])->update(['last_com_time' => date("Y-m-d H:i:s")]);

        // 添加数据
        if(!$Comment->validate(true)->save()){
            return $this->error('回复失败：'.$Comment->getError());
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
        // return $Article->atype_id;
    	//数据更新
    	if(!$Article->validate()->save()){
    		return $this->error('修改失败'.$Article->getError());
    	}else{
    		return $this->success('修改成功',url('Article/index?aid='.$id));
        }
    }

    //删除帖子功能
    public function deleteArt()
    {
        $Request = Request::instance();
        $delArtId = $Request->param('aid/d');

        $Article = Article::get($delArtId);
        if($Article->art_status=="0"){
            //未删除状态，则执行删除
            $Article->art_status=1;

            // 删除
            if(!$Article->validate(true)->save()){
                return $this->error('删除失败:' . $Article->getError());
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
            return $this->success('删除成功', $Request->header('referer'));;
        }
    }

    //编辑帖子
    public function edit()
    {
        $Request = Request::instance();
        $id = $Request->param('aid/d');
        
        $Article = Article::get($id);
        $this->assign('Article',$Article);
        return $this->fetch();
    }
}