<?php

declare(strict_types=1);

use App\Controllers\BlogController;
use App\Controllers\AuthController;
use App\Controllers\PageController;
use App\Controllers\VerifyController;
use App\Controllers\ContactController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;
use App\Controllers\RoleController;
use App\Controllers\AdminPostsController;
use App\Controllers\AdminPagesController;
use App\Controllers\CommentController;
use App\Controllers\SettingsController;
/*
|--------------------------------------------------------------------------
| Pages
|--------------------------------------------------------------------------
*/

$router->get(
    '/',
    [PageController::class, 'home']
);

$router->get(
    '/blog',
    [BlogController::class, 'index']
);

$router->get(
    '/blog/post',
    [BlogController::class, 'show']
);

$router->post(
    '/blog/comment/store',
    [CommentController::class, 'store']
);

$router->get(
    '/admin/roles/create',
    [RoleController::class, 'create']
);

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin',
    [AdminController::class, 'index']
);

$router->get(
    '/admin/users',
    [AdminController::class, 'users']
);

$router->get(
    '/admin/users/create',
    [AdminController::class, 'createUser']
);

$router->get(
    '/admin/roles',
    [RoleController::class, 'index']
);

$router->get(
    '/admin/users/edit',
    [AdminController::class, 'editUser']
);

$router->post(
    '/admin/users/create',
    [AdminController::class, 'storeUser']
);

$router->post(
    '/admin/users/update',
    [AdminController::class, 'updateUser']
);

$router->post(
    '/admin/users/delete',
    [AdminController::class, 'deleteUser']
);

$router->get(
    '/admin/settings',
    [SettingsController::class, 'index']
);

$router->post(
    '/admin/settings/update',
    [SettingsController::class, 'update']
);
/*
|--------------------------------------------------------------------------
| Admin Media
|--------------------------------------------------------------------------
*/
$router->get(
    '/admin/media',
    [AdminController::class, 'media']
);

$router->get(
    '/admin/media/view',
    [AdminController::class, 'viewMedia']
);

$router->get(
    '/admin/media/upload',
    [AdminController::class, 'uploadMedia']
);

$router->post(
    '/admin/media/upload',
    [AdminController::class, 'storeMedia']
);

$router->post(
    '/admin/media/delete',
    [AdminController::class, 'deleteMedia']
);
/*
|--------------------------------------------------------------------------
| Admin Posts
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/posts',
    [AdminPostsController::class, 'index']
);

$router->get(
    '/admin/posts/create',
    [AdminPostsController::class, 'create']
);

$router->post(
    '/admin/posts/store',
    [AdminPostsController::class, 'store']
);

$router->get(
    '/admin/posts/edit',
    [AdminPostsController::class, 'edit']
);

$router->post(
    '/admin/posts/update',
    [AdminPostsController::class, 'update']
);

$router->post(
    '/admin/posts/delete',
    [AdminPostsController::class, 'delete']
);

/*
|--------------------------------------------------------------------------
| Admin Pages
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/pages',
    [AdminPagesController::class, 'index']
);

$router->get(
    '/admin/pages/create',
    [AdminPagesController::class, 'create']
);

$router->post(
    '/admin/pages/store',
    [AdminPagesController::class, 'store']
);

$router->get(
    '/admin/pages/edit',
    [AdminPagesController::class, 'edit']
);

$router->post(
    '/admin/pages/update',
    [AdminPagesController::class, 'update']
);

$router->post(
    '/admin/pages/delete',
    [AdminPagesController::class, 'delete']
);

/*
|--------------------------------------------------------------------------
| Admin Comments
|--------------------------------------------------------------------------
*/

$router->get(
    '/admin/comments',
    [CommentController::class, 'index']
);

$router->post(
    '/admin/comments/approve',
    [CommentController::class, 'approve']
);

$router->post(
    '/admin/comments/delete',
    [CommentController::class, 'delete']
);

/*
|--------------------------------------------------------------------------
| Roles
|--------------------------------------------------------------------------
*/

$router->post(
    '/admin/roles/create',
    [RoleController::class, 'store']
);

$router->get(
    '/admin/roles/edit',
    [RoleController::class, 'edit']
);

$router->post(
    '/admin/roles/update',
    [RoleController::class, 'update']
);

$router->post(
    '/admin/roles/delete',
    [RoleController::class, 'delete']
);

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

$router->get(
    '/dashboard',
    [DashboardController::class, 'index']
);

$router->get(
    '/dashboard/posts/edit',
    [DashboardController::class, 'editPost']
);

$router->post(
    '/dashboard/posts/update',
    [DashboardController::class, 'updatePost']
);

$router->post(
    '/dashboard/posts/store',
    [DashboardController::class, 'storePost']
);

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

$router->get(
    '/login',
    [AuthController::class, 'login']
);

$router->post(
    '/login',
    [AuthController::class, 'authenticate']
);

$router->post(
    '/logout',
    [AuthController::class, 'logout']
);

$router->get(
    '/register',
    [AuthController::class, 'register']
);

$router->post(
    '/register',
    [AuthController::class, 'store']
);

$router->get(
    '/verify',
    [VerifyController::class, 'verify']
);

$router->get(
    '/contact',
    [ContactController::class, 'index']
);

$router->post(
    '/contact',
    [ContactController::class, 'send']
);

$router->post(
    '/dashboard/upload-image',
    [DashboardController::class, 'uploadImage']
);

$router->post(
    '/dashboard/save-theme',
    [DashboardController::class, 'saveTheme']
);

$router->post(
    '/dashboard/change-password',
    [
        DashboardController::class,
        'changePassword'
    ]
);

