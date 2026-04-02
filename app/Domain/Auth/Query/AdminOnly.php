<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Attribute\RequiresRole;
use Arcanum\Rune\Attribute\Description;

#[RequiresRole('admin')]
#[Description('Admin-only endpoint')]
final class AdminOnly
{
}
