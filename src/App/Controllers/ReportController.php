<?php

namespace FeedzRecoloca\Controllers;

use FeedzRecoloca\Libs\SpreadsheetGenerate;
use FeedzRecoloca\Models\Resume;
use Slim\Http\Request;
use Slim\Http\Response;

class ReportController {
    
    private $app;
    private $view;

    public function __construct($app)
    {
        $this->app = $app;
        $this->view = $app->view;
    }


    public function generateAllReport(Request $request, Response $response, array $args)
    {
        $token = $request->getParam('token');

        if ($token !== '399cfdb1b09a8737714654d424cecf4c') {
            return $response->withRedirect('/erro');
        }

        $resumes = Resume::where('status', 1)->get();
        
        $spreadSheet = new SpreadsheetGenerate();
        $spreadSheet->addHeaders(['nome', 'email', 'área de atuação', 'cidade', 'aceita trabalho remoto', 'telefone', 'habilidades', 'LinkedIn', 'URL Recoloca', 'data de publicação']);
        
        foreach ($resumes as $resume) {
            $spreadSheet->writeValues([
                $resume->name,
                $resume->email,
                !is_null($resume->occupation) ? $resume->occupation->name : '',
                !is_null($resume->city) ? $resume->city->name : '',
                $resume->remote_work == 1 ? 'sim' : 'não',
                $resume->phone,
                implode(',', $resume->skills),
                $resume->linkedin_url,
                'https://recoloca.feedz.com.br/perfil/' . $resume->id,
                $resume->created_at->format('d/m/Y')
            ]);
        }

        $spreadSheet->createFile('php://output');
        $spreadSheet->downloadFile('relatorio_candidatos_recoloca__'. date('Ymd') .'.xlsx');
    }
}