@extends('layouts.app')

@section('title', 'Catégories de dépenses - MyGest')

@section('page-title', 'Catégories de dépenses')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des catégories</h2>
        <a href="{{ route('categories-depenses.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle catégorie</a>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Libellé</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Compte de charge par défaut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Nb dépenses</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $categorie)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#f1f5f9;">{{ $categorie->libelle }}</td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">
                            @if($categorie->compte)
                                {{ $categorie->compte->numero_compte }} - {{ $categorie->compte->libelle }}
                            @else
                                <span style="color:rgba(255,255,255,.3);">—</span>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.45);">{{ $categorie->depenses_count }}</td>
                        <td style="padding:12px 16px;text-align:right;">
                            <a href="{{ route('categories-depenses.edit', $categorie) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                            <form method="POST" action="{{ route('categories-depenses.destroy', $categorie) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;text-decoration:none;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucune catégorie trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
