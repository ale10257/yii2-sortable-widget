<?php

namespace ale10257\sortable;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;

class ServiceFactory
{
    /**
     * @throws InvalidConfigException
     */
    public static function getServiceFromModel(ActiveRecord $model)
    {
        foreach ($model->behaviors() as $behaviorArr) {
            if ($behaviorArr['class'] === SortableBehavior::class) {
                /** @var SortableBehavior $behavior */
                $behavior = Yii::createObject($behaviorArr);
                return self::getServiceFromBehavior($behavior, $model);
            }
        }
        throw new \InvalidArgumentException('Undefined SortableBehavior');
    }

    public static function getServiceFromBehavior(SortableBehavior $behavior, ?ActiveRecord $model = null)
    {
        $service = $behavior->serviceClass;
        /** @var SortableService|SortableServicePostgres $service */
        $service = new $service($behavior->owner ?? $model);
        $service->sortField = $behavior->sortField;
        $service->step = $behavior->step;
        return $service;
    }
}