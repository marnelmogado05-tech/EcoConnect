<section class="space-y-6">
<div class="space-y-6">
    <div class="max-w-xl text-sm text-gray-600">
        <p>
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </div>

    <!-- Modal Trigger -->
    <button type="button" class="btn-danger" onclick="openDeleteModal()">
        Delete Account
    </button>

    <!-- Delete Account Confirmation Modal -->
    <div id="deleteModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 500px; margin: 20px;">
            <h3 style="color: #ef4444; margin-bottom: 10px;">Delete Account</h3>
            <p style="margin-bottom: 20px; color: var(--denr-gray);">
                Are you sure you want to delete your account? This action cannot be undone.
            </p>
            
            <form method="post" action="{{ route('profile.destroy') }}" id="deleteForm">
                @csrf
                @method('delete')
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Enter your password to confirm" required>
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                
                <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                    <button type="button" class="btn btn-outline" onclick="closeDeleteModal()">
                        Cancel
                    </button>
                    <button type="submit" class="btn-danger">
                        Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openDeleteModal() {
    document.getElementById('deleteModal').style.display = 'flex';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDeleteModal();
    }
});
</script>
</section>
