<?php

namespace App\Support;

use App\Models\Agence;
use App\Models\Compagnie;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Génère les rapports financiers PDF avec une présentation commune (vue parente pdf.rapport) :
 * en-tête société (logo, NIU, RCCM), titre, période, agence, pied de page avec date d'édition,
 * auteur et pagination.
 */
class RapportPdf
{
    public static function generer(
        string $vue,
        array $donnees,
        string $titre,
        string $fichier,
        ?string $periode = null,
        ?int $agenceId = null,
        bool $paysage = false,
    ): Response {
        if (!$agenceId && AgencyContext::isRestricted()) {
            $agenceId = AgencyContext::agenceId();
        }
        $agence = $agenceId ? Agence::with('compagnie')->find($agenceId) : null;
        $compagnie = $agence?->compagnie ?? Compagnie::orderBy('id')->first();

        $entete = [
            'titre' => $titre,
            'periode' => $periode,
            'agence' => $agence?->name_agence,
            'compagnie' => $compagnie,
            'logo' => self::logo($compagnie),
            'edition' => now(),
            'auteur' => auth()->user()?->name,
        ];

        $pdf = Pdf::loadView($vue, $donnees + ['entete' => $entete])
            ->setPaper('A4', $paysage ? 'landscape' : 'portrait');

        self::paginer($pdf);

        return $pdf->stream($fichier);
    }

    /**
     * « Page X / Y » en bas à droite de chaque page. Le total de pages n'est connu qu'après
     * le rendu : la CSS (counter(pages)) n'est pas fiable avec DomPDF, on écrit donc sur le canevas.
     */
    private static function paginer(\Barryvdh\DomPDF\PDF $pdf): void
    {
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $police = $dompdf->getFontMetrics()->getFont('DejaVu Sans');

        $canvas->page_text(
            $canvas->get_width() - 80,
            $canvas->get_height() - 32,
            'Page {PAGE_NUM} / {PAGE_COUNT}',
            $police,
            6,
            [0.4, 0.4, 0.4]
        );
    }

    public static function periode(?string $debut, ?string $fin): string
    {
        $format = fn (?string $d) => $d ? Carbon::parse($d)->format('d/m/Y') : null;

        return match (true) {
            $debut && $fin => 'Du ' . $format($debut) . ' au ' . $format($fin),
            (bool) $debut => 'Depuis le ' . $format($debut),
            (bool) $fin => 'Jusqu\'au ' . $format($fin),
            default => 'Toutes périodes',
        };
    }

    /**
     * Logo embarqué en data URI : DomPDF n'a ainsi besoin ni d'accès réseau ni de chemin public.
     */
    private static function logo(?Compagnie $compagnie): ?string
    {
        if (!$compagnie?->logo || !Storage::disk('public')->exists($compagnie->logo)) {
            return null;
        }

        $chemin = Storage::disk('public')->path($compagnie->logo);
        $mime = mime_content_type($chemin) ?: 'image/png';

        return "data:{$mime};base64," . base64_encode(file_get_contents($chemin));
    }
}
