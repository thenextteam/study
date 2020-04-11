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
    public function __construct()
    {        
        //调用父类构造函数(必须)
        parent::__construct();


        // 验证用户是否登陆
        if (!User::isLogin()) {
            return $this->error('签到请先登录', url('Login/index'));
        }
        else{

        }
    }

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
        $join = [
            ['user b','a.user_id=b.user_id'],
        ];
        $Sign = db('sign')->alias('a')->join($join)->order('sign_time desc')->paginate(10,false);
        // 获取分页显示
        $page = $Sign->render();

        //今日签到人数
        $TodaySign = db('sign')
            ->where('sign_time','< time',date('Y-m-d', time()).'23:59:59')
            ->where('sign_time','> time',date('Y-m-d', time()).'00:00:00')
            ->count('sign_id');
        //昨日签到人数
        $YesSign = db('sign')
        ->where('sign_time','< time',date('Y-m-d', time()-86400).'23:59:59')
        ->where('sign_time','> time',date('Y-m-d', time()-86400).'00:00:00')
        ->count('sign_id');
        //总签到人数
        $AllSign = db('sign')->count('sign_id');

        // 心情人数
        $mood0 = array(0,db('sign')->where('user_mood',0)->count('sign_id'));
        $mood1 = array(1,db('sign')->where('user_mood',1)->count('sign_id'));
        $mood2 = array(2,db('sign')->where('user_mood',2)->count('sign_id'));
        $mood3 = array(3,db('sign')->where('user_mood',3)->count('sign_id'));
        $mood4 = array(4,db('sign')->where('user_mood',4)->count('sign_id'));
        $mood5 = array(5,db('sign')->where('user_mood',5)->count('sign_id'));
        $mood6 = array(6,db('sign')->where('user_mood',6)->count('sign_id'));
        $mood7 = array(7,db('sign')->where('user_mood',7)->count('sign_id'));
        $mood8 = array(8,db('sign')->where('user_mood',8)->count('sign_id'));
        $moodall = array($mood0,$mood1,$mood2,$mood3,$mood4,$mood5,$mood6,$mood7,$mood8);
        $moodnum = array_column($moodall,1);
        //由心情人数排序
        array_multisort($moodnum,SORT_DESC,$moodall);

        $this->assign('Sign',$Sign);
        $this->assign('page',$page);
        $this->assign('TodaySign',$TodaySign);
        $this->assign('YesSign',$YesSign);
        $this->assign('AllSign',$AllSign);
        $this->assign('mood',$moodall);
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
        //随机积分,50到100之间
        $randpoint = mt_rand(50,100);
        $Sign->sign_reward = $randpoint;
        //随机金币,5到20之间
        $randmoney = mt_rand(5,20);
        //更新积分、签到时间
        $User = User::get(Session::get('UserId'));
        $User->user_point = $User->user_point+$randpoint;
        $User->user_money = $User->user_money+$randmoney;
        $User->user_days = $User->user_days+1;
        $User->save();
        // Db::table('article')->where('art_id', $pararr[0])->update(['last_com_time' => date("Y-m-d H:i:s")]);
        
        // 添加数据
        if(!$Sign->validate(true)->save()){
            return $this->error('签到失败：'.$Sign->getError());
        }
        $User->sign_id = db('sign')->where('user_id',Session::get('UserId'))->order('sign_time desc')->field('sign_id')->find()['sign_id'];
        $User->save();
        //计算用户等级
        User::userLv(Session::get('UserId'));
        return $this->success('签到成功，获得积分 '.$randpoint.' 分，金币 '.$randmoney.' 个', $Request->header('referer'));
    }
}