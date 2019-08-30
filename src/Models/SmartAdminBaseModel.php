<?php
namespace MgpLabs\SmartAdmin\Models;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\MessageBag;
use MgpLabs\SmartAdmin\Modules\Cache\CachedModel;
use MgpLabs\SmartAdmin\Modules\Relation\HierarchicalRelation;
use MgpLabs\SmartAdmin\Patterns\ModelFactory;
use MgpLabs\SmartAdmin\Services\JWTService;
use MgpLabs\SmartAdmin\Services\StringService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\App;
use JWTAuth;
use Auth;

class SmartAdminBaseModel extends CachedModel
{
    use SoftDeletes, HierarchicalRelation;

    /**
     * Models Namespace
     */
    const __MODEL_NAMESPACE__ = 'App\\Models\\';

    /**
     * Vendor Model Namespace
     */
    const __VENDOR_MODEL_NAMESPACE__ = 'MgpLabs\\SmartAdmin\\Models\\';

    /**
     * Model Fields [with rules]
     *
     * @var array
     */
    protected $fields = [];

    /**
     * Rich Text Format Fields
     *
     * @var array
     */
    protected $rtf = [];

    /**
     * Table has revisions or not
     *
     * @var bool
     */
    protected $revisionable = false;

    /**
     * Field under revisionable
     *
     * @var
     */
    protected $revision_field;

    /**
     * Model Caching
     *
     * @var bool
     */
    protected $cache = true;

    /**
     * CRUD options
     * Available Values ['c' => bool, 'r' => bool, 'u' => bool, 'd' => bool]
     * 'c' => Create | 'r' => Read | 'u' => Update | 'd' => Delete
     *
     * @var array
     */
    protected $crudable = [];

    /**
     * Relation Models
     *
     * @var array
     */
    protected $rels = [];

    /**
     * Model Relation is Hierarchical
     *
     * @var bool
     */
    protected $hierarchical = false;

    /**
     * Model Default Withs
     *
     * @var array
     */
    protected $withs = [];

    /**
     * Many To Many Relationship connections [only defined in Rel tables]
     *
     * @var array
     */
    protected $destinations = [];

    /**
     * Many to Many Relationships
     *
     * @var array
     */
    protected $multiselect = [];

    /**
     * One To Many Relationships
     *
     * @var array
     */
    protected $one_to_many = [];

    /**
     * Type [DEFAULT, CUSTOM]
     * By Default All models extends DEFAULT type from Base,
     * To Change Model Type, just assign $type property in model with [CUSTOM] value
     *
     * @var string
     */
    protected $type = 'DEFAULT';

    /**
     * Fillable inputs
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * NonFilterable inputs
     *
     * @var array
     */
    protected $non_filterable = [];

    /**
     * Attributes that cannot be edited
     *
     * @var array
     */
    protected $non_editable = [];

    /**
     * Validation Rules
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Attributes that must be invisible
     *
     * @var array
     */
    protected $invisible = [];

    /**
     * Attributes need to be bcrypted before save
     *
     * @var array
     */
    protected $hashable = [];

    /**
     * Translatable rules
     *
     * @var array
     */
    protected $translatable_rules = [];

    /**
     * Translated Attributes
     *
     * @var array
     */
    protected $translatedAttributes = [];

    /**
     * Relation Names
     *
     * @var array
     */
    protected $rel_names = [];

    /**
     * Deleted at column
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * Add Models Namespace to given model name and return
     *
     * @param $model_name
     * @return string
     */
    protected function modelName($model_name){
        if(array_key_exists(snake_case($model_name), config('smartadmin.vendormodels'))){
            return self::__VENDOR_MODEL_NAMESPACE__ . $model_name;
        }

        return self::__MODEL_NAMESPACE__ . $model_name;
    }

	/**
     * Check if locale must be lowercase, than return in lower case,
     * else return in upper case
     *
     * @param $locale
     * @return string
     */
    protected function resolveLangChars($locale){
        if(config('smartadmin.locale_is_lower'))
            return strtolower($locale);

        return strtoupper($locale);
    }

