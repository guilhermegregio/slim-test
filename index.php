<?php
require_once 'models/Users.php';
require_once 'models/Products.php';
require 'vendor/autoload.php';

$app = new \Slim\Slim(array('templates.path' => 'templates'));

function verifyRequiredParams($required_fields) {
    $error = false;
    $error_fields = "";
    $request_params = array();
    $request_params = $_REQUEST;
    // Handling PUT request params
    if ($_SERVER['REQUEST_METHOD'] == 'PUT') {
        $app = \Slim\Slim::getInstance();
		$body = json_decode($app->request()->getBody(), true);
		foreach ($body as $key => $value) {
			$request_params[$key] = $value;
		}
    }
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $app = \Slim\Slim::getInstance();
		$body = json_decode($app->request()->getBody(), true);
		foreach ($body as $key => $value) {
			$request_params[$key] = $value;
		}
    }

    foreach ($required_fields as $field) {
        if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }
 
    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $response = array();
        $app = \Slim\Slim::getInstance();

        $response["error"] = true;
        $response["message"] = 'Required field(s) ' . substr($error_fields, 0, -2) . ' is missing or empty';
		$app->render('default.php',$response,400);
        $app->stop();
    }
}

$app->get('/', function() use ($app) {
	$data = array(
		'status'=>'200'
	);

	$app->render('default.php',$data,200);
});

$app->group('/products',function() use ($app){
	//list
	//delete id
	//update id
	//ADD
	$app->post('/upload', function() use ($app){
		if (!isset($_FILES['file'])) {
			echo "No files uploaded!!";
			return;
		}

		$file = $_FILES['file'];

		if ($file['error'] !== 0) {
			echo "Error no upload!!";
			return;
		}

		$name = md5($file['tmp_name']) . '-' . $file['name'];

		if(move_uploaded_file($file['tmp_name'], '../images/products/' . $name) === true){
			$image = array('url' => 'images/products/' . $name, 'name' => $file['name']);
		}

		$response = array();
		$response['image'] = $image;

		$app->render('default.php', $response, 200);
	});
});

$app->group('/users', function() use ($app){
	//login
 
  //rota para a home
  $app->get('/',function() use ($app){
	$users = new Users();

	$data = array(
		'login'=>$users->checkLogin('guilherme', '123')
	);

	//$users->createUser('guilherme', '1234');

	$app->render('default.php',$data,200);
  });

  //rota para login
  $app->post('/login/', function() use ($app){
	  $response = array();
	  $users = new Users();

	  $body = json_decode($app->request->getBody(),true);

	  $username = $body['username'];
	  $password = $body['password'];

	  $response['logged'] = $users->checkLogin($username, $password);

	  if($response['logged']) {
		  $app->render('default.php', $response, 200);
	  } else {
		  $app->render(404);
	  }
  });
});

$app->run();
?>
