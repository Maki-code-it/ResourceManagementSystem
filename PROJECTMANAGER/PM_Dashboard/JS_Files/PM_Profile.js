document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("editProfileModal");
  const editBtn = document.getElementById("edit-profile-btn");
  const closeBtn = document.querySelector(".close-btn");
  const form = document.getElementById("editProfileForm");

  // Open Modal with SweetAlert confirmation
  editBtn.addEventListener("click", (e) => {
      e.preventDefault(); // prevent default click

      Swal.fire({
          title: 'Are you sure?',
          text: "Do you want to edit your profile?",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, edit',
          cancelButtonText: 'Cancel'
      }).then((result) => {
          if (result.isConfirmed) {
              modal.style.display = "flex"; // show modal if confirmed
          }
      });
  });

  // Close Modal
  closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
  });

  window.addEventListener("click", e => {
      if (e.target === modal) modal.style.display = "none";
  });

  // Handle Form Submission
  form.addEventListener("submit", e => {
      e.preventDefault();
      const formData = new FormData(form);

      fetch("../PHP_Files/update_profile.php", {
          method: "POST",
          body: formData
      })
      .then(res => res.json())
      .then(data => {
          Swal.fire({
              icon: data.status === "success" ? "success" : "error",
              title: data.message,
              timer: 2000,
              showConfirmButton: false
          });

          if (data.status === "success") {
              setTimeout(() => window.location.reload(), 1500);
          }
      })
      .catch(err => console.error("Error updating profile:", err));
  });
});


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

document.addEventListener("DOMContentLoaded", async () => {
    try {
        const response = await fetch("../PHP_Files/fetch_profile.php");
        const result = await response.json();

        if (result.status === "success") {
            const data = result.data;

            document.querySelector(".profile-header-info h2").textContent = data.name || "No Name";

            document.querySelector(".profile-role").textContent = data.job_title || "Not set";

            const statusBadge = document.querySelector(".status-badge");
            const statusText = (data.status || "Unavailable").trim();
            statusBadge.innerHTML = `<span class="status-dot"></span> ${statusText}`;
            statusBadge.className = `status-badge ${statusText.toLowerCase().replace(/\s+/g, '-')}`;

        const avatar = document.querySelector(".profile-avatar-large");
        avatar.innerHTML = ""; // clear existing content

        if (data.profile_pic && data.profile_pic.trim() !== "") {
            // Use absolute path from web root
            const imgPath = `/ResourceManagementSystem/${data.profile_pic.replace(/^\/?/, '')}`;

            const img = document.createElement("img");
            img.src = imgPath;
            img.alt = "Profile Picture";
            img.classList.add("profile-img"); // add CSS class for sizing/circle
            avatar.appendChild(img);
        } else {
            const initials = data.name
                ? data.name.split(" ").map(n => n[0]).join("").toUpperCase()
                : "PM";
            avatar.textContent = initials;
        }
        } else {
            console.error(result.message);
        }
    } catch (err) {
        console.error("Error loading profile:", err);
    }
});

document.addEventListener("DOMContentLoaded", () => {
  const modal = document.getElementById("editProfModal");
  const openBtn = document.getElementById("edit-prof-btn");  // "Update Information" button
  const closeBtn = modal.querySelector(".close-btn");
  const form = document.getElementById("editProfForm");

  // Open modal with SweetAlert confirmation
  openBtn.addEventListener("click", (e) => {
      e.preventDefault();

      Swal.fire({
          title: 'Are you sure?',
          text: "Do you want to update your professional information?",
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: 'Yes, update',
          cancelButtonText: 'Cancel'
      }).then((result) => {
          if (result.isConfirmed) {
              modal.style.display = "flex"; // show modal if confirmed
          }
      });
  });

  // Close modal
  closeBtn.addEventListener("click", () => {
      modal.style.display = "none";
  });

  window.addEventListener("click", e => {
      if (e.target === modal) modal.style.display = "none";
  });

  // Handle form submission
  form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const formData = new FormData(form);
      try {
          const response = await fetch("../PHP_Files/update_professional.php", {
              method: "POST",
              body: formData
          });
          const result = await response.json();

          Swal.fire({
              icon: result.status === "success" ? "success" : "error",
              title: result.message,
              timer: 2000,
              showConfirmButton: false
          });

          if (result.status === "success") {
              setTimeout(() => window.location.reload(), 1500);
          }
      } catch (err) {
          console.error("Error updating professional info:", err);
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Something went wrong while updating your information.',
              timer: 2000,
              showConfirmButton: false
          });
      }
  });
});


