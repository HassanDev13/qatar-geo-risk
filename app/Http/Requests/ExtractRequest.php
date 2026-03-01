<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExtractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:51200'], // 10MB max
            'use_gpt' => ['required', 'boolean'],
            'use_tesseract' => ['required', 'boolean'],
            'page' => ['integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $file = $this->file('file');

            if ($file) {
                $mime = $file->getMimeType(); // الحصول على MIME type الفعلي
                if ($mime === 'application/pdf' && !$this->has('page')) {
                    $validator->errors()->add('page', 'The page field is required when uploading a PDF.');
                }
            }
        });
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
