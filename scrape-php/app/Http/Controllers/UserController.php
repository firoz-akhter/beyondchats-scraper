<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    //
    // function getUser() {
    //     return "Anil Sidhu";
    // }

    // function aboutUser() {
    //     return "this is about user";
    // }

    // function getUserName($name) {
    //     // return "hello user name is ".$name;
    //     return view("getUser", ["name"=> $name]);
    // } 


    function users() {
        // return "user function";
        $users = DB::select('select * from users');
        return view("users", ['users'=> $users]);
    }
}
