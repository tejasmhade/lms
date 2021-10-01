<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'firstname' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'lastname' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'mobile' => 'required|digits:10|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'age' => 'required|numeric|min:10|max:100',
            'gender' => 'required|in:m,f,o',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        DB::beginTransaction();
        $user = User::create([
            'firstname' => $request->get('firstname'),
            'lastname' => $request->get('lastname'),
            'mobile' => $request->get('mobile'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'age' => $request->get('age'),
            'gender' => $request->get('gender'),
            'city' => $request->get('city'),
            'status' => 1,
        ]);
        DB::commit();

        $token = JWTAuth::fromUser($user);

        $response['status'] = 'success';
        $response['user'] = $user;
        $response['token'] = $token;
        $response['message'] = "User added successfully.";
        return response()->json($response, 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $validator = Validator::make($credentials, [
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                $response['status'] = 'error';
                $response['message'] = "Email or password is invalid.";
                return response()->json($response, 400);
            }
        } catch (JWTException $e) {
            $response['status'] = 'error';
            $response['message'] = "Something went wrong! Please try again later.";
            return response()->json($response, 500);
        }
        $response['status'] = 'success';
        $response['user'] = \Auth::user();
        $response['token'] = $token;
        $response['message'] = "Logged in successfully.";
        return response()->json($response, 200);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $users = User::all();
        $response['status'] = 'success';
        $response['data'] = ['users' => $users];
        return response()->json($response, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);
        if(!empty($user)) {
            $response['status'] = 'success';
            $response['data'] = ['user' => $user];
        } else {
            $response['status'] = 'error';
            $response['message'] = "User not found.";
        }
        return response()->json($response, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'firstname' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'lastname' => 'required|regex:/^[a-zA-Z]+$/u|max:255',
            'mobile' => 'required|digits:10|unique:users,mobile,' . $id . ',u_id',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id . ',u_id',
            'password' => 'required_with:password_confirmation|string|min:6|confirmed',
            'age' => 'required|numeric|min:10|max:100',
            'gender' => 'required|in:m,f,o',
            'city' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $response['status'] = 'error';
            $response['message'] = $validator->errors()->first();
            return response()->json($response, 400);
        }

        DB::beginTransaction();
        $user = User::find($id);
        if(empty($user)) {
            $response['status'] = 'error';
            $response['message'] = "User not found.";
            return response()->json($response, 404);
        }

        $user->fill($request->all());
        if (!empty($request->get('password'))) {
            $user->password = Hash::make($request->get('password'));
        }
        $user->save();
        DB::commit();

        $response['status'] = 'success';
        $response['data'] = ['user' => $user];
        $response['message'] = "User updated successfully.";
        return response()->json($response, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        $user = User::find($id);
        if(empty($user)) {
            $response['status'] = 'error';
            $response['message'] = "User not found.";
            return response()->json($response, 404);
        }

        $user->delete();
        DB::commit();

        $response['status'] = 'success';
        $response['data'] = ['user' => $user];
        $response['message'] = "User deleted successfully.";
        return response()->json($response, 200);
    }
}
