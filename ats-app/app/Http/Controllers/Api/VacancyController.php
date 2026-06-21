<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\VacancyResource;
use App\Models\Vacancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VacancyController extends Controller
{
    /**
     * Lijst van publiek zichtbare vacatures, met optionele filters.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $vacancies = Vacancy::query()
            ->published()
            ->when(
                $request->filled('department'),
                fn (Builder $q) => $q->where('department', $request->string('department'))
            )
            ->when(
                $request->filled('location'),
                fn (Builder $q) => $q->where('location', $request->string('location'))
            )
            ->when(
                $request->filled('employment_type'),
                fn (Builder $q) => $q->where('employment_type', $request->string('employment_type'))
            )
            ->when(
                $request->filled('q'),
                fn (Builder $q) => $q->where(function (Builder $sub) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $sub->where('title', 'like', $term)
                        ->orWhere('description', 'like', $term);
                })
            )
            ->orderByDesc('published_at')
            ->get();

        return VacancyResource::collection($vacancies);
    }

    /**
     * Detail van één publiek zichtbare vacature (op slug).
     */
    public function show(string $slug): VacancyResource
    {
        $vacancy = Vacancy::query()
            ->published()
            ->with('screeningQuestions')
            ->where('slug', $slug)
            ->firstOrFail();

        return new VacancyResource($vacancy);
    }
}
