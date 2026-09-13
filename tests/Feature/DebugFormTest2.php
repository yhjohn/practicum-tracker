<?php

namespace Tests\Feature;

use App\Filament\Resources\CounsellingSessions\Pages\CreateCounsellingSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DebugFormTest2 extends TestCase
{
    use RefreshDatabase;

    public function test_debug(): void
    {
        $this->actingAs(User::factory()->create());

        $test = Livewire::test(CreateCounsellingSession::class)
            ->fillForm(['type' => 'individual']);

        $schema = $test->instance()->form;
        $fields = $schema->getFlatFields(withHidden: true);
        foreach ($fields as $key => $field) {
            dump($key, get_class($field), $field->isVisible());
        }
    }
}
