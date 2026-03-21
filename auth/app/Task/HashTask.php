<?php

namespace App\Task;

use Hyperf\Task\Annotation\Task;

class HashTask
{
    #[Task]
    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    #[Task]
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
}
