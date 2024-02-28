<?php
namespace app\modules\v1\controllers;

use Yii;
use yii\db\Exception;

use app\services\ExcelService;
use yii\filters\AccessControl;
use yii\data\ActiveDataProvider;
use yii\web\HttpException;

use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;
use app\models\Customer;
use app\models\Invoice;
use app\models\Invoices;
use app\models\User;
use yii\rest\ActiveController;


class ProjectController extends ActiveController
{
    public $modelClass = 'app\models\User';

        public function __construct($id, $module, $config = [])
        {
            parent::__construct($id, $module, $config);

        }

        public function actions()
        {
            return [];
        }

        public function behaviors()
        {
            $behaviors = parent::behaviors();

            $behaviors['authenticator'] = [
                'class' => CompositeAuth::className(),
                'authMethods' => [
                    HttpBearerAuth::className(),
                ],

            ];

            $behaviors['verbs'] = [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => [
                    'login' => ['post'],
                    'update-program' => ['post'],
                    'logout' => ['post'],
                    'readcustomerandinvoice' => ['GET'],
                    'addcustomer' => ['POST'],
                    'addinvoice' => ['POST'],
                ],
            ];

            // remove authentication filter
            $auth = $behaviors['authenticator'];
            unset($behaviors['authenticator']);

            // add CORS filter
            $behaviors['corsFilter'] = [
                'class' => \yii\filters\Cors::className(),
                'cors' => [
                    /*'Origin' => ['*'],
                    'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                    'Access-Control-Request-Headers' => ['*'],
                    'Access-Control-Allow-Credentials' => true,
                    'Access-Control-Allow-Origin' => ['*'],*/
                ],
            ];

            // re-add authentication filter
            $behaviors['authenticator'] = $auth;
            // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
            $behaviors['authenticator']['except'] = ['options', 'login','update-program','logout','readcustomerandinvoice','addcustomer','addinvoice'];


	        // setup access
	        $behaviors['access'] = [
		        'class' => AccessControl::className(),
		        'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
		        'rules' => [
			        [
				        'allow' => true,
				        'actions' => ['index', 'view', 'create', 'update', 'delete','login','update-program','logout',
                                        'readcustomerandinvoice',
                                        'addcustomer',
                                        'addinvoice'],
				        'roles' => ['admin', 'manageUsers'],
			        ],
			        [
			            'allow' => true,
			            'actions'   => ['me'],
			            'roles' => ['user']
			        ]
		        ],
	        ];

            return $behaviors;
        }
        public function auth()
        {
            return [
                'bearerAuth' => [
                    'class' => \yii\filters\auth\HttpBearerAuth::className(),
                ],
            ];
        }

        public function actionOptions($id = null) {
            return "ok";
        }

