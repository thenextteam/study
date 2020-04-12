<?php

namespace app\admin\controller;

use app\common\model\File;
use app\common\model\User;
use app\common\model\Article;
use app\common\model\Comment;
use think\Request;

class DeleteController extends BasicController
{
  public function delete()
  {
    return view('delete');
  }

  public function delDirAndFile($path, $time, $delDir = FALSE)
  {
    if (is_array($path)) {
      foreach ($path as $subPath)
        delDirAndFile($subPath, $delDir);
    }
    if (is_dir($path)) {
      $handle = opendir($path);
      if ($handle) {
        while (false !== ($item = readdir($handle))) {
          if ($item != "." && $item != "..")
            // is_dir("$path/$item") ? delDirAndFile("$path/$item", $delDir) : unlink("$path/$item");
            if (is_dir("$path/$item")) {
              delDirAndFile("$path/$item", $delDir);
            } else {
              if (time() - filemtime("$path/$item") > $time) {
                unlink("$path/$item");
              }
            }
        }
        closedir($handle);
        if ($delDir)
          return rmdir($path);
      }
    } else {
      if (file_exists($path)) {
        if (time() - filemtime("$path/$item") > $time) {
          return unlink($path);
        }
      } else {
        return FALSE;
      }
    }
    clearstatcache();
  }
  public function delSave()
  {
    // $path = request()->param('path');
    $path = 'icons';
    $time = 10;
    $Request = Request::instance();
    $time = $Request->post('time');
    $this->delDirAndFile(ROOT_PATH . 'public' . DS . 'uploads' . DS . $path, $time);
    // rmdir(ROOT_PATH . 'public' . DS . 'uploads' .DS .'icons' . DS . $path);
    return $this->success('删除成功', 'delete');
  }

  public function delBoard()
  {
    // $path = request()->param('path');
    $path = 'board';
    $time = 10;
    $Request = Request::instance();
    // $time = $Request->post('time');
    $this->delDirAndFile(ROOT_PATH . 'public' . DS . 'uploads' . DS . $path, $time);
    // rmdir(ROOT_PATH . 'public' . DS . 'uploads' .DS .'icons' . DS . $path);
    return $this->success('删除成功', 'delete');
  }
  public function delArticle()
  {
    Article::destroy(['art_status' =>1]);
    return $this->success('删除成功', 'delete');
  }
  public function delComment()
  {
    Comment::destroy(['art_status' =>1]);
    return $this->success('删除成功', 'delete');
  }
  // public function delFile()
  // {
  //   Comment::destroy(['art_status' =>1]);
  //   return $this->success('删除成功', 'delete');
  // }
}
