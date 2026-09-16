<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArticleUpsertRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'title' => ['required','string','max:190'],
            'excerpt' => ['nullable','string','max:400'],
            'content_html' => ['required','string','max:200000'],
            'category_id' => ['required','integer','exists:categories,id'],
            'tag_ids' => ['nullable','array'],
            'tag_ids.*' => ['integer','exists:tags,id'],
            'new_tags' => ['nullable','array'],
            'new_tags.*' => ['string','max:80'],
            'cover_image_path' => ['nullable','string','max:500'],
            'cover_image_file' => ['nullable','image','mimes:jpg,jpeg,png,webp,gif','max:5120'],
            'is_breaking' => ['sometimes','boolean'],
            'is_featured' => ['sometimes','boolean'],
            'seo_title' => ['nullable','string','max:190'],
            'seo_desc' => ['nullable','string','max:300'],
            'canonical_url' => ['nullable','url','max:500'],
            'regenerate_slug' => ['sometimes','boolean'],
        ];
    }
}
