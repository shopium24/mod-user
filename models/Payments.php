<?php

namespace shopium24\mod\user\models;


use app\modules\hosting\components\Api;
use panix\engine\CMS;
use Yii;
use panix\engine\db\ActiveRecord;
use shopium24\mod\plans\models\Plans;

/**
 * This is the model class for table "tbl_payments".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $site_id
 * @property integer $month
 * @property double $price
 *
 * @property User $user
 */
class Payments extends ActiveRecord
{
    const MODULE_ID = 'user';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%payments}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // [['subdomain'], 'string', 'max' => 100],
            [['site_id', 'user_id'], 'required'],
            ['month', 'in', 'range' => [1, 6, 12]],

        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSite()
    {
        return $this->hasOne(Sites::class, ['id' => 'site_id']);
    }

    public function getPlan()
    {
        return $this->hasOne(Plans::class, ['id' => 'plan_id']);
    }
    public function beforeValidate()
    {
        if($this->isNewRecord){
            $this->user_id = Yii::$app->user->id;
        }
        return parent::beforeValidate();
    }



}
