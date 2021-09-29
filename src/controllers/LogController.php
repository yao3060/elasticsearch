<?php

namespace app\controllers;

use Yii;
use yii\base\DynamicModel;
use yii\base\UnknownPropertyException;
use yii\web\Request;
use app\components\Response;
use app\helpers\StringHelper;

class LogController extends BaseController
{

    /**
     * @api {get} /v1/logs Request Logs
     * @apiName GetLogs
     * @apiGroup Logs
     *
     * @apiParamExample {json} Request-Example:
     *  {
     *      "name": "admin",
     *      "email": "test@app.com",
     *      "age": 100
     *  }
     */
    public function actionIndex(Request $request)
    {
        try {
            $model = DynamicModel::validateData($request->getBodyParams(), [
                ['name', 'string'],
                ['email', 'email'],
                ['age', 'integer', 'min' => 10, 'max' => 20],
            ]);
            if ($model->hasErrors()) {
                return  $this->response(new Response(
                    'unprocessable_entity',
                    'Unprocessable Entity',
                    $model->errors
                ));
            }

            // code here...
            return $this->response(new Response(
                'readable_response_code',
                'Response Message',
                array_merge($model->getAttributes(), [
                    'user' => Yii::$app->user->identity
                ]),
                200
            ));
        } catch (UnknownPropertyException $e) {
            return  $this->response(new Response(
                StringHelper::snake($e->getName()),
                str_replace('yii\\base\\DynamicModel::', '', $e->getMessage()),
                [],
                422,
                [
                    'X-Total' => 100,
                    'X-UserId' => 199
                ]
            ));
        } catch (\Throwable $th) {
            $response = new Response();
            $response->status(500);
            $response->code('internal_server_error');
            $response->message($e->getMessage());
            $response->data(YII_DEBUG ? explode("\n", $th->getTraceAsString()) : []);
            return $this->response($response);
        }
    }

    public function actionCreate(Request $request)
    {
        $this->response->headers->set('Pragma', 'no-cache');
        return $this->asJson($request->post());
    }

    public function actionUpdate(Request $request, int $id)
    {
        $data = [
            'id' => $id,
            'params' => $request->getQueryParams(),
            'body' => $request->post(),
        ];

        // the way to send HTTP Headers
        // @see https://www.yiiframework.com/doc/guide/2.0/en/runtime-responses#http-headers
        $this->response->headers->set('Pragma', 'no-cache');

        return $this->asJson($data);
    }


    /**
     * @api {post} /auth/v1/register Register
     * @apiName Register
     * @apiGroup Auth
     * @apiDescription Register as a user
     *
     * @apiParam {String} first_name        User's first_name
     * @apiParam {String} last_name         User's last_name.
     * @apiParam {String} email             User's email.
     * @apiParam {String} password          User's password.
     * @apiParam {Number} specialty_id      User's specialty id.
     * @apiParam {String} hospital          User's hospital.
     * @apiParam {String} city              User's city.
     * @apiParam {String} country           User's country code.
     */
    public function register(Request $request)
    {
    }

    /**
     * @api {post} /auth/v1/login Login
     * @apiName Login
     * @apiGroup Auth
     * @apiDescription get JWT add it to redis cache.
     *
     * @apiParam {String} username   Users username.
     * @apiParam {String} password   Users password.
     *
     * @apiSuccess {String} token JWT.
     */
    public function login(Request $request)
    {
    }

    /**
     * @api {get} /cases/v1/cases 1. Get Cases
     *
     * @apiName GetCases
     * @apiGroup Cases
     *
     * @apiHeader {String} authorization="Bearer JWT" Authorization value.
     *
     * @apiParam {String} [case_keyword] search title by keyword
     * @apiParam {String} [country]   Country Code, e.g: UK, US
     * @apiParam {String} [city]      City Name
     * @apiParam {Number} [user_id]   Author Id
     * @apiParam {Number} [case_state]   Case state id
     * @apiParam {String} [status]    Status
     * @apiParam {Number} [vessel_location_id] Procudure: Vessel Location Id
     * @apiParam {Number} [vessel_sublocation_id] Procudure: Vessel Sublocation Id
     * @apiParam {Number} [location_in_vessel_id] Procudure: Location In Vessel Id
     * @apiParam {Number} [procedure_type_id] Procudure: Type Id
     * @apiParam {String='small', 'large', 'giant'} [aneurysm_size] Procudure: aneurysm_size
     * @apiParam {String='ruptured', 'unruptured'} [rupture_status] Procudure: rupture_status
     * @apiParam {Number}  [aneurysm_neck_length] Procudure: aneurysm_neck_length
     * @apiParam {Number}  [vessel_diameter_distal] Procudure: vessel_diameter_distal
     * @apiParam {Number}  [vessel_diameter_proximal] Procudure: vessel_diameter_proximal
     * @apiParam {Object}  [product_ids] Product: Array Product ids
     * @apiParam {String="yes","no"} [procedural_success] Insight: procedural_success
     * @apiParam {String="1 month", "3 months", "6 months", "12 months"} [time_period] FollowUp: time_period
     * @apiParam {String='A', 'B', 'C', 'D'} [occlusion_status] FollowUp: occlusion_status
     * @apiParam {String} [order_by="id"]   Order By: id, ...
     * @apiParam {String="asc","desc"} [order="desc"]    Order
     * @apiParam {Number} [page=1]
     * @apiParam {Number} [per_page=15]
     */
    public function getCases(Request $request)
    {
    }

    /**
     * @api {post} /cases/v1/cases 1. Create Cases
     *
     * @apiName CreateCases
     * @apiGroup Cases
     *
     * @apiHeader {String} authorization="Bearer JWT" Authorization value.
     *
     * @apiParam {String} title Case Title
     * @apiParam {String} uuid  UUID V4
     * @apiParam {String} country   Country Code, e.g: UK, US
     * @apiParam {String} city  City Name
     * @apiParam {String="male","female"}       patient_gender  patient gender
     * @apiParam {String} hospital
     * @apiParam {String} physicians            Physician names, separate by comma
     * @apiParam {String} proctors              Proctor names, separate by comma
     * @apiParam {String} stryker_specialists   Stryker specialist names, separate by comma
     * @apiParam {Number} case_state_id         Case State Id
     * @apiParam {String} datetime              Datetime
     * @apiParam {Object} pre_case_media        Media Ids, e.g: [1,2,3]
     * @apiParam {String} patient_history       Patient history
     * @apiParam {Object} pharmacology          Pharmacology ids, e.g: [1,2]
     * @apiParam {String} [status=draft]        Case status
     *
     * @apiParamExample {json} Request-Example:
     * {
     *   "title": "Medical Case Test Title",
     *   "uuid": "ef928d02-d3c3-11eb-963a-5794de9e7c95",
     *   "country": "CN",
     *   "city": "Shanghai",
     *   "patient_gender": "male",
     *   "hospital": "8th hospital",
     *   "physicians": "physician name, physician name2",
     *   "proctors": "proctor name, proctor name2",
     *   "stryker_specialists": "stryker specialists",
     *   "case_state_id": 1,
     *   "datetime": "2019-01-01 00:00:00",
     *   "patient_history": "this is patient_history",
     * 	 "pre_case_media": [1,2,3],
     * 	 "pharmacology": [1,2],
     * 	 "status": "draft"
     * }
     */
    public function createCases(Request $request)
    {
    }
}
