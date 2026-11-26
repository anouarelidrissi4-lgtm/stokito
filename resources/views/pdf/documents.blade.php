{{-- several documents in one PDF, one per page (e.g. the delivery notes selected in the list), 2026-11-27 --}}
<!DOCTYPE html>
<html>
<head>
@include('pdf._head')
</head>
<body>
@include('pdf._footer')
@foreach ($docs as $doc)
    <div @if (! $loop->last) style="page-break-after: always;" @endif>
        @include('pdf._body', $doc)
    </div>
@endforeach
</body>
</html>
