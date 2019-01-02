<?php
include 'websocket.class.php';
$config=array(
  'address'=>'127.0.0.1',
  'port'=>'8000',
  'event'=>'WSevent',//回调函数的函数名
  'log'=>true,
);

$websocket = new websocket($config);
$websocket->run();

function WSevent($type,$event){
  global $websocket;
    if('in'==$type){
      $arr = array('type'=>'getname');
      $json = json_encode($arr);
      $websocket->idwrite($event['id'],$json);
    }elseif('out'==$type){
      $websocket->log('客户退出id:'.$event['id']);
    }elseif('msg'==$type){
      //$websocket->log($event['id'].'message:'.$event['msg']['msg']);
      selectsend($event['sign'],$event['msg']);
    }
}

function selectsend($sign,$msg){
    global $websocket;
    if($msg['type'] == 'ping'){
        $arr = array('type'=>'ping');
        $json = json_encode($arr);
        $websocket->signwrite($sign,$json);
    }
    elseif($msg['type'] == 'all'){
        $arr1 = array('type'=>'my','msg'=>$msg['msg']);
        $arr2 = array('type'=>'other','msg'=>$msg['msg'],'name'=>$msg['name']);
        //echo $msg['name']; 
        $json1 = json_encode($arr1);
        $json2 = json_encode($arr2);
        $websocket->signwrite($sign,$json1);
        $websocket->allwrite($sign,$json2);
        //echo '已发送';
    }
    elseif($msg['type'] == 'friend'){
        $iid = $websocket->searchbyname($msg['fname']);
        echo $iid;
        //$id = 1;
        $json = json_encode($msg);
        $websocket->idwrite($iid,$json);
        //$websocket->signwrite($fsign,$json);
        $websocket->signwrite($sign,$json);
    }
    elseif($msg['type'] == 'name'){
        $id = $websocket->search($sign);
        $a = false;
        $a = $websocket->setname($sign,$msg['name'],$msg['uid'],$id);
        if($a){
            $arr = array('type'=>'ok');
            $json = json_encode($arr);
            $websocket->signwrite($sign,$json);
            //$websocket->users[$id]['name'] = 'ghgh';
            //echo $websocket->users[$id]['name'];
            $websocket->log('客户进入id：'.$id.'   '.'名字为：'.$msg['name']);
        } 
        else{
            $websocket->log('客户退出id：'.$id);
            $websocket->close($sign);
        }
    }
    //elseif($msg['type'] == 'seeinformation')
      //  $websocket->informationwrite($sign,$msg);
}
?>
