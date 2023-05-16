<?php

declare(strict_types=1);

use LireinCore\YMLParser\Offer\VendorModelOffer;
use ShopManApi\Entity\OfferParams;
use ShopManApi\ShopManApi;

//die('Disabled. The code moved to the main Laravel application.');

require __DIR__ . '/vendor/autoload.php';

echo 'Start at ' . date('h:i:s Y-m-d') . PHP_EOL;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required([
    'API_SHOPMANAGER_SHOP_ID',
    'API_SHOPMANAGER_SHOP_ID',
    'API_SHOPMANAGER_FILE',
    'DB_HOST',
    'DB_DATABASE',
    'DB_USERNAME',
    'DB_PASSWORD'
]);
$shopId = (int)$_ENV['API_SHOPMANAGER_SHOP_ID'];
$key = $_ENV['API_SHOPMANAGER_KEY'];
$file = $_ENV['API_SHOPMANAGER_FILE'];

$handler = (new ShopManApi($shopId, $key))->parseYml($file);

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_DATABASE']
    ),
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD'],
    [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']
);

foreach ($handler->getOffers() as $offer) {
    /** @var VendorModelOffer $offer */

    if ($offer->getPrice() > 9000) {
        echo sprintf(
                'Probably too big price %s is for offer %d.',
                $offer->getPrice(),
                $offer->getId(),
            ) . PHP_EOL;

        continue;
    }

    $typePrefix = mb_strtolower($offer->getTypePrefix());

    if (str_contains($typePrefix, 'шин')) {
        $table = 'shopmanager_offers';
    } elseif (str_contains($typePrefix, 'диск')) {
        $table = 'shopmanager_rims';
    } else {
        $table = 'shopmanager_boxes';
    }

    /** @var VendorModelOffer $offer */
    $stmt = $pdo->prepare("SELECT id FROM $table WHERE id = :id");
    $stmt->execute(['id' => $offer->getId()]);
    $id = $stmt->fetch(PDO::FETCH_COLUMN) ?: 0;

    try {
        if ('shopmanager_offers' === $table) {
            syncTireOffer($pdo, $offer, $id, $table);
        } else if ('shopmanager_rims' === $table) {
            syncRimOffer($pdo, $offer, $id, $table);
        } else if ('shopmanager_boxes' === $table) {
            syncBoxOffer($pdo, $offer, $id, $table);
        } else {
            throw new RuntimeException('Unknown table');
        }
    } catch (RuntimeException $e) {
        echo "RuntimeException:" . $e->getMessage() . PHP_EOL;
    } catch (PDOException $e) {
        echo "Error with ID $id:" . $e->getMessage() . PHP_EOL;
        echo "Цена: " . $offer->getPrice() . PHP_EOL;
        echo '--' . PHP_EOL;
    }
}

echo 'End at ' . date('h:i:s Y-m-d') . PHP_EOL;


