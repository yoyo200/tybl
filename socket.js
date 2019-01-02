link();
function link(){
      var name = $("#myming").attr("name");
      var uid = $("#myming").attr("uid");
      //$("#uid").remove();
      //$("#mingzi").remove();//删除传值的标签
      //alert(name+uid);
      var url="ws://127.0.0.1:8000";
      ws=new WebSocket(url);
      ws.onopen=function(){
      heartCheck.start();//心跳检测开始
      log('连接成功');
  }
  ws.onmessage=function(msg){//如果获取到消息，心跳检测重置
         heartCheck.reset();      //拿到任何消息都说明当前连接是正常的
         heartCheck.start();
         var da = JSON.parse(msg.data);
         if('getname' == da['type']){
             var arr = {'type':'name','name':name,'uid':uid};
             var json = JSON.stringify(arr);
             ws.send(json);
         }
         else if('ok' == da['type']){}
         else 
         log(da);
         /*
         else{
         log('获得消息:'+da['type']+da['msg']);
         console.log(msg.data);
         }
         */
  }
  ws.onclose=function(){
      log('断开连接');
  }
}

function dis(){
  ws.close();
  ws=null;
}

//写入数据
function log(da){
    if('ping' == da['type']){
             console.log('ping');
             return;
         }
         else if('my' == da['type']){
             ming = $('#myming').attr("name");
             addmsgtogp(ming,da['msg']);
         }
         else if('other' == da['type']){
             ming = da['name'];
             addmsgtogp(ming,da['msg']);
         }
         else if('friend' == da['type']){
                 addmsgtofd(da['name'],da['fname'],da['msg']);
         }
    /*setTimeout(function(){
           str[0].scrollIntoViewIfNeeded();
     }, 300);*/
}

//获取时间
function getNowDate() {
        var date = new Date();
        var sign1 = "-";
        var sign2 = ":";
        var year = date.getFullYear(); // 年
        var month = date.getMonth() + 1; // 月
        var day  = date.getDate(); // 日
        var hour = date.getHours(); // 时
        var minutes = date.getMinutes(); // 分
        var seconds = date.getSeconds(); //秒
        var weekArr = ['星期一', '星期二', '星期三', '星期四', '星期五', '星期六', '星期天'];
        var week = weekArr[date.getDay()];
        // 给一位数数据前面加 “0”
        if (month >= 1 && month <= 9) {
            month = "0" + month;
        }
        if (day >= 0 && day <= 9) {
            day = "0" + day;
        }
        if (hour >= 0 && hour <= 9) {
            hour = "0" + hour;
        }
        if (minutes >= 0 && minutes <= 9) {
            minutes = "0" + minutes;
        }
        if (seconds >= 0 && seconds <= 9) {
            seconds = "0" + seconds;
        }
        var currentdate = year + sign1 + month + sign1 + day + " " + hour + sign2 + minutes + sign2 + seconds + " " + week;
        return currentdate;
}

function friendsend(fming){
   var arr = {'type':'friend','msg':$('#friendtext').val(),'name':$('#myming').attr("name"),'fname':fming};
   //alert(arr['fname']);
   var json = JSON.stringify(arr);
   ws.send(json);
}

function allsend(){
  var arr1 = {'type':'all','msg':$("#alltext").val(),'name':$('#myming').attr("name")};
  //var arr2 = {'type':'friend','msg':'你好朋友','fname':'you','name':'my'};
  var json1 = JSON.stringify(arr1);
  //var json2 = JSON.stringify(arr2);
  //var js = JSON.parse(json);
  //alert(js['msg']);
  ws.send(json1);
  //ws.send(json2);
}

// 监听窗口关闭事件，当窗口关闭时，主动去关闭websocket连接，防止连接还没断开就关闭窗口，server端会抛异常。
window.onbeforeunload = function() {
    ws.close();
}  

