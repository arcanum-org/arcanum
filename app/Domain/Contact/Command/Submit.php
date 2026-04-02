<?php

declare(strict_types=1);

namespace App\Domain\Contact\Command;

use Arcanum\Hyper\Attribute\HttpOnly;
use Arcanum\Rune\Attribute\Description;
use Arcanum\Validation\Rule\Email;
use Arcanum\Validation\Rule\MaxLength;
use Arcanum\Validation\Rule\NotEmpty;

#[Description('Submit a contact form')]
#[HttpOnly]
final class Submit
{
    public function __construct(
        #[NotEmpty] #[MaxLength(100)]
        public readonly string $name,
        #[NotEmpty] #[Email]
        public readonly string $email,
        #[MaxLength(5000)]
        public readonly string $message = '',
    ) {
    }
}
