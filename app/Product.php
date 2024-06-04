<?php

namespace Warehouse;

use Carbon\Carbon;
use JsonSerializable;

class Product implements JsonSerializable
{
    private int $id;
    private string $name;
    private int $amount;
    private Carbon $createdAt;
    private Carbon $updatedAt;

    public function __construct(
        int $id,
        string $name,
        int $amount,
        ?Carbon $createdAt = null,
        ?Carbon $updatedAt = null
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->amount = $amount;
        $this->createdAt = $createdAt ?? Carbon::now();
        $this->updatedAt = $updatedAt ?? Carbon::now();
    }

    public function getId(): int
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

    public function addAmount(int $amount): void
    {
        $this->amount += $amount;
        $this->updatedAt = Carbon::now();
    }

    public function withdrawAmount(int $amount): void
    {
        $this->amount -= $amount;
        $this->updatedAt = Carbon::now();
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt->toDateTimeString();
    }

    public function getUpdatedAt(): string
    {
        return $this->updatedAt->toDateTimeString();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'createdAt' => $this->createdAt->toDateTimeString(),
            'updatedAt' => $this->updatedAt->toDateTimeString(),
        ];
    }

    public static function fromJson(array $data): self
    {
        return new self(
            $data['id'],
            $data['name'],
            $data['amount'],
            new Carbon($data['createdAt']),
            new Carbon($data['updatedAt'])
        );
    }
}

