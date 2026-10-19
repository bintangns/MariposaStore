@extends('layouts.app')
@section('title', 'Survival RPG')

@push('head')
<style>
    .mp-rpg { --rpg-gold:#efc76f; --rpg-muted:#b8b9aa; background:#10130f; color:#f7f4e9; }
    .mp-rpg__wrap { max-width:76rem; margin:auto; padding:0 1.5rem; }
    .mp-rpg__eyebrow { display:inline-flex; align-items:center; gap:.65rem; color:var(--rpg-gold); font-family:'JetBrains Mono',monospace; font-size:.72rem; font-weight:700; letter-spacing:.15em; text-transform:uppercase; }
    .mp-rpg__eyebrow::before { content:""; width:.6rem; height:.6rem; background:#7ecb78; box-shadow:2px 2px 0 #345f37; }
    .mp-rpg-hero { position:relative; min-height:42rem; display:flex; align-items:center; overflow:hidden; border-bottom:4px solid #48553d; background-image:linear-gradient(90deg,rgba(8,12,9,.98) 0%,rgba(8,12,9,.88) 24%,rgba(8,12,9,.54) 48%,rgba(8,12,9,.06) 78%),linear-gradient(0deg,#10130f,transparent 28%),url('{{ asset('images/survival-rpg-hero.jpg') }}'); background-size:cover; background-position:center; }
    .mp-rpg-hero::after { content:""; position:absolute; inset:0; pointer-events:none; background:repeating-linear-gradient(90deg,transparent 0 31px,rgba(0,0,0,.065) 31px 32px); }
    .mp-rpg-hero__content { position:relative; z-index:1; width:100%; padding-top:4rem; padding-bottom:5rem; }
    .mp-rpg-hero h1 { max-width:10ch; margin:1.4rem 0 1.3rem; color:#fff; font-family:'JetBrains Mono',monospace; font-size:clamp(3.4rem,7.2vw,7rem); font-weight:800; line-height:.98; letter-spacing:-.075em; text-shadow:5px 5px 0 #1d281d,0 10px 35px rgba(0,0,0,.7); }
    .mp-rpg-hero h1 span { color:var(--rpg-gold); }
    .mp-rpg-hero__lead { max-width:32rem; color:#ebeee3; font-size:1.04rem; line-height:1.75; text-shadow:0 2px 12px #000; }
    .mp-rpg-hero__actions { display:flex; flex-wrap:wrap; gap:.85rem; margin-top:2rem; }
    .mp-rpg-hero__actions .btn-primary,.mp-rpg-cta .btn-primary { display:inline-flex; align-items:center; justify-content:center; min-height:3.15rem; padding:.82rem 1.3rem; text-decoration:none; }
    .mp-rpg-hero__copy { display:inline-flex; align-items:center; gap:.7rem; min-height:3.15rem; padding:.82rem 1rem; border:2px solid #839677; border-radius:0; background:#182218; box-shadow:4px 4px 0 #070b08; color:#f6f6ec; font-family:'JetBrains Mono',monospace; font-size:.82rem; font-weight:700; cursor:pointer; }
    .mp-rpg-hero__copy:hover { background:#253726; border-color:#a7c796; }
    .mp-rpg-hero__copy span:first-child { color:#9cde90; }
    .mp-rpg-hero__hint { color:#d0d2c5; font-size:.78rem; margin-top:1.15rem; text-shadow:0 2px 8px #000; }
    .mp-rpg-strip { position:relative; z-index:2; display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1px; margin-top:-2.2rem; border:2px solid #4b5b42; background:#4b5b42; box-shadow:7px 7px 0 #080b08; }
    .mp-rpg-strip__item { display:flex; align-items:center; gap:1rem; min-height:5.4rem; padding:1rem 1.35rem; background:#1a2119; }
    .mp-rpg-strip__icon { display:grid; place-items:center; width:2.5rem; height:2.5rem; flex:none; border:2px solid #637954; background:#2b3b27; color:#e4cb81; font-size:1.25rem; box-shadow:3px 3px 0 #0b100b; }
    .mp-rpg-strip strong { display:block; color:#f5f1dd; font-family:'JetBrains Mono',monospace; font-size:.77rem; }
    .mp-rpg-strip small { display:block; color:#aeb8a7; font-size:.7rem; line-height:1.4; margin-top:.2rem; }
    .mp-rpg-section { padding:6.5rem 0 5.5rem; }
    .mp-rpg-section__heading { max-width:43rem; margin-bottom:2rem; }
    .mp-rpg-section__heading h2 { color:#f7f5e9; font-family:'JetBrains Mono',monospace; font-size:clamp(1.9rem,4vw,3rem); line-height:1.2; letter-spacing:-.055em; margin:.9rem 0; }
    .mp-rpg-section__heading p { color:var(--rpg-muted); font-size:.94rem; line-height:1.7; }
    .mp-rpg-cards { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.2rem; }
    .mp-rpg-card { --accent:#84c783; display:flex; flex-direction:column; min-height:19rem; padding:1.5rem; border:2px solid #536449; background:linear-gradient(150deg,color-mix(in srgb,var(--accent) 9%,#1a2219),#151b15 65%); box-shadow:6px 6px 0 #080b08; transition:transform .2s,box-shadow .2s; }
    .mp-rpg-card:hover { transform:translate(-2px,-2px); box-shadow:8px 8px 0 #080b08; }
    .mp-rpg-card__top { display:flex; align-items:start; justify-content:space-between; }
    .mp-rpg-card__icon { display:grid; place-items:center; width:3.4rem; height:3.4rem; border:2px solid color-mix(in srgb,var(--accent) 65%,#1c271d); background:color-mix(in srgb,var(--accent) 18%,#1c271d); box-shadow:4px 4px 0 #0b120d; color:var(--accent); font-size:1.7rem; }
    .mp-rpg-card__number { color:#7d9277; font-family:'JetBrains Mono',monospace; font-size:.76rem; font-weight:700; }
    .mp-rpg-card h3 { color:#fff; font-family:'JetBrains Mono',monospace; font-size:1.45rem; font-weight:800; margin:1.6rem 0 .65rem; }
    .mp-rpg-card p { color:#b7c0af; font-size:.86rem; line-height:1.65; }
    .mp-rpg-card__tags { display:flex; flex-wrap:wrap; gap:.45rem; margin-top:auto; padding-top:1.35rem; }
    .mp-rpg-card__tags span { padding:.3rem .55rem; border:1px solid #435441; background:#222d21; color:#d6e2cc; font-family:'JetBrains Mono',monospace; font-size:.65rem; }
    .mp-rpg-systems { padding:0 0 5.5rem; }
    .mp-rpg-systems__grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1.2rem; }
    .mp-rpg-system { position:relative; padding:1.45rem; border:2px solid #5d704c; background:#1b241a; box-shadow:6px 6px 0 #080b08; }
    .mp-rpg-system::before { content:""; position:absolute; top:0; left:0; right:0; height:.35rem; background:var(--system-accent,#8ed58d); }
    .mp-rpg-system__top { display:flex; align-items:center; justify-content:space-between; gap:1rem; color:var(--system-accent,#8ed58d); font-family:'JetBrains Mono',monospace; font-size:.68rem; font-weight:700; letter-spacing:.08em; }
    .mp-rpg-system__icon { display:grid; place-items:center; width:2.7rem; height:2.7rem; border:2px solid color-mix(in srgb,var(--system-accent,#8ed58d) 58%,#263222); background:#283527; color:var(--system-accent,#8ed58d); font-size:1.3rem; }
    .mp-rpg-system h3 { color:#fff; font-family:'JetBrains Mono',monospace; font-size:1.35rem; margin:1.25rem 0 .6rem; }
    .mp-rpg-system p { color:#b9c3b3; font-size:.86rem; line-height:1.65; }
    .mp-rpg-system__list { display:flex; flex-wrap:wrap; gap:.4rem; margin-top:1.1rem; }
    .mp-rpg-system__list span { padding:.28rem .48rem; border:1px solid #45543d; color:#d4dfc9; font-size:.69rem; }
    .mp-rpg-lore { display:grid; grid-template-columns:minmax(0,1.1fr) minmax(17rem,.9fr); gap:2rem; align-items:center; margin-bottom:5.5rem; padding:2.2rem; border:2px solid #8e784a; background:radial-gradient(circle at right,rgba(141,110,56,.19),transparent 55%),#1b2018; box-shadow:7px 7px 0 #080b08; }
    .mp-rpg-lore h2 { color:#fff; font-family:'JetBrains Mono',monospace; font-size:clamp(1.8rem,3.7vw,2.8rem); line-height:1.18; letter-spacing:-.05em; margin:.8rem 0 1rem; }
    .mp-rpg-lore p { color:#c3c8b8; font-size:.9rem; line-height:1.75; margin-top:.85rem; }
    .mp-rpg-lore__seal { display:grid; place-items:center; min-height:17rem; padding:1.5rem; border:2px solid #75623f; background:repeating-linear-gradient(0deg,transparent 0 23px,rgba(239,199,111,.035) 23px 24px),#24281d; color:#e8cf90; text-align:center; }
    .mp-rpg-lore__seal span { display:block; font-size:3.8rem; line-height:1; }
    .mp-rpg-lore__seal strong { display:block; max-width:16rem; margin-top:1rem; font-family:'JetBrains Mono',monospace; font-size:1.05rem; line-height:1.5; }
    .mp-rpg-guild { margin-bottom:5.5rem; padding:2.1rem; border:2px solid #526b4a; background:linear-gradient(125deg,#202b1d,#151c15); box-shadow:7px 7px 0 #080b08; }
    .mp-rpg-guild__ranks { display:flex; align-items:center; flex-wrap:wrap; gap:.45rem; margin:1.5rem 0; }
    .mp-rpg-guild__ranks span { display:grid; place-items:center; width:3.3rem; height:3.3rem; border:2px solid #8b7848; background:#302c1d; color:#f0cf82; font-family:'JetBrains Mono',monospace; font-size:1.2rem; font-weight:800; box-shadow:3px 3px 0 #0c100b; }
    .mp-rpg-guild__ranks b { color:#8fac82; font-size:1.1rem; }
    .mp-rpg-guild__copy { color:#bac5b3; font-size:.87rem; line-height:1.7; }
    .mp-rpg-guild__features { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.8rem; margin-top:1.4rem; }
    .mp-rpg-guild__features div { padding:1rem; border:1px solid #4c6045; background:#182118; }
    .mp-rpg-guild__features strong { display:block; color:#f4e7bd; font-family:'JetBrains Mono',monospace; font-size:.78rem; }
    .mp-rpg-guild__features p { color:#b6c1ae; font-size:.78rem; line-height:1.55; margin-top:.45rem; }
    .mp-rpg-dungeons { margin-bottom:5.5rem; }
    .mp-rpg-dungeons__grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; }
    .mp-rpg-dungeon { display:flex; flex-direction:column; padding:1.3rem; border:2px solid #605f48; background:#1d211b; box-shadow:5px 5px 0 #080b08; }
    .mp-rpg-dungeon__index { color:#d9ba76; font-family:'JetBrains Mono',monospace; font-size:.7rem; letter-spacing:.1em; }
    .mp-rpg-dungeon h3 { color:#fff; font-family:'JetBrains Mono',monospace; font-size:1.08rem; line-height:1.4; margin:.85rem 0 .6rem; }
    .mp-rpg-dungeon p { color:#b9c0b0; font-size:.8rem; line-height:1.6; }
    .mp-rpg-dungeon__boss { margin-top:auto; padding-top:1.2rem; color:#e8cf90; font-family:'JetBrains Mono',monospace; font-size:.7rem; }
    .mp-rpg-quests { margin-bottom:5.5rem; }
    .mp-rpg-quests__grid { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.7rem; }
    .mp-rpg-quest { padding:1rem 1.1rem; border:1px solid #51674a; background:#1a2419; color:#e7ecd9; font-family:'JetBrains Mono',monospace; font-size:.78rem; }
    .mp-rpg-quest::before { content:"✦"; margin-right:.65rem; color:#e9c776; }
    .mp-rpg-cta { display:flex; justify-content:space-between; align-items:center; gap:2rem; padding:2.4rem; border:2px solid #917a45; background:linear-gradient(115deg,#24271c,#182018); box-shadow:7px 7px 0 #080b08; }
    .mp-rpg-cta h2 { color:#fff; font-family:'JetBrains Mono',monospace; font-size:clamp(1.5rem,3vw,2.3rem); line-height:1.2; letter-spacing:-.04em; margin:.8rem 0 .4rem; }
    .mp-rpg-cta p { color:#b8bdac; font-size:.86rem; }
    @media(max-width:800px) { .mp-rpg-hero { min-height:38rem; background-position:58% center; } .mp-rpg-hero h1 { max-width:12ch; } .mp-rpg-cards,.mp-rpg-systems__grid,.mp-rpg-dungeons__grid { grid-template-columns:repeat(2,minmax(0,1fr)); } .mp-rpg-lore { grid-template-columns:1fr; } .mp-rpg-lore__seal { min-height:10rem; } }
    @media(max-width:640px) { .mp-rpg__wrap { padding:0 1rem; } .mp-rpg-hero { min-height:39rem; align-items:end; background-position:66% center; background-image:linear-gradient(0deg,rgba(8,12,9,.98),rgba(8,12,9,.75) 42%,rgba(8,12,9,.18) 90%),url('{{ asset('images/survival-rpg-hero.jpg') }}'); } .mp-rpg-hero__content { padding-top:3rem; padding-bottom:3.5rem; } .mp-rpg-hero h1 { font-size:clamp(3rem,13vw,4.4rem); } .mp-rpg-strip { grid-template-columns:1fr; margin-top:-1rem; } .mp-rpg-strip__item { min-height:4.7rem; } .mp-rpg-section { padding:5rem 0 4rem; } .mp-rpg-cards,.mp-rpg-systems__grid,.mp-rpg-dungeons__grid { grid-template-columns:1fr; } .mp-rpg-systems { padding-bottom:4.5rem; } .mp-rpg-lore,.mp-rpg-guild { padding:1.3rem; margin-bottom:4.5rem; } .mp-rpg-guild__features,.mp-rpg-quests__grid { grid-template-columns:1fr; } .mp-rpg-dungeons,.mp-rpg-quests { margin-bottom:4.5rem; } .mp-rpg-cta { display:block; padding:1.5rem; } .mp-rpg-cta .btn-primary { width:100%; margin-top:1.4rem; } }
    @media(prefers-reduced-motion:reduce) { .mp-rpg-card { transition:none; } .mp-rpg-card:hover { transform:none; } }
</style>
@endpush

@section('content')
<div class="mp-rpg">
    <section class="mp-rpg-hero" aria-labelledby="mp-rpg-title">
        <div class="mp-rpg__wrap mp-rpg-hero__content">
            <span class="mp-rpg__eyebrow">Mariposa Indonesia · Fantasy Medieval Survival RPG</span>
            <h1 id="mp-rpg-title">BUILD YOUR <span>LEGACY.</span></h1>
            <p class="mp-rpg-hero__lead">Begin your adventure. Bangun rumah, kembangkan karakter, jalani quest, dan bantu bangkitkan kembali Kerajaan Mariposa bersama petualang lain.</p>
            <div class="mp-rpg-hero__actions">
                <a href="{{ route('store') }}" class="btn-primary">Jelajahi Rank <span aria-hidden="true" style="margin-left:.6rem;">↗</span></a>
                <button type="button" class="mp-rpg-hero__copy" onclick="mpCopyServerIp(this)" title="Salin IP server"><span aria-hidden="true">▣</span><span class="mp-ip-text">play.mariposa.id</span></button>
            </div>
            <p class="mp-rpg-hero__hint">Salin alamat server, lalu tambahkan di daftar server Minecraft kamu.</p>
        </div>
    </section>

    <div class="mp-rpg__wrap">
        <div class="mp-rpg-strip" aria-label="Fitur Survival RPG">
            <div class="mp-rpg-strip__item"><span class="mp-rpg-strip__icon" aria-hidden="true">⚔</span><div><strong>JAVA & BEDROCK</strong><small>Berpetualang bersama teman</small></div></div>
            <div class="mp-rpg-strip__item"><span class="mp-rpg-strip__icon" aria-hidden="true">⬡</span><div><strong>LAND CLAIM</strong><small>Bangun wilayahmu sendiri</small></div></div>
            <div class="mp-rpg-strip__item"><span class="mp-rpg-strip__icon" aria-hidden="true">◆</span><div><strong>EKONOMI PEMAIN</strong><small>Berdagang dan berkembang</small></div></div>
        </div>

        <section class="mp-rpg-section" aria-labelledby="mp-rpg-section-title">
            <div class="mp-rpg-section__heading">
                <span class="mp-rpg__eyebrow">Mulai petualangan</span>
                <h2 id="mp-rpg-section-title">Duniamu. Ceritamu.</h2>
                <p>Mariposa Indonesia menggabungkan kebebasan Survival dengan eksplorasi, kerja sama, dan perkembangan karakter dalam dunia Fantasy Medieval.</p>
            </div>
            <div class="mp-rpg-cards">
                <article class="mp-rpg-card" style="--accent:#8ed58d;">
                    <div class="mp-rpg-card__top"><span class="mp-rpg-card__icon" aria-hidden="true">⚔</span><span class="mp-rpg-card__number">01 / JELAJAH</span></div>
                    <h3>Jelajahi dunia</h3>
                    <p>Jelajahi dunia, mining, farming, dan kumpulkan resource untuk memulai kisah petualanganmu.</p>
                    <div class="mp-rpg-card__tags"><span>Eksplorasi</span><span>Mining & Farming</span></div>
                </article>
                <article class="mp-rpg-card" style="--accent:#b2cd78;">
                    <div class="mp-rpg-card__top"><span class="mp-rpg-card__icon" aria-hidden="true">⛏</span><span class="mp-rpg-card__number">02 / BANGUN</span></div>
                    <h3>Bangun wilayah</h3>
                    <p>Wujudkan markas impianmu dan gunakan sistem klaim lahan untuk menjaga hasil kerja kerasmu.</p>
                    <div class="mp-rpg-card__tags"><span>Land Claim</span><span>Player Warps</span></div>
                </article>
                <article class="mp-rpg-card" style="--accent:#efc76f;">
                    <div class="mp-rpg-card__top"><span class="mp-rpg-card__icon" aria-hidden="true">◆</span><span class="mp-rpg-card__number">03 / BERKEMBANG</span></div>
                    <h3>Tumbuh bersama</h3>
                    <p>Berdagang dengan pemain lain, kunjungi lokasi mereka melalui Player Warps, dan bangun komunitas.</p>
                    <div class="mp-rpg-card__tags"><span>Ekonomi</span><span>Perdagangan</span></div>
                </article>
            </div>
        </section>

        <section class="mp-rpg-systems" aria-labelledby="mp-rpg-systems-title">
            <div class="mp-rpg-section__heading">
                <span class="mp-rpg__eyebrow">Sistem RPG</span>
                <h2 id="mp-rpg-systems-title">Lebih dari sekadar bertahan.</h2>
                <p>Pilih class dan tingkatkan level karaktermu. Aktivitas serta pertarungan mengembangkan skill untuk petualangan jangka panjang.</p>
            </div>
            <div class="mp-rpg-systems__grid">
                <article class="mp-rpg-system" style="--system-accent:#efc76f;">
                    <div class="mp-rpg-system__top"><span>01 / MISI</span><span class="mp-rpg-system__icon" aria-hidden="true">▤</span></div>
                    <h3>Quests & Story</h3>
                    <p>Ikuti kisah dunia melalui main quest, kontrak Guild, misi eksplorasi, dan cerita dari NPC.</p>
                    <div class="mp-rpg-system__list"><span>Main Quest</span><span>Guild Quest</span><span>Side Quest</span></div>
                </article>
                <article class="mp-rpg-system" style="--system-accent:#91ce8c;">
                    <div class="mp-rpg-system__top"><span>02 / KEMAMPUAN</span><span class="mp-rpg-system__icon" aria-hidden="true">✦</span></div>
                    <h3>Skills</h3>
                    <p>Kumpulkan XP, tingkatkan statistik, dan kembangkan kemampuan aktif maupun pasif lewat aktivitas dan pertarungan.</p>
                    <div class="mp-rpg-system__list"><span>Level & XP</span><span>Skill Aktif</span><span>Skill Pasif</span></div>
                </article>
                <article class="mp-rpg-system" style="--system-accent:#a9b8ed;">
                    <div class="mp-rpg-system__top"><span>03 / PERAN</span><span class="mp-rpg-system__icon" aria-hidden="true">♜</span></div>
                    <h3>Class</h3>
                    <p>Pilih class untuk membentuk gaya bertarungmu. Kelola mana, energy, dan cooldown saat menghadapi tantangan.</p>
                    <div class="mp-rpg-system__list"><span>Class Karakter</span><span>Mana & Energy</span><span>Cooldown</span></div>
                </article>
            </div>
        </section>

        <section class="mp-rpg-lore" aria-labelledby="mp-rpg-lore-title">
            <div>
                <span class="mp-rpg__eyebrow">Kisah Kerajaan Mariposa</span>
                <h2 id="mp-rpg-lore-title">Kerajaan yang menunggu kebangkitannya.</h2>
                <p>Dahulu Mariposa damai dan makmur. Sekitar lima tahun lalu, invasi misterius dari dimensi lain menghancurkan sebagian besar kerajaan. Sejak itu, makhluk dan kekuatan gelap muncul di berbagai wilayah.</p>
                <p>Kini, petualang baru dipanggil untuk membangun kembali Mariposa, menjelajahi tempat yang terlupakan, dan mengungkap misteri di balik ancaman tersebut.</p>
            </div>
            <div class="mp-rpg-lore__seal" aria-hidden="true"><div><span>✥</span><strong>Build Your Legacy,<br>Begin Your Adventure.</strong></div></div>
        </section>

        <section class="mp-rpg-guild" aria-labelledby="mp-rpg-guild-title">
            <div class="mp-rpg-section__heading">
                <span class="mp-rpg__eyebrow">Adventure Guild</span>
                <h2 id="mp-rpg-guild-title">Buktikan dirimu sebagai petualang.</h2>
                <p>Temui Guild Master, ambil kontrak sesuai rank, dan jalani ujian untuk naik ke tingkat berikutnya.</p>
            </div>
            <div class="mp-rpg-guild__ranks" aria-label="Urutan Guild Rank dari D hingga S"><span>D</span><b>→</b><span>C</span><b>→</b><span>B</span><b>→</b><span>A</span><b>→</b><span>S</span></div>
            <p class="mp-rpg-guild__copy">Guild Rank menunjukkan pencapaian petualangan, terpisah dari rank donor. Rank ini juga menjadi syarat akses untuk dungeon tertentu.</p>
            <div class="mp-rpg-guild__features">
                <div><strong>KONTRAK GUILD</strong><p>Hadapi monster, kumpulkan resource, atau jelajahi wilayah dalam misi sesuai rank.</p></div>
                <div><strong>UJIAN PROMOSI</strong><p>Selesaikan tantangan kenaikan rank untuk melanjutkan perjalanan dari D hingga S.</p></div>
                <div><strong>HADIAH MISI</strong><p>Dapatkan uang, item, dan Quest Points dari petualanganmu.</p></div>
            </div>
        </section>

        <section class="mp-rpg-dungeons" aria-labelledby="mp-rpg-dungeons-title">
            <div class="mp-rpg-section__heading">
                <span class="mp-rpg__eyebrow">Dungeons & Boss Battles</span>
                <h2 id="mp-rpg-dungeons-title">Masuki tempat yang terlupakan.</h2>
                <p>Lima dungeon telah dirancang dan dikonfigurasi dengan monster khusus, boss, serta tingkat kesulitan berbeda. Beberapa konten memakai party, instance, dan syarat Guild Rank.</p>
            </div>
            <div class="mp-rpg-dungeons__grid">
                <article class="mp-rpg-dungeon"><span class="mp-rpg-dungeon__index">01 / AZTEC</span><h3>Ancient Aztec Temple</h3><p>Kuil kuno untuk petualang pemula, dihuni Jaguar Warrior, Sun Archer, dan Jade Guardian.</p><span class="mp-rpg-dungeon__boss">BOSS · Kukulkan</span></article>
                <article class="mp-rpg-dungeon"><span class="mp-rpg-dungeon__index">02 / DARK MAGIC</span><h3>Hollow Sanctum</h3><p>Tempat gelap dan magis dengan undead serta penyihir, bagian dari progresi Guild.</p><span class="mp-rpg-dungeon__boss">BOSS · Archlich Nerath</span></article>
                <article class="mp-rpg-dungeon"><span class="mp-rpg-dungeon__index">03 / CURSED CRYPT</span><h3>Ebonveil Crypt</h3><p>Kastel dan ruang bawah tanah terkutuk untuk petualang yang lebih berpengalaman.</p><span class="mp-rpg-dungeon__boss">BOSS · Lord Vaelric</span></article>
                <article class="mp-rpg-dungeon"><span class="mp-rpg-dungeon__index">04 / HARD</span><h3>The Endless Mine</h3><p>Tambang berbahaya dengan monster kuat dan pertarungan intens untuk petualang tingkat lanjut.</p><span class="mp-rpg-dungeon__boss">BOSS · Azhur, the Witherheart</span></article>
                <article class="mp-rpg-dungeon"><span class="mp-rpg-dungeon__index">05 / LABYRINTH</span><h3>Maze Dungeon</h3><p>Temukan jalan melalui labirin sambil menghadapi monster dan pertarungan sihir.</p><span class="mp-rpg-dungeon__boss">BOSS · Undead Wizard</span></article>
            </div>
        </section>

        <section class="mp-rpg-quests" aria-labelledby="mp-rpg-quests-title">
            <div class="mp-rpg-section__heading">
                <span class="mp-rpg__eyebrow">Quest & Storytelling</span>
                <h2 id="mp-rpg-quests-title">Selalu ada cerita untuk diikuti.</h2>
                <p>Kenali dunia Mariposa, bantu Adventure Guild, dan temukan kisah para NPC melalui berbagai jenis quest.</p>
            </div>
            <div class="mp-rpg-quests__grid">
                <div class="mp-rpg-quest">Main Quest</div>
                <div class="mp-rpg-quest">Guild Quest</div>
                <div class="mp-rpg-quest">Rank Promotion Quest</div>
                <div class="mp-rpg-quest">Side Quest</div>
                <div class="mp-rpg-quest">Exploration Quest</div>
                <div class="mp-rpg-quest">NPC Story Quest</div>
            </div>
        </section>

        <section class="mp-rpg-cta" aria-labelledby="mp-rpg-community-title">
            <div><span class="mp-rpg__eyebrow">Komunitas Mariposa Indonesia</span><h2 id="mp-rpg-community-title">Petualangan lebih seru bersama.</h2><p>Temui pemain lain dan ikuti kabar server melalui Discord.</p></div>
            <a href="https://discord.gg/GSzE45fpz" target="_blank" rel="noopener noreferrer" class="btn-primary">Gabung Discord ↗</a>
        </section>
    </div>
</div>
@endsection
