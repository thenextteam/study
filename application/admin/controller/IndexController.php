<?php

namespace app\admin\controller;

use think\Db;
use think\db\Query;

class IndexController extends BasicController
{
    public function index()
    {
        return view('index');
    }

    public function home()
    {
        $name = session('admin');
        $this->assign('name', $name['admin_username']);
        $user_num = Db::name('user')->count();
        $this->assign('usernum', $user_num);

        $articles = Db::table('article')->count();
        $this->assign('articles', $articles);
        $board = Db::table('board')->count();
        $this->assign('board', $board);

        return $this->fetch();
    }

    public function getRecArt(){
        $arr = array();
        $date = date('Y-m-d');

        $art_recent = Db::query("select date_format(art_time,'%d') days,count(art_time) count  from article where(art_time>'2020-03-13') group by days");
        $data = $this->get_weeks('2020-03-20');
        $arr['art'] = $art_recent;
        $arr['data'] = $data;
        echo json_encode($arr);
    }

    public function getRecUser(){
        $arr = array();
        $date = date('Y-m-d');

        $user_recent = Db::query("select date_format(user_regtime,'%d') days,count(user_regtime) count from user where(user_regtime>'2020-03-26') group by days");;
        $data = $this->get_weeks('2020-04-01');
        $arr['user'] = $user_recent;
        $arr['data'] = $data;
        echo json_encode($arr);
    }


    /**

     * 获取最近七天所有日期

     */

    function get_weeks($time = '', $format='d'){
        $time = $time != '' ? strtotime($time) : time();
//        $time = strtotime('2020-03-20');
        //组合数据
        $date = [];
        for ($i=1; $i<=7; $i++){
            $date[$i] = date($format ,strtotime( '+' . $i-7 .' days', $time));
        }
        return $date;

    }
}