<?php
declare(strict_types=1);

namespace common\repositories;

use app\dto\profile\UserProfileShowDto;
use common\entities\UserProfileShow;
use Ramsey\Uuid\Uuid;
use yii\db\Exception;

final class UserProfileShowDbRepository implements UserProfileShowDbRepositoryInterface
{

    public function getByUserId(string $userId): ?UserProfileShow
    {
        return UserProfileShow::findOne(['user_id' => $userId]);
    }

    /**
     * @throws Exception
     */
    public function updateByUserId(string $userId, UserProfileShowDto $data): void
    {
        $profileShow = $this->getByUserId($userId);
        if (!$profileShow) {
            $profileShow = new UserProfileShow();
            $profileShow->user_profile_show_id = Uuid::uuid7()->toString();
            $profileShow->guid = Uuid::uuid7()->toString();
            $profileShow->user_id = $userId;
        }

        $profileShow->all_user_show = $data->all_user_show;
        $profileShow->reg_user_show = $data->reg_user_show;
        $profileShow->profile_spec_list_show = $data->profile_spec_list_show;
        $profileShow->gender_show = $data->gender_show;
        $profileShow->birth_date_show = $data->birth_date_show;
        $profileShow->registration_region_show = $data->registration_region_show;
        $profileShow->education_level_show = $data->education_level_show;
        $profileShow->languages_show = $data->languages_show;
        $profileShow->communication_show = $data->communication_show;
        $profileShow->education_show = $data->education_show;
        $profileShow->work_experience_show = $data->work_experience_show;
        $profileShow->rnd_show = $data->rnd_show;
        $profileShow->event_show = $data->event_show;
        $profileShow->prof_restriction_show = $data->prof_restriction_show;

        if (!$profileShow->save()) {
            throw new \RuntimeException('Saving error.');
        }
    }

    /**
     * @throws Exception
     */
    public function createDefault(string $userId): void
    {
        $profileShow = new UserProfileShow();
        $profileShow->user_profile_show_id = Uuid::uuid7()->toString();
        $profileShow->guid = Uuid::uuid7()->toString();
        $profileShow->user_id = $userId;

        $profileShow->all_user_show = 0;
        $profileShow->reg_user_show = 0;
        $profileShow->profile_spec_list_show = 0;
        $profileShow->gender_show = 0;
        $profileShow->birth_date_show = 0;
        $profileShow->registration_region_show = 0;
        $profileShow->education_level_show = 0;
        $profileShow->languages_show = 0;
        $profileShow->communication_show = 0;
        $profileShow->education_show = 0;
        $profileShow->work_experience_show = 0;
        $profileShow->rnd_show = 0;
        $profileShow->event_show = 0;
        $profileShow->prof_restriction_show = 0;

        try {
            $profileShow->save();
        } catch (\Throwable $e) {
            throw new \RuntimeException('Saving error.');
        }
    }
}