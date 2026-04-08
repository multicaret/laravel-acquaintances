<?php

namespace Multicaret\Acquaintances\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class VerificationGroups
 */
class VerificationGroups extends Model
{
    /**
     * @var array
     */
    protected $fillable = ['verification_id', 'group_id', 'verifier_id', 'verifier_type'];

    /**
     * @var bool
     */
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        $this->table = config('acquaintances.tables.verification_groups');

        parent::__construct($attributes);
    }
}
