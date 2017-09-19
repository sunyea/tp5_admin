<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>错误</title>
    <link rel="stylesheet" href="/static/css/global.css">
    <link rel="stylesheet" href="/static/plugin/iconadmin/iconfont.css">
    <style>
    .wrong{margin:20px auto;color:#FF5722;width: 100%;text-align: center;}
    .wrong i{font-size: 50px;}
    .msg{margin:0 auto;line-height: 50px;font-size: 20px;color: #2F4056;width:100%;text-align: center;}
    .jump{margin:10px auto;width: 100%;line-height: 30px;font-size: 15px;color: #5FB878;text-align: center;}
    </style>
</head>
<body>
    <div class="wrong"><i class="iconadmin iconadmin-roundclosefill"></i></div>
    <div class="msg"><?php echo(strip_tags($msg));?></div>
    <div class="jump">
        页面将在<b id="wait"><?php echo($wait);?></b>秒后自动跳转，如果您的浏览器不支持自动跳转，请点击 <a id="href" href="<?php echo($url);?>">跳转</a> 连接
    </div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').href;
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    window.top.location.href = href;
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>
