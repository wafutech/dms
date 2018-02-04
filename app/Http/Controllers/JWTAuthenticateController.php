<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use App\Permission;
use App\Role;
use App\User;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Log;
use Validator;
use DB;
use Hash;
use Illuminate\Http\Request;
use Session;
use Mail;
use App\Mail\NewUser;



class JWTAuthenticateController extends Controller
{
    //

    function __construct()
    {
        if(!Auth::check())
        {
 $this->middleware('auth');
 $this->middleware('role:admin');
}




    }
    public function index()
    {
    	$users = User::all();
    	return view('auth.users.index',array('title'=>'Users','users'=>$users));
       // return response()->json(['auth'=>Auth::user(), 'users'=>User::all()]);
    }
    /*public function loginForm()
    {
        return view('auth.login');
    }*/

    /*public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        try {
            // verify the credentials and create a token for the user
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // if no errors are encountered we can return a JWT
        $response= response()->json(compact('token'));
         if (Auth::attempt(['email' => $request->get('email'), 'password' => $request->get('password')])) {
        return redirect()->intended('/');
    } else {
        return view('auth.login', array('title' => 'Welcome', 'description' => '', 'page' => 'home'));
    }

    }*/
    //Show create roles form
    public function createRoleForm()
    {
    	return view('auth.roles.create',array('title'=>'Create New Role','errors'=>''));
    }

    public function createRole(Request $request){
        // Todo  
        $validation_rules = array(
          'name'         => 'bail|required|unique:roles',
          'display_name'         => 'required',
          'description'      => '', 
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      }   
        $role = new Role();
        $role->name = $request->input('name');
        $role->display_name = $request->input('display_name');
        $role->description = $request->input('description');

        $role->save();

       /// return response()->json("created");
        return Redirect::back()->with('message','Role created successfully');
  
    }
     public function createPermissionForm()
    {
    	return view('auth.permissions.create',array('title'=>'Create Permission'));
    }

    public function createPermission(Request $request){
        // Todo    
        $permission = new Permission();
        $permission->name = $request->input('name');
        $permission->display_name = $request->input('display_name');
        $permission->description = $request->input('description');
        $permission->save();

        return response()->json("created");   
    }
     public function assignRoleForm($id)
    {
    	$user = User::where('id',$id)->first();
    	$roles = Role::all();
    	return view('auth.users.assign_roles',array('user'=>$user,'title'=>'Assign User Roles','roles'=>$roles));
    }

    public function assignRole(Request $request){
         // Todo

        $user = User::where('email', '=', $request->input('user'))->first();

        $role = Role::where('name', '=', $request->input('role'))->first();
       $user_has_role = DB::table('role_user')
                        ->where('user_id',$user->id)->first();
                if($user_has_role===null)
                {
                  $user->attachRole($role->id);  
                }
                else
                {
         DB::update('update role_user set role_id=? where user_id =?',[$role->id,$user->id]);
    
                }
        
      $message = 'User role updated to '.$request->input('role');
                Session::flash('message', $message);

    return redirect()->back();
        //return response()->json("User Role Updated");
    }

    public function assignPermissionForm($id)
    {
    	
    	$role = Role::where('id',$id)->first();
    	$privillages = Permission::all();
        $current_privillages = DB::table('permission_role')
                            ->leftjoin('permissions','permission_id',
                                'permissions.id','permission_role.permission_id')
        ->where('permission_role.role_id',$role->id)->get();
          $current_privillages_array=[];     
         foreach($current_privillages as $cp)
        {
        $current_privillages_array[] = $cp->name;
    }
   

    
                   
    	return view('auth.users.edit_user_privillages',
            array('role'=>$role,'title'=>'Edit User Privillages',
                'privillages'=>$privillages,'current_privillages'=>$current_privillages_array));
    }

    public function attachPermission(Request $request){
        // Todo
        $permissions = Input::get('permission'); 
        //Get role Id from the roles table based on submitted role name
        $role = Role::where('name', '=', $request->input('role'))->first();
      //Delete all permissions already assgined to the role if any
         DB::table('permission_role')->where('role_id', '=', $role->id)->delete();
       //Extract key values from the submitted permissions in an array
        for($i=0;$i<count($permissions);$i++)
        {

        $role = Role::where('name', '=', $request->input('role'))->first();
        $permission = Permission::where('name', '=', $permissions[$i])->first();
       // $role->permission()->sync($permissions); 
       // Assign new permissions   
        $role->attachPermission($permission);
    }

// Return back with response message
session::flash('message','Permissions attached successfuly');
        return redirect()->back();
        //return response()->json("created");  

         
    }
    public function login()
    {
    	return view('auth.login');
    }

