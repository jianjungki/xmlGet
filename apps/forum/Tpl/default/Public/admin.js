var newThreadInput = "";
var count = 0;
$(function() {
	newThreadInput = $('#addThread').clone();
	$('#addThread').remove();
});

function addChildrenThread(fid, last) {
	var id = "#f" + fid;
	count++;
	if (last == undefined) {
		var parentPosition = $(id).css('padding-left');
		// 定位
		if (parentPosition == null) {
			position = "30px";
		} else {
			var parentId = parentPosition.replace("px", "");
			position = (parseInt(parentId) / 30 + 1) * 30 + "px";
		}
		var newThread = newThreadInput.clone().show().attr('id', "").css(
				"padding-left", position);
		newThread.find(":text").attr('id', 'fcinput' + count).attr("name",
				"newThread[" + fid + "][name][" + count + "]");
		newThread.find(":checkbox").attr('id', 'fcinput' + count).attr("name",
				"newThread[" + fid + "][id][" + count + "]");

		if ($("#fc" + fid).html() == null) {
			var html = '<ul id="fc' + fid + '" class="sort"></ul>';
			$(id).after(html);
		}
		$('#fc' + fid).append(newThread);
		foldCate(fid, $(id + " img:first"), "on");
	} else {
		position = "30px";
		var newThread = newThreadInput.clone().show().attr('id', "").css(
				"padding-left", position);
		newThread.find(":text").attr('id', 'fcinput' + count).attr("name",
				"newThread[" + fid + "][name][" + count + "]");
		newThread.find(":checkbox").attr('id', 'fcinput' + count).attr("name",
				"newThread[" + fid + "][id][" + count + "]");
		$('#rootList').append(newThread);
	}

}

function foldCate(fid, obj, switched) {
	var id = "#fc" + fid;
	var id2 = "#fac" + fid;
	var rel = obj.attr('rel');
	if (switched != null)
		rel = switched;
	switch (rel) {
	case "on":
		$(id).show();
		$(id2).show();
		obj.attr('src', PUBLIC + "/images/aaOff.png");
		obj.attr('rel', "off");
		break;
	case "off":
		$(id).hide();
		$(id2).hide();
		obj.attr('src', PUBLIC + "/images/aaOn.png");
		obj.attr('rel', "on");
		break;
	}
}

function setAdmin(fid, title, user) {
	var getURL = URL + "&act=setAdmin&fid=" + fid;

	if (user != '') {
		getURL += "&user=" + user;
	}
	$.tbox.popup(getURL, title, {
		ok : "confirmExpert"
	});
}

function deleteCate(fid, hasChildren) {
	if (window.confirm("确认删除?")) {
		$.post(URL + '&act=checkCateIsLeaf', {
			fid : fid
		}, function(result) {
			if (result == -1) {
				alert("下级版块不为空，请先返回删除本分类或版块的下级版块。");
			} else {
				$.tbox.popup(URL + "&act=wind&fid=" + fid, '确定删除分类', {
					ok : "confirmDelete"
				});
			}
		});
	}
}

function sortCate(fid, position) {
	$.post(URL + '&act=sortCate', {
		fid : fid,
		position : position
	}, function(result) {
		if (result == 1) {
			location.reload();
		} else {
			alert(result);
		}
	});
}

