-- Import berita baru dari "Berita STBA Prayoga.docx".
-- Jalankan setelah seluruh file pada assets/uploads ikut terunggah ke server.
-- Migrasi ini idempoten: berita dengan slug yang sama tidak akan diduplikasi.

SET NAMES utf8mb4;
START TRANSACTION;

INSERT INTO tbl_news
    (news_title, news_slug, news_content, news_content_short, news_date, photo,
     category_id, publisher, total_view, meta_title, meta_keyword, meta_description)
SELECT
    'Mahasiswa STBA Prayoga Padang Raih Dua Gelar Juara pada PORPROV XVI 2026 Cabang Shorinji Kempo',
    'mahasiswa-stba-prayoga-padang-raih-dua-gelar-juara-porprov-xvi-2026-shorinji-kempo',
    '<p>Mahasiswa STBA Prayoga Padang, <strong>Zyadane Fachri</strong>, berhasil menorehkan prestasi membanggakan dalam ajang Pekan Olahraga Provinsi (PORPROV) XVI Tahun 2026 pada cabang olahraga Shorinji Kempo. Dalam kompetisi tersebut, Zyadane berhasil meraih dua gelar juara II, yaitu pada nomor Embu Pasangan Putra Kyu 1 dan Embu Beregu Campuran.</p><p>Prestasi ini menjadi kebanggaan bagi keluarga besar STBA Prayoga Padang sekaligus menunjukkan bahwa mahasiswa mampu mengembangkan potensi dan meraih pencapaian positif tidak hanya di bidang akademik, tetapi juga di bidang olahraga. Keberhasilan Zyadane merupakan hasil dari semangat, kerja keras, latihan, dan dedikasi dalam mengikuti kompetisi.</p><p>STBA Prayoga Padang mengucapkan selamat dan sukses kepada Zyadane Fachri atas prestasi yang telah diraih. Semoga pencapaian ini menjadi motivasi untuk terus berkembang, meningkatkan kemampuan, dan meraih prestasi yang lebih tinggi pada kompetisi berikutnya.</p>',
    'Mahasiswa STBA Prayoga Padang, Zyadane Fachri, meraih dua gelar juara II cabang Shorinji Kempo pada PORPROV XVI Tahun 2026.',
    '2026-10-14',
    'news-porprov-zyadane-fachri-2026.jpeg',
    1,
    'Admin STBA',
    0,
    'Mahasiswa STBA Prayoga Padang Raih Dua Gelar Juara pada PORPROV XVI 2026 Cabang Shorinji Kempo',
    'STBA Prayoga, Zyadane Fachri, PORPROV XVI 2026, Shorinji Kempo, prestasi mahasiswa',
    'Zyadane Fachri, mahasiswa STBA Prayoga Padang, meraih dua gelar juara II cabang Shorinji Kempo pada PORPROV XVI Tahun 2026.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_news
    WHERE news_slug = 'mahasiswa-stba-prayoga-padang-raih-dua-gelar-juara-porprov-xvi-2026-shorinji-kempo'
);

INSERT INTO tbl_news
    (news_title, news_slug, news_content, news_content_short, news_date, photo,
     category_id, publisher, total_view, meta_title, meta_keyword, meta_description)
