/*********************************************************************************
 * screen.js
 * 
 * @project ts
 * @version $id$
 * @copyright 2001-2013 sampeng
 * @author sampeng <penglingjun@zhishisoft.com> 
 * @license {@link www.zhishisoft.com}
 *********************************************************************************/
var Screen = (function(){
    var nextQueue = ["step1","step2","step3"];
    var previousQueue = [];
    var nowDiv;
    var weiboQueue = [];
    var setting = {};
    var showSetting = {};
    var showWeiboQueue = [];
    var lastTimestemp = 0;
    var showInterval,getWeiboInterval;
    return {
        start:function(){
              url = U('screen/index/show',['id='+setting.screenId]);
              var test = window.open(url,'newwindow','top=0,left=0,toolbar=no,menubar=no,scrollbars=no, resizable=yes,location=no, status=no'); 
            $.post(U('screen/do/start'),{screen_id:setting.screenId},function(res){
                  $(setting.queueManage.button).removeClass(setting.queueManage.start);
                  $(setting.queueManage.button).addClass(setting.queueManage.stop);
            });
        },
        stop:function(auto,screenId){
            
            if(auto == undefined){
                $.post(U('screen/do/stop'),{screen_id:setting.screenId},function(res){
                    $(setting.queueManage.button).removeClass(setting.queueManage.stop);
                    $(setting.queueManage.button).addClass(setting.queueManage.start);
                });  
            }else{
                $.ajax({
                      type:"POST",
                      url:U('screen/do/stop'),
                      data:{screen_id:screenId},
                      async:false
                 });
            }
          
        },
        show:{
            init:function(options){
              showSetting = options;
              getWeiboInterval = setInterval(function(){
                   $.post(U('screen/index/showWeibo'),{screen_id:showSetting.screenId,last:lastTimestemp},function(res){
                       res = eval("("+res+")");
                       if(res.status && res.info != null){
                         for(var one in res.data){
                             showWeiboQueue.unshift(res.data[one]);
                         }
                         lastTimestemp = res.info;
                       }
                   });
               },5000);
               
              showInterval = setInterval(function(){
                  if(showWeiboQueue.length >0){
                      var weibo = showWeiboQueue.pop();
                      //设置为已显示
                      $.post(U('screen/do/setWeiboIsShow'),{screen_id:showSetting.screenId,weibo_id:weibo.weibo_id},function(res){
                          res = eval("("+res+")");
                          if(res.status){
                              var newWeibo = $(showSetting.listScreen).children(':last').clone();
                              newWeibo.find('.userface').attr('src',res.data.userface);
                              newWeibo.find('.username').html(res.data.username);
                              var content = newWeibo.find('.userInfo');
                              content.html(res.data.content);
                              if(res.data.pic != undefined){
                                content.prepend(res.data.pic);
                              }
                              $(showSetting.listScreen).prepend(newWeibo);
                              newWeibo.show('scale');
                          }
                      });
                  } 
               },3000);
                 // $(window).bind('onbeforeunload',function(){
                // Screen.stop();
                // });
                $(window).unload(function(){
                    Screen.stop(true,showSetting.screenId);
                })
                
            }
          
        },
        detailAdmin:{
           init:function(options){
               setting = options;
               $(setting.search).validVal();
               this.click();
               this.hover();
               this.deleteQueue();
               this.clear();
               for(var one in options.queue){
                   weiboQueue.push(options.queue[one].weibo_id)
               }
               this.checkWeiboIsShow();
           },
           hover:function(classes){
            $(setting.weiboListClass).hover(function() {
                $(this).addClass('current');
            }, function() {
                if($(this).attr('inQueue') != "true"){
                    $(this).removeClass('current');
                }
            });  
           },
           clear:function(){
               $(setting.clearClass).click(function(){
                   $('#queue_count').html(0);
                   var tempQueue = weiboQueue;
                   weiboQueue = [];
                   $.each($(setting.queueUlClass).children(),function(index,val){
                       var test = $(val);
                       test.hide('scale');
                      
                   });
                   $.post(U('screen/do/deleteAllQueue'),{screen_id:setting.screenId},function(res){
                       res= eval('('+res+')');
                       if(!res.status){
                           ui.error(res.info);
                       }else{
                           for(var one in tempQueue){
                               $('#list_li_'+tempQueue[one]).removeClass('current').attr('inQueue','false');
                           }
                       }
                   })
               });
           },
           deleteQueue:function(){
             $(setting.deleteClass).click(function() {
                var id = $(this).attr('weiboId');
                $(this).parent().hide("scale");
                weiboQueue.splice($.inArray(id,weiboQueue),1);
                $('#queue_count').html(weiboQueue.length);
                
                $.post(U('screen/do/deleteQueue'),{weibo_id:id,screen_id:setting.screenId},function(res){
                    res = eval('('+res+')');
                    if(!res.status){
                        ui.error(res.info);
                    }else{
                        $('#list_li_'+id).removeClass('current').attr('inQueue','false');
                    }
                })
             });  
           },
           click:function(){
               $(setting.queueManage.button).click(function(event){
                   if($(this).hasClass(setting.queueManage.start)){
                       Screen.start(); 
                   }else{
                       Screen.stop(); 
                   }
               });
               $(setting.weiboListClass).click(function(event) {
                    var id = $(this).attr('id');
                    var weiboId = id.split("_").pop();
                    var self = this;
                   
                    if(-1 != $.inArray(weiboId, weiboQueue)) {
                        if($(self).attr('inQueue') != 'true') {
                            $(self).attr('inQueue', 'false');
                        }
                        ui.error("该微博已经处于消息队列中");
                        return;
                    }
                    
                    $.post(U('screen/do/addWeiboToScreen'),{weibo_id:weiboId,screen_id:setting.screenId},function(res){
                        var res = eval('('+res+')');
                        if(res.status){
                            $(self).addClass('current');
                            $(self).attr('inQueue','true');
                            weiboQueue.unshift(weiboId);
                            
                            //将微博数据给提取出来
                            var content  = res.data.content;
                            var time     = res.data.time;
                            var a     = res.data.a;

                            //得到一个右侧队列的html对象
                            var newWeibo = $(setting.queueUlClass).children(':last').clone(true);

                            newWeibo.find('a:first').attr('weiboid',weiboId);
                            newWeibo.find('.username').replaceWith(a);
                            newWeibo.find('id','queue_item_'+weiboId);
                            newWeibo.find('.date_S').html(time);
                            newWeibo.find('span:first').html(content);
                            newWeibo.hide();
                            $(setting.queueUlClass).prepend(newWeibo);
                            newWeibo.show('scale');
                        }else{
                            if($(self).attr('inQueue') != 'true'){
                                $(self).attr('inQueue','false');
                            }
                            ui.error(res.info);
                        }
                        $('#queue_count').html(weiboQueue.length);
                    });
                    event.stopPropagation;
               });
           },
           checkWeiboIsShow:function(){
             setInterval(function() {
                 if(weiboQueue.length > 0){
                     $.post(U('screen/index/getScreenWeiboIsShow'),{weibo_id:weiboQueue,screen_id:setting.screenId},function(res){
                         res = eval('('+res+')');
                         if(res.status){
                             if(res.info){
                                 var id,queueIndex;
                                 for(var one in res.info){
                                     id = res.info[one];
                                     $('#list_li_'+id).removeClass('current').attr('inQueue','false');
                                     queueIndex = $.inArray(id,weiboQueue);
                                     $('#queue_item_'+id).hide('scale');
                                     weiboQueue.splice(queueIndex,1);
                                     $('#queue_count').html(weiboQueue.length);
                                 }
                                
                             }
                             if(res.data.status == "start"){
                                     $(setting.queueManage.button).removeClass(setting.queueManage.start);
                                     $(setting.queueManage.button).addClass(setting.queueManage.stop);
                             }else{
                                     $(setting.queueManage.button).removeClass(setting.queueManage.stop);
                                     $(setting.queueManage.button).addClass(setting.queueManage.start);
                             }
                         }else{
                             ui.error(res.info);
                         }
                     });
                 }
             },5000);
           }
        },
        applyPopup:function(){
            ui.box.load( U("screen/index/apply"),{title:'申请微博大屏幕',closeable:true,callback:this.clear});
        },
       clear:function(){
                nextQueue = ["step1","step2","step3"];
                previousQueue = [];
                nowDiv;
        },
        //检查字数输入
        checkInputLength:function(obj){
            var len = getLength($(obj).val(), true);
            var wordNumObj = $('#'+$(obj).attr('id')+"-num");
            var num = $(obj).attr('limit');
            if(len==0){
                wordNumObj.css('color','').html('<strong id="strconunt">'+ (len) + '</strong>/'+num);
                $(obj).data({"wordnum":true});
            }else if( len > num ){
                wordNumObj.css('color','red').html('<strong id="strconunt">'+ (num) +'</strong>/'+num);
                $(obj).data({"wordnum":false});

            }else if( len <= num ){
                wordNumObj.css('color','').html('<strong id="strconunt">'+ (len) + '</strong>/'+num);
                $(obj).data({"wordnum":true});
            }
        },
        apply:{
            next:function(){
                var nextDiv;
                var stepUi=$('.step_apply');
                if(nowDiv == undefined){
                   nowDiv = nextQueue.shift();
                }
                if(!this[nowDiv]()) return false;

                nextDiv = nextQueue.shift();
                
                $('#'+nextDiv).show();
                $('#'+nowDiv).hide();
                stepUi.removeClass(nowDiv);
                stepUi.addClass(nextDiv);
                previousQueue.push(nowDiv);
                nowDiv = nextDiv;
            },
            previous:function(){
                var stepUi=$('.step_apply');

                if(nowDiv == undefined || previousQueue.length==0){
                    ui.box.close(Screen.clear());
                }else{
                    
                    if(!this[nowDiv]()) return false;
                    var previousDiv;
                    previousDiv = previousQueue.pop();
                    nextQueue.unshift(nowDiv);
                    
                    $('#'+nowDiv).hide();
                    $('#'+previousDiv).show();
                    
                    stepUi.removeClass(nowDiv);
                    stepUi.addClass(previousDiv);
                    nowDiv = previousDiv;
                }
            },
            step1:function(){
                $('#step1').validVal();
                var res = $('#step1').submit();
                if($(res).attr('validate') == 'true'){
                    return true;
                }else{
                    return false;
                }
            },
            step2:function(){
                $('#step2').validVal();
                var res = $('#step2').submit();
                if($(res).attr('validate') == 'true'){
                     var data = {};
                    data.title = $('#title').val();
                    data.start_time = $('#start-date').val();
                    data.end_time   = $('#end-date').val();
                    data.explains   = $('#explains').val();
                    data.logo       = $('#attach_upload_data').find(':hidden').val();
                    data.topic      = $('#topic').val();   
                    data.keyword    = $('#keyword').val();
                    // $.post(U('screen/do/apply'),data,function(res){
                    //     var res = eval('('+res+')');
                    //     if(res.status == 0){
                    //         Screen.clear();
                    //         Screen.apply.next();
                    //     }else{
                    //         setTimeout(function(){
                    //            ui.box.close(function(){
                    //               window.location.href=res.data.url; 
                    //            });
                    //         },2000);
                    //     }
                    //  });
                    return true;
                }else{
                    return false;
                }
            }

       }
       
    };
})();

Screen.applyPopup.onload = function() {
    var Interval;
    var input = "#title,#explains";
    //$.datepicker.setDefaults( $.datepicker.regional[ "" ] );
    $('.date-pick').datePicker({
        clickInput : true,
        createButton : false
    });
    $('#start-date').bind('dpClosed', function(e, selectedDates) {
        var d = selectedDates[0];
        if(d) {
            d = new Date(d);
            $('#end-date').dpSetStartDate(d.addDays(1).asString());
        }
    });
    $('#end-date').bind('dpClosed', function(e, selectedDates) {
        var d = selectedDates[0];
        if(d) {
            d = new Date(d);
            $('#start-date').dpSetEndDate(d.addDays(-1).asString());
        }
    });

    $(input).keypress(function(event) {
        var key = event.keyCode ? event.keyCode : event.which ? event.which : event.charCode;
        if(key == 27) {
            clearInterval(Interval);
        }
        Screen.checkInputLength(this);
    }).blur(function() {
        clearInterval(Interval);
        Screen.checkInputLength(this);
    }).focus(function() {
        var self = this;
        clearInterval(Interval);
        Interval = setInterval(function() {
            Screen.checkInputLength(self);
        }, 300)
    });
}
