<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();

// Set to false in a production environment
$app['debug'] = true;

$users = array(
	'00001' => array(
		'username' => 'mezod',
		'registered' => '31/08/2014',
		'numberKanbans' => '32',
		'numberTasks' => '654',
		'numberCompletedTasks' => '321'
	),
	'00002' => array(
		'username' => 'CowboyCoder',
		'registered' => '01/09/2014',
		'numberKanbans' => '12',
		'numberTasks' => '65',
		'numberCompletedTasks' => '21'
	)

);

$app->get('/', function() use ($users){
    return json_encode($users);
});

$app->get('/{user_id}', function (Silex\Application $app, $user_id) use ($users){

	if (!isset($users[$user_id])){
		$app->abort(404, "User with id {$user_id} does not exist.");
	}
	return json_encode($users[$user_id]);
});

$app->run();
