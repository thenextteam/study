<?php
namespace app\common\validate;
use think\Validate;

class Comment extends Validate
{
    protected $rule = [
        'user_id' => 'require',
        'art_id' => 'require',
        'com_content'  => 'require|length:20,2500',
    ];
}