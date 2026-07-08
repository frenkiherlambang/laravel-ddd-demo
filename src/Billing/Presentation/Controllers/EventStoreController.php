<?php

declare(strict_types=1);

namespace Src\Billing\Presentation\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\EventSourcing\StoredEvents\Models\EloquentStoredEvent;

/**
 * EventStoreController — Panel Admin untuk menampilkan EVENT STORE.
 *
 * Berbeda dengan read model (`invoices`), halaman ini membaca langsung dari
 * tabel `stored_events`: sumber kebenaran WRITE-side pada Event Sourcing.
 * Setiap baris adalah fakta domain yang tak terhapus — audit trail lengkap
 * dari seluruh aggregate (Invoice) di aplikasi.
 *
 * Controller ini murni QUERY: tidak menulis state apa pun.
 */
final class EventStoreController extends Controller
{
    /**
     * Daftar seluruh event tersimpan, opsional difilter per aggregate UUID.
     */
    public function index(Request $request): View
    {
        $aggregate = trim((string) $request->string('aggregate'));

        $events = EloquentStoredEvent::query()
            ->when($aggregate !== '', fn ($q) => $q->where('aggregate_uuid', $aggregate))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.events.index', [
            'events' => $events,
            'aggregate' => $aggregate,
            'typeLabel' => $this->eventTypeLabel(...),
            'typeColor' => $this->eventTypeColor(...),
        ]);
    }

    /**
     * Label ramah manusia dari nama kelas event domain.
     */
    private function eventTypeLabel(string $eventClass): string
    {
        return Str::snake(class_basename($eventClass), ' ');
    }

    /**
     * Warna badge Tailwind sesuai jenis event.
     *
     * @return string{0:string,1:string}
     */
    private function eventTypeColor(string $eventClass): array
    {
        return match (class_basename($eventClass)) {
            'InvoiceCreated' => ['bg-blue-100', 'text-blue-700'],
            'InvoicePaid' => ['bg-green-100', 'text-green-700'],
            'PaymentGatewaySessionStarted' => ['bg-amber-100', 'text-amber-700'],
            'PaymentPollAttempted' => ['bg-gray-100', 'text-gray-600'],
            default => ['bg-purple-100', 'text-purple-700'],
        };
    }
}
