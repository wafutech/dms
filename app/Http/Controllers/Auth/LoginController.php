<?php 
namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Auth;



class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

     public function login(Request $request)
    {
        $this->validate($request,[
            'email'=>'required|email','password'=>'required']);
       // $credentials = $this->getCredentials($request);
        //if(Auth::validate($credentials))
        //{
          if (Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password'),'activated'=>1,'banned'=>0])) {
            // Authentication passed...
            return redirect()->intended('/');
        }  

    return redirect()->back()->with(['message', 'Could not log you in!']);

        //}
        
         
    }

    //Logout a user
    public function logout() {
    Auth::logout();
    
    return Redirect::route('login');
}
}
