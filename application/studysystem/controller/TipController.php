<?php
namespace app\studysystem\controller;
use think\Controller;
use think\Request;              // 请求
use app\common\model\User;   // 用户模型
use app\common\model\Article;   // 帖子模型
use app\common\model\Comment;   // 回复模型
use app\common\model\Tip;   // 举报模型
use think\Session;

class TipController extends Controller
{

    // 用户登录表单
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
                }
            }
            else{
                $this->assign('nowuser','');
            }
        }

        $Tip = new Tip;
        $Request = Request::instance();
        $bid = $Request->param('bid/d');
        $tips = $Tip->where('board_id',$bid)->order('tip_op asc,tip_time desc')->paginate(10,false, [
            'query' => [
                'bid' => $bid,
                ]
            ]);
        // 获取分页显示
        $page = $tips->render();
        $this->assign('tips',$tips);
        $this->assign('page',$page);

        return $this->fetch();
    }

    public function tipop()
    {
        $Request = Request::instance();
        $tid = $Request->param('tid');
        $top = $Request->param('type');
        if($top!='1'&&$top!='2'&&$top!='3'){
            //防止参数被用户篡改
            return $this->error('参数错误', $Request->header('referer'));
        }

        $Tip = Tip::get($tid);
        $Tip->tip_op = $top;
        $Tip->tip_over_time = date('Y-m-d H:i:s', time());
        $Tip->op_user_id = Session::get('UserId');
        if($Tip->validate(true)->save()){
            $Article = Article::get($Tip->art_id);
            //如果=3就是锁定操作
            if($top=='3'){
                //art_status=2代表锁定
                $Article->art_status = 2;
                $Article->save();
            }
            //=2删除操作
            else if($top=='2'){
                //举报的是帖子
                if($Tip->com_id==0){
                    $Article->art_status = 1;
                    $Article->save();
                }
                //举报的是回复
                else if($Tip->art_id==0){
                    $Comment = Comment::get($Tip->com_id);
                    $Comment->com_status = 1;
                    $Comment->save();
                }
            }
            return $this->success('处理成功', $Request->header('referer'));
        }
    }
}