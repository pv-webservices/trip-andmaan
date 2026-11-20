<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/** Small helpers shared by the admin screens. */
class Admin
{
    public static function previewAction(callable|string $url, string $label = 'Preview'): Action
    {
        return Action::make('preview')
            ->label($label)
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->url($url, shouldOpenInNewTab: true)
            ->tooltip('Opens the page with your saved drafts. Only signed-in administrators can see it.');
    }

    /** Run an action, turning validation errors into a red notification. */
    public static function attempt(callable $callback, string $success): bool
    {
        try {
            $callback();
        } catch (ValidationException $e) {
            Notification::make()->danger()->title('Not done')->body(implode(' ', $e->validator->errors()->all()))->persistent()->send();

            return false;
        }
        Notification::make()->success()->title($success)->send();

        return true;
    }

    public static function when(?Carbon $at, ?string $by = null): string
    {
        if (! $at) {
            return '—';
        }

        return $at->timezone('Asia/Kolkata')->format('j M Y, g:i a').($by ? " by {$by}" : '');
    }
}
