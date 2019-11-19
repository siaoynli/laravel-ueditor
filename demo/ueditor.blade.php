<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>UEditor Demo</title>
    @include('vendor.ueditor.assets')
</head>
<body>

<script id="container" name="content" type="text/plain"></script>
<script>
    var ue = UE.getEditor('container',{
        initialFrameHeight: 480
    });
</script>

</body>
</html>
