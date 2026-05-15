<?php

namespace App\Providers;

use App\Models\DrillRecord;
use App\Policies\DrillRecordPolicy;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Mime\Address;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(DrillRecord::class, DrillRecordPolicy::class);

        $redirect = config('mail.redirect_all_to');
        if ($redirect) {
            $recipients = array_values(array_filter(array_map('trim', explode(',', $redirect))));
            Event::listen(function (MessageSending $event) use ($recipients) {
                $email = $event->message;
                $original = collect($email->getTo())
                    ->map(fn (Address $a) => $a->toString())
                    ->implode(', ');
                $email->subject('[UAT] ' . $email->getSubject());
                $email->to(...array_map(fn ($r) => new Address($r), $recipients));
                $email->cc([]);
                $email->bcc([]);
                if ($original) {
                    $html = $email->getHtmlBody();
                    $banner = '<div style="background:#fff7e8;border:1px solid #f0b15c;padding:8px 12px;'
                        . 'border-radius:4px;font:13px sans-serif;margin-bottom:12px;color:#6b3a00;">'
                        . '<strong>UAT redirect:</strong> originally addressed to '
                        . htmlspecialchars($original) . '</div>';
                    if ($html !== null) {
                        $email->html($banner . $html);
                    }
                    $text = $email->getTextBody();
                    if ($text !== null) {
                        $email->text("[UAT redirect — originally to: {$original}]\n\n" . $text);
                    }
                }
            });
        }
    }
}
