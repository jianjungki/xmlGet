function htmlspecialchars(p_string) {
	p_string = p_string.replace(/&/g, '&amp;');
	p_string = p_string.replace(/</g, '&lt;');
	p_string = p_string.replace(/>/g, '&gt;');
	p_string = p_string.replace(/"/g, '&quot;');
//	p_string = p_string.replace(/'/g, '&#039;');
	return p_string;
};

/********************************* 评论BEGIN ************************************/
function docomment(id,uid,name){
	var o = '';
	$.each($("div[rel='comment_replay']"),function(i,n){
		o = $(this).attr('type');
		if(id==o){
			$(this).show();
			$("div[rel='uncomment_replay'][type='"+o+"']").hide();
		}else{
			$(this).hide();
			$("div[rel='uncomment_replay'][type='"+o+"']").show();
		}
	});
	
	if(uid){
		$("#comment_replayto_"+id).val(uid);
		$("#comment_textarea_"+id).val('回复'+name+':');
	}else{
		$("#comment_textarea_"+id).val('');
	}
	$("#comment_textarea_"+id).focus();
}  


function undocomment(id){
	$("div[rel='comment_replay'][type='"+id+"']").hide();
	$("div[rel='uncomment_replay'][type='"+id+"']").show();
}

function doaddcomment(id){
	var content  = $.trim($("#comment_textarea_"+id).val());
	var replyuid = $.trim($("#comment_replayto_"+id).val());
	if (content) {
		$.post(SITE_URL+'/index.php?app=home&mod=User&act=docomment', {
			content: content,
			fid: id,
			replyuid: replyuid
		}, function(txt){
				txt = eval('('+txt+')');
				if(txt.status){//htmlspecialchars( content ) +
					var html = '<div id="comment_content_'+txt.info.type+'_'+txt.info.id+'" class="rcbox line_CCC">' +
					'<div onmouseover="this.className=\'on\'" onmouseout="this.className=\'#\'">' +
					'<div class="headpic50 L"><img src="'+txt.info.uhead+'" width="50" /></div>' +
					'<div class="rcc"><a href="javascript:void(0)" onclick=delreply('+txt.info.id+',"'+txt.info.type+'") class="delR">删除</a> <a href="index.php?app=space&mod=View&uid='+txt.info.uid+'" class="f12 mr10">'+txt.info.uname+'</a><em>刚刚</em><br />' +
					txt.info.comment +
					' </div>' +
					'<div class="C"></div></div></div>';
					$("div[rel='uncomment_replay'][type='"+id+"']").before( html );
					$("#comment_textarea_" + id).val('');
					undocomment(id);
				}else{
					alert(txt.info);
				}
		});

	}else {
		$.tbox.no('回复内容不能为空', '错误');
	}
}

function hide_reply(o,id){
	if($(o).attr('rel')=='on'){
		$(o).attr('rel','off');
		$(o).html('收起回复');
		$("#reply_list_"+id).slideDown("normal");
	}else{
		$(o).attr('rel','on');
		$(o).html('展开回复');
		$("#reply_list_"+id).slideUp("normal");
	}
}

function delreply(id,type){
	$.post(SITE_URL+'/index.php?app=home&mod=User&act=dodelcomment',{id:id},function(txt){
		txt = eval('('+txt+')');
		if(txt.status){
			$("#comment_content_"+type+"_"+id).slideUp("fast",function(){
				   $(this).remove();
			 });
		}else{
			alert('删除失败');
		}
	});
}
/********************************* 评论END ************************************/


function photo_size(name,sizeNum){
	var newWidth = $(name).width();
    $(name +" img").each(function(){
        var width = sizeNum || 728;
        var images = $(this);
        
        //判断是否是IE
        if (-[1, ]) {
        	  if (images.width() >= width) {
                  images.click(function(){
                      tb_show("", this.src, false);
                  });
                  images.width(width);
                  images.height(width / images.width() * images.height());
              }
           
        }
        else {
        	 image = new Image();
             image.src = $(this).attr('src');
             image.onload = function(){
                 if (image.width >= width) {
                 	images.click(function(){
                         tb_show("", this.src, false);
                     });
                     images.width(width);
                     images.height(width / image.width * image.height);
                 }
             }
	       	  if (images.width() >= width) {
	              images.click(function(){
	                  tb_show("", this.src, false);
	              });
	              images.width(width);
	              images.height(width / images.width() * images.height());
	          }
        }

		
		//image.attr('rel','imageGroup');

    });
}


function photo_size3(name,sizeNum){
	var newWidth = $(name).width();
    $(name +" img").each(function(){
        var width = sizeNum || 728;
        var images = $(this);
        
        //判断是否是IE
        if (-[1, ]) {
            if (images.width() >= width) {
                images.css("width",width);
                images.css("height",width / images.width() * images.height());
            }
        }
        else {
        	  image = new Image();
               image.src = $(this).attr('src');
               image.onload = function(){
                   if (image.width >= width) {
                       images.attr("width",width);
                       images.attr("height",width / image.width * image.height);
                   }
               };
        	
        }
		//image.attr('rel','imageGroup');

    });
}



function photo_size2(name,sizeNum){
	var newWidth = $(name).width();
    $(name +" img").each(function(){
 //       var width = sizeNum || 728;
        var images = $(this);
        
        //判断是否是IE
        if (-[1, ]) {
            image = new Image();
            image.src = $(this).attr('src');
            image.onload = function(){
//                if (image.width >= width) {
                	images.click(function(){
                        tb_show("", this.src, false);
                    });
//                    images.width(width);
//                    images.height(width / image.width * image.height);
//                }
            };
        }
        else {
//            if (images.width() >= width) {
                images.click(function(){
                    tb_show("", this.src, false);
                });
//                images.width(width);
//                images.height(width / images.width() * images.height());
//            }
        }

		
		//image.attr('rel','imageGroup');

    });
}

/**
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/
 * Version : 1.00.A
 * By Binny V A
 * 键盘绑定事件
 * License : BSD
 */
function shortcut(shortcut,callback,opt) {
	//Provide a set of default options
	var default_options = {
		'type':'keydown',
		'propagate':false,
		'target':document
	}
	if(!opt) opt = default_options;
	else {
		for(var dfo in default_options) {
			if(typeof opt[dfo] == 'undefined') opt[dfo] = default_options[dfo];
		}
	}

	var ele = opt.target
	if(typeof opt.target == 'string') ele = document.getElementById(opt.target);
	var ths = this;

	//The function to be called at keypress
	var func = function(e) {
		e = e || window.event;

		//Find Which key is pressed
		if (e.keyCode) code = e.keyCode;
		else if (e.which) code = e.which;
		var character = String.fromCharCode(code).toLowerCase();

		var keys = shortcut.toLowerCase().split("+");
		//Key Pressed - counts the number of valid keypresses - if it is same as the number of keys, the shortcut function is invoked
		var kp = 0;
		
		//Work around for stupid Shift key bug created by using lowercase - as a result the shift+num combination was broken
		var shift_nums = {
			"`":"~",
			"1":"!",
			"2":"@",
			"3":"#",
			"4":"$",
			"5":"%",
			"6":"^",
			"7":"&",
			"8":"*",
			"9":"(",
			"0":")",
			"-":"_",
			"=":"+",
			";":":",
			"'":"\"",
			",":"<",
			".":">",
			"/":"?",
			"\\":"|"
		}
		//Special Keys - and their codes
		var special_keys = {
			'esc':27,
			'escape':27,
			'tab':9,
			'space':32,
			'return':13,
			'enter':13,
			'backspace':8,

			'scrolllock':145,
			'scroll_lock':145,
			'scroll':145,
			'capslock':20,
			'caps_lock':20,
			'caps':20,
			'numlock':144,
			'num_lock':144,
			'num':144,
			
			'pause':19,
			'break':19,
			
			'insert':45,
			'home':36,
			'delete':46,
			'end':35,
			
			'pageup':33,
			'page_up':33,
			'pu':33,

			'pagedown':34,
			'page_down':34,
			'pd':34,

			'left':37,
			'up':38,
			'right':39,
			'down':40,

			'f1':112,
			'f2':113,
			'f3':114,
			'f4':115,
			'f5':116,
			'f6':117,
			'f7':118,
			'f8':119,
			'f9':120,
			'f10':121,
			'f11':122,
			'f12':123
		}


		for(var i=0; k=keys[i],i<keys.length; i++) {
			//Modifiers
			if(k == 'ctrl' || k == 'control') {
				if(e.ctrlKey) kp++;

			} else if(k ==  'shift') {
				if(e.shiftKey) kp++;

			} else if(k == 'alt') {
					if(e.altKey) kp++;

			} else if(k.length > 1) { //If it is a special key
				if(special_keys[k] == code) kp++;

			} else { //The special keys did not match
				if(character == k) kp++;
				else {
					if(shift_nums[character] && e.shiftKey) { //Stupid Shift key bug created by using lowercase
						character = shift_nums[character]; 
						if(character == k) kp++;
					}
				}
			}
		}

		if(kp == keys.length) {
			callback(e);

			if(!opt['propagate']) { //Stop the event
				//e.cancelBubble is supported by IE - this will kill the bubbling process.
				e.cancelBubble = true;
				e.returnValue = false;

				//e.stopPropagation works only in Firefox.
				if (e.stopPropagation) {
					e.stopPropagation();
					e.preventDefault();
				}
				return false;
			}
		}
	}

	//Attach the function with the event	
	if(ele.addEventListener) ele.addEventListener(opt['type'], func, false);
	else if(ele.attachEvent) ele.attachEvent('on'+opt['type'], func);
	else ele['on'+opt['type']] = func;
}

function copyToClipboard(txt){
    if (window.clipboardData) {
        window.clipboardData.clearData();
        window.clipboardData.setData("Text", txt);
		alert("复制成功！")
    }
    else 
        if (window.netscape) {
            try {
                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");
            } 
            catch (e) {
                alert("被浏览器拒绝！\n请在浏览器地址栏输入'about:config'并回车\n然后将'signed.applets.codebase_principal_support'设置为'true'\n或者直接Ctrl+C直接复制该段代码");
            }
            var clip = Components.classes['@mozilla.org/widget/clipboard;1'].createInstance(Components.interfaces.nsIClipboard);
            if (!clip) 
                return;
            var trans = Components.classes['@mozilla.org/widget/transferable;1'].createInstance(Components.interfaces.nsITransferable);
            if (!trans) 
                return;
            trans.addDataFlavor('text/unicode');
            var str = new Object();
            var len = new Object();
            var str = Components.classes["@mozilla.org/supports-string;1"].createInstance(Components.interfaces.nsISupportsString);
            var copytext = txt;
            str.data = copytext;
            trans.setTransferData("text/unicode", str, copytext.length * 2);
            var clipid = Components.interfaces.nsIClipboard;
            if (!clip) 
                return false;
            clip.setData(trans, null, clipid.kGlobalClipboard);
            alert("复制成功！")
        }else{
			alert("被浏览器拒绝！！直接Ctrl+C直接复制该段代码")
		}
}
var aid;
function checkattach(id){
	 aid = id;
	 if(typeof(id)=='number'){
		 $.tbox.confirm("你确定要审核附件？","提示",{ok:"docheck"});
	 }else{
		 $.tbox.confirm("你确定要批量审核附件？","提示",{ok:"docheck"});
	 	}
	
}

function docheck(){
	var attachId= aid;
	$.post(SITE_URL+'/index.php?app=forum&mod=Manage&act=doAttachAudit',{tp:'attach',id:attachId,status:0},function(result){
		if(result){
			location.reload();		
		}
	});
}
function cacleCheck(id){
	aid = id;
	$.tbox.confirm("你确定要取消审核附件？","提示",{ok:"docacle"});
}
function docacle(id){
	$.post(SITE_URL+'/index.php?app=forum&mod=Manage&act=doAttachAudit',{tp:'attach',id:aid,status:1},function(result){
		if(result){
			 location.reload();		
		}
	});
}
function onloadWindows(){}

function diyPopup(id,signId,widgetType){
	$.tbox.popup(APP + "&mod=Do&sign="+signId+"&act=getPopUp&tagName=" + widgetType + "&widgetId="+id, "添加自定义模块");
    var button = '<p><input class="btn_sea" id="savemodel"  name="" type="button" value="确定" /><input class="btn_sea_n ml5" name="" id="preview_button" type="button" value="预览"/></p>';
    $('#tbox .tb_button_list').show().html(button);
    $('#preview_button').click(function(){
        preview();
        
    });
    $('#savemodel').click(function(){
        savemodel();
        $.tbox.close();
        
    });
}

function switchTitleBar(){
	var obj = $('#headerBar');
	if(obj.css('display') == "none"){
		obj.show();
	}else{
		obj.hide();
	}
}