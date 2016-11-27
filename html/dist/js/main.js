
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
    $scope.msgLists = MsgLists;

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
    var socketUrl = "ws://192.168.0.116:9501?name="+selfName+'&token='+selfToken+'&type=2';
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
                if (MsgLists.length == 0)
                {
                    MsgLists = msg.success;
                    var html = RenderToGroup(msg.success);
                    $('#groups').html();

                } else {

                    MsgLists.push(msg.success);
                    var addHtml = addToGroup(msg.success);
                    console.log(msg.success)
                    $('.groups').append(addHtml)

                }
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
    html += '<span style="font-size: 14px">&nbsp;'+obj.name+'</span>';
    html += '<span style="font-size: 10px">&nbsp;'+obj.time+':&nbsp;</span>';
    html += '<span>'+obj.content+'</span>';
    html += '</li>';
    return html;
}