<?php

namespace ale10257\sortable;

use yii\base\BaseObject;
use yii\base\InvalidArgumentException;
use yii\jui\JuiAsset;
use yii\web\View;
use Yii;

class SortableJs extends BaseObject
{
    public ?string $cssSelector = null;
    public ?string $ajaxUrl = null;

    private ?View $view = null;

    public function init()
    {
        parent::init();
        $this->view = Yii::$app->getView();
        JuiAsset::register($this->view);
    }

    public function registerJs()
    {
        if (!$this->cssSelector) {
            throw new InvalidArgumentException('Unknown css selector');
        }
        $js = "
            $(function () {
                let el = document.querySelector('$this->cssSelector')
                if (!el) {
                    console.error('Element $this->cssSelector not found!')
                    return false
                }
                el = el.tagName.toLowerCase()
                if (el !== 'table' && el !== 'ul') {
                    console.error('Element must be table or ul only!')
                    return false
                }
                let items = $('$this->cssSelector')
                items.each(function () {
                    let url = '$this->ajaxUrl'
                    if (!url) {
                        console.error('Url is undefined!')
                    }
                    let sortable
                    if (el === 'table') {
                        sortable = $(this).find('tbody')
                        if (!sortable) {
                            console.error('tbody in table not found!')
                            return false
                        }
                    } else {
                        sortable = $(this)
                    }
                    sortable.sortable({
                        cursor: 'move',
                        update: (event, ui) => {
                            let prev = ui.item.prev()
                            if (ui.item.data('excluded') !== undefined) {
                                alert('Нельзя изменять неактивные позиции!')
                                return false
                            }
                            let position = prev.length === 0 ? 0 : prev.data('id')
                            let id = ui.item.data('id')
                            $.post(url, {id, position})
                        },
                        delay: 200
                    })
                })
            })
        ";
        $this->view->registerJs($js);
    }
}