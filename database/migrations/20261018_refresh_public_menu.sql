START TRANSACTION;

DELETE FROM tbl_menu;

INSERT INTO tbl_menu (menu_id, menu_type, page_id, menu_name, menu_url, menu_order, menu_parent) VALUES
(1,  'Other', '',   'Beranda',       'modern/',                1, 0),
(2,  'Other', '',   'Profil',        '#',                      2, 0),
(3,  'Other', '',   'Program Studi', '#',                      3, 0),
(4,  'Other', '',   'PMB',           '#',                      4, 0),
(5,  'Other', '',   'Akademik',      '#',                      5, 0),
(6,  'Other', '',   'Berita',        'modern/#news',           6, 0),
(7,  'Page',  '2',  'Kontak',        '',                       9, 2),

(8,  'Page',  '1',  'Tentang STBA',  '',                       1, 2),
(9,  'Page',  '16', 'Visi & Misi',   '',                       2, 2),
(10, 'Page',  '23', 'Struktur Organisasi', '',                  3, 2),
(11, 'Page',  '15', 'Dosen & Tenaga Kependidikan', '',          4, 2),
(12, 'Page',  '26', 'Kemitraan',     '',                       5, 2),

(13, 'Other', '',   'S1 Sastra Inggris', 'modern/#program-studi', 1, 3),

(15, 'Page',  '22', 'Informasi PMB', '',                       1, 4),
(16, 'Page',  '17', 'Pendaftaran',   '',                       2, 4),
(17, 'Page',  '20', 'Biaya Kuliah',  '',                       3, 4),
(18, 'Page',  '19', 'Beasiswa',      '',                       4, 4),

(19, 'Page',  '21', 'Panduan Akademik', '',                    1, 5),
(20, 'Page',  '25', 'Panduan Tugas Akhir', '',                  2, 5),
(21, 'Page',  '24', 'Tracer Study',  '',                       3, 5);

ALTER TABLE tbl_menu AUTO_INCREMENT = 22;
COMMIT;
