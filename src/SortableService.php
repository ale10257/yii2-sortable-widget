<?php

namespace ale10257\sortable;

use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\QueryBuilder;
use Yii;

class SortableService extends BaseObject
{
    public string $sortField = 'sort';
    /** ID записи ПЕРЕД которой должна встать модель */
    public ?int $position = null;
    public array $condition = [];

    private ?int $newSortValue = null;
    private ActiveRecord $model;

    public function __construct(ActiveRecord $model, $config = [])
    {
        $this->model = $model;
        parent::__construct($config);
    }

    /**
     * @throws Exception
     */
    public function changeSort()
    {
        $this->updateSort();
        if (!$this->newSortValue) {
            if ($this->position == 0) {
                $this->newSortValue = 1;
            } else {
                $this->newSortValue = ($this->model::find()
                        ->where($this->condition)
                        ->andWhere(['id' => $this->position])
                        ->select($this->sortField)
                        ->scalar()) + 1;
            }
        }
        $this->model->{$this->sortField} = $this->newSortValue;
        $this->model->save();
        $this->updateSort();
    }

    /**
     * @throws Exception
     */
    public function addToBeginning()
    {
        $this->newSortValue = 1;
        $this->changeSort();
    }

    /**
     * @throws Exception
     */
    public function addToEnd()
    {
        $max = $this->model::find()->where($this->condition)->max($this->sortField);
        if ($max == $this->model->{$this->sortField}) {
            return;
        }
        $this->newSortValue = $max + 1;
        $this->changeSort();
    }

    /**
     * @throws Exception
     */
    public function updateSort()
    {
        $params = [];
        $where = (new QueryBuilder(Yii::$app->db))->buildWhere($this->condition, $params);
        /** @noinspection SqlInsertValues */
        $sql = "
            INSERT INTO {$this->model::tableName()} (id, $this->sortField)
            SELECT id, rank() OVER (ORDER BY $this->sortField, id) * 10 AS newsort
            FROM {$this->model::tableName()}
            $where
            ON CONFLICT (id)
                DO UPDATE
                SET $this->sortField = EXCLUDED.$this->sortField;
        ";
        Yii::$app->db->createCommand($sql)->execute();
    }
}