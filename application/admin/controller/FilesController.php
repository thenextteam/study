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
            foreach ($get as $key=>$value)
            {
                if(empty($value)){  
                    unset($get[$key]);
                }
            }
            $files = $File->alias('f')
                      ->join('user u','f.user_id=u.user_id')
                      ->limit(($page-1)*$limit,$limit)
                      ->where($get)
                      ->select();
            $arr['count'] = $File->where($get)->count();
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
}