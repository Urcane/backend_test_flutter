<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\ToDoList as Todo;

class ApiController extends BaseController
{
    public function login(Request $request) {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('api_token')->plainTextToken; 
            $success['name'] =  $user->name;
   
            return $this->sendResponse($success, 'User login successfully.');
        } 
        else{ 
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        };
    }


    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
        ]);
   
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }
   
        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] =  $user->createToken('MyApp')->plainTextToken;
        $success['name'] =  $user->name;
   
        return $this->sendResponse($success, 'User register successfully.');
    }

    function logout(Request $request) {
        try {
            $user = Auth::user();
            $user->tokens()->delete();

            return response()->json(['message' => 'Logout successful.'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Logout failed.'], 500);
        }
    }

    function auth(Request $request) {
        return response()->json([
            'user' => $request->user(),
        ]);
    }

    public function createTodo(Request $request) {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $todo = Todo::create([
            'title' => $request->title,
            'description' => $request->description,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'user_id' => Auth::id(),
        ]);

        return $this->sendResponse($todo, 'To-Do created successfully.');
    }

    public function getTodos() {
        $todos = Todo::where('user_id', Auth::id())->get();
        return $this->sendResponse($todos, 'To-Do list retrieved successfully.');
    }

    public function updateTodo(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'required|date',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $todo = Todo::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$todo) {
            return $this->sendError('To-Do not found.', [], 404);
        }

        $todo->update($request->only(['title', 'description', 'start_time', 'end_time']));

        return $this->sendResponse($todo, 'To-Do updated successfully.');
    }

    public function deleteTodo($id) {
        $todo = Todo::where('id', $id)->where('user_id', Auth::id())->first();

        if (!$todo) {
            return $this->sendError('To-Do not found.', [], 404);
        }

        $todo->delete();

        return $this->sendResponse([], 'To-Do deleted successfully.');
    }
}
