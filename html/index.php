<?php
    session_start();
    if (empty($_SESSION['user'])){
        unset($user);
        header('Location: login.php');
        exit();
    }

//$db = new \MongoClient('mongodb://192.168.0.116:12345');
//$mongo = $db->selectDB('swoole');
//$a = $mongo->discuss_group_record->find();
//foreach ($a as $k=>$v){
//    var_dump($v['user']);
//}

    $url = 'http://s.test.net/chat/html/index.php';
    include "common/header.html";
?>
<body ng-app="swoole">
    <div class='contanier'>
        <nav class="navbar navbar-default" role="navigation">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="#">
                        <img alt="Brand" class='img-responsive' src="dist/imgs/swoole.png">
                    </a>
                </div>
            </div>
        </nav>
        <div class='row'>
            <div class="col-md-offset-4 col-md-4 main">
                
                <div class='row'>
                    
                    <div class='col-md-12'>
                        <h4><?php echo $_SESSION['user']['name'];?></h4>
                    </div>
                    <div class='col-md-12'>
                        <div class="btn-group btn-group-justified">

                            <div class="btn-group">
                                <a type="button" href="#/" class="btn btn-primary btn-tag">
                                    <i class='glyphicon glyphicon-user'></i>
                                </a>
                            </div>

                            <div class="btn-group">
                                <a type="button" href="#/organization"  class="btn btn-default btn-tag">
                                    <i class='glyphicon glyphicon-send'></i>
                                </a>
                            </div>

                            <div class="btn-group">
                                <a type="button" href="#/recent" class="btn btn-default btn-tag">
                                    <i class="glyphicon glyphicon-comment"></i>
                                </a>
                            </div>
                
                        </div>
                    </div>

                    <div class="ng-view"></div>
                    
                </div>
            </div>
        </div>
    </div>

</body>
<script type="text/javascript">
    var selfUrl   = "<?php echo $_SESSION['user']['avatar']?>";
    var selfName  = "<?php echo $_SESSION['user']['name']?>";
    var selfToken = "<?php echo $_SESSION['user']['token']?>";
    var WS;//存放WebSocket实例
    var MsgLists=[];
    connect();
</script>
</html>