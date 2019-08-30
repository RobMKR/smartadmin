<?php
namespace MgpLabs\SmartAdmin\Models;

class Export extends SmartAdminBaseModel
{
    protected $fillable = [
        'model',
    ];

    protected $rules = [
        'model' => 'required|unique:exports,model',
    ];

    protected $rels = [
        'fields' => [
            'model' => 'ExportField'
        ]
    ];

    public function fields(){
        return parent::hasMany($this->modelName('ExportField'));
    }

    /**
     * Get Export Fields by model name
     *
     * @param $model
     * @return array
     */
    public function getAvailableFields($model){
        $fields = $this->where('model', $model)->leftJoin('export_fields as FIELDS', function ($join){
            $join->on('FIELDS.export_id', '=', 'exports.id');
        })->select(
            'exports.id as id',
            'exports.model as model',
            'exports.alias as model_alias',
            'FIELDS.field as field',
            'FIELDS.alias as field_alias'
        )->orderBy('FIELDS.position')->get();

        $fields_array = [
            'fields' => [],
            'model' => []
        ];

        foreach ($fields as $_k => $_field){
            $fields_array['fields'][$_k]['field'] = $_field->field;
            $fields_array['fields'][$_k]['alias'] = $_field->field_alias;

            if( !isset($fields_array['model']['alias'])){
                $fields_array['model']['alias'] = $_field->model_alias;
            }
        }

        return $fields_array;
    }
}
