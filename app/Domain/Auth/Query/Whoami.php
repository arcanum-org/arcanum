<?php

declare(strict_types=1);

namespace App\Domain\Auth\Query;

use Arcanum\Auth\Attribute\RequiresAuth;
use Arcanum\Rune\Attribute\Description;

#[RequiresAuth]
#[Description('Show the authenticated identity')]
final class Whoami
{
}
