<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="space-y-6" enctype="multipart/form-data">
        @csrf
        @method('patch')

        <!-- Name Fields -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label class="form-label" for="fname">First Name</label>
                <input type="text" id="fname" name="fname" class="form-input" 
                       value="{{ old('fname', $user->fname) }}" required autofocus autocomplete="given-name">
                @error('fname')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="mname">Middle Name</label>
                <input type="text" id="mname" name="mname" class="form-input" 
                       value="{{ old('mname', $user->mname) }}" autocomplete="additional-name">
                @error('mname')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <div class="form-group">
                <label class="form-label" for="lname">Last Name</label>
                <input type="text" id="lname" name="lname" class="form-input" 
                       value="{{ old('lname', $user->lname) }}" required autocomplete="family-name">
                @error('lname')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="extname">Suffix</label>
                <select id="extname" name="extname" class="form-input">
                    <option value="">None</option>
                    <option value="Jr." {{ old('extname', $user->extname) == 'Jr.' ? 'selected' : '' }}>Jr.</option>
                    <option value="Sr." {{ old('extname', $user->extname) == 'Sr.' ? 'selected' : '' }}>Sr.</option>
                    <option value="II" {{ old('extname', $user->extname) == 'II' ? 'selected' : '' }}>II</option>
                    <option value="III" {{ old('extname', $user->extname) == 'III' ? 'selected' : '' }}>III</option>
                    <option value="IV" {{ old('extname', $user->extname) == 'IV' ? 'selected' : '' }}>IV</option>
                </select>
                @error('extname')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- Contact Information -->
        <div class="form-group">
            <label class="form-label" for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" class="form-input" 
                   value="{{ old('phone', $user->phone) }}" required autocomplete="tel">
            @error('phone')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <!-- Location Fields -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
            <!-- Municipality -->
            <div class="form-group">
                <label class="form-label" for="municipality_id">Municipality</label>
                <select id="municipality_id" name="municipality_id" class="form-input" required>
                    <option value="">Select Municipality</option>
                    @foreach($municipalities as $municipality)
                        <option value="{{ $municipality->id }}" 
                            {{ old('municipality_id', $user->municipality_id) == $municipality->id ? 'selected' : '' }}>
                            {{ $municipality->name }}
                        </option>
                    @endforeach
                </select>
                @error('municipality_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <!-- Barangay -->
            <div class="form-group">
                <label class="form-label" for="barangay_id">Barangay</label>
                <select id="barangay_id" name="barangay_id" class="form-input" required>
                    <option value="">Select Barangay</option>
                    @if($user->barangay_id && $user->municipality_id)
                        @foreach($barangays as $barangay)
                            @if($barangay->municipality_id == $user->municipality_id)
                                <option value="{{ $barangay->id }}" 
                                    {{ old('barangay_id', $user->barangay_id) == $barangay->id ? 'selected' : '' }}>
                                    {{ $barangay->name }}
                                </option>
                            @endif
                        @endforeach
                    @endif
                </select>
                @error('barangay_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <!-- ID Card Upload -->
        <div class="form-group">
            <label class="form-label">ID Card Upload</label>
            <div class="file-upload-container">
                <div class="file-upload-box" id="fileUploadBox">
                    <div class="file-upload-icon">📷</div>
                    <div class="file-upload-text">
                        <h4>Upload your ID Card</h4>
                        <p>JPEG, JPG, or PNG (Max: 5MB)</p>
                    </div>
                    <button type="button" class="file-upload-btn" onclick="document.getElementById('id_card').click()">
                        Choose File
                    </button>
                    <input 
                        type="file" 
                        id="id_card" 
                        name="id_card" 
                        class="file-input" 
                        accept=".jpeg,.jpg,.png"
                        onchange="previewFile(this)"
                    >
                </div>
                <div class="file-preview" id="filePreview" style="display: none;">
                    <img id="previewImage" src="" alt="ID Card Preview">
                    <div class="file-info">
                        <span id="fileName"></span>
                        <span class="remove-file" onclick="removeFile()">Remove</span>
                    </div>
                </div>
            </div>
            @error('id_card')
                <div class="error-message">{{ $message }}</div>
            @enderror
            
            <!-- Display current ID card if exists -->
            @if($user->id_card_path)
                <div class="mt-3 current-file">
                    <p class="mb-2 text-sm text-gray-600">Current ID Card:</p>
                    <div class="current-id-card">
                        <img src="{{ route('profile.id-card.view') }}"
                             alt="Current ID Card"
                             class="current-id-image"
                             onerror="this.style.display='none'">
                        <div class="id-card-actions">
                            {{-- <button type="button" class="btn-view-id" onclick="viewFullIdCard()">
                                <i class="fas fa-expand"></i> View Full Size
                            </button> --}}
                            <button type="button" class="btn-download-id" onclick="downloadIdCard()">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Email -->
        <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <input type="email" id="email" name="email" class="form-input" 
                   value="{{ old('email', $user->email) }}" required autocomplete="username">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div>
                <p class="text-sm text-gray-800">
                    {{ __('Your email address is unverified.') }}

                    <button form="send-verification" class="btn btn-outline" style="margin-left: 10px;">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                </p>

                @if (session('status') === 'verification-link-sent')
                    <p class="success-message">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-primary">Save Changes</button>

            @if (session('status') === 'profile-updated')
                <p class="success-message">Saved.</p>
            @endif
        </div>
    </form>
</section>

<!-- Full Size ID Card Modal -->
<div class="modal fade" id="idCardModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ID Card - {{ $user->fname }} {{ $user->lname }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="text-center modal-body">
                <img id="fullSizeIdCard" src="" alt="Full Size ID Card" class="img-fluid">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="downloadIdCard()">
                    <i class="fas fa-download"></i> Download
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .file-upload-container {
        margin-top: 10px;
    }

    .file-upload-box {
        border: 2px dashed #d1d5db;
        border-radius: 8px;
        padding: 30px;
        text-align: center;
        background: #f9fafb;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .file-upload-box:hover {
        border-color: var(--denr-green);
        background: #f0f9f0;
    }

    .file-upload-box.dragover {
        border-color: var(--denr-green);
        background: #e8f5e8;
    }

    .file-upload-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .file-upload-text h4 {
        margin: 0 0 5px 0;
        color: #374151;
        font-size: 1.1rem;
    }

    .file-upload-text p {
        margin: 0;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .file-upload-btn {
        background: var(--denr-green);
        color: white;
        padding: 10px 20px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 15px;
        transition: all 0.3s ease;
    }

    .file-upload-btn:hover {
        background: var(--denr-light-green);
        transform: translateY(-1px);
    }

    .file-input {
        display: none;
    }

    .file-preview {
        margin-top: 15px;
        padding: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
    }

    .file-preview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 6px;
        margin-bottom: 10px;
    }

    .file-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .remove-file {
        color: #ef4444;
        cursor: pointer;
        font-weight: 500;
    }

    .remove-file:hover {
        text-decoration: underline;
    }

    .current-file {
        margin-top: 15px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .current-id-card {
        text-align: center;
    }

    .current-id-image {
        max-width: 300px;
        max-height: 200px;
        border-radius: 6px;
        border: 1px solid #ddd;
        margin-bottom: 10px;
    }

    .id-card-actions {
        display: flex;
        gap: 10px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-view-id, .btn-download-id {
        background: var(--denr-green);
        color: white;
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .btn-view-id:hover, .btn-download-id:hover {
        background: var(--denr-light-green);
        transform: translateY(-1px);
    }

    /* Form Styling */
    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: var(--denr-green);
    }

    .form-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 14px;
        transition: all 0.3s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--denr-green);
        box-shadow: 0 0 0 3px rgba(26, 71, 42, 0.1);
    }

    .form-input.error {
        border-color: #ef4444;
    }

    .error-message {
        color: #ef4444;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    .success-message {
        color: #22c55e;
        font-size: 0.875rem;
        margin-top: 5px;
    }

    /* Button Styles */
    .btn-primary {
        background: var(--denr-green);
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: var(--denr-light-green);
        transform: translateY(-1px);
    }

    .btn-outline {
        background: transparent;
        color: var(--denr-green);
        padding: 8px 16px;
        border: 1px solid var(--denr-green);
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .btn-outline:hover {
        background: var(--denr-green);
        color: white;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .file-upload-box {
            padding: 20px;
        }
        
        .form-input {
            padding: 10px;
        }
        
        .btn-primary {
            width: 100%;
        }
        
        .id-card-actions {
            flex-direction: column;
        }
        
        .current-id-image {
            max-width: 100%;
        }
    }
</style>

<script>
// File upload functionality
function previewFile(input) {
    const fileUploadBox = document.getElementById('fileUploadBox');
    const filePreview = document.getElementById('filePreview');
    const previewImage = document.getElementById('previewImage');
    const fileName = document.getElementById('fileName');

    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            input.value = '';
            return;
        }

        // Validate file type
        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!validTypes.includes(file.type)) {
            alert('Please select a valid image file (JPG, JPEG, PNG)');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImage.src = e.target.result;
            fileName.textContent = file.name;
            fileUploadBox.style.display = 'none';
            filePreview.style.display = 'block';
        }
        
        reader.readAsDataURL(file);
    }
}

