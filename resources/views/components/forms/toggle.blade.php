@props(['id' => 1, 'model' => null])

<div x-data="{ isActive: @entangle($model) }" class="flex items-center">
    <div :class="isActive ? 'bg-green-400' : 'bg-gray-400'" class="relative rounded-full w-16 h-8 transition duration-200 ease-linear">
        <label for="toggle{{ $id }}" class="absolute left-0 bg-white border-2 mb-2 w-1/2 h-full rounded-full transition transform duration-100 ease-linear cursor-pointer"
               :class="isActive ? 'translate-x-full border-green-400' : 'translate-x-0 border-gray-400'"></label>
        <input x-ref="toggle{{ $id }}" type="checkbox" id="toggle{{ $id }}" class="appearance-none w-full h-full active:outline-none focus:outline-none"
               x-model="isActive" />
    </div>
    <span class="ml-2" x-text="isActive ? 'Ativo' : 'Inativo'"></span>
</div>
