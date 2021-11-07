<?php

namespace ale10257\sortable\testModels;

use ale10257\sortable\ISortableModel;
use ale10257\sortable\SortableBehavior;
use ale10257\sortable\SortableServicePostgres;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $sort
 */
class SortModel extends ActiveRecord implements ISortableModel
{
    public static function tableName(): string
    {
        return 'sort';
    }

    public function behaviors(): array
    {
        return [
            [
                'class' => SortableBehavior::class,
                'serviceClass' => SortableServicePostgres::class
            ]
        ];
    }

    public function rules(): array
    {
        return [
            [['id', 'sort', 'parent_id', 'title'], 'safe'],
            [['id'], 'required'],
        ];
    }

    public function sortableCondition(): array
    {
        return ['parent_id' => $this->parent_id];
    }
}