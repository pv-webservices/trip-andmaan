<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\CreateMedia;
use App\Filament\Resources\Media\Pages\EditMedia;
use App\Media\MediaUploader;
use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class MediaTest extends TestCase
{
    private function jpeg(int $width = 2000, int $height = 1000): UploadedFile
    {
        $image = imagecreatetruecolor($width, $height);
        imagefill($image, 0, 0, imagecolorallocate($image, 20, 120, 200));
        $path = tempnam(sys_get_temp_dir(), 'img');
        imagejpeg($image, $path, 80);

        return new UploadedFile($path, 'beach photo.jpg', 'image/jpeg', null, true);
    }

    private function fake(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'up');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function uploader(): MediaUploader
    {
        return app(MediaUploader::class);
    }

    public function test_images_are_reencoded_to_webp_with_responsive_variants(): void
    {
        $media = $this->uploader()->store($this->jpeg(), 'image', ['alt' => 'A beach'], null, 'beach photo.jpg');

        $this->assertSame('image', $media->type);
        $this->assertSame('image/webp', $media->mime);
        $this->assertSame([480, 800, 1200, 1600, 2000], array_map('intval', array_keys($media->variants)));
        $this->assertSame(2000, $media->width);
        $this->assertSame(1000, $media->height);
        foreach ($media->allPaths() as $path) {
            Storage::disk('public')->assertExists($path);
            $this->assertMatchesRegularExpression('#^media/\d{4}/\d{2}/[0-9a-z]{26}(-\d+)?\.webp$#', $path, 'Random safe names only');
        }
        $this->assertStringContainsString('480w', $media->srcset());
    }

    public function test_executable_and_disguised_files_are_rejected(): void
    {
        foreach ([
            ['shell.php', '<?php echo 1;'],
            ['photo.jpg', '<?php system($_GET["c"]); ?>'],
            ['image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'],
            ['page.html', '<html><script>alert(1)</script></html>'],
        ] as [$name, $contents]) {
            try {
                $this->uploader()->store($this->fake($name, $contents), null);
                $this->fail("{$name} should have been rejected");
            } catch (ValidationException $e) {
                $this->assertStringContainsString('not allowed', $e->getMessage());
            }
        }
        $this->assertSame(0, Media::query()->count());
    }

    public function test_type_size_and_content_checks(): void
    {
        $this->expectException(ValidationException::class);
        $this->uploader()->store($this->jpeg(), 'video'); // an image where a video is required
    }

    public function test_oversized_uploads_are_rejected(): void
    {
        config(['cms.media.document_max_mb' => 0]);
        $this->expectException(ValidationException::class);
        $this->uploader()->store($this->fake('itinerary.pdf', "%PDF-1.4\n".str_repeat('x', 2048)), 'document');
    }

    public function test_pdf_and_video_signatures_are_checked(): void
    {
        $pdf = $this->uploader()->store($this->fake('trip.pdf', "%PDF-1.4\n1 0 obj<<>>endobj\ntrailer<<>>\n%%EOF"), 'document', [], null, 'trip.pdf');
        $this->assertSame('application/pdf', $pdf->mime);
        $this->assertStringEndsWith('.pdf', $pdf->path);

        $mp4 = "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom".str_repeat("\x00", 64);
        $video = $this->uploader()->store($this->fake('clip.mp4', $mp4), 'video', [], null, 'clip.mp4');
        $this->assertSame('video', $video->type);
    }

    public function test_media_in_use_cannot_be_deleted_and_unused_media_can(): void
    {
        $this->importContent();
        $hero = Media::query()->where('legacy_key', 'image:hero')->firstOrFail();
        $this->assertTrue($hero->usages()->exists());
        try {
            $this->uploader()->delete($hero);
            $this->fail('Used media must not be deletable');
        } catch (ValidationException) {
            $this->assertModelExists($hero);
        }

        $unused = $this->uploader()->store($this->jpeg(600, 400), 'image');
        $paths = $unused->allPaths();
        $this->uploader()->delete($unused);
        $this->assertModelMissing($unused);
        foreach ($paths as $path) {
            Storage::disk('public')->assertMissing($path);
        }
    }

    public function test_usages_list_every_place_a_file_appears(): void
    {
        $this->importContent();
        $sunset = Media::query()->where('legacy_key', 'image:sunset')->firstOrFail();
        $labels = $sunset->usages()->pluck('label')->unique()->implode(' | ');
        $this->assertStringContainsString('Global settings › Enquiry banner (shared text)', $labels);
        $this->assertStringContainsString('Contact › Hero banner', $labels);
        $this->assertStringContainsString('Package › Havelock Holiday with Baratang', $labels);
    }

    public function test_replacing_a_file_updates_it_everywhere_and_removes_old_files(): void
    {
        $media = $this->uploader()->store($this->jpeg(900, 600), 'image');
        $old = $media->allPaths();
        $this->uploader()->replace($media, $this->jpeg(1300, 700), 'new.jpg');

        $media->refresh();
        $this->assertSame(1300, $media->width);
        foreach ($old as $path) {
            Storage::disk('public')->assertMissing($path);
        }
        Storage::disk('public')->assertExists($media->path);
    }

    public function test_upload_and_replace_through_the_admin_screens(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(CreateMedia::class)
            ->fillForm(['file' => UploadedFile::fake()->image('harbour.jpg', 1000, 700), 'title' => 'Harbour', 'alt' => 'Boats in the harbour'])
            ->call('create')
            ->assertHasNoFormErrors();
        $media = Media::query()->where('title', 'Harbour')->firstOrFail();
        $this->assertSame('Boats in the harbour', $media->alt);
        $this->assertSame(1000, $media->width);

        Livewire::test(EditMedia::class, ['record' => $media->id])
            ->callAction('replace', ['file' => UploadedFile::fake()->image('harbour2.jpg', 1400, 900)]);
        $this->assertSame(1400, $media->fresh()->width);
    }

    public function test_admin_upload_rejects_disallowed_types(): void
    {
        $this->actingAs($this->admin());
        Livewire::test(CreateMedia::class)
            ->fillForm(['file' => UploadedFile::fake()->createWithContent('evil.php', '<?php echo 1;')])
            ->call('create')
            ->assertHasFormErrors(['file']);
        $this->assertSame(0, Media::query()->count());
    }

    public function test_legacy_media_urls_redirect_to_the_library(): void
    {
        $this->importContent();
        $hero = Media::query()->where('legacy_key', 'image:hero')->firstOrFail();
        $this->get('/images/editorial/hero-1200.webp')->assertStatus(301)->assertHeader('Location', $hero->urlNear(1200));
        $this->get('/videos/sea-walk.mp4')->assertStatus(301);
        $this->get('/itineraries/tripandaman-6-nights-4n-1n-1n-andaman-tour-package.pdf')->assertStatus(301);
        $this->get('/images/editorial/unknown-1200.webp')->assertNotFound();
    }
}
