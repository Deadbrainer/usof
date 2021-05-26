<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PostController;

use App\Http\Controllers\ChangeController;
use App\Http\Controllers\ResetController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::group(['prefix' => 'api/'], function () {
    Route::group(['prefix' => 'auth/'], function () {
        Route::post('register', [UserController::class, 'register']);
        Route::post('login', [UserController::class, 'authenticate']);
        Route::post('logout', [UserController::class, 'logout']);
        Route::get('password-reset/{token}', [ChangeController::class, 'resetPassword']);
        Route::get('password-reset', [ResetController::class, 'sendPasswordResetEmail']);
    });

    Route::post('users', [UserController::class, 'createUser']);
    Route::get('users', [UserController::class, 'getEveryone']);
    Route::get('users/{user_id}', [UserController::class, 'getSpecified']);
    Route::get('user/verify/{verification_code}', [UserController::class, 'verifyUser']);
    Route::get('open', [DataController::class, 'open']);

    Route::group(['middleware' => ['jwt.verify']], function () {
        Route::patch('users/{user_id}', [UserController::class, 'updateData']);
        Route::delete('users/{user_id}', [UserController::class, 'deleteUser']);
        Route::get('user', [UserController::class, 'getAuthenticatedUser']);
        Route::get('closed', [DataController::class, 'closed']);
        Route::get('users/avatar', [UserController::class, 'imageView']);
        Route::post('users/avatar', [UserController::class, 'uploadImage']);
        Route::post('posts', [PostController::class, 'doPost']);
        Route::post('posts/{post_id}/comments', [PostController::class, 'doComment']);
        Route::post('posts/{post_id}/like', [PostController::class, 'doLike']);
        Route::post('posts/{post_id}/dislike', [PostController::class, 'doDislike']);
        Route::delete('posts/{post_id}/like', [PostController::class, 'deleteLike']);
        Route::delete('posts/{post_id}/dislike', [PostController::class, 'deleteDislike']);
        Route::patch('posts/{post_id}', [PostController::class, 'updatePost']);
        Route::delete('posts/{post_id}', [PostController::class, 'deletePost']);
        Route::post('categories', [CategoriesController::class, 'doCategory']);
        Route::patch('categories/{category_id}', [CategoriesController::class, 'updateData']);
        Route::delete('categories/{category_id}', [CategoriesController::class, 'deletecategory']);
        Route::post('comments/{comment_id}/like', [CommentController::class, 'doLike']);
        Route::post('comments/{comment_id}/dislike', [CommentController::class, 'doDislike']);
        Route::delete('comments/{comment_id}/like', [CommentController::class, 'deleteLike']);
        Route::delete('comments/{comment_id}/dislike', [CommentController::class. 'deleteDislike']);
        Route::patch('comments/{comment_id}', [CommentController::class, 'updateData']);
        Route::delete('comments/{comment_id}', [CommentController::class, 'deleteComment']);
        Route::post('posts/{post_id}/lock', [PostController::class, 'lockPost']);
        Route::post('posts/{post_id}/lock-comments', [PostController::class, 'lockComments']);
    });
    Route::get('posts', [PostController::class, 'getPosts']);
    Route::get('posts/{post_id}', [PostController::class, 'getSpecified']);
    Route::get('posts/{post_id}/comments', [PostController::class, 'getComment']);
    Route::get('posts/{post_id}/categories', [PostController::class, 'getCategories']);
    Route::get('posts/{post_id}/like', [PostController::class, 'getLikes']);
    Route::get('categories/{category_id}', [CategoriesController::class, 'getSpecified']);
    Route::get('categories', [CategoriesController::class, 'getCategories']);
    Route::get('categories/{category_id}/posts', [CategoriesController::class, 'getPostsByCategory']);
    Route::get('comments/{comment_id}', [CommentController::class, 'getSpecified']);
    Route::get('comments/{comment_id}/like', [CommentController::class, 'getLikes']);
});
