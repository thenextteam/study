<?php
namespace app\common\validate;
use think\Validate;

class Aremind extends Validate
{
    protected $rule = [
        'user_id' => 'require',
        'ao_user_id' => 'require',
        'art_id'  => 'require',
        'aremind_op'  => 'require',
    ];
}