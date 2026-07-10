<?php

namespace App\Modules\Auth\Repositories;

use App\Models\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
    public function update(int $id, array $data): bool;
}
