<?php

namespace App\Repositories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

interface VideoRepositoryInterface
{
    public function create(array $data): Video;
    public function findById(int $id): ?Video;
    public function update(Video $video, array $data): Video;
    public function getAllPaginated(int $perPage = 15);
    public function getByStatus(string $status): Collection;
}
