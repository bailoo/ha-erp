<?php

/* display all errors */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/* Require Slim and plugins */
require 'vendor/autoload.php';
require 'plugins/NotORM.php';

/* Register autoloader and instantiate Slim */
$app = new \Slim\Slim();
$app->add(new \CorsSlim\CorsSlim());

/* Database Configuration */
#$dbhost   = 'ganga.cxcnagtujnjl.ap-south-1.rds.amazonaws.com';
$dbhost   = 'localhost';
$dbuser   = 'haerp';
$dbpass   = 'neon04$HAERP';
$dbname   = 'haerp';
$dbmethod = 'mysql:host='.$dbhost.';dbname='.$dbname;
$pdo = new PDO($dbmethod, $dbuser, $dbpass);
$db = new NotORM($pdo);


/*****************
**  Routes	**
******************/

// Home route
$app->get('/', function(){
	echo "HotelArmy ERP API v0.1<br/>\n";
	echo "<br/>\n";	
	echo "/users/:mobile <br/>\n";
	echo "<br/>\n";	
	echo "/products/ <br/>\n";
	echo "<br/>\n";	
	echo "/products/:name <br/>\n";
	echo "<br/>\n";	
	echo "/products/catid/:catid <br/>\n";
	echo "<br/>\n";	
	echo "/products/productid/:productid <br/>\n";
	echo "<br/>\n";	
	echo "/reports <br/>\n";
	echo "<br/>\n";	
	echo "/categories <br/>\n";
	echo "<br/>\n";	
});

/*****************
**  User	**
******************/

// Get userid
$app->get('/users/:mobile', function($mobile) use ($app, $db) {
    $app->response()->headers->set("Content-Type", "application/json");
    $user = $db->user()->where('mobile', $mobile);
    if($data = $user->fetch()){
        $app->response()->setStatus(200);
        echo json_encode(array(
            'id' => $data['id'],
            'mobile' => $data['mobile']
        ));
    }
    else{
        $app->response()->setStatus(204);
        echo json_encode(array(
            'status' => false,
            'message' => "mobile $mobile does not exist"
        ));
    }
});

// Add a new user 
$app->post('/users', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $app->request()->post();
    $result = $db->user->insert($user);
    echo json_encode(array('id' => $result['id']));
});

// Update a user 
$app->put('/users/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->user()->where("id", $id);
    if ($user->fetch()) {
        $post = $app->request()->put();
        $result = $user->update($post);
        echo json_encode(array(
            "status" => (bool)$result,
            "message" => "user updated successfully"
            ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "user id $id does not exist"
        ));
    }
});

// Remove a user
$app->delete('/users/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $user = $db->user()->where('id', $id);
    if($user->fetch()){
        $result = $user->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "user deleted successfully"
        ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "user id $id does not exist"
        ));
    }
});


/*****************
**  Products	**
******************/

// Get all products of a user
$app->get('/products', function () use($app, $db) {
    $app->response()->header("Content-Type", "application/json");
	$userid = $app->request->headers->get('User-Id');
	if ($userid == '') {
        echo json_encode(array(
            "status" => false,
            "message" => "set custom header User-Id"
        ));
	}
	else {
    	$user = array();
    	foreach ($db->item()->where('userid', $userid) as $item) {
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
        	echo json_encode(array(
            	"status" => false,
            	"message" => "user id $userid does not exist"
        	));
		}
		else {
			echo json_encode($prod);
	    }
	}
});

// Add a new item
$app->post('/item', function() use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $app->request()->post();
    $result = $db->item->insert($item);
    echo json_encode(array('id' => $result['id']));
});

// Update an item
$app->put('/item/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $db->item()->where("id", $id);
    if ($item->fetch()) {
        $post = $app->request()->put();
        $result = $item->update($post);
        echo json_encode(array(
            "status" => (bool)$result,
            "message" => "Order updated successfully"
            ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "Order id $id does not exist"
        ));
    }
});

// Remove an item 
$app->delete('/item/:id', function($id) use($app, $db){
    $app->response()->header("Content-Type", "application/json");
    $item = $db->item()->where('id', $id);
    if($item->fetch()){
        $result = $item->delete();
        echo json_encode(array(
            "status" => true,
            "message" => "Order deleted successfully"
        ));
    }
    else{
        echo json_encode(array(
            "status" => false,
            "message" => "Order id $id does not exist"
        ));
    }
});

/* Run the application */
$app->run();
