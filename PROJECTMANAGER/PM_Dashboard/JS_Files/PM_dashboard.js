document.getElementById('requestForm').addEventListener('submit', async (e) => {
  e.preventDefault();

  const formData = new FormData();
  formData.append('projectName', document.getElementById('projectName').value);
  formData.append('resourceType', document.getElementById('resourceType').value);
  formData.append('numResources', document.getElementById('numResources').value);
  formData.append('skills', document.getElementById('skills').value);
  formData.append('startDate', document.getElementById('startDate').value);
  formData.append('duration', document.getElementById('duration').value);
  formData.append('priority', document.getElementById('priority').value);
  formData.append('notes', document.getElementById('notes').value);

  try {
    const response = await fetch('../PHP_Files/Process_Request.php', {
      method: 'POST',
      body: formData
    });

    const result = await response.json();

    if (result.status === 'success') {
      await Swal.fire({
        icon: 'success',
        title: 'Request Submitted',
        text: result.message || 'Your resource request was submitted successfully!',
        showConfirmButton: false,
        timer: 1500
      });
      // optional redirect to dashboard
      window.location.href = 'PM_dashboard.html';
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Submission Failed',
        text: result.message || 'There was an issue submitting your request.'
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
});

document.getElementById('requestForm').addEventListener('submit', async (e) => {
    e.preventDefault(); // prevent normal form submission

    // SweetAlert confirmation
    const confirm = await Swal.fire({
        title: "Submit Request?",
        text: "Are you sure you want to send this resource request?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Yes, submit",
    });

    if (!confirm.isConfirmed) return; // stop if user cancels

    // Collect form data
    const formData = new FormData(e.target);

    try {
        const response = await fetch('../../PHP_Files/Process_Request.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            await Swal.fire({
                icon: 'success',
                title: 'Request Submitted',
                text: result.message || 'Your resource request was submitted successfully!',
                showConfirmButton: false,
                timer: 1500
            });
            e.target.reset(); // reset form
            window.location.href = 'PM_dashboard.html'; // optional redirect
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Submission Failed',
                text: result.message || 'There was an issue submitting your request.'
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
});



