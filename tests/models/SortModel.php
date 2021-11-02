<?php

namespace tests\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $parent_id
 * @property int $sort
 */
class SortModel extends ActiveRecord
{
    public static function tableName(): string
    {
        return 'sort';
    }

    public function rules(): array
    {
        return [
            [['id', 'sort', 'parent_id'], 'safe'],
            [['id'], 'required'],
        ];
    }
}