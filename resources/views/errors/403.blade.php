@extends('layouts.app')

@section('title', 'Accès refusé - MyGest')
@section('page-title', 'Accès refusé')

@section('content')
<div style="display:flex;align-items:center;justify-content:center;min-height:60vh;">
    <div style="text-align:center;max-width:420px;">

        <div style="width:72px;height:72px;border-radius:50%;background:rgba(248,113,113,.1);border:1.5px solid rgba(248,113,113,.25);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/>
                <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
            </svg>
        </div>

        <h2 style="font-size:22px;font-weight:700;color:#f1f5f9;margin:0 0 12px;">Accès refusé</h2>
        <p style="font-size:14px;color:rgba(255,255,255,.45);margin:0 0 32px;line-height:1.6;">
            Vous n'avez pas les droits nécessaires pour accéder à cette page.<br>
            Contactez votre administrateur si vous pensez qu'il s'agit d'une erreur.
        </p>

        <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
            <button onclick="history.back()" style="padding:10px 22px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:rgba(255,255,255,.6);font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;">
                &larr; Retour
            </button>
            <a href="{{ url('/dashboard') }}" style="padding:10px 22px;background:#2563eb;border-radius:10px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;display:inline-block;">
                Tableau de bord
            </a>
        </div>

    </div>
</div>
@endsection
