<?php

namespace App\Http\Controllers;

use App\Http\Requests\VideoUploadRequest;
use App\Http\Resources\VideoResource;
use App\Services\VideoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function __construct(
        private VideoService $videoService
    ) {}

    public function store(VideoUploadRequest $request): JsonResponse
    {
        try {
            $video = $this->videoService->uploadVideo($request->file('video'));

            return response()->json([
                'success' => true,
                'message' => 'Vídeo enviado com sucesso!',
                'data' => new VideoResource($video)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no upload do vídeo.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(int $id): JsonResponse
    {
        $video = $this->videoService->getVideo($id);

        if (!$video) {
            return response()->json([
                'success' => false,
                'message' => 'Vídeo não encontrado.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new VideoResource($video)
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $videos = $this->videoService->getAllVideos($perPage);

        return response()->json([
            'success' => true,
            'data' => VideoResource::collection($videos->items()),
            'pagination' => [
                'current_page' => $videos->currentPage(),
                'last_page' => $videos->lastPage(),
                'per_page' => $videos->perPage(),
                'total' => $videos->total(),
            ]
        ]);
    }
}
