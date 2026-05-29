<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'motivation' => ['nullable', 'string', 'max:5000'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'consent' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Vul je naam in.',
            'email.required' => 'Vul je e-mailadres in.',
            'email.email' => 'Vul een geldig e-mailadres in.',
            'cv.mimes' => 'Upload een cv als PDF, DOC of DOCX.',
            'cv.max' => 'Het cv mag maximaal 5 MB zijn.',
            'consent.accepted' => 'Je moet akkoord gaan met de verwerking van je gegevens.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'naam',
            'email' => 'e-mailadres',
            'phone' => 'telefoonnummer',
            'motivation' => 'motivatie',
            'cv' => 'cv',
        ];
    }
}
