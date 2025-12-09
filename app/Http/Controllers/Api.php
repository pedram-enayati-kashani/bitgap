<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;

class Api extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $users = User::all();
        $task = Task::all();
        dd($task);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function Users()
    {
        return User::select('id', 'name', 'email', 'role')->get();
    }
}
