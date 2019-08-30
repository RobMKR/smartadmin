<?php

/**
 * !!! WARNING !!!
 *
 * This trait is a part of Main Controller, and cannot work without it
 *
 * In Future we'll make it to work separate
 */

namespace MgpLabs\SmartAdmin\Modules\Exportable;

use MgpLabs\SmartAdmin\Models\Export;
use Excel;
use MgpLabs\SmartAdmin\Services\ApiResponseService;

trait Exportable
{
    protected $allowed_types = [
        'xlsx', 'xls'
    ];

    /**
     * exports table model instance
     *
     * @var
     */
    protected $Export;

    /**
     * Export Data Array
     *
     * @var
     */
    protected $export_array;

    /**
     * Max or default limit of exportable
     *
     * @var int
     */
    protected $limit;

    /**
     * Fields needed to be in export data
     *
     * @var array
     */
    protected $fields = [];

    /**
     * File Type
     * {csv, xls}
     *
     * @var
     */
    protected $file_type;

    /**
     * Exportable model info holder
     *
     * @var array
     */
    protected $exportable;

    /**
     * Array ready to make file
     *
     * @var
     */
    protected $normalized;

    /**
     * Get Export Data as array
     *
     * @throws \Exception
     * @return array
     */
    protected function getExportData(){
        if(empty($this->fields)){
            ApiResponseService::forceJson('Fields Not Defined For this model.');
        }

        if(! in_array($this->model_table . '.id', $this->fields))
            array_unshift($this->fields, $this->model_table . '.id as ID');

        $this->model = $this->model->hasTranslatedAttributes() ? $this->prepareTranslatedPagination() : $this->prepareDefaultPagination();

        call_user_func([$this->model, 'select'], $this->fields);

        return $this->model->limit($this->limit)->get()->toArray();
    }

    /**
     * Add prefixes to table
     *
     * @param $field
     * @return string
     */
    protected function addPrefixes($field){
        return in_array($field['field'], $this->translated_attributes) ? 't.' . $field['field'] : $this->model_table . '.' . $field['field'] ;
    }

    /**
     * Initialize Exportable
     *
     * @param $data
     * @return $this
     */
    protected function init($data){
        $this->Export = new Export();

        $this->exportable = $this->Export->getAvailableFields($this->getModelName(false));

        $this->fields = array_map(
            [$this, 'addPrefixes'],
            $this->exportable['fields']
        );

        $this->file_type = $this->getFileType(data_get($data, 'export_type'));

        $this->limit = (isset($data['limit']) && $data['limit'] < $this->limit) ? $data['limit'] : 200;

        return $this;
    }

    /**
     * Create File name for file
     *
     * @return string
     */
    protected function getFileName(){
        return $this->exportable['model']['alias']
            ? $this->exportable['model']['alias']  . '_export_' . date("d_m_Y")
            : $this->model_name . '_export_' . date("d_m_Y");
    }

    protected function getFileType($file_type){
        if(in_array($file_type, $this->allowed_types))
            return $file_type;


        return 'csv';
    }

    /**
     * Normalize export data
     *
     * @return self
     */
    protected function normalize(){
        foreach ($this->export_array as $_key => &$_value){
            if(isset($_value['translations']))
                unset($this->export_array[$_key]['translations']);
        }

        return $this;
    }

    /**
     * Make File
     *
     * @return mixed
     */
    protected function make(){
        $this->normalize();

        return Excel::create($this->getFileName(), function($excel){
            $excel->sheet('main', function($sheet)
            {
                $sheet->fromArray($this->export_array, null, 'A1', true);
            });
        })->download($this->file_type);
    }

    /**
     * Exportable Handler
     *
     * @param $data
     * @return \Maatwebsite\Excel\Excel
     */
    protected function getExport($data){

        $this->export_array = $this->init($data)->getExportData();

        return $this->make();
    }
}