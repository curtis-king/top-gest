<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\CategorieDocument;
use App\Models\Document;
use App\Models\Employee;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\View\View;

class DocumentController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        // Mode explorateur : sans paramètre dossier → vue des dossiers (catégories)
        if (!$request->has('dossier')) {
            $agenceFilter = null;
            if (!auth()->user()->isAdmin()) {
                $userEmp = Employee::where('user_id', auth()->id())->first();
                $agenceFilter = $userEmp?->agence_id;
            }

            $dossiers = CategorieDocument::withCount(['documents' => function ($q) use ($agenceFilter) {
                if ($agenceFilter) {
                    $q->where('agence_id', $agenceFilter);
                }
            }])->orderBy('nom')->get();

            $nonClassesQuery = Document::whereNull('categorie_document_id');
            if ($agenceFilter) {
                $nonClassesQuery->where('agence_id', $agenceFilter);
            }
            $nonClassesCount = $nonClassesQuery->count();

            return view('documents.index', compact('dossiers', 'nonClassesCount'));
        }

        // Mode fichiers : affiche les documents du dossier sélectionné
        $dossierId = (int) $request->input('dossier');
        $dossier   = $dossierId > 0 ? CategorieDocument::findOrFail($dossierId) : null;

        $sortable = ['id', 'nom', 'date_document', 'type_fichier', 'created_at'];
        $query    = Document::with(['categorie', 'agence', 'user']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($dossierId > 0) {
            $query->where('categorie_document_id', $dossierId);
        } else {
            $query->whereNull('categorie_document_id');
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($agence_id = $request->input('agence_id')) {
            $query->where('agence_id', $agence_id);
        }

        $query     = $this->applySorting($query, $sortable);
        $documents = $query->paginate(15)->withQueryString();
        $sort      = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $agences   = Agence::pluck('name_agence', 'id');

        return view('documents.index', compact('documents', 'dossier', 'dossierId', 'sort', 'direction', 'agences'));
    }

    public function create(): View
    {
        $categories = CategorieDocument::orderBy('nom')->pluck('nom', 'id');
        $agences    = Agence::pluck('name_agence', 'id');
        $dossierId  = (int) request('dossier', 0);
        return view('documents.create', compact('categories', 'agences', 'dossierId'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom'                    => ['required', 'string', 'max:255'],
            'description'            => ['nullable', 'string'],
            'fichier'                => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,tif,tiff', 'max:20480'],
            'date_document'          => ['nullable', 'date'],
            'categorie_document_id'  => ['nullable', 'exists:categories_documents,id'],
            'agence_id'              => ['nullable', 'exists:agences,id'],
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents', 'private');

        Document::create([
            'nom'                   => $validated['nom'],
            'description'           => $validated['description'] ?? null,
            'fichier_path'          => $path,
            'type_fichier'          => $file->getMimeType(),
            'taille'                => $file->getSize(),
            'date_document'         => $validated['date_document'] ?? null,
            'categorie_document_id' => $validated['categorie_document_id'] ?? null,
            'agence_id'             => $validated['agence_id'] ?? null,
            'user_id'               => auth()->id(),
        ]);

        $dossierId = $validated['categorie_document_id'] ?? null;
        return redirect()->route('documents.index', $dossierId ? ['dossier' => $dossierId] : [])
            ->with('success', 'Document archivé avec succès.');
    }

    public function show(Document $document): View
    {
        $document->load(['categorie', 'agence', 'user']);
        return view('documents.show', compact('document'));
    }

    public function download(Document $document): BinaryFileResponse
    {
        abort_unless(Storage::disk('private')->exists($document->fichier_path), 404);

        return response()->download(
            Storage::disk('private')->path($document->fichier_path),
            $document->nom . '.' . pathinfo($document->fichier_path, PATHINFO_EXTENSION)
        );
    }

    public function preview(Document $document): BinaryFileResponse
    {
        abort_unless(Storage::disk('private')->exists($document->fichier_path), 404);

        $mime = $document->type_fichier ?? 'application/octet-stream';

        return response()->file(
            Storage::disk('private')->path($document->fichier_path),
            ['Content-Type' => $mime]
        );
    }

    public function destroy(Document $document): RedirectResponse
    {
        $dossierId = $document->categorie_document_id;
        Storage::disk('private')->delete($document->fichier_path);
        $document->delete();

        return redirect()->route('documents.index', ['dossier' => $dossierId ?? 0])
            ->with('success', 'Document supprimé.');
    }
}