    //register a new user
    public function register(Request $request) 
    {
        $validation_rules = array(
          'name'         => 'bail|required',
          'email'         => 'required|email|unique:users',
          'password'      => 'required', 
                 
          
      );
    $validator = Validator::make(Input::all(), $validation_rules);
     // Return back to form w/ validation errors & session data as input
      if($validator->fails()) {
        return  Redirect::back()->withErrors($validator)->withInput();
      } 
    //if (Request::isMethod('post')) {
        User::create([
                    'name' => $request->input('name'),
                    'email' => $request->input('email'),
                    'password' => bcrypt($request->input('password')),
                    'token' => str_random(64),
                    'activated' => !config('settings.activation')
        ]);

     //Assign default role
        $user = User::where('email', '=', $request->input('email'))->first();

        $role = Role::where('name', '=', 'member')->first();
        $user->attachRole($role->id);
    //}

         //Email user credentials

        $to = $request->input('email');
        $password = $request->input('password');

        Mail::to($to)->send(new NewUser($user,$password));
       
       return redirect()->back()->with('message','User created successfuly');
    } 
    

//Logout a user
    public function logout() {
    Auth::logout();
    
    return Redirect::route('login');
}
//Change password form
public function changePasswordForm()
{
    return view('auth.passwords.change_password');
}

 //Change user password
public function changePasswordRules(array $data)
{
    $messages = [
    'current-password.required' => 'Please enter current password',
    'password.required' => 'Please enter password',
  ];

  $validator = Validator::make($data, [
    'current-password' => 'required',
    'password' => 'required|same:password',
    'password_confirmation' => 'required|same:password',     
  ], $messages);

  return $validator;
}
public function changePassword(Request $request)
{
  if(Auth::Check())
  {
    $request_data = $request->All();
    $validator = $this->changePasswordRules($request_data);
    if($validator->fails())
    {
      return response()->json(array('error' => $validator->getMessageBag()->toArray()), 400);
    }
    else
    {  
      $current_password = Auth::User()->password;           
      if(\Hash::check($request_data['current-password'], $current_password))
      {           
        $user_id = Auth::User()->id;                       
        $obj_user = User::find($user_id);
        $obj_user->password = Hash::make($request_data['password']);;
        $obj_user->save(); 
        return redirect()->back()->with('message','Password was successfuly changed');
      }
      else
      {           
        $error = array('current-password' => 'Please enter correct current password');
        return response()->json(array('error' => $error), 400);   
      }
    }        
  }
  else
  {
    return redirect()->to('/');
  }    
}
public function admin()

{
    $users = count(User::all());
    $banned =count(DB::table('users')->where('banned',1)->get());
    $active = count(DB::table('users')->where('activated',1)->get());
    $pending = count(DB::table('users')->where('activated',0)->get());;
	return view ('admin.index',['title'=>'Admin Panel',
        'pending'=>$pending,'users'=>$users,'banned'=>$banned,
        'active'=>$active]);
}

public function bannedUsers()
{
        $banned =DB::table('users')->where('banned',1)->get();
       
        return view('auth.users.index',array('title'=>'Banned Users',
            'users'=>$banned));


}

public function activeUsers()
{
        $active =DB::table('users')->where('activated','=',1)->get();

      

        return view('auth.users.index',array('title'=>'Active Users',
            'users'=>$active));
}

public function pendingUsers()
{
        $pending =DB::table('users')->where('activated',0)->get();
      

        return view('auth.users.index',array('title'=>'Pending Users',
            'users'=>$pending));


}
public function rolesWithPermissions($id)

{   
  

    $roles= DB::table('roles')
        ->leftjoin('permission_role','roles.id',
            'permission_role.role_id','roles.id')
        ->leftjoin('permissions','permission_role.permission_id',
            'permissions.id','permission_role.permission_id')
        ->select(DB::raw('roles.id'),DB::raw('roles.name'),DB::raw('roles.display_name'),
            DB::raw('roles.description'),DB::raw('permissions.name as permissions'))
        ->where('roles.id',$id)
        ->get();

    $role = Role::where('id',$id)->first();
    return view('auth.roles.rolesWithPermissions',
        array('roles'=>$roles,'role'=>$role,
            'title'=>'Role With Permission'));
}

public function usersWithRoles()

{   
     $users= DB::table('users')
        ->leftjoin('role_user','id',
            'role_user.user_id','users.id')
        ->leftjoin('roles','role_user.role_id',
            'roles.id','role_user.role_id')
        ->select(DB::raw('users.id'),DB::raw('users.name'),
            DB::raw('users.email'),
            DB::raw('roles.name as role'),DB::raw('roles.display_name'),
            DB::raw('roles.description'))            
             ->get();


    return view('admin.users_with_roles',array('title'=>'Users With Roles','users'=>$users));


}



}
