<?php

namespace App\Contracts;

use App\Models\Registration;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface RegistrationRepositoryInterface
{
    public function all(): Collection;
    
    public function paginate(int $perPage = 15): LengthAwarePaginator;
    
    public function find(int $id): ?Registration;
    
    public function findWithRelations(int $id): ?Registration;
    
    public function getPaidRegistrations(): Collection;
    
    public function getPendingRegistrations(): Collection;
    
    public function filterByPaymentStatus(string $status): Collection;
    
    public function searchByUser(string $search): Collection;
}

