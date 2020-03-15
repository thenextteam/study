<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use app\common\model\Article;   // 帖子模型
use app\common\model\Rremind;   // 回复提醒模型
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
        $Rremind = new Rremind;
        $Rremind = $Rremind->where('user_id',$uid)->order('rremind_status,rremind_time desc')->paginate(10,false, [
            'query' => [
                'uid' => $uid,
                ]
            ]);
        // 获取分页显示
        $page = $Rremind->render();

        $Article = new Article;
        $Article = $Article->where('user_id',$uid)->select();
        $User = db('User')->field('user_id,nick_name,user_img')->select();
        // var_dump($Article);
        $this->assign('Rremind',$Rremind);
        $this->assign('Article',$Article);
        $this->assign('User',$User);
        $this->assign('page',$page);
        return $this->fetch();
    }
     
    //设置已读（AJAX）
    public function read()
    {
        if(request()->isAjax()){
            $rrid = str_replace('sr','',input('rrid'));
        }
        // $Request = Request::instance();
        // $rrid = $Request->param('rrid/d');
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
}