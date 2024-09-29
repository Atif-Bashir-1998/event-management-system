<?php

namespace App\Constants\Event;

class Constants
{
    public const EVENT_STATUSES = [
        'DRAFT' => 'draft',
        'PUBLISHED' => 'published',
        'CANCELLED' => 'cancelled',
    ];


    public const EVENT_TYPES = [
        'WORKSHOP' => 'workshop',
        'CONFERENCE' => 'conference',
        'WEBINAR' => 'webinar',
        'SEMINAR' => 'seminar',
        'MEETUP' => 'meetup',
        'NETWORKING_EVENT' => 'networking event',
        'LECTURE' => 'lecture',
        'TRAINING' => 'training',
        'PANEL_DISCUSSION' => 'panel discussion',
        'ROUND_TABLE' => 'round table',
        'SOCIAL_EVENT' => 'social event',
        'FUNDRAISER' => 'fundraiser',
    ];
}
