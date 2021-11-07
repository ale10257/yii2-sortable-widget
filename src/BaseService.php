<?php

namespace ale10257\sortable;

use InvalidArgumentException;
use yii\db\ActiveRecord;

abstract class BaseService
{
    public ?string $sortField = null;
    public int $step;
    /**
     * @var int|string The ID of the record after which the model should appear, if zero, then the model will appear at the beginning of the list
     */
    public $previous_id = null;

    /**
     * @var array|string
     */
    public $condition;
    protected ActiveRecord $model;


    public function __construct(ActiveRecord $model)
    {
        $this->model = $model;
        if (!$this->model instanceof ISortableModel) {
            throw new InvalidArgumentException('Model must be instanceof ISortableModel');
        }
        $this->condition = $this->model->sortableCondition();
    }

    abstract public function changeSort();

    abstract public function updateSort();

    abstract public function addToBeginning();

    abstract public function addToEnd();

    abstract public function delete();

    protected function getPrev()
    {
        $prev = $this->model::find()
            ->where($this->condition)
            ->andWhere(['id' => $this->previous_id])
            ->select($this->sortField)
            ->scalar();
        if (!$prev) {
            throw new InvalidArgumentException("Record with id = $this->previous_id not found");
        }
        return $prev;
    }
}