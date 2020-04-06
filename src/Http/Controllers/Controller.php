<?php

namespace Insowe\AdminTemplate\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use View;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    public function __construct()
    {
        // No session in __construct()
        $this->middleware(function ($request, $next){
            View::share('successMessage', session('successMessage'));
            View::share('errorMessage', session('errorMessage'));
            return $next($request);
        });
    }
    
    protected function json($data = null, $message = null, $code = 0, $httpCode = 200)
    {
        return response()->json(compact('code', 'data', 'message'), $httpCode);
    }

    /**
     * Validate the given request & route parameters 
     * with the given rules.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return array
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function validate(Request $request, array $rules,
                             array $messages = [], array $customAttributes = [])
    {
        $data = array_merge($request->route()->parameters(), $request->all());
        
        return $this->getValidationFactory()->make(
            $data, $rules, $messages, $customAttributes
        )->validate();
    }
}
