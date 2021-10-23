<?php

use ale10257\sortable\SortableService;
use app\tests\models\SortModel;
use Codeception\Test\Unit;

class SortTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        $test_db = dirname(dirname(__DIR__)) . '/test_db.php';
        `php $test_db`;
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
    }

    /**
     * @throws \yii\db\Exception
     */
    public function testSort()
    {
        // parent_id is null
        $model = $this->getModel(1);
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
        $service->position = 3;
        $service->changeSort();
        $models = $this->getModels();
        $this->tester->assertEquals(3, $models[0]->id);
        $this->tester->assertEquals(1, $models[1]->id);
        $this->tester->assertEquals(2, $models[2]->id);
        $this->checkSortOrder($models);
        
        // parent_id is integer
        $model = $this->getModel(5);
        $service = $this->getService($model);
        $service->updateSort();
        $models = $this->getModels();
        $this->checkSortOrder($models);

        $model = $this->getModel(7);
        $service = $this->getService($model);
        $service->addToBeginning();
        $models = $this->getModels(1);
        $this->tester->assertEquals(7, $models[0]->id);
        $this->tester->assertEquals(5, $models[1]->id);
        $this->tester->assertEquals(6, $models[2]->id);
        $this->checkSortOrder($models);

        $model = $this->getModel(5);
        $service = $this->getService($model);
        $service->addToEnd();
        $models = $this->getModels(1);
        $this->tester->assertEquals(7, $models[0]->id);
        $this->tester->assertEquals(6, $models[1]->id);
        $this->tester->assertEquals(5, $models[2]->id);
        $this->checkSortOrder($models);
    }

    /**
     * @param int|null $parent_id
     * @return SortModel[]
     */
    private function getModels(?int $parent_id = null): array
    {
        return SortModel::find()->where(['parent_id' => $parent_id])->orderBy(['sort' => SORT_ASC])->all();
    }

    private function getModel(int $id): ?SortModel
    {
        return SortModel::findOne($id);
    }

    private function getService(SortModel $model): SortableService
    {
        $service = new SortableService($model);
        $service->condition = ['parent_id' => $model->parent_id];
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
