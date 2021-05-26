<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use DB;

use Illuminate\Support\Facades\Validator;

class CategoriesController extends Controller
{
    public function getCategories()
    {
        $categoriesData = DB::select('select * from categories');
        return response()->json([
            'data' => $categoriesData
        ]);
    }

    public function getSpecified($category_id)
    {
        $categoriesData = DB::select('select * from categories where id = ' . $category_id);

        if (empty($categoriesData)) {
            return response()->json([
                'data' => 'No category with such id'
            ]);
        }

        return response()->json([
            'data' => $categoriesData
        ]);
    }

    public function getPostsByCategory($category_id)
    {
        $category_name_raw = DB::select('select * from categories where id = ?', [$category_id]);
        $m = array_pop($category_name_raw);

        if (empty($m)) {
            return response()->json([
                'data' => "There is no category with such id"
            ]);
        }
        $name = $m->name;

        $arr_posts = DB::select('select * from posts where (categories like ? or categories like ?)', ["%" . $name . "|%", "%|" . $name . "%"]);

        return response()->json([
            'data' => $arr_posts
        ]);
    }

    public function doCategory(Request $request)
    {
        $role = UserController::getAuthenticatedUser()->role;

        if ($role == 'admin') {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string|max:511',
            ]);

            if ($validator->fails()) {
                return response()->json($validator->errors()->toJson(), 400);
            }

            DB::insert('insert into categories (name, description) values (?, ?)', [$request->title, $request->description]);

            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'data' => 'That feature is only for admins'
            ]);
        }
    }

    public function updateData(Request $request, $category_id)
    {

        $role = UserController::getAuthenticatedUser()->role;

        if ($role == 'admin') {

            $name = $request->title;
            $description = $request->description;

            $changes = array();

            if ($name) {
                DB::update('update categories set name = ? where id = ?', [$name, $category_id]);
                $changes['name'] = $name;
            }

            if ($description) {
                DB::update('update categories set description = ? where id = ?', [$description, $category_id]);
                $changes['description'] = $description;
            }

            return response()->json([
                'changes' =>  $changes
            ]);
        } else {
            return response()->json([
                'data' => 'That feature is only for admins'
            ]);
        }
    }

    public function deleteCategory($category_id)
    {
        $role = UserController::getAuthenticatedUser()->role;

        if ($role == 'admin') {
            $quan = DB::delete('delete from categories where id = ?', [$category_id]);
            if ($quan) {
                return response()->json([
                    'data' => 'Category deleted'
                ]);
            } else {
                return response()->json([
                    'data' => 'No category with such id'
                ]);
            }
        } else {
            return response()->json([
                'data' => 'That feature is only for admins'
            ]);
        }
    }
}