document.addEventListener("DOMContentLoaded", async () => {
  try {
      const response = await fetch("../PHP_Files/fetch_professional.php");
      const result = await response.json();

      if (result.status === "success") {
          const data = result.data;

          const infoItems = document.querySelectorAll(".profile-info-item");

          infoItems.forEach(item => {
              const label = item.querySelector(".info-label").textContent.trim().toLowerCase();

              switch(label) {
                  case "email":
                      item.querySelector(".info-value").textContent = data.email || "-";
                      break;
                  case "phone no.":
                      item.querySelector(".info-value").textContent = data.phone_number || "-";
                      break;
                  case "location":
                      item.querySelector(".info-value").textContent = data.location || "-";
                      break;
                  case "department":
                      item.querySelector(".info-value").textContent = data.department || "-";
                      break;
                  case "employee id":
                      item.querySelector(".info-value").textContent = data.employee_id || "-";
                      break;
                  case "join date":
                      item.querySelector(".info-value").textContent = data.join_date 
                          ? new Date(data.join_date).toLocaleDateString('en-US', {year: 'numeric', month: 'long', day: 'numeric'})
                          : "-";
                      break;
              }
          });
      } else {
          console.error(result.message);
      }
  } catch (err) {
      console.error("Error fetching professional info:", err);
  }
});

document.addEventListener("DOMContentLoaded", async () => {
  const skillsContainer = document.querySelector(".skills-container");
  const addBtn = document.getElementById("addSkillBtn");
  const addSkillModal = document.getElementById("addSkillModal");
  const closeBtn = addSkillModal.querySelector(".close-btn");
  const saveBtn = document.getElementById("saveSkillBtn");
  const newSkillInput = document.getElementById("newSkillInput");

  // Load skills from DB
  async function loadSkills() {
    try {
      const response = await fetch("../PHP_Files/fetch_skills.php");
      const result = await response.json();

      if (result.status === "success") {
        skillsContainer.innerHTML = "";
        result.skills.forEach(skill => {
          const span = document.createElement("span");
          span.className = "skill-tag";
          span.textContent = skill;
          skillsContainer.appendChild(span);
        });
      } else {
        console.error(result.message);
      }
    } catch (err) {
      console.error("Error fetching skills:", err);
    }
  }

  // Initial load
  loadSkills();

  // Open modal with SweetAlert confirmation
  addBtn.addEventListener("click", (e) => {
    e.preventDefault();

    Swal.fire({
      title: 'Are you sure?',
      text: "Do you want to add a new skill?",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, add',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        addSkillModal.style.display = "flex";
      }
    });
  });

  // Close modal
  closeBtn.addEventListener("click", () => addSkillModal.style.display = "none");
  window.addEventListener("click", e => { if (e.target === addSkillModal) addSkillModal.style.display = "none"; });

  // Save new skill with SweetAlert confirmation
  saveBtn.addEventListener("click", async () => {
    const skill = newSkillInput.value.trim();
    if (!skill) {
      Swal.fire({ icon: 'warning', title: 'Oops!', text: 'Please enter a skill', timer: 2000, showConfirmButton: false });
      return;
    }

    Swal.fire({
      title: 'Are you sure?',
      text: `Do you want to add "${skill}" to your skills?`,
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Yes, add',
      cancelButtonText: 'Cancel'
    }).then(async (result) => {
      if (result.isConfirmed) {
        try {
          const formData = new FormData();
          formData.append("skill", skill);

          const res = await fetch("../PHP_Files/add_skill.php", { method: "POST", body: formData });
          const data = await res.json();

          Swal.fire({ icon: data.status === "success" ? 'success' : 'error', title: data.message, timer: 2000, showConfirmButton: false });

          if (data.status === "success") {
            loadSkills();
            newSkillInput.value = "";
            addSkillModal.style.display = "none";
          }
        } catch (err) {
          console.error("Error adding skill:", err);
          Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong while adding the skill.', timer: 2000, showConfirmButton: false });
        }
      }
    });
  });
});
