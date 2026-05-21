<?php

namespace App\Imports;

use App\Models\Student;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class StudentsImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new Student([
            'full_name' => $row['full_name'],
            'academic_number' => $row['academic_number'],
            'specialization_id' => $row['specialization_id'],
            'project_id' => $row['project_id'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            '*.full_name' => 'required|string|max:255',

            '*.academic_number' => 'required|unique:students,academic_number',

            '*.specialization_id' => 'required|exists:specializations,id',

            '*.project_id' => 'nullable|exists:projects,id',
        ];
    }
    public function customValidationMessages()
{
    return [
        'academic_number.unique' => 'الرقم الأكاديمي مكرر',
        'specialization_id.exists' => 'التخصص غير موجود',
        'project_id.exists' => 'المشروع غير موجود',
    ];
}
}