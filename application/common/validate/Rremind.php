<?php
namespace app\common\validate;
use think\Validate;

class Rremind extends Validate
{
    protected $rule = [
        'user_id'  => 'require',
        're_user_id' => 'require',
        'art_id' => 'require',
    ];
}