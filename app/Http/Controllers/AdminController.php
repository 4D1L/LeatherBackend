<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Returns a list of users registered on the application.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllUsers()
    {
        $users = User::all();
        
        $response = [
            'success' => true, 
            'response' => $users
        ];

        return response()->json($response, 200);
    }

    /**
     * Returns a list of users registered on the application.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser($userid)
    {
        $user = User::find($userid);

        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "User does not exist."
                ]
            ];

            return response()->json($response, 404);
        }
        
        $response = [
            'success' => true, 
            'response' => $user
        ];

        return response()->json($response, 200);
    }

    /**
     * Allows an admin to edit a user's personal details.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function editUser(Request $request, $userid)
    {
        $user = User::find($userid);

        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "User does not exist."
                ]
            ];

            return response()->json($response, 404);
        }

        if(!$request->hasAny(['name', 'email'])) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No data provided."
                ]
            ];

            return response()->json($response, 403);
        }

        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        $user->save();
    

        $response = [
            'success' => true, 
            'response' => $user
        ];

        return response()->json($response, 200);
    }
}
