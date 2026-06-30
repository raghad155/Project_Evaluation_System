<?php

namespace App\Http\Controllers;

use App\Models\Project;
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

        'student_id'=>'required|exists:students,id',

        'scores'=>'required|array'
    ]);

    foreach($request->scores as $item){

        EvaluationScore::create([

            'evaluation_id'=>$request->evaluation_id,

            'student_id'=>$request->student_id,

            'criteria_id'=>$item['criteria_id'],

            'score'=>$item['score']

        ]);

    }

    return response()->json([
        'message'=>'Scores saved successfully'
    ]);
}
public function finalScore($evaluation_id, $student_id)
{
    $evaluation = Evaluation::with(['scores.criteria'])
        ->findOrFail($evaluation_id);

    $total = 0;

    foreach ($evaluation->scores as $score) {

        if ($score->student_id != $student_id) {
            continue;
        }

        $weight = $score->criteria->weight;

        $total += ($score->score * $weight) / 100;
    }

    return response()->json([
        'evaluation_id' => $evaluation_id,
        'student_id' => $student_id,
        'final_score' => round($total, 2)
    ]);
}

public function projectReport($id)
{
    $project = Project::with([
        'students',
        'evaluations.scores.criteria'
    ])->findOrFail($id);

    $studentsReport = [];

    foreach ($project->students as $student) {

        $total = 0;
        $scoresArray = [];

        foreach ($project->evaluations as $evaluation) {

            foreach ($evaluation->scores as $score) {

                if ($score->student_id != $student->id) {
                    continue;
                }

                $weighted = ($score->score * $score->criteria->weight) / 100;

                $total += $weighted;

                $scoresArray[] = [
                    'criteria' => $score->criteria->name,
                    'score' => $score->score,
                    'weight' => $score->criteria->weight,
                    'weighted_score' => round($weighted, 2)
                ];
            }
        }

        $studentsReport[] = [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'scores' => $scoresArray,
            'final_score' => round($total, 2)
        ];
    }

    return response()->json([
        'project_id' => $project->id,
        'project_title' => $project->title,
        'students' => $studentsReport
    ]);
}


public function allProjectsReport()
{
    $projects = Project::with(['students', 'evaluations'])->get();

    $reports = [];

    foreach ($projects as $project) {

        $total = 0;
        $count = $project->evaluations->count();

        foreach ($project->evaluations as $evaluation) {
            $total += $evaluation->final_score ?? 0;
        }

        $avg = $count > 0 ? $total / $count : 0;

        $reports[] = [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'students_count' => $project->students->count(),
            'average_score' => round($avg, 2)
        ];
    }

    return response()->json($reports);
}

public function projectsWithEvaluations()
{
    $projects = Project::with([
        'students',
        'evaluations.scores.criteria'
    ])->get();

    $result = [];

    foreach ($projects as $project) {

        $evaluationsList = [];

        foreach ($project->evaluations as $evaluation) {

            $scores = [];

            foreach ($evaluation->scores as $score) {
                $scores[] = [
                    'criteria' => $score->criteria->name,
                    'score' => $score->score
                ];
            }

            $evaluationsList[] = [
                'evaluation_id' => $evaluation->id,
                'student_id' => $evaluation->student_id ?? null,
                'scores' => $scores
            ];
        }

        $result[] = [
            'project_id' => $project->id,
            'project_title' => $project->title,
            'students_count' => $project->students->count(),
            'evaluations' => $evaluationsList
        ];
    }

    return response()->json($result);
}

public function calculateFinalScores($id)
{
    $project = Project::with([
        'students',
        'evaluations.scores.criteria'
    ])->findOrFail($id);

    $results = [];

    foreach ($project->students as $student) {

        $total = 0;

        foreach ($project->evaluations as $evaluation) {

            foreach ($evaluation->scores as $score) {

                if ($score->student_id != $student->id) {
                    continue;
                }

                $weight = $score->criteria->weight ?? 0;

                $total += ($score->score * $weight) / 100;
            }
        }

        $results[] = [
            'student_id' => $student->id,
            'student_name' => $student->full_name,
            'final_score' => round($total, 2)
        ];
    }

    return response()->json([
        'project_id' => $project->id,
        'project_title' => $project->title,
        'students' => $results
    ]);
}
}
