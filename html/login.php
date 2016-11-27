<?php
session_start();
if (!empty($_SESSION['user'])){
    header('Location: index.php');
    exit();
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Document</title>
    <link rel="stylesheet" href="dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="dist/css/main.css">
    <script type="text/javascript" src='dist/js/jquery.min.js'></script>
    <script type="text/javascript" src='dist/js/bootstrap.min.js'></script>
</head>
<body>

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
        <div class="col-md-offset-4 col-md-4">

            <div class='row'>

                <form class="form-horizontal" role="form" method="post" action="handle.php?type=login" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-3 control-label">Name (必填) </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control" name="name"  placeholder="Name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-3 control-label">avatar (选填)</label>
                        <div class="col-sm-9">
                            <input type="file" name="avatar">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary form-control">Login</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>


</body>
</html>