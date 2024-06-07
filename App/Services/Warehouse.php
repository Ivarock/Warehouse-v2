<?php

namespace Warehouse\Services;

use Carbon\Carbon;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Ramsey\Uuid\Uuid;
use Warehouse\Models\Product;

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
        $this->logger = new Logger('Warehouse');
        $handler = new StreamHandler(__DIR__ . '../../Storage/Warehouse.log', Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, 'Y-m-d H:i:s'));
        $this->logger->pushHandler($handler);
    }

    private function loadProducts(): void
    {
        $filePath = __DIR__ . "../../Storage/{$this->user}_products.json";
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath));
            foreach ($data as $productData) {
                $product = Product::unserialize($productData);
                $this->products[$product->getId()] = $product;
            }
        }
    }

    private function saveProducts(): void
    {
        $data = array_map(function (Product $product) {
            return $product->jsonSerialize();
        }, $this->products);
        $filePath = __DIR__ . "../../Storage/{$this->user}_products.json";
        file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    public function createProduct(
        string  $name,
        int     $amount,
        float   $price,
        ?string $expirationDate
    ): void
    {
        $id = Uuid::uuid4()->toString();
        $product = new Product($name, $amount, $price, $id, $expirationDate);
        $this->products[$id] = $product;
        $this->saveProducts();
        $this->logger->info("Product added", [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,
            'price' => $price,
            'expirationDate' => $expirationDate,
            'user' => $this->user,
        ]);
    }

    public function addAmount(string $id, int $amount): void
    {
        $product = $this->getProduct($id);
        $product->setAmount($product->getAmount() + $amount);
        $this->saveProducts();
        $this->logger->info("Amount added", [
            'id' => $id,
            'amount' => $amount,
            'user' => $this->user,
        ]);
    }

    public function withdrawAmount(string $id, int $amount): void
    {
        $product = $this->getProduct($id);
        $product->setAmount($product->getAmount() - $amount);
        $this->saveProducts();
        $this->logger->info("Amount withdrawn", [
            'id' => $id,
            'amount' => $amount,
            'user' => $this->user,
        ]);

    }

    public function deleteProduct(string $id): void
    {
        if (isset($this->products[$id])) {
            unset($this->products[$id]);
            $this->saveProducts();
            $this->logger->info("Product deleted", [
                'id' => $id,
                'user' => $this->user,
            ]);
        }
    }

    public function getProducts(): array
    {
        return $this->products;
    }

    public function getProduct(string $id): Product
    {
        return $this->products[$id];
    }

    public function getReport(): array
    {
        $totalProducts = count($this->products);
        $totalValue = 0;
        foreach ($this->products as $product) {
            $totalValue += $product->getPrice() * $product->getAmount();
        }
        return [
            'totalProducts' => $totalProducts,
            'totalValue' => $totalValue,
        ];
    }

    public function updateProductId(): void
    {
        $newProducts = [];
        foreach ($this->products as $oldId => $product) {
            $newId = Uuid::uuid4()->toString();
            $product->setId($newId);
            $newProducts[$newId] = $product;
        }
        $this->products = $newProducts;
        $this->saveProducts();
    }

}
