<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Attribute\RequiresAuth;
use Arcanum\Rune\Attribute\Description;

#[RequiresAuth]                                // Returns 401 if no identity is resolved
#[Description('Show the authenticated identity')] // Shows in CLI help output
final class Whoami
{
}
