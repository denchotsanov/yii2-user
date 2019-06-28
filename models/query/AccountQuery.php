<?php
namespace denchotsanov\user\models\query;

use denchotsanov\user\clients\ClientInterface;
use denchotsanov\user\models\Account;
use yii\db\ActiveQuery;


/**
 * @method Account|null one($db = null)
 * @method Account[]    all($db = null)
  */
class AccountQuery extends ActiveQuery
{
    /**
     * @param  string       $code
     * @return AccountQuery
     */
    public function byCode($code)
    {
        return $this->andWhere(['code' => md5($code)]);
    }
    /**
     * @param  integer      $id
     * @return AccountQuery
     */
    public function byId($id)
    {
        return $this->andWhere(['id' => $id]);
    }
    /**
     * @param  integer      $userId
     * @return AccountQuery
     */
    public function byUser($userId)
    {
        return $this->andWhere(['user_id' => $userId]);
    }
    /**
     * @param  ClientInterface $client
     * @return AccountQuery
     */
    public function byClient(ClientInterface $client)
    {
        return $this->andWhere([
            'provider'  => $client->getId(),
            'client_id' => $client->getUserAttributes()['id'],
        ]);
    }
}