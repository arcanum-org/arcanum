<?php

declare(strict_types=1);

namespace App\Domain\Contact;

/**
 * Example domain-scoped template helper.
 *
 * Only available in templates rendered for DTOs under App\Domain\Contact.
 * Registered via app/Domain/Contact/Helpers.php.
 */
final class ContactHelper
{
    public function supportEmail(): string
    {
        return 'support@example.com';
    }
}
