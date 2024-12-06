@if (Route::has('login'))
<livewire:auth.navigation />
@endif
<x-guest-layout>

        <livewire:tasks /> 
</x-guest-layout>
        
        