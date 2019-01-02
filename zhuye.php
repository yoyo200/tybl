<?php 
session_start(); //检测是否登录，若没登录则转向登录界面 
if(!isset($_SESSION['userid'])){
    header("Location:denglu.html"); 
    exit();
} 
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="renderer" content="webkit|ie-comp|ie-stand">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1.0,maximum-scale=1.0,user-scalable=no"/>
        <meta http-equiv="Cache=Control" content="no-siteapp" />
        <title>
            天涯 比邻
        </title>
        <meta name="keywords" content="">
        <meta name="description" content="">
        <!--[if lt IE 9]>
            <script type="text/javascript" src="lib/html5shiv.js">
            </script>
            <script type="text/javascript" src="lib/respond.min.js">
            </script>
        <![endif]-->
        <!--[if lt IE 9]>
            <link href="static/h-ui/css/H-ui.ie.css" rel="stylesheet" type="text/css"
            />
        <![endif]-->
        <!--[if IE 6]>
            <script type="text/javascript" src="lib/DD_belatedPNG_0.0.8a-min.js">
            </script>
            <script>
                DD_belatedPNG.fix('*');
            </script>
        <![endif]-->
        <link rel="stylesheet" type="text/css" href="static/h-ui/css/H-ui.min.css"/>
        <link rel="stylesheet" type="text/css" href="lib/Hui-iconfont/1.0.8/iconfont.min.css"/>
        <style>
            .qipao{display:inline-block; margin-left:50px; margin-top:0px; border:1px solid #ccffff; border-radius:10px; background:rgba(204,255,204,0.3);}
            .shijian{margin-left:50px; margin-top:-45px;}
        </style>
    </head>
    
    <body ontouchstart>
        <!--内容页面开始-->
        <!--最外层内容框-->
        <div class="container ui-sortable">
            <nav>
                <div class="container">
                    <i class="Hui-iconfont">
                        &#xe67f;
                    </i>
                    <a href="huanying.html" class="c-primary">
                        欢迎
                    </a>
                    <span class="c-gray en">
                        &gt;
                    </span>
                    <span class="c-gray">
                        首页
                    </span>
                </div>
            </nav>
            <div class="Huialert Huialert-info">
                 <i class="Hui-iconfont">&#xe6a6;</i>
                 请勿刷新页面以防信息丢失
            </div>
            <!--外层内容框-->
            <div class="panel panel-default mt-20">
                <!--内容头部框-->
                <div class="panel-header">
                    <i class="avatar size-S radius">
                        <?php 
                         if(isset($_SESSION['userid'])){ 
                            //包含数据库连接文件 
                            include('conn.php');
                            $userid = $_SESSION['userid'];
                            $username = $_SESSION['username'];
                            echo '<img alt="" id="myming" uid="'.$userid.'" name="'.$username.'" src="static/h-ui/images/ucnter/avatar-default.jpg">';
                            $conn->close();
                         }
                        ?>
                    </i> 
                    <span id="haoyou" onclick="qiehuan(this)">&nbsp;&nbsp;私聊&nbsp;&nbsp;</span>
                    <span id="gongping" onclick="qiehuan(this)">公屏&nbsp;&nbsp;</span>
                    <span id="yonghu" onclick="qiehuan(this)">用户</span>
                </div>
                
                <!--用户列表-->
                <div id="userslist" class="tabBar f-r" style="width:100px; height:700px; overflow:auto; display:none;">
                    <span style="min-width:70px;max-width:150px;">系统</span>
                    <?php
                         if(isset($_SESSION['userid'])){
                             include('conn.php');
                             $user_query = mysqli_query($conn,"select username from user");
                             while($row = mysqli_fetch_array($user_query)){
                                 echo '<span style="min-width:70px;max-width:150px;">'.$row['username'].'</span>';
                             }
                             $conn->close();
                         }
                    ?>
                </div>
                <!--用户列表结束-->
                <!--公屏聊天开始-->
                <div id="gongpingchar">
                <div class="panel-body" style="height:700px;; overflow-y:auto;">
                <div class="panel-body" id="allliao" style="height:500px; overflow-y:auto;">
                       <div style="display:inline-block; width:100%;">
                           <div style="text-align:center;">
                              <p style="color:#999">往上已无更多内容</p>
                              <hr style="margin-bottom:20px;">
                           </div>
                       </div>
                       <ul class="commentList">
                           <li class="item cl"> 
                               <ul>
                                   <li class="dropDown dropDown_hover">
                                       <a id="aming" href="#">
                                           <i class="avatar size-L radius">
                                              <img alt="" id="imgming" onclick="showittip(this)" src="static/h-ui/images/ucnter/avatar-default.jpg">
                                           </i>
                                       </a>
                                       <ul class="dropDown-menu menu radius box-shadow" style="top:-33px">
                                           <li>
                                                <a href="#" target="_blank">私聊</a>
                                          </li>
                                      </ul>
                                   </li>
                               </ul>
                               <div>
                                   <header style="background:white;">
                                       <div class="comment-meta shijian">
                                           <a class="comment-author" href="#">yoyo</a>
                                           <time title="2014年8月31日 下午3:20" datetime="2014-08-31T03:54:20">2014-8-31 15:20</time>
                                       </div>
                                   </header>
                                   <div class="comment-body qipao">
                                       <p>
                                           <a href="#">@某人</a><br/>
                                           试例<br/>
                                       </p>
                                   </div>
                               </div>
                           </li>
                       </ul>
                   </div>
                   <form action="" method="post" class="form form-horizontal responsive" id="allform">
                       <div class="row cl">
                           <div class="formControls col-xs-8" style="width:100%;">
                               <textarea class="textarea" name="alltext" id="alltext" placeholder="说点什么...最少输入10个字符"></textarea>
                           </div>
                       </div>
                       <input class="btn btn-block btn-primary radius" type="submit" id="allfasong" value="发送" />
                   </form>
                </div>
                </div>
                <!--公屏聊天结束-->
                
                <div style="clear:both"></div>
                
                <!--好友功能最外层框-->
                
                <div id="friendchar" class="HuiTab" style="display:none;">
                    <div id="openfriendlist" onclick="qiehuan(this)" style="display:none;">打开列表</div>
                    <div id="friendlist" class="tabBar f-l" style="width:200px; max-height:700px;">
                        <div id="addspan" style="max-height:700px; overflow-y:auto;">
                        <div id="offfriendlist" onclick="qiehuan(this)">点击关闭</div>
                        <span style="width:170px">
                            系统消息
                        </span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                        <span name="tt" onclick="setformname(this)" style="width:170px;display:none;"></span>
                       
                        </div>
                    </div>
                    <div class="panel-body" style="height:700px;; overflow-y:auto;">
                    <div class="panel-body" style="height:500px;; overflow-y:auto;">
                        <div id="addtabCon">
                             <div class="tabCon">
                                 系统
                             </div>
                        </div>
                    </div>
                         <form action="" method="post" class="form form-horizontal responsive" id="friendform">
                                <div class="row cl">
                                    <div class="formControls col-xs-8" style="width:100%;">
                                        <textarea class="textarea" name="" id="friendtext" placeholder="说点什么...最少输入10个字符"></textarea>
                                    </div>
                                </div>
                                <input class="btn btn-block btn-primary radius" type="submit" id="friendfasong" value="发送" />
                        </form>
                </div>
                </div>
                </div>
                <div style="clear:both"></div>
                <!--好友功能结束-->
            </div>
        </div>
        
        <script type="text/javascript" src="lib/jquery/1.9.1/jquery.min.js">
        </script>
        <script type="text/javascript" src="lib/jquery-ui/1.9.1/jquery-ui.min.js">
        </script>
        <script type="text/javascript" src="static/h-ui/js/H-ui.js">
        </script>
        <script type="text/javascript" src="lib/jquery.SuperSlide/2.1.1/jquery.SuperSlide.min.js">
        </script>
        <script type="text/javascript" src="lib/jquery.validation/1.14.0/jquery.validate.min.js">
        </script>
        <script type="text/javascript" src="lib/jquery.validation/1.14.0/validate-methods.js">
        </script>
        <script type="text/javascript" src="lib/jquery.validation/1.14.0/messages_zh.min.js">
        </script>
        <?php
        if(isset($_SESSION['userid'])){
        echo '<script type="text/javascript" src="socket.js"></script>';
        }
        ?>
    </body>
</html>