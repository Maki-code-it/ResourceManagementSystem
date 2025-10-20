// Load projects on page load
document.addEventListener('DOMContentLoaded', async () => {
    await loadProjects();
});

async function loadProjects() {
    try {
        const response = await fetch('../PHP_Files/Get_Projects.php');
        
        console.log('Response status:', response.status); // Debug log
        const data = await response.json();

        if (data.status === 'error') {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to load projects'
            });
            return;
        }

        // Update manager info
        if (data.manager) {
            document.getElementById('sidebarInitials').textContent = data.manager.initials;
            document.getElementById('headerInitials').textContent = data.manager.initials;
            document.getElementById('sidebarName').textContent = data.manager.name;
            document.getElementById('sidebarEmail').textContent = data.manager.email;
        }

        // Render projects
        renderProjects(data.projects);

    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load projects. Please try again later.'
        });
    }
}

function renderProjects(projects) {
    const projectsList = document.getElementById('projectsList');

    if (!projects || projects.length === 0) {
        projectsList.innerHTML = `
            <div style="text-align: center; padding: 40px; color: #666;">
                <p>No projects found. Click "New Project" to create your first project.</p>
            </div>
        `;
        return;
    }

    projectsList.innerHTML = projects.map(project => `
        <div class="project-card" data-project-name="${project.project_name.toLowerCase()}">
            <div class="project-card-header">
                <div class="project-card-title">
                    <h3>${escapeHtml(project.project_name)}</h3>
                    <span class="badge ${project.badge_class}">
                        ${escapeHtml(project.display_status)}
                    </span>
                </div>
                <div class="project-actions">
                    <button class="icon-btn" onclick="editProject(${project.id})" title="Edit">✏️</button>
                    <button class="icon-btn" onclick="deleteProject(${project.id})" title="Delete">🗑️</button>
                </div>
            </div>
            
            <div class="project-card-body">
                <div class="project-detail">
                    <p class="detail-label">Progress</p>
                    <div class="progress-wrapper">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: ${project.progress}%"></div>
                        </div>
                        <span class="progress-text">${project.progress}%</span>
                    </div>
                </div>
                
                <div class="project-detail">
                    <p class="detail-label">Deadline</p>
                    <p class="detail-value">
                        <span class="icon">🕐</span>
                        ${project.deadline}
                    </p>
                </div>
                
                <div class="project-detail">
                    <p class="detail-label">Team (${project.assigned_employees}/${project.employees_needed})</p>
                    <div class="team-avatars">
                        ${renderTeamMembers(project.team_members)}
                    </div>
                </div>

                <div class="project-detail">
                    <p class="detail-label">Required Skills</p>
                    <p class="detail-value" style="font-size: 13px;">
                        ${escapeHtml(project.required_skills)}
                    </p>
                </div>
            </div>
        </div>
    `).join('');
}

function renderTeamMembers(teamMembers) {
    if (!teamMembers || teamMembers.length === 0) {
        return '<span style="font-size: 12px; color: #666;">No team members assigned yet</span>';
    }

    return teamMembers.map(member => {
        const initial = member.name.charAt(0).toUpperCase();
        return `<div class="avatar" title="${escapeHtml(member.name)}">${initial}</div>`;
    }).join('');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function redirectToNewProject() {
    window.location.href = 'PM_RequestProject.html';
}

async function editProject(projectId) {
    const result = await Swal.fire({
        icon: 'info',
        title: 'Coming Soon',
        text: 'Edit functionality is under development.',
        confirmButtonText: 'OK'
    });
}

async function deleteProject(projectId) {
    const result = await Swal.fire({
        title: 'Delete Project?',
        text: 'Are you sure you want to delete this project? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    });

    if (!result.isConfirmed) return;

    try {
        const response = await fetch('../PHP_Files/Delete_Project.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ project_id: projectId })
        });

        const data = await response.json();

        if (data.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Deleted!',
                text: data.message || 'Project has been deleted.',
                showConfirmButton: false,
                timer: 1500
            });
            await loadProjects(); // Reload projects
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to delete project'
            });
        }
    } catch (error) {
        console.error(error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Something went wrong. Please try again later.'
        });
    }
}

function searchProjects() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toLowerCase();
    const cards = document.getElementsByClassName('project-card');

    for (let i = 0; i < cards.length; i++) {
        const projectName = cards[i].getAttribute('data-project-name');
        if (projectName.includes(filter)) {
            cards[i].style.display = '';
        } else {
            cards[i].style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const logoutLink = document.querySelector('.logout');

    if (logoutLink) {
        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: 'You will be logged out of your session.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, logout',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = this.getAttribute('href');
                }
            });
        });
    }
});