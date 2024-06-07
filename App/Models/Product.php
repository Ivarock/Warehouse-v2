<?php

namespace Warehouse\Models;

use Carbon\Carbon;
use JsonSerializable;
use Ramsey\Uuid\Uuid;

class Product implements JsonSerializable
{
    private string $id;
    private string $name;
    private int $amount;
    private float $price;
    private ?string $expirationDate;
    private ?Carbon $createdAt;
    private ?Carbon $updatedAt;

    public function __construct(
        string $name,
        int $amount,
        float $price,
        string $id,
        ?string $expirationDate,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->price = $price;
        $this->expirationDate = $expirationDate;
        $this->createdAt = $createdAt ?? Carbon::now();
        $this->updatedAt = $updatedAt ?? Carbon::now();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getExpirationDate(): ?string
    {
        return $this->expirationDate;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->toDateTimeString();
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt->toDateTimeString();
    }

    public function setId(string $id): void
    {
        $this->id = $id;
        $this->updatedAt = Carbon::now();
    }

    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
        $this->updatedAt = Carbon::now();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'price' => $this->price,
            'expirationDate' => $this->expirationDate,
            'createdAt' => $this->createdAt->toDateTimeString(),
            'updatedAt' => $this->updatedAt->toDateTimeString(),
        ];
    }

    public static function unserialize($data): self
    {
        return new self(
            $data->name,
            $data->amount,
            $data->price,
            $data->id,
            $data->expirationDate,
            new Carbon($data->createdAt),
            new Carbon($data->updatedAt)
        );
    }
}
