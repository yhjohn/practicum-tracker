<?php

namespace Tests\Feature;

use App\Filament\Resources\Clients\Pages\ListClients;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TablePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_tables_show_50_records_per_page_by_default(): void
    {
        $this->actingAs(User::factory()->create());
        Client::factory()->count(51)->create();

        Livewire::test(ListClients::class)
            ->assertSet('tableRecordsPerPage', 50);
    }
}
