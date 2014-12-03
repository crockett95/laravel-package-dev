<script src="Duo-Web-v1.bundled.min.js"></script>
<input type="hidden" id="duo_host" value="{{ $host }}">
<input type="hidden" id="duo_sig_request" value="{{ $sig_request }}">
<script src="Duo-Init.js"></script>

{{ Form::start() }}
{{ Form::end() }}

<iframe id="duo_iframe" width="620" height="500" frameborder="0" allowtransparency="true" style="background: transparent;"></iframe>
