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

    public function group()
    {
        return view('group');
    }

    public function getChatLog()
    {
        $arr = array();
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
//        }
    }


    public function Dellog()
    {
        $id = $_POST['id'];
        Db::table('chatmsg')->where('id', $id)->delete();
        echo '删除成功';
    }

    public function Delgroup()
    {
        $id = $_POST['id'];
        Db::table('groups')->where('id', $id)->delete();
        echo '删除成功';
    }

    public function getGroup()
    {
        $arr = array();
        $arr['code'] = 0;
        $arr['msg'] = "";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        $MsgCount = Db::name('groups')->count();

        if (Request::instance()->has('search', 'get')) {

            $get = $_GET['search'];
            $groupname = $get['groupname'];

            $map = [];
            $groupname == "" ?: $map['groupname'] = ['LIKE', '%' . $groupname . '%'];

            $Count = Db::table('groups')
                ->where($map)->count();

            $groups = Db::table('groups')
                ->limit(($page - 1) * $limit, $limit)
                ->where($map)->select();
            $arr['count'] = $Count;
            $arr['data'] = $groups;
            return $arr;
        } else {
            $groups = Db::table('groups')
                ->limit(($page - 1) * $limit, $limit)->select();
            $arr['count'] = $MsgCount;
            $arr['data'] = $groups;
        }
        return $arr;
    }

    public function editGroup(){
        $id = $_GET['gid'];
        $group = Db::table('groups')->where('id', $id)->select();
        $this->assign('group',$group);

        return view('editGroup');
    }

    public function edit(){
        $data = [];
        if (Request::instance()->has('post', 'post')) {
            $post = json_decode($_POST['post'], 1);
            $id = $post['id'];
            Db::table('groups')->where('id',$id)->update($post);
            $data['msg'] = '修改成功';
        } else {
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }
}
