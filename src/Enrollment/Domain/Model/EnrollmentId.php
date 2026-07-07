<?php

declare(strict_types=1);

namespace Src\Enrollment\Domain\Model;

use Src\Shared\Domain\ValueObjects\Identifier;

/**
 * EnrollmentId — identitas kuat untuk aggregate Enrollment.
 */
final class EnrollmentId extends Identifier {}