//心跳检测
var heartCheck = {
    timeout: 20000,        //20秒发一次心跳
    timeoutObj: null,
    serverTimeoutObj: null,
    reset: function(){
        clearTimeout(this.timeoutObj);
        clearTimeout(this.serverTimeoutObj);
        return this;
    },
    start: function(){
        var self = this;
        this.timeoutObj = setTimeout(function(){
            //这里发送一个心跳，后端收到后，返回一个心跳消息，
            //onmessage拿到返回的心跳就说明连接正常
            var arr = {'type':'ping','msg':'ping'};
            var json = JSON.stringify(arr);
            ws.send(json);
            console.log("ping!");
            self.serverTimeoutObj = setTimeout(function(){//如果超过一定时间还没重置，说明后端主动断开了
                ws.close();     //如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
            }, self.timeout);
        }, this.timeout);
    }
};

//点击私聊打开聊天框
            function opensiliao(it){
                var name = it.id.substring(2);
                if(name == $('#myming').attr("name")){
                    alert('不能和自己聊天');
                }
                else{
                     var spid = 'sp'+name;
                     //alert(spid);
                     var t=$("span[id='"+ spid +"']");//属性值为变量时要在前后加+并用''包起来
                     //alert(t[0].id);
                     if(t.length == 1){
                         $('#haoyou').click();
                         t[0].click();
                     }
                     else{
                         addfriendchar(name);
                         opensiliao(it);
                     }
                 }
            }
            
            //点击好友列表的好友设置输入框相应的name值
            function setformname(ming){
                var st = $(ming).attr("id");
                var sname = 'fo'+st.substring(2);
                $("#friendform").attr("name",sname);
                $(ming).css("background","#ddd");
                //alert($('#friendform').attr("name"));
            }
            
            //获取好友列表中第一个未定义的span标签
            function getonespan(){
                var t=$('span[name="tt"]');
                $(t[0]).attr("name","itt");
                //alert($(t[0]).attr("name"));
                return $(t[0]);
            }
            
            //添加私聊列表项及其聊天框
            function addfriendchar(name){
                var onespan = getonespan();
                //alert(onespan.attr("name"));
                var spanid = 'sp'+name;
                var tabid = 'ta'+name;
                onespan.attr("id",spanid);
                onespan.show();
                //alert(onespan.attr("id"));
                //var addspanstr = $(spanid);
                var addtabConstr = $('<div class="tabCon"><div class="panel-body" id="'
                                     +tabid
                                     +'" style="height:500px; overflow-y:auto;"><div style="display:inline-block; width:100%;">'
                                     +'<div style="text-align:center;"><p style="color:#999">'
                                     +'往上已无更多内容</p><hr style="margin-bottom:20px;"></div></div>'
                                     +'</div></div>'
                                     );
                onespan.append(spanid);
                $('#addtabCon').append(addtabConstr);
            }
            
            //添加用户进用户列表
            function adduserslist(name){
                var useid = 'us'+name;
                var user = $('span style="max-width:100px">'
                             +useid
                             +'</span>'
                             );
                $('#userslist').append(user);
            }
            
            // 向公屏写入消息
            function addmsgtogp(sming,smsg){
                if(sming == $('#myming').attr("name")){
                   var style = 'style="background:#ffccff"';
                }else{
                   var style = '';
                }
                $('#gongping').css("color","green");
                var ming = 'a1'+sming;
                var msg = smsg;
                var newDate = getNowDate();
                var str = $('<ul class="commentList"><li class="item cl"><ul>'
                            +'<li class="dropDown dropDown_hover"><a><i class="avatar size-L radius">'
                            +'<img alt="" src="static/h-ui/images/ucnter/avatar-default.jpg">'
                            +'</i></a><ul class="dropDown-menu menu radius box-shadow" style="top:-33px">'
                            +'<li><a "target="_blank"><p onclick="opensiliao(this)" id="'
                            +ming
                            +'">私聊</p></a></li></ul></li></ul><div>'
                            +'<header style="background:white;"><div class="comment-meta shijian">'
                            +'<a class="comment-author" href="#">'
                            +sming
                            +'</a><time>'
                            +newDate
                            +'</time></div></header><div class="comment-body qipao" '
                            +style
                            +'>'
                            +msg
                            +'</div></div></li></ul>'
                            );
                $('#allliao').append(str);
            }
            
            //向私聊框写入消息
            function addmsgtofd(mming,fming,smsg){
                $('#haoyou').css("color","green");
                if(mming == $('#myming').attr("name")){
                    //alert($('#myming').attr("name"));
                    var id = '#ta'+fming;
                    var sming = mming;
                    var style = 'style="background:#ffccff"';
                }
                else{
                    //alert(fming);
                    var slyle = '';
                    var id = '#ta'+mming;
                    var sming = mming;
                    var spid = 'sp'+mming;
                    var t=$("span[id='"+ spid +"']");
                    if(t.length != 1){
                        addfriendchar(mming);
                    }
                }
                var msg = smsg;
                var newDate = getNowDate();
                var str = $('<ul class="commentList"><li class="item cl"><ul>'
                            +'<li class="dropDown dropDown_hover"><a><i class="avatar size-L radius">'
                            +'<img alt="" src="static/h-ui/images/ucnter/avatar-default.jpg">'
                            +'</i></a><ul class="dropDown-menu menu radius box-shadow" style="top:-33px">'
                            +'<li><a "target="_blank">待定</a></li></ul></li></ul><div>'
                            +'<header style="background:white;"><div class="comment-meta shijian">'
                            +'<a class="comment-author" href="#">'
                            +sming
                            +'</a><time>'
                            +newDate
                            +'</time></div></header><div class="comment-body qipao" '
                            +style
                            +'>'
                            +msg
                            +'</div></div></li></ul>'
                            );
                $(id).append(str);
                sspid = '#'+spid;
                $(sspid).css("background","#00CC99");
            }
            
            //根据tpye的值切换不同的显隐
            function qiehuan(type){
                if(type.id == 'gongping'){
                    $('#gongping').css("color","#333");
                    $('#friendchar').hide();
                    $('#userslist').hide();
                    $('#gongpingchar').show();
                }else if(type.id == 'haoyou'){
                    $('#haoyou').css("color","#333");
                    $('#gongpingchar').hide();
                    $('#userslist').hide();
                    $('#friendchar').show();
                }else if(type.id == 'yonghu'){
                    $('#gongpingchar').show();
                    $('#userslist').show();
                    $('#friendchar').hide();
                }else if(type.id == 'openfriendlist'){
                    $('#openfriendlist').toggle();
                    $('#friendlist').show();
                }else if(type.id == 'offfriendlist'){
                    $('#friendlist').hide();
                    $('#openfriendlist').toggle();
                }
            }
            
            //私聊列表中点击名字出现聊天框
            $("#friendchar").Huitab({
                index: 0
            });
            
            $(function () { $("[data-toggle='popover']").popover()});
            //输入框获取焦点显蓝色
            $(".input-text,.textarea").Huifocusblur();
            $('#stip').Huifocusblur();
            //输入框输入字数限制
            $(".textarea").Huitextarealength({
                minlength: 10,
                maxlength: 200.
            });

            //公屏聊天输入框框内容验证规则及成功验证后提交输入框内容
            $("#allform").validate({
                rules: {
                    alltext: {
                        required: true,
                        minlength: 10,
                        maxlength: 200
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    $("#modal-shenqing-success").modal("show");
                    allsend();
                    //addmsgtogp('haoyou','消息');
                    //alert($(form).attr('name'));
                    //send();
                }
            });
            
            //好友聊天输入框框验证规则及成功后提交输入框内容
            $("#friendform").validate({
                rules: {
                    friendtext: {
                        required: true,
                        minlength: 10,
                        maxlength: 200
                    }
                },
                onkeyup: false,
                focusCleanup: true,
                success: "valid",
                submitHandler: function(form) {
                    $("#modal-shenqing-success").modal("show");
                    var ming = $(form).attr('name');
                    var fming = ming.substring(2);
                    //alert(fming);
                    friendsend(fming);
                    //addmsgtofd('haoyou','消息');
                    //alert($(form).attr('name'));
                    //send();
                }
            });
            
            //获取标签中name属性的值
            function getname(get){
                //alert($(get).attr('name'));
                //alert(get.id);
            }
            
            //alert($('textarea[name="alltext"]').val());