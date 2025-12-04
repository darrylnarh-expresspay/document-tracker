<?php
namespace App\Interfaces;

interface FileHostInterface {
    public function upload(array $file, string $namePrefix): string;
}