<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace App\Controller;

use Cycle\ORM\ORMInterface;
use Puff\Database\DatabaseManager;
use Puff\Database\Query;

final class Data
{
    public function index(string $id, DatabaseManager $database): array
    {
        return $database->table('users')->where('id', $id)->get();
    }

    public function page(DatabaseManager $database): Query
    {
        return $database->table('users')->orderBy('id');
    }

    public function eloquent(): mixed
    {
        // echo env('APP__NAME');
        $users = \Database\Model\Users::where('id', 1)->get();

        return $users;
    }

    public function thinkOrm(): mixed
    {
        $users = \Database\Model\Users::where('id', '>', 5)->select();

        return $users;
    }

    public function cycleOrm(ORMInterface $orm): mixed
    {
        $users = $orm->getRepository(\Database\Entity\Users::class);

        return $users->findAll();
    }
}
