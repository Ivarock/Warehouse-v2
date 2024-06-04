<?php

namespace Warehouse;

class User
{
    private array $users = [];
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '/data/users.json';
        $this->loadUser();
    }

    private function loadUser(): void
    {
        if (file_exists($this->filePath)) {
            $data = json_decode(file_get_contents($this->filePath), true);
            if ($data !== null) {
                $this->users = $data;
            }
        }
    }

    private function saveUser(): void
    {
        file_put_contents($this->filePath, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function authorize(string $username, string $password): bool
    {
        return isset($this->users[$username]) && $this->users[$username] === $password;
    }

    public function register(string $username, string $password): void
    {
        $this->users[$username] = $password;
        $this->saveUser();
    }
}
