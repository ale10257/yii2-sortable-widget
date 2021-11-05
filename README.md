### Yi2 sortable виджет, на основе <a href="https://github.com/SortableJS/Sortable">sortablejs</a>

Виджет представляет обертку над sortablejs, также он умеет сохранять данные, после перетаскивания, в postgres DB. Если вы используете другую БД, то логику сохранения необходимо реализовать самостоятельно.

В модели ActiveRecord должно быть поле sort типа integer. Изначально поле sort может быть не заполнено (null).

После перетаскивания поле sort заполняется верными упорядоченными данными (10, 20, 30 ... 100 ... 1000 ...).

Установка ```composer require ale10257/yii2-sortable-widget```

**Базовое использование**

В файле представления:

```php
<?php
use ale10257\sortable\SortableWidget;
use yii\helpers\Url;
use yii\grid\GridView;

// аттрибут data-id обязателен для заполнения
// элементы с аттрибутом data-excluded перетаскиваться не будут
// data-url у родительского элемента - адрес для сохранения порядка сортировки после перетаскивания

SortableWidget::widget([
//    'cssSelector' => '#my-id', // по умолчанию .sortable
//     Подробности для pluginOptions на <a href="https://github.com/SortableJS/Sortable">sortablejs</a>
//    'pluginOptions' => [
//        'delay' => 150 
//        'onSort' => '(e) => {}'
//        ...
//    ]
]);
?>

<div class="sortable>
    <p>First</p>
    <p>Two</p>
    <p>Three</p>
</div>

<ul data-url="<?= Url::to(['sort']) ?>" class="sortable">
    <li data-id="1"></li>
    <li data-id="2"></li>
</ul>

<table data-url="<?= Url::to(['sort']) ?>" class="sortable">
    <tbody>
        <tr data-id="1"><td></td></tr>
        <tr data-id="2" data-excluded="1"><td></td></tr>
    </tbody>
</table>

<?= GridView::widget([
...
    'tableOptions' => ['class' => '... sortable', 'data-url' => Url::to(['sort'])],
    'rowOptions' => function (\yii\db\ActiveRecord $model) {
        return ['data-id' => $model->id];
    },
...
]);
?>
```

В классе контроллера:

```php
    public function actions(): array
    {
        return [
            'sort' => [
                'class' => \ale10257\sortable\SortAction::class,
                'modelClass' => MyActiveRecordModel::class,
                'conditionAttribute' => 'attribute' // необязательное поле, если оно объявлено будет сформировано условие where(['attribute' => $model->attribute]), например where(['parent_id' => $model->parent_id])
                //'sortField' => 'my_field_sort_name' по умолчанию sort
            ]
        ];
    }
```

Создание, удаление записей в БД на работу сервиса для сортировки ```\ale10257\sortabl\SortableService``` не влияют. Данные в поле sort будут переписаны на верные, после первого перетаскивания.

===============================

**Если условие для выбора полей сортировки более сложное**, чем в примере: ```'conditionAttribute' => 'attribute'```, то в модели ActiveRecord необходимо реализовать интерфейс ISortableModel
```php

class MyModel extends \yii\db\ActiveRecord implements \ale10257\sortable\ISortableModel 
{
    public function sortableCondition() {
        // return difficult condition
    }
}
```

С сервисом ```SortableService``` можно работать напрямую из модели

```php
use ale10257\sortable\SortableService

class MyModel extends \yii\db\ActiveRecord implements \ale10257\sortable\ISortableModel 
{
    public function getSortableService() {
        $service = new SortableService($this);
        $service->condition = $this->sortableCondition();
        return $service;
    }
    
    public function myFunction () {
        $sortableService = $this->getSortableService();
        
        // запись будет первой в списке
        $sortableService->addToBeginning();
        
        // запись будет последней в списке
        $sortableService->addToEnd();
        
        // запись в списке будет выведена после записи с id = 1
        $sortableService->previous_id = 1;
        $sortableService->changeSort();
        
        // просто упорядочить записи в БД, например, когда в поле sort есть пустые значения
        $sortableService->updateSort();
    }
}
```

**Unit тесты (запустить команду в корне папки с виджетом):**

```
docker-compose up -d && docker-compose run --rm php composer install && docker-compose run --rm php bash -c './vendor/bin/codecept run unit' && docker-compose down
```

