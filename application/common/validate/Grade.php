<?php
namespace app\common\validate;
use think\Validate;

class Grade extends Validate
{
    protected $rule = [
        'user_id' => 'require',
        'grade_point'  => 'require|between:100,1000',
        'grade_content'  => 'require|length:0,20',
    ];
}