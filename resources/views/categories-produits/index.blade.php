@extends('layouts.app')

@section('title', 'Catégories de produits - MyGest')
@section('page-title', 'Catégories de produits')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des catégories</h2>
        <a href="{{ route('categories-produits.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle catégorie</a>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
        @forelse($categories as $categorie)
            <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px;transition:all .2s;" onmouseover="this.style.borderColor='rgba(59,130,246,.3)';this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)';this.style.background='rgba(255,255,255,.03)'">
                <div style="margin-bottom:10px;">
                    <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ $categorie->nom }}</div>
                    @if($categorie->description)
                        <div style="font-size:12px;color:rgba(255,255,255,.4);margin-top:3px;">{{ $categorie->description }}</div>
                    @endif
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,.35);margin-bottom:14px;">
                    {{ $categorie->produits_count }} produit(s)
                </div>
                <div style="display:flex;gap:8px;justify-content:flex-end;">
                    <a href="{{ route('categories-produits.edit', $categorie) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                    <form method="POST" action="{{ route('categories-produits.destroy', $categorie) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="grid-column:1/-1;padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
                <div style="font-size:14px;color:rgba(255,255,255,.3);">Aucune catégorie trouvée.</div>
            </div>
        @endforelse
    </div>
</div>
@endsection
