<?php

namespace App\Media;

use App\Models\Destination;
use App\Models\Media;
use App\Models\MediaUsage;
use App\Models\Package;
use App\Models\Popup;
use App\Models\Section;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Rebuilds the media_usages rows of one owner from its current content.
 * Both draft and published content count, so a file that is live (or about
 * to go live) can never be deleted.
 */
class MediaUsageRecorder
{
    /** @return array<string, int> "json.path" => media id, for keys ending in "_id" */
    public static function extract(?array $data, string $prefix = ''): array
    {
        $found = [];
        foreach ($data ?? [] as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";
            if (is_array($value)) {
                $found += self::extract($value, $path);
            } elseif (str_ends_with((string) $key, '_id') && is_numeric($value) && (int) $value > 0) {
                $found[$path] = (int) $value;
            }
        }

        return $found;
    }

    public static function forSection(Section $section): void
    {
        $section->loadMissing('page');
        $label = ($section->page?->title ?? 'Page').' › '.$section->label;
        $refs = [];
        foreach (self::extract($section->draft_data) as $path => $id) {
            $refs["draft.{$path}"] = [$id, "{$label} (draft)"];
        }
        foreach (self::extract($section->published_data) as $path => $id) {
            $refs["published.{$path}"] = [$id, $label];
        }
        self::store($section, $refs);
    }

    public static function forModel(Model $model): void
    {
        $refs = match (true) {
            $model instanceof Package => self::packageRefs($model),
            $model instanceof Destination => self::destinationRefs($model),
            $model instanceof Popup => self::popupRefs($model),
            default => [],
        };
        self::store($model, $refs);
    }

    public static function clear(Model $model): void
    {
        MediaUsage::query()->where('owner_type', $model->getMorphClass())->where('owner_id', $model->getKey())->delete();
    }

    private static function packageRefs(Package $package): array
    {
        $label = 'Package › '.$package->title;
        $refs = [];
        foreach (self::extract($package->liveData()) as $path => $id) {
            $refs["live.{$path}"] = [$id, $label];
        }
        foreach (self::extract($package->published_snapshot) as $path => $id) {
            $refs["published.{$path}"] = [$id, $label.' (published)'];
        }

        return $refs;
    }

    private static function destinationRefs(Destination $destination): array
    {
        $label = 'Destination › '.$destination->name;
        $refs = [];
        foreach (self::extract($destination->liveData()) as $path => $id) {
            $refs["live.{$path}"] = [$id, $label];
        }
        foreach (self::extract($destination->published_snapshot) as $path => $id) {
            $refs["published.{$path}"] = [$id, $label.' (published)'];
        }

        return $refs;
    }

    private static function popupRefs(Popup $popup): array
    {
        return $popup->image_id ? ['image_id' => [$popup->image_id, 'Popup › '.$popup->name]] : [];
    }

    /** @param array<string, array{0:int,1:string}> $refs */
    private static function store(Model $owner, array $refs): void
    {
        DB::transaction(function () use ($owner, $refs) {
            self::clear($owner);
            $now = now();
            $rows = [];
            foreach ($refs as $field => [$id, $label]) {
                $rows[] = [
                    'media_id' => $id,
                    'owner_type' => $owner->getMorphClass(),
                    'owner_id' => $owner->getKey(),
                    'field' => mb_substr($field, 0, 250),
                    'label' => mb_substr($label, 0, 250),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $existing = Media::query()->whereIn('id', array_column($rows, 'media_id'))->pluck('id')->all();
            $rows = array_values(array_filter($rows, fn ($r) => in_array($r['media_id'], $existing, true)));
            foreach (array_chunk($rows, 200) as $chunk) {
                MediaUsage::query()->insert($chunk);
            }
        });
    }
}
