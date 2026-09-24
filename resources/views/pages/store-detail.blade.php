@extends('layouts.app')
@section('title', $product->name)

@php
    use App\Models\Setting;
    $maintenanceMode = Setting::isMaintenanceMode();
    $promoFormatted = fn (?int $price) => $price !== null
        ? 'Rp ' . number_format(Setting::applyPromo($price), 0, ',', '.')
        : '';

    $mcColors = [
        '0' => ['Black', '#000000'], '1' => ['Dark Blue', '#0000AA'], '2' => ['Dark Green', '#00AA00'], '3' => ['Dark Aqua', '#00AAAA'],
        '4' => ['Dark Red', '#AA0000'], '5' => ['Dark Purple', '#AA00AA'], '6' => ['Gold', '#FFAA00'], '7' => ['Gray', '#AAAAAA'],
        '8' => ['Dark Gray', '#555555'], '9' => ['Blue', '#5555FF'], 'a' => ['Green', '#55FF55'], 'b' => ['Aqua', '#55FFFF'],
        'c' => ['Red', '#FF5555'], 'd' => ['Light Purple', '#FF55FF'], 'e' => ['Yellow', '#FFFF55'], 'f' => ['White', '#FFFFFF'],
    ];
    $mcFormats = ['k' => 'Obfuscated', 'l' => 'Bold', 'm' => 'Strikethrough', 'n' => 'Underline', 'o' => 'Italic', 'r' => 'Reset'];
@endphp

