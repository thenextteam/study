<?php
namespace app\common\model;
use think\Model;
/**
 * 板块大类
 */
class Bhead extends Model
{
    /**
     * 关联版块表
     */
    public function Board()
    {
        return $this->hasMany('board','bhead_id');
    }

    /**
     * 按优先度排序
     */
    public function GetBoardTop($value)
    {
        // // $boardid = $boardid = db('board')->where('bhead_id',$value)->order('board_top desc')->field('board_id')->find();
        // // $kosql = db('article')->where('art_status',0)->where('art_board_id',$boardid['board_id'])->order('last_com_time desc')->find();
        // $result = db('board')->where('bhead_id',$value)->order('board_top desc')->select();
        // return $result;
        // $join = ([$kosql=> b], 'a.board_id = b.art_board_id');
        // $join = [
        //     ['article b','a.board_id=b.art_board_id and b.art_status=0'],
        // ];
        // return db('board')->alias('a')->join($join)->where('bhead_id',$value)->order('board_top desc')->select();
        return db('board')->where('bhead_id',$value)->order('board_top desc')->select();
    }
}