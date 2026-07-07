<?php

declare(strict_types=1);

namespace Src\Enrollment\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\View\View;
use Src\Catalog\Application\CourseService;
use Src\Enrollment\Application\EnrollmentService;

/**
 * MyCoursesController — Presentation (mahasiswa) untuk kursus yang dimiliki.
 */
final class MyCoursesController extends Controller
{
    public function __construct(
        private readonly EnrollmentService $enrollments,
        private readonly CourseService $courses,
    ) {}

    /**
     * Daftar kursus yang bisa diakses mahasiswa (hasil pelunasan).
     */
    public function index(): View
    {
        $studentId = (int) auth()->id();

        // Ambil id kursus yang dimiliki, lalu resolve detailnya dari Catalog.
        $courseIds = $this->enrollments->accessibleCourseIds($studentId);

        $courses = array_values(array_filter(array_map(
            fn (string $id) => $this->courses->find($id),
            $courseIds,
        )));

        return view('enrollment.my-courses', ['courses' => $courses]);
    }

    /**
     * Halaman "belajar" — akses konten kursus, dijaga oleh kepemilikan.
     */
    public function learn(string $course): View
    {
        $studentId = (int) auth()->id();

        // Guard akses: hanya mahasiswa yang sudah lunas boleh masuk.
        abort_unless($this->enrollments->canAccess($studentId, $course), 403,
            'Anda belum memiliki akses ke kursus ini.');

        return view('enrollment.learn', [
            'course' => $this->courses->find($course),
        ]);
    }
}
