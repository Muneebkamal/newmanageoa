<?php

use App\Http\Controllers\BuyerController;
use App\Http\Controllers\LeadsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\SystemManagerController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UploadController;
use App\Models\Shipping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PHPUnit\Event\Telemetry\System;
use Spatie\Permission\Models\Permission;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Auth::routes();
Route::get('/clear-cache', function () {
    \Artisan::call('cache:clear');
    \Artisan::call('config:clear');
    \Artisan::call('route:clear');
    \Artisan::call('view:clear');
    \Artisan::call('permission:cache-reset'); // Make sure spatie/laravel-permission is installed

    return "All caches cleared successfully!";
});

Route::middleware('auth')->group(function () {
    Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::get('/leads', [LeadsController::class, 'index'])->name('leads');
    Route::get('/leads-new', [LeadsController::class, 'index2'])->name('leads.new');
    Route::get('/leads-rejected', [LeadsController::class, 'rejectedLeads'])->name('leads.rejected');
    Route::get('/my-uploads', [UploadController::class, 'index'])->name('my.uploads');
    Route::post('/source-create', [UploadController::class, 'sourceStore'])->name('source.create');
    Route::post('/source-update/{id}', [UploadController::class, 'sourceUpdate'])->name('source.update');
    Route::post('/source-delete/{id}', [UploadController::class, 'sourceDelete'])->name('source.delete');
    Route::get('/sources', [UploadController::class, 'getSources'])->name('sources.fetch');
    Route::get('/sources/{id}', [UploadController::class, 'sourceFind'])->name('sources.id');
    Route::post('/upload-file', [UploadController::class, 'uploadFile'])->name('file.upload');
    Route::get('/get-templates', [UploadController::class, 'getTemplates'])->name('get.templates');
    Route::post('/get-file-data', [UploadController::class, 'getData'])->name('get-file-data');
    Route::post('/save-mapping-template', [UploadController::class, 'saveTemplate'])->name('saveTemplate');
    // web.php or api.php
    Route::get('/get-sinlge-template', [UploadController::class ,'getTemplateMapping'])->name('get-template-mapping');
    Route::get('/get-template-data', [UploadController::class ,'getTemplateData'])->name('getTemplateData');
    Route::get('/delete-template', [UploadController::class ,'deleteTemplate'])->name('delete-template');
    Route::post('/process-template', [UploadController::class, 'processTemplate']);




    Route::post('/lead-add', [LeadsController::class, 'store'])->name('lead.add');
    Route::get('/leads-fetch/{id}', [LeadsController::class, 'show'])->name('leads.fetch');
    Route::get('/leads-fetch-new/{id}', [LeadsController::class, 'getLatestLeads'])->name('leads.getLatestLeads');
    Route::get('/lead/{id}', [LeadsController::class, 'edit'])->name('lead.id');
    Route::post('/lead-update/{id}', [LeadsController::class, 'update'])->name('lead.update');
    Route::post('/lead-delete/{id}', [LeadsController::class, 'destroy'])->name('lead.delete');
    Route::post('/update-leads-source', [LeadsController::class, 'updateLeadsSoruces'])->name('updateLeadsSoruces');
    Route::post('/update-date', [LeadsController::class, 'updateDate'])->name('bulk.updateDate');
    Route::post('/delete-rows', [LeadsController::class, 'deleteRows'])->name('bulk.delete');
    Route::get('/get-single-lead', [LeadsController::class, 'getLeadData'])->name('getLeadData');
    Route::get('/dashboard/list', [LeadsController::class, 'ListView'])->name('ListView');
    Route::get('/get-top-shelf-leads', [LeadsController::class, 'getTopLeads'])->name('get.top.shelf.leads');
    Route::post('/update-bluk-tag', [LeadsController::class, 'updateBlukTags'])->name('updateBlukTags');
    Route::get('/table-view-data', [LeadsController::class, 'getTableViewData'])->name('getTableViewData');
    Route::post('/leads/reject', [LeadsController::class, 'rejectLead'])->name('rejectLead');



    Route::post('/tag-store', [TagController::class, 'store'])->name('tag.store');
    Route::get('/tags-fetch', [TagController::class, 'create'])->name('tags.fetch');
    Route::post('/tag-update/{id}', [TagController::class, 'update'])->name('tag.update');
    Route::post('/tag-delete/{id}', [TagController::class, 'destroy'])->name('tag.delete');
    Route::get('/tags-get', [TagController::class, 'tagsGet'])->name('tags.get');
    Route::post('/tag-checked/{id}', [TagController::class, 'tagsCheck'])->name('tag.checked');
    Route::get('/search-tags', [TagController::class, 'searchTags'])->name('search.tags');
    Route::post('/lead-tags-store/{id}', [TagController::class, 'leadTagsStore'])->name('lead.tags.store');
    Route::post('tag-unchecked', [TagController::class, 'tagsUncheck'])->name('tag.unchecked');
    //order and buy routes
    Route::get('list/buycostcalculator/{id?}', [OrderController::class, 'show'])->name('list.create');
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/create-order', [OrderController::class, 'create'])->name('orders.create');
    Route::get('orders/data', [OrderController::class, 'getOrders'])->name('orders.data');
    Route::get('get-order-items', [OrderController::class, 'getOrderItems'])->name('orders.getOrderItems');
    Route::get('get-order-items-dashboard', [OrderController::class, 'getOrderItemsDashboard'])->name('orders.getOrderItemsDashboard');
    Route::post('save-order-item', [OrderController::class, 'saveOrderItem'])->name('orders.save.items');
    Route::post('add-item-data', [OrderController::class, 'addItems'])->name('orders.add.items');
    Route::post('update-order-item', [OrderController::class, 'updateOrderItem'])->name('orders.update.items');
    Route::post('delete-order-item', [OrderController::class, 'deleteOrderItem'])->name('orders.delete.items');
    Route::get('orderattachments/list', [OrderController::class, 'attchmentList'])->name('orders.attachement.list');
    Route::post('orderattachments/upload', [OrderController::class, 'uplladFile'])->name('orders.attachement.upload');
    Route::post('save-line-item-tax', [OrderController::class, 'saveLineItemTax'])->name('orders.saveLineItemTax');
    Route::post('files/delete', [OrderController::class, 'deleteFile'])->name('orders.delete.file');
    Route::post('/files/update', [OrderController::class, 'updateFileInput'])->name('orders.update.file.inputs');
    Route::post('/save-orders-updated', [OrderController::class, 'saveOrdersUpdate'])->name('orders.update.inputs');
    Route::post('/save-order-data', [OrderController::class, 'saveOrderData'])->name('saveOrderData');
    Route::delete('/order/{id?}/delete', [OrderController::class, 'destroy'])->name('deleteOrder');
    Route::post('/order/{id?}/duplicate', [OrderController::class, 'duplicateOrder'])->name('duplicateOrder');
    Route::get('/order/{id?}', [OrderController::class, 'orderDetail'])->name('order-detail');
    Route::post('/save-event-logs', [OrderController::class, 'saveEvents'])->name('order-saveEvents');
    Route::delete('/eventlog/{id?}/delete', [OrderController::class, 'deleteEvent'])->name('order-deleteEvent');
    Route::get('/get-order/{orderId?}/eventlogs', [OrderController::class, 'getEventLogs'])->name('order-getEventLogs');
    Route::post('/download-orders', [OrderController::class, 'exportOrders'])->name('download.orders');
    Route::get('/get-orders-cheder', [OrderController::class, 'imprtOrder'])->name('imprtOrder');
    Route::post('/get-imprtORderNew', [OrderController::class, 'imprtORderNew'])->name('upload.file');
    Route::get('/get-toektn', [OrderController::class, 'fetchOrders'])->name('getTokenfile');

    Route::get('/shippingbatches', [ShippingController::class, 'index'])->name('shippingbatches.index');
    Route::post('/save-shipping-batch', [ShippingController::class, 'store'])->name('shippingbatches.store');
    Route::get('/get-shipping-batches', [ShippingController::class, 'getShippingBatches'])->name('shipping.batches');
    Route::post('/sav-shipping-event', [ShippingController::class, 'saveShippingEvent'])->name('shipping.saveShippingEvent');
    Route::delete('/shipping/event/{id?}/delete', [ShippingController::class, 'deleteShipping'])->name('deleteShipping');
    Route::get('get-shipping/{id?}/events', [ShippingController::class, 'getShippingEvetsAll'])->name('getShippingEvetsAll');
    Route::get('get/event/{id?}', [ShippingController::class, 'getSingleEvent'])->name('getSingleEvent');
    Route::get('/get-shipping', [ShippingController::class, 'getShipping'])->name('getShipping');
    Route::get('/shippingbatch/{id}/edit', [ShippingController::class, 'edit']);
    Route::get('/shippingbatch/{id}', [ShippingController::class, 'show']);
    Route::put('/update-shippingbatch/{id}', [ShippingController::class, 'update']);
    Route::delete('/delete-shippingbatch/{id}', [ShippingController::class, 'destroy']);
    Route::get('/buylist', [BuyerController::class, 'index'])->name('buylist.index');
    Route::post('/save-buylist', [BuyerController::class, 'store'])->name('save-buylist');
    Route::get('/get-buylists', [BuyerController::class, 'getBuylists'])->name('get-buylist');
    Route::get('buylists/{buylistId}/items', [BuyerController::class, 'getItems']);
    Route::get('buylists/items', [BuyerController::class, 'getItemsAll']);
    Route::get('/aprroved/buylist', [BuyerController::class, 'index2'])->name('buylist.index2');
    Route::post('rename-buylist', [BuyerController::class, 'remnameBuyList']);
    Route::post('delete-buylist', [BuyerController::class, 'deleteBuyList']);
    Route::post('save-buylist-data', [BuyerController::class, 'saveData']);
    Route::delete('/items/{id?}/delete', [BuyerController::class, 'deleteItem']);
    Route::post('/items/{itemId?}/reject', [BuyerController::class, 'rejectItem']);
    Route::post('/items/{itemId?}/duplicate', [BuyerController::class, 'duplicateItem']);
    Route::get('/items/{itemId?}/edit', [BuyerController::class, 'editItem']);
    Route::post('/item/{itemId?}/update', [BuyerController::class, 'updateItemData']);
    Route::post('/items/{itemId?}/undo-rejection', [BuyerController::class, 'undoRejection']);
    Route::post('/items/{itemId?}/move-copy', [BuyerController::class, 'moveOrCopyItem']);
    Route::post('/items/{itemId?}/create-order', [BuyerController::class, 'createSingleOrder']);
    Route::post('/delete/multiple/items', [BuyerController::class, 'deleteMultipleItems'])->name('orders.deleteMultiple');
    Route::post('/copy/move/multiple/items', [BuyerController::class, 'copyMoveMultiple'])->name('orders.moveCopyToBuylist');
    Route::post('/create/multiple/items/orders', [BuyerController::class, 'createMultipleItemsOrder'])->name('orders.createMultiple');
    Route::post('save-order-bundle-items', [OrderController::class, 'saveOrderBundleItems'])->name('orders.saveOrderBundleItems');
    Route::post('save-buylist-bundle-items', [BuyerController::class, 'saveBuyListBundleItems'])->name('orders.saveOrderBundleItems');
    Route::post('copyto/{id?}/buylist', [OrderController::class, 'copyItemToBuyList'])->name('copy.item.tobuylist');
    Route::post('update-shipping-event', [ShippingController::class, 'updateshippingEvent'])->name('updateshippingEvent');
    Route::post('update-issue-event/{id?}', [ShippingController::class, 'updateIssueEvent'])->name('updateIssueEvent');
    Route::get('get-leads-data', [LeadsController::class, 'getLeadsData'])->name('leads.data');
    //system manager index...
    Route::resource('emails', 'App\Http\Controllers\SystemManagerController');
    Route::get('locations', [SystemManagerController::class, 'lcationsIndex'])->name('locations.index');
    Route::post('locations/store', [SystemManagerController::class, 'locationStore'])->name('locations.store');
    Route::put('locations/update/{id?}', [SystemManagerController::class, 'locationUpdate'])->name('locations.update');
    Route::delete('locations/delete/{id?}', [SystemManagerController::class, 'locationDestroy'])->name('locations.destroy');
    Route::get('locations/list', [SystemManagerController::class, 'list'])->name('locations.list');
    Route::resource('/employees', 'App\Http\Controllers\EmployeeController');
    Route::get('/sync-lead-cron', 'App\Http\Controllers\EmployeeController@syncEmployeeLeadsCron');
    Route::get('/sync-lead-cron-email', 'App\Http\Controllers\EmployeeController@sendDailyEmailCron');
    Route::get('//get-employee-leads/{id?}', 'App\Http\Controllers\EmployeeController@getEmployeeLeadsNew');
    Route::get('settings', [SystemManagerController::class, 'settingsIndex'])->name('settings.index');
    Route::post('add-cashback-source', [SystemManagerController::class, 'storeCashBack'])->name('settings.storeCashBack');
    Route::get('/cashbacks-data', [SystemManagerController::class, 'getCashbacks'])->name('cashbacks.data');
    Route::get('/get-msku', [LeadsController::class, 'generateMSKU'])->name('generateMSKU.data');
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/filter', [ReportsController::class, 'filterReport'])->name('reports.filter');
    Route::get('/getProductByAsin/{asin?}', [LeadsController::class, 'getProductByAsin'])->name('getProductByAsin.data');
    Route::post('assign-work-order', [OrderController::class, 'assignedWorkOrder'])->name('assignedWorkOrder');
    Route::get('updateLineItemLeadIds', [BuyerController::class, 'updateLineItemLeadIds'])->name('updateLineItemLeadIds');
    Route::get('rejected-reasons/data', [SystemManagerController::class, 'reasonData'])->name('rejected-reasons.data');
    Route::post('add-rejected-reason', [SystemManagerController::class, 'storeReason']);
    Route::delete('delete-rejected-reason/{id}', [SystemManagerController::class, 'destroyReason']);
    Route::get('rejected-reasons/list', [SystemManagerController::class, 'Rasonlist']);
    Route::get('tags-data-list', [SystemManagerController::class, 'tagsList'])->name('tags.data.list');
    Route::post('add-tag-data', [SystemManagerController::class, 'addTag'])->name('tags.data.add');
    Route::delete('delete-tag-data/{id}', [SystemManagerController::class, 'destroyTags'])->name('tags.data.delete');





   



});

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
