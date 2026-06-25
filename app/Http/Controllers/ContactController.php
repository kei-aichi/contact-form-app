<?php

namespace App\Http\Controllers;

use App\Http\Requests\ExportContactRequest;
use App\Http\Requests\StoreContactRequest;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ContactController extends Controller
{
    public function index(): View
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('contact.index', compact('categories', 'tags'));
    }

    public function confirm(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $category = Category::find($validated['category_id']);

        $tags = Tag::whereIn('id', $validated['tag_ids'] ?? [])->get();

        return view('contact.confirm', compact(
            'validated',
            'category',
            'tags'
        ));
    }

    public function store(StoreContactRequest $request)
    {
        $validated = $request->validated();

        $tagIds = $validated['tag_ids'] ?? [];

        unset($validated['tag_ids']);

        $contact = Contact::create($validated);

        $contact->tags()->attach($tagIds);

        return redirect('/thanks');
    }

    public function thanks()
    {
        return view('contact.thanks');
    }

    public function export(ExportContactRequest $request): StreamedResponse
    {
        $validated = $request->validated();

        $query = Contact::with('category');

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

        $contacts = $query->latest()->get();

        $csvHeader = ['ID', '氏名', '性別', 'メール', '電話', '住所', '建物', 'カテゴリ', '内容', '作成日時'];

        $genderLabels = [
            1 => '男性',
            2 => '女性',
            3 => 'その他',
        ];

        $callback = function () use ($contacts, $csvHeader, $genderLabels) {
            $file = fopen('php://output', 'w');

            fwrite($file, "\xEF\xBB\xBF");

            fputcsv($file, $csvHeader);

            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->id,
                    $contact->first_name.' '.$contact->last_name,
                    $genderLabels[$contact->gender] ?? '',
                    $contact->email,
                    $contact->tel,
                    $contact->address,
                    $contact->building,
                    $contact->category?->content,
                    $contact->detail,
                    $contact->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=contacts.csv',
        ]);
    }
}
