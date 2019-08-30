<?php
namespace MgpLabs\SmartAdmin\Models;

use Illuminate\Database\Eloquent\Model;

class TranslationTranslation extends SmartAdminBaseTranslationModel
{
    public $timestamps = false;

    protected $fillable = ['text'];

    public $rules = [
        'text' => 'required'
    ];
}
