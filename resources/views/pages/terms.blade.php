@extends('layouts.app')
@section('title', 'Syarat & Ketentuan')

@section('content')
<style>
    .tos h2 { font-size: 1.375rem; font-weight: 700; color: white; margin-bottom: 0.875rem; scroll-margin-top: 5.5rem; }
    .tos h3 { font-size: 0.9375rem; font-weight: 600; color: #cbd5e1; margin: 1.25rem 0 0.5rem; }
    .tos p, .tos li { color: #94a3b8; font-size: 0.875rem; line-height: 1.75; }
    .tos ul, .tos ol { margin: 0.5rem 0 0.5rem 1.25rem; display: flex; flex-direction: column; gap: 0.375rem; }
    .tos section { margin-bottom: 2.5rem; padding-bottom: 2.5rem; border-bottom: 1px solid rgba(255,255,255,0.06); }
    .tos section:last-of-type { border-bottom: none; }
    .tos strong { color: #cbd5e1; }
    .tos a { color: #a78bfa; }
    .toc a { display: block; color: #94a3b8; text-decoration: none; font-size: 0.8125rem; padding: 0.25rem 0; }
    .toc a:hover { color: #c4b5fd; }
</style>

<div style="max-width:48rem;margin:0 auto;padding:3rem 1.5rem;">
    <h1 style="font-size:2.5rem;font-weight:700;color:white;margin-bottom:0.5rem;">Syarat & Ketentuan Donasi & Layanan Mariposa</h1>
    <p style="color:#64748b;font-size:0.8125rem;margin-bottom:0.25rem;">Berlaku sejak: 15 September 2026 &nbsp;·&nbsp; Versi: 1.0</p>
    <p style="color:#94a3b8;margin-bottom:2.5rem;line-height:1.7;">Selamat datang di Mariposa. Terima kasih atas dukungan kamu terhadap server kami — kontribusi kamu sangat berarti bagi pengembangan dan pemeliharaan server. Dengan melakukan kontribusi (donasi/pembelian) dan/atau menggunakan layanan kami, kamu menyatakan telah membaca, memahami, dan menyetujui seluruh Syarat dan Ketentuan ("S&K") berikut.</p>

    <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.3);border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:2.5rem;">
        <h3 style="color:#f87171;font-weight:600;margin:0 0 0.5rem;">⚠ Ringkasan: Donasi Bersifat Final</h3>
        <p style="color:#fca5a5;font-size:0.875rem;line-height:1.7;margin:0;">Semua kontribusi pada dasarnya <strong style="color:#fca5a5;">tidak dapat dikembalikan (non-refundable)</strong> karena manfaat virtual (rank, koin, item) diberikan dan dinikmati secara langsung. Pengecualian terbatas cuma berlaku kalau manfaatnya <strong style="color:#fca5a5;">gagal terkirim karena kesalahan sistem kami</strong> atau terjadi <strong style="color:#fca5a5;">pembayaran ganda</strong> — lihat Bagian 9. Ini cuma ringkasan; baca dokumen lengkap di bawah sebelum membeli.</p>
    </div>

    <div style="background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:2.5rem;" class="toc">
        <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;margin-bottom:0.5rem;">Daftar Isi</div>
        <a href="#s1">1. Definisi</a>
        <a href="#s2">2. Manfaat yang Kamu Dapatkan dari Donasi</a>
        <a href="#s3">3. Hak dan Perlindungan Kamu sebagai Pengguna</a>
        <a href="#s4">4. Donasi: Pengisian Data dan Koreksi</a>
        <a href="#s5">5. Pembayaran</a>
        <a href="#s6">6. Privasi Data Kamu</a>
        <a href="#s7">7. Kontak</a>
        <a href="#s8">8. Ketentuan Penggunaan dan Sanksi</a>
        <a href="#s9">9. Pengembalian Dana (Refund)</a>
        <a href="#s10">10. Sumber Dana dan Kepatuhan Hukum</a>
        <a href="#s11">11. Pengguna di Bawah Umur</a>
        <a href="#s12">12. Perubahan Manfaat (Hak Pengelola)</a>
        <a href="#s13">13. Ketentuan Penutup</a>
    </div>

    <div class="tos">

        <section id="s1">
            <h2>1. Definisi</h2>
            <ul>
                <li><strong>Mariposa</strong> — layanan server Minecraft (termasuk seluruh mode permainan di dalamnya, mis. Survival, ChunkSMP, Anarchy) yang kami kelola dan operasikan di <code style="color:#a78bfa;">play.mariposa.id</code>.</li>
                <li><strong>Kontribusi</strong> — pembayaran sukarela dari Pengguna melalui Store untuk mendukung operasional server, yang diiringi pemberian Manfaat Virtual sebagai bentuk apresiasi.</li>
                <li><strong>Manfaat Virtual</strong> — rank, koin, item, crate key, dan/atau fitur lain yang diberikan kepada Pengguna sebagai apresiasi atas kontribusinya. Manfaat Virtual hanya berlaku di dalam server Mariposa.</li>
                <li><strong>Pengguna / Kamu</strong> — individu yang menggunakan layanan kami dan/atau memberikan kontribusi.</li>
                <li><strong>Pengelola / Kami</strong> — pihak yang menjalankan dan bertanggung jawab atas server Mariposa.</li>
            </ul>
        </section>

        <section id="s2">
            <h2>2. Manfaat yang Kamu Dapatkan dari Donasi</h2>

            <h3>2.1. Kontribusi</h3>
            <p>Kontribusi kamu bersifat sukarela dan ditujukan untuk mendukung operasional, pengembangan, dan pemeliharaan server. Sebagai apresiasi, kamu menerima Manfaat Virtual sesuai paket yang dipilih di Store (termasuk durasi rank kalau produknya berupa langganan 7 Hari / 30 Hari / Permanent).</p>

            <h3>2.2. Manfaat Virtual</h3>
            <ul>
                <li>Hanya berlaku di server Mariposa dan tidak dapat digunakan di server atau platform lain.</li>
                <li>Tidak dapat dipindahtangankan, dijual, atau ditukar antar akun.</li>
                <li>Bukan merupakan uang, mata uang, aset digital, properti, atau barang yang dapat diuangkan kembali.</li>
                <li>Bersifat lisensi penggunaan terbatas yang Kami berikan kepada kamu, dan bukan kepemilikan. Lisensi ini dapat dicabut sesuai S&K ini.</li>
            </ul>

            <h3>2.3. Disclaimer</h3>
            <p>Manfaat Virtual dirancang agar tidak memberikan keunggulan yang tidak adil dan tetap sejalan dengan ketentuan penggunaan resmi Minecraft (Mojang/Microsoft). Kami berhak menyesuaikan jenis manfaat agar tetap mematuhi ketentuan tersebut.</p>
        </section>

        <section id="s3">
            <h2>3. Hak dan Perlindungan Kamu sebagai Pengguna</h2>

            <h3>3.1. Non-Diskriminasi</h3>
            <p>Kami memperlakukan seluruh Pengguna secara setara dalam penerapan aturan server, penegakan sanksi, proses donasi, dan penanganan komplain — tanpa membedakan berdasarkan suku, agama, ras, antargolongan, gender, kebangsaan, kondisi fisik, atau latar belakang pribadi lainnya. Ketentuan ini tidak mengurangi perbedaan akses terhadap Manfaat Virtual yang memang merupakan bagian dari sistem donasi sebagaimana diatur pada Bagian 2, karena perbedaan tersebut bersifat sukarela dan terbuka bagi siapa pun yang memilih untuk berkontribusi.</p>

            <h3>3.2. Hak Mengajukan Keberatan</h3>
            <p>Setiap Pengguna — baik donatur maupun bukan — berhak mengajukan keberatan, klarifikasi, atau komplain atas keputusan moderasi, kendala teknis, atau hal lain terkait layanan Mariposa, melalui Discord resmi kami (Bagian 7). Setiap keberatan yang diajukan akan Kami tanggapi secara wajar. Pengajuan keberatan merupakan bagian dari proses peninjauan dan tidak menjamin pembatalan suatu keputusan; keputusan akhir tetap berada pada kewenangan tim moderasi sebagaimana diatur pada poin 8.1.</p>

            <h3>3.3. Ketidaksesuaian Manfaat yang Diterima</h3>
            <p>Apabila Manfaat Virtual yang kamu terima tidak sesuai dengan deskripsi paket yang berlaku pada saat transaksi — misalnya akibat informasi di Store yang belum diperbarui, fitur yang belum diberikan, atau jumlah koin yang kurang dari seharusnya — kamu dapat mengajukan komplain melalui Discord resmi kami (Bagian 7) disertai bukti transaksi (Order ID), paling lambat 14 (empat belas) hari sejak transaksi dilakukan. Kami wajib menindaklanjuti dan melengkapi atau menyesuaikan Manfaat Virtual agar sesuai dengan yang seharusnya kamu terima. Pelengkapan/penyesuaian ini merupakan bentuk penyelesaian utama untuk ketidaksesuaian semacam ini dan diprioritaskan dibanding pengembalian dana sebagaimana diatur pada Bagian 9.</p>

            <h3>3.4. Pemberitahuan dan Non-Retroaktif atas Perubahan Manfaat</h3>
            <p>Perubahan yang mengurangi manfaat inti dari rank atau koin yang sudah kamu miliki akan diumumkan melalui kanal resmi (Discord) paling lambat 7 hari sebelum diberlakukan. Manfaat yang telah dibeli sebelum pengumuman tersebut tetap berlaku sesuai deskripsi pada saat pembelian dan tidak berkurang secara retroaktif, kecuali perubahan dilakukan untuk:</p>
            <ol>
                <li>mematuhi kebijakan resmi Minecraft (Mojang/Microsoft) sebagaimana dimaksud pada poin 2.3; atau</li>
                <li>menutup eksploitasi, bug, atau celah keamanan yang berisiko membahayakan keseimbangan atau keberlangsungan server,</li>
            </ol>
            <p>yang dalam hal demikian dapat diberlakukan segera demi kepentingan bersama. Hak Kami untuk mengubah Manfaat Virtual secara umum diatur lebih lanjut pada Bagian 12.</p>
        </section>

        <section id="s4">
            <h2>4. Donasi: Pengisian Data dan Koreksi</h2>

            <h3>4.1. Format Pengisian Data Akun</h3>
            <p>Saat checkout, kamu wajib memilih platform yang sesuai (Java atau Bedrock) dan mengisi username Minecraft dengan benar. Khusus pemain Bedrock, sistem kami otomatis menambahkan tanda titik (<code style="color:#a78bfa;">.</code>) di depan username (contoh: <code style="color:#a78bfa;">.NamaKamu</code>) sesuai format yang berlaku di server. Kamu bertanggung jawab memeriksa dan memastikan platform serta username sudah benar sebelum menyelesaikan pembayaran, karena Manfaat Virtual dikirim otomatis ke username yang terverifikasi pada transaksi tersebut.</p>

            <h3>4.2. Koreksi Kesalahan Data</h3>
            <p>Meskipun kesalahan input merupakan tanggung jawab kamu, Kami dapat membantu koreksi secara case-by-case melalui Discord resmi kami (Bagian 7), dengan syarat:</p>
            <ul>
                <li>diajukan dalam waktu paling lambat 48 jam sejak Manfaat Virtual diberikan;</li>
                <li>disertai bukti pembayaran/transaksi (Order ID) yang valid; dan</li>
                <li>terbukti merupakan kesalahan penulisan nama atau kesalahan pemilihan platform (Java/Bedrock) yang wajar.</li>
            </ul>
            <p>Permintaan koreksi tidak akan diproses apabila terindikasi sebagai upaya agar Manfaat Virtual lebih dulu diterima oleh suatu akun untuk kemudian "dipindahkan"/diklaim ulang ke akun lain, mengingat Manfaat Virtual bersifat tidak dapat dipindahtangankan (lihat poin 2.2). Tindakan tersebut dapat dikenai sanksi sesuai Bagian 8. Keputusan atas permintaan koreksi bersifat final dan menjadi kewenangan Kami.</p>
        </section>

        <section id="s5">
            <h2>5. Pembayaran</h2>

            <h3>5.1. Metode Pembayaran</h3>
            <p>Kontribusi hanya boleh dilakukan melalui metode pembayaran resmi yang Kami sediakan di Store (diproses lewat Midtrans — QRIS, Transfer Bank, GoPay, OVO, dan metode lain yang tersedia). Kami tidak bertanggung jawab atas pembayaran melalui pihak ketiga tidak resmi atau di luar sistem Store.</p>

            <h3>5.2. Biaya Tambahan</h3>
            <p>Biaya transaksi, biaya admin, atau selisih konversi mata uang menjadi tanggung jawab kamu.</p>

            <h3>5.3. Proses Transaksi</h3>
            <p>Manfaat Virtual dikirim otomatis ke server begitu pembayaran terkonfirmasi berhasil. Ikuti seluruh prosedur verifikasi username dan konfirmasi pembayaran agar Manfaat Virtual dapat segera diproses. Waktu pemrosesan dapat bervariasi tergantung kondisi server.</p>
        </section>

        <section id="s6">
            <h2>6. Privasi Data Kamu</h2>

            <h3>6.1. Pengumpulan Data</h3>
            <p>Untuk memproses kontribusi, Kami dapat mengumpulkan data tertentu seperti username Minecraft, riwayat transaksi, dan informasi yang diperlukan oleh penyedia pembayaran (Midtrans). Kalau kamu menghubungi kami lewat Discord untuk dukungan, kami juga dapat menerima informasi dari interaksi tersebut.</p>

            <h3>6.2. Penggunaan Data</h3>
            <p>Data digunakan semata-mata untuk memproses kontribusi, pemberian Manfaat Virtual, dan dukungan layanan. Kami tidak menjual data kamu kepada pihak ketiga.</p>

            <h3>6.3. Perlindungan & Kerahasiaan Data</h3>
            <p>Pemrosesan data dilakukan sesuai ketentuan hukum perlindungan data pribadi yang berlaku di Indonesia.</p>
        </section>

        <section id="s7">
            <h2>7. Kontak</h2>
            <p>Discord resmi Kami adalah kanal utama untuk pertanyaan, pengajuan keberatan (poin 3.2), komplain ketidaksesuaian manfaat (poin 3.3), koreksi data (poin 4.2), maupun permintaan pengembalian dana (Bagian 9).</p>
            <p>Discord: <a href="https://discord.gg/GSzE45fpz" target="_blank" rel="noopener noreferrer">discord.gg/GSzE45fpz</a></p>
        </section>

        <section id="s8">
            <h2>8. Ketentuan Penggunaan dan Sanksi</h2>
            <p>Dengan berkontribusi, kamu setuju untuk:</p>

            <h3>8.1. Mematuhi aturan server — status donatur tidak memberikan kekebalan</h3>
            <ul>
                <li>Donasi yang kamu berikan adalah bentuk dukungan terhadap layanan, bukan pembelian hak istimewa untuk melanggar aturan, dan bukan "asuransi" atas pelanggaran yang kamu lakukan.</li>
                <li>Kamu tetap wajib mematuhi seluruh <a href="{{ route('rules') }}">peraturan server</a>, kebijakan, dan keputusan tim moderasi Mariposa, baik di dalam server, Discord, maupun platform resmi lain — tanpa terkecuali, terlepas dari rank, koin, jumlah donasi, atau status lain yang kamu miliki.</li>
                <li>Tindakan disipliner (peringatan, mute, kick, ban sementara, hingga ban permanen) tetap berlaku secara penuh terhadap donatur sebagaimana berlaku terhadap pengguna lain. Rank atau koin tidak: membebaskan kamu dari sanksi atas pelanggaran yang dilakukan sebelum maupun setelah berdonasi; menjadi alasan untuk peninjauan ulang, keringanan, atau pembatalan sanksi yang sudah dijatuhkan; atau menjadi bahan negosiasi ("saya sudah donasi, masa di-ban") terhadap keputusan tim moderasi.</li>
                <li><strong>Akibat terhadap donasi jika terkena sanksi.</strong> Apabila akun kamu dikenai sanksi karena melanggar aturan — termasuk namun tidak terbatas pada cheating, eksploitasi bug, toxic behavior, penipuan terhadap pengguna lain, atau pelanggaran lain — maka: donasi yang telah kamu berikan tetap dianggap sah dan final sebagaimana diatur dalam Bagian 9; tidak ada pengembalian dana dalam bentuk apa pun atas dasar akun terkena sanksi; dan Manfaat Virtual yang melekat pada akun tersebut dapat dicabut, dibekukan, atau dihapus sesuai berat pelanggaran, tanpa kompensasi. Ketentuan ini berlaku terlepas dari kapan donasi dilakukan — baik sebelum, bersamaan, maupun setelah pelanggaran terjadi.</li>
            </ul>

            <h3>8.2. Tidak menyalahgunakan Manfaat Virtual</h3>
            <p>Penyalahgunaan rank, koin, item, bug, atau eksploitasi sistem dapat mengakibatkan sanksi hingga ban permanen serta pencabutan manfaat tanpa pengembalian dana.</p>

            <h3>8.3. Menanggung risiko keberlangsungan layanan</h3>
            <p>Kami berupaya maksimal menjaga server tetap online, namun tidak dapat menjamin layanan selalu tersedia tanpa gangguan. Jika terjadi peristiwa di luar kendali (force majeure), penutupan, atau gangguan serius yang menyebabkan Manfaat Virtual tidak lagi dapat digunakan, Kami tidak berkewajiban memberikan kompensasi, kecuali diatur lain dalam Bagian 9.</p>

            <h3>8.4. Transaksi di Luar Sistem Resmi (Jual-Beli Antar Pemain)</h3>
            <p>Segala bentuk jual-beli, tukar-menukar, titip-beli, atau transaksi lain antar Pengguna yang dilakukan <strong>di luar Store resmi Mariposa</strong> — termasuk namun tidak terbatas pada jual-beli akun, rank, item, koin secara pribadi/player-to-player, real-money trading (RMT), atau kesepakatan lain di luar platform kami (baik dilakukan di dalam server, Discord, maupun platform pihak ketiga) — sepenuhnya merupakan tanggung jawab dan risiko masing-masing pihak yang terlibat. Kami tidak terlibat, tidak memfasilitasi, tidak menjamin, dan tidak bertanggung jawab atas kerugian, penipuan, atau perselisihan dalam bentuk apa pun yang timbul dari transaksi semacam itu.</p>
            <p>Pengguna yang terbukti melakukan penipuan dalam transaksi tersebut tetap dapat dikenai sanksi sesuai peraturan server, namun penjatuhan sanksi tersebut tidak menimbulkan kewajiban ganti rugi atau pengembalian dana dari Kami kepada pihak yang dirugikan.</p>
        </section>

        <section id="s9">
            <h2>9. Pengembalian Dana (Refund)</h2>

            <h3>9.1. Prinsip Umum</h3>
            <p>Karena Manfaat Virtual diberikan dan dinikmati secara langsung, kontribusi pada dasarnya bersifat final dan tidak dapat dikembalikan.</p>

            <h3>9.2. Pengecualian</h3>
            <p>Pengembalian dapat dipertimbangkan jika:</p>
            <ul>
                <li>Manfaat Virtual tidak terkirim setelah pembayaran berhasil dan kesalahan berada di pihak Kami; atau</li>
                <li>Terjadi pembayaran ganda (double payment) untuk transaksi yang sama.</li>
            </ul>

            <h3>9.3. Prosedur</h3>
            <p>Permintaan pengembalian pada poin 9.2 wajib diajukan melalui Discord resmi kami (Bagian 7) dalam waktu 7 (tujuh) hari sejak transaksi, disertai bukti pembayaran (Order ID).</p>

            <h3>9.4. Chargeback / Pembatalan Sepihak</h3>
            <p>Pengajuan chargeback, sengketa pembayaran, atau pembatalan transaksi secara sepihak tanpa terlebih dahulu menghubungi Kami akan dianggap pelanggaran dan dapat mengakibatkan ban permanen serta pencabutan seluruh Manfaat Virtual tanpa pengembalian dana.</p>
        </section>

        <section id="s10">
            <h2>10. Sumber Dana, Kepatuhan Hukum, dan Penyitaan Aset</h2>

            <h3>10.1. Sumber Dana yang Sah</h3>
            <p>Dengan melakukan kontribusi, kamu menyatakan dan menjamin bahwa dana yang digunakan adalah milik kamu sendiri atau telah diperoleh secara sah, dan bukan berasal dari hasil tindak pidana — termasuk namun tidak terbatas pada korupsi, penggelapan, penipuan, atau tindak pidana pencucian uang. Segala akibat hukum atas pelanggaran pernyataan ini menjadi tanggung jawab penuh kamu.</p>

            <h3>10.2. Kepatuhan terhadap Perintah Hukum dan Penyitaan Aset</h3>
            <p>Apabila terdapat permintaan atau perintah resmi dari aparat penegak hukum, pengadilan, atau otoritas yang berwenang sehubungan dengan dugaan bahwa dana yang digunakan untuk kontribusi berasal dari tindak pidana, Kami akan bekerja sama sepenuhnya, termasuk menyerahkan dana dan/atau data transaksi terkait sesuai perintah tersebut. Dalam hal ini:</p>
            <ul>
                <li>Penyerahan dana dilakukan kepada otoritas/pihak yang berhak menerimanya sesuai perintah hukum yang berlaku — bukan pengembalian kepada Pengguna yang bersangkutan, sehingga ketentuan non-refund pada poin 9.1 tidak menghalangi kepatuhan ini.</li>
                <li>Manfaat Virtual yang terkait dengan dana tersebut dapat dibekukan, dicabut, atau dihapus tanpa kompensasi kepada Pengguna.</li>
                <li>Pengguna yang terbukti menggunakan dana ilegal untuk berkontribusi tetap bertanggung jawab penuh secara hukum atas asal-usul dana tersebut sebagaimana diatur dalam poin 10.1; Kami tidak menanggung akibat hukum apa pun yang timbul dari tindakan Pengguna.</li>
                <li><strong>Hak Tagih Balik (Indemnifikasi).</strong> Apabila Kami diwajibkan menyerahkan, mengembalikan, atau mengganti sejumlah dana sehubungan dengan kontribusi yang terbukti berasal dari tindak pidana — termasuk dalam hal dana tersebut telah digunakan untuk operasional dan tidak lagi tersedia secara fisik — Pengguna yang bersangkutan wajib mengganti seluruh nilai tersebut kepada Kami, beserta biaya wajar lain yang timbul akibatnya (termasuk biaya hukum). Hak tagih ini berlaku terlepas dari sanksi atau proses hukum lain yang mungkin dihadapi Pengguna.</li>
            </ul>

            <h3>10.3. Pengungkapan kepada Aparat Penegak Hukum</h3>
            <p>Kami dapat mengungkapkan data transaksi dan data terkait lainnya kepada aparat penegak hukum, pengadilan, atau otoritas yang berwenang apabila diwajibkan oleh hukum yang berlaku, termasuk dalam rangka penegakan tindak pidana pencucian uang dan pengembalian aset hasil tindak pidana sebagaimana dimaksud dalam poin 10.2.</p>
        </section>

        <section id="s11">
            <h2>11. Pengguna di Bawah Umur</h2>

            <h3>11.1. Persyaratan Usia</h3>
            <p>Jika kamu berusia di bawah 18 tahun atau belum dianggap dewasa secara hukum, kamu hanya boleh berkontribusi setelah mendapat izin dari orang tua atau wali yang sah.</p>

            <h3>11.2. Tanggung Jawab Orang Tua/Wali</h3>
            <p>Kontribusi yang dilakukan oleh anak di bawah umur dianggap telah memperoleh izin dan menggunakan dana milik orang tua/wali yang bertanggung jawab. Kami tidak bertanggung jawab atas kontribusi yang dilakukan tanpa izin tersebut.</p>
        </section>

        <section id="s12">
            <h2>12. Perubahan Manfaat (Hak Pengelola)</h2>

            <h3>12.1. Penyesuaian Fitur dan Manfaat</h3>
            <p>Kami berhak mengubah, menambah, mengurangi, atau menyesuaikan fitur dan manfaat yang terkait dengan rank, koin, dan item sewaktu-waktu, demi menjaga keseimbangan dan keberlangsungan layanan. Ketentuan mengenai pemberitahuan dan non-retroaktif atas perubahan yang mengurangi manfaat yang sudah kamu miliki diatur dalam poin 3.4.</p>

            <h3>12.2. Dampak terhadap Refund</h3>
            <p>Perubahan sebagaimana dimaksud pada poin ini tidak memberikan hak untuk meminta pengembalian dana, kecuali sebagaimana diatur dalam Bagian 9.</p>
        </section>

        <section id="s13" style="margin-bottom:1rem;">
            <h2>13. Ketentuan Penutup</h2>

            <h3>13.1. Perubahan S&K</h3>
            <p>Kami dapat memperbarui S&K ini sewaktu-waktu. Versi terbaru akan diumumkan melalui kanal resmi Kami. Dengan terus menggunakan layanan setelah perubahan berlaku, kamu dianggap menyetujui perubahan tersebut.</p>

            <h3>13.2. Keterpisahan (Severability)</h3>
            <p>Jika salah satu ketentuan dalam S&K ini dinyatakan tidak sah atau tidak dapat diberlakukan, ketentuan lainnya tetap berlaku penuh.</p>

            <h3>13.3. Hukum yang Berlaku</h3>
            <p>S&K ini tunduk pada dan ditafsirkan menurut hukum Negara Republik Indonesia.</p>

            <h3>13.4. Penyelesaian Sengketa</h3>
            <p>Setiap perselisihan akan diupayakan diselesaikan terlebih dahulu secara musyawarah melalui Discord resmi Kami sebelum menempuh jalur lain.</p>
        </section>

    </div>

    <p style="color:#64748b;font-size:0.75rem;line-height:1.7;">Dokumen ini merupakan template dan bukan nasihat hukum. Untuk kepastian hukum, disarankan berkonsultasi dengan profesional hukum.</p>
</div>
@endsection
