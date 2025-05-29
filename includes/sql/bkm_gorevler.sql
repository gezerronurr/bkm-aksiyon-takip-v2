CREATE TABLE `{$wpdb->prefix}bkm_gorevler` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `aksiyon_id` bigint(20) NOT NULL,
    `gorev_icerik` text NOT NULL,
    `baslangic_tarihi` date NOT NULL,
    `sorumlu_kisi` bigint(20) NOT NULL,
    `hedef_tarih` date NOT NULL,
    `ilerleme_durumu` int(3) NOT NULL DEFAULT 0,
    `gercek_bitis_tarihi` date DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime NOT NULL,
    PRIMARY KEY (`id`),
    KEY `aksiyon_id` (`aksiyon_id`),
    KEY `sorumlu_kisi` (`sorumlu_kisi`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;