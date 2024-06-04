<?php

namespace Warehouse;

use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Warehouse
{
    private array $products = [];
    private Logger $logger;
    private string $user;

    public function __construct(string $user)
    {
        $this->setupLogger();
        $this->user = $user;
        $this->loadProducts();
    }

    private function setupLogger(): void
    {
        $this->logger = new Logger('warehouse');
        $handler = new StreamHandler(__DIR__ . '/warehouse.log', Logger::INFO);
        $handler->setFormatter(new LineFormatter(null,'Y-m-d H:i:s'));
        $this->logger->pushHandler($handler);
    }

    private function loadProducts(): void
    {
        $filePath = __DIR__ . "/data/{$this->user}_products.json";
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            foreach ($data as $productData) {
                $product = Product::fromJson($productData);
                $this->products[$product->getId()] = $product;
            }
        }
    }

    private function saveProducts(): void
    {
        $data = array_map(function (Product $product) {
            return $product->jsonSerialize();
        }, $this->products);
        $filePath = __DIR__ . "/data/{$this->user}_products.json";
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function createProduct(int $id, string $name, int $amount): void
    {
        $product = new Product($id, $name, $amount);
        $this->products[$id] = $product;
        $this->saveProducts();
        $this->logger->info("Product added", [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'user' => $this->user,
        ]);
    }

    public function updateProduct(int $id, int $amount, string $action): void
    {
        $product = $this->products[$id];
        if ($action === 'add') {
            $product->addAmount($amount);
        }
        if ($action === 'withdraw') {
            $product->withdrawAmount($amount);
        }
        $this->saveProducts();
        $this->logger->info("Product updated", [
            'id' => $id,
            'amount' => $amount,
            'action' => $action,
            'user' => $this->user,
        ]);
    }

    public function deleteProduct(int $id): void
    {
        unset($this->products[$id]);
        $this->saveProducts();
        $this->logger->info("Product deleted", [
            'id' => $id,
            'user' => $this->user,
        ]);
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getProduct(int $id): ?Product
    {
        return $this->products[$id] ?? null;
    }

}
