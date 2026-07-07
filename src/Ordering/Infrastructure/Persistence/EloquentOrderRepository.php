<?php

declare(strict_types=1);

namespace Src\Ordering\Infrastructure\Persistence;

use Src\Ordering\Domain\Model\Order;
use Src\Ordering\Domain\Model\OrderId;
use Src\Ordering\Domain\Model\OrderStatus;
use Src\Ordering\Domain\Repository\OrderRepository;
use Src\Ordering\Infrastructure\Eloquent\OrderEloquentModel;
use Src\Shared\Domain\ValueObjects\Money;

/**
 * EloquentOrderRepository — implementasi Repository Pattern untuk Order.
 *
 * Bertanggung jawab memetakan aggregate Order <-> OrderEloquentModel.
 */
final class EloquentOrderRepository implements OrderRepository
{
    public function nextIdentity(): OrderId
    {
        return OrderId::generate();
    }

    public function save(Order $order): void
    {
        OrderEloquentModel::query()->updateOrCreate(
            ['id' => $order->id()->value],
            [
                'student_id' => $order->studentId(),
                'course_id' => $order->courseId(),
                'course_title' => $order->courseTitle(),
                'amount' => $order->amount()->amount,
                'currency' => $order->amount()->currency,
                'status' => $order->status()->value,
            ],
        );
    }

    public function findById(OrderId $id): ?Order
    {
        $model = OrderEloquentModel::query()->find($id->value);

        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return Order[]
     */
    public function forStudent(string $studentId): array
    {
        return OrderEloquentModel::query()
            ->where('student_id', $studentId)
            ->latest('created_at')
            ->get()
            ->map(fn (OrderEloquentModel $m) => $this->toDomain($m))
            ->all();
    }

    /**
     * Mapping OUT: model persistensi -> aggregate domain.
     */
    private function toDomain(OrderEloquentModel $model): Order
    {
        return Order::reconstitute(
            OrderId::fromString($model->id),
            (string) $model->student_id,
            $model->course_id,
            $model->course_title,
            Money::of($model->amount, $model->currency),
            OrderStatus::from($model->status),
        );
    }
}
