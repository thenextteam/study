<?php
namespace app\common\validate;
use think\Validate;     // 内置验证类

class User extends Validate
{
    protected $rule = [
        'user_name' => 'require|unique:user|length:4,25',
        'user_pwd' => 'require|length:32,32',
        'user_email' => 'email',
    ];
}