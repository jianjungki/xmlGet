/*
 * Thickbox 3.1 - One Box To Rule Them All.
 * By Cody Lindley (http://www.codylindley.com)
 * Copyright (c) 2007 cody lindley
 * Licensed under the MIT License: http://www.opensource.org/licenses/mit-license.php
 */
var tb_pathToImage = JS_DIR + "/thickbox/images/transparent.gif";

/*!!!!!!!!!!!!!!!!! edit below this line at your own risk !!!!!!!!!!!!!!!!!!!!!!!*/

//on page load call tb_init
$(document).ready(function(){
    tb_init('a.thickbox, area.thickbox, input.thickbox');//pass where to apply thickbox
    imgLoader = new Image();// preload image
    imgLoader.src = tb_pathToImage;
});

//add thickbox to href & area elements that have a class of .thickbox
function tb_init(domChunk){
    $(domChunk).click(function(){
        var t = this.title || this.name || null;
        var a = this.href || this.alt;
        var g = this.rel || false;
        tb_show(t, a, g);
        this.blur();
        return false;
    });
}

function tb_show(caption, url, imageGroup){//function called when the user clicks on a thickbox link
    try {
        if (typeof document.body.style.maxHeight === "undefined") {//if IE 6
            $("body", "html").css({
                height: "100%",
                width: "100%"
            });
            $("html").css("overflow", "hidden");
            if (document.getElementById("TB_HideSelect") === null) {//iframe to hide select elements in ie6
                $("body").append("<iframe id='TB_HideSelect'></iframe><div id='TB_overlay'></div><div id='TB_window'></div>");
                $("#TB_overlay").click(tb_remove);
            }
        }
        else {//all others
            if (document.getElementById("TB_overlay") === null) {
                $("body").append("<div id='TB_overlay'></div><div id='TB_window'></div>");
                $("#TB_overlay").click(tb_remove);
            }
        }
        
        if (tb_detectMacXFF()) {
            $("#TB_overlay").addClass("TB_overlayMacFFBGHack");//use png overlay so hide flash
        }
        else {
            $("#TB_overlay").addClass("TB_overlayBG");//use background and opacity
        }
        
        if (caption === null) {
            caption = "";
        }
        $("body").append("<div id='TB_load'><img src='" + imgLoader.src + "' /></div>");//add loader to the page
        $('#TB_load').show();//show loader
        var baseURL;
        //修改
        baseURL = url;
//        if (url.indexOf("?") !== -1) { //ff there is a query string involved
//            baseURL = url.substr(0, url.indexOf("?"));
//        }
//        else {
//            baseURL = url;
//        }
        
        var urlString = /\.jpg$|\.jpeg$|\.png$|\.gif$|\.bmp$/;
            TB_PrevCaption = "";
            TB_PrevURL = "";
            TB_PrevHTML = "";
            TB_NextCaption = "";
            TB_NextURL = "";
            TB_NextHTML = "";
            TB_imageCount = "";
            TB_FoundURL = false;
            if (imageGroup) {
                TB_TempArray = $("img[rel=" + imageGroup + "]").get();
                for (TB_Counter = 0; ((TB_Counter < TB_TempArray.length) && (TB_NextHTML === "")); TB_Counter++) {
                    var urlTypeTemp = TB_TempArray[TB_Counter].src.toLowerCase().match(urlString);
                    if (!(TB_TempArray[TB_Counter].src == url)) {
                        if (TB_FoundURL) {
                            TB_NextCaption = TB_TempArray[TB_Counter].title;
                            TB_NextURL = TB_TempArray[TB_Counter].src;
                            TB_NextHTML = "<span id='TB_next'>&nbsp;&nbsp;<a href='#'>下一个 &gt;</a></span>";
                        }
                        else {
                            TB_PrevCaption = TB_TempArray[TB_Counter].title;
                            TB_PrevURL = TB_TempArray[TB_Counter].src;
                            TB_PrevHTML = "<span id='TB_prev'>&nbsp;&nbsp;<a href='#'>&lt; 上一个</a></span>";
                        }
                    }
                    else {
                        TB_FoundURL = true;
                        TB_imageCount = (TB_Counter + 1) + " / " + (TB_TempArray.length);
                    }
                }
            }
            
            imgPreloader = new Image();
            imgPreloader.onload = function(){
                imgPreloader.onload = null;
                
                // Resizing large images - orginal by Christian Montoya edited by me.
                var pagesize = tb_getPageSize();
                var x = pagesize[0] - 150;
                var y = pagesize[1] - 150;
                var imageWidth = imgPreloader.width;
                var imageHeight = imgPreloader.height;
                var bigImageWidth = imageWidth;
                var bigImageHeight = imageHeight;
                
                if (imageWidth > x) {
                    imageHeight = imageHeight * (x / imageWidth);
                    imageWidth = x;
                    if (imageHeight > y) {
                        imageWidth = imageWidth * (y / imageHeight);
                        imageHeight = y;
                    }
                }
                else 
                    if (imageHeight > y) {
                        imageWidth = imageWidth * (y / imageHeight);
                        imageHeight = y;
                        if (imageWidth > x) {
                            imageHeight = imageHeight * (x / imageWidth);
                            imageWidth = x;
                        }
                    }
                //监听滚轮事件
                var newImageWidth = imageWidth;
                var newImageHeight = imageHeight;
                //算最大比例值
                
                $('#TB_window').bind('mousewheel', function(event, delta){
                    var vel = Math.abs(delta);
                    var oldWidth = newImageWidth;
                    var oldHeight = newImageHeight;
                    
                    //往上滚动
                    if (delta > 0) {
                        newImageWidth *= (vel /10 + 1);
                        newImageHeight *= (vel /10  + 1);
                        if (newImageWidth > bigImageWidth) {
                            newImageWidth = bigImageWidth;
                            newImageHeight = bigImageHeight;
                        }
                    }
                    else {
                        //往下滚动
                        newImageWidth *= (1 - vel / 10);
                        newImageHeight *= (1 - vel / 10);
                        if (bigImageWidth <= x) {
                            if (newImageWidth < bigImageWidth) {
                                newImageWidth = bigImageWidth;
                                newImageHeight = bigImageHeight;
                            }
                        }
                        else {
                            if (newImageWidth <= imageWidth) {
                                newImageWidth = imageWidth;
                                newImageHeight = bigImageHeight * (imageWidth / bigImageWidth);
                            }
                        }
                        
                        
                    }
                    
                    //		            var dir = delta > 0 ? 'Up' : 'Down',
                    //		                vel = Math.abs(delta);
                    //		            $(this).text(dir + ' at a velocity of ' + vel);
                    $('#TB_Image').attr('width', newImageWidth).attr('height', newImageHeight);
                    TB_WIDTH = newImageWidth + 30;
                    TB_HEIGHT = imageHeight + 60;
                    tb_position();
                    return false;
                });
				

                
                // End Resizing
                
                TB_WIDTH = imageWidth + 30;
                TB_HEIGHT = imageHeight + 60;
				var TOP_DIV ="<div class='TB_padding'><div id='TB_closeWindow'><span class='R'><a title='查看原图' target='_blank' class='imglink' href='"+url+"'>查看原图</a><a title='最大化' class='imgadjust'  href='#'>最大化</a><a title='ESC键也能关闭' id='TB_closeWindowButton' class='imgclose'  href='#'>关闭</a></span>Esc键可以退出</div>";
                $("#TB_window").append(TOP_DIV+"<img id='TB_Image' src='" + url + "' width='" + imageWidth + "' height='" + imageHeight + "' alt='" + caption + "'/>" + "<div id='TB_caption'>" + caption + "<div id='TB_secondLine'>" + TB_imageCount + TB_PrevHTML + TB_NextHTML + "</div></div></div>");
                $("#TB_window").addClass('ui-draggable');
                $("#TB_closeWindowButton").click(tb_remove);
                $(".imgadjust").click(function(){
					$('#TB_Image').attr('width', bigImageWidth).attr('height', bigImageHeight);
                    TB_WIDTH = bigImageWidth + 30;
                    TB_HEIGHT = bigImageHeight + 60;
                    tb_position();
                    return false;
				})
				//设置图片可拖拽，并且拖拽一张图片时，同时移动另外一张图片
			    $("#TB_window").draggable();
                
				
				if (!(TB_PrevHTML === "")) {
                    function goPrev(){
                        if ($(document).unbind("click", goPrev)) {
                            $(document).unbind("click", goPrev);
                        }
                        $("#TB_window").remove();
                        $("body").append("<div id='TB_window'></div>");
                        tb_show(TB_PrevCaption, TB_PrevURL, imageGroup);
                        return false;
                    }
                    $("#TB_prev").click(goPrev);
                }
                
                if (!(TB_NextHTML === "")) {
                    function goNext(){
                        $("#TB_window").remove();
                        $("body").append("<div id='TB_window'></div>");
                        
                        tb_show(TB_NextCaption, TB_NextURL, imageGroup);
                        return false;
                    }
                    $("#TB_next").click(goNext);
                    
                }
                
                document.onkeydown = function(e){
                    if (e == null) { // ie
                        keycode = event.keyCode;
                    }
                    else { // mozilla
                        keycode = e.which;
                    }
                    if (keycode == 27) { // close
                        tb_remove();
                    }
                    else 
                        if (keycode == 190) { // display previous image
                            if (!(TB_NextHTML == "")) {
                                document.onkeydown = "";
                                goNext();
                            }
                        }
                        else 
                            if (keycode == 188) { // display next image
                                if (!(TB_PrevHTML == "")) {
                                    document.onkeydown = "";
                                    goPrev();
                                }
                            }
                };
				
                tb_position();
                $("#TB_load").remove();
                $("#TB_ImageOff").click(tb_remove);
                $("#TB_window").css({
                    display: "block"
                }); //for safari using css instead of show
            };
            
            imgPreloader.src = url;
        
        
        if (!params['modal']) {
            document.onkeyup = function(e){
                if (e == null) { // ie
                    keycode = event.keyCode;
                }
                else { // mozilla
                    keycode = e.which;
                }
                if (keycode == 27) { // close
                    tb_remove();
                }
            };
        }
        
    } 
    catch (e) {
        //nothing here
    }
}

