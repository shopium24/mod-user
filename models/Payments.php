<?php

namespace shopium24\mod\user\models;


use app\modules\hosting\components\Api;
use panix\engine\CMS;
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
            [['subdomain'], 'string', 'max' => 100],
            [['subdomain', 'plan_id'], 'required'],
            [['subdomain'], 'validateSubdomain'],
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

    public function validateSubdomain($attribute)
    {
        $site = 'shopium24.com';
        $api = new Api('hosting_site', 'info', ['site' => $site]);
        if ($api->response['status'] == 'success') {
            $domains = [];
            foreach ($api->response['data'][$site]['hosts'] as $subdomain => $data) {
                $domains[] = $subdomain;
            }
            if (in_array($this->$attribute, $domains)) {
                $this->addError($attribute, 'Такой поддомен уже есть');
            }
        }
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            $params['site'] = 'shopium24.com';
            $params['subdomain'] = $this->subdomain;

            $api = new Api('hosting_site', 'host_create', $params);

            if ($api->response['status'] == 'success') {
                //$response = $api->response['data'];
                $this->createMailbox();
                $this->unZip();
                //unzip files


            }
        }

        parent::afterSave($insert, $changedAttributes);
    }

    private function unZip()
    {
        $file = COMMON_PATH . DIRECTORY_SEPARATOR . 'client.zip';
        if (file_exists($file)) {
            $zipFile = new \PhpZip\ZipFile();
            $zipFile->openFile($file);
            $extract = $zipFile->extractTo(Yii::getAlias('@app') . '/../'.$this->subdomain);
        } else {
            die('no find file zip');
        }
    }

    private function createMailbox()
    {
        $mailboxPassword = CMS::gen(10);
        $params['mailbox'] = $this->subdomain . '@shopium24.com';
        $params['password'] = $mailboxPassword;
        $params['type'] = 'mailbox';
        $params['antispam'] = 'medium';

        if (false) {
            $params['autoresponder']['enabled'] = $model->autoresponder;
            $params['autoresponder']['title'] = $model->autoresponder_title;
            $params['autoresponder']['text'] = $model->autoresponder_text;
        }
        if (false) {
            $params['forward'] = explode(',', $model->forward);
        }
        $api = new Api('hosting_mailbox', 'create', $params);

        if ($api->response['status'] == 'success') {
            $response = $api->response['data'];

        } else {

        }
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
