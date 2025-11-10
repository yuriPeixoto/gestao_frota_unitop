@props(['striped' => false])

<tbody class="bg-white divide-y divide-gray-200" {{ $attributes }}>
    {{ $slot }}
</tbody>