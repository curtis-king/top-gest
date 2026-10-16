@extends('layouts.app')

@section('title', $compagnie->name . ' - MyGest')

@section('page-title', $compagnie->name)

@section('content')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;">
            <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:20px;">Informations</h2>

            <div style="display:grid;gap:16px;">
                <div>
                    <span style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.4px;">Nom</span>
                    <p style="font-size:14px;color:#f1f5f9;margin-top:4px;">{{ $compagnie->name }}</p>
                </div>
                <div>
                    <span style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.4px;">Slogan</span>
                    <p style="font-size:14px;color:#f1f5f9;margin-top:4px;">{{ $compagnie->slogan ?? '—' }}</p>
                </div>
                <div>
                    <span style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.4px;">Forme juridique</span>
                    <p style="font-size:14px;color:#f1f5f9;margin-top:4px;">{{ $compagnie->forme_juridique ?? '—' }}</p>
                </div>
                <div>
                    <span style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.4px;">NUI</span>
                    <p style="font-size:14px;color:#f1f5f9;margin-top:4px;">{{ $compagnie->nui ?? '—' }}</p>
                </div>
                <div>
                    <span style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.4px;">RCCM</span>
                    <p style="font-size:14px;color:#f1f5f9;margin-top:4px;">{{ $compagnie->rccm ?? '—' }}</p>
                </div>
            </div>
        </div>

        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;">
            <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:20px;">Logo</h2>
            @if($compagnie->logo)
                <img src="{{ Storage::url($compagnie->logo) }}" alt="{{ $compagnie->name }}" style="max-width:200px;max-height:200px;border-radius:10px;border:1px solid rgba(255,255,255,.06);">
            @else
                <div style="width:120px;height:120px;border-radius:10px;background:rgba(255,255,255,.04);border:1px dashed rgba(255,255,255,.08);display:flex;align-items:center;justify-content:center;font-size:12px;color:rgba(255,255,255,.25);">Aucun logo</div>
            @endif
        </div>
    </div>

    @if($compagnie->agences->isNotEmpty())
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;margin-top:20px;">
            <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:16px;">Agences ({{ $compagnie->agences->count() }})</h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:12px;">
                @foreach($compagnie->agences as $agence)
                    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:16px;">
                        <strong style="font-size:14px;color:#f1f5f9;">{{ $agence->name_agence }}</strong>
                        <p style="font-size:12px;color:rgba(255,255,255,.45);margin-top:4px;">{{ $agence->ville ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endsection
