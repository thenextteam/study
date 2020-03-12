<?php
namespace app\common\validate;
use think\Validate;

class Article extends Validate
{
    protected $rule = [
        'user_id' => 'require',
        'atype_id' => 'require',
        'art_title'  => 'require|length:7,70',
        'art_content'  => 'require|length:20,2500',
    ];
}