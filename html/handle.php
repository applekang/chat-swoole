<?php

use Libs\Classes\Message;
session_start();
spl_autoload_register('loadClass');

$type = $_GET['type'];

switch ($type) {
    case 'login':
        login();
        break;
    default:
        exit('unkown!');
        break;
}

//处理登陆
function login()
{
    $name = $_POST['name'];

    if (empty($name)) {
        exit('输入用户名');
    }

    $user = Message::getUser($name);

    $token    = uniqid();
    $username = $name;
    if ($user)
    {
        $avatar   =  isset($user['avatar'])?'dist/imgs/avatar/'.$user['avatar']:'dist/imgs/avatar/default.png';
        $result = Message::updateUser(['token'=>$token],['name'=>$name]);
    }

    else
    {
        //有上传头像
        if ($_FILES['avatar']['size'] > 0)
        {
            if ($_FILES['avatar']['error'] > 0)
            {
                exit($_FILES['avatar']['error']);
            }

            $filename = uniqid().$_FILES['avatar']['name'];
            move_uploaded_file($_FILES['avatar']['tmp_name'],'dist/imgs/avatar/'.$filename);
        }

        $one = [
            'name'  => $username,
            'token' => $token,//token用作验证是否通过客户端是否是有效的socket连接
        ];

        $avatar = 'dist/imgs/avatar/default.png';
        if (isset($filename)) {
            $one['avatar'] = $filename;
            $avatar = 'dist/imgs/avatar/'.$filename;
        }

        $result = Message::addUser($one);

        if ($result['err'])
        {
            exit($result['errmsg']);
        }

    }

    $_SESSION['user'] = [
        'name'   => $username,
        'avatar' => $avatar,
        'token'  => $token,
    ];

    //每次登录更新token
    header('Location: index.php');
    exit();


}


function loadClass($name)
{

    $class_path = str_replace('\\', DIRECTORY_SEPARATOR, $name);
    $file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.$class_path.'.php';

    if (file_exists($file))
    {
        require_once $file;
        if (class_exists($name, false)){
            return true;
        }

        return false;
    }
}

