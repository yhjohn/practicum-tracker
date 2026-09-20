<?php

namespace Tests\Feature;

use App\Models\GroupCounsellingLog;
use App\Models\IndividualCounsellingLog;
use Tests\TestCase;

class PracticumWeekTest extends TestCase
{
    public function test_week_is_counted_in_seven_day_blocks_from_the_start_date(): void
    {
        config(['practicum.start_date' => '2026-09-13']);

        $expected = [
            '2026-09-13' => 1,
            '2026-09-19' => 1,
            '2026-09-20' => 2,
            '2026-10-04' => 4,
        ];

        foreach ($expected as $date => $week) {
            $this->assertSame($week, (new IndividualCounsellingLog(['session_date' => $date]))->week, $date);
            $this->assertSame($week, (new GroupCounsellingLog(['session_date' => $date]))->week, $date);
        }
    }

    public function test_week_is_null_without_a_date_or_before_the_start_date(): void
    {
        config(['practicum.start_date' => '2026-09-13']);

        $this->assertNull((new IndividualCounsellingLog)->week);
        $this->assertNull((new IndividualCounsellingLog(['session_date' => '2026-09-12']))->week);
    }
}
