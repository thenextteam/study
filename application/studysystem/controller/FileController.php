<?php
namespace app\studysystem\controller;     
use think\Controller;
use app\common\model\User;      // 引入用户
use app\common\model\File;      // 引入签到
use think\Session;
use think\Db;
use think\Request;              // 请求

/**
 * 签到
 */
class FileController extends Controller
{
    public function __construct()
    {        
        //调用父类构造函数(必须)
        parent::__construct();


        // 验证用户是否登陆
        if (!User::isLogin()) {
            return $this->error('上传/下载请先登录', url('Login/index'));
        }
        else{

        }
    }

    public function index()
    {
        //获取当前用户
        if(Session::get('UserId')){
            $nowuser = new User;
            // $nowuser::get(Session::get('UserId'));
            $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
        }
        else{
            $Key="safe";
            //如果Session中没有登录信息，尝试从Cookie中加载用户信息
            if(isset($_COOKIE['Login'])){
                $Value = $_COOKIE['Login'];
                // 去掉魔术引号
                if (get_magic_quotes_gpc()) {
                    $Value = stripslashes($Value);
                }
                $Str = substr($Value,0,32);
                $Value = substr($Value,32);
                //校验
                if (md5($Value . $Key) == $Str) {
                    $User = unserialize($Value);
                    session('UserId', $User[0]);
                    session('UserName', $User[1]);
                    session('NickName', $User[2]);
                    $nowuser = new User;
                    $this->assign('nowuser',$nowuser::get(Session::get('UserId')));
                }
            }
            else{
                $this->assign('nowuser','');
            }
        }
        $join = [
            ['user b','a.user_id=b.user_id'],
        ];
        $File = new File();
        $User = new User();
        $file = $File->alias('a')->join($join)->order('file_time desc')->paginate(5,false);
        // $user = $User->select();
        $page = $file->render();
        $this->assign('page',$page);
        $this->assign('file',$file);
        // $this->assign('user',$User);
        return $this->fetch();
    }

    public function upfile(){
      // 获取表单上传文件 例如上传了001.jpg

       // 实例化请求信息
       $Request = Request::instance();
       $file = $Request->file('file');
       $file_name = $Request->post('file_name');
      // 移动到框架应用根目录/public/uploads/ 目录下
      if($file){
          $info = $file->validate(['ext'=>'rar'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS .'files');
          if($info){
              // 成功上传后 获取上传信息
              // 输出 jpg
              $path = '/uploads/files/'.$info->getSaveName();
              // $file_name = $info->getFilename();
              $File = new File();
              $file = $File->data(['file_name'=>$file_name,'file_path'=>$path,
              'user_id'=>Session::get('UserId'),'file_path'=>$path,'file_time'=>date("Y-m-d H:i:s")
              ]);
              $file = $File->save();
              return $this->success('上传成功', $Request->header('referer'));
              // echo $info->getExtension();
              // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
              // echo $info->getSaveName();
              // // 输出 42a79759f284b767dfcb2a0197904287.jpg
              // echo $info->getFilename(); 
          }else{
              // 上传失败获取错误信息
              return $this->error('上传失败：'.$file->getError());
          }
      }
    }

    public function downloadFile(){
      $Request = Request::instance();
      $file_path=$Request->post('file_path');
      // $File = new File();
      // $file = $File->where('file_id='.$file_id)->select();
      if($file_path){
       $file_dir = ROOT_PATH . 'public' . DS . $file_path;
       $file1 = fopen($file_dir, "r");
        // 输入文件标签
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length:".filesize($file_dir));
        Header("Content-Disposition: attachment;filename=" . $file_dir);
        ob_clean();     // 重点！！！
        flush();        // 重点！！！！可以清除文件中多余的路径名以及解决乱码的问题：
        //输出文件内容
        //读取文件内容并直接输出到浏览器
        echo fread($file1, filesize($file_dir));
        fclose($file1);
        exit();
      }else{
        return $this->error('文件不存在');
      }

    }  
}