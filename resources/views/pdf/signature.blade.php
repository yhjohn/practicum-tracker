@if (is_file($signaturePath = resource_path('templates/signature.png')))
    <img src="data:image/png;base64,{{ base64_encode(file_get_contents($signaturePath)) }}" style="height: 60px;">
@endif
