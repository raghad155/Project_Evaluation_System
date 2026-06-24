<?php

namespace App\Http\Controllers;

use App\Models\EvaluationScore;
use App\Models\Evaluation;
use Illuminate\Http\Request;

class EvaluationController extends Controller
{
    // عرض جميع التقييمات
    public function index()
    {
        return Evaluation::with([
            'project',
            'evaluator'
        ])->get();
    }

    // إضافة تقييم
public function store(Request $request)
{
    $request->validate([

        'project_id' => 'required|exists:projects,id',

        'user_id' => 'required|exists:users,id',

        'score' => 'required|integer|min:0|max:100',

        'notes' => 'nullable|string|max:500'

    ]);

    $evaluation = Evaluation::create([
        'project_id' => $request->project_id,
        'user_id' => $request->user_id,
        'score' => $request->score,
        'notes' => $request->notes
    ]);

    return response()->json([
        'message' => 'Evaluation created successfully',
        'data' => $evaluation
    ],201);
}
    // تعديل تقييم
public function update(Request $request, $id)
{
    $evaluation = Evaluation::findOrFail($id);

    $request->validate([

        'score' => 'required|integer|min:0|max:100',

        'notes' => 'nullable|string|max:500'

    ]);

    $evaluation->update([
        'score' => $request->score,
        'notes' => $request->notes
    ]);

    return response()->json([
        'message'=>'Evaluation updated successfully',
        'data'=>$evaluation
    ]);
}
    // حذف تقييم
    public function destroy($id)
    {
        $evaluation=Evaluation::findOrFail($id);

        $evaluation->delete();

        return response()->json([
            'message'=>'Evaluation deleted successfully'
        ]);
    }

public function storeScores(Request $request)
{
    $request->validate([

        'evaluation_id'=>'required|exists:evaluations,id',

        'scores'=>'required|array'

    ]);

    foreach($request->scores as $score){

        EvaluationScore::create([

            'evaluation_id'=>$request->evaluation_id,

            'criteria_id'=>$score['criteria_id'],

            'score'=>$score['score']
        ]);
    }

    return response()->json([

        'message'=>'Scores saved successfully'

    ]);
}
public function calculateFinalScore($evaluation_id)
{
    $evaluation = Evaluation::with('scores')->findOrFail($evaluation_id);

    $total = 0;

    foreach ($evaluation->scores as $score) {
        $total += $score->score;
    }

    return response()->json([
        'evaluation_id' => $evaluation->id,
        'final_score' => $total
    ]);
}
}