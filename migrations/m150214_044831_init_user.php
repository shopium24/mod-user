<?php
namespace shopium24\mod\user\migrations;

use shopium24\mod\user\models\Payments;
use shopium24\mod\user\models\UserAuth;
use shopium24\mod\user\models\UserKey;
use yii\db\Schema;
use panix\engine\db\Migration;
use shopium24\mod\user\models\User;
use shopium24\mod\user\models\Sites;

class m150214_044831_init_user extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;

        $this->createTable(Payments::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->notNull(),
            'site_id' => $this->integer()->notNull(),
            'month' => "ENUM('1', '6', '12')",
            'price' => $this->money(10,2),
            'created_at' => $this->integer(),
        ]);
        $this->createIndex('site_id', Payments::tableName(), 'site_id');
        $this->createIndex('user_id', Payments::tableName(), 'user_id');

        $this->createTable(User::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'image' => $this->string(100)->null(),
            'status' => Schema::TYPE_SMALLINT . ' not null',
            'email' => Schema::TYPE_STRING . ' null default null',
            'phone' => $this->string(50)->null(),
            'timezone' => $this->string(10)->null(),
            'gender' => $this->tinyInteger(1)->null(),
            'new_email' => Schema::TYPE_STRING . ' null default null',
            'username' => Schema::TYPE_STRING . ' null default null',
            'password' => Schema::TYPE_STRING . ' null default null',
            'auth_key' => Schema::TYPE_STRING . ' null default null',
            'api_key' => Schema::TYPE_STRING . ' null default null',
            'subscribe' => $this->boolean()->defaultValue(1),
            'login_ip' => Schema::TYPE_STRING . ' null default null',
            'login_time' => Schema::TYPE_TIMESTAMP . ' null default null',
            'create_ip' => Schema::TYPE_STRING . ' null default null',
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'ban_time' => Schema::TYPE_TIMESTAMP . ' null default null',
            'ban_reason' => Schema::TYPE_STRING . ' null default null',
        ]);

        $this->createTable(UserKey::tableName(), [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' not null',
            'type' => Schema::TYPE_SMALLINT . ' not null',
            'key' => Schema::TYPE_STRING . ' not null',
            'created_at' => Schema::TYPE_TIMESTAMP . ' null default null',
            'consume_time' => Schema::TYPE_TIMESTAMP . ' null default null',
            'expire_time' => Schema::TYPE_TIMESTAMP . ' null default null',
        ]);
        $this->createTable(Sites::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'user_id' => $this->integer()->unsigned(),
            'plan_id' => $this->tinyInteger()->unsigned(),
            'hosting_account_id'=>$this->string(15),
            'expire'=>$this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'subdomain' => $this->string(50)->notNull(),
        ]);
        $this->createTable(UserAuth::tableName(), [
            'id' => Schema::TYPE_PK,
            'user_id' => Schema::TYPE_INTEGER . ' not null',
            'provider' => Schema::TYPE_STRING . ' not null',
            'provider_id' => Schema::TYPE_STRING . ' not null',
            'provider_attributes' => Schema::TYPE_TEXT . ' not null',
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // add indexes for performance optimization
        $this->createIndex('{{%user_email}}', User::tableName(), 'email', true);
        $this->createIndex('{{%user_username}}', User::tableName(), 'username', true);
        $this->createIndex('{{%user_key_key}}', UserKey::tableName(), 'key', true);
        $this->createIndex('{{%user_auth_provider_id}}', UserAuth::tableName(), 'provider_id', false);

        // add foreign keys for data integrity
        $this->addForeignKey('{{%user_key_user_id}}', UserKey::tableName(), 'user_id', User::tableName(), 'id');
        $this->addForeignKey('{{%user_auth_user_id}}', UserAuth::tableName(), 'user_id', User::tableName(), 'id');


        // insert admin user: neo/neo
        $security = \Yii::$app->security;
        $columns = ['email', 'username', 'password', 'status', 'created_at', 'api_key', 'auth_key'];
        $this->batchInsert('{{%user}}', $columns, [
            [
                'dev@pixelion.com.ua',
                'admin',
                '$2y$13$VCTF0TcDFSb/1LkfKzR5uOAiQJIztPcBWVKMd/3VvIBUy.6sSAPvq',
                User::STATUS_ACTIVE,
                date('Y-m-d H:i:s'),
                $security->generateRandomString(),
                $security->generateRandomString(),
            ],
        ]);
    }

    public function safeDown()
    {
        // drop tables in reverse order (for foreign key constraints)
        $this->dropTable(UserAuth::tableName());
        $this->dropTable(UserKey::tableName());
        $this->dropTable(Payments::tableName());
        $this->dropTable(User::tableName());
    }

}
