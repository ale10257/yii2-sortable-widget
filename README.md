### Yi2 sortable виджет, на основе <a href="https://github.com/SortableJS/Sortable">sortablejs</a>

Виджет представляет обертку над sortablejs. Также в состав виджета входят два сервиса (SortableServicePostgres и SortableService) для работы с БД. Т.е. после перетаскивания элементов на странице, порядок сортировки в БД, также меняется.

**Отличия сервисов**

1. SortableServicePostgres работает только с Postgres DB, SortableService с любой, БД, поддерживаемой Yii2
2. У SortableServicePostgres шаг сортировки всегда 10, в SortableService шаг можно настроить

В модели ActiveRecord, с которой работают сервисы, должно быть поле sort типа integer.

Установка ```composer require ale10257/yii2-sortable-widget```

**Использование**

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
//     Подробности для pluginOptions на https://github.com/SortableJS/Sortable
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
                'modelClass' => MyActiveRecordModel::class
            ]
        ];
    }
```

**В модели ActiveRecord необходимо реализовать интерфейс ISortableModel, и прикрепить поведение SortableBehavior**
```php

class MyModel extends \yii\db\ActiveRecord implements \ale10257\sortable\ISortableModel 
{

    public function behaviors(): array
    {
        return [
        ...
            [
                'class' => \ale10257\sortable\SortableBehavior::class,
                'serviceClass' => \ale10257\sortable\SortableServicePostgres::class // по умолчанию SortableService,
                'sortField' => 'my_field', // поле для сортировки, по умолчанию sort
                'step' => 1, // по умолчанию 10
                'addToBeginning' => true // по умолчанию false, новые записи добавляются в конец списка
            ]
        ...    
        ];
    }
    
    /**
    * @return array|string|null условие where для сортировки, например ['parent_id' => $this->parent_id]
    */
    public function sortableCondition() 
    {
        return null;
    }
}
```

Поведение SortableBehavior обрабатывает события afterDelete и afterInsert
```php
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }
```

Если в своей модели вы также обрабатываете эти события, то логику для изменения порядка сортировки, после создания, или удаления новой записи необходимо реализовать самостоятельно, например:

```php
    public fuction afterDelete
    {
        ...
        $service = ale10257\sortable\ServiceFactory::getServiceFromModel($this);
        $service->delete();
    }

```


**Unit тесты (запустить команду в корне папки с виджетом):**

```
docker-compose up -d && docker-compose run --rm php composer install && docker-compose run --rm php bash -c './vendor/bin/codecept run unit' && docker-compose down
```

