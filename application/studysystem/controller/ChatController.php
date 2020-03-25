<?php

namespace app\studysystem\controller;
//命名空间，也说明了文件所在的文件夹
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\Article;      // 引入帖子
use app\common\model\Board;      // 引入版块
use app\common\model\Comment;      // 引入回复
use app\common\model\Rremind;      // 引入回复提醒
use app\common\model\Aremind;
use app\common\model\Grade;
use app\common\model\Tip;
use think\Session;
use think\Request;
use think\Db;

class ChatController extends Controller
{

    //获取用户数据接口
    public function getUserdata()
    {

        $my = array();
        $friend = array();

        $flist = array();
        $arr = array();
        $glist = array();

        //获取个人信息
        $user = Db::table('user')->where('user_id', Session::get('UserId'))->find();

        //查出用户id对应的好友id和好友分组id,去除重复项
        $fg_id = Db::table('friend')->where('user_id', Session::get('UserId'))->group("friend_user_id")->select();

        $friendgroup = Db::table('fgroupname')->where('user_id', Session::get('UserId'))->select();

        if ($fg_id == null) {
            //如果该用户没有好友
            $friendgroup[0]['list'] = $friend;

        } else {
            //该用户有好友
            //遍历第一层为好友分组
            for ($g = 0; sizeof($friendgroup) - 1 >= $g; $g++) {
                $gid = $friendgroup[$g]['id'];
                //遍历第二层为获取到的gid去获取fid
                for ($i = 0; count($fg_id) -1 >= $i; $i++) {
                    //查询每一条数据是否有符合分组id下面的好友
                    if ($gid == $fg_id[$i]['gid']) {
                        $fid = $fg_id[$i]['friend_user_id'];
                        $fri = Db::table('user')->where('user_id', $fid)->find();
                        $flist['id'] = $fri['user_id'];
                        $flist['username'] = $fri['nick_name'];
                        $flist['sign'] = $fri['sign'];
                        //这个路径需要根据具体情况修改，绝对路径
                        $flist['avatar'] = "/thinkphp/public/static/study/img/userimg/" . $fri['user_img'];
                        if ($fri['status'] == 1) {
                            $flist['status'] = "online";
                        } else {
                            $flist['status'] = "hide";
                        }
                        $friend[] = $flist;
                    }
                }
                $friendgroup[$g]['list'] = $friend;
                //清空数组
                $friend = array();
                $flist = array();
            }
        }

        //通过id获取用户的好友分组名称


        //获取用户的群聊id
        $g = Db::table('groupmember')->where('uid', Session::get('UserId'))->select();
        for ($gr = 0; sizeof($g) - 1 >= $gr; $gr++) {
            //遍历
            $group = Db::table('groups')->where('id', $g[$gr]['gid'])->find();
            $glist[] = $group;
        }


        //返回的json
        $arr['code'] = 0;
        $arr['msg'] = "";

        //自身信息
        $alldata = array();
        $my['id'] = $user['user_id'];
        $my['username'] = $user['nick_name'];
        $my['sign'] = $user['sign'];
        $my['avatar'] = "/thinkphp/public/static/study/img/userimg/" . $user['user_img'];
        if ($user['status'] == 1) {
            $my['status'] = "online";
        } else {
            $my['status'] = "hide";
        }
        $alldata['mine'] = $my;

        //好友信息
        $alldata['friend'] = $friendgroup;
        //群聊信息
        $alldata['group'] = $glist;

        $arr['data'] = $alldata;

        //群组信息
        return $arr;
    }

    //获取群友接口
    public function getMembers()
    {
        $gid = $_GET['id'];
        $allarray = array();
        $members = array();
        $m = array();
        $mlist = array();
        //获取群聊id对应的群友id
        $fid = Db::table('groupmember')->where('gid', $gid)->select();
        foreach ($fid as $key => $vaules) {
            $uid = $vaules['uid'];
            //群友id对应信息
            $member = Db::table('user')->where('user_id', $uid)->find();
            $m['id'] = $member['user_id'];
            $m['username'] = $member['nick_name'];
            $m['sign'] = $member['sign'];
            $m['avatar'] = "/thinkphp/public/static/study/img/userimg/" . $member['user_img'];
            //1为在线
            if ($member['status'] == 1) {
                $m['status'] = "online";
            } else {
                $m['status'] = "hide";
            }
            $members[] = $m;
        }
        $allarray['code'] = 0;
        $allarray['msg'] = "";
        $mlist['list'] = $members;
        $allarray['data'] = $mlist;

        return $allarray;
    }

    //切换在线状态
    public function changeStatus()
    {
        $status = $_POST['mystatus'];
        echo $status;
        if ($status == "online") {
            Db::table('user')->where('user_id', Session::get('UserId'))->update(['status' => 1]);
        } else {
            Db::table('user')->where('user_id', Session::get('UserId'))->update(['status' => 0]);
        }
    }

    public function getChatLog()
    {
        //查询指定的id和类型
        $allmsg = array();
        $data = array();
        $itemdata = array();
        $uid = Session::get('UserId');
        $id = $_POST['id'];
        $type = $_POST['type'];
        if ($type == "friend") {
            //查出双方的聊天
            $msg = Db::query("select * from chatmsg where fromid in(" . $uid . "," . $id . ") and toid in(" . $uid . "," . $id . ") order by timeline");
            $my = Db::table('user')->where('user_id', $uid)->find();
            $friend = Db::table('user')->where('user_id', $id)->find();

            $mydata['username'] = $my['nick_name'];
            $mydata['avatar'] = "/thinkphp/public/static/study/img/userimg/" . $my['user_img'];
            $mydata['id'] = $my['user_id'];

            $fridata['username'] = $friend['nick_name'];
            $fridata['avatar'] = "/thinkphp/public/static/study/img/userimg/" . $friend['user_img'];
            $fridata['id'] = $friend['user_id'];
            for ($i = 0; count($msg) > $i; $i++) {
                if ($msg[$i]['fromid'] == $uid) {
                    //自己说的
                    $mydata['timestamp'] = $msg[$i]['timeline'] * 1000;
                    $mydata['content'] = $msg[$i]['content'];
                    $data[$i] = $mydata;
                } else {
                    //别人说的
                    $fridata['timestamp'] = $msg[$i]['timeline'] * 1000;
                    $fridata['content'] = $msg[$i]['content'];
                    $data[$i] = $fridata;
                }
            }
            $allmsg['code'] = 0;
            $allmsg['msg'] = "";
            $allmsg['data'] = $data;
            return $allmsg;
        }else{

        }
    }
    public  function changeSign(){
        $sign = $_POST['sign'];

        Db::table('user')->where('user_id',Session::get('UserId'))->update(['sign' => $sign]);
    }
}