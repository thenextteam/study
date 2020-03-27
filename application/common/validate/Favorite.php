<?php
namespace app\common\validate;
use think\Validate;

class Favorite extends Validate
{
    protected $rule = [
        'user_id'  => 'require'
    ];
}