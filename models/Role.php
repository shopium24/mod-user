<?php

namespace shopium24\mod\user\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "tbl_role".
 *
 * @property integer $id
 * @property string  $name
 * @property string  $create_time
 * @property string  $update_time
 * @property integer $can_admin
 *
 * @property User[]  $users
 */
class Role extends ActiveRecord {
    const MODULE_ID = 'user';
    /**
     * @var int Admin user role
     */
    const ROLE_ADMIN = 1;

    /**
     * @var int Default user role
     */
    const ROLE_USER = 2;

    /**
     * @inheritdoc
     */
    public static function tableName() {

        return "{{%user_role}}";
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['name'], 'required'],
            //            [['create_time', 'update_time'], 'safe'],
            [['can_admin'], 'integer'],
            [['name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => Yii::t('user', 'ID'),
            'name' => Yii::t('user', 'Name'),
            'created_at' => Yii::t('user', 'Create Time'),
            'updated_at' => Yii::t('user', 'Update Time'),
            'can_admin' => Yii::t('user', 'Can Admin'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers() {
        $user = Yii::$app->getModule("user")->model("User");
        return $this->hasMany($user::className(), ['role_id' => 'id']);
    }

    /**
     * Check permission
     *
     * @param string $permission
     * @return bool
     */
    public function checkPermission($permission) {
        $roleAttribute = "can_{$permission}";
        return $this->$roleAttribute ? true : false;
    }

    /**
     * Get list of roles for creating dropdowns
     *
     * @return array
     */
    public static function dropdown() {
        // get and cache data
        static $dropdown;
        if ($dropdown === null) {

            // get all records from database and generate
            $models = static::find()->all();
            foreach ($models as $model) {
                $dropdown[$model->id] = $model->name;
            }
        }

        return $dropdown;
    }

}
