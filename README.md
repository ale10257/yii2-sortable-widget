### Yi2 sortable виджет, на основе <a href="https://github.com/SortableJS/Sortable">sortablejs</a>

The widget is a wrapper over sortablejs that allows you to drag and drop any elements on the html page
Also, the widget includes two services (SortableServicePostgres and SortableService) for working with the database. That is, after dragging and dropping elements on the page, the sorting order in the database also changes.

**Service differences**

1. SortableServicePostgres works only with Postgres DB, SortableService with any DB supported by Yii2
2. SortableServicePostgres always has a sorting step of 10, in SortableService the step can be configured

Installing: ```composer require ale10257/yii2-sortable-widget```

**Usage**

In the View file:

```php
<?php
use ale10257\sortable\SortableWidget;
use yii\helpers\Url;
use yii\grid\GridView;

// the data-id attribute is required
// elements with the data-excluded attribute will not be dragged
// data-url of the parent element - the address to save the sort order after dragging

SortableWidget::widget([
//    'cssSelector' => '#my-id', // default .sortable
//     Details for pluginOptions at https://github.com/SortableJS/Sortable
//    'pluginOptions' => [
//        'delay' => 150, 
//        'onSort' => '(e) => {}',
//        'onMove' => '(e) => {
//            if (e.dragged.dataset.excluded !== undefined) {
//               return false;
//            }
//        }'  
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

In Controller class:

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

**In the ActiveRecord model, you need to implement the ISortableModel interface, and attach the SortableBehavior behavior**
```php

class MyModel extends \yii\db\ActiveRecord implements \ale10257\sortable\ISortableModel 
{

    public function behaviors(): array
    {
        return [
        ...
            [
                'class' => \ale10257\sortable\SortableBehavior::class,
                'serviceClass' => \ale10257\sortable\SortableServicePostgres::class // default SortableService,
                'sortField' => 'my_field', // sorting field, default sort
                'step' => 1, // default 10
                'addToBeginning' => true // default false, new entries are added to the end of the list
            ]
        ...    
        ];
    }
    
    /**
    * @return array|string|null condition WHERE for sort, example ['parent_id' => $this->parent_id]
    */
    public function sortableCondition() 
    {
        return null;
    }
}
```

The SortableBehavior behavior handles the afterDelete and afterInsert events
```php
    public function events(): array
    {
        return [
            BaseActiveRecord::EVENT_AFTER_DELETE => 'afterDelete',
            BaseActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
        ];
    }
```

If in your model you also handle these events, then the logic for changing the sort order, after creating, or deleting a record, you must implement yourself, for example:

```php
    public fuction afterDelete
    {
        ...
        $service = ale10257\sortable\ServiceFactory::getServiceFromModel($this);
        $service->delete();
    }

```

**Unit tests (run the command in the root of the widget folder):**

```
docker-compose up -d && docker-compose run --rm php composer install && docker-compose run --rm php bash -c './vendor/bin/codecept run unit' && docker-compose down
```

