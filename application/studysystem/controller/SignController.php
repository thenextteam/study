<?php
namespace app\studysystem\controller;     
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Sign;      // 引入签到
use think\Session;
use think\Db;
use think\Request;              // 请求

/**
 * 签到
 */
class SignController extends Controller
{

    public function index()
    {
        $join = [
            ['user b','a.user_id=b.user_id'],
        ];
        $Sign = db('sign')->alias('a')->join($join)->order('sign_time desc')->paginate(5,false);
        // 获取分页显示
        $page = $Sign->render();
        $this->assign('Sign',$Sign);
        $this->assign('page',$page);
        return $this->fetch();
    }

    public function posign()
    {
        //检测当天是否已签到
        $isSign = db('Sign')->where('user_id',Session::get('UserId'))
            ->where('sign_time','< time',date('Y-m-d', time()).'23:59:59')
            ->where('sign_time','> time',date('Y-m-d', time()).'00:00:00')
            ->order('sign_id desc')
            ->find();
        //已签到报错，否则继续执行
        if(isset($isSign)){
            return $this->error('今日已签到！');
        }

        // 实例化请求信息
        $Request = Request::instance();

        //获取心情
        $usermood=$Request->post('mood');
        $signcon=$Request->post('say');

        
        // 实例化回复并赋值
        $Sign = new Sign();
        $Sign->user_id = Session::get('UserId');
        $Sign->user_mood = $usermood;
        $Sign->sign_con = $signcon;
        $Sign->sign_reward = mt_rand(50,100);
        // Db::table('article')->where('art_id', $pararr[0])->update(['last_com_time' => date("Y-m-d H:i:s")]);
        
        // 添加数据
        if(!$Sign->validate(true)->save()){
            return $this->error('签到失败：'.$Sign->getError());
        }
        return $this->success('签到成功', $Request->header('referer'));
    }
}