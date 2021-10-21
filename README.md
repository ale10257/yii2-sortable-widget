### Простой yii2 sortable виджет, на основе jquery sortable ui

В состав виджета включен SortableService для работы с Postgres DB. С другими БД работать не будет.

Установка ```composer require ale10257/yii2-sortable-widget```

Виджет работает с объектами ActiveRecord, модель для сортировки должна имплементировать ISortableModel интерфейс
```php
class MyModel extends ActiveRecord implements ale10257\sortableISortableModel
```

В ISortableModel два метода sortableCondition() и getSortableService(), примеры реализации

```php
    public function sortableCondition(): array
    {
        return ['parent_id' => $this->parent_id];
    }

    public function getSortableService(): SortableService
    {
        return new SortableService($this);
    }
```

Примеры работы с SortableService на уровне модели:

```php
 ($this->getSortableService())->addToBeginning(); // добавить в начало списка
 ($this->getSortableService())->addToEnd(); // добавить в конец списка
 
// вставить ПЕРЕД какой-либо записью
$prevId = 1; // id записи, перед которой нужно вставить нашу модель
$sortableService = $this->getSortableService();
$sortableService->position = $prevId;
$sortableService->changeSort();
```
Сам виджет работает с таблицами и ненумерованными списками (ul). У таблицы должен быть прописан элемент tbody.

У элементов списка необходимо прописать аттрибут ```data-id```

```html
<ul class="sortable">
<li data-id="1"></li>
<li data-id="2"></li>
</ul>

<table class="sortable">
    <tbody>
        <tr data-id="1"><td></td></tr>
        <tr data-id="2" data-excluded="1"><td></td></tr>
    </tbody>
</table>
```
Элементы с аттрибутом ```data-excluded``` перетаскиваться не будут.

Подключение js sortable в view:

```php
$sortableJs = new ale10257\sortable\SortableJs();
$sortableJs->cssSelector = '.sortable';
$sortableJs->ajaxUrl = \yii\helpers\Url::to(['sort']);
$sortableJs->registerJs();
```
При использовании GridView

```php
use ale10257\sortable\SortableGridView

echo SortableGridView::widget(
    [
        ...
        'rowOptions' => function (\yii\db\ActiveRecord $model) {
            return ['data-id' => $model->id];;
        },
        'ajaxUrl' => \yii\helpers\Url::to(['sort']),
        ...
    ]
);
```
Добавление actionSort в контроллере:

```php
    public function actions(): array
    {
        return [
            'sort' => [
                'class' => \ale10257\sortable\SortAction::class,
                'modelClass' => MyActiveRecordModel::class,
                //'sortField' => 'MyActiveRecordModeFieldSortName' по умолчанию название поля для сортировки sort
            ]
        ];
    }
```
