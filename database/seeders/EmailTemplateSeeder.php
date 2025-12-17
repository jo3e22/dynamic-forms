<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Email\EmailTemplate;
use Illuminate\Support\Facades\Config;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $emailConfig = config('email');

        foreach ($emailConfig['defaults'] as $event => $data) {
            EmailTemplate::firstOrCreate(
                [
                    'event' => $event,
                    'organisation_id' => null,
                    'locale' => 'en',
                    'is_default' => true,
                ],
                [
                    'key' => "default.{$event}",
                    'name' => $emailConfig['events'][$event]['name'] ?? $event,
                    'subject' => $data['subject'],
                    'body' => $data['body'],
                    'type' => 'html',
                    'enabled' => true,
                    'variables' => $emailConfig['events'][$event]['variables'] ?? [],
                ]
            );
        }
    }
}