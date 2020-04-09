<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */

//declare(ticks=1);
use \GatewayWorker\Lib\Db;
use think\Session;

use \GatewayWorker\Lib\Gateway;

require_once __DIR__ . '/../../../vendor/autoload.php';

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     *
     * @param int $client_id 连接id
     * @throws Exception
     */
    public static function onConnect($client_id)
    {
//        $db = Db::instance('db1');
//        $db->query("update user set status = 0 where user_id = ".$_SESSION['UID'] );
        // 向当前client_id发送数据 
        Gateway::sendToClient($client_id, json_encode(array(
            'type' => 'init',
            'client_id' => $client_id
        )));
        // 向所有人发送
        //Gateway::sendToAll("$client_id login\r\n");
    }

    /**
     * 当客户端发来消息时触发
     * @param int $client_id 连接id
     * @param mixed $message 具体消息
     * @throws Exception
     */
    public static function onMessage($client_id, $message)
    {
//        echo $message;
        $message_data = json_decode($message, true);
//        var_dump($message_data['uid']);
        switch ($message_data['type']) {
            case 'bind':
                $db = Db::instance('db1');
                $gid = $db->query("select gid from groupmember where uid = {$message_data['uid']}");
                foreach ($gid as $key => $values) {
                    Gateway::joinGroup($client_id, $values['gid']);
                }
                Gateway::bindUid($client_id, $message_data['uid']);

                //最近一周时间
                $time = time() - 7 * 3600 * 24;
                $resMsg =$db->select('chatmsg.*, user.nick_name, user.user_img')->from('chatmsg')
                    ->innerJoin('user','chatmsg.fromid = user.user_id')
                    ->where("toid= {$message_data['uid']} and timeline > {$time} and type = 'friend' and needsend = 1")->query();
//                print_r($resMsg);
                if (!empty($resMsg)) {

                    foreach ($resMsg as $key => $vo) {

                        $log_message = [
                            'type' => 'logMessage',
                            'data' => [
                                'username' => $vo['nick_name'],
                                'avatar' => "/thinkphp/public/uploads/userimgs/".$vo['user_img'],
                                'id' => $vo['fromid'],
                                'type' => 'friend',
                                'content' => htmlspecialchars($vo['content']),
                                'timestamp' => $vo['timeline'] * 1000,
                            ]
                        ];

                        Gateway::sendToUid($message_data['uid'], json_encode($log_message));

                        //设置推送状态为已经推送
                        $db->query("UPDATE `chatmsg` SET `needsend` = '0' WHERE id=" . $vo['id']);

                    }
                }

                return;
                break;
            case 'chatMessage':
                $db = Db::instance('db1');
                $to_id = $message_data['data']['to']['id'];
                $uid = $message_data['data']['mine']['id'];
                $type = $message_data['data']['to']['type'];
                $chat_message = [
                    'type' => 'chatMessage',
                    'data' => [
                        'username' => $message_data['data']['mine']['username'],
                        'avatar' => $message_data['data']['mine']['avatar'],
                        'id' => $type === 'friend' ? $uid : $to_id,
                        'type' => $type,
                        'content' => htmlspecialchars($message_data['data']['mine']['content']),
                        'timestamp' => time() * 1000,
                    ]
                ];

                //聊天记录数组
                $param = [
                    'fromid' => $uid,
                    'toid' => $to_id,
                    'timeline' => time(),
                    'content' => htmlspecialchars($message_data['data']['mine']['content']),
                    'needsend' => 0
                ];
                switch ($type) {
                    case 'group':
                        return Gateway::sendToGroup($to_id, json_encode($chat_message), $client_id);
                    case 'friend':
                        $param['type'] = 'friend';
                        $i = Gateway::getClientIdByUid($to_id);
                        if (empty($i)) {
                            $param['needsend'] = 1;  //用户不在线,标记此消息推送
                        }
//                        var_dump($param);
                        $db->insert('chatmsg')->cols($param)->query();
                        return Gateway::sendToUid($to_id, json_encode($chat_message));
                }
                return;
                break;
        }
        // 向所有人发送

//        Gateway::sendToAll("$client_id said $message\r\n");
    }


    /**
     * 当用户断开连接时触发
     * @param int $client_id 连接id
     * @throws Exception
     */
    public static function onClose($client_id)
    {
//        $db = Db::instance('db1');
//        $db->query("update user set status = 0 where user_id = ".$_SESSION['UID'] );
//        GateWay::sendToAll("$client_id logout\r\n");
    }

}
