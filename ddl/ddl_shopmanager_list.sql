    CREATE TABLE IF NOT EXISTS `shopmanager_offers`
    (
        `id`             int(11)      NOT NULL,
        `zoomos_id`      int(11)      DEFAULT NULL,
        `price`          double(8, 2) NOT NULL,
        `available`      tinyint(1)   DEFAULT 0,
        `quantity`       int(11)      DEFAULT 0,
        `category_id`    int(11)      NOT NULL,
        `category_name`  varchar(255) DEFAULT NULL,
        `delivery`       tinyint(1)   NOT NULL,
        `type_prefix`    varchar(255) NOT NULL,
        `vendor`         varchar(255) NOT NULL,
        `model`          varchar(255) NOT NULL,
        `description`    text         NOT NULL,
        `picture`        varchar(255) NOT NULL,
        `purpose`        varchar(255) DEFAULT '' COMMENT 'Назначение',
        `seat_diameter`  int(11)      DEFAULT 0 COMMENT 'Посадочный диаметр шины',
        `season`         varchar(255) DEFAULT '' COMMENT 'Сезонность шин',
        `profile_height` int(11)      DEFAULT 0 COMMENT 'Высота профиля шины',
        `width`          int(11)      DEFAULT 0 COMMENT 'Ширина шины',
        `release_date`   varchar(255) DEFAULT '' COMMENT 'Дата выхода на рынок',
        `run_flat`       tinyint(1)   DEFAULT 0 COMMENT 'Run-flat',
        `load_index`     int(11)      DEFAULT 0 COMMENT 'Индекс нагрузки шины',
        `speed_index`    varchar(255) DEFAULT '' COMMENT 'Индекс скорости',
        `construction`   varchar(255) DEFAULT '' COMMENT 'Конструкция',
        `type`           varchar(255) DEFAULT '' COMMENT 'Тип',
        `spikes`         tinyint(1)   DEFAULT 0 COMMENT 'Шипы',
        PRIMARY KEY (`id`)
    ) ENGINE = InnoDB
      DEFAULT CHARSET = utf8mb4
      COLLATE = utf8mb4_unicode_ci;
