<?php
namespace app\admin\controller;
use app\common\model\Article;
use app\common\model\Board;
use app\common\model\Atype;
use think\Request;

class ArticlesController extends \think\Controller
{
    public function articles()
    {
        return view('articles');
    }

    public function getArticles(){
        $Article = new Article();
        //json头
        $arr = array();
        $search = array();
        $arr['code'] = 0;
        $arr['msg']="";
        //分页
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
            $articles = $Article->alias('a')
                        ->join('user u','a.user_id=u.user_id')
                        ->join('board b','a.art_board_id=b.board_id')
                        ->join('atype t','a.atype_id=t.atype_id')
                        ->limit(($page-1)*$limit,$limit)
                        ->where($get)
                        ->select();
            $arr['count'] = $Article->where($get)->count();
            $arr['data']=$articles;
        }else{
            $articles = $Article->alias('a')
                        ->join('user u','a.user_id=u.user_id')
                        ->join('board b','a.art_board_id=b.board_id')
                        ->join('atype t','a.atype_id=t.atype_id')
                        ->limit(($page-1)*$limit,$limit)
                        ->select();
            $arr['count'] = $Article->count();
            $arr['data']=$articles;
        }
        echo json_encode($arr);
    }

    public function editArticle(){
        $Article = new Article();
        $Board = new Board();
        $Atype = new Atype();
        if(Request::instance()->has('art_id','get')){
            $art_id = $_GET['art_id'];
            $article = $Article->where('art_id='.$art_id)->select();
            $board = $Board->select();
            $board_id = $_GET['board_id'];
            $atype = $Atype
                    ->where('board_id='.$board_id)
                    ->select();
            $this->assign('article',$article);
            $this->assign('board',$board);
            $this->assign('atype',$atype);
            return view('editArticle');
        }
    }    

    public function getAtype(){
        if(Request::instance()->has('board_id','post')){
            $board_id = json_decode($_POST['board_id'], 1);
            $Atype = new Atype();
            $atype = $Atype->where('board_id='.$board_id)->select();
            return json($atype);
        }    
    }

    public function edit(){
        $Article = new Article();
        $data = [];
        if(Request::instance()->has('post','post')){
            $post = json_decode($_POST['post'], 1);
            $art_id = $post['art_id'];
                $article = $Article->isUpdate(true,['art_id='.$art_id])->save($post);
                $data['msg'] = '修改成功';
        }else{
            $data['msg'] = '修改失败';
        }
        echo json_encode($data);
    }    

    public function moveArticle(){
        $Article = new Article();
        $Board = new Board();
        $Atype = new Atype();
        if(Request::instance()->has('art_id','get')){
            $art_id = $_GET['art_id'];
            $article = $Article->where('art_id='.$art_id)->select();
            $board = $Board->select();
            $atype = $Atype->select();
            $this->assign('article',$article);
            $this->assign('board',$board);
            $this->assign('atype',$atype);
            return view('moveArticle');
        }else{
            var_dump('222');
        }
    }
}
