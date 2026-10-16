@if ($paginator->hasPages())
<nav style="display:flex;align-items:center;justify-content:space-between;gap:16px;margin-top:20px;flex-wrap:wrap;">

    {{-- Compteur --}}
    <p style="font-size:12px;color:rgba(255,255,255,.35);margin:0;">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}
        sur {{ $paginator->total() }} résultats
    </p>

    {{-- Boutons --}}
    <div style="display:flex;align-items:center;gap:4px;">

        {{-- Précédent --}}
        @if ($paginator->onFirstPage())
            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.06);color:rgba(255,255,255,.2);cursor:not-allowed;">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.6);text-decoration:none;transition:all .15s;" onmouseover="this.style.borderColor='rgba(59,130,246,.5)';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.6)'">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </a>
        @endif

        {{-- Numéros de page --}}
        @foreach ($elements as $element)
            @if (is_string($element))
                <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;font-size:12px;color:rgba(255,255,255,.3);">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;background:#2563eb;font-size:12px;font-weight:600;color:#fff;">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.08);font-size:12px;color:rgba(255,255,255,.5);text-decoration:none;transition:all .15s;" onmouseover="this.style.borderColor='rgba(59,130,246,.4)';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Suivant --}}
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.08);color:rgba(255,255,255,.6);text-decoration:none;transition:all .15s;" onmouseover="this.style.borderColor='rgba(59,130,246,.5)';this.style.color='#fff'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.6)'">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </a>
        @else
            <span style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:8px;border:1px solid rgba(255,255,255,.06);color:rgba(255,255,255,.2);cursor:not-allowed;">
                <svg width="14" height="14" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
            </span>
        @endif

    </div>
</nav>
@endif
