// Modern Profile Management with Axios and modern libraries
import axios from 'axios';
import Swal from 'sweetalert2';

class ProfileManager {
    constructor() {
        this.currentUserId = null;
        this.profileData = null;
        
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.checkAuthentication();
        this.loadProfileData();
    }

    setupEventListeners() {
        // Profile form submission
        const profileForm = document.getElementById('profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => this.handleProfileUpdate(e));
        }

        // Photo upload
        const photoInput = document.getElementById('photo-upload');
        if (photoInput) {
            photoInput.addEventListener('change', (e) => this.handlePhotoUpload(e));
        }

        // Interest tags
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('interest-tag')) {
                this.toggleInterest(e.target);
            }
        });

        // Skills form
        const skillsForm = document.getElementById('skills-form');
        if (skillsForm) {
            skillsForm.addEventListener('submit', (e) => this.handleSkillsUpdate(e));
        }
    }

    async checkAuthentication() {
        try {
            const response = await axios.get('/api/auth-check.php');
            if (!response.data.authenticated) {
                window.location.href = '/login.php';
                return;
            }
            
            this.currentUserId = response.data.user.id;
        } catch (error) {
            console.error('Auth check failed:', error);
            window.location.href = '/login.php';
        }
    }

    async loadProfileData() {
        try {
            const response = await axios.get('/api/profile.php');
            this.profileData = response.data.profile;
            
            this.populateProfileForm();
            this.updateProfileDisplay();
        } catch (error) {
            console.error('Failed to load profile:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to load profile data'
            });
        }
    }

    populateProfileForm() {
        if (!this.profileData) return;

        // Fill in form fields
        const fields = ['first_name', 'last_name', 'age', 'bio', 'stem_field', 'experience_level'];
        fields.forEach(field => {
            const input = document.getElementById(field);
            if (input && this.profileData[field]) {
                input.value = this.profileData[field];
            }
        });

        // Populate interests
        if (this.profileData.interests) {
            const interests = JSON.parse(this.profileData.interests);
            interests.forEach(interest => {
                const tag = document.querySelector(`[data-interest="${interest}"]`);
                if (tag) {
                    tag.classList.add('selected');
                }
            });
        }

        // Set profile photo
        if (this.profileData.photo_url) {
            const photoPreview = document.getElementById('photo-preview');
            if (photoPreview) {
                photoPreview.src = this.profileData.photo_url;
                photoPreview.style.display = 'block';
            }
        }
    }

    updateProfileDisplay() {
        if (!this.profileData) return;

        // Update profile card
        const profileName = document.getElementById('profile-name');
        if (profileName) {
            profileName.textContent = `${this.profileData.first_name} ${this.profileData.last_name}`;
        }

        const profileField = document.getElementById('profile-field');
        if (profileField && this.profileData.stem_field) {
            profileField.textContent = this.profileData.stem_field;
        }

        const profileBio = document.getElementById('profile-bio');
        if (profileBio && this.profileData.bio) {
            profileBio.textContent = this.profileData.bio;
        }

        // Update profile stats
        this.updateProfileStats();
    }

    async handleProfileUpdate(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);
        
        // Add selected interests
        const selectedInterests = Array.from(document.querySelectorAll('.interest-tag.selected'))
            .map(tag => tag.dataset.interest);
        
        formData.append('interests', JSON.stringify(selectedInterests));

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

        try {
            const response = await axios.post('/api/update-profile.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Profile Updated!',
                    text: response.data.message,
                    timer: 2000,
                    showConfirmButton: false
                });
                
                // Reload profile data
                await this.loadProfileData();
            } else {
                throw new Error(response.data.message);
            }
        } catch (error) {
            console.error('Profile update failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: error.response?.data?.message || error.message || 'Failed to update profile'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    async handlePhotoUpload(e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'Please select an image file'
            });
            return;
        }

        // Validate file size (5MB max)
        if (file.size > 5 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Please select an image smaller than 5MB'
            });
            return;
        }

        // Show preview
        const photoPreview = document.getElementById('photo-preview');
        if (photoPreview) {
            const reader = new FileReader();
            reader.onload = (e) => {
                photoPreview.src = e.target.result;
                photoPreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }

        // Upload immediately
        const formData = new FormData();
        formData.append('photo', file);

        try {
            const response = await axios.post('/api/upload-photo.php', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            if (response.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Photo Uploaded!',
                    text: 'Your profile photo has been updated',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Photo upload failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: 'Failed to upload photo. Please try again.'
            });
        }
    }

    toggleInterest(tag) {
        tag.classList.toggle('selected');
        
        // Add visual feedback
        if (tag.classList.contains('selected')) {
            tag.style.transform = 'scale(1.1)';
            setTimeout(() => {
                tag.style.transform = 'scale(1)';
            }, 150);
        }
    }

    async handleSkillsUpdate(e) {
        e.preventDefault();
        
        const form = e.target;
        const formData = new FormData(form);

        try {
            const response = await axios.post('/api/update-skills.php', formData);

            if (response.data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Skills Updated!',
                    text: 'Your skills have been saved',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
        } catch (error) {
            console.error('Skills update failed:', error);
            Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: 'Failed to update skills'
            });
        }
    }

    async updateProfileStats() {
        try {
            const response = await axios.get('/api/profile-stats.php');
            const stats = response.data.stats;

            // Update various stats displays
            const elements = {
                'profile-views': stats.profile_views,
                'total-matches': stats.total_matches,
                'messages-sent': stats.messages_sent,
                'profile-completion': stats.completion_percentage
            };

            Object.entries(elements).forEach(([id, value]) => {
                const element = document.getElementById(id);
                if (element && value !== undefined) {
                    element.textContent = value;
                }
            });

            // Update progress bar
            const progressBar = document.querySelector('.profile-completion-bar');
            if (progressBar && stats.completion_percentage) {
                progressBar.style.width = `${stats.completion_percentage}%`;
            }
        } catch (error) {
            console.error('Failed to load profile stats:', error);
        }
    }

    async deleteAccount() {
        const result = await Swal.fire({
            title: 'Delete Account?',
            text: 'This action cannot be undone. All your data will be permanently deleted.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete my account',
            cancelButtonText: 'Cancel'
        });

        if (result.isConfirmed) {
            try {
                const response = await axios.delete('/api/delete-account.php');

                if (response.data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account Deleted',
                        text: 'Your account has been permanently deleted',
                        timer: 3000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = '/';
                    });
                }
            } catch (error) {
                console.error('Account deletion failed:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Deletion Failed',
                    text: 'Failed to delete account. Please try again.'
                });
            }
        }
    }
}

// Initialize profile manager when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.profileManager = new ProfileManager();
});

// Export for global use
window.ProfileManager = ProfileManager;