<?php
namespace app\common\validate;
use think\Validate;

class Sign extends Validate
{
    protected $rule = [
        'user_id'  => 'require',
        'user_mood'  => 'require',
        'sign_con' => 'length:0,100',
    ];
}