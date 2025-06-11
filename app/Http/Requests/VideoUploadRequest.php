<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VideoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'video' => [
                'required',
                'file',
                'mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/avi',
                'max:102400', // 100MB in KB
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'video.required' => 'O arquivo de vídeo é obrigatório.',
            'video.file' => 'O arquivo enviado deve ser um arquivo válido.',
            'video.mimetypes' => 'O arquivo deve ser um vídeo válido (MP4, MOV, AVI).',
            'video.max' => 'O arquivo não pode ser maior que 100MB.',
        ];
    }
}
