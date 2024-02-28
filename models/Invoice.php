<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoices".
 *
 * @property int $id
 * @property int|null $customer_id
 * @property string|null $date
 * @property float|null $amount
 * @property int|null $status 0-> inactive, 1-> pending, 2->approved, 3 -> rejected
 * @property string|null $created_date
 * @property string|null $updated_date
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'invoices';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'status'], 'default', 'value' => null],
            [['customer_id', 'status'], 'integer'],
            [['date', 'created_date', 'updated_date'], 'safe'],
            [['amount'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'customer_id' => 'Customer ID',
            'date' => 'Date',
            'amount' => 'Amount',
            'status' => 'Status',
            'created_date' => 'Created Date',
            'updated_date' => 'Updated Date',
        ];
    }

    public function readinvoice()
    {
        $sql = "SELECT inv.*, c.name,
                CASE WHEN inv.status = 0 THEN 'Unpaid'
                    WHEN inv.status = 1 THEN 'Paid'
                    WHEN inv.status = 2 THEN 'Cancelled' END AS status_text
                FROM invoices AS inv
                LEFT JOIN customers AS c ON (inv.customer_id = c.id)
                ORDER BY inv.id";
        return Yii::$app->db->createcommand($sql)->queryAll();
    }


}
