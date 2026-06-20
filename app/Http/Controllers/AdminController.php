<?php

namespace App\Http\Controllers;

use App\Http\Requests\IndexContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(IndexContactRequest $request): View
    {
        $validated = $request->validated();

        $query = Contact::with(['category', 'tags']);

        $query->when(
            ! empty($validated['keyword']),
            function ($query) use ($validated) {
                $query->where(function ($query) use ($validated) {
                    $query->where('first_name', 'like', '%'.$validated['keyword'].'%')
                        ->orWhere('last_name', 'like', '%'.$validated['keyword'].'%')
                        ->orWhere('email', 'like', '%'.$validated['keyword'].'%');
                });
            }
        );

        $query->when(
            ! empty($validated['gender']),
            fn ($query) => $query->where('gender', $validated['gender'])
        );

        $query->when(
            ! empty($validated['category_id']),
            fn ($query) => $query->where('category_id', $validated['category_id'])
        );

        $query->when(
            ! empty($validated['date']),
            fn ($query) => $query->whereDate('created_at', $validated['date'])
        );

        $contacts = $query
            ->latest()
            ->paginate(7)
            ->withQueryString();

        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.index', compact(
            'contacts',
            'categories',
            'tags'
        ));
    }

    public function show(Contact $contact): View
    {
        $contact->load(['category', 'tags']);

        return view('admin.show', compact('contact'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect('/admin');
    }
}
