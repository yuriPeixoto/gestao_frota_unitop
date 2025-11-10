@props([
'striped' => false,
'hover' => true,
'bordered' => false,
'responsive' => true,
'compact' => false,
'checkable' => false,
])

@php
$tableClasses = 'min-w-full divide-y divide-gray-200';
if ($bordered) $tableClasses .= ' border-gray-200 border';

$wrapperClasses = 'overflow-hidden shadow-sm rounded-lg';
if ($responsive) $wrapperClasses .= ' overflow-x-auto relative';
@endphp

<div class="{{ $wrapperClasses }}">
    @if($checkable)
    <div class="flex items-center p-2 bg-gray-50 border-b border-gray-200">
        <div class="flex items-center">
            <input type="checkbox" id="select-all-checkbox"
                class="form-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
            <label for="select-all-checkbox" class="ml-2 text-sm text-gray-700">Selecionar Todos</label>
        </div>
        <div class="ml-4 hidden" id="selected-actions">
            <button type="button" id="btn-selected-action"
                class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Ação com Selecionados
            </button>
        </div>
    </div>
    @endif
    <table {{ $attributes->merge(['class' => $tableClasses]) }}>
        {{ $slot }}
    </table>
</div>

@if($checkable)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const checkboxes = document.querySelectorAll('.table-row-checkbox');
        const selectedActions = document.getElementById('selected-actions');
        
        // Toggle all checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = isChecked;
                
                // Toggle row selected class
                const row = checkbox.closest('tr');
                if (row) {
                    if (isChecked) {
                        row.classList.add('bg-gray-100');
                    } else {
                        row.classList.remove('bg-gray-100');
                    }
                }
            });
            
            updateSelectedActions();
        });
        
        // Individual checkbox change
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                
                const row = this.closest('tr');
                if (row) {
                    if (this.checked) {
                        row.classList.add('bg-gray-100');
                    } else {
                        row.classList.remove('bg-gray-100');
                    }
                }
                
                updateSelectAllCheckbox();
                updateSelectedActions();
            });
        });
        
        // Check if all checkboxes are selected
        function updateSelectAllCheckbox() {
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            const someChecked = Array.from(checkboxes).some(checkbox => checkbox.checked);
            
            selectAllCheckbox.checked = allChecked;
            selectAllCheckbox.indeterminate = someChecked && !allChecked;
        }
        
        // Show/hide selected actions based on selection
        function updateSelectedActions() {
            const hasSelection = Array.from(checkboxes).some(checkbox => checkbox.checked);
            
            if (hasSelection) {
                selectedActions.classList.remove('hidden');
            } else {
                selectedActions.classList.add('hidden');
            }
        }
        
        // Initialize the UI state
        updateSelectAllCheckbox();
        updateSelectedActions();
        
        // Handle row click to toggle checkboxes
        document.querySelectorAll('[data-id]').forEach(row => {
            row.addEventListener('click', function(e) {
                // If the click is on the checkbox itself, don't handle it here
                if (e.target.classList.contains('table-row-checkbox')) {
                    return;
                }
                
                const id = this.getAttribute('data-id');
                if (!id) return;
                
                const checkbox = document.getElementById(`row-checkbox-${id}`);
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    
                    // Dispatch a change event to trigger the checkbox event listeners
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        });
        
        // Setup selected action button
        document.getElementById('btn-selected-action').addEventListener('click', function() {
            const selectedIds = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => checkbox.getAttribute('data-id'));
                
            console.log('Selected IDs:', selectedIds);
            // Implement your action here
            alert(`Ação com ${selectedIds.length} itens selecionados`);
        });
    });
    
    // Public function to toggle row selection
    function toggleRow(id) {
        if (!id) return;
        
        const checkbox = document.getElementById(`row-checkbox-${id}`);
        if (checkbox) {
            // We don't toggle here because the row click event will handle it
            // Just preventing default actions
        }
    }
</script>
@endif