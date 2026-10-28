<?php

namespace Tests\Feature;

use App\Filament\Resources\Contacts\Pages\ListContacts;
use App\Filament\Resources\Contacts\Pages\ViewContact;
use App\Filament\Resources\PdfDocuments\Pages\ListPdfDocuments;
use App\Filament\Resources\PdfDocuments\Pages\ViewPdfDocument;
use App\Filament\Resources\WebsiteLinks\Pages\ListWebsiteLinks;
use App\Filament\Resources\WebsiteLinks\Pages\ViewWebsiteLink;
use App\Models\Contact;
use App\Models\PdfDocument;
use App\Models\User;
use App\Models\WebsiteLink;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ResourcesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_creating_a_contact_from_the_modal_saves_it(): void
    {
        Livewire::test(ListContacts::class)
            ->callAction(CreateAction::class, ['name' => 'Samaritans of Singapore', 'contact' => '1767'])
            ->assertHasNoActionErrors();

        $this->assertDatabaseHas(Contact::class, ['name' => 'Samaritans of Singapore', 'contact' => '1767']);
    }

    public function test_contact_requires_name_and_contact(): void
    {
        Livewire::test(ListContacts::class)
            ->callAction(CreateAction::class, ['name' => null, 'contact' => null])
            ->assertHasActionErrors(['name' => 'required', 'contact' => 'required']);
    }

    public function test_contact_detail_page_can_be_edited_from_a_modal(): void
    {
        $contact = Contact::factory()->create();

        Livewire::test(ViewContact::class, ['record' => $contact->getRouteKey()])
            ->assertOk()
            ->assertSee($contact->name)
            ->callAction(EditAction::class, ['contact' => '6123 4567'])
            ->assertHasNoActionErrors();

        $this->assertSame('6123 4567', $contact->refresh()->contact);
    }

    public function test_uploading_multiple_pdfs_keeps_their_original_names(): void
    {
        Storage::fake(config('filament.default_filesystem_disk'));

        Livewire::test(ListPdfDocuments::class)
            ->callAction(CreateAction::class, [
                'title' => 'Grounding worksheets',
                'files' => [
                    UploadedFile::fake()->create('breathing.pdf', 10, 'application/pdf'),
                    UploadedFile::fake()->create('5-4-3-2-1.pdf', 10, 'application/pdf'),
                ],
            ])
            ->assertHasNoActionErrors();

        $files = PdfDocument::sole()->files;

        $this->assertEqualsCanonicalizing(['breathing.pdf', '5-4-3-2-1.pdf'], array_map('basename', $files));
        foreach ($files as $path) {
            Storage::disk(config('filament.default_filesystem_disk'))->assertExists($path);
        }
    }

    public function test_pdf_upload_rejects_other_file_types(): void
    {
        Storage::fake(config('filament.default_filesystem_disk'));

        Livewire::test(ListPdfDocuments::class)
            ->callAction(CreateAction::class, [
                'title' => 'Not a PDF',
                'files' => [UploadedFile::fake()->image('photo.png')],
            ])
            ->assertHasActionErrors();

        $this->assertFalse(PdfDocument::exists());
    }

    public function test_pdf_detail_page_links_files_to_open_in_a_new_tab(): void
    {
        $document = PdfDocument::factory()->create(['files' => ['pdf-documents/abc/worksheet.pdf']]);

        Livewire::test(ViewPdfDocument::class, ['record' => $document->getRouteKey()])
            ->assertOk()
            ->assertSeeHtml('target="_blank"')
            ->assertSee('worksheet.pdf');
    }

    public function test_website_link_requires_a_valid_url(): void
    {
        Livewire::test(ListWebsiteLinks::class)
            ->callAction(CreateAction::class, ['title' => 'IMH', 'url' => 'not a url'])
            ->assertHasActionErrors(['url' => 'url']);

        $this->assertFalse(WebsiteLink::exists());
    }

    public function test_website_link_detail_page_shows_the_link(): void
    {
        $link = WebsiteLink::factory()->create(['url' => 'https://www.imh.com.sg']);

        Livewire::test(ViewWebsiteLink::class, ['record' => $link->getRouteKey()])
            ->assertOk()
            ->assertSeeHtml('href="https://www.imh.com.sg"');
    }
}
