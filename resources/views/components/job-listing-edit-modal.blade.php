@props([
    'jobListing',
    'module'
])

<dialog id="description_modal_{{ $jobListing->id }}" class="modal">
    <div class="modal-box w-[92vw] max-w-3xl">
        <form method="POST" action="{{ route('dashboard.modules.job-listings.update', [$module, $jobListing]) }}">
            @csrf
            @method("PATCH")
            <button
                type="button"
                class="btn btn-sm btn-circle btn-outline absolute right-2 top-2"
                onclick="description_modal_{{ $jobListing->id }}.close()"
                aria-label="Close"
            >
                x
            </button>

            <header class="space-y-1">
                <h3 class="text-2xl font-bold text-primary">Job listing</h3>
            </header>

            <fieldset class="mt-4 flex flex-col gap-5">
                <label class="form-control">
                    <span class="label-text mb-1">Title</span>
                    <input
                        type="text"
                        name="name"
                        value="{{ $jobListing->name}}"
                        placeholder="Job Title"
                        class="input input-bordered w-full @error('name') input-error @enderror"
                        required
                    />
                    @error('name')
                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                </label>
                <label 
                class="form-control">
                    <span class="label-text mb-1">Description</span>
                    <textarea
                        name="description"
                        placeholder="Job description and requirements..."
                        class="textarea textarea-bordered h-64 w-full @error('description') textarea-error @enderror"

                        required
                    >{{old($jobListing->description)}}</textarea>
                    @error('description')
                        <span class="label-text-alt mt-1 text-error">{{ $message }}</span>
                    @enderror
                </label>
                <div class="flex justify-between">
                    <button 
                        type="button" 
                        class="btn btn-error btn-outline"
                        onclick="(() => {
                            description_modal_{{ $jobListing->id }}.close();
                            delete_modal_{{ $jobListing->id }}.showModal();
                        })()"
                    >
                        Delete job listing
                    </button>
                    <div>
                        <button type="reset" class="btn btn-outline mr-2"
                        onclick="description_modal_{{ $jobListing->id }}.close()">
                            Cancel edits
                        </button> 
                        <button type="submit" class="btn btn-primary">
                            Save changes
                        </button> 
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button type="submit">close</button>
    </form>
</dialog>
<dialog id="delete_modal_{{ $jobListing->id }}" class="modal">
    <div class="modal-box max-w-lg">
        <button 
            class="btn btn-sm btn-circle btn-outline absolute right-2 top-2" 
            aria-label="Close"
            onclick="delete_modal_{{ $jobListing->id }}.close()"
            >
            x
        </button>
        <form 
            method="POST" 
            action="{{ route('dashboard.modules.job-listings.delete', [$module, $jobListing])}}">
            @csrf
            @method("DELETE")
            <h4 class="text-lg font-semibold">
                Delete "{{ $jobListing->name }}"?
            </h4>
            <p class="mt-2 text-sm text-base-content/80">
                This will remove this job listing from any assignments that reference it.
            </p>
            <div class="mt-4 flex justify-between">
                <button type="button"
                    class='btn btn-outline'
                    onclick="delete_modal_{{ $jobListing->id }}.close()"
                >
                    Cancel
                </button>
                <button type="submit" class="btn btn-error">
                    Delete job listing
                </button>
            </div>
        </form>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>