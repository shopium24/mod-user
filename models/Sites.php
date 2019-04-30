<?php

namespace shopium24\mod\user\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_sites".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $full_name
 *
 * @property User $user
 */
class Sites extends ActiveRecord
{
    const MODULE_ID = 'user';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return "{{%user_sites}}";
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            //            [['user_id'], 'required'],
            //            [['user_id'], 'integer'],
            //            [['create_time', 'update_time'], 'safe'],
            [['full_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('user/default', 'ID'),
            'user_id' => Yii::t('user/default', 'User ID'),
            'create_time' => Yii::t('user/default', 'Create Time'),
            'update_time' => Yii::t('user/default', 'Update Time'),
            'full_name' => Yii::t('user/default', 'Full Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        $user = Yii::$app->getModule("user")->model("User");
        return $this->hasOne($user::className(), ['id' => 'user_id']);
    }

    /**
     * Set user id
     *
     * @param int $userId
     * @return static
     */
    public function setUser($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

}
