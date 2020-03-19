<?php
namespace app\common\validate;
use think\Validate;

class Tip extends Validate
{
    protected $rule = [
        'user_id' => 'require',
    ];
}