//helper functions below
function tb_showIframe(){
    $("#TB_load").remove();
    $("#TB_window").css({
        display: "block"
    });
}

function tb_remove(){
    $("#TB_imageOff").unbind("click");
    $("#TB_closeWindowButton").unbind("click");
    $("#TB_window").fadeOut("fast", function(){
        $('#TB_window,#TB_overlay,#TB_HideSelect').trigger("unload").unbind().remove();
    });
    $("#TB_load").remove();
    if (typeof document.body.style.maxHeight == "undefined") {//if IE 6
        $("body", "html").css({
            height: "auto",
            width: "auto"
        });
        $("html").css("overflow", "");
    }
    document.onkeydown = "";
    document.onkeyup = "";
    return false;
}

function tb_position(){
	var temp = TB_WIDTH;
	TB_WIDTH = TB_WIDTH < 210 || TB_WIDTH;
	if(TB_WIDTH == true){
		 $("#TB_window").css({
		        marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
		        width: temp + 'px',
				marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
				top:$(document).scrollTop()+parseInt(($(window).height() / 2), 10)
		 });
	}else{
		 $("#TB_window").css({
		        marginLeft: '-' + parseInt((TB_WIDTH / 2), 10) + 'px',
		        width: TB_WIDTH + 'px',
				marginTop: '-' + parseInt((TB_HEIGHT / 2), 10) + 'px',
				top:$(document).scrollTop()+parseInt(($(window).height() / 2), 10)
		 });
	}
}

function tb_parseQuery(query){
    var Params = {};
    if (!query) {
        return Params;
    }// return empty object
    var Pairs = query.split(/[;&]/);
    for (var i = 0; i < Pairs.length; i++) {
        var KeyVal = Pairs[i].split('=');
        if (!KeyVal || KeyVal.length != 2) {
            continue;
        }
        var key = unescape(KeyVal[0]);
        var val = unescape(KeyVal[1]);
        val = val.replace(/\+/g, ' ');
        Params[key] = val;
    }
    return Params;
}

function tb_getPageSize(){
    var de = document.documentElement;
    var w = window.innerWidth || self.innerWidth || (de && de.clientWidth) || document.body.clientWidth;
    var h = window.innerHeight || self.innerHeight || (de && de.clientHeight) || document.body.clientHeight;
    arrayPageSize = [w, h];
    return arrayPageSize;
}

function tb_detectMacXFF(){
    var userAgent = navigator.userAgent.toLowerCase();
    if (userAgent.indexOf('mac') != -1 && userAgent.indexOf('firefox') != -1) {
        return true;
    }
}


