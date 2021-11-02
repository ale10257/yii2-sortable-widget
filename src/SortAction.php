<?php

namespace ale10257\sortable;

use yii\base\Action;
use yii\base\InvalidArgumentException;
use Yii;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\web\NotFoundHttpException;

class SortAction extends Action
{
    public ?string $modelClass = null;
    public ?string $sortField = null;
    public ?string $conditionAttribute = null;

    /**
     * @throws Exception
     * @throws NotFoundHttpException
     */
    public function run()
    {
        $post = Yii::$app->request->post();
        if (!empty($post['id']) && (is_numeric($post['previous_id']) || is_string($post['previous_id']))) {
            /** @var ActiveRecord $model */
            $model = new $this->modelClass();
            if (!$model instanceof ActiveRecord) {
                throw new InvalidArgumentException('Model must be instanceof ActiveRecord');
            }
            $model = $model::findOne($post['id']);
            if (!$model) {
                throw new NotFoundHttpException();
            }
            $sortableService = new SortableService($model);
            if ($this->conditionAttribute) {
                $sortableService->condition = [$this->conditionAttribute => $model->{$this->conditionAttribute}];
            }
            $sortableService->previous_id = $post['previous_id'];
            if ($this->sortField) {
                $sortableService->sortField = $this->sortField;
            }
            $sortableService->changeSort();
        }
    }
}