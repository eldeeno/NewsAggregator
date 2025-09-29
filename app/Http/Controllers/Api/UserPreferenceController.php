<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePreferenceRequest;
use App\Http\Resources\UserPreferenceResource;
use App\Traits\HasApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserPreferenceController extends Controller
{
    public function show(): JsonResponse
    {
        $preferences = auth()->user()->preferences;

        if (!$preferences) {
            $preferences = auth()->user()->preferences()->create([
                'preferred_sources' => [],
                'preferred_categories' => [],
                'preferred_authors' => [],
            ]);
        }

        return $this->successResponse(
            new UserPreferenceResource($preferences),
            'User preferences retrieved successfully'
        );
    }

    public function update(UpdatePreferenceRequest $request): JsonResponse
    {
        $preferences = auth()->user()->preferences()->updateOrCreate(
            ['user_id' => auth()->id()],
            $request->validated()
        );

        return $this->successResponse(
            new UserPreferenceResource($preferences),
            'Preferences updated successfully'
        );
    }
}
