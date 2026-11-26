{{-- shared <head> of the PDF documents (2026-11-27) --}}
<meta charset="utf-8">
@php $pc = $societe->pdfColors(); /* accent chosen in Ma Société (2026-11-26) */ @endphp
<style>
    /* Classic Moroccan invoice layout, accent colour chosen by the company (2026-11-26): company left, logo centre, client right,
       coloured table header, totals attached under the table, amount in words, legal footer. dompdf: tables only. */
    @page { margin: 26px 32px 96px; }
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #111; }
    table { border-collapse: collapse; }
    .topbar { height: 5px; background: {{ $pc['accent'] }}; margin-bottom: 14px; }
    table.head { width: 100%; }
    table.head td { vertical-align: top; padding: 0; }
    .co-name { margin: 0 0 3px; font-size: 15px; font-weight: bold; text-transform: uppercase; letter-spacing: .3px; }
    .co-act { margin: 0 0 4px; font-size: 10.5px; font-weight: bold; }
    .co p { margin: 1px 0; font-size: 10px; }
    .logo { text-align: center; }
    .logo img { max-height: 105px; max-width: 175px; }
    .place { text-align: right; font-size: 10.5px; margin-bottom: 12px; }
    table.meta { width: 100%; }
    table.meta td { padding: 2px 0; font-size: 10.5px; vertical-align: top; }
    table.meta td.k { width: 72px; padding-right: 6px; color: #555; white-space: nowrap; }
    table.meta td.v { font-weight: bold; }
    .title { text-align: center; margin: 20px 0 14px; }
    .title span { font-size: 19px; font-weight: bold; letter-spacing: 3px; text-transform: uppercase; border-bottom: 4px solid {{ $pc['accent'] }}; padding: 0 8px 3px; }
    .objet { margin-bottom: 8px; font-size: 10.5px; }
    .objet p { margin: 1px 0; }
    table.items { width: 100%; }
    table.items th { background: {{ $pc['accent'] }}; color: {{ $pc['on'] }}; font-size: 10px; font-weight: bold; padding: 6px 5px; border: 1px solid #111; text-align: center; }
    table.items td { border: 1px solid #111; padding: 5px 6px; font-size: 10.5px; }
    table.items td.c { text-align: center; }
    table.items td.r { text-align: right; white-space: nowrap; }
    table.items td.blank { border: 0; }
    table.items tr.t td.l { background: {{ $pc['tint'] }}; font-size: 10px; font-weight: bold; text-align: center; text-transform: uppercase; }
    table.items tr.t td.r { font-weight: bold; }
    table.items tr.g td.l, table.items tr.g td.r { background: {{ $pc['accent'] }}; color: {{ $pc['on'] }}; font-size: 11.5px; }
    .words { margin-top: 14px; font-size: 11px; }
    .words b { font-weight: bold; }
    .small { margin-top: 8px; font-size: 9.5px; color: #333; }
    .sign { width: 230px; height: 105px; border: 1px dashed #a1a1aa; vertical-align: top; text-align: center; padding-top: 6px; font-size: 9px; color: #555; }
    .footer { position: fixed; bottom: -76px; left: 0; right: 0; text-align: center; font-size: 8.5px; color: #111; border-top: 2px solid {{ $pc['accent'] }}; padding-top: 5px; }
    .footer p { margin: 1px 0; }
    .footer .l1 { font-weight: bold; font-size: 9px; }
    .footer .gen { margin-top: 3px; color: #a1a1aa; font-size: 7px; }
</style>
