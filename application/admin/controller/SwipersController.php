<?php

namespace app\admin\controller;

use app\common\model\Article;
use app\common\model\Swiper;
use think\Request;

class SwipersController extends BasicController
{
    public function swipers()
    {
        return view('swipers');
    }

    //获取用户信息
    public function getSwipers()
    {
        $Swiper = new Swiper();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg'] = "";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if (Request::instance()->has('search', 'get')) {
            $get = $_GET['search'];
            $sp_id = $get['sp_id'];
            $sp_name = $get['sp_name'];
            $map = [];
            $sp_id == "" ?: $map['sp_id'] = ['=', $sp_id];
            $sp_name == "" ?: $map['sp_name'] = ['LIKE', '%' . $sp_name . '%'];
            $swipers = $Swiper->alias('s')
                ->join('article a', 's.art_id=a.art_id')
                ->limit(($page - 1) * $limit, $limit)
                ->where($map)
                ->select();
            $arr['count'] = $Swiper->where($map)->count();
            $arr['data'] = $swipers;
        } else {
            $swipers = $Swiper->alias('s')
                ->join('article a', 's.art_id=a.art_id')
                ->limit(($page - 1) * $limit, $limit)
                ->select();
            $arr['count'] = $Swiper->count();
            $arr['data'] = $swipers;
        }
        echo json_encode($arr);
    }

    public function addSwiper()
    {
        return view('addSwiper');
    }

    public function add()
    {
        $Swiper = new Swiper();
        $data = [];
        if (Request::instance()->has('post', 'post')) {
            $post = json_decode($_POST['post'], 1);
            $art0 = Article::get(['art_id' => $post['art_id']]);
            if (!$art0) {
                $data['status'] = '1';
                $data['msg'] = '文章不存在，添加失败';
            } else {
                $data['status'] = '0';
                $swiper = $Swiper->data([
                    'sp_name' => $post['sp_name'], 'art_id' => $post['art_id'], 'sp_time' => $post['sp_time'], 'sp_status' => $post['sp_status'], 'sp_img' => $post['sp_img']
                ]);
                $data['msg'] = '添加成功';
                $swiper = $Swiper->save();
            }
        } else {
            $data['msg'] = '添加失败';
        }
        echo json_encode($data);
    }

    public function editSwiper()
    {
        $Swiper = new Swiper();
        if (Request::instance()->has('sp_id', 'get')) {
            $sp_id = $_GET['sp_id'];
            $swiper = $Swiper->where('sp_id=' . $sp_id)->select();
            $this->assign('swiper', $swiper);
            return view('editSwiper');
        }
    }

    public function edit()
    {
        $Swiper = new Swiper();
        $data = [];
        if (Request::instance()->has('post', 'post')) {
            $post = json_decode($_POST['post'], 1);
            $sp_id = $post['sp_id'];
            $swiper = $Swiper->isUpdate(true, ['sp_id=' . $sp_id])->save($post);
            $data['msg'] = '修改成功';
            // $user0 = User::get(['user_name' => $post['user_name']]);
            // if($user0){
            //     $data['msg'] = '用户名已存在';
            // }else{
            //     $user = $User->isUpdate(true,['user_id='.$user_id])->save($post);
            //     $data['msg'] = '修改成功';
            // }
        } else {
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }


    //删除一行
    public function deleteSwiper()
    {
        $Swiper = new Swiper();
        $data = [];
        if (Request::instance()->has('sp_id', 'get')) {
            $sp_id = $_GET['sp_id'];
            Swiper::destroy(['sp_id' => $sp_id]);
            $data['status'] = 1;
            $data['msg'] = '删除成功';
        } else {
            $data['status'] = 2;
            $data['msg'] = '删除失败';
        }
        echo json_encode($data);
    }

    public function upload()
    {
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if ($file) {
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'swiper');
            if ($info) {
                // 成功上传后 获取上传信息
                // 输出 jpg
                $path = '/thinkphp/public/uploads/swiper/' . $info->getSaveName();
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
            } else {
                // 上传失败获取错误信息
                echo $file->getError();
            }
        }
    }

    public function saveicon()
    {
        $Swiper = new Swiper();
        $data = [];
        if (Request::instance()->has('sp_id', 'get')) {
            $sp_id = $_GET['sp_id'];
            $sp_img = $_GET['path'];
            $swiper = $Swiper->save(['sp_img' => $sp_img], ['sp_id' => $sp_id]);
            $data['msg'] = '上传成功';
        } else {
            $data['msg'] = '上传失败';
        }
        echo json_encode($data);
    }
}
