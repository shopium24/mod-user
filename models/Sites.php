<?php

namespace shopium24\mod\user\models;


use Yii;
use panix\engine\db\ActiveRecord;
use shopium24\mod\plans\models\Plans;

/**
 * This is the model class for table "tbl_sites".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $full_name
 * @property integer $hosting_account_id
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
            [['subdomain'], 'string', 'max' => 100],
            [['subdomain','plan_id'], 'required']
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getPlan()
    {
        return $this->hasOne(Plans::class, ['id' => 'plan_id']);
    }

    /**
     * Set user id
     *
     * @param int $userId
     * @return static
     */
    public function setUser22($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

}
