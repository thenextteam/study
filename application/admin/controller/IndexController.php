<?php
namespace app\admin\controller;

class IndexController extends BasicController 
{
    public function index()
    {
        return view('index');
    }
}