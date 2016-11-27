<?php

require_once(__DIR__.DIRECTORY_SEPARATOR.'Autoload.php');
use Libs\Classes\Storage;
use Libs\Classes\Message;


class Server{

    //服务端socket的IP
    private $ip   = '0.0.0.0';
    //服务端socket监听的端口
    private $port = '9501';
    //多进程
    private $base = SWOOLE_PROCESS;
    //TCP协议
    private $type = SWOOLE_SOCK_TCP;
    //swoole对象
    private $static = null;

    //通过此参数来调节poll线程的数量
    private $reactorNum= 2;
    //当设定的worker进程数小于reactor线程数时，会自动调低reactor线程的数量
    private $workerNum = 4;
    //后台守护进程运行
    private $daemonize = false; 
    //worker进程在处理完n次请求后结束运行。manager会重新创建一个worker进程。此选项用来防止worker进程内存溢出
    private $maxRequest= 100;
    //此参数将决定最多同时有多少个待accept的连接
    private $backlog   = 128;
    //log路径
    private $logFile   = '/tmp/swoole.log';

    public function __construct()
    {
        $this->static = new \swoole_websocket_server($this->ip, $this->port, $this->base, $this->type);
        $this->setParams();
        //连接事件
        $this->static->on('open', function ($server, $request){

            //验证用户的有效性
            if ($this->verifyUser($request))
            {
                $this->connection($server, $request);
            }

        });

        //接收事件
        $this->static->on('message', function($server, $frame){

            $this->receivion($server, $frame);
        });

        //连接关闭事件
        $this->static->on('close', function($server, $fd){

            $this->closion($server, $fd);
        });
    }

    //初始化设置参数
    private function setParams()
    {
        $params = [
            'reactor_num'=> $this->reactorNum,
            'worker_num' => $this->workerNum,
            'daemonize'  => $this->daemonize,
            'max_request'=> $this->maxRequest,
            'backlog'    => $this->backlog,
            'log_file'   => $this->logFile   
        ];

        $this->static->set($params);
    }

    //连接处理
    private function connection($server, $request)
    {
        echo '客户端'.$request->fd.'连接上来了'.PHP_EOL;
        //每个客户端fd存入
        Storage::addFd($request->fd);

        //根据request 中的type决定是群聊还是单聊
        
        if ($request->get['type'] == 2)
        {
            //消息的格式
            //['name'=>'sun','content'=>'123','time'=>'2016',avatar=>'1.png']
            $system_all = [
                'name'=>'系统',
                'content'=>$request->get['name'].'进入了PHP+Swoole聊天室',
                'time'=>date('Y-m-d H:i:s'),
                'avatar'=>'dist/imgs/man.png'
            ];
            var_dump($request->fd);
            //连接上来广播所有人,
            $this->broadcast(['success'=>$system_all],$request->fd);

            //自己,系统提示，并把所有的聊天记录发送给他
            $msg_all = Message::getAllMsgFromGroup();
            $system_one = [
                'name'=>'系统',
                'content'=>'欢迎来到PHP+Swoole聊天室',
                'time'=>date('Y-m-d H:i:s'),
                'avatar'=>'dist/imgs/man.png'
            ];
            array_push($msg_all, $system_one);

            $this->emitMsg($request->fd, ['success'=>$msg_all]);
        }


    }

    //接收事件处理
    private function receivion($server, $frame)
    {
        //接收事件之前先 有没有建立链接,即使open没有通过，此处也能接收到
        $currenFd = $frame->fd;
        if (Storage::existFd($currenFd))
        {
            //用户发过来的数据
            $data = json_decode($frame->data);
            $content = $data->text;
            $user    = $data->user;
            $avatar  = $data->avatar;
            $type    = $data->type;

            if ($type == 1)
            {
                //个人对个人


            } elseif ($type == 2){
                //群聊 所有的数据记录到mongodb中
                $oneMsg = [
                    'name'   => $data->user,
                    'avatar' => $data->avatar,
                    'content'=> $data->text,
                    'time'   => date('Y-m-d H:i:s')
                ];

                Message::addMsgtoGroup($oneMsg);
                //同时发送给所有人
                self::broadcast(['success'=>$oneMsg]);

            } else {

            }

        }

    }

    //连接关闭事件
    private function closion($server, $fd)
    {
        echo '客户端'.$fd.'断开连接'.PHP_EOL;
        //客户端主动exit,踢出fd
        Storage::moveFd($fd);
    }

    //发送数据给客户端
    private function emitMsg($fd, $message)
    {
        if (is_array($message))
        {
            $message = json_encode($message);
        }
        $this->static->push($fd, $message);
    }

    /**
     * 广播所有人
     */
    private function broadcast($message, $except=null)
    {
        //获取所有的fd
        $fds = Storage::listFd();
        if ($except)
        {
            $key = array_search($except, $fds);
            unset($fds[$key]);
        }

        foreach ($fds as $k=>$fd)
        {
            $this->emitMsg($fd, $message);
        }
    }

    //启动
    public function start()
    {
        $this->static->start();
    }

    //验证用户的有效性
    private function verifyUser($request)
    {

        $name  = $request->get['name'];
        $token = $request->get['token'];
        $msg   = '';
        if (empty($name)) {
            $msg = '用户名不能是空';
        }
        if (empty($token)) {
            $msg = 'token不能是空';
        }

        $user = Message::getUser($name);
        if (!$user) {
            $msg = '用户不存在';
        }
        else if($user['token'] != $token)
        {
            $msg = 'token不对';
        }

        if (!empty($msg))
        {
            $this->emitMsg($request->fd, ['errorMsg'=>$msg]);
            return false;
        }

        return true;
    }

}

$server = new \Server();
$server->start();
