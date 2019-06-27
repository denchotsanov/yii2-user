<?php


namespace denchotsanov\user\models;

use denchotsanov\user\Finder;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends Model
{
    public $id;
    public $username;
    public $email;
    public $created_at;
    public $last_login_at;
    public $registration_ip;
    protected $finder;

    public function __construct(Finder $finder, $config = [])
    {
        $this->finder = $finder;
        parent::__construct($config);
    }
    /** @inheritdoc */
    public function rules()
    {
        return [
            'fieldsSafe' => [['id', 'username', 'email', 'registration_ip', 'created_at', 'last_login_at'], 'safe'],
            'createdDefault' => ['created_at', 'default', 'value' => null],
            'lastloginDefault' => ['last_login_at', 'default', 'value' => null],
        ];
    }
    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'id'              => Yii::t('user', '#'),
            'username'        => Yii::t('user', 'Username'),
            'email'           => Yii::t('user', 'Email'),
            'created_at'      => Yii::t('user', 'Registration time'),
            'last_login_at'   => Yii::t('user', 'Last login'),
            'registration_ip' => Yii::t('user', 'Registration ip'),
        ];
    }
    /**
     * @param $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = $this->finder->getUserQuery();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => ['defaultOrder' => ['created_at' => SORT_DESC]],
        ]);
        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $modelClass = $query->modelClass;
        $table_name = $modelClass::tableName();
        if ($this->created_at !== null) {
            $date = strtotime($this->created_at);
            $query->andFilterWhere(['between', $table_name . '.created_at', $date, $date + 3600 * 24]);
        }
        $query->andFilterWhere(['like', $table_name . '.username', $this->username])
            ->andFilterWhere(['like', $table_name . '.email', $this->email])
            ->andFilterWhere([$table_name . '.id' => $this->id])
            ->andFilterWhere([$table_name . '.registration_ip' => $this->registration_ip]);
        return $dataProvider;
    }
}