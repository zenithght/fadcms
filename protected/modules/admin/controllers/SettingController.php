<?php
/**
 * User: fad
 * Date: 19.09.12
 * Time: 11:58
 */
class SettingController extends Controller
{
    public $defaultAction = 'update';

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array('rights');
    }

    /**
     * @param string $slug
     * @throws CHttpException
     */
    public function actionUpdate($slug)
    {
        /**
         * @var $module WebModule
         * @var $settings Setting[]
         */
        $module_id = $slug;
        unset($slug);
        if (!$module = Yii::app()->getModule($module_id)) {
            throw new CHttpException(404, Yii::t('yii', 'The system is unable to find the requested action "{action}".', array('{action}' => $module_id)));
        }

        $settings = $this->getSettingsToUpdate($module);

        if (isset($_POST['Setting'])) {
            $valid = true;
            //settingKey => settingData's etc..
            foreach ($settings as $key => $setting) {
                if (isset($_POST['Setting'][$key])) {
                    $settings[$key]->setAttributes($_POST['Setting'][$key]);
                    $valid = $settings[$key]->validate() && $valid;
                }
            }
            if ($valid) {
                foreach ($settings as $setting) {
                    if ($setting->save()) {
                        Yii::app()->user->setFlash('success', Yii::t('setting', 'Настройки сохранены!'));
                    }
                }
            } else {
                Yii::app()->user->setFlash('error', Yii::t('setting', 'Произошла ошибка при сохранении!'));
            }
        }
        $this->render('update', array('module' => $module, 'settings' => $settings));
    }

    /**
     * @todo move to Model
     * @param CModule $module
     * @return array Setting[]
     * @throws CHttpException
     */
    public function getSettingsToUpdate($module)
    {
        $settingLabels = $module->settingLabels;
        if (!count($settingLabels)) {
            throw new CHttpException(404, Yii::t(
                'admin',
                "У модуля {name} нет настроек",
                array('{name}' => $module->name)
            ));
        }

        $settingKeys = array_keys($settingLabels);
        $settingData = $module->settingData;
        /** @var $settings Setting[] */
        $settings = Setting::model()->getSettings($module->id, $settingKeys);
        foreach ($module as $key => $value) //settingKey and settingValue
        {
            if (in_array($key, $settingKeys)) {
                if (!isset($settings[$key])) {
                    $settings[$key] = new Setting;
                    $settings[$key]->setAttributes(
                        array(
                            'module_id' => $module->id,
                            'key'       => $key,
                            'value'     => $value,
                        )
                    );
                }
                $settings[$key]->label = $settingLabels[$key];
                if (isset($settingData[$key]['tag'])) {
                    $settings[$key]->tag = $settingData[$key]['tag'];
                }
                if (isset($settingData[$key]['data'])) {
                    $settings[$key]->data = $settingData[$key]['data'];
                }
                if (isset($settingData[$key]['htmlOptions'])) {
                    $settings[$key]->htmlOptions = $settingData[$key]['htmlOptions'];
                }
            }
        }
        return $settings;
    }
}
