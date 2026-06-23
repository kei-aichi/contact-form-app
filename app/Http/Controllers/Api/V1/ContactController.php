<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\IndexContactRequest;
use App\Http\Requests\Api\V1\StoreContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ContactController extends Controller
{
    public function index(IndexContactRequest $request): AnonymousResourceCollection
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        $query->when(! empty($validated['keyword']), function ($query) use ($validated) {
            $query->where(function ($query) use ($validated) {
                $query->where('first_name', 'like', '%'.$validated['keyword'].'%')
                    ->orWhere('last_name', 'like', '%'.$validated['keyword'].'%')
                    ->orWhere('email', 'like', '%'.$validated['keyword'].'%');
            });
        });

        $query->when(! empty($validated['gender']), function ($query) use ($validated) {
            $query->where('gender', $validated['gender']);
        });

        $query->when(! empty($validated['category_id']), function ($query) use ($validated) {
            $query->where('category_id', $validated['category_id']);
        });

        $query->when(! empty($validated['date']), function ($query) use ($validated) {
            $query->whereDate('created_at', $validated['date']);
        });

        $perPage = $validated['per_page'] ?? 20;

        $contacts = $query
            ->latest()
            ->paginate($perPage);

        return ContactResource::collection($contacts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'] ?? [];

        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        $contact->tags()->attach($tagIds);

        $contact->load(['category', 'tags']);

        return (new ContactResource($contact))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Contact $contact): ContactResource
    {
        $contact->load(['category', 'tags']);

        return new ContactResource($contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
