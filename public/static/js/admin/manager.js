$(function(){
	layui.use(['layer','form','element'], function(){
		var layer = layui.layer,
		form = layui.form(),
		element = layui.element();
		
		//全选
	  form.on('checkbox(allChoose)', function(data){
	    var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
	    child.each(function(index, item){
	      item.checked = data.elem.checked;
	    });
	    form.render('checkbox');
	  });

	  //添加
	  $('#add').on('click', function(){
	  	layer.open({
	  		type:2,
	  		title:'添加人员信息',
	  		area:['500px', '400px'],
	  		content:'/admin/manager/add'
	  	});
	  });

	  //删除选中
	  $('#delall').on('click', function(){
	  	var ids = '';
	  	$(":input[name='ruleid']:checked").each(function(index, element){
	  		if (index == 0) {
	  			ids = $(element).val();
	  		}else{
	  			ids += ','+$(element).val();
	  		}
	  	});
	  	alert(ids);
	  	if (ids == '') {
	  		layer.alert('你没有选择被删除的人员信息', {icon:2, title:'警告'});
	  		return false;
	  	}
	  	layer.confirm('是否删除所有选中人员信息？', {icon:3, title:'删除确认'}, function(index){
	  		layer.close(index);
	  		$.post('/admin/manager/delete',{
	  			id:ids
	  		}, function(res){
	  			if (res.code == '1') {
	  				layer.msg(res.msg, {icon:1, time:1000}, function(){
	  					location.reload();
	  				});
	  			}else{
	  				layer.msg(res.msg, {icon:2, time:1000});
	  			}
	  		});
	  	})
	  });

	  //修改
	  $('.admin-update').on('click', function(){
	  	var id = $(this).closest('tr').attr('ruleid');
	  	layer.open({
	  		type:2,
	  		title:'修改人员信息',
	  		area:['500px', '400px'],
	  		content:'/admin/manager/update/id/'+id
	  	});
	  });

	  //删除
	  $('.admin-del').on('click', function(){
	  	var id = $(this).closest('tr').attr('ruleid');
	  	layer.confirm('是否删除该人员信息？', {icon:3, title:'删除确认'}, function(index){
	  		layer.close(index);
	  		$.post('/admin/manager/delete',{
	  			id:id
	  		}, function(res){
	  			if (res.code == '1') {
	  				layer.msg(res.msg, {icon:1, time:1000}, function(){
	  					location.reload();
	  				});
	  			}else{
	  				layer.msg(res.msg, {icon:2, time:1000});
	  			}
	  		});
	  	});
	  });
	  //角色分配
	  $('.admin-group').on('click', function(){
	  	var id = $(this).closest('tr').attr('ruleid');
	  	layer.open({
	  		type:2,
	  		title:'角色分配',
	  		area:['500px', '400px'],
	  		content:'/admin/manager/group/id/'+id
	  	});
	  });

	});
});