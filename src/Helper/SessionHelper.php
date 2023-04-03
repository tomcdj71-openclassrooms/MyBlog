<?php

declare(strict_types=1);

namespace App\Helper;

use App\Router\Request;

class SessionHelper
{
    /**
     * Start the session.
     * If the session is already started, do nothing.
     * If the session is not started, start it.
     */
    public function startSession()
    {
        if (PHP_SESSION_NONE == session_status()) {
            session_start();
        }
    }

    /**
     * Function to check if the user is authenticated.
     * If not, redirect to login page.
     *
     * If the user is authenticated, return true.
     *
     * @return bool
     */
    public function mustBeAuthenticated()
    {
        $request = new Request();

        if (empty($_SESSION)) {
            $request->redirectToRoute('login');
        } else {
            return true;
        }
    }

    /**
     * Function to check if the user is Admin.
     * If not, redirect to login page.
     * If yes, return true.
     * If the user is not admin, return false.
     *
     * If the user is not admin, destroy the session and redirect to login page.
     *
     * @return bool
     */
    public function checkAdminSession()
    {
        $request = new Request();

        if (isset($_SESSION) && !empty($_SESSION)) {
            $role = $_SESSION['userRole'];
            if ('admin' === $role) {
                return true;
            }
            if ('user' === $role) {
                return false;
            }
            session_unset();
            session_destroy();
            session_write_close();
            $request->redirectToRoute('login');
        }

        return false;
    }
}
