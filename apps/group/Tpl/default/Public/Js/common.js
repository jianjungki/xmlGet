// 加入群组
function joingroup(gid) {
	// 未登录则弹出登录框
	if ($CONFIG['mid'] <= 0) {
		ui.quicklogin();
		return ;
	}
     if( grouptypeparam==0){
       ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,{title:'加入圈子'});
  }else if(grouptypeparam==1){
          ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,{title:'加入项目'});
   }else{
             ui.box.load(U('group/Group/joinGroup')+'&gid='+gid,{title:'加入组织'});
   }

}
// 删除群组
function delgroup(gid,grouptypeparam) {
  if( grouptypeparam==0){
         ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,{title:'解散圈子'});
  }else if(grouptypeparam==1){
         ui.box.load(U('group/Group/delProjectDialog')+'&gid='+gid,{title:'解散项目'});
   }else{
          ui.box.load(U('group/Group/delGroupDialog')+'&gid='+gid,{title:'解散组织'});

   }

 
}
// 退出群组
function quitgroup(gid) {
  if( grouptypeparam==0){
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,{title:'脱离圈子'});
  }else if(grouptypeparam==1){
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,{title:'脱离项目'});
   }else{
    ui.box.load(U('group/Group/quitGroupDialog')+'&gid='+gid,{title:'脱离组织'});

   }

}
// 过滤html，字串检测长度
function checkPostContent(content)
{
	content = content.replace(/&nbsp;/g, "");
	content = content.replace(/<br>/g, "");
	content = content.replace(/<p>/g, "");
	content = content.replace(/<\/p>/g, "");
	return getLength(content);
}

// 完成项目
function endproject(gid) {
    ui.box.load(U('group/Group/endProjectDialog')+'&gid='+gid,{title:'结束项目'});
}