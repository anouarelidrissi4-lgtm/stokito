<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ $sujet }}</title></head>
<body style="margin:0; padding:0; background:#f4f2ec; font-family:Arial, Helvetica, sans-serif; color:#111;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f2ec; padding:24px 12px;">
<tr><td align="center">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border:2px solid #111;">
<tr><td style="height:6px; background:#ffcc00; border-bottom:2px solid #111;"></td></tr>
<tr><td style="padding:20px 24px 4px; font-size:18px; font-weight:bold; text-transform:uppercase; letter-spacing:.5px;">{{ $societeName }}</td></tr>
<tr><td style="padding:12px 24px 24px; font-size:15px; line-height:1.6;">{!! nl2br(e($corps)) !!}</td></tr>
@if ($filename)
<tr><td style="padding:0 24px 20px; font-size:13px; color:#555;">Pièce jointe : {{ $filename }}</td></tr>
@endif
</table>
<p style="font-size:11px; color:#777; margin:12px 0 0;">Envoyé avec Stokito · logiciel de devis, factures et stock</p>
</td></tr>
</table>
</body>
</html>
