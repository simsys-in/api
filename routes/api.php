<?php

use Illuminate\Http\Request;

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::apiResource('/products','ProductController');


//Route::apiResource('payroll_report', 'PayrollReportController');

Route::group(['prefix'=>'products'],function(){
    Route::apiResource('/{product}/review','ReviewController');
});

Route::post('payroll_report/attendance_register','PayrollReportController@attendance_register')->middleware('auth:api');
