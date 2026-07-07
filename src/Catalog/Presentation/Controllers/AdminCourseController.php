<?php

declare(strict_types=1);

namespace Src\Catalog\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Src\Catalog\Application\CourseService;

/**
 * AdminCourseController — Presentation layer (panel Admin) untuk Catalog.
 *
 * Controller "tipis": hanya memvalidasi input HTTP lalu mendelegasikan
 * ke Application Service. Tidak ada logika bisnis di sini.
 */
final class AdminCourseController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
    ) {}

    /**
     * Daftar seluruh kursus untuk admin.
     */
    public function index(): View
    {
        return view('admin.courses.index', [
            'courses' => $this->courses->allForAdmin(),
        ]);
    }

    /**
     * Form buat kursus baru.
     */
    public function create(): View
    {
        return view('admin.courses.create');
    }

    /**
     * Menyimpan kursus baru (Admin "Bikin Kursus").
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        $this->courses->createCourse(
            title: $data['title'],
            description: $data['description'],
            priceAmount: (int) $data['price'],
            publish: (bool) ($data['published'] ?? true),
        );

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Kursus berhasil dibuat.');
    }

    /**
     * Form edit kursus.
     */
    public function edit(string $course): View
    {
        return view('admin.courses.edit', [
            'course' => $this->courses->find($course),
        ]);
    }

    /**
     * Memperbarui kursus.
     */
    public function update(Request $request, string $course): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'price' => ['required', 'integer', 'min:0'],
            'published' => ['nullable', 'boolean'],
        ]);

        $this->courses->updateCourse(
            courseId: $course,
            title: $data['title'],
            description: $data['description'],
            priceAmount: (int) $data['price'],
            published: (bool) ($data['published'] ?? false),
        );

        return redirect()
            ->route('admin.courses.index')
            ->with('status', 'Kursus berhasil diperbarui.');
    }
}
