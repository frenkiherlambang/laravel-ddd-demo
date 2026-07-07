<?php

declare(strict_types=1);

namespace Src\Ordering\Domain\Model;

use Src\Shared\Domain\ValueObjects\Identifier;

/**
 * OrderId — identitas kuat untuk aggregate Order (Ordering context).
 */
final class OrderId extends Identifier {}
