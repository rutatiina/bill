<?php

Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

	Route::prefix('bills')->group(function () {

        //Route::get('summary', 'Rutatiina\Bill\Http\Controllers\DefaultController@summary');
        Route::post('export-to-excel', 'Rutatiina\Bill\Http\Controllers\DefaultController@exportToExcel');
        Route::post('{id}/approve', 'Rutatiina\Bill\Http\Controllers\DefaultController@approve');
        //Route::post('contact-bills', 'Rutatiina\Bill\Http\Controllers\Sales\ReceiptController@bills');
        Route::get('{id}/copy', 'Rutatiina\Bill\Http\Controllers\DefaultController@copy');

    });

    Route::resource('bills/settings', 'Rutatiina\Bill\Http\Controllers\SettingsController');
    Route::resource('bills', 'Rutatiina\Bill\Http\Controllers\DefaultController');

});


Route::group(['middleware' => ['web', 'auth', 'tenant', 'service.accounting']], function() {

    Route::prefix('recurring-bills')->group(function () {

        //Route::get('summary', 'Rutatiina\Bill\Http\Controllers\RecurringBillController@summary');
        Route::post('export-to-excel', 'Rutatiina\Bill\Http\Controllers\RecurringController@exportToExcel');
        Route::post('{id}/approve', 'Rutatiina\Bill\Http\Controllers\RecurringController@approve');
        //Route::post('contact-estimates', 'Rutatiina\Bill\Http\Controllers\Sales\ReceiptController@estimates');
        Route::get('{id}/copy', 'Rutatiina\Bill\Http\Controllers\RecurringController@copy');

    });

    Route::resource('recurring-bills/recurring/{txnId}/properties', 'Rutatiina\Bill\Http\Controllers\RecurringPropertiesController');
    Route::resource('recurring-bills/settings', 'Rutatiina\Bill\Http\Controllers\RecurringSettingController');
    Route::resource('recurring-bills', 'Rutatiina\Bill\Http\Controllers\RecurringController');

});

