@extends('layouts.app')

@section('title', 'Contacts - MyGest')

@section('page-title', 'Contacts')

@section('content')
    <div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des contacts</h2>
            <a href="{{ route('contacts.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouveau contact</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
            <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#60a5fa;">{{ $stats['total'] }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Contacts</div>
            </div>
            <div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.15);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#4ade80;">{{ $stats['client'] }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Clients</div>
            </div>
            <div style="background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.15);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#facc15;">{{ $stats['fournisseur'] }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Fournisseurs</div>
            </div>
            <div style="background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.15);border-radius:12px;padding:16px;text-align:center;">
                <div style="font-size:24px;font-weight:700;color:#94a3b8;">{{ $stats['autre'] }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Autres</div>
            </div>
        </div>

        <form method="GET" action="{{ route('contacts.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;position:relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom, email ou téléphone..." style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            </div>
            <select name="sort" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }} style="color:#000;">Plus récents</option>
                <option value="raison_social" {{ request('sort') == 'raison_social' ? 'selected' : '' }} style="color:#000;">Raison sociale</option>
                <option value="nom_complet" {{ request('sort') == 'nom_complet' ? 'selected' : '' }} style="color:#000;">Nom complet</option>
                <option value="type_contact" {{ request('sort') == 'type_contact' ? 'selected' : '' }} style="color:#000;">Type</option>
                <option value="id" {{ request('sort') == 'id' ? 'selected' : '' }} style="color:#000;">ID</option>
            </select>
            <select name="direction" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }} style="color:#000;">Descendant</option>
                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }} style="color:#000;">Ascendant</option>
            </select>
            <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
            @if(request()->anyFilled(['search', 'sort', 'direction']))
                <a href="{{ route('contacts.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
            @endif
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
            @forelse($contacts as $contact)
                @php
                    $tv = $contact->type_contact?->value ?? 'autre';
                    $tc = ['fournisseur'=>'#f59e0b','client'=>'#3b82f6','autre'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
                    $displayName = $contact->raison_social ?? $contact->nom_complet ?? 'Sans nom';
                @endphp
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px;transition:all .2s;position:relative;" onmouseover="this.style.borderColor='rgba(59,130,246,.3)';this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)';this.style.background='rgba(255,255,255,.03)'">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:42px;height:42px;border-radius:42px;background:linear-gradient(135deg,#6366f1,#4f46e5);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:600;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($displayName, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ $displayName }}</div>
                                <div style="font-size:12px;color:rgba(255,255,255,.35);">#{{ $contact->id }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('contacts.show', $contact) }}" style="width:32px;height:32px;border-radius:8px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.15);display:flex;align-items:center;justify-content:center;color:#60a5fa;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='rgba(96,165,250,.2)'" onmouseout="this.style.background='rgba(96,165,250,.1)'" title="Voir">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </div>

                    <div style="padding:12px 0;border-top:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px;">
                            <div>
                                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Type</div>
                                <div><span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span></div>
                            </div>
                            <div>
                                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Téléphone</div>
                                <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $contact->telephone }}</div>
                            </div>
                        </div>
                        <div>
                            <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Email</div>
                            <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $contact->adresse_email }}</div>
                        </div>
                        @if($contact->secteur_activites || $contact->agence)
                            <div style="margin-top:6px;display:flex;gap:12px;">
                                @if($contact->secteur_activites)
                                <div>
                                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Secteur</div>
                                    <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $contact->secteur_activites }}</div>
                                </div>
                                @endif
                                @if($contact->agence)
                                <div>
                                    <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Agence</div>
                                    <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $contact->agence->name_agence }}</div>
                                </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <div style="padding-top:10px;display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('contacts.edit', $contact) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                        <form method="POST" action="{{ route('contacts.destroy', $contact) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;text-decoration:none;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
                    <div style="font-size:14px;color:rgba(255,255,255,.3);">Aucun contact trouvé.</div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:24px;">
            {{ $contacts->links() }}
        </div>
    </div>
@endsection
