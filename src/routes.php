<?php

use \Slim\Http\Request;
use \Slim\Http\Response;

use FeedzRecoloca\Controllers\AuthController;
use FeedzRecoloca\Controllers\JobController;
use FeedzRecoloca\Controllers\ReportController;
use FeedzRecoloca\Controllers\ResumeController;
use FeedzRecoloca\Models\Resume;

// Routes
$app->get('/', function (Request $request, Response $response, $args) use ($app) {
    return (new ResumeController($this))->showList($request, $response, $args);
});

$app->get('/erro', function (Request $request, Response $response, $args) use ($app) {
    return $this->view->render($response, 'error.twig');
});

$app->get('/vagas', function (Request $request, Response $response, $args) use ($app) {
    return (new JobController($this))->showJobs($request, $response, $args);
});


$app->get('/perfil/{id}', function (Request $request, Response $response, $args) use ($app) {
    return (new ResumeController($this))->showResume($request, $response, $args);
});

$app->get('/perfil', function (Request $request, Response $response, $args) use ($app) {
    return (new ResumeController($this))->showPerfil($request, $response, $args);
})->add($auth);

$app->get('/login', function (Request $request, Response $response, $args) use ($app) {
    $userSession = $this->userSession;
    if (!is_null($userSession)) {
        if ($userSession->linkedin_token_expires_in > (new DateTime('now'))) {
            return $response->withRedirect('/perfil');
        }
    }

    $authController = new AuthController();
    return $response->withRedirect($authController->getUrlAuthorizationCode());
});

$app->get('/login/linkedin', function (Request $request, Response $response, $args) use ($app) {
    return (new AuthController())->auth($request, $response, $args);
});

$app->get('/logout[/]', function (Request $request, Response $response, $args) use ($app) {
    unset($_SESSION['user']);
    return $response->withRedirect('/');
});


$app->post('/perfil', function (Request $request, Response $response, $args) use ($app) {
    return (new ResumeController($this))->update($request, $response, $args);
})->add($auth);;

$app->post('/desabilitar', function (Request $request, Response $response, $args) use ($app) {
    return (new ResumeController($this))->delete($request, $response, $args);
})->add($auth);

$app->get('/stats[/]', function (Request $request, Response $response, $args) use ($app) {
    $user = $this->userSession;
    if (!in_array($user->id, [1, 2, 3, 5, 552, 1432])) {
        return $response->withRedirect('/');
    }

    return (new ResumeController($this))->showStats($request, $response, $args);
});

$app->get('/relatorio[/]', function (Request $request, Response $response, $args) use ($app) {
    return (new ReportController($this))->generateAllReport($request, $response, $args);
});


$app->get('/testes[/]', function (Request $request, Response $response, $args) use ($app) {
    try {
        $resumesCollection = Resume::whereNull('description_new')->orWhere('description_new', '')->get();
	echo "<textarea>";
        foreach ($resumesCollection as $resume) {
		echo "UPDATE resumes set description_new = $resume->description WHERE id = $resume->id;";
		echo PHP_EOL . PHP_EOL;
	}
	echo "</textarea>";

        echo 'Operação finalizada com sucesso';
        
    } catch (Exception $e) {
        echo "Deu merda: ". $e->getMessage();
    }
});
