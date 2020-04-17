<?php
namespace app\studysystem\controller;     //命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Article;      // 引入帖子
use app\common\model\Board;      // 引入版块
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
        $uid = Request::instance()->param('uid/d');
        $fid = Request::instance()->param('fid/d');
        $type = Request::instance()->param('type');
        $User = User::get($uid);
        if(!$User){
            return $this->error('用户不存在！');
        }
        if($fid!=1&&$fid!=2&&$fid!=3&&$fid!=0){
            $fid = 0;
        }
        $this->assign('User',$User);
        $this->assign('ismut', 0);
        //判断是否好友
        if(db('friend')->where('user_id',Session::get('UserId'))->where('friend_user_id',$uid)->where('ismut',1)->count('friend_id')==1){
            $this->assign('ismut', 1);
        }
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
        else if($fid==3){
            $uid = Request::instance()->param('uid/d');
            $fid = Request::instance()->param('fid/d');
            $type = Request::instance()->param('type');
            if(($type!='fboa'&&$type!='fart')||$type==null){
                $type = 'fboa';
            }
            if($type=='fboa'){
                $favoriteboard = $User->Favorite()->where('art_id',0)->order('favorite_time desc')->paginate(10,false, [
                    'query' => [
                        'uid' => $uid,
                        'fid' => 3,
                        'type' => 'fboa',
                        ]
                    ]);
                // 获取分页显示
                $page = $favoriteboard->render();
                $this->assign('favoriteboard', $favoriteboard);
            }
            else if($type=='fart'){
                $favoriteart = $User->Favorite()->where('board_id',0)->order('favorite_time desc')->paginate(10,false, [
                    'query' => [
                        'uid' => $uid,
                        'fid' => 3,
                        'type' => 'fart',
                        ]
                    ]);
                // 获取分页显示
                $page = $favoriteart->render();
                $this->assign('favoriteart', $favoriteart);
            }
            
            $this->assign('page', $page);
            $this->assign('type', $type);
        }
        $this->assign('fid',$fid);
        return $this->fetch();
    }

    //修改个人信息页面
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
        if($type!='userimg'&&$type!='resume'){
            $type = 'resume';
        }
        else if($type=='userimg'){

        }
        $this->assign('type', $type);
        return $this->fetch();
    }

    //更新信息
    public function save()
    {
        $Request = Request::instance();
        $uid = $Request->param('id');
        $User = User::get($uid);
        if(!$User){
            return $this->error('用户不存在');
        }
        if($Request->param('changepwd')==1){
            if(md5($Request->param('opwd'))!=$User->user_pwd){
                return $this->error('原密码不正确！');
            }
            //记忆旧密码，当用户还是使用旧密码时返回成功提示
            $oldpwd = $User->user_pwd;

            $User->user_pwd = md5($Request->param('npwd'));
        }
        else if(($Request->param('changepwd')==0)&&($User->user_email == $Request->param('email'))&&($User->nick_name == $Request->param('name'))){
            return $this->success('修改成功', $Request->header('referer'));
        }
        $User->user_email = $Request->param('email');
        $User->nick_name = $Request->param('name');
        
    	if(!$User->validate()->save()){
            //密码没有修改时还是返回修改成功
            if($oldpwd==md5($Request->param('npwd'))){
                return $this->success('修改成功', $Request->header('referer'));
            }
            return $this->error('修改失败！'.$User->getError());
        }
        return $this->success('修改成功', $Request->header('referer'));
    }

    //更新头像
    public function upload()
    {
        // 获取表单上传文件
        $Request = Request::instance();
        $file = request()->file('image');
        
        // 移动到用户头像目录下
        if($file){
            // $saveName = Session::get('UserId'); 
            $info = $file->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads\userimgs');
            if($info){
                $User = User::get(Session::get('UserId'));
                // $path = '/thinkphp/public/uploads/userimgs'.$info->getSaveName();
                $oldimg = $User->user_img;
                $User->user_img = $info->getSaveName();
                $User->save();
                // $oldpath = substr($path,0,45);
                //删除旧头像
                unlink(ROOT_PATH.'/public/uploads/userimgs/'.$oldimg);
                return $this->success('上传成功', $Request->header('referer'));
            }else{
                // 上传失败获取错误信息
                return $this->error('上传失败');
            }
        }
        // // 获取表单上传文件
        // $Request = Request::instance();
        // $file = request()->file('image');
        
        // // 移动到用户头像目录下
        // if($file){
        //     $saveName = Session::get('UserId'); 
        //     $info = $file->move(ROOT_PATH . 'public' . DS . 'static\study\img\userimg',$saveName,true);
        //     if($info){
        //         $User = User::get(Session::get('UserId'));
        //         $User->user_img = Session::get('UserId').'.jpg';
        //         $User->save();
        //         return $this->success('上传成功', $Request->header('referer'));
        //     }else{
        //         // 上传失败获取错误信息
        //         return $this->error('上传失败');
        //     }
        // }
    }

    //添加好友
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

    //取消收藏
    public function canfav()
    {
        $Request = Request::instance();
        $bid = $Request->param('bid');
        $aid = $Request->param('aid');
        //$bid不为空则是取消收藏版块
        if($bid!=null){
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
        //$aid不为空则是取消收藏帖子
        else if($aid!=null){
            //防止用户乱写aid
            if(!Article::get($aid)){
                return $this->error('帖子不存在！');
            }
            //在收藏表中执行删除操作
            if((db('favorite')->where('user_id',Session::get('UserId'))->where('board_id',0)->where('art_id',$aid)->delete())==0){
                return $this->error('取消收藏失败！');
            }
            return $this->success('取消收藏成功！');
        }
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