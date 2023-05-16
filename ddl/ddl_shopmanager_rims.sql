DROP TABLE IF EXISTS `shopmanager_rims`;
CREATE TABLE IF NOT EXISTS `shopmanager_rims`
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

    `diameter`            int(11)               DEFAULT 0 COMMENT 'Диаметр колеса/диска',
    `wheel_type`          varchar(255)          DEFAULT '' COMMENT 'Тип колеса/диска',
    `param_et`            mediumint             DEFAULT 0 COMMENT 'ET',
    `param_pcd`           mediumint             DEFAULT 0 COMMENT 'PCD',
    `diameter_hub_hole`   FLOAT(4, 1)           DEFAULT 0 COMMENT 'Диаметр ступичного отверстия (мм)',
    `fixing_holes_number` smallint              DEFAULT 0 COMMENT 'Количество крепежных отверстий',
    `width`               smallint              DEFAULT 0 COMMENT 'Ширина диска в дюймах',

    `created_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`          timestamp    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
