<?php

namespace app\controllers;

use app\dto\profile\UserProfileShowDto;
use app\forms\UserProfileShowForm;
use app\mappers\UserProfileShowToViewMapper;
use common\components\web\BaseWebController;
use common\components\web\WebUser;
use common\repositories\UserProfileShowDbRepositoryInterface;
use framework\components\ApiResponse;
use yii\filters\VerbFilter;
use yii\web\Request;
use yii\web\Response;

class ProfileShowController extends BaseWebController
{
    private UserProfileShowDbRepositoryInterface $profileShowDbRepository;

    public function __construct(
        $id,
        $module,
        UserProfileShowDbRepositoryInterface $profileShowDbRepository,
        $config = []
    ) {
        parent::__construct($id, $module, $config);
        $this->profileShowDbRepository = $profileShowDbRepository;
    }

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
            'verbs' => [
                'class'   => VerbFilter::class,
                'actions' => [
                    'get' => ['get'],
                    'update' => ['post'],
                ]
            ]
        ]);
    }

    /**
     * @OA\Get(
     *     path="/profile-show/get",
     *     summary="Получить информацию о профиле пользователя",
     *     description="Возвращает данные профиля пользователя в зависимости от его настроек отображения.",
     *     operationId="getUserProfile",
     *     tags={"User"},
     *     @OA\Parameter(
     *         name="Authorization",
     *         in="header",
     *         required=true,
     *         description="Токен авторизации пользователя",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с данными профиля пользователя",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_profile_show_id", type="uuid", description="ID профиля"),
     *             @OA\Property(property="profile_spec_list_show", type="integer", description="Отображение списка специализаций"),
     *             @OA\Property(property="gender_show", type="integer", description="Отображение пола"),
     *             @OA\Property(property="birth_date_show", type="integer", description="Отображение даты рождения"),
     *             @OA\Property(property="registration_region_show", type="integer", description="Отображение региона регистрации"),
     *             @OA\Property(property="education_level_show", type="integer", description="Отображение уровня образования"),
     *             @OA\Property(property="languages_show", type="integer", description="Отображение языков"),
     *             @OA\Property(property="communication_show", type="integer", description="Отображение коммуникации"),
     *             @OA\Property(property="education_show", type="integer", description="Отображение образования"),
     *             @OA\Property(property="work_experience_show", type="integer", description="Отображение опыта работы"),
     *             @OA\Property(property="rnd_show", type="integer", description="Отображение R&D информации"),
     *             @OA\Property(property="event_show", type="integer", description="Отображение событий"),
     *             @OA\Property(property="prof_restriction_show", type="integer", description="Отображение профессиональных ограничений")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации или некорректный запрос",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code", type="integer", example=1020, description="Код ошибки")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Ошибка авторизации",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     * )
     */
    public function actionGet(WebUser $user): Response
    {
        try {
            $profileShowDto = $this->getUserProfileShow($user->getId());
        } catch (\Throwable $e) {
            throw new \RuntimeException($e->getMessage(), 0, $e);
            //return ApiResponse::getInstance()->addError(1020)->build();
        }

        return ApiResponse::addValue((array) UserProfileShowToViewMapper::map($profileShowDto))->build();
    }

    public function getUserProfileShow(string $userId): UserProfileShowDto
    {
        $userProfileShow = $this->profileShowDbRepository->getByUserId($userId);
        return UserProfileShowDto::fromEntity($userProfileShow);
    }

    /**
     * @OA\Post(
     *     path="/profile-show/update",
     *     summary="Обновить настройки отображения профиля пользователя",
     *     description="Обновляет настройки отображения профиля пользователя и возвращает обновленные данные.",
     *     operationId="updateUserProfile",
     *     tags={"User"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Данные для обновления настроек отображения профиля",
     *         @OA\JsonContent(
     *             type="object",
     *             required={
     *                 "user_profile_show_id"
     *             },
     *             @OA\Property(property="user_profile_show_id", type="string", format="uuid", description="ID профиля"),
     *             @OA\Property(property="profile_spec_list_show", type="integer", description="Отображение списка специализаций (0 или 1)"),
     *             @OA\Property(property="gender_show", type="integer", description="Отображение пола (0 или 1)"),
     *             @OA\Property(property="birth_date_show", type="integer", description="Отображение даты рождения (0 или 1)"),
     *             @OA\Property(property="registration_region_show", type="integer", description="Отображение региона регистрации (0 или 1)"),
     *             @OA\Property(property="education_level_show", type="integer", description="Отображение уровня образования (0 или 1)"),
     *             @OA\Property(property="languages_show", type="integer", description="Отображение языков (0 или 1)"),
     *             @OA\Property(property="communication_show", type="integer", description="Отображение коммуникации (0 или 1)"),
     *             @OA\Property(property="education_show", type="integer", description="Отображение образования (0 или 1)"),
     *             @OA\Property(property="work_experience_show", type="integer", description="Отображение опыта работы (0 или 1)"),
     *             @OA\Property(property="rnd_show", type="integer", description="Отображение R&D информации (0 или 1)"),
     *             @OA\Property(property="event_show", type="integer", description="Отображение событий (0 или 1)"),
     *             @OA\Property(property="prof_restriction_show", type="integer", description="Отображение профессиональных ограничений (0 или 1)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Успешный ответ с обновленными данными профиля",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="user_profile_show_id", type="string", format="uuid", description="ID профиля"),
     *             @OA\Property(property="profile_spec_list_show", type="integer", description="Отображение списка специализаций (0 или 1)"),
     *             @OA\Property(property="gender_show", type="integer", description="Отображение пола (0 или 1)"),
     *             @OA\Property(property="birth_date_show", type="integer", description="Отображение даты рождения (0 или 1)"),
     *             @OA\Property(property="registration_region_show", type="integer", description="Отображение региона регистрации (0 или 1)"),
     *             @OA\Property(property="education_level_show", type="integer", description="Отображение уровня образования (0 или 1)"),
     *             @OA\Property(property="languages_show", type="integer", description="Отображение языков (0 или 1)"),
     *             @OA\Property(property="communication_show", type="integer", description="Отображение коммуникации (0 или 1)"),
     *             @OA\Property(property="education_show", type="integer", description="Отображение образования (0 или 1)"),
     *             @OA\Property(property="work_experience_show", type="integer", description="Отображение опыта работы (0 или 1)"),
     *             @OA\Property(property="rnd_show", type="integer", description="Отображение R&D информации (0 или 1)"),
     *             @OA\Property(property="event_show", type="integer", description="Отображение событий (0 или 1)"),
     *             @OA\Property(property="prof_restriction_show", type="integer", description="Отображение профессиональных ограничений (0 или 1)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Ошибка валидации данных",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error_code", type="integer", example=1020, description="Код ошибки")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function actionUpdate(Request $request, WebUser $user): Response
    {
        try {
            $data = $request->post();
            $form = new UserProfileShowForm(['user_id' => $user->getId()]);
            $form->setAttributes($data);
            if (!$form->validate()) {
                throw new \RuntimeException('Validation failed');
            }

            $this->profileShowDbRepository->updateByUserId($user->getId(), $form->toDto());

            $profileShowDto = $this->getUserProfileShow($user->getId());
            return ApiResponse::addValue((array) UserProfileShowToViewMapper::map($profileShowDto))->build();

        } catch (\Throwable $e) {
            //return ApiResponse::getInstance()->addError(1020)->build();
            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }
}
