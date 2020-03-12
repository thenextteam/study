<?php
namespace app\common\model;
use think\Model;
/**
 * 班级
 */
class Klass extends Model
{
    private $Teacher;

    public function Teacher()
    {
        return $this->belongsTo('Teacher');
    }

}