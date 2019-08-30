<?php
namespace MgpLabs\SmartAdmin\Models;

use Illuminate\Database\Eloquent\Model;

class RoleTranslation extends SmartAdminBaseTranslationModel
{
    public $timestamps = false;

    protected $fillable = ['alias'];

    public $rules = [
        'alias' => 'required|translatable:role_translations,locale',
    ];
}
