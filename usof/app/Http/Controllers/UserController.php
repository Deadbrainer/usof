<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth, DB, Mail, Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // $token = JWTAuth::refresh(JWTAuth::getToken());

        return response()->json(compact('token'));
    }

    public function verifyUser($verification_code)
    {
        $check = DB::table('user_verifications')->where('token', $verification_code)->first();

        // print_r($check);

        if (!is_null($check)) {
            $user = User::find($check->user_id);

            // print_r($user->is_verified);

            if ($user->is_verified == 1) {
                return response()->json([
                    'success' => true,
                    'message' => 'Account already verified..'
                ]);
            }

            // $user->update(['is_verified' => 1]);

            DB::update('update users set is_verified = 1 where name = ?', [$user->name]);

            DB::table('user_verifications')->where('token', $verification_code)->delete();

            return response()->json([
                'success' => true,
                'message' => 'You have successfully verified your email address.'
            ]);
        }

        return response()->json(['success' => false, 'error' => "Verification code is invalid."]);
    }

    public function logout(Request $request)
    {

        $token = $request->header('token');

        try {
            JWTAuth::parseToken()->invalidate($token);

            return response()->json([
                'success' => true,
                'message' => 'successfully logged out'
            ]);
        } catch (TokenExpiredException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'a token is expired'

            ], 401);
        } catch (TokenInvalidException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'invalid token'
            ], 401);
        } catch (JWTException $exception) {
            return response()->json([
                'success' => false,
                'message' => 'missing a token'
            ], 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'fullname' => $request->get('fullname'),
            'password' => Hash::make($request->get('password')),
        ]);

        $token = JWTAuth::fromUser($user);

        $name = $request->name;
        $email = $request->email;

        $verification_code = uniqid(10);
        DB::table('user_verifications')->insert(['user_id' => $user->id, 'token' => $verification_code]);

        $subject = "Please verify your email address.";
        Mail::send(
            'email.verify',
            ['name' => $name, 'verification_code' => $verification_code],
            function ($mail) use ($email, $name, $subject) {
                $mail->from("cool.edikss77@gmail.com", "From Dudos");
                $mail->to($email, $name);
                $mail->subject($subject);
            }
        );

        return response()->json(compact('user', 'token'), 201);
    }

    public function getEveryone()
    {
        // $usersData = DB::table('users')->get();
        $usersData = DB::select('select * from users');
        return response()->json([
            'data' => $usersData
        ]);
    }

    public function getSpecified($user_id)
    {
        $userData = DB::select('select * from users where id = ' . $user_id);
        return response()->json([
            'data' => $userData
        ]);
    }

    static public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }

        return $user;
    }

    public function createUser(Request $request)
    {
        $user = $this->getAuthenticatedUser();

        $role = $user->role;

        if ($role != 'admin') {
            return response()->json([
                'data' => 'That content is only for admins'
            ]);
        }

        $rules = [
            'fullname' => 'required|max:255',
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|confirmed|min:6',
            'role' => 'required',
        ];

        $input = $request->only(
            'fullname',
            'name',
            'email',
            'password',
            'password_confirmation',
            'role'
        );

        $validator = Validator::make($input, $rules);

        if ($validator->fails()) {
            $error = $validator->messages()->toJson();
            return response()->json(['success' => false, 'error' => $error]);
        }

        $fullName = $request->fullName;
        $name = $request->name;
        $email = $request->email;
        $password = $request->password;
        $role = $request->role;
        User::create(['name' => $name, 'email' => $email, 'password' => Hash::make($password), 'role' => $role]);

        $dd = DB::update('update users set fullname = ?', [$fullName]);

        return response()->json([
            'data' => 'User created'
        ]);
    }

    public function updateData(Request $request, $user_id)
    {

        $user = $this->getAuthenticatedUser();

        if ($user->id != $user_id) {
            return response()->json([
                'data' => 'That feature is only for profile owner'
            ]);
        }

        $name = $request->name;
        $fullname = $request->fullname;
        $email = $request->email;
        $password = $request->password;
        $role = $request->role;

        $changes = array();

        if ($fullname) {
            DB::update('update users set fullname = ? where id = ?', [$fullname, $user_id]);
            $changes['fullname'] = $fullname;
        }

        if ($name) {
            DB::update('update users set name = ? where id = ?', [$name, $user_id]);
            $changes['name'] = $name;
        }

        if ($email) {
            DB::update('update users set email = ? where id = ?', [$email, $user_id]);
            $changes['email'] = $email;
        }

        if ($password) {
            DB::update('update users set password = ? where id = ?', [$password, $user_id]);
            $changes['password'] = $password;
        }

        if ($role) {
            DB::update('update users set role = ? where id = ?', [$role, $user_id]);
            $changes['role'] = $role;
        }

        DB::update('update users set updated_at = ? where id = ?', [Carbon::now()->toDateTimeString(), $user_id]);

        return response()->json([
            'changes' =>  $changes
        ]);
    }

    public function deleteUser($user_id)
    {

        $user = $this->getAuthenticatedUser();

        $role = $user->role;

        $id = $user->id;

        if ($role != 'admin' && $id != $user_id) {
            return response()->json([
                'data' => 'That content is only for admins and profile owners'
            ]);
        }

        $quan = DB::delete('delete from users where id = ?', [$user_id]);
        if ($quan) {
            return response()->json([
                'data' => 'User deleted'
            ]);
        } else {
            return response()->json([
                'data' => 'No user with such id'
            ]);
        }
    }

    public function imageView()
    {
        return view('imageView');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageName = time() . '.' . $request->image->extension();

        DB::update('update users set avatar = ? where id = ?', [$imageName, $this->getAuthenticatedUser()->id]);

        $request->image->move(public_path('images'), $imageName);

        return response()->json([
            'data' => 'Avatar was uploaded'
        ]);
    }
}
