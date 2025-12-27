<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudentController extends Controller
{
    //
    function getStudents() {
        // return "list of students";
        $students = \App\Models\Student::all();
        // return $students;
        return view('student', ['data'=> $students]);

    }
}
