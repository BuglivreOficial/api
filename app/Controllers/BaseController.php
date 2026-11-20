<?php
namespace App\Controllers;

use Core\Helpers\Validation;
use Core\Http\Request;
use Core\Http\Response;

class BaseController {
    public function __construct(
        protected Request $request,
        protected Response $response,
        protected Validation $validation
    ) {}
}