function syncTireOffer(PDO $pdo, VendorModelOffer $offer, int $id, string $table): void
{
    $offerParams = new OfferParams($offer->getParams());

    if ($id) {
        $stmt = $pdo->prepare(
            sprintf(
                'UPDATE %s
            SET price = :price, 
                price_old = :price_old, 
                available = :available, 
                quantity = :quantity, 
                category_id = :category_id, 
                delivery = :delivery, 
                type_prefix = :type_prefix, 
                vendor = :vendor, 
                model = :model, 
                description = :description, 
                picture = :picture, 
                
                purpose = :purpose, 
                seat_diameter = :seat_diameter, 
                season = :season, 
                profile_height = :profile_height, 
                width = :width, 
                release_date = :release_date, 
                run_flat = :run_flat, 
                load_index = :load_index, 
                speed_index = :speed_index, 
                construction = :construction, 
                type = :type, 
                spikes = :spikes
            WHERE id = :id',
                $table
            )
        );
    } else {
        $stmt = $pdo->prepare(
            sprintf(
                'INSERT INTO %s
                (
                 id, 
                 price, 
                 price_old, 
                 available, 
                 quantity, 
                 category_id,
                 delivery,
                 type_prefix,
                 vendor,
                 model,
                 description,
                 picture,
                 purpose,
                 seat_diameter,
                 season,
                 profile_height,
                 width,
                 release_date,
                 run_flat,
                 load_index,
                 speed_index,
                 construction,
                 type,
                 spikes
                 )
            VALUES (
                    :id, 
                    :price,
                    :price_old,
                    :available,
                    :quantity,
                    :category_id,
                    :delivery,
                    :type_prefix,
                    :vendor,
                    :model,
                    :description,
                    :picture,
                    :purpose,
                    :seat_diameter,
                    :season,
                    :profile_height,
                    :width,
                    :release_date,
                    :run_flat,
                    :load_index,
                    :speed_index,
                    :construction,
                    :type,
                    :spikes
                )',
                $table
            )
        );
    }

    $stmt->execute([
        'id' => $offer->getId(),
        'price' => $offer->getPrice(),
        'price_old' => $offer->getOldprice(),
        'available' => (int)$offer->getAvailable(),
        'quantity' => (int)$offer->getQuantityInStock(),
        'category_id' => $offer->getCategoryId(),
        'delivery' => $offer->getDelivery(),
        'type_prefix' => $offer->getTypePrefix(),
        'vendor' => $offer->getVendor(),
        'model' => $offer->getModel(),
        'description' => $offer->getDescription(),
        'picture' => $offer->getPictures()[0] ?? '',

        'purpose' => $offerParams->getParamValue('Назначение') ?? '',
        'seat_diameter' => $offerParams->getParamValueInt('Посадочный диаметр шины') ?? 0,
        'season' => $offerParams->getParamValue('Сезонность шин') ?? '',
        'profile_height' => $offerParams->getParamValueInt('Высота профиля шины') ?? 0,
        'width' => $offerParams->getParamValueInt('Ширина шины') ?? 0,
        'release_date' => $offerParams->getParamValue('Дата выхода на рынок') ?? '',
        'run_flat' => (int)$offerParams->getParamValueBool('Дата выхода на рынок'),
        'load_index' => $offerParams->getParamValueInt('Индекс нагрузки шины') ?? 0,
        'speed_index' => $offerParams->getParamValue('Индекс скорости шины') ?? '',
        'construction' => $offerParams->getParamValue('Конструкция') ?? '',
        'type' => $offerParams->getParamValue('Тип') ?? '',
        'spikes' => (int)$offerParams->getParamValueBool('Шипы'),
    ]);
}


function syncRimOffer(PDO $pdo, VendorModelOffer $offer, int $id, string $table): void
{
    $offerParams = new OfferParams($offer->getParams());

    if ($id) {
        $stmt = $pdo->prepare(
            sprintf(
                'UPDATE %s
            SET price = :price, 
                price_old = :price_old, 
                available = :available, 
                quantity = :quantity, 
                category_id = :category_id, 
                delivery = :delivery, 
                type_prefix = :type_prefix, 
                vendor = :vendor, 
                model = :model, 
                description = :description, 
                picture = :picture, 
                
                diameter = :diameter, 
                wheel_type = :wheel_type, 
                param_et = :param_et, 
                param_pcd = :param_pcd, 
                diameter_hub_hole = :diameter_hub_hole, 
                fixing_holes_number = :fixing_holes_number, 
                width = :width, 
            WHERE id = :id',
                $table
            )
        );
    } else {
        $stmt = $pdo->prepare(
            sprintf(
                'INSERT INTO %s
                (
                 id, 
                 price, 
                 price_old, 
                 available, 
                 quantity, 
                 category_id,
                 delivery,
                 type_prefix,
                 vendor,
                 model,
                 description,
                 picture,
                 
                 diameter,
                 wheel_type,
                 param_et,
                 param_pcd,
                 diameter_hub_hole,
                 fixing_holes_number,
                 width
                 )
            VALUES (
                    :id, 
                    :price,
                    :price_old,
                    :available,
                    :quantity,
                    :category_id,
                    :delivery,
                    :type_prefix,
                    :vendor,
                    :model,
                    :description,
                    :picture,
                    
                    :diameter,
                    :wheel_type,
                    :param_et,
                    :param_pcd,
                    :diameter_hub_hole,
                    :fixing_holes_number,
                    :width
                )',
                $table
            )
        );
    }

    $stmt->execute([
        'id' => $offer->getId(),
        'price' => $offer->getPrice(),
        'price_old' => $offer->getOldprice(),
        'available' => (int)$offer->getAvailable(),
        'quantity' => (int)$offer->getQuantityInStock(),
        'category_id' => $offer->getCategoryId(),
        'delivery' => $offer->getDelivery(),
        'type_prefix' => $offer->getTypePrefix(),
        'vendor' => $offer->getVendor(),
        'model' => $offer->getModel(),
        'description' => $offer->getDescription(),
        'picture' => $offer->getPictures()[0] ?? '',

        'diameter' => $offerParams->getParamValueInt('Диаметр колеса/диска') ?? 0,
        'wheel_type' => $offerParams->getParamValue('Тип') ?? '',
        'param_et' => $offerParams->getParamValueInt('ET') ?? 0,
        'param_pcd' => $offerParams->getParamValueInt('PCD') ?? 0,
        'diameter_hub_hole' => (float)$offerParams->getParamValue('Диаметр ступичного отверстия') ?? 0,
        'fixing_holes_number' => (float)$offerParams->getParamValue('Количество крепежных отверстий') ?? 0,
        'width' => $offerParams->getParamValueInt('Ширина диска в дюймах') ?? 0,
    ]);
}



