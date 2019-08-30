<?php
namespace MgpLabs\SmartAdmin\Modules\Revision;

use \Venturecraft\Revisionable\RevisionableTrait;
use \Venturecraft\Revisionable\Revision;
use Illuminate\Support\Str;

trait RevisionTrait
{
    use RevisionableTrait;

    /**
     * Checks Revision Model is translatable or not
     *
     * @return bool
     */
    protected function isTranslatable(){
        return property_exists($this, 'translatable') && $this->translatable;
    }

    /**
     * Get Translatable Locale
     *
     * @return mixed
     */
    protected function getLocale(){
        return Str::lower($this->updatedData['locale']);
    }

    /**
     * Get Translatable Table Main ID
     * e.g. if translatable id is 10 and relation id is 20
     * we took 20 instead of 10
     *
     * @return mixed
     */
    protected function getTranslatableId(){
        /* Method called from App\Models\BaseTranslationModel Class */
        return $this->updatedData[$this->getTranslatableRelationName()];
    }

    /**
     * Get Translatable Main Class Name
     *
     * @return mixed
     */
    protected function getTranslatableClass(){
        /* Method called from App\Models\BaseTranslationModel Class */
        return $this->getParentClassName();
    }

    /**
     * Called after a model is successfully saved.
     *
     * @return void
     */
    public function postSave()
    {
        if (isset($this->historyLimit) && $this->revisionHistory()->count() >= $this->historyLimit) {
            $LimitReached = true;
        } else {
            $LimitReached = false;
        }
        if (isset($this->revisionCleanup)){
            $RevisionCleanup=$this->revisionCleanup;
        }else{
            $RevisionCleanup=false;
        }

        // check if the model already exists
        if (((!isset($this->revisionEnabled) || $this->revisionEnabled) && $this->updating) && (!$LimitReached || $RevisionCleanup)) {
            // if it does, it means we're updating

            $changes_to_record = $this->changedRevisionableFields();

            $revisions = array();

            foreach ($changes_to_record as $key => $change) {
                $revision = array(
                    'old_value' => array_get($this->originalData, $key),
                    'new_value' => $this->updatedData[$key],
                    'user_id' => $this->getSystemUserId(),
                    'created_at' => new \DateTime(),
                    'updated_at' => new \DateTime(),
                );

                if($this->isTranslatable()){
                    $revision['key'] = $key . ':' . $this->getLocale();
                    $revision['revisionable_id'] = $this->getTranslatableId();
                    $revision['revisionable_type'] = $this->getTranslatableClass();
                } else {
                    $revision['key'] = $key;
                    $revision['revisionable_id'] = $this->getKey();
                    $revision['revisionable_type'] = $this->getMorphClass();
                }

                $revisions[] = $revision;
            }

            if (count($revisions) > 0) {
                if($LimitReached && $RevisionCleanup){
                    $toDelete = $this->revisionHistory()->orderBy('id','asc')->limit(count($revisions))->get();
                    foreach($toDelete as $delete){
                        $delete->delete();
                    }
                }
                $revision = new Revision;
                \DB::table($revision->getTable())->insert($revisions);
                \Event::fire('revisionable.saved', array('model' => $this, 'revisions' => $revisions));
            }
        }
    }
}