<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResetController extends Controller
{
    public function sendPasswordResetEmail(Request $request){
        // If email does not exist
        if(!$this->validEmail($request->email)) {
            return response()->json([
                'message' => 'Email is not valid'
            ], Response::HTTP_NOT_FOUND);
        } else {
            // If email exists
            $this->sendMail($request->email);
            return response()->json([
                'message' => 'Check your inbox, we have sent a link to reset email.'
            ], Response::HTTP_OK);            
        }
    }

    public function sendMail($email){
        $verification_code = $this->generateToken($email);
        $user_id = UserController::getAuthenticatedUser()->id;
        DB::table('user_verifications')->insert(['user_id' =>$user_id, 'token' => $verification_code]);

        $subject = "Here is your token";
        Mail::send(
            'email.resetPassword',
            ['verification_code' => $verification_code],
            function ($mail) use ($email, $subject) {
                $mail->from("roma141402@gmail.com", "From Roman LLC");
                $mail->to($email);
                $mail->subject($subject);
            }
        );
    }

    public function validEmail($email) {
        
        $user_id = UserController::getAuthenticatedUser()->id;
        $email_ = DB::select('select email from users where id = ?', [$user_id])[0]->email;
        if ($email == $email_) {
            return true;
        } else {
            return false;
        }
    }

    public function generateToken($email){
      $isOtherToken = DB::table('password_resets')->where('email', $email)->first();

      if($isOtherToken) {
        return $isOtherToken->token;
      }

      $token = Str::random(80);;
      $this->storeToken($token, $email);
      return $token;
    }

    public function storeToken($token, $email){
        DB::table('password_resets')->insert([
            'email' => $email,
            'token' => $token,
            'created_at' => Carbon::now()            
        ]);
    }
}
