<?php

namespace FeedzRecoloca\Controllers;

use DateTime;
use FeedzRecoloca\Models\Resume;
use FeedzRecoloca\Models\User;
use GuzzleHttp\Client;
use Slim\Http\Request;
use Slim\Http\Response;

class AuthController
{

    private $app;
    const CLIENT_ID = '###############';
    const CLIENT_SECRET = '###########';
    const REDIRECT_URI = 'https://######/login/linkedin';
    //const REDIRECT_URI = 'http://localhost:8081/login/linkedin';
    const SCOPES = 'r_emailaddress,r_liteprofile,w_member_social';

    public function __controller($app)
    {
        $this->app = $app;
    }


    public function auth(Request $request, Response $response, array $args)
    {
        if ($request->getParam('error')) {
            return $response->withRedirect('/logout');
        }

        $dataToken = $this->getAccessToken($request->getParam('code'));
        $linkedinUserId = $this->getLinkedinId($dataToken['access_token']);

        $user = User::findByLinkedinId($linkedinUserId);
        
        $newUser = false;
        if (is_null($user)) {
            $dataLinkedin = $this->getLinkedinData($dataToken['access_token']);
            $user = new User();
            $user->name = $dataLinkedin['name'];
            $user->linkedin_image_url = $dataLinkedin['image'];
            $newUser = true;
        }

        $user->linkedin_access_token = $dataToken['access_token'];
        $user->linkedin_user_id = $linkedinUserId;
        $user->linkedin_token_expires_in = (new DateTime('now'))->modify('+' . $dataToken['expires_in'] . ' seconds');
        $user->save();
        
        if ($newUser === true){
            $resume = new Resume();
            $resume->name = $user->name;
            $resume->id_user = $user->id;
            $resume->email = $this->getLinkedinEmail($dataToken['access_token']);
            $resume->linkedin_image_url = $dataLinkedin['image'];
            $resume->save();
        }

        $_SESSION['user'] = serialize($user);

        return $response->withRedirect('/perfil');
    }

    /**
     * Returns a url of gets authorization code
     *
     * @return string
     */
    public function getUrlAuthorizationCode()
    {
        return "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=" . $this::CLIENT_ID . "&redirect_uri=" . $this::REDIRECT_URI . "&scope=r_emailaddress,r_liteprofile,w_member_social";
    }

