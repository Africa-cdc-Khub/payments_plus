<?php

namespace App\Repositories;

use App\Contracts\RegistrationRepositoryInterface;
use App\Models\Registration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class RegistrationRepository implements RegistrationRepositoryInterface
{
    public function all(): Collection
    {
        return Registration::with(['user', 'package'])->latest('created_at')->get();
    }

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Registration::with(['user', 'package'])
            ->latest('created_at')
            ->paginate($perPage);
    }

    public function find(int $id): ?Registration
    {
        return Registration::find($id);
    }

    public function findWithRelations(int $id): ?Registration
    {
        return Registration::with(['user', 'package', 'participants'])->find($id);
    }

    public function getPaidRegistrations(): Collection
    {
        return Registration::with(['user', 'package'])
            ->where('payment_status', 'completed')
            ->latest('payment_completed_at')
            ->get();
    }

    public function getPendingRegistrations(): Collection
    {
        return Registration::with(['user', 'package'])
            ->where('payment_status', 'pending')
            ->latest('created_at')
            ->get();
    }

    public function filterByPaymentStatus(string $status): Collection
    {
        return Registration::with(['user', 'package'])
            ->where('payment_status', $status)
            ->latest('created_at')
            ->get();
    }

    public function searchByUser(string $search): Collection
    {
        return Registration::with(['user', 'package'])
            ->whereHas('user', function ($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest('created_at')
            ->get();
    }
}

