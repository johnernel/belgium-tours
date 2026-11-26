<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\Package;
use App\Models\PackageImage;
use App\Models\TourType;
use App\Services\ImageService;
use App\Support\Html;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PackageController extends Controller
{
    public function index()
    {
        return view('admin.packages.index', ['packages' => Package::with(['tourType', 'destination'])->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.packages.form', $this->formData(new Package(['currency' => 'EUR', 'sort' => Package::max('sort') + 1])));
    }

    public function store(Request $request, ImageService $images)
    {
        $package = new Package;
        $this->persist($request, $package, $images);

        return redirect()->route('admin.packages.edit', $package)->with('status', 'Package created. Add gallery photos below if you like.');
    }

    public function edit(Package $package)
    {
        return view('admin.packages.form', $this->formData($package->load('images')));
    }

    public function update(Request $request, Package $package, ImageService $images)
    {
        $this->persist($request, $package, $images);

        return back()->with('status', 'Package saved.');
    }

    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('admin.packages.index')->with('status', 'Package deleted.');
    }

    public function duplicate(Package $package)
    {
        $copy = $package->replicate();
        $copy->title = $package->title.' (copy)';
        $copy->slug = $this->uniqueSlug(Str::slug($copy->title));
        $copy->is_published = false;
        $copy->is_featured = false;
        $copy->sort = Package::max('sort') + 1;
        $copy->save();

        foreach ($package->images as $img) {
            $copy->images()->create($img->only(['path', 'alt', 'sort']));
        }

        return redirect()->route('admin.packages.edit', $copy)->with('status', 'Duplicated as a draft. Edit the copy below.');
    }

    public function toggle(Package $package, string $field)
    {
        abort_unless(in_array($field, ['is_published', 'is_featured'], true), 404);
        $package->update([$field => ! $package->{$field}]);

        return back()->with('status', $field === 'is_published' ? ($package->is_published ? 'Published.' : 'Unpublished.') : ($package->is_featured ? 'Marked as featured.' : 'No longer featured.'));
    }

    public function reorder(Request $request)
    {
        foreach ((array) $request->input('sort', []) as $id => $sort) {
            Package::whereKey($id)->update(['sort' => (int) $sort]);
        }

        return back()->with('status', 'Order saved.');
    }

    public function destroyImage(Package $package, PackageImage $image)
    {
        abort_unless($image->package_id === $package->id, 404);
        $image->delete();

        return back()->with('status', 'Photo removed.');
    }

    private function formData(Package $package): array
    {
        return [
            'package' => $package,
            'tourTypes' => TourType::orderBy('sort')->pluck('name', 'id')->all(),
            'destinations' => Destination::orderBy('sort')->pluck('name', 'id')->all(),
            'experiences' => \App\Models\Experience::orderBy('sort')->pluck('name', 'id')->all(),
            'travelStyles' => \App\Models\TravelStyle::orderBy('sort')->pluck('name', 'id')->all(),
        ];
    }

    private function uniqueSlug(string $base): string
    {
        $slug = $base;
        $i = 2;
        while (Package::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /** Split a textarea into trimmed non-empty lines. */
    private function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    }

    private function persist(Request $request, Package $package, ImageService $images): void
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'alpha_dash', 'max:160', Rule::unique('packages', 'slug')->ignore($package->id)],
            'tour_type_id' => ['nullable', 'exists:tour_types,id'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'nights' => ['required', 'integer', 'min:0', 'max:365'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0', Rule::when($request->filled('price_from'), ['gt:price_from'])],
            'currency' => ['required', 'in:EUR,USD,GBP'],
            'route' => ['nullable', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:600'],
            'description' => ['nullable', 'string'],
            'highlights' => ['nullable', 'string'],
            'inclusions' => ['nullable', 'string'],
            'exclusions' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'array'],
            'itinerary.*.title' => ['nullable', 'string', 'max:160'],
            'itinerary.*.text' => ['nullable', 'string', 'max:3000'],
            'faqs' => ['nullable', 'array'],
            'faqs.*.question' => ['nullable', 'string', 'max:255'],
            'faqs.*.answer' => ['nullable', 'string', 'max:2000'],
            'hero_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'hero_image_alt' => ['nullable', 'string', 'max:200'],
            'social_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'seo_title' => ['nullable', 'string', 'max:160'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'experience_ids' => ['nullable', 'array'],
            'experience_ids.*' => ['integer', 'exists:experiences,id'],
            'travel_style_ids' => ['nullable', 'array'],
            'travel_style_ids.*' => ['integer', 'exists:travel_styles,id'],
            'new_images.*.file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'new_images.*.alt' => ['nullable', 'string', 'max:200'],
            'existing.*.alt' => ['required', 'string', 'max:200'],
            'existing.*.sort' => ['nullable', 'integer'],
        ], [
            'existing.*.alt.required' => 'Every gallery photo needs a description (alt text).',
            'original_price.gt' => 'The original price must be higher than the current (From) price, otherwise it would not look like a reduction.',
        ]);

        // A new hero photo needs alt text, and so does every new gallery photo.
        if ($request->hasFile('hero_image') && blank($request->input('hero_image_alt'))) {
            back()->withErrors(['hero_image_alt' => 'Please describe the hero photo (alt text).'])->throwResponse();
        }
        foreach ((array) $request->file('new_images', []) as $i => $row) {
            if (! empty($row['file']) && blank($request->input("new_images.$i.alt"))) {
                back()->withErrors(["new_images.$i.alt" => 'Every new gallery photo needs a description (alt text).'])->throwResponse();
            }
        }

        $package->fill([
            'title' => $data['title'],
            'slug' => ($data['slug'] ?? null) ?: ($package->exists ? $package->slug : $this->uniqueSlug(Str::slug($data['title']))),
            'tour_type_id' => $data['tour_type_id'] ?? null,
            'destination_id' => $data['destination_id'] ?? null,
            'nights' => $data['nights'],
            'price_from' => $data['price_from'] ?? null,
            // The crossed-out price only makes sense next to a current price.
            'original_price' => filled($data['price_from'] ?? null) ? ($data['original_price'] ?? null) : null,
            'currency' => $data['currency'],
            'route' => $data['route'] ?? null,
            'summary' => $data['summary'] ?? null,
            'description' => Html::clean($data['description'] ?? null),
            'highlights' => $this->lines($data['highlights'] ?? '') ?: null,
            'inclusions' => $this->lines($data['inclusions'] ?? '') ?: null,
            'exclusions' => $this->lines($data['exclusions'] ?? '') ?: null,
            'itinerary' => collect($data['itinerary'] ?? [])->filter(fn ($r) => filled($r['title'] ?? null) || filled($r['text'] ?? null))->values()->all() ?: null,
            'faqs' => collect($data['faqs'] ?? [])->filter(fn ($r) => filled($r['question'] ?? null) && filled($r['answer'] ?? null))->values()->all() ?: null,
            'seo_title' => $data['seo_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'hero_image_alt' => $data['hero_image_alt'] ?? $package->hero_image_alt,
            'noindex' => $request->boolean('noindex'),
            'is_published' => $request->boolean('is_published'),
            'is_featured' => $request->boolean('is_featured'),
            'sort' => (int) $request->input('sort', $package->sort ?? 0),
        ]);

        if ($request->hasFile('hero_image')) {
            $package->hero_image = $images->store($request->file('hero_image'), $data['hero_image_alt'])->path;
        }
        if ($request->hasFile('social_image')) {
            $base = $images->store($request->file('social_image'))->path;
            $package->social_image = $base.'-1600.webp';
        }
        $package->save();
        $package->experiences()->sync($request->input('experience_ids', []));
        $package->travelStyles()->sync($request->input('travel_style_ids', []));

        foreach ((array) $request->input('existing', []) as $id => $row) {
            $package->images()->whereKey($id)->update(['alt' => $row['alt'], 'sort' => (int) ($row['sort'] ?? 0)]);
        }
        foreach ((array) $request->file('new_images', []) as $i => $row) {
            if (! empty($row['file'])) {
                $alt = $request->input("new_images.$i.alt");
                $package->images()->create(['path' => $images->store($row['file'], $alt)->path, 'alt' => $alt, 'sort' => 100 + $i]);
            }
        }
    }
}
