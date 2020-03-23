<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Article;      // 引入帖子
use app\common\model\Comment;      // 引入回复
use app\common\model\Friend;      // 引入好友
use think\Session;
use think\Db;
use think\Request;              // 请求

class UserController extends Controller
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
        $uid = Request::instance()->param('uid/d');
        $fid = Request::instance()->param('fid/d');
        $type = Request::instance()->param('type');
        $User = User::get($uid);
        $this->assign('User',$User);
        //所有帖子
        if($fid==1){
            $arts = $User::get($uid)->Article()->where('art_status',0)->order('art_time desc')->paginate(10,false, [
                'query' => [
                    'uid' => $uid,
                    'fid' => 1,
                    ]
                ]);
            // 获取分页显示
            $page = $arts->render();

            //默认值
            if($type!="com"){
                $type = 'art';
            }
            else{
                $Comment = Comment::all(['com_status'=>0,'user_id'=>$uid]);
                $newarr = [];
                for($i=0;$i<sizeof($Comment);$i++)
                {
                    $newarr[$i] = $Comment[$i]['art_id'];
                }
                //读取该用户有回复的帖子及其该用户在该帖子的回复，暂未解决的问题：无法分页
                if(!empty($newarr)){
                    $comart = Article::all(array_unique($newarr));
                }
                else{
                    $comart = null;
                }

                $this->assign('comarts', $comart);

                $this->assign('Comment', $Comment);
            }

            $this->assign('page', $page);
            $this->assign('type', $type);
            $this->assign('arts',$arts);
        }
        else if($fid==2){
            //所有好友（不分页）
        }
        $this->assign('fid',$fid);
        return $this->fetch();
    }

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
            return $this->error('请先登录！'); 
        }
        $type = Request::instance()->param('type');
        if($type==null||$type=='resume'){
            $type = 'resume';
        }
        else if($type=='userimg'){

        }
        $this->assign('type', $type);
        return $this->fetch();
    }

    public function upload(){
        // 获取表单上传文件
        $Request = Request::instance();
        $file = request()->file('image');
        
        // 移动到用户头像目录下
        if($file){
            $saveName = Session::get('UserId'); 
            $info = $file->move(ROOT_PATH . 'public' . DS . 'static\study\img\userimg',$saveName,true);
            if($info){
                $User = User::get(Session::get('UserId'));
                $User->user_img = Session::get('UserId').'.jpg';
                $User->save();
                return $this->success('上传成功', $Request->header('referer'));
            }else{
                // 上传失败获取错误信息
                return $this->error('上传失败',$file->getError());
            }
        }
    }

    public function newfriend()
    {
        $Request = Request::instance();
        //获取自己的ID
        $user = Session::get('UserId');
        //获取欲加好友的用户的ID
        $friuser = $Request->param('uid');

        //调用user模型的添加好友
        if(User::newfriend($friuser,$user)=='true'){
            $User = User::get($friuser);
            //只有当单方面添加好友才发出提醒
            if(db('friend')->where('user_id',$friuser)->where('friend_user_id',$user)->where('ismut',1)->count('friend_id')==0){
                $User->remind = $User->remind+1;
            }
            //查询是否双方好友
            if(db('friend')->where('friend_user_id',$friuser)->where('user_id',$user)->where('ismut',1)->count('friend_id')>0){
                //减掉自己的一条提醒
                db('user')->where('user_id',$user)->setDec('remind');
                return $this->success('添加好友成功！', $Request->header('referer'));
            }
            $User->save();
            return $this->success('好友申请已发出，等待对方处理', $Request->header('referer'));
        }
        //返回ismut，双方已是好友
        else if((User::newfriend($friuser,$user))=='ismut'){
            return $this->error('你们已经是好友了！');
        }
        return $this->error('申请失败');
    }

    //备用暂留
    // public function newfriend()
    // {
    //     $Request = Request::instance();
    //     $user = $Request->param('uid');
    //     $friuser = Session::get('UserId');
    //     if(User::newfriend($user,$friuser)=='true'){
    //         $User = User::get($user);
    //         //只有当单方面添加好友才发出提醒
    //         if(db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',1)->count('friend_id')==0){
    //             $User->remind = $User->remind+1;
    //         }
    //         //查询是否双方好友
    //         if(db('friend')->where('friend_user_id',$user)->where('user_id',$friuser)->where('ismut',1)->count('friend_id')>0){
    //             //减掉自己的一条提醒
    //             db('user')->where('user_id',$friuser)->setDec('remind');
    //             return $this->success('添加好友成功！', $Request->header('referer'));
    //         }
    //         $User->save();
    //         return $this->success('好友申请已发出，等待对方处理', $Request->header('referer'));
    //     }
    //     else if((User::newfriend($user,$friuser))=='ismut'){
    //         return $this->error('你们已经是好友了！');
    //     }
    //     return $this->error('申请失败');
    // }
}