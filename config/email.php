<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Email Events
    |--------------------------------------------------------------------------
    |
    | Available email events and their default configurations
    |
    */
    'events' => [
        'submission.received' => [
            'name' => 'Submission Received (Respondent)',
            'description' => 'Sent to respondents when they submit a form',
            'recipients' => ['respondent'],
            'variables' => [
                'form_title' => 'The title of the form',
                'organisation_name' => 'The organisation name',
                'recipient_name' => 'The respondent\'s name',
                'submission_link' => 'Direct link to the submission',
                'created_at' => 'When the submission was created',
            ],
        ],
        'submission.manager_alert' => [
            'name' => 'Submission Alert (Manager)',
            'description' => 'Sent to form managers when a new submission arrives',
            'recipients' => ['form_owner', 'form_managers'],
            'variables' => [
                'form_title' => 'The title of the form',
                'organisation_name' => 'The organisation name',
                'respondent_email' => 'Email of the respondent',
                'respondent_name' => 'Name of the respondent',
                'submission_link' => 'Link to view the submission',
                'created_at' => 'When the submission was created',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Templates
    |--------------------------------------------------------------------------
    |
    | Default templates for each event (will be created via seeder)
    |
    */
    'defaults' => [
        'submission.received' => [
            'subject' => 'Thank you for submitting {form_title}',
            'body' => <<<'HTML'
<p>Hi {recipient_name},</p>
<p>Thank you for submitting the form <strong>{form_title}</strong>.</p>
<p>We have received your response and will review it shortly.</p>
<p><a href="{submission_link}">View your submission</a></p>
<p>Best regards,<br>{organisation_name}</p>
HTML,
        ],
        'submission.manager_alert' => [
            'subject' => 'New submission for {form_title}',
            'body' => <<<'HTML'
<p>Hi {recipient_name},</p>
<p>A new submission has been received for <strong>{form_title}</strong>.</p>
<p><strong>Respondent:</strong> {respondent_name} ({respondent_email})</p>
<p><a href="{submission_link}">View submission</a></p>
HTML,
        ],
    ],
];