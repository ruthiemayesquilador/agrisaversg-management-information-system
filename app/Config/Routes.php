<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'AuthController::index');
$routes->get('auth/login', 'AuthController::login');
$routes->post('auth/login', 'AuthController::login');
$routes->get('auth/logout', 'AuthController::logout');

$routes->get('profile', 'ProfileController::index');
$routes->post('profile/update', 'ProfileController::update');

$routes->get('dashboard', 'Home::index');
$routes->get('dashboard/upc/template', 'Home::downloadUpcTemplate');
$routes->post('dashboard/upc/import', 'Home::importUpcMap');
$routes->post('dashboard/upc/add-single', 'Home::addSingleUpcMap');
$routes->get('dashboard/upc/export', 'Home::exportUpcMapCsv');
$routes->get('dashboard/upc/export/pdf', 'Home::exportUpcMapPdf');
$routes->get('dashboard/upc/print', 'Home::printUpcLabels');
$routes->get('dashboard/upc/stickers/pdf', 'Home::exportUpcStickersPdf');
$routes->post('dashboard/users/add', 'Home::addUser');
$routes->post('dashboard/users/update', 'Home::updateUser');
$routes->get('dashboard/users/delete/(:num)', 'Home::deleteUser/$1');
$routes->post('dashboard/branches/add', 'Home::addBranch');
$routes->post('dashboard/branches/update', 'Home::updateBranch');
$routes->get('dashboard/branches/delete/(:num)', 'Home::deleteBranch/$1');

$routes->get('products', 'ProductsController::index');
$routes->get('products/getProductsJson', 'ProductsController::getProductsJson');
$routes->post('products/store', 'ProductsController::store');
$routes->post('products/update', 'ProductsController::update');
$routes->post('products/delete', 'ProductsController::delete');
$routes->post('products/delete-all', 'ProductsController::deleteAll');
$routes->post('products/delete-selected', 'ProductsController::deleteSelected');
$routes->post('products/import', 'ProductsController::import');
$routes->get('products/template', 'ProductsController::downloadTemplate');
$routes->get('products/export/pdf', 'ProductsController::exportPdf');
$routes->get('products/export/excel', 'ProductsController::exportExcel');

$routes->get('categories', 'CategoriesController::index');
$routes->post('categories/store', 'CategoriesController::store');
$routes->post('categories/update', 'CategoriesController::update');
$routes->post('categories/delete', 'CategoriesController::delete');

$routes->get('sales', 'AccountingController::index');
$routes->get('accounting/export/pdf', 'AccountingController::exportPdf');
$routes->get('accounting/export/excel', 'AccountingController::exportExcel');
$routes->get('accounting/payment-receipt/(:segment)', 'AccountingController::paymentReceipt/$1');
$routes->post('accounting/storeSale', 'AccountingController::storeSale');
$routes->post('accounting/updateSale', 'AccountingController::updateSale');
$routes->post('accounting/collectPayment', 'AccountingController::collectPayment');
$routes->post('accounting/deleteSale', 'AccountingController::deleteSale');

$routes->post('accounting/storeExpense', 'AccountingController::storeExpense');
$routes->post('accounting/updateExpense', 'AccountingController::updateExpense');
$routes->post('accounting/deleteExpense', 'AccountingController::deleteExpense');

$routes->get('stocks', 'StocksController::index');
$routes->post('stocks/store', 'StocksController::store');
$routes->post('stocks/update', 'StocksController::update');
$routes->post('stocks/delete', 'StocksController::delete');
$routes->post('stocks/import', 'StocksController::import');
$routes->get('stocks/backfill-images', 'StocksController::backfillImagesBySku');
$routes->get('stocks/template', 'StocksController::downloadTemplate');
$routes->get('stocks/export/csv', 'StocksController::exportCsv');
$routes->get('stocks/export/pdf', 'StocksController::exportPdf');

// Stock Requests
$routes->get('stocks/requests', 'StocksController::requests');
$routes->post('stocks/requestStock', 'StocksController::requestStock');
$routes->post('stocks/approveRequest', 'StocksController::approveRequest');
$routes->post('stocks/fulfillRequest', 'StocksController::fulfillRequest');
$routes->post('stocks/cancelRequest', 'StocksController::cancelRequest');

$routes->get('suppliers', 'SuppliersController::index');
$routes->post('suppliers/store', 'SuppliersController::store');
$routes->post('suppliers/update', 'SuppliersController::update');
$routes->post('suppliers/delete', 'SuppliersController::delete');
$routes->get('suppliers/admin-transfers', 'SuppliersController::adminTransfers');
$routes->get('suppliers/transfer-products', 'SuppliersController::transferProducts');
$routes->get('suppliers/get-products-by-branch', 'SuppliersController::getProductsByBranch');
$routes->get('suppliers/get-recipients-by-branch', 'SuppliersController::getRecipientsByBranch');
$routes->post('suppliers/store-transfer', 'SuppliersController::storeTransfer');
$routes->post('suppliers/store-bulk-transfer', 'SuppliersController::storeBulkTransfer');
$routes->get('suppliers/get-transfers', 'SuppliersController::getTransfers');
$routes->post('suppliers/update-transfer-status', 'SuppliersController::updateTransferStatus');

$routes->get('reports', 'ReportsController::index');
$routes->get('reports/sales-data', 'ReportsController::salesData');
$routes->get('reports/sales', 'ReportsController::salesReport');
$routes->get('reports/stocks', 'ReportsController::stocksReport'); 

$routes->get('history', 'HistoryController::index');

$routes->get('pos', 'PosController::index');
$routes->get('pos/search', 'PosController::search');
$routes->get('pos/scan-lookup', 'PosController::scanLookup');
$routes->get('pos/sale-items', 'PosController::saleItemsLookup');
$routes->get('pos/printers', 'PosController::printers');
$routes->post('pos/checkout', 'PosController::checkout');
$routes->post('pos/process-return', 'PosController::processReturn');
$routes->post('pos/void-sale', 'PosController::voidSale');
$routes->post('pos/directPrint', 'PosController::directPrint');