@section('content')
<div style="max-width:56rem;margin:0 auto;padding:3rem 1.5rem;">

    <a href="{{ route('store') }}" style="display:inline-flex;align-items:center;gap:0.5rem;color:#94a3b8;text-decoration:none;font-size:0.875rem;margin-bottom:2rem;">
        ← Kembali ke Store
    </a>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2.5rem;align-items:start;" class="detail-grid">
        {{-- Product info --}}
        <div>
            <h1 style="font-size:2rem;font-weight:700;color:{{ $product->color ?? '#a78bfa' }};margin-bottom:1rem;">{{ $product->name }}</h1>
            <p style="color:#94a3b8;line-height:1.75;margin-bottom:1.5rem;">{{ $product->description }}</p>

            @if($product->features)
            <div style="margin-bottom:1.5rem;">
                <h3 style="font-size:0.875rem;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:0.75rem;">Yang kamu dapatkan</h3>
                <ul style="display:flex;flex-direction:column;gap:0.625rem;">
                    @foreach($product->features as $feature)
                    <li style="display:flex;align-items:center;gap:0.75rem;color:#e2e8f0;font-size:0.875rem;">
                        <span style="color:{{ $product->color ?? '#a78bfa' }};font-size:1rem;">✦</span> {{ $feature }}
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif
        </div>

        {{-- Checkout form --}}
        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:1rem;padding:1.75rem;position:sticky;top:5rem;">
            @php
                $firstPrice = $product->durations->first()->price ?? $product->price;
                $firstHasDiscount = Setting::isPromoActive() && Setting::applyPromo($firstPrice) < $firstPrice;
            @endphp
            @if($product->durations->isNotEmpty())
            <div id="detail-price-original" style="font-size:1rem;color:#64748b;text-decoration:line-through;{{ $firstHasDiscount ? '' : 'display:none;' }}">{{ $product->durations->first()->formatted_price }}</div>
            <div id="detail-price" style="font-size:2rem;font-weight:700;color:{{ $firstHasDiscount ? '#4ade80' : 'white' }};margin-bottom:0.25rem;">{{ $promoFormatted($firstPrice) }}</div>
            <div style="font-size:0.875rem;color:#64748b;margin-bottom:1rem;">Pilih durasi rank</div>
            <div style="display:flex;flex-wrap:wrap;gap:0.5rem;margin-bottom:1.5rem;">
                @foreach($product->durations as $i => $duration)
                <button type="button" class="duration-option{{ $i === 0 ? ' active' : '' }}"
                    data-id="{{ $duration->id }}" data-formatted="{{ $promoFormatted($duration->price) }}"
                    data-original="{{ $duration->formatted_price }}"
                    data-discounted="{{ Setting::isPromoActive() && Setting::applyPromo($duration->price) < $duration->price ? '1' : '0' }}">
                    {{ $duration->label }}
                </button>
                @endforeach
            </div>
            @else
            @if($firstHasDiscount)
            <div style="font-size:1rem;color:#64748b;text-decoration:line-through;">{{ $product->formatted_price }}</div>
            @endif
            <div style="font-size:2rem;font-weight:700;color:{{ $firstHasDiscount ? '#4ade80' : 'white' }};margin-bottom:0.25rem;">{{ $promoFormatted($firstPrice) }}</div>
            <div style="font-size:0.875rem;color:#64748b;margin-bottom:1.5rem;">Pembayaran sekali bayar</div>
            @endif

            @if($maintenanceMode)
            <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.3);border-radius:0.5rem;padding:1rem;text-align:center;">
                <div style="color:#fbbf24;font-size:1.25rem;margin-bottom:0.5rem;">🛠</div>
                <div style="color:#fcd34d;font-size:0.8125rem;">{{ Setting::maintenanceMessage() }}</div>
            </div>
            @else

            <form action="{{ route('checkout.create', $product) }}" method="POST" id="checkout-form">
                @csrf
                <input type="hidden" name="duration_id" id="duration-id-input" value="{{ $product->durations->first()->id ?? '' }}">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.875rem;color:#94a3b8;margin-bottom:0.5rem;">Username Minecraft</label>
                    <div id="checkout-platform-toggle" class="mp-platform-toggle">
                        <button type="button" class="mp-platform-btn active" data-platform="java">🖥️ Java</button>
                        <button type="button" class="mp-platform-btn" data-platform="bedrock">📱 Bedrock</button>
                    </div>
                    <div id="checkout-skin-preview" class="mp-skin-preview" style="display:none;margin-bottom:0.625rem;">
                        <div class="mp-skin-box">
                            <div class="mp-skin-spinner"><div></div></div>
                            <img class="mp-skin-img" alt="Skin preview">
                            <div class="mp-skin-bedrock-badge">📱</div>
                        </div>
                        <span class="mp-skin-username"></span>
                    </div>
                    <input type="text" id="username-field" placeholder="Masukkan username kamu"
                        style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.625rem 1rem;color:white;font-size:0.875rem;outline:none;box-sizing:border-box;font-family:inherit;"
                        required>
                    <input type="hidden" name="username" id="username-hidden">
                    <div id="username-check-result" style="margin-top:0.5rem;font-size:0.8rem;"></div>
                </div>

                <div id="verify-section" style="display:none;margin-bottom:1rem;padding:0.75rem;background:rgba(139,92,246,0.08);border:1px solid rgba(139,92,246,0.2);border-radius:0.5rem;">
                    <p style="font-size:0.8rem;color:#c4b5fd;margin-bottom:0.5rem;">Ketik command ini di server:</p>
                    <div id="verify-cmd" style="font-family:'JetBrains Mono',monospace;color:#a78bfa;font-size:0.8rem;background:rgba(0,0,0,0.2);padding:0.5rem;border-radius:0.25rem;"></div>
                    <p style="font-size:0.7rem;color:#64748b;margin-top:0.5rem;">Menunggu verifikasi...</p>
                </div>

                @if($product->requires_nickname && $product->nickname_type !== 'gradient')
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.875rem;color:#94a3b8;margin-bottom:0.5rem;">Nickname Custom</label>
                    <input type="text" name="nickname" id="nickname-input" maxlength="64" placeholder="Contoh: &aBinghem"
                        style="width:100%;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.625rem 1rem;color:white;font-size:0.875rem;outline:none;box-sizing:border-box;font-family:'JetBrains Mono',monospace;"
                        required>
                    <div style="display:flex;justify-content:space-between;margin-top:0.375rem;">
                        <div id="nickname-error" style="color:#f87171;font-size:0.7rem;"></div>
                        <div id="nickname-counter" style="color:#64748b;font-size:0.7rem;">0/32</div>
                    </div>

                    <div style="margin-top:0.625rem;padding:0.875rem 1rem;background:#0f0f16;border:1px solid rgba(255,255,255,0.08);border-radius:0.5rem;">
                        <div style="font-size:0.625rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Preview</div>
                        <div id="nickname-preview" style="font-family:'JetBrains Mono',monospace;font-size:1rem;min-height:1.4em;"><span style="color:#475569;">Preview nickname muncul di sini...</span></div>
                    </div>

                    <details style="margin-top:0.625rem;">
                        <summary style="cursor:pointer;font-size:0.75rem;color:#a78bfa;">Lihat kode warna &amp; format Minecraft</summary>
                        <div style="margin-top:0.625rem;display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:0.375rem;">
                            @foreach($mcColors as $code => [$name, $hex])
                            <div style="display:flex;align-items:center;gap:0.375rem;font-size:0.7rem;color:#cbd5e1;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:0.375rem;padding:0.25rem 0.5rem;">
                                <span style="width:0.75rem;height:0.75rem;border-radius:9999px;background:{{ $hex }};border:1px solid rgba(255,255,255,0.2);flex-shrink:0;"></span>
                                <span style="font-family:'JetBrains Mono',monospace;color:#a78bfa;">&amp;{{ $code }}</span> {{ $name }}
                            </div>
                            @endforeach
                            @foreach($mcFormats as $code => $name)
                            <div style="display:flex;align-items:center;gap:0.375rem;font-size:0.7rem;color:#cbd5e1;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:0.375rem;padding:0.25rem 0.5rem;">
                                <span style="font-family:'JetBrains Mono',monospace;color:#a78bfa;">&amp;{{ $code }}</span> {{ $name }}
                            </div>
                            @endforeach
                        </div>
                    </details>
                </div>
                @elseif($product->requires_nickname && $product->nickname_type === 'gradient')
                <div style="margin-bottom:1rem;">
                    <label style="display:block;font-size:0.875rem;color:#94a3b8;margin-bottom:0.5rem;">Pilih Gradient Nickname</label>
                    @if($gradients->isEmpty())
                    <div style="color:#f87171;font-size:0.8rem;">Belum ada preset gradient tersedia, hubungi admin.</div>
                    @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(110px,1fr));gap:0.5rem;">
                        @foreach($gradients as $i => $g)
                        <label class="gradient-option" style="cursor:pointer;border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.5rem;display:block;">
                            <input type="radio" name="gradient_id" value="{{ $g->id }}" {{ $i === 0 ? 'checked' : '' }} onchange="mpRenderGradientPreview()" style="display:none;">
                            <div style="height:1.25rem;border-radius:0.25rem;background:linear-gradient(to right, {{ implode(',', $g->colors) }});margin-bottom:0.375rem;"></div>
                            <div style="font-size:0.7rem;color:#cbd5e1;text-align:center;">{{ $g->name }}</div>
                        </label>
                        @endforeach
                    </div>

                    <div style="margin-top:0.625rem;padding:0.875rem 1rem;background:#0f0f16;border:1px solid rgba(255,255,255,0.08);border-radius:0.5rem;">
                        <div style="font-size:0.625rem;color:#64748b;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:0.5rem;">Preview (username kamu)</div>
                        <div id="gradient-preview" style="font-family:'JetBrains Mono',monospace;font-size:1.25rem;min-height:1.6em;"><span style="color:#475569;">Isi username dulu buat lihat preview...</span></div>
                    </div>
                    @endif
                </div>
                @endif

                <div style="display:flex;align-items:flex-start;gap:0.5rem;margin-bottom:1rem;">
                    <input type="checkbox" name="terms_accepted" id="terms-checkbox" value="1" style="width:auto;margin-top:0.2rem;accent-color:#7c3aed;flex-shrink:0;">
                    <label for="terms-checkbox" style="margin-bottom:0;cursor:pointer;font-size:0.8rem;color:#94a3b8;line-height:1.5;">
                        Saya sudah membaca dan menyetujui <a href="{{ route('terms') }}" target="_blank" style="color:#a78bfa;">Syarat & Ketentuan</a>, termasuk kebijakan <strong style="color:#cbd5e1;">tidak ada pengembalian dana</strong> atas barang yang sudah dibeli.
                    </label>
                </div>

                <button type="submit" id="submit-btn" disabled
                    style="width:100%;background:linear-gradient(135deg,#7c3aed,#6d28d9);color:white;padding:0.75rem;border-radius:0.5rem;font-weight:600;font-size:0.875rem;border:none;cursor:not-allowed;opacity:0.5;transition:all 0.2s;font-family:inherit;">
                    Verifikasi dulu untuk lanjut
                </button>
            </form>
            @endif

            <div style="margin-top:1rem;padding-top:1rem;border-top:1px solid rgba(255,255,255,0.06);">
                <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;color:#64748b;margin-bottom:0.375rem;">
                    <span>🔒</span> Pembayaran aman via Midtrans
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;color:#64748b;margin-bottom:0.375rem;">
                    <span>⚡</span> Rank langsung aktif setelah pembayaran
                </div>
                <div style="display:flex;align-items:center;gap:0.5rem;font-size:0.75rem;color:#64748b;">
                    <span>💳</span> QRIS, Transfer Bank, GoPay, OVO
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media (max-width: 640px) {
    .detail-grid { grid-template-columns: 1fr !important; }
}
.duration-option {
    flex: 1; min-width: 6rem; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
    color: #e2e8f0; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8125rem; cursor: pointer; font-family: inherit;
}
.duration-option.active { background: rgba(139,92,246,0.18); border-color: rgba(139,92,246,0.5); color: #c4b5fd; }
</style>

@unless($maintenanceMode)
@push('scripts')
<script>
let isVerified = false;
let pollInterval = null;

const usernameInput = document.getElementById('username-field');
const usernameHidden = document.getElementById('username-hidden');
const submitBtn = document.getElementById('submit-btn');
const checkResult = document.getElementById('username-check-result');
const termsCheckbox = document.getElementById('terms-checkbox');
let nicknameValid = {{ $product->requires_nickname ? 'false' : 'true' }};

function updateSubmitState() {
    const canSubmit = isVerified && termsCheckbox.checked && nicknameValid;
    submitBtn.disabled = !canSubmit;
    submitBtn.style.opacity = canSubmit ? '1' : '0.5';
    submitBtn.style.cursor = canSubmit ? 'pointer' : 'not-allowed';
    submitBtn.textContent = !isVerified
        ? 'Verifikasi dulu untuk lanjut'
        : (!nicknameValid
            ? 'Isi nickname yang valid dulu'
            : (termsCheckbox.checked ? 'Beli Sekarang →' : 'Setujui Syarat & Ketentuan dulu'));
}

termsCheckbox.addEventListener('change', updateSubmitState);

@if($product->requires_nickname && $product->nickname_type !== 'gradient')
const MC_COLORS = {
    '0':'#000000','1':'#0000AA','2':'#00AA00','3':'#00AAAA','4':'#AA0000','5':'#AA00AA',
    '6':'#FFAA00','7':'#AAAAAA','8':'#555555','9':'#5555FF','a':'#55FF55','b':'#55FFFF',
    'c':'#FF5555','d':'#FF55FF','e':'#FFFF55','f':'#FFFFFF'
};
const OBF_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%';

const nicknameInput = document.getElementById('nickname-input');
const nicknamePreview = document.getElementById('nickname-preview');
const nicknameCounter = document.getElementById('nickname-counter');
const nicknameError = document.getElementById('nickname-error');
let obfuscateInterval = null;

function blankSegment() {
    return { text: '', color: '#e2e8f0', bold: false, italic: false, underline: false, strike: false, obf: false };
}

function parseNicknameSegments(raw) {
    const segments = [];
    let current = blankSegment();
    let i = 0;
    while (i < raw.length) {
        if (raw[i] === '&' && i + 1 < raw.length) {
            const code = raw[i + 1].toLowerCase();
            if (MC_COLORS[code] || 'klmnor'.includes(code)) {
                if (current.text) segments.push(current);
                if (code === 'r') {
                    current = blankSegment();
                } else {
                    current = { ...current, text: '' };
                    if (MC_COLORS[code]) {
                        current.color = MC_COLORS[code];
                        current.bold = current.italic = current.underline = current.strike = current.obf = false;
                    } else if (code === 'l') current.bold = true;
                    else if (code === 'm') current.strike = true;
                    else if (code === 'n') current.underline = true;
                    else if (code === 'o') current.italic = true;
                    else if (code === 'k') current.obf = true;
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

function randomObf(len) {
    let s = '';
    for (let i = 0; i < len; i++) s += OBF_CHARS[Math.floor(Math.random() * OBF_CHARS.length)];
    return s;
}

function renderNicknamePreview() {
    const raw = nicknameInput.value;
    const segments = parseNicknameSegments(raw);
    nicknamePreview.innerHTML = '';
    if (obfuscateInterval) { clearInterval(obfuscateInterval); obfuscateInterval = null; }

    if (!segments.length) {
        nicknamePreview.innerHTML = '<span style="color:#475569;">Preview nickname muncul di sini...</span>';
    } else {
        const obfSpans = [];
        segments.forEach(seg => {
            const span = document.createElement('span');
            span.style.color = seg.color;
            if (seg.bold) span.style.fontWeight = '700';
            if (seg.italic) span.style.fontStyle = 'italic';
            const deco = [];
            if (seg.underline) deco.push('underline');
            if (seg.strike) deco.push('line-through');
            if (deco.length) span.style.textDecoration = deco.join(' ');
            if (seg.obf) {
                span.dataset.original = seg.text;
                span.textContent = randomObf(seg.text.length);
                obfSpans.push(span);
            } else {
                span.textContent = seg.text;
            }
            nicknamePreview.appendChild(span);
        });
        if (obfSpans.length) {
            obfuscateInterval = setInterval(() => {
                obfSpans.forEach(span => { span.textContent = randomObf(span.dataset.original.length); });
            }, 80);
        }
    }

    // Validasi client-side, cerminan dari validasi server (whitelist ketat)
    const validPattern = /^(?:&[0-9a-fk-or]|[a-zA-Z0-9 ])+$/i;
    const visibleLength = raw.replace(/&[0-9a-fk-or]/gi, '').length;
    nicknameCounter.textContent = visibleLength + '/32';
    nicknameCounter.style.color = visibleLength > 32 ? '#f87171' : '#64748b';

    if (!raw.trim()) {
        nicknameError.textContent = '';
        nicknameValid = false;
    } else if (!validPattern.test(raw)) {
        nicknameError.textContent = 'Cuma boleh huruf, angka, spasi, & kode warna (&a, &l, dst).';
        nicknameValid = false;
    } else if (visibleLength < 1) {
        nicknameError.textContent = 'Isi teksnya, jangan cuma kode warna.';
        nicknameValid = false;
    } else if (visibleLength > 32) {
        nicknameError.textContent = 'Maksimal 32 karakter (tanpa hitung kode warna).';
        nicknameValid = false;
    } else {
        nicknameError.textContent = '';
        nicknameValid = true;
    }

    updateSubmitState();
}

nicknameInput.addEventListener('input', renderNicknamePreview);
@elseif($product->requires_nickname && $product->nickname_type === 'gradient')
const GRADIENTS = @js($gradients->pluck('colors', 'id'));

function mpHexToRgb(hex) {
    hex = hex.replace('#', '');
    return [parseInt(hex.substr(0, 2), 16), parseInt(hex.substr(2, 2), 16), parseInt(hex.substr(4, 2), 16)];
}

// Interpolasi linear RGB per-karakter, sama persis kayak Gradient::apply() di backend
// (backend tetap yang jadi sumber kebenaran final pas submit, ini cuma buat preview).
function mpApplyGradient(stops, text) {
    const len = text.length;
    if (stops.length < 2 || len === 0) return [];
    const segments = stops.length - 1;
    const result = [];
    for (let i = 0; i < len; i++) {
        const pos = len === 1 ? 0 : i / (len - 1);
        const segPos = pos * segments;
        let segIndex = Math.floor(segPos);
        let frac;
        if (segIndex >= segments) { segIndex = segments - 1; frac = 1; } else { frac = segPos - segIndex; }
        const from = mpHexToRgb(stops[segIndex]);
        const to = mpHexToRgb(stops[segIndex + 1]);
        const r = Math.round(from[0] + (to[0] - from[0]) * frac);
        const g = Math.round(from[1] + (to[1] - from[1]) * frac);
        const b = Math.round(from[2] + (to[2] - from[2]) * frac);
        result.push({ char: text[i], color: `rgb(${r},${g},${b})` });
    }
    return result;
}

function mpRenderGradientPreview() {
    const selected = document.querySelector('input[name="gradient_id"]:checked');
    const previewEl = document.getElementById('gradient-preview');
    if (!previewEl) return;

    document.querySelectorAll('.gradient-option').forEach(el => {
        const radio = el.querySelector('input[type=radio]');
        el.style.borderColor = radio.checked ? '#a78bfa' : 'rgba(255,255,255,0.1)';
        el.style.background = radio.checked ? 'rgba(139,92,246,0.08)' : 'transparent';
    });

    const text = usernameInput.value.trim();
    previewEl.innerHTML = '';

    if (!text || !selected) {
        const span = document.createElement('span');
        span.style.color = '#475569';
        span.textContent = 'Isi username dulu buat lihat preview...';
        previewEl.appendChild(span);
        nicknameValid = false;
        updateSubmitState();
        return;
    }

    const stops = GRADIENTS[selected.value] || [];
    mpApplyGradient(stops, text).forEach(seg => {
        const span = document.createElement('span');
        span.style.color = seg.color;
        span.textContent = seg.char;
        previewEl.appendChild(span);
    });

    nicknameValid = true;
    updateSubmitState();
}

usernameInput.addEventListener('input', mpRenderGradientPreview);
mpRenderGradientPreview();
@endif

const durationButtons = document.querySelectorAll('.duration-option');
const durationIdInput = document.getElementById('duration-id-input');
const priceDisplay = document.getElementById('detail-price');
const priceOriginalDisplay = document.getElementById('detail-price-original');
durationButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        durationButtons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        durationIdInput.value = btn.dataset.id;
        const discounted = btn.dataset.discounted === '1';
        if (priceDisplay) {
            priceDisplay.textContent = btn.dataset.formatted;
            priceDisplay.style.color = discounted ? '#4ade80' : 'white';
        }
        if (priceOriginalDisplay) {
            priceOriginalDisplay.textContent = btn.dataset.original;
            priceOriginalDisplay.style.display = discounted ? 'block' : 'none';
        }
    });
});

const platformToggle = mpAttachPlatformToggle(document.getElementById('checkout-platform-toggle'));
const renderSkinPreview = mpAttachSkinPreview(usernameInput, document.getElementById('checkout-skin-preview'), () => platformToggle.isBedrock());

function composedUsername() {
    return (platformToggle.isBedrock() ? '.' : '') + usernameInput.value.trim();
}

function syncHiddenUsername() {
    usernameHidden.value = usernameInput.value.trim() ? composedUsername() : '';
}

document.getElementById('checkout-platform-toggle').addEventListener('mp:platformchange', () => {
    renderSkinPreview();
    syncHiddenUsername();
    // Platform berubah -> username efektif berubah, minta verifikasi ulang
    isVerified = false;
    updateSubmitState();
    checkResult.innerHTML = '';
    document.getElementById('verify-section').style.display = 'none';
    if (pollInterval) clearInterval(pollInterval);
});

// Restore from session
const savedUsername = sessionStorage.getItem('mc_username');
if (savedUsername) {
    const isBedrockSaved = savedUsername.startsWith('.');
    usernameInput.value = isBedrockSaved ? savedUsername.slice(1) : savedUsername;
    if (isBedrockSaved) platformToggle.select('bedrock');
    syncHiddenUsername();
    renderSkinPreview();
    verifyUsername(savedUsername);
}

usernameInput.addEventListener('input', syncHiddenUsername);
usernameInput.addEventListener('blur', () => {
    syncHiddenUsername();
    if (usernameInput.value.trim().length >= 3) {
        verifyUsername(composedUsername());
    }
});

async function verifyUsername(username) {
    checkResult.innerHTML = '<span style="color:#94a3b8;">Mengecek...</span>';

    const res = await fetch('/verify/check', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({username})
    });
    const data = await res.json();

    if (!data.exists) {
        checkResult.innerHTML = `<span style="color:#f87171;">✗ ${data.message}</span>`;
        return;
    }

    if (data.verified) {
        showVerified(username);
        return;
    }

    checkResult.innerHTML = '<span style="color:#4ade80;">✓ Username ditemukan, perlu verifikasi</span>';

    const genRes = await fetch('/verify/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
        body: JSON.stringify({username})
    });
    const genData = await genRes.json();

    document.getElementById('verify-section').style.display = 'block';
    document.getElementById('verify-cmd').textContent = `/verify ${genData.token}`;
    startPolling(username);
}

function startPolling(username) {
    if (pollInterval) clearInterval(pollInterval);
    pollInterval = setInterval(async () => {
        const res = await fetch(`/verify/status?username=${username}`);
        const data = await res.json();
        if (data.verified) {
            clearInterval(pollInterval);
            showVerified(username);
        }
    }, 3000);
}

function showVerified(username) {
    isVerified = true;
    checkResult.innerHTML = '<span style="color:#4ade80;">✓ Terverifikasi!</span>';
    document.getElementById('verify-section').style.display = 'none';
    updateSubmitState();
    usernameHidden.value = username;
    sessionStorage.setItem('mc_username', username);
}
</script>
@endpush
@endunless
@endsection