SELECT
    'Mahasiswa STBA Prayoga Padang Ikut Meriahkan English Festival 2026 di Universitas Negeri Padang',
    'mahasiswa-stba-prayoga-padang-ikut-meriahkan-english-festival-2026-di-universitas-negeri-padang',
    '<p>Padang, 17 Oktober 2026 — Mahasiswa Sekolah Tinggi Bahasa Asing (STBA) Prayoga Padang turut memeriahkan kegiatan <em>English Festival 2026</em> yang diselenggarakan oleh Departemen Bahasa dan Sastra Inggris, Fakultas Bahasa dan Seni, Universitas Negeri Padang (UNP), pada Sabtu, 17 Oktober 2026. Kegiatan ini mengusung tema <em>“Celebrating Creativity and Unity Through English Performance”</em> dan berlangsung di Teater Mursal Esten, Fakultas Bahasa dan Seni, Universitas Negeri Padang.</p><p>Keikutsertaan mahasiswa STBA Prayoga Padang dalam kegiatan ini menjadi kesempatan untuk menambah pengalaman, memperluas wawasan, serta mengenal berbagai kreativitas dan penampilan yang berkaitan dengan bahasa Inggris. Selain itu, kegiatan ini juga menjadi wadah bagi mahasiswa untuk membangun relasi dan bertukar pengalaman dengan mahasiswa dari perguruan tinggi lain.</p><p>Melalui partisipasi dalam <em>English Festival 2026</em>, STBA Prayoga Padang terus mendorong mahasiswa untuk aktif mengikuti kegiatan akademik maupun nonakademik guna mengembangkan kemampuan berbahasa Inggris, kreativitas, dan rasa percaya diri. Diharapkan, pengalaman ini dapat memberikan inspirasi serta motivasi bagi mahasiswa untuk terus mengembangkan potensi diri dan berpartisipasi dalam berbagai kegiatan positif di masa mendatang.</p><figure><img src="https://www.stba-prayoga.ac.id/assets/uploads/news-english-festival-2026-2.jpeg" alt="Mahasiswa STBA Prayoga Padang pada English Festival 2026 di Universitas Negeri Padang"></figure>',
    'Mahasiswa STBA Prayoga Padang berpartisipasi dalam English Festival 2026 di Universitas Negeri Padang untuk menambah pengalaman, wawasan, kreativitas, dan relasi.',
    '2026-10-17',
    'news-english-festival-2026-1.jpeg',
    1,
    'Admin STBA',
    0,
    'Mahasiswa STBA Prayoga Padang Ikut Meriahkan English Festival 2026 di Universitas Negeri Padang',
    'STBA Prayoga, English Festival 2026, Universitas Negeri Padang, mahasiswa, bahasa Inggris',
    'Mahasiswa STBA Prayoga Padang mengikuti English Festival 2026 di Universitas Negeri Padang untuk mengembangkan kemampuan bahasa Inggris, kreativitas, dan relasi.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_news
    WHERE news_slug = 'mahasiswa-stba-prayoga-padang-ikut-meriahkan-english-festival-2026-di-universitas-negeri-padang'
);

INSERT INTO tbl_news
    (news_title, news_slug, news_content, news_content_short, news_date, photo,
     category_id, publisher, total_view, meta_title, meta_keyword, meta_description)
SELECT
    'STBA Prayoga Padang Menerima Kunjungan Monitoring dan Evaluasi dari LLDIKTI',
    'stba-prayoga-padang-menerima-kunjungan-monitoring-dan-evaluasi-dari-lldikti',
    '<p>Padang, 19 Oktober 2026 — STBA Prayoga Padang menerima kunjungan Monitoring dan Evaluasi (Monev/ME) CKPT dari Lembaga Layanan Pendidikan Tinggi (LLDIKTI) pada Senin, 19 Oktober 2026. Kegiatan ini dilaksanakan sebagai bagian dari upaya pemantauan dan evaluasi terhadap pelaksanaan serta peningkatan mutu penyelenggaraan pendidikan tinggi.</p><p>Kunjungan tersebut menjadi kesempatan bagi STBA Prayoga Padang untuk melakukan evaluasi terhadap pelaksanaan program dan pengelolaan institusi, sekaligus memperoleh masukan serta arahan dari pihak LLDIKTI. Melalui kegiatan ini, diharapkan berbagai aspek penyelenggaraan pendidikan tinggi dapat terus ditingkatkan sesuai dengan standar dan ketentuan yang berlaku.</p><p>STBA Prayoga Padang menyambut baik pelaksanaan kegiatan monitoring dan evaluasi ini sebagai bentuk dukungan terhadap peningkatan kualitas tata kelola perguruan tinggi. Diharapkan, hasil dari kegiatan tersebut dapat menjadi bahan evaluasi dan tindak lanjut bagi institusi dalam mewujudkan penyelenggaraan pendidikan tinggi yang semakin berkualitas, efektif, dan berkelanjutan.</p><figure><img src="https://www.stba-prayoga.ac.id/assets/uploads/news-monev-lldikti-2026-2.jpeg" alt="Pelaksanaan monitoring dan evaluasi LLDIKTI di STBA Prayoga Padang"></figure>',
    'STBA Prayoga Padang menerima kunjungan Monitoring dan Evaluasi CKPT dari LLDIKTI sebagai bagian dari peningkatan mutu penyelenggaraan pendidikan tinggi.',
    '2026-10-19',
    'news-monev-lldikti-2026-1.jpeg',
    1,
    'Admin STBA',
    0,
    'STBA Prayoga Padang Menerima Kunjungan Monitoring dan Evaluasi dari LLDIKTI',
    'STBA Prayoga Padang, LLDIKTI, monitoring dan evaluasi, Monev CKPT, mutu pendidikan tinggi',
    'STBA Prayoga Padang menerima kunjungan monitoring dan evaluasi CKPT dari LLDIKTI untuk mendukung peningkatan mutu dan tata kelola perguruan tinggi.'
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM tbl_news
    WHERE news_slug = 'stba-prayoga-padang-menerima-kunjungan-monitoring-dan-evaluasi-dari-lldikti'
);

COMMIT;
