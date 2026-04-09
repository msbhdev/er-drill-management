<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-2xl font-extrabold text-stone-900">Account Profile</h2>
            <p class="mt-1 text-sm text-stone-600">Review your shared role assignment and maintain the account password.</p>
        </div>
    </x-slot>

    <div class="space-y-6">
        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="max-w-3xl">
                    <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="rounded-[2rem] border border-white/70 bg-white/85 p-6 shadow-xl shadow-stone-900/5 backdrop-blur">
            <div class="max-w-3xl">
                    <livewire:profile.update-password-form />
            </div>
        </div>
    </div>
</x-app-layout>
