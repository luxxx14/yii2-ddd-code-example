<?php
declare(strict_types=1);

namespace common\repositories;

use app\dto\profile\UserProfileShowDto;
use common\entities\UserProfileShow;

interface UserProfileShowDbRepositoryInterface
{
    public function getByUserId(string $userId): ?UserProfileShow;

    public function updateByUserId(string $userId, UserProfileShowDto $data): void;

    public function createDefault(string $userId): void;
}