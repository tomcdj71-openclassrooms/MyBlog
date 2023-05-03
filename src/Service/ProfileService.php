<?php

declare(strict_types=1);

namespace App\Service;

use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Manager\UserManager;
use App\Router\Session;
use App\Validator\EditProfileFormValidator;

class ProfileService extends AbstractService
{
    protected UserManager $userManager;
    protected CsrfTokenService $csrfTokenService;
    protected Session $session;
    protected SecurityHelper $securityHelper;
    private $imageHelper;

    public function __construct(UserManager $userManager, CsrfTokenService $csrfTokenService, Session $session, SecurityHelper $securityHelper)
    {
        $this->imageHelper = new ImageHelper('uploads/avatars/', 200, 200);
        $this->userManager = $userManager;
        $this->csrfTokenService = $csrfTokenService;
        $this->session = $session;
        $this->securityHelper = $securityHelper;
    }

    public function handleProfilePostRequest($user)
    {
        $errors = [];
        $csrfToCheck = $this->serverRequest->getPost('csrfToken');
        if (!$this->csrfTokenService->checkCsrfToken('editProfile', $csrfToCheck)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $postData = $this->getPostData();
        $editProfileFV = new EditProfileFormValidator($this->userManager, $this->session, $this->csrfTokenService, $this->securityHelper);
        $response = $editProfileFV->validate($postData);
        $message = $response['valid'] ? $this->updateUserProfile($user, $postData) : null;
        $errors = $response['valid'] ? null : $response['errors'];

        return [$errors, $message];
    }

    public function getPostData()
    {
        $fields = ['firstName', 'lastName', 'email', 'username', 'bio', 'twitter', 'facebook', 'github', 'linkedin'];
        $postData = array_map(function ($field) {
            return $this->serverRequest->getPost($field, '');
        }, array_combine($fields, $fields));
        $postData['avatar'] = $_FILES['avatar'] ?? null;
        $postData['csrfToken'] = $this->serverRequest->getPost('csrfToken');
        if (!empty($_FILES['avatar'] || null === $_FILES['avatar'])) {
            unset($postData['avatar']);
        }

        return $postData;
    }

    public function updateUserProfile($user, $data)
    {
        if (isset($data['avatar']) && null !== $data['avatar']) {
            $filename = $this->imageHelper->uploadImage($data['avatar'], 200, 200);
            if (0 === strpos($filename, 'Error')) {
                throw new \RuntimeException($filename);
            }
            $filename = explode('.', $filename)[0];
            $data['avatar'] = $filename;
            $user->setAvatar($filename);
        }
        $fields = ['FirstName', 'LastName', 'Email', 'Bio', 'Twitter', 'Facebook', 'Github', 'Linkedin'];
        foreach ($fields as $field) {
            $setter = 'set'.$field;
            $dataKey = lcfirst($field);
            if (isset($data[$dataKey])) {
                $user->{$setter}($data[$dataKey]);
            }
        }

        if ($this->userManager->updateProfile($user, $data)) {
            return 'Votre profil a été mis à jour avec succés!';
        }

        return null;
    }
}
