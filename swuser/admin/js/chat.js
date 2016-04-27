var ws = {};
var client_id = 0;

$(document).ready(function () {
    //使用原生WebSocket
    if (window.WebSocket || window.MozWebSocket)
    {
        ws = new WebSocket(webim.server);
    }
    listenEvent();
});

function listenEvent() {

    var muser = document.getElementById('muser').value;
    var mpasswd = document.getElementById('mpasswd').value;
    /**
     * 连接建立时触发
     */
    ws.onopen = function (e) {
         if (muser == undefined ||mpasswd == undefined) {
            alert('必须的输入一个名称和密码');
            ws.close();
            return false;
        }
        //连接成功
        console.log("connect swuser server success.");
        //发送登录信息
        msg = new Object();
        msg.cmd = 'login';
        msg.name =muser;
        msg.passwd = mpasswd;
        ws.send($.toJSON(msg));
    };

    //有消息到来时触发
    ws.onmessage = function (e) {
        var message = $.evalJSON(e.data);
        var cmd = message.cmd;
        if(cmd=='input' || cmd=='error'){
            alert( e.data );
            ws.close();
            location.href = 'login.html';
        }

        if (cmd == 'login')
        {
            //关闭连接
            alert( "你的帐号在别的地方登录");
            ws.close();
            location.href = 'login.html';
        }

        if (cmd == 'success')
        {
            //关闭连接
            alert( "success");

        }

    };

    /**
     * 连接关闭事件
     */
    ws.onclose = function (e) {
         if (confirm("聊天服务器已关闭")) {
            alert(e.data);
             ws.close();
             location.href = 'login.html';
        //    location.href = 'index.html';
        }
    };

    /**
     * 异常事件
     */
    ws.onerror = function (e) {
        alert("异常:" + e.data);
        console.log("onerror");
        ws.close();
        location.href = 'login.html';

    };
}

