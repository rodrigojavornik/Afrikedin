<?php

namespace FeedzRecoloca\Controllers;

use DateTime;
use Exception;
use FeedzRecoloca\Models\City;
use FeedzRecoloca\Models\JobsCompanies;
use FeedzRecoloca\Models\Occupation;
use FeedzRecoloca\Models\Resume;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;

class JobController
{

    private $app;
    private $view;

    public function __construct($app)
    {
        $this->app = $app;
        $this->view = $app->view;
    }

    /**
     * Show jobs list
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Twig
     */
    public function showJobs(Request $request, Response $response, array $args)
    {
        $user = $this->app->userSession;
        $jobsCompanies = JobsCompanies::all();
        
        return $this->view->render($response, 'employers/jobList.twig', [
            'user' => $user,
            'jobsCompanies' => $jobsCompanies
        ]);
    }
}
