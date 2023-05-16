DROP TABLE IF EXISTS `shopmanager_boxes`;
CREATE TABLE IF NOT EXISTS `shopmanager_boxes`
(
    `id`                  int(11)      NOT NULL,
    `zoomos_id`           int(11)               DEFAULT NULL,
    `price`               double(8, 2) NOT NULL,
    `price_old`           double(8, 2)          DEFAULT 0,
    `available`           tinyint(1)            DEFAULT 0,
    `quantity`            int(11)               DEFAULT 0,
    `category_id`         int(11)      NOT NULL,
    `category_name`       varchar(255)          DEFAULT NULL,
    `delivery`            tinyint(1)   NOT NULL,
    `type_prefix`         varchar(255) NOT NULL,
    `vendor`              varchar(255) NOT NULL,
    `model`               varchar(255) NOT NULL,
    `description`         text         NOT NULL,
    `picture`             varchar(255) NOT NULL,

    `diameter_bike_frame` varchar(255)          DEFAULT '' COMMENT 'Диаметр рамы велосипеда',
    `bikes_number`        SMALLINT              DEFAULT 0 COMMENT 'Количество велосипедов',
    `material`            varchar(255)          DEFAULT 0 COMMENT 'Материал',
    `installation_method` varchar(255)          DEFAULT 0 COMMENT 'Метод установки',
    `weight_limit`        varchar(255)          DEFAULT '' COMMENT 'Ограничение веса',
    `color`               varchar(255)          DEFAULT '' COMMENT 'Цвет',
    `weight`              varchar(255)          DEFAULT '' COMMENT 'Вес',

    `created_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
