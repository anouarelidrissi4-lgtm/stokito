{{-- loading slips, one per page (2026-11-27) --}}
<!DOCTYPE html>
<html>
<head>
@include('pdf._head')
</head>
<body>
@include('pdf._footer')
@foreach ($bcs as $bc)
    <div @if (! $loop->last) style="page-break-after: always;" @endif>
        @include('pdf._chargement_body', ['bc' => $bc])
    </div>
@endforeach
</body>
</html>
