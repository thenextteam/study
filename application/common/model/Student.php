<?php
namespace app\common\model;
use think\Model;
/**
 * 学生
 */
class Student extends Model
{
    /**
     * 输出性别的属性
     * @return string 0男，1女
     * @author 梦云智 http://www.mengyunzhi.com
     */
    public function getSexAttr($value)
    {
        $status = array('0'=>'男','1'=>'女');
        $sex = $status[$value];
        if (isset($sex))
        {
            return $sex;
        } else {
            return $status[0];
        }
    } 
    /**
     * 自定义自转换字换
     * @var array
     */
    protected $type = [
        'create_time' => 'datetime',
    ];
    protected $dateFormat = 'Y年m月d日';    // 日期格式
    /**
     * 获取要显示的创建时间
     * @param  int $value 时间戳
     * @return string  转换后的字符串
     * @author panjie <panjie@yunzhiclub.com>
     */
    public function getCreateTimeAttr($value)
    {
        return date('Y-m-d', $value);
    }
    /**
     * ThinkPHP使用一个叫做__get()的魔法函数来完成这个函数的自动调用
     * 在本章第五节中，我们将专门对__get()进行讲解
     * @author 梦云智 http://www.mengyunzhi.com
    */
    public function Klass()
    {
        return $this->belongsTo('Klass');
    }
}