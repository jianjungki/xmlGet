(function($){
    var resIdArray = new Array();
    var clickedAdmin = new Array();
    /**
     * 下面是方法
     */
    //定位
    function orientation(check, box, options){
    
        //单选框的位置
        var height = check.offset().top;
        var width = check.offset().left;
		 var top = height;
        if(options.position == 'left'){
		    //管理盒子的位置
	        var left = width - box.width() - options.sOffset;
		}else{
			var left = width + check.width()+options.sOffset;
		}    
        
        box.css('top', top).css('left', left);
        
    };
    
    
    //按键绑定
    function keyBinding(options, box, a, checkedModule, checkedSpan){
        checkedModule.click(function(){
			clickedAdmin[options.prefix] = $(this);
            checkedAction($(this).attr('dataId'), $(this), box, options, true, a)
        });
    };
    
    //选中动作
    function checkedAction(resId, check, box, options, checkedWhere, a){
        var checked = check.attr('checked');
        if (checked) {
            if (checkedWhere) {
                box.show();
            }
            else {
                check.attr('checked', true);
                if (options.type) {
                    box.hide();
                }
                else {
                    if (resIdArray[options.prefix].length == 0) 
                        box.hide();
                }
            }
            
        }
        else {
            if (checkedWhere) {
                if (options.type) {
                    check.removeAttr('checked');
                    box.hide();
                }
                else {
                    if (resIdArray[options.prefix].length == 1) {
                        check.removeAttr('checked');
                        box.hide();
                    }
                }
            }
            else {
                box.show();
            }
        }
        
        ////如果点击之后的最终状态为checked。做一系列的数据处理
        checked = check.attr('checked');
        options.clickBefore();
        

        
        if (checked) {
            //将资源Id存储
            resIdArray[options.prefix].push(resId);
            
        }
        else {
            //将资源Id移除
            var aindex = $.inArray(resId, resIdArray[options.prefix]);
			if(aindex != -1){
				resIdArray[options.prefix] = rmArray(aindex, resIdArray[options.prefix]);
			}
        }

        options.clickAfter();

        var needA = new Array();
		needA = getAdminHref(check.attr('rel'), a);

		//渲染内部链接
		//$('#'+options.prefix+'admin_list').html(' ');
        for (var one in needA) {
            $('#'+options.prefix+'admin_list').append(needA[one]);
        }
		//为box中的计数进行更换.只有列表类型的才能使用
        if (!options.type) {
            $('#' + options.prefix + 'admin_box_count').html(resIdArray[options.prefix].length);
        }
		//为box进行定位
        orientation(check, box, options);
    };
    
    //根据所给索引,获得相对应的链接
    function getAdminHref(index, a){
        var needA = index.split(',');
        var newArray = new Array();
        for (var one in needA) {
            if (one == 'remove') 
                continue;
            newArray.push(a[needA[one]]);
        }

        return newArray;
    };
    
    //数组移除动作
    function rmArray(dx, array){
        if (isNaN(dx) || dx > array.length) {
            return array;
        }
        for (var i = 0, n = 0; i < array.length; i++) {
            if (array[i] != array[dx]) {
                array[n++] = array[i];
            }
        }
        
        array.length -= 1;
        
        return array;
    }
    
    
    $.fn.extend({
        admin: function(setting){
            //默认参数
            var options = $.extend({
                prefix: 'ts_', //这个对象所用到的前缀。整个页面不能重复使用
                checkClass: 'admin_romance', //单选框的Class
                hiddenId: 'admin_romance', //隐藏div的id
                checkSpanClass: false, //单选框后面的span的Class
                spanCanCheck: false, //span是否可以单击
                type: true, //true为单用类型，false为列表多用类型
                sOffset: 18, //管理box距离单选框的距离
                xMinOffset: 20, //管理box距离屏幕左侧的最小距离
                lMinOffset: 18, //管理box距离单选框右侧的距离
                clickBefore: function(){},   //点击事件处理之前
				clickAfter: function(){},	//点击事件处理之后
				position: 'left'
            }, setting);
            
            var adminBox, list;
            var checkedWhere = true;
            var checkedModule, checkedSpan, Box;
            var a = new Array();
            
            
            resIdArray[options.prefix] = new Array();
            adminBox = '<div id="' + options.prefix + 'admin_box" class="edit_box_b" \
							style="display:none;"><span id="' +
            options.prefix +
            'admin_list"></span></div>';
            list = '选择<span id="' + options.prefix + 'admin_box_count" \
							class="modcount"></span>个';
            
            
            
            checkedWhere = true;
            
            
            
            //将box加载进入页面
            $('body').append(adminBox);
            Box = $('#' + options.prefix + 'admin_box');
            if (!options.type) {
                Box.prepend(list);
            }
            //将链接加入页面。然后是删除这个链接box
            $('#' + options.hiddenId).children().each(function(){
                a.push($(this).clone());
            }).remove();
            
            //减少代码量。。
            checkedModule = $('.' + options.checkClass);
            checkedSpan = $('.' + options.checkSpanClass);
            keyBinding(options, Box, a, checkedModule, checkedSpan);
            
        },
		
		//获取被选择的资源Id数组
        getData: function(prefix){
            return resIdArray[prefix];
        },
		
		//移除管理盒子中的成员
		removeAdminTeam:function(targetTeam,prefix){
			var team = clickedAdmin[prefix].attr('rel').split(',');
			var index = $.inArray(targetTeam,team);
			if(index != -1) clickedAdmin[prefix].attr('rel',rmArray(index,team));
			return clickedAdmin[prefix];
		},
		
		//增加管理盒子中的成员
		addAdminTeam:function(targetTeam,prefix){
			var team = clickedAdmin[prefix].attr('rel').split(',');
			var index = $.inArray(targetTeam,team);
			if (index == -1) {
				team.push(targetTeam);
				clickedAdmin[prefix].attr('rel', team.join(','));
			}
			return clickedAdmin[prefix];
		}
        
    });
})(jQuery);
