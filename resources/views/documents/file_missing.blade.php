<x-app-layout>
    <div class="max-w-xl mx-auto px-4 py-12">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm p-6 text-center">
            <div class="mx-auto mb-4 w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-950 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            </div>
            <h1 class="text-lg font-bold text-slate-900 dark:text-white">This file is no longer on the server</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                <span class="font-semibold">{{ $document->title }}</span>
                @if($document->file_name)
                    <span class="text-slate-400">({{ $document->file_name }})</span>
                @endif
                was uploaded before files were saved permanently, and the server cleared it on a restart.
            </p>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Upload it again from the project's Documents tab. Files uploaded from now on are kept.
            </p>
            @if($document->project)
                <a href="{{ route('projects.show', ['project' => $document->project, 'tab' => 'documents']) }}" class="btn-primary text-xs inline-flex mt-5">
                    Go to the project's documents
                </a>
            @endif
        </div>
    </div>
</x-app-layout>
