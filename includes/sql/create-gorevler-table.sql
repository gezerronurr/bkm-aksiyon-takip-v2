CREATE TABLE IF NOT EXISTS `{prefix}bkm_gorevler` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `aksiyon_id` bigint(20) NOT NULL,
    `gorev_icerigi` text NOT NULL,
    `baslangic_tarihi` date NOT NULL,
    `sorumlu_id` bigint(20) NOT NULL,
    `hedef_bitis_tarihi` date NOT NULL,
    `gercek_bitis_tarihi` datetime DEFAULT NULL,
    `ilerleme_durumu` int(11) NOT NULL DEFAULT 0,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `aksiyon_id` (`aksiyon_id`),
    KEY `sorumlu_id` (`sorumlu_id`),
    CONSTRAINT `{prefix}bkm_gorevler_ibfk_1` FOREIGN KEY (`aksiyon_id`) REFERENCES `{prefix}bkm_aksiyonlar` (`id`) ON DELETE CASCADE,
    CONSTRAINT `{prefix}bkm_gorevler_ibfk_2` FOREIGN KEY (`sorumlu_id`) REFERENCES `{prefix}users` (`ID`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 