    /**
     * Getting Current Model Name without namespace
     *
     * @return string
     */
    protected function getModelName(){

        if(StringService::existInString(self::__VENDOR_MODEL_NAMESPACE__, static::class)) {
            return snake_case(StringService::cutFromString(self::__VENDOR_MODEL_NAMESPACE__, static::class));
        }

        return snake_case(StringService::cutFromString(self::__MODEL_NAMESPACE__, static::class));
    }

    /**
     * Get Relational model and fields
     *
     * @param $rel_data
     * @return array
     */
    protected function getRelationalData($rel_data){
        $return['model'] = snake_case($rel_data['model']);
        $return['fields'] = isset($rel_data['fields']) ? $rel_data['fields'] : ['name'];
        $return['depends_on'] = isset($rel_data['depends_on']) ? $rel_data['depends_on'] : null;
        return $return;
    }

    /**
     * Get Field Enum items
     *
     * @param $value
     * @return array
     */
    protected function getEnumData($value){
        $enum_data = [];

        if(!isset($this->rules[$value])) {
            return $enum_data;
        }

        $_rules = explode('|', $this->rules[$value]);
        foreach($_rules as $_rule){
            if(strpos($_rule, 'in:') === 0){
                $enum_data[$value] = explode(',', explode('in:', $_rule)[1]);
            }
        }
        return $enum_data;
    }

    /**
     * Check fields is nonEditable or not
     *
     * @param $value
     * @return bool
     */
    protected function fieldNonEditable($value){
        return array_search($value, $this->non_editable) !== false;
    }

    /**
     * Check Model is Cached or not
     *
     * @return bool
     */
    protected function isCached(){
        return $this->cache;
    }

    /**
     * Get Only given fields rules
     *
     * @param $fields
     * @return array
     */
    protected function getRulesFromFields($fields){
        $rules = [];

        foreach ($fields as $_field => $_value){
            if(isset($this->rules[$_field])){
                $rules[$_field] = $this->rules[$_field];
            }
        }

        return $rules;
    }

    /**
     * Add Id to Role Options, if needed, else return null
     *
     * @param $rule
     * @param $id
     * @return null|string
     */
    protected function addIdToRuleOptions($rule, $id){
        if(is_array($rule)){
            $options = $rule;
        }else{
            $options = explode('|', $rule);
        }

        $changed = false;

        foreach ($options as $k => $_option) {
            if(StringService::existInString('unique', $_option) || StringService::existInString('unique_with', $_option)){
                $options[$k] = $_option . ',' . $id;
                $changed = true;
            }
        }

        return $changed ? implode('|', $options) : null;
    }

    /**
     * Add Id To entity rules
     *
     * @param $id
     * @return void
     */
    protected function addIdToRules($id){
        foreach($this->rules as $key => $_rule){
            // Get New rule or null, if rule haven't id-able parts
            $new_rule = $this->addIdToRuleOptions($_rule, $id);

            if($new_rule){
                // Save new rule
                $this->rules[$key] = $new_rule;
            }
        }
    }
    /**
     * Get Many To Many Relationship Destination Relation, giving foreign key of first table
     *
     * @param $key
     * @return array
     */
    protected function getDestinationRel($key){
        $return = [];

        $return['model'] = $this->destinations[$key]['model'];
        $return['relKey '] = $this->destinations[$key]['key'];
        $return['fields'] = isset($this->destinations[$key]['fields']) ? $this->destinations[$key]['fields'] : ['name'];

        return $return;
    }


    /**
     * Getting Relation Names from model $rels array using 'name' key
     *
     * @return void
     */
    protected function getRelNames(){
        foreach ($this->rels as $_rel_key =>  $_rel){
            // Set rel name $_rel[name]
            // If not set $_rel[name] cut "_id" from key and use it as relation name
            $this->rel_names[] = isset($_rel['name']) ? $_rel['name'] : str_replace("_id", "", $_rel_key);
        }
    }

