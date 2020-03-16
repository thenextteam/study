<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use app\common\model\Article;   // 帖子模型
use app\common\model\Comment;   // 回复模型
use app\common\model\Rremind;   // 回复提醒模型
use app\common\model\Aremind;   // 帖子提醒模型
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

        $Article = new Article;
        $Article = $Article->where('user_id',$uid)->field('art_content',true)->select();
        $User = db('User')->field('user_id,nick_name,user_img')->select();
        // var_dump($Article);
        $this->assign('type',$type);
        $this->assign('Article',$Article);
        $this->assign('User',$User);
        $this->assign('page',$page);
        return $this->fetch();
    }
     
    //设置已读（AJAX）
    public function read()
    {
        
        if(request()->isAjax()){
            // $rrid = str_replace('sr','',input('rrid'));
            $rrid = input('rrid');
            if(strstr($rrid,'sr')!=null){
                //是回复提醒
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
            else{
                //是帖子被版主操作的提醒
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
        }

        

    }
}