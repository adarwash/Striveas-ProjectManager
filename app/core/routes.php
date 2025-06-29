// Supplier routes
$router->addRoute('/suppliers', 'Suppliers@index');
$router->addRoute('/suppliers/add', 'Suppliers@add');
$router->addRoute('/suppliers/create', 'Suppliers@create');
$router->addRoute('/suppliers/edit/:id', 'Suppliers@edit');
$router->addRoute('/suppliers/update', 'Suppliers@update');
$router->addRoute('/suppliers/view', 'Suppliers@viewDetail');
$router->addRoute('/suppliers/view/:id', 'Suppliers@viewDetail');
$router->addRoute('/suppliers/delete', 'Suppliers@delete');
$router->addRoute('/suppliers/deleteAjax', 'Suppliers@deleteAjax');

// Invoice routes
$router->addRoute('/invoices', 'Invoices@index');
$router->addRoute('/invoices/add', 'Invoices@add');
$router->addRoute('/invoices/create', 'Invoices@create');
$router->addRoute('/invoices/edit/:id', 'Invoices@edit');
$router->addRoute('/invoices/update', 'Invoices@update');
$router->addRoute('/invoices/view/:id', 'Invoices@viewDetail');
$router->addRoute('/invoices/delete', 'Invoices@delete');
$router->addRoute('/invoices/deleteAjax', 'Invoices@deleteAjax');
$router->addRoute('/invoices/markAsPaid', 'Invoices@markAsPaid');
$router->addRoute('/invoices/markAsPaidAjax', 'Invoices@markAsPaidAjax'); 