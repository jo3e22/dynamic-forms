<?php

namespace App\Enums;

enum OrganisationType: string
{
    case SCHOOL = 'school';
    case CLUB = 'club';
    case BUSINESS = 'business';
    case NON_PROFIT = 'non_profit';
    case GOVERNMENT = 'government';
    case SPORTS = 'sports';
    case COMMUNITY = 'community';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::SCHOOL => 'School',
            self::CLUB => 'Club',
            self::BUSINESS => 'Business',
            self::NON_PROFIT => 'Non-Profit',
            self::GOVERNMENT => 'Government',
            self::SPORTS => 'Sports',
            self::COMMUNITY => 'Community',
            self::OTHER => 'Other',
        };
    }
}