<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;

class ChangeController extends Controller
{
    // Verify if token is valid
    private function updatePasswordRow($request)
    {
        return DB::table('password_resets')->where([
            'token' => $request->passwordToken
        ]);
    }
    // Reset password
    public function resetPassword(Request $request, $token)
    {
        $userData = DB::table('password_resets')->where('token', $token)->first();
        // update password
        if ($userData) {
            DB::update('update users set password = "' . bcrypt($request->password) . '" where email = ?', [$userData->email]);
            // remove verification data from db
            $this->updatePasswordRow($request)->delete();

            // reset password response
            return response()->json([
                'data' => 'Password has been updated.'
            ], Response::HTTP_CREATED);
        } else {
            return response()->json([
                'data' => 'There is no user with such token.'
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
