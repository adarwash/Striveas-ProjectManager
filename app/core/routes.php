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

// Ticket routes
$router->addRoute('/tickets', 'Tickets@index');
$router->addRoute('/tickets/create', 'Tickets@create');
$router->addRoute('/tickets/store', 'Tickets@store');
$router->addRoute('/tickets/view/:id', 'Tickets@show');
$router->addRoute('/tickets/show/:id', 'Tickets@show');
$router->addRoute('/tickets/edit/:id', 'Tickets@edit');
$router->addRoute('/tickets/update', 'Tickets@update');
$router->addRoute('/tickets/delete', 'Tickets@delete');
$router->addRoute('/tickets/assign/:id', 'Tickets@assign');
$router->addRoute('/tickets/assign', 'Tickets@assign');
$router->addRoute('/tickets/close/:id', 'Tickets@close');
$router->addRoute('/tickets/addMessage/:id', 'Tickets@addMessage');
$router->addRoute('/tickets/addMessage', 'Tickets@addMessage');
$router->addRoute('/tickets/updateStatus', 'Tickets@updateStatus');
$router->addRoute('/tickets/fragment/:id', 'Tickets@fragment');
$router->addRoute('/tickets/archive/:id', 'Tickets@archive');
$router->addRoute('/tickets/kickoffAttachments/:id', 'Tickets@kickoffAttachments');

 