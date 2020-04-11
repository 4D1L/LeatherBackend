<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;

class DeploymentController extends Controller
{
    /**
     * This code is from https://medium.com/@gmaumoh/laravel-how-to-automate-deployment-using-git-and-webhooks-9ae6cd8dffae
     * It allows any commits to the master branch to appear on the webserver @ https://leather.adil.tech
     */
    public function deploy(Request $request)
    {
        $githubPayload = $request->getContent();
        $githubHash = $request->header('X-Hub-Signature');

        $localToken = config('app.deployment_secret');
        $localHash = 'sha1=' . hash_hmac('sha1', $githubPayload, $localToken, false);

        if(hash_equals($githubHash, $localHash)) {
            $rootPath = base_path();
            //$process = new Process('cd ' . $rootPath . '; ./deploy.sh');
            
            // Now compatible with Symfony/Process 5.x
            $process = new Process(['cd ..; ./deploy.sh']);
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });
        }
    }
}
