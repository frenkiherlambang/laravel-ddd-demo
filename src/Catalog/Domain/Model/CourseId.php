<?php

declare(strict_types=1);

namespace Src\Catalog\Domain\Model;

use Src\Shared\Domain\ValueObjects\Identifier;

/**
 * CourseId — identitas kuat untuk aggregate Course (Catalog context).
 *
 * Mewarisi seluruh perilaku UUID dari Shared Kernel sehingga tidak bisa
 * tertukar dengan identifier context lain.
 */
final class CourseId extends Identifier {}
