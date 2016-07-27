<?php

/* display all errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* Require Slim */
require 'vendor/autoload.php';

/* Register autoloader and instantiate Slim */
$app = new \Slim\Slim();
$app->add(new \CorsSlim\CorsSlim());

/* database */
require 'settings.php';
$pdo = new PDO(DB_METHOD.':host='.DB_HOST.';dbname='.DB_NAME, DB_USER, DB_PASS);

/* plugin notORM */
require 'plugins/NotORM.php';
$db = new NotORM($pdo);

/*****************
**  Functions	**
******************/

function echoJson($truth, $payload) {
	echo json_encode(array(
		'success' => $truth,
		'payload' => $payload
	));
}


/*****************
**  Routes	**
******************/

// Home route
$app->get('/', function(){
	echo "HotelArmy ERP API v0.1<br/>\n";
	echo "<br/>\n";	
	echo "/users/:mobile <br/>\n";
	echo "<br/>\n";	
	echo "/products/:userid <br/>\n";
	echo "<br/>\n";	
	echo "/products/:userid/:name <br/>\n";
	echo "<br/>\n";	
	echo "/products/catid/:userid/:catid <br/>\n";
	echo "<br/>\n";	
	echo "/products/productid/:userid/:productid <br/>\n";
	echo "<br/>\n";	
	echo "/reports/:userid <br/>\n";
	echo "<br/>\n";	
	echo "/categories <br/>\n";
	echo "<br/>\n";	
});

/*****************
**  Users		**
******************/

// Get userid
$app->get('/users/:mobile', function($mobile) use ($app, $db) {
    $app->response()->headers->set("Content-Type", "application/json");
    $user = $db->user()->where('mobile', $mobile);
    if($data = $user->fetch()){
        echoJson(TRUE, array(
            'id' => $data['id'],
            'mobile' => $data['mobile']
        ));
    }
    else{
        $app->response()->setStatus(204);
    }
});

// Add a new user 
$app->post('/users', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $app->request()->post();
    $result = $db->user->insert($user);
    echoJson(TRUE, array('id' => $result['id']));
});

// Update a user 
$app->put('/users/:userid', function($userid) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->user()->where("id", $userid);
    if ($user->fetch()) {
        $post = $app->request()->put();
        $result = $user->update($post);
        echoJson(TRUE, array(
            "status" => (bool)$result,
            "message" => "user updated successfully"
            ));
    }
    else{
        $app->response()->setStatus(204);
    }
});

// Remove a user
$app->delete('/users/:userid', function($userid) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->user()->where('id', $userid);
    if($user->fetch()){
        $result = $user->delete();
        echoJson(TRUE, array(
            "status" => true,
            "message" => "user deleted successfully"
        ));
    }
    else{
        $app->response()->setStatus(204);
    }
});


/*****************
**  Products	**
******************/

// Get all products of a user
$app->get('/products/:userid', function ($userid) use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	if ($userid == '') {
        $app->response()->setStatus(204);
	}
	else {
		$prod = array();
    	foreach ($db->item()->where('userid', $userid) as $item) {
			$prod[$item['id']] = array(
            	'id' => $item['id'],			/* item ID */
	            'userid' => $item['userid'],
            	'name' => $item['name'],
            	'details' => $item['details'],
            	'catid' => $item['catid'],
	            'company' => $item['company'],
	            'price' => $item['price'],
	            'qty' => $item['qty'],
	            'location' => $item['location'],
	            'date' => $item['date'],
	            'productid' => $item['productid'],
	            'brand' => $item['brand'],
	            'minlimit' => $item['minlimit']
	        );
		}
		if (!isset($prod)) {
        	$app->response()->setStatus(204);
		}
		else {
			echoJson(TRUE, $prod);
	    }
	}
});

// Search product name
$app->get('/products/:userid/:name', function ($userid, $name) use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	if ($userid == '') {
        $app->response()->setStatus(204);
	}
	else {
    	foreach ($db->item()->where('userid', $userid)->and('name LIKE ?', "%$name%") as $item) {
			$prod[] = array(
            	'id' => $item['id'],			/* item ID */
	            'userid' => $item['userid'],
            	'name' => $item['name'],
            	'details' => $item['details'],
            	'catid' => $item['catid'],
	            'company' => $item['company'],
	            'price' => $item['price'],
	            'qty' => $item['qty'],
	            'location' => $item['location'],
	            'date' => $item['date'],
	            'productid' => $item['productid'],
	            'brand' => $item['brand'],
	            'minlimit' => $item['minlimit']
	        );
		}
		if (!isset($prod)) {
        	$app->response()->setStatus(204);
		}
		else {
			echoJson(TRUE, $prod);
	    }
	}
});


