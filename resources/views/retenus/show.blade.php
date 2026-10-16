@extends('layouts.app')

@section('title', 'Détail retenue - MyGest')
@section('page-title', 'Détail retenue')

@section('content')
<div style="max-width:600px;">
    <a href="{{ route('retenus.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:24px;">
            <div>
                <div style="font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px;">Retenue #{{ $retenu->id }}</div>
                <div style="font-size:20px;font-weight:700;color:#f87171;">{{ number_format($retenu->montant, 0, ',', ' ') }} FCFA</div>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="{{ route('retenus.edit', $retenu) }}" style="padding:7px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:12px;font-weight:500;">Modifier</a>
                <form method="POST" action="{{ route('retenus.destroy', $retenu) }}" onsubmit="return confirm('Confirmer la suppression ?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="padding:7px 16px;background:rgba(248,113,113,.06);border:1px solid rgba(248,113,113,.15);border-radius:8px;color:#f87171;font-size:12px;font-weight:500;cursor:pointer;font-family:inherit;">Supprimer</button>
                </form>
            </div>
        </div>

        <div style="display:grid;gap:1px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.06);border-radius:10px;overflow:hidden;">
            <div style="display:grid;grid-template-columns:160px 1fr;background:rgba(255,255,255,.02);">
                <div style="padding:12px 16px;font-size:12px;font-weight:600;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;border-right:1px solid rgba(255,255,255,.04);">Employé</div>
                <div style="padding:12px 16px;font-size:13px;color:#f1f5f9;font-weight:500;">{{ $retenu->employee->nom_complet }}</div>
            </div>
            <div style="display:grid;grid-template-columns:160px 1fr;background:rgba(255,255,255,.01);">
                <div style="padding:12px 16px;font-size:12px;font-weight:600;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;border-right:1px solid rgba(255,255,255,.04);">Date</div>
                <div style="padding:12px 16px;font-size:13px;color:#f1f5f9;">{{ $retenu->date_retenu->format('d/m/Y') }}</div>
            </div>
            <div style="display:grid;grid-template-columns:160px 1fr;background:rgba(255,255,255,.02);">
                <div style="padding:12px 16px;font-size:12px;font-weight:600;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;border-right:1px solid rgba(255,255,255,.04);">Montant</div>
                <div style="padding:12px 16px;font-size:14px;font-weight:700;color:#f87171;">{{ number_format($retenu->montant, 0, ',', ' ') }} FCFA</div>
            </div>
            <div style="display:grid;grid-template-columns:160px 1fr;background:rgba(255,255,255,.01);">
                <div style="padding:12px 16px;font-size:12px;font-weight:600;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;border-right:1px solid rgba(255,255,255,.04);">Motif</div>
                <div style="padding:12px 16px;font-size:13px;color:#f1f5f9;white-space:pre-wrap;">{{ $retenu->motif }}</div>
            </div>
            <div style="display:grid;grid-template-columns:160px 1fr;background:rgba(255,255,255,.02);">
                <div style="padding:12px 16px;font-size:12px;font-weight:600;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;border-right:1px solid rgba(255,255,255,.04);">Créé le</div>
                <div style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.5);">{{ $retenu->created_at->format('d/m/Y à H:i') }}</div>
            </div>
        </div>

    </div>
</div>
@endsection
