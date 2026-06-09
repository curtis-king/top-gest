@extends('layouts.app')

@section('title', 'Dashboard - MyGest')

@section('page-title', 'Tableau de bord')

@section('content')
    <div class="stats-row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Clients</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">1 248</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">Relation client active</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Revenu</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">€ 89 450</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">Ce mois-ci</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Projets</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">24</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">En cours</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Alertes</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">3</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">Actions requises</p>
        </article>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(340px,1fr));gap:16px;">
        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Activité récente</h2>
                <a href="#" style="font-size:13px;color:#60a5fa;text-decoration:none;font-weight:500;">Voir tout</a>
            </div>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;">
                <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                    <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;flex-shrink:0;"></span>
                    Nouvelle facture générée pour Client A
                </li>
                <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                    <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;flex-shrink:0;"></span>
                    5 nouvelles demandes validées
                </li>
                <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                    <span style="width:7px;height:7px;border-radius:50%;background:#facc15;flex-shrink:0;"></span>
                    Mise à jour du profil équipe marketing
                </li>
                <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                    <span style="width:7px;height:7px;border-radius:50%;background:#f87171;flex-shrink:0;"></span>
                    Alerte de dépassement de budget
                </li>
            </ul>
        </section>

        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Vue rapide</h2>
                <a href="#" style="font-size:13px;color:#60a5fa;text-decoration:none;font-weight:500;">Exporter</a>
            </div>
            <div style="background:rgba(255,255,255,.02);border-radius:10px;padding:32px 24px;text-align:center;border:1px dashed rgba(255,255,255,.05);">
                <div style="font-size:13px;color:rgba(255,255,255,.3);">Graphique des revenus</div>
            </div>
        </section>
    </div>
@endsection
