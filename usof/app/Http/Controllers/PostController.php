<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB, Carbon\Carbon;

use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function getPosts(Request $request)
    {
        $bydateSort = $request->date;

        $bycategoriesFilter = $request->categories;
        $bydateIntervalFilter = $request->dateInterval;
        $bystatusFilter = $request->status;

        $postsData = DB::table('posts');

        if ($bycategoriesFilter) {
            // $postsData = DB::table('posts')->where('categories', 'like', '%|' . $bycategoriesFilter . '%')->orWhere('categories', 'like', '%' . $bycategoriesFilter . '|%')->get();
            $postsData = $postsData->where('categories', 'like', '%|' . $bycategoriesFilter . '%')->orWhere('categories', 'like', '%' . $bycategoriesFilter . '|%');
        }

        if ($bydateIntervalFilter) {
            switch ($bydateIntervalFilter) {
                case 1:
                    $postsData = $postsData->whereDate('created_at', '=', Carbon::today()->toDateString());
                    break;
                case 2:
                    $postsData = $postsData->whereMonth('created_at', '=', date('m'))->whereYear('created_at', '=', date('Y'));
                    break;
                default:
                    $postsData = $postsData->whereYear('created_at', '=', date('Y'));
                    break;
            }
        }

        if ($bystatusFilter) {
            switch ($bystatusFilter) {
                case 1:
                    $postsData = $postsData->where('status', '=', 'active');
                    break;
                default:
                    $postsData = $postsData->where('status', '=', 'inactive');
                    break;
            }
        }

        if ($bydateSort) {
            $postsData = $postsData->orderBy('created_at', 'DESC');
        } else {
            $postsData = $postsData->orderBy('likes', 'DESC');
        }

        // $postsData = DB::select('select * from posts');
        return response()->json([
            'data' => $postsData->get()
        ]);
    }

    public function getSpecified($post_id)
    {
        $postsData = DB::select('select * from posts where id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        return response()->json([
            'data' => $postsData
        ]);
    }

    public function getComment($post_id)
    {
        $postsData = DB::select('select * from comments where post_id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        return response()->json([
            'data' => $postsData
        ]);
    }

    public function doComment($post_id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'content' => 'required|string|max:511',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if ($post = DB::select("select * from posts where id = " . $post_id)) {

            if ($post[0]->is_locked == 1) {
                return response()->json([
                    'data' => "That post is locked"
                ]);
            }

            if ($post[0]->is_locked_commenting == 1) {
                return response()->json([
                    'data' => "That post comment section is locked"
                ]);
            }

            $user_id = UserController::getAuthenticatedUser()->id;

            $comment = DB::insert('insert into comments (post_id, content, created_at, updated_at, author) values (?, ?, ?, ?, ?)', [$post_id, $request->content, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $user_id]);
            return response()->json([
                'Success' => $comment
            ]);
        } else {
            return response()->json([
                'data' => "There is no post with such id"
            ]);
        }
    }

    public function getCategories($post_id)
    {
        $postsData = DB::select('select * from categories where post_id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        return response()->json([
            'data' => $postsData
        ]);
    }

    public function doLike($post_id)
    {
        $postsData = DB::select('select * from posts where id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        if ($postsData[0]->is_locked == 1) {
            return response()->json([
                'data' => "That post is locked"
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $post_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        $check_if_2 = false;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and post_id = ' . $post_id)) {
            $m = array_pop($like);
            if ($m->type == 'like') {
                return response()->json([
                    'data' =>  'You have already liked that post'
                ]);
            } else if ($m->type == 'dislike') {
                $check_if_2 = true;
                DB::update('update likes set type = "like" where author = ? and post_id = ?', [$user_id, $post_id]);
            }
        } else {
            DB::insert('insert into likes (post_id, created_at, updated_at, author, type) values (?, ?, ?, ?, ?)', [$post_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $user_id, 'like']);
        }

        DB::update('update posts set likes = likes + 1 where id = ?', [$post_id]); // increase by 1

        if ($check_if_2) {
            DB::update('update users set rating = rating + 2 where id = ?', [$author]); // increase by 2
        } else {
            DB::update('update users set rating = rating + 1 where id = ?', [$author]); // increase by 1
        }

        return response()->json([
            'Success' => true
        ]);
    }

    public function deleteLike($post_id)
    {
        $postsData = DB::select('select * from posts where id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        if ($postsData[0]->is_locked == 1) {
            return response()->json([
                'data' => "That post is locked"
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $post_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and post_id = ' . $post_id)) {
            $m = array_pop($like);
            if ($m->type == 'like') {
                DB::delete('delete from likes where post_id = ? and author = ?', [$post_id, $user_id]);

                DB::update('update posts set likes = likes - 1 where id = ?', [$post_id]); // decrease by 1

                DB::update('update users set rating = rating - 1 where id = ?', [$author]); // decrease by 1

                return response()->json([
                    'data' =>  'Like has been deleted'
                ]);
            } else {
                return response()->json([
                    'data' =>  'You have dislaked that post, not liked'
                ]);
            }
        } else {
            return response()->json([
                'data' =>  'You havent liked that post yet'
            ]);
        }
    }

    public function doDislike($post_id)
    {
        $postsData = DB::select('select * from posts where id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        if ($postsData[0]->is_locked == 1) {
            return response()->json([
                'data' => "That post is locked"
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $post_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        $check_if_2 = false;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and post_id = ' . $post_id)) {
            $m = array_pop($like);
            if ($m->type == 'dislike') {
                return response()->json([
                    'data' =>  'You have already disliked that post'
                ]);
            } else if ($m->type == 'like') {
                $check_if_2 = true;
                DB::update('update likes set type = "dislike" where author = ? and post_id = ?', [$user_id, $post_id]);
            }
        } else {
            DB::insert('insert into likes (post_id, created_at, updated_at, author, type) values (?, ?, ?, ?, ?)', [$post_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString(), $user_id, 'dislike']);
        }

        DB::update('update posts set likes = likes - 1 where id = ?', [$post_id]); // decrease by 1

        if ($check_if_2) {
            DB::update('update users set rating = rating - 2 where id = ?', [$author]); // decrease by 2
        } else {
            DB::update('update users set rating = rating - 1 where id = ?', [$author]); // decrease by 1
        }

        return response()->json([
            'Success' => true
        ]);
    }

    public function deleteDislike($post_id)
    {
        $postsData = DB::select('select * from posts where id = ' . $post_id);

        if (empty($postsData)) {
            return response()->json([
                'data' => 'No post with such id'
            ]);
        }

        if ($postsData[0]->is_locked == 1) {
            return response()->json([
                'data' => "That post is locked"
            ]);
        }

        $user_id = UserController::getAuthenticatedUser()->id;

        $user_raw = DB::select('select author from posts where id = ' . $post_id);
        $m = array_pop($user_raw);
        $author = $m->author;

        if ($like = DB::select('select * from likes where author = ' . $user_id . ' and post_id = ' . $post_id)) {
            $m = array_pop($like);
            if ($m->type == 'dislike') {
                DB::delete('delete from likes where post_id = ? and author = ?', [$post_id, $user_id]);

                DB::update('update posts set likes = likes + 1 where id = ?', [$post_id]); // increase by 1

                DB::update('update users set rating = rating + 1 where id = ?', [$author]); // increase by 1

                return response()->json([
                    'data' =>  'Dislike has been deleted'
                ]);
            }
        } else {
            return response()->json([
                'data' =>  'You havent disliked that post yet'
            ]);
        }
    }

    public function getLikes($post_id)
    {
        $postsData = DB::select('select * from likes where post_id = ' . $post_id);
        return response()->json([
            'data' => $postsData
        ]);
    }

    private function getPostIdByUserId($user_id)
    {
        $post_ids = DB::select('select id from posts where author = ' . $user_id);
        $post_id = array_pop($post_ids);
        return $post_id->id;
    }

    public function doPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string|max:511',
            'categories' => 'required|string|max:511',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $title = $request->title;
        $content = $request->content;
        $categories = $request->categories;

        $user_id = UserController::getAuthenticatedUser()->id;

        DB::insert('insert into posts (categories, title, content, author, created_at, updated_at) values (?, ?, ?, ?, ?, ?)', [$categories, $title, $content, $user_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString()]);

        // $post_id = $this->getPostIdByUserId($user_id);

        // $categories_arr = explode('|', $categories);

        // foreach ($categories_arr as $one) {
        //     DB::insert('insert into categories (name, post_id, created_at, updated_at) values (?, ?, ?, ?)', [$one, $post_id, Carbon::now()->toDateTimeString(), Carbon::now()->toDateTimeString()]);
        // }

        return response()->json([
            'success' => true
        ]);
    }

    public function updatePost(Request $request, $post_id)
    {
        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from posts where author = ? and id = ?', [$user_id, $post_id]);

        if ($ok) {

            if ($ok[0]->is_locked == 1) {
                return response()->json([
                    'data' => "That post is locked"
                ]);
            }

            $title = $request->title;
            $content = $request->content;
            $categories = $request->categories;


            $date = $request->date;

            $changes = array();

            if ($date) {
                DB::update('update posts set created_at = ? where id = ?', [$date, $post_id]);
                $changes['date'] = $date;
            }

            if ($title) {
                DB::update('update posts set title = ? where id = ?', [$title, $post_id]);
                $changes['title'] = $title;
            }

            if ($content) {
                DB::update('update posts set content = ? where id = ?', [$content, $post_id]);
                $changes['content'] =  $content;
            }

            if ($categories) {
                DB::update('update posts set categories = ? where id = ?', [$categories, $post_id]);

                $categories_arr = explode('|', $categories);

                $changes['categories'] = $categories_arr;
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

    public function deletePost($post_id)
    {
        $role = UserController::getAuthenticatedUser()->role;
        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from posts where author = ? and id = ?', [$user_id, $post_id]);

        if ($role == 'admin' || $ok) {
            $quan = DB::delete('delete from posts where id = ?', [$post_id]);
            if ($quan) {
                return response()->json([
                    'data' => 'Post deleted'
                ]);
            } else {
                return response()->json([
                    'data' => 'No post with such id'
                ]);
            }
        } else {
            return response()->json([
                'data' => 'That feature is only for admins or post creators'
            ]);
        }
    }

    public function lockPost($post_id)
    {
        $role = UserController::getAuthenticatedUser()->role;
        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from posts where author = ? and id = ?', [$user_id, $post_id]);

        if ($role == 'admin' || $ok) {
            DB::update('update posts set is_locked = 1 where id = ?', [$post_id]);

            return response()->json([
                'data' => 'Post is locked'
            ]);
        } else {
            return response()->json([
                'data' => 'That feature is only for admins or post creators'
            ]);
        }
    }

    public function lockComments($post_id)
    {
        $role = UserController::getAuthenticatedUser()->role;
        $user_id = UserController::getAuthenticatedUser()->id;

        $ok = DB::select('select * from posts where author = ? and id = ?', [$user_id, $post_id]);

        if ($role == 'admin' || $ok) {
            DB::update('update posts set is_locked_commenting = 1 where id = ?', [$post_id]);

            return response()->json([
                'data' => 'Post commenting is locked'
            ]);
        } else {
            return response()->json([
                'data' => 'That feature is only for admins or post creators'
            ]);
        }
    }
}
