<?php

declare(strict_types=1);

namespace App\Controller;

use App\Config\Configuration;
use App\Helper\SecurityHelper;
use App\Helper\TwigHelper;
use App\Manager\UserManager;
use App\Router\Request;
use App\Router\ServerRequest;
use App\Router\Session;
use App\Service\CsrfTokenService;
use App\Service\MailerService;
use App\Service\PostService;
use App\Service\ProfileService;
use App\Validator\LoginFormValidator;
use App\Validator\RegistrationFormValidator;

class UserController extends AbstractController
{
    protected UserManager $userManager;
    private MailerService $mailerService;
    private ProfileService $profileService;
    private PostService $postService;
    private LoginFormValidator $loginFV;
    private Configuration $configuration;
    private RegistrationFormValidator $registrationFV;
    private CsrfTokenService $csrfTokenService;
    private $navbar;

    public function __construct(
        TwigHelper $twig,
        Session $session,
        ServerRequest $serverRequest,
        SecurityHelper $securityHelper,
        UserManager $userManager,
        Request $request,
        CsrfTokenService $csrfTokenService,
    ) {
        parent::__construct($twig, $session, $serverRequest, $securityHelper, $userManager, $request, $csrfTokenService);
        $this->navbar = [
            'profile' => $this->securityHelper->getUser(),
        ];
    }

    // Display the profile page.
    public function profile()
    {
        $this->securityHelper->denyAccessUnlessAuthenticated();
        $user = $this->securityHelper->getUser();
        $errors = [];
        if ('POST' == $this->serverRequest->getRequestMethod() && filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS)) {
            list($errors, $message, $postData, $update) = $this->profileService->handleProfilePostRequest($user);
        }
        $userPostsData = $this->postService->getUserPostsData();
        $hasPost = ($userPostsData['total'] > 0) ? true : false;
        $csrfToken = $this->csrfTokenService->generateToken('editProfile');

        return $this->twig->render('pages/profile/profile.html.twig', array_merge([
            'errors' => $errors ?? null,
            'csrfToken' => $csrfToken,
            'hasPost' => $hasPost,
            'impersonate' => false,
            'user' => $user,
            'errors' => $errors ?? [],
            'message' => $message ?? '',
            'postData' => $postData ?? [],
            'session' => $this->session,
        ], $this->navbar));
    }

    /**
     * Display the login page.
     */
    public function login()
    {
        $this->securityHelper->denyAccessIfAuthenticated();
        $errors = [];
        if ('POST' === $this->serverRequest->getRequestMethod()) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'csrfToken' => filter_input(INPUT_POST, 'csrfToken', FILTER_SANITIZE_SPECIAL_CHARS),
            ];
            $validationResult = $this->loginFV->validate($postData);
            $errors = $validationResult['errors'];
            if ($validationResult['valid']) {
                $login = $this->securityHelper->authenticateUser($postData);
                if ($login) {
                    $url = $this->request->generateUrl('blog');
                    $this->request->redirect($url);
                }
                $errors = ['email' => 'Email ou mot de passe incorrect.'];
            }
        }
        $csrfToken = $this->csrfTokenService->generateToken('login');

        return $this->twig->render('pages/security/login.html.twig', [
            'csrfToken' => $csrfToken,
            'errors' => $errors ?? null,
            'postData' => $postData ?? null,
            'message' => $message ?? null,
        ]);
    }

    /**
     * Display the register page.
     */
    public function register()
    {
        $this->securityHelper->denyAccessIfAuthenticated();
        $errors = [];
        $csrfToken = $this->csrfTokenService->generateToken('register');
        if ('POST' === $this->serverRequest->getRequestMethod()) {
            $postData = [
                'email' => filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL),
                'username' => filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS),
                'password' => filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS),
                'passwordConfirm' => filter_input(INPUT_POST, 'passwordConfirm', FILTER_SANITIZE_SPECIAL_CHARS),
                'csrfToken' => $csrfToken,
            ];
            $validationResult = $this->registrationFV->validate($postData);
            if ($validationResult['valid']) {
                $registered = $this->securityHelper->registerUser($postData);
                if ($registered) {
                    $csrfToken = $this->csrfTokenService->generateToken('register');
                    $message = 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.';
                    $this->mailerService->sendEmail(
                        $this->configuration->get('mailer.from_email'),
                        $postData['email'],
                        'Bienvenue sur MyBlog',
                        $this->twig->render('emails/registration.html.twig')
                    );

                    $url = $this->request->generateUrl('blog');
                    $this->request->redirect($url);
                }
                $errors[] = "Échec de l'enregistrement. Veuillez réessayer.";
            }
            $errors = array_merge($errors, $validationResult['errors']);
        }

        return $this->twig->render('pages/security/register.html.twig', [
            'csrfToken' => $csrfToken,
            'errors' => $errors ?? null,
            'postData' => $postData ?? null,
            'message' => $message ?? null,
        ]);
    }

    /**
     * Display the user profile page.
     *
     * @param mixed $username
     */
    public function userProfile(string $username)
    {
        $username = $this->serverRequest->getPath();
        $user = $this->userManager->findOneBy(['username' => $username]);
        $userPostsData = $this->postService->getOtherUserPostsData($user->getId());
        $hasPost = ($userPostsData['total'] > 0) ? true : false;

        return $this->twig->render('pages/profile/profile.html.twig', array_merge([
            'userPostsData' => $userPostsData,
            'hasPost' => $hasPost,
            'impersonate' => true,
            'user' => $user,
            'session' => $this->session,
        ], $this->navbar));
    }

    /**
     * Logout the user.
     */
    public function logout()
    {
        $this->session->destroy();

        return $this->request->redirectToRoute('blog');
    }
}
