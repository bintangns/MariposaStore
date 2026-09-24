@extends('layouts.app')
@section('title', 'Koleksi Nickname')

@section('content')
<div style="max-width:48rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2rem;font-weight:700;color:white;margin-bottom:0.5rem;">Koleksi Nickname</h1>
    <p style="color:#94a3b8;margin-bottom:2rem;">Semua nickname cosmetics yang pernah kamu beli tersimpan di sini. Ganti-ganti kapan aja, gratis, gak perlu beli ulang.</p>

    @if(session('success'))
    <div style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;margin-bottom:1.5rem;font-size:0.875rem;">{{ session('error') }}</div>
    @endif

    @if(!$username)
    <div style="text-align:center;padding:3rem;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:0.75rem;color:#94a3b8;">
        <p style="margin-bottom:1rem;">Verifikasi username Minecraft kamu dulu buat lihat koleksi nickname.</p>
        <button onclick="mpOpenVerifyModal()" class="btn-primary">Masukkan Username</button>
    </div>
    @elseif($nicknames->isEmpty())
    <div style="text-align:center;padding:3rem;color:#64748b;">
        <p style="margin-bottom:0.75rem;">Belum ada nickname yang kamu beli.</p>
        <a href="{{ route('store') }}" style="color:#a78bfa;text-decoration:none;">Lihat Store →</a>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
        @foreach($nicknames as $n)
        <div style="background:rgba(255,255,255,0.03);border:1px solid {{ $n->is_active ? 'rgba(167,139,250,0.4)' : 'rgba(255,255,255,0.07)' }};border-radius:0.75rem;padding:1.25rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div class="mp-nick-preview" data-value="{{ $n->value }}" style="font-family:'JetBrains Mono',monospace;font-size:1.0625rem;margin-bottom:0.25rem;"></div>
                <div style="font-size:0.75rem;color:#64748b;">
                    {{ $n->label }} · {{ $n->type === 'gradient' ? 'Gradient' : 'Custom' }} · dibeli {{ $n->created_at->format('d M Y') }}
                </div>
            </div>
            <div>
                @if($n->is_active)
                <span style="background:rgba(167,139,250,0.15);color:#c4b5fd;font-size:0.75rem;padding:0.5rem 0.875rem;border-radius:9999px;border:1px solid rgba(167,139,250,0.3);white-space:nowrap;">✓ Terpasang</span>
                @else
                <form action="{{ route('nicknames.equip', $n) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-primary" style="font-size:0.8125rem;padding:0.5rem 1.25rem;">Pasang</button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@push('scripts')
<script>
const MC_COLORS_INV = {
    '0':'#000000','1':'#0000AA','2':'#00AA00','3':'#00AAAA','4':'#AA0000','5':'#AA00AA',
    '6':'#FFAA00','7':'#AAAAAA','8':'#555555','9':'#5555FF','a':'#55FF55','b':'#55FFFF',
    'c':'#FF5555','d':'#FF55FF','e':'#FFFF55','f':'#FFFFFF'
};

// Parser gabungan: kode warna lama (&0-&f, &k-&o, &r) DAN hex gradient (&#RRGGBB)
function mpParseAnyNickname(raw) {
    const segments = [];
    let current = { text: '', color: '#e2e8f0', bold: false, italic: false, underline: false, strike: false };
    let i = 0;
    while (i < raw.length) {
        if (raw[i] === '&') {
            const hexMatch = raw.slice(i).match(/^&#([0-9a-fA-F]{6})/);
            if (hexMatch) {
                if (current.text) segments.push(current);
                current = { text: '', color: '#' + hexMatch[1], bold: false, italic: false, underline: false, strike: false };
                i += hexMatch[0].length;
                continue;
            }
            const code = (raw[i + 1] || '').toLowerCase();
            if (MC_COLORS_INV[code] || 'klmnor'.includes(code)) {
                if (current.text) segments.push(current);
                if (code === 'r') {
                    current = { text: '', color: '#e2e8f0', bold: false, italic: false, underline: false, strike: false };
                } else {
                    current = { ...current, text: '' };
                    if (MC_COLORS_INV[code]) {
                        current.color = MC_COLORS_INV[code];
                        current.bold = current.italic = current.underline = current.strike = false;
                    } else if (code === 'l') current.bold = true;
                    else if (code === 'm') current.strike = true;
                    else if (code === 'n') current.underline = true;
                    else if (code === 'o') current.italic = true;
                }
                i += 2;
                continue;
            }
        }
        current.text += raw[i];
        i++;
    }
    if (current.text) segments.push(current);
    return segments;
}

document.querySelectorAll('.mp-nick-preview').forEach(el => {
    mpParseAnyNickname(el.dataset.value || '').forEach(seg => {
        const span = document.createElement('span');
        span.style.color = seg.color;
        if (seg.bold) span.style.fontWeight = '700';
        if (seg.italic) span.style.fontStyle = 'italic';
        const deco = [];
        if (seg.underline) deco.push('underline');
        if (seg.strike) deco.push('line-through');
        if (deco.length) span.style.textDecoration = deco.join(' ');
        span.textContent = seg.text;
        el.appendChild(span);
    });
});
</script>
@endpush
@endsection
