<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\UserActivation;
use ActivationTrait;

class ActivateController extends Controller
{
     public function activate($token)
    {
        if (auth()->user()->activated) {

            return redirect()->route('user_dashboard')
                ->with('status', 'success')
                ->with('message', 'Your email is already activated.');
        }

        $activation = UserActivation::where('token', $token)
            ->where('user_id', auth()->user()->id)
            ->first();

        if (empty($activation)) {

            return redirect()->back()
                ->with('status', 'wrong')
                ->with('message', 'No such token in the database!');

        }

        auth()->user()->activated = true;
        auth()->user()->save();

        $activation->delete();

        session()->forget('above-navbar-message');

        return redirect()->route('activationSuccess')
            ->with('status', 'success')
            ->with('message', 'You successfully activated your email!');

    }

    public function activationSuccess()
    {
    	return view('activation.activation_success');
    }
}
