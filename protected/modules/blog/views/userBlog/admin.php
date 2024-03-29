<?php

/**
 * @var $this Controller
 * @var $model UserBlog
 */
$this->breadcrumbs = array(
    Yii::t('BlogModule.blog', 'Members') => array('admin'),
    Yii::t('BlogModule.blog', 'Manage'),
);

$this->menu = array(
    array('label' => Yii::t('BlogModule.blog', 'Members')),
    array('icon' => 'list-alt', 'label' => Yii::t('BlogModule.blog', 'Manage'), 'url' => array('admin')),
    array('icon' => 'file', 'label' => Yii::t('BlogModule.blog', 'Create'), 'url' => array('create')),
);

Yii::app()->clientScript->registerScript(
    'search',
    "
$('.search-button').click(function(){
    $('.search-form').toggle();
    return false;
});
$('.search-form form').submit(function(){
    $.fn.yiiGridView.update('user-to-blog-grid', {
        data: $(this).serialize()
    });
    return false;
});
"
);
?>
<?php echo CHtml::link(Yii::t('BlogModule.blog', 'Search'), '#', array('class'=> 'search-button btn btn-small')); ?>
<div class="search-form" style="display:none">
    <?php $this->renderPartial('_search', array('model' => $model)); ?>
</div><!-- search-form -->

<?php $this->widget(
    'FadTbGridView',
    array(
        'id'            => 'user-to-blog-grid',
        'dataProvider'  => $model->search(),
        'enableHistory' => true,
        'filter'        => $model,
        'columns'       => array(
            array(
                'name'        => 'id',
                'htmlOptions' => array('style' => 'width: 20px; text-align: center'),
            ),
            array(
                'name'        => 'user_id',
                'type'        => 'raw',
                'value'       => 'CHtml::link($data->user->username, array("/user/default/view/", "id" => $data->user->id))',
                'htmlOptions' => array('style' => 'width: 100px;'),
            ),
            array(
                'name'  => 'blog_id',
                'type'  => 'raw',
                'value' => 'CHtml::link($data->blog->title, array("/blog/default/view/", "id" => $data->blog->id))',
            ),
            array(
                'name'        => 'create_time',
                'value'       => 'Yii::app()->getDateFormatter()->formatDateTime($data->create_time, "short", "short")',
                'htmlOptions' => array('style' => 'width: 100px; text-align: center'),
            ),
            array(
                'name'        => 'update_time',
                'value'       => 'Yii::app()->getDateFormatter()->formatDateTime($data->update_time, "short", "short")',
                'htmlOptions' => array('style' => 'width: 100px; text-align: center'),
            ),
            //@todo bootstrapStatus
            /*array(
                'name'  => 'role',
                'type'  => 'raw',
                'value' => '$this->grid->getStatus($data)',
            ),*/
            array(
                'name'        => 'status',
                'type'        => 'raw',
                'value'       => '$this->grid->getStatus($data)',
                'htmlOptions' => array('style' => 'width: 20px; text-align: center'),
            ),
            'note',
            array(
                'class'=> 'bootstrap.widgets.TbButtonColumn',
            ),
        ),
    )
); ?>
