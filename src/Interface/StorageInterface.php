<?php
namespace App\Interfaces;

interface StorageInterface {
    public function getAll(): array;
    public function create(array $data): void;
    public function update(int $id, array $data): void;
    public function delete(int $id): void;
}