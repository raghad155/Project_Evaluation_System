<?php

namespace App\Http\Controllers;
use App\Models\EvaluationCriteria;


use Illuminate\Http\Request;

class EvaluationCriteriaController extends Controller
{
    //
    public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'weight' => 'required|integer|min:1|max:100'
    ]);

    $criteria = EvaluationCriteria::create($request->all());

    return response()->json([
        'message' => 'Criteria created successfully',
        'data' => $criteria
    ]);
}

public function index()
{
    return EvaluationCriteria::all();
}
public function update(Request $request, $id)
{
    $criteria = EvaluationCriteria::findOrFail($id);

    $request->validate([
        'name' => 'required|string|max:255',
        'description' => 'nullable|string',
        'weight' => 'required|integer|min:1|max:100'
    ]);

    $criteria->update($request->all());

    return response()->json([
        'message' => 'Criteria updated successfully',
        'data' => $criteria
    ]);
}
public function destroy($id)
{
    $criteria = EvaluationCriteria::findOrFail($id);

    $criteria->delete();

    return response()->json([
        'message' => 'Criteria deleted successfully'
    ]);
}
}
