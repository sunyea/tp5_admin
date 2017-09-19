<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <title>提醒</title>
    <link rel="stylesheet" href="/static/css/global.css">
    <link rel="stylesheet" href="/static/plugin/iconadmin/iconfont.css">
    <style>
    .warning{margin:20px auto;color:#F7B824;width: 100%;text-align: center;}
    .warning i{font-size: 50px;}
    .msg{margin:0 auto;line-height: 50px;font-size: 20px;color: #2F4056;width:100%;text-align: center;}
    </style>
</head>
<body>
  <div class="warning"><i class="iconadmin iconadmin-warnfill"></i></div>
  <div class="msg"><?php echo(strip_tags($msg));?></div>
</body>
</html>
