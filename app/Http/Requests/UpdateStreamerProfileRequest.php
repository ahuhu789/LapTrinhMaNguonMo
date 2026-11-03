<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStreamerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->streamerProfile !== null;
    }

    public function rules(): array
    {
        return [
            'stage_name' => ['required', 'string', 'max:100'],
            'category' => ['required', 'string', Rule::in(config('mcn.categories'))],
            'bio' => ['nullable', 'string', 'max:2000'],
            'rate_per_hour' => ['required', 'numeric', 'min:100000'],
            // Cho phép URL http(s) hoặc đường dẫn nội bộ bắt đầu bằng '/' (ảnh seed: /images/streamers/...)
            'avatar_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/[^\s]+|\/[^\s]*)$/'],
            'banner_url' => ['nullable', 'string', 'max:255', 'regex:/^(https?:\/\/[^\s]+|\/[^\s]*)$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'stage_name.required' => 'Nghệ danh không được để trống.',
            'stage_name.string' => 'Nghệ danh không hợp lệ.',
            'stage_name.max' => 'Nghệ danh tối đa 100 ký tự.',
            'category.required' => 'Vui lòng chọn thể loại.',
            'category.string' => 'Thể loại không hợp lệ.',
            'category.in' => 'Thể loại không hợp lệ.',
            'bio.string' => 'Giới thiệu không hợp lệ.',
            'bio.max' => 'Giới thiệu tối đa 2000 ký tự.',
            'rate_per_hour.required' => 'Vui lòng nhập giá booking theo giờ.',
            'rate_per_hour.numeric' => 'Giá booking phải là số.',
            'rate_per_hour.min' => 'Giá tối thiểu là 100.000đ/giờ.',
            'avatar_url.regex' => 'Link ảnh đại diện phải là URL http(s) hoặc đường dẫn bắt đầu bằng /.',
            'avatar_url.max' => 'Link ảnh đại diện tối đa 255 ký tự.',
            'banner_url.regex' => 'Link ảnh bìa phải là URL http(s) hoặc đường dẫn bắt đầu bằng /.',
            'banner_url.max' => 'Link ảnh bìa tối đa 255 ký tự.',
        ];
    }
}
