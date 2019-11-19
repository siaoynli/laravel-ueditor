<script type="text/javascript" src="{{ asset('static/ueditor/ueditor.config.js') }}"></script>
<!-- 编辑器源码文件 -->
<script type="text/javascript" src="{{ asset('static/ueditor/ueditor.all.min.js') }}"></script>
<script>
    window.UEDITOR_URL = "{{ url('/static/ueditor') }}/";
    window.UEDITOR_CONFIG.serverUrl = '{{ url(config('ueditor.route.uri')) }}'
</script>