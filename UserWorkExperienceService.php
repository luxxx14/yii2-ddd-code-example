<?php

declare(strict_types=1);

namespace app\services;

use app\dto\CreateUserWorkExperienceProfileDto;
use app\dto\profile\UserWorkExperienceDto;
use app\dto\CreateUserWorkExperienceDto;
use common\dto\UserInfoDto;
use common\facades\Uuid;
use common\providers\DbConnectionProvider;
use common\repositories\UserInfoDbRepositoryInterface;
use common\repositories\UserWorkExperienceDbRepositoryInterface;
use common\repositories\UserWorkExperienceProfileDbRepositoryInterface;
use RuntimeException;
use yii\db\Exception;

final readonly class UserWorkExperienceService implements UserWorkExperienceServiceInterface
{
    public function __construct(
        private UserInfoDbRepositoryInterface $userInfoDbRepository,
        private UserWorkExperienceDbRepositoryInterface $userWorkExperienceDbRepository,
        private UserWorkExperienceProfileDbRepositoryInterface  $userWorkExperienceProfileDbRepository,
        private DbConnectionProvider $connection
    ) {}

    /**
     * @throws Exception
     */
    public function createFromList(string $userId, array $dtoList, bool $profExperience): void
    {
        $transaction = $this->connection->getDb()->beginTransaction();

        try {
            $user = $this->userInfoDbRepository->getByUserId($userId);
            $user->have_prof_experience = $profExperience;
            $userInfoDto = UserInfoDto::fromEntity($user);
            $this->userInfoDbRepository->update($userInfoDto);

            $workItems = $this->userWorkExperienceDbRepository->getByUserId($userId);
            if ($workItems) {
                $workItemsIds = array_column($workItems, 'user_work_exp_id');
                $this->userWorkExperienceProfileDbRepository->deleteAllByWorkId($workItemsIds);
                $this->userWorkExperienceDbRepository->deleteAllByUserId($userId);
            }

            foreach ($dtoList as $dto) {
                $this->userWorkExperienceDbRepository->add($dto);

                foreach ($dto->profIds as $item) {
                    $userWorkExpProfileDto = new CreateUserWorkExperienceProfileDto(
                        userWorkExpProfId: Uuid::generate()->toString(),
                        guid: Uuid::generate()->toString(),
                        userWorkExpId: $dto->userWorkExpId,
                        rExperienceProfId: $item
                    );
                    $this->userWorkExperienceProfileDbRepository->add($userWorkExpProfileDto);
                }
            }
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new RuntimeException($e->getMessage(), 0, $e);
        }

        $transaction->commit();
    }

    /**
     * @throws Exception
     */
    public function getProfIdsByWorkExpId(string $userId): array
    {
        try {
            $user = $this->userInfoDbRepository->getByUserId($userId);
            $result['having_work_experience'] = $user->have_prof_experience;
            $result['places_of_work'] = [];
            $works = $this->userWorkExperienceDbRepository->getByUserId($userId);
            foreach ($works as $work) {
                $profIds = array_column(
                    $this->userWorkExperienceProfileDbRepository->getByWorkExpId($work->user_work_exp_id),
                    'r_experience_prof_id'
                );

                $result['places_of_work'][] = UserWorkExperienceDto::fromEntities($work, $profIds);
            }

            return $result;
        } catch (\Throwable $e) {
            throw new RuntimeException('Ошибка при получении профилей: ' . $e->getMessage(), 0, $e);
        }
    }

    public function deleteAll(string $userId): void
    {
        $this->userWorkExperienceDbRepository->deleteAllByUserId($userId);
    }

    public function getAllByUserId(string $userId): array
    {
        try {
            $works = $this->userWorkExperienceDbRepository->getByUserId($userId);
            $result = [];

            foreach ($works as $work) {
                $profIds = array_column(
                    $this->userWorkExperienceProfileDbRepository->getByWorkExpId($work->user_work_exp_id),
                    'r_experience_prof_id'
                );

                $workArray = method_exists($work, 'toArray') ? $work->toArray() : (array)$work;

                $workArray['profIds'] = $profIds;

                $result[] = $workArray;
            }

            return $result;
        } catch (\Throwable $e) {
            throw new RuntimeException('Ошибка при получении опыта работы: ' . $e->getMessage(), 0, $e);
        }
    }

    public function addMany(array $dtoList): array
    {
        $transaction = $this->connection->getDb()->beginTransaction();

        try {

            $userId = $dtoList[0]['user_id'];

            foreach ($dtoList as $dtoData) {
                $userWorkExpId = Uuid::generate()->toString();
                $guid = Uuid::generate()->toString();

                $dto = new CreateUserWorkExperienceDto(
                    userWorkExpId: $userWorkExpId,
                    guid: $guid,
                    userId: $dtoData['user_id'],
                    workOrgName: $dtoData['work_org_name'],
                    description: $dtoData['description'] ?? null,
                    workPosition: is_array($dtoData['work_position']) ? null : $dtoData['work_position'],
                    profIds: $dtoData['prof_ids'] ?? [],
                    workBeginDate: $dtoData['work_begin_date'] ?? null,
                    workEndDate: $dtoData['work_end_date'] ?? null,
                    isWorkContinues: $dtoData['is_work_continues'],
                    showRowInfo: $dtoData['show_row_info'] ?? true
                );

                $this->userWorkExperienceDbRepository->add($dto);
            }

            $existingRecords = $this->userWorkExperienceDbRepository->getByUserId($userId);

            $transaction->commit();

            return $existingRecords;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new RuntimeException('Ошибка при массовом добавлении опыта работы: ' . $e->getMessage(), 0, $e);
        }
    }

    public function updateMany(string $userId, array $dtoList): array
    {
        $transaction = \Yii::$app->db->beginTransaction();

        try {
            foreach ($dtoList as $dto) {
                if (!$dto instanceof CreateUserWorkExperienceDto) {
                    throw new \InvalidArgumentException('Элемент должен быть экземпляром CreateUserWorkExperienceDto');
                }

                if (empty($dto->userWorkExpId)) {
                    $newDto = new CreateUserWorkExperienceDto(
                        userWorkExpId: Uuid::generate()->toString(),
                        guid: Uuid::generate()->toString(),
                        userId: $dto->userId,
                        workOrgName: $dto->workOrgName,
                        description: $dto->description,
                        workPosition: $dto->workPosition,
                        profIds: $dto->profIds,
                        workBeginDate: $dto->workBeginDate,
                        workEndDate: $dto->workEndDate,
                        isWorkContinues: $dto->isWorkContinues,
                        showRowInfo: $dto->showRowInfo,
                    );

                    $this->userWorkExperienceDbRepository->add($newDto);
                } else {
                    $existingWorkExperience = $this->userWorkExperienceDbRepository->getById($dto->userWorkExpId);

                    if (!$existingWorkExperience) {
                        throw new \RuntimeException("UserWorkExperience не найден: {$dto->userWorkExpId}");
                    }

                    $this->userWorkExperienceDbRepository->updateWorkExperience(
                        $dto->userWorkExpId,
                        $dto->workOrgName,
                        $dto->workPosition,
                        $dto->description,
                        $dto->workBeginDate,
                        $dto->workEndDate,
                        $dto->isWorkContinues,
                        $dto->showRowInfo
                    );
                }
            }

            $updatedWorkExperiences = $this->userWorkExperienceDbRepository->getByUserId($userId);

            $transaction->commit();

            return $updatedWorkExperiences;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw new \RuntimeException('Ошибка при массовом обновлении: ' . $e->getMessage(), 0, $e);
        }
    }

    public function deleteOneAndReturnList(string $userId, string $userWorkExpId): array
    {
        $transaction = $this->connection->getDb()->beginTransaction();

        try {
            $this->userWorkExperienceProfileDbRepository->deleteAllByWorkId([$userWorkExpId]);

            $this->userWorkExperienceDbRepository->deleteOneById($userId, $userWorkExpId);

            $transaction->commit();

            return $this->userWorkExperienceDbRepository->getByUserId($userId);
        } catch (\Throwable $e) {
            print_r($e);
            $transaction->rollBack();
            throw new RuntimeException('Ошибка при удалении опыта работы: ' . $e->getMessage(), 0, $e);
        }
    }
}
