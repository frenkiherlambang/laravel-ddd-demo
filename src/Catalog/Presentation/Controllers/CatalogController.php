<?php

declare(strict_types=1);

namespace Src\Catalog\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Src\Catalog\Application\CourseService;
use Src\Enrollment\Application\EnrollmentService;

/**
 * CatalogController — Presentation (mahasiswa) untuk melihat katalog kursus.
 */
final class CatalogController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
        private readonly EnrollmentService $enrollments,
    ) {}

    /**
     * Menampilkan katalog kursus yang dipublikasikan.
     */
    public function index(): View
    {
        $studentId = (int) auth()->id();

        return view('catalog.index', [
            'courses' => $this->courses->catalog(),
            // Kirim daftar kursus yang sudah dimiliki agar UI bisa menandai.
            'ownedCourseIds' => $this->enrollments->accessibleCourseIds($studentId),
        ]);
    }

    /**
     * Detail satu kursus dari katalog.
     */
    public function show(string $course): View
    {
        $studentId = (int) auth()->id();

        return view('catalog.show', [
            'course' => $this->courses->find($course),
            'alreadyOwned' => $this->enrollments->canAccess($studentId, $course),
        ]);
    }
}
