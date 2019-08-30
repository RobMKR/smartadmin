<?php
namespace MgpLabs\SmartAdmin\Models;

class ExportField extends SmartAdminBaseModel
{
    protected $fillable = [
        'export_id',
        'field',
        'position'
    ];

    protected $rules = [
        'export_id' => 'required|exists:exports,id',
        'field' => 'required',
        'position' => 'numeric'
    ];

    protected $rels = [
        'export_id' => [
            'model' => 'Export'
        ]
    ];

    public function export(){
        return parent::belongsTo($this->modelName('Export'));
    }
}
