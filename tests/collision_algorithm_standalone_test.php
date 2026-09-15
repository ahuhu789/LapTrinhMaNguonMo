<?php
/**
 * Test Thuật toán Collision-Free Booking độc lập
 * Chạy lệnh: php tests/collision_algorithm_standalone_test.php
 */

function checkCollision($newStart, $newEnd, $existingStart, $existingEnd) {
    $nStart = strtotime($newStart);
    $nEnd   = strtotime($newEnd);
    $eStart = strtotime($existingStart);
    $eEnd   = strtotime($existingEnd);

    return ($nStart < $eEnd) && ($nEnd > $eStart);
}

$testCases = [
    [
        'name' => 'Case 1: Trùng hoàn toàn (Exact match)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'expected' => true,
    ],
    [
        'name' => 'Case 2: Bắt đầu trước nhưng kết thúc lấn vào giữa (Overlap start)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 13:00', '2026-10-10 15:00'],
        'expected' => true,
    ],
    [
        'name' => 'Case 3: Bắt đầu ở giữa và kết thúc muộn hơn (Overlap end)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 16:00', '2026-10-10 19:00'],
        'expected' => true,
    ],
    [
        'name' => 'Case 4: Nằm hoàn toàn bên trong khoảng thời gian đã bận (Sub-interval)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 15:00', '2026-10-10 16:00'],
        'expected' => true,
    ],
    [
        'name' => 'Case 5: Khung giờ tách biệt hoàn toàn trước đó (No overlap before)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 10:00', '2026-10-10 13:00'],
        'expected' => false,
    ],
    [
        'name' => 'Case 6: Khung giờ tách biệt hoàn toàn sau đó (No overlap after)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 18:00', '2026-10-10 20:00'],
        'expected' => false,
    ],
    [
        'name' => 'Case 7: Tiếp giáp thời gian chính xác (Adjacent - Không tính là trùng)',
        'existing' => ['2026-10-10 14:00', '2026-10-10 17:00'],
        'new'      => ['2026-10-10 17:00', '2026-10-10 19:00'],
        'expected' => false,
    ],
];

echo "=== KIỂM THỬ THUẬT TOÁN COLLISION DETECTION ===\n\n";
$allPassed = true;

foreach ($testCases as $i => $tc) {
    $result = checkCollision(
        $tc['new'][0], $tc['new'][1],
        $tc['existing'][0], $tc['existing'][1]
    );

    $passed = ($result === $tc['expected']);
    if (!$passed) $allPassed = false;

    echo sprintf(
        "[%s] %s: Kết quả = %s (Kỳ vọng: %s)\n",
        $passed ? 'PASS' : 'FAIL',
        $tc['name'],
        $result ? 'TRÙNG LỊCH' : 'TRỐNG LỊCH',
        $tc['expected'] ? 'TRÙNG LỊCH' : 'TRỐNG LỊCH'
    );
}

echo "\nTổng kết: " . ($allPassed ? "TẤT CẢ TEST CASES ĐÃ ĐẠT CHUẨN (100% SUCCESS)" : "CÓ TEST CASE THẤT BẠI") . "\n";
