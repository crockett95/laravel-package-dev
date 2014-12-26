<script src="Duo-Web-v1.bundled.min.js"></script>
<input type="hidden" id="duo_host" value="{{ $host }}">
<input type="hidden" id="post_action" value="{{ $endpoint }}">
<input type="hidden" id="duo_sig_request" value="{{ $sig_request }}">
<script>
    Duo.init({
        'host': document.getElementById("duo_host").value,
        'post_action':'index.php',
        'sig_request': document.getElementById("duo_sig_request").value
    });
</script>

{{ Form::open(['id' => 'duo_form']) }}
{{ Form::close() }}

<iframe id="duo_iframe" width="620" height="500" frameborder="0" allowtransparency="true" style="background: transparent;"></iframe>
