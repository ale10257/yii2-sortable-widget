<?php

use ale10257\sortable\ISortableModel;
use ale10257\sortable\SortableService;
use tests\models\SortModel;
use tests\models\SortModelI;
use Codeception\Test\Unit;

class SortTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var string|null|SortModel
     */
    private ?string $modelClass = null;
    private bool $condition = true;

    protected function _before()
    {
        Yii::$app->db->createCommand()->createTable(SortModel::tableName(), [
            'id' => 'pk',
            'parent_id' => 'integer',
            'sort' => 'integer'
        ])->execute();
        for ($parent_id = 1; $parent_id < 4; $parent_id++) {
            $model = new SortModel();
            $model->id = $parent_id;
            $model->save();
        }
        for ($child_id = 5; $child_id < 8; $child_id++) {
            $model = new SortModel();
            $model->id = $child_id;
            $model->parent_id = 1;
            $model->save();
        }
    }

    protected function _after()
    {
        Yii::$app->db->createCommand()->dropTable(SortModel::tableName())->execute();
    }

    public function testSort()
    {
        $this->modelClass = SortModel::class;
        $this->sort();
    }

    public function testSortI()
    {
        $this->modelClass = SortModelI::class;
        $this->sort();
    }

    public function testSortWithoutCondition()
    {
        $this->modelClass = SortModel::class;
        $this->condition = false;
        $model = $this->getModel();
        $service = $this->getService($model);
        $service->updateSort();
        $models = $this->getModels();
        $sort = 10;
        foreach ($models as $model) {
            $this->tester->assertEquals($sort, $model->sort);
            $sort += 10;
        }
    }

    private function sort()
    {
        // parent_id is null
        $model = $this->getModel();
        $service = $this->getService($model);
        $service->updateSort();
        $models = $this->getModels();
        $this->checkSortOrder($models);

        $model = $this->getModel(3);
        $service = $this->getService($model);
        $service->addToBeginning();
        $models = $this->getModels();
        $this->tester->assertEquals(3, $models[0]->id);
        $this->tester->assertEquals(1, $models[1]->id);
        $this->tester->assertEquals(2, $models[2]->id);
        $this->checkSortOrder($models);

        $model = $this->getModel(1);
        $service = $this->getService($model);
        $service->addToEnd();
        $models = $this->getModels();
        $this->tester->assertEquals(3, $models[0]->id);
        $this->tester->assertEquals(2, $models[1]->id);
        $this->tester->assertEquals(1, $models[2]->id);
        $this->checkSortOrder($models);

        $model = $this->getModel(1);
        $service = $this->getService($model);
        $service->previous_id = 3;
        $service->changeSort();
        $this->tester->assertEquals(20, $model->sort);
        $models = $this->getModels();
        $this->tester->assertEquals(3, $models[0]->id);
        $this->tester->assertEquals(1, $models[1]->id);
        $this->tester->assertEquals(2, $models[2]->id);
        $this->checkSortOrder($models);

        // parent_id is integer
        $model = $this->getModel(5);
        $service = $this->getService($model);
        $service->updateSort();
        $models = $this->getModels(1);
        $this->tester->assertEquals(5, $models[0]->id);
        $this->tester->assertEquals(6, $models[1]->id);
        $this->tester->assertEquals(7, $models[2]->id);
        $this->checkSortOrder($models);
    }

    /**
     * @param int|null $parent_id
     * @return SortModel[]
     */
    private function getModels(?int $parent_id = null): array
    {
        $query = $this->modelClass::find()->orderBy(['sort' => SORT_ASC]);
        if ($this->condition) {
            $query->where(['parent_id' => $parent_id]);
        }
        return $query->all();
    }

    private function getModel(int $id = null): ?SortModel
    {
        return $id ? $this->modelClass::findOne($id) : new $this->modelClass();
    }

    private function getService(SortModel $model): SortableService
    {
        $service = new SortableService($model);
        if ($this->condition && !$model instanceof ISortableModel) {
            $service->condition = ['parent_id' => $model->parent_id];
        }
        return $service;
    }

    /**
     * @param SortModel[] $models
     */
    private function checkSortOrder(array $models)
    {
        $this->tester->assertEquals(10, $models[0]->sort);
        $this->tester->assertEquals(20, $models[1]->sort);
        $this->tester->assertEquals(30, $models[2]->sort);
    }
}
