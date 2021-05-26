<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB, Carbon\Carbon;

use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function getSpecified($comment_id)
    {
        $commentsData = DB::select('select * from comments where id = ' . $comment_id);

        if (empty($commentsData)) {
            return response()->json([
                'data' => 'No comments with such id'
            ]);
        }

        return response()->json([
            'data' => $commentsData
        ]);
    }

    public function getLikes($comment_id)
    {
        $commentData = DB::select('select * from likes where comment_id = ' . $comment_id);
        return response()->json([
            'data' => $commentData
        ]);
    }

    public function doLike($comment_id)
    {
        $commentsData = DB::select('select * from comments where id = ' . $comment_id);

        if (empty($commentsData)) {
            return response()->json([
                'data' => 'No comment with such id'
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $comment_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        $check_if_2 = false;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and comment_id = ' . $comment_id)) {
            $m = array_pop($like);
            if ($m->type == 'like') {
                return response()->json([
                    'data' =>  'You have already liked that comment'
                ]);
            } else if ($m->type == 'dislike') {
                $check_if_2 = true;
                DB::update('update likes set type = "like" where author = ? and comment_id = ?', [$user_id, $comment_id]);
            }
        } else {
            DB::insert('insert into likes (comment_id, created_at, updated_at, author, type) values (?, ?, ?, ?, ?)', [$comment_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $user_id, 'like']);
        }

        $rating_before = DB::select('select rating from users where id = ' . $author);
        $raw = array_pop($rating_before);

        if ($check_if_2) {
            DB::update('update users set rating = ? where id = ?', [$raw->rating + 2, $author]);
        } else {
            DB::update('update users set rating = ? where id = ?', [$raw->rating + 1, $author]);
        }

        return response()->json([
            'Success' => true
        ]);
    }

    public function deleteLike($comment_id)
    {
        $postsData = DB::select('select * from comments where id = ' . $comment_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No comment with such id'
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $comment_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and comment_id = ' . $comment_id)) {
            $m = array_pop($like);
            if ($m->type == 'like') {
                DB::delete('delete from likes where comment_id = ? and author = ?', [$comment_id, $user_id]);


                $rating_before = DB::select('select rating from users where id = ' . $author);
                $raw = array_pop($rating_before);

                DB::update('update users set rating = ? where id = ?', [$raw->rating - 1, $author]);


                return response()->json([
                    'data' =>  'Like has been deleted'
                ]);
            }
        } else {
            return response()->json([
                'data' =>  'You havent liked that comment yet'
            ]);
        }
    }

    public function doDislike($comment_id)
    {
        $commentsData = DB::select('select * from comments where id = ' . $comment_id);

        if (empty($commentsData)) {
            return response()->json([
                'data' => 'No comment with such id'
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $comment_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        $check_if_2 = false;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and comment_id = ' . $comment_id)) {
            $m = array_pop($like);
            if ($m->type == 'dislike') {
                return response()->json([
                    'data' =>  'You have already disliked that comment'
                ]);
            } else if ($m->type == 'like') {
                $check_if_2 = true;
                DB::update('update likes set type = "dislike" where author = ? and comment_id = ?', [$user_id, $comment_id]);
            }
        } else {
            DB::insert('insert into likes (comment_id, created_at, updated_at, author, type) values (?, ?, ?, ?, ?)', [$comment_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $user_id, 'dislike']);
        }

        $rating_before = DB::select('select rating from users where id = ' . $author);
        $raw = array_pop($rating_before);

        if ($check_if_2) {
            DB::update('update users set rating = ? where id = ?', [$raw->rating - 2, $author]);
        } else {
            DB::update('update users set rating = ? where id = ?', [$raw->rating - 1, $author]);
        }

        return response()->json([
            'Success' => true
        ]);
    }

    public function deleteDislike($comment_id)
    {
        $commentData = DB::select('select * from comments where id = ' . $comment_id);

        if (empty($commentData)) {
            return response()->json([
                'data' => 'No comment with such id'
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $comment_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and comment_id = ' . $comment_id)) {
            $m = array_pop($like);
            if ($m->type == 'dislike') {
                DB::delete('delete from likes where comment_id = ? and author = ?', [$comment_id, $user_id]);


                $rating_before = DB::select('select rating from users where id = ' . $author);
                $raw = array_pop($rating_before);

                DB::update('update users set rating = ? where id = ?', [$raw->rating + 1, $author]);


                return response()->json([
                    'data' =>  'Dislike has been deleted'
                ]);
            }
        } else {
            return response()->json([
                'data' =>  'You havent disliked that comment yet'
            ]);
        }
    }

    public function updateData(Request $request, $comment_id)
    {

        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from comments where author = ? and comment_id = ?', [$user_id, $comment_id]);

        if ($ok) {
            $content = $request->content;

            $changes = array();

            if ($content) {
                DB::update('update comments set content = ? where id = ?', [$content, $comment_id]);
                $changes['content'] = $content;
            }

            return response()->json([
                'changes' =>  $changes
            ]);
        } else {
            return response()->json([
                'data' => 'That feature is only for post creator'
            ]);
        }
    }

    public function deleteComment($comment_id)
    {
        $role = UserController::getAuthenticatedUser()->role;
        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from comments where author = ? and comment_id = ?', [$user_id, $comment_id]);

        if ($ok || $role == 'admin') {
            $quan = DB::delete('delete from comments where id = ?', [$comment_id]);
            if ($quan) {
                return response()->json([
                    'data' => 'Comment deleted'
                ]);
            } else {
                return response()->json([
                    'data' => 'No comment with such id'
                ]);
            }
        } else {
            return response()->json([
                'data' => 'That feature is only for post creator or admins'
            ]);
        }
    }
}