        public function getBearerAccessToken()
        {
            $bearer = null;
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                $matches = array();
                preg_match('/^Bearer\s+(.*?)$/', $headers['Authorization'], $matches);
                if (isset($matches[1])) {
                    $bearer = $matches[1];
                }
            } elseif (isset($headers['authorization'])) {
                $matches = array();
                preg_match('/^Bearer\s+(.*?)$/', $headers['authorization'], $matches);
                if (isset($matches[1])) {
                    $bearer = $matches[1];
                }
            }
            return $bearer;
        }

    /**
     * Generic function to throw HttpExceptions
     * @param $errCode
     * @param $errMsg
     * @author Suresh N
     */
    private function throwException($errCode, $errMsg)
    {
        throw new \yii\web\HttpException($errCode, $errMsg);
    }


    public function actionLogin()
    {
        $current_time   = date('Y-m-d H:i:s');
        $current_date   = date('Y-m-d');
        $parms = Yii::$app->request->post('LoginForm');
        $response = [];
        if(isset($parms) && !empty($parms['username'])){
            $login_details = User::find()->where(['username'=>$parms['username']])->one();
            if(isset($login_details) && !empty($login_details['id'])){
                if($parms['password'] === $login_details['password_hash']){
                    $userauthh = new User();
                    $userauthh->generateAccessToken();
                    // return $userauthh -> access_token_expired_at;
                    $login_details -> device_token = $userauthh -> device_token;
                    $login_details -> access_token_expired_at = date("Y-m-d H:i:s", $userauthh -> access_token_expired_at);
                    $login_details -> last_login_at = $current_time;
                    if($login_details -> save(false)){
                        $response['message'] = "Login successful";
                        $response['device_token'] = $userauthh -> device_token;
                        $response['user_name'] = $login_details['username'];
                        $response['program_id'] = $login_details['program_id'];
                        $response['role_id'] = $login_details['user_role_id'];

                        return $response;
                    }else{
                        throw new HttpException(422, "Something went wrong.!");
                    }

                }else{
                    throw new HttpException(422, "Incorrect password.!");
                }
            }else{
                throw new HttpException(422, "Username not found.!");
            }
        }else{
            throw new HttpException(422, "Credential required.!");
        }
    }


    public function actionReadcustomerandinvoice()
    {
        $current_time   = date('Y-m-d H:i:s');
        $current_date   = date('Y-m-d');
        $response = [];
        $program_id = 1;
        $access_token  = $this->getBearerAccessToken();
        if(isset($access_token) && !empty($access_token)){
            $userLogin      = new User();
            $userDetails    = $userLogin->getUserDetailsByAccessToken($program_id,$access_token);
            if(isset($userDetails['user_id']) && isset($userDetails['access_token_expired_at']) && $userDetails['access_token_expired_at'] > $current_time){

                $customer_details = Customer::find()->orderBy(['id' => SORT_ASC])->all();
                $response['customer_detailds'] = $customer_details;

                // $invoices_details = Invoice::find()->select(['invoices.*', 'c.name'])->leftJoin('customers as c', 'c.id = invoices.customer_id')->all();
                $invoices = new Invoice();
                $invoice_details = $invoices -> readinvoice();
                $response['invoice_details'] = $invoice_details;

                return $response;

            }else{
                throw new HttpException(401, json_encode("Unauthorized user access!!"));
            }
        }else{
            throw new HttpException(401, json_encode("Access Token is not set permission denied!"));
        }

    }

    public function actionAddcustomer()
    {
        $current_time   = date('Y-m-d H:i:s');
        $current_date   = date('Y-m-d');
        $response = [];
        $program_id = 1;
        $access_token  = $this->getBearerAccessToken();
        if(isset($access_token) && !empty($access_token)){
            $userLogin      = new User();
            $userDetails    = $userLogin->getUserDetailsByAccessToken($program_id,$access_token);
            if(isset($userDetails['user_id']) && isset($userDetails['access_token_expired_at']) && $userDetails['access_token_expired_at'] > $current_time){
                $is_customer = Yii::$app->request->post('is_customer');
                if($is_customer == 1){
                    $name = Yii::$app->request->post('name');
                    $phone = Yii::$app->request->post('phone');
                    $email = Yii::$app->request->post('email');
                    $address = Yii::$app->request->post('address');
                    $id = Yii::$app->request->post('id');


                    if(isset($id) && !empty($id)){
                        if(filter_var($id, FILTER_VALIDATE_INT) !== false){
                            $customer_details = Customer::find()->where(['id' => $id])->one();
                            if(!isset($customer_details) || empty($customer_details['id'])){
                                throw new HttpException(404, json_encode("No customer found.!"));
                            }
                            $response['message'] = "Details updated successfully.!";

                        }else{
                            throw new HttpException(422, json_encode("Invoice details format is not correct.!"));
                        }
                    }


                    if(!isset($customer_details) || empty($customer_details['id'])){
                        $customer_details = new Customer();
                        $customer_details -> created_date = $current_time;
                        $response['message'] = "Details Stored successfully.!";
                    }

                    if(isset($name) && !empty($name)){
                        $customer_details -> name = $name;
                    }else{
                        throw new HttpException(422, "customer name cannot be empty.!");
                    }

                    $customer_details -> phone = (string)$phone;
                    $customer_details -> email = $email;
                    $customer_details -> address = $address;
                    // return $customer_details;
                    if($customer_details -> validate() && $customer_details -> save()){
                        $response['id'] = $customer_details -> id;
                        return $response;
                    }else{
                        throw new HttpException(422, "Something went wrong.!");
                    }
                }elseif($is_customer == 0){
                    $customer_id = Yii::$app->request->post('customer_id');
                    $invoice_date = Yii::$app->request->post('invoice_date');
                    $amount = Yii::$app->request->post('amount');
                    $status = Yii::$app->request->post('status');
                    $id = Yii::$app->request->post('id');

                    if(isset($id) && !empty($id)){
                        if(filter_var($id, FILTER_VALIDATE_INT) !== false){
                            $invoice_details = Invoice::find()->where(['id' => $id])->one();
                            if(!isset($invoice_details) || empty($invoice_details['id'])){
                                throw new HttpException(404, json_encode("No invoice found.!"));
                            }
                            $response['message'] = "Details updated successfully.!";

                        }else{
                            throw new HttpException(422, json_encode("Invoice details format is not correct.!"));
                        }
                    }

                    if(!isset($invoice_details) || empty($invoice_details['id'])){
                        $invoice_details = new Invoice();
                        $invoice_details -> created_date = $current_time;
                        $response['message'] = "Details Stored successfully.!";
                    }



                    if(isset($customer_id) && !empty($customer_id)){
                        if(filter_var($customer_id, FILTER_VALIDATE_INT) !== false){
                            $customer_details = Customer::find()->where(['id' => $customer_id])->one();
                            if(isset($customer_details) && !empty($customer_details['id'])){
                                $invoice_details -> customer_id = $customer_id;
                                $invoice_details -> date = $invoice_date;

                                if(is_numeric($amount)){
                                    $invoice_details -> amount = $amount;
                                }else{
                                    throw new HttpException(422, json_encode("Amount format is not correct.!"));
                                }

                                if(filter_var($status, FILTER_VALIDATE_INT) !== false){
                                    $invoice_details -> status = $status;
                                }else{
                                    throw new HttpException(422, json_encode("status format is not correct.!"));
                                }


                                $invoice_details -> updated_date = $current_time;
                                if($invoice_details -> validate() && $invoice_details -> save()){
                                    $response['id'] = $invoice_details -> id;
                                    return $response;
                                }else{
                                    throw new HttpException(422, json_encode("Something went wrong.!"));
                                }

                            }else{
                                throw new HttpException(404, json_encode("No customer found.!"));
                            }
                        }else{
                            throw new HttpException(422, json_encode("Customer details format is not correct.!"));
                        }
                    }else{
                        throw new HttpException(422, json_encode("Customer details cannot be empty.!"));
                    }
                }else{
                    throw new HttpException(422, json_encode("Specify the change properly.!"));
                }
            }else{
                throw new HttpException(401, json_encode("Unauthorized user access!!"));
            }
        }else{
            throw new HttpException(401, json_encode("Access Token is not set permission denied!"));
        }
    }


    public function actionAddinvoice()
    {
        $current_time   = date('Y-m-d H:i:s');
        $current_date   = date('Y-m-d');
        $response = [];
        $program_id = 1;
        $access_token  = $this->getBearerAccessToken();
        if(isset($access_token) && !empty($access_token)){
            $userLogin      = new User();
            $userDetails    = $userLogin->getUserDetailsByAccessToken($program_id,$access_token);
            if(isset($userDetails['user_id']) && isset($userDetails['access_token_expired_at']) && $userDetails['access_token_expired_at'] > $current_time){

            }else{
                throw new HttpException(401, json_encode("Unauthorized user access!!"));
            }
        }else{
            throw new HttpException(401, json_encode("Access Token is not set permission denied!"));
        }

    }



}


