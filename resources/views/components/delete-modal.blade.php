{{--
    Delete Confirmation Modal Component

    Usage in your blade view:
    1. Include the modal at the bottom of your page:
       @include('components.delete-modal', ['id' => 'deleteModal', 'route' => 'admin.parents.destroy'])

    2. Add delete button:
       <button type="button" class="btn btn-danger btn-sm"
               onclick="confirmDelete({{ $parent->id }}, '{{ $parent->user->name }}')">
           <i class="fas fa-trash"></i>
       </button>
--}}

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="{{ $id }}Label">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ $title }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-trash-alt text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <p class="mb-2">{{ $message }}</p>
                    <p class="fw-bold text-danger" id="{{ $id }}ItemName"></p>
                </div>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>This action cannot be undone. All associated data will be permanently removed.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancel
                </button>
                <form id="{{ $id }}Form" action="" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Yes, Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(id, name) {
    var modal = document.getElementById('{{ $id }}');
    var form = document.getElementById('{{ $id }}Form');
    var itemNameEl = document.getElementById('{{ $id }}ItemName');

    // Set the item name
    if (itemNameEl) {
        itemNameEl.textContent = name || '';
    }

    // Build the action URL
    @if($route)
        var baseUrl = "{{ route($route, ['__ID__']) }}";
        form.action = baseUrl.replace('__ID__', id);
    @endif

    // Show the modal
    var bsModal = new bootstrap.Modal(modal);
    bsModal.show();
}
</script>
