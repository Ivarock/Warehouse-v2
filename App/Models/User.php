<?php

namespace Warehouse\Models;

class User
{
    private array $users = [];
    private string $filePath;

    public function __construct()
    {
        $this->filePath = __DIR__ . '../../storage/users.json';
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

    public function register(string $username, string $password): void
    {
        $this->users[$username] = password_hash($password, PASSWORD_DEFAULT);
        $this->saveUser();
    }

    private function saveUser(): void
    {
        file_put_contents($this->filePath, json_encode($this->users, JSON_PRETTY_PRINT));
    }

    public function authorize(string $username, string $password): bool
    {
        return isset($this->users[$username]) && password_verify($password, $this->users[$username]);
    }
}