    /**
     * Get Linkedin access token
     *
     * @param null $authorizationCode
     * @return bool | array
     */
    public function getAccessToken($authorizationCode = null)
    {
        if (is_null($authorizationCode)) {
            return false;
        }

        $client = new Client(['base_uri' => 'https://www.linkedin.com']);
        $response = $client->request('POST', '/oauth/v2/accessToken', [
            'form_params' => [
                "grant_type" => "authorization_code",
                "code" => $authorizationCode,
                "redirect_uri" => $this::REDIRECT_URI,
                "client_id" => $this::CLIENT_ID,
                "client_secret" => $this::CLIENT_SECRET,
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['access_token'])) {
            return false;
        }

        return $data;
    }

    /**
     * Get Linkedin id user
     *
     * @param $accessToken
     * @return bool | string
     */
    public function getLinkedinId($accessToken)
    {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('GET', '/v2/me', [
            'headers' => [
                "Authorization" => "Bearer " . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['id'])) {
            return false;
        }

        return $data['id'];
    }

    /**
     * Send a post publication to linkedin
     *
     * @param $accessToken
     * @param $linkedinId
     * @param $title
     * @param $content      Limit of 1300 characters
     * @param string $link
     * @return bool
     */
    public function sendPost($accessToken, $linkedinId, $title, $content, $link = 'http://###########')
    {
        $body = new \stdClass();
        $body->content = new \stdClass();
        $body->content->contentEntities[0] = new \stdClass();
        $body->text = new \stdClass();
        $body->content->contentEntities[0]->thumbnails[0] = new \stdClass();
        $body->content->contentEntities[0]->entityLocation = $link;
        $body->content->contentEntities[0]->thumbnails[0]->resolvedUrl = "https://########/assets/images/feedz_logo_linkedin.png";
        $body->content->title = $title;
        $body->owner = 'urn:li:person:' . $linkedinId;
        $body->text->text = $content;
        $bodyJson = json_encode($body, true);

        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('POST', '/v2/shares', [
            'headers' => [
                "Authorization" => "Bearer " . $accessToken,
                "Content-Type" => "application/json",
                "x-li-format" => "json"
            ],
            'body' => $bodyJson,
        ]);

        if ($response->getStatusCode() !== 201) {
            return false;
        }

        return true;
    }

    /**
     * Check if token is actived
     *
     * @param Datetime $tokenExpiresIn
     * @return bool
     */
    public function tokenIsActive($tokenExpiresIn)
    {
        if (!$tokenExpiresIn instanceof DateTime) {
            $tokenExpiresIn = new DateTime($tokenExpiresIn);
        }

        if ($tokenExpiresIn <= (new Datetime('now'))->setTime(0, 0, 0)) {
            return false;
        }

        return true;
    }

    /**
     * Return Linkedin data
     *
     * @return array
     */
    public function getLinkedinData($accessToken)
    {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('GET', "/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))", [
            'headers' => [
                "Authorization" => "Bearer " . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        $name = null;
        if (isset($data['firstName']['localized']) && isset($data['lastName']['localized'])) {
            $keyFirstName = current(array_keys($data['firstName']['localized']));
            $keyLastName = current(array_keys($data['lastName']['localized']));
            $firsNameData = $data['firstName']['localized'];
            $lastNameData = $data['lastName']['localized'];
            $name = $firsNameData[$keyFirstName] .' '. $lastNameData[$keyLastName];
        }

        $image = null;
        if (isset($data['profilePicture']['displayImage~']['elements'][1]['identifiers']['0']['identifier'])) {
            $image = $data['profilePicture']['displayImage~']['elements'][1]['identifiers']['0']['identifier'];
        }
        
        return ['name' => $name, 'image' => $image];
    }

    /**
     * Return email of user
     *
     * @return string | null
     */
    public function getLinkedinEmail($accessToken)
    {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        $response = $client->request('GET', "/v2/emailAddress?q=members&projection=(elements*(handle~))", [
            'headers' => [
                "Authorization" => "Bearer " . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        if (!isset($data['elements'][0]['handle~']['emailAddress'])) {
            return null;
        }

        return $data['elements'][0]['handle~']['emailAddress'];
    }

    


    /**
     * função de testes para o linkedin
     *
     * @param string $linkedinUserId
     * @param string $accessToken
     * @return 
     */
    public function getBasicLinkedinProfile($linkedinUserId = "", $accessToken = "")
    {
        $client = new Client(['base_uri' => 'https://api.linkedin.com']);
        // $response = $client->request('GET', "/v2/people/(id:{$linkedinUserId})", [
        // $response = $client->request('GET', "/v2/me", [
        // $response = $client->request('GET', "/v2/emailAddress?q=members&projection=(elements*(handle~))", [
        // $response = $client->request('GET', "/v2/me?projection=(id,firstName,lastName,profilePicture(displayImage~:playableStreams))", [
        $response = $client->request('GET', "/v2/me?projection=(id,firstName,lastName,vanityName,profilePicture(displayImage~:playableStreams))", [
            'headers' => [
                "Authorization" => "Bearer " . $accessToken,
            ],
        ]);
        $data = json_decode($response->getBody()->getContents(), true);

        echo '<pre>';
        $key = current(array_keys($data['lastName']['localized']));
        $array = $data['lastName']['localized'];
        var_dump($array[$key]);
        echo '</pre>';
        die();

        if (!isset($data['id'])) {
            return false;
        }

        return $data['id'];
    }
}
