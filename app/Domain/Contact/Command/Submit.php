<?php

declare(strict_types=1);

namespace App\Domain\Contact\Command;

use Arcanum\Hyper\Attribute\HttpOnly;
use Arcanum\Rune\Attribute\Description;
use Arcanum\Validation\Rule\Email;
use Arcanum\Validation\Rule\MaxLength;
use Arcanum\Validation\Rule\NotEmpty;

#[Description('Submit a contact form')]  // Shows in CLI help output
#[HttpOnly]                               // Rejects CLI invocations with a clear error
final class Submit
{
    public function __construct(
        #[NotEmpty] #[MaxLength(100)]      // Validated automatically by ValidationGuard
        public readonly string $name,
        #[NotEmpty] #[Email]               // Validates format via filter_var
        public readonly string $email,
        #[MaxLength(5000)]
        public readonly string $message = '',
    ) {
    }
}
