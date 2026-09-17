<?php

/*
 * PHP Unison Fiber Framework
 * https://github.com/php-puff/puff
 * https://github.com/php-puff/puff/issues
 * Copyright (c) Puff
 */

declare(strict_types=1);

namespace Database\Entity;

use Cycle\Annotated\Annotation\Column;
use Cycle\Annotated\Annotation\Entity;

#[Entity(table: 'users')]
class Users
{
    #[Column(type: 'bigPrimary', nullable: true)]
    public ?int $id = null;

    #[Column(type: 'bigInteger', nullable: true)]
    public ?int $pid = null;

    #[Column(type: 'bigInteger', name: 'agent_id', nullable: true)]
    public ?int $agentId = null;

    #[Column(type: 'bigInteger', name: 'group_id', nullable: true)]
    public ?int $groupId = null;

    #[Column(type: 'bigInteger', name: 'level_id', nullable: true)]
    public ?int $levelId = null;

    #[Column(type: 'string(32)', nullable: true)]
    public ?string $account = null;

    #[Column(type: 'string(32)', nullable: true)]
    public ?string $name = null;

    #[Column(type: 'string(2)', nullable: true)]
    public ?string $area = null;

    #[Column(type: 'string(96)', nullable: true)]
    public ?string $email = null;

    #[Column(type: 'string(24)', nullable: true)]
    public ?string $phone = null;

    #[Column(type: 'integer', nullable: true)]
    public ?int $state = null;

    #[Column(type: 'datetime', name: 'created_at', nullable: true)]
    public ?\DateTimeImmutable $createdAt = null;

    #[Column(type: 'datetime', name: 'updated_at', nullable: true)]
    public ?\DateTimeImmutable $updatedAt = null;

    #[Column(type: 'datetime', name: 'deleted_at', nullable: true)]
    public ?\DateTimeImmutable $deletedAt = null;
}
