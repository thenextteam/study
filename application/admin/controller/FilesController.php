<?php
namespace app\admin\controller;
use app\common\model\File;
use app\common\model\User;
use think\Request;

class FilesController extends BasicController
{
    public function files()
    {
        return view('files');
    }

     //获取用户信息
     public function getFiles(){
        $File = new File();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if(Request::instance()->has('search','get')){
            $get = $_GET['search'];
            $file_id = $get['file_id'];
            $file_name = $get['file_name'];
            $map = [];
            $file_id==""?:$map['file_id']=['=',$file_id];
            $file_name==""?:$map['file_name']=['LIKE','%'.$file_name.'%'];
            $files = $File->alias('f')
                      ->join('user u','f.user_id=u.user_id')
                      ->limit(($page-1)*$limit,$limit)
                      ->where($map)
                      ->select();
            $arr['count'] = $File->where($map)->count();
            $arr['data']=$files;
        }else{
            $files = $File->alias('f')
                    ->join('user u','f.user_id=u.user_id')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
            $arr['count'] = $File->count();
            $arr['data']=$files;
        }
        echo json_encode($arr);
    }

    public function editFile(){
        $File = new File();
        if(Request::instance()->has('file_id','get')){
            $file_id = $_GET['file_id'];
            $file = $File->where('file_id='.$file_id)->select();
            $this->assign('file',$file);
            return view('editFile');
        }
    }    

    public function edit(){
        $File = new File();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $file_id = $post['file_id'];
            $file = $File->isUpdate(true,['file_id='.$file_id])->save($post);
            $data['msg'] = '修改成功';
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    
}