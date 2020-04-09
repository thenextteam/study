<?php
namespace app\admin\controller;
use app\common\model\Board;
use app\common\model\Bhead;
use app\common\model\Atype;
use think\Request;

class BoardsController extends BasicController
{
    public function boards()
    {
        return view('boards');
    }

    //获取用户信息
    public function getBoards(){
        $Board = new Board();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if(Request::instance()->has('search','get')){
            $get = $_GET['search'];
           
            $board_id = $get['board_id'];
            $board_name = $get['board_name'];
            $map = [];
            $board_id==""?:$map['board_id']=['=',$board_id];
            $board_name==""?:$map['board_name']=['LIKE','%'.$board_name.'%'];
            $arr['msg']= $get;
            $boards = $Board->alias('b')
                      ->join('bhead bh','b.bhead_id=bh.bhead_id')
                      ->limit(($page-1)*$limit,$limit)
                      ->where($map)
                      ->select();
            $arr['count'] = $Board->where($map)->count();
            $arr['data']=$boards;
        }else{
            $boards = $Board->alias('b')
                    ->join('bhead bh','b.bhead_id=bh.bhead_id')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
            $arr['count'] = $Board->count();
            $arr['data']=$boards;
        }
        echo json_encode($arr);
    }

    public function addBoard(){
        $Bhead = new Bhead();
        $bhead = $Bhead->select();
        $this->assign('bhead',$bhead);
        return view('addBoard');
    }


    public function add(){
        $Board = new Board();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $bo0 = Board::get(['board_name' => $post['board_name']]);
            if($bo0){
                $data['status'] = '1';
                $data['msg'] = '版块名已存在,添加失败';
            }else{
                $data['status'] = '0';
                $board = $Board->data(['board_name'=>$post['board_name'],'board_info'=>$post['board_info']
                ,'board_top'=>$post['board_top'] ,'bhead_id'=>$post['bhead_id'] ,'board_th'=>$post['board_th']
                ,'board_status'=>$post['board_status'],'board_img'=>'base.png'
                ]);
                $data['msg'] = '添加成功';
                $board = $Board->save();
            }
        }else{
            $data['msg'] = '添加失败';
        }
        echo json_encode($data);
    }


    public function editBoard(){
        $Board = new Board();
        $Bhead = new Bhead();
        if(Request::instance()->has('board_id','get')){
            $board_id = $_GET['board_id'];
            $board = $Board->where('board_id='.$board_id)->select();
            $bhead = $Bhead->select();
            $this->assign('board',$board);
            $this->assign('bhead',$bhead);
            return view('editBoard');
        }
    }    

    public function edit(){
        $Board = new Board();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $board_id = $post['board_id'];
                $board = $Board->isUpdate(true,['board_id='.$board_id])->save($post);
                $data['msg'] = '修改成功';
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    


     //删除一行
     public function deleteBoard(){
        $Atype = new Atype();
        $Board = new Board();
        $data = [];
        if(Request::instance()->has('board_id','get')){
            // $post = json_decode($_POST['board_id'], 1);
            // $board_id = $post['board_id'];
            $board_id = $_GET['board_id'];
            $atype = Atype::get(['board_id' => $board_id]);
            if($atype){
                $data['status'] = 0;
                $data['msg'] = '子类不为空，删除失败';
            }else{
                $board = $Board->save(['board_status'=>1],['board_id' => $board_id]);
                $data['status'] = 1;
                $data['msg'] = '删除成功';
            }
        } else{
            $data['status'] = 2;
            $data['msg'] = '删除失败';
        }   
        echo json_encode($data);
    }

    public function atypesShow(){
        if(Request::instance()->has('board_id','get')){
            $board_id = $_GET['board_id'];
            $this->assign('board_id',$board_id);
            return view('atypesShow');
        }    
    }

    public function getAtypes(){
        $Atype = new Atype();
        if(Request::instance()->has('board_id','get')){
            $board_id = $_GET['board_id'];
            $arr['code'] = 0;
            $arr['msg']="";
            $atypes = $Atype->where('board_id='.$board_id)->select();
            $arr['count'] = $Atype->where('board_id='.$board_id)->count();
            $arr['data']=$atypes;
            echo json_encode($arr);
        }    
    }    

    public function upload(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->rule('uniqid')->move(ROOT_PATH . 'public' . DS . 'uploads' . DS .'board');
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                $path = '/thinkphp/public/uploads/board/'.$info->getSaveName();
                $data = [];
                $data['code'] = 0;
                $data['savename'] = $info->getSaveName();
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
        $Board = new Board();
        $data = [];
        if(Request::instance()->has('board_id','get')){
            $board_id = $_GET['board_id'];
            $board_img = $_GET['board_img'];
            $savename = $_GET['savename'];
            $path = ROOT_PATH . 'public/uploads/board/' . $savename;
            $re_path = ROOT_PATH . 'public/uploads/boardimgs/' . $savename;
            rename($path, $re_path);
            if ($board_img != 'base.png') {
                $path0 = ROOT_PATH . 'public/uploads/boardimgs/' . $board_img;
                $re_path0 = ROOT_PATH . 'public/uploads/board/' .  $board_img;
                rename($path0, $re_path0);
            }
            $board = $Board->save(['board_img' => $savename],['board_id' => $board_id]);
            $data['msg'] = '上传成功';
        }else{
            $data['msg'] = '上传失败';
        }
        echo json_encode($data);
    }    

}
