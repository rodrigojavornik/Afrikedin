<?php

namespace FeedzRecoloca\Controllers;

use DateTime;
use Exception;
use FeedzRecoloca\Models\City;
use FeedzRecoloca\Models\Occupation;
use FeedzRecoloca\Models\Resume;
use GuzzleHttp\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Views\Twig;

class ResumeController
{

    private $app;
    private $view;

    public function __construct($app)
    {
        $this->app = $app;
        $this->view = $app->view;
    }

    /**
     * Show edit Resume view
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Twig
     */
    public function showPerfil(Request $request, Response $response, array $args)
    {
        $user = $this->app->userSession;

        $resume = Resume::get($user->id);

        return $this->view->render($response, 'candidates/profile.twig', [
            'user' => $user,
            'resume' => $resume,
            'cities' => City::getData(),
            'occupations' => Occupation::all()->toArray()
        ]);
    }

    /**
     * Show new Resume view
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Twig
     */
    public function showList(Request $request, Response $response, array $args)
    {
        $user = $this->app->userSession;
        $occupation = $request->getParam('area') != '' ? $request->getParam('area') : null;
        $city = $request->getParam('cidade') != '' ? $request->getParam('cidade') : null;
        $page = $request->getParam('page') != '' ? $request->getParam('page') : null;

        $resumes = Resume::getList($occupation, $city, $page);

        return $this->view->render($response, 'candidates/listCandidate.twig', [
            'user' => $user,
            'cities' => City::getData(),
            'occupations' => Occupation::all()->toArray(),
            'resumes' => $resumes['data'],
            'currentPage' => $resumes['currentPage'],
            'maxPage' => $resumes['maxPage'],
            'citySearch' => $city,
            'occupationSearch' => $occupation
        ]);
    }

    /**
     * Show Resume view by id
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function showResume(Request $request, Response $response, array $args)
    {
        $user = $this->app->userSession;
        $resume = Resume::get($args['id']);

        if ($resume->status != 1) {
            return $response->withRedirect('/');
        }

        return $this->view->render($response, 'candidates/showCandidate.twig', [
            'user' => $user,
            'resume' => $resume
        ]);
    }

    /**
     * Update a Resume
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return boolean
     */
    public function update(Request $request, Response $response, array $args)
    {
        try {
            $user = $this->app->userSession;
            
            $resume = Resume::get($user->id);

            $resume->name = strip_tags(trim($request->getParam('name')));
            $resume->email = strip_tags(trim($request->getParam('email')));
            $resume->linkedin_url = strip_tags(trim($request->getParam('linkedinUrl')));
            $resume->id_occupation_area = strip_tags(trim($request->getParam('occupationArea')));
            $resume->id_city = strip_tags(trim($request->getParam('city')));
            $resume->remote_work = $request->getParam('remoteWork') == 'on' ? true : false;
            $resume->phone = strip_tags(trim($request->getParam('phone')));
            $resume->skills = strip_tags(trim($request->getParam('skills')));
            $resume->description = $request->getParam('description');
            $resume->status = $request->getParam('status') == 'on' ? true : false;
            
            $resume->save();

            $user->name = $resume->name;
            $user->save();

            $_SESSION['user'] = serialize($user);

            return $response->withRedirect('/');
        } catch (Exception $e) {
            return $response->withRedirect('/erro');
        }
    }

    /**
     * Delete a Resume
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return boolean
     */
    public function delete(Request $request, Response $response, array $args)
    {
        try {
            $user = $this->app->userSession;
            $resume = Resume::get($user->id);            

            return $resume->delete();
        } catch (Exception $e) {
            return $response->withRedirect('/erro');
        }
    }

    public function getLinkedinData()
    {
        $user = $this->app->userSession;
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        // $response = $client->request('GET', "/v2/emailAddress?q=members&projection=(elements*(handle~))", [
        $response = $client->request('GET', "/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))", [
            'headers' => [
                "Authorization" => "Bearer " . $user->linkedin_access_token,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        $name = $data['firstName']['localized']['pt_BR'] . ' ' .$data['lastName']['localized']['pt_BR'];
        $image = $data['profilePicture']['displayImage~']['elements'][1]['identifiers']['0']['identifier'];

        return [$name, $image];
    }

    public function getLinkedinEmail()
    {
        $user = $this->app->userSession;
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('GET', "/v2/emailAddress?q=members&projection=(elements*(handle~))", [
            'headers' => [
                "Authorization" => "Bearer " . $user->linkedin_access_token,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['elements'][0]['handle~']['emailAddress'])) {
            return null;
        }

        return $data['elements'][0]['handle~']['emailAddress'];
    }

    public function showStats(Request $request, Response $response, array $args)
    {
        $user = $this->app->userSession;

        // $stats = Resume::select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as id'))
        $labels = Resume::select(DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y') as date"))
            ->groupBy(DB::raw('Date(created_at)'), 'date')
            ->orderBy(DB::raw('Date(created_at)'), 'ASC')
            ->get()
            ->pluck('date')
            ->toArray();

        $inscritos = Resume::select(DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y') as date"), DB::raw('count(*) as id'))
            ->groupBy(DB::raw('Date(created_at)'), 'date')
            ->orderBy(DB::raw('Date(created_at)'), 'ASC')
            ->get()
            ->pluck('id')
            ->toArray();

        $publicados = Resume::select(DB::raw("DATE_FORMAT(created_at, '%d/%m/%Y') as date"), DB::raw('count(*) as id'))
            ->where('status', 1)
            ->groupBy(DB::raw('Date(created_at)'), 'date')
            ->orderBy(DB::raw('Date(created_at)'), 'ASC')
            ->get()
            ->pluck('id')
            ->toArray();

        $totalPublicados = Resume::select('id')
            ->where('status', 1)
            ->get()
            ->count();
        
        $totalInstritos = Resume::select('id')
            ->get()
            ->count();

        return $this->view->render($response, 'stats/index.twig', [
            'user' => $user,
            'labels' => $labels,
            'inscritos' => $inscritos,
            'publicados' => $publicados,
            'totalInscritos' => $totalInstritos,
            'totalPublicados' => $totalPublicados
        ]);
    }
}
