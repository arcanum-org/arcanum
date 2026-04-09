<?php

declare(strict_types=1);

namespace App\Domain\Guestbook\Command;

use Arcanum\Validation\Rule\MaxLength;
use Arcanum\Validation\Rule\MinLength;
use Arcanum\Validation\Rule\NotEmpty;

final class AddEntry
{
    public function __construct(
        #[NotEmpty]
        #[MinLength(2)]
        #[MaxLength(50)]
        public readonly string $name,
        #[NotEmpty]
        #[MinLength(3)]
        #[MaxLength(500)]
        public readonly string $message,
    ) {
    }
}
