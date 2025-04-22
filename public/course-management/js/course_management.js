// public/js/course_management.js
document.getElementById('createCourseForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = {
        code: e.target.code.value,
        title: e.target.title.value
    };
    
    try {
        const response = await fetch('/backend/api/courses.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= $_SESSION['csrf_token'] ?? '' ?>'
            },
            body: JSON.stringify(formData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('responseMessage').innerHTML = 
                '<div class="alert success">Course created successfully!</div>';
            // Refresh course list
            location.reload();
        } else {
            document.getElementById('responseMessage').innerHTML = 
                `<div class="alert error">${data.error || 'Unknown error'}</div>`;
        }
    } catch (error) {
        document.getElementById('responseMessage').innerHTML = 
            '<div class="alert error">Network error occurred</div>';
    }
});