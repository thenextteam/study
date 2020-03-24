let ws;

if (uid == null || uid == undefined || uid == '') {
    console.log("不渲染聊天组件");

} else {
    console.log("渲染聊天组件");
    layui.use('layim', function (layim) {
        layim.config({
            title: "我的IM",
            min: true,
            copyright: true,
            chatLog: '/thinkphp/public/static/admin/dist/css/modules/layim/html/chatlog.html',
            find: '/thinkphp/public/static/admin/dist/css/modules/layim/html/find.html',
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
                    console.log(msg_json);
                    ws.send(msg_json);
                    break;
                case 'bind':
                    console.log(data.data);
                    break;
                case 'chatMessage':
                    console.log('收到消息');
                    console.log(data.data);
                    layim.getMessage(data.data);
                    break;
                case 'logMessage':
                    console.log('收到消息');
                    setTimeout(function () {
                        layim.getMessage(data.data)
                    }, 1000);
                // 当mvc框架调用GatewayClient发消息时直接alert出来
                default :

            }
        };


        layim.on('ready', function (res) {
            layim.on('sendMessage', function (res) {
                // console.log(res);
                // var mine = JSON.stringify(res.mine);
                // var to = JSON.stringify(res.to);
                // var login_data = '{"type":"chatMessage","data":'+res+'}';
                console.log(JSON.stringify({
                    type: 'chatMessage' //随便定义，用于在服务端区分消息类型
                    , data: res
                }));
                ws.send(JSON.stringify({
                    type: 'chatMessage'
                    , data: res
                }));
                // console.log();
            });
        });

        layim.on('online', function (status) {
            console.log(status); //获得online或者hide
            $.ajax({
                type: 'post',
                url: changestatus,
                data: {mystatus: status},
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


    function Loging() {
        // $.post(
        //     "{:url('Home/setSion')}",
        //     {name: uname.value},
        //     function (data) {
        //     },
        //     'json'
        // );

        $.ajax({
            type: 'post',
            url: "{:url('Home/setSion')}",
            data: {name: uname.value},
            dataType: "text",
            success: function (da) {

            }
        })
    }
}
