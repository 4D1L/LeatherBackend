<?php

namespace App;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'email_verified_at'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'roles' => 'array',
    ];

    /**
     * Add a role to a user.
     * 
     * @param string $role
     * @return $this
     */
    public function addRole(string $role)
    {
        $roles = $this->getRoles();
        $roles[] = $role;
        
        $roles = array_unique($roles);
        $this->setRoles($roles);

        return $this;
    }

    /**
     * Removes a role to a user.
     * 
     * @param string $role
     * @return $this
     */
    public function removeRole(string $role)
    {
        $roles = $this->getRoles();

        if (($key = array_search($role, $roles)) !== false) {
            unset($roles[$key]);
        }
        
        $roles = array_unique($roles);
        $this->setRoles($roles);

        return $this;
    }

    /**
     * Sets/overwrites a user's roles.
     * 
     * @param array $roles
     * @return $this
     */
    public function setRoles(array $roles)
    {
        $this->setAttribute('roles', $roles);
        return $this;
    }

    /**
     * Checks if a user has a specified roles.
     * 
     * @param $role
     * @return mixed
     */
    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
    }

    /**
     * Checks if a user has any of the supplied roles.
     * 
     * @param array $roles
     * @return mixed
     */
    public function hasRoles($roles)
    {
        $currentRoles = $this->getRoles();
        foreach($roles as $role) {
            if (!in_array($role, $currentRoles)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Gets a user's roles.
     * 
     * @return array
     */
    public function getRoles()
    {
        $roles = $this->getAttribute('roles');

        if (is_null($roles)) {
            $roles = [];
        }

        return $roles;
    }

    /*
    ** Support Ticket Relationship
    */

    /**
     * Get the support queries created by the user.
     */
    public function supportTickets()
    {
        return $this->hasMany('App\SupportTicket');
    }

    /**
     * Get the support queries created by the user.
     */
    public function supportTicketMessages()
    {
        return $this->hasMany('App\SupportTicketMessages', 'user_id');
    }

    /*
    **  Following code pulled from documentation:
    **      https://jwt-auth.readthedocs.io/en/develop/quick-start/#update-your-user-model
    */

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
