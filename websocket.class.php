<?php
/*
创建类websocket($config);
$config结构:
$config=array(
  'address'=>'127.0.0.1',//绑定地址
  'port'=>'8000',//绑定端口
  'event'=>'WSevent',//回调函数的函数名
  'log'=>true,//命令行显示记录
);

回调函数返回数据格式
function WSevent($type,$event)
$type字符串 事件类型有以下三种
in  客户端进入
out 客户端断开
msg 客户端消息到达
均为小写

$event 数组
$event['k']内置用户列表的userid;
$event['sign']客户标示
$event['msg']收到的消息 $type='msg'时才有该信息

方法:
run()运行
search(标示)遍历取得该标示的id
close(标示)断开连接
write(标示,信息)推送信息
idwrite(id,信息)推送信息

属性:
$users 客户列表
 
结构:
$users=array(
[用户id]=>array('socket'=>[标示],'hand'=[是否握手-布尔值],'name'=[用户名字]),
[用户id]=>arr.....
)
*/

class websocket{
    public $log;
    public $event;
    public $signets;
    public $users;  
    public $master; 

    public function __construct($config){
        if (substr(php_sapi_name(), 0, 3) !== 'cli') {
            die("请通过命令行模式运行!");
        }
        error_reporting(E_ALL);
        set_time_limit(0);
        ob_implicit_flush();
        $this->event = $config['event'];
        $this->log = $config['log'];
        $this->master=$this->WebSocket($config['address'], $config['port']);
        $this->sockets=array('s'=>$this->master);
    }

