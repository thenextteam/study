<?php

namespace app\admin\controller;

use app\common\model\User;
use think\Db;
use think\Request;

class ChatController extends BasicController
{
    public function chatlog()
    {
        return view('chatlog');
    }

    public function getChatLog()
    {
        $arr = array();
        $data = array();
        $arr['code'] = 0;
        $arr['msg'] = "";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        $MsgCount = Db::name('chatmsg')->count();

        if (Request::instance()->has('search', 'get')) {

            $get = $_GET['search'];
            $user_id = $get['user_id'];
            $nick_name = $get['nick_name'];


            $map = [];
            $user_id == "" ?: $map['u.user_id'] = ['=', $user_id];
            $nick_name == "" ?: $map['u.nick_name'] = ['LIKE', '%' . $nick_name . '%'];

            $Count = Db::table('chatmsg')->alias('a')
                ->join('user u', 'a.fromid = u.user_id')
                ->join('user uu', 'a.toid = uu.user_id')
                ->where($map)->count();

            $resMsg = Db::table('chatmsg')->alias('a')
                ->join('user u', 'a.fromid = u.user_id')
                ->join('user uu', 'a.toid = uu.user_id')
                ->field('a.*, u.nick_name as fromname, uu.nick_name as toname ')
                ->limit(($page - 1) * $limit, $limit)
                ->where($map)->select();
            $arr['count'] = $Count;
            $arr['data'] = $resMsg;
            return $arr;
        } else {
            $resMsg = Db::table('chatmsg')->alias('a')
                ->join('user u', 'a.fromid = u.user_id')
                ->join('user uu', 'a.toid = uu.user_id')
                ->field('a.*, u.nick_name as fromname, uu.nick_name as toname ')
                ->limit(($page - 1) * $limit, $limit)->select();
            $arr['count'] = $MsgCount;
            $arr['data'] = $resMsg;
        }
        return $arr;
    }


    public function multiDelete()
    {
//        $i = array();
        $id = $_POST['allid'];
        $allid = json_decode($id, 1);
//        for ($j = 0; count($allid) > $j; $j++) {
//            echo $allid[$j];
//            $i[] = $allid[$j];
//        }
//        Db::table('chatmsg')->where('id',$id)->delete();
            var_dump(array_values($allid));
//        }
    }


//    删除多行
    public
    function Dellog()
    {
        $id = $_POST['id'];
        Db::table('chatmsg')->where('id', $id)->delete();
        echo $id;
    }


}
