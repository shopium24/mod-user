<?php

namespace shopium24\mod\user\models;


use app\modules\hosting\components\Api;
use panix\engine\CMS;
use panix\engine\db\Connection;
use Yii;
use panix\engine\db\ActiveRecord;
use shopium24\mod\plans\models\Plans;
use yii\helpers\FileHelper;

/**
 * This is the model class for table "tbl_sites".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $create_time
 * @property string $update_time
 * @property string $full_name
 * @property integer $expire
 * @property integer $hosting_account_id
 *
 * @property User $user
 */
class Sites extends ActiveRecord
{
    const MODULE_ID = 'user';
    const FREE_TIME = 86400 * 14;

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
            [['subdomain'], 'string', 'max' => 100],
            [['subdomain', 'plan_id'], 'required'],
            [['subdomain'], 'validateSubdomain'],
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

    public function validateSubdomain($attribute)
    {
        $site = Yii::$app->params['domain'];
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

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->expire = time() + self::FREE_TIME;
        }
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        $isCreateHost = false;
        $isCreateDb = false;
        $isChangePwdDb = false;
        $db_password = CMS::gen(25);
        if ($insert) {
            // Create subdomain
            $params['site'] = Yii::$app->params['domain'];
            $params['subdomain'] = $this->subdomain;

            $api = new Api('hosting_site', 'host_create', $params);

            if ($api->response['status'] == 'success') {
                $isCreateHost = true;
            }


            // Create Database
            $params = [];
            $params['name'] = 'c' . $this->user_id;
            $params['collation'] = 'utf8_general_ci';
            $params['user_create'] = true;
            $api = new Api('hosting_database', 'database_create', $params);
            if ($api->response['status'] == 'success') {
                $isCreateDb = true;

                // Chnage Db user password
                $params = [];
                $params['user'] = 's24_c' . $this->user_id;
                $params['password'] = $db_password;
                $api = new Api('hosting_database', 'user_password', $params);
                if ($api->response['status'] == 'success') {
                    $isChangePwdDb = true;
                }


            }


            if ($isCreateHost && $isCreateDb && $isChangePwdDb) {
                $this->createMailbox();
                $this->unZip();
                $this->insertDb('s24_c' . $this->user_id, $db_password);
            } else {
                var_dump($isCreateHost);
                var_dump($isCreateDb);
                var_dump($isChangePwdDb);
                die('no valid');
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
            $subDomainPath = Yii::getAlias('@app') . '/../' . $this->subdomain;
            FileHelper::createDirectory($subDomainPath, $mode = 0750, $recursive = true);
            $extract = $zipFile->extractTo($subDomainPath);
        } else {
            die('no find file zip');
        }
    }


    private function insertDb($user, $password)
    {
        $file = COMMON_PATH . DIRECTORY_SEPARATOR . 'client.sql';
        if (file_exists($file)) {
            $connection = new Connection([
                'dsn' => strtr('mysql:host=s24.mysql.tools;dbname={db_name}', [
                    '{db_name}' => $user,
                ]),
                'username' => $user,
                'password' => $password,
                'tablePrefix' => CMS::gen(5) . '_'
            ]);
            $connection->open();
            $connection->import($file);
            $connection->close();
        } else {
            die('no find file sql');
        }
    }

    private function createMailbox()
    {
        $mailboxPassword = CMS::gen(10);
        $params['mailbox'] = $this->subdomain . '@' . Yii::$app->params['domain'];
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
