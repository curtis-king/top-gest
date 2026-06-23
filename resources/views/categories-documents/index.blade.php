@extends('layouts.app')

@section('title', 'Catégories de documents - MyGest')
@section('page-title', 'Catégories de documents')

@section('content')
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <a href="{{ route('documents.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;">&larr; Retour aux archives</a>
    <a href="{{ route('categories-documents.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle catégorie</a>
</div>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;margin-bottom:20px;">{{ session('success') }}</div>
@endif

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead>
            <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                <th style="text-align:left;padding:12px 16px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Nom</th>
                <th style="text-align:left;padding:12px 16px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Description</th>
                <th style="text-align:center;padding:12px 16px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Documents</th>
                <th style="width:80px;"></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
                <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                    <td style="padding:12px 16px;color:#f1f5f9;font-weight:500;">{{ $cat->nom }}</td>
                    <td style="padding:12px 16px;color:rgba(255,255,255,.45);">{{ $cat->description ?? '—' }}</td>
                    <td style="padding:12px 16px;text-align:center;color:rgba(255,255,255,.5);">{{ $cat->documents_count }}</td>
                    <td style="padding:12px 16px;text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ route('categories-documents.edit', $cat) }}" style="padding:5px 10px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.15);border-radius:6px;color:#60a5fa;text-decoration:none;font-size:12px;">Modifier</a>
                            <form method="POST" action="{{ route('categories-documents.destroy', $cat) }}" onsubmit="return confirm('Supprimer cette catégorie ?');">
                                @csrf @method('DELETE')
                                <button type="submit" style="padding:5px 10px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.15);border-radius:6px;color:#f87171;font-size:12px;cursor:pointer;font-family:inherit;">&times;</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:32px;text-align:center;color:rgba(255,255,255,.3);">Aucune catégorie.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
