$(function(){
	layui.use(['layer', 'form'], function(){
		var layer = layui.layer,
		form = layui.form();
		//验证字段
		form.verify({
			uname:[/^[\S]{4,20}$/, '账号必须为4-20位字符'],
			upwd:[/^[\S]{6,20}$/, '密码必须为6-20位字符'],
			captcha:[/^[\S]{4}$/, '验证码必须为4位字符']
		});

		form.on('submit(login)', function(data){
			$(data.elem).text('正在登录...');
			$(data.elem).attr('disabled', true);
			$.post('/admin/login/login',data.field,
				function(rt, status){
					if(rt.code == '1'){
						layer.msg(rt.msg, {icon:1, time:1000},function(){
							location.href='/admin';
						});						
					}else{
						if (rt.errorcode == '-1') {
							$('#captchaimg').attr('src','/captcha.html?time=' + Math.random());
						}
						layer.msg(rt.msg, {icon:2, time:1000},function(){
							$(data.elem).text('立即登录');
							$(data.elem).attr('disabled', false);
						});
					}
				}
			);
			return false;
		});
	});
})
