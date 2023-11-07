<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

interface IController
{
	public function Get(Request $request, Response $response, array $args);
	public function GetAll(Request $request, Response $response, array $args);
	public function Create(Request $request, Response $response, array $args);
	public function Delete(Request $request, Response $response, array $args);
	public function Update(Request $request, Response $response, array $args);
}

?>