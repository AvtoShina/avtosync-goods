<?php

declare(strict_types=1);

use LireinCore\YMLParser\Offer\VendorModelOffer;
use ShopManApi\Entity\OfferParams;
use ShopManApi\ShopManApi;

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
    $_ENV['DB_PASSWORD']
);

foreach ($handler->getOffers() as $offer) {
    /** @var VendorModelOffer $offer */
    $stmt = $pdo->prepare("SELECT id FROM shopmanager_offers WHERE id = :id");
    $stmt->execute(['id' => $offer->getId()]);
    $id = $stmt->fetch(PDO::FETCH_COLUMN);

    $offerParams = new OfferParams($offer->getParams());

    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE shopmanager_offers 
            SET price = :price, available = :available, category_id = :category_id, delivery = :delivery, type_prefix = :type_prefix, vendor = :vendor, model = :model, description = :description, picture = :picture, purpose = :purpose, seat_diameter = :seat_diameter, season = :season, profile_height = :profile_height, width = :width, release_date = :release_date, run_flat = :run_flat, load_index = :load_index, speed_index = :speed_index, construction = :construction, type = :type, spikes = :spikes
            WHERE id = :id'
        );
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO shopmanager_offers
            (id, price, available, category_id, delivery, type_prefix, vendor, model, description, picture, purpose, seat_diameter, season, profile_height, width, release_date, run_flat, load_index, speed_index, construction, type, spikes)
            VALUES (:id, :price, :available, :category_id, :delivery, :type_prefix, :vendor, :model, :description, :picture, :purpose, :seat_diameter, :season, :profile_height, :width, :release_date, :run_flat, :load_index, :speed_index, :construction, :type, :spikes)'
        );

        // Escape special characters. TODO: refactor this
        $model = $offer->getModel();
        $model = str_replace('/', '\/', $model);
        $model = str_replace('\\', '\\\\', $model);
        $model = str_replace('\'', '\\\'', $model);
        $model = str_replace('"', '\\"', $model);
        $model = str_replace('`', '\\`', $model);
        $model = str_replace('?', '\\?', $model);
        $model = str_replace('!', '\\!', $model);
        $model = str_replace(';', '\\;', $model);

        try {
            $stmt->execute([
                'id' => $offer->getId(),
                'price' => $offer->getPrice(),
                'available' => (int)$offer->getAvailable(),
                'category_id' => $offer->getCategoryId(),
                'delivery' => $offer->getDelivery(),
                'type_prefix' => $offer->getTypePrefix(),
                'vendor' => $offer->getVendor(),
                'model' => $model,
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
        } catch (PDOException $e) {
            echo "Error with ID $id:" . $e->getMessage() . PHP_EOL;
        }
    }
}

echo 'End at ' . date('h:i:s Y-m-d') . PHP_EOL;
