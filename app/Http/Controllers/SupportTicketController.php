<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use App\User;
use App\Role\RoleChecker;
use App\SupportTicket;
use App\SupportTicketMessage;

class SupportTicketController extends Controller
{
    /**
     * Create a new SupportTicketController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    /**
     * Returns an array of all the support tickets in the system.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($active = null)
    {
        // Create new instance of the Role Checker.
        $roleChecker = new RoleChecker();

        // Check if the authenticated user is the author of the ticket, or if they have permission to the support role.
        if(!$roleChecker->check(Auth::user(), 'ROLE_SUPPORT'))
        {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No permissions to view the ticket list."
                ]
            ];
            return response()->json($response, 403);
        }

        $tickets = null;
        if($active == null) {
            // If parameter not passed, load all tickets.
            $tickets = SupportTicket::without('messages')->get();

            $response = [
                'success' => true,
                'response' => $tickets
            ];
            return response()->json($response);
        } 

        // 'Clean' the parameter so the it can either be True, False, or NULL
        $active = filter_var($active, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        // If NULL, return an error.
        if($active == null) {
            $response = [
                'success' => false,
                'response' => "Invalid parameter passed."
            ];
            return response()->json($response, 400);
        }

        // If active is set, get tickets conditionally and then return.
        $tickets = SupportTicket::where('active', $active)->without('messages')->get();
        $response = [
            'success' => true,
            'response' => $tickets
        ];
        return response()->json($response);
    }

    /**
     * Returns an array of a user's support tickets.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function showByUser($userid = null)
    {
        if($userid == null) {
            $userid = Auth::user()->id;
        } else {
            // Create new instance of the Role Checker.
            $roleChecker = new RoleChecker();

            // Check if the authenticated user is the author of the ticket, or if they have permission to the support role.
            if(!$roleChecker->check(Auth::user(), 'ROLE_SUPPORT'))
            {
                $response = [
                    'success' => false,
                    'response' => [
                        "message" => "No permissions to view the ticket list."
                    ]
                ];
                return response()->json($response, 403);
            }
        }

        // Load the user from the database.
        $user = User::find($userid);

        // Check if the specified user exists within the system.
        if(!$user) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The specified user does not exist."
                ]
            ];
            return response()->json($response, 404);
        }


        // Make use of the relationship to get a list of a user's support tickets.
        return response()->json($user->supportTickets);
    }

    /**
     * Returns data about a Support Ticket.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($ticketid)
    {
        // Fetch support ticket from the database
        $supportTicket = SupportTicket::find($ticketid);

        // If it does not exist, return error.
        if(!$supportTicket) {
            return response()->json([
                'success' => false, 
                'message' => 'Ticket not found.'
            ], 404);
        }

        // Create new instance of the Role Checker.
        $roleChecker = new RoleChecker();

        // Check if the authenticated user is the author of the ticket, or if they have permission to the support role.
        if(!($supportTicket->author->id === Auth::user()->id || $roleChecker->check(Auth::user(), 'ROLE_SUPPORT')))
        {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No permissions to make changes to this ticket."
                ]
            ];
            return response()->json($response, 403);
        }


        // Return the ticket.
        /**
         * NOTE: We do not have to manually retrieve details about the author 
         *       and the messages associated with this ticket because of the
         *       relationships set up between the entities.
         *
         */
        $response = [
            'success' => true,
            'response' => [
                "ticket" => $supportTicket
            ]
        ];
        return response()->json($response);
        
    }

    /**
     * Creates a support ticket.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        // Validate the submission. Tickets need a ticket and an initial message.
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'message' => 'required',
        ]);

        // If validation fails, return an error message.
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The form is not complete."
                ]
            ];
            return response()->json($response, 400);
        }

        // Create a new instance of a SupportTicket and store it in the database.
        $supportTicket = new SupportTicket();
        $supportTicket->title = $request->title;
        $supportTicket->user_id = Auth::user()->id;
        $supportTicket->save();

        // Create a new instance of a support ticket message.
        $message = new SupportTicketMessage();
        $message->user_id = Auth::user()->id;
        $message->content = $request->message;

        // Store the message in the database, linking it to the ticket it belongs to.
        $supportTicket->messages()->save($message);


        // Return the new ticket and message.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The support ticket was created.",
                "ticket" => $supportTicket,
                "ticketMessage" => $message
            ]
        ];
        return response()->json($response);
    }

    /**
     * Adds a support ticket message.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addMessage(Request $request, $ticketid)
    {
        // Try to load the support ticket.
        $supportTicket = SupportTicket::find($ticketid);

        // If it does not exist, return an error.
        if(!$supportTicket) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The ticket does not exist."
                ]
            ];
            return response()->json($response, 404);
        }

        // Create new instance of the Role Checker.
        $roleChecker = new RoleChecker();

        // Check if the authenticated user is the author of the ticket, or if they have permission to the support role.
        if(!($supportTicket->author->id === Auth::user()->id || $roleChecker->check(Auth::user(), 'ROLE_SUPPORT')))
        {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No permissions to make changes to this ticket."
                ]
            ];
            return response()->json($response, 403);
        }

        // Validate the form.
        $validator = Validator::make($request->all(), [
            'message' => 'required',
        ]);

        // Else, return an error.
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The form is not complete."
                ]
            ];
            return response()->json($response, 400);
        }


        // Create a new instance of a support ticket message.
        $message = new SupportTicketMessage();
        $message->user_id = Auth::user()->id;
        $message->content = $request->message;

        // Store the message in the database, linking it to the ticket it belongs to.
        $supportTicket->messages()->save($message);

        // Return the message object back.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The message was successfully added to the ticket.",
                "ticketMessage" => $message,
                "ticket" => $supportTicket
            ]
        ];
        return response()->json($response);
    }

    /**
     * Updates a ticket. User/Support Staff can change the ticket's title or it's active state.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $ticketid)
    {
        // Try to load the support ticket.
        $supportTicket = SupportTicket::find($ticketid);

        // If it does not exist, return an error.
        if(!$supportTicket) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The ticket does not exist."
                ]
            ];
            return response()->json($response, 404);
        }

        // Create new instance of the Role Checker.
        $roleChecker = new RoleChecker();

        // Check if the authenticated user is the author of the ticket, or if they have permission to the support role.
        if(!($supportTicket->author->id === Auth::user()->id || $roleChecker->check(Auth::user(), 'ROLE_SUPPORT')))
        {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "No permissions to make changes to this ticket."
                ]
            ];
            return response()->json($response, 403);
        }

        // Validate the form. (The sometimes rule makes sure the input is validated if it's included with the request)
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|min:3|max:255',
            'active' => 'sometimes|required|boolean'
        ]);

        // Else, return an error.
        if ($validator->fails()) {
            $response = [
                'success' => false,
                'response' => [
                    "message" => "The form is not complete."
                ]
            ];
            return response()->json($response, 400);
        }

        // If either of the attributes exist with the form, update the object accordingly.
        if($request->has('title')) {
            $supportTicket->title = $request->title;
        }

        if($request->has('active')) {
            $supportTicket->active = $request->active;
        }

        // Save the object.
        $supportTicket->save();

        // Return the message object back.
        $response = [
            'success' => true,
            'response' => [
                "message" => "The support ticket was successfully edited.",
                "ticket" => $supportTicket
            ]
        ];
        return response()->json($response);
    }
}
