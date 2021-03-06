let ws;

if (uid == null || uid == undefined || uid == '') {
    console.log("不渲染聊天组件");

} else {
    console.log("渲染聊天组件");
    layui.use(['layim'], function (layim) {
        layim.config({
            title: "我的IM",
            min: true,
            copyright: true,
            chatLog: '/thinkphp/public/chat/chatlog.html?' + getchatlog,
            // find: seachgroup,
            init: {
                url: getuser,
                type: "get",
                data: {}
            },
            members: {
                url: getmember //接口地址
                , type: 'get' //默认get
                , data: {} //额外参数
            },
            uploadImage: {
                url: uploadimg
            }, uploadFile: {
                url: uploadfile
            }, tool: [{
                alias: 'code' //工具别名
                , title: '代码' //工具名称
                , icon: '&#xe64e;' //工具图标，参考图标文档
            }],
        });
        //
        ws = new WebSocket("ws://127.0.0.1:8282");
        console.log(ws);
        //监听收到的消息
        ws.onmessage = function (res) {
            var data = eval("(" + res.data + ")");
            console.log(data);
            var type = data.type || '';
            switch (type) {
                // Events.php中返回的init类型的消息，将client_id发给后台进行uid绑定
                case 'init':
                    // 利用jquery发起ajax请求，将client_id发给后端进行uid绑定
                    var msg = {"type": "bind", "uid": uid};
                    msg_json = JSON.stringify(msg);
                    // console.log(msg_json);
                    ws.send(msg_json);
                    break;
                case 'bind':
                    // console.log(data.data);
                    break;
                case 'chatMessage':
                    // console.log('收到消息');
                    // console.log(data.data);
                    layim.getMessage(data.data);
                    break;
                case 'logMessage':
                    // console.log('收到消息');
                    setTimeout(function () {
                        layim.getMessage(data.data)
                    }, 1000);
                    break;
                // 在线
                case 'online':
                    layim.setFriendStatus(data.id, 'online');
                    break;
                // 下线
                case 'hide':
                    layim.setFriendStatus(data.id, 'hide');
                    break;
                // 当mvc框架调用GatewayClient发消息时直接alert出来
                default:
                    ;
            }
        };

        layim.on('tool(code)', function (insert, send, obj) { //事件中的tool为固定字符，而code则为过滤器，对应的是工具别名（alias）
            layer.prompt({
                title: '插入代码'
                , formType: 2
                , shade: 0
            }, function (text, index) {
                layer.close(index);
                insert('[pre class=layui-code]' + text + '[/pre]'); //将内容插入到编辑器，主要由insert完成
                send() //自动发送
            });
            // console.log(this); //获取当前工具的DOM对象
            // console.log(obj); //获得当前会话窗口的DOM对象、基础信息
        });

        layim.on('ready', function (res) {
//初始化菜单
            layui.layim.on('sendMessage', function (res) {
                layim.on('sendMessage', function (res) {

                    console.log(JSON.stringify({
                        type: 'chatMessage' //随便定义，用于在服务端区分消息类型
                        , data: res
                    }));
                    ws.send(JSON.stringify({
                        type: 'chatMessage'
                        , data: res
                    }));
                });
            });

            layim.on('online', function (status) {
                console.log(status); //获得online或者hide
                var change_data = '{"type":"online", "status":"' + status + '", "uid":"' + uid + '"}';
                ws.send(change_data);
                $.ajax({
                    type: 'post',
                    url: changestatus,
                    data: {mystatus: status},
                    dataType: "text",
                })
            });
            layim.on('sign', function (value) {
                // console.log(value); //获得新的签名

                $.ajax({
                    type: 'post',
                    url: changesign,
                    data: {sign: value},
                    dataType: "text",
                })
            });

        });


        var uname = document.getElementById("username");


        function sendM() {
            msg = document.getElementById("msg");
            var sendmsg = {'type': 'say', 'msg': msg.value}
            console.log(JSON.stringify(sendmsg));
            ws.send(JSON.stringify(sendmsg));
        }
    })
}