function syncBoxOffer(PDO $pdo, VendorModelOffer $offer, int $id, string $table): void
{
    $offerParams = new OfferParams($offer->getParams());

    if ($id) {
        $stmt = $pdo->prepare(
            sprintf(
                'UPDATE %s
            SET price = :price, 
                price_old = :price_old, 
                available = :available, 
                quantity = :quantity, 
                category_id = :category_id, 
                delivery = :delivery, 
                type_prefix = :type_prefix, 
                vendor = :vendor, 
                model = :model, 
                description = :description, 
                picture = :picture, 
                
                diameter_bike_frame = :diameter_bike_frame, 
                bikes_number = :bikes_number, 
                material = :material, 
                installation_method = :installation_method, 
                weight_limit = :weight_limit, 
                color = :color, 
                weight = :weight
            WHERE id = :id',
                $table
            )
        );
    } else {
        $stmt = $pdo->prepare(
            sprintf(
                'INSERT INTO %s
                (
                 id, 
                 price, 
                 price_old, 
                 available, 
                 quantity, 
                 category_id,
                 delivery,
                 type_prefix,
                 vendor,
                 model,
                 description,
                 picture,
                 
                 diameter_bike_frame,
                 bikes_number,
                 material,
                 installation_method,
                 weight_limit,
                 color,
                 weight
                 )
            VALUES (
                    :id, 
                    :price,
                    :price_old,
                    :available,
                    :quantity,
                    :category_id,
                    :delivery,
                    :type_prefix,
                    :vendor,
                    :model,
                    :description,
                    :picture,
                    
                    :diameter_bike_frame,
                    :bikes_number,
                    :material,
                    :installation_method,
                    :weight_limit,
                    :color,
                    :weight
                )',
                $table
            )
        );
    }

    $stmt->execute([
        'id' => $offer->getId(),
        'price' => $offer->getPrice(),
        'price_old' => $offer->getOldprice(),
        'available' => (int)$offer->getAvailable(),
        'quantity' => (int)$offer->getQuantityInStock(),
        'category_id' => $offer->getCategoryId(),
        'delivery' => $offer->getDelivery(),
        'type_prefix' => $offer->getTypePrefix(),
        'vendor' => $offer->getVendor(),
        'model' => $offer->getModel(),
        'description' => $offer->getDescription(),
        'picture' => $offer->getPictures()[0] ?? '',

        'diameter_bike_frame' => $offerParams->getParamValue('Диаметр рамы велосипеда') ?? '',
        'bikes_number' => $offerParams->getParamValueInt('Количество велосипедов') ?? 0,
        'material' => $offerParams->getParamValue('Материал') ?? '',
        'installation_method' => $offerParams->getParamValue('Метод установки') ?? '',
        'weight_limit' => $offerParams->getParamValue('Ограничение веса') ?? '',
        'color' => $offerParams->getParamValue('Цвет') ?? '',
        'weight' => $offerParams->getParamValue('Вес') ?? '',
    ]);
}
