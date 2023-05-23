<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Manager\UserManager;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Validator\EditProfileFormValidator;

class ProfileService extends AbstractService
{
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;
    protected Session $session;
    protected SecurityHelper $securityHelper;
    protected ServerRequest $serverRequest;
    private $imageHelper;

    public function __construct(UserManager $userManager, CsrfTokenService $csrfTokenService, Session $session, SecurityHelper $securityHelper, ServerRequest $serverRequest)
    {
        $this->imageHelper = new ImageHelper('uploads/avatars/', 200, 200);
        $this->userManager = $userManager;
        $this->csrfTokenService = $csrfTokenService;
        $this->session = $session;
        $this->securityHelper = $securityHelper;
        $this->serverRequest = $serverRequest;
    }

    public function handleProfilePostRequest($user)
    {
        $errors = [];
        $message = [];
        $formData = $this->getFormData();
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('editProfile', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        foreach ($formData as $key => $value) {
            if ('csrfToken' === $key) {
                continue;
            }
            foreach ($formData as $key => $value) {
                if ('editProfile' === $key) {
                    continue;
                }
            }
        }
        if (empty($errors)) {
            if (is_string($formData['avatar'])) {
                unset($formData['avatar']);
            }
            $editProfileFV = new EditProfileFormValidator($this->userManager, $this->session, $this->csrfTokenService, $this->securityHelper);
            $response = $editProfileFV->validate($formData);
            $update = $response['valid'] ? $this->updateUserProfile($user, $response['data']) : null;
            $message = $response['valid'] ? 'Mise à jour du profil effectué avec succès!' : null;
            $errors = !$response['valid'] ? $response['errors'] : $errors;

            return [$errors, $message, $formData, $update];
        }
    }

    public function getFormData()
    {
        $fields = ['firstName', 'lastName', 'bio', 'twitter', 'facebook', 'github', 'linkedin'];
        $formData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));
        $formData['avatar'] = $_FILES['avatar'] ?? null;
        $formData['csrfToken'] = $this->serverRequest->getPost('csrfToken');

        return $formData;
    }

    public function updateUserProfile($user, $data)
    {
        try {
            $fields = ['firstName', 'lastName', 'bio', 'twitter', 'facebook', 'github', 'linkedin', 'avatar'];
            foreach ($fields as $field) {
                $setter = 'set'.$field;
                $dataKey = lcfirst($field);
                if (isset($data[$dataKey])) {
                    if ('avatar' == $field) {
                        $data[$dataKey] = $this->setAvatar($user, $data[$dataKey]);
                    }
                    $user->{$setter}($data[$dataKey]);
                }
            }
            $userUpdated = $this->userManager->updateProfile($user, $data);
            if ($userUpdated) {
                return [
                    'formData' => $data,
                ];
            }
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }

    private function setAvatar($user, $avatarData)
    {
        try {
            if (!empty($avatarData['name']) && UPLOAD_ERR_NO_FILE !== $avatarData['error']) {
                $filename = $this->imageHelper->uploadImage($avatarData, 200, 200);
                if (0 === strpos($filename, 'Error')) {
                    throw new \RuntimeException($filename);
                }

                return explode('.', $filename)[0];
            }

            return $user->getAvatar();
        } catch (\Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }
    }
}
