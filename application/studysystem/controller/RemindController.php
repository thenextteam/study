<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use app\common\model\Article;   // 帖子模型
use app\common\model\Comment;   // 回复模型
use app\common\model\Rremind;   // 回复提醒模型
use app\common\model\Aremind;   // 帖子提醒模型
use app\common\model\Friend;   // 好友模型
use think\Session;

class RemindController extends Controller
{
    // 提醒
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
        if(!(User::get($uid))){
            return $this->error('用户不存在！');
        }
        $type = Request::instance()->param('type');
        $page = null;
        if($type==null||$type=='rre'){
            $type = 'rre';
            $Rremind = new Rremind;
            $Rremind = $Rremind->where('user_id',$uid)->order('rremind_status,rremind_time desc')->paginate(10,false, [
                'query' => [
                    'uid' => $uid,
                    ]
                ]);
            // 获取分页显示
            $page = $Rremind->render();
            $this->assign('Rremind',$Rremind);
        }
        else if($type=='are'){
            $Aremind = new Aremind;
            $Aremind = $Aremind->where('user_id',$uid)->order('aremind_status,aremind_time desc')->paginate(10,false, [
                'query' => [
                    'uid' => $uid,
                    ]
                ]);
            // 获取分页显示
            $page = $Aremind->render();
            $Comment = new Comment;
            $Comment = $Comment->where('user_id',$uid)->field('text',true)->select();
            $this->assign('Aremind',$Aremind);
            $this->assign('Comment',$Comment);
        }
        else if($type=='fre'){
            $Friend = new Friend;
            $Friend = $Friend->where('user_id',$uid)->order('ismut,friend_time desc')->paginate(10,false, [
                'query' => [
                    'uid' => $uid,
                    ]
                ]);
            // 获取分页显示
            $page = $Friend->render();
            $this->assign('Friend',$Friend);
        }

        $Article = new Article;
        $Article = $Article->where('user_id',$uid)->field('art_content',true)->select();
        $User = db('User')->field('user_id,nick_name,user_img')->select();
        
        //获取各种通知的数量
        $rremindnum = db('Rremind')->where('user_id',$uid)->where('rremind_status',0)->count('rremind_id');
        $aremindnum = db('Aremind')->where('user_id',$uid)->where('aremind_status',0)->count('aremind_id');
        $friendnum = db('Friend')->where('user_id',$uid)->where('ismut',0)->count('friend_id');

        $this->assign('type',$type);
        $this->assign('Article',$Article);
        $this->assign('User',$User);
        $this->assign('page',$page);
        $this->assign('rremindnum',$rremindnum);
        $this->assign('aremindnum',$aremindnum);
        $this->assign('friendnum',$friendnum);
        return $this->fetch();
    }
     
    //设置已读（AJAX）
    public function read()
    {

        if(request()->isAjax()){
            // $rrid = str_replace('sr','',input('rrid'));
            $rrid = input('rrid');

            //回复提醒
            if(strstr($rrid,'sr')!=null){
                $rrid = str_replace('sr','',$rrid);
                $Rremind = Rremind::get($rrid);
                $User = User::get(Session::get('UserId'));
                if($Rremind->rremind_status==0&&$User->remind>0){
                    $Rremind->rremind_status = 1;
                    $User->remind = $User->remind-1;
                }
                else{
                    return false;
                }
                if($Rremind->save()&&$User->save()){
                    return true;
                }
                else{
                    return false;
                }
            }
            
            //帖子被版主操作的提醒
            else if(strstr($rrid,'ar')!=null){
                $arid = str_replace('ar','',$rrid);
                $Aremind = Aremind::get($arid);
                $User = User::get(Session::get('UserId'));
                if($Aremind->aremind_status==0&&$User->remind>0){
                    $Aremind->aremind_status = 1;
                    $User->remind = $User->remind-1;
                }
                else{
                    return false;
                }
                if($Aremind->save()&&$User->save()){
                    return true;
                }
                else{
                    return false;
                }
            }

            //好友申请的提醒
            else{
                if(strstr($rrid,'ign')!=null){
                    //忽略
                    $frid = str_replace('ign','',$rrid);
                    $Friend = Friend::get($frid);
                    $Friend->ismut = 2;
                    $Friend->save();
                    db('user')->where('user_id',Session::get('UserId'))->setDec('remind');
                    return true;
                }
                else{
                    //同意
                    $frid = str_replace('fr','',$rrid);
                }
                
                // $Friend = Friend::get($frid);
                // $User = User::get(Session::get('UserId'));
                $user = Friend::get($frid)->friend_user_id;
                $friuser =Session::get('UserId');
                
                if(User::newfriend($user,$friuser)=='true'){
                    $User = User::get($user);
                    //只有当单方面添加好友才发出提醒
                    if(db('friend')->where('user_id',$user)->where('friend_user_id',$friuser)->where('ismut',1)->count('friend_id')==0){
                        $User->remind = $User->remind+1;
                    }
                    //查询是否双方好友
                    if(db('friend')->where('friend_user_id',$user)->where('user_id',$friuser)->where('ismut',1)->count('friend_id')>0){
                        //减掉自己的一条提醒
                        db('user')->where('user_id',$friuser)->setDec('remind');
                        // return $this->success('添加好友成功！', $Request->header('referer'));
                        return true;
                    }
                    $User->save();
                    return true;
                    // return $this->success('好友申请已发出，等待对方处理', $Request->header('referer'));
                }
                else if((User::newfriend($user,$friuser))=='ismut'){
                    return false;
                    // return $this->error('你们已经是好友了！');
                }
                    return false;
                // return $this->error('申请失败');
                // if($Friend->ismut==0&&$User->remind>0){
                //     if(strstr($rrid,'ign')!=null){
                //         $Friend->ismut = 2;
                //     }
                //     else{
                //         $Friend->ismut = 1;
                //     }
                    
                //     $User->remind = $User->remind-1;
                // }
                // else{
                //     return false;
                // }
                // if($Friend->save()&&$User->save()){
                //     return true;
                // }
                // else{
                //     return false;
                // }
            }
        }

        

    }

    public function allre()
    {
        if(!Session::get('UserId')){
            return false;
        }
        //取出用户
        $User = User::get(Session::get('UserId'));
        $User->remind = 0;
        $User->save();
        db('rremind')->where('user_id',Session::get('UserId'))->update(['rremind_status' => 1]);
        db('aremind')->where('user_id',Session::get('UserId'))->update(['aremind_status' => 1]);
        db('friend')->where('user_id',Session::get('UserId'))->where('ismut',0)->update(['ismut' => 2]);
        // $this->success('已读成功');
        return true;
    }
}