    function WebSocket($address,$port){
        $server = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($server, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($server, $address, $port);
        socket_listen($server);
        $this->log('开始监听: '.$address.' : '.$port);
        return $server;
    }
 
  function run(){
    while(true){
      $changes=$this->sockets;
      @socket_select($changes,$write=NULL,$except=NULL,NULL);
      foreach($changes as $sign){
        if($sign==$this->master){
          $client=socket_accept($this->master);
          $this->sockets[]=$client;
          $user = array(
            'socket'=>$client,
            'hand'=>false,
            'name'=>'',
          );
          $this->users[] = $user;
          $k=$this->search($client);
        }else{
          $len=socket_recv($sign,$buffer,2048,0);
          $k=$this->search($sign);
          $user=$this->users[$k];
          if($len<7){
            $this->close($sign);
            $eventreturn = array('id'=>$k,'sign'=>$sign);
            $this->eventoutput('out',$eventreturn);
            continue;
          }
          if(!$this->users[$k]['hand']){//没有握手进行握手
            $this->handshake($k,$buffer);
            $eventreturn = array('id'=>$k,'sign'=>$sign);
            $this->eventoutput('in',$eventreturn);
          }else{
            $buffer = $this->uncode($buffer);
            $buf = json_decode($buffer,true);
           // $this->log($buf['type'].$buf['msg']);
            $eventreturn = array('id'=>$k,'sign'=>$sign,'msg'=>$buf);
            $this->eventoutput('msg',$eventreturn);
          }
        }
      }
    }
  }

  function close($sign){//通过标示断开连接
    $k=array_search($sign, $this->sockets);
    socket_close($sign);
    unset($this->sockets[$k]);
    unset($this->users[$k]);
  }

  function handshake($k,$buffer){
    $buf  = substr($buffer,strpos($buffer,'Sec-WebSocket-Key:')+18);
    $key  = trim(substr($buf,0,strpos($buf,"\r\n")));
    $new_key = base64_encode(sha1($key."258EAFA5-E914-47DA-95CA-C5AB0DC85B11",true));
    $new_message = "HTTP/1.1 101 Switching Protocols\r\n";
    $new_message .= "Upgrade: websocket\r\n";
    $new_message .= "Sec-WebSocket-Version: 13\r\n";
    $new_message .= "Connection: Upgrade\r\n";
    $new_message .= "Sec-WebSocket-Accept: " . $new_key . "\r\n\r\n";
    socket_write($this->users[$k]['socket'],$new_message,strlen($new_message));
    $this->users[$k]['hand']=true;
    return true;
  }

    //解码数据
    function uncode($text)
    {
         $length = ord($text[1]) & 127;
         if ($length == 126) {
              $masks = substr($text, 4, 4);
              $data = substr($text, 8);
        } elseif ($length == 127) {
              $masks = substr($text, 10, 4);
              $data = substr($text, 14);
        } else {
              $masks = substr($text, 2, 4);
              $data = substr($text, 6);
        }
        $text = "";
        for ($i = 0; $i < strlen($data); ++$i) {
             $text .= $data[$i] ^ $masks[$i % 4];
        }
         return $text;
    }

    //编码数据
    function code($text)
    {
         $b1 = 0x80 | (0x1 & 0x0f);
         $length = strlen($text);
         if ($length <= 125)
             $header = pack('CC', $b1, $length);
         elseif ($length > 125 && $length < 65536)
             $header = pack('CCn', $b1, 126, $length);
         elseif ($length >= 65536)
             $header = pack('CCNN', $b1, 127, $length);
         return $header . $text;
    }

    function search($sign){//通过标示遍历获取id
      foreach ($this->users as $k=>$v){
        if($sign==$v['socket'])
        return $k;
      }
      return false;
    }
  
    function searchbyname($name){//通过名字找id
        foreach($this->users as $k=>$v){
            if($name == $v['name'])
            {
                return $k;
            }
        }
        return false;
    }
    
    function setname($sign,$name,$uid,$id){
        include 'conn.php';
        $nuid = (int)$uid;
        $check_query = mysqli_query($conn,"select * from user where uid='$nuid' and username='$name' limit 1");
        if(mysqli_fetch_array($check_query)){
              $this->users[$id]['name'] = $name;
              $conn->close();
              return true;
        }else{
             $conn->close();
             return false;
        }
    }
    
    function idwrite($id,$t){//通过id推送信息
      if(!$this->users[$id]['socket']){
          return false;
      }//没有这个标示
      $t=$this->code($t);
      return socket_write($this->users[$id]['socket'],$t,strlen($t));
    }
    
    function allwrite($sign,$t){//广播消息
        $t= $this->code($t);
        foreach($this->users as $v){
            if($sign != $v['socket'])
                socket_write($v['socket'],$t,strlen($t));
        }
    }
    
    function signwrite($sign,$t){//通过标示推送信息
      $t=$this->code($t);
      return socket_write($sign,$t,strlen($t));
    }

    function eventoutput($type,$event){//事件回调
      call_user_func($this->event,$type,$event);
    }

    function log($t){//控制台输出
      if($this->log){
        $t=$t."\n";
       // fwrite(STDOUT, iconv('utf-8','gbk//IGNORE',$t));
        echo $t;
      }
    }
}
/*php中数组和json的转换：
 * $arr = json_decode($str,ture); 将json转化为数组
 * $json = json_encode($data); 将数组转换为json格式
 * 
 * js中数组和json的转换:
 * 数组转字符串
 *          var arr = [1,2,3,4,'巴德','merge'];
            var str = arr.join(',');
            console.log(str); // 1,2,3,4,巴德,merge
 * 字符串转数组
 *          var str = '1,2,3,4,巴德,merge';
            var arr = str.split(',');
            console.log(arr);     // ["1", "2", "3", "4", "巴德", "merge"]   数组
            console.log(arr[4]);  // 巴德
 * 字符串转数组，数组转数组格式化，数组格式化转数组
 *          var str = '1,2,3,4,巴德,merge';
            var arr = str.split(',');
            var strify = JSON.stringify(arr);
            console.log(arr);       // ["1", "2", "3", "4", "巴德", "merge"]   数组
            console.log(arr[4]);    // 巴德
            console.log(strify);    // ["1", "2", "3", "4", "巴德", "merge"]   字符串

            var arrParse = JSON.parse(strify);
            console.log(arrParse);  // ["1", "2", "3", "4", "巴德", "merge"]   数组
 */