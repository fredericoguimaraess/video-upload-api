<?php

namespace App\Repositories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Collection;

class VideoRepository implements VideoRepositoryInterface
{
    public function create(array $data): Video
    {
        return Video::create($data);
    }

    public function findById(int $id): ?Video
    {
        return Video::find($id);
    }

    public function update(Video $video, array $data): Video
    {
        $video->update($data);
        return $video->fresh();
    }

    public function getAllPaginated(array $filters = [], int $perPage = 15)
    {
        return Video::orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function getByStatus(string $status): Collection
    {
        return Video::where('status', $status)->get();
    }
}
