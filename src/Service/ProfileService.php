<?php

declare(strict_types=1);

namespace App\Service;

use App\DependencyInjection\Container;
use App\Helper\ImageHelper;
use App\Helper\SecurityHelper;
use App\Manager\UserManager;
use App\Validator\EditProfileFormValidator;

class ProfileService
{
    private $securityHelper;
    private $imageHelper;
    private $userManager;

    public function __construct(Container $container)
    {
        $this->securityHelper = $container->get(SecurityHelper::class);
        $this->imageHelper = new ImageHelper('uploads/avatars/', 200, 200);
        $this->userManager = $container->get(UserManager::class);
    }

    public function handleProfilePostRequest($user)
    {
        $errors = [];
        $csrf_to_check = $_POST['csrf_token'];
        if (!$this->securityHelper->checkCsrfToken('editProfile', $csrf_to_check)) {
            $errors[] = 'Jeton CSRF invalide.';
        }
        $postData = $this->getPostData();
        $editProfileFV = new EditProfileFormValidator($this->securityHelper);
        $response = $editProfileFV->validate($postData);
        if ($response['valid']) {
            $message = $this->updateUserProfile($user, $response['data']);
        } else {
            $errors = $response['errors'];
            $message = null;
        }

        return [$errors, $message];
    }

    public function getPostData()
    {
        $fields = ['firstName', 'lastName', 'email', 'username', 'bio', 'twitter', 'facebook', 'github', 'linkedin'];
        $postData = array_map(function ($field) {
            return isset($_POST[$field]) ? htmlspecialchars($_POST[$field], ENT_QUOTES, 'UTF-8') : '';
        }, array_combine($fields, $fields));
        $postData['avatar'] = $_FILES['avatar'] ?? null;
        $postData['csrf_token'] = $_POST['csrf_token'];
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
            $user->{$setter}($data[$dataKey]);
        }

        if ($this->userManager->updateProfile($user, $data)) {
            return 'Votre profil a été mis à jour avec succés!';
        }

        return null;
    }
}
