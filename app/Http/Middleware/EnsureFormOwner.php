<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFormOwner
{
    public function handle(Request $request, Closure $next)
    {
        $form = $request->route('form');
        
        if ($form && $form->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized access to form');
        }

        return $next($request);
    }
}