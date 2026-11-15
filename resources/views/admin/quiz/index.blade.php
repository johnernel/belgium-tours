@extends('layouts.admin')
@section('title', 'Quiz')
@section('heading', 'Hero quiz')

@section('content')
<p class="mb-4 max-w-3xl text-slate">These are the questions in the "Start Your Adventure" card, in order. The contact form is always the final step. Changing a step's wording never loses past answers. Each answer is saved with the enquiry and sent to GoHighLevel.</p>

<div class="space-y-5">
@foreach($steps as $step)
    <section class="rounded-2xl bg-paper p-5 shadow-card" aria-label="Quiz step {{ $loop->iteration }}">
        <form method="POST" action="{{ route('admin.quiz.steps.update', $step) }}" class="grid gap-3 sm:grid-cols-[1fr_9rem_6rem_auto] sm:items-end">
            @csrf @method('PUT')
            <x-admin.input :name="'question'" label="Question {{ $loop->iteration }}" :value="$step->question" />
            <x-admin.select name="type" label="Answer style" :options="['tiles' => 'Tiles', 'select' => 'Dropdown']" :value="$step->type" />
            <x-admin.input name="sort" type="number" label="Order" :value="$step->sort" />
            <div class="flex items-center gap-3"><x-admin.check name="active" label="On" :checked="$step->active" /><x-button type="submit" size="sm">Save</x-button></div>
        </form>

        <div class="mt-4 overflow-x-auto"><table class="w-full min-w-[40rem] text-left text-sm">
            <thead class="text-slate"><tr><th class="py-1 pr-2">Answer</th><th class="pr-2">Stored value</th><th class="pr-2">Icon</th><th class="pr-2">Order</th><th class="pr-2">On</th><th></th></tr></thead>
            <tbody>
            @foreach($step->options as $o)
                <tr class="border-t border-ink/10">
                    <td class="py-2 pr-2"><input form="o{{ $o->id }}" name="label" value="{{ $o->label }}" aria-label="Answer text" class="min-h-10 w-full rounded-lg border-2 border-slate/30 px-2"></td>
                    <td class="pr-2"><input form="o{{ $o->id }}" name="value" value="{{ $o->value }}" aria-label="Stored value" class="min-h-10 w-full rounded-lg border-2 border-slate/30 px-2"></td>
                    <td class="pr-2"><select form="o{{ $o->id }}" name="icon" aria-label="Icon" class="min-h-10 rounded-lg border-2 border-slate/30 px-1"><option value="">None</option>@foreach($icons as $k)<option value="{{ $k }}" @selected($o->icon === $k)>{{ $k }}</option>@endforeach</select></td>
                    <td class="pr-2"><input form="o{{ $o->id }}" type="number" name="sort" value="{{ $o->sort }}" aria-label="Order" class="min-h-10 w-16 rounded-lg border-2 border-slate/30 px-2"></td>
                    <td class="pr-2"><input form="o{{ $o->id }}" type="checkbox" name="active" value="1" @checked($o->active) aria-label="Active" class="size-5 accent-ink"></td>
                    <td class="whitespace-nowrap text-right">
                        <button form="o{{ $o->id }}" class="mr-2 font-semibold underline">Save</button>
                        <button form="od{{ $o->id }}" class="font-semibold text-red-800 underline">Delete</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table></div>
        @foreach($step->options as $o)
            <form id="o{{ $o->id }}" method="POST" action="{{ route('admin.quiz.options.update', $o) }}" class="hidden">@csrf @method('PUT')</form>
            <form id="od{{ $o->id }}" method="POST" action="{{ route('admin.quiz.options.destroy', $o) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach

        <form method="POST" action="{{ route('admin.quiz.options.store', $step) }}" class="mt-3 flex flex-wrap items-end gap-2">
            @csrf
            <x-admin.input name="label" label="New answer" class="min-w-56 flex-1" />
            <x-admin.select name="icon" label="Icon" :options="$icons" blank="None" />
            <x-button type="submit" size="sm" variant="sky">Add answer</x-button>
        </form>
        <form method="POST" action="{{ route('admin.quiz.steps.destroy', $step) }}" class="mt-3" onsubmit="return confirm('Delete this step and its answers?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-red-800 underline">Delete this step</button></form>
    </section>
@endforeach
</div>

<form method="POST" action="{{ route('admin.quiz.steps.store') }}" class="mt-6 max-w-2xl space-y-3 rounded-2xl bg-paper p-5 shadow-card">
    @csrf
    <h2 class="text-xl">Add a step</h2>
    <x-admin.input name="question" label="Question" :required="true" />
    <x-admin.select name="type" label="Answer style" :options="['tiles' => 'Tiles (tap to continue)', 'select' => 'Dropdown (Next button)']" />
    <x-button type="submit">Add step</x-button>
</form>
@endsection
