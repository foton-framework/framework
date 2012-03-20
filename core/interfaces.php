<?php

//--------------------------------------------------------------------------

interface SYS_Loader_Interface
{
	public function component($component, $exec_method, $arguments);
}

//--------------------------------------------------------------------------

interface SYS_Router_Interface
{
	public function set_request($segments);
	public function set_component($component);
	public function component();
	public function set_method($method);
	public function method();
	public function set_arguments($arguments);
	public function arguments();
}

//--------------------------------------------------------------------------

interface SYS_Uri_Interface
{
	public function segments();
}

//--------------------------------------------------------------------------

interface SYS_Tempalte_Interface
{
	public function render($template, $data);
	public function template_folder();
	public function enable();
}