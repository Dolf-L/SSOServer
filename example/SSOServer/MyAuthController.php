<?php

namespace Dolf\SSO\example\SSOServer;

use Dolf\SSOServer\SSOServer;
use Illuminate\Http\Request;
use Illuminate\Contracts\Logging\Log;

class MyAuthController
{
    /**
     * @var SSOServer
     */
    private $SSOServer;

    /**
     * @var
     */
    protected $log;

    /**
     * MyAuthController constructor.
     *
     * @param SSOServer $SSOServer
     * @param Log       $log
     */
    function __construct(SSOServer $SSOServer, Log $log)
    {
        $this->SSOServer = $SSOServer;
        $this->log       = $log;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ssoIndex(Request $request)
    {
        $ssoAction   = $request->get('action');
        $destination = $request->get('destination');

        if ($ssoAction == 'login') {
            // enter login action and pass $destination
            // redirect to /sso/login (ssoBrokerAction) afterwards and pass $destination as GET parameter
        }
        if ($ssoAction == 'logout') {
            // enter logout action and pass $destination
            // redirect to /sso/logout (ssoBrokerAction) afterwards and pass $destination as GET parameter
        }

        return redirect()->away($destination);
    }

    /**
     * @param $action
     * @return \Illuminate\Http\RedirectResponse
     */
    public function ssoBrokerAction($action)
    {
        if ($action == 'login' || $action == 'logout') {
            $this->SSOServer->ssoAction($action);
        } else {
            $this->log->info('Wrong action');
        }

        return redirect()->back();
    }
}