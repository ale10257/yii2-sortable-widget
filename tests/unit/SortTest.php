<?php

use ale10257\sortable\ISortableModel;
use ale10257\sortable\SortableService;
use ale10257\sortable\testModels\SortModel;
use ale10257\sortable\testModels\SortModelI;
use Ramsey\Uuid\Uuid;
use Codeception\Test\Unit;
use yii\db\Exception;

class SortTest extends Unit
{
    protected UnitTester $tester;

    /**
     * @var string|null|SortModel
     */
    private ?string $modelClass = null;
    private bool $condition = true;

    /**
     * @throws Exception
     */
    protected function _before()
    {
    }

    private function createDataIdIsInt()
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

    private function createDataIdIsUuid()
    {
        Yii::$app->db->createCommand()->createTable(SortModel::tableName(), [
            'id' => 'string',
            'parent_id' => 'string',
            'sort' => 'integer'
        ])->execute();
        Yii::$app->db->createCommand()->addPrimaryKey('pk_' . SortModel::tableName(), SortModel::tableName(), 'id')->execute();
        for ($i = 0; $i < 3; $i++) {
            $id = Uuid::uuid4()->toString();
            $model = new SortModel();
            $model->id = $id;
            $model->save();
        }
    }

    /**
     * @throws Exception
     */
    protected function _after()
    {
        Yii::$app->db->createCommand()->dropTable(SortModel::tableName())->execute();
    }

    /**
     * @throws Exception
     */
    public function testSort()
    {
        $this->modelClass = SortModel::class;
        $this->sort();
    }

    /**
     * @throws Exception
     */
    public function testSortI()
    {
        $this->modelClass = SortModelI::class;
        $this->sort();
    }

    /**
     * @throws Exception
     */
    public function testSortWithoutCondition()
    {
        $this->createDataIdIsInt();
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

    /**
     * @throws Exception
     */
    public function testPreviousIdNum()
    {
        $this->createDataIdIsInt();
        $this->modelClass = SortModel::class;
        $model = $this->getModel();
        $service = $this->getService($model);
        $service->previous_id = -1;
        $this->expectException(InvalidArgumentException::class);
        $service->changeSort();
    }

    /**
     * @throws Exception
     */
    public function testPreviousIdNotFound()
    {
        $this->createDataIdIsInt();
        $this->modelClass = SortModel::class;
        $model = $this->getModel();
        $service = $this->getService($model);
        $service->previous_id = 10;
        $this->expectException(InvalidArgumentException::class);
        $service->changeSort();
    }

    /**
     * @throws Exception
     */
    public function testSortUuid()
    {
        $this->modelClass = SortModel::class;
        $this->createDataIdIsUuid();
        $models = $this->getModels();
        $service = $this->getService($models[0]);
        $service->updateSort();
        $models = $this->getModels();
        $this->checkSortOrder($models);

        $startUuid = $models[0]->id;
        $service = $this->getService($models[0]);
        $service->previous_id = $models[2]->id;
        $service->changeSort();
        $models = $this->getModels();
        $this->tester->assertEquals($startUuid, $models[2]->id);

        $service = $this->getService($models[2]);
        $service->previous_id = 0;
        $service->changeSort();
        $models = $this->getModels();
        $this->tester->assertEquals($startUuid, $models[0]->id);
    }

    /**
     * @throws Exception
     */
    private function sort()
    {
        $this->createDataIdIsInt();
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
