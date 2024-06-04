<?php

namespace Warehouse;

use InvalidArgumentException;
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

    public function addProduct(int $id, string $name, int $amount): void
    {
        if (isset($this->products[$id])) {
            throw new InvalidArgumentException("Product with ID $id already exists.");
        }
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
        if (isset($this->products[$id]) === false) {
            throw new InvalidArgumentException("Product with ID $id does not exist.");
        }
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
        if (!isset($this->products[$id])) {
            throw new InvalidArgumentException("Product with ID $id does not exist.");
        }

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

    public function updateDatabase(string $status): void
    {
        $this->logger->info("Database updated", [
            'status' => $status,
            'user' => $this->user,
        ]);
    }
}
