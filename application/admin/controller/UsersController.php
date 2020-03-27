<?php
namespace app\admin\controller;
use app\common\model\User;
use think\Request;

class UsersController extends BasicController
{
    public function users()
    {
        return view('users');
    }

    //获取用户信息
    public function getUsers(){
        $User = new User();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if(Request::instance()->has('search','get')){
            $get = $_GET['search'];
            foreach ($get as $key=>$value)
            {
                if(empty($value)){  
                    unset($get[$key]);
                }
            }
            $arr['msg']=$get;
            $users = $User->limit(($page-1)*$limit,$limit)->where($get)->select();
            $arr['count'] = $User->where($get)->count();
            $arr['data']=$users;
        }else{
            $users = $User->limit(($page-1)*$limit,$limit)->select();
            $arr['count'] = $User->count();
            $arr['data']=$users;
        }
        echo json_encode($arr);
    }

    public function editUser(){
        $User = new User();
        if(Request::instance()->has('user_id','get')){
            $user_id = $_GET['user_id'];
            $user = $User->where('user_id='.$user_id)->select();
            $this->assign('user',$user);
            return view('editUser');
        }    
        
    }    

    public function edit(){
        $User = new User();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $user_id = $post['user_id'];
            $user = $User->isUpdate(true,['user_id='.$user_id])->save($post);
            $data['msg'] = '修改成功';
            // $user0 = User::get(['user_name' => $post['user_name']]);
            // if($user0){
            //     $data['msg'] = '用户名已存在';
            // }else{
            //     $user = $User->isUpdate(true,['user_id='.$user_id])->save($post);
            //     $data['msg'] = '修改成功';
            // }
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    

    public function editPassword(){
        $User = new User();
        if(Request::instance()->has('user_id','get')){
            $user_id = $_GET['user_id'];
             $user = $User->where('user_id='.$user_id)->select();
            $this->assign('user',$user);
            return view('editPassword');
        }else{

        }    
    }    

    public function ed_pw(){
        $User = new User();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $user_id = $post['user_id'];
            $user_pwd = md5($post['user_pwd']);
            $user = $User->save(['user_pwd' => $user_pwd],['user_id' => $user_id]);
            $data['msg'] = '重置成功';
        }else{
            $data['msg'] = '重置失败';
        }
        echo json_encode($data);
    }  

    public function addUser(){
        return view('addUser');
    }

    //添加
    public function add(){
        $User = new User();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $user0 = User::get(['user_name' => $post['user_name']]);
            if($user0){
                $data['status'] = '1';
                $data['msg'] = '用户名已存在,添加失败';
            }else{
                $data['status'] = '0';
                $user = $User->data(['user_name'=>$post['user_name'],'nick_name'=>$post['nick_name']
                ,'user_email'=>$post['user_email'] ,'user_pwd'=>md5($post['user_pwd'])
                ,'user_regtime'=>$post['user_regtime'] ,'user_lasttime'=>$post['user_regtime']
                ,'user_lv'=>$post['user_lv'] ,'user_point'=>$post['user_point']
                ,'user_lv'=>$post['user_lv'] ,'user_money'=>$post['user_money']
                ,'user_grant'=>$post['user_grant'],'user_img'=>'/thinkphp/public/uploads/icons/base.jpg'
                ]);
                $data['msg'] = '添加成功';
                $user = $User->save();
            }
        }else{
            $data['msg'] = '添加失败';
        }
        echo json_encode($data);
    }

    //删除多行
    public function multiDelete(){
        $User = new User();
        $data = [];
        if(Request::instance()->has('array','post')){
            $post = json_decode($_POST['array'], 1);
            $user_id ="";
            for($i=0;$i<count($post);$i++){
                $user_id =$user_id.$post[$i].",";
            }
             $user_id = substr($user_id,0,-1);
             $user = $User->destroy($user_id);
             $data['msg'] = '删除成功';
        }else{
            $data['msg'] = '删除失败';
        }
        echo json_encode($data);
    }

    public function upload(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS .'icons');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $path = '/thinkphp/public/uploads/icons/'.$info->getSaveName();
                $data = [];
                $data['code'] = 0;
                $data['path'] = $path;
                $data['msg'] = '上传成功';
                echo json_encode($data);
                // echo $info->getExtension();
                // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getSaveName();
                // // 输出 42a79759f284b767dfcb2a0197904287.jpg
                // echo $info->getFilename(); 
            }else{
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

    public function saveicon(){
        $User = new User();
        $data = [];
        if(Request::instance()->has('user_id','get')){
            $user_id = $_GET['user_id'];
            $user_img = $_GET['path'];
            $user = $User->save(['user_img' => $user_img],['user_id' => $user_id]);
            $data['msg'] = '上传成功';
        }else{
            $data['msg'] = '上传失败';
        }
        echo json_encode($data);
    }    
}
