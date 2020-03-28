<?php
namespace app\admin\controller;
use app\common\model\Comment;
use think\Request;

class CommentsController extends BasicController
{
    public function Comments()
    {
        return view('comments');
    }

    //获取用户信息
    public function getComments(){
        $Comment = new Comment();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if(Request::instance()->has('search','get')){
            $get = $_GET['search'];
            $com_id = $get['com_id'];
            $user_id = $get['user_id'];
            $art_id = $get['art_id'];
            $map = [];
            $com_id==""?:$map['com_id']=['=',$com_id];
            $user_id==""?:$map['user_id']=['=',$user_id];
            $art_id==""?:$map['art_id']=['=',$art_id];
            $comments = $Comment->alias('c')
                        ->join('user u','c.user_id=u.user_id')
                        ->limit(($page-1)*$limit,$limit)
                        ->where($map)
                        ->select();
            $arr['count'] = $Comment->where($map)->count();
            $arr['data']=$comments;
        }else{
            $comments = $Comment->alias('c')
                        ->join('user u','c.user_id=u.user_id')
                        ->limit(($page-1)*$limit,$limit)
                        ->select();
            $arr['count'] = $Comment->count();
            $arr['data']=$comments;
        }
        echo json_encode($arr);
    }

    public function editComment(){
        $Comment = new Comment();
        if(Request::instance()->has('com_id','get')){
            $com_id = $_GET['com_id'];
            $comment = $Comment->where('com_id='.$com_id)->select();
            $this->assign('comment',$comment);
            return view('editComment');
        }
    }    

    public function edit(){
        $Comment = new Comment();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $com_id = $post['com_id'];
            $comment = $Comment->isUpdate(true,['com_id='.$com_id])->save($post);
            $data['msg'] = '修改成功';
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    
}
