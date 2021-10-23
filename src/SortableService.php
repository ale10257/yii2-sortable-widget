<?php

namespace ale10257\sortable;

use InvalidArgumentException;
use yii\base\BaseObject;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\Expression;
use yii\db\Query;
use Yii;

class SortableService extends BaseObject
{
    public string $sortField = 'sort';
    /** ID записи после которой должна встать модель */
    public ?int $previous_id = null;
    /**
     * @var array|string
     */
    public $condition;

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
            if ($this->previous_id == 0) {
                $this->newSortValue = 1;
            } else {
                $prev = $this->model::find()
                    ->where($this->condition)
                    ->andWhere(['id' => $this->previous_id])
                    ->select($this->sortField)
                    ->scalar();
                if (!$prev) {
                    throw new InvalidArgumentException("Record with id = $this->previous_id not found");
                }
                $this->newSortValue = ++$prev;
            }
        }
        $this->model->{$this->sortField} = $this->newSortValue;
        $this->model->save();
        $this->updateSort();
        $this->model->refresh();
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
        $condition = $this->model instanceof ISortableModel ? $this->model->sortableCondition() : $this->condition;
        $select = (new Query())
            ->from($this->model::tableName())
            ->select(new Expression("id, rank() OVER (ORDER BY $this->sortField, id) * 10 AS newsort"))
            ->where($condition)
            ->createCommand()
            ->getRawSql();
        /** @noinspection SqlInsertValues */
        $sql = "
            INSERT INTO {$this->model::tableName()} (id, $this->sortField)
            $select
            ON CONFLICT (id)
                DO UPDATE
                SET $this->sortField = EXCLUDED.$this->sortField;
        ";
        Yii::$app->db->createCommand($sql)->execute();
    }
}