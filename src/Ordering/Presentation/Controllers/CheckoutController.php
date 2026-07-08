<?php

declare(strict_types=1);

namespace Src\Ordering\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Src\Billing\Application\BillingService;
use Src\Catalog\Application\CourseService;
use Src\Ordering\Application\OrderService;

/**
 * CheckoutController — Presentation (mahasiswa) untuk alur Order -> Checkout.
 *
 * Mengorkestrasi lintas-context pada level use case aplikasi:
 *   Pilih Kursus (Order) -> Checkout -> Buat Invoice Pending -> Arahkan ke
 *   Payment Gateway (DOKU) Checkout.
 *
 * Perhatikan: controller hanya memanggil Application Service tiap context;
 * ia tidak memuat aturan bisnis, hanya alur.
 */
final class CheckoutController extends Controller
{
    public function __construct(
        private readonly CourseService $courses,
        private readonly OrderService $orders,
        private readonly BillingService $billing,
    ) {}

    /**
     * Langkah 1: mahasiswa memilih kursus -> buat Order pending, tampilkan
     * halaman ringkasan checkout.
     */
    public function start(string $course): RedirectResponse
    {
        $student = auth()->user();
        $courseModel = $this->courses->find($course);

        // Guard: kursus harus ada dan terpublikasi.
        if ($courseModel === null || ! $courseModel->isPublished()) {
            abort(404);
        }

        // Use case Ordering: place order (snapshot judul & harga).
        $orderId = $this->orders->placeOrder(
            studentId: (int) $student->id,
            courseId: (string) $courseModel->id(),
            courseTitle: $courseModel->title(),
            price: $courseModel->price(),
        );

        return redirect()->route('checkout.show', ['order' => (string) $orderId]);
    }

    /**
     * Menampilkan ringkasan order sebelum membayar.
     */
    public function show(string $order): View
    {
        $orderModel = $this->orders->find($order);

        // Guard kepemilikan: hanya pemilik order yang boleh melihat.
        abort_if($orderModel === null || $orderModel->studentId() !== (string) auth()->id(), 403);

        return view('checkout.show', ['order' => $orderModel, 'orderId' => $order]);
    }

    /**
     * Langkah 2: "Bayar" -> checkout order + buat Invoice Pending +
     * mulai sesi Payment Gateway, lalu arahkan ke halaman pembayaran.
     */
    public function pay(string $order): RedirectResponse
    {
        $student = auth()->user();
        $orderModel = $this->orders->find($order);

        abort_if($orderModel === null || $orderModel->studentId() !== (string) $student->id, 403);

        // Ordering: transisi Pending -> CheckedOut.
        $this->orders->checkout($order);

        // Billing: buat Invoice Pending + mulai sesi DOKU (via ACL).
        $invoiceId = $this->billing->createPendingInvoiceForOrder(
            orderId: $order,
            studentId: (int) $student->id,
            courseId: $orderModel->courseId(),
            courseTitle: $orderModel->courseTitle(),
            amount: $orderModel->amount()->amount,
            studentName: $student->name,
            studentEmail: $student->email,
        );

        // Arahkan ke halaman invoice/pembayaran (yang menautkan ke checkout DOKU).
        return redirect()->route('payment.show', ['invoice' => $invoiceId]);
    }

    /**
     * Halaman "Pesanan Saya".
     */
    public function myOrders(): View
    {
        $orders = $this->orders->forStudent((int) auth()->id());

        // Petakan tiap order ke invoice-nya (read-side Billing) agar tersedia
        // tautan "Lihat Invoice" untuk order yang sudah checkout/lunas.
        $orderIds = array_map(
            static fn ($order): string => (string) $order->id(),
            $orders,
        );
        $invoiceIds = $this->billing->invoiceIdsByOrderIds($orderIds);

        return view('orders.index', [
            'orders' => $orders,
            'invoiceIds' => $invoiceIds,
        ]);
    }
}
