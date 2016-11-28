
var app = angular.module('swoole',['ngRoute']);

//angular 路由
app.config(function ($routeProvider) {

    $routeProvider
        .when('/',{
            templateUrl:'common/friends.html',
            controller :'friendsController'
        })
        .when('/organization',{
            templateUrl:'common/organization.html',
            controller :'organizationController'
        })
        .when('/recent',{
            templateUrl:'common/recent.html',
            controller :'recentController'
        })
        .otherwise({
            redirectTo:'/'
        });

});

app.controller('friendsController',function ($scope, $location) {

    changeClassByUrl($location.$$url);

});

app.controller('organizationController',function ($scope, $location) {

    $scope.selfUrl = selfUrl;
    changeClassByUrl($location.$$url);
    $scope.Msg = '';
    $scope.sendMsg = function () {
        if ($scope.Msg == '')
        {
            return false;
        }
        else
        {
            WS.send(getMsg($scope.Msg,2));
            //对话框置空
            $scope.Msg = '';
        }
    };
    //消息内容列表
    $scope.msgLists    = MsgLists;
    //在线用户信息列表
    $scope.onlineLists = OnlineLists;

});

app.controller('recentController',function ($scope, $location) {

    changeClassByUrl($location.$$url);
});

$(function(){

    $('.btn-tag').click(function(){
        $(this).addClass('btn-primary').parent('.btn-group').siblings('.btn-group').children('a').removeClass('btn-primary').addClass('btn-default');
    });

});

//tab样式动态改变
function changeClassByUrl($tag) {

    var objs = $('.btn-tag');
    $.each(objs, function (i, item) {
        var Ctag = $(item).attr('href');
        if ('#'+$tag == Ctag){
            $(item).addClass('btn-primary').removeClass('btn-default');
        } else {
            $(item).addClass('btn-default').removeClass('btn-primary');
        }
    })
}


$(document).keypress(function(e){

    if (e.which == '10')
    {
        var msg = $('.inputMsg').val();
        if (msg == '') return false;
        //发送消息
        WS.send(getMsg(msg,2))
        $('.inputMsg').val('')
    
    }

});


function connect() {
    var socketUrl = "ws://192.168.13.191:9501?name="+selfName+'&token='+selfToken+'&type=2';
    console.log(socketUrl)
    WS = new WebSocket(socketUrl);

    WS.onopen = function (event) {
        console.log('connected');
    };

    WS.onmessage = function (event) {
        //服务端推过来的数据
        var msg = JSON.parse(event.data);
        if (msg.hasOwnProperty('errorMsg'))
        {
            console.log(msg.errorMsg);
            //主动关闭
            WS.close();
        }else {

            if (msg.hasOwnProperty('success'))
            {
                //消息内容列表
                getMsgLists(msg.success)
                //在线用户列表
                getOnlineLists(msg.success);
            }

        }

    };

    WS.onclose = function (event) {
        console.log('关闭连接');
    };

    WS.onerror = function () {
        console.log('出现error')
    }
}

//1单 2群聊
function getMsg(text,type) {
    var sendText = {
        "text":text,
        "user":selfName,
        "avatar":selfUrl,
        "type":type
    }

   return JSON.stringify(sendText);
}

function RenderToGroup(obj) {

    var html = '';
    $.each(obj,function (i, item) {
        html += '<li class="list-group-item groups">';
        html += '<img src="'+item.avatar+'" style="width:20px" class="img-circle">';
        html += '<span style="font-size: 14px">&nbsp;'+item.name+'</span>';
        html += '<span style="font-size: 10px">&nbsp;'+item.time+':&nbsp;</span>';
        html += '<span>'+item.content+'</span>';
        html += '</li>';
    });

    return html;
}

function addToGroup(obj) {
    var html = '';
    html += '<li class="list-group-item groups">';
    html += '<img src="'+obj.avatar+'" style="width:20px" class="img-circle">';
    html += '<span style="font-size: 14px"> '+obj.name+'</span>';
    html += '<span style="font-size: 10px"> '+obj.time+':&nbsp;</span>';
    html += '<span>'+obj.content+'</span>';
    html += '</li>';
    return html;
}


//服务器推过来的信息
function getMsgLists(Msg)
{
    //1.消息的内容
    if (Msg.hasOwnProperty('content'))
    {
        //推给刚刚进入群组的用户
        if (MsgLists.length == 0)
        {
            MsgLists = Msg.content;
        }
        //推给已经在线的用户
        else
        {
            // MsgLists.push(Msg.content);
            var addHtml = addToGroup(Msg.content);
            $('#groups').append(addHtml);
        }

        //让对话框自动滚到底部
        lct = document.getElementById('manyFlow');
        if (lct)
        {
            lct.scrollTop=Math.max(0,lct.scrollHeight-lct.offsetHeight);
        }
        
    }

}

//服务器推过来的在线用户信息
function getOnlineLists(Msg)
{
    //2.在线用户信息列表
    if (Msg.hasOwnProperty('onLine_users'))
    {
        //推给刚刚进入群组的用户
        if (OnlineLists.length == 0)
        {
            OnlineLists = Msg.onLine_users
        }
        //推给已经在线的用户
        else
        {
            // OnlineLists.push(Msg.onLine_users);
            var addHtml = addToOnlineLists(Msg.onLine_users)
            $('.onlineLists').append(addHtml);
        }

    }
}

//追加新用户到在线列表
function addToOnlineLists(obj)
{
    var html = '';
    html += '<li class="list-group-item avatar-list">';
    html += '<img src="'+obj.avatar+'" class="img-circle" style="width:20px"> ';
    html += '<p>'+obj.name+'</p>';
    html += '</li>';
    return html;
}