<?php

namespace Tests\Feature;

use App\Filament\Resources\CounsellingSessions\Pages\CreateCounsellingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DebugFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        $this->actingAs(User::factory()->create());

        $test = Livewire::test(CreateCounsellingSession::class)
            ->fillForm(['type' => 'individual']);

        dump($test->get('data'));
    }
}
