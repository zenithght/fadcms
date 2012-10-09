<?php
/**
 * Config for Translate Yii::t messages
 *
 * Example use (Windows):
 * Open CMD -> cd PATH_TO_PROTECTED_FOLDER -> enter "yiic message messages\config.php"
 * User: fad
 * Date: 25.09.12
 * Time: 13:25
 */
return array(
    'sourcePath'  => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules/comment',
    #'messagePath' => dirname(__FILE__),
    'messagePath' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'modules/comment/messages',
    'languages'   => array('en', 'ru'),
    'fileTypes'   => array('php'),
    'overwrite'   => true,
    'removeOld'   => true,
    'sort'        => true,
);