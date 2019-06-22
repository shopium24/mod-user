<?php

namespace shopium24\mod\user\models\search;

use Yii;
use yii\base\Model;
use panix\engine\data\ActiveDataProvider;
use shopium24\mod\user\models\Sites;

/**
 * UserSearch represents the model behind the search form about `shopium24\mod\user\models\User`.
 */
class SitesSearch extends Sites {

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['id', 'role_id', 'status'], 'integer'],
            [['email', 'new_email', 'username', 'password', 'auth_key', 'api_key', 'login_ip', 'login_time', 'create_ip', 'create_time', 'update_time', 'ban_time', 'ban_reason', 'full_name'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios() {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function attributes() {
        // add related fields to searchable attributes
        return array_merge(parent::attributes(), ['profile.full_name']);
    }

    /**
     * Search
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params) {

        // get models
        $user = new Sites;
        $userTable = $user::tableName();

        // set up query with relation to `profile.full_name`
        $query = $user::find();

        // create data provider
        $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                ]);

        // enable sorting for the related columns
        $addSortAttributes = ["profile.full_name"];
        foreach ($addSortAttributes as $addSortAttribute) {
            $dataProvider->sort->attributes[$addSortAttribute] = [
                'asc' => [$addSortAttribute => SORT_ASC],
                'desc' => [$addSortAttribute => SORT_DESC],
            ];
        }

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            "{$userTable}.id" => $this->id,
            'role_id' => $this->role_id,
            'status' => $this->status,
        ]);

        $query->andFilterWhere(['like', 'email', $this->email])
                ->andFilterWhere(['like', 'new_email', $this->new_email])
                ->andFilterWhere(['like', 'username', $this->username])
                ->andFilterWhere(['like', 'password', $this->password])
                ->andFilterWhere(['like', 'auth_key', $this->auth_key]);

        return $dataProvider;
    }

}