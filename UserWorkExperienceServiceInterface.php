<?php
declare(strict_types=1);

namespace app\services;

interface UserWorkExperienceServiceInterface
{

    public function createFromList(string $userId, array $dtoList, bool $profExperience): void;
    public function getProfIdsByWorkExpId(string $userId): array;
    public function deleteAll(string $userId): void;
    public function getAllByUserId(string $userId): array;
    public function addMany(array $dtoList): array;
    public function updateMany(string $userId, array $dtoList): array;
    public function deleteOneAndReturnList(string $userId, string $userWorkExpId): array;
}