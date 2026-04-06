<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Attribute\RequiresRole;
use Arcanum\Rune\Attribute\Description;

#[RequiresRole('admin')]                   // Returns 403 if identity lacks the 'admin' role
#[Description('Admin-only endpoint')]      // Shows in CLI help output
final class AdminOnly
{
}