    /**
     * Check field is filterable or not
     *
     * @param $value
     * @return bool
     */
    public function isFilterable($value){
        if(in_array($value, $this->non_filterable) || in_array($value, $this->hidden))
            return false;
        return true;
    }

    /**
     * Get Model Fillable Fields
     *
     * @return array
     */
    protected function getFillableFields(){
        DB::connection()->getDoctrineSchemaManager()->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'integer');

        foreach($this->fillable as $_k => $_value){
            // Check if value is for fixing fillable bug [see app/Models/Role.php]
            if($_value === sha1($this->getTable() . $_REQUEST['mt'])){
                continue;
            }

            if(isset($this->rels[$_value])){
                $this->fields[$_k]['type'] = 'select';
                $this->fields[$_k]['data'] = [];
                $this->fields[$_k]['relation'] = $this->getRelationalData($this->rels[$_value]);
            }elseif(in_array($_value, $this->rtf)){
                $this->fields[$_k]['type'] = 'rtf';
                $this->fields[$_k]['data'] = [];
            }else{
                $this->fields[$_k]['type'] = in_array($_value, $this->hidden) ? 'password' : 'input' ;
                $this->fields[$_k]['data'] = [];
            }

            $this->fields[$_k]['mt'] = DB::connection()->getDoctrineColumn($this->getTable(), $_value)->getType()->getName();
            $this->fields[$_k]['name'] = $_value;
            $this->fields[$_k]['rules'] = isset($this->rules[$_value]) ? $this->rules[$_value] : '' ;
            $this->fields[$_k]['enumData'] = $this->getEnumData($_value);
            $this->fields[$_k]['filterable'] = $this->isFilterable($_value);
            $this->fields[$_k]['col'] = in_array($_value, $this->invisible) ? false : true;
            if($this->fieldNonEditable($_value)){
                $this->fields[$_k]['editable'] = false;
            }
        }

