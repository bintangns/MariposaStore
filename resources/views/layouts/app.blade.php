<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - Mariposa Indonesia</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: radial-gradient(circle at 50% -18rem,rgba(125,173,91,.065),transparent 42rem),#10130f; color: #e7ebdf; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        .gradient-text { background: linear-gradient(135deg, #ffe39a, #f1b74e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .btn-primary { background: linear-gradient(#f7d990, #dfa94a); color: #22200f; padding: 0.625rem 1.5rem; border-radius: 0; font-family:'JetBrains Mono',monospace; font-weight: 700; font-size: 0.875rem; transition: all 0.2s; cursor: pointer; border: 2px solid #ffe9ad; box-shadow:4px 4px 0 #614b25; }
        .btn-primary:hover { background:#ffe39c; transform: translate(-2px,-2px); box-shadow:6px 6px 0 #614b25; }
        .mp-site-nav { position:fixed; top:0; left:0; width:100%; z-index:50; background:rgba(15,20,14,.95); backdrop-filter:blur(14px); border-bottom:2px solid #506644; }
        .mp-site-nav__inner { max-width:76rem; height:4.5rem; margin:auto; padding:0 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1.25rem; }
        .mp-site-nav__brand { display:flex; align-items:center; gap:.5rem; flex:none; color:#fff; font-family:'JetBrains Mono',monospace; font-size:.95rem; font-weight:800; letter-spacing:.08em; text-decoration:none; text-shadow:2px 2px 0 #3c512f; }
        .mp-site-nav__brand img { width:3.4rem; height:3rem; object-fit:contain; }
        .mp-site-nav__links { display:flex; align-items:center; gap:1.25rem; }
        .mp-site-nav__links a,.mp-site-nav__mobile a { color:#a6a6b4; text-decoration:none; font-size:.82rem; font-weight:550; transition:color .2s; }
        .mp-site-nav__links a:hover,.mp-site-nav__links a[aria-current="page"],.mp-site-nav__mobile a:hover,.mp-site-nav__mobile a[aria-current="page"] { color:#f7cc78; }
        .mp-site-nav__actions { display:flex; align-items:center; gap:.65rem; flex:none; }
        .mp-site-nav__ip { border:1px solid #688459; border-radius:0; background:#243322; color:#cfe9bb; padding:.48rem .7rem; font-family:'JetBrains Mono',monospace; font-size:.68rem; cursor:pointer; }
        .mp-site-nav__login { border:1px solid #668055; border-radius:0; background:#263521; color:#e6f0db; padding:.48rem .8rem; font-family:'JetBrains Mono',monospace; font-size:.75rem; cursor:pointer; }
        .mp-site-nav__menu-button { display:none; width:2.5rem; height:2.5rem; border:1px solid #668055; border-radius:0; background:#263521; color:#fff; font-size:1.25rem; cursor:pointer; }
        .mp-site-nav__mobile { display:none; padding:.35rem 1.5rem 1.15rem; border-top:1px solid #506644; background:#172016; }
        .mp-site-nav__mobile.is-open { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:.2rem .8rem; }
        .mp-site-nav__mobile a { padding:.65rem .25rem; }
        .mp-site-footer { border-top:2px solid #506644; padding:2.5rem 0; margin-top:5rem; background:#0c100c; }
        .mp-site-footer__inner { max-width:76rem; margin:auto; padding:0 1.5rem; display:flex; align-items:center; justify-content:space-between; gap:1rem; color:#898999; font-size:.8rem; }
        .mp-site-footer__inner strong { color:#f5d28d; letter-spacing:.08em; }
        @media(max-width:1050px) { .mp-site-nav__links { gap:.7rem; } .mp-site-nav__ip { display:none; } }
        @media(max-width:850px) { .mp-site-nav__links { display:none; } .mp-site-nav__menu-button { display:block; } }
        @media(min-width:851px) { .mp-site-nav__mobile.is-open { display:none; } }
        @media(max-width:560px) { .mp-site-nav__inner { padding:0 1rem; } .mp-site-nav__brand span { display:none; } .mp-site-footer__inner { flex-direction:column; text-align:center; } }
        @keyframes mp-spin { to { transform: rotate(360deg); } }
        .mp-skin-preview { align-items: center; gap: 0.625rem; }
        .mp-skin-preview .mp-skin-box { position: relative; width: 48px; height: 48px; border-radius: 0.5rem; overflow: hidden; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); flex-shrink: 0; }
        .mp-skin-preview .mp-skin-spinner { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
        .mp-skin-preview .mp-skin-spinner div { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.2); border-top-color: #a78bfa; border-radius: 50%; animation: mp-spin 0.6s linear infinite; }
        .mp-skin-preview .mp-skin-img { width: 100%; height: 100%; object-fit: cover; display: none; image-rendering: pixelated; }
        .mp-skin-preview .mp-skin-username { font-size: 0.8125rem; color: #94a3b8; font-family: 'JetBrains Mono', monospace; }
        .mp-skin-preview .mp-skin-bedrock-badge { position: absolute; inset: 0; display: none; align-items: center; justify-content: center; font-size: 1.375rem; background: rgba(139,92,246,0.12); }
        .mp-platform-toggle { display: flex; gap: 0.5rem; margin-bottom: 0.875rem; }
        .mp-platform-btn { flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #94a3b8; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8125rem; cursor: pointer; font-family: inherit; transition: all 0.15s; }
        .mp-platform-btn.active { background: rgba(119,177,96,0.16); border-color: #638752; color: #c8e8b5; }
        #mp-verify-modal > div { border-radius:0 !important; border:2px solid #638752 !important; background:#172016 !important; box-shadow:7px 7px 0 #080c07; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen antialiased">

    <nav class="mp-site-nav" aria-label="Navigasi utama">
        <div class="mp-site-nav__inner">
            <a href="{{ route('home') }}" class="mp-site-nav__brand" aria-label="Mariposa Indonesia - Beranda">
                <img src="{{ asset('images/logo.png') }}" alt="" width="54" height="48">
                <span>MARIPOSA INDONESIA</span>
            </a>
            <div class="mp-site-nav__links">
                @foreach(['home' => 'Beranda', 'store' => 'Store', 'rules' => 'Peraturan', 'staff' => 'Staff', 'terms' => 'S&K', 'orders' => 'Riwayat', 'nicknames' => 'Koleksi'] as $routeName => $label)
                <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName, $routeName.'.*')) aria-current="page" @endif>{{ $label }}</a>
                @endforeach
            </div>
            <div class="mp-site-nav__actions">
                <button type="button" onclick="mpCopyServerIp(this)" title="Klik untuk salin IP server"
                    class="mp-site-nav__ip"><span class="mp-ip-text">play.mariposa.id</span></button>

                @if(session('verified_username'))
                <div style="position:relative;">
                    <button id="mp-user-menu-btn" onclick="mpToggleUserMenu()" type="button"
                        style="display:flex;align-items:center;gap:0.375rem;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#4ade80;padding:0.45rem 0.75rem;border-radius:0.6rem;font-size:0.75rem;font-family:'JetBrains Mono',monospace;cursor:pointer;">
                        <span style="font-size:0.5rem;">●</span> {{ session('verified_username') }}
                    </button>
                    <div id="mp-user-menu" style="display:none;position:absolute;top:calc(100% + 0.5rem);right:0;background:#13131a;border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.375rem;min-width:8rem;box-shadow:0 10px 30px rgba(0,0,0,0.4);z-index:60;">
                        <form method="POST" action="{{ route('verify.logout') }}">
                            @csrf
                            <button type="submit" style="width:100%;text-align:left;background:none;border:none;color:#f87171;font-size:0.8125rem;padding:0.5rem 0.625rem;border-radius:0.375rem;cursor:pointer;font-family:inherit;">Logout</button>
                        </form>
                    </div>
                </div>
                @else
                <button onclick="mpOpenVerifyModal()" type="button" class="mp-site-nav__login">Login</button>
                @endif
                <button type="button" id="mp-site-menu-button" class="mp-site-nav__menu-button" aria-label="Buka menu" aria-controls="mp-site-mobile-menu" aria-expanded="false" onclick="mpToggleSiteMenu()">☰</button>
            </div>
        </div>
        <div id="mp-site-mobile-menu" class="mp-site-nav__mobile">
            @foreach(['home' => 'Beranda', 'store' => 'Store', 'rules' => 'Peraturan', 'staff' => 'Staff', 'terms' => 'S&K', 'orders' => 'Riwayat', 'nicknames' => 'Koleksi'] as $routeName => $label)
            <a href="{{ route($routeName) }}" @if(request()->routeIs($routeName, $routeName.'.*')) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </div>
    </nav>

    {{-- Global username verify modal --}}
    <div id="mp-verify-modal" style="display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,0.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:1.5rem;" onclick="if(event.target===this) mpCloseVerifyModal()">
        <div style="background:#13131a;border:1px solid rgba(255,255,255,0.1);border-radius:1rem;padding:2rem;max-width:24rem;width:100%;position:relative;box-sizing:border-box;">
            <button onclick="mpCloseVerifyModal()" type="button" style="position:absolute;top:1rem;right:1rem;background:none;border:none;color:#64748b;font-size:1.25rem;cursor:pointer;line-height:1;">×</button>
            <h3 style="font-weight:600;color:white;font-size:1.125rem;margin-bottom:0.5rem;">Masukkan Username Minecraft</h3>
            <p style="color:#94a3b8;font-size:0.8125rem;margin-bottom:1.25rem;">Username harus sudah pernah join ke play.mariposa.id minimal sekali.</p>
            <div id="mp-platform-toggle" class="mp-platform-toggle">
                <button type="button" class="mp-platform-btn active" data-platform="java">🖥️ Java</button>
                <button type="button" class="mp-platform-btn" data-platform="bedrock">📱 Bedrock</button>
            </div>
            <div id="mp-verify-skin-preview" class="mp-skin-preview" style="display:none;margin-bottom:0.875rem;">
                <div class="mp-skin-box">
                    <div class="mp-skin-spinner"><div></div></div>
                    <img class="mp-skin-img" alt="Skin preview">
                    <div class="mp-skin-bedrock-badge">📱</div>
                </div>
                <span class="mp-skin-username"></span>
            </div>
            <div style="display:flex;gap:0.625rem;">
                <input type="text" id="mp-username-input" maxlength="16" placeholder="Username Minecraft" onkeydown="if(event.key==='Enter') mpCheckUsername()"
                    style="flex:1;min-width:0;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);border-radius:0.5rem;padding:0.625rem 1rem;color:white;font-size:0.875rem;outline:none;font-family:inherit;box-sizing:border-box;">
                <button onclick="mpCheckUsername()" type="button" class="btn-primary">Cek</button>
            </div>
            <div id="mp-verify-result" style="margin-top:0.75rem;font-size:0.8125rem;min-height:1.2em;"></div>
        </div>
    </div>

    <main style="padding-top:4.5rem;">
        @if(session('success'))
        <div style="position:fixed;top:5rem;right:1.5rem;z-index:50;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#4ade80;padding:0.75rem 1rem;border-radius:0.5rem;font-size:0.875rem;max-width:20rem;">
            {{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div style="position:fixed;top:5rem;right:1.5rem;z-index:50;background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.3);color:#f87171;padding:0.75rem 1rem;border-radius:0.5rem;font-size:0.875rem;max-width:20rem;">
            {{ session('error') }}
        </div>
        @endif
        @yield('content')
    </main>

    <footer class="mp-site-footer">
        <div class="mp-site-footer__inner">
            <strong>MARIPOSA INDONESIA</strong>
            <span>Minecraft Survival RPG</span>
            <span style="font-family:'JetBrains Mono',monospace;">play.mariposa.id</span>
        </div>
    </footer>

    <script>
        // Reusable Minecraft skin-face preview for any username input.
        // previewEl must contain: .mp-skin-spinner, .mp-skin-img, .mp-skin-username (optional)
        // getIsBedrock (optional): fn returning true if the player is on Bedrock (username gets a "." prefix).
        // Bedrock accounts have no real Java skin to look up, so we show a badge instead of hitting Crafatar.
        function mpAttachSkinPreview(inputEl, previewEl, getIsBedrock) {
            if (!inputEl || !previewEl) return () => {};
            const img = previewEl.querySelector('.mp-skin-img');
            const spinner = previewEl.querySelector('.mp-skin-spinner');
            const badge = previewEl.querySelector('.mp-skin-bedrock-badge');
            const nameEl = previewEl.querySelector('.mp-skin-username');
            let debounceTimer = null;

            function render() {
                const raw = inputEl.value.trim();
                if (raw.length < 3) {
                    previewEl.style.display = 'none';
                    return;
                }

                const isBedrock = getIsBedrock ? getIsBedrock() : false;
                previewEl.style.display = 'flex';
                if (nameEl) nameEl.textContent = (isBedrock ? '.' : '') + raw;

                if (isBedrock) {
                    spinner.style.display = 'none';
                    img.style.display = 'none';
                    if (badge) badge.style.display = 'flex';
                    return;
                }

                if (badge) badge.style.display = 'none';
                spinner.style.display = 'flex';
                img.style.display = 'none';

                const fallbackUrl = `https://mc-heads.net/avatar/${encodeURIComponent(raw)}/64`;

                const probe = new Image();
                probe.onload = () => {
                    img.src = probe.src;
                    spinner.style.display = 'none';
                    img.style.display = 'block';
                };
                probe.onerror = () => {
                    // Crafatar gagal untuk username non-premium (server pakai AuthMe/offline mode) -> fallback ke skin generik
                    if (probe.src !== fallbackUrl) {
                        probe.onerror = () => { spinner.style.display = 'none'; };
                        probe.src = fallbackUrl;
                        return;
                    }
                    spinner.style.display = 'none';
                };
                probe.src = `https://crafatar.com/avatars/${encodeURIComponent(raw)}?size=64&overlay`;
            }

            inputEl.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(render, 400);
            });
            inputEl.addEventListener('blur', () => {
                clearTimeout(debounceTimer);
                render();
            });

            return render;
        }

        // Wires up a Java/Bedrock toggle button group. Returns { isBedrock, select }.
        function mpAttachPlatformToggle(containerEl) {
            if (!containerEl) return { isBedrock: () => false, select: () => {} };
            let platform = 'java';
            const buttons = [...containerEl.querySelectorAll('.mp-platform-btn')];

            function select(newPlatform) {
                platform = newPlatform;
                buttons.forEach(b => b.classList.toggle('active', b.dataset.platform === newPlatform));
                containerEl.dispatchEvent(new Event('mp:platformchange'));
            }

            buttons.forEach(btn => btn.addEventListener('click', () => select(btn.dataset.platform)));

            return { isBedrock: () => platform === 'bedrock', select };
        }

        const mpPlatformToggle = mpAttachPlatformToggle(document.getElementById('mp-platform-toggle'));
        const mpRenderSkinPreview = mpAttachSkinPreview(
            document.getElementById('mp-username-input'),
            document.getElementById('mp-verify-skin-preview'),
            () => mpPlatformToggle.isBedrock()
        );
        document.getElementById('mp-platform-toggle')?.addEventListener('mp:platformchange', () => mpRenderSkinPreview());

        const mpIsVerified = @json((bool) session('verified_username'));

        function mpOpenVerifyModal() {
            document.getElementById('mp-verify-modal').style.display = 'flex';
            document.getElementById('mp-username-input').focus();
        }

        function mpCloseVerifyModal() {
            document.getElementById('mp-verify-modal').style.display = 'none';
        }

        function mpCopyServerIp(btn) {
            const ip = 'play.mariposa.id';
            navigator.clipboard.writeText(ip).then(() => {
                const textEl = btn.querySelector('.mp-ip-text') || btn;
                const original = textEl.textContent;
                textEl.textContent = '✓ Disalin!';
                setTimeout(() => { textEl.textContent = original; }, 1500);
            });
        }

        function mpToggleUserMenu() {
            const menu = document.getElementById('mp-user-menu');
            if (menu) menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }

        function mpToggleSiteMenu() {
            const menu = document.getElementById('mp-site-mobile-menu');
            const button = document.getElementById('mp-site-menu-button');
            const isOpen = menu.classList.toggle('is-open');
            button.setAttribute('aria-expanded', String(isOpen));
            button.setAttribute('aria-label', isOpen ? 'Tutup menu' : 'Buka menu');
        }

        document.addEventListener('click', (e) => {
            const menu = document.getElementById('mp-user-menu');
            const btn = document.getElementById('mp-user-menu-btn');
            if (!menu || !btn) return;
            if (menu.style.display === 'block' && !menu.contains(e.target) && !btn.contains(e.target)) {
                menu.style.display = 'none';
            }
        });

        async function mpCheckUsername() {
            const input = document.getElementById('mp-username-input');
            const resultEl = document.getElementById('mp-verify-result');
            const raw = input.value.trim();

            if (raw.length < 3) {
                resultEl.innerHTML = '<span style="color:#f87171;">Username minimal 3 karakter</span>';
                return;
            }

            const username = (mpPlatformToggle.isBedrock() ? '.' : '') + raw;
            resultEl.innerHTML = '<span style="color:#94a3b8;">Mengecek...</span>';

            try {
                const res = await fetch('{{ route('verify.check') }}', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content},
                    body: JSON.stringify({ username })
                });
                const data = await res.json();

                if (!data.exists) {
                    resultEl.innerHTML = `<span style="color:#f87171;">✗ ${data.message}</span>`;
                    return;
                }

                resultEl.innerHTML = '<span style="color:#4ade80;">✓ Terverifikasi! Memuat ulang...</span>';
                setTimeout(() => window.location.reload(), 500);
            } catch (e) {
                resultEl.innerHTML = '<span style="color:#f87171;">Gagal mengecek, coba lagi.</span>';
            }
        }

        {{-- Halaman tertentu (mis. Store) bisa minta modal ini otomatis tampil
             dengan set window.mpAutoPrompt = true sebelum event ini jalan --}}
        window.addEventListener('DOMContentLoaded', () => {
            if (!mpIsVerified && window.mpAutoPrompt) mpOpenVerifyModal();
        });
    </script>

    @stack('scripts')
</body>
</html>
