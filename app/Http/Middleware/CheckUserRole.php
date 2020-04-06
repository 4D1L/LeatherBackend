<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use App\Role\RoleChecker;
use Auth;

class CheckUserRole
{
    /**
     * @var RoleChecker
     */
    protected $roleChecker;

    public function __construct(RoleChecker $roleChecker)
    {
        $this->roleChecker = $roleChecker;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::guard()->user();

        if (!$this->roleChecker->check($user, $role)) {
            return response()->json(["message" => "You are not authorised to use this endpoint.", 403]);
        }

        return $next($request);
    }
}
