<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadMetricsCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'csv_file' => ['required', 'file', 'extensions:csv,txt', 'max:5120'], // Tối đa 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'csv_file.required' => 'Vui lòng chọn tệp CSV cần tải lên.',
            'csv_file.file' => 'Tệp tải lên không hợp lệ.',
            'csv_file.extensions' => 'Chỉ chấp nhận tệp tin có định dạng .csv hoặc .txt.',
            'csv_file.max' => 'Dung lượng tệp không được vượt quá 5MB.',
        ];
    }
}
