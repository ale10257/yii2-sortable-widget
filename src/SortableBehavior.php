<?php

namespace ale10257\sortable;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;

class SortableBehavior extends Behavior
{
    public string $sortField = 'sort';
    public int $step = 10;
    public bool $addToBeginning = false;
    public string $serviceClass = SortableService::class;

    /** @var ActiveRecord */
    public $owner;

    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterDelete()
    {
        ($this->getService())->delete();
    }

    /**
     * @throws \yii\db\Exception
     */
    public function afterInsert()
    {
        $service = $this->getService();
        if (!$this->addToBeginning) {
            $service->addToEnd();
        } else {
            $service->addToBeginning();
        }
    }

    public function getService()
    {
        /** @var SortableService|SortableServicePostgres $service */
        return ServiceFactory::getServiceFromBehavior($this);
    }
}