<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Activity types of the practicum log book. Labels match the XLSX template's
 * "Activity Type" dropdown, which its weekly totals are keyed on.
 */
enum LogBookActivity: string implements HasColor, HasLabel
{
    case IndividualCounselling = 'individual_counselling';
    case GroupCounselling = 'group_counselling';
    case CareerFamilyCoupleCounselling = 'career_family_couple_counselling';
    case ProfessionalDevelopment = 'professional_development';
    case GuidanceActivities = 'guidance_activities';
    case Supervision = 'supervision';
    case ManagementAndAdministration = 'management_and_administration';
    case FaceToFaceSeminar = 'face_to_face_seminar';
    case Other = 'other';

    public function getLabel(): string
    {
        return match ($this) {
            self::IndividualCounselling => 'Individual Counselling',
            self::GroupCounselling => 'Group Counselling',
            self::CareerFamilyCoupleCounselling => 'Career / Family / Couple Counselling',
            self::ProfessionalDevelopment => 'Professional Development',
            self::GuidanceActivities => 'Guidance Activities',
            self::Supervision => 'Supervision',
            self::ManagementAndAdministration => 'Management and Administration',
            self::FaceToFaceSeminar => 'Face-to-Face Seminar',
            self::Other => 'Other',
        };
    }

    public function getColor(): string
    {
        return $this->isDirectClientContact() ? 'primary' : 'gray';
    }

    public function isDirectClientContact(): bool
    {
        return in_array($this, [
            self::IndividualCounselling,
            self::GroupCounselling,
            self::CareerFamilyCoupleCounselling,
        ], true);
    }

    /**
     * Individual and group entries are generated from their counselling logs,
     * so only the remaining activities can be typed in by hand.
     *
     * @return array<string, string>
     */
    public static function manualOptions(): array
    {
        return collect(self::cases())
            ->reject(fn (self $activity): bool => in_array($activity, [self::IndividualCounselling, self::GroupCounselling], true))
            ->mapWithKeys(fn (self $activity): array => [$activity->value => $activity->getLabel()])
            ->all();
    }
}
