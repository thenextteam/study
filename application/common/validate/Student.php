<?php
namespace app\common\validate;
use think\Validate;     // 内置验证类

class Student extends Validate
{
    protected $rule = [
        'name'  => 'require|length:2,25',
        'num' => 'number|length:1,5',
        'sex' => 'in:0,1',
        'klass_id' => 'number|length:1,5',
        'email' => 'email',
    ];
}