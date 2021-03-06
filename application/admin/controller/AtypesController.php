<?php
namespace app\admin\controller;
use app\common\model\Board;
use app\common\model\Bhead;
use app\common\model\Atype;
use app\common\model\Article;
use think\Request;

class AtypesController extends BasicController
{
    public function atypes()
    {
        return view('atypes');
    }

    //获取用户信息
    public function getAtypes(){
        $Atype = new Atype();

        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";

        $page = $_GET['page'];
        $limit = $_GET['limit'];

        if(Request::instance()->has('search','get')){
            $get = $_GET['search'];
            $atype_id = $get['atype_id'];
            $atype_name = $get['atype_name'];
            $map = [];
            $atype_id==""?:$map['atype_id']=['=',$atype_id];
            $atype_name==""?:$map['atype_name']=['LIKE','%'.$atype_name.'%'];
            $atypes = $Atype->alias('a')
                      ->join('board b','a.board_id=b.board_id')
                      ->limit(($page-1)*$limit,$limit)
                      ->where($map)
                      ->select();
            $arr['count'] = $Atype->where($map)->count();
            $arr['data']=$atypes;
        }else{
            $atypes = $Atype->alias('a')
                    ->join('board b','a.board_id=b.board_id')
                    ->limit(($page-1)*$limit,$limit)
                    ->select();
            $arr['count'] = $Atype->count();
            $arr['data']=$atypes;
        }
        echo json_encode($arr);
    }

    public function addAtype(){
        $Board = new Board();
        $board = $Board->select();
        $this->assign('board',$board);
        return view('addAtype');
    }


    public function add(){
        $Atype = new Atype();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $at0 = $Atype->where('atype_name = "'.$post['atype_name'].'" and board_id = "'.$post['board_id'].'"')->select();
            if($at0){
                $data['status'] = '1';
                $data['msg'] = '同版块分类名已存在,添加失败';
            }else{
                $data['status'] = '0';
                $atype = $Atype->data(['atype_name'=>$post['atype_name'],'board_id'=>$post['board_id']]);
                $data['msg'] = '添加成功';
                $atype = $Atype->save();
            }
        }else{
            $data['msg'] = '添加失败';
        }
        echo json_encode($data);
    }


    public function editAtype(){
        $Board = new Board();
        $Bhead = new Bhead();
        $Atype = new Atype();
        if(Request::instance()->has('atype_id','get')){
            $atype_id = $_GET['atype_id'];
            $board = $Board->select();
            $atype = $Atype->where('atype_id='.$atype_id)->select();
            // $bhead = $Bhead->select();
            $this->assign('board',$board);
            $this->assign('atype',$atype);
            // $this->assign('bhead',$bhead);
            return view('editAtype');
        }
    }    

    public function edit(){
        $Atype = new Atype();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $atype_id = $post['atype_id'];
                $atype = $Atype->isUpdate(true,['atype_id='.$atype_id])->save($post);
                $data['msg'] = '修改成功';
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    


     //删除一行
     public function deleteAtype(){
        $Atype = new Atype();
        $Article = new Article();
        $data = [];
        if(Request::instance()->has('atype_id','get')){
            $atype_id = $_GET['atype_id'];
            $article = Article::get(['atype_id' => $atype_id]);
            if($article){
                $data['status'] = 0;
                $data['msg'] = '子类不为空，删除失败';
            }else{
                Atype::destroy(['atype_id' =>$atype_id]);
                $data['status'] = 1;
                $data['msg'] = '删除成功';
            }
        } else{
            $data['status'] = 2;
            $data['msg'] = '删除失败';
        }   
        echo json_encode($data);
    }
    
    public function articlesShow(){
        if(Request::instance()->has('atype_id','get')){
            $atype_id = $_GET['atype_id'];
            $this->assign('atype_id',$atype_id);
            return view('articlesShow');
        }    
    }

    public function getArticles(){
        $Article = new Article();
        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";
        //分页
    
        if(Request::instance()->has('atype_id','get')){
            $atype_id = $_GET['atype_id'];
            $page = $_GET['page'];
            $limit = $_GET['limit'];
            $articles = $Article->where('atype_id='.$atype_id)->limit(($page-1)*$limit,$limit)->select();
            $arr['count'] = $Article->where('atype_id='.$atype_id)->limit(($page-1)*$limit,$limit)->count();
            $arr['data']=$articles;
            echo json_encode($arr);
        }    
    }    
}
