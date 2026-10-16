@extends('layouts.app')

@section('title', 'Aperçu fiche - {{ $employee->nom_complet }}')
@section('page-title', 'Aperçu fiche employé')

@section('content')
<div style="max-width:960px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <a href="{{ url()->previous() }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;">&larr; Retour</a>

        <div style="display:flex;align-items:center;gap:10px;">
            <div style="font-size:13px;color:rgba(255,255,255,.5);">{{ $employee->nom_complet }}</div>
            <a href="{{ route('employees.pdf.download', $employee) }}"
               style="display:inline-flex;align-items:center;gap:6px;padding:9px 20px;background:#2563eb;border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;transition:background .2s;"
               onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Télécharger
            </a>
        </div>
    </div>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <iframe src="{{ route('employees.pdf.stream', $employee) }}"
                style="width:100%;height:80vh;border:none;display:block;"
                title="Fiche employé {{ $employee->nom_complet }}">
            <p style="padding:20px;color:rgba(255,255,255,.5);">
                Votre navigateur ne supporte pas l'affichage PDF.
                <a href="{{ route('employees.pdf.download', $employee) }}" style="color:#60a5fa;">Télécharger directement</a>.
            </p>
        </iframe>
    </div>
</div>
@endsection
