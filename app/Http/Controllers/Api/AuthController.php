<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if($validation->fails()) {
            return response()->json(['Código' => 401, 'Error' => 'Unauthorized', 'Mensaje' => $validation->errors()], 401);
        }

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);

        $success['token'] = $user->createToken('MyApiToken')->accessToken;
        $success['name'] = $user->name;

        return response()->json(['success' => $success]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if(Auth::attempt($credentials)) {
            $user = Auth::user();
            $success['mensaje'] = 'Acceso concedido';
            $success['token'] = $user->createToken('MyApiToken')->accessToken;

            return response()->json(['success' => $success]);

        } else {
            return response()->json(['error' => 'Acceso denegado'], 401);
        }
    }

    public function details()
    {
        $user = Auth::user();
        return response()->json(['success' => 'Acceso concedido', 'data' => $user]);
    }

    public function detailsAnotherUser(Request $request, $id)
    {
        $user = User::find($id);
        if($user == null || $user == 'null') {
            return response()->json(['Código' => 404, 'Error' => 'Not found', 'Mensaje' => 'El id del usuario solicitado no existe'], 404);
        }

        return response()->json(['success' => 'Acceso concedido', 'data' => $user]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);

        $user->update();

        return response()->json(['success' => 'Datos actualizados satisfactoriamente', 'Datos' => $user]);
    }

    public function delete(Request $request, $id) 
    {
        $user = User::find($id);
        if($user == null || $user == 'null') {
            return response()->json(['Código' => 404, 'Error' => 'Not found', 'Mensaje' => 'El id del usuario solicitado no existe'], 404);
        }

        $user->delete();
        return response()->json(['success' => 'Usuario eliminado', 'Data' => $user]);
    }
    
}
