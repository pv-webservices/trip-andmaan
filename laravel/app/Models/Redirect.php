<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $guarded = ['id'];

    /** Record a permanent redirect and re-point older ones so redirects never chain. */
    public static function record(string $from, string $to, string $reason): void
    {
        if ($from === $to) {
            return;
        }
        static::query()->where('to_path', $from)->update(['to_path' => $to]);
        static::query()->where('from_path', $to)->delete();
        static::query()->updateOrCreate(
            ['from_path' => $from],
            ['to_path' => $to, 'status_code' => 301, 'reason' => $reason],
        );
    }
}
