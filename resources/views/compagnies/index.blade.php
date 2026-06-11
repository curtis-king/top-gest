@extends('layouts.app')

@section('title', 'Compagnies - MyGest')

@section('page-title', 'Compagnies')

@section('content')
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des compagnies</h2>
            <a href="{{ route('compagnies.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle compagnie</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">#</th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">Nom</th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">Slogan</th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">Forme juridique</th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">NUI</th>
                        <th style="padding:12px 16px;text-align:center;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($compagnies as $compagnie)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;">
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $compagnie->id }}</td>
                            <td style="padding:12px 16px;color:#f1f5f9;font-weight:500;">{{ $compagnie->name }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $compagnie->slogan ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $compagnie->forme_juridique ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $compagnie->nui ?? '—' }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <div style="display:flex;gap:8px;justify-content:center;">
                                    <a href="{{ route('compagnies.show', $compagnie) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;">Voir</a>
                                    <a href="{{ route('compagnies.edit', $compagnie) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;">Modifier</a>
                                    <form method="POST" action="{{ route('compagnies.destroy', $compagnie) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:none;border:none;color:#f87171;text-decoration:none;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="padding:32px 16px;text-align:center;color:rgba(255,255,255,.3);font-size:14px;">Aucune compagnie trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $compagnies->links() }}
        </div>
    </div>
@endsection
