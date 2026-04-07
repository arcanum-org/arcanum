<?php

declare(strict_types=1);

namespace App\Pages;

use App\Helpers\EnvCheckHelper;
use App\Helpers\IncantationHelper;
use App\Helpers\WiredUpHelper;
use Arcanum\Shodo\Attribute\WithHelper;

#[WithHelper(EnvCheckHelper::class, alias: 'Env')]
#[WithHelper(WiredUpHelper::class, alias: 'Wired')]
#[WithHelper(IncantationHelper::class, alias: 'Tip')]
final class Index
{
    public function __construct(
        public readonly string $name = 'Arcanum',
        public readonly string $message = 'Welcome to the Arcanum framework.',
    ) {
    }
}
