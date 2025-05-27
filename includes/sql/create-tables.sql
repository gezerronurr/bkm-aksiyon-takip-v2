-- Aksiyonlar tablosu
CREATE TABLE IF NOT EXISTS `{prefix}bkm_aksiyonlar` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `kategori_id` bigint(20) NOT NULL,
    `tanimlayan_id` bigint(20) NOT NULL,
    `sorumlular` text NOT NULL,
    `acilma_tarihi` date NOT NULL,
    `hedef_tarih` date NOT NULL,
    `kapanma_tarihi` datetime DEFAULT NULL,
    `onem_derecesi` int(11) NOT NULL DEFAULT 0,
    `ilerleme_durumu` int(11) NOT NULL DEFAULT 0,
    `hafta` int(11) NOT NULL,
    `tespit_nedeni` text DEFAULT NULL,
    `aciklama` text DEFAULT NULL,
    `notlar` text DEFAULT NULL,
    `performans_id` bigint(20) DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `kategori_id` (`kategori_id`),
    KEY `tanimlayan_id` (`tanimlayan_id`),
    KEY `performans_id` (`performans_id`),
    CONSTRAINT `{prefix}bkm_aksiyonlar_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `{prefix}bkm_kategoriler` (`id`),
    CONSTRAINT `{prefix}bkm_aksiyonlar_ibfk_2` FOREIGN KEY (`tanimlayan_id`) REFERENCES `{prefix}users` (`ID`),
    CONSTRAINT `{prefix}bkm_aksiyonlar_ibfk_3` FOREIGN KEY (`performans_id`) REFERENCES `{prefix}bkm_performanslar` (`id`)
) {charset_collate};

-- Kategoriler tablosu
CREATE TABLE IF NOT EXISTS `{prefix}bkm_kategoriler` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `kategori_adi` varchar(255) NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `kategori_adi` (`kategori_adi`)
) {charset_collate};

-- Performanslar tablosu
CREATE TABLE IF NOT EXISTS `{prefix}bkm_performanslar` (
    `id` bigint(20) NOT NULL AUTO_INCREMENT,
    `performans_adi` varchar(255) NOT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `performans_adi` (`performans_adi`)
) {charset_collate};

-- Görevler tablosu
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
) {charset_collate}; 