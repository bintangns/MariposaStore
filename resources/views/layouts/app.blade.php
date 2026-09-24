<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Project Mariposa') - Minecraft Server</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #0a0a0f; color: #e2e8f0; font-family: 'Inter', sans-serif; }
        .glass { background: rgba(255,255,255,0.04); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.08); }
        .gradient-text { background: linear-gradient(135deg, #c4b5fd, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .btn-primary { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; padding: 0.625rem 1.5rem; border-radius: 0.5rem; font-weight: 500; font-size: 0.875rem; transition: all 0.2s; cursor: pointer; border: none; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
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
        .mp-platform-btn.active { background: rgba(139,92,246,0.15); border-color: rgba(139,92,246,0.4); color: #c4b5fd; }
    </style>
    @stack('head')
</head>
<body class="min-h-screen antialiased">

    <nav style="position:fixed;top:0;width:100%;z-index:50;background:rgba(10,10,15,0.8);backdrop-filter:blur(12px);border-bottom:1px solid rgba(255,255,255,0.05);">
        <div style="max-width:72rem;margin:0 auto;padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:4rem;">
            <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:0.625rem;text-decoration:none;">
                <img src="{{ asset('images/logo.png') }}" alt="Mariposa ID" style="height:2.5rem;width:auto;border-radius:0.375rem;">
                <span style="font-weight:600;color:white;font-size:1.0625rem;">Mariposa ID</span>
            </a>
            <div style="display:flex;align-items:center;gap:2rem;font-size:0.875rem;color:#94a3b8;">
                <a href="{{ route('home') }}"   style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Beranda</a>
                <a href="{{ route('store') }}"  style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Store</a>
                <a href="{{ route('rules') }}"  style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Peraturan</a>
                <a href="{{ route('staff') }}"  style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Staff</a>
                <a href="{{ route('terms') }}"  style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">S&K</a>
                <a href="{{ route('orders') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Riwayat</a>
                <a href="{{ route('nicknames') }}" style="color:#94a3b8;text-decoration:none;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Koleksi</a>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
                <button type="button" onclick="mpCopyServerIp(this)" title="Klik untuk salin IP server"
                    style="font-family:'JetBrains Mono',monospace;font-size:0.75rem;color:#a78bfa;background:rgba(139,92,246,0.1);padding:0.25rem 0.75rem;border-radius:9999px;border:1px solid rgba(139,92,246,0.2);cursor:pointer;">play.mariposa.id</button>

                @if(session('verified_username'))
                <div style="position:relative;">
                    <button id="mp-user-menu-btn" onclick="mpToggleUserMenu()" type="button"
                        style="display:flex;align-items:center;gap:0.375rem;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#4ade80;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;font-family:'JetBrains Mono',monospace;cursor:pointer;">
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
                <button onclick="mpOpenVerifyModal()" type="button"
                    style="background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:#94a3b8;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.75rem;cursor:pointer;font-family:inherit;">Login</button>
                @endif
            </div>
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

    <main style="padding-top:4rem;">
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

    <footer style="border-top:1px solid rgba(255,255,255,0.05);padding:3rem 0;margin-top:5rem;">
        <div style="max-width:72rem;margin:0 auto;padding:0 1.5rem;text-align:center;color:#64748b;font-size:0.875rem;">
            <p style="font-weight:600;color:#94a3b8;margin-bottom:0.5rem;">Project Mariposa</p>
            <p>Minecraft Survival Server Indonesia</p>
            <p style="margin-top:1rem;font-size:0.75rem;font-family:'JetBrains Mono',monospace;">play.mariposa.id</p>
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
