<?php

declare(strict_types=1);

use ZoomosApi\Entity\PriceListItem;
use ZoomosApi\Fetcher\PriceListFetcher;

require __DIR__ . '/vendor/autoload.php';

echo 'Start at ' . date('h:i:s Y-m-d') . PHP_EOL;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
$dotenv->required(['API_ZOOMOS_KEY', 'DB_HOST', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD']);
$key = $_ENV['API_ZOOMOS_KEY'];

/** @var PriceListItem[] $items */
$items = PriceListFetcher::create($key)
    ->useWarrantyInfo(0)
    ->useCompetitorInfo(0)
    ->useDeliveryInfo(0)
    ->getItems();

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;dbname=%s',
        $_ENV['DB_HOST'],
        $_ENV['DB_DATABASE']
    ),
    $_ENV['DB_USERNAME'],
    $_ENV['DB_PASSWORD']
);

foreach ($items as $item) {
    $stmt = $pdo->prepare("SELECT id FROM zoomos_pricelist WHERE id = :id");
    $stmt->execute(['id' => $item->id]);
    $id = $stmt->fetch(PDO::FETCH_COLUMN);

    if ($id) {
        $stmt = $pdo->prepare(
            'UPDATE zoomos_pricelist 
            SET model = :model, quantity = :quantity, price = :price, slug = :slug, status = :status, is_new = :is_new, image = :image, vendor_id = :vendor_id, vendor_name = :vendor_name, category_id = :category_id, category_name = :category_name, category_slug = :category_slug, item_date_add = :item_date_add, item_date_upd = :item_date_upd
            WHERE id = :id'
        );
    } else {
        // insert
        $stmt = $pdo->prepare(
            'INSERT INTO zoomos_pricelist 
            (id, model, quantity, price, slug, status, is_new, image, vendor_id, vendor_name, category_id, category_name, category_slug, item_date_add, item_date_upd)
            VALUES (:id, :model, :quantity, :price, :slug, :status, :is_new, :image, :vendor_id, :vendor_name, :category_id, :category_name, :category_slug, :item_date_add, :item_date_upd)'
        );
    }

    try {
        $stmt->execute([
            'id' => $item->getId(),
            'model' => str_replace('/', ' ', $item->getModel()),
            'quantity' => $item->getSupplierInfo()->getQuantityInt(),
            'price' => $item->getPrice(),
            'slug' => $item->getLinkRewrite(),
            'status' => $item->getStatus(),
            'is_new' => $item->isNew(),
            'image' => $item->getImage(),
            'vendor_id' => $item->getVendor()->getId(),
            'vendor_name' => $item->getVendor()->getName(),
            'category_id' => $item->getCategory()->getId(),
            'category_name' => $item->getCategory()->getName(),
            'category_slug' => $item->getCategory()->getLinkRewrite(),
            'item_date_add' => $item->getDateAddMillis()?->getTimestamp() ?? 0,
            'item_date_upd' => $item->getDateUpdMillis()?->getTimestamp() ?? 0,
        ]);
    } catch (PDOException $e) {
        echo "Error with ID $id:" . $e->getMessage() . PHP_EOL;
    }
}

echo 'End at ' . date('h:i:s Y-m-d') . PHP_EOL;