        return $this->fields;
    }

    /**
     * Checks Model has many to many relations or not
     *
     * @return bool
     */
    protected function hasManyToManyRel(){
        return !empty($this->multiselect);
    }

    /**
     * Check Model has one to many rels or no
     *
     * @return bool
     */
    protected function hasOneToManyRel(){
        return !empty($this->one_to_many);
    }

    /**
     * Check Model is hierarchical relationable or not
     * property $hierarchical
     *
     * @uses $this->hierarchical
     * @return bool
     */
    protected function isHierarchical(){
        return $this->hierarchical;
    }

    /**
     * Get Many to Many Relation from given $multiselect value [for example: "user_role as user_id"]
     * Where "user_role" is a relational table name, and user_id is a fk
     *
     * @param $rel
     * @return array
     */
    protected function getManyToManyRel($rel){
        $model_name = $this->modelName(StringService::toCamelCase($rel['model']));
        $Model = new $model_name();

        $return['type'] = 'multiselect';
        $return['filterable'] = false;

        $return['relation'] = [
            'from' => [
                'model' => $this->getModelName(),
                'relKey' => $rel['as']
            ],
            'to' => $Model->getDestinationRel($rel['as']),
            'model' => $rel['model'],
            'relation_name' => $rel['rel_name']
        ];

        return $return;
    }

    /**
     * Get Multiselect Rules
     *
     * @param $field
     * @return array
     */
    protected function getManyToManyRules($field){
        $model_name = $this->modelName(
            StringService::toCamelCase($this->multiselect[$field]['model'])
        );

        $MultiselectModel = new $model_name;

        $rule = $MultiselectModel->getRules()[$this->multiselect[$field]['fk']];

        return [
            'field' => $field . '.*',
            'value' => $rule
        ];
    }

    /**
     * Getting Many to Many Relationships of Model
     *
     * @return array
     */
    protected function getManyToManyRels(){
        $rels = [];

        foreach ($this->multiselect as $_rel){
            $rels[] = $this->getManyToManyRel($_rel);
        }
        return $rels;
    }

    /**
     * Add Many To Many Rules to validation rules
     *
     * @param $rules
     * @param $data
     */
    protected function addManyToManyRules(&$rules, $data){
        foreach($data as $_field => $_value){
            foreach ($this->multiselect as $_rel_key => $_rel){
                if($_rel['rel_name'] === $_field){
                    $rule = $this->getManyToManyRules($_rel_key);
                    $rules[$rule['field']] = $rule['value'];
                }
            }
        }
    }

    /**
     * Get relation data for given model
     *
     * @param $rel [model_name]
     * @return mixed
     */
    protected function getOneToManyRel($rel){
        $return['type'] = 'one_to_many';
        $return['filterable'] = false;
        $return['field'] = isset($this->relation_column) ? $this->relation_column : str_singular($this->getTable()) . '_id';
        $return['value'] = $rel;
        $return['model'] = StringService::toCamelCase($rel);

        return $return;
    }

    /**
     * Get All One To Many rels for current model
     *
     * @return array
     */
    protected function getOneToManyRels(){
        $fields = [];

        foreach ($this->one_to_many as $_rel){
            $fields[] = $this->getOneToManyRel($_rel);
        }
        return $fields;
    }

    /**
     * Get translatable model translation relation name
     *
     * @return mixed|null
     */
    protected function getTranslatableRelationName(){
        return isset($this->relation_column) ? $this->relation_column : null;
    }

    /**
     * Get Rule for translatable field
     *
     * @param $field
     * @return array
     */
    protected function getTranslatableRule($field){
        $trans_model_name = $this->getTranslationModelName();
        $trans_model = new $trans_model_name();


        if(!isset($trans_model->rules[$field])){
            return [];
        }

        foreach(Lang::getLocales(1) as $_locale){
            $_rules = explode('|', $trans_model->rules[$field]);

            foreach($_rules as &$_rule){
                if(StringService::existInString('translatable', $_rule)){
                    $_rule = 'unique_translation';
                }
            }

            $rules[$field . ':' . $_locale] = implode('|', $_rules);
        }

        return isset($rules) ? $rules : [];
    }

    /**
     * Get Model Translatable fields
     *
     * @return array
     */
    protected function getTranslatableFields(){
        $available_locales = Lang::getLocales(1);

        foreach($this->translatedAttributes as $_k => $_attribute){
            $translatable_fields[$_k]['type'] = 'translatable';
            $translatable_fields[$_k]['data'] = [];
            $translatable_fields[$_k]['name'] = $_attribute;
            $translatable_fields[$_k]['locales'] = $available_locales;
            $translatable_fields[$_k]['rules'] = $this->getTranslatableRule($_attribute);
            $translatable_fields[$_k]['filterable'] = true;
            $translatable_fields[$_k]['col'] =  in_array($_attribute, $this->invisible) ? false : true;;
        }

        return isset($translatable_fields) ? $translatable_fields : [] ;
    }

    /**
     * Getting Relation Names, to use in Eloquent with() method as func args
     *
     * @return array
     */
    public function getRels(){
        $this->getRelNames();
        return $this->rel_names;
    }

    /**
     * Check Model has Default Relations or not
     *
     * @return bool
     */
    public function hasWiths(){
        if(!empty($this->withs) || $this->isHierarchical())
            return true;

        return false;
    }

    /**
     * Check Model Has given relation or not
     *
     * @param $relation_name
     * @return bool
     */
    public function hasRelation($relation_name){
        return method_exists($this, $relation_name);
    }

    /**
     * Get Model Default Withs
     *
     * @return array|mixed
     */
    public function getWiths(){
        if(!empty($this->withs))
            return $this->withs;

        if($this->isHierarchical())
            return $this->addHierarchicalRelations($this);

        return [];
    }

    /**
     * Get Full Rels array
     *
     * @return array
     */
    public function getRelsFull(){
        foreach ($this->rels as $_rel_key =>  $_rel){
            // Set rel name $_rel[name]
            // If not set $_rel[name] cut "_id" from key and use it as relation name
            $name = isset($_rel['name']) ? $_rel['name'] : str_replace("_id", "", $_rel_key);
            $model = $_rel['model'];

            $rels[$name] = $model;
        }

        return isset($rels) ? $rels : [];
    }

    /**
     * Get Model Rules
     *
     * @return array
     */
    public function getRules(){
        return $this->rules;
    }

    /**
     * Check model has translated attributes or not
     *
     * @return bool
     */
    public function hasTranslatedAttributes(){
        return !empty($this->translatedAttributes);
    }

    /**
     * Get concat(translate model alias . Translated attributes)
     *
     * @param $alias
     * @return array
     */
    public function getTranslatedAttributes($alias){
        $attributes = [];

        foreach($this->translatedAttributes as $_attribute){
            $attributes[] = $alias . '.' . $_attribute;
        }

        return $attributes;
    }

    /**
     * Before Inserting to models add Created_by, Updated_by, Created_at, Updated_at
     *
     * @param $data
     * @param $only_update
     * @return self
     */
    protected function addStamps(&$data, $only_update = false){
        $now = Carbon::now(config('app.timezone'))->toDateTimeString();

        if($only_update){
            $data['updated_by'] = JWTService::getUserId();
            $data['updated_at'] = $now;
        }else{
            $data['created_by'] = JWTService::getUserId();
            $data['updated_by'] = JWTService::getUserId();
            $data['created_at'] = $now;
            $data['updated_at'] = $now;
        }

        return $this;
    }

    /**
     * Separate data to trans and main
     *
     * @param $data
     * @return array
     */
    public function separateData($data){
        $new_data = [
            'translatable' => [],
            'main' => [],
            'multiselect' => [],
            'files' => []
        ];

        // Set Data to empty array, if something else is passed
        $data = (!is_array($data))? [] : $data ;

        foreach($data as $_field => $_value){

            if(StringService::existInString(':', $_field)){

                // Case When field is localized
                list($field['name'], $field['locale']) = explode(':', $_field);

                if(in_array($field['name'], $this->translatedAttributes) && in_array($this->resolveLangChars($field['locale']), Lang::getLocales())){
                    $new_data['translatable'][$_field] = $_value;

                    foreach(Lang::getLocales() as $_locale){
                        $key = $field['name'] . ':' . Str::lower($_locale);
                        if(!isset($data[$key])){
                            $new_data['translatable'][$key] = $_value;
                        }
                    }
                }

            }elseif(isset($this->multiselect[$_field])){

                $new_data['multiselect'][$this->multiselect[$_field]['rel_name']] = [
                    'pivot' => $_value,
                    'extra' => [
                        'created_by' => JWTService::getUserId(),
                        'updated_by' => JWTService::getUserId()
                    ]
                ];

                // Add to main data just for validation
                $new_data['main'][$_field] = $_value;

            }else{
                // Case When field is non localized

                if($_value instanceof UploadedFile){
                    // Case field is an uploaded file
                    $new_data['files'][$_field] = $_value;
                }else{
                    $new_data['main'][$_field] = $_value;
                }
            }
        }

        return $new_data;
    }

    /**
     * Merge given arrays
     * implemented
     *
     * @param $array1
     * @param $array2
     * @return array
     */
    protected function merge($array1, $array2){
        return array_merge($array1, $array2);
    }

    /**
     * Add Updated string to translatable rule
     *
     * @param $_rule
     * @param $update_str
     * @return string
     */
    protected function addUpdateStringToRule($_rule, $update_str){
        if(!$update_str){
            return $_rule;
        }

        $current_rules = explode('|', $_rule);

        foreach($current_rules as &$_one_rule){
            if(StringService::existInString('translatable', $_one_rule)){
                $_one_rule = $_rule . $update_str;
            }
        }
        return implode('|', $current_rules);
    }

    /**
     * Add Translation rules to model rules
     *
     * @param $update_id | null
     * @return self
     */
    public function setTranslatableRules($update_id = null){
        if(!$this->hasTranslatedAttributes()){
            return $this;
        }

        // If Update id passed add to rules ,id,relation e.g. ['name:ru' => translatable:table_name,locale,relation,id]
        $update_str =  $update_id ?  ',' . $this->getTranslatableRelationName() . ',' .  $update_id : '' ;
        $trans_model_name = $this->getTranslationModelName();
        $trans_model = new $trans_model_name();

        foreach(Lang::getLocales() as $_locale){
            foreach($trans_model->rules as $_rule_name => $_rule){
                $_rule = $this->addUpdateStringToRule($_rule, $update_str);
                $this->translatable_rules[$_rule_name . ':' . strtolower($_locale)] = $_rule;
            }
        }

        return $this;
    }

    /**
     * Get Model CRUD-able options
     * By Default return all options as true
     *
     * @return array
     */
    public function getCrudOptions(){
        $available_options = [ 'c' => true, 'r' => true, 'u' => true, 'd' => true];

        if(!empty($this->crudable)){
            foreach($this->crudable as $_option => $_value){
                $available_options[$_option] = boolval($_value);
            }
        }

        return $available_options;
    }

    /**
     * Get Model Rules (Fields)
     * Getting all model fields with rules
     *
     * @return array
     */
    public function getFields(){
        if(!config('app.debug') && Cache::has($this->getTable())){
            $this->fields = Cache::get($this->getTable());
            return $this->fields;
        }

        $this->fields = $this->getFillableFields();

        if($this->hasManyToManyRel()){
            $this->fields = $this->merge($this->fields, $this->getManyToManyRels());
        }

        if($this->hasOneToManyRel()){
            $this->fields = $this->merge($this->fields, $this->getOneToManyRels());
        }

        if($this->hasTranslatedAttributes()){
            $this->fields = $this->merge($this->getTranslatableFields(), $this->fields);
        }

        Cache::forever($this->getTable(), $this->fields);
        return $this->fields;
    }

    public function getNextRevisionNumber($field, $value){
        $table = $this->getTable();

        $last_revision = $this->join(
            DB::raw("(SELECT max(revision) last_rev, name FROM $table GROUP BY name) tmp"),
            function($join) use ($field, $table, $value){
                $join->on("tmp.$field", '=', "$table.$field");
                $join->on( "$table.revision", '=', 'tmp.last_rev');
                $join->where("tmp.$field", $value);
            }
        )->first();

        return $last_revision ? $last_revision->revision + 1 : 0;
    }

    /**
     * Add Revisions to Builder instance
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param $field
     * @return Builder
     */
    public function addRevisions(Builder $builder, $field = null){
        if(! $this->revisionable)
            return $builder;

        $table = $this->getTable();

        if(! $field && $this->revision_field)
            $field = $this->revision_field;

        return $builder
            ->join(
                DB::raw("(SELECT max(revision) last_rev, name FROM $table GROUP BY name) tmp"),
                function($join) use ($field, $table){
                    $join->on("tmp.$field", '=', "$table.$field");
                    $join->on( "$table.revision", '=', 'tmp.last_rev');
                }
            );
    }

    /**
     * Validate File can be removed or not
     *
     * @param $errors
     * @param $Entity
     * @param $file_name
     * @return array|self
     */
    protected function validateFileRemove(&$errors, $Entity, $file_name){
        if(! isset($Entity->{$file_name}))
            return [];

        if(in_array('required', explode('|', $this->rules[$file_name]))){
            $errors[$file_name] = [
                __('validation.file_remove', ['attribute' => $file_name])
            ];
        }

        return $this;
    }

    /**
     * Validate Model Files can be removed or not
     *
     * @param $errors
     * @param $Entity
     * @param $remove_files
     * @return MessageBag
     */
    public function validateFilesRemove(&$errors, $Entity, $remove_files){

        if(empty($remove_files)){
            return $errors;
        }

        foreach($remove_files as $_file_name => $_delete){
            // Checking for 'true' because
            // This is from request, where
            // Delete parameters is string
            // We don't want to change type of string to bool
            // so check it with string: 'true'
            if($_delete === 'true'){
                $this->validateFileRemove($errors, $Entity, $_file_name);
            }
        }

        return $errors;
    }

    /**
     * Validating Unique Logic, Return array of errors, or empty array, if no errors
     * $update_id need to pass only on updates
     *
     * @param $data
     * @param $update_id
     * @param $fields
     * @return array
     */
    public function validate($data = [], $update_id = null, $fields = null){
        // Need on updates, when unique fields need an id to ignore itself
        $this->addIdToRules($update_id);

        // Separate translatable and main data fields
        $data = $this->separateData($data);

        // Get field rules, if fields passed
        $rules = $fields ? $this->getRulesFromFields($fields) : $this->rules;

        // $rules passed as Reference
        $this->addManyToManyRules($rules, $data['multiselect']);

        $errors = [];

        // Validating Main Rules
        $main_v = Validator::make(array_merge($data['main'], $data['files']), $rules);
        if($main_v->fails()){
            $errors = $main_v->errors()->toArray();
        }

        // Validation Translatable Rules
        $trans_v = Validator::make($data['translatable'], $this->translatable_rules);
        if($trans_v->fails()){
            foreach($trans_v->errors()->toArray() as $_k => $_error){
                $field = explode(':', $_k)[0];
                $errors[$field][] = $_error;
            }
        }

        return $errors;
    }

    /**
     * Fill created_by and updated_by fields using Magic Function beforeSave(), that calls before Model save() method
     *
     * @return self
     */
    public function beforeSave(){
        $user_id =  null;

        if (JWTAuth::getToken() && !config('smartadmin.basic_auth')) {
            $user = JWTAuth::parseToken()->toUser();
            if ( $user )
                $user_id = $user['id'];
        }

        if(!$this->id){
            $this->created_by = $user_id;
        }

        $this->updated_by = $user_id;
        return $this;
    }

    /**
     * Change empty date fields to null, to avoid database errors
     *
     * @param $attributes
     * @return $this
     */
    public function resolveNullValues(array &$attributes){
        foreach($attributes as $_field => &$_value){
            // Continue if field not found in rules
            if(!array_key_exists($_field, $this->rules))
                continue;

            // Implode array to string, if rule passed as array
            if(is_array($this->rules[$_field])){
                $this->rules[$_field] = implode('|', $this->rules[$_field]);
            }

            // change empty dates to null
            if(StringService::existInString('date', $this->rules[$_field]) && $_value === '')
                $_value = null;

            // change empty integers to null
            if($_value === '' && StringService::existInString('integer', $this->rules[$_field]))
                $_value = null;

            if($_value === '' && StringService::existInString('numeric', $this->rules[$_field]))
                $_value = null;
        }

        return $this;
    }

    /**
     * Hash Hashable attributes
     *
     * @param $attributes
     * @return $this
     */
    protected function hash(&$attributes){
        $obj = new static;

        foreach($attributes as $_field => &$_attribute){
            if(in_array($_field, $obj->hashable)){
                $_attribute = bcrypt($_attribute);
            }
        }

        return $this;
    }

    /**
     * Remove hidden values from attributes array, if its an empty string
     * For example to avoid empty string in password field
     *
     * @param array $attributes
     * @return $this
     */
    public function removeEmptyHiddens(array &$attributes){
        foreach($attributes as $_field => &$_attribute){

            if($_attribute === '' && in_array($_field, $this->hidden)){
                unset($attributes[$_field]);
            }
        }

        return $this;
    }

    /**
     * Override Model::create function to add hash to hidden fields
     *
     * @param array $attributes
     * @return Model
     */
    public function create(array $attributes = []){
        $obj = new static;

        $obj->resolveNullValues($attributes)->hash($attributes);
        return parent::create($attributes);
    }

    /**
     * Override parent::save() method to implement beforeSave() magic method
     * which calls every time, when base model instances called save method
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options = [])
    {
        $this->resolveNullValues($this->attributes)->beforeSave();
        return parent::save();
    }

    /**
     * Override parent::update() method to call beforeSave() magic method
     *
     * @param array $attributes
     * @param array $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = [])
    {
        $this->resolveNullValues($attributes)
            ->removeEmptyHiddens($attributes)
            ->hash($attributes)
            ->beforeSave()
            ->save();

        return parent::update($attributes, $options);
    }
}