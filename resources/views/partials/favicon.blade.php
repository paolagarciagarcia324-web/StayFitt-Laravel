<link rel="icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}" sizes="any">
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) ?: time() }}" sizes="192x192">
<link rel="icon" type="image/png" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}" sizes="32x32">
<link rel="icon" type="image/png" href="{{ asset('favicon-16x16.png') }}?v={{ @filemtime(public_path('favicon-16x16.png')) ?: time() }}" sizes="16x16">
<link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}?v={{ @filemtime(public_path('apple-touch-icon.png')) ?: time() }}">