function removeFile() {
    const fileInput = document.getElementById('id_card');
    const fileUploadBox = document.getElementById('fileUploadBox');
    const filePreview = document.getElementById('filePreview');
    
    fileInput.value = '';
    filePreview.style.display = 'none';
    fileUploadBox.style.display = 'block';
}

// View full size ID card
function viewFullIdCard() {
    const currentIdImage = document.querySelector('.current-id-image');
    const fullSizeImage = document.getElementById('fullSizeIdCard');
    
    if (currentIdImage && fullSizeImage) {
        fullSizeImage.src = currentIdImage.src;
        const modal = new bootstrap.Modal(document.getElementById('idCardModal'));
        modal.show();
    }
}

// Download ID card
function downloadIdCard() {
    const currentIdImage = document.querySelector('.current-id-image');
    if (currentIdImage) {
        const link = document.createElement('a');
        link.href = currentIdImage.src;
        link.download = `id-card-{{ $user->fname }}-{{ $user->lname }}.jpg`;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Municipality and Barangay dynamic dropdown
document.getElementById('municipality_id').addEventListener('change', function() {
    const municipalityId = this.value;
    const barangaySelect = document.getElementById('barangay_id');
    
    // Clear existing options
    barangaySelect.innerHTML = '<option value="">Select Barangay</option>';
    
    if (municipalityId) {
        // Fetch barangays for selected municipality
        fetch(`/api/municipalities/${municipalityId}/barangays`)
            .then(response => response.json())
            .then(data => {
                data.forEach(barangay => {
                    const option = document.createElement('option');
                    option.value = barangay.id;
                    option.textContent = barangay.name;
                    barangaySelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('Error fetching barangays:', error);
            }); 
    }
});

// Drag and drop functionality
const fileUploadBox = document.getElementById('fileUploadBox');
const fileInput = document.getElementById('id_card');

fileUploadBox.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.classList.add('dragover');
});

fileUploadBox.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
});

fileUploadBox.addEventListener('drop', function(e) {
    e.preventDefault();
    this.classList.remove('dragover');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        previewFile(fileInput);
    }
});
</script>