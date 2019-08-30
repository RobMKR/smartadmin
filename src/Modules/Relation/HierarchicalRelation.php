<?php

namespace MgpLabs\SmartAdmin\Modules\Relation;

use MgpLabs\SmartAdmin\Models\SmartAdminBaseModel as BaseModel;
use MgpLabs\SmartAdmin\Services\ArrayService;
use Illuminate\Database\Eloquent\Builder;

trait HierarchicalRelation
{
    /**
     * Relations Collection
     *
     * @var
     */
    protected $relation_collection;

    /**
     * Relations Collection in Dotted Syntax
     * To Work with Builder->with() function
     *
     * @var
     */
    protected $relation_collection_dotted;

    /**
     * Create Model From given Model name
     *
     * @param $_model_name
     * @return mixed
     */
    protected function createModel($_model_name){
        // Model Namespace taken form Base Model const
        $new_model_name = BaseModel::__MODEL_NAMESPACE__ . $_model_name;
        return new $new_model_name();
    }

    /**
     * Collect Whole Model Relations of any depth
     *
     * @param $Model
     * @param $relation_collection
     * @return mixed
     */
    protected function collectRelations(BaseModel $Model, array $relation_collection){
        $relations = $Model->getRelsFull();

        foreach ($relations as $_rel_name => $_model_name){
            $NewModel = $this->createModel($_model_name);
            $relation_collection[$_rel_name] =  $this->collectRelations($NewModel, []);
        }
        return $relation_collection;
    }

    /**
     * Add Whole relations to Builder and Return
     *
     * @param BaseModel $Model [Instance of Base Model]
     * @return Builder
     */
    public function addHierarchicalRelations(BaseModel $Model){
        $this->relation_collection = ArrayService::arrayKeysToDots(
            $this->relation_collection_dotted, /* Passed by reference */
            $this->collectRelations($Model, []) /* Collect model relations recursive */
        );

        return call_user_func_array([$Model, 'with'], $this->relation_collection_dotted);
    }
}