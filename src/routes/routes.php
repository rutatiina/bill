<?php

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

	Route::prefix('bills')->group(function () {

        //Route::get('summary', 'Rutatiina\Bill\Http\Controllers\BillController@summary');
        Route::post('export-to-excel', 'Rutatiina\Bill\Http\Controllers\BillController@exportToExcel');
        Route::post('{id}/approve', 'Rutatiina\Bill\Http\Controllers\BillController@approve');
        //Route::post('contact-bills', 'Rutatiina\Bill\Http\Controllers\Sales\ReceiptController@bills');
        Route::get('{id}/copy', 'Rutatiina\Bill\Http\Controllers\BillController@copy');

    });

    Route::resource('bills/settings', 'Rutatiina\Bill\Http\Controllers\BillSettingsController');
    Route::resource('bills', 'Rutatiina\Bill\Http\Controllers\BillController');

});


Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

    Route::prefix('recurring-bills')->group(function () {

        //Route::get('summary', 'Rutatiina\Bill\Http\Controllers\RecurringBillController@summary');
        Route::post('export-to-excel', 'Rutatiina\Bill\Http\Controllers\RecurringBillController@exportToExcel');
        Route::post('{id}/activate', 'Rutatiina\Bill\Http\Controllers\RecurringBillController@activate');
        //Route::post('contact-estimates', 'Rutatiina\Bill\Http\Controllers\Sales\ReceiptController@estimates');
        Route::get('{id}/copy', 'Rutatiina\Bill\Http\Controllers\RecurringBillController@copy');

    });

    Route::resource('recurring-bills/recurring/{txnId}/properties', 'Rutatiina\Bill\Http\Controllers\RecurringBillPropertiesController');
    Route::resource('recurring-bills/settings', 'Rutatiina\Bill\Http\Controllers\RecurringBillSettingController');
    Route::resource('recurring-bills', 'Rutatiina\Bill\Http\Controllers\RecurringBillController');

});

