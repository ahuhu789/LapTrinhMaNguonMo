<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Nghiệp vụ MCN Platform
    |--------------------------------------------------------------------------
    */

    // % hoa hồng agency áp dụng cho booking mới
    'commission_rate' => (float) env('MCN_COMMISSION_RATE', 15.00),

    // Danh sách thể loại nội dung hợp lệ của Talent
    'categories' => [
        'Gaming',
        'Just Chatting',
        'Review',
        'Lifestyle',
        'Esports',
        'Entertainment',
    ],

    // Giá trị mặc định khi tạo hồ sơ streamer tự động (Admin đổi role -> streamer)
    'default_category' => 'Gaming',
    'default_rate_per_hour' => 500000,

];