// get all products in category
$app->get('/products/catid/:userid/:catid', function ($userid, $catid) use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	if ($userid == '') {
        $app->response()->setStatus(204);
	}
	else {
    	foreach ($db->item()->where('userid', $userid)->and('catid', $catid) as $item) {
			$prod[] = array(
            	'id' => $item['id'],			/* item ID */
	            'userid' => $item['userid'],
            	'name' => $item['name'],
            	'details' => $item['details'],
            	'catid' => $item['catid'],
	            'company' => $item['company'],
	            'price' => $item['price'],
	            'qty' => $item['qty'],
	            'location' => $item['location'],
	            'date' => $item['date'],
	            'productid' => $item['productid'],
	            'brand' => $item['brand'],
	            'minlimit' => $item['minlimit']
	        );
		}
		if (!isset($prod)) {
        	$app->response()->setStatus(204);
		}
		else {
			echoJson(TRUE, $prod);
	    }
	}
});

// get product by id
// userid not required 
$app->get('/products/productid/:userid/:productid', function ($userid, $productid) use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	/* 
	$userid = $app->request->headers->get('User-Id');
	if ($userid == '') {
        echoJson(FALSE, array(
            "status" => false,
            "message" => "set custom header User-Id"
        ));
	}
	else {
    	foreach ($db->item()->where('userid', $userid)->and('id', $productid) as $item) {
	*/
    	foreach ($db->item()->where('id', $productid) as $item) {
			$prod[] = array(
            	'id' => $item['id'],			/* item ID */
	            'userid' => $item['userid'],
            	'name' => $item['name'],
            	'details' => $item['details'],
            	'catid' => $item['catid'],
	            'company' => $item['company'],
	            'price' => $item['price'],
	            'qty' => $item['qty'],
	            'location' => $item['location'],
	            'date' => $item['date'],
	            'productid' => $item['productid'],
	            'brand' => $item['brand'],
	            'minlimit' => $item['minlimit']
	        );
		}
		if (!isset($prod)) {
        	$app->response()->setStatus(204);
		}
		else {
			echoJson(TRUE, $prod);
	    }
	//}
});


// Add a new item
$app->post('/products/productid', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $app->request()->post();
    $result = $db->item->insert($item);
    echoJson(TRUE, array('id' => $result['id']));
});

// Update an item
$app->put('/products/productid/:id', function($productid) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $db->item()->where("id", $productid);
    if ($item->fetch()) {
        $post = $app->request()->put();
        $result = $item->update($post);
        echoJson(TRUE, array(
            "status" => (bool)$result,
            "message" => "See status"
            ));
    }
    else{
        $app->response()->setStatus(204);
    }
});

// Remove an item 
$app->delete('/products/productid/:productid', function($productid) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $db->item()->where('id', $productid);
    if($item->fetch()){
        $result = $item->delete();
        echoJson(TRUE, array(
            "status" => true,
            "message" => "Product deleted successfully"
        ));
    }
    else{
        $app->response()->setStatus(204);
    }
});

// get reports category id and total product count in it
$app->get('/reports/:userid', function ($userid) use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	if ($db->user()->where('id', $userid)->fetch()) {		/* userid exists */
    	foreach ($db->category() as $cat) {
			$prod[] = array(
        		'catid' => $cat['id'],						/* category ID */
	    		'count' => $db->item()->where('catid', $cat['id'])->and('userid', $userid)->count()
			);
		}
		if (!isset($prod)) {
        	$app->response()->setStatus(204);
		}
		else {
			echoJson(TRUE, $prod);
		}
	}
	else {													/* userid does not exist */
        $app->response()->setStatus(204);
	}	
});

// get categories
$app->get('/categories', function () use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
    foreach ($db->category() as $cat) {
		$prod[] = array(
        	'id' => $cat['id'],			/* category ID */
	    	'name' => $cat['name']
		);
	}
	if (!isset($prod)) {
        $app->response()->setStatus(204);
	}
	else {
		echoJson(TRUE, $prod);
	}
});


/* Run the application */
$app->run();
