<?php
declare(strict_types=1);

namespace common\entities;

use yii\db\ActiveRecord;
use yii\validators\DateValidator;
use yii\validators\NumberValidator;
use yii\validators\RequiredValidator;
use yii\validators\StringValidator;

/**
 * @property string $user_profile_show_id
 * @property string $guid
 * @property string $user_id
 * @property int $all_user_show
 * @property int $reg_user_show
 * @property int $profile_spec_list_show
 * @property int $gender_show
 * @property int $birth_date_show
 * @property int $registration_region_show
 * @property int $education_level_show
 * @property int $languages_show
 * @property int $communication_show
 * @property int $education_show
 * @property int $work_experience_show
 * @property int $rnd_show
 * @property int $event_show
 * @property int $prof_restriction_show
 */
final class UserProfileShow extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'users.user_profile_show';
    }

    public function rules(): array
    {
        return [
            [
                [
                    'user_profile_show_id',
                    'guid',
                    'user_id',
                    'all_user_show',
                    'reg_user_show',
                    'profile_spec_list_show',
                    'gender_show',
                    'birth_date_show',
                    'registration_region_show',
                    'education_level_show',
                    'languages_show',
                    'communication_show',
                    'education_show',
                    'work_experience_show',
                    'rnd_show',
                    'event_show',
                    'prof_restriction_show'
                ], RequiredValidator::class],
            [['user_profile_show_id', 'guid', 'user_id'], StringValidator::class, 'max' => 255],
            [[
                'all_user_show',
                'reg_user_show',
                'profile_spec_list_show',
                'gender_show',
                'birth_date_show',
                'registration_region_show',
                'education_level_show',
                'languages_show',
                'communication_show',
                'education_show',
                'work_experience_show',
                'rnd_show',
                'event_show',
                'prof_restriction_show',
                'active_ind'
            ], NumberValidator::class],
        ];
    }
}