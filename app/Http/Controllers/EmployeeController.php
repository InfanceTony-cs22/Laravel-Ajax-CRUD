<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index() {
        return view('index');
    }

    public function fetchAll() {
        $employee = Employee::all();// Retrieves all records from the Employee model
        $output = '';//Initializes an empty string to store the HTML output.
        if ($employee->count() > 0) {//Checks if there are any employee records.
            $output .= '<table class="table table-striped align-middle">
            <thead>
              <tr>
                <th>ID</th>
                <th>Avatar</th>
                <th>Name</th>
                <th>E-mail</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>'; //Builds the HTML structure for displaying employee data in a table format.
            foreach ($employee as $rs) {//Eallathayum one By one ah eaduthutu varum
                $output .= '<tr>
                <td>' . $rs->id . '</td>
                <td><img src="storage/images/' . $rs->avatar . '" width="50" class="img-thumbnail rounded-circle"></td>
                <td>' . $rs->first_name . ' ' . $rs->last_name . '</td>
                <td>' . $rs->email . '</td>
                <td>
                  <a href="#" id="' . $rs->id . '" class="text-success mx-1 editIcon" data-bs-toggle="modal" data-bs-target="#editEmployeeModal"><i class="bi-pencil-square h4"></i></a>
                  <a href="#" id="' . $rs->id . '" class="text-danger mx-1 deleteIcon"><i class="bi-trash h4"></i></a>
                </td>
              </tr>';
            }
            $output .= '</tbody></table>';//Constructs a table row for each employee with their details (ID, avatar, name, email).
            echo $output;
        } else {
            echo '<h1 class="text-center text-secondary my-5">No record in the database!</h1>';
        }
    }

    public function store(Request $request) {
        $file = $request->file('avatar');//Retrieves the uploaded file from the request.
        $fileName = time() . '.' . $file->getClientOriginalExtension();//Generates a unique file name using the current timestamp and the original file extension.
        $file->storeAs('public/images', $fileName); //php artisan storage:link

        $empData = ['first_name' => $request->fname, 'last_name' => $request->lname, 'email' => $request->email, 'avatar' => $fileName];//Creates an array with employee data, including the generated file name for the avatar.
        Employee::create($empData);// Inserts a new employee record into the database.
        return response()->json([
            'status' => 200,
        ]);//Just a JSON response to indicate that the data was inserted successfully.
    }

    public function edit(Request $request) {
        $id = $request->id;//Retrieves the employee ID from the request.
        $emp = Employee::find($id);// Fetches the employee record with the given ID.
        return response()->json($emp);// Returns the employee data as a JSON response.
    }

    public function update(Request $request) {
        $fileName = '';//Initializes the file name variable.
        $emp = Employee::find($request->emp_id);// Retrieves the employee record by the provided ID.
        if ($request->hasFile('avatar')) {//Checks if a new avatar file is uploaded.
            $file = $request->file('avatar');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->storeAs('public/images', $fileName);
            if ($emp->avatar) {
                Storage::delete('public/images/' . $emp->avatar);
            }//Deletes the old avatar file if it exists.
        } else {
            $fileName = $request->emp_avatar;//If no new avatar file is uploaded, the existing avatar file name is used.
        }

        $empData = ['first_name' => $request->fname, 'last_name' => $request->lname, 'email' => $request->email, 'avatar' => $fileName];//Creates an array with the updated employee data.

        $emp->update($empData);//Updates the employee record in the database
        return response()->json([
            'status' => 200,
        ]);//Json response to indicate that the data was updated successfully.
    }

    // delete an employee ajax request
    public function delete(Request $request) {
        $id = $request->id;
        $emp = Employee::find($id);// Retrieves the employee record by the provided ID.
        if (Storage::delete('public/images/' . $emp->avatar)) {//Deletes the avatar file if it exists.
            Employee::destroy($id);//Deletes the employee record from the database.
        }
    }
}
