<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use App\Role\UserRole;
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

        // Check if the User exists within the system.
        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "User does not exist."
                ]
            ];

            return response()->json($response, 404);
        }
        
        // Return success status and the data.
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

        // Check if the User exists within the system.
        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "User does not exist."
                ]
            ];

            return response()->json($response, 404);
        }

        // Check if any data was passed, if not, return error message.
        if(!$request->hasAny(['name', 'email'])) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No data provided."
                ]
            ];

            return response()->json($response, 403);
        }

        // If data was passed and ...
        if ($request->has('name')) {
            $user->name = $request->name;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        // ... save the data.
        $user->save();
    

        // Return updated user object back.
        $response = [
            'success' => true, 
            'response' => $user
        ];

        return response()->json($response, 200);
    }

    /**
     * Allows an admin to add a role to a user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addRoleToUser(Request $request, $userid)
    {
        // Check if a role was passed to the method.
        if(!$request->has('role')) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No role provided."
                ]
            ];
            return response()->json($response, 403);
        }

        $user = User::find($userid);

        // Check if the specified user exists within the system.
        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The specified user does not exist."
                ]
            ];
            return response()->json($response, 403);
        }

        // Get the available roles within the system.
        $roles = UserRole::getRoleList();

        // Flip keys and values around and then make the keys uppercase and then flip case.
        $roles = array_flip($roles);
        $roles = array_change_key_case($roles, CASE_UPPER); 
        $roles = array_flip($roles);

        $role = strtoupper($request->role);

        // If the role does not exist within the scope of the system, return an error.
        if (($key = array_search($role, $roles)) === false) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "Role doesn't exist."
                ]
            ];
            return response()->json($response, 403);
        }
        
        // Otherwise, add the role to the user.
        $user->addRole('ROLE_' . $role);
        $user->save();

        // Return the updated user object.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The " . $request->role . " role has been added to the user.",
                "role" => $request->role,
                "user" => $user
            ]
        ];

        return response()->json($response);

    }

    /**
     * Allows an admin to remove a role from a user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeRoleFromUser(Request $request, $userid)
    {
        // Check if a role was passed to the method.
        if(!$request->has('role')) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No role provided."
                ]
            ];
            return response()->json($response, 403);
        }

        $user = User::find($userid);

        // Check if the specified user exists within the system.
        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The specified user does not exist."
                ]
            ];
            return response()->json($response, 403);
        }

        $role = strtoupper($request->role);

        // Check if the user has the role that is trying to be removed, if not, return an error message.
        if(!$user->hasRole("ROLE_" . $role)) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The specified user does not have this role."
                ]
            ];
            return response()->json($response, 403);
        }
        
        // Remove the specified role from the user.
        $user->removeRole("ROLE_" . $role);
        $user->save();


        //Return the updated user model.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The " . $request->role . " role has been removed from the user.",
                "role" => $request->role,
                "user" => $user
            ]
        ];

        return response()->json($response);

    }
}
