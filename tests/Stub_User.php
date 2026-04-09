<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Multicaret\Acquaintances\Traits\CanBeFavorited;
use Multicaret\Acquaintances\Traits\CanBeFollowed;
use Multicaret\Acquaintances\Traits\CanBeLiked;
use Multicaret\Acquaintances\Traits\CanBeRated;
use Multicaret\Acquaintances\Traits\CanBeReported;
use Multicaret\Acquaintances\Traits\CanBeSubscribed;
use Multicaret\Acquaintances\Traits\CanBeViewed;
use Multicaret\Acquaintances\Traits\CanBeVoted;
use Multicaret\Acquaintances\Traits\CanFavorite;
use Multicaret\Acquaintances\Traits\CanFollow;
use Multicaret\Acquaintances\Traits\CanLike;
use Multicaret\Acquaintances\Traits\CanRate;
use Multicaret\Acquaintances\Traits\CanReport;
use Multicaret\Acquaintances\Traits\CanSubscribe;
use Multicaret\Acquaintances\Traits\CanView;
use Multicaret\Acquaintances\Traits\CanVote;
use Multicaret\Acquaintances\Traits\Friendable;
use Multicaret\Acquaintances\Traits\Verifiable;

class User extends Model implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable;
    use Authorizable;
    use CanBeFavorited;
    use CanBeFollowed;
    use CanBeLiked;
    use CanBeRated;
    use CanBeReported;
    use CanBeSubscribed;
    use CanBeViewed;
    use CanBeVoted;
    use CanFavorite;
    use CanFollow;
    use CanLike;
    use CanRate;
    use CanReport;
    use CanResetPassword;
    use CanSubscribe;
    use CanView;
    use CanVote;
    use Friendable;
    use Verifiable;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];
}
