<?php
namespace App\Controllers;

use Core\Helpers\Pkg;
use Core\Helpers\Validation;
use Core\Http\Request;
use Core\Http\Response;

abstract class BaseController {
    protected Request $request;
    protected Response $response;
    protected Validation $validation;
    protected Pkg $pkg;
    public function __construct(
    ) {
        $this->request = new Request();
        $this->response = new Response();
        $this->validation = new Validation();
        $this->pkg = new Pkg();